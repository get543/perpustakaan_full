<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_librarian();

$randomStock = random_int(0, 100);

// Get book ID from OpenLibrary (format: /works/OL45804W)
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (!$id) {
  die('ID buku tidak valid');
}

// Fetch book details from OpenLibrary API
$url = "https://openlibrary.org/works/" . urlencode($id) . ".json";
$bookData = @file_get_contents($url);

if ($bookData === false) {
  die('Buku tidak ditemukan');
}

$book = json_decode($bookData, true);

// -------------------------------------------------------------------------

// Get unique ID for each book
function getBookId($book)
{
  // Use OLID or work key as unique ID
  return $book['key'] ?? null;
}

// Generate or retrieve stock
function getBookStock($book, &$stockData, $stockFile)
{
  $id = getBookId($book);
  if (!$id)
    return 0;

  // If stock already exists, return it
  if (isset($stockData[$id])) {
    return $stockData[$id];
  }

  // Otherwise create new stock 0–100
  $newStock = random_int(0, 100);

  // Save it
  $stockData[$id] = $newStock;
  file_put_contents($stockFile, json_encode($stockData, JSON_PRETTY_PRINT));

  return $newStock;
}
// -------------------------------------------------------------------------



// Get additional info (editions for ISBN, publisher, etc.)
$editionsUrl = "https://openlibrary.org/works/" . urlencode($id) . "/editions.json";
$editionsData = @file_get_contents($editionsUrl);
$editions = $editionsData ? json_decode($editionsData, true) : null;

// Extract cover ID from book data
$coverId = null;
if (isset($book['covers']) && is_array($book['covers']) && count($book['covers']) > 0) {
  $coverId = $book['covers'][0];
}

// Get first publish year from editions
$firstPublishYear = "N/A";
if (isset($editions['entries']) && count($editions['entries']) > 0) {
  foreach ($editions['entries'] as $edition) {
    if (isset($edition['publish_date'])) {
      $firstPublishYear = $edition['publish_date'];
      break;
    }
  }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="row">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <?php if ($coverId): ?>
        <img src="https://covers.openlibrary.org/b/id/<?= $coverId ?>-L.jpg" class="book-cover-large mx-auto d-block" alt="Book Cover">
      <?php else: ?>
        <img src="../assets/uploads/no_cover.png" class="book-cover-large mx-auto d-block" alt="No Cover">
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-8">
    <h3><?= esc($book['title'] ?? 'Unknown Title') ?></h3>

    <p class="text-muted">
      oleh
      <?php
      if (isset($book['authors']) && is_array($book['authors'])) {
        $authorNames = [];
        foreach ($book['authors'] as $author) {
          if (isset($author['author']['key'])) {
            // Fetch author name
            $authorKey = $author['author']['key'];
            $authorUrl = "https://openlibrary.org" . $authorKey . ".json";
            $authorData = @file_get_contents($authorUrl);
            if ($authorData) {
              $authorInfo = json_decode($authorData, true);
              $authorNames[] = $authorInfo['name'] ?? 'Unknown';
            }
          }
        }
        echo esc(implode(', ', $authorNames) ?: 'Unknown Author');
      } else {
        echo 'Unknown Author';
      }
      ?>
    </p>

    <p>Kategori:
      <?php
      if (isset($book['subjects']) && is_array($book['subjects'])) {
        echo esc(implode(', ', array_slice($book['subjects'], 0, 3)));
      } else {
        echo 'N/A';
      }
      ?>
    </p>

    <p>Tahun: <?= esc($firstPublishYear) ?></p>

    <?php if (isset($book['description'])): ?>
      <div class="mb-3">
        <h5>Deskripsi:</h5>
        <p>
          <?php
          if (is_array($book['description'])) {
            echo esc($book['description']['value'] ?? 'No description');
          } else {
            echo esc($book['description']);
          }
          ?>
        </p>
      </div>
    <?php endif; ?>

    <a href="books.php?title=<?= urlencode($book['title'] ?? 'Unknown Title') ?>&author=<?= urlencode(implode(', ', $authorNames) ?: 'Unknown Author') ?>&year=<?= urlencode($firstPublishYear) ?>&cover=<?= urlencode($coverId ? "https://covers.openlibrary.org/b/id/$coverId-L.jpg" : "../assets/uploads/no_cover.png") ?>"
      class="btn btn-secondary">
      Add to Library
    </a>

    <a href="books.php" class="btn btn-outline-secondary">
      Kembali
    </a>
  </div>


</div>

<?php include '../includes/footer.php'; ?>