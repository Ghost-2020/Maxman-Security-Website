<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'security_company_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['alertName'] ?? 'Anonymous';
    $phone = $_POST['alertPhone'] ?? 'Not provided';
    $message = $_POST['alertMessage'] ?? '';
    $location = $_POST['alertLocation'] ?? null;
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Emergency message is required']);
        exit;
    }
    
    try {
        // Insert alert into database
        $stmt = $pdo->prepare("
            INSERT INTO emergency_alerts (name, phone, message, location, status) 
            VALUES (?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$name, $phone, $message, $location]);
        
        $alertId = $pdo->lastInsertId();
        
        // Send notification to website owner (you can customize this)
        sendOwnerNotification($name, $phone, $message, $location, $alertId);
        
        // Log the emergency alert
        logEmergencyAlert($alertId, $name, $phone, $message, $location);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Emergency alert sent successfully',
            'alert_id' => $alertId
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Function to send notification to website owner
function sendOwnerNotification($name, $phone, $message, $location, $alertId) {
    // You can customize this function to send notifications via:
    // - Email
    // - SMS
    // - Push notifications
    // - Webhook to another service
    
    $ownerEmail = 'cephaskasanda15@gmail.com'; // Website owner's email
    $subject = 'EMERGENCY ALERT - Maxman Security';
    
    $emailBody = "
    EMERGENCY ALERT RECEIVED
    
    Alert ID: {$alertId}
    Time: " . date('Y-m-d H:i:s') . "
    
    From: {$name}
    Phone: {$phone}
    Location: " . ($location ? $location : 'Not provided') . "
    
    Message: {$message}
    
    Please respond immediately!
    
    ---
    Maxman Security Emergency System
    ";
    
    // Send email (you'll need to configure your server's mail settings)
    $headers = 'From: emergency@maxmansecurity.com' . "\r\n" .
               'Reply-To: emergency@maxmansecurity.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    // Uncomment the line below when your server is configured for email
    // mail($ownerEmail, $subject, $emailBody, $headers);
    
    // For now, just log the notification
    error_log("EMERGENCY ALERT: " . $emailBody);
}

// Function to log emergency alerts
function logEmergencyAlert($alertId, $name, $phone, $message, $location) {
    $logEntry = date('Y-m-d H:i:s') . " - Alert ID: {$alertId} - From: {$name} - Phone: {$phone} - Location: {$location} - Message: {$message}\n";
    
    // Log to file
    $logFile = __DIR__ . '/../logs/emergency_alerts.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?> 