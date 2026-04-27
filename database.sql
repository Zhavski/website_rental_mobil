CREATE DATABASE IF NOT EXISTS rental_mobil;
USE rental_mobil;

-- tabel user
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel mobil
CREATE TABLE mobil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mobil VARCHAR(100) NOT NULL,
    merek VARCHAR(50) NOT NULL,
    tahun INT NOT NULL,
    warna VARCHAR(30),
    plat_nomor VARCHAR(20) NOT NULL UNIQUE,
    harga_sewa INT NOT NULL COMMENT 'Harga per hari dalam rupiah',
    status ENUM('tersedia', 'disewa', 'perawatan') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel pelanggan
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_ktp VARCHAR(20) NOT NULL UNIQUE,
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tabel transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    id_mobil INT NOT NULL,
    tgl_mulai DATE NOT NULL,
    tgl_selesai DATE NOT NULL,
    total_hari INT NOT NULL,
    total_bayar INT NOT NULL,
    status ENUM('aktif', 'selesai', 'dibatalkan') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mobil) REFERENCES mobil(id) ON DELETE CASCADE
);

-- =============================================
-- data awal
-- =============================================

-- User admin default: username=admin, password=admin123
INSERT INTO users (username, password, nama_lengkap) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Data mobil 
INSERT INTO mobil (nama_mobil, merek, tahun, warna, plat_nomor, harga_sewa, status) VALUES
('Avanza', 'Toyota', 2022, 'Putih', 'AB 1234 CD', 350000, 'tersedia'),
('Brio', 'Honda', 2023, 'Merah', 'AB 5678 EF', 300000, 'tersedia'),
('Innova Reborn', 'Toyota', 2021, 'Silver', 'AB 9012 GH', 500000, 'disewa'),
('Xpander', 'Mitsubishi', 2022, 'Hitam', 'AB 3456 IJ', 450000, 'tersedia'),
('Jazz', 'Honda', 2020, 'Biru', 'AB 7890 KL', 280000, 'perawatan');

-- Data pelanggan 
INSERT INTO pelanggan (nama, no_ktp, no_hp, alamat) VALUES
('Budi Santoso', '3401010101800001', '081234567890', 'Jl. Mawar No. 10, Yogyakarta'),
('Siti Rahayu', '3401010101850002', '082345678901', 'Jl. Melati No. 5, Sleman'),
('Agus Wijaya', '3401010101900003', '083456789012', 'Jl. Kamboja No. 20, Bantul');

-- Data transaksi
INSERT INTO transaksi (id_pelanggan, id_mobil, tgl_mulai, tgl_selesai, total_hari, total_bayar, status) VALUES
(1, 1, '2026-03-01', '2026-03-03', 3, 1050000, 'selesai'),
(2, 3, '2026-04-01', '2026-04-05', 4, 2000000, 'aktif'),
(3, 2, '2026-03-20', '2026-03-22', 2, 600000, 'selesai');
