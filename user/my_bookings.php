<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['cancel_id'])) {
    $cancel_id = (int)$_GET['cancel_id'];
    
    $stmt_cancel = $conn->prepare("UPDATE bookings SET status='rejected' WHERE id=? AND user_id=? AND status='pending'");
    $stmt_cancel->bind_param("ii", $cancel_id, $user_id);
    $stmt_cancel->execute();
    
    header("Location: my_bookings.php?msg=canceled");
    exit();
}

$stmt_select = $conn->prepare("
    SELECT b.*, c.name AS car_name, c.brand, c.image
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt_select->bind_param("i", $user_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking Saya - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Booking Saya</h2>
        <a href="index.php" class="btn btn-secondary">← Kembali ke Daftar Mobil</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'canceled'): ?>
        <div class="alert alert-warning">Booking berhasil dibatalkan.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Booking Anda berhasil dibuat dan sedang menunggu persetujuan admin.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Mobil</th>
                    <th>Tanggal</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../uploads/<?= htmlspecialchars($row['image']); ?>" width="60" height="40" class="me-2" style="object-fit:cover;">
                            <div>
                                <strong><?= htmlspecialchars($row['car_name']); ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['brand']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= $row['start_date']; ?> → <?= $row['end_date']; ?></td>
                    <td>Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                        <?php elseif ($row['status'] == 'approved'): ?>
                            <span class="badge bg-success">Disetujui</span>
                        <?php elseif ($row['status'] == 'completed'): ?>
                            <span class="badge bg-secondary">Selesai</span>
                        <?php elseif ($row['status'] == 'rejected'): ?>
                            <span class="badge bg-danger">Dibatalkan</span>
                        <?php endif; ?>
                        </td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="?cancel_id=<?= $row['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin membatalkan booking ini?')">Batalkan</a>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>