<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

/*
 * =========================================
 * APPOINTMENT ACCESS
 * =========================================
 *
 * ADMIN:
 * Can create appointments.
 *
 * CLIENT:
 * Can submit appointments.
 */

if (!is_admin() && !is_client()) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error = '';

/*
 * =========================================
 * AVAILABLE TIME SLOTS
 * =========================================
 *
 * 1 HOUR EACH
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
 * GET APPOINTMENT STATUSES
 * =========================================
 */

$statuses = $pdo->query(
    "SELECT status_id, status_name
     FROM status
     ORDER BY status_id"
)->fetchAll();


/*
 * =========================================
 * FIND PENDING STATUS
 * =========================================
 */

$pending_status_id = null;

foreach ($statuses as $status) {

    if (strcasecmp($status['status_name'], 'Pending') === 0) {
        $pending_status_id = (int)$status['status_id'];
        break;
    }
}


/*
 * =========================================
 * MAKE SURE PENDING STATUS EXISTS
 * =========================================
 */

if ($pending_status_id === null) {

    $error = 'The Pending appointment status was not found in the database.';

}


/*
 * =========================================
 * SAVE APPOINTMENT
 * =========================================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {

    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $date = trim($_POST['appointment_date'] ?? '');
    $time = trim($_POST['appointment_time'] ?? '');


    /*
     * =========================================
     * STATUS
     * =========================================
     *
     * CLIENT:
     * Always Pending.
     *
     * ADMIN:
     * Can choose status.
     */

    if (is_admin()) {

        $status_id = (int)(
            $_POST['status_id'] ?? $pending_status_id
        );

    } else {

        $status_id = $pending_status_id;

    }


    /*
     * =========================================
     * REQUIRED FIELDS
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
     * TIME VALIDATION
     * =========================================
     */

    elseif (!array_key_exists($time, $time_slots)) {

        $error = 'Please select a valid appointment time between 8:00 AM and 5:00 PM.';

    }


    /*
     * =========================================
     * STATUS VALIDATION
     * =========================================
     */

    elseif ($status_id <= 0) {

        $error = 'Please select a valid appointment status.';

    }


    else {

        try {

            /*
             * =========================================
             * PREVENT DOUBLE BOOKING
             * =========================================
             */

            $stmt = $pdo->prepare(
                "SELECT appointment_id
                 FROM appointment
                 WHERE appointment_date = ?
                 AND appointment_time = ?
                 LIMIT 1"
            );

            $stmt->execute([
                $date,
                $time
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
                 * FIND CLIENT
                 * =========================================
                 *
                 * Find by logged-in user.
                 *
                 * This is more reliable than checking
                 * name + contact number.
                 */

                $stmt = $pdo->prepare(
                    "SELECT client_id
                     FROM client
                     WHERE user_id = ?
                     LIMIT 1"
                );

                $stmt->execute([
                    $_SESSION['user_id']
                ]);

                $client = $stmt->fetch();


                /*
                 * =========================================
                 * CREATE OR UPDATE CLIENT
                 * =========================================
                 */

                if ($client) {

                    $client_id = (int)$client['client_id'];


                    /*
                     * Update client information
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
                        $client_id
                    ]);

                } else {

                    /*
                     * Create new client
                     */

                    $stmt = $pdo->prepare(
                        "INSERT INTO client
                         (
                            user_id,
                            name,
                            contact_number
                         )
                         VALUES (?, ?, ?)"
                    );

                    $stmt->execute([
                        $_SESSION['user_id'],
                        $name,
                        $contact
                    ]);

                    $client_id = (int)$pdo->lastInsertId();
                }


                /*
                 * =========================================
                 * INSERT APPOINTMENT
                 * =========================================
                 */

                $stmt = $pdo->prepare(
                    "INSERT INTO appointment
                    (
                        client_id,
                        status_id,
                        service,
                        appointment_date,
                        appointment_time
                    )
                    VALUES (?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $client_id,
                    $status_id,
                    $service,
                    $date,
                    $time
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
                        'Location: ../dashboard.php?message=' .
                        urlencode('Appointment added successfully!')
                    );

                } else {

                    header(
                        'Location: ../client_dashboard.php?message=' .
                        urlencode(
                            'Appointment submitted successfully! Your appointment is pending confirmation.'
                        )
                    );

                }

                exit;
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Unable to save the appointment: ' .
                     $e->getMessage();
        }
    }
}


$page_title = 'Add Appointment';

require_once __DIR__ . '/../includes/header.php';
?>


<div class="card p-4">

    <h2 class="fw-bold mb-2">
        Add Appointment
    </h2>

    <p class="text-muted mb-4">
        Appointments are available
        <strong>Monday to Friday</strong>,
        from <strong>8:00 AM to 5:00 PM</strong>.
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
                        $_POST['name'] ?? ''
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
                        $_POST['contact_number'] ?? ''
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
                        $_POST['service'] ?? ''
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
                        $_POST['appointment_date'] ?? ''
                    ) ?>"
                    onchange="checkWeekday(this)"
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
                                ($_POST['appointment_time'] ?? '')
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
                                        ?? $pending_status_id
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

                </div>

            <?php else: ?>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="Pending"
                        readonly
                    >

                    <small class="text-muted">
                        Your appointment will be reviewed and confirmed by the administrator.
                    </small>

                </div>

            <?php endif; ?>


        </div>


        <!-- BUTTONS -->

        <button
            type="submit"
            class="btn btn-primary"
        >
            Book Appointment
        </button>


        <?php if (is_admin()): ?>

            <a
                href="../dashboard.php"
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


<script>
function checkWeekday(input) {

    if (!input.value) {
        return;
    }

    const date = new Date(input.value + 'T00:00:00');

    const day = date.getDay();

    // 0 = Sunday
    // 6 = Saturday

    if (day === 0 || day === 6) {

        alert(
            'Appointments are closed on Saturdays and Sundays.'
        );

        input.value = '';
    }
}
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>