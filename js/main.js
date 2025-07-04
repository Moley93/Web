// js/main.js - Main JavaScript file for VYLO website

// Cart Management
class CartManager {
    constructor() {
        this.init();
    }

    init() {
        // Bind event listeners
        document.addEventListener('DOMContentLoaded', () => {
            this.bindEvents();
            this.updateCartDisplay();
        });
    }

    bindEvents() {
        // Add to cart buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = button.dataset.productId;
                const quantity = button.dataset.quantity || 1;
                this.addToCart(productId, quantity);
            });
        });

        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const action = button.dataset.action;
                const productId = button.dataset.productId;
                this.updateQuantity(productId, action);
            });
        });

        // Remove from cart
        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = button.dataset.productId;
                this.removeFromCart(productId);
            });
        });

        // Apply discount code
        const discountForm = document.getElementById('discount-form');
        if (discountForm) {
            discountForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyDiscountCode();
            });
        }
    }

    async addToCart(productId, quantity = 1) {
        try {
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            const response = await fetch('api/cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Product added to cart!', 'success');
                this.updateCartDisplay();
                
                // Track cart abandonment
                cartAbandonmentTracking.trackCartActivity();
            } else {
                this.showNotification(result.message || 'Error adding to cart', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Error adding to cart', 'error');
        }
    }

    async updateQuantity(productId, action) {
        try {
            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('product_id', productId);
            formData.append('quantity_action', action);

            const response = await fetch('api/cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                location.reload(); // Refresh to show updated quantities
            } else {
                this.showNotification(result.message || 'Error updating quantity', 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Error updating quantity', 'error');
        }
    }

    async removeFromCart(productId) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'remove_from_cart');
            formData.append('product_id', productId);

            const response = await fetch('api/cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Item removed from cart', 'success');
                location.reload();
            } else {
                this.showNotification(result.message || 'Error removing item', 'error');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('Error removing item', 'error');
        }
    }

    async applyDiscountCode() {
        const codeInput = document.getElementById('discount-code');
        const code = codeInput.value.trim();

        if (!code) {
            this.showNotification('Please enter a discount code', 'warning');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'apply_discount');
            formData.append('discount_code', code);

            const response = await fetch('api/discount.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Discount code applied!', 'success');
                location.reload();
            } else {
                this.showNotification(result.message || 'Invalid discount code', 'error');
            }
        } catch (error) {
            console.error('Error applying discount:', error);
            this.showNotification('Error applying discount code', 'error');
        }
    }

    updateCartDisplay() {
        // Update cart count in header
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            fetch('api/cart.php?action=get_count')
                .then(response => response.json())
                .then(data => {
                    cartCountElement.textContent = data.count || 0;
                });
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
}

// Cart Abandonment Tracking
class CartAbandonmentTracking {
    constructor() {
        this.abandonmentTimer = null;
        this.hasItems = false;
        this.emailSent = false;
    }

    init() {
        this.checkCartStatus();
        this.trackCartActivity();
    }

    async checkCartStatus() {
        try {
            const response = await fetch('api/cart.php?action=get_count');
            const data = await response.json();
            this.hasItems = (data.count || 0) > 0;
            
            if (this.hasItems && !this.emailSent) {
                this.startAbandonmentTimer();
            }
        } catch (error) {
            console.error('Error checking cart status:', error);
        }
    }

    trackCartActivity() {
        this.resetAbandonmentTimer();
        
        if (this.hasItems && !this.emailSent) {
            this.startAbandonmentTimer();
        }
    }

    startAbandonmentTimer() {
        this.resetAbandonmentTimer();
        
        // Set timer for 1 hour (3600000 ms)
        this.abandonmentTimer = setTimeout(() => {
            this.sendAbandonmentEmail();
        }, 3600000); // 1 hour
    }

    resetAbandonmentTimer() {
        if (this.abandonmentTimer) {
            clearTimeout(this.abandonmentTimer);
            this.abandonmentTimer = null;
        }
    }

    async sendAbandonmentEmail() {
        if (this.emailSent) return;

        try {
            const formData = new FormData();
            formData.append('action', 'send_abandonment_email');

            const response = await fetch('api/abandonment.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.emailSent = true;
                console.log('Abandonment email sent');
            }
        } catch (error) {
            console.error('Error sending abandonment email:', error);
        }
    }
}

// Form Validation
class FormValidator {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    static validatePassword(password) {
        return password.length >= 8;
    }

    static validateForm(formElement) {
        const errors = [];
        const inputs = formElement.querySelectorAll('[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                errors.push(`${input.name} is required`);
                input.classList.add('error');
            } else {
                input.classList.remove('error');
                
                // Email validation
                if (input.type === 'email' && !this.validateEmail(input.value)) {
                    errors.push('Please enter a valid email address');
                    input.classList.add('error');
                }
                
                // Password validation
                if (input.type === 'password' && !this.validatePassword(input.value)) {
                    errors.push('Password must be at least 8 characters long');
                    input.classList.add('error');
                }
            }
        });

        return errors;
    }
}

// Initialize classes
const cartManager = new CartManager();
const cartAbandonmentTracking = new CartAbandonmentTracking();

// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle forms with validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const errors = FormValidator.validateForm(form);
            
            if (errors.length > 0) {
                e.preventDefault();
                cartManager.showNotification(errors[0], 'error');
            }
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile menu toggle (if needed)
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('mobile-open');
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }
});

// Search function
async function performSearch(query) {
    if (query.length < 2) return;
    
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        displaySearchResults(results);
    } catch (error) {
        console.error('Search error:', error);
    }
}

function displaySearchResults(results) {
    // Implementation for displaying search results
    // This would typically update a search results container
    const resultsContainer = document.querySelector('.search-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
        results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.innerHTML = `
                <h4>${result.name}</h4>
                <p>${result.description}</p>
                <span class="price">${result.price}</span>
            `;
            resultsContainer.appendChild(item);
        });
    }
}

// Utility functions
function formatPrice(price) {
    return '£' + parseFloat(price).toFixed(2);
}

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

// Loading states
function showLoading(element) {
    element.innerHTML = '<div class="loading"></div>';
    element.disabled = true;
}

function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

// Export for global access
window.cartManager = cartManager;
window.cartAbandonmentTracking = cartAbandonmentTracking;
window.FormValidator = FormValidator;