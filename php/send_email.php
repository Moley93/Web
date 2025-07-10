<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $type = $input['type'];
    $email = $input['email'];
    
    // Email configuration
    $smtp_host = 'ukc10.uk';
    $smtp_username = 'admin@vylodma.com';
    $smtp_password = 'M0l3y1993#][';
    $from_email = 'admin@vylo.com';
    $from_name = 'VYLO';
    
    try {
        if ($type === 'cart_reminder') {
            $cartItems = $input['cartItems'];
            $subject = 'Don\'t forget your VYLO items!';
            $message = generateCartReminderEmail($cartItems);
        } elseif ($type === 'order_confirmation') {
            $orderData = $input['orderData'];
            $subject = 'Order Confirmation - ' . $orderData['orderNumber'];
            $message = generateOrderConfirmationEmail($orderData);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email type']);
            exit;
        }
        
        // Send email using your preferred method (PHPMailer, native mail(), or API)
        $emailSent = sendEmail($email, $subject, $message, $from_email, $from_name);
        
        echo json_encode(['success' => $emailSent]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function generateCartReminderEmail($cartItems) {
    $itemsList = '';
    foreach ($cartItems as $item) {
        $itemsList .= "<li>{$item['name']} - £{$item['price']} x {$item['quantity']}</li>";
    }
    
    return "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #6366f1;'>Don't forget your VYLO items!</h2>
            <p>You left some great items in your basket:</p>
            <ul style='margin: 20px 0;'>
                {$itemsList}
            </ul>
            <p>Complete your purchase now to secure these innovative hardware products.</p>
            <a href='https://vylo.com/checkout' style='background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin: 20px 0;'>Complete Purchase</a>
            <p>Best regards,<br>The VYLO Team</p>
        </div>
    </body>
    </html>";
}

function generateOrderConfirmationEmail($orderData) {
    $itemsList = '';
    foreach ($orderData['items'] as $item) {
        $itemsList .= "<li>{$item['name']} - £{$item['price']} x {$item['quantity']}</li>";
    }
    
    return "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #6366f1;'>Order Confirmation</h2>
            <p>Thank you for your order! Here are the details:</p>
            <p><strong>Order Number:</strong> {$orderData['orderNumber']}</p>
            <p><strong>Items:</strong></p>
            <ul style='margin: 20px 0;'>
                {$itemsList}
            </ul>
            <p><strong>Total:</strong> £{$orderData['total']}</p>
            <p>Your order will be processed within 1-2 business days. You'll receive tracking information once your items ship.</p>
            <p>Need support? Join our Discord: <a href='https://discord.gg/vylo'>discord.gg/vylo</a></p>
            <p>Best regards,<br>The VYLO Team</p>
        </div>
    </body>
    </html>";
}

function sendEmail($to, $subject, $message, $from_email, $from_name) {
    // Using PHP's mail() function (you can replace this with PHPMailer or API)
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$from_name} <{$from_email}>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>