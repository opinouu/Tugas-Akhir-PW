CREATE DATABASE IF NOT EXISTS `helmet_market` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `helmet_market`;

-- 1. Struktur Tabel `users`
DROP TABLE IF EXISTS `helm`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_wa` VARCHAR(25) NOT NULL,
  `kota` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Struktur Tabel `helm`
CREATE TABLE `helm` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `merk` VARCHAR(50) NOT NULL,
  `tipe` VARCHAR(50) NOT NULL,
  `ukuran` VARCHAR(20) NOT NULL,
  `kondisi` VARCHAR(20) NOT NULL,
  `harga` INT NOT NULL,
  `lokasi` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT,
  `foto` VARCHAR(255),
  `status` VARCHAR(20) DEFAULT 'Tersedia',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_helm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Data Awal Akun Demo (Govin Faris)
-- Password: password123
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_wa`, `kota`) VALUES
(1, 'Govin Faris', 'govinfaris@gmail.com', '$2y$10$Y1/s6o3Fw3kY4w9XGgK4A.4d4z6Pz5xM0y1N8F.4tZ8P8e2t1P7Z2', '6281234567890', 'Yogyakarta');