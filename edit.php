<?php
 Form Ubah / Perbarui Data Postingan Helm (UPDATE)
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

// 2. Ambil data helm dari database
$stmt = $db->prepare("SELECT * FROM helm WHERE id = ?");
$stmt->execute([$id]);
$helm = $stmt->fetch();

if (!$helm) {
    die("Produk helm tidak ditemukan.");
}

// 3. Cek Otorisasi (Hanya pemilik yang boleh edit!)
if ($helm['user_id'] != $_SESSION['user_id']) {
    die("Akses Ditolak! Anda bukan pemilik iklan helm ini. <a href='index.php'>Kembali</a>");
}

$pesanError = '';

// 4. PROSES UPDATE (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $judul     = input($_POST['judul'] ?? '');
    $merk      = input($_POST['merk'] ?? '');
    $tipe      = input($_POST['tipe'] ?? '');
    $ukuran    = input($_POST['ukuran'] ?? '');
    $kondisi   = input($_POST['kondisi'] ?? '');
    $status    = input($_POST['status'] ?? 'Tersedia');
    $harga     = (int)($_POST['harga'] ?? 0);
    $lokasi    = input($_POST['lokasi'] ?? '');
    $deskripsi = input($_POST['deskripsi'] ?? '');
    $fotoUrl   = trim($_POST['foto_url'] ?? '');

    if (empty($judul) || empty($merk) || empty($tipe) || empty($ukuran) || empty($kondisi) || $harga <= 0 || empty($lokasi)) {
        $pesanError = "Semua kolom wajib diisi dengan benar!";
    } else {
        $fotoPath = $helm['foto']; // Pertahankan foto lama secara default

        // Jika upload file gambar baru
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['foto_file']['tmp_name'];
            $fileName = $_FILES['foto_file']['name'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $extValid = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (in_array($fileExt, $extValid)) {
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
        } elseif (!empty($fotoUrl)) {
            // Jika user memasukkan link URL foto baru
            $fotoPath = $fotoUrl;
        }

        // Jalankan Update PDO
        $stmtUpdate = $db->prepare("UPDATE helm SET 
            judul = ?, merk = ?, tipe = ?, ukuran = ?, kondisi = ?, 
            harga = ?, lokasi = ?, deskripsi = ?, foto = ?, status = ? 
            WHERE id = ? AND user_id = ?");

        $sukses = $stmtUpdate->execute([
            $judul, $merk, $tipe, $ukuran, $kondisi,
            $harga, $lokasi, $deskripsi, $fotoPath, $status,
            $id, $_SESSION['user_id']
        ]);

        if ($sukses) {
            $_SESSION['flash_sukses'] = "Perubahan pada helm berhasil disimpan!";
            header("Location: detail.php?id=" . $id);
            exit;
        } else {
            $pesanError = "Gagal memperbarui data helm.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Helm: <?= htmlspecialchars($helm['judul']) ?> - HelmetMarket</title>
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

    <!-- Header Navbar -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2">
                <span class="w-9 h-9 bg-fb text-white rounded-full flex items-center justify-center font-bold shadow">
                    <i class="fa-solid fa-helmet-safety"></i>
                </span>
                <span class="text-xl font-black text-fb">HelmetMarket</span>
            </a>
            <a href="detail.php?id=<?= $helm['id'] ?>" class="text-sm font-semibold text-gray-600 hover:text-fb flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail
            </a>
        </div>
    </header>

    <!-- Main Form Edit -->
    <main class="max-w-4xl mx-auto w-full px-4 py-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
            
            <div class="border-b border-gray-200 pb-4 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-fb"></i> Edit Data Iklan Helm
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Perbarui rincian harga, foto, atau tandai sebagai sudah laku terjual.</p>
                </div>
                <span class="px-3 py-1 bg-blue-100 text-fb font-bold text-xs rounded-full">
                    ID Helm: #<?= $helm['id'] ?>
                </span>
            </div>

            <?php if (!empty($pesanError)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?= $pesanError ?></span>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?= $helm['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <!-- Status Penjualan (Tersedia / Terjual) -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold uppercase text-blue-900 block">Status Ketersediaan Barang</span>
                        <p class="text-xs text-blue-700">Ubah status menjadi 'Terjual' jika helm sudah laku terjual ke pembeli.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-green-700">
                            <input type="radio" name="status" value="Tersedia" <?= $helm['status'] === 'Tersedia' ? 'checked' : '' ?> class="text-green-600 focus:ring-green-500">
                            <span>Tersedia (Ready)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-red-600">
                            <input type="radio" name="status" value="Terjual" <?= $helm['status'] === 'Terjual' ? 'checked' : '' ?> class="text-red-600 focus:ring-red-500">
                            <span>Sudah Terjual (Sold Out)</span>
                        </label>
                    </div>
                </div>

                <!-- Judul Helm -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Judul Postingan Helm <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul" required
                           value="<?= htmlspecialchars($helm['judul']) ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                </div>

                <!-- Baris: Merk, Tipe, Ukuran, Kondisi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Merk / Brand</label>
                        <select name="merk" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <?php 
                            $listMerk = ['KYT', 'AGV', 'Shoei', 'Arai', 'NJS', 'NHK', 'RSV', 'Bogo', 'Zeus', 'HJC', 'MDS', 'Lainnya'];
                            foreach ($listMerk as $m): ?>
                                <option value="<?= $m ?>" <?= $helm['merk'] === $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipe Helm</label>
                        <select name="tipe" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="Full Face" <?= $helm['tipe'] === 'Full Face' ? 'selected' : '' ?>>Full Face</option>
                            <option value="Half Face" <?= $helm['tipe'] === 'Half Face' ? 'selected' : '' ?>>Half Face</option>
                            <option value="Modular" <?= $helm['tipe'] === 'Modular' ? 'selected' : '' ?>>Modular</option>
                            <option value="Retro" <?= $helm['tipe'] === 'Retro' ? 'selected' : '' ?>>Retro / Classic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ukuran (Size)</label>
                        <select name="ukuran" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <?php foreach (['All Size', 'S', 'M', 'L', 'XL', 'XXL'] as $uk): ?>
                                <option value="<?= $uk ?>" <?= $helm['ukuran'] === $uk ? 'selected' : '' ?>><?= $uk ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kondisi</label>
                        <select name="kondisi" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:border-fb outline-none">
                            <option value="Baru" <?= $helm['kondisi'] === 'Baru' ? 'selected' : '' ?>>Baru (Gres / BNIB)</option>
                            <option value="Bekas" <?= $helm['kondisi'] === 'Bekas' ? 'selected' : '' ?>>Bekas (Second Mulus)</option>
                        </select>
                    </div>
                </div>

                <!-- Baris: Harga & Lokasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Harga Jual (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-gray-500 text-sm">Rp</span>
                            <input type="number" name="harga" required min="10000" step="5000"
                                   value="<?= $helm['harga'] ?>"
                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Lokasi COD / Kota</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <input type="text" name="lokasi" required
                                   value="<?= htmlspecialchars($helm['lokasi']) ?>"
                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi & Keterangan</label>
                    <textarea name="deskripsi" rows="4"
                              class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-fb focus:border-fb focus:bg-white transition outline-none"><?= htmlspecialchars($helm['deskripsi']) ?></textarea>
                </div>

                <!-- Foto Produk (Preview & Ganti) -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Foto Saat Ini & Perubahan Foto</label>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4 mb-4">
                        <img src="<?= htmlspecialchars($helm['foto']) ?>" 
                             alt="Preview Foto" 
                             class="w-24 h-24 object-cover rounded-lg border border-gray-300 bg-white shadow-sm"
                             onerror="this.src='https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=600&auto=format&fit=crop&q=80'">
                        <div class="text-xs text-gray-600 flex-1">
                            <p class="font-bold">Foto aktif tersimpan saat ini.</p>
                            <p class="text-gray-400">Kosongkan kolom di bawah jika tidak ingin mengganti foto helm.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-gray-600 block mb-1 font-semibold">Ganti dengan File Gambar Baru:</span>
                            <input type="file" name="foto_file" accept="image/*"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-fb file:text-white hover:file:bg-fbHover cursor-pointer">
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 block mb-1 font-semibold">Atau Ganti dengan Link URL Gambar Baru:</span>
                            <input type="url" name="foto_url"
                                   placeholder="https://..."
                                   class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs focus:border-fb outline-none">
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                    <a href="detail.php?id=<?= $helm['id'] ?>" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-fb hover:bg-fbHover text-white font-bold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-8">
        &copy; <?= date('Y') ?> HelmetMarket Indonesia. All rights reserved.
    </footer>

</body>
</html>