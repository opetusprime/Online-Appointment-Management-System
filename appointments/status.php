<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

/*
 * =========================================
 * ADMIN ONLY
 * =========================================
 *
 * Only administrators can update appointment
 * statuses.
 *
 * Clients are redirected to their dashboard.
 */

if (!is_admin()) {
    header('Location: ../client_dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

/*
 * =========================================
 * UPDATE APPOINTMENT STATUS
 * =========================================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $status_id = (int)($_POST['status_id'] ?? 0);

    if ($appointment_id <= 0 || $status_id <= 0) {
        $error = 'Please select an appointment and a status.';
    } else {

        $sql = "UPDATE appointment
                SET status_id = ?
                WHERE appointment_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status_id,
            $appointment_id
        ]);

        if ($stmt->rowCount() > 0) {
            $message = 'Appointment status updated successfully!';
        } else {
            $error = 'Appointment not found or no change was made.';
        }
    }
}

/*
 * =========================================
 * GET AVAILABLE STATUSES
 * =========================================
 */

$statuses = $pdo->query(
    "SELECT status_id, status_name
     FROM status
     ORDER BY status_id"
)->fetchAll();

/*
 * =========================================
 * GET ALL APPOINTMENTS
 * =========================================
 *
 * Admin can see all appointments.
 */

$stmt = $pdo->query(
    "SELECT
        a.appointment_id,
        c.name,
        a.service,
        a.appointment_date,
        a.appointment_time,
        a.status_id,
        s.status_name
     FROM appointment a
     INNER JOIN client c
        ON a.client_id = c.client_id
     INNER JOIN status s
        ON a.status_id = s.status_id
     ORDER BY
        a.appointment_date ASC,
        a.appointment_time ASC"
);

$appointments = $stmt->fetchAll();

$page_title = 'Update Status';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card p-4">

    <h2 class="fw-bold mb-4">
        Update Appointment Status
    </h2>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- UPDATE STATUS FORM -->
    <form method="POST" class="row g-3 mb-4">

        <div class="col-md-5">

            <label class="form-label">
                Appointment
            </label>

            <select
                name="appointment_id"
                class="form-select"
                required
            >

                <option value="">
                    Select appointment
                </option>

                <?php foreach ($appointments as $a): ?>

                    <option value="<?= $a['appointment_id'] ?>">

                        #<?= htmlspecialchars($a['appointment_id']) ?>
                        -
                        <?= htmlspecialchars($a['name']) ?>
                        -
                        <?= htmlspecialchars($a['service']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="col-md-5">

            <label class="form-label">
                New Status
            </label>

            <select
                name="status_id"
                class="form-select"
                required
            >

                <option value="">
                    Select status
                </option>

                <?php foreach ($statuses as $status): ?>

                    <option value="<?= $status['status_id'] ?>">

                        <?= htmlspecialchars($status['status_name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="col-md-2 d-flex align-items-end">

            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Update
            </button>

        </div>

    </form>

    <!-- CURRENT APPOINTMENTS -->

    <h5 class="fw-bold">
        Current Appointments
    </h5>

    <div class="table-responsive">

        <table class="table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($appointments as $a): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($a['appointment_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a['name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a['service']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a['appointment_date']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a['appointment_time']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a['status_name']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$appointments): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="text-center text-muted"
                        >
                            No appointments found.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>