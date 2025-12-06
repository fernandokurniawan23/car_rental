<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "car_rental";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi ke database BERHASIL!";
}
?>
