<?php

session_start();

echo '<pre>';

echo "User ID: ";
var_dump($_SESSION['user_id'] ?? null);

echo "Username: ";
var_dump($_SESSION['username'] ?? null);

echo "Role: ";
var_dump($_SESSION['role'] ?? null);

echo '</pre>';