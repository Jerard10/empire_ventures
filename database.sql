CREATE DATABASE IF NOT EXISTS empire_ventures CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE empire_ventures;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'wholesale', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rice Varieties table
CREATE TABLE IF NOT EXISTS rice_varieties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    grain_ratio DECIMAL(3,1) DEFAULT 3.0,
    price_per_sack DECIMAL(10,2) NOT NULL, -- Price for 50kg sack
    sack_weight_kg INT DEFAULT 50,
    stock_sacks INT DEFAULT 500,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Wholesale Registrations table
CREATE TABLE IF NOT EXISTS wholesale_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    full_name VARCHAR(100) NOT NULL,
    registered_name VARCHAR(150) NOT NULL,
    primary_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(30) NOT NULL,
    business_types TEXT NOT NULL, -- Stored as comma-separated values
    rice_variety_id INT NOT NULL,
    supply_volume VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rice_variety_id) REFERENCES rice_varieties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    rice_variety_id INT NOT NULL,
    quantity_sacks INT NOT NULL,
    sack_weight_kg INT DEFAULT 50,
    price_per_sack DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    contact_phone VARCHAR(30) NOT NULL,
    payment_method ENUM('cod', 'bank_transfer', 'gcash') DEFAULT 'cod',
    order_status ENUM('pending', 'processing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rice_variety_id) REFERENCES rice_varieties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Initial Seed Data: Rice Varieties
INSERT INTO rice_varieties (name, description, grain_ratio, price_per_sack, sack_weight_kg, stock_sacks) VALUES
('Jasmine Premium Rice', 'Soft, fragrant and slightly sticky. A reliable everyday table rice.', 3.4, 2450.00, 50, 1000),
('Sinandomeng Special', 'A popular Philippine staple with a soft, fluffy bite. Easy to move in retail.', 3.0, 2100.00, 50, 1200),
('Japanese Sticky Rice (Sushirice)', 'Short, plump grains that cling together. Made for sushi and rice bowls.', 1.9, 3200.00, 50, 400),
('Brown Organic Rice', 'Whole grain with a nutty flavor. A good fit for health-minded shoppers.', 2.6, 2800.00, 50, 300),
('Basmati Long Grain', 'Slender grains that cook up long, dry and separate. Suits biryani and pilaf.', 4.6, 3800.00, 50, 500),
('Dinorado Fancy Rice', 'A premium Philippine rice prized for its aroma and tender texture.', 3.2, 2600.00, 50, 800),
('Mixed Assorted Varieties', 'Combine several grades in one order to match what your customers ask for.', 3.0, 2300.00, 50, 999);

-- Run once in phpMyAdmin (database: empire_ventures). Needs MariaDB (XAMPP); on plain MySQL remove "IF NOT EXISTS".
ALTER TABLE users          ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;
ALTER TABLE rice_varieties ADD COLUMN IF NOT EXISTS image  VARCHAR(255) NULL;
 
-- Make yourself the admin (change the email):
UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
-- If that errors because `role` is an ENUM without 'admin', first run:
-- ALTER TABLE users MODIFY role ENUM('customer','admin') NOT NULL DEFAULT 'customer';
 
