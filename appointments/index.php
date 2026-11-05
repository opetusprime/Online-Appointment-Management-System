<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/database.php';

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
            <span id="appointmentCount">0</span> appointment(s)
        </p>

    </div>


    <a href="add.php" class="btn btn-primary">
        + Add Appointment
    </a>

</div>


<!-- =========================================
     LIVE SEARCH
========================================= -->

<div class="card p-3 mb-4">

    <div class="position-relative">

        <input
            type="text"
            id="searchInput"
            class="form-control"
            placeholder="Search by client name, contact number, or service..."
            autocomplete="off"
        >

    </div>

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

            <tbody id="appointmentTable">

                <tr>

                    <td
                        colspan="<?= is_admin() ? '8' : '6' ?>"
                        class="text-center text-muted py-4"
                    >
                        Loading appointments...
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================
     LIVE SEARCH JAVASCRIPT
========================================= -->

<script>

const searchInput = document.getElementById('searchInput');
const appointmentTable = document.getElementById('appointmentTable');
const appointmentCount = document.getElementById('appointmentCount');

let searchTimer;


/*
 * =========================================
 * LOAD APPOINTMENTS
 * =========================================
 */

function loadAppointments() {

    const search = searchInput.value.trim();

    fetch('live_search.php?search=' + encodeURIComponent(search))
        .then(response => response.text())
        .then(data => {

            appointmentTable.innerHTML = data;

            /*
             * Count table rows
             */
            const rows = appointmentTable.querySelectorAll(
                'tr[data-appointment]'
            );

            appointmentCount.textContent = rows.length;

        })
        .catch(error => {

            console.error(error);

            appointmentTable.innerHTML = `
                <tr>
                    <td
                        colspan="<?= is_admin() ? '8' : '6' ?>"
                        class="text-center text-danger py-4"
                    >
                        Unable to load appointments.
                    </td>
                </tr>
            `;

            appointmentCount.textContent = '0';

        });
}


/*
 * =========================================
 * SEARCH WHILE TYPING
 * =========================================
 */

searchInput.addEventListener('input', function () {

    clearTimeout(searchTimer);

    searchTimer = setTimeout(function () {

        loadAppointments();

    }, 200);

});


/*
 * =========================================
 * LOAD ALL APPOINTMENTS ON PAGE LOAD
 * =========================================
 */

loadAppointments();

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>