<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: cars.php"); 
    exit();
}

$id = (int) $_GET['id'];

$stmt_get = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$car = $result->fetch_assoc();
// -------------------------

if (!$car) {
    header("Location: cars.php");
    exit();
}

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $price_per_day = (float) $_POST['price_per_day'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    $stock = (int) $_POST['stock'];
    // --------------------

    $image = $car['image'];
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    $stmt = $conn->prepare("UPDATE cars SET name=?, brand=?, price_per_day=?, image=?, description=?, status=?, stock=? WHERE id=?");
    
    $stmt->bind_param("ssdsssii", $name, $brand, $price_per_day, $image, $description, $status, $stock, $id);
    $stmt->execute();

    header("Location: cars.php?updated=1"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mobil - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>Edit Mobil</h2>
            <a href="cars.php" class="btn btn-secondary mb-3">← Kembali</a>

            <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
                <div class="mb-3">
                    <label class="form-label">Nama Mobil</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($car['name']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Merek</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga Sewa per Hari (Rp)</label>
                    <input type="number" step="1000" name="price_per_day" value="<?= $car['price_per_day'] ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($car['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label><br>
                    <?php if ($car['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($car['image']) ?>" width="120" class="mb-2 img-thumbnail">
                    <?php else: ?>
                        <small class="text-muted">Belum ada gambar</small>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control mt-2">
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jumlah Stok</label>
                        <input type="number" name="stock" class="form-control" value="<?= (int)$car['stock'] ?>" min="0" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= $car['status'] == 'available' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="unavailable" <?= $car['status'] == 'unavailable' ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="update" class="btn btn-primary w-100">Update</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>