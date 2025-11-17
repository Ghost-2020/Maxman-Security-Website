<?php
// request_service.php - Handles AJAX service request submissions
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/includes/dbh.inc.php';

// Validate POST data
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$errors = [];
$fullName = clean_input($_POST['fullName'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$phone = clean_input($_POST['phone'] ?? '');
$serviceType = clean_input($_POST['serviceType'] ?? '');
$otherService = clean_input($_POST['otherService'] ?? '');
$numGuards = intval($_POST['numGuards'] ?? 0);
$serviceDate = clean_input($_POST['serviceDate'] ?? null);
$message = clean_input($_POST['message'] ?? '');

// Validation
if (empty($fullName)) {
    $errors[] = 'Full Name is required.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid Email is required.';
}
if (empty($phone)) {
    $errors[] = 'Phone Number is required.';
}
if (empty($serviceType)) {
    $errors[] = 'Type of Service is required.';
}
if ($serviceType === 'Other' && empty($otherService)) {
    $errors[] = 'Please specify the service when selecting "Other".';
}
if ($numGuards < 1) {
    $errors[] = 'Number of Security Guards must be at least 1.';
}
if (empty($message)) {
    $errors[] = 'Additional Details are required.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Use 'Other' service if selected
$finalServiceType = ($serviceType === 'Other') ? $otherService : $serviceType;

// Convert serviceDate to null if empty
$serviceDate = empty($serviceDate) ? null : $serviceDate;

try {
    // Insert data securely using PDO
    $stmt = $pdo->prepare("
        INSERT INTO service_requests 
        (full_name, email, phone, service_type, other_service, num_guards, service_date, message) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $fullName,
        $email,
        $phone,
        $finalServiceType,
        ($serviceType === 'Other' ? $otherService : null),
        $numGuards,
        $serviceDate,
        $message
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Your request has been submitted successfully! We will contact you soon.'
    ]);
    
} catch (PDOException $e) {
    error_log("Service request error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to submit your request. Please try again later.'
    ]);
}
?> 