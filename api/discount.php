<?php
// api/discount.php - Discount code management API
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'apply_discount':
            $discount_code = strtoupper(sanitize_input($_POST['discount_code'] ?? ''));
            
            if (empty($discount_code)) {
                $response['message'] = 'Please enter a discount code';
                break;
            }
            
            // Check if discount code exists and is valid
            $stmt = $pdo->prepare("
                SELECT * FROM discount_codes 
                WHERE code = ? AND is_active = 1 
                AND (expires_at IS NULL OR expires_at > NOW())
                AND (usage_limit IS NULL OR used_count < usage_limit)
            ");
            $stmt->execute([$discount_code]);
            $discount = $stmt->fetch();
            
            if (!$discount) {
                $response['message'] = 'Invalid or expired discount code';
                break;
            }
            
            // Calculate cart total
            $cart_total = 0;
            if (is_logged_in()) {
                $stmt = $pdo->prepare("
                    SELECT SUM(c.quantity * p.price) as total
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ? AND p.is_active = 1
                ");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT SUM(c.quantity * p.price) as total
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.session_id = ? AND p.is_active = 1
                ");
                $stmt->execute([session_id()]);
            }
            
            $result = $stmt->fetch();
            $cart_total = $result['total'] ?? 0;
            
            if ($cart_total == 0) {
                $response['message'] = 'Your cart is empty';
                break;
            }
            
            // Check minimum order requirement
            if ($discount['minimum_order'] > 0 && $cart_total < $discount['minimum_order']) {
                $response['message'] = 'Minimum order of ' . format_price($discount['minimum_order']) . ' required for this discount';
                break;
            }
            
            // Calculate discount amount
            $discount_amount = 0;
            if ($discount['discount_type'] === 'percentage') {
                $discount_amount = ($cart_total * $discount['discount_value']) / 100;
            } else {
                $discount_amount = $discount['discount_value'];
            }
            
            // Ensure discount doesn't exceed cart total
            $discount_amount = min($discount_amount, $cart_total);
            
            // Store discount in session
            $_SESSION['discount_code'] = $discount_code;
            $_SESSION['discount_amount'] = $discount_amount;
            $_SESSION['discount_id'] = $discount['id'];
            
            $response['success'] = true;
            $response['message'] = 'Discount code applied successfully';
            $response['discount_amount'] = $discount_amount;
            $response['formatted_discount'] = format_price($discount_amount);
            break;
            
        case 'remove_discount':
            unset($_SESSION['discount_code']);
            unset($_SESSION['discount_amount']);
            unset($_SESSION['discount_id']);
            
            $response['success'] = true;
            $response['message'] = 'Discount code removed';
            break;
            
        case 'validate_discount':
            $discount_code = strtoupper(sanitize_input($_POST['discount_code'] ?? ''));
            
            if (empty($discount_code)) {
                $response['message'] = 'Please enter a discount code';
                break;
            }
            
            // Check if discount code exists and is valid
            $stmt = $pdo->prepare("
                SELECT code, discount_type, discount_value, minimum_order, expires_at, usage_limit, used_count
                FROM discount_codes 
                WHERE code = ? AND is_active = 1
            ");
            $stmt->execute([$discount_code]);
            $discount = $stmt->fetch();
            
            if (!$discount) {
                $response['message'] = 'Discount code not found';
                break;
            }
            
            // Check expiry
            if ($discount['expires_at'] && strtotime($discount['expires_at']) < time()) {
                $response['message'] = 'Discount code has expired';
                break;
            }
            
            // Check usage limit
            if ($discount['usage_limit'] && $discount['used_count'] >= $discount['usage_limit']) {
                $response['message'] = 'Discount code usage limit reached';
                break;
            }
            
            $response['success'] = true;
            $response['message'] = 'Discount code is valid';
            $response['discount'] = [
                'code' => $discount['code'],
                'type' => $discount['discount_type'],
                'value' => $discount['discount_value'],
                'minimum_order' => $discount['minimum_order']
            ];
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
    
} catch (PDOException $e) {
    error_log("Discount API Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Discount API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);
?>