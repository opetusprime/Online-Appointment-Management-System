<?php
// Run this file once after importing database.sql.
// Then DELETE this file for safety.

require_once __DIR__ . '/config/database.php';

$username = 'admin';
$password = 'admin123';
$role = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT user_id FROM `user` WHERE username = ?");
$stmt->execute([$username]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare("UPDATE `user` SET password_hash = ?, role = ? WHERE user_id = ?");
    $stmt->execute([$hash, $role, $existing['user_id']]);
    $message = 'Admin account updated. Username: admin | Password: admin123';
} else {
    $stmt = $pdo->prepare("INSERT INTO `user` (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hash, $role]);
    $message = 'Admin account created. Username: admin | Password: admin123';
}

echo '<h2>' . htmlspecialchars($message) . '</h2>';
echo '<p>Delete create_admin.php after using it.</p>';
?>
