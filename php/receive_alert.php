<?php
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'security_company_db';
$user = 'root';
$pass = '';
function clean_input($data) { return htmlspecialchars(strip_tags(trim($data))); }
$name = clean_input($_POST['alertName'] ?? '');
$phone = clean_input($_POST['alertPhone'] ?? '');
$message = clean_input($_POST['alertMessage'] ?? '');
$location = clean_input($_POST['alertLocation'] ?? '');
if (!$message) {
    echo json_encode(['success' => false, 'message' => 'Message is required.']);
    exit;
}
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO emergency_alerts (name, phone, message, location) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $phone, $message, $location);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Alert received. Help is on the way!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send alert.']);
}
$stmt->close();
$conn->close(); 