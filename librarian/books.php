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

// If search is submitted
$keyword = $_GET['q'] ?? '';

$results = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
  $query = urlencode($_GET['q']);
  $url = "https://openlibrary.org/search.json?q=$query&limit=12";
  $data = json_decode(file_get_contents($url), true);

  if (!empty($data["docs"])) {
    $results = $data["docs"];
  }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Kelola Buku (Librarian)</h3>
</div>

<!-- search button -->
<form class="mb-3" method="get">
  <div class="input-group">
    <input type="text" name="q" class="form-control" placeholder="Cari judul atau penulis..."
      value="<?= esc($keyword) ?>">
    <button class="btn btn-secondary" type="submit">Cari</button>
  </div>
</form>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= $editBook ? 'Edit Buku' : 'Tambah Buku' ?></h5>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $editBook['id'] ?? '' ?>">

          <div class="mb-2">
            <label class="form-label">Judul</label>
            <input type="text" name="title" id="title" class="form-control" required
              value="<?= esc($editBook['title'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Penulis</label>
            <input type="text" name="author" id="author" class="form-control" required
              value="<?= esc($editBook['author'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" id="category" class="form-control"
              value="<?= esc($editBook['category'] ?? '') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Tahun</label>
            <input type="number" name="year" id="year" class="form-control" value="<?= esc($editBook['year'] ?? 0) ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Stok</label>
            <input type="number" name="stock" class="form-control" required value="<?= esc($editBook['stock'] ?? 0) ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Cover (JPG/PNG)</label>
            <input type="file" name="cover" id="cover" class="form-control">
          </div>

          <?php if ($editBook && $editBook['cover']): ?>
            <p class="small text-muted mt-2">Cover saat ini:</p>
            <img src="../assets/uploads/<?= esc($editBook['cover']) ?>" style="height:60px; border-radius:4px;">
          <?php endif; ?>

          <button class="btn btn-secondary w-100 mt-3" type="submit">
            Simpan
          </button>
        </form>

      </div>
    </div>
  </div>

  <!-- Cards on the right -->
  <div class="col-md-8">
    <div class="card shadow-sm border-0">
      <div class="card-body">

        <!-- If search results exist -->
        <?php if (!empty($results)): ?>

          <div class="row">
            <?php foreach ($results as $book): ?>
              <?php
              $title = $book["title"] ?? "Unknown Title";
              $author = isset($book['author_name']) ? implode(', ', $book['author_name']) : "Unknown Author";
              $year = $book["first_publish_year"] ?? "";
              $coverId = $book["cover_i"] ?? null;
              $coverUrl = $coverId
                ? "https://covers.openlibrary.org/b/id/$coverId-M.jpg"
                : "../assets/uploads/no_cover.png";
              ?>

              <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100 border-0">

                  <!-- cover book -->
                  <img src="<?= esc($coverUrl) ?>" class="book-cover mx-auto d-block" alt="<?= esc($title) ?>">

                  <div class="card-body d-flex flex-column">
                    <h6 class="card-title"><?= esc($title) ?></h6>
                    <p class="text-muted small mb-1"><?= esc($author) ?></p>
                    <p class="small mb-2">Tahun: <?= esc($year ?: "N/A") ?></p>

                    <button class="btn btn-sm btn-dark mt-auto" type="button" onclick="fillForm(this)"
                      data-title="<?= htmlspecialchars($title) ?>" 
                      data-author="<?= htmlspecialchars($author) ?>"
                      data-year="<?= htmlspecialchars($year) ?>" 
                      data-cover="<?= htmlspecialchars($coverUrl) ?>">
                      Tambah Buku
                    </button>

                    <!-- Detail link -->
                    <?php if (isset($book['key'])): ?>
                      <a href="book_detail.php?id=<?= urlencode(str_replace('/works/', '', $book['key'])) ?>"
                        class="btn btn-sm btn-outline-dark mt-2">
                        Lihat Detail
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        <?php else: ?>

          <!-- If no search keyword or no results, show database books -->
          <!-- Daftar Buku -->
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

        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>