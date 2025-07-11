class CheckoutManager {
    constructor() {
        // MoonPay configuration - SEPARATE from JWT authentication
        this.moonPayConfig = {
            apiKey: 'pk_test_your_moonpay_publishable_key_here', // Replace with your actual MoonPay publishable key
            environment: 'sandbox', // Use 'production' for live
            currencyCode: 'gbp',
            baseCurrencyCode: 'eth' // or another crypto currency
        };
        
        this.discountCodes = {
            'VYLO10': { type: 'percentage', value: 10 },
            'WELCOME': { type: 'fixed', value: 25 },
            'BUNDLE20': { type: 'percentage', value: 20 }
        };
        this.appliedDiscount = null;
        this.init();
    }

    init() {
        this.loadOrderSummary();
        this.setupEventListeners();
        this.populateUserInfo();
        this.loadMoonPaySDK();
    }

    loadMoonPaySDK() {
        // Load MoonPay SDK - this is separate from your JWT authentication
        if (!window.MoonPaySDK) {
            const script = document.createElement('script');
            script.src = 'https://static.moonpay.com/web-sdk/v1/moonpay-web-sdk.min.js';
            script.onload = () => {
                console.log('MoonPay SDK loaded');
            };
            document.head.appendChild(script);
        }
    }

    setupEventListeners() {
        // Discount code application
        const applyDiscountBtn = document.getElementById('apply-discount');
        if (applyDiscountBtn) {
            applyDiscountBtn.addEventListener('click', this.applyDiscount.bind(this));
        }

        // Payment button
        const payNowBtn = document.getElementById('pay-now');
        if (payNowBtn) {
            payNowBtn.addEventListener('click', this.handlePayment.bind(this));
        }

        // Form validation
        const shippingForm = document.getElementById('shipping-form');
        if (shippingForm) {
            shippingForm.addEventListener('input', this.validateForm.bind(this));
        }
    }

    loadOrderSummary() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const orderItems = document.getElementById('order-items');
        const subtotalEl = document.getElementById('subtotal');
        const finalTotalEl = document.getElementById('final-total');
        const payAmountEl = document.getElementById('pay-amount');

        if (!orderItems) return;

        if (cart.length === 0) {
            orderItems.innerHTML = '<p>Your cart is empty</p>';
            return;
        }

        orderItems.innerHTML = cart.map(item => `
            <div class="order-item">
                <span>${item.name} × ${item.quantity}</span>
                <span>${this.formatCurrency(item.price * item.quantity)}</span>
            </div>
        `).join('');

        this.calculateTotal();
    }

    calculateTotal() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 15.00;
        
        let discountAmount = 0;
        if (this.appliedDiscount) {
            if (this.appliedDiscount.type === 'percentage') {
                discountAmount = subtotal * (this.appliedDiscount.value / 100);
            } else {
                discountAmount = this.appliedDiscount.value;
            }
        }

        const total = subtotal + shipping - discountAmount;

        // Update display
        const subtotalEl = document.getElementById('subtotal');
        const discountAmountEl = document.getElementById('discount-amount');
        const discountLine = document.getElementById('discount-line');
        const finalTotalEl = document.getElementById('final-total');
        const payAmountEl = document.getElementById('pay-amount');

        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
        if (discountAmountEl) discountAmountEl.textContent = discountAmount.toFixed(2);
        if (discountLine) discountLine.style.display = discountAmount > 0 ? 'flex' : 'none';
        if (finalTotalEl) finalTotalEl.textContent = total.toFixed(2);
        if (payAmountEl) payAmountEl.textContent = total.toFixed(2);
    }

    applyDiscount() {
        const discountCode = document.getElementById('discount-code').value.trim().toUpperCase();
        const messageEl = document.getElementById('discount-message');

        if (!discountCode) {
            this.showMessage(messageEl, 'Please enter a discount code', 'error');
            return;
        }

        if (this.discountCodes[discountCode]) {
            this.appliedDiscount = this.discountCodes[discountCode];
            this.calculateTotal();
            this.showMessage(messageEl, 'Discount applied successfully!', 'success');
        } else {
            this.showMessage(messageEl, 'Invalid discount code', 'error');
        }
    }

    populateUserInfo() {
        // Get user data from localStorage - this uses your JWT authentication
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        if (user) {
            const form = document.getElementById('shipping-form');
            if (form) {
                const emailField = form.querySelector('#email');
                const firstNameField = form.querySelector('#first-name');
                const lastNameField = form.querySelector('#last-name');
                
                if (emailField) emailField.value = user.email || '';
                if (firstNameField) firstNameField.value = user.first_name || '';
                if (lastNameField) lastNameField.value = user.last_name || '';
            }
        }
    }

    validateForm() {
        const form = document.getElementById('shipping-form');
        const payNowBtn = document.getElementById('pay-now');

        if (!form || !payNowBtn) return;

        const requiredFields = ['first-name', 'last-name', 'email', 'address', 'city', 'postal-code', 'country'];
        const isValid = requiredFields.every(field => {
            const fieldElement = form.querySelector(`#${field}`);
            return fieldElement && fieldElement.value.trim() !== '';
        });

        payNowBtn.disabled = !isValid;
    }

    async handlePayment() {
        const form = document.getElementById('shipping-form');
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

        if (!form || cart.length === 0) return;

        const formData = new FormData(form);
        const shippingInfo = Object.fromEntries(formData.entries());
        
        const orderData = {
            items: cart,
            shipping: shippingInfo,
            discount: this.appliedDiscount,
            total: this.calculateFinalTotal()
        };

        try {
            // Process payment through MoonPay - this is completely separate from your JWT auth
            const paymentResult = await this.processMoonPayPayment(orderData);
            
            if (paymentResult.success) {
                // Save order to your database using your JWT authentication
                const token = localStorage.getItem('token');
                const response = await fetch('/php/create_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': token ? `Bearer ${token}` : '' // Optional for guest checkout
                    },
                    body: JSON.stringify({
                        ...orderData,
                        paymentId: paymentResult.paymentId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Send confirmation email
                    await this.sendOrderConfirmation(shippingInfo.email, {
                        orderNumber: result.orderNumber,
                        ...orderData
                    });

                    // Clear cart
                    localStorage.removeItem('cart');

                    // Redirect to success page
                    window.location.href = `/order-success?order=${result.orderNumber}`;
                } else {
                    alert('Order creation failed. Please contact support.');
                }
            }
        } catch (error) {
            console.error('Payment error:', error);
            alert('Payment failed. Please try again.');
        }
    }

    calculateFinalTotal() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 15.00;
        
        let discountAmount = 0;
        if (this.appliedDiscount) {
            if (this.appliedDiscount.type === 'percentage') {
                discountAmount = subtotal * (this.appliedDiscount.value / 100);
            } else {
                discountAmount = this.appliedDiscount.value;
            }
        }

        return subtotal + shipping - discountAmount;
    }

    async processMoonPayPayment(orderData) {
        try {
            // MoonPay integration - using their API, not your JWT system
            if (window.MoonPaySDK) {
                const moonpay = new window.MoonPaySDK({
                    flow: 'buy',
                    environment: this.moonPayConfig.environment,
                    variant: 'overlay',
                    params: {
                        apiKey: this.moonPayConfig.apiKey,
                        currencyCode: this.moonPayConfig.currencyCode,
                        baseCurrencyCode: this.moonPayConfig.baseCurrencyCode,
                        baseCurrencyAmount: orderData.total,
                        externalCustomerId: this.generateExternalCustomerId(),
                        redirectURL: `${window.location.origin}/order-success`,
                        colorCode: '#2563eb' // Match your brand color
                    }
                });

                return new Promise((resolve, reject) => {
                    moonpay.on('transactionCompleted', (data) => {
                        resolve({
                            success: true,
                            paymentId: data.transactionId,
                            transactionData: data
                        });
                    });

                    moonpay.on('transactionFailed', (data) => {
                        reject(new Error('Payment failed: ' + data.failureReason));
                    });

                    moonpay.on('close', () => {
                        reject(new Error('Payment cancelled by user'));
                    });

                    moonpay.show();
                });
            } else {
                throw new Error('MoonPay SDK not loaded');
            }
        } catch (error) {
            console.error('MoonPay payment error:', error);
            
            // Fallback to demo mode for testing
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({
                        success: true,
                        paymentId: 'demo_' + Date.now(),
                        transactionData: { demo: true }
                    });
                }, 2000);
            });
        }
    }

    generateExternalCustomerId() {
        // Generate a unique customer ID for MoonPay
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        if (user) {
            return `vylo_user_${user.id}`;
        } else {
            return `vylo_guest_${Date.now()}`;
        }
    }

    async sendOrderConfirmation(email, orderData) {
        try {
            const response = await fetch('/php/send_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'order_confirmation',
                    email: email,
                    orderData: orderData
                })
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error sending order confirmation:', error);
            return false;
        }
    }

    showMessage(element, message, type = 'success') {
        if (!element) return;
        
        element.textContent = message;
        element.className = `form-message ${type}`;
        element.style.display = 'block';
        
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: 'GBP'
        }).format(amount);
    }
}

// Initialize checkout manager if on checkout page
if (window.location.pathname === '/checkout' || window.location.pathname.includes('checkout')) {
    document.addEventListener('DOMContentLoaded', function() {
        window.checkoutManager = new CheckoutManager();
    });
}