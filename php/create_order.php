<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get authorization header (optional for guest checkout)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $user_id = null;
    
    if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
        $token = substr($authHeader, 7);
        $payload = verifyJWT($token);
        if ($payload) {
            $user_id = $payload['user_id'];
        }
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    $items = json_encode($input['items']);
    $shipping = json_encode($input['shipping']);
    $discount = $input['discount'] ? json_encode($input['discount']) : null;
    $total = $input['total'];
    $payment_id = $input['paymentId'];
    
    try {
        $db->beginTransaction();
        
        // Create order
        $query = "INSERT INTO orders (user_id, items, shipping_info, discount_info, total, payment_id, status, created_at) 
                  VALUES (:user_id, :items, :shipping, :discount, :total, :payment_id, 'paid', NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':items', $items);
        $stmt->bindParam(':shipping', $shipping);
        $stmt->bindParam(':discount', $discount);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':payment_id', $payment_id);
        
        if ($stmt->execute()) {
            $order_id = $db->lastInsertId();
            $order_number = 'VYLO-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
            
            // Update order with order number
            $update_query = "UPDATE orders SET order_number = :order_number WHERE id = :order_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':order_number', $order_number);
            $update_stmt->bindParam(':order_id', $order_id);
            $update_stmt->execute();
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'orderNumber' => $order_number,
                'orderId' => $order_id
            ]);
        } else {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Order creation failed']);
        }
        
    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>