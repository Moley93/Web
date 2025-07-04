<?php
// api/reorder.php - Reorder items API
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Require login
if (!is_logged_in()) {
    $response['message'] = 'Login required';
    echo json_encode($response);
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    $response['message'] = 'Invalid order ID';
    echo json_encode($response);
    exit;
}

try {
    // Verify order belongs to user
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $response['message'] = 'Order not found';
        echo json_encode($response);
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.product_id, oi.quantity, p.stock_quantity, p.is_active, p.name
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    if (empty($order_items)) {
        $response['message'] = 'No items found in order';
        echo json_encode($response);
        exit;
    }
    
    $added_items = [];
    $unavailable_items = [];
    
    foreach ($order_items as $item) {
        // Check if product is still active and available
        if (!$item['is_active']) {
            $unavailable_items[] = $item['name'] . ' (no longer available)';
            continue;
        }
        
        // Check stock availability
        $quantity_to_add = min($item['quantity'], $item['stock_quantity']);
        
        if ($quantity_to_add <= 0) {
            $unavailable_items[] = $item['name'] . ' (out of stock)';
            continue;
        }
        
        if ($quantity_to_add < $item['quantity']) {
            $unavailable_items[] = $item['name'] . ' (limited stock: only ' . $quantity_to_add . ' available)';
        }
        
        // Check if item already exists in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $item['product_id']]);
        $existing_item = $stmt->fetch();
        
        if ($existing_item) {
            // Update existing item
            $new_quantity = min($existing_item['quantity'] + $quantity_to_add, $item['stock_quantity']);
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
            
            $added_items[] = $item['name'] . ' (updated quantity)';
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $item['product_id'], $quantity_to_add]);
            
            $added_items[] = $item['name'];
        }
    }
    
    // Build response message
    if (!empty($added_items)) {
        $response['success'] = true;
        $response['message'] = count($added_items) . ' item(s) added to cart';
        
        if (!empty($unavailable_items)) {
            $response['message'] .= '. Some items were unavailable: ' . implode(', ', $unavailable_items);
        }
    } else {
        $response['message'] = 'No items could be added to cart: ' . implode(', ', $unavailable_items);
    }
    
} catch (PDOException $e) {
    error_log("Reorder API Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Reorder API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);
?>