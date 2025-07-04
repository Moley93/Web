# VYLO E-Commerce Website Setup Guide

## 📋 Overview

Your VYLO website is a complete modular e-commerce platform with cryptocurrency payment integration. This guide will help you set up all the necessary services for full functionality.

## 🗂️ File Structure

```
vylo-website/
├── homepage.html          # Main landing page
├── hardware.html          # Hardware store with products
├── firmware.html          # Firmware products page
├── software.html          # Software recommendations page
├── register.html          # User registration
├── login.html             # User authentication
├── profile.html           # User account management
├── basket.html            # Shopping cart
├── checkout.html          # Crypto payment checkout
├── assets/                # (create this folder)
│   ├── css/              # Optional: extract CSS to separate files
│   ├── js/               # Optional: extract JavaScript
│   └── images/           # Product images, logos, etc.
└── README.md             # This file
```

## 🚀 Quick Start (Basic Setup)

1. **Upload Files**: Upload all HTML files to your web hosting provider
2. **Update Wallet Addresses**: Replace demo addresses in `checkout.html` with your real crypto wallets
3. **Test Locally**: Open `homepage.html` in a web browser to test basic functionality

## ⚡ Full Functionality Setup

### 1. EmailJS Configuration (Required for Email Features)

**What it does**: Sends registration confirmations, abandoned cart reminders, and order confirmations.

#### Step 1: Create EmailJS Account
1. Go to [EmailJS.com](https://www.emailjs.com/)
2. Sign up for a free account (1000 emails/month)
3. Verify your email address

#### Step 2: Set Up Email Service
1. In EmailJS dashboard, go to **Email Services**
2. Click **Add New Service**
3. Choose your email provider (Gmail, Outlook, etc.)
4. Follow setup instructions to connect your email
5. Note your **Service ID** (e.g., `service_abc123`)

#### Step 3: Create Email Templates
Create these 3 templates in EmailJS dashboard:

**Template 1: Registration Confirmation**
- Template ID: `template_registration`
- Subject: `Welcome to VYLO - Account Created Successfully`
- HTML Content:
```html
<h2>Welcome to VYLO!</h2>
<p>Hi {{user_name}},</p>
<p>Your account has been successfully created. Here are your details:</p>
<ul>
  <li><strong>Email:</strong> {{user_email}}</li>
  <li><strong>Registration Date:</strong> {{registration_date}}</li>
</ul>
<p>You can now start shopping for premium hardware solutions with next-day UK delivery!</p>
<p>Best regards,<br>The VYLO Team</p>
```

**Template 2: Abandoned Cart Reminder**
- Template ID: `template_abandoned_cart`
- Subject: `Don't forget your VYLO items - 5% discount inside!`
- HTML Content:
```html
<h2>Don't Forget Your Items! 🛒</h2>
<p>Hi {{user_name}},</p>
<p>You left some great items in your basket 2 hours ago:</p>
<p>{{basket_items}}</p>
<p><strong>Complete your purchase now and get 5% off your entire order!</strong></p>
<p>Use discount code: <strong>SAVE5NOW</strong></p>
<p><a href="{{checkout_link}}">Complete Your Order</a></p>
<p>Best regards,<br>The VYLO Team</p>
```

**Template 3: Order Confirmation**
- Template ID: `template_order_confirmation`
- Subject: `VYLO Order Confirmation - {{order_id}}`
- HTML Content:
```html
<h2>Order Confirmed! ✅</h2>
<p>Hi {{user_name}},</p>
<p>Thank you for your order! Payment has been received.</p>
<p><strong>Order Details:</strong></p>
<ul>
  <li><strong>Order ID:</strong> {{order_id}}</li>
  <li><strong>Total:</strong> £{{order_total}}</li>
  <li><strong>Payment Method:</strong> {{crypto_method}}</li>
  <li><strong>Items:</strong> {{order_items}}</li>
</ul>
<p><strong>Next Steps:</strong></p>
<p>Your order will be dispatched within 24 hours with next-day UK delivery.</p>
<p>Track your order: <a href="{{profile_link}}">View Order Status</a></p>
<p>Best regards,<br>The VYLO Team</p>
```

#### Step 4: Get Public Key
1. Go to **Account** → **General**
2. Copy your **Public Key** (e.g., `user_abc123xyz`)

#### Step 5: Implement EmailJS in Your Website

Add this script before the closing `</body>` tag in ALL HTML files:

```html
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
<script type="text/javascript">
(function() {
    emailjs.init("YOUR_PUBLIC_KEY_HERE"); // Replace with your public key
})();
</script>
```

#### Step 6: Update Email Functions

**In `register.html`**, replace the console.log in the setTimeout function with:
```javascript
// Send registration confirmation email
emailjs.send("YOUR_SERVICE_ID", "template_registration", {
    user_name: userData.firstName + ' ' + userData.lastName,
    user_email: userData.email,
    registration_date: new Date().toLocaleDateString('en-UK')
});
```

**In `basket.html`**, replace the console.log in showAbandonedCartNotification with:
```javascript
// Send abandoned cart email
const userData = JSON.parse(localStorage.getItem('userData'));
const basketItems = basket.map(item => `${item.name} (£${item.price})`).join(', ');
emailjs.send("YOUR_SERVICE_ID", "template_abandoned_cart", {
    user_name: userData?.firstName || 'Customer',
    user_email: localStorage.getItem('userEmail'),
    basket_items: basketItems,
    checkout_link: window.location.origin + '/checkout.html'
});
```

**In `checkout.html`**, replace the console.log in processOrder with:
```javascript
// Send order confirmation email
const userData = JSON.parse(localStorage.getItem('userData'));
const itemsList = basket.map(item => `${item.name} x${item.quantity}`).join(', ');
emailjs.send("YOUR_SERVICE_ID", "template_order_confirmation", {
    user_name: userData?.firstName || 'Customer',
    user_email: localStorage.getItem('userEmail'),
    order_id: orderId,
    order_total: orderTotal.toFixed(2),
    crypto_method: selectedCrypto,
    order_items: itemsList,
    profile_link: window.location.origin + '/profile.html'
});
```

### 2. Crypto Price API Integration (Recommended)

**Current State**: Prices are simulated with random fluctuations.

**For Real Prices**, replace the price update function in `checkout.html`:

```javascript
// Replace updateCryptoPrices function with:
async function updateCryptoPrices() {
    try {
        const response = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,litecoin,ripple&vs_currencies=usd');
        const data = await response.json();
        
        cryptoPrices = {
            BTC: data.bitcoin.usd,
            ETH: data.ethereum.usd,
            LTC: data.litecoin.usd,
            XRP: data.ripple.usd
        };
        
        Object.keys(cryptoPrices).forEach(crypto => {
            document.getElementById(`${crypto.toLowerCase()}-price`).textContent = 
                `$${cryptoPrices[crypto].toFixed(2)}`;
        });
        
        if (selectedCrypto) {
            updateCryptoAmount();
        }
    } catch (error) {
        console.error('Failed to fetch crypto prices:', error);
    }
}
```

### 3. QR Code Generation (Recommended)

**Current State**: QR codes are placeholders.

Add QR code library before closing `</body>` tag in `checkout.html`:

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
```

Replace QR code generation in `updatePaymentDetails()`:

```javascript
// Replace QR code section with:
const qrEl = document.getElementById('qrCode');
qrEl.innerHTML = ''; // Clear existing content
QRCode.toCanvas(qrEl, walletAddresses[selectedCrypto], {
    width: 200,
    height: 200,
    margin: 2
}, function (error) {
    if (error) {
        qrEl.innerHTML = `QR Code for ${selectedCrypto}<br>${walletAddresses[selectedCrypto].substring(0, 20)}...`;
    }
});
```

### 4. Update Crypto Wallet Addresses

**⚠️ IMPORTANT**: Replace demo addresses in `checkout.html` with your real wallet addresses:

```javascript
const walletAddresses = {
    BTC: 'YOUR_BITCOIN_ADDRESS_HERE',
    ETH: 'YOUR_ETHEREUM_ADDRESS_HERE', 
    LTC: 'YOUR_LITECOIN_ADDRESS_HERE',
    XRP: 'YOUR_RIPPLE_ADDRESS_HERE'
};
```

## 💾 Database Requirements

### Current Setup (No Database Required)
- Uses **localStorage** for all data persistence
- Suitable for single-user demo or small-scale deployment
- Data stored in user's browser

### Production Database Setup (Optional)

For multi-user production environment, consider:

**Option 1: Firebase (Recommended)**
- Free tier available
- Real-time database
- Built-in authentication
- Easy integration with JavaScript

**Option 2: Traditional Backend**
- Node.js + Express + MongoDB/PostgreSQL
- Requires server setup and maintenance
- More control and scalability

**What to migrate to database:**
- User accounts and profiles
- Order history
- Product inventory
- Abandoned cart tracking

## 🌐 Hosting Options

### Static Hosting (Current Setup)
- **Netlify** (Recommended) - Free tier with custom domains
- **Vercel** - Great for frontend projects
- **GitHub Pages** - Free with GitHub repository
- **Traditional Web Hosting** - Any provider with HTML support

### Dynamic Hosting (If Adding Backend)
- **Heroku** - Easy deployment
- **DigitalOcean** - VPS hosting
- **AWS/Google Cloud** - Enterprise solutions

## 🔒 Security Considerations

1. **HTTPS Required**: Crypto transactions need secure connections
2. **Wallet Security**: Use separate wallets for business transactions
3. **API Keys**: Never expose private keys in frontend code
4. **Email Security**: Use environment variables for EmailJS keys in production

## 🧪 Testing Checklist

### Before Going Live:
- [ ] EmailJS working for all 3 email types
- [ ] Real crypto wallet addresses added
- [ ] QR codes generating properly
- [ ] All forms validating correctly
- [ ] Mobile responsive on all pages
- [ ] Crypto price updates working
- [ ] Order flow complete from basket to confirmation

### Test User Flows:
1. **Registration** → Check email confirmation
2. **Add items to basket** → Wait 2 hours → Check abandoned cart email
3. **Complete purchase** → Check order confirmation email
4. **Login/logout** → Verify state management
5. **Discount codes** → Test all valid codes

## 📞 Support & Maintenance

### Regular Tasks:
- Monitor email delivery rates
- Update crypto wallet addresses if needed
- Check for broken links or forms
- Update product information
- Monitor localStorage usage limits

### Troubleshooting:
- Check browser console for JavaScript errors
- Verify EmailJS quota and service status
- Test crypto price API availability
- Ensure all HTML files are uploaded correctly

## 🚀 Optional Enhancements

### Advanced Features to Consider:
1. **Real Payment Verification**: Integrate blockchain APIs
2. **Inventory Management**: Track product stock
3. **Customer Support**: Add chat widget
4. **Analytics**: Google Analytics integration
5. **SEO Optimization**: Meta tags and structured data
6. **Progressive Web App**: Add service worker for offline functionality

## 📝 Configuration Summary

**Replace these values in your files:**

| File | Variable | Replace With |
|------|----------|-------------|
| All HTML files | `YOUR_PUBLIC_KEY_HERE` | EmailJS Public Key |
| All email functions | `YOUR_SERVICE_ID` | EmailJS Service ID |
| checkout.html | `walletAddresses` | Your real crypto addresses |

## ✅ Launch Checklist

- [ ] All files uploaded to hosting
- [ ] EmailJS configured and tested
- [ ] Real crypto addresses added
- [ ] QR codes working
- [ ] SSL certificate installed (HTTPS)
- [ ] Domain name configured
- [ ] All email templates tested
- [ ] Mobile testing completed
- [ ] Backup of all files created

---

**Need Help?** 
- EmailJS Documentation: https://www.emailjs.com/docs/
- QR Code Library: https://github.com/soldair/node-qrcode
- CoinGecko API: https://www.coingecko.com/en/api/documentation

Your VYLO website is designed to work immediately with basic functionality, and these enhancements will make it production-ready!