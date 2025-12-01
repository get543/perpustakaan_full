<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (empty($_SESSION['user'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function require_role($roles = [])
{
    require_login();
    if (!empty($roles) && !in_array($_SESSION['user']['role'], $roles)) {
        header('HTTP/1.1 403 Forbidden');
        echo "Akses ditolak";
        exit;
    }
}

function require_admin()
{
    require_login();
    if ($_SESSION['user']['role'] !== 'Admin') {
        header('HTTP/1.1 403 Forbidden');
        echo "Akses ditolak (Admin only)";
        exit;
    }
}

function require_librarian()
{
    require_login();
    if ($_SESSION['user']['role'] !== 'Petugas') {
        header('HTTP/1.1 403 Forbidden');
        echo "Akses ditolak (Librarian only)";
        exit;
    }
}

function require_member()
{
    require_login();
    if ($_SESSION['user']['role'] !== 'Member') {
        header('HTTP/1.1 403 Forbidden');
        echo "Akses ditolak (Member only)";
        exit;
    }
}
