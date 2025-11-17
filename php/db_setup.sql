-- =====================================================
-- Maxman Security Database Setup Script
-- =====================================================
-- This script creates the database and all required tables
-- for the Maxman Security website and admin dashboard.
--
-- Usage:
--   1. Import this file via phpMyAdmin, or
--   2. Run via MySQL command line: mysql -u root < db_setup.sql
--   3. After setup, run php/setup_admin.php to ensure admin user exists
--
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS security_company_db;
USE security_company_db;

-- =====================================================
-- TABLE: service_requests
-- Stores all service requests from the website
-- =====================================================
CREATE TABLE IF NOT EXISTS service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    other_service VARCHAR(100) NULL,
    num_guards INT NOT NULL,
    service_date DATETIME NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_requested_at (requested_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: newsletter_subscribers
-- Stores newsletter subscription emails
-- =====================================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_subscribed_at (subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: staff_users
-- Stores admin and staff user accounts
-- Only admin role can access the dashboard
-- =====================================================
CREATE TABLE IF NOT EXISTS staff_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'operator') DEFAULT 'operator',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: security_guards
-- Stores information about security guards
-- =====================================================
CREATE TABLE IF NOT EXISTS security_guards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guard_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100) NULL,
    certification_number VARCHAR(50) NULL,
    status ENUM('available', 'assigned', 'off_duty') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_guard_number (guard_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: guard_assignments
-- Tracks assignments of guards to service requests
-- =====================================================
CREATE TABLE IF NOT EXISTS guard_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    service_request_id INT NOT NULL,
    assignment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES security_guards(id) ON DELETE CASCADE,
    FOREIGN KEY (service_request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    INDEX idx_guard_id (guard_id),
    INDEX idx_service_request_id (service_request_id),
    INDEX idx_assignment_date (assignment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT ADMIN USER
-- =====================================================
-- Email: cephaskasanda15@gmail.com
-- Password: 1234567890
-- 
-- Note: The password hash below is a placeholder.
-- For security, run php/setup_admin.php after database setup
-- to generate a fresh password hash.
-- =====================================================
INSERT INTO staff_users (username, email, password, full_name, role, is_active) VALUES 
('admin', 'cephaskasanda15@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cephas Kasanda', 'admin', 1)
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    role = 'admin',
    is_active = 1,
    full_name = 'Cephas Kasanda';

-- =====================================================
-- SAMPLE SECURITY GUARDS
-- =====================================================
INSERT INTO security_guards (guard_number, full_name, phone, email, certification_number, status) VALUES 
('G001', 'John Smith', '+1234567890', 'john.smith@maxman.com', 'CERT001', 'available'),
('G002', 'Sarah Johnson', '+1234567891', 'sarah.johnson@maxman.com', 'CERT002', 'available'),
('G003', 'Michael Brown', '+1234567892', 'michael.brown@maxman.com', 'CERT003', 'available'),
('G004', 'Emily Davis', '+1234567893', 'emily.davis@maxman.com', 'CERT004', 'available'),
('G005', 'David Wilson', '+1234567894', 'david.wilson@maxman.com', 'CERT005', 'available')
ON DUPLICATE KEY UPDATE 
    full_name = VALUES(full_name),
    phone = VALUES(phone),
    email = VALUES(email),
    certification_number = VALUES(certification_number);

-- =====================================================
-- SETUP COMPLETE
-- =====================================================
-- Next steps:
-- 1. Run php/setup_admin.php to ensure admin password is correctly hashed
-- 2. Test login with: cephaskasanda15@gmail.com / 1234567890
-- 3. Access admin dashboard at: admin-dashboard.php
-- =====================================================
