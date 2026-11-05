<?php
session_start();

require_once __DIR__ . '/config/database.php';

$error = '';

// If already logged in, send user to the correct dashboard
if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: client_dashboard.php');
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        $stmt = $pdo->prepare(
            "SELECT user_id, username, password_hash, role
             FROM `user`
             WHERE username = ?"
        );

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Admin goes to admin dashboard
            if ($user['role'] === 'admin') {

                header('Location: dashboard.php');
                exit;

            // Client goes to client dashboard
            } else {

                header('Location: client_dashboard.php');
                exit;
            }
        }

        $error = 'Invalid username or password.';
    }
}
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
        Login - Online Appointment Management System
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

<body>

<div class="container">

    <div class="card login-card p-4">

        <div class="text-center mb-4">

            <h2 class="fw-bold">
                Online Appointment
            </h2>

            <p class="text-muted mb-0">
                Management System
            </p>

        </div>


        <?php if ($error): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if (isset($_GET['logged_out'])): ?>

            <div class="alert alert-success">
                You have been logged out.
            </div>

        <?php endif; ?>


        <?php if (isset($_GET['setup'])): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($_GET['setup']) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    autocomplete="username"
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    autocomplete="current-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Login
            </button>

        </form>


        <!-- Register Link -->
        <div class="text-center mt-3">

            <span class="text-muted">
                Don't have an account?
            </span>

            <a href="register.php">
                Register here
            </a>

        </div>


        <p class="text-center text-muted small mt-4 mb-0">

            Authorized users only

        </p>

    </div>

</div>

</body>

</html>