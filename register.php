<?php
require_once __DIR__ . '/config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($username === '' || $password === '' || $name === '' || $contact_number === '') {
        $message = 'Please fill in all fields.';
        $message_type = 'danger';

    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';

    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $message_type = 'danger';

    } else {

        try {
            // Check if username already exists
            $check = $pdo->prepare(
                "SELECT user_id FROM `user` WHERE username = ?"
            );
            $check->execute([$username]);

            if ($check->fetch()) {
                $message = 'Username already exists. Please choose another one.';
                $message_type = 'danger';

            } else {

                // Start transaction so both records are created together
                $pdo->beginTransaction();

                // Create user account
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $user_stmt = $pdo->prepare(
                    "INSERT INTO `user` (username, password_hash, role)
                     VALUES (?, ?, 'user')"
                );

                $user_stmt->execute([
                    $username,
                    $password_hash
                ]);

                // Get the newly created user ID
                $user_id = $pdo->lastInsertId();

                // Create client record connected to the user
                $client_stmt = $pdo->prepare(
                    "INSERT INTO client (user_id, name, contact_number)
                     VALUES (?, ?, ?)"
                );

                $client_stmt->execute([
                    $user_id,
                    $name,
                    $contact_number
                ]);

                $pdo->commit();

                $message = 'Registration successful! You can now log in.';
                $message_type = 'success';

                // Clear form values
                $_POST = [];
            }

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = 'Registration failed. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration - Online Appointment Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-body p-4">

                    <h2 class="text-center mb-4">
                        Client Registration
                    </h2>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input
                                type="text"
                                name="contact_number"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Register
                        </button>

                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php">
                            Already have an account? Login
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>