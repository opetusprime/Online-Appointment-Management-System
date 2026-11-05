<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$keyword = trim($_GET['q'] ?? '');
$appointments = [];

if ($keyword !== '') {
    $like = '%' . $keyword . '%';

    $sql = "SELECT a.appointment_id, c.name, c.contact_number, a.service,
                   a.appointment_date, a.appointment_time, s.status_name
            FROM appointment a
            INNER JOIN client c ON a.client_id = c.client_id
            INNER JOIN status s ON a.status_id = s.status_id
            WHERE (c.name LIKE ? OR c.contact_number LIKE ? OR a.service LIKE ?
                   OR s.status_name LIKE ?)";
    $params = [$like, $like, $like, $like];

    if (!is_admin()) {
        $sql .= " AND c.user_id = ?";
        $params[] = $_SESSION['user_id'];
    }

    $sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
}

$page_title = 'Search Appointments';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">
    <h2 class="fw-bold mb-4">Search Appointments</h2>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-10">
            <input type="text" name="q" class="form-control"
                   placeholder="Enter name, contact number, service, or status"
                   value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <?php if ($keyword !== ''): ?>
        <p class="text-muted">Search results for: <strong><?= htmlspecialchars($keyword) ?></strong></p>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$appointments): ?>
                    <tr><td colspan="7" class="text-center">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['appointment_id']) ?></td>
                            <td><?= htmlspecialchars($a['name']) ?></td>
                            <td><?= htmlspecialchars($a['contact_number']) ?></td>
                            <td><?= htmlspecialchars($a['service']) ?></td>
                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td><?= htmlspecialchars(date('g:i A', strtotime($a['appointment_time']))) ?></td>
                            <td><?= htmlspecialchars($a['status_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
