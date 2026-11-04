CREATE DATABASE IF NOT EXISTS shop_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE shop_db;

SET NAMES utf8mb4;

-- 1. ตารางหมวดหมู่สินค้า
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- 2. ตารางสินค้า
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
);

-- 3. ตารางคำสั่งซื้อ
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0
);

-- 4. ตารางรายการสินค้าในคำสั่งซื้อ
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)
        REFERENCES orders(order_id),
    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
);

-- ข้อมูลหมวดหมู่ตัวอย่าง
INSERT INTO categories (category_name) VALUES
('อุปกรณ์คอมพิวเตอร์'),
('อุปกรณ์เสริม'),
('เครื่องเสียง');

-- ข้อมูลสินค้าตัวอย่าง
INSERT INTO products
(category_id, product_name, price, stock) VALUES
(1, 'Wireless Mouse', 299.00, 20),
(1, 'USB Keyboard', 590.00, 15),
(2, 'Laptop Bag', 450.00, 10),
(2, 'Phone Stand', 159.00, 30),
(3, 'Bluetooth Headphones', 790.00, 12);

-- ข้อมูลคำสั่งซื้อตัวอย่าง
INSERT INTO orders (customer_name, total_price) VALUES
('ปกป้อง ใจรัก', 897.00),
('มาติกา ใจดี', 1339.00);

-- รายละเอียดคำสั่งซื้อ
INSERT INTO order_items
(order_id, product_id, quantity, price) VALUES
(1, 1, 1, 299.00),
(1, 2, 1, 590.00),
(1, 3, 1, 450.00),
(2, 5, 1, 790.00);
