<?php
//Menghapus session login user dan redirect
require_once 'koneksi.php';

// Hapus semua session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Mulai session baru hanya untuk flash message
session_start();
$_SESSION['flash_info'] = "Anda telah berhasil keluar (logout).";

header("Location: index.php");
exit;