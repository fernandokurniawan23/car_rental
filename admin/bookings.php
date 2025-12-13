<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

# 1. TANGANI INPUT FILTER
$filter_month = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null;
$filter_year = isset($_GET['year']) && $_GET['year'] !== '' ? $_GET['year'] : null;

$where_clause = "";
$bind_params = [];
$bind_types = "";
$filter_applied = false;
$display_period = "Semua Booking Terbaru";

if ($filter_month !== null && $filter_year !== null) {
    $where_clause = " WHERE MONTH(b.start_date) = ? AND YEAR(b.start_date) = ?";
    $bind_params[] = (int)$filter_month;
    $bind_params[] = (int)$filter_year;
    $bind_types = "ii";
    $filter_applied = true;
    
    $display_period = getMonthName($filter_month) . " " . $filter_year; 
}

# 2. LOGIKA REKAPAN BULANAN (Kode SQL DIBERSIHKAN)
$rekap_query = $conn->query("
SELECT
    YEAR(created_at) AS year,
    MONTH(created_at) AS month,
    COUNT(id) AS total_bookings,
    SUM(total_price) AS total_revenue
FROM
    bookings
WHERE
    status IN ('approved', 'completed')
GROUP BY
    year, month
ORDER BY
    year DESC, month DESC
LIMIT 12
");

# 3. LOGIKA DAFTAR BOOKING LENGKAP (Kode SQL DIBERSIHKAN)
$sql = "
SELECT b.*, u.name AS user_name, c.name AS car_name
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN cars c ON b.car_id = c.id
" . $where_clause . " 
ORDER BY b.start_date DESC
";

$stmt = $conn->prepare($sql);

if ($filter_applied) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();


# FUNGSI HELPER
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

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Booking - Admin</title>
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
            <a href="bookings.php" class="list-group-item list-group-item-action active">
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
                <button class="sidebar-toggle-btn me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Manajemen Booking</h4>
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
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white"> <h5 class="mb-0"><i class="fas fa-chart-line"></i> Rekap Penjualan Bulanan (Disetujui/Selesai)</h5>
                </div>
                <div class="card-body">
                    <?php if ($rekap_query->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan & Tahun</th>
                                    <th>Jumlah Booking</th>
                                    <th>Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($rekap = $rekap_query->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= getMonthName($rekap['month']) . " " . $rekap['year']; ?></strong></td>
                                    <td><span class="badge bg-primary rounded-pill"><?= $rekap['total_bookings']; ?></span> Booking</td>
                                    <td>**Rp <?= number_format($rekap['total_revenue'], 0, ',', '.'); ?>**</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Belum ada data booking yang disetujui atau selesai untuk dibuat rekapan.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <h4 class="mt-4"><i class="fas fa-filter"></i> Filter Daftar Booking</h4>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="bookings.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Pilih Bulan</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">-- Tampilkan Semua Bulan --</option>
                                <?php foreach ($month_options as $num => $name): ?>
                                    <option value="<?= $num; ?>" <?= $filter_month == $num ? 'selected' : ''; ?>>
                                        <?= $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Tahun</label>
                            <input type="number" name="year" id="year" class="form-control" placeholder="Cth: 2024" value="<?= htmlspecialchars($filter_year); ?>">
                        </div>
                        <div class="col-md-5">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> Tampilkan Hasil Filter</button>
                            <a href="bookings.php" class="btn btn-warning"><i class="fas fa-sync"></i> Reset Filter</a>
                        </div>
                    </form>
                </div>
            </div>

            <h3><i class="fas fa-list-alt"></i> Daftar Booking: <?= $display_period; ?></h3>
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama User</th>
                                    <th>Mobil</th>
                                    <th>Tanggal Sewa</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id']; ?></td>
                                        <td><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($row['user_name']); ?></td>
                                        <td><i class="fas fa-car me-1"></i> <?= htmlspecialchars($row['car_name']); ?></td>
                                        <td>
                                            <small class="text-muted d-block d-md-inline">
                                                <?= date('d/m/Y', strtotime($row['start_date'])); ?> 
                                                <i class="fas fa-long-arrow-alt-right d-none d-md-inline"></i> 
                                                <?= date('d/m/Y', strtotime($row['end_date'])); ?>
                                            </small>
                                        </td>
                                        <td>**Rp <?= number_format($row['total_price'], 0, ',', '.'); ?>**</td>
                                        <td>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                                            <?php elseif ($row['status'] == 'approved'): ?>
                                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Disetujui</span>
                                            <?php elseif ($row['status'] == 'completed'): ?>
                                                <span class="badge bg-secondary"><i class="fas fa-flag-checkered"></i> Selesai</span>
                                            <?php elseif ($row['status'] == 'rejected'): ?>
                                                <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="update_booking_status.php?id=<?= $row['id']; ?>&action=approve" class="btn btn-success btn-sm mb-1" onclick="return confirm('Setujui booking ini? Stok akan dikurangi.');">
                                                    <i class="fas fa-thumbs-up"></i> <span class="d-none d-md-inline">Approve</span>
                                                </a>
                                                <a href="update_booking_status.php?id=<?= $row['id']; ?>&action=reject" class="btn btn-danger btn-sm" onclick="return confirm('Tolak booking ini?');">
                                                    <i class="fas fa-times"></i> <span class="d-none d-md-inline">Reject</span>
                                                </a>
                                            <?php elseif ($row['status'] == 'approved'): ?>
                                                <a href="update_booking_status.php?id=<?= $row['id']; ?>&action=complete" class="btn btn-primary btn-sm" onclick="return confirm('Tandai booking ini selesai? Stok akan dikembalikan.');">
                                                    <i class="fas fa-check"></i> <span class="d-none d-md-inline">Selesai</span>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Aksi Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data booking.</td>
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
<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('wrapper').classList.toggle('toggled');
    });
</script>
</body>
</html>