<?php
$user = $_SESSION['user'] ?? null;
?>
<nav class="navbar navbar-expand-lg bg-gradient shadow-sm">
  <div class="container">
    <a href="../index.php"><img src="../favicon.ico" alt="Icon" style="height:24px; width:24px;" class="me-2"></a>
    <a class="navbar-brand fw-bold" href="../index.php">Perpus Modern</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">

        <?php if ($user): ?>

          <!-- ADMIN MENU -->
          <?php if ($user['role'] === 'Admin'): ?>
            <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="../admin/users.php">User</a></li>
            <li class="nav-item"><a class="nav-link" href="../admin/reports.php">Laporan</a></li>
          <?php endif; ?>

          <!-- LIBRARIAN MENU -->
          <?php if ($user['role'] === 'Petugas'): ?>
            <li class="nav-item"><a class="nav-link" href="../librarian/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="../librarian/books.php">Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="../librarian/bookings.php">Booking</a></li>
            <li class="nav-item"><a class="nav-link" href="../librarian/loans.php">Peminjaman</a></li>
          <?php endif; ?>

          <!-- MEMBER MENU -->
          <?php if ($user['role'] === 'Member'): ?>
            <li class="nav-item"><a class="nav-link" href="../member/catalog.php">Katalog</a></li>
            <li class="nav-item"><a class="nav-link" href="../member/my_bookings.php">Booking</a></li>
            <li class="nav-item"><a class="nav-link" href="../member/my_loans.php">Peminjaman</a></li>
          <?php endif; ?>

        <?php endif; ?>

      </ul>

      <ul class="navbar-nav">
        <button id="theme-toggle" class="btn btn-outline-secondary btn-sm me-3">
          Toggle to Dark Mode
        </button>
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">
              <?= esc($user['name']) ?> (<?= esc($user['role']) ?>)
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm" href="../auth/logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm" href="../auth/login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<div class="container py-4">