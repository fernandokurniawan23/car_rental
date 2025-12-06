<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

// Hapus gambar juga jika ada
$result = $conn->query("SELECT image FROM cars WHERE id=$id");
$car = $result->fetch_assoc();

if ($car && $car['image'] && file_exists("../uploads/" . $car['image'])) {
    unlink("../uploads/" . $car['image']);
}

$conn->query("DELETE FROM cars WHERE id=$id");

header("Location: index.php?deleted=1");
exit();
?>
