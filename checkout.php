<?php
// checkout.php - Checkout Page
$page_title = "Checkout";
require_once 'includes/header.php';

// Require login for checkout
if (!is_logged_in()) {
    redirect('login.php?redirect=checkout.php');
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('login.php');
    }
} catch(PDOException $e) {
    redirect('login.php');
}

// Get cart items
$cart_items = [];
$cart_total = 0;
$discount_amount = 0;
$discount_code = $_SESSION['discount_code'] ?? '';

try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.stock_quantity, p.image_url
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
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

// Redirect if cart is empty
if (empty($cart_items)) {
    redirect('cart.php');
}

$final_total = $cart_total - $discount_amount;
$delivery_cost = $final_total >= 50 ? 0 : 4.99;
$grand_total = $final_total + $delivery_cost;

// Handle form submission
$errors = [];
$processing = false;

if ($_POST) {
    $billing_same = isset($_POST['billing_same']);
    $shipping_address = [
        'first_name' => sanitize_input($_POST['shipping_first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['shipping_last_name'] ?? ''),
        'address_line1' => sanitize_input($_POST['shipping_address_line1'] ?? ''),
        'address_line2' => sanitize_input($_POST['shipping_address_line2'] ?? ''),
        'city' => sanitize_input($_POST['shipping_city'] ?? ''),
        'postal_code' => sanitize_input($_POST['shipping_postal_code'] ?? ''),
        'country' => sanitize_input($_POST['shipping_country'] ?? ''),
        'phone' => sanitize_input($_POST['shipping_phone'] ?? '')
    ];
    
    // Use billing address if same as shipping
    if ($billing_same) {
        $billing_address = $shipping_address;
    } else {
        $billing_address = [
            'first_name' => sanitize_input($_POST['billing_first_name'] ?? ''),
            'last_name' => sanitize_input($_POST['billing_last_name'] ?? ''),
            'address_line1' => sanitize_input($_POST['billing_address_line1'] ?? ''),
            'address_line2' => sanitize_input($_POST['billing_address_line2'] ?? ''),
            'city' => sanitize_input($_POST['billing_city'] ?? ''),
            'postal_code' => sanitize_input($_POST['billing_postal_code'] ?? ''),
            'country' => sanitize_input($_POST['billing_country'] ?? ''),
            'phone' => sanitize_input($_POST['billing_phone'] ?? '')
        ];
    }
    
    // Validation
    foreach (['first_name', 'last_name', 'address_line1', 'city', 'postal_code', 'country'] as $field) {
        if (empty($shipping_address[$field])) {
            $errors[] = "Shipping " . str_replace('_', ' ', $field) . " is required";
        }
    }
    
    if (!$billing_same) {
        foreach (['first_name', 'last_name', 'address_line1', 'city', 'postal_code', 'country'] as $field) {
            if (empty($billing_address[$field])) {
                $errors[] = "Billing " . str_replace('_', ' ', $field) . " is required";
            }
        }
    }
    
    // If no errors, create order and redirect to payment
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $order_number = generate_order_number();
            
            $shipping_address_json = json_encode([
                'shipping' => $shipping_address,
                'billing' => $billing_address
            ]);
            
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, discount_code, 
                                  discount_amount, status, payment_status, crypto_address, 
                                  shipping_address) 
                VALUES (?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $order_number,
                $grand_total,
                $discount_code,
                $discount_amount,
                CRYPTO_WALLET_ADDRESS,
                $shipping_address_json
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $pdo->commit();
            
            // Store order info in session for payment
            $_SESSION['pending_order'] = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'amount' => $grand_total
            ];
            
            // Redirect to payment
            redirect('payment.php');
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Failed to create order. Please try again.";
        }
    }
}
?>

<div class="container" style="padding: 2rem 0;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Checkout</h1>
        <p style="color: var(--text-light);">Complete your order details below</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <h4>Please correct the following errors:</h4>
            <ul style="margin-top: 0.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" data-validate>
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; align-items: start;">
            
            <!-- Checkout Form -->
            <div>
                <!-- Shipping Address -->
                <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-shipping-fast"></i> Shipping Address
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="shipping_first_name">First Name *</label>
                            <input type="text" id="shipping_first_name" name="shipping_first_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_first_name'] ?? $user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="shipping_last_name">Last Name *</label>
                            <input type="text" id="shipping_last_name" name="shipping_last_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_last_name'] ?? $user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="shipping_address_line1">Address Line 1 *</label>
                        <input type="text" id="shipping_address_line1" name="shipping_address_line1" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['shipping_address_line1'] ?? $user['address_line1']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="shipping_address_line2">Address Line 2</label>
                        <input type="text" id="shipping_address_line2" name="shipping_address_line2" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['shipping_address_line2'] ?? $user['address_line2']); ?>">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="shipping_city">City *</label>
                            <input type="text" id="shipping_city" name="shipping_city" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_city'] ?? $user['city']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="shipping_postal_code">Postal Code *</label>
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_postal_code'] ?? $user['postal_code']); ?>" required>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="shipping_country">Country *</label>
                            <select id="shipping_country" name="shipping_country" class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="United Kingdom" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="Ireland" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'Ireland' ? 'selected' : ''; ?>>Ireland</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="shipping_phone">Phone Number</label>
                            <input type="tel" id="shipping_phone" name="shipping_phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? $user['phone']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-credit-card"></i> Billing Address
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="billing_same" id="billing_same" 
                                   <?php echo isset($_POST['billing_same']) ? 'checked' : 'checked'; ?>>
                            <span>Same as shipping address</span>
                        </label>
                    </div>
                    
                    <div id="billing-fields" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label" for="billing_first_name">First Name</label>
                                <input type="text" id="billing_first_name" name="billing_first_name" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="billing_last_name">Last Name</label>
                                <input type="text" id="billing_last_name" name="billing_last_name" class="form-input">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="billing_address_line1">Address Line 1</label>
                            <input type="text" id="billing_address_line1" name="billing_address_line1" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="billing_address_line2">Address Line 2</label>
                            <input type="text" id="billing_address_line2" name="billing_address_line2" class="form-input">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label" for="billing_city">City</label>
                                <input type="text" id="billing_city" name="billing_city" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="billing_postal_code">Postal Code</label>
                                <input type="text" id="billing_postal_code" name="billing_postal_code" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="billing_country">Country</label>
                                <select id="billing_country" name="billing_country" class="form-select">
                                    <option value="">Select Country</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Ireland">Ireland</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Options -->
                <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-truck"></i> Delivery Options
                    </h3>
                    
                    <div style="border: 2px solid var(--primary-color); border-radius: 0.5rem; padding: 1rem; background: var(--bg-light);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin-bottom: 0.25rem;">Next Day Delivery</h4>
                                <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                    Order before 3PM for next business day delivery
                                </p>
                            </div>
                            <div style="font-weight: 600; color: var(--primary-color);">
                                <?php echo $delivery_cost == 0 ? 'FREE' : format_price($delivery_cost); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div style="position: sticky; top: 100px;">
                <div class="order-summary" style="background: white; border-radius: 1rem; box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 1.5rem;">Order Summary</h3>

                    <!-- Cart Items -->
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                        <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 50px; height: 50px; background: var(--bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                                <?php else: ?>
                                    <i class="fas fa-microchip" style="color: var(--text-light);"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <h5 style="margin-bottom: 0.25rem; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h5>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-light); font-size: 0.875rem;">
                                        Qty: <?php echo $item['quantity']; ?>
                                    </span>
                                    <span style="font-weight: 600;">
                                        <?php echo format_price($item['price'] * $item['quantity']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totals -->
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>

                    <?php if ($discount_amount > 0): ?>
                    <div class="summary-row" style="color: var(--success-color);">
                        <span>Discount (<?php echo htmlspecialchars($discount_code); ?>)</span>
                        <span>-<?php echo format_price($discount_amount); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Delivery</span>
                        <span><?php echo $delivery_cost == 0 ? 'FREE' : format_price($delivery_cost); ?></span>
                    </div>

                    <div class="summary-row" style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                        <span>Total</span>
                        <span><?php echo format_price($grand_total); ?></span>
                    </div>

                    <!-- Continue Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; font-size: 1.125rem; padding: 1rem;">
                        <i class="fas fa-credit-card"></i> Continue to Payment
                    </button>

                    <!-- Security Note -->
                    <div style="text-align: center; margin-top: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
                        <p style="font-size: 0.875rem; color: var(--text-light); margin: 0;">
                            <i class="fas fa-shield-alt" style="color: var(--success-color);"></i>
                            Secure checkout powered by MoonPay
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Toggle billing address fields
document.getElementById('billing_same').addEventListener('change', function() {
    const billingFields = document.getElementById('billing-fields');
    const billingInputs = billingFields.querySelectorAll('input, select');
    
    if (this.checked) {
        billingFields.style.display = 'none';
        billingInputs.forEach(input => {
            input.removeAttribute('required');
        });
    } else {
        billingFields.style.display = 'block';
        billingInputs.forEach(input => {
            if (['billing_first_name', 'billing_last_name', 'billing_address_line1', 'billing_city', 'billing_postal_code', 'billing_country'].includes(input.name)) {
                input.setAttribute('required', 'required');
            }
        });
    }
});

// Initialize billing toggle
document.getElementById('billing_same').dispatchEvent(new Event('change'));
</script>

<?php require_once 'includes/footer.php'; ?>