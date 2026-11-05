<?php
require_once __DIR__ . '/includes/auth.php';
require_login();


/*
 * =========================================
 * ADMIN ONLY
 * =========================================
 *
 * Only administrators can access this dashboard.
 * Clients are redirected to their own dashboard.
 */

if (!is_admin()) {
    header('Location: client_dashboard.php');
    exit;
}


require_once __DIR__ . '/config/database.php';

/*
 * =========================================
 * DASHBOARD STATISTICS
 * =========================================
 */

$total_appointments = 0;
$pending_appointments = 0;
$confirmed_appointments = 0;
$completed_appointments = 0;

/* Total appointments */
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM appointment
");

$total_appointments = $stmt->fetchColumn();

/* Pending appointments */
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM appointment a
    INNER JOIN status s ON a.status_id = s.status_id
    WHERE s.status_name = 'Pending'
");

$pending_appointments = $stmt->fetchColumn();

/* Confirmed appointments */
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM appointment a
    INNER JOIN status s ON a.status_id = s.status_id
    WHERE s.status_name = 'Confirmed'
");

$confirmed_appointments = $stmt->fetchColumn();

/* Completed appointments */
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM appointment a
    INNER JOIN status s ON a.status_id = s.status_id
    WHERE s.status_name = 'Completed'
");

$completed_appointments = $stmt->fetchColumn();


/*
 * =========================================
 * RECENT APPOINTMENTS
 * =========================================
 */

$stmt = $pdo->query("
    SELECT
        a.appointment_id,
        c.name AS client_name,
        a.service,
        a.appointment_date,
        a.appointment_time,
        s.status_name
    FROM appointment a
    INNER JOIN client c
        ON a.client_id = c.client_id
    INNER JOIN status s
        ON a.status_id = s.status_id
    ORDER BY a.created_at DESC
    LIMIT 5
");

$recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);


$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<!-- =========================================
     DASHBOARD HEADER
========================================= -->

<div class="mb-4">

    <h1 class="fw-bold">
        Dashboard
    </h1>

    <p class="text-muted">
        Manage your appointments from one place.
    </p>

</div>


<!-- =========================================
     SUCCESS MESSAGE
========================================= -->

<?php if (isset($_GET['message'])): ?>

    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['message']) ?>
    </div>

<?php endif; ?>


<!-- =========================================
     DASHBOARD STATISTICS
========================================= -->

<div class="row g-4 mb-4">

    <!-- Total -->
    <div class="col-md-6 col-lg-3">

        <div class="card dashboard-stat-card h-100">

            <div class="card-body">

                <div class="stat-label">
                    Total Appointments
                </div>

                <div class="stat-number">
                    <?= $total_appointments ?>
                </div>

                <div class="stat-description">
                    All appointments
                </div>

            </div>

        </div>

    </div>


    <!-- Pending -->
    <div class="col-md-6 col-lg-3">

        <div class="card dashboard-stat-card h-100">

            <div class="card-body">

                <div class="stat-label">
                    Pending
                </div>

                <div class="stat-number">
                    <?= $pending_appointments ?>
                </div>

                <div class="stat-description">
                    Awaiting confirmation
                </div>

            </div>

        </div>

    </div>


    <!-- Confirmed -->
    <div class="col-md-6 col-lg-3">

        <div class="card dashboard-stat-card h-100">

            <div class="card-body">

                <div class="stat-label">
                    Confirmed
                </div>

                <div class="stat-number">
                    <?= $confirmed_appointments ?>
                </div>

                <div class="stat-description">
                    Confirmed appointments
                </div>

            </div>

        </div>

    </div>


    <!-- Completed -->
    <div class="col-md-6 col-lg-3">

        <div class="card dashboard-stat-card h-100">

            <div class="card-body">

                <div class="stat-label">
                    Completed
                </div>

                <div class="stat-number">
                    <?= $completed_appointments ?>
                </div>

                <div class="stat-description">
                    Finished appointments
                </div>

            </div>

        </div>

    </div>

</div>


<!-- =========================================
     DASHBOARD ACTIONS
========================================= -->

<div class="row g-4">

    <!-- Add Appointment -->
    <div class="col-md-6 col-lg-4">

        <div class="card dashboard-card p-4 h-100">

            <h4>
                Add Appointment
            </h4>

            <p class="text-muted">
                Create a new appointment.
            </p>

            <a
                href="/online-appointment-management-system/online-appointment/appointments/add.php"
                class="btn btn-primary mt-auto"
            >
                Add Appointment
            </a>

        </div>

    </div>


    <!-- View Appointments -->
    <div class="col-md-6 col-lg-4">

        <div class="card dashboard-card p-4 h-100">

            <h4>
                View Appointments
            </h4>

            <p class="text-muted">
                Display the appointment list.
            </p>

            <a
                href="/online-appointment-management-system/online-appointment/appointments/index.php"
                class="btn btn-primary mt-auto"
            >
                View Appointments
            </a>

        </div>

    </div>





    <!-- Update Status -->
    <div class="col-md-6 col-lg-4">

        <div class="card dashboard-card p-4 h-100">

            <h4>
                Update Status
            </h4>

            <p class="text-muted">
                Change an appointment status.
            </p>

            <a
                href="/online-appointment-management-system/online-appointment/appointments/status.php"
                class="btn btn-primary mt-auto"
            >
                Update Status
            </a>

        </div>

    </div>

</div>


<!-- =========================================
     RECENT APPOINTMENTS
========================================= -->

<div class="card recent-appointments-card mt-5 mb-5">

    <div class="card-header recent-header">

        <div>

            <h5 class="mb-1">
                Recent Appointments
            </h5>

            <small class="text-muted">
                Latest appointment activity
            </small>

        </div>

        <a
            href="/online-appointment-management-system/online-appointment/appointments/index.php"
            class="btn btn-sm btn-outline-primary"
        >
            View All
        </a>

    </div>


    <div class="card-body p-0">

        <?php if (empty($recent_appointments)): ?>

            <div class="empty-state">

                <div class="empty-state-icon">
                    📅
                </div>

                <h5>
                    No appointments yet
                </h5>

                <p class="text-muted">
                    Your recent appointments will appear here.
                </p>

                <a
                    href="/online-appointment-management-system/online-appointment/appointments/add.php"
                    class="btn btn-primary"
                >
                    Add Appointment
                </a>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table recent-table">

                    <thead>

                        <tr>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($recent_appointments as $appointment): ?>

                        <tr>

                            <td class="fw-semibold">
                                <?= htmlspecialchars(
                                    $appointment['client_name']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $appointment['service']
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    'M d, Y',
                                    strtotime($appointment['appointment_date'])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    'g:i A',
                                    strtotime($appointment['appointment_time'])
                                ) ?>
                            </td>

                            <td>

                                <?php

                                $status = $appointment['status_name'];

                                $badge_class = 'status-badge';

                                if ($status === 'Pending') {

                                    $badge_class .= ' status-pending';

                                } elseif ($status === 'Confirmed') {

                                    $badge_class .= ' status-confirmed';

                                } elseif ($status === 'Completed') {

                                    $badge_class .= ' status-completed';

                                }

                                ?>

                                <span class="<?= $badge_class ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>