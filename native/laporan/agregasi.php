<?php
/**
 * Modul Laporan - Agregasi Penjualan per Pelanggan
 * PHP Native (PDO) - Database: db_penjualan
 * 
 * Menggunakan LEFT JOIN, COUNT, COALESCE(SUM, 0), GROUP BY, dan ORDER BY kode_pelanggan
 */
require_once __DIR__ . '/../config/koneksi.php';

try {
    $pdo = getKoneksi('db_penjualan');

    $sql = "SELECT 
                c.kode_pelanggan,
                c.nama_pelanggan,
                COUNT(p.no_penjualan) AS jumlah_transaksi,
                COALESCE(SUM(p.total), 0) AS total_penjualan
            FROM pelanggan c
            LEFT JOIN penjualan p ON c.kode_pelanggan = p.kode_pelanggan
            GROUP BY c.kode_pelanggan, c.nama_pelanggan
            ORDER BY c.kode_pelanggan ASC";

    $stmt = $pdo->query($sql);
    $data_agregasi = $stmt->fetchAll();
    $error = '';
} catch (PDOException $e) {
    $data_agregasi = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregasi per Pelanggan - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px 25px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .nav-link { display: inline-block; margin-bottom: 15px; color: #4a5568; text-decoration: none; font-size: 14px; }
        .nav-link:hover { text-decoration: underline; }
        h2 { margin-top: 0; color: #1a202c; }
        .nav-tabs { display: flex; gap: 8px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .tab-btn { padding: 6px 12px; font-size: 13px; text-decoration: none; color: #4a5568; border-radius: 4px; }
        .tab-btn.active { background: #3182ce; color: #fff; font-weight: bold; }
        .tab-btn:hover:not(.active) { background: #edf2f7; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #e2e8f0; padding: 9px 12px; text-align: left; }
        th { background: #edf2f7; color: #2d3748; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot tr { background: #edf2f7; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; }
        .badge-info { background: #bee3f8; color: #2b6cb0; }
        .alert-danger { padding: 10px 14px; background: #fed7d7; color: #742a2a; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <a href="../index.php" class="nav-link">&larr; Kembali ke Menu Utama</a>
    <h2>Laporan Agregasi Penjualan per Pelanggan</h2>

    <div class="nav-tabs">
        <a href="penjualan.php" class="tab-btn">1. JOIN Penjualan</a>
        <a href="agregasi.php" class="tab-btn active">2. Agregasi per Pelanggan</a>
        <a href="per_produk.php" class="tab-btn">3. Penjualan per Produk</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-danger">Gagal memuat data: <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th class="text-center">Jumlah Transaksi (COUNT)</th>
                <th class="text-right">Total Penjualan (SUM)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data_agregasi)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #718096;">
                        Belum ada data pelanggan.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $sum_transaksi = 0;
                $sum_penjualan = 0;
                ?>
                <?php foreach ($data_agregasi as $idx => $row): ?>
                    <?php 
                    $sum_transaksi += (int)$row['jumlah_transaksi'];
                    $sum_penjualan += (float)$row['total_penjualan'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1; ?></td>
                        <td><code><?= htmlspecialchars($row['kode_pelanggan']); ?></code></td>
                        <td><strong><?= htmlspecialchars($row['nama_pelanggan']); ?></strong></td>
                        <td class="text-center">
                            <span class="badge badge-info"><?= $row['jumlah_transaksi']; ?> transaksi</span>
                        </td>
                        <td class="text-right">Rp <?= number_format($row['total_penjualan'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($data_agregasi)): ?>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Total Keseluruhan:</td>
                    <td class="text-center"><?= $sum_transaksi; ?> transaksi</td>
                    <td class="text-right">Rp <?= number_format($sum_penjualan, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
