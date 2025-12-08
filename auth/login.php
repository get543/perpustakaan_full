<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

if (isset($_SESSION['user'])) {
  header('Location: ../index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();

  if ($user && hash('sha256', $password) === $user['password']) {
    $_SESSION['user'] = [
      'id' => $user['id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'role' => $user['role']
    ];
    header('Location: ../index.php');
    exit;
  } else {
    $error = 'Email atau password salah';
  }
}
?>

<?php include '../includes/header.php'; ?>
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient">
  <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
    <h4 class="mb-3 text-center">Login Perpustakaan</h4>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-outline-secondary w-100 mt-2" type="submit">Masuk</button>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>