<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_librarian();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Hapus buku
if ($action === 'delete' && $id) {

  // Hapus cover juga (opsional)
  $old = $conn->query("SELECT cover FROM books WHERE id=$id")->fetch_assoc()['cover'];
  if ($old && file_exists("../assets/uploads/$old")) {
    unlink("../assets/uploads/$old");
  }

  $conn->query("DELETE FROM books WHERE id = $id");
  header('Location: books.php');
  exit;
}

// Proses tambah / edit buku
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $author = $_POST['author'];
  $category = $_POST['category'];
  $year = (int) $_POST['year'];
  $stock = (int) $_POST['stock'];

  $cover_file = null;

  // Jika upload cover baru
  if (!empty($_FILES['cover']['name'])) {
    $fileName = time() . '_' . basename($_FILES['cover']['name']);
    $target = '../assets/uploads/' . $fileName;

    if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
      $cover_file = $fileName;
    }
  }

  // Update data
  if (!empty($_POST['id'])) {
    $id = (int) $_POST['id'];

    // Ambil cover lama
    $old_cover = $conn->query("SELECT cover FROM books WHERE id=$id")->fetch_assoc()['cover'];

    if ($cover_file) {
      // Hapus cover lama
      if ($old_cover && file_exists("../assets/uploads/$old_cover")) {
        unlink("../assets/uploads/$old_cover");
      }

      // Update dengan cover baru
      $stmt = $conn->prepare("UPDATE books SET title=?,author=?,category=?,year=?,stock=?,cover=? WHERE id=?");
      $stmt->bind_param('sssissi', $title, $author, $category, $year, $stock, $cover_file, $id);
    } else {
      // Update tanpa ganti cover
      $stmt = $conn->prepare("UPDATE books SET title=?,author=?,category=?,year=?,stock=? WHERE id=?");
      $stmt->bind_param('sssisi', $title, $author, $category, $year, $stock, $id);
    }

    $stmt->execute();

  } else {
    // Tambah buku baru
    $stmt = $conn->prepare("INSERT INTO books (title,author,category,year,stock,cover) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('sssiss', $title, $author, $category, $year, $stock, $cover_file);
    $stmt->execute();
  }

  header('Location: books.php');
  exit;
}

// Data edit
$editBook = null;
if ($action === 'edit' && $id) {
  $res = $conn->query("SELECT * FROM books WHERE id=$id");
  $editBook = $res->fetch_assoc();
}

$books = $conn->query("SELECT * FROM books ORDER BY created_at DESC");

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Kelola Buku (Librarian)</h3>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= $editBook ? 'Edit Buku' : 'Tambah Buku' ?></h5>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $editBook['id'] ?? '' ?>">

          <div class="mb-2">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control" required value="<?= esc($editBook['title'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Penulis</label>
            <input type="text" name="author" class="form-control" required
              value="<?= esc($editBook['author'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control" value="<?= esc($editBook['category'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Tahun</label>
            <input type="number" name="year" class="form-control" value="<?= esc($editBook['year'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Stok</label>
            <input type="number" name="stock" class="form-control" required value="<?= esc($editBook['stock'] ?? 0) ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Cover (JPG/PNG)</label>
            <input type="file" name="cover" class="form-control">
          </div>

          <?php if ($editBook && $editBook['cover']): ?>
            <p class="small text-muted mt-2">Cover saat ini:</p>
            <img src="../assets/uploads/<?= esc($editBook['cover']) ?>" style="height:60px; border-radius:4px;">
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
        <h5 class="card-title">Daftar Buku</h5>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Stok</th>
                <th>Cover</th>
                <th></th>
              </tr>
            </thead>
            <tbody>

              <?php $no = 1;
              while ($b = $books->fetch_assoc()): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= esc($b['title']) ?></td>
                  <td><?= esc($b['author']) ?></td>
                  <td><?= esc($b['stock']) ?></td>

                  <td>
                    <?php if ($b['cover']): ?>
                      <img src="../assets/uploads/<?= esc($b['cover']) ?>" style="height:40px; border-radius:4px;">
                    <?php endif; ?>
                  </td>

                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="books.php?action=edit&id=<?= $b['id'] ?>">Edit</a>

                    <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus buku ini?')"
                      href="books.php?action=delete&id=<?= $b['id'] ?>">Hapus</a>
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