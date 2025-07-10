<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $order_id = $_POST['order_id'];
    $tracking_number = $_POST['tracking_number'];
    $carrier = $_POST['carrier'];
    $status = $_POST['status'];
    
    try {
        $db->beginTransaction();
        
        // Insert tracking information
        $tracking_query = "INSERT INTO tracking (order_id, tracking_number, carrier, status) VALUES (:order_id, :tracking_number, :carrier, :status)";
        $tracking_stmt = $db->prepare($tracking_query);
        $tracking_stmt->bindParam(':order_id', $order_id);
        $tracking_stmt->bindParam(':tracking_number', $tracking_number);
        $tracking_stmt->bindParam(':carrier', $carrier);
        $tracking_stmt->bindParam(':status', $status);
        $tracking_stmt->execute();
        
        // Update order status
        $order_status = $status === 'Delivered' ? 'delivered' : 'shipped';
        $order_query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->bindParam(':status', $order_status);
        $order_stmt->bindParam(':order_id', $order_id);
        $order_stmt->execute();
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Tracking information added successfully']);
        
    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>