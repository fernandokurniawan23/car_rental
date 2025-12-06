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
    <title>Login - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h4 class="text-center mb-3">Login</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="mb-3">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar</a></p>
            </form>
        </div>
    </div>
</div>

<script>
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
include '../includes/footer.php'; 
?>