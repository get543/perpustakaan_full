<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_librarian();

include '../includes/header.php';
include '../includes/navbar.php';


$total_books = $conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'] ?? 0;
$ongoing_loans = $conn->query("SELECT COUNT(*) AS c FROM loans WHERE status='ongoing'")->fetch_assoc()['c'] ?? 0;
$pending_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
?>
<h3 class="mb-4">Dashboard Librarian</h3>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h6 class="text-muted">Total Buku</h6>
        <h3><?= $total_books ?></h3>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h6 class="text-muted">Peminjaman Aktif</h6>
        <h3><?= $ongoing_loans ?></h3>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h6 class="text-muted">Booking Pending</h6>
        <h3><?= $pending_bookings ?></h3>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>