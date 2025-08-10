<?php
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'security_company_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { echo json_encode([]); exit; }
$result = $conn->query('SELECT * FROM emergency_alerts ORDER BY alert_time DESC');
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close(); 