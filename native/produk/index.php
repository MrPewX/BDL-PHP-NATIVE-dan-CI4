<?php
/**
 * Modul Produk - Tampil Daftar & Cari Produk
 * PHP Native (PDO) - Database: db_penjualan
 */
require_once __DIR__ . '/../config/koneksi.php';

$pdo = getKoneksi('db_penjualan');
$q   = isset($_GET['q']) ? trim($_GET['q']) : '';

// Query dengan pencarian case-insensitive menggunakan PostgreSQL ILIKE
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE nama_produk ILIKE :q ORDER BY kode_produk ASC");
    $stmt->execute([':q' => "%{$q}%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM produk ORDER BY kode_produk ASC");
}
$daftar_produk = $stmt->fetchAll();

$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error  = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 1050px; margin: 0 auto; background: #fff; padding: 20px 25px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .nav-link { display: inline-block; margin-bottom: 12px; color: #4a5568; text-decoration: none; font-size: 14px; }
        .nav-link:hover { color: #2b6cb0; text-decoration: underline; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        h2 { margin: 0; color: #1a202c; }
        .btn { display: inline-block; padding: 7px 12px; background: #3182ce; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .btn:hover { background: #2b6cb0; }
        .btn-edit { background: #d69e2e; }
        .btn-edit:hover { background: #b7791f; }
        .btn-hapus { background: #e53e3e; }
        .btn-hapus:hover { background: #c53030; }
        .btn-reset { background: #718096; }
        .btn-reset:hover { background: #4a5568; }
        .search-box { margin-bottom: 15px; display: flex; gap: 8px; }
        .search-box input { flex: 1; padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #e2e8f0; padding: 9px 12px; text-align: left; }
        th { background: #edf2f7; color: #2d3748; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .alert { padding: 10px 14px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .alert-danger { background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; }
    </style>
</head>
<body>
<div class="container">
    <a href="../index.php" class="nav-link">&larr; Kembali ke Menu Utama</a>

    <div class="header">
        <h2>Daftar Produk (PHP Native - PDO)</h2>
        <a href="tambah.php" class="btn">+ Tambah Produk</a>
    </div>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses); ?></div>
    <?php endif; ?>
    <?php if ($pesan_error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($pesan_error); ?></div>
    <?php endif; ?>

    <!-- Form Pencarian (Case-insensitive ILIKE) -->
    <form method="GET" class="search-box">
        <input type="text" name="q" placeholder="Cari nama produk (case-insensitive)..." value="<?= htmlspecialchars($q); ?>">
        <button type="submit" class="btn">Cari</button>
        <?php if ($q !== ''): ?>
            <a href="index.php" class="btn btn-reset">Reset</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-center">Stok</th>
                <th>Deskripsi</th>
                <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftar_produk)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #718096;">
                        Data produk tidak ditemukan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($daftar_produk as $idx => $p): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1; ?></td>
                        <td><strong><?= htmlspecialchars($p['kode_produk']); ?></strong></td>
                        <td><?= htmlspecialchars($p['nama_produk']); ?></td>
                        <td><?= htmlspecialchars($p['satuan']); ?></td>
                        <td class="text-right">Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?= number_format($p['harga_beli'], 0, ',', '.'); ?></td>
                        <td class="text-center"><?= number_format($p['stok'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($p['deskripsi'] ?? '-'); ?></td>
                        <td class="text-center">
                            <a href="edit.php?kode=<?= urlencode($p['kode_produk']); ?>" class="btn btn-edit" style="padding: 4px 8px; font-size: 12px;">Ubah</a>
                            <a href="hapus.php?kode=<?= urlencode($p['kode_produk']); ?>" class="btn btn-hapus" style="padding: 4px 8px; font-size: 12px;" onclick="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars($p['kode_produk']); ?>?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
