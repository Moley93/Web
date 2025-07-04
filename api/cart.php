<?php
// api/cart.php - Cart management API
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'add_to_cart':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0 || $quantity <= 0) {
                $response['message'] = 'Invalid product or quantity';
                break;
            }
            
            // Check if product exists and is active
            $stmt = $pdo->prepare("SELECT id, stock_quantity FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $response['message'] = 'Product not found';
                break;
            }
            
            // Check if we have enough stock
            if ($product['stock_quantity'] < $quantity) {
                $response['message'] = 'Not enough stock available';
                break;
            }
            
            // Check if item already exists in cart
            if (is_logged_in()) {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([session_id(), $product_id]);
            }
            
            $existing_item = $stmt->fetch();
            
            if ($existing_item) {
                // Update existing item
                $new_quantity = $existing_item['quantity'] + $quantity;
                
                if ($new_quantity > $product['stock_quantity']) {
                    $response['message'] = 'Cannot add more items - insufficient stock';
                    break;
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                // Add new item
                if (is_logged_in()) {
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([session_id(), $product_id, $quantity]);
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Product added to cart';
            break;
            
        case 'update_quantity':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity_action = $_POST['quantity_action'] ?? '';
            
            if ($product_id <= 0) {
                $response['message'] = 'Invalid product';
                break;
            }
            
            // Get current cart item
            if (is_logged_in()) {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([session_id(), $product_id]);
            }
            
            $cart_item = $stmt->fetch();
            
            if (!$cart_item) {
                $response['message'] = 'Item not found in cart';
                break;
            }
            
            $new_quantity = $cart_item['quantity'];
            
            if ($quantity_action === 'increase') {
                $new_quantity++;
            } elseif ($quantity_action === 'decrease') {
                $new_quantity--;
            }
            
            if ($new_quantity <= 0) {
                // Remove item from cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
                $stmt->execute([$cart_item['id']]);
                $response['message'] = 'Item removed from cart';
            } else {
                // Check stock availability
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($new_quantity > $product['stock_quantity']) {
                    $response['message'] = 'Not enough stock available';
                    break;
                }
                
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $cart_item['id']]);
                $response['message'] = 'Quantity updated';
            }
            
            $response['success'] = true;
            break;
            
        case 'update_quantity_direct':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if ($product_id <= 0 || $quantity < 0) {
                $response['message'] = 'Invalid product or quantity';
                break;
            }
            
            // Get current cart item
            if (is_logged_in()) {
                $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([session_id(), $product_id]);
            }
            
            $cart_item = $stmt->fetch();
            
            if (!$cart_item) {
                $response['message'] = 'Item not found in cart';
                break;
            }
            
            if ($quantity == 0) {
                // Remove item from cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
                $stmt->execute([$cart_item['id']]);
                $response['message'] = 'Item removed from cart';
            } else {
                // Check stock availability
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($quantity > $product['stock_quantity']) {
                    $response['message'] = 'Not enough stock available';
                    break;
                }
                
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$quantity, $cart_item['id']]);
                $response['message'] = 'Quantity updated';
            }
            
            $response['success'] = true;
            break;
            
        case 'remove_from_cart':
            $product_id = (int)($_POST['product_id'] ?? 0);
            
            if ($product_id <= 0) {
                $response['message'] = 'Invalid product';
                break;
            }
            
            if (is_logged_in()) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
                $stmt->execute([session_id(), $product_id]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
            break;
            
        case 'get_count':
            $count = get_cart_count();
            $response['success'] = true;
            $response['count'] = $count;
            break;
            
        case 'clear_cart':
            if (is_logged_in()) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
                $stmt->execute([session_id()]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Cart cleared';
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
    
} catch (PDOException $e) {
    error_log("Cart API Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);
?>