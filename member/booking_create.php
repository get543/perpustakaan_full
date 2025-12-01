<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];
$book_id = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;

$book = $conn->query("SELECT * FROM books WHERE id=$book_id")->fetch_assoc();
if (!$book || $book['stock'] <= 0) {
    die('Buku tidak tersedia untuk booking.');
}

$booking_date = date('Y-m-d');
$expire_date = date('Y-m-d', strtotime('+2 days'));

$stmt = $conn->prepare("INSERT INTO bookings (user_id, book_id, booking_date, expire_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiss', $user_id, $book_id, $booking_date, $expire_date);
$stmt->execute();

header('Location: my_bookings.php');
exit;
