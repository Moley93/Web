class EmailManager {
    constructor() {
        this.init();
    }

    init() {
        // Email functionality will be handled by PHP backend
        console.log('Email manager initialized');
    }

    async sendCartReminder(email, cartItems) {
        try {
            const response = await fetch('/php/send_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'cart_reminder',
                    email: email,
                    cartItems: cartItems
                })
            });

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error sending cart reminder:', error);
            return false;
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
}

const emailManager = new EmailManager();