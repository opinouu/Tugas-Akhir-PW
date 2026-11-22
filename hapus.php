<?php
hapus.php
// Memproses Penghapusan Listing Helm (DELETE)
// Diproteksi Session & Otorisasi Pemilik Helm
require_once 'koneksi.php';

// 1. Cek Login
if (!cekLogin()) {
    $_SESSION['flash_info'] = "Silakan login terlebih dahulu.";
    header("Location: auth.php");
    exit;
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit; 
}

// 2. Ambil data helm untuk cek kepemilikan & path foto
$stmt = $db->prepare("SELECT * FROM helm WHERE id = ?");
$stmt->execute([$id]);
$helm = $stmt->fetch(); 

if (!$helm) {
    $_SESSION['flash_info'] = "Data helm tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// 3. Cek Otorisasi (Hanya pemilik yang berhak menghapus)
if ($helm['user_id'] != $_SESSION['user_id']) {
    die("Akses ditolak! Anda bukan pemilik iklan helm ini. <a href='index.php'>Kembali</a>");
} 

// 4. Jika ada file foto lokal di folder uploads/, hapus file fisiknya
if (!empty($helm['foto']) && strpos($helm['foto'], 'uploads/') === 0) {
    $filePath = __DIR__ . '/' . $helm['foto'];
    if (file_exists($filePath)) { 
        unlink($filePath);
    }
}

// 5. Eksekusi Hapus dari Database
$stmtDelete = $db->prepare("DELETE FROM helm WHERE id = ? AND user_id = ?");
$sukses = $stmtDelete->execute([$id, $_SESSION['user_id']]);

if ($sukses) {
    $_SESSION['flash_sukses'] = "Iklan helm '" . $helm['judul'] . "' berhasil dihapus secara permanen.";
} else {
    $_SESSION['flash_info'] = "Gagal menghapus data helm.";
}

// Arahkan kembali ke halaman kelola tawaran atau index
$referer = $_SERVER['HTTP_REFERER'] ?? 'kelola.php';
if (strpos($referer, 'kelola.php') !== false) {
    header("Location: kelola.php");
} else {
    header("Location: index.php");
}
exit;
