CREATE DATABASE IF NOT EXISTS rst_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rst_erp;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin','manager','staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    barcode VARCHAR(50),
    imei VARCHAR(50),
    cost DECIMAL(10,3) DEFAULT 0.000,
    price DECIMAL(10,3) NOT NULL,
    stock INT DEFAULT 0,
    low_stock_alert INT DEFAULT 5,
    category VARCHAR(50),
    is_deleted TINYINT(1) DEFAULT 0
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    balance DECIMAL(10,3) DEFAULT 0.000
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(20) UNIQUE,
    customer_id INT,
    user_id INT,
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,3),
    discount DECIMAL(10,3) DEFAULT 0.000,
    vat DECIMAL(10,3) DEFAULT 0.000,
    grand_total DECIMAL(10,3),
    payment_method VARCHAR(20),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    product_id INT,
    qty INT,
    price DECIMAL(10,3),
    cost DECIMAL(10,3),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE telecom_operators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80),
    code VARCHAR(10),
    balance DECIMAL(10,3) DEFAULT 0.000,
    commission_percent DECIMAL(5,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1
);

-- Seed admin (password: admin123)
INSERT INTO users (username, password_hash, full_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Sample products
INSERT INTO products (name, sku, barcode, price, cost, stock) VALUES
('iPhone 15 Pro Max 256GB', 'IP15PM-256', '8901234567890', 479.000, 410.000, 12),
('Samsung Galaxy S24 Ultra', 'S24U-512', '8901234567891', 429.000, 365.000, 9),
('Sony WH-1000XM5', 'SONY-XM5', '8901234567892', 134.900, 105.000, 18);