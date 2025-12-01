<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'perpustakaan_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

function esc($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
