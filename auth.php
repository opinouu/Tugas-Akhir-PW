<?php
// Halaman Autentikasi (Login & Register) + Session
require_once 'koneksi.php';

// Jika sudah login, langsung arahkan ke halaman utama
if (cekLogin()) {
    header("Location: index.php");
    exit;
}

$pesanError = '';
$pesanSukses = '';
$mode = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';

// PROSES POST FORM
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. PROSES LOGIN
    if ($action === 'login') {
        $email = input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $pesanError = 'Silakan isi email dan kata sandi Anda.';
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set SESSION saat login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_wa'] = $user['no_wa'];
                $_SESSION['user_kota'] = $user['kota'];
                $_SESSION['flash_sukses'] = "Selamat datang kembali, " . $user['nama'] . "!";

                header("Location: index.php");
                exit;
            } else {
                $pesanError = 'Email atau kata sandi salah. Silakan coba lagi.';
            }
        }
    }

    // 2. PROSES REGISTER (DAFTAR AKUN BARU)
    if ($action === 'register') {
        $nama = input($_POST['nama'] ?? '');
        $email = input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $no_wa = input($_POST['no_wa'] ?? '');
        $kota = input($_POST['kota'] ?? '');

        // Format nomor WhatsApp (pastikan diawali 62)
        $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
        if (substr($no_wa, 0, 1) === '0') {
            $no_wa = '62' . substr($no_wa, 1);
        }

        if (empty($nama) || empty($email) || empty($password) || empty($no_wa) || empty($kota)) {
            $pesanError = 'Semua kolom wajib diisi!';
            $mode = 'register';
        } elseif (strlen($password) < 6) {
            $pesanError = 'Kata sandi minimal 6 karakter.';
            $mode = 'register';
        } else {
            // Cek apakah email sudah terdaftar
            $cek = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $cek->execute([$email]);
            if ($cek->fetchColumn() > 0) {
                $pesanError = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
                $mode = 'register';
            } else {
                // Simpan user baru ke database
                $hashPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmtInsert = $db->prepare("INSERT INTO users (nama, email, password, no_wa, kota) VALUES (?, ?, ?, ?, ?)");
                $stmtInsert->execute([$nama, $email, $hashPassword, $no_wa, $kota]);
                $newUserId = $db->lastInsertId();

                // Langsung simpan session login
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_nama'] = $nama;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_wa'] = $no_wa;
                $_SESSION['user_kota'] = $kota;
                $_SESSION['flash_sukses'] = "Pendaftaran berhasil! Selamat datang di HelmetMarket.";

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'register' ? 'Daftar Akun Baru' : 'Masuk' ?> - HelmetMarket FB Style</title>
    <!-- Framework/Library: Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library: Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Library: SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fb: '#1877F2',
                        fbHover: '#166fe5',
                        fbBg: '#F0F2F5',
                        fbCard: '#FFFFFF',
                        fbDark: '#050505',
                        fbGray: '#65676B'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-fbBg min-h-screen flex flex-col justify-between font-sans">

    <!-- Top Simple Bar -->
    <header class="bg-white border-b border-gray-200 py-3 px-6 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2">
                <span class="w-10 h-10 bg-fb text-white rounded-full flex items-center justify-center font-bold text-xl shadow">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="text-2xl font-black text-fb tracking-tight">HelmetMarket</span>
            </a>
            <a href="index.php" class="text-sm font-semibold text-fb hover:underline flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Main Container Card -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-gray-200 p-8 my-6">

            <!-- Title & Subtitle -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    <?= $mode === 'register' ? 'Buat Akun Penjual Helm' : 'Masuk ke HelmetMarket' ?>
                </h1>
                <p class="text-sm text-fbGray mt-1">
                    Jual & beli helm motor terpercaya model Facebook Marketplace
                </p>
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b border-gray-200 mb-6">
                <a href="auth.php?mode=login" 
                   class="flex-1 text-center py-2.5 font-semibold text-sm border-b-2 transition <?= $mode === 'login' ? 'border-fb text-fb' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                    <i class="fa-solid fa-right-to-bracket mr-1"></i> Masuk
                </a>
                <a href="auth.php?mode=register" 
                   class="flex-1 text-center py-2.5 font-semibold text-sm border-b-2 transition <?= $mode === 'register' ? 'border-fb text-fb' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                    <i class="fa-solid fa-user-plus mr-1"></i> Daftar Akun
                </a>
            </div>

            <!-- Pesan Notifikasi Error -->
            <?php if (!empty($pesanError)): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                    <span><?= $pesanError ?></span>
                </div>
            <?php endif; ?>

            <!-- FORM LOGIN -->
            <?php if ($mode === 'login'): ?>
                <form action="auth.php?mode=login" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="login">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" name="email" id="inputEmail" required
                                   class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="nama@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="inputPassword" required
                                   class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 bg-fb hover:bg-fbHover text-white font-bold rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                    </button>
                </form>

                <!-- Box Bantuan Pengujian Dosen / Mahasiswa -->
                <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-900 font-bold mb-1">
                        <i class="fa-solid fa-lightbulb text-yellow-500 mr-1"></i> Akun Demo untuk Pengujian:
                    </p>
                    <p class="text-xs text-blue-800">Email: <code class="bg-blue-100 px-1 py-0.5 rounded font-mono">govinfaris@gmail.com</code></p>
                    <p class="text-xs text-blue-800">Password: <code class="bg-blue-100 px-1 py-0.5 rounded font-mono">password123</code></p>
                    <button type="button" onclick="isiAkunDemo()" 
                            class="mt-2 text-xs font-bold text-fb hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Klik untuk isi otomatis form
                    </button>
                </div>

            <!-- FORM REGISTER -->
            <?php else: ?>
                <form action="auth.php?mode=register" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="register">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Lengkap Penjual</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" name="nama" required
                                   class="w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="Contoh: Govin Faris">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" name="email" required
                                   class="w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="govinfaris@gmail.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kata Sandi (Minimal 6 Karakter)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" minlength="6" required
                                   class="w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="Minimal 6 karakter">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">No. WhatsApp</label>
                            <input type="text" name="no_wa" required
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="08123456789">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kota / Domisili</label>
                            <input type="text" name="kota" required
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition"
                                   placeholder="Yogyakarta">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-check"></i> Daftar & Mulai Jualan
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </main>

    <!-- Simple Footer -->
    <footer class="py-4 text-center text-xs text-gray-500">
        &copy; <?= date('Y') ?> HelmetMarket Indonesia. All rights reserved.
    </footer>

    <script>
        function isiAkunDemo() {
            document.getElementById('inputEmail').value = 'govinfaris@gmail.com';
            document.getElementById('inputPassword').value = 'password123';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Data demo Govin Faris telah diisikan!',
                showConfirmButton: false,
                timer: 1500
            });
        }
    </script>
</body>
</html>