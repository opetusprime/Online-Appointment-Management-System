<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

/*
 * =========================================
 * SEARCH
 * =========================================
 */

$search = trim($_GET['search'] ?? '');


/*
 * =========================================
 * GET APPOINTMENTS
 * =========================================
 *
 * ADMIN:
 * Can see all appointments.
 *
 * CLIENT:
 * Can only see their own appointments.
 */

if (is_admin()) {

    $sql = "
        SELECT
            a.appointment_id,
            c.name,
            c.contact_number,
            c.user_id,
            a.service,
            a.appointment_date,
            a.appointment_time,
            s.status_name
        FROM appointment a
        INNER JOIN client c
            ON a.client_id = c.client_id
        INNER JOIN status s
            ON a.status_id = s.status_id
        WHERE
            c.name LIKE ?
            OR c.contact_number LIKE ?
            OR a.service LIKE ?
        ORDER BY
            a.appointment_date ASC,
            a.appointment_time ASC
    ";

    $stmt = $pdo->prepare($sql);

    $search_value = '%' . $search . '%';

    $stmt->execute([
        $search_value,
        $search_value,
        $search_value
    ]);

} else {

    $sql = "
        SELECT
            a.appointment_id,
            c.name,
            c.contact_number,
            c.user_id,
            a.service,
            a.appointment_date,
            a.appointment_time,
            s.status_name
        FROM appointment a
        INNER JOIN client c
            ON a.client_id = c.client_id
        INNER JOIN status s
            ON a.status_id = s.status_id
        WHERE
            c.user_id = ?
            AND (
                c.name LIKE ?
                OR c.contact_number LIKE ?
                OR a.service LIKE ?
            )
        ORDER BY
            a.appointment_date ASC,
            a.appointment_time ASC
    ";

    $stmt = $pdo->prepare($sql);

    $search_value = '%' . $search . '%';

    $stmt->execute([
        $_SESSION['user_id'],
        $search_value,
        $search_value,
        $search_value
    ]);
}

$appointments = $stmt->fetchAll();

$page_title = 'Appointments';

require_once __DIR__ . '/../includes/header.php';
?>


<!-- =========================================
     PAGE HEADER
========================================= -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Appointment List
        </h2>

        <p class="text-muted mb-0">
            <?= count($appointments) ?> appointment(s)
        </p>

    </div>


    <a href="add.php" class="btn btn-primary">
        + Add Appointment
    </a>

</div>


<!-- =========================================
     SEARCH
========================================= -->

<div class="card p-3 mb-4">

    <form method="GET">

        <div class="row g-2">

            <div class="col-md-9">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by client name, contact number, or service..."
                    value="<?= htmlspecialchars($search) ?>"
                >

            </div>

            <div class="col-md-3 d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Search
                </button>

                <?php if ($search !== ''): ?>

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary w-100"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </form>

</div>


<!-- =========================================
     APPOINTMENT TABLE
========================================= -->

<div class="card p-3">

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>

                    <th>ID</th>

                    <?php if (is_admin()): ?>

                        <th>Client</th>
                        <th>Contact</th>

                    <?php endif; ?>

                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if (!$appointments): ?>

                <tr>

                    <td
                        colspan="<?= is_admin() ? '8' : '6' ?>"
                        class="text-center text-muted"
                    >

                        <?php if ($search !== ''): ?>

                            No appointments found for
                            "<strong><?= htmlspecialchars($search) ?></strong>".

                        <?php else: ?>

                            No appointments found.

                        <?php endif; ?>

                    </td>

                </tr>


            <?php else: ?>

                <?php foreach ($appointments as $a): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($a['appointment_id']) ?>
                        </td>


                        <?php if (is_admin()): ?>

                            <td>
                                <?= htmlspecialchars($a['name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($a['contact_number']) ?>
                            </td>

                        <?php endif; ?>


                        <td>
                            <?= htmlspecialchars($a['service']) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($a['appointment_date']) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                date(
                                    'g:i A',
                                    strtotime($a['appointment_time'])
                                )
                            ) ?>
                        </td>


                        <td>

                            <span class="badge bg-<?=
                                $a['status_name'] === 'Completed'
                                    ? 'success'
                                    : (
                                        $a['status_name'] === 'Confirmed'
                                            ? 'primary'
                                            : 'warning text-dark'
                                    )
                            ?>">

                                <?= htmlspecialchars($a['status_name']) ?>

                            </span>

                        </td>


                        <td class="text-nowrap">

                            <?php if (
                                is_admin()
                                ||
                                (int)$a['user_id'] === (int)$_SESSION['user_id']
                            ): ?>

                                <a
                                    href="edit.php?id=<?= $a['appointment_id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Edit
                                </a>

                            <?php endif; ?>


                            <?php if (is_admin()): ?>

                                <a
                                    href="delete.php?id=<?= $a['appointment_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to delete this appointment?');"
                                >
                                    Delete
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>