<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_librarian();

if (isset($_GET['action'], $_GET['id'])) {
  $id = (int) $_GET['id'];
  $action = $_GET['action'];

  if ($action === 'approve') {
    $conn->query("UPDATE bookings SET status='approved' WHERE id=$id");
  } elseif ($action === 'cancel') {
    $conn->query("UPDATE bookings SET status='cancelled' WHERE id=$id");
  } elseif ($action === 'pickup') {
    $booking = $conn->query("SELECT * FROM bookings WHERE id=$id")->fetch_assoc();
    if ($booking && $booking['status'] === 'approved') {
      $user_id = $booking['user_id'];
      $book_id = $booking['book_id'];

      $book = $conn->query("SELECT stock FROM books WHERE id=$book_id")->fetch_assoc();
      if ($book && $book['stock'] > 0) {
        $loan_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+7 days'));

        $stmt = $conn->prepare("INSERT INTO loans (user_id, book_id, loan_date, due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiss', $user_id, $book_id, $loan_date, $due_date);
        $stmt->execute();

        $conn->query("UPDATE books SET stock = stock - 1 WHERE id=$book_id");
        $conn->query("UPDATE bookings SET status='picked_up' WHERE id=$id");
      }
    }
  }

  header('Location: bookings.php');
  exit;
}

$bookings = $conn->query("
    SELECT b.id, u.name AS member_name, bk.title AS book_title,
           b.booking_date, b.expire_date, b.status
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN books bk ON bk.id = b.book_id
    ORDER BY b.created_at DESC
");

include '../includes/header.php';
include '../includes/navbar.php';

?>

<h3 class="mb-3">Booking Buku (Librarian)</h3>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Buku</th>
            <th>Tanggal Booking</th>
            <th>Kadaluarsa</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          while ($b = $bookings->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($b['member_name']) ?></td>
              <td><?= esc($b['book_title']) ?></td>
              <td><?= esc($b['booking_date']) ?></td>
              <td><?= esc($b['expire_date']) ?></td>
              <td><span class="badge bg-secondary"><?= esc($b['status']) ?></span></td>
              <td class="text-end">
                <?php if ($b['status'] === 'pending'): ?>
                  <a class="btn btn-sm btn-outline-success" href="?action=approve&id=<?= $b['id'] ?>">Approve</a>
                  <a class="btn btn-sm btn-outline-danger" href="?action=cancel&id=<?= $b['id'] ?>">Cancel</a>
                <?php elseif ($b['status'] === 'approved'): ?>
                  <a class="btn btn-sm btn-secondary" href="?action=pickup&id=<?= $b['id'] ?>">Konfirmasi &amp; Pinjam</a>
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