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
$stok = random_int(1, 100);

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
						<img src="https://covers.openlibrary.org/b/id/<?= $book["cover_i"] ?>-M.jpg" class="book-cover">
					<?php else: ?>
						<img src="no_cover.png" class="book-cover">
					<?php endif; ?>

					<div class="card-body d-flex flex-column">
						<h6 class="card-title"><?= esc($book['title'] ?? "Unknown Title") ?></h6>
						<p class="text-muted small mb-1">
							<?= isset($book['author_name']) ? esc(implode(', ', $book['author_name'])) : "Unknown Author" ?>
						</p>
						<p class="small mb-2">Tahun: <?= $book['first_publish_year'] ?? "N/A" ?></p>
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