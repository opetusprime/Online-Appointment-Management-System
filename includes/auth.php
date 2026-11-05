<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
 * =========================================
 * REQUIRE LOGIN
 * =========================================
 */

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {

        header(
            'Location: /online-appointment-management-system/online-appointment/login.php'
        );

        exit;
    }
}


/*
 * =========================================
 * CHECK ADMIN
 * =========================================
 */

function is_admin(): bool
{
    return isset($_SESSION['role'])
        && $_SESSION['role'] === 'admin';
}


/*
 * =========================================
 * CHECK CLIENT
 * =========================================
 *
 * Supports both:
 * - client
 * - user
 *
 * This prevents role mismatch problems.
 */

function is_client(): bool
{
    return isset($_SESSION['role'])
        && (
            $_SESSION['role'] === 'client'
            || $_SESSION['role'] === 'user'
        );
}

?>