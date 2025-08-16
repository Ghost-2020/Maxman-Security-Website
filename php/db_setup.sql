-- SQL to create the database and all tables for Maxman Security
CREATE DATABASE IF NOT EXISTS security_company_db;
USE security_company_db;

-- Service requests table
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Emergency alerts table
CREATE TABLE IF NOT EXISTS emergency_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    message TEXT NOT NULL,
    location VARCHAR(100) NULL,
    status ENUM('active', 'resolved', 'false_alarm') DEFAULT 'active',
    alert_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL
);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Staff users table with improved structure
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Security guards table
CREATE TABLE IF NOT EXISTS security_guards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guard_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100) NULL,
    certification_number VARCHAR(50) NULL,
    status ENUM('available', 'assigned', 'off_duty') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Assignments table to track guard assignments
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
    FOREIGN KEY (guard_id) REFERENCES security_guards(id),
    FOREIGN KEY (service_request_id) REFERENCES service_requests(id)
);

-- Insert default admin user
INSERT INTO staff_users (username, email, password, full_name, role) VALUES 
('admin', 'cephaskasanda15@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cephas Kasanda', 'admin');

-- Insert sample security guards
INSERT INTO security_guards (guard_number, full_name, phone, email, certification_number) VALUES 
('G001', 'John Smith', '+1234567890', 'john.smith@maxman.com', 'CERT001'),
('G002', 'Sarah Johnson', '+1234567891', 'sarah.johnson@maxman.com', 'CERT002'),
('G003', 'Michael Brown', '+1234567892', 'michael.brown@maxman.com', 'CERT003'),
('G004', 'Emily Davis', '+1234567893', 'emily.davis@maxman.com', 'CERT004'),
('G005', 'David Wilson', '+1234567894', 'david.wilson@maxman.com', 'CERT005');

-- Create indexes for better performance
CREATE INDEX idx_service_requests_status ON service_requests(status);
CREATE INDEX idx_service_requests_date ON service_requests(requested_at);
CREATE INDEX idx_emergency_alerts_status ON emergency_alerts(status);
CREATE INDEX idx_emergency_alerts_time ON emergency_alerts(alert_time);
CREATE INDEX idx_guard_assignments_date ON guard_assignments(assignment_date);
CREATE INDEX idx_guard_assignments_status ON guard_assignments(status); 