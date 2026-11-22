<?php
// Mengatur koneksi database MySQL (PDO)

// Konfigurasi folder penyimpanan session agar kompatibel di semua sistem
if (!is_writable(session_save_path())) {
    @session_save_path(sys_get_temp_dir());
}

// Mulai session di semua halaman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parameter Koneksi MySQL XAMPP
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'helmet_market';

try {
    // 1. Hubungkan ke MySQL Server (Auto-create database jika belum dibuat di phpMyAdmin)
    $pdoInit = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdoInit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 2. Hubungkan ke database helmet_market
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 3. Membuat tabel 'users' jika belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        no_wa VARCHAR(25) NOT NULL,
        kota VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Membuat tabel 'helm' jika belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS helm (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        judul VARCHAR(255) NOT NULL,
        merk VARCHAR(50) NOT NULL,
        tipe VARCHAR(50) NOT NULL,
        ukuran VARCHAR(20) NOT NULL,
        kondisi VARCHAR(20) NOT NULL,
        harga INT NOT NULL,
        lokasi VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        foto VARCHAR(255),
        status VARCHAR(20) DEFAULT 'Tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_helm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Cek apakah user demo sudah ada, jika kosong buat akun default Govin Faris
    $cekUser = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($cekUser == 0) {
        $stmtUser = $db->prepare("INSERT INTO users (nama, email, password, no_wa, kota) VALUES (?, ?, ?, ?, ?)");
        $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmtUser->execute(['Govin Faris', 'govinfaris@gmail.com', $defaultPassword, '6281234567890', 'Yogyakarta']);
    }

} catch (PDOException $e) {
    die("
    <div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 2px solid #ef4444; border-radius: 12px; background: #fef2f2; color: #991b1b;'>
        <h2 style='margin-top: 0;'>⚠️ Koneksi Database MySQL Gagal</h2>
        <p>Pastikan modul <strong>MySQL</strong> di <strong>XAMPP Control Panel</strong> sudah Anda nyalakan (klik tombol <strong>Start</strong> di sebelah MySQL hingga berwarna hijau).</p>
        <p style='font-size: 13px; color: #6b7280; font-family: monospace;'>Pesan Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}

// Fungsi pembantu (helper) untuk format mata uang Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi pembantu untuk cek apakah user sudah login
function cekLogin() {
    return isset($_SESSION['user_id']);
}

// Fungsi pembantu untuk mengambil data user yang sedang login
function getUserLogin($db) {
    if (!cekLogin()) return null;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Fungsi untuk sanitasi input agar aman dari XSS
function input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
    