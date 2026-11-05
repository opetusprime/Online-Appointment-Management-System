<?php

require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../config/database.php';


/*
 * =========================================
 * GET SEARCH
 * =========================================
 */

$search = trim($_GET['search'] ?? '');

$search_value = '%' . $search . '%';


/*
 * =========================================
 * ADMIN
 * =========================================
 *
 * Admin can see all appointments.
 */

if (is_admin()) {

    $stmt = $pdo->prepare(
        "SELECT
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
            a.appointment_time ASC"
    );

    $stmt->execute([
        $search_value,
        $search_value,
        $search_value
    ]);

} else {


    /*
     * =========================================
     * CLIENT
     * =========================================
     *
     * Client can only see their own appointments.
     */

    $stmt = $pdo->prepare(
        "SELECT
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
            a.appointment_time ASC"
    );

    $stmt->execute([
        $_SESSION['user_id'],
        $search_value,
        $search_value,
        $search_value
    ]);
}


$appointments = $stmt->fetchAll();


/*
 * =========================================
 * NO RESULTS
 * =========================================
 */

if (!$appointments) {

    ?>

    <tr>

        <td
            colspan="<?= is_admin() ? '8' : '6' ?>"
            class="text-center text-muted py-4"
        >

            <?php if ($search !== ''): ?>

                No appointments found for
                "<strong><?= htmlspecialchars($search) ?></strong>".

            <?php else: ?>

                No appointments found.

            <?php endif; ?>

        </td>

    </tr>

    <?php

    exit;
}


/*
 * =========================================
 * DISPLAY APPOINTMENTS
 * =========================================
 */

foreach ($appointments as $a):

?>

<tr data-appointment>

    <!-- ID -->

    <td>
        <?= htmlspecialchars($a['appointment_id']) ?>
    </td>


    <!-- CLIENT -->

    <?php if (is_admin()): ?>

        <td>
            <?= htmlspecialchars($a['name']) ?>
        </td>

        <td>
            <?= htmlspecialchars($a['contact_number']) ?>
        </td>

    <?php endif; ?>


    <!-- SERVICE -->

    <td>
        <?= htmlspecialchars($a['service']) ?>
    </td>


    <!-- DATE -->

    <td>
        <?= htmlspecialchars($a['appointment_date']) ?>
    </td>


    <!-- TIME -->

    <td>
        <?= htmlspecialchars(
            date(
                'g:i A',
                strtotime($a['appointment_time'])
            )
        ) ?>
    </td>


    <!-- STATUS -->

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


    <!-- ACTION -->

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