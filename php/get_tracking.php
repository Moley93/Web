<?php
require_once 'config.php';

// Get authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    echo json_encode(['success' => false, 'message' => 'Authorization required']);
    exit;
}

$token = substr($authHeader, 7);
$payload = verifyJWT($token);

if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT t.*, o.order_number 
              FROM tracking t 
              JOIN orders o ON t.order_id = o.id 
              WHERE o.user_id = :user_id 
              ORDER BY t.updated_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $payload['user_id']);
    $stmt->execute();
    
    $tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'tracking' => $tracking]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>