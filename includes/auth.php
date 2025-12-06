<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: /car_rental/auth/login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        echo "403 Forbidden - Access denied.";
        exit;
    }
}
