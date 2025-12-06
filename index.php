<?php
session_start();
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: /car_rental/admin/index.php');
    } else {
        header('Location: /car_rental/user/index.php');
    }
} else {
    header('Location: /car_rental/user/index.php');
}
exit;
