<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

/*
 * =========================================
 * CLIENT ACCESS
 * =========================================
 */

if (!is_client()) {
    header('Location: dashboard.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Client';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Client Dashboard - Online Appointment Management System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="bg-light">


<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <span class="navbar-brand fw-bold">
            Online Appointment Management System
        </span>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">
                Welcome,
                <?= htmlspecialchars($username) ?>!
            </span>


            <a
                href="/online-appointment-management-system/online-appointment/logout.php"
                class="btn btn-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>



<div class="container mt-5">


    <div class="text-center mb-5">

        <h1 class="fw-bold">
            Client Dashboard
        </h1>

        <p class="text-muted">
            Manage your appointments easily.
        </p>

    </div>



    <div class="row justify-content-center g-4">


        <!-- BOOK APPOINTMENT -->

        <div class="col-md-5">

            <div class="card shadow-sm h-100">

                <div class="card-body text-center p-4">

                    <h3 class="card-title">
                        Book an Appointment
                    </h3>


                    <p class="card-text text-muted">

                        Schedule a new appointment by
                        selecting a service, date, and time.

                    </p>


                    <a
                        href="/online-appointment-management-system/online-appointment/appointments/add.php"
                        class="btn btn-primary"
                    >
                        Book Appointment
                    </a>

                </div>

            </div>

        </div>



        <!-- MY APPOINTMENTS -->

        <div class="col-md-5">

            <div class="card shadow-sm h-100">

                <div class="card-body text-center p-4">

                    <h3 class="card-title">
                        My Appointments
                    </h3>


                    <p class="card-text text-muted">

                        View and manage your existing
                        appointments.

                    </p>


                    <a
                        href="/online-appointment-management-system/online-appointment/appointments/index.php"
                        class="btn btn-success"
                    >
                        View My Appointments
                    </a>

                </div>

            </div>

        </div>


    </div>



    <!-- LOGOUT -->

    <div class="text-center mt-5">

        <a
            href="/online-appointment-management-system/online-appointment/logout.php"
            class="btn btn-outline-danger"
        >
            Logout
        </a>

    </div>


</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>