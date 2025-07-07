<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid JSON input'], 400);
    }
    
    // Check if this is a MoonPay webhook or a manual verification
    if (isset($_SERVER['HTTP_MOONPAY_SIGNATURE'])) {
        // Handle MoonPay webhook
        handleMoonPayWebhook($input);
    } else {
        // Handle manual payment verification (for demo purposes)
        handleManualVerification($input);
    }
    
} catch (Exception $e) {
    logError("Payment verification error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}

function handleMoonPayWebhook($data) {
    // Verify webhook signature
    $signature = $_SERVER['HTTP_MOONPAY_SIGNATURE'];
    $payload = file_get_contents('php://input');
    
    $expectedSignature = hash_hmac('sha256', $payload, MOONPAY_WEBHOOK_SECRET);
    
    if (!hash_equals($signature, $expectedSignature)) {
        logError("Invalid MoonPay webhook signature", ['received_signature' => $signature]);
        sendJsonResponse(['success' => false, 'message' => 'Invalid signature'], 401);
    }
    
    // Process the webhook based on event type
    $eventType = $data['type'] ?? '';
    
    switch ($eventType) {
        case 'transaction_completed':
            processCompletedTransaction($data['data']);
            break;
        case 'transaction_failed':
            processFailedTransaction($data['data']);
            break;
        default:
            logError("Unknown MoonPay webhook event", ['event_type' => $eventType]);
            break;
    }
    
    sendJsonResponse(['success' => true, 'message' => 'Webhook processed']);
}

function handleManualVerification($input) {
    // For demo purposes - in production, this would verify with MoonPay API
    if (!isset($input['order_id'])) {
        sendJsonResponse(['success' => false, 'message' => 'Order ID is required'], 400);
    }
    
    $orderId = sanitizeInput($input['order_id']);
    $paymentStatus = $input['payment_status'] ?? 'completed';
    
    $db = Database::getInstance()->getConnection();
    
    // Get order details
    $stmt = $db->prepare("
        SELECT id, user_id, status, payment_status, total_amount,
               shipping_first_name, shipping_last_name
        FROM orders 
        WHERE id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        sendJsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    if ($order['payment_status'] === 'completed') {
        sendJsonResponse(['success' => true, 'message' => 'Payment already processed']);
    }
    
    // Update order status
    if ($paymentStatus === 'completed') {
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'completed', 
                status = 'processing',
                payment_reference = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            'DEMO-' . time(), // Demo payment reference
            $orderId
        ]);
        
        // Add tracking update
        $stmt = $db->prepare("
            INSERT INTO order_tracking_updates (order_id, status, notes, updated_by, created_at)
            VALUES (?, 'payment_completed', 'Payment verified successfully', 'system', NOW())
        ");
        $stmt->execute([$orderId]);
        
        // Queue confirmation email
        queueOrderConfirmationEmail($order);
        
        // Log payment completion
        logError("Payment completed", [
            'order_id' => $orderId,
            'user_id' => $order['user_id'],
            'amount' => $order['total_amount']
        ]);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Payment verified successfully',
            'order_status' => 'processing'
        ]);
    } else {
        // Handle failed payment
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'failed',
                status = 'cancelled',
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        // Restore product stock (this will be handled by the database trigger)
        
        sendJsonResponse([
            'success' => false,
            'message' => 'Payment failed',
            'order_status' => 'cancelled'
        ]);
    }
}

function processCompletedTransaction($transactionData) {
    $externalTransactionId = $transactionData['externalTransactionId'] ?? '';
    $transactionId = $transactionData['id'] ?? '';
    $amount = $transactionData['baseCurrencyAmount'] ?? 0;
    
    if (!$externalTransactionId) {
        logError("Missing external transaction ID in MoonPay webhook");
        return;
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Find order by external transaction ID
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND payment_status != 'completed'");
    $stmt->execute([$externalTransactionId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        logError("Order not found for completed transaction", ['external_id' => $externalTransactionId]);
        return;
    }
    
    // Verify amount matches
    if (abs($amount - $order['total_amount']) > 0.02) {
        logError("Amount mismatch in payment", [
            'order_id' => $order['id'],
            'expected' => $order['total_amount'],
            'received' => $amount
        ]);
        return;
    }
    
    // Update order
    $stmt = $db->prepare("
        UPDATE orders 
        SET payment_status = 'completed',
            status = 'processing',
            payment_reference = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$transactionId, $order['id']]);
    
    // Add tracking update
    $stmt = $db->prepare("
        INSERT INTO order_tracking_updates (order_id, status, notes, updated_by, created_at)
        VALUES (?, 'payment_completed', ?, 'moonpay_webhook', NOW())
    ");
    $stmt->execute([
        $order['id'],
        "Payment completed via MoonPay. Transaction ID: $transactionId"
    ]);
    
    // Queue confirmation email
    queueOrderConfirmationEmail($order);
    
    logError("Payment completed via MoonPay", [
        'order_id' => $order['id'],
        'transaction_id' => $transactionId,
        'amount' => $amount
    ]);
}

function processFailedTransaction($transactionData) {
    $externalTransactionId = $transactionData['externalTransactionId'] ?? '';
    $failureReason = $transactionData['failureReason'] ?? 'Unknown';
    
    if (!$externalTransactionId) {
        return;
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Update order status
    $stmt = $db->prepare("
        UPDATE orders 
        SET payment_status = 'failed',
            status = 'cancelled',
            updated_at = NOW()
        WHERE id = ? AND payment_status != 'completed'
    ");
    $stmt->execute([$externalTransactionId]);
    
    // Add tracking update
    $stmt = $db->prepare("
        INSERT INTO order_tracking_updates (order_id, status, notes, updated_by, created_at)
        VALUES (?, 'payment_failed', ?, 'moonpay_webhook', NOW())
    ");
    $stmt->execute([$externalTransactionId, "Payment failed: $failureReason"]);
    
    logError("Payment failed via MoonPay", [
        'order_id' => $externalTransactionId,
        'reason' => $failureReason
    ]);
}

function queueOrderConfirmationEmail($order) {
    $db = Database::getInstance()->getConnection();
    
    // Get user email
    $stmt = $db->prepare("SELECT email, first_name FROM users WHERE id = ?");
    $stmt->execute([$order['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return;
    }
    
    // Get order items
    $stmt = $db->prepare("
        SELECT product_name, quantity, unit_price, total_price
        FROM order_items 
        WHERE order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $items = $stmt->fetchAll();
    
    $subject = "Order Confirmation - " . $order['id'] . " - VYLO";
    
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemsHtml .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . formatPrice($item['unit_price']) . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . formatPrice($item['total_price']) . "</td>
            </tr>
        ";
    }
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background-color: #007acc; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .order-details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-radius: 5px; }
            .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .items-table th { background-color: #f4f4f4; padding: 10px; text-align: left; }
            .footer { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Order Confirmation</h1>
        </div>
        <div class='content'>
            <h2>Thank you for your order, {$user['first_name']}!</h2>
            <p>Your order has been confirmed and payment has been received. We'll process your order and send tracking information once it ships.</p>
            
            <div class='order-details'>
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> {$order['id']}</p>
                <p><strong>Order Date:</strong> " . date('d/m/Y H:i', strtotime($order['created_at'])) . "</p>
                <p><strong>Delivery Method:</strong> " . ucfirst(str_replace('_', ' ', $order['delivery_method'])) . "</p>
                <p><strong>Total Amount:</strong> " . formatPrice($order['total_amount']) . "</p>
            </div>
            
            <h3>Items Ordered</h3>
            <table class='items-table'>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style='text-align: center;'>Quantity</th>
                        <th style='text-align: right;'>Unit Price</th>
                        <th style='text-align: right;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    $itemsHtml
                </tbody>
            </table>
            
            <div class='order-details'>
                <h3>Shipping Address</h3>
                <p>
                    {$order['shipping_first_name']} {$order['shipping_last_name']}<br>
                    " . ($order['shipping_company'] ? $order['shipping_company'] . "<br>" : "") . "
                    {$order['shipping_address_1']}<br>
                    " . ($order['shipping_address_2'] ? $order['shipping_address_2'] . "<br>" : "") . "
                    {$order['shipping_city']}, {$order['shipping_postcode']}<br>
                    {$order['shipping_county']}
                </p>
            </div>
            
            <p>You can track your order status by logging into your account at <a href='" . SITE_URL . "/profile.html'>" . SITE_URL . "</a></p>
            
            <p>If you have any questions, please contact us at <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a></p>
            
            <p>Thank you for choosing VYLO!</p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " VYLO. All rights reserved.</p>
        </div>
    </body>
    </html>
    ";
    
    // Add to email queue
    $stmt = $db->prepare("
        INSERT INTO email_queue (recipient_email, subject, body, type, order_id, user_id, status, created_at)
        VALUES (?, ?, ?, 'order_confirmation', ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$user['email'], $subject, $body, $order['id'], $order['user_id']]);
}
?>