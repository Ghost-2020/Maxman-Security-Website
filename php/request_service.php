<?php
// request_service.php - Handles AJAX service request submissions
header('Content-Type: application/json');

// Database config
$host = 'localhost';
$db   = 'security_company_db';
$user = 'root'; // Change if needed
$pass = '';     // Change if needed

// Validate POST data
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$errors = [];
$fullName = clean_input($_POST['fullName'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$phone = clean_input($_POST['phone'] ?? '');
$serviceType = clean_input($_POST['serviceType'] ?? '');
$otherService = clean_input($_POST['otherService'] ?? '');
$numGuards = intval($_POST['numGuards'] ?? 0);
$serviceDate = clean_input($_POST['serviceDate'] ?? '');
$message = clean_input($_POST['message'] ?? '');

if (!$fullName) $errors[] = 'Full Name is required.';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required.';
if (!$phone) $errors[] = 'Phone Number is required.';
if (!$serviceType) $errors[] = 'Type of Service is required.';
if ($serviceType === 'Other' && !$otherService) $errors[] = 'Please specify the service.';
if ($numGuards < 1) $errors[] = 'Number of Security Guards Needed is required.';
if (!$message) $errors[] = 'Message is required.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Use 'Other' service if selected
$finalServiceType = ($serviceType === 'Other') ? $otherService : $serviceType;

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Insert data securely
$stmt = $conn->prepare("INSERT INTO service_requests (full_name, email, phone, service_type, num_guards, service_date, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssiss', $fullName, $email, $phone, $finalServiceType, $numGuards, $serviceDate, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Your request has been submitted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit your request. Please try again.']);
}
$stmt->close();
$conn->close(); 