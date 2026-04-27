<?php
// config.php — Koneksi ke database MySQL (XAMPP)

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Biarkan kosong jika pakai XAMPP default
define('DB_NAME', 'rental_mobil');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:red;">
        <h3>Koneksi database gagal!</h3>
        <p>' . $conn->connect_error . '</p>
        <p>Pastikan XAMPP sudah berjalan dan database <b>rental_mobil</b> sudah diimport.</p>
    </div>');
}

// format rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// cek session login
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>
