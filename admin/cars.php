<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil semua data mobil
$result = $conn->query("SELECT * FROM cars ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mobil</title>
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
            <a href="cars.php" class="list-group-item list-group-item-action active">
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
            <nav class="navbar navbar-light bg-white border-bottom mb-4 rounded shadow-sm p-3">
                <h4 class="mb-0"><i class="fas fa-car me-2"></i> Kelola Mobil</h4>
                <div class="ms-auto">
                    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Mobil</a>
                </div>
            </nav>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Mobil</th>
                                    <th>Gambar</th>
                                    <th>Harga/Hari</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['image']) : ?>
                                            <img src="../uploads/<?= $row['image']; ?>" width="80" class="img-thumbnail">
                                        <?php else : ?>
                                            <span class="text-muted"><i class="fas fa-image"></i> Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rp <?= number_format($row['price_per_day'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['stock'] > 5 ? 'success' : ($row['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                            <?= $row['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                            <?= ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm me-1"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus mobil ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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