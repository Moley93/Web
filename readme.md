📄 README.md
markdown# 🚀 VYLO E-commerce Website - Complete Setup Guide

## 📋 Overview
A modern, mobile-friendly e-commerce website for VYLO's DMA hardware products with dark theme, user authentication, cart functionality, and secure checkout via MoonPay.

## 🗂️ File Structure
vylo-website/
├── index.html              # Homepage
├── hardware.html           # Product details page
├── store.html              # Product listings & cart
├── register.html           # User registration
├── login.html              # User login
├── checkout.html           # Checkout process
├── profile.html            # User dashboard
├── style.css               # Single stylesheet
├── .htaccess               # Clean URLs & security
├── js/
│   ├── main.js             # Core functionality
│   ├── auth.js             # Authentication
│   ├── cart.js             # Shopping cart
│   ├── email.js            # Email management
│   ├── checkout.js         # Checkout process
│   └── profile.js          # User profile
├── php/
│   ├── config.php          # Database & JWT config
│   ├── register.php        # User registration
│   ├── login.php           # User authentication
│   ├── get_profile.php     # Get user data
│   ├── update_profile.php  # Update user data
│   ├── create_order.php    # Order creation
│   ├── get_orders.php      # Order history
│   ├── get_tracking.php    # Tracking info
│   ├── send_email.php      # Email functionality
│   └── admin/
│       ├── login.php       # Admin authentication
│       ├── dashboard.php   # Admin dashboard
│       ├── update_tracking.php # Manual tracking updates
│       └── logout.php      # Admin logout
├── assets/
│   └── img/                # Product images
├── database_schema.sql     # MySQL/MariaDB schema
└── README.md              # This file

## 🚀 Quick Setup (Plesk Environment)

### 1. Database Setup
1. Create a new MySQL/MariaDB database in Plesk
2. Import the `database_schema.sql` file
3. Update database credentials in `/php/config.php`

### 2. File Upload
1. Upload all files to your domain's root directory
2. Ensure proper file permissions (644 for files, 755 for directories)

### 3. Configuration
Edit `/php/config.php`:
```php
private $host = 'localhost';
private $db_name = 'your_database_name';
private $username = 'your_db_username';
private $password = 'your_db_password';

// Change the JWT secret key
function generateJWT($user_id) {
    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, 'YOUR_RANDOM_SECRET_KEY', true);
}
4. Email Configuration
Update email settings in /php/send_email.php:
php$smtp_host = 'your-smtp-host.com';
$smtp_username = 'your-smtp-username';
$smtp_password = 'your-smtp-password';
$from_email = 'noreply@yourdomain.com';
🛒 MoonPay Integration

Create a MoonPay merchant account
Get your API keys
Update /js/checkout.js with your MoonPay configuration

🔐 Security Setup

Change JWT secret key in config.php
Update admin password (default: admin123)
Enable HTTPS in .htaccess
Configure proper SMTP authentication

📧 Email Features

Order confirmations
Cart abandonment reminders (1 hour)
Account registration confirmations

👨‍💼 Admin Panel
Access: /php/admin/login.php

Default login: admin / admin123
View and manage orders
Add FedEx tracking numbers manually
View sales statistics

🎯 Features
✅ Responsive dark theme design
✅ User registration and authentication
✅ Shopping cart with persistence
✅ MoonPay crypto payment integration
✅ Order tracking system
✅ Admin dashboard
✅ Email notifications
✅ Clean SEO-friendly URLs
🔧 Deployment

Upload all files to server
Import database schema
Configure database connection
Update email settings
Set up MoonPay integration
Test complete checkout flow
Enable HTTPS
Change default passwords

📞 Support

Discord: https://discord.gg/vylo
Email: support@vylo.com

This website is production-ready and follows modern web standards.

## **ASSETS DIRECTORY**

### **📁 assets/img/ (Image Files Needed)**
You'll need to add these image files to the `assets/img/` directory:

- `favicon.ico` - Website favicon
- `vylo-75t.jpg` - VYLO 75T DMA Card product image
- `vylo-75t-main.jpg` - Main detailed image for hardware page
- `vylo-75t-side.jpg` - Side view thumbnail
- `vylo-75t-ports.jpg` - Ports view thumbnail
- `vylo-makcu.jpg` - VYLO Makcu product image
- `vylo-makcu-main.jpg` - Main detailed image for hardware page
- `vylo-makcu-pins.jpg` - Pinout thumbnail
- `vylo-makcu-size.jpg` - Size comparison thumbnail
- `vylo-dichen.jpg` - VYLO Dichen product image
- `vylo-dichen-main.jpg` - Main detailed image for hardware page
- `vylo-dichen-connections.jpg` - Connections thumbnail
- `vylo-dichen-setup.jpg` - Setup thumbnail

---

## **🎯 SUMMARY - File Locations:**

**ROOT LEVEL (7 files):**
- `index.html`, `hardware.html`, `store.html`, `register.html`, `login.html`, `checkout.html`, `profile.html`
- `style.css`, `.htaccess`, `README.md`, `database_schema.sql`

**js/ directory (5 files):**
- `main.js`, `auth.js`, `cart.js`, `email.js`, `checkout.js`, `profile.js`

**php/ directory (8 files):**
- `config.php`, `register.php`, `login.php`, `get_profile.php`, `update_profile.php`, `create_order.php`, `get_orders.php`, `get_tracking.php`, `send_email.php`

**php/admin/ directory (4 files):**
- `login.php`, `dashboard.php`, `update_tracking.php`, `logout.php`

**assets/img/ directory:**
- Product images and favicon (13 image files total)

This gives you a complete, production-ready e-commerce website with all the functionality you requested! 🚀