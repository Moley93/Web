// Hardware Store Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeHardwareStore();
});

function initializeHardwareStore() {
    setupFilters();
    setupSearch();
    setupLoadMore();
    loadRecommendedProducts();
    trackProductViews();
}

// Product filtering functionality
function setupFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active filter button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            filterProducts(category, productCards);
        });
    });
}

function filterProducts(category, productCards) {
    productCards.forEach(card => {
        const productCategory = card.dataset.category;
        
        if (category === 'all' || productCategory === category) {
            card.style.display = 'block';
            // Add fade-in animation
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.opacity = '1';
            }, 50);
        } else {
            card.style.display = 'none';
        }
    });

    updateProductCount();
}

// Search functionality
function setupSearch() {
    const searchInput = document.getElementById('product-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            searchProducts(searchTerm);
        }, 300));
    }
}

function searchProducts(searchTerm) {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.querySelector('.product-name').textContent.toLowerCase();
        const productDescription = card.querySelector('.product-description').textContent.toLowerCase();
        
        if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    updateProductCount();
}

// Load more products functionality
function setupLoadMore() {
    const loadMoreBtn = document.getElementById('load-more');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreProducts);
    }
}

async function loadMoreProducts() {
    const loadMoreBtn = document.getElementById('load-more');
    const productsContainer = document.getElementById('products-container');
    
    // Show loading state
    loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    loadMoreBtn.disabled = true;

    try {
        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // In a real implementation, this would fetch from your API
        const newProducts = getAdditionalProducts();
        
        // Add new products to the grid
        newProducts.forEach(product => {
            const productCard = createProductCard(product);
            productsContainer.appendChild(productCard);
        });

        // Reset button
        loadMoreBtn.innerHTML = 'Load More Products';
        loadMoreBtn.disabled = false;

        // Hide button if no more products
        if (newProducts.length < 6) {
            loadMoreBtn.style.display = 'none';
        }

    } catch (error) {
        console.error('Error loading more products:', error);
        loadMoreBtn.innerHTML = 'Error Loading Products';
        setTimeout(() => {
            loadMoreBtn.innerHTML = 'Load More Products';
            loadMoreBtn.disabled = false;
        }, 3000);
    }
}

function getAdditionalProducts() {
    // Mock additional products
    return [
        {
            id: 'cpu-004',
            category: 'processors',
            name: 'Intel Xeon W-3275M',
            price: 1899.99,
            description: 'Professional workstation processor with 28 cores, designed for demanding computational workloads and server applications.',
            icon: 'fas fa-microchip'
        },
        {
            id: 'gpu-001',
            category: 'processors',
            name: 'NVIDIA RTX 4090',
            price: 1699.99,
            description: 'Ultimate graphics card for AI, content creation, and high-end gaming with 24GB GDDR6X memory.',
            icon: 'fas fa-microchip'
        },
        {
            id: 'ram-004',
            category: 'memory',
            name: 'Samsung DDR5-4800 256GB',
            price: 1999.99,
            description: 'Ultra-high capacity enterprise memory module for servers and workstations requiring maximum memory bandwidth.',
            icon: 'fas fa-memory'
        },
        {
            id: 'ssd-003',
            category: 'storage',
            name: 'Intel Optane P5800X 800GB',
            price: 1299.99,
            description: 'Enterprise NVMe SSD with 3D XPoint technology for ultra-low latency storage applications.',
            icon: 'fas fa-hdd'
        },
        {
            id: 'net-003',
            category: 'networking',
            name: 'Cisco Catalyst 9300-48P',
            price: 2499.99,
            description: 'Enterprise-grade 48-port switch with advanced security features and network automation capabilities.',
            icon: 'fas fa-network-wired'
        },
        {
            id: 'srv-003',
            category: 'servers',
            name: 'Supermicro SuperServer 2049U',
            price: 5999.99,
            description: '2U Twin server with dual Intel Xeon processors, optimized for high-density datacenter deployments.',
            icon: 'fas fa-server'
        }
    ];
}

function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.dataset.productId = product.id;
    card.dataset.category = product.category;
    
    card.innerHTML = `
        <div class="product-image">
            <i class="${product.icon}"></i>
            <button class="wishlist-btn" onclick="addToWishlist('${product.id}')">
                <i class="fas fa-heart"></i>
            </button>
        </div>
        <h3 class="product-name">${product.name}</h3>
        <p class="product-price">£${product.price.toFixed(2)}</p>
        <p class="product-description">${product.description}</p>
        <div class="product-actions">
            <button class="add-to-cart">Add to Cart</button>
        </div>
    `;
    
    return card;
}

// Product view tracking
function trackProductViews() {
    const productCards = document.querySelectorAll('.product-card');
    
    // Intersection Observer to track which products are viewed
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const productId = entry.target.dataset.productId;
                trackProductView(productId);
            }
        });
    }, {
        threshold: 0.5
    });

    productCards.forEach(card => {
        observer.observe(card);
    });
}

function trackProductView(productId) {
    // Track recently viewed products
    let recentlyViewed = JSON.parse(localStorage.getItem('vylo_recently_viewed') || '[]');
    
    // Remove if already exists
    recentlyViewed = recentlyViewed.filter(id => id !== productId);
    
    // Add to beginning
    recentlyViewed.unshift(productId);
    
    // Keep only last 10
    recentlyViewed = recentlyViewed.slice(0, 10);
    
    localStorage.setItem('vylo_recently_viewed', JSON.stringify(recentlyViewed));
}

// Load recommended products for basket page
function loadRecommendedProducts() {
    const recommendedContainer = document.getElementById('recommended-products');
    
    if (recommendedContainer) {
        const recommendations = getRecommendedProducts();
        
        recommendations.forEach(product => {
            const recommendedItem = document.createElement('div');
            recommendedItem.className = 'recommended-item';
            recommendedItem.innerHTML = `
                <div class="item-image">
                    <i class="${product.icon}"></i>
                </div>
                <div class="item-info">
                    <h5>${product.name}</h5>
                    <div class="item-price">£${product.price.toFixed(2)}</div>
                </div>
            `;
            
            recommendedItem.addEventListener('click', () => {
                window.location.href = `hardware.html?product=${product.id}`;
            });
            
            recommendedContainer.appendChild(recommendedItem);
        });
    }
}

function getRecommendedProducts() {
    // Mock recommended products based on cart contents or popular items
    return [
        {
            id: 'cpu-001',
            name: 'Intel Core i9-13900K',
            price: 589.99,
            icon: 'fas fa-microchip'
        },
        {
            id: 'ram-001',
            name: 'Corsair DDR5-5600 32GB',
            price: 299.99,
            icon: 'fas fa-memory'
        },
        {
            id: 'ssd-001',
            name: 'Samsung 980 PRO 2TB',
            price: 199.99,
            icon: 'fas fa-hdd'
        }
    ];
}

// Update product count display
function updateProductCount() {
    const visibleProducts = document.querySelectorAll('.product-card[style="display: block"], .product-card:not([style])');
    const totalProducts = document.querySelectorAll('.product-card');
    
    // You could add a product count display here
    console.log(`Showing ${visibleProducts.length} of ${totalProducts.length} products`);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Product comparison functionality
function compareProducts(productIds) {
    // Store products to compare
    localStorage.setItem('vylo_compare_products', JSON.stringify(productIds));
    window.location.href = 'compare.html';
}

// Add to wishlist functionality
function addToWishlist(productId) {
    if (!window.vyloApp.user) {
        window.vyloApp.showNotification('Please log in to add items to your wishlist', 'error');
        return;
    }

    let wishlist = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    
    if (!wishlist.includes(productId)) {
        wishlist.push(productId);
        localStorage.setItem('vylo_wishlist', JSON.stringify(wishlist));
        window.vyloApp.showNotification('Added to wishlist!', 'success');
        
        // Update wishlist button appearance
        const wishlistBtns = document.querySelectorAll(`[onclick="addToWishlist('${productId}')"]`);
        wishlistBtns.forEach(btn => {
            btn.innerHTML = '<i class="fas fa-heart" style="color: #ff4444;"></i>';
            btn.onclick = () => removeFromWishlist(productId);
        });
    } else {
        window.vyloApp.showNotification('Item already in wishlist', 'info');
    }
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    wishlist = wishlist.filter(id => id !== productId);
    localStorage.setItem('vylo_wishlist', JSON.stringify(wishlist));
    
    window.vyloApp.showNotification('Removed from wishlist', 'info');
    
    // Update wishlist button appearance
    const wishlistBtns = document.querySelectorAll(`[data-product-id="${productId}"] .wishlist-btn`);
    wishlistBtns.forEach(btn => {
        btn.innerHTML = '<i class="fas fa-heart"></i>';
        btn.onclick = () => addToWishlist(productId);
    });
}

function getProductById(productId) {
    // This would typically fetch from an API
    // For now, return mock data based on the product cards on the page
    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
    if (!productCard) return null;

    return {
        id: productId,
        name: productCard.querySelector('.product-name').textContent,
        price: parseFloat(productCard.querySelector('.product-price').textContent.replace('£', '')),
        description: productCard.querySelector('.product-description').textContent,
        icon: productCard.querySelector('.product-image i').className
    };
}

// Initialize wishlist states on page load
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    const wishlist = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    
    productCards.forEach(card => {
        const productId = card.dataset.productId;
        const wishlistBtn = card.querySelector('.wishlist-btn');
        
        if (wishlistBtn && wishlist.includes(productId)) {
            wishlistBtn.innerHTML = '<i class="fas fa-heart" style="color: #ff4444;"></i>';
            wishlistBtn.onclick = () => removeFromWishlist(productId);
        }
    });
});

// Export functions for use in other modules
window.hardwareStore = {
    filterProducts,
    searchProducts,
    addToWishlist,
    trackProductView
};