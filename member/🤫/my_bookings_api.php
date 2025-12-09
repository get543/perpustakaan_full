<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

// Add notifications
$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'cancelled') {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      Booking berhasil dibatalkan.
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}


$user_id = $_SESSION['user']['id'];

// Fetch bookings from database (now contains OpenLibrary book_id)
$bookings = $conn->query("
    SELECT * FROM api_bookings
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';
?>


<h3 class="mb-3">Booking Saya</h3>

<?= $message ?>

<?php if ($bookings->num_rows > 0): ?>
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
              <th>Tgl Booking</th>
              <th>Kadaluarsa</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $no = 1;
            while ($b = $bookings->fetch_assoc()): 
              // Check if booking is expired
              $today = date('Y-m-d');
              if ($b['status'] == 'active' && $b['expire_date'] < $today) {
                // Auto-update expired bookings
                $updateStmt = $conn->prepare("UPDATE bookings SET status = 'expired' WHERE id = ?");
                $updateStmt->bind_param('i', $b['id']);
                $updateStmt->execute();
                $b['status'] = 'expired';
              }
              
              // Determine badge color based on status
              $badgeClass = '';
              switch($b['status']) {
                case 'active':
                  $badgeClass = 'bg-success';
                  break;
                case 'expired':
                  $badgeClass = 'bg-danger';
                  break;
                case 'collected':
                  $badgeClass = 'bg-primary';
                  break;
                case 'cancelled':
                  $badgeClass = 'bg-secondary';
                  break;
                default:
                  $badgeClass = 'bg-secondary';
              }
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>
                  <?php if ($b['book_cover']): ?>
                    <img src="<?= esc($b['book_cover']) ?>" alt="Cover" style="width: 40px; height: 60px; object-fit: cover;" class="rounded">
                  <?php else: ?>
                    <img src="../assets/uploads/no_cover.png" alt="No Cover" style="width: 40px; height: 60px; object-fit: cover;" class="rounded">
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= esc($b['book_title']) ?></strong>
                  <br>
                  <a href="book_detail_api.php?id=<?= urlencode($b['book_id']) ?>" class="small text-decoration-none">
                    Lihat Detail
                  </a>
                </td>
                <td><?= esc($b['book_author']) ?></td>
                <td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>
                <td>
                  <?= date('d/m/Y', strtotime($b['expire_date'])) ?>
                  <?php if ($b['status'] == 'active'): ?>
                    <br>
                    <small class="text-muted">
                      <?php
                      $days_left = floor((strtotime($b['expire_date']) - strtotime($today)) / 86400);
                      if ($days_left > 0) {
                        echo "($days_left hari lagi)";
                      } else {
                        echo "(Hari ini!)";
                      }
                      ?>
                    </small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $badgeClass ?>">
                    <?= ucfirst(esc($b['status'])) ?>
                  </span>
                </td>
                <td>
                  <?php if ($b['status'] == 'active'): ?>
                    <a href="booking_cancel_api.php?id=<?= $b['id'] ?>" 
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Yakin ingin membatalkan booking ini?')">
                      Batalkan
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="alert alert-info">
    <p class="mb-0">Anda belum memiliki booking.</p>
    <a href="catalog_api.php" class="alert-link">Lihat katalog buku</a>
  </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>