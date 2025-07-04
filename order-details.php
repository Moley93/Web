<?php
// order-details.php - Order Details Page
$page_title = "Order Details";
require_once 'includes/header.php';

// Require login
if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    redirect('profile.php?tab=orders');
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.email, u.first_name, u.last_name
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('profile.php?tab=orders');
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.description, p.image_url
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    // Parse shipping address
    $addresses = json_decode($order['shipping_address'], true);
    $shipping_address = $addresses['shipping'] ?? [];
    $billing_address = $addresses['billing'] ?? [];
    
} catch(PDOException $e) {
    redirect('profile.php?tab=orders');
}
?>

<div class="container" style="padding: 2rem 0;">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 2rem; color: var(--text-light);">
        <a href="index.php" style="color: var(--primary-color);">Home</a> 
        → <a href="profile.php?tab=orders" style="color: var(--primary-color);">My Orders</a> 
        → Order Details
    </div>

    <!-- Order Header -->
    <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Order <?php echo htmlspecialchars($order['order_number']); ?></h1>
                <p style="color: var(--text-light);">
                    Placed on <?php echo date('j F Y \a\t H:i', strtotime($order['created_at'])); ?>
                </p>
            </div>
            
            <div style="text-align: right;">
                <span class="order-status status-<?php echo $order['status']; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo ucfirst($order['status']); ?>
                </span>
                <div style="margin-top: 0.5rem; font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                    <?php echo format_price($order['total_amount']); ?>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
        
        <!-- Order Items -->
        <div>
            <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h2>Order Items</h2>
                </div>
                
                <div style="padding: 1.5rem;">
                    <?php foreach ($order_items as $item): ?>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                        <div style="width: 80px; height: 80px; background: var(--bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                            <?php else: ?>
                                <i class="fas fa-microchip" style="color: var(--text-light); font-size: 1.5rem;"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div style="flex: 1;">
                            <h3 style="margin-bottom: 0.5rem; font-size: 1.125rem;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </h3>
                            <p style="color: var(--text-light); margin-bottom: 0.5rem; font-size: 0.875rem;">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-light);">
                                    Qty: <?php echo $item['quantity']; ?> × <?php echo format_price($item['price']); ?>
                                </span>
                                <span style="font-weight: 700; color: var(--primary-color);">
                                    <?php echo format_price($item['price'] * $item['quantity']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Address -->
            <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow);">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h2>Shipping Information</h2>
                </div>
                
                <div style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Shipping Address</h3>
                            <div style="background: var(--bg-light); padding: 1rem; border-radius: 0.5rem;">
                                <?php if (!empty($shipping_address)): ?>
                                    <p style="margin-bottom: 0.25rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($shipping_address['first_name'] . ' ' . $shipping_address['last_name']); ?>
                                    </p>
                                    <p style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($shipping_address['address_line1']); ?></p>
                                    <?php if ($shipping_address['address_line2']): ?>
                                        <p style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($shipping_address['address_line2']); ?></p>
                                    <?php endif; ?>
                                    <p style="margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($shipping_address['city'] . ', ' . $shipping_address['postal_code']); ?>
                                    </p>
                                    <p style="margin-bottom: 0;"><?php echo htmlspecialchars($shipping_address['country']); ?></p>
                                    <?php if ($shipping_address['phone']): ?>
                                        <p style="margin-top: 0.5rem; color: var(--text-light); font-size: 0.875rem;">
                                            Phone: <?php echo htmlspecialchars($shipping_address['phone']); ?>
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p style="color: var(--text-light);">Address information not available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Billing Address</h3>
                            <div style="background: var(--bg-light); padding: 1rem; border-radius: 0.5rem;">
                                <?php if (!empty($billing_address) && $billing_address !== $shipping_address): ?>
                                    <p style="margin-bottom: 0.25rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($billing_address['first_name'] . ' ' . $billing_address['last_name']); ?>
                                    </p>
                                    <p style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($billing_address['address_line1']); ?></p>
                                    <?php if ($billing_address['address_line2']): ?>
                                        <p style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($billing_address['address_line2']); ?></p>
                                    <?php endif; ?>
                                    <p style="margin-bottom: 0.25rem;">
                                        <?php echo htmlspecialchars($billing_address['city'] . ', ' . $billing_address['postal_code']); ?>
                                    </p>
                                    <p style="margin-bottom: 0;"><?php echo htmlspecialchars($billing_address['country']); ?></p>
                                <?php else: ?>
                                    <p style="color: var(--text-light);">Same as shipping address</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div style="position: sticky; top: 100px;">
            <!-- Order Status -->
            <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h3>Order Status</h3>
                </div>
                
                <div style="padding: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.25rem;">Order Placed</h4>
                            <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                <?php echo date('j M Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: <?php echo $order['payment_status'] === 'completed' ? 'var(--success-color)' : 'var(--border-color)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas <?php echo $order['payment_status'] === 'completed' ? 'fa-check' : 'fa-clock'; ?>"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.25rem;">Payment <?php echo ucfirst($order['payment_status']); ?></h4>
                            <?php if ($order['payment_reference']): ?>
                                <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                    Ref: <?php echo htmlspecialchars($order['payment_reference']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'var(--success-color)' : 'var(--border-color)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'fa-check' : 'fa-clock'; ?>"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.25rem;">Shipped</h4>
                            <?php if ($order['tracking_number']): ?>
                                <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                    Track: <?php echo htmlspecialchars($order['tracking_number']); ?>
                                </p>
                            <?php else: ?>
                                <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                    <?php echo $order['status'] === 'shipped' ? 'In transit' : 'Pending shipment'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: <?php echo $order['status'] === 'delivered' ? 'var(--success-color)' : 'var(--border-color)'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas <?php echo $order['status'] === 'delivered' ? 'fa-check' : 'fa-clock'; ?>"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.25rem;">Delivered</h4>
                            <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                <?php echo $order['status'] === 'delivered' ? 'Package delivered' : 'Pending delivery'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Total -->
            <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h3>Order Summary</h3>
                </div>
                
                <div style="padding: 1.5rem;">
                    <?php
                    $subtotal = 0;
                    foreach ($order_items as $item) {
                        $subtotal += $item['price'] * $item['quantity'];
                    }
                    $delivery_cost = $subtotal >= 50 ? 0 : 4.99;
                    ?>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal</span>
                        <span><?php echo format_price($subtotal); ?></span>
                    </div>
                    
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--success-color);">
                        <span>Discount <?php echo $order['discount_code'] ? '(' . htmlspecialchars($order['discount_code']) . ')' : ''; ?></span>
                        <span>-<?php echo format_price($order['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <span>Delivery</span>
                        <span><?php echo $delivery_cost == 0 ? 'FREE' : format_price($delivery_cost); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.125rem; color: var(--primary-color);">
                        <span>Total</span>
                        <span><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php if ($order['tracking_number']): ?>
                    <a href="<?php echo FEDEX_TRACKING_URL . $order['tracking_number']; ?>" 
                       target="_blank" class="btn btn-primary" style="text-align: center;">
                        <i class="fas fa-truck"></i> Track Package
                    </a>
                <?php endif; ?>
                
                <?php if ($order['status'] === 'delivered'): ?>
                    <button onclick="reorderItems(<?php echo $order['id']; ?>)" class="btn btn-outline" style="text-align: center;">
                        <i class="fas fa-redo"></i> Reorder Items
                    </button>
                <?php endif; ?>
                
                <a href="profile.php?tab=orders" class="btn btn-outline" style="text-align: center;">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function reorderItems(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        fetch('api/reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                cartManager.showNotification('Items added to cart!', 'success');
                cartManager.updateCartDisplay();
            } else {
                cartManager.showNotification(result.message || 'Error adding items to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            cartManager.showNotification('Error adding items to cart', 'error');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>