<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_admin();

include '../includes/header.php';
include '../includes/navbar.php';

// Statistik sederhana
$total_books = $conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'] ?? 0;
$total_members = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='Member'")->fetch_assoc()['c'] ?? 0;
$total_librarians = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='Petugas'")->fetch_assoc()['c'] ?? 0;
?>
<h3 class="mb-4">Dashboard Admin</h3>

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
        <h6 class="text-muted">Total Member</h6>
        <h3><?= $total_members ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h6 class="text-muted">Total Librarian</h6>
        <h3><?= $total_librarians ?></h3>
      </div>
    </div>
  </div>
</div>

<p class="mt-4 text-muted">
  Admin dapat memonitor statistik sistem. Untuk tugas ini, fitur manajemen user bisa ditambahkan jika diperlukan.
</p>

<?php include '../includes/footer.php'; ?>