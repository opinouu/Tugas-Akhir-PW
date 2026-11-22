<?php
//  Dashboard "Tawaran Anda" ala Facebook Marketplace
// Fitur: Menampilkan listing helm milik user yang sedang login,
//        statistik helm aktif/terjual, quick toggle status, edit & hapus.
require_once 'koneksi.php';

// Proteksi Session: User harus login
if (!cekLogin()) {
    $_SESSION['flash_info'] = "Silakan login untuk mengelola tawaran helm Anda.";
    header("Location: auth.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fitur Quick Toggle Status (Tersedia <-> Terjual)
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $helmId = (int)$_GET['toggle_status'];
    $stmtCek = $db->prepare("SELECT status FROM helm WHERE id = ? AND user_id = ?");
    $stmtCek->execute([$helmId, $userId]);
    $currStatus = $stmtCek->fetchColumn();

    if ($currStatus) {
        $statusBaru = ($currStatus === 'Tersedia') ? 'Terjual' : 'Tersedia';
        $stmtUp = $db->prepare("UPDATE helm SET status = ? WHERE id = ? AND user_id = ?");
        $stmtUp->execute([$statusBaru, $helmId, $userId]);
        $_SESSION['flash_sukses'] = "Status helm berhasil diubah menjadi: $statusBaru!";
    }
    header("Location: kelola.php");
    exit;
}

// Ambil semua helm milik user yang sedang login
$stmt = $db->prepare("SELECT * FROM helm WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$myHelms = $stmt->fetchAll();

// Hitung statistik user
$totalBarang = count($myHelms);
$totalTersedia = 0;
$totalTerjual = 0;
$nilaiAset = 0;

foreach ($myHelms as $h) {
    if ($h['status'] === 'Tersedia') {
        $totalTersedia++;
        $nilaiAset += $h['harga'];
    } else {
        $totalTerjual++;
    }
}

// Flash Message
$flashSukses = $_SESSION['flash_sukses'] ?? '';
$flashInfo = $_SESSION['flash_info'] ?? '';
unset($_SESSION['flash_sukses'], $_SESSION['flash_info']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tawaran Anda - HelmetMarket</title>
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
<body class="bg-fbBg min-h-screen font-sans text-gray-900 flex flex-col justify-between">

    <!-- Header Navbar FB Style -->
    <header class="bg-white border-b border-gray-200 px-4 py-2.5 shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 group">
                <span class="w-9 h-9 bg-fb text-white rounded-full flex items-center justify-center font-bold shadow">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="text-xl font-black text-fb tracking-tight">HelmetMarket</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-xs sm:text-sm font-semibold text-gray-600 hover:text-fb flex items-center gap-1.5">
                    <i class="fa-solid fa-store"></i> Beranda Marketplace
                </a>
                <a href="logout.php" class="text-xs sm:text-sm font-semibold text-red-600 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto w-full px-4 py-6">
        
        <!-- Header Profil & Statistik Penjual -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-100 text-fb font-black text-2xl flex items-center justify-center border-2 border-blue-200 shadow-sm">
                        <?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-gray-900"><?= htmlspecialchars($_SESSION['user_nama']) ?></h1>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <i class="fa-solid fa-envelope mr-1"></i> <?= htmlspecialchars($_SESSION['user_email']) ?> • 
                            <i class="fa-brands fa-whatsapp text-green-600 mx-1"></i> <?= htmlspecialchars($_SESSION['user_wa']) ?> • 
                            <i class="fa-solid fa-location-dot text-red-500 mx-1"></i> <?= htmlspecialchars($_SESSION['user_kota']) ?>
                        </p>
                    </div>
                </div>

                <a href="tambah.php" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-fb hover:bg-fbHover text-white font-bold rounded-xl text-sm shadow-md transition">
                    <i class="fa-solid fa-plus-circle text-base"></i> + Pasang Iklan Helm Baru
                </a>
            </div>

            <!-- Kartu Statistik -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase">Total Postingan</p>
                    <p class="text-2xl font-black text-gray-900 mt-1"><?= $totalBarang ?></p>
                </div>
                <div class="bg-green-50 p-4 rounded-xl border border-green-200 text-center">
                    <p class="text-xs font-bold text-green-700 uppercase">Sedang Tayang</p>
                    <p class="text-2xl font-black text-green-600 mt-1"><?= $totalTersedia ?></p>
                </div>
                <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-center">
                    <p class="text-xs font-bold text-red-700 uppercase">Sudah Terjual</p>
                    <p class="text-2xl font-black text-red-600 mt-1"><?= $totalTerjual ?></p>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-center">
                    <p class="text-xs font-bold text-blue-700 uppercase">Nilai Listing Aktif</p>
                    <p class="text-lg font-black text-blue-900 mt-1"><?= formatRupiah($nilaiAset) ?></p>
                </div>
            </div>
        </div>

        <!-- Daftar Tawaran Helm Milik Saya -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-tags text-fb"></i> Daftar Iklan Tawaran Helm Anda
                </h2>
                <span class="text-xs text-gray-500"><?= $totalBarang ?> barang terdaftar</span>
            </div>

            <?php if (empty($myHelms)): ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <h3 class="font-bold text-gray-700">Belum Ada Iklan Helm</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Anda belum pernah membuat postingan jual helm di HelmetMarket.
                    </p>
                    <a href="tambah.php" class="inline-block mt-4 px-4 py-2 bg-fb text-white text-xs font-bold rounded-lg hover:bg-fbHover transition">
                        Mulai Jual Helm Sekarang
                    </a>
                </div>
            <?php else: ?>
                
                <div class="divide-y divide-gray-100">
                    <?php foreach ($myHelms as $item): ?>
                        <?php $isTerjual = ($item['status'] === 'Terjual'); ?>
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-gray-50 transition">
                            
                            <!-- Foto & Deskripsi Singkat -->
                            <div class="flex items-center gap-4 flex-1">
                                <img src="<?= htmlspecialchars($item['foto']) ?>" 
                                     alt="<?= htmlspecialchars($item['judul']) ?>"
                                     class="w-20 h-20 rounded-xl object-cover border border-gray-200 shrink-0 bg-gray-100 <?= $isTerjual ? 'filter grayscale opacity-75' : '' ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600&auto=format&fit=crop&q=80'">

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <?php if ($isTerjual): ?>
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 font-bold text-[10px] rounded">SUDAH TERJUAL</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 font-bold text-[10px] rounded">TAYANG (TERSEDIA)</span>
                                        <?php endif; ?>
                                        <span class="text-xs text-gray-400">• <?= $item['tipe'] ?> (Size <?= $item['ukuran'] ?>)</span>
                                    </div>
                                    <a href="detail.php?id=<?= $item['id'] ?>" class="font-bold text-sm text-gray-900 hover:text-fb block">
                                        <?= htmlspecialchars($item['judul']) ?>
                                    </a>
                                    <p class="text-sm font-black text-gray-800">
                                        <?= formatRupiah($item['harga']) ?>
                                        <span class="text-xs font-normal text-gray-400 ml-2">Lokasi: <?= htmlspecialchars($item['lokasi']) ?></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Tombol Aksi Kelola -->
                            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end pt-2 sm:pt-0 border-t sm:border-0 border-gray-100">
                                
                                <!-- Toggle Status Terjual / Tersedia -->
                                <a href="kelola.php?toggle_status=<?= $item['id'] ?>" 
                                   class="px-3 py-1.5 border text-xs font-semibold rounded-lg transition <?= $isTerjual ? 'border-green-300 text-green-700 bg-green-50 hover:bg-green-100' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-100' ?>"
                                   title="Klik untuk ubah status ketersediaan">
                                    <i class="fa-solid fa-repeat mr-1"></i>
                                    <?= $isTerjual ? 'Tandai Tersedia' : 'Tandai Terjual' ?>
                                </a>

                                <!-- Lihat Detail -->
                                <a href="detail.php?id=<?= $item['id'] ?>" 
                                   class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Lihat Tampilan">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <a href="edit.php?id=<?= $item['id'] ?>" 
                                   class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-fb text-xs font-semibold rounded-lg transition" title="Ubah Data">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>

                                <!-- Hapus (SweetAlert2) -->
                                <button onclick="konfirmasiHapus(<?= $item['id'] ?>)" 
                                        class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition" title="Hapus Iklan">
                                    <i class="fa-solid fa-trash mr-1"></i> Hapus
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-12">
        &copy; <?= date('Y') ?> HelmetMarket Indonesia. All rights reserved.
    </footer>

    <script>
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Postingan Helm?',
                text: "Postingan helm ini akan dihapus secara permanen dari sistem!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'hapus.php?id=' + id;
                }
            });
        }
    </script>

    <?php if (!empty($flashSukses)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($flashSukses) ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

</body>
</html>
