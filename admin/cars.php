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
    <title>Kelola Mobil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <h2>Kelola Mobil</h2>
    <a href="add.php" class="btn btn-primary mb-3">+ Tambah Mobil</a>
    <a href="index.php" class="btn btn-secondary mb-3">← Kembali</a>

    <table class="table table-bordered">
        <thead>
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
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td>
                    <?php if ($row['image']) : ?>
                        <img src="../uploads/<?= $row['image']; ?>" width="80">
                    <?php else : ?>
                        <span class="text-muted">Tidak ada</span>
                    <?php endif; ?>
                </td>
                <td>Rp<?= number_format($row['price_per_day']); ?></td>
                <td><?= $row['stock']; ?></td>
                <td><?= ucfirst($row['status']); ?></td>
                <td>
                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus mobil ini?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
