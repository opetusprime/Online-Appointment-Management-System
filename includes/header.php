<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Online Appointment Management System';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/online-appointment-management-system/online-appointment/css/style.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary py-3">
    <div class="container">

        <!-- System Name -->

        <?php
        /*
         * =========================================
         * DASHBOARD LINK BASED ON USER ROLE
         * =========================================
         *
         * ADMIN  -> dashboard.php
         * CLIENT -> client_dashboard.php
         */

        if (isset($_SESSION['user_id'])) {

            if (is_admin()) {

                $dashboard_link =
                    '/online-appointment-management-system/online-appointment/dashboard.php';

            } else {

                $dashboard_link =
                    '/online-appointment-management-system/online-appointment/client_dashboard.php';

            }

        } else {

            $dashboard_link =
                '/online-appointment-management-system/online-appointment/login.php';

        }
        ?>

        <a class="navbar-brand fw-bold d-flex align-items-center gap-2"
           href="<?= $dashboard_link ?>">

            <span class="navbar-brand-icon">
                OA
            </span>

            <span>
                Online Appointment
            </span>

        </a>


        <!-- User Navigation -->

        <?php if (isset($_SESSION['user_id'])): ?>

            <div class="d-flex align-items-center gap-3">

                <!-- Logged-in User -->

                <div class="d-flex align-items-center gap-2 text-white">

                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>

                    <div class="d-none d-sm-block">

                        <div class="small opacity-75">
                            Welcome
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </div>

                    </div>

                </div>


                <!-- Logout -->

                <a href="/online-appointment-management-system/online-appointment/logout.php"
                   class="btn btn-light btn-sm px-3"
                   onclick="return confirm('Are you sure you want to logout?');">

                    Logout

                </a>

            </div>

        <?php endif; ?>

    </div>
</nav>

<main class="container py-4">