<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];

// Fetch loans from database (now contains OpenLibrary book_id)
$loans = $conn->query("
  SELECT * FROM api_loans
  WHERE user_id = $user_id
  ORDER BY created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';
?>

<h3 class="mb-3">Peminjaman Saya</h3>

<?php if ($loans->num_rows > 0): ?>
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Cover</th>
              <th>Buku</th>
              <th>Penulis</th>
              <th>Tgl Pinjam</th>
              <th>Jatuh Tempo</th>
              <th>Tgl Kembali</th>
              <th>Status</th>
              <th>Denda</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            while ($l = $loans->fetch_assoc()):
              // Auto-calculate fine for overdue loans
              $today = date('Y-m-d');
              if ($l['status'] == 'borrowed' && $l['due_date'] < $today) {
                $days_overdue = floor((strtotime($today) - strtotime($l['due_date'])) / 86400);
                $fine_per_day = 1000; // Rp 1.000 per hari
                $calculated_fine = $days_overdue * $fine_per_day;

                // Update fine in database
                $updateFine = $conn->prepare("UPDATE loans SET fine = ? WHERE id = ?");
                $updateFine->bind_param('ii', $calculated_fine, $l['id']);
                $updateFine->execute();
                $l['fine'] = $calculated_fine;
              }

              // Determine badge color based on status
              $badgeClass = '';
              $statusText = '';
              switch ($l['status']) {
                case 'borrowed':
                  if ($l['due_date'] < $today) {
                    $badgeClass = 'bg-danger';
                    $statusText = 'Terlambat';
                  } else {
                    $badgeClass = 'bg-warning text-dark';
                    $statusText = 'Dipinjam';
                  }
                  break;
                case 'returned':
                  $badgeClass = 'bg-success';
                  $statusText = 'Dikembalikan';
                  break;
                default:
                  $badgeClass = 'bg-secondary';
                  $statusText = ucfirst($l['status']);
              }
              ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>
                  <?php if ($l['book_cover']): ?>
                    <img src="<?= esc($l['book_cover']) ?>" alt="Cover" style="width: 40px; height: 60px; object-fit: cover;"
                      class="rounded">
                  <?php else: ?>
                    <img src="../assets/uploads/no_cover.png" alt="No Cover"
                      style="width: 40px; height: 60px; object-fit: cover;" class="rounded">
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= esc($l['book_title']) ?></strong>
                  <br>
                  <a href="book_detail_api.php?id=<?= urlencode($l['book_id']) ?>" class="small text-decoration-none">
                    Lihat Detail
                  </a>
                </td>
                <td class="small"><?= esc($l['book_author']) ?></td>
                <td><?= date('d/m/Y', strtotime($l['loan_date'])) ?></td>
                <td>
                  <?= date('d/m/Y', strtotime($l['due_date'])) ?>
                  <?php if ($l['status'] == 'borrowed'): ?>
                    <br>
                    <small class="<?= ($l['due_date'] < $today) ? 'text-danger' : 'text-muted' ?>">
                      <?php
                      $days_diff = floor((strtotime($l['due_date']) - strtotime($today)) / 86400);
                      if ($days_diff > 0) {
                        echo "($days_diff hari lagi)";
                      } elseif ($days_diff == 0) {
                        echo "(Hari ini!)";
                      } else {
                        echo "(" . abs($days_diff) . " hari terlambat)";
                      }
                      ?>
                    </small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($l['return_date']): ?>
                    <?= date('d/m/Y', strtotime($l['return_date'])) ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $badgeClass ?>">
                    <?= $statusText ?>
                  </span>
                </td>
                <td>
                  <?php if ($l['fine'] > 0): ?>
                    <span class="text-danger fw-bold">
                      Rp <?= number_format($l['fine'], 0, ',', '.') ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Summary Section -->
      <?php
      $loans->data_seek(0); // Reset pointer
      $total_borrowed = 0;
      $total_returned = 0;
      $total_fine = 0;

      while ($l = $loans->fetch_assoc()) {
        if ($l['status'] == 'borrowed')
          $total_borrowed++;
        if ($l['status'] == 'returned')
          $total_returned++;
        $total_fine += $l['fine'];
      }
      ?>

      <div class="mt-3 p-3 bg-light rounded">
        <div class="row text-center">
          <div class="col-md-4">
            <h5 class="text-warning"><?= $total_borrowed ?></h5>
            <small class="text-muted">Sedang Dipinjam</small>
          </div>
          <div class="col-md-4">
            <h5 class="text-success"><?= $total_returned ?></h5>
            <small class="text-muted">Sudah Dikembalikan</small>
          </div>
          <div class="col-md-4">
            <h5 class="text-danger">Rp <?= number_format($total_fine, 0, ',', '.') ?></h5>
            <small class="text-muted">Total Denda</small>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <div class="alert alert-info">
    <p class="mb-0">Anda belum memiliki riwayat peminjaman.</p>
    <a href="catalog_api.php" class="alert-link">Lihat katalog buku</a>
  </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>