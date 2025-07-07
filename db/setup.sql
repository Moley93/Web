-- VYLO Database Setup Script
-- Run this script in your MariaDB database to create the required tables

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS vylo_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vylo_store;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    address_line_1 VARCHAR(255),
    address_line_2 VARCHAR(255),
    city VARCHAR(100),
    postcode VARCHAR(20),
    county VARCHAR(100),
    country VARCHAR(2) DEFAULT 'GB',
    email_verified BOOLEAN DEFAULT FALSE,
    newsletter_subscribed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Products table
CREATE TABLE products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('processors', 'memory', 'storage', 'networking', 'servers', 'custom') NOT NULL,
    stock_quantity INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    weight_kg DECIMAL(8,3),
    dimensions VARCHAR(100),
    manufacturer VARCHAR(100),
    warranty_months INT DEFAULT 12,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    delivery_cost DECIMAL(10,2) DEFAULT 0,
    vat_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GBP',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(255),
    
    -- Shipping information
    shipping_first_name VARCHAR(100) NOT NULL,
    shipping_last_name VARCHAR(100) NOT NULL,
    shipping_company VARCHAR(100),
    shipping_address_1 VARCHAR(255) NOT NULL,
    shipping_address_2 VARCHAR(255),
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postcode VARCHAR(20) NOT NULL,
    shipping_county VARCHAR(100) NOT NULL,
    shipping_country VARCHAR(2) DEFAULT 'GB',
    shipping_phone VARCHAR(20),
    
    -- Delivery information
    delivery_method ENUM('next_day', 'express', 'collection') DEFAULT 'next_day',
    delivery_instructions TEXT,
    tracking_number VARCHAR(100),
    tracking_url VARCHAR(500),
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_orders (user_id),
    INDEX idx_order_status (status),
    INDEX idx_order_date (created_at)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(50) NOT NULL,
    product_id VARCHAR(50) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_items (order_id)
);

-- User addresses table
CREATE TABLE user_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(100),
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    postcode VARCHAR(20) NOT NULL,
    county VARCHAR(100) NOT NULL,
    country VARCHAR(2) DEFAULT 'GB',
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_addresses (user_id)
);

-- Discount codes table
CREATE TABLE discount_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    minimum_order_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount_amount DECIMAL(10,2),
    usage_limit INT,
    usage_count INT DEFAULT 0,
    valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_discount_code (code),
    INDEX idx_discount_active (is_active, valid_from, valid_until)
);

-- Wishlist table
CREATE TABLE wishlist_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id),
    INDEX idx_user_wishlist (user_id)
);

-- Cart items table (for persistent cart storage)
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user_cart (user_id)
);

-- Admin tracking table for order updates
CREATE TABLE order_tracking_updates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    tracking_number VARCHAR(100),
    notes TEXT,
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_tracking_order (order_id)
);

-- Email queue table for order confirmations and notifications
CREATE TABLE email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    type ENUM('order_confirmation', 'shipping_notification', 'delivery_confirmation', 'abandoned_cart') NOT NULL,
    order_id VARCHAR(50),
    user_id INT,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_email_status (status),
    INDEX idx_email_type (type)
);

-- Insert sample products
INSERT INTO products (id, name, description, price, category, stock_quantity, sku, manufacturer) VALUES
('cpu-001', 'Intel Core i9-13900K', '24-core processor with hybrid architecture, 3.0GHz base clock, ideal for high-performance computing and gaming applications.', 589.99, 'processors', 50, 'INTEL-I9-13900K', 'Intel'),
('cpu-002', 'AMD Ryzen 9 7900X', '12-core Zen 4 processor with advanced 5nm technology, exceptional performance for content creation and enterprise workloads.', 449.99, 'processors', 30, 'AMD-R9-7900X', 'AMD'),
('cpu-003', 'Apple M2 Ultra Module', 'Revolutionary ARM-based processor module with unified memory architecture, optimized for professional creative workflows.', 1299.99, 'processors', 10, 'APPLE-M2-ULTRA', 'Apple'),
('ram-001', 'Corsair Dominator DDR5-5600 32GB', 'High-performance DDR5 memory kit with premium aluminum heat spreaders and optimized timings for extreme overclocking.', 299.99, 'memory', 75, 'CORSAIR-DDR5-32GB', 'Corsair'),
('ram-002', 'G.Skill Trident Z5 DDR5-6000 64GB', 'Ultra-high capacity memory kit designed for content creators and professionals requiring maximum system memory bandwidth.', 599.99, 'memory', 25, 'GSKILL-DDR5-64GB', 'G.Skill'),
('ram-003', 'Kingston Server Premier DDR4-3200 128GB', 'Enterprise-grade server memory with ECC support, designed for mission-critical applications and data center environments.', 899.99, 'memory', 15, 'KINGSTON-DDR4-128GB', 'Kingston'),
('ssd-001', 'Samsung 980 PRO NVMe SSD 2TB', 'PCIe 4.0 NVMe SSD with exceptional read/write speeds up to 7,000 MB/s, perfect for gaming and professional applications.', 199.99, 'storage', 100, 'SAMSUNG-980PRO-2TB', 'Samsung'),
('ssd-002', 'WD Black SN850X NVMe 4TB', 'High-capacity gaming SSD with advanced thermal management and game accelerator technology for seamless performance.', 399.99, 'storage', 40, 'WD-SN850X-4TB', 'Western Digital'),
('hdd-001', 'Seagate Exos X18 18TB Enterprise HDD', 'Enterprise-class hard drive with 7200 RPM, designed for 24/7 operation in demanding datacenter environments.', 449.99, 'storage', 20, 'SEAGATE-EXOS-18TB', 'Seagate'),
('net-001', 'Ubiquiti Dream Machine Pro', 'Enterprise UniFi gateway with 10Gbps SFP+ WAN, built-in controller, and advanced security features for business networks.', 379.99, 'networking', 35, 'UBNT-UDM-PRO', 'Ubiquiti'),
('net-002', 'ASUS AX6000 WiFi 6E Router', 'Next-generation WiFi 6E router with 6GHz band support, advanced QoS, and enterprise-grade security features.', 299.99, 'networking', 45, 'ASUS-AX6000', 'ASUS'),
('switch-001', 'Netgear ProSAFE 48-Port Gigabit Switch', 'Managed Layer 2+ switch with 48 Gigabit ports, advanced VLAN support, and comprehensive network management features.', 599.99, 'networking', 15, 'NETGEAR-GS748T', 'Netgear'),
('srv-001', 'Dell PowerEdge R750 2U Server', '2U rack server with dual Intel Xeon processors, 128GB RAM, and enterprise-class reliability for demanding workloads.', 3299.99, 'servers', 8, 'DELL-R750-2U', 'Dell'),
('srv-002', 'HPE ProLiant DL380 Gen11', 'Enterprise server platform with advanced security features, intelligent automation, and optimized performance for virtualization.', 4599.99, 'servers', 5, 'HPE-DL380-G11', 'HPE'),
('blade-001', 'Cisco UCS B200 M6 Blade Server', 'Half-width blade server with Intel Xeon Scalable processors, optimized for high-density datacenter deployments.', 2899.99, 'servers', 12, 'CISCO-UCS-B200M6', 'Cisco');

-- Insert sample discount codes
INSERT INTO discount_codes (code, description, type, value, minimum_order_amount, usage_limit, valid_until) VALUES
('WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 100.00, 100, DATE_ADD(NOW(), INTERVAL 3 MONTH)),
('SAVE15', 'Save 15% on orders over £500', 'percentage', 15.00, 500.00, 50, DATE_ADD(NOW(), INTERVAL 2 MONTH)),
('STUDENT', 'Student discount', 'percentage', 15.00, 50.00, 200, DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('SUMMER25', '£25 off summer sale', 'fixed', 25.00, 200.00, 75, DATE_ADD(NOW(), INTERVAL 1 MONTH)),
('BULK20', '20% off for bulk orders', 'percentage', 20.00, 1000.00, 25, DATE_ADD(NOW(), INTERVAL 6 MONTH));

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_orders_created_date ON orders(created_at DESC);

-- Create view for order summaries
CREATE VIEW order_summary AS
SELECT 
    o.id,
    o.user_id,
    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
    u.email as customer_email,
    o.status,
    o.total_amount,
    o.payment_status,
    o.tracking_number,
    o.created_at,
    o.shipped_at,
    o.delivered_at,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- Trigger to update product stock when order is placed
DELIMITER //
CREATE TRIGGER update_product_stock_after_order
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- Trigger to restore product stock when order is cancelled
DELIMITER //
CREATE TRIGGER restore_product_stock_after_cancel
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != 'cancelled' AND NEW.status = 'cancelled' THEN
        UPDATE products p
        JOIN order_items oi ON p.id = oi.product_id
        SET p.stock_quantity = p.stock_quantity + oi.quantity
        WHERE oi.order_id = NEW.id;
    END IF;
END//
DELIMITER ;

-- Sample admin user (password: admin123)
INSERT INTO users (email, password_hash, first_name, last_name, country, status) VALUES
('admin@vylo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'GB', 'active');

COMMIT;