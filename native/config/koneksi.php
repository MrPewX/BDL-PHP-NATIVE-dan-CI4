<?php
/**
 * Konfigurasi Koneksi Database PostgreSQL dengan PDO
 * Modul: Praktikum Basis Data Lanjut
 * 
 * Kredensial database dapat disesuaikan di variabel bawah ini.
 */

// Kredensial Database PostgreSQL
$db_host = 'localhost';
$db_port = '5432';
$db_user = 'postgres';
$db_pass = 'seva.123'; // Ganti dengan password PostgreSQL Anda

/**
 * Fungsi untuk mendapatkan koneksi PDO ke database PostgreSQL
 * Mendukung perpindahan database antara database utama dan database uji
 * 
 * @param string $namaDb Nama database ('db_penjualan' atau 'db_penjualan_test')
 * @return PDO Objek koneksi PDO
 * @throws PDOException Jika koneksi gagal
 */
function getKoneksi($namaDb = 'db_penjualan')
{
    global $db_host, $db_port, $db_user, $db_pass;

    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$namaDb}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $db_user, $db_pass, $options);
    } catch (PDOException $e) {
        // Tampilkan pesan kesalahan yang rapi
        throw new PDOException("Gagal terhubung ke database '{$namaDb}': " . $e->getMessage(), (int)$e->getCode());
    }
}

// Inisialisasi instance default $pdo untuk kompatibilitas skrip yang langsung memanggil $pdo
try {
    $pdo = getKoneksi('db_penjualan');
} catch (PDOException $e) {
    // Instance $pdo dapat diinisialisasi null bila service db offline
    $pdo = null;
}

// Tampilkan pesan konfirmasi jika file ini dibuka langsung di browser
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'koneksi.php') {
    if ($pdo) {
        echo "<div style='font-family:sans-serif;padding:20px;'>";
        echo "<h2 style='color:#22543d;background:#c6f6d5;padding:12px;border-radius:6px;display:inline-block;'>&#10004; Koneksi ke PostgreSQL ('db_penjualan') BERHASIL!</h2>";
        echo "<p style='color:#4a5568;'>Driver PDO PostgreSQL aktif dan siap digunakan.</p>";
        echo "</div>";
    } else {
        echo "<div style='font-family:sans-serif;padding:20px;'>";
        echo "<h2 style='color:#742a2a;background:#fed7d7;padding:12px;border-radius:6px;display:inline-block;'>&#10008; Koneksi ke PostgreSQL GAGAL!</h2>";
        echo "<p style='color:#4a5568;'>Pastikan service PostgreSQL berjalan dan kredensial di koneksi.php sudah sesuai.</p>";
        echo "</div>";
    }
}
