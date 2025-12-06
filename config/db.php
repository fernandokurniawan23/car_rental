<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'car_rental';

// Buat koneksi MySQLi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
