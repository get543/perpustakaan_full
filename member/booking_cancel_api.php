<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify that this booking belongs to the current user and is active
$stmt = $conn->prepare("SELECT * FROM api_bookings WHERE id = ? AND user_id = ? AND status = 'active'");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die('Booking tidak ditemukan atau tidak dapat dibatalkan.');
}

// Update booking status to cancelled
$updateStmt = $conn->prepare("UPDATE api_bookings SET status = 'cancelled' WHERE id = ?");
$updateStmt->bind_param('i', $booking_id);

if ($updateStmt->execute()) {
    header('Location: my_bookings_api.php?msg=cancelled');
    exit;
} else {
    die('Gagal membatalkan booking: ' . $conn->error);
}
?>