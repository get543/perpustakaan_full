<!-- // !INI BLM ADA YG DIGANTI -->


<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];

$loans = $conn->query("
    SELECT l.*, b.title AS book_title
    FROM loans l
    JOIN books b ON b.id = l.book_id
    WHERE l.user_id = $user_id
    ORDER BY l.created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';

?>

<h3 class="mb-3">Peminjaman Saya</h3>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Buku</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Tgl Kembali</th>
            <th>Status</th>
            <th>Denda</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          while ($l = $loans->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($l['book_title']) ?></td>
              <td><?= esc($l['loan_date']) ?></td>
              <td><?= esc($l['due_date']) ?></td>
              <td><?= esc($l['return_date'] ?? '-') ?></td>
              <td><span class="badge bg-secondary"><?= esc($l['status']) ?></span></td>
              <td>Rp <?= number_format($l['fine'], 0, ',', '.') ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>