<?php
// Beranda Utama Bergaya Facebook Marketplace
// Fitur: Read Helm, Filter Kategori, Filter Kondisi, Pencarian, Filter Rentang Harga, Kontak WhatsApp
require_once 'koneksi.php';

// Ambil parameter filter dari URL (GET)
$cari      = input($_GET['cari'] ?? '');
$kategori  = input($_GET['kategori'] ?? '');
$kondisi   = input($_GET['kondisi'] ?? '');
$lokasi    = input($_GET['lokasi'] ?? '');
$minHarga  = isset($_GET['min_harga']) && is_numeric($_GET['min_harga']) ? (int)$_GET['min_harga'] : 0;
$maxHarga  = isset($_GET['max_harga']) && is_numeric($_GET['max_harga']) ? (int)$_GET['max_harga'] : 0;
$status    = input($_GET['status'] ?? '');

// Susun Query SQL dinamis dengan Prepared Statement PDO
$sql = "SELECT h.*, u.nama AS nama_penjual, u.no_wa, u.kota AS kota_penjual 
        FROM helm h 
        JOIN users u ON h.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($cari)) {
    $sql .= " AND (h.judul LIKE ? OR h.merk LIKE ? OR h.deskripsi LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}

if (!empty($kategori)) {
    $sql .= " AND h.tipe = ?";
    $params[] = $kategori;
}

if (!empty($kondisi)) {
    $sql .= " AND h.kondisi = ?";
    $params[] = $kondisi;
}

if (!empty($lokasi)) {
    $sql .= " AND (h.lokasi LIKE ? OR u.kota LIKE ?)";
    $params[] = "%$lokasi%";
    $params[] = "%$lokasi%";
}

if ($minHarga > 0) {
    $sql .= " AND h.harga >= ?";
    $params[] = $minHarga;
}

if ($maxHarga > 0) {
    $sql .= " AND h.harga <= ?";
    $params[] = $maxHarga;
}

if (!empty($status)) {
    $sql .= " AND h.status = ?";
    $params[] = $status;
}

// Urutkan produk terbaru di atas
$sql .= " ORDER BY h.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$daftarHelm = $stmt->fetchAll();

// Flash Messages
$flashSukses = $_SESSION['flash_sukses'] ?? '';
$flashInfo = $_SESSION['flash_info'] ?? '';
unset($_SESSION['flash_sukses'], $_SESSION['flash_info']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Helm - Jual Beli Helm Motor</title>
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
                        fbGray: '#65676B'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-fbBg min-h-screen font-sans text-gray-900 flex flex-col">

    <!--  NAVBAR  -->
    <nav class="sticky top-0 z-50 bg-white border-b border-gray-200 px-4 py-2.5 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="index.php" class="flex items-center gap-2 group">
                    <span class="w-10 h-10 bg-fb text-white rounded-full flex items-center justify-center font-bold text-xl shadow group-hover:scale-105 transition">
                        <i class="fa-solid fa-helmet-safety"></i>
                    </span>
                    <span class="text-2xl font-black text-fb tracking-tight hidden sm:inline">HelmetMarket</span>
                </a>
            </div>

            <!-- Search Bar Tengah (FB Style) -->
            <form action="index.php" method="GET" class="flex-1 max-w-xl">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>"
                           placeholder="Cari helm di Marketplace (misal: KYT, AGV, Full Face)..." 
                           class="w-full pl-10 pr-20 py-2 bg-gray-100 hover:bg-gray-200 focus:bg-white border border-transparent focus:border-fb rounded-full text-sm transition outline-none">
                    <?php if (!empty($cari)): ?>
                        <a href="index.php" class="absolute inset-y-0 right-12 pr-2 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="absolute inset-y-1 right-1.5 px-3 bg-fb text-white text-xs font-semibold rounded-full hover:bg-fbHover transition flex items-center">
                        Cari
                    </button>
                </div>
            </form>

            <!-- Nav Action Buttons -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Tombol Jual Helm (+ Tawaran Baru) -->
                <a href="tambah.php" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-fb text-white rounded-lg text-sm font-semibold hover:bg-fbHover shadow-sm transition">
                    <i class="fa-solid fa-plus-circle text-base"></i>
                    <span class="hidden md:inline">Jual Helm</span>
                </a>

                <?php if (cekLogin()): ?>
                    <!-- Menu Pengguna Login -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 transition">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-fb font-bold flex items-center justify-center text-sm border border-blue-300">
                                <?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?>
                            </div>
                            <span class="text-xs font-semibold hidden lg:inline max-w-[120px] truncate"><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 hidden group-hover:block transition">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Masuk sebagai:</p>
                                <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_nama']) ?></p>
                            </div>
                            <a href="kelola.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-fb">
                                <i class="fa-solid fa-tags text-fb w-4"></i> Tawaran Saya
                            </a>
                            <a href="tambah.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-fb">
                                <i class="fa-solid fa-plus text-green-600 w-4"></i> Pasang Iklan Helm
                            </a>
                            <hr class="my-1 border-gray-100">
                            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Keluar (Logout)
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tombol Masuk / Daftar -->
                    <a href="auth.php" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-sm font-semibold transition">
                        <i class="fa-solid fa-user"></i>
                        <span>Masuk</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </nav>

    <!--  KONTEN MARKETPLACE  -->
    <div class="max-w-7xl mx-auto w-full px-4 py-6 flex-1 flex flex-col md:flex-row gap-6">

        <!--  SIDEBAR FILTER  -->
        <aside class="w-full md:w-64 shrink-0 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm h-fit md:sticky md:top-20">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 mb-4">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-fb"></i> Filter Produk
                </h2>
                <a href="index.php" class="text-xs text-fb font-semibold hover:underline">Reset</a>
            </div>

            <form action="index.php" method="GET" class="space-y-4">
                <?php if (!empty($cari)): ?>
                    <input type="hidden" name="cari" value="<?= htmlspecialchars($cari) ?>">
                <?php endif; ?>

                <!-- Filter Kategori -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tipe Helm</label>
                    <div class="space-y-1 text-sm">
                        <?php 
                        $kategoriList = ['Full Face', 'Half Face', 'Modular', 'Retro'];
                        ?>
                        <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer <?= empty($kategori) ? 'font-bold text-fb bg-blue-50' : 'text-gray-700' ?>">
                            <input type="radio" name="kategori" value="" <?= empty($kategori) ? 'checked' : '' ?> onchange="this.form.submit()" class="text-fb focus:ring-fb">
                            <span>Semua Kategori</span>
                        </label>
                        <?php foreach ($kategoriList as $kat): ?>
                            <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer <?= $kategori === $kat ? 'font-bold text-fb bg-blue-50' : 'text-gray-700' ?>">
                                <input type="radio" name="kategori" value="<?= $kat ?>" <?= $kategori === $kat ? 'checked' : '' ?> onchange="this.form.submit()" class="text-fb focus:ring-fb">
                                <span><?= $kat ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filter Kondisi -->
                <div class="pt-3 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kondisi Barang</label>
                    <select name="kondisi" onchange="this.form.submit()" class="w-full p-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:border-fb outline-none">
                        <option value="">Semua Kondisi</option>
                        <option value="Baru" <?= $kondisi === 'Baru' ? 'selected' : '' ?>>Baru (Gres / BNIB)</option>
                        <option value="Bekas" <?= $kondisi === 'Bekas' ? 'selected' : '' ?>>Bekas (Second Mulus)</option>
                    </select>
                </div>

                <!-- Filter Rentang Harga -->
                <div class="pt-3 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Maksimal (Rp)</label>
                    <div class="relative">
                        <input type="number" name="max_harga" value="<?= $maxHarga > 0 ? $maxHarga : '' ?>" 
                               placeholder="Contoh: 2000000"
                               class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:border-fb outline-none">
                    </div>
                </div>

                <!-- Filter Ketersediaan Status -->
                <div class="pt-3 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status Penjualan</label>
                    <select name="status" onchange="this.form.submit()" class="w-full p-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:border-fb outline-none">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" <?= $status === 'Tersedia' ? 'selected' : '' ?>>Tersedia (Ready)</option>
                        <option value="Terjual" <?= $status === 'Terjual' ? 'selected' : '' ?>>Sudah Terjual</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2 bg-fb hover:bg-fbHover text-white font-semibold text-sm rounded-lg shadow-sm transition">
                    Terapkan Filter
                </button>
            </form>
        </aside>

        <!-- FEED GRID PRODUK HELM  -->
        <main class="flex-1">
            
            <!-- Header Grid Title & Breadcrumb -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-store text-fb"></i> Pilihan Hari Ini untuk Anda
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Menampilkan <?= count($daftarHelm) ?> postingan helm siap COD & kirim
                        <?= !empty($cari) ? 'untuk kata kunci "<strong>' . htmlspecialchars($cari) . '</strong>"' : '' ?>
                    </p>
                </div>

                <!-- Tag Kategori Cepat -->
                <div class="flex items-center gap-1.5 overflow-x-auto text-xs py-1">
                    <a href="index.php" class="px-3 py-1.5 rounded-full border transition <?= empty($kategori) ? 'bg-fb text-white border-fb font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200' ?>">
                        Semua
                    </a>
                    <a href="index.php?kategori=Full+Face" class="px-3 py-1.5 rounded-full border transition <?= $kategori === 'Full Face' ? 'bg-fb text-white border-fb font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200' ?>">
                        Full Face
                    </a>
                    <a href="index.php?kategori=Half+Face" class="px-3 py-1.5 rounded-full border transition <?= $kategori === 'Half Face' ? 'bg-fb text-white border-fb font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200' ?>">
                        Half Face
                    </a>
                    <a href="index.php?kategori=Retro" class="px-3 py-1.5 rounded-full border transition <?= $kategori === 'Retro' ? 'bg-fb text-white border-fb font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200' ?>">
                        Retro / Bogo
                    </a>
                    <a href="index.php?kategori=Modular" class="px-3 py-1.5 rounded-full border transition <?= $kategori === 'Modular' ? 'bg-fb text-white border-fb font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200' ?>">
                        Modular
                    </a>
                </div>
            </div>

            <!-- JIKA TIDAK ADA DATA -->
            <?php if (empty($daftarHelm)): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                    <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Tidak ada helm yang cocok</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                        Coba gunakan kata kunci lain atau reset filter untuk melihat semua helm yang tersedia.
                    </p>
                    <a href="index.php" class="inline-block mt-4 px-4 py-2 bg-fb text-white text-sm font-semibold rounded-lg hover:bg-fbHover transition">
                        Reset Semua Filter
                    </a>
                </div>
            <?php else: ?>

                <!-- GRID KARTU PRODUK ALA FACEBOOK MARKETPLACE -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <?php foreach ($daftarHelm as $item): ?>
                        <?php 
                        $isTerjual = ($item['status'] === 'Terjual');
                        // Format pesan WhatsApp otomatis
                        $pesanWA = "Halo kak " . $item['nama_penjual'] . ", saya melihat postingan helm *" . $item['judul'] . "* di HelmetMarket dengan harga " . formatRupiah($item['harga']) . ". Apakah barang ini masih tersedia?";
                        $urlWA = "https://wa.me/" . $item['no_wa'] . "?text=" . urlencode($pesanWA);
                        ?>
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col group relative">
                            
                            <!--  Foto Produk  -->
                            <a href="detail.php?id=<?= $item['id'] ?>" class="block relative aspect-square bg-gray-100 overflow-hidden">
                                <img src="<?= htmlspecialchars($item['foto']) ?>" 
                                     alt="<?= htmlspecialchars($item['judul']) ?>" 
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300 <?= $isTerjual ? 'filter grayscale opacity-75' : '' ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600&auto=format&fit=crop&q=80'">

                                <!-- Badge Status Terjual / Tersedia -->
                                <?php if ($isTerjual): ?>
                                    <span class="absolute top-2.5 left-2.5 bg-red-600 text-white text-xs font-black px-2.5 py-1 rounded-md shadow">
                                        SUDAH TERJUAL
                                    </span>
                                <?php else: ?>
                                    <span class="absolute top-2.5 left-2.5 bg-green-600 text-white text-[11px] font-bold px-2 py-0.5 rounded shadow">
                                        <?= $item['kondisi'] ?>
                                    </span>
                                <?php endif; ?>

                                <!-- Badge Tipe & Ukuran -->
                                <span class="absolute bottom-2.5 right-2.5 bg-black/70 backdrop-blur-sm text-white text-[11px] font-medium px-2 py-0.5 rounded">
                                    <?= $item['tipe'] ?> • Size <?= $item['ukuran'] ?>
                                </span>
                            </a>

                            <!-- Info Detail Card (FB Style) -->
                            <div class="p-3.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <!-- Harga Bold Khas Marketplace -->
                                    <p class="text-lg font-black text-gray-900 leading-snug">
                                        <?= formatRupiah($item['harga']) ?>
                                    </p>
                                    
                                    <!-- Judul Barang (2 Baris) -->
                                    <a href="detail.php?id=<?= $item['id'] ?>" class="block text-sm font-semibold text-gray-800 hover:text-fb hover:underline line-clamp-2 mt-0.5">
                                        <?= htmlspecialchars($item['judul']) ?>
                                    </a>

                                    <!-- Lokasi & Penjual -->
                                    <div class="mt-2 text-xs text-fbGray flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-red-500"></i>
                                        <span class="truncate"><?= htmlspecialchars($item['lokasi']) ?></span>
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-400 flex items-center gap-1">
                                        <i class="fa-solid fa-user-circle"></i>
                                        <span class="truncate"><?= htmlspecialchars($item['nama_penjual']) ?></span>
                                    </div>
                                </div>

                                <!-- Action Buttons Card -->
                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                                    <a href="detail.php?id=<?= $item['id'] ?>" 
                                       class="flex-1 py-1.5 px-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg text-center transition">
                                        Detail
                                    </a>
                                    
                                    <?php if (!$isTerjual): ?>
                                        <a href="<?= $urlWA ?>" target="_blank" 
                                           class="py-1.5 px-3 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-lg flex items-center justify-center gap-1 shadow-sm transition"
                                           title="Chat Penjual via WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-sm"></i> Chat
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </main>

    </div>

    <!--  FOOTER  -->
    <footer class="bg-white border-t border-gray-200 py-6 px-4 mt-12 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 bg-fb text-white rounded-full flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="font-bold text-gray-800">HelmetMarket Indonesia</span>
                <span>— Marketplace Khusus Helm Motor</span>
            </div>
            <p>&copy; <?= date('Y') ?> HelmetMarket Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <!-- Notifikasi Flash SweetAlert2 -->
    <?php if (!empty($flashSukses)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($flashSukses) ?>',
                timer: 2500,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($flashInfo)): ?>
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: '<?= addslashes($flashInfo) ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

</body>
</html>