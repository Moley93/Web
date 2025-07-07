<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['code'])) {
        sendJsonResponse(['success' => false, 'message' => 'Discount code is required'], 400);
    }
    
    $code = sanitizeInput(strtoupper($input['code']));
    $orderTotal = isset($input['order_total']) ? (float)$input['order_total'] : 0;
    
    $db = Database::getInstance()->getConnection();
    
    // Get discount code details
    $stmt = $db->prepare("
        SELECT id, code, description, type, value, minimum_order_amount, 
               maximum_discount_amount, usage_limit, usage_count,
               valid_from, valid_until, is_active
        FROM discount_codes 
        WHERE code = ? AND is_active = 1
    ");
    $stmt->execute([$code]);
    $discount = $stmt->fetch();
    
    if (!$discount) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid discount code']);
    }
    
    // Check if discount is currently valid
    $now = new DateTime();
    $validFrom = new DateTime($discount['valid_from']);
    $validUntil = $discount['valid_until'] ? new DateTime($discount['valid_until']) : null;
    
    if ($now < $validFrom) {
        sendJsonResponse(['success' => false, 'message' => 'This discount code is not yet active']);
    }
    
    if ($validUntil && $now > $validUntil) {
        sendJsonResponse(['success' => false, 'message' => 'This discount code has expired']);
    }
    
    // Check usage limit
    if ($discount['usage_limit'] && $discount['usage_count'] >= $discount['usage_limit']) {
        sendJsonResponse(['success' => false, 'message' => 'This discount code has reached its usage limit']);
    }
    
    // Check minimum order amount
    if ($orderTotal > 0 && $orderTotal < $discount['minimum_order_amount']) {
        $minAmount = formatPrice($discount['minimum_order_amount']);
        sendJsonResponse(['success' => false, 'message' => "Minimum order amount of $minAmount required for this discount"]);
    }
    
    // Calculate discount amount
    $discountAmount = 0;
    if ($discount['type'] === 'percentage') {
        $discountAmount = $orderTotal * ($discount['value'] / 100);
        
        // Apply maximum discount limit if set
        if ($discount['maximum_discount_amount'] && $discountAmount > $discount['maximum_discount_amount']) {
            $discountAmount = $discount['maximum_discount_amount'];
        }
        
        $discountDisplay = $discount['value'] . '% off';
    } else {
        // Fixed amount discount
        $discountAmount = min($discount['value'], $orderTotal);
        $discountDisplay = formatPrice($discount['value']) . ' off';
    }
    
    // Check if user has already used this discount (optional - for single-use per user codes)
    $authUser = getAuthUser();
    if ($authUser) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as usage_count 
            FROM orders 
            WHERE user_id = ? AND payment_status = 'completed' 
            AND (discount_amount > 0 OR id IN (
                SELECT DISTINCT order_id 
                FROM order_tracking_updates 
                WHERE notes LIKE CONCAT('%', ?, '%')
            ))
        ");
        $stmt->execute([$authUser['user_id'], $code]);
        $userUsage = $stmt->fetch();
        
        // For certain codes, limit to one use per user
        $singleUsePerUserCodes = ['WELCOME10', 'STUDENT'];
        if (in_array($code, $singleUsePerUserCodes) && $userUsage['usage_count'] > 0) {
            sendJsonResponse(['success' => false, 'message' => 'You have already used this discount code']);
        }
    }
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Discount code is valid',
        'discount' => [
            'code' => $discount['code'],
            'description' => $discount['description'],
            'type' => $discount['type'],
            'discount' => $discount['value'],
            'discount_amount' => $discountAmount,
            'discount_display' => $discountDisplay,
            'minimum_order_amount' => $discount['minimum_order_amount'],
            'maximum_discount_amount' => $discount['maximum_discount_amount']
        ]
    ]);
    
} catch (PDOException $e) {
    logError("Database error during discount validation", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    logError("Discount validation error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Validation failed: ' . $e->getMessage()], 500);
}
?>