<?php
// Form Tambah Postingan Helm Baru (CREATE)
// Diproteksi Session: Hanya user yang login yang bisa jual helm
require_once 'koneksi.php';

// Proteksi Session: Harus login terlebih dahulu
if (!cekLogin()) {
    $_SESSION['flash_info'] = "Silakan login terlebih dahulu untuk memasang iklan jual helm.";
    header("Location: auth.php");
    exit;
}

$pesanError = '';

// Proses Submit Form (CREATE)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $judul     = input($_POST['judul'] ?? '');
    $merk      = input($_POST['merk'] ?? '');
    $tipe      = input($_POST['tipe'] ?? '');
    $ukuran    = input($_POST['ukuran'] ?? '');
    $kondisi   = input($_POST['kondisi'] ?? '');
    $harga     = (int)($_POST['harga'] ?? 0);
    $lokasi    = input($_POST['lokasi'] ?? '');
    $deskripsi = input($_POST['deskripsi'] ?? '');
    $fotoUrl   = trim($_POST['foto_url'] ?? '');

    // Validasi input
    if (empty($judul) || empty($merk) || empty($tipe) || empty($ukuran) || empty($kondisi) || $harga <= 0 || empty($lokasi)) {
        $pesanError = "Semua kolom bertanda bintang (*) wajib diisi dengan benar!";
    } else {
        $fotoPath = '';

        // Penanganan upload file foto jika user memilih upload file
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['foto_file']['tmp_name'];
            $fileName = $_FILES['foto_file']['name'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $extValid = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (in_array($fileExt, $extValid)) {
                // Buat folder uploads jika belum ada
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $namaFileBaru = 'helm_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
                $tujuan = $uploadDir . '/' . $namaFileBaru;

                if (move_uploaded_file($fileTmp, $tujuan)) {
                    $fotoPath = 'uploads/' . $namaFileBaru;
                }
            }
        }

        // Jika tidak upload file, gunakan URL foto dari input teks
        if (empty($fotoPath)) {
            if (!empty($fotoUrl)) {
                $fotoPath = $fotoUrl;
            } else {
                // Placeholder default jika tidak ada gambar
                $fotoPath = 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600&auto=format&fit=crop&q=80';
            }
        }

        // Simpan ke database menggunakan PDO Prepared Statement
        $stmt = $db->prepare("INSERT INTO helm 
            (user_id, judul, merk, tipe, ukuran, kondisi, harga, lokasi, deskripsi, foto, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Tersedia')");
        
        $sukses = $stmt->execute([
            $_SESSION['user_id'],
            $judul,
            $merk,
            $tipe,
            $ukuran,
            $kondisi,
            $harga,
            $lokasi,
            $deskripsi,
            $fotoPath
        ]);

        if ($sukses) {
            $newId = $db->lastInsertId();
            $_SESSION['flash_sukses'] = "Iklan helm Anda berhasil diposting!";
            header("Location: detail.php?id=" . $newId);
            exit;
        } else {
            $pesanError = "Gagal menyimpan data ke database. Silakan coba lagi.";
        }
    }
}

// Ambil kota user saat ini sebagai default lokasi
$defaultLokasi = $_SESSION['user_kota'] ?? 'Yogyakarta';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jual Helm Baru - HelmetMarket</title>
    <!-- Framework/Library: Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library: Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 group">
                <span class="w-9 h-9 bg-fb text-white rounded-full flex items-center justify-center font-bold shadow">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="text-xl font-black text-fb">HelmetMarket</span>
            </a>
            <a href="index.php" class="text-sm font-semibold text-gray-600 hover:text-fb flex items-center gap-1">
                <i class="fa-solid fa-xmark text-base"></i> Batal & Kembali
            </a>
        </div>
    </header>

    <!-- Main Content: Form FB Marketplace Listing -->
    <main class="max-w-4xl mx-auto w-full px-4 py-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
            
            <div class="border-b border-gray-200 pb-4 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-plus text-fb"></i> Buat Tawaran Jual Helm
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Pasang helm Anda agar dapat dilihat oleh ribuan pengendara motor di Marketplace.
                    </p>
                </div>
                <div class="hidden sm:block text-right text-xs text-gray-500">
                    Penjual: <span class="font-bold text-gray-800"><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                </div>
            </div>

            <?php if (!empty($pesanError)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?= $pesanError ?></span>
                </div>
            <?php endif; ?>

            <form action="tambah.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <!-- Judul Helm -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Judul Postingan Helm <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul" required
                           value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>"
                           placeholder="Contoh: KYT TT Course Dalla Porta Size L Mulus Like New"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                </div>

                <!-- Baris: Merk, Tipe, Ukuran, Kondisi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Merk / Brand <span class="text-red-500">*</span>
                        </label>
                        <select name="merk" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="">-- Pilih Merk --</option>
                            <?php 
                            $listMerk = ['KYT', 'AGV', 'Shoei', 'Arai', 'NJS', 'NHK', 'RSV', 'Bogo', 'Zeus', 'HJC', 'MDS', 'Lainnya'];
                            foreach ($listMerk as $m): ?>
                                <option value="<?= $m ?>"><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Tipe Helm <span class="text-red-500">*</span>
                        </label>
                        <select name="tipe" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Full Face">Full Face</option>
                            <option value="Half Face">Half Face</option>
                            <option value="Modular">Modular</option>
                            <option value="Retro">Retro / Classic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Ukuran (Size) <span class="text-red-500">*</span>
                        </label>
                        <select name="ukuran" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="All Size">All Size</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L" selected>L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Kondisi <span class="text-red-500">*</span>
                        </label>
                        <select name="kondisi" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="Baru">Baru (Gres / BNIB)</option>
                            <option value="Bekas" selected>Bekas (Second Mulus)</option>
                        </select>
                    </div>
                </div>

                <!-- Baris: Harga & Lokasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Harga Jual (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-gray-500 text-sm">Rp</span>
                            <input type="number" name="harga" required min="10000" step="5000"
                                   value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>"
                                   placeholder="Contoh: 1250000"
                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Lokasi COD / Kota <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <input type="text" name="lokasi" required
                                   value="<?= htmlspecialchars($_POST['lokasi'] ?? $defaultLokasi) ?>"
                                   placeholder="Contoh: Sleman, Yogyakarta"
                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Keterangan & Deskripsi Helm
                    </label>
                    <textarea name="deskripsi" rows="4"
                              placeholder="Jelaskan kondisi fisik helm, visor, busa interior, kelengkapan (dus, sarung, buku panduan), minus jika ada..."
                              class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <!-- Pilihan Foto Helm: Upload File ATAU URL Gambar Online -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                        Foto Produk Helm
                    </label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-gray-600 block mb-1 font-semibold">Opsi 1: Upload File Gambar</span>
                            <input type="file" name="foto_file" accept="image/*"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-fb file:text-white hover:file:bg-fbHover cursor-pointer">
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 block mb-1 font-semibold">Opsi 2: Atau Masukkan Link / URL Gambar</span>
                            <input type="url" name="foto_url"
                                   value="<?= htmlspecialchars($_POST['foto_url'] ?? '') ?>"
                                   placeholder="https://images.unsplash.com/..."
                                   class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs focus:border-fb outline-none">
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">
                        * Jika kedua opsi di atas dikosongkan, sistem akan otomatis menggunakan foto ilustrasi helm default.
                    </p>
                </div>

                <!-- Tombol Aksi Submit -->
                <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                    <a href="index.php" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-fb hover:bg-fbHover text-white font-bold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Terbitkan Postingan
                    </button>
                </div>

            </form>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-8">
        &copy; <?= date('Y') ?> HelmetMarket Indonesia. All rights reserved.
    </footer>

</body>
</html>
