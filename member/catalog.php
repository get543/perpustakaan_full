<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$keyword = $_GET['q'] ?? '';

if ($keyword) {
  $k = '%' . $conn->real_escape_string($keyword) . '%';
  $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ?");
  $stmt->bind_param('ss', $k, $k);
  $stmt->execute();
  $books = $stmt->get_result();
} else {
  $books = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<h3 class="mb-3">Katalog Buku</h3>

<form class="mb-3" method="get">
  <div class="input-group">
    <input type="text" name="q" class="form-control" placeholder="Cari judul atau penulis..."
      value="<?= esc($keyword) ?>">
    <button class="btn btn-secondary" type="submit">Cari</button>
  </div>
</form>

<div class="row g-3">
  <?php while ($b = $books->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card shadow-lg h-100 border-0">
        <?php if ($b['cover']): ?>
          <img src="../assets/uploads/<?= esc($b['cover']) ?>" class="book-cover mx-auto d-block">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
          <h6 class="card-title"><?= esc($b['title']) ?></h6>
          <p class="text-muted small mb-1"><?= esc($b['author']) ?></p>
          <p class="small mb-2">Stok: <?= esc($b['stock']) ?></p>
          <a href="book_detail.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-secondary mt-auto">Detail</a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>