<?php
// index.php - Homepage
$page_title = "Home";
require_once 'includes/header.php';

// Get featured products
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
} catch(PDOException $e) {
    $featured_products = [];
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to VYLO</h1>
        <p>Your trusted UK hardware specialist providing quality electronics and development boards with next-day delivery across the UK.</p>
        <div style="margin-top: 2rem;">
            <a href="hardware.php" class="btn btn-primary" style="margin-right: 1rem;">Shop Hardware</a>
            <a href="#why-choose-us" class="btn btn-outline">Learn More</a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="features" id="why-choose-us">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 1rem; font-size: 2.5rem; color: var(--text-dark);">Why Choose VYLO?</h2>
        <p style="text-align: center; color: var(--text-light); font-size: 1.125rem; max-width: 600px; margin: 0 auto;">We're different from the rest because we focus on quality, speed, and customer satisfaction.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Next-Day Delivery</h3>
                <p>Order before 3PM and receive your hardware the next business day anywhere in the UK. Fast, reliable delivery you can count on.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Quality Guaranteed</h3>
                <p>All our products undergo rigorous quality testing. We only stock hardware from trusted manufacturers with proven track records.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Expert Support</h3>
                <p>Our technical team provides expert advice and support for all your hardware needs. Get help choosing the right components for your projects.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bitcoin"></i>
                </div>
                <h3>Crypto Payments</h3>
                <p>We accept cryptocurrency payments alongside traditional payment methods, making it easy and secure to purchase your hardware.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Developer Focused</h3>
                <p>Built by developers, for developers. We understand your needs and provide the exact components and tools you require for your projects.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>Not satisfied? No problem. We offer hassle-free returns within 30 days. Your satisfaction is our priority.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Hardware Section -->
<section style="padding: 5rem 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 1rem; font-size: 2.5rem; color: var(--text-dark);">Our Hardware Range</h2>
        <p style="text-align: center; color: var(--text-light); font-size: 1.125rem; max-width: 800px; margin: 0 auto 3rem;">We specialize in high-quality development boards, sensors, microcontrollers, and electronic components for makers, developers, and engineers.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3>Development Boards</h3>
                <p>From Arduino-compatible boards to advanced ESP32 modules with WiFi and Bluetooth connectivity. Perfect for IoT and embedded projects.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-thermometer-half"></i>
                </div>
                <h3>Sensors & Modules</h3>
                <p>Comprehensive range of sensors including temperature, humidity, motion, light, and environmental monitoring solutions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3>Development Tools</h3>
                <p>Professional breadboards, jumper wires, multimeters, and other essential tools for prototyping and development.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-plug"></i>
                </div>
                <h3>Power Solutions</h3>
                <p>Battery packs, power adapters, voltage regulators, and charging modules to keep your projects powered and running.</p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="hardware.php" class="btn btn-primary">Browse All Hardware</a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<?php if (!empty($featured_products)): ?>
<section style="padding: 5rem 0; background-color: var(--bg-light);">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem; color: var(--text-dark);">Featured Products</h2>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-microchip"></i>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                    <div class="product-price"><?php echo format_price($product['price']); ?></div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Route to Market Section -->
<section style="padding: 5rem 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h2 style="margin-bottom: 2rem; font-size: 2.5rem; color: var(--text-dark);">Our Route to Market</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 3rem;">
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                        <i class="fas fa-industry"></i>
                    </div>
                    <h4>Direct from Manufacturers</h4>
                    <p style="color: var(--text-light);">We work directly with trusted hardware manufacturers to ensure authenticity and competitive pricing.</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h4>UK Warehouse</h4>
                    <p style="color: var(--text-light);">All products are stocked in our UK warehouse for fast, reliable next-day delivery across the country.</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h4>Online Store</h4>
                    <p style="color: var(--text-light);">Easy online ordering with secure crypto and card payments, plus full order tracking and support.</p>
                </div>
                
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>You</h4>
                    <p style="color: var(--text-light);">Delivered straight to your door with tracking, support, and our commitment to your success.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section style="padding: 5rem 0; background: linear-gradient(135deg, var(--primary-color), #3b82f6); color: white; text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: 1rem; font-size: 2.5rem;">Ready to Start Your Next Project?</h2>
        <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">Browse our hardware collection and get everything you need delivered tomorrow.</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="hardware.php" class="btn" style="background: white; color: var(--primary-color); font-weight: 600;">Shop Hardware</a>
            <a href="register.php" class="btn btn-outline" style="border-color: white; color: white;">Create Account</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>