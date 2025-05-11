<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

header('Content-Type: application/json');

// Assuming isLoggedIn() is defined in config.php
$isLoggedIn = isLoggedIn();
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'isAdmin' => $isAdmin
]);
?>