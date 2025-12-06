<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Pengguna berhasil dihapus.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Pengguna tidak ditemukan atau bukan 'user'.";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Gagal menghapus pengguna: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "ID Pengguna tidak valid.";
    $_SESSION['message_type'] = "danger";
}

header("Location: users.php");
exit();
?>