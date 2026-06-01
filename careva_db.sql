-- CAReva Database Schema
-- Import this file in phpMyAdmin or run: mysql -u root -p < careva_db.sql

CREATE DATABASE IF NOT EXISTS careva_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE careva_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('male','female','other') NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars Table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    variant VARCHAR(100) DEFAULT NULL,
    year INT NOT NULL,
    fuel_type ENUM('petrol','diesel','electric','hybrid','cng') NOT NULL,
    transmission ENUM('manual','automatic','amt') NOT NULL,
    km_driven INT NOT NULL,
    expected_price DECIMAL(12,2) NOT NULL,
    seller_name VARCHAR(100) NOT NULL,
    seller_contact VARCHAR(15) NOT NULL,
    seller_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    number_plate_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','sold','pending','rejected') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Car Images Table
CREATE TABLE IF NOT EXISTS car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Predictions Table
CREATE TABLE IF NOT EXISTS predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    purchase_price DECIMAL(12,2) NOT NULL,
    maintenance_cost DECIMAL(10,2) NOT NULL,
    purchase_year INT NOT NULL,
    value_5yr DECIMAL(12,2) NOT NULL,
    value_10yr DECIMAL(12,2) NOT NULL,
    value_20yr DECIMAL(12,2) NOT NULL,
    maintenance_5yr DECIMAL(12,2) NOT NULL,
    maintenance_10yr DECIMAL(12,2) NOT NULL,
    maintenance_20yr DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, car_id)
);

-- Insert default admin
INSERT INTO admins (username, email, password) VALUES
('admin', 'admin@careva.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Default admin password: 'password'

-- Sample cars data
INSERT INTO users (full_name, dob, gender, mobile, email, city, state, password) VALUES
('Demo User', '1995-01-01', 'male', '9999999999', 'demo@careva.com', 'Mumbai', 'Maharashtra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO cars (user_id, brand, model, variant, year, fuel_type, transmission, km_driven, expected_price, seller_name, seller_contact, seller_address, city, state, latitude, longitude) VALUES
(1, 'Maruti Suzuki', 'Swift', 'ZXI', 2020, 'petrol', 'manual', 35000, 750000, 'Rahul Sharma', '9876543210', 'Andheri West, Mumbai', 'Mumbai', 'Maharashtra', 19.1197, 72.8464),
(1, 'Hyundai', 'Creta', 'SX', 2021, 'diesel', 'automatic', 20000, 1450000, 'Priya Patel', '9876543211', 'Koregaon Park, Pune', 'Pune', 'Maharashtra', 18.5362, 73.8940),
(1, 'Tata', 'Nexon', 'XZ Plus', 2022, 'electric', 'automatic', 15000, 1650000, 'Amit Kumar', '9876543212', 'Indiranagar, Bangalore', 'Bangalore', 'Karnataka', 12.9716, 77.6411),
(1, 'Honda', 'City', 'ZX CVT', 2019, 'petrol', 'automatic', 45000, 1100000, 'Sneha Reddy', '9876543213', 'Banjara Hills, Hyderabad', 'Hyderabad', 'Telangana', 17.4126, 78.4467),
(1, 'Toyota', 'Fortuner', 'Legender 4x4', 2021, 'diesel', 'automatic', 25000, 3800000, 'Vikram Singh', '9876543214', 'Defence Colony, Delhi', 'New Delhi', 'Delhi', 28.5706, 77.2279),
(1, 'Mahindra', 'XUV700', 'AX7 L', 2022, 'diesel', 'automatic', 18000, 2200000, 'Arun Nair', '9876543215', 'Kakkanad, Kochi', 'Kochi', 'Kerala', 10.0329, 76.3010);