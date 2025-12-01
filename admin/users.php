<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_admin();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Hapus user
if ($action === 'delete' && $id) {
  if ($id != $_SESSION['user']['id']) {  // Hindari hapus diri sendiri
    $conn->query("DELETE FROM users WHERE id=$id");
  }
  header('Location: users.php');
  exit;
}

// Tambah/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $role = $_POST['role'];

  if (!empty($_POST['id'])) {
    // update
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param('sssi', $name, $email, $role, $id);
    $stmt->execute();
  } else {
    // insert dengan default password 123456
    $password = hash('sha256', '123456');
    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $email, $password, $role);
    $stmt->execute();
  }

  header('Location: users.php');
  exit;
}

// Edit user
$editUser = null;
if ($action === 'edit' && $id) {
  $res = $conn->query("SELECT * FROM users WHERE id=$id");
  $editUser = $res->fetch_assoc();
}

// Ambil semua user
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

include '../includes/header.php';
include '../includes/navbar.php';

?>

<h3 class="mb-4">Manajemen User</h3>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= $editUser ? 'Edit User' : 'Tambah User' ?></h5>
        <form method="post">
          <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">

          <div class="mb-2">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" required value="<?= esc($editUser['name'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= esc($editUser['email'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
              <?php
              $roles = ['Admin', 'Petugas', 'Member'];
              $current_role = $editUser['role'] ?? '';
              foreach ($roles as $r):
                ?>
                <option value="<?= $r ?>" <?= $current_role == $r ? 'selected' : '' ?>>
                  <?= $r ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if (!$editUser): ?>
            <p class="small text-muted mt-1">Password default: <b>123456</b></p>
          <?php endif; ?>

          <button class="btn btn-dark w-100 mt-3" type="submit">
            Simpan
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title">Daftar User</h5>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Dibuat</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1;
              while ($u = $users->fetch_assoc()): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= esc($u['name']) ?></td>
                  <td><?= esc($u['email']) ?></td>
                  <td><?= esc($u['role']) ?></td>
                  <td><?= esc($u['created_at']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="users.php?action=edit&id=<?= $u['id'] ?>">Edit</a>

                    <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                      <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus user ini?')"
                        href="users.php?action=delete&id=<?= $u['id'] ?>">Hapus</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>