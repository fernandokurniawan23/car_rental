<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Logika untuk UPDATE user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($name) || empty($email)) {
        $error = "Nama dan Email wajib diisi.";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check->bind_param("si", $email, $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt_update->bind_param("sssi", $name, $email, $hashed_password, $user_id);
            } else {
                $stmt_update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $name, $email, $user_id);
            }

            if ($stmt_update->execute()) {
                $_SESSION['message'] = "Data pengguna berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header("Location: users.php");
                exit();
            } else {
                $error = "Gagal memperbarui data: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
        $stmt_check->close();
    }
}

$stmt_get = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt_get->bind_param("i", $user_id);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['message'] = "Pengguna tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: users.php");
    exit();
}
$stmt_get->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Formulir Edit Pengguna</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>
                
                    <form method="POST" action="edit_user.php?id=<?= $user['id']; ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="users.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Perbarui Pengguna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>