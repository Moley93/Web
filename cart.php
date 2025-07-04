<?php
// cart.php - Shopping Cart Page
$page_title = "Shopping Cart";
require_once 'includes/header.php';

// Get cart items
$cart_items = [];
$cart_total = 0;
$discount_amount = 0;
$discount_code = $_SESSION['discount_code'] ?? '';

try {
    if (is_logged_in()) {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price, p.stock_quantity, p.image_url, p.description
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price, p.stock_quantity, p.image_url, p.description
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.session_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([session_id()]);
    }
    
    $cart_items = $stmt->fetchAll();
    
    // Calculate totals
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
    
    // Apply discount if set
    if ($discount_code && isset($_SESSION['discount_amount'])) {
        $discount_amount = $_SESSION['discount_amount'];
    }
    
} catch(PDOException $e) {
    $cart_items = [];
}

$final_total = $cart_total - $discount_amount;
$delivery_cost = $final_total >= 50 ? 0 : 4.99; // Free delivery over £50
$grand_total = $final_total + $delivery_cost;
?>

<div class="container" style="padding: 2rem 0;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Shopping Cart</h1>
        <p style="color: var(--text-light);">
            <?php echo count($cart_items); ?> item<?php echo count($cart_items) !== 1 ? 's' : ''; ?> in your cart
        </p>
    </div>

    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div style="text-align: center; padding: 4rem 0;">
            <div style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 style="margin-bottom: 1rem;">Your cart is empty</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">
                Add some quality hardware to get started with your next project.
            </p>
            <a href="hardware.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>

    <?php else: ?>
        <!-- Cart Content -->
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; align-items: start;">
            
            <!-- Cart Items -->
            <div>
                <div class="cart-table" style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-light);">
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Product</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600;">Quantity</th>
                                <th style="padding: 1rem; text-align: right; font-weight: 600;">Price</th>
                                <th style="padding: 1rem; text-align: right; font-weight: 600;">Total</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 60px; height: 60px; background: var(--bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                                            <?php else: ?>
                                                <i class="fas fa-microchip" style="color: var(--text-light);"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 style="margin-bottom: 0.25rem; font-size: 1rem;">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </h4>
                                            <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                                <?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>...
                                            </p>
                                            <?php if ($item['stock_quantity'] < $item['quantity']): ?>
                                                <p style="color: var(--error-color); font-size: 0.75rem; margin-top: 0.25rem;">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    Only <?php echo $item['stock_quantity']; ?> in stock
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <td style="padding: 1rem; text-align: center;">
                                    <div class="quantity-controls" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                        <button class="quantity-btn" data-action="decrease" data-product-id="<?php echo $item['product_id']; ?>">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock_quantity']; ?>"
                                               class="quantity-input" 
                                               data-product-id="<?php echo $item['product_id']; ?>"
                                               style="width: 60px; text-align: center; border: 1px solid var(--border-color); border-radius: 0.25rem; padding: 0.25rem;">
                                        <button class="quantity-btn" data-action="increase" data-product-id="<?php echo $item['product_id']; ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                                
                                <td style="padding: 1rem; text-align: right; font-weight: 600;">
                                    <?php echo format_price($item['price']); ?>
                                </td>
                                
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: var(--primary-color);">
                                    <?php echo format_price($item['price'] * $item['quantity']); ?>
                                </td>
                                
                                <td style="padding: 1rem; text-align: center;">
                                    <button class="btn btn-danger remove-from-cart" 
                                            data-product-id="<?php echo $item['product_id']; ?>"
                                            style="padding: 0.5rem; font-size: 0.875rem;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Continue Shopping -->
                <div style="margin-top: 1rem;">
                    <a href="hardware.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div style="position: sticky; top: 100px;">
                <div class="order-summary" style="background: white; border-radius: 1rem; box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Order Summary</h3>

                    <div class="summary-row">
                        <span>Subtotal (<?php echo array_sum(array_column($cart_items, 'quantity')); ?> items)</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>

                    <?php if ($discount_amount > 0): ?>
                    <div class="summary-row" style="color: var(--success-color);">
                        <span>
                            Discount (<?php echo htmlspecialchars($discount_code); ?>)
                            <button onclick="removeDiscount()" style="background: none; border: none; color: var(--error-color); margin-left: 0.5rem;">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                        <span>-<?php echo format_price($discount_amount); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>
                            Delivery
                            <?php if ($delivery_cost == 0): ?>
                                <small style="color: var(--success-color);">(Free over £50)</small>
                            <?php endif; ?>
                        </span>
                        <span><?php echo $delivery_cost == 0 ? 'FREE' : format_price($delivery_cost); ?></span>
                    </div>

                    <div class="summary-row" style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                        <span>Total</span>
                        <span><?php echo format_price($grand_total); ?></span>
                    </div>

                    <!-- Discount Code Section -->
                    <?php if (!$discount_code): ?>
                    <div class="discount-section" style="margin: 1.5rem 0;">
                        <h4 style="margin-bottom: 0.5rem;">Have a discount code?</h4>
                        <form id="discount-form">
                            <div class="discount-input-group">
                                <input type="text" id="discount-code" placeholder="Enter code" 
                                       class="form-input discount-input" style="margin-bottom: 0;">
                                <button type="submit" class="btn btn-outline">Apply</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Checkout Button -->
                    <div style="margin-top: 2rem;">
                        <?php if (is_logged_in()): ?>
                            <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.125rem; padding: 1rem;">
                                <i class="fas fa-lock"></i> Proceed to Checkout
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.125rem; padding: 1rem;">
                                <i class="fas fa-sign-in-alt"></i> Login to Checkout
                            </a>
                            <p style="text-align: center; margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-light);">
                                Or <a href="register.php" style="color: var(--primary-color);">create an account</a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Security Badge -->
                    <div style="text-align: center; margin-top: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                        <div style="color: var(--success-color); font-size: 1.5rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p style="font-size: 0.875rem; color: var(--text-light); margin: 0;">
                            Secure checkout with crypto payments via MoonPay
                        </p>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div style="background: var(--primary-color); color: white; padding: 1.5rem; border-radius: 1rem; margin-top: 1rem;">
                    <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-shipping-fast"></i> Next Day Delivery
                    </h4>
                    <p style="font-size: 0.875rem; margin: 0; opacity: 0.9;">
                        Order before 3PM for next business day delivery anywhere in the UK.
                    </p>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
// Update quantity when input changes
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const productId = this.dataset.productId;
        const quantity = parseInt(this.value);
        updateCartQuantity(productId, quantity);
    });
});

async function updateCartQuantity(productId, quantity) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_quantity_direct');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const response = await fetch('api/cart.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            cartManager.showNotification(result.message || 'Error updating quantity', 'error');
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        cartManager.showNotification('Error updating quantity', 'error');
    }
}

function removeDiscount() {
    if (confirm('Remove discount code?')) {
        fetch('api/discount.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove_discount'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                cartManager.showNotification('Error removing discount', 'error');
            }
        });
    }
}

// Track cart abandonment
if (window.cartAbandonmentTracking) {
    cartAbandonmentTracking.trackCartActivity();
}
</script>

<?php require_once 'includes/footer.php'; ?>