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

function getMonthName($month_number) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[(int)$month_number] ?? 'Bulan Tidak Valid';
}

$month_options = [];
for ($i = 1; $i <= 12; $i++) {
    $month_options[$i] = getMonthName($i);
}

$user_name = htmlspecialchars($_SESSION['name'] ?? 'Pengguna');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Saya - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<header class="main-nav">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="logo mb-0">
                Car<span class="logo-accent">Rent</span> 
                <small class="tagline d-none d-sm-inline">| Solusi Sewa Mobil</small>
            </h1>
            
            <nav>
                <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Daftar Mobil</a>
            </nav>
        </div>
    </div>
</header>
<div class="container mt-5 mb-5">
    
    <h2 class="mb-4 text-center"><i class="fas fa-calendar-alt me-2"></i> Booking Saya</h2>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'canceled'): ?>
        <div class="alert alert-warning d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i> Booking berhasil dibatalkan.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i> Booking Anda berhasil dibuat dan sedang menunggu persetujuan admin.</div>
    <?php endif; ?>

    <div class="card shadow-lg p-3">
        <div class="card-body">

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th><i class="fas fa-car me-1"></i> Mobil</th>
                    <th><i class="fas fa-calendar-day me-1"></i> Tanggal Sewa</th>
                    <th><i class="fas fa-money-bill-wave me-1"></i> Total Harga</th>
                    <th><i class="fas fa-info-circle me-1"></i> Status</th>
                    <th><i class="fas fa-wrench me-1"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../uploads/<?= htmlspecialchars($row['image']); ?>" width="80" height="50" class="me-3 img-thumbnail" style="object-fit:cover;">
                            <div>
                                <strong><?= htmlspecialchars($row['car_name']); ?></strong><br>
                                <small class="text-secondary"><?= htmlspecialchars($row['brand']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <i class="fas fa-calendar-check text-primary"></i> <?= date('d M Y', strtotime($row['start_date'])); ?> 
                        <i class="fas fa-arrow-right mx-1 text-muted"></i> 
                        <?= date('d M Y', strtotime($row['end_date'])); ?>
                    </td>
                    <td class="fw-bold text-success">Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark p-2"><i class="fas fa-clock"></i> Menunggu</span>
                        <?php elseif ($row['status'] == 'approved'): ?>
                            <span class="badge bg-success p-2"><i class="fas fa-check"></i> Disetujui</span>
                        <?php elseif ($row['status'] == 'completed'): ?>
                            <span class="badge bg-secondary p-2"><i class="fas fa-flag-checkered"></i> Selesai</span>
                        <?php elseif ($row['status'] == 'rejected'): ?>
                            <span class="badge bg-danger p-2"><i class="fas fa-times-circle"></i> Dibatalkan</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="?cancel_id=<?= $row['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                                <i class="fas fa-trash-alt"></i> <span class="d-none d-md-inline">Batalkan</span>
                            </a>
                        <?php else: ?>
                            <em class="text-muted"><i class="fas fa-ban"></i></em>
                        <?php endif; ?>
                    </td>
                </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            <i class="fas fa-info-circle me-2"></i> Anda belum memiliki riwayat booking.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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