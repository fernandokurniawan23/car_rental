<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['message'] = "Permintaan tidak valid.";
    $_SESSION['message_type'] = "danger";
    header("Location: bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$action = $_GET['action']; 
$stmt_booking = $conn->prepare("SELECT car_id, status FROM bookings WHERE id = ?");
$stmt_booking->bind_param("i", $booking_id);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();

if ($result_booking->num_rows === 0) {
    $_SESSION['message'] = "Booking tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: bookings.php");
    exit();
}

$booking = $result_booking->fetch_assoc();
$car_id = $booking['car_id'];
$current_status = $booking['status'];

$conn->begin_transaction();

try {
    if ($action == 'approve') {
        // --- AKSI: APPROVE ---
        if ($current_status !== 'pending') {
            throw new Exception("Hanya booking 'pending' yang bisa disetujui.");
        }

        $stmt_car = $conn->prepare("SELECT stock, status FROM cars WHERE id = ? FOR UPDATE");
        $stmt_car->bind_param("i", $car_id);
        $stmt_car->execute();
        $car = $stmt_car->get_result()->fetch_assoc();

        if (!$car) {
            throw new Exception("Mobil tidak ditemukan.");
        }
        if ($car['stock'] <= 0) {
            throw new Exception("Stok mobil habis! Tidak dapat menyetujui booking.");
        }

        $new_stock = $car['stock'] - 1;
        $new_status = ($new_stock == 0) ? 'unavailable' : 'available';

        $stmt_update_car = $conn->prepare("UPDATE cars SET stock = ?, status = ? WHERE id = ?");
        $stmt_update_car->bind_param("isi", $new_stock, $new_status, $car_id);
        $stmt_update_car->execute();

        $stmt_update_booking = $conn->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
        $stmt_update_booking->bind_param("i", $booking_id);
        $stmt_update_booking->execute();

        $_SESSION['message'] = "Booking disetujui. Stok mobil dikurangi.";
        $_SESSION['message_type'] = "success";

    } elseif ($action == 'reject') {
        // --- AKSI: REJECT ---
        if ($current_status !== 'pending') {
            throw new Exception("Hanya booking 'pending' yang bisa ditolak.");
        }
        $stmt_update_booking = $conn->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
        $stmt_update_booking->bind_param("i", $booking_id);
        $stmt_update_booking->execute();
        
        $_SESSION['message'] = "Booking telah ditolak.";
        $_SESSION['message_type'] = "success";

    } elseif ($action == 'complete') {
        if ($current_status !== 'approved') {
            throw new Exception("Hanya booking 'approved' yang bisa ditandai selesai.");
        }

        $stmt_return_car = $conn->prepare("UPDATE cars SET stock = stock + 1, status = 'available' WHERE id = ?");
        $stmt_return_car->bind_param("i", $car_id);
        $stmt_return_car->execute();

        $stmt_update_booking = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
        $stmt_update_booking->bind_param("i", $booking_id);
        $stmt_update_booking->execute();
        
        $_SESSION['message'] = "Booking ditandai selesai. Stok mobil telah dikembalikan.";
        $_SESSION['message_type'] = "success";

    } else {
        throw new Exception("Aksi tidak dikenal.");
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

$stmt_booking->close();
if (isset($stmt_car)) $stmt_car->close();
if (isset($stmt_update_car)) $stmt_update_car->close();
if (isset($stmt_update_booking)) $stmt_update_booking->close();
if (isset($stmt_return_car)) $stmt_return_car->close();
$conn->close();

header("Location: bookings.php");
exit();
?>