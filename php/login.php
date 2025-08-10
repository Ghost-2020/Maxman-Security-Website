<?php
session_start();
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'security_company_db';
$user = 'root';
$pass = '';
function clean_input($data) { return htmlspecialchars(strip_tags(trim($data))); }
$username = clean_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password required.']);
    exit;
}
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
$stmt = $conn->prepare("SELECT id, password FROM staff_users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hash);
    $stmt->fetch();
    if (password_verify($password, $hash)) {
        $_SESSION['staff_id'] = $id;
        $_SESSION['staff_username'] = $username;
        echo json_encode(['success' => true, 'message' => 'Login successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
}
$stmt->close();
$conn->close(); 