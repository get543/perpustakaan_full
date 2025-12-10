<!-- //!DON'T USE THIS, USELESS CODE -->
<?php

$query = "harry potter";
$url = "https://openlibrary.org/search.json?q=" . urlencode($query);

$response = file_get_contents($url);
$data = json_decode($response, true);

foreach ($data['docs'] as $book) {
    echo "Title: " . ($book['title'] ?? 'N/A') . "<br>";
    echo "Author: " . ($book['author_name'][0] ?? 'N/A') . "<br>";
    echo "First Publish: " . ($book['first_publish_year'] ?? 'N/A') . "<br>";
    echo "<hr>";
}



for ($i = 1; $i <= 10; $i++) {
    $url = "https://openlibrary.org/search.json?q=*&page=$i";
    $data = json_decode(file_get_contents($url), true);

    foreach ($data["docs"] as $b) {
        echo $b["title"] . "<br>";
    }
}


include '../includes/header.php';
include '../includes/navbar.php';
?>



<?php
function apiRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp;
}

// If search is submitted
$results = [];
if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);
    $url = "https://openlibrary.org/search.json?q=$query";
    $data = json_decode(apiRequest($url), true);

    if (!empty($data["docs"])) {
        $results = $data["docs"];
    }
}
?>

<form method="GET">
    <input type="text" name="q" placeholder="Search books..." required>
    <button type="submit">Search</button>
</form>

<hr>

<?php
foreach ($results as $book):
    $title = $book["title"] ?? "Unknown";
    $author = $book["author_name"][0] ?? "Unknown";
    $year = $book["first_publish_year"] ?? "0";
    $coverId = $book["cover_i"] ?? null;

    $coverUrl = $coverId
        ? "https://covers.openlibrary.org/b/id/$coverId-M.jpg"
        : "../assets/uploads/no_cover.png";  // fallback image
?>
<div style="margin-bottom:20px;">
    <img src="<?= $coverUrl ?>" width="100">
    <h3><?= $title ?></h3>
    <p>By: <?= $author ?></p>
    <p>Year: <?= $year ?></p>

    <form method="POST" action="add_book.php">
        <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
        <input type="hidden" name="author" value="<?= htmlspecialchars($author) ?>">
        <input type="hidden" name="year" value="<?= $year ?>">
        <input type="hidden" name="cover" value="<?= $coverUrl ?>">
        <button type="submit">Add to Library</button>
    </form>
</div>
<hr>
<?php endforeach; ?>
