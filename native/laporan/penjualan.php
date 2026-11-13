<?php
/**
 * Modul Laporan - JOIN Penjualan dan Pelanggan
 * PHP Native (PDO) - Database: db_penjualan
 */
require_once __DIR__ . '/../config/koneksi.php';

try {
    $pdo = getKoneksi('db_penjualan');

    // Query JOIN penjualan dengan pelanggan, urut tgl_penjualan lalu no_penjualan
    $sql = "SELECT 
                p.no_penjualan,
                p.tgl_penjualan,
                c.nama_pelanggan,
                p.total
            FROM penjualan p
            INNER JOIN pelanggan c ON p.kode_pelanggan = c.kode_pelanggan
            ORDER BY p.tgl_penjualan ASC, p.no_penjualan ASC";

    $stmt = $pdo->query($sql);
    $data_penjualan = $stmt->fetchAll();
    $error = '';
} catch (PDOException $e) {
    $data_penjualan = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan (JOIN) - PHP Native</title>
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
    <h2>Laporan Data Penjualan (Query JOIN)</h2>

    <div class="nav-tabs">
        <a href="penjualan.php" class="tab-btn active">1. JOIN Penjualan</a>
        <a href="agregasi.php" class="tab-btn">2. Agregasi per Pelanggan</a>
        <a href="per_produk.php" class="tab-btn">3. Penjualan per Produk</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-danger">Gagal memuat data: <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>No Penjualan</th>
                <th>Tanggal Penjualan</th>
                <th>Nama Pelanggan</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data_penjualan)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #718096;">
                        Belum ada data penjualan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $grand_total = 0; ?>
                <?php foreach ($data_penjualan as $idx => $row): ?>
                    <?php $grand_total += (float)$row['total']; ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1; ?></td>
                        <td><code><?= htmlspecialchars($row['no_penjualan']); ?></code></td>
                        <td><?= htmlspecialchars($row['tgl_penjualan']); ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_pelanggan']); ?></strong></td>
                        <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($data_penjualan)): ?>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Grand Total:</td>
                    <td class="text-right">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
