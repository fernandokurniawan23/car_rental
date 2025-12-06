<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); 
    exit();
}

if (isset($_POST['submit'])) {
    
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $price_per_day = (float) $_POST['price_per_day'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $stock = (int) $_POST['stock'];
    // --------------------

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image = basename($_FILES["image"]["name"]); 
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    $stmt = $conn->prepare("INSERT INTO cars (name, brand, price_per_day, image, description, status, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssdsssi", $name, $brand, $price_per_day, $image, $description, $status, $stock);
    $stmt->execute();

    header("Location: cars.php?success=1"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mobil - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>Tambah Mobil</h2>
            <a href="cars.php" class="btn btn-secondary mb-3">← Kembali</a>

            <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
                <div class="mb-3">
                    <label class="form-label">Nama Mobil</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Merek</label>
                    <input type="text" name="brand" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga Sewa per Hari (Rp)</label>
                    <input type="number" step="1000" name="price_per_day" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Mobil</label>
                    <input type="file" name="image" class="form-control">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Stok</label>
                        <input type="number" name="stock" class="form-control" value="1" min="0" required>
                        <small class="form-text text-muted">Atur jumlah unit yang tersedia.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="available">Tersedia</option>
                            <option value="unavailable">Tidak Tersedia</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>