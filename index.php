<?php
session_start();

// Inisialisasi Data Awal di Session (Simulasi Database Seeder agar langsung jalan tanpa setup)
if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = [
        1 => ['id' => 1, 'title' => 'KYT TT Course Matt Black', 'category' => 'Full Face', 'brand' => 'KYT', 'price' => 1450000, 'size' => 'L', 'condition' => 'Baru', 'stock' => 12, 'status' => 'available', 'description' => 'Helm Full Face KYT TT Course flat visor original SNI & DOT.', 'image' => 'https://placehold.co/400x400/2563eb/white?text=KYT+TT+Course'],
        2 => ['id' => 2, 'title' => 'AGV K1 S Rossi Mugello', 'category' => 'Full Face', 'brand' => 'AGV', 'price' => 3850000, 'size' => 'M', 'condition' => 'Baru', 'stock' => 5, 'status' => 'available', 'description' => 'Helm balap AGV K1 S standar ECE 22.06 terbaru.', 'image' => 'https://placehold.co/400x400/dc2626/white?text=AGV+K1+S'],
        3 => ['id' => 3, 'title' => 'NJS Kairoz Solid White', 'category' => 'Open Face', 'brand' => 'NJS', 'price' => 680000, 'size' => 'L', 'condition' => 'Baru', 'stock' => 20, 'status' => 'available', 'description' => 'Helm Open Face NJS Kairoz intercom ready.', 'image' => 'https://placehold.co/400x400/334155/white?text=NJS+Kairoz'],
        4 => ['id' => 4, 'title' => 'Shoei X-Fifteen Marquez', 'category' => 'Full Face', 'brand' => 'Shoei', 'price' => 12500000, 'size' => 'XL', 'condition' => 'Baru', 'stock' => 2, 'status' => 'available', 'description' => 'Flagship racing helmet Shoei X-Fifteen original Jepang.', 'image' => 'https://placehold.co/400x400/991b1b/white?text=Shoei+X15'],
        5 => ['id' => 5, 'title' => 'NHK Mark-1 Double Visor', 'category' => 'Modular', 'brand' => 'NHK', 'price' => 950000, 'size' => 'XL', 'condition' => 'Baru', 'stock' => 8, 'status' => 'available', 'description' => 'Helm Modular flip-up cocok untuk touring jarak jauh.', 'image' => 'https://placehold.co/400x400/475569/white?text=NHK+Mark-1'],
        6 => ['id' => 6, 'title' => 'NJS Freedom Classic Retro', 'category' => 'Retro', 'brand' => 'NJS', 'price' => 450000, 'size' => 'M', 'condition' => 'Baru', 'stock' => 15, 'status' => 'available', 'description' => 'Helm Retro klasik lengkap dengan kaca bogo.', 'image' => 'https://placehold.co/400x400/d97706/white?text=NJS+Freedom'],
        7 => ['id' => 7, 'title' => 'KYT Kyoto R Gorilla Grey', 'category' => 'Open Face', 'brand' => 'KYT', 'price' => 420000, 'size' => 'S', 'condition' => 'Bekas', 'stock' => 1, 'status' => 'available', 'description' => 'Kondisi 95% mulus seperti baru.', 'image' => 'https://placehold.co/400x400/64748b/white?text=KYT+Kyoto'],
        8 => ['id' => 8, 'title' => 'Shoei Glamster Basalt Grey', 'category' => 'Retro', 'brand' => 'Shoei', 'price' => 7900000, 'size' => 'L', 'condition' => 'Bekas', 'stock' => 0, 'status' => 'sold_out', 'description' => 'Helm Neo-Classic Full Face seri Shoei Glamster.', 'image' => 'https://placehold.co/400x400/94a3b8/white?text=Shoei+Glamster'],
    ];
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

$page = $_GET['page'] ?? 'catalog';
$action = $_GET['action'] ?? '';

// ==================== PROSES ACTION PHP ====================

// 1. Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($email === 'admin@helm.com' && $pass === 'password123') {
        $_SESSION['user'] = ['name' => 'Admin HelmetMarket', 'email' => 'admin@helm.com', 'role' => 'admin'];
        $_SESSION['success'] = 'Selamat datang, Admin!';
        header('Location: index.php?page=admin');
        exit;
    } elseif ($email === 'user@helm.com' && $pass === 'password123') {
        $_SESSION['user'] = ['name' => 'Budi Santoso', 'email' => 'user@helm.com', 'role' => 'customer'];
        $_SESSION['success'] = 'Login berhasil sebagai Customer!';
        header('Location: index.php?page=catalog');
        exit;
    } else {
        $_SESSION['error'] = 'Email atau password salah! Gunakan admin@helm.com atau user@helm.com (password123)';
        header('Location: index.php?page=login');
        exit;
    }
}

// 2. Logout
if ($action === 'logout') {
    unset($_SESSION['user']);
    $_SESSION['success'] = 'Anda berhasil logout.';
    header('Location: index.php?page=catalog');
    exit;
}

// 3. Tambah ke Session Cart
if ($action === 'cart_add' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if (isset($_SESSION['products'][$id]) && $_SESSION['products'][$id]['stock'] > 0) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $p = $_SESSION['products'][$id];
            $_SESSION['cart'][$id] = [
                'title' => $p['title'],
                'brand' => $p['brand'],
                'size' => $p['size'],
                'price' => $p['price'],
                'quantity' => 1,
            ];
        }
        $_SESSION['success'] = 'Helm berhasil dimasukkan ke keranjang!';
    }
    header('Location: index.php?page=catalog');
    exit;
}

// 4. Update Qty Cart
if ($action === 'cart_update' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $op = $_GET['op'] ?? '';
    if (isset($_SESSION['cart'][$id])) {
        if ($op === 'plus') $_SESSION['cart'][$id]['quantity']++;
        if ($op === 'minus') {
            $_SESSION['cart'][$id]['quantity']--;
            if ($_SESSION['cart'][$id]['quantity'] <= 0) unset($_SESSION['cart'][$id]);
        }
    }
    header('Location: index.php?page=cart');
    exit;
}

// 5. Hapus Item Cart
if ($action === 'cart_remove' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    unset($_SESSION['cart'][$id]);
    $_SESSION['success'] = 'Item dihapus dari keranjang.';
    header('Location: index.php?page=cart');
    exit;
}

// 6. Checkout
if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = 'Silakan login terlebih dahulu untuk checkout!';
        header('Location: index.php?page=login');
        exit;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $item) {
        $total += $item['price'] * $item['quantity'];
        if (isset($_SESSION['products'][$id])) {
            $_SESSION['products'][$id]['stock'] = max(0, $_SESSION['products'][$id]['stock'] - $item['quantity']);
            if ($_SESSION['products'][$id]['stock'] == 0) {
                $_SESSION['products'][$id]['status'] = 'sold_out';
            }
        }
    }
    $inv = 'INV-' . time();
    $_SESSION['orders'][] = [
        'invoice_number' => $inv,
        'user_name' => $_SESSION['user']['name'],
        'shipping_address' => $_POST['shipping_address'],
        'total_price' => $total,
        'created_at' => date('d-m-Y H:i'),
    ];
    $_SESSION['cart'] = [];
    $_SESSION['success'] = "Checkout berhasil! Nomor Invoice: $inv (Total: Rp " . number_format($total, 0, ',', '.') . ")";
    header('Location: index.php?page=catalog');
    exit;
}

// 7. Admin Simpan / Update Produk
if ($action === 'admin_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId = !empty($_POST['edit_id']) ? (int) $_POST['edit_id'] : time();
    $imgUrl = $_SESSION['products'][$editId]['image'] ?? 'https://placehold.co/400x400/2563eb/white?text=' . urlencode($_POST['brand']);

    // Jika ada upload foto
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = 'uploads/helm_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $fileName);
        $imgUrl = $fileName;
    }

    $stock = (int) $_POST['stock'];
    $_SESSION['products'][$editId] = [
        'id' => $editId,
        'title' => $_POST['title'],
        'category' => $_POST['category'],
        'brand' => $_POST['brand'],
        'price' => (float) $_POST['price'],
        'size' => $_POST['size'],
        'condition' => $_POST['condition'],
        'stock' => $stock,
        'status' => $stock > 0 ? 'available' : 'sold_out',
        'description' => $_POST['description'],
        'image' => $imgUrl,
    ];
    $_SESSION['success'] = 'Data produk helm berhasil disimpan!';
    header('Location: index.php?page=admin');
    exit;
}

// 8. Admin Hapus Produk
if ($action === 'admin_delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    unset($_SESSION['products'][$id]);
    $_SESSION['success'] = 'Produk berhasil dihapus!';
    header('Location: index.php?page=admin');
    exit;
}

// Proteksi Middleware Role Admin
if (in_array($page, ['admin', 'admin_form', 'report']) && (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin')) {
    $_SESSION['error'] = 'Akses ditolak! Halaman Admin hanya bisa diakses oleh Administrator (Login: admin@helm.com / password123).';
    header('Location: index.php?page=catalog');
    exit;
}

$cartCount = 0;
foreach ($_SESSION['cart'] as $c) $cartCount += $c['quantity'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HelmetMarket - Toko Helm Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Header Navbar (Facebook Marketplace Style) -->
    <nav class="bg-white border-b border-gray-300 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="index.php?page=catalog" class="flex items-center gap-2 text-blue-600 font-bold text-xl">
                <i class="fa-solid fa-helmet-safety text-2xl"></i>
                <span>HelmetMarket</span>
            </a>

            <form action="index.php" method="GET" class="flex-1 max-w-md flex">
                <input type="hidden" name="page" value="catalog">
                <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       placeholder="Cari helm..."
                       class="w-full px-4 py-1.5 bg-gray-100 border border-gray-300 rounded-l-full text-sm focus:outline-none focus:border-blue-500">
                <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded-r-full text-sm hover:bg-blue-700">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <div class="flex items-center gap-3 text-sm">
                <a href="index.php?page=catalog" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Katalog</a>

                <a href="index.php?page=cart" class="relative px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    <i class="fa-solid fa-cart-shopping text-blue-600 mr-1"></i> Keranjang
                    <span class="ml-1 px-2 py-0.5 text-xs bg-red-600 text-white rounded-full font-bold"><?= $cartCount ?></span>
                </a>

                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="index.php?page=admin" class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium">Admin Panel</a>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 border-l pl-3 border-gray-300">
                        <span class="font-semibold"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                        <a href="index.php?action=logout" class="text-red-600 hover:underline ml-2">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="index.php?page=login" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Notifikasi Flash Session PHP -->
    <div class="max-w-7xl w-full mx-auto px-4 mt-4">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-800 rounded text-sm">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-800 rounded text-sm">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Konten Utama -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 py-4">

        <?php if ($page === 'login'): ?>
            <!-- HALAMAN LOGIN -->
            <div class="max-w-md mx-auto bg-white p-6 rounded-lg border border-gray-300 mt-6">
                <h1 class="text-xl font-bold mb-4 text-center">Login HelmetMarket</h1>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs space-y-1">
                    <p class="font-bold text-blue-800">Akun Demo Seeder:</p>
                    <p><strong>Admin:</strong> admin@helm.com / password123</p>
                    <p><strong>Customer:</strong> user@helm.com / password123</p>
                </div>
                <form action="index.php?action=login" method="POST" class="space-y-3 text-sm">
                    <div>
                        <label class="block font-semibold mb-1">Email</label>
                        <input type="email" name="email" value="admin@helm.com" required class="w-full border border-gray-300 rounded p-2">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Password</label>
                        <input type="password" name="password" value="password123" required class="w-full border border-gray-300 rounded p-2">
                    </div>
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded">Masuk</button>
                </form>
            </div>

        <?php elseif ($page === 'cart'): ?>
            <!-- HALAMAN KERANJANG BELANJA -->
            <div class="bg-white p-6 rounded-lg border border-gray-300">
                <h1 class="text-xl font-bold mb-4 border-b pb-2">Keranjang Belanja (Session Cart)</h1>
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="text-center py-8 text-gray-500">
                        <p class="mb-3">Keranjang belanja Anda masih kosong.</p>
                        <a href="index.php?page=catalog" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Belanja Sekarang</a>
                    </div>
                <?php else: ?>
                    <table class="w-full text-sm border-collapse border border-gray-300 mb-4">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="border border-gray-300 p-2">Nama Produk</th>
                                <th class="border border-gray-300 p-2">Merek / Ukuran</th>
                                <th class="border border-gray-300 p-2">Harga</th>
                                <th class="border border-gray-300 p-2 text-center">Jumlah (Qty)</th>
                                <th class="border border-gray-300 p-2">Subtotal</th>
                                <th class="border border-gray-300 p-2 text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; foreach ($_SESSION['cart'] as $id => $item): $sub = $item['price'] * $item['quantity']; $total += $sub; ?>
                                <tr>
                                    <td class="border border-gray-300 p-2 font-semibold"><?= htmlspecialchars($item['title']) ?></td>
                                    <td class="border border-gray-300 p-2"><?= htmlspecialchars($item['brand']) ?> - Size <?= htmlspecialchars($item['size']) ?></td>
                                    <td class="border border-gray-300 p-2">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="border border-gray-300 p-2 text-center">
                                        <a href="index.php?action=cart_update&id=<?= $id ?>&op=minus" class="px-2 py-0.5 bg-gray-200 rounded font-bold">-</a>
                                        <span class="mx-2 font-bold"><?= $item['quantity'] ?></span>
                                        <a href="index.php?action=cart_update&id=<?= $id ?>&op=plus" class="px-2 py-0.5 bg-gray-200 rounded font-bold">+</a>
                                    </td>
                                    <td class="border border-gray-300 p-2 font-bold text-blue-600">Rp <?= number_format($sub, 0, ',', '.') ?></td>
                                    <td class="border border-gray-300 p-2 text-center">
                                        <a href="index.php?action=cart_remove&id=<?= $id ?>" class="text-red-600 hover:underline text-xs">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="4" class="border border-gray-300 p-2 text-right">Total Harga:</td>
                                <td colspan="2" class="border border-gray-300 p-2 text-red-600 text-base">Rp <?= number_format($total, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="max-w-md ml-auto bg-gray-50 p-4 rounded border border-gray-300">
                        <form action="index.php?action=checkout" method="POST">
                            <label class="block text-sm font-semibold mb-1">Alamat Pengiriman:</label>
                            <textarea name="shipping_address" rows="2" required placeholder="Masukkan alamat lengkap..." class="w-full border border-gray-300 rounded p-2 text-sm mb-3">Jl. Soekarno Hatta No. 9, Malang</textarea>
                            <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded text-sm">Checkout Sekarang</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($page === 'admin'): ?>
            <!-- HALAMAN DASHBOARD ADMIN CRUD -->
            <div class="bg-white p-5 rounded-lg border border-gray-300">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b">
                    <div>
                        <h1 class="text-xl font-bold">Dashboard Admin - Kelola Produk Helm</h1>
                        <p class="text-xs text-gray-500">Halaman CRUD Produk & Export Laporan PDF</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="index.php?page=report" target="_blank" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded">
                            <i class="fa-solid fa-file-pdf mr-1"></i> Export PDF
                        </a>
                        <a href="index.php?page=admin_form" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded">
                            + Tambah Produk
                        </a>
                    </div>
                </div>

                <table class="w-full text-sm border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="border border-gray-300 p-2">Foto</th>
                            <th class="border border-gray-300 p-2">Nama Helm</th>
                            <th class="border border-gray-300 p-2">Kategori</th>
                            <th class="border border-gray-300 p-2">Merek / Ukuran</th>
                            <th class="border border-gray-300 p-2">Harga</th>
                            <th class="border border-gray-300 p-2">Stok</th>
                            <th class="border border-gray-300 p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['products'] as $p): ?>
                            <tr>
                                <td class="border border-gray-300 p-2"><img src="<?= htmlspecialchars($p['image']) ?>" class="w-12 h-12 object-cover rounded"></td>
                                <td class="border border-gray-300 p-2 font-semibold"><?= htmlspecialchars($p['title']) ?></td>
                                <td class="border border-gray-300 p-2"><?= htmlspecialchars($p['category']) ?></td>
                                <td class="border border-gray-300 p-2"><?= htmlspecialchars($p['brand']) ?> (<?= htmlspecialchars($p['size']) ?>)</td>
                                <td class="border border-gray-300 p-2">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                <td class="border border-gray-300 p-2">
                                    <?= $p['stock'] > 0 ? '<span class="text-green-600 font-bold">' . $p['stock'] . ' (Tersedia)</span>' : '<span class="text-red-600 font-bold">Habis</span>' ?>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <a href="index.php?page=admin_form&id=<?= $p['id'] ?>" class="px-2 py-1 bg-yellow-500 text-white text-xs rounded mr-1">Edit</a>
                                    <a href="index.php?action=admin_delete&id=<?= $p['id'] ?>" onclick="return confirm('Hapus produk ini?')" class="px-2 py-1 bg-red-600 text-white text-xs rounded">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page === 'admin_form'):
            $editItem = isset($_GET['id']) && isset($_SESSION['products'][(int)$_GET['id']]) ? $_SESSION['products'][(int)$_GET['id']] : null;
        ?>
            <!-- FORM TAMBAH / EDIT PRODUK -->
            <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg border border-gray-300">
                <h1 class="text-lg font-bold mb-4 border-b pb-2"><?= $editItem ? 'Edit Produk Helm' : 'Tambah Produk Helm Baru' ?></h1>
                <form action="index.php?action=admin_save" method="POST" enctype="multipart/form-data" class="space-y-3 text-sm">
                    <input type="hidden" name="edit_id" value="<?= $editItem['id'] ?? '' ?>">
                    <div>
                        <label class="block font-semibold mb-1">Judul / Nama Helm</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($editItem['title'] ?? '') ?>" required class="w-full border border-gray-300 rounded p-2">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1">Kategori</label>
                            <select name="category" class="w-full border border-gray-300 rounded p-2">
                                <?php foreach (['Full Face', 'Open Face', 'Modular', 'Retro'] as $c): ?>
                                    <option value="<?= $c ?>" <?= ($editItem['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Merek</label>
                            <select name="brand" class="w-full border border-gray-300 rounded p-2">
                                <?php foreach (['KYT', 'AGV', 'NJS', 'Shoei', 'NHK'] as $b): ?>
                                    <option value="<?= $b ?>" <?= ($editItem['brand'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold mb-1">Harga (Rp)</label>
                            <input type="number" name="price" value="<?= $editItem['price'] ?? '' ?>" required class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Ukuran</label>
                            <select name="size" class="w-full border border-gray-300 rounded p-2">
                                <?php foreach (['S', 'M', 'L', 'XL'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($editItem['size'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Kondisi</label>
                            <select name="condition" class="w-full border border-gray-300 rounded p-2">
                                <option value="Baru" <?= ($editItem['condition'] ?? '') === 'Baru' ? 'selected' : '' ?>>Baru</option>
                                <option value="Bekas" <?= ($editItem['condition'] ?? '') === 'Bekas' ? 'selected' : '' ?>>Bekas</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1">Stok</label>
                            <input type="number" name="stock" value="<?= $editItem['stock'] ?? 5 ?>" required class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Upload Foto Helm</label>
                            <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded p-1.5">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" required class="w-full border border-gray-300 rounded p-2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <a href="index.php?page=admin" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold">Simpan</button>
                    </div>
                </form>
            </div>

        <?php elseif ($page === 'report'): ?>
            <!-- HALAMAN LAPORAN PDF -->
            <div class="bg-white p-8 rounded border border-gray-300">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">LAPORAN STOK & PENJUALAN - HELMETMARKET</h2>
                    <button onclick="window.print()" class="px-4 py-2 bg-red-600 text-white text-xs font-bold rounded">Cetak / Simpan PDF</button>
                </div>
                <table class="w-full text-sm border-collapse border border-gray-400">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-400 p-2">Nama Helm</th>
                            <th class="border border-gray-400 p-2">Kategori</th>
                            <th class="border border-gray-400 p-2">Merek</th>
                            <th class="border border-gray-400 p-2">Ukuran</th>
                            <th class="border border-gray-400 p-2">Harga</th>
                            <th class="border border-gray-400 p-2">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['products'] as $p): ?>
                            <tr>
                                <td class="border border-gray-400 p-2"><?= htmlspecialchars($p['title']) ?></td>
                                <td class="border border-gray-400 p-2"><?= htmlspecialchars($p['category']) ?></td>
                                <td class="border border-gray-400 p-2"><?= htmlspecialchars($p['brand']) ?></td>
                                <td class="border border-gray-400 p-2"><?= htmlspecialchars($p['size']) ?></td>
                                <td class="border border-gray-400 p-2">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                <td class="border border-gray-400 p-2"><?= $p['stock'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <script>window.onload = () => window.print();</script>

        <?php else: ?>
            <!-- HALAMAN KATALOG UTAMA (2 KOLOM) -->
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-64 bg-white p-4 rounded-lg border border-gray-300 h-fit">
                    <h2 class="font-bold text-base mb-3 border-b pb-2">Filter Produk</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="font-semibold block mb-1">Kategori</label>
                            <select id="filterCategory" onchange="filterProductsJS()" class="w-full border border-gray-300 rounded p-2">
                                <option value="">Semua Kategori</option>
                                <option value="Full Face">Full Face</option>
                                <option value="Open Face">Open Face</option>
                                <option value="Modular">Modular</option>
                                <option value="Retro">Retro</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold block mb-1">Merek Helm</label>
                            <select id="filterBrand" onchange="filterProductsJS()" class="w-full border border-gray-300 rounded p-2">
                                <option value="">Semua Merek</option>
                                <option value="KYT">KYT</option>
                                <option value="AGV">AGV</option>
                                <option value="NJS">NJS</option>
                                <option value="Shoei">Shoei</option>
                                <option value="NHK">NHK</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold block mb-1">Ukuran</label>
                            <select id="filterSize" onchange="filterProductsJS()" class="w-full border border-gray-300 rounded p-2">
                                <option value="">Semua Ukuran</option>
                                <option value="S">Ukuran S</option>
                                <option value="M">Ukuran M</option>
                                <option value="L">Ukuran L</option>
                                <option value="XL">Ukuran XL</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold block mb-1">Rentang Harga (Rp)</label>
                            <input type="number" id="filterMinPrice" oninput="filterProductsJS()" placeholder="Harga Min" class="w-full border border-gray-300 rounded p-2 mb-2">
                            <input type="number" id="filterMaxPrice" oninput="filterProductsJS()" placeholder="Harga Max" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <a href="index.php?page=catalog" class="block text-center w-full py-2 bg-gray-200 hover:bg-gray-300 rounded font-semibold">Reset Filter</a>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="mb-3 flex items-center justify-between">
                        <h1 class="text-lg font-bold">Katalog Marketplace Helm</h1>
                        <span class="text-sm text-gray-600">Total: <?= count($_SESSION['products']) ?> produk</span>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php foreach ($_SESSION['products'] as $p): ?>
                            <div class="product-card bg-white rounded-lg border border-gray-300 overflow-hidden flex flex-col justify-between"
                                 data-title="<?= htmlspecialchars(strtolower($p['title'])) ?>"
                                 data-category="<?= htmlspecialchars($p['category']) ?>"
                                 data-brand="<?= htmlspecialchars($p['brand']) ?>"
                                 data-size="<?= htmlspecialchars($p['size']) ?>"
                                 data-price="<?= $p['price'] ?>">
                                <div>
                                    <div class="aspect-square bg-gray-200 relative">
                                        <img src="<?= htmlspecialchars($p['image']) ?>" class="w-full h-full object-cover">
                                        <?php if ($p['stock'] > 0): ?>
                                            <span class="absolute top-2 right-2 bg-green-600 text-white text-xs px-2 py-0.5 rounded">Tersedia (<?= $p['stock'] ?>)</span>
                                        <?php else: ?>
                                            <span class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded">Habis</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-3">
                                        <p class="text-blue-600 font-bold text-base">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                                        <h3 class="text-sm font-semibold mt-1"><?= htmlspecialchars($p['title']) ?></h3>
                                        <div class="flex flex-wrap gap-1 mt-2 text-xs">
                                            <span class="bg-gray-200 px-2 py-0.5 rounded font-medium">Size <?= htmlspecialchars($p['size']) ?></span>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded"><?= htmlspecialchars($p['category']) ?></span>
                                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded"><?= htmlspecialchars($p['condition']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 pt-0 flex gap-2">
                                    <button type="button" onclick="alert('Detail Helm:\n<?= htmlspecialchars(addslashes($p['title'])) ?>\nMerek: <?= $p['brand'] ?> (Size <?= $p['size'] ?>)\n\n<?= htmlspecialchars(addslashes($p['description'])) ?>')"
                                            class="flex-1 py-1.5 bg-gray-200 hover:bg-gray-300 rounded text-xs font-semibold">Detail</button>
                                    <?php if ($p['stock'] > 0): ?>
                                        <a href="index.php?action=cart_add&id=<?= $p['id'] ?>" class="flex-1 text-center py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">+ Keranjang</a>
                                    <?php else: ?>
                                        <button disabled class="flex-1 py-1.5 bg-gray-300 text-gray-500 rounded text-xs">Habis</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('searchInput')?.addEventListener('input', filterProductsJS);
                function filterProductsJS() {
                    let search = (document.getElementById('searchInput')?.value || '').toLowerCase();
                    let category = document.getElementById('filterCategory').value;
                    let brand = document.getElementById('filterBrand').value;
                    let size = document.getElementById('filterSize').value;
                    let minPrice = parseFloat(document.getElementById('filterMinPrice').value) || 0;
                    let maxPrice = parseFloat(document.getElementById('filterMaxPrice').value) || Infinity;

                    document.querySelectorAll('.product-card').forEach(card => {
                        let match = true;
                        if (search && !card.dataset.title.includes(search)) match = false;
                        if (category && card.dataset.category !== category) match = false;
                        if (brand && card.dataset.brand !== brand) match = false;
                        if (size && card.dataset.size !== size) match = false;
                        let price = parseFloat(card.dataset.price);
                        if (price < minPrice || price > maxPrice) match = false;
                        card.style.display = match ? 'flex' : 'none';
                    });
                }
            </script>
        <?php endif; ?>
    </main>

    <footer class="bg-white border-t border-gray-300 text-center py-4 text-xs text-gray-500 mt-8">
        &copy; <?= date('Y') ?> HelmetMarket - Proyek Akhir Praktikum Pemrograman Web
    </footer>
</body>
</html>
