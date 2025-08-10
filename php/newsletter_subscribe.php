<?php
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'security_company_db';
$user = 'root';
$pass = '';
function clean_input($data) { return htmlspecialchars(strip_tags(trim($data))); }
$email = clean_input($_POST['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email required.']);
    exit;
}
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
$stmt->bind_param('s', $email);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Subscribed successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Subscription failed or already subscribed.']);
}
$stmt->close();
$conn->close(); 