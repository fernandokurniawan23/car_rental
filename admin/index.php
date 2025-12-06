<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$total_cars = $conn->query("SELECT COUNT(*) AS total FROM cars")->fetch_assoc()['total'];
$total_bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user'")->fetch_assoc()['total'];

// status booking
$pending = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='pending'")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='approved'")->fetch_assoc()['total'];
$rejected = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='rejected'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['name']); ?></strong>!</p>

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Mobil</h5>
                    <h3><?= $total_cars; ?></h3>
                    <a href="cars.php" class="btn btn-outline-primary btn-sm">Kelola Mobil</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Pengguna</h5>
                    <h3><?= $total_users; ?></h3>
                    <a href="users.php" class="btn btn-outline-primary btn-sm">Kelola Pengguna</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Booking</h5>
                    <h3><?= $total_bookings; ?></h3>
                    <a href="bookings.php" class="btn btn-outline-primary btn-sm">Lihat Booking</a>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-5">Status Booking</h4>
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3 class="text-warning"><?= $pending; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <h6>Disetujui</h6>
                    <h3 class="text-success"><?= $approved; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <h6>Ditolak</h6>
                    <h3 class="text-danger"><?= $rejected; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <a href="../auth/logout.php" class="btn btn-danger mt-4">Logout</a>

</div> 

<?php 
include '../includes/footer.php'; 
?>