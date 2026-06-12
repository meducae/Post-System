-- UPDATED POS SYSTEM DATABASE SCHEMA
-- Based on the new roadmap requirements

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS product_barcodes;
DROP TABLE IF EXISTS products;

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    buy_price DECIMAL(10,2) NOT NULL,
    sell_price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Product barcodes table (multiple barcodes per product)
CREATE TABLE product_barcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    barcode VARCHAR(13) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_barcode (barcode),
    INDEX idx_product_id (product_id)
);

-- Sales table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('cash', 'card') DEFAULT 'cash',
    profit_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_payment_type (payment_type)
);

-- Sale items table
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    sell_price DECIMAL(10,2) NOT NULL,
    buy_price DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_sale_id (sale_id),
    INDEX idx_product_id (product_id)
);

-- Users table for simple authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier');

-- Insert sample products for testing
INSERT INTO products (name, buy_price, sell_price, quantity) VALUES 
('Pepsi 1.5L', 8000, 12000, 50),
('Coca-Cola 1L', 7000, 11000, 30),
('Fanta 1L', 7500, 11500, 25),
('Snickers', 3000, 5000, 100),
('KitKat', 3500, 5500, 80);

-- Insert sample barcodes
INSERT INTO product_barcodes (product_id, barcode) VALUES 
(1, '1234567890123'),
(1, '9876543210987'),
(2, '1111111111111'),
(3, '2222222222222'),
(4, '3333333333333'),
(5, '4444444444444'); 