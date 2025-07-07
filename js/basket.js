// Basket/Cart Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeBasket();
});

function initializeBasket() {
    renderBasketItems();
    updateBasketSummary();
    setupBasketEventListeners();
    loadRecentlyViewed();
    checkEmptyBasket();
}

function renderBasketItems() {
    const cartItemsContainer = document.getElementById('cart-items-container');
    const basketActions = document.getElementById('basket-actions');
    
    if (!cartItemsContainer) return;

    const cart = window.vyloApp.cart;
    
    if (cart.length === 0) {
        showEmptyBasket();
        return;
    }

    cartItemsContainer.innerHTML = cart.map(item => createCartItemHTML(item)).join('');
    
    // Show basket actions
    if (basketActions) {
        basketActions.style.display = 'flex';
    }
}

function createCartItemHTML(item) {
    return `
        <div class="cart-item" data-product-id="${item.id}">
            <div class="item-image">
                <i class="fas fa-microchip"></i>
            </div>
            
            <div class="item-details">
                <h4>${item.name}</h4>
                <p class="item-description">${item.description}</p>
                <p class="item-price">£${item.price.toFixed(2)} each</p>
                <div class="item-actions">
                    <button class="save-later-btn" onclick="saveForLater('${item.id}')">
                        <i class="fas fa-bookmark"></i> Save for Later
                    </button>
                    <button class="remove-btn" onclick="removeFromBasket('${item.id}')">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            
            <div class="quantity-controls">
                <button class="quantity-btn" onclick="updateQuantity('${item.id}', ${item.quantity - 1})">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" class="quantity-input" value="${item.quantity}" 
                       min="1" max="99" onchange="updateQuantity('${item.id}', this.value)">
                <button class="quantity-btn" onclick="updateQuantity('${item.id}', ${item.quantity + 1})">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            
            <div class="item-total">
                £${(item.price * item.quantity).toFixed(2)}
            </div>
        </div>
    `;
}

function updateQuantity(productId, newQuantity) {
    newQuantity = parseInt(newQuantity);
    
    if (newQuantity < 1) {
        removeFromBasket(productId);
        return;
    }
    
    if (newQuantity > 99) {
        window.vyloApp.showNotification('Maximum quantity is 99', 'error');
        return;
    }

    window.vyloApp.updateQuantity(productId, newQuantity);
    renderBasketItems();
    updateBasketSummary();
}

function removeFromBasket(productId) {
    const item = window.vyloApp.cart.find(item => item.id === productId);
    
    if (confirm(`Remove ${item.name} from your basket?`)) {
        window.vyloApp.removeFromCart(productId);
        renderBasketItems();
        updateBasketSummary();
        checkEmptyBasket();
        
        window.vyloApp.showNotification('Item removed from basket', 'info');
    }
}

function saveForLater(productId) {
    const item = window.vyloApp.cart.find(item => item.id === productId);
    
    if (!item) return;

    // Add to saved items
    let savedItems = JSON.parse(localStorage.getItem('vylo_saved_items') || '[]');
    const existingItem = savedItems.find(saved => saved.id === productId);
    
    if (!existingItem) {
        savedItems.push({
            ...item,
            savedAt: new Date().toISOString()
        });
        localStorage.setItem('vylo_saved_items', JSON.stringify(savedItems));
    }

    // Remove from cart
    window.vyloApp.removeFromCart(productId);
    renderBasketItems();
    updateBasketSummary();
    renderSavedItems();
    checkEmptyBasket();
    
    window.vyloApp.showNotification('Item saved for later', 'success');
}

function updateBasketSummary() {
    const cart = window.vyloApp.cart;
    const subtotal = window.vyloApp.getCartTotal();
    const vat = subtotal * 0.2; // 20% VAT
    const total = subtotal + vat;
    const itemCount = cart.reduce((total, item) => total + item.quantity, 0);

    // Update summary elements
    updateElementText('items-count', `${itemCount} item${itemCount !== 1 ? 's' : ''}`);
    updateElementText('cart-subtotal', `£${subtotal.toFixed(2)}`);
    updateElementText('cart-vat', `£${vat.toFixed(2)}`);
    updateElementText('cart-total', `£${total.toFixed(2)}`);

    // Enable/disable checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = cart.length === 0;
        
        if (cart.length > 0) {
            checkoutBtn.onclick = () => {
                if (window.vyloApp.user) {
                    window.location.href = 'checkout.html';
                } else {
                    window.vyloApp.showNotification('Please log in to proceed to checkout', 'info');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                }
            };
        }
    }
}

function setupBasketEventListeners() {
    // Clear cart button
    const clearCartBtn = document.getElementById('clear-cart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearBasket);
    }

    // Continue shopping button is already linked in HTML

    // Apply discount code (if on checkout page)
    const discountForm = document.getElementById('discount-form');
    if (discountForm) {
        discountForm.addEventListener('submit', applyDiscountCode);
    }
}

function clearBasket() {
    if (confirm('Are you sure you want to clear your entire basket?')) {
        window.vyloApp.clearCart();
        renderBasketItems();
        updateBasketSummary();
        checkEmptyBasket();
        window.vyloApp.showNotification('Basket cleared', 'info');
    }
}

function checkEmptyBasket() {
    const emptyBasket = document.getElementById('empty-basket');
    const basketContent = document.querySelector('.basket-content');
    
    if (window.vyloApp.cart.length === 0) {
        if (emptyBasket) emptyBasket.style.display = 'block';
        if (basketContent) basketContent.style.display = 'none';
    } else {
        if (emptyBasket) emptyBasket.style.display = 'none';
        if (basketContent) basketContent.style.display = 'grid';
    }
}

function showEmptyBasket() {
    const cartItemsContainer = document.getElementById('cart-items-container');
    const basketActions = document.getElementById('basket-actions');
    
    if (cartItemsContainer) {
        cartItemsContainer.innerHTML = `
            <div class="empty-cart-message">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your basket is empty</h3>
                <p>Add some amazing hardware to get started!</p>
                <a href="hardware.html" class="cta-button">Browse Hardware</a>
            </div>
        `;
    }
    
    if (basketActions) {
        basketActions.style.display = 'none';
    }
}

function renderSavedItems() {
    const savedItemsContainer = document.getElementById('saved-items-list');
    const savedItemsSection = document.getElementById('saved-items');
    
    if (!savedItemsContainer) return;

    const savedItems = JSON.parse(localStorage.getItem('vylo_saved_items') || '[]');
    
    if (savedItems.length === 0) {
        savedItemsSection.style.display = 'none';
        return;
    }

    savedItemsSection.style.display = 'block';
    savedItemsContainer.innerHTML = savedItems.map(item => `
        <div class="saved-item" data-product-id="${item.id}">
            <div class="saved-item-info">
                <h5>${item.name}</h5>
                <p>£${item.price.toFixed(2)}</p>
            </div>
            <div class="saved-item-actions">
                <button onclick="moveToCart('${item.id}')" class="btn-small">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
                <button onclick="removeSavedItem('${item.id}')" class="btn-small remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function moveToCart(productId) {
    let savedItems = JSON.parse(localStorage.getItem('vylo_saved_items') || '[]');
    const item = savedItems.find(saved => saved.id === productId);
    
    if (item) {
        // Add to cart
        window.vyloApp.addToCart({
            id: item.id,
            name: item.name,
            price: item.price,
            description: item.description
        });
        
        // Remove from saved items
        savedItems = savedItems.filter(saved => saved.id !== productId);
        localStorage.setItem('vylo_saved_items', JSON.stringify(savedItems));
        
        // Update displays
        renderBasketItems();
        updateBasketSummary();
        renderSavedItems();
        
        window.vyloApp.showNotification('Item moved to cart', 'success');
    }
}

function removeSavedItem(productId) {
    let savedItems = JSON.parse(localStorage.getItem('vylo_saved_items') || '[]');
    savedItems = savedItems.filter(saved => saved.id !== productId);
    localStorage.setItem('vylo_saved_items', JSON.stringify(savedItems));
    
    renderSavedItems();
    window.vyloApp.showNotification('Saved item removed', 'info');
}

function loadRecentlyViewed() {
    const recentlyViewedContainer = document.getElementById('recently-viewed-items');
    const recentlyViewedSection = document.getElementById('recently-viewed');
    
    if (!recentlyViewedContainer) return;

    const recentlyViewed = JSON.parse(localStorage.getItem('vylo_recently_viewed') || '[]');
    
    if (recentlyViewed.length === 0) {
        recentlyViewedSection.style.display = 'none';
        return;
    }

    recentlyViewedSection.style.display = 'block';
    
    // Get product details for recently viewed items
    const recentProducts = recentlyViewed.slice(0, 5).map(id => {
        // This would typically fetch from an API
        return getMockProductById(id);
    }).filter(Boolean);

    recentlyViewedContainer.innerHTML = recentProducts.map(product => `
        <div class="recently-viewed-item" onclick="window.location.href='hardware.html?product=${product.id}'">
            <div class="item-image">
                <i class="${product.icon}"></i>
            </div>
            <div class="item-info">
                <h5>${product.name}</h5>
                <p>£${product.price.toFixed(2)}</p>
            </div>
        </div>
    `).join('');
}

function getMockProductById(productId) {
    // Mock product data - in a real app this would come from an API
    const mockProducts = {
        'cpu-001': { id: 'cpu-001', name: 'Intel Core i9-13900K', price: 589.99, icon: 'fas fa-microchip' },
        'cpu-002': { id: 'cpu-002', name: 'AMD Ryzen 9 7900X', price: 449.99, icon: 'fas fa-microchip' },
        'ram-001': { id: 'ram-001', name: 'Corsair DDR5-5600 32GB', price: 299.99, icon: 'fas fa-memory' },
        'ssd-001': { id: 'ssd-001', name: 'Samsung 980 PRO 2TB', price: 199.99, icon: 'fas fa-hdd' }
    };
    
    return mockProducts[productId];
}

function applyDiscountCode(event) {
    event.preventDefault();
    const discountCode = event.target.discount_code.value.trim();
    
    if (!discountCode) {
        window.vyloApp.showNotification('Please enter a discount code', 'error');
        return;
    }

    // Validate discount code
    validateDiscountCode(discountCode);
}

async function validateDiscountCode(code) {
    try {
        // Show loading state
        const submitBtn = document.querySelector('#discount-form button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Applying...';
        submitBtn.disabled = true;

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Mock discount validation
        const validCodes = {
            'SAVE10': { discount: 10, type: 'percentage' },
            'WELCOME5': { discount: 5, type: 'percentage' },
            'STUDENT': { discount: 15, type: 'percentage' },
            'SUMMER25': { discount: 25, type: 'fixed' }
        };

        const discountInfo = validCodes[code.toUpperCase()];
        
        if (discountInfo) {
            applyDiscount(discountInfo);
            window.vyloApp.showNotification(`Discount applied: ${discountInfo.discount}${discountInfo.type === 'percentage' ? '%' : '£'} off!`, 'success');
        } else {
            window.vyloApp.showNotification('Invalid discount code', 'error');
        }

        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;

    } catch (error) {
        console.error('Error validating discount code:', error);
        window.vyloApp.showNotification('Error applying discount code', 'error');
    }
}

function applyDiscount(discountInfo) {
    // Store discount info
    localStorage.setItem('vylo_applied_discount', JSON.stringify(discountInfo));
    
    // Update basket summary with discount
    updateBasketSummaryWithDiscount(discountInfo);
}

function updateBasketSummaryWithDiscount(discountInfo) {
    const subtotal = window.vyloApp.getCartTotal();
    let discountAmount = 0;
    
    if (discountInfo.type === 'percentage') {
        discountAmount = subtotal * (discountInfo.discount / 100);
    } else {
        discountAmount = Math.min(discountInfo.discount, subtotal);
    }
    
    const discountedSubtotal = subtotal - discountAmount;
    const vat = discountedSubtotal * 0.2;
    const total = discountedSubtotal + vat;

    // Update display
    updateElementText('cart-subtotal', `£${subtotal.toFixed(2)}`);
    
    // Show discount row
    const discountRow = document.getElementById('discount-row');
    if (discountRow) {
        discountRow.style.display = 'flex';
        updateElementText('discount-amount', `-£${discountAmount.toFixed(2)}`);
    }
    
    updateElementText('cart-vat', `£${vat.toFixed(2)}`);
    updateElementText('cart-total', `£${total.toFixed(2)}`);
}

function updateElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
    }
}

// Auto-save basket state
function autoSaveBasket() {
    // The main app already handles this, but we can add additional basket-specific saving here
    const basketState = {
        lastUpdated: new Date().toISOString(),
        itemCount: window.vyloApp.cart.length,
        total: window.vyloApp.getCartTotal()
    };
    
    localStorage.setItem('vylo_basket_state', JSON.stringify(basketState));
}

// Call auto-save when basket changes
document.addEventListener('cartUpdated', autoSaveBasket);

// Initialize saved items on page load
document.addEventListener('DOMContentLoaded', function() {
    renderSavedItems();
});

// Export functions for global access
window.basketFunctions = {
    updateQuantity,
    removeFromBasket,
    saveForLater,
    moveToCart,
    clearBasket,
    applyDiscountCode
};