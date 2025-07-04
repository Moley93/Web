<?php
// hardware.php - Hardware Store Page
$page_title = "Hardware Store";
require_once 'includes/header.php';

// Pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';

// Build query
$where_conditions = ["is_active = 1"];
$params = [];

if ($category) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options
$sort_options = [
    'newest' => 'created_at DESC',
    'oldest' => 'created_at ASC',
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    'name_az' => 'name ASC',
    'name_za' => 'name DESC'
];

$order_by = isset($sort_options[$sort]) ? $sort_options[$sort] : 'created_at DESC';

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetch()['total'];
    
    // Get products
    $sql = "SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get categories for filter
    $cat_stmt = $pdo->prepare("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category");
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll();
    
} catch(PDOException $e) {
    $products = [];
    $categories = [];
    $total_products = 0;
}

$total_pages = ceil($total_products / $per_page);
?>

<div class="container" style="padding: 2rem 0;">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-dark);">Hardware Store</h1>
        <p style="font-size: 1.125rem; color: var(--text-light); max-width: 600px; margin: 0 auto;">
            Discover our comprehensive range of development boards, sensors, and electronic components. 
            All products come with next-day UK delivery and expert support.
        </p>
    </div>

    <!-- Filters and Search -->
    <div style="background: var(--bg-light); padding: 2rem; border-radius: 1rem; margin-bottom: 2rem;">
        <form method="GET" action="hardware.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                
                <!-- Search -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Search Products</label>
                    <input type="text" name="search" class="form-input" 
                           placeholder="Search for products..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <!-- Category Filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A-Z</option>
                        <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name: Z-A</option>
                    </select>
                </div>
                
                <!-- Filter Button -->
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <p style="color: var(--text-light);">
                Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products
                <?php if ($search): ?>
                    for "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
                <?php if ($category): ?>
                    in <?php echo htmlspecialchars($category); ?>
                <?php endif; ?>
            </p>
        </div>
        
        <?php if ($search || $category): ?>
        <div>
            <a href="hardware.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear Filters
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Products Grid -->
    <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-microchip"></i>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div style="margin-bottom: 0.5rem;">
                        <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </span>
                    </div>
                    
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div class="product-price"><?php echo format_price($product['price']); ?></div>
                        <div style="font-size: 0.875rem; color: var(--text-light);">
                            <?php if ($product['stock_quantity'] > 10): ?>
                                <i class="fas fa-check-circle" style="color: var(--success-color);"></i> In Stock
                            <?php elseif ($product['stock_quantity'] > 0): ?>
                                <i class="fas fa-exclamation-triangle" style="color: var(--warning-color);"></i> Low Stock
                            <?php else: ?>
                                <i class="fas fa-times-circle" style="color: var(--error-color);"></i> Out of Stock
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-primary add-to-cart" 
                                    data-product-id="<?php echo $product['id']; ?>" 
                                    style="flex: 1;">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn btn-outline" 
                                    onclick="showProductDetails(<?php echo $product['id']; ?>)">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled style="width: 100%;">
                            <i class="fas fa-times"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="margin-top: 3rem; text-align: center;">
            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="btn btn-outline">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="btn btn-outline">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <p style="margin-top: 1rem; color: var(--text-light);">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </p>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Products Found -->
        <div style="text-align: center; padding: 4rem 0;">
            <div style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;">
                <i class="fas fa-search"></i>
            </div>
            <h3 style="margin-bottom: 1rem;">No Products Found</h3>
            <p style="color: var(--text-light); margin-bottom: 2rem;">
                Sorry, we couldn't find any products matching your criteria.
            </p>
            <a href="hardware.php" class="btn btn-primary">Browse All Products</a>
        </div>
    <?php endif; ?>
</div>

<!-- Product Details Modal (placeholder) -->
<div id="product-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; max-height: 80%; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 id="modal-title">Product Details</h3>
            <button onclick="closeProductModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-content">
            <!-- Product details will be loaded here -->
        </div>
    </div>
</div>

<script>
function showProductDetails(productId) {
    // This would typically load product details via AJAX
    const modal = document.getElementById('product-modal');
    const content = document.getElementById('modal-content');
    
    content.innerHTML = '<div class="loading"></div>';
    modal.style.display = 'flex';
    
    // Simulate loading product details
    setTimeout(() => {
        content.innerHTML = `
            <p>Detailed product information would be loaded here via AJAX.</p>
            <p>This could include specifications, compatibility, reviews, etc.</p>
        `;
    }, 500);
}

function closeProductModal() {
    document.getElementById('product-modal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('product-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>