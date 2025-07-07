// Checkout Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeCheckout();
});

function initializeCheckout() {
    checkUserAuthentication();
    populateOrderSummary();
    setupCheckoutForm();
    setupDeliveryOptions();
    setupDiscountCode();
    populateUserData();
    updateProgressIndicator();
}

function checkUserAuthentication() {
    if (!window.vyloApp.user) {
        window.vyloApp.showNotification('Please log in to proceed with checkout', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
        return;
    }

    if (window.vyloApp.cart.length === 0) {
        window.vyloApp.showNotification('Your cart is empty', 'error');
        setTimeout(() => {
            window.location.href = 'hardware.html';
        }, 2000);
        return;
    }
}

function populateOrderSummary() {
    const orderItemsContainer = document.getElementById('order-items');
    if (!orderItemsContainer) return;

    const cart = window.vyloApp.cart;
    
    orderItemsContainer.innerHTML = cart.map(item => `
        <div class="order-item">
            <div class="item-image">
                <i class="fas fa-microchip"></i>
            </div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <p>Quantity: ${item.quantity}</p>
                <p class="item-price">£${item.price.toFixed(2)} each</p>
            </div>
            <div class="item-total">
                £${(item.price * item.quantity).toFixed(2)}
            </div>
        </div>
    `).join('');

    updateOrderTotals();
}

function updateOrderTotals() {
    const subtotal = window.vyloApp.getCartTotal();
    const deliveryCost = getDeliveryCost();
    const discountAmount = getDiscountAmount(subtotal);
    const discountedSubtotal = subtotal - discountAmount;
    const vat = discountedSubtotal * 0.2;
    const total = discountedSubtotal + deliveryCost + vat;

    // Update display
    updateElement('subtotal', `£${subtotal.toFixed(2)}`);
    updateElement('delivery-cost', deliveryCost === 0 ? 'Free' : `£${deliveryCost.toFixed(2)}`);
    updateElement('vat-amount', `£${vat.toFixed(2)}`);
    updateElement('final-total', `£${total.toFixed(2)}`);

    // Store total for payment processing
    localStorage.setItem('vylo_order_total', `£${total.toFixed(2)}`);

    // Show/hide discount row
    const discountRow = document.getElementById('discount-row');
    if (discountRow) {
        if (discountAmount > 0) {
            discountRow.style.display = 'flex';
            updateElement('discount-amount', `-£${discountAmount.toFixed(2)}`);
        } else {
            discountRow.style.display = 'none';
        }
    }

    // Enable proceed button if total > 0
    const proceedBtn = document.getElementById('proceed-to-payment');
    if (proceedBtn) {
        proceedBtn.disabled = total <= 0;
    }
}

function getDeliveryCost() {
    const selectedDelivery = document.querySelector('input[name="delivery_method"]:checked');
    if (!selectedDelivery) return 0;

    const deliveryCosts = {
        'next_day': 0,
        'express': 9.99,
        'collection': 0
    };

    return deliveryCosts[selectedDelivery.value] || 0;
}

function getDiscountAmount(subtotal) {
    const discountInfo = JSON.parse(localStorage.getItem('vylo_applied_discount') || 'null');
    if (!discountInfo) return 0;

    if (discountInfo.type === 'percentage') {
        return subtotal * (discountInfo.discount / 100);
    } else {
        return Math.min(discountInfo.discount, subtotal);
    }
}

function setupCheckoutForm() {
    const checkoutForm = document.getElementById('checkout-form');
    if (!checkoutForm) return;

    // Form validation
    const requiredFields = checkoutForm.querySelectorAll('input[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', validateField);
        field.addEventListener('input', clearFieldError);
    });

    // UK postcode validation
    const postcodeField = document.getElementById('shipping_postcode');
    if (postcodeField) {
        postcodeField.addEventListener('input', validatePostcode);
    }

    // Phone number formatting
    const phoneField = document.getElementById('shipping_phone');
    if (phoneField) {
        phoneField.addEventListener('input', formatPhoneNumber);
    }

    // Use profile address checkbox
    const useProfileAddress = document.getElementById('use_profile_address');
    if (useProfileAddress) {
        useProfileAddress.addEventListener('change', toggleProfileAddress);
    }
}

function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    
    clearFieldError(event);

    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }

    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }

    return true;
}

function validatePostcode(event) {
    const field = event.target;
    const postcode = field.value.trim().toUpperCase();
    
    // UK postcode regex
    const postcodeRegex = /^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/;
    
    if (postcode && !postcodeRegex.test(postcode)) {
        showFieldError(field, 'Please enter a valid UK postcode');
    } else {
        clearFieldError(event);
        // Format the postcode
        if (postcode) {
            field.value = formatPostcode(postcode);
        }
    }
}

function formatPostcode(postcode) {
    // Add space in UK postcode if not present
    postcode = postcode.replace(/\s/g, '');
    if (postcode.length > 3) {
        return postcode.slice(0, -3) + ' ' + postcode.slice(-3);
    }
    return postcode;
}

function formatPhoneNumber(event) {
    const field = event.target;
    let value = field.value.replace(/[^\d\s+]/g, '');
    
    // Basic UK phone number formatting
    if (value.startsWith('0') && value.length === 11) {
        value = value.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
    } else if (value.startsWith('+44')) {
        value = value.replace(/(\+44)(\d{4})(\d{3})(\d{4})/, '$1 $2 $3 $4');
    }
    
    field.value = value;
}

function showFieldError(field, message) {
    clearFieldError({ target: field });
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
    field.classList.add('error');
}

function clearFieldError(event) {
    const field = event.target;
    const existingError = field.parentNode.querySelector('.field-error');
    
    if (existingError) {
        existingError.remove();
    }
    
    field.classList.remove('error');
}

function setupDeliveryOptions() {
    const deliveryOptions = document.querySelectorAll('input[name="delivery_method"]');
    
    deliveryOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Update visual selection
            document.querySelectorAll('.delivery-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            this.closest('.delivery-option').classList.add('selected');
            
            // Update delivery cost
            updateOrderTotals();
        });
    });
}

function setupDiscountCode() {
    const discountForm = document.getElementById('discount-form');
    if (!discountForm) return;

    discountForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const codeInput = this.discount_code;
        const code = codeInput.value.trim().toUpperCase();
        
        if (!code) {
            showDiscountMessage('Please enter a discount code', 'error');
            return;
        }

        await applyDiscountCode(code);
    });
}

async function applyDiscountCode(code) {
    const submitBtn = document.querySelector('#discount-form button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    try {
        submitBtn.textContent = 'Applying...';
        submitBtn.disabled = true;

        // Simulate API call
        const result = await window.vyloApp.validateDiscountCode(code);
        
        if (result.success) {
            localStorage.setItem('vylo_applied_discount', JSON.stringify(result.discount));
            updateOrderTotals();
            showDiscountMessage(`Discount applied: ${result.discount.discount}${result.discount.type === 'percentage' ? '%' : '£'} off!`, 'success');
            
            // Disable the form
            document.getElementById('discount_code').disabled = true;
            submitBtn.textContent = 'Applied';
        } else {
            showDiscountMessage(result.message || 'Invalid discount code', 'error');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
        
    } catch (error) {
        console.error('Error applying discount:', error);
        showDiscountMessage('Error applying discount code', 'error');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

function showDiscountMessage(message, type) {
    const messageContainer = document.getElementById('discount-message');
    if (!messageContainer) return;

    messageContainer.innerHTML = `
        <div class="discount-message ${type}">
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
            ${message}
        </div>
    `;

    // Clear message after 5 seconds
    setTimeout(() => {
        messageContainer.innerHTML = '';
    }, 5000);
}

function populateUserData() {
    const user = window.vyloApp.user;
    if (!user) return;

    // Populate form fields with user data
    const fieldMappings = {
        'shipping_first_name': user.first_name,
        'shipping_last_name': user.last_name,
        'shipping_company': user.company,
        'shipping_phone': user.phone,
        'shipping_address_1': user.address_line_1,
        'shipping_address_2': user.address_line_2,
        'shipping_city': user.city,
        'shipping_postcode': user.postcode,
        'shipping_county': user.county
    };

    Object.entries(fieldMappings).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field && value) {
            field.value = value;
        }
    });
}

function toggleProfileAddress(event) {
    const useProfile = event.target.checked;
    const user = window.vyloApp.user;
    
    if (useProfile && user) {
        populateUserData();
    } else if (!useProfile) {
        // Clear address fields
        const addressFields = [
            'shipping_address_1', 'shipping_address_2', 'shipping_city', 
            'shipping_postcode', 'shipping_county'
        ];
        
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }
}

function updateProgressIndicator() {
    const progressSteps = document.querySelectorAll('.progress-step');
    
    // Mark first step as active (review order)
    if (progressSteps.length > 0) {
        progressSteps[0].classList.add('active');
    }
}

function proceedToPayment() {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    // Validate form
    const isValid = validateForm(form);
    if (!isValid) {
        window.vyloApp.showNotification('Please complete all required fields correctly', 'error');
        return;
    }

    // Collect form data
    const formData = new FormData(form);
    const orderData = {
        user_id: window.vyloApp.user.id,
        items: window.vyloApp.cart,
        shipping_info: Object.fromEntries(formData),
        delivery_method: formData.get('delivery_method'),
        delivery_instructions: formData.get('delivery_instructions'),
        subtotal: window.vyloApp.getCartTotal(),
        delivery_cost: getDeliveryCost(),
        discount: JSON.parse(localStorage.getItem('vylo_applied_discount') || 'null'),
        total: parseFloat(localStorage.getItem('vylo_order_total').replace('£', ''))
    };

    // Store order data for payment processing
    localStorage.setItem('vylo_checkout_data', JSON.stringify(orderData));
    
    // Update progress indicator
    updateProgressStep(2);
    
    // Initiate payment process
    initiatePayment(orderData);
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });

    return isValid;
}

function updateProgressStep(step) {
    const progressSteps = document.querySelectorAll('.progress-step');
    
    progressSteps.forEach((stepEl, index) => {
        if (index < step) {
            stepEl.classList.add('completed');
            stepEl.classList.remove('active');
        } else if (index === step - 1) {
            stepEl.classList.add('active');
            stepEl.classList.remove('completed');
        } else {
            stepEl.classList.remove('active', 'completed');
        }
    });
}

async function initiatePayment(orderData) {
    try {
        // Create order in backend
        const response = await fetch('php/create_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('vylo_auth_token')}`
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            // Store order ID
            localStorage.setItem('vylo_pending_order', result.order_id);
            
            // Redirect to MoonPay
            redirectToMoonPay(result.order_id, orderData.total);
        } else {
            throw new Error(result.message || 'Failed to create order');
        }

    } catch (error) {
        console.error('Error creating order:', error);
        window.vyloApp.showNotification('Error processing order. Please try again.', 'error');
    }
}

function redirectToMoonPay(orderId, amount) {
    // Update progress to payment step
    updateProgressStep(3);
    
    // Show payment redirection message
    window.vyloApp.showNotification('Redirecting to secure payment...', 'info');
    
    // Construct MoonPay URL
    const moonpayParams = new URLSearchParams({
        apiKey: 'YOUR_MOONPAY_API_KEY', // Replace with actual API key
        currencyCode: 'gbp',
        baseCurrencyAmount: amount.toString(),
        redirectURL: `${window.location.origin}/payment-success.html?order_id=${orderId}`,
        externalCustomerId: window.vyloApp.user.id,
        externalTransactionId: orderId
    });

    const moonpayUrl = `https://buy.moonpay.com/?${moonpayParams.toString()}`;
    
    // Redirect after short delay
    setTimeout(() => {
        window.location.href = moonpayUrl;
    }, 2000);
}

function updateElement(id, content) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = content;
    }
}

// Setup proceed to payment button
document.addEventListener('DOMContentLoaded', function() {
    const proceedBtn = document.getElementById('proceed-to-payment');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', proceedToPayment);
    }
});

// Export functions for global access
window.checkoutFunctions = {
    proceedToPayment,
    validateForm,
    applyDiscountCode,
    updateOrderTotals
};

// Add styles for form validation
const checkoutStyles = `
    .field-error {
        color: #ff4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .form-group input.error,
    .form-group select.error {
        border-color: #ff4444;
    }
    
    .delivery-option {
        border: 2px solid #333;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .delivery-option.selected {
        border-color: #007acc;
        background-color: rgba(0, 122, 204, 0.1);
    }
    
    .delivery-option:hover {
        border-color: #555;
    }
    
    .option-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .option-header .price {
        color: #007acc;
        font-weight: bold;
    }
    
    .discount-message {
        padding: 0.75rem;
        border-radius: 4px;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .discount-message.success {
        background-color: rgba(68, 255, 68, 0.1);
        color: #44ff44;
        border: 1px solid #44ff44;
    }
    
    .discount-message.error {
        background-color: rgba(255, 68, 68, 0.1);
        color: #ff4444;
        border: 1px solid #ff4444;
    }
    
    .progress-step.completed .step-number {
        background-color: #44ff44;
        color: #000;
    }
    
    .progress-step.active .step-number {
        background-color: #007acc;
        color: #fff;
    }
`;

// Inject checkout styles
const styleSheet = document.createElement('style');
styleSheet.textContent = checkoutStyles;
document.head.appendChild(styleSheet);