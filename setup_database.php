<?php
// Database setup script for Maxman Security
echo "Setting up Maxman Security Database...\n";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL successfully.\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS security_company_db");
    echo "Database 'security_company_db' created/verified.\n";
    
    // Select the database
    $pdo->exec("USE security_company_db");
    
    // Create tables
    $pdo->exec("
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
        )
    ");
    echo "Table 'service_requests' created.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS emergency_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NULL,
            phone VARCHAR(30) NULL,
            message TEXT NOT NULL,
            location VARCHAR(100) NULL,
            status ENUM('active', 'resolved', 'false_alarm') DEFAULT 'active',
            alert_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resolved_at TIMESTAMP NULL
        )
    ");
    echo "Table 'emergency_alerts' created.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )
    ");
    echo "Table 'newsletter_subscribers' created.\n";
    
    $pdo->exec("
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
        )
    ");
    echo "Table 'staff_users' created.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS security_guards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            guard_number VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(100) NULL,
            certification_number VARCHAR(50) NULL,
            status ENUM('available', 'assigned', 'off_duty') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'security_guards' created.\n";
    
    $pdo->exec("
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
        )
    ");
    echo "Table 'guard_assignments' created.\n";
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_service_requests_status ON service_requests(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_service_requests_date ON service_requests(requested_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emergency_alerts_status ON emergency_alerts(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emergency_alerts_time ON emergency_alerts(alert_time)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_guard_assignments_date ON guard_assignments(assignment_date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_guard_assignments_status ON guard_assignments(status)");
    echo "Indexes created.\n";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM staff_users WHERE email = ?");
    $stmt->execute(['cephaskasanda15@gmail.com']);
    
    if (!$stmt->fetch()) {
        // Create admin user with password '1234'
        $hashedPassword = password_hash('1234', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO staff_users (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'cephaskasanda15@gmail.com', $hashedPassword, 'Cephas Kasanda', 'admin']);
        echo "Admin user created successfully.\n";
        echo "Username: admin\n";
        echo "Email: cephaskasanda15@gmail.com\n";
        echo "Password: 1234\n";
    } else {
        echo "Admin user already exists.\n";
    }
    
    // Insert sample security guards
    $guards = [
        ['G001', 'John Smith', '+1234567890', 'john.smith@maxman.com', 'CERT001'],
        ['G002', 'Sarah Johnson', '+1234567891', 'sarah.johnson@maxman.com', 'CERT002'],
        ['G003', 'Michael Brown', '+1234567892', 'michael.brown@maxman.com', 'CERT003'],
        ['G004', 'Emily Davis', '+1234567893', 'emily.davis@maxman.com', 'CERT004'],
        ['G005', 'David Wilson', '+1234567894', 'david.wilson@maxman.com', 'CERT005']
    ];
    
    foreach ($guards as $guard) {
        $stmt = $pdo->prepare("SELECT id FROM security_guards WHERE guard_number = ?");
        $stmt->execute([$guard[0]]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO security_guards (guard_number, full_name, phone, email, certification_number) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute($guard);
        }
    }
    echo "Sample security guards added.\n";
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now log in with:\n";
    echo "Username: admin\n";
    echo "Email: cephaskasanda15@gmail.com\n";
    echo "Password: 1234\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
