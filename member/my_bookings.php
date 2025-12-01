<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$user_id = $_SESSION['user']['id'];

$bookings = $conn->query("
    SELECT b.*, bk.title AS book_title
    FROM bookings b
    JOIN books bk ON bk.id = b.book_id
    WHERE b.user_id = $user_id
    ORDER BY b.created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';

?>

<h3 class="mb-3">Booking Saya</h3>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Buku</th>
            <th>Tgl Booking</th>
            <th>Kadaluarsa</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          while ($b = $bookings->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($b['book_title']) ?></td>
              <td><?= esc($b['booking_date']) ?></td>
              <td><?= esc($b['expire_date']) ?></td>
              <td><span class="badge bg-secondary"><?= esc($b['status']) ?></span></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
