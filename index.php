<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit;
}

$role = $_SESSION['user']['role'];

if ($role === 'Admin') {
    header('Location: admin/dashboard.php');
    exit;
}

if ($role === 'Petugas') {
    header('Location: librarian/dashboard.php');
    exit;
}

if ($role === 'Member') {
    header('Location: member/catalog_api.php');
    exit;
}

echo "Role tidak dikenal.";
