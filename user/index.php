<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$result = $conn->query("SELECT * FROM cars ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Mobil - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card.unavailable {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Mobil Kami</h2>
        <div>
            <a href="my_bookings.php" class="btn btn-outline-primary me-2">📘 Booking Saya</a>
            <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            
            <?php
                $is_available = ($row['status'] == 'available' && $row['stock'] > 0);
            ?>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm <?= $is_available ? '' : 'unavailable' ?>">
                    
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                    
                    <?php if (!$is_available): ?>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger fs-6">
                            Tidak Tersedia
                        </span>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="card-text">
                            Merek: <?= htmlspecialchars($row['brand']) ?><br>
                            Harga: <strong>Rp <?= number_format($row['price_per_day'], 0, ',', '.') ?>/hari</strong>
                        </p>
                        
                        <div class="mt-auto"> <?php if ($is_available): ?>
                                <a href="book.php?car_id=<?= $row['id'] ?>" class="btn btn-primary w-100">
                                    Sewa Sekarang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    Stok Habis / Tidak Tersedia
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php 
    include '../includes/footer.php';
    ?>
</div>

</body>
</html>