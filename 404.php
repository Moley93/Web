<?php
// 404.php - Page Not Found
http_response_code(404);
$page_title = "Page Not Found";
require_once 'includes/header.php';

// Get some popular products to suggest
$popular_products = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY RAND() LIMIT 4");
    $stmt->execute();
    $popular_products = $stmt->fetchAll();
} catch(PDOException $e) {
    // Ignore error for 404 page
}
?>

<div class="container" style="padding: 4rem 0; text-align: center;">
    <!-- 404 Message -->
    <div style="margin-bottom: 3rem;">
        <div style="font-size: 8rem; font-weight: 900; color: var(--primary-color); margin-bottom: 1rem; line-height: 1;">
            404
        </div>
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-dark);">
            Oops! Page Not Found
        </h1>
        <p style="font-size: 1.125rem; color: var(--text-light); max-width: 500px; margin: 0 auto;">
            The page you're looking for doesn't exist. It might have been moved, deleted, 
            or you entered the wrong URL.
        </p>
    </div>

    <!-- Search Box -->
    <div style="max-width: 500px; margin: 0 auto 3rem;">
        <form action="hardware.php" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" placeholder="Search for products..." 
                   class="form-input" style="flex: 1; margin-bottom: 0;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <!-- Quick Actions -->
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 4rem;">
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-home"></i> Go Home
        </a>
        <a href="hardware.php" class="btn btn-outline">
            <i class="fas fa-shopping-bag"></i> Browse Products
        </a>
        <a href="javascript:history.back()" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
    </div>

    <!-- Popular Products -->
    <?php if (!empty($popular_products)): ?>
        <div style="max-width: 800px; margin: 0 auto;">
            <h2 style="margin-bottom: 2rem; color: var(--text-dark);">
                Popular Products
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem;">
                <?php foreach ($popular_products as $product): ?>
                <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); transition: transform 0.3s;" 
                     onmouseover="this.style.transform='translateY(-5px)'" 
                     onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 100%; height: 120px; background: var(--bg-light); display: flex; align-items: center; justify-content: center;">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-microchip" style="font-size: 2rem; color: var(--text-light);"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div style="padding: 1rem;">
                        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-dark);">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <div style="font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <?php echo format_price($product['price']); ?>
                        </div>
                        <a href="hardware.php?search=<?php echo urlencode($product['name']); ?>" 
                           class="btn btn-outline btn-sm" style="width: 100%; text-align: center;">
                            View Product
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div style="background: var(--bg-light); border-radius: 1rem; padding: 2rem; margin-top: 4rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        <h3 style="margin-bottom: 1rem;">Still Can't Find What You're Looking For?</h3>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">
            Our team is here to help you find the right hardware for your project.
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="mailto:support@vylo.co.uk" class="btn btn-outline">
                <i class="fas fa-envelope"></i> Contact Support
            </a>
            <a href="tel:+441234567890" class="btn btn-outline">
                <i class="fas fa-phone"></i> Call Us
            </a>
        </div>
    </div>

    <!-- Common Links -->
    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <h4 style="margin-bottom: 1rem; color: var(--text-dark);">Helpful Links</h4>
        <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
            <a href="firmware.php" style="color: var(--primary-color); text-decoration: none;">Firmware Solutions</a>
            <a href="software.php" style="color: var(--primary-color); text-decoration: none;">Software Recommendations</a>
            <a href="profile.php" style="color: var(--primary-color); text-decoration: none;">My Account</a>
            <a href="cart.php" style="color: var(--primary-color); text-decoration: none;">Shopping Cart</a>
        </div>
    </div>
</div>

<!-- Fun Animation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add a subtle floating animation to the 404 number
    const errorNumber = document.querySelector('div[style*="font-size: 8rem"]');
    if (errorNumber) {
        errorNumber.style.animation = 'float 3s ease-in-out infinite';
    }
    
    // Add CSS for the animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php require_once 'includes/footer.php'; ?>