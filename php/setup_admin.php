<?php
/**
 * Setup Admin User Script
 * Run this once to create/update the admin user
 */

require_once __DIR__ . '/includes/dbh.inc.php';

$email = 'cephaskasanda15@gmail.com';
$password = '1234567890';
$username = 'admin';
$fullName = 'Cephas Kasanda';

try {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM staff_users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing admin
        $updateStmt = $pdo->prepare("
            UPDATE staff_users 
            SET password = ?, 
                role = 'admin', 
                is_active = 1,
                username = ?,
                full_name = ?
            WHERE email = ?
        ");
        $updateStmt->execute([$hashedPassword, $username, $fullName, $email]);
        echo "Admin user updated successfully!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    } else {
        // Insert new admin
        $insertStmt = $pdo->prepare("
            INSERT INTO staff_users (username, email, password, full_name, role, is_active) 
            VALUES (?, ?, ?, ?, 'admin', 1)
        ");
        $insertStmt->execute([$username, $email, $hashedPassword, $fullName]);
        echo "Admin user created successfully!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    }
    
    echo "\nYou can now login with:\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

