// VYLO Main JavaScript Functions
class VyloApp {
    constructor() {
        this.cart = this.loadCart();
        this.user = this.loadUser();
        this.init();
    }

    init() {
        this.updateCartCount();
        this.updateAuthButtons();
        this.setupEventListeners();
        this.checkAbandonedCart();
    }

    // Cart Management
    loadCart() {
        const cart = localStorage.getItem('vylo_cart');
        return cart ? JSON.parse(cart) : [];
    }

    saveCart() {
        localStorage.setItem('vylo_cart', JSON.stringify(this.cart));
        this.updateCartCount();
    }

    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                ...product,
                quantity: 1,
                addedAt: new Date().toISOString()
            });
        }
        
        this.saveCart();
        this.showNotification(`${product.name} added to cart!`, 'success');
        
        // Set abandoned cart timer
        this.setAbandonedCartTimer();
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        localStorage.removeItem('vylo_abandoned_cart_timer');
    }

    getCartTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const totalItems = this.cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
        }
    }

    // Abandoned Cart Management
    setAbandonedCartTimer() {
        // Clear existing timer
        localStorage.removeItem('vylo_abandoned_cart_timer');
        
        // Set new timer for 1 hour (3600000 ms)
        const timerData = {
            timestamp: Date.now(),
            cartContents: [...this.cart]
        };
        
        localStorage.setItem('vylo_abandoned_cart_timer', JSON.stringify(timerData));
        
        setTimeout(() => {
            this.checkAbandonedCart();
        }, 3600000); // 1 hour
    }

    checkAbandonedCart() {
        const timerData = localStorage.getItem('vylo_abandoned_cart_timer');
        if (!timerData) return;

        const { timestamp, cartContents } = JSON.parse(timerData);
        const oneHourAgo = Date.now() - 3600000;

        if (timestamp < oneHourAgo && cartContents.length > 0 && this.cart.length > 0) {
            this.showAbandonedCartNotification();
            localStorage.removeItem('vylo_abandoned_cart_timer');
        }
    }

    showAbandonedCartNotification() {
        const userEmail = this.user?.email;
        if (userEmail) {
            // In a real implementation, this would send an email
            this.showNotification('Check your email - we saved your cart items!', 'info');
        } else {
            this.showNotification('Items in your cart are waiting! Complete your purchase now.', 'info');
        }
    }

    // User Authentication
    loadUser() {
        const user = localStorage.getItem('vylo_user');
        return user ? JSON.parse(user) : null;
    }

    saveUser(userData) {
        this.user = userData;
        localStorage.setItem('vylo_user', JSON.stringify(userData));
        this.updateAuthButtons();
    }

    logout() {
        this.user = null;
        localStorage.removeItem('vylo_user');
        localStorage.removeItem('vylo_auth_token');
        this.updateAuthButtons();
        this.showNotification('Logged out successfully', 'success');
    }

    updateAuthButtons() {
        const registerBtn = document.getElementById('register-btn');
        const loginBtn = document.getElementById('login-btn');
        const profileBtn = document.getElementById('profile-btn');

        if (this.user) {
            if (registerBtn) registerBtn.classList.add('hidden');
            if (loginBtn) loginBtn.classList.add('hidden');
            if (profileBtn) profileBtn.classList.remove('hidden');
        } else {
            if (registerBtn) registerBtn.classList.remove('hidden');
            if (loginBtn) loginBtn.classList.remove('hidden');
            if (profileBtn) profileBtn.classList.add('hidden');
        }
    }

    // Event Listeners
    setupEventListeners() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                const productCard = e.target.closest('.product-card');
                if (productCard) {
                    const product = this.extractProductData(productCard);
                    this.addToCart(product);
                }
            }

            // Logout button
            if (e.target.id === 'logout-btn') {
                this.logout();
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'register-form') {
                e.preventDefault();
                this.handleRegister(e.target);
            }

            if (e.target.id === 'login-form') {
                e.preventDefault();
                this.handleLogin(e.target);
            }

            if (e.target.id === 'checkout-form') {
                e.preventDefault();
                this.handleCheckout(e.target);
            }
        });
    }

    extractProductData(productCard) {
        return {
            id: productCard.dataset.productId,
            name: productCard.querySelector('.product-name').textContent,
            price: parseFloat(productCard.querySelector('.product-price').textContent.replace('£', '')),
            description: productCard.querySelector('.product-description').textContent,
            image: productCard.querySelector('.product-image img')?.src || ''
        };
    }

    // Registration
    async handleRegister(form) {
        const formData = new FormData(form);
        const userData = Object.fromEntries(formData);

        try {
            const response = await fetch('php/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Registration successful! Please log in.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                this.showNotification(result.message || 'Registration failed', 'error');
            }
        } catch (error) {
            this.showNotification('Registration failed. Please try again.', 'error');
        }
    }

    // Login
    async handleLogin(form) {
        const formData = new FormData(form);
        const loginData = Object.fromEntries(formData);

        try {
            const response = await fetch('php/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();

            if (result.success) {
                this.saveUser(result.user);
                localStorage.setItem('vylo_auth_token', result.token);
                this.showNotification('Login successful!', 'success');
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1000);
            } else {
                this.showNotification(result.message || 'Login failed', 'error');
            }
        } catch (error) {
            this.showNotification('Login failed. Please try again.', 'error');
        }
    }

    // Checkout
    async handleCheckout(form) {
        if (!this.user) {
            this.showNotification('Please log in to complete your purchase', 'error');
            return;
        }

        if (this.cart.length === 0) {
            this.showNotification('Your cart is empty', 'error');
            return;
        }

        const formData = new FormData(form);
        const checkoutData = {
            user_id: this.user.id,
            items: this.cart,
            total: this.getCartTotal(),
            discount_code: formData.get('discount_code'),
            shipping_address: Object.fromEntries(formData)
        };

        try {
            // First, create the order
            const orderResponse = await fetch('php/create_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('vylo_auth_token')}`
                },
                body: JSON.stringify(checkoutData)
            });

            const orderResult = await orderResponse.json();

            if (orderResult.success) {
                // Redirect to MoonPay for payment
                this.redirectToMoonPay(orderResult.order_id, checkoutData.total);
            } else {
                this.showNotification(orderResult.message || 'Checkout failed', 'error');
            }
        } catch (error) {
            this.showNotification('Checkout failed. Please try again.', 'error');
        }
    }

    redirectToMoonPay(orderId, amount) {
        // Store order ID for payment verification
        localStorage.setItem('vylo_pending_order', orderId);
        
        // MoonPay integration would go here
        // For now, simulate redirect
        const moonpayUrl = `https://buy.moonpay.com/?apiKey=YOUR_MOONPAY_API_KEY&currencyCode=gbp&baseCurrencyAmount=${amount}&redirectURL=${encodeURIComponent(window.location.origin + '/payment-success.html')}`;
        
        this.showNotification('Redirecting to payment processor...', 'info');
        setTimeout(() => {
            window.location.href = moonpayUrl;
        }, 2000);
    }

    // Utility Functions
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">×</button>
        `;

        // Add notification styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#007acc'};
            color: white;
            padding: 1rem;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 300px;
        `;

        notification.querySelector('button').style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Discount Code Validation
    async validateDiscountCode(code) {
        try {
            const response = await fetch('php/validate_discount.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code })
            });

            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Failed to validate discount code' };
        }
    }

    // Initialize page-specific functions
    initPage() {
        const page = window.location.pathname.split('/').pop().split('.')[0];
        
        switch (page) {
            case 'basket':
                this.initBasketPage();
                break;
            case 'checkout':
                this.initCheckoutPage();
                break;
            case 'profile':
                this.initProfilePage();
                break;
            case 'hardware':
                this.initHardwarePage();
                break;
        }
    }

    initBasketPage() {
        this.renderCartItems();
    }

    initCheckoutPage() {
        this.renderCheckoutSummary();
        this.setupDiscountCode();
    }

    initProfilePage() {
        this.loadOrderHistory();
    }

    initHardwarePage() {
        this.loadProducts();
    }

    renderCartItems() {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;

        if (this.cart.length === 0) {
            cartContainer.innerHTML = '<p>Your cart is empty</p>';
            return;
        }

        cartContainer.innerHTML = this.cart.map(item => `
            <div class="cart-item" data-product-id="${item.id}">
                <div class="item-info">
                    <h3>${item.name}</h3>
                    <p>£${item.price.toFixed(2)}</p>
                </div>
                <div class="item-controls">
                    <button onclick="vyloApp.updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="vyloApp.updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                    <button onclick="vyloApp.removeFromCart('${item.id}')" class="remove-btn">Remove</button>
                </div>
            </div>
        `).join('');

        const totalElement = document.getElementById('cart-total');
        if (totalElement) {
            totalElement.textContent = `£${this.getCartTotal().toFixed(2)}`;
        }
    }

    setupDiscountCode() {
        const discountForm = document.getElementById('discount-form');
        if (discountForm) {
            discountForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = e.target.discount_code.value;
                const result = await this.validateDiscountCode(code);
                
                if (result.success) {
                    this.showNotification(`Discount applied: ${result.discount}% off`, 'success');
                    this.updateCheckoutTotal(result.discount);
                } else {
                    this.showNotification(result.message, 'error');
                }
            });
        }
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.vyloApp = new VyloApp();
    window.vyloApp.initPage();
});

// Handle payment success
if (window.location.pathname.includes('payment-success')) {
    document.addEventListener('DOMContentLoaded', async () => {
        const orderId = localStorage.getItem('vylo_pending_order');
        if (orderId) {
            // Verify payment and update order status
            const response = await fetch('php/verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_id: orderId })
            });

            const result = await response.json();
            if (result.success) {
                window.vyloApp.clearCart();
                localStorage.removeItem('vylo_pending_order');
            }
        }
    });
}