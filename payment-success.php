<?php
// payment-success.php - Payment Success Page
$page_title = "Payment Successful";
require_once 'includes/header.php';

// Get order number from URL
$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    redirect('index.php');
}

// If user is logged in, get order details
$order = null;
if (is_logged_in()) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
        $stmt->execute([$order_number, $_SESSION['user_id']]);
        $order = $stmt->fetch();
    } catch(PDOException $e) {
        // Order not found
    }
}
?>

<div class="container" style="padding: 2rem 0; max-width: 700px;">
    <!-- Success Message -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <div style="width: 100px; height: 100px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: white; font-size: 3rem;">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--success-color);">Payment Successful!</h1>
        <p style="font-size: 1.25rem; color: var(--text-light);">
            Thank you for your order. We've received your payment and will process your order shortly.
        </p>
    </div>

    <!-- Order Details -->
    <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); padding: 2rem; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-receipt"></i> Order Confirmation
        </h2>
        
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <h4 style="margin-bottom: 0.5rem; color: var(--text-dark);">Order Number</h4>
                    <p style="font-family: monospace; font-size: 1.125rem; font-weight: 600; color: var(--primary-color); margin: 0;">
                        <?php echo htmlspecialchars($order_number); ?>
                    </p>
                </div>
                
                <?php if ($order): ?>
                <div>
                    <h4 style="margin-bottom: 0.5rem; color: var(--text-dark);">Order Total</h4>
                    <p style="font-size: 1.125rem; font-weight: 600; color: var(--primary-color); margin: 0;">
                        <?php echo format_price($order['total_amount']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #d1fae5; border-radius: 0.5rem;">
                <div style="width: 40px; height: 40px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem; color: #065f46;">Payment Received</h4>
                    <p style="color: #047857; font-size: 0.875rem; margin: 0;">Your payment has been successfully processed</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #fef3c7; border-radius: 0.5rem;">
                <div style="width: 40px; height: 40px; background: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem; color: #92400e;">Order Processing</h4>
                    <p style="color: #d97706; font-size: 0.875rem; margin: 0;">We're preparing your items for shipment</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #dbeafe; border-radius: 0.5rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem; color: #1e40af;">Next Day Delivery</h4>
                    <p style="color: #2563eb; font-size: 0.875rem; margin: 0;">Your order will be shipped for next business day delivery</p>
                </div>
            </div>
        </div>
    </div>

    <!-- What Happens Next -->
    <div style="background: white; border-radius: 1rem; box-shadow: var(--shadow); padding: 2rem; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">What Happens Next?</h2>
        
        <div style="display: grid; gap: 1.5rem;">
            <div style="display: flex; gap: 1rem;">
                <div style="width: 30px; height: 30px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">
                    1
                </div>
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Order Processing (Within 2 hours)</h4>
                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem;">
                        Our team will pick and pack your items from our UK warehouse
                    </p>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <div style="width: 30px; height: 30px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">
                    2
                </div>
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Shipping & Tracking (Same day if ordered before 3PM)</h4>
                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem;">
                        Your order will be dispatched via FedEx with full tracking information sent to your email
                    </p>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <div style="width: 30px; height: 30px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">
                    3
                </div>
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Delivery (Next business day)</h4>
                    <p style="color: var(--text-light); margin: 0; font-size: 0.875rem;">
                        Your hardware will arrive at your door the next business day
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Confirmation -->
    <div style="background: var(--bg-light); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2rem; color: var(--primary-color);">
                <i class="fas fa-envelope"></i>
            </div>
            <div>
                <h3 style="margin-bottom: 0.5rem;">Confirmation Email Sent</h3>
                <p style="color: var(--text-light); margin: 0;">
                    A detailed order confirmation has been sent to 
                    <?php echo is_logged_in() ? htmlspecialchars($_SESSION['user_email']) : 'your email address'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php if (is_logged_in()): ?>
            <a href="profile.php?tab=orders" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Orders
            </a>
        <?php endif; ?>
        
        <a href="hardware.php" class="btn btn-outline">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
        
        <a href="index.php" class="btn btn-outline">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>

    <!-- Support Information -->
    <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: white; border-radius: 1rem; box-shadow: var(--shadow);">
        <h3 style="margin-bottom: 1rem;">Need Help?</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">
            Our support team is here to help with any questions about your order.
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="mailto:support@vylo.co.uk" class="btn btn-outline">
                <i class="fas fa-envelope"></i> Email Support
            </a>
            <a href="tel:+441234567890" class="btn btn-outline">
                <i class="fas fa-phone"></i> Call Us
            </a>
        </div>
        
        <p style="color: var(--text-light); font-size: 0.875rem; margin-top: 1rem;">
            Support hours: Monday-Friday 9AM-6PM GMT
        </p>
    </div>
</div>

<!-- Celebration Animation (Optional) -->
<script>
// Add some celebration effects
document.addEventListener('DOMContentLoaded', function() {
    // Simple confetti effect using text
    const celebrationDiv = document.createElement('div');
    celebrationDiv.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1000;
    `;
    
    // Create confetti elements
    const confetti = ['🎉', '🎊', '✨', '🎈'];
    
    for (let i = 0; i < 20; i++) {
        const confettiPiece = document.createElement('div');
        confettiPiece.textContent = confetti[Math.floor(Math.random() * confetti.length)];
        confettiPiece.style.cssText = `
            position: absolute;
            font-size: 2rem;
            animation: fall 3s linear infinite;
            animation-delay: ${Math.random() * 3}s;
            left: ${Math.random() * 100}%;
            top: -50px;
        `;
        celebrationDiv.appendChild(confettiPiece);
    }
    
    document.body.appendChild(celebrationDiv);
    
    // Remove after animation
    setTimeout(() => {
        if (celebrationDiv.parentNode) {
            celebrationDiv.parentNode.removeChild(celebrationDiv);
        }
    }, 6000);
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once 'includes/footer.php'; ?>