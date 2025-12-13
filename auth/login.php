<?php
ob_start();
session_start();
include '../config/db.php';

$error = '';

if (isset($_POST['login'])) {
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Prepare
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        // 2. Bind
        $stmt->bind_param("s", $email);
        
        // 3. Execute
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $error = "Email atau password salah.";
        } else {
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php");
                    exit();
                } else {
                    header("Location: ../user/index.php");
                    exit();
                }
            } else {
                $error = "Email atau password salah.";
            }
        }
        $stmt->close();
    } else {
        $error = "Terjadi kesalahan pada server. Silakan coba lagi.";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card mx-auto" style="max-width: 420px;">
        <div class="login-header">
            <h4><i class="fas fa-lock me-2"></i> Selamat Datang Kembali!</h4>
        </div>

        <div class="card-body p-4">
            <h5 class="text-center mb-4 text-secondary">Akses Akun Anda</h5>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i> Registrasi berhasil! Silakan login.</div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan Email Anda" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Tampilkan Password">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100 mb-3 fs-5 py-2">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </button>
                <p class="mt-3 text-center">
                    Belum punya akun? <a href="register.php" class="text-primary fw-bold">Daftar Sekarang</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// Logika Toggle Password
const toggleButton = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const icon = document.getElementById('toggleIcon');

toggleButton.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    if (type === 'password') {
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>

<?php 
// include '../includes/footer.php'; 
?>
</body>
</html>