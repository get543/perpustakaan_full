<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$book = $conn->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();
if (!$book) {
  die('Buku tidak ditemukan');
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <?php if ($book['cover']): ?>
        <img src="../assets/uploads/<?= esc($book['cover']) ?>" class="book-cover-large">
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-8">
    <h3><?= esc($book['title']) ?></h3>
    <p class="text-muted">oleh <?= esc($book['author']) ?></p>
    <p>Kategori: <?= esc($book['category']) ?></p>
    <p>Tahun: <?= esc($book['year']) ?></p>
    <p>Stok tersedia: <strong><?= esc($book['stock']) ?></strong></p>

    <?php if ($book['stock'] > 0): ?>
      <a href="booking_create.php?book_id=<?= $book['id'] ?>" class="btn btn-dark">
        Booking Buku Ini
      </a>
      <p class="small text-muted mt-2">
        Booking berlaku selama 2 hari. Ambil buku di perpustakaan sebelum kadaluarsa.
      </p>
    <?php else: ?>
      <div class="alert alert-warning">Stok sedang habis.</div>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>