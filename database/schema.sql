-- HarvestTrack Database Schema
-- Drop existing database if exists
DROP DATABASE IF EXISTS harvesttrack;
CREATE DATABASE harvesttrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE harvesttrack;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'farmer', 'officer') NOT NULL DEFAULT 'farmer',
    phone VARCHAR(20),
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Harvests Table
CREATE TABLE harvests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_type VARCHAR(100) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'tons',
    harvest_date DATE NOT NULL,
    location VARCHAR(255),
    farm_name VARCHAR(100),
    season VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_crop_type (crop_type),
    INDEX idx_harvest_date (harvest_date)
) ENGINE=InnoDB;

-- Reports Table
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    harvest_id INT,
    report_type VARCHAR(50) NOT NULL,
    metrics JSON,
    generated_by INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (harvest_id) REFERENCES harvests(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_type)
) ENGINE=InnoDB;

-- Feedback Table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- Storage Capacity Table
CREATE TABLE storage_capacity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    total_capacity DECIMAL(10, 2) NOT NULL,
    used_capacity DECIMAL(10, 2) DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'tons',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- User Settings Table
CREATE TABLE user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_settings (user_id)
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@harvesttrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample storage capacity
INSERT INTO storage_capacity (location, total_capacity, used_capacity) VALUES 
('Main Storage', 160, 120);

-- Insert sample data for demonstration
INSERT INTO users (name, email, password, role, location) VALUES 
('Farmer John', 'john@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Region A'),
('Officer Jane', 'jane@agri.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 'Region B');

-- Insert sample harvest data
INSERT INTO harvests (user_id, crop_type, quantity, harvest_date, location, season) VALUES 
(2, 'Maize', 25.5, '2024-01-15', 'Region A', 'Winter'),
(2, 'Beans', 15.2, '2024-02-20', 'Region A', 'Winter'),
(2, 'Wheat', 30.0, '2024-03-10', 'Region A', 'Spring'),
(2, 'Maize', 28.3, '2024-04-05', 'Region A', 'Spring'),
(2, 'Beans', 18.5, '2024-05-12', 'Region A', 'Spring'),
(2, 'Maize', 32.1, '2024-06-18', 'Region A', 'Summer'),
(2, 'Wheat', 22.7, '2024-07-22', 'Region A', 'Summer'),
(2, 'Beans', 20.3, '2024-08-15', 'Region A', 'Summer'),
(2, 'Maize', 35.8, '2024-09-10', 'Region A', 'Fall'),
(2, 'Wheat', 26.4, '2024-10-05', 'Region A', 'Fall');

-- Insert sample notifications
INSERT INTO notifications (user_id, type, title, message) VALUES 
(NULL, 'system', 'New harvest record added by Farmer John', 'A new harvest record has been added to the system'),
(NULL, 'alert', 'Storage levels low in Region B', 'Storage capacity is running low in Region B. Please review.');
