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
    
    // Validate required fields
    if (!isset($input['order_id']) || !isset($input['status'])) {
        sendJsonResponse(['success' => false, 'message' => 'Order ID and status are required'], 400);
    }
    
    $orderId = sanitizeInput($input['order_id']);
    $status = sanitizeInput($input['status']);
    $trackingNumber = isset($input['tracking_number']) ? sanitizeInput($input['tracking_number']) : null;
    $notes = isset($input['notes']) ? sanitizeInput($input['notes']) : null;
    $updatedBy = isset($input['updated_by']) ? sanitizeInput($input['updated_by']) : 'admin';
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($status, $validStatuses)) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if order exists
    $stmt = $db->prepare("SELECT id, user_id, status as current_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        sendJsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Update order status and tracking
        $updateFields = ['status = ?', 'updated_at = NOW()'];
        $updateValues = [$status];
        
        if ($trackingNumber) {
            $updateFields[] = 'tracking_number = ?';
            $updateFields[] = 'tracking_url = ?';
            $updateValues[] = $trackingNumber;
            $updateValues[] = generateFedExTrackingUrl($trackingNumber);
        }
        
        if ($status === 'shipped' && !$order['shipped_at']) {
            $updateFields[] = 'shipped_at = NOW()';
        }
        
        if ($status === 'delivered') {
            $updateFields[] = 'delivered_at = NOW()';
        }
        
        $updateValues[] = $orderId;
        
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($updateValues);
        
        // Add tracking update record
        $trackingNotes = $notes;
        if ($trackingNumber) {
            $trackingNotes = ($notes ? $notes . ' ' : '') . "Tracking number: $trackingNumber";
        }
        
        $stmt = $db->prepare("
            INSERT INTO order_tracking_updates (order_id, status, tracking_number, notes, updated_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$orderId, $status, $trackingNumber, $trackingNotes, $updatedBy]);
        
        // Send notification email based on status
        if ($status === 'shipped' && $trackingNumber) {
            queueShippingNotificationEmail($order['user_id'], $orderId, $trackingNumber);
        } elseif ($status === 'delivered') {
            queueDeliveryConfirmationEmail($order['user_id'], $orderId);
        }
        
        $db->commit();
        
        // Log the update
        logError("Order tracking updated", [
            'order_id' => $orderId,
            'old_status' => $order['current_status'],
            'new_status' => $status,
            'tracking_number' => $trackingNumber,
            'updated_by' => $updatedBy
        ]);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Order tracking updated successfully',
            'order_id' => $orderId,
            'status' => $status,
            'tracking_number' => $trackingNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    logError("Database error during tracking update", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    logError("Tracking update error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}

function generateFedExTrackingUrl($trackingNumber) {
    return "https://www.fedex.com/fedextrack/?trknbr=" . urlencode($trackingNumber);
}

function queueShippingNotificationEmail($userId, $orderId, $trackingNumber) {
    $db = Database::getInstance()->getConnection();
    
    // Get user and order details
    $stmt = $db->prepare("
        SELECT u.email, u.first_name, o.shipping_first_name, o.shipping_last_name,
               o.delivery_method, o.total_amount
        FROM users u
        JOIN orders o ON u.id = o.user_id
        WHERE u.id = ? AND o.id = ?
    ");
    $stmt->execute([$userId, $orderId]);
    $details = $stmt->fetch();
    
    if (!$details) {
        return;
    }
    
    $trackingUrl = generateFedExTrackingUrl($trackingNumber);
    $subject = "Your order has shipped - " . $orderId . " - VYLO";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background-color: #007acc; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .tracking-box { background-color: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px; text-align: center; }
            .button { background-color: #007acc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
            .footer { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Your order is on its way!</h1>
        </div>
        <div class='content'>
            <h2>Hi {$details['first_name']},</h2>
            <p>Great news! Your order <strong>$orderId</strong> has been shipped and is on its way to you.</p>
            
            <div class='tracking-box'>
                <h3>Track Your Package</h3>
                <p><strong>Tracking Number:</strong> $trackingNumber</p>
                <a href='$trackingUrl' class='button' target='_blank'>Track Package</a>
            </div>
            
            <p><strong>Delivery Information:</strong></p>
            <ul>
                <li><strong>Delivery Method:</strong> " . ucfirst(str_replace('_', ' ', $details['delivery_method'])) . "</li>
                <li><strong>Shipping Address:</strong> {$details['shipping_first_name']} {$details['shipping_last_name']}</li>
                <li><strong>Order Total:</strong> " . formatPrice($details['total_amount']) . "</li>
            </ul>
            
            <p>Your package is being delivered by FedEx. You can track its progress using the tracking number above or by visiting the FedEx website directly.</p>
            
            <p>If you have any questions about your order, please contact us at <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a></p>
            
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
        VALUES (?, ?, ?, 'shipping_notification', ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$details['email'], $subject, $body, $orderId, $userId]);
}

function queueDeliveryConfirmationEmail($userId, $orderId) {
    $db = Database::getInstance()->getConnection();
    
    // Get user and order details
    $stmt = $db->prepare("
        SELECT u.email, u.first_name, o.delivered_at, o.total_amount
        FROM users u
        JOIN orders o ON u.id = o.user_id
        WHERE u.id = ? AND o.id = ?
    ");
    $stmt->execute([$userId, $orderId]);
    $details = $stmt->fetch();
    
    if (!$details) {
        return;
    }
    
    $subject = "Delivery confirmed - " . $orderId . " - VYLO";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background-color: #44ff44; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .success-box { background-color: #f0fff0; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #44ff44; }
            .button { background-color: #007acc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
            .footer { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>✓ Delivery Confirmed</h1>
        </div>
        <div class='content'>
            <h2>Hi {$details['first_name']},</h2>
            
            <div class='success-box'>
                <h3>Your order has been delivered!</h3>
                <p><strong>Order:</strong> $orderId</p>
                <p><strong>Delivered:</strong> " . date('d/m/Y H:i', strtotime($details['delivered_at'])) . "</p>
                <p><strong>Total:</strong> " . formatPrice($details['total_amount']) . "</p>
            </div>
            
            <p>We hope you're satisfied with your purchase! If you have any issues with your order, please don't hesitate to contact us.</p>
            
            <h3>What's Next?</h3>
            <ul>
                <li>Leave a review for the products you purchased</li>
                <li>Browse our latest hardware arrivals</li>
                <li>Sign up for our newsletter for exclusive offers</li>
            </ul>
            
            <a href='" . SITE_URL . "/hardware.html' class='button'>Shop Again</a>
            
            <p>Thank you for choosing VYLO for your hardware needs!</p>
            
            <p>Best regards,<br>The VYLO Team</p>
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
        VALUES (?, ?, ?, 'delivery_confirmation', ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$details['email'], $subject, $body, $orderId, $userId]);
}
?>