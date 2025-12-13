<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$result = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link rel="stylesheet" href="../css/admin.css"> 
</head>
<body>

<div id="wrapper">

    <div id="sidebar-wrapper">
        <div class="sidebar-heading">ADMIN DASHBOARD</div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="cars.php" class="list-group-item list-group-item-action">
                <i class="fas fa-car me-2"></i> Kelola Mobil
            </a>
            <a href="bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check me-2"></i> Kelola Booking
            </a>
            <a href="users.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-users me-2"></i> Kelola Pengguna
            </a>
            <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <nav class="navbar navbar-light bg-white border-bottom mb-4 rounded shadow-sm p-3">
                <h4 class="mb-0"><i class="fas fa-users me-2"></i> Kelola Pengguna</h4>
                <div class="ms-auto">
                    </div>
            </nav>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Terdaftar Sejak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php $i = 1; ?>
                                    <?php while ($user = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td>
                                            <i class="fas fa-user me-2"></i> 
                                            <strong><?= htmlspecialchars($user['name']); ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($user['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait (booking) mungkin akan terpengaruh.');">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada data pengguna yang terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>