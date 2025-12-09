<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_member();

$keyword = $_GET['q'] ?? '';

if ($keyword) {
	// Search OpenLibrary API with the keyword
	$query = urlencode($keyword);
	$url = "https://openlibrary.org/search.json?q=" . $query;
} else {
	// Default query when no search keyword
	$url = "https://openlibrary.org/search.json?q=programming";
}

$data = json_decode(file_get_contents($url . "&limit=12"), true);
// $stok = random_int(1, 100);

// -------------------------------------------------------------------------
// Load the stock file
$stockFile = '/book_stock.json'; // adjust path
if (!file_exists($stockFile)) {
	file_put_contents($stockFile, "{}");
}

$stockData = json_decode(file_get_contents($stockFile), true);
if (!is_array($stockData))
	$stockData = [];

// Get unique ID for each book
function getBookId($book) {
	// Use OLID or work key as unique ID
	return $book['key'] ?? null;
}

// Generate or retrieve stock
function getBookStock($book, &$stockData, $stockFile) {
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




include '../includes/header.php';
include '../includes/navbar.php';
?>

<h3 class="mb-3">Katalog Buku</h3>

<form class="mb-3" method="get">
	<div class="input-group">
		<input type="text" name="q" class="form-control" placeholder="Cari judul atau penulis..."
			value="<?= esc($keyword) ?>">
		<button class="btn btn-dark" type="submit">Cari</button>
	</div>
</form>

<div class="row g-3">
	<?php if (isset($data['docs']) && count($data['docs']) > 0): ?>
		<?php foreach ($data['docs'] as $book) { ?>
			<div class="col-md-3">
				<div class="card shadow-sm h-100 border-0">

					<!-- cover book checking -->
					<?php if (isset($book["cover_i"]) && $book["cover_i"]): ?>
						<img src="https://covers.openlibrary.org/b/id/<?= esc($book["cover_i"]) ?>-M.jpg" class="book-cover">
					<?php else: ?>
						<img src="../assets/uploads/no_cover.png" class="book-cover">
					<?php endif; ?>

					<div class="card-body d-flex flex-column">
						<h6 class="card-title"><?= esc($book['title'] ?? "Unknown Title") ?></h6>
						<p class="text-muted small mb-1">
							<?= isset($book['author_name']) ? esc(implode(', ', $book['author_name'])) : "Unknown Author" ?>
						</p>
						<p class="small mb-2">Tahun: <?= esc($book['first_publish_year'] ?? "N/A") ?></p>

						<!-- stocks -->
						<p class="small mb-2">Stok: <?= getBookStock($book, $stockData, $stockFile) ?></p>

						<!-- Add this link -->
						<?php if (isset($book['key'])): ?>
							<a href="book_detail_api.php?id=<?= urlencode(str_replace('/works/', '', $book['key'])) ?>"
								class="btn btn-sm btn-dark mt-auto">
								Lihat Detail
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php else: ?>
		<div class="col-12">
			<div class="alert alert-info">Tidak ada buku ditemukan.</div>
		</div>
	<?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>