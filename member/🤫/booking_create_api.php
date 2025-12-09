<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];
$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : ''; // OpenLibrary ID (e.g., OL45804W)
$book_title = isset($_GET['title']) ? $_GET['title'] : '';

if (!$book_id) {
    die('ID buku tidak valid.');
}

// Fetch book details from OpenLibrary API to verify
$url = "https://openlibrary.org/works/" . urlencode($book_id) . ".json";
$bookData = @file_get_contents($url);

if ($bookData === false) {
    die('Buku tidak ditemukan di OpenLibrary.');
}

$book = json_decode($bookData, true);

// Get author names
$authorNames = [];
if (isset($book['authors']) && is_array($book['authors'])) {
    foreach ($book['authors'] as $author) {
        if (isset($author['author']['key'])) {
            $authorKey = $author['author']['key'];
            $authorUrl = "https://openlibrary.org" . $authorKey . ".json";
            $authorData = @file_get_contents($authorUrl);
            if ($authorData) {
                $authorInfo = json_decode($authorData, true);
                $authorNames[] = $authorInfo['name'] ?? 'Unknown';
            }
        }
    }
}
$authorString = implode(', ', $authorNames) ?: 'Unknown Author';

// Get cover ID
$coverId = null;
if (isset($book['covers']) && is_array($book['covers']) && count($book['covers']) > 0) {
    $coverId = $book['covers'][0];
}
$coverUrl = $coverId ? "https://covers.openlibrary.org/b/id/$coverId-S.jpg" : null;

// Set booking dates
$booking_date = date('Y-m-d');
$expire_date = date('Y-m-d', strtotime('+2 days'));

// Check if user already has an active booking for this book
$checkStmt = $conn->prepare("SELECT id FROM api_bookings WHERE user_id = ? AND book_id = ? AND status = 'active'");
$checkStmt->bind_param('is', $user_id, $book_id);
$checkStmt->execute();
$existingBooking = $checkStmt->get_result()->fetch_assoc();

if ($existingBooking) {
    die('Anda sudah memiliki booking aktif untuk buku ini.');
}

// Insert booking into database
$stmt = $conn->prepare("INSERT INTO api_bookings (user_id, book_id, book_title, book_author, book_cover, booking_date, expire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
$stmt->bind_param('issssss', $user_id, $book_id, $book['title'], $authorString, $coverUrl, $booking_date, $expire_date);

if ($stmt->execute()) {
    // Redirect to my bookings page
    header('Location: my_bookings_api.php');
    exit;
} else {
    die('Gagal melakukan booking: ' . $conn->error);
}
?>