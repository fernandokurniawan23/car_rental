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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link rel="stylesheet" href="../css/admin.css"> 
</head>
<body>

<div id="wrapper">

    <div id="sidebar-wrapper">
        <div class="sidebar-heading">ADMIN DASHBOARD</div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="cars.php" class="list-group-item list-group-item-action">
                <i class="fas fa-car me-2"></i> Kelola Mobil
            </a>
            <a href="bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check me-2"></i> Kelola Booking
            </a>
            <a href="users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users me-2"></i> Kelola Pengguna
            </a>
            <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4 rounded shadow-sm p-3">
                <h4 class="mb-0">Selamat datang, <strong><?= htmlspecialchars($_SESSION['name']); ?></strong>!</h4>
                <div class="ms-auto">
                    </div>
            </nav>

            <h2 class="mt-4 mb-4">Ringkasan Sistem</h2>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Mobil</h5>
                            <h3><?= $total_cars; ?></h3>
                            <a href="cars.php" class="btn btn-light btn-sm mt-2">Kelola Mobil</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <h5>Total Pengguna</h5>
                            <h3><?= $total_users; ?></h3>
                            <a href="users.php" class="btn btn-light btn-sm mt-2">Kelola Pengguna</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <h5>Total Booking</h5>
                            <h3><?= $total_bookings; ?></h3>
                            <a href="bookings.php" class="btn btn-light btn-sm mt-2">Lihat Booking</a>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3">Status Booking Terbaru</h4>
            <div class="row text-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6><i class="fas fa-clock me-2 text-warning"></i> Pending</h6>
                            <h3 class="text-warning"><?= $pending; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6><i class="fas fa-check-circle me-2 text-success"></i> Disetujui</h6>
                            <h3 class="text-success"><?= $approved; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6><i class="fas fa-times-circle me-2 text-danger"></i> Ditolak</h6>
                            <h3 class="text-danger"><?= $rejected; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php 
include '../includes/footer.php'; 
?>
</body>
</html>