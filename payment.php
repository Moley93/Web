<?php
// payment.php - Payment Processing Page
$page_title = "Payment";
require_once 'includes/header.php';

// Require login
if (!is_logged_in()) {
    redirect('login.php?redirect=checkout.php');
}

// Check if we have a pending order
if (!isset($_SESSION['pending_order'])) {
    redirect('cart.php');
}

$pending_order = $_SESSION['pending_order'];
$order_id = $pending_order['order_id'];
$order_number = $pending_order['order_number'];
$amount = $pending_order['amount'];

// Get order details
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order || $order['payment_status'] === 'completed') {
        redirect('profile.php?tab=orders');
    }
} catch(PDOException $e) {
    redirect('cart.php');
}

// Handle payment verification
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'verify_payment') {
    $payment_reference = sanitize_input($_POST['payment_reference'] ?? '');
    
    if ($payment_reference) {
        try {
            // Update order with payment reference
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_reference = ?, payment_status = 'completed', status = 'paid' 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$payment_reference, $order_id, $_SESSION['user_id']]);
            
            // Get order items for email
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            // Send confirmation email
            $user_email = $_SESSION['user_email'];
            $user_name = $_SESSION['user_name'];
            
            $subject = "Order Confirmation - " . $order_number;
            
            $message = "
                <h2>Thank you for your order!</h2>
                <p>Dear {$user_name},</p>
                <p>We've received your payment and your order is being processed.</p>
                
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> {$order_number}</p>
                <p><strong>Payment Reference:</strong> {$payment_reference}</p>
                <p><strong>Total Amount:</strong> " . format_price($amount) . "</p>
                
                <h3>Items Ordered</h3>
                <ul>
            ";
            
            foreach ($order_items as $item) {
                $message .= "<li>{$item['name']} - Qty: {$item['quantity']} - " . format_price($item['price'] * $item['quantity']) . "</li>";
            }
            
            $message .= "
                </ul>
                
                <p>Your order will be shipped via next-day delivery. You'll receive tracking information once your order has been dispatched.</p>
                
                <p>You can track your order status in your account: <a href='" . SITE_URL . "/profile.php?tab=orders'>View Orders</a></p>
                
                <p>Thank you for choosing VYLO!</p>
                <p>Best regards,<br>The VYLO Team</p>
            ";
            
            send_email($user_email, $subject, $message);
            
            // Send notification to admin
            $admin_subject = "New Order Received - " . $order_number;
            $admin_message = "
                <h2>New Order Received</h2>
                <p><strong>Order Number:</strong> {$order_number}</p>
                <p><strong>Customer:</strong> {$user_name} ({$user_email})</p>
                <p><strong>Amount:</strong> " . format_price($amount) . "</p>
                <p><strong>Payment Reference:</strong> {$payment_reference}</p>
                <p>Please process this order for shipment.</p>
            ";
            
            send_email(ADMIN_EMAIL, $admin_subject, $admin_message);
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Clear session data
            unset($_SESSION['pending_order']);
            unset($_SESSION['discount_code']);
            unset($_SESSION['discount_amount']);
            
            // Redirect to success page
            redirect('payment-success.php?order=' . urlencode($order_number));
            
        } catch(PDOException $e) {
            $error_message = "Failed to process payment verification. Please contact support.";
        }
    } else {
        $error_message = "Payment reference is required.";
    }
}

// Calculate crypto amount (this would normally use real-time rates)
$crypto_amount = $amount; // Simplified - in reality you'd convert GBP to crypto
?>

<div class="container" style="padding: 2rem 0; max-width: 800px;">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Complete Payment</h1>
        <p style="color: var(--text-light);">Order: <?php echo htmlspecialchars($order_number); ?></p>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Payment Options -->
    <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); overflow: hidden; margin-bottom: 2rem;">
        
        <!-- Payment Method Tabs -->
        <div style="display: flex; border-bottom: 1px solid var(--border-color);">
            <button class="payment-tab active" data-tab="crypto" style="flex: 1; padding: 1rem; border: none; background: var(--primary-color); color: white; font-weight: 600;">
                <i class="fab fa-bitcoin"></i> Crypto Payment
            </button>
            <button class="payment-tab" data-tab="moonpay" style="flex: 1; padding: 1rem; border: none; background: var(--bg-light); color: var(--text-dark); font-weight: 600;">
                <i class="fas fa-credit-card"></i> Card via MoonPay
            </button>
        </div>

        <!-- Crypto Payment Tab -->
        <div id="crypto-tab" class="payment-content" style="padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fab fa-bitcoin" style="color: #f7931a;"></i> Direct Crypto Payment
            </h3>
            
            <div style="background: var(--bg-light); padding: 2rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                <p style="margin-bottom: 1rem;"><strong>Send exactly:</strong></p>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 1rem;">
                    £<?php echo number_format($amount, 2); ?>
                </div>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Convert this amount to your preferred cryptocurrency and send to the address below
                </p>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Crypto Wallet Address:</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="wallet-address" class="form-input" 
                           value="<?php echo htmlspecialchars(CRYPTO_WALLET_ADDRESS); ?>" 
                           readonly style="flex: 1; font-family: monospace; background: var(--bg-light);">
                    <button onclick="copyToClipboard('wallet-address')" class="btn btn-outline">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <small style="color: var(--text-light);">Send to this address only. Other addresses will result in loss of funds.</small>
            </div>

            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; padding: 1rem; margin-bottom: 2rem;">
                <h4 style="margin-bottom: 0.5rem; color: #856404;">
                    <i class="fas fa-exclamation-triangle"></i> Important Instructions
                </h4>
                <ul style="margin: 0; color: #856404; font-size: 0.875rem;">
                    <li>Send the exact amount or slightly more (overpayments will be refunded)</li>
                    <li>Do not send from an exchange wallet - use a personal wallet</li>
                    <li>Transaction must complete within 24 hours</li>
                    <li>Keep your transaction ID for verification</li>
                </ul>
            </div>

            <!-- Manual Verification Form -->
            <form method="POST" action="" style="border: 2px dashed var(--border-color); padding: 2rem; border-radius: 0.5rem;">
                <input type="hidden" name="action" value="verify_payment">
                
                <h4 style="margin-bottom: 1rem;">Payment Verification</h4>
                <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.875rem;">
                    After sending the payment, enter your transaction ID below to verify your payment.
                </p>
                
                <div class="form-group">
                    <label class="form-label" for="payment_reference">Transaction ID / Hash *</label>
                    <input type="text" id="payment_reference" name="payment_reference" class="form-input" 
                           placeholder="Enter your transaction ID" required>
                    <small style="color: var(--text-light);">This can be found in your wallet or blockchain explorer</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Verify Payment
                </button>
            </form>
        </div>

        <!-- MoonPay Tab -->
        <div id="moonpay-tab" class="payment-content" style="padding: 2rem; display: none;">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-credit-card" style="color: var(--primary-color);"></i> Pay with Card via MoonPay
            </h3>
            
            <p style="color: var(--text-light); margin-bottom: 2rem;">
                Pay with your debit or credit card. MoonPay will convert your payment to cryptocurrency 
                and send it to our wallet automatically.
            </p>

            <div style="background: var(--bg-light); padding: 2rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">💳</div>
                <h4 style="margin-bottom: 1rem;">Amount to Pay</h4>
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                    £<?php echo number_format($amount, 2); ?>
                </div>
            </div>

            <!-- MoonPay Integration -->
            <div id="moonpay-widget" style="min-height: 400px; border: 1px solid var(--border-color); border-radius: 0.5rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; justify-content: center; height: 400px; color: var(--text-light);">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4 style="margin-bottom: 1rem;">MoonPay Widget</h4>
                        <p>Integration with MoonPay would appear here</p>
                        <button onclick="initMoonPay()" class="btn btn-primary">
                            Initialize MoonPay
                        </button>
                    </div>
                </div>
            </div>

            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.5rem; padding: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: #155724;">
                    <i class="fas fa-shield-alt"></i> Secure Payment
                </h4>
                <p style="margin: 0; color: #155724; font-size: 0.875rem;">
                    Your payment is processed securely by MoonPay. We never store your card details.
                </p>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); padding: 2rem;">
        <h3 style="margin-bottom: 1.5rem;">Order Summary</h3>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
            <span>Order Number:</span>
            <span style="font-weight: 600;"><?php echo htmlspecialchars($order_number); ?></span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
            <span>Total Amount:</span>
            <span style="font-weight: 600; font-size: 1.25rem; color: var(--primary-color);">
                £<?php echo number_format($amount, 2); ?>
            </span>
        </div>
        
        <div style="display: flex; justify-content: space-between;">
            <span>Payment Status:</span>
            <span style="color: var(--warning-color); font-weight: 600;">
                <i class="fas fa-clock"></i> Pending Payment
            </span>
        </div>
    </div>

    <!-- Help Section -->
    <div style="background: var(--bg-light); border-radius: 1rem; padding: 2rem; margin-top: 2rem;">
        <h3 style="margin-bottom: 1rem;">Need Help?</h3>
        <p style="color: var(--text-light); margin-bottom: 1rem;">
            If you encounter any issues with your payment, please contact our support team.
        </p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="mailto:support@vylo.co.uk" class="btn btn-outline">
                <i class="fas fa-envelope"></i> Email Support
            </a>
            <a href="tel:+441234567890" class="btn btn-outline">
                <i class="fas fa-phone"></i> Call Support
            </a>
        </div>
    </div>
</div>

<script>
// Payment tab switching
document.querySelectorAll('.payment-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        
        // Update tab appearance
        document.querySelectorAll('.payment-tab').forEach(t => {
            t.classList.remove('active');
            t.style.background = 'var(--bg-light)';
            t.style.color = 'var(--text-dark)';
        });
        
        this.classList.add('active');
        this.style.background = 'var(--primary-color)';
        this.style.color = 'white';
        
        // Show/hide content
        document.querySelectorAll('.payment-content').forEach(content => {
            content.style.display = 'none';
        });
        
        document.getElementById(tabName + '-tab').style.display = 'block';
    });
});

// Copy to clipboard function
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        cartManager.showNotification('Address copied to clipboard!', 'success');
    } catch (err) {
        cartManager.showNotification('Failed to copy address', 'error');
    }
}

// MoonPay integration (placeholder)
function initMoonPay() {
    const widget = document.getElementById('moonpay-widget');
    widget.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: center; height: 400px; background: var(--bg-light); border-radius: 0.5rem;">
            <div style="text-align: center;">
                <div class="loading" style="margin: 0 auto 1rem;"></div>
                <p>Loading MoonPay widget...</p>
                <p style="font-size: 0.875rem; color: var(--text-light);">
                    In a real implementation, this would load the MoonPay widget<br>
                    with your API key and order details.
                </p>
            </div>
        </div>
    `;
    
    // Simulate widget loading
    setTimeout(() => {
        widget.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; color: white;">
                <div style="text-align: center;">
                    <h3 style="margin-bottom: 1rem;">MoonPay Widget</h3>
                    <p style="margin-bottom: 2rem; opacity: 0.9;">
                        Amount: £${<?php echo $amount; ?>}<br>
                        Wallet: ${<?php echo "'" . CRYPTO_WALLET_ADDRESS . "'"; ?>}
                    </p>
                    <button onclick="simulatePayment()" class="btn" style="background: white; color: #667eea; font-weight: 600;">
                        Complete Payment
                    </button>
                </div>
            </div>
        `;
    }, 2000);
}

// Simulate payment completion (for demo purposes)
function simulatePayment() {
    const mockTxId = 'moonpay_' + Math.random().toString(36).substr(2, 9);
    
    if (confirm('Simulate successful payment?')) {
        document.getElementById('payment_reference').value = mockTxId;
        cartManager.showNotification('Mock payment completed! Transaction ID: ' + mockTxId, 'success');
        
        // Switch to crypto tab to show verification form
        document.querySelector('[data-tab="crypto"]').click();
    }
}

// Auto-refresh payment status (in a real app, you'd check payment status via API)
setInterval(() => {
    // This would check payment status with your payment processor
    console.log('Checking payment status...');
}, 30000); // Check every 30 seconds
</script>

<style>
.payment-tab.active {
    background-color: var(--primary-color) !important;
    color: white !important;
}

.payment-tab:hover {
    opacity: 0.8;
}
</style>

<?php require_once 'includes/footer.php'; ?>