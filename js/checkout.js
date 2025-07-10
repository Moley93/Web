class CheckoutManager {
    constructor() {
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
                <span>${formatCurrency(item.price * item.quantity)}</span>
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
            showMessage(messageEl, 'Please enter a discount code', 'error');
            return;
        }

        if (this.discountCodes[discountCode]) {
            this.appliedDiscount = this.discountCodes[discountCode];
            this.calculateTotal();
            showMessage(messageEl, 'Discount applied successfully!', 'success');
        } else {
            showMessage(messageEl, 'Invalid discount code', 'error');
        }
    }

    populateUserInfo() {
        const user = auth.getUser();
        if (user) {
            const form = document.getElementById('shipping-form');
            if (form) {
                form.email.value = user.email || '';
                form['first-name'].value = user.first_name || '';
                form['last-name'].value = user.last_name || '';
            }
        }
    }

    validateForm() {
        const form = document.getElementById('shipping-form');
        const payNowBtn = document.getElementById('pay-now');

        if (!form || !payNowBtn) return;

        const requiredFields = ['first-name', 'last-name', 'email', 'address', 'city', 'postal-code', 'country'];
        const isValid = requiredFields.every(field => form[field].value.trim() !== '');

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
            // Integrate with MoonPay for payment processing
            const paymentResult = await this.processMoonPayPayment(orderData);
            
            if (paymentResult.success) {
                // Save order to database
                const response = await fetch('/php/create_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${auth.getToken()}`
                    },
                    body: JSON.stringify({
                        ...orderData,
                        paymentId: paymentResult.paymentId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Send confirmation email
                    await emailManager.sendOrderConfirmation(shippingInfo.email, {
                        orderNumber: result.orderNumber,
                        ...orderData
                    });

                    // Clear cart
                    cart.clearCart();

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
        // MoonPay integration would go here
        // This is a placeholder for the actual MoonPay API integration
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    success: true,
                    paymentId: 'mp_' + Date.now()
                });
            }, 2000);
        });
    }
}

// Initialize checkout manager if on checkout page
if (window.location.pathname === '/checkout') {
    const checkout = new CheckoutManager();
}