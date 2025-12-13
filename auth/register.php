<?php
include '../config/db.php';

$error = '';

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    // Validasi
    if (empty($name) || empty($email) || empty($password_raw)) {
        $error = "Semua kolom harus diisi.";
    } elseif (strlen($password_raw) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

        // Cek email
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Email sudah terdaftar! Silakan Login.";
        } else {
            // Insert Data
            $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt_insert->bind_param("sss", $name, $email, $password_hashed);
            
            if ($stmt_insert->execute()) {
                header("Location: login.php?success=registered");
                exit;
            } else {
                $error = "Gagal mendaftar, coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card mx-auto" style="max-width: 420px;">
        <div class="login-header">
            <h4><i class="fas fa-user-plus me-2"></i> Buat Akun Baru</h4>
        </div>

        <div class="card-body p-4">
            <h5 class="text-center mb-4 text-secondary">Daftar sebagai Penyewa</h5>
            
            <?php if (!empty($error)): ?>
                <div class='alert alert-danger d-flex align-items-center'><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nama Anda" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email Aktif" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 mb-3 fs-5 py-2">
                    <i class="fas fa-user-plus me-1"></i> Daftar Akun
                </button>
                
                <p class="mt-3 text-center">
                    Sudah punya akun? <a href="login.php" class="text-primary fw-bold">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>