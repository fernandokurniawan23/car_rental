<?php
require_once __DIR__ . '/db.php';

$name = 'Admin';
$email = 'admin@example.com';
$password_plain = '123';
$role = 'admin';

$hash = password_hash($password_plain, PASSWORD_DEFAULT);

// cek apakah email sudah ada
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Admin dengan email $email sudah terdaftar.";
    exit;
}

$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $email, $hash, $role]);

echo "Admin dibuat: $email dengan password: $password_plain. hapus file setelah digunakan.";
