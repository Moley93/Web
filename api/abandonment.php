<?php
// api/abandonment.php - Cart abandonment email API
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'send_abandonment_email':
            // Check if user has items in cart
            $cart_items = [];
            $user_email = '';
            $user_name = '';
            
            if (is_logged_in()) {
                // Get user info
                $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $response['message'] = 'User not found';
                    break;
                }
                
                $user_email = $user['email'];
                $user_name = $user['first_name'] . ' ' . $user['last_name'];
                
                // Get cart items
                $stmt = $pdo->prepare("
                    SELECT c.*, p.name, p.price, p.image_url
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ? AND p.is_active = 1
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $cart_items = $stmt->fetchAll();
                
                // Check if we've already sent an abandonment email recently
                $stmt = $pdo->prepare("
                    SELECT id FROM abandoned_cart_emails 
                    WHERE user_id = ? AND email_sent_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $recent_email = $stmt->fetch();
                
                if ($recent_email) {
                    $response['message'] = 'Abandonment email already sent recently';
                    break;
                }
                
            } else {
                // For guest users, we need an email (this would require collecting it earlier)
                $response['message'] = 'Email required for abandonment tracking';
                break;
            }
            
            if (empty($cart_items)) {
                $response['message'] = 'No items in cart';
                break;
            }
            
            // Calculate cart total
            $cart_total = 0;
            foreach ($cart_items as $item) {
                $cart_total += $item['price'] * $item['quantity'];
            }
            
            // Build email content
            $subject = "Don't forget your items at VYLO!";
            
            $message = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #2563eb;'>Hi {$user_name},</h2>
                    
                    <p>You left some great items in your cart! Don't miss out on these quality hardware components.</p>
                    
                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Your Cart Items:</h3>
            ";
            
            foreach ($cart_items as $item) {
                $item_total = $item['price'] * $item['quantity'];
                $message .= "
                    <div style='display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;'>
                        <div style='flex: 1;'>
                            <h4 style='margin: 0; color: #1e293b;'>{$item['name']}</h4>
                            <p style='margin: 5px 0; color: #64748b;'>Qty: {$item['quantity']} × " . format_price($item['price']) . "</p>
                        </div>
                        <div style='font-weight: bold; color: #2563eb;'>
                            " . format_price($item_total) . "
                        </div>
                    </div>
                ";
            }
            
            $message .= "
                        <div style='text-align: right; font-size: 18px; font-weight: bold; color: #2563eb; margin-top: 15px;'>
                            Total: " . format_price($cart_total) . "
                        </div>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . SITE_URL . "/cart.php' 
                           style='background: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Complete Your Order
                        </a>
                    </div>
                    
                    <div style='background: #dbeafe; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4 style='margin-top: 0; color: #1e40af;'>🚚 Free Next-Day Delivery</h4>
                        <p style='margin-bottom: 0; color: #1e40af;'>Order before 3PM for next business day delivery on orders over £50!</p>
                    </div>
                    
                    <p>Need help? Reply to this email or contact our support team.</p>
                    
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;'>
                    
                    <p style='font-size: 12px; color: #64748b; text-align: center;'>
                        You're receiving this because you have items in your cart at VYLO.<br>
                        Don't want these emails? <a href='" . SITE_URL . "/unsubscribe.php?email=" . urlencode($user_email) . "'>Unsubscribe here</a>
                    </p>
                </div>
            ";
            
            // Send email
            if (send_email($user_email, $subject, $message)) {
                // Log the abandonment email
                if (is_logged_in()) {
                    $stmt = $pdo->prepare("INSERT INTO abandoned_cart_emails (user_id) VALUES (?)");
                    $stmt->execute([$_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO abandoned_cart_emails (session_id) VALUES (?)");
                    $stmt->execute([session_id()]);
                }
                
                $response['success'] = true;
                $response['message'] = 'Abandonment email sent successfully';
            } else {
                $response['message'] = 'Failed to send abandonment email';
            }
            break;
            
        case 'check_abandonment_status':
            // Check if cart has items and how long they've been there
            if (is_logged_in()) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as item_count, 
                           MIN(created_at) as oldest_item,
                           MAX(created_at) as newest_item
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ? AND p.is_active = 1
                ");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as item_count, 
                           MIN(created_at) as oldest_item,
                           MAX(created_at) as newest_item
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.session_id = ? AND p.is_active = 1
                ");
                $stmt->execute([session_id()]);
            }
            
            $cart_info = $stmt->fetch();
            
            $response['success'] = true;
            $response['has_items'] = $cart_info['item_count'] > 0;
            $response['item_count'] = $cart_info['item_count'];
            
            if ($cart_info['item_count'] > 0) {
                $response['oldest_item_age'] = time() - strtotime($cart_info['oldest_item']);
                $response['should_send_email'] = $response['oldest_item_age'] >= CART_ABANDONMENT_DELAY;
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
    
} catch (PDOException $e) {
    error_log("Abandonment API Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Abandonment API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);
?>