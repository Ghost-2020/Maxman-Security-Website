<?php
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/includes/dbh.inc.php';

// Clean input function
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = clean_input($_POST['email'] ?? '');

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email address is required.']);
    exit;
}

try {
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
        exit;
    }
    
    // Insert new subscriber
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    
    echo json_encode(['success' => true, 'message' => 'Subscribed successfully!']);
    
} catch (PDOException $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    
    // Check if error is due to duplicate entry
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Subscription failed. Please try again later.']);
    }
}
?> 