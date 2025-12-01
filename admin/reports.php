<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_admin();

include '../includes/header.php';
include '../includes/navbar.php';


// Statistik
$total_books = $conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'];
$total_members = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='Member'")->fetch_assoc()['c'];
$total_librarians = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='Petugas'")->fetch_assoc()['c'];

$loan_this_month = $conn->query("
    SELECT COUNT(*) AS c 
    FROM loans 
    WHERE MONTH(loan_date)=MONTH(NOW()) AND YEAR(loan_date)=YEAR(NOW())
")->fetch_assoc()['c'];

$booking_this_month = $conn->query("
    SELECT COUNT(*) AS c 
    FROM bookings
    WHERE MONTH(booking_date)=MONTH(NOW()) AND YEAR(booking_date)=YEAR(NOW())
")->fetch_assoc()['c'];

$late_loans = $conn->query("SELECT COUNT(*) AS c FROM loans WHERE status='late'")->fetch_assoc()['c'];
$fines_total = $conn->query("SELECT SUM(fine) AS total FROM loans")->fetch_assoc()['total'] ?? 0;
?>

<h3 class="mb-4">Laporan Statistik</h3>

<div class="row gy-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Total Buku</h6>
                <h2><?= $total_books ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Total Member</h6>
                <h2><?= $total_members ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Total Librarian</h6>
                <h2><?= $total_librarians ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Peminjaman Bulan Ini</h6>
                <h2><?= $loan_this_month ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Booking Bulan Ini</h6>
                <h2><?= $booking_this_month ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Peminjaman Telat</h6>
                <h2><?= $late_loans ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Total Denda Terkumpul</h6>
                <h2>Rp <?= number_format($fines_total, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>