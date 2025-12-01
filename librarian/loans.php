<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_librarian();

// Proses pengembalian
if (isset($_GET['return_id'])) {
  $id = (int) $_GET['return_id'];

  $loan = $conn->query("SELECT * FROM loans WHERE id=$id")->fetch_assoc();
  if ($loan && $loan['status'] === 'ongoing') {
    $today = date('Y-m-d');
    $fine = 0;

    if ($today > $loan['due_date']) {
      $late_days = (strtotime($today) - strtotime($loan['due_date'])) / (60 * 60 * 24);
      $fine = max(0, (int) $late_days) * 1000;
      $status = 'late';
    } else {
      $status = 'returned';
    }

    $stmt = $conn->prepare("UPDATE loans SET return_date=?, status=?, fine=? WHERE id=?");
    $stmt->bind_param('ssii', $today, $status, $fine, $id);
    $stmt->execute();

    $conn->query("UPDATE books SET stock = stock + 1 WHERE id=" . $loan['book_id']);
  }

  header('Location: loans.php');
  exit;
}

$loans = $conn->query("
    SELECT l.*, u.name AS member_name, b.title AS book_title
    FROM loans l
    JOIN users u ON u.id = l.user_id
    JOIN books b ON b.id = l.book_id
    ORDER BY l.created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';

?>

<h3 class="mb-3">Transaksi Peminjaman (Librarian)</h3>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Buku</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Tgl Kembali</th>
            <th>Status</th>
            <th>Denda</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          while ($l = $loans->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($l['member_name']) ?></td>
              <td><?= esc($l['book_title']) ?></td>
              <td><?= esc($l['loan_date']) ?></td>
              <td><?= esc($l['due_date']) ?></td>
              <td><?= esc($l['return_date'] ?? '-') ?></td>
              <td><span class="badge bg-secondary"><?= esc($l['status']) ?></span></td>
              <td>Rp <?= number_format($l['fine'], 0, ',', '.') ?></td>
              <td class="text-end">
                <?php if ($l['status'] === 'ongoing'): ?>
                  <a class="btn btn-sm btn-dark" href="?return_id=<?= $l['id'] ?>"
                    onclick="return confirm('Konfirmasi pengembalian?')">
                    Kembalikan
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>