<?php
/**
 * Modul Produk - Hapus Produk
 * PHP Native (PDO) - Database: db_penjualan
 * 
 * Menangani Foreign Key Constraint secara ramah jika produk sudah dipakai di detil transaksi
 */
require_once __DIR__ . '/../config/koneksi.php';

$kode = trim($_GET['kode'] ?? '');

if ($kode === '') {
    header("Location: index.php");
    exit;
}

try {
    $pdo = getKoneksi('db_penjualan');

    // Hapus dengan prepared statement
    $stmt = $pdo->prepare("DELETE FROM produk WHERE kode_produk = :kode");
    $stmt->execute([':kode' => $kode]);

    if ($stmt->rowCount() > 0) {
        $msg = "Produk [{$kode}] berhasil dihapus.";
        header("Location: index.php?sukses=" . urlencode($msg));
    } else {
        $msg = "Produk [{$kode}] tidak ditemukan.";
        header("Location: index.php?error=" . urlencode($msg));
    }
    exit;

} catch (PDOException $e) {
    // Tangkap error PostgreSQL foreign key violation (SQLSTATE 23503)
    $sqlState = $e->errorInfo[0] ?? $e->getCode();
    if ($sqlState === '23503' || stripos($e->getMessage(), 'foreign key') !== false || stripos($e->getMessage(), '23503') !== false) {
        $pesan_ramah = "Produk [{$kode}] TIDAK BISA DIHAPUS karena sudah digunakan di detil_penjualan atau detil_pembelian (Foreign Key Constraint).";
    } else {
        $pesan_ramah = "Gagal menghapus produk [{$kode}]: " . $e->getMessage();
    }

    header("Location: index.php?error=" . urlencode($pesan_ramah));
    exit;
}
