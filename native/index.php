<?php
/**
 * Menu Utama Aplikasi PHP Native (PDO)
 * Mata Kuliah: Basis Data Lanjut
 */
require_once __DIR__ . '/config/koneksi.php';

// Cek status koneksi ke db_penjualan dan db_penjualan_test
$status_utama = 'Terkoneksi';
$status_test  = 'Terkoneksi';
$err_utama    = '';
$err_test     = '';

try {
    $pdo_utama = getKoneksi('db_penjualan');
} catch (Exception $e) {
    $status_utama = 'Gagal';
    $err_utama    = $e->getMessage();
}

try {
    $pdo_test = getKoneksi('db_penjualan_test');
} catch (Exception $e) {
    $status_test = 'Gagal';
    $err_test    = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Praktikum Basis Data Lanjut - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f7f9fc; color: #333; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; color: #1a365d; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
        h2 { color: #2d3748; margin-top: 25px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-top: 15px; }
        .card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; background: #fafafa; }
        .card h3 { margin-top: 0; color: #2b6cb0; font-size: 17px; }
        .card p { margin-bottom: 12px; font-size: 14px; color: #4a5568; }
        .btn { display: inline-block; padding: 8px 14px; background: #3182ce; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn:hover { background: #2b6cb0; }
        .btn-outline { background: #fff; color: #3182ce; border: 1px solid #3182ce; }
        .btn-outline:hover { background: #ebf8ff; }
        .status-box { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; background: #edf2f7; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Aplikasi Praktikum Basis Data Lanjut (PHP Native - PDO)</h1>
    <p>Perbandingan Akses Database PostgreSQL Menggunakan PHP Native PDO vs CodeIgniter 4.</p>

    <div class="status-box">
        <strong>Status Koneksi Database:</strong><br>
        1. Database Utama (<code>db_penjualan</code>): 
        <span class="badge <?= $status_utama === 'Terkoneksi' ? 'badge-success' : 'badge-danger'; ?>"><?= $status_utama; ?></span>
        <?php if ($err_utama): ?><small style="color:red;"><br><?= htmlspecialchars($err_utama); ?></small><?php endif; ?>
        <br>
        2. Database Uji (<code>db_penjualan_test</code>): 
        <span class="badge <?= $status_test === 'Terkoneksi' ? 'badge-success' : 'badge-danger'; ?>"><?= $status_test; ?></span>
        <?php if ($err_test): ?><small style="color:red;"><br><?= htmlspecialchars($err_test); ?></small><?php endif; ?>
    </div>

    <h2>Daftar Modul & Fitur</h2>
    <div class="grid">
        <!-- Modul CRUD Produk -->
        <div class="card">
            <h3>1. CRUD Produk</h3>
            <p>Kelola data master produk: Tampil, Cari (case-insensitive ILIKE), Tambah, Edit, dan Hapus (dengan penanganan error foreign key).</p>
            <a href="produk/index.php" class="btn">Buka Modul Produk</a>
        </div>

        <!-- Modul JOIN Penjualan -->
        <div class="card">
            <h3>2. Laporan JOIN Penjualan</h3>
            <p>Query relasi JOIN antara tabel <code>penjualan</code> dan <code>pelanggan</code>, diurutkan berdasarkan tanggal dan nomor penjualan.</p>
            <a href="laporan/penjualan.php" class="btn btn-outline">Buka Laporan JOIN</a>
        </div>

        <!-- Modul Agregasi Pelanggan -->
        <div class="card">
            <h3>3. Agregasi per Pelanggan</h3>
            <p>Statistik penjualan dengan <code>LEFT JOIN</code>, <code>COUNT</code> transaksi, dan <code>COALESCE(SUM, 0)</code> per pelanggan.</p>
            <a href="laporan/agregasi.php" class="btn btn-outline">Buka Agregasi Pelanggan</a>
        </div>

        <!-- Modul Penjualan per Produk -->
        <div class="card">
            <h3>4. Laporan per Produk</h3>
            <p>Rekapitulasi penjualan per produk dengan <code>LEFT JOIN</code> ke <code>detil_penjualan</code>, <code>SUM(jumlah)</code>, dan <code>SUM(subtotal)</code>.</p>
            <a href="laporan/per_produk.php" class="btn btn-outline">Buka Laporan Produk</a>
        </div>

        <!-- Modul Transaksi ACID -->
        <div class="card" style="grid-column: 1 / -1; border-left: 4px solid #dd6b20;">
            <h3>5. Transaksi Penjualan ACID (db_penjualan_test)</h3>
            <p>Uji transaksi atomik (Header + minimal 2 Detail) menggunakan <code>beginTransaction()</code>, <code>commit()</code>, dan <code>rollBack()</code> pada database uji. Dilengkapi tombol autofill simulasi sukses & gagal beserta verifikasi rollback.</p>
            <a href="transaksi/simpan.php" class="btn" style="background:#dd6b20;">Buka Simulasi Transaksi</a>
        </div>
    </div>
</div>
</body>
</html>
