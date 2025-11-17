<?php
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/includes/dbh.inc.php';

try {
    $stmt = $pdo->query('SELECT * FROM service_requests ORDER BY requested_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    error_log("Fetch service requests error: " . $e->getMessage());
    echo json_encode([]);
}
?> 