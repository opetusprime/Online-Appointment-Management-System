<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $sql = "DELETE a FROM appointment a
            INNER JOIN client c ON a.client_id = c.client_id
            WHERE a.appointment_id = ?";
    $params = [$id];

    if (!is_admin()) {
        $sql .= " AND c.user_id = ?";
        $params[] = $_SESSION['user_id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

header('Location: index.php?message=' . urlencode('Appointment deleted successfully!'));
exit;
?>
