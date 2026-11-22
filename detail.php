<?php
// Halaman Detail Produk Helm 
// Fitur: Spesifikasi lengkap, info profil penjual,
//        tombol langsung WhatsApp, tombol edit/hapus bagi pemilik
require_once 'koneksi.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
} 

// Ambil data helm dan data penjualnya
$stmt = $db->prepare("SELECT h.*, u.nama AS nama_penjual, u.email AS email_penjual, u.no_wa, u.kota AS kota_penjual, u.created_at AS member_since 
                      FROM helm h 
                      JOIN users u ON h.user_id = u.id 
                      WHERE h.id = ?");
$stmt->execute([$id]);
$helm = $stmt->fetch();

if (!$helm) {
    die("Produk helm tidak ditemukan atau sudah dihapus. <a href='index.php'>Kembali ke beranda</a>");
}

// Cek apakah pengunjung adalah pemilik helm ini
$isPemilik = (cekLogin() && $_SESSION['user_id'] == $helm['user_id']);
$isTerjual = ($helm['status'] === 'Terjual');

// Template pesan WhatsApp
$pesanWA = "Halo kak *" . $helm['nama_penjual'] . "*, saya tertarik dengan helm yang kakak jual di HelmetMarket:\n\n"
         . "• *Helm*: " . $helm['judul'] . "\n"
         . "• *Harga*: " . formatRupiah($helm['harga']) . "\n"
         . "• *Lokasi*: " . $helm['lokasi'] . "\n\n"
         . "Apakah helm ini masih ada dan bisa COD / kirim?";
$urlWA = "https://wa.me/" . $helm['no_wa'] . "?text=" . urlencode($pesanWA);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($helm['judul']) ?> - HelmetMarket</title>
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

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 group">
                <span class="w-9 h-9 bg-fb text-white rounded-full flex items-center justify-center font-bold shadow">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="text-xl font-black text-fb">HelmetMarket</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-sm font-semibold text-gray-600 hover:text-fb flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Marketplace
                </a>
            </div>
        </div>
    </nav>

    <!-- Konten Detail Produk -->
    <main class="max-w-6xl mx-auto w-full px-4 py-6">

        <!-- Breadcrumb -->
        <div class="text-xs text-gray-500 mb-4 flex items-center gap-2">
            <a href="index.php" class="hover:text-fb">Marketplace</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a href="index.php?kategori=<?= urlencode($helm['tipe']) ?>" class="hover:text-fb"><?= htmlspecialchars($helm['tipe']) ?></a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-gray-800 font-semibold truncate max-w-xs"><?= htmlspecialchars($helm['judul']) ?></span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">
            
            <!-- Kolom Kiri: Foto Besar Produk (7 Kolom di Desktop) -->
            <div class="lg:col-span-7 bg-gray-900 flex items-center justify-center relative min-h-[400px] lg:min-h-[520px]">
                <img src="<?= htmlspecialchars($helm['foto']) ?>" 
                     alt="<?= htmlspecialchars($helm['judul']) ?>"
                     class="max-h-[500px] w-full object-contain p-4 <?= $isTerjual ? 'filter grayscale opacity-70' : '' ?>"
                     onerror="this.src='https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600&auto=format&fit=crop&q=80'">

                <!-- Badge Status Overlay -->
                <?php if ($isTerjual): ?>
                    <div class="absolute top-4 left-4 bg-red-600 text-white font-black px-4 py-2 rounded-lg shadow-lg text-sm flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i> SUDAH TERJUAL
                    </div>
                <?php else: ?>
                    <div class="absolute top-4 left-4 bg-green-600 text-white font-bold px-3 py-1 rounded-md shadow text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> <?= $helm['status'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Detail, Deskripsi & Kontak Penjual (5 Kolom) -->
            <div class="lg:col-span-5 p-6 flex flex-col justify-between">
                <div>
                    <!-- Judul & Harga -->
                    <h1 class="text-2xl font-black text-gray-900 leading-tight">
                        <?= htmlspecialchars($helm['judul']) ?>
                    </h1>
                    <p class="text-3xl font-extrabold text-fb mt-2">
                        <?= formatRupiah($helm['harga']) ?>
                    </p>

                    <!-- Lokasi & Waktu -->
                    <div class="mt-2 text-xs text-gray-500 flex items-center gap-3">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-location-dot text-red-500"></i> <?= htmlspecialchars($helm['lokasi']) ?>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-clock"></i> Diposting <?= date('d M Y', strtotime($helm['created_at'])) ?>
                        </span>
                    </div>

                    <!-- Tabel Spesifikasi Cepat (Badges) -->
                    <div class="grid grid-cols-2 gap-2 mt-5 p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                        <div>
                            <span class="text-gray-400 block font-semibold">Merk</span>
                            <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($helm['merk']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-semibold">Tipe Helm</span>
                            <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($helm['tipe']) ?></span>
                        </div>
                        <div class="mt-2">
                            <span class="text-gray-400 block font-semibold">Ukuran / Size</span>
                            <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($helm['ukuran']) ?></span>
                        </div>
                        <div class="mt-2">
                            <span class="text-gray-400 block font-semibold">Kondisi</span>
                            <span class="font-bold <?= $helm['kondisi'] === 'Baru' ? 'text-green-600' : 'text-blue-600' ?> text-sm">
                                <?= htmlspecialchars($helm['kondisi']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Deskripsi Barang -->
                    <div class="mt-5">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Keterangan Penjual</h3>
                        <div class="text-sm text-gray-700 leading-relaxed bg-white border border-gray-100 p-3 rounded-lg max-h-40 overflow-y-auto whitespace-pre-line">
                            <?= nl2br(htmlspecialchars($helm['deskripsi'])) ?>
                        </div>
                    </div>

                    <!-- Informasi Profil Penjual -->
                    <div class="mt-5 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-blue-100 text-fb font-black text-lg flex items-center justify-center border border-blue-200">
                                <?= strtoupper(substr($helm['nama_penjual'], 0, 1)) ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900"><?= htmlspecialchars($helm['nama_penjual']) ?></h4>
                                <p class="text-xs text-gray-500">Domisili: <?= htmlspecialchars($helm['kota_penjual']) ?></p>
                                <p class="text-[11px] text-green-600 font-semibold"><i class="fa-solid fa-circle-check"></i> Penjual Terverifikasi</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bagian Tombol Aksi Bawah -->
                <div class="mt-6 pt-4 border-t border-gray-200 space-y-3">
                    
                    <!-- Tombol untuk Pemilik Helm (Bisa Edit / Hapus) -->
                    <?php if ($isPemilik): ?>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl mb-3 text-xs text-amber-900">
                            <i class="fa-solid fa-shield-halved text-amber-600 mr-1"></i> Ini adalah iklan helm milik Anda sendiri.
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="edit.php?id=<?= $helm['id'] ?>" 
                               class="flex-1 py-2.5 bg-fb hover:bg-fbHover text-white font-bold rounded-lg text-sm text-center shadow transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i> Edit Listing Helm
                            </a>
                            <button onclick="konfirmasiHapus(<?= $helm['id'] ?>)" 
                                    class="py-2.5 px-4 bg-red-100 hover:bg-red-200 text-red-700 font-bold rounded-lg text-sm transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>

                    <!-- Tombol Chat WhatsApp untuk Pembeli -->
                    <?php else: ?>
                        <?php if (!$isTerjual): ?>
                            <a href="<?= $urlWA ?>" target="_blank" 
                               class="w-full py-3.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-base text-center shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                                <i class="fa-brands fa-whatsapp text-2xl"></i> Hubungi Penjual via WhatsApp
                            </a>
                        <?php else: ?>
                            <button disabled class="w-full py-3.5 bg-gray-300 text-gray-500 font-bold rounded-xl text-sm cursor-not-allowed">
                                Helm Ini Sudah Laku Terjual
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Tombol Bagikan Postingan -->
                    <button onclick="salinLink()" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-share-nodes"></i> Salin Link Postingan Helm
                    </button>
                </div>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-10">
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
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'hapus.php?id=' + id;
                }
            });
        }

        function salinLink() {
            navigator.clipboard.writeText(window.location.href);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Link postingan disalin ke clipboard!',
                showConfirmButton: false,
                timer: 1500
            });
        }
    </script>
</body>
</html>
 