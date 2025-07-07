<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Authenticate user
    $authUser = getAuthUser();
    if (!$authUser) {
        sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid JSON input'], 400);
    }
    
    // Validate required fields
    $requiredFields = ['items', 'shipping_info', 'subtotal', 'total'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            sendJsonResponse(['success' => false, 'message' => "Field '$field' is required"], 400);
        }
    }
    
    // Validate items
    if (empty($input['items']) || !is_array($input['items'])) {
        sendJsonResponse(['success' => false, 'message' => 'Order must contain at least one item'], 400);
    }
    
    // Validate shipping information
    $shippingRequired = ['shipping_first_name', 'shipping_last_name', 'shipping_address_1', 'shipping_city', 'shipping_postcode', 'shipping_county'];
    foreach ($shippingRequired as $field) {
        if (empty($input['shipping_info'][$field])) {
            sendJsonResponse(['success' => false, 'message' => "Shipping field '$field' is required"], 400);
        }
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Calculate order totals
        $subtotal = 0;
        $validatedItems = [];
        
        // Validate each item and calculate totals
        foreach ($input['items'] as $item) {
            if (!isset($item['id'], $item['name'], $item['price'], $item['quantity'])) {
                throw new Exception('Invalid item data');
            }
            
            // Verify product exists and get current price
            $stmt = $db->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$item['id']]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Product '{$item['name']}' is no longer available");
            }
            
            // Check stock
            if ($product['stock_quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for '{$product['name']}'. Available: {$product['stock_quantity']}");
            }
            
            $itemTotal = $product['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $validatedItems[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'description' => $item['description'] ?? '',
                'quantity' => (int)$item['quantity'],
                'unit_price' => $product['price'],
                'total_price' => $itemTotal
            ];
        }
        
        // Calculate delivery cost
        $deliveryCost = 0;
        $deliveryMethod = $input['delivery_method'] ?? 'next_day';
        switch ($deliveryMethod) {
            case 'express':
                $deliveryCost = 9.99;
                break;
            case 'next_day':
            case 'collection':
            default:
                $deliveryCost = 0;
                break;
        }
        
        // Apply discount if provided
        $discountAmount = 0;
        if (isset($input['discount']) && $input['discount']) {
            $discount = $input['discount'];
            $discountAmount = calculateDiscountAmount($subtotal, $discount);
        }
        
        // Calculate VAT (20% on discounted subtotal + delivery)
        $taxableAmount = ($subtotal - $discountAmount) + $deliveryCost;
        $vatAmount = $taxableAmount * 0.2;
        
        // Calculate final total
        $totalAmount = $subtotal - $discountAmount + $deliveryCost + $vatAmount;
        
        // Validate total matches frontend calculation (allow small rounding differences)
        if (abs($totalAmount - $input['total']) > 0.02) {
            throw new Exception('Order total mismatch. Please refresh and try again.');
        }
        
        // Generate order ID
        $orderId = generateOrderId();
        
        // Sanitize shipping information
        $shippingInfo = [
            'first_name' => sanitizeInput($input['shipping_info']['shipping_first_name']),
            'last_name' => sanitizeInput($input['shipping_info']['shipping_last_name']),
            'company' => isset($input['shipping_info']['shipping_company']) ? sanitizeInput($input['shipping_info']['shipping_company']) : null,
            'address_1' => sanitizeInput($input['shipping_info']['shipping_address_1']),
            'address_2' => isset($input['shipping_info']['shipping_address_2']) ? sanitizeInput($input['shipping_info']['shipping_address_2']) : null,
            'city' => sanitizeInput($input['shipping_info']['shipping_city']),
            'postcode' => sanitizeInput(strtoupper($input['shipping_info']['shipping_postcode'])),
            'county' => sanitizeInput($input['shipping_info']['shipping_county']),
            'country' => isset($input['shipping_info']['shipping_country']) ? sanitizeInput($input['shipping_info']['shipping_country']) : 'GB',
            'phone' => isset($input['shipping_info']['shipping_phone']) ? sanitizeInput($input['shipping_info']['shipping_phone']) : null
        ];
        
        // Validate UK postcode
        if (!validateUKPostcode($shippingInfo['postcode'])) {
            throw new Exception('Invalid UK postcode format');
        }
        
        // Insert order
        $stmt = $db->prepare("
            INSERT INTO orders (
                id, user_id, status, subtotal, discount_amount, delivery_cost, vat_amount, total_amount,
                payment_method, payment_status,
                shipping_first_name, shipping_last_name, shipping_company,
                shipping_address_1, shipping_address_2, shipping_city, shipping_postcode,
                shipping_county, shipping_country, shipping_phone,
                delivery_method, delivery_instructions, created_at
            ) VALUES (
                ?, ?, 'pending', ?, ?, ?, ?, ?,
                'cryptocurrency', 'pending',
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, NOW()
            )
        ");
        
        $stmt->execute([
            $orderId,
            $authUser['user_id'],
            $subtotal,
            $discountAmount,
            $deliveryCost,
            $vatAmount,
            $totalAmount,
            $shippingInfo['first_name'],
            $shippingInfo['last_name'],
            $shippingInfo['company'],
            $shippingInfo['address_1'],
            $shippingInfo['address_2'],
            $shippingInfo['city'],
            $shippingInfo['postcode'],
            $shippingInfo['county'],
            $shippingInfo['country'],
            $shippingInfo['phone'],
            $deliveryMethod,
            $input['delivery_instructions'] ?? null
        ]);
        
        // Insert order items
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_description, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($validatedItems as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['description'],
                $item['quantity'],
                $item['unit_price'],
                $item['total_price']
            ]);
        }
        
        // Clear user's cart
        $stmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$authUser['user_id']]);
        
        // Commit transaction
        $db->commit();
        
        // Log order creation
        logError("Order created successfully", [
            'order_id' => $orderId,
            'user_id' => $authUser['user_id'],
            'total' => $totalAmount
        ]);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId,
            'total_amount' => $totalAmount,
            'payment_required' => true
        ], 201);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    logError("Database error during order creation", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    logError("Order creation error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}

function calculateDiscountAmount($subtotal, $discount) {
    if ($discount['type'] === 'percentage') {
        $amount = $subtotal * ($discount['discount'] / 100);
        if (isset($discount['maximum_discount_amount']) && $amount > $discount['maximum_discount_amount']) {
            return $discount['maximum_discount_amount'];
        }
        return $amount;
    } else {
        return min($discount['discount'], $subtotal);
    }
}
?>