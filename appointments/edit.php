<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

$error = '';

/*
 * =========================================
 * APPOINTMENT ACCESS
 * =========================================
 *
 * ADMIN:
 * Can edit any appointment.
 *
 * CLIENT:
 * Can edit only their own appointment.
 */

$appointment_id = (int)($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    header('Location: index.php');
    exit;
}


/*
 * =========================================
 * AVAILABLE APPOINTMENT TIME SLOTS
 * =========================================
 *
 * 1 hour each
 * 8:00 AM - 5:00 PM
 */

$time_slots = [
    '08:00' => '8:00 AM - 9:00 AM',
    '09:00' => '9:00 AM - 10:00 AM',
    '10:00' => '10:00 AM - 11:00 AM',
    '11:00' => '11:00 AM - 12:00 PM',
    '12:00' => '12:00 PM - 1:00 PM',
    '13:00' => '1:00 PM - 2:00 PM',
    '14:00' => '2:00 PM - 3:00 PM',
    '15:00' => '3:00 PM - 4:00 PM',
    '16:00' => '4:00 PM - 5:00 PM'
];


/*
 * =========================================
 * GET EXISTING APPOINTMENT
 * =========================================
 */

$stmt = $pdo->prepare(
    "SELECT
        a.appointment_id,
        a.client_id,
        a.status_id,
        a.service,
        a.appointment_date,
        a.appointment_time,
        c.user_id,
        c.name,
        c.contact_number
     FROM appointment a
     INNER JOIN client c
        ON a.client_id = c.client_id
     WHERE a.appointment_id = ?
     LIMIT 1"
);

$stmt->execute([$appointment_id]);

$appointment = $stmt->fetch();

if (!$appointment) {
    header('Location: index.php');
    exit;
}


/*
 * =========================================
 * CLIENT OWNERSHIP CHECK
 * =========================================
 *
 * Admin can edit any appointment.
 * Client can edit only their own appointment.
 */

if (
    !is_admin()
    &&
    (int)$appointment['user_id'] !== (int)$_SESSION['user_id']
) {
    header('Location: index.php');
    exit;
}


/*
 * =========================================
 * PROCESS UPDATE
 * =========================================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';


    /*
     * =========================================
     * STATUS PROTECTION
     * =========================================
     *
     * ADMIN:
     * Can change status.
     *
     * CLIENT:
     * Status cannot be changed.
     */

    if (is_admin()) {

        $status_id = (int)(
            $_POST['status_id']
            ?? $appointment['status_id']
        );

    } else {

        $status_id = (int)$appointment['status_id'];

    }


    /*
     * =========================================
     * REQUIRED FIELD VALIDATION
     * =========================================
     */

    if (
        $name === '' ||
        $contact === '' ||
        $service === '' ||
        $date === '' ||
        $time === ''
    ) {

        $error = 'Please complete all required fields.';

    }


    /*
     * =========================================
     * DATE VALIDATION
     * =========================================
     */

    elseif ($date < date('Y-m-d')) {

        $error = 'The appointment date cannot be earlier than today.';

    }


    /*
     * =========================================
     * WEEKEND VALIDATION
     * =========================================
     */

    elseif (date('N', strtotime($date)) >= 6) {

        $error = 'Appointments are closed on Saturdays and Sundays. Please select a weekday.';

    }


    /*
     * =========================================
     * TIME SLOT VALIDATION
     * =========================================
     */

    elseif (!array_key_exists($time, $time_slots)) {

        $error = 'Please select a valid appointment time between 8:00 AM and 5:00 PM.';

    }


    else {

        try {

            /*
             * =========================================
             * PREVENT DOUBLE BOOKING
             * =========================================
             *
             * Exclude the appointment currently being edited.
             */

            $stmt = $pdo->prepare(
                "SELECT appointment_id
                 FROM appointment
                 WHERE appointment_date = ?
                 AND appointment_time = ?
                 AND appointment_id != ?
                 LIMIT 1"
            );

            $stmt->execute([
                $date,
                $time,
                $appointment_id
            ]);

            $existingAppointment = $stmt->fetch();


            if ($existingAppointment) {

                $error = 'This appointment time is already booked. Please choose another time.';

            } else {

                /*
                 * =========================================
                 * START TRANSACTION
                 * =========================================
                 */

                $pdo->beginTransaction();


                /*
                 * =========================================
                 * UPDATE CLIENT INFORMATION
                 * =========================================
                 */

                $stmt = $pdo->prepare(
                    "UPDATE client
                     SET name = ?,
                         contact_number = ?
                     WHERE client_id = ?"
                );

                $stmt->execute([
                    $name,
                    $contact,
                    $appointment['client_id']
                ]);


                /*
                 * =========================================
                 * UPDATE APPOINTMENT
                 * =========================================
                 */

                $stmt = $pdo->prepare(
                    "UPDATE appointment
                     SET status_id = ?,
                         service = ?,
                         appointment_date = ?,
                         appointment_time = ?
                     WHERE appointment_id = ?"
                );

                $stmt->execute([
                    $status_id,
                    $service,
                    $date,
                    $time,
                    $appointment_id
                ]);


                /*
                 * =========================================
                 * COMMIT
                 * =========================================
                 */

                $pdo->commit();


                /*
                 * =========================================
                 * REDIRECT AFTER SUCCESS
                 * =========================================
                 */

                if (is_admin()) {

                    header(
                        'Location: index.php?message=' .
                        urlencode('Appointment updated successfully!')
                    );

                } else {

                    header(
                        'Location: ../client_dashboard.php?message=' .
                        urlencode('Appointment updated successfully!')
                    );

                }

                exit;
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Unable to update the appointment: ' .
                     $e->getMessage();
        }
    }
}


/*
 * =========================================
 * GET STATUS LIST
 * =========================================
 */

$statuses = $pdo->query(
    "SELECT status_id, status_name
     FROM status
     ORDER BY status_id"
)->fetchAll();


/*
 * =========================================
 * GET CURRENT STATUS NAME
 * =========================================
 */

$current_status_name = 'Pending';

foreach ($statuses as $status) {

    if (
        (int)$status['status_id']
        ===
        (int)$appointment['status_id']
    ) {

        $current_status_name = $status['status_name'];
        break;
    }
}


$page_title = 'Edit Appointment';

require_once __DIR__ . '/../includes/header.php';
?>


<div class="card p-4">

    <h2 class="fw-bold mb-2">
        Edit Appointment
    </h2>

    <p class="text-muted mb-4">
        Appointments are available from
        <strong>8:00 AM to 5:00 PM</strong>.
        Each appointment lasts <strong>1 hour</strong>.
    </p>


    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="row">

            <!-- CLIENT NAME -->

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Client Name
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars(
                        $_POST['name']
                        ?? $appointment['name']
                    ) ?>"
                >

            </div>


            <!-- CONTACT NUMBER -->

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Contact Number
                </label>

                <input
                    type="text"
                    name="contact_number"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars(
                        $_POST['contact_number']
                        ?? $appointment['contact_number']
                    ) ?>"
                >

            </div>


            <!-- SERVICE -->

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Service
                </label>

                <input
                    type="text"
                    name="service"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars(
                        $_POST['service']
                        ?? $appointment['service']
                    ) ?>"
                >

            </div>


            <!-- APPOINTMENT DATE -->

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Appointment Date
                </label>

                <input
                    type="date"
                    name="appointment_date"
                    class="form-control"
                    required
                    min="<?= date('Y-m-d') ?>"
                    value="<?= htmlspecialchars(
                        $_POST['appointment_date']
                        ?? $appointment['appointment_date']
                    ) ?>"
                >

                <small class="text-muted">
                    Monday to Friday only
                </small>

            </div>


            <!-- APPOINTMENT TIME -->

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Appointment Time
                </label>

                <select
                    name="appointment_time"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select Time
                    </option>

                    <?php foreach ($time_slots as $time_value => $time_label): ?>

                        <option
                            value="<?= $time_value ?>"
                            <?= (
                                ($_POST['appointment_time']
                                    ?? $appointment['appointment_time'])
                                === $time_value
                            ) ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($time_label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- STATUS -->

            <?php if (is_admin()): ?>

                <!-- ADMIN CAN CHANGE STATUS -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status_id"
                        class="form-select"
                        required
                    >

                        <?php foreach ($statuses as $status): ?>

                            <option
                                value="<?= $status['status_id'] ?>"
                                <?= (
                                    (int)(
                                        $_POST['status_id']
                                        ?? $appointment['status_id']
                                    )
                                    ===
                                    (int)$status['status_id']
                                ) ? 'selected' : '' ?>
                            >

                                <?= htmlspecialchars(
                                    $status['status_name']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <small class="text-muted">
                        Only administrators can update the appointment status.
                    </small>

                </div>

            <?php else: ?>

                <!-- CLIENT CANNOT CHANGE STATUS -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $current_status_name
                        ) ?>"
                        readonly
                    >

                    <small class="text-muted">
                        Only the administrator can change the appointment status.
                    </small>

                </div>

            <?php endif; ?>

        </div>


        <!-- BUTTONS -->

        <button
            type="submit"
            class="btn btn-primary"
        >
            Update Appointment
        </button>


        <?php if (is_admin()): ?>

            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        <?php else: ?>

            <a
                href="../client_dashboard.php"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        <?php endif; ?>

    </form>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>