<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$result = $conn->query("SELECT * FROM cars ORDER BY name ASC");
$user_name = htmlspecialchars($_SESSION['name'] ?? 'Pengguna');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mobil - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="main-nav">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="logo mb-0">
                Car<span class="logo-accent">Rent</span> 
                <small class="tagline d-none d-sm-inline">| Solusi Sewa Mobil</small>
            </h1>
            
            <nav class="d-flex align-items-center">
                <span class="me-3 d-none d-md-inline text-muted">Halo, <strong><?= $user_name; ?></strong></span>
                <a href="my_bookings.php" class="btn btn-outline-primary me-2"><i class="fas fa-calendar-check me-1"></i> Booking Saya</a>
                <a href="../auth/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </div>
</header>
<div class="container mt-5 mb-5">

    <h2 class="mb-4">Daftar Mobil Tersedia</h2>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            
            <?php
                $is_available = ($row['status'] == 'available' && $row['stock'] > 0);
            ?>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card car-card h-100 <?= $is_available ? '' : 'unavailable' ?>">
                    
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
                    
                    <?php if (!$is_available): ?>
                        <span class="position-absolute top-0 start-50 translate-middle badge badge-unavailable fs-6 p-2">
                            TIDAK TERSEDIA
                        </span>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="card-text text-muted mb-2">
                            <i class="fas fa-tag"></i> Merek: <?= htmlspecialchars($row['brand']) ?>
                        </p>
                        <p class="card-text">
                            <i class="fas fa-rupiah-sign"></i> Harga: 
                            <strong>Rp <?= number_format($row['price_per_day'], 0, ',', '.') ?>/hari</strong>
                        </p>
                        
                        <div class="mt-auto pt-3 border-top"> 
                            <?php if ($is_available): ?>
                                <a href="book.php?car_id=<?= $row['id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-handshake"></i> Sewa Sekarang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-car-crash"></i> Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
</div>

<?php 
include '../includes/footer.php'; 
?> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>