class CartManager {
    constructor() {
        this.cart = this.loadCart();
        this.init();
    }

    init() {
        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', this.handleAddToCart.bind(this));
        });

        // Clear cart button
        const clearCartBtn = document.getElementById('clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', this.clearCart.bind(this));
        }

        // Initialize cart display
        this.updateCartDisplay();
        this.updateCartCount();

        // Cart reminder timer
        this.startCartReminderTimer();
    }

    loadCart() {
        return JSON.parse(localStorage.getItem('cart') || '[]');
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
        localStorage.setItem('cartUpdated', Date.now().toString());
    }

    handleAddToCart(e) {
        const button = e.target;
        const productId = button.dataset.product;
        const price = parseFloat(button.dataset.price);
        
        // Get product info
        const productCard = button.closest('.product-card, .product-detail, .bundle-card');
        const productName = productCard.querySelector('h2, h3')?.textContent || productId;
        
        // Check if item already exists
        const existingItem = this.cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: productId,
                name: productName,
                price: price,
                quantity: 1
            });
        }

        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
        
        // Visual feedback
        button.textContent = 'Added!';
        button.style.background = 'var(--success)';
        setTimeout(() => {
            button.textContent = 'Add to Cart';
            button.style.background = '';
        }, 2000);
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                this.updateCartDisplay();
                this.updateCartCount();
            }
        }
    }

    updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }
    }

    updateCartDisplay() {
        const cartSummary = document.getElementById('cart-summary');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');

        if (!cartItems) return;

        if (this.cart.length === 0) {
            if (cartSummary) cartSummary.style.display = 'none';
            return;
        }

        if (cartSummary) cartSummary.style.display = 'block';

        cartItems.innerHTML = this.cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>${formatCurrency(item.price)} each</p>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="cart.updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn" onclick="cart.updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                    </div>
                    <button class="btn btn-outline" onclick="cart.removeFromCart('${item.id}')">Remove</button>
                </div>
            </div>
        `).join('');

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        if (cartTotal) cartTotal.textContent = total.toFixed(2);
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
    }

    getTotal() {
        return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    startCartReminderTimer() {
        // Check for cart reminder every minute
        setInterval(() => {
            this.checkCartReminder();
        }, 60000);
    }

    checkCartReminder() {
        if (this.cart.length === 0) return;

        const cartUpdated = parseInt(localStorage.getItem('cartUpdated') || '0');
        const oneHourAgo = Date.now() - (60 * 60 * 1000);
        const reminderShown = localStorage.getItem('cartReminderShown');

        if (cartUpdated < oneHourAgo && !reminderShown) {
            this.showCartReminder();
            localStorage.setItem('cartReminderShown', 'true');
        }
    }

    showCartReminder() {
        const modal = document.getElementById('cart-reminder-modal');
        if (modal) {
            modal.style.display = 'flex';
            
            document.getElementById('continue-shopping')?.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
    }
}

// Initialize cart manager
const cart = new CartManager();