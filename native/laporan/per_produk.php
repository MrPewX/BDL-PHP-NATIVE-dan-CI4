<?php
/**
 * Modul Laporan - Penjualan per Produk
 * PHP Native (PDO) - Database: db_penjualan
 * 
 * Menggunakan LEFT JOIN produk ke detil_penjualan, COALESCE(SUM, 0), GROUP BY, dan ORDER BY kode_produk
 */
require_once __DIR__ . '/../config/koneksi.php';

try {
    $pdo = getKoneksi('db_penjualan');

    $sql = "SELECT 
                pr.kode_produk,
                pr.nama_produk,
                COALESCE(SUM(dp.jumlah), 0) AS total_qty,
                COALESCE(SUM(dp.subtotal), 0) AS total_nilai
            FROM produk pr
            LEFT JOIN detil_penjualan dp ON pr.kode_produk = dp.kode_produk
            GROUP BY pr.kode_produk, pr.nama_produk
            ORDER BY pr.kode_produk ASC";

    $stmt = $pdo->query($sql);
    $data_produk = $stmt->fetchAll();
    $error = '';
} catch (PDOException $e) {
    $data_produk = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan per Produk - PHP Native</title>
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
        .alert-danger { padding: 10px 14px; background: #fed7d7; color: #742a2a; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <a href="../index.php" class="nav-link">&larr; Kembali ke Menu Utama</a>
    <h2>Laporan Penjualan per Produk</h2>

    <div class="nav-tabs">
        <a href="penjualan.php" class="tab-btn">1. JOIN Penjualan</a>
        <a href="agregasi.php" class="tab-btn">2. Agregasi per Pelanggan</a>
        <a href="per_produk.php" class="tab-btn active">3. Penjualan per Produk</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-danger">Gagal memuat data: <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th class="text-center">Total Terjual (SUM Qty)</th>
                <th class="text-right">Total Nilai Penjualan (SUM)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data_produk)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #718096;">
                        Belum ada data produk.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $grand_qty = 0;
                $grand_nilai = 0;
                ?>
                <?php foreach ($data_produk as $idx => $row): ?>
                    <?php 
                    $grand_qty += (int)$row['total_qty'];
                    $grand_nilai += (float)$row['total_nilai'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1; ?></td>
                        <td><code><?= htmlspecialchars($row['kode_produk']); ?></code></td>
                        <td><strong><?= htmlspecialchars($row['nama_produk']); ?></strong></td>
                        <td class="text-center"><?= number_format($row['total_qty'], 0, ',', '.'); ?> unit</td>
                        <td class="text-right">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($data_produk)): ?>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Grand Total:</td>
                    <td class="text-center"><?= number_format($grand_qty, 0, ',', '.'); ?> unit</td>
                    <td class="text-right">Rp <?= number_format($grand_nilai, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
