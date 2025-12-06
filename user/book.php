<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['car_id'])) {
    header("Location: index.php");
    exit();
}

$car_id = (int)$_GET['car_id'];

$stmt_car = $conn->prepare("SELECT * FROM cars WHERE id = ? AND status = 'available' AND stock > 0");
$stmt_car->bind_param("i", $car_id);
$stmt_car->execute();
$result_car = $stmt_car->get_result();
$car = $result_car->fetch_assoc();

if (isset($_POST['submit'])) {
    
    if (!$car) {
        $error = "Mobil ini sudah tidak tersedia.";
    } else {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $stmt_check = $conn->prepare("SELECT stock, status, price_per_day FROM cars WHERE id = ?");
        $stmt_check->bind_param("i", $car_id);
        $stmt_check->execute();
        $car_fresh = $stmt_check->get_result()->fetch_assoc();

        if (!$car_fresh || $car_fresh['stock'] <= 0 || $car_fresh['status'] !== 'available') {
            $error = "Maaf, mobil ini baru saja habis dipesan atau tidak tersedia. Silakan pilih mobil lain.";
        } else {
            $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
            if ($days <= 0) {
                $error = "Tanggal kembali harus setelah tanggal mulai.";
            } else {
                $total_price = $days * $car_fresh['price_per_day'];
                $user_id = $_SESSION['user_id'];

                $stmt_insert = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("iissd", $user_id, $car_id, $start_date, $end_date, $total_price);
                
                if ($stmt_insert->execute()) {
                    header("Location: my_bookings.php?success=1");
                    exit();
                } else {
                    $error = "Terjadi kesalahan saat menyimpan booking Anda.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sewa Mobil - <?= htmlspecialchars($car['name'] ?? 'Tidak Tersedia') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if ($car): ?>
                        <h3 class="mb-3">Sewa Mobil: <?= htmlspecialchars($car['name']) ?></h3>
                        <p>Merek: <?= htmlspecialchars($car['brand']) ?></p>
                        <p>Harga: <strong>Rp <?= number_format($car['price_per_day'], 0, ',', '.') ?>/hari</strong></p>
                        <hr>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required min="<?= date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Tanggal Kembali</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <button type="submit" name="submit" class="btn btn-success w-100 btn-lg">Konfirmasi Sewa</button>
                        </form>
                    
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <h4>Mobil Tidak Tersedia</h4>
                            <p class="mb-0">Maaf, mobil yang Anda pilih tidak ditemukan atau stoknya telah habis.</p>
                        </div>
                    <?php endif; ?>

                    <a href="index.php" class="btn btn-secondary mt-3 w-100">← Kembali ke Daftar Mobil</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>