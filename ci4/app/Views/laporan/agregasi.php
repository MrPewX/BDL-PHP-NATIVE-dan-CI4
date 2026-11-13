<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<h2>Laporan Agregasi Penjualan per Pelanggan</h2>

<div class="nav-tabs">
    <a href="<?= base_url('laporan/penjualan'); ?>" class="tab-btn">1. JOIN Penjualan</a>
    <a href="<?= base_url('laporan/agregasi'); ?>" class="tab-btn active">2. Agregasi per Pelanggan</a>
    <a href="<?= base_url('laporan/per-produk'); ?>" class="tab-btn">3. Penjualan per Produk</a>
</div>

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
        <?php if (empty($laporan)): ?>
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
            <?php foreach ($laporan as $idx => $row): ?>
                <?php 
                $sum_transaksi += (int)$row['jumlah_transaksi'];
                $sum_penjualan += (float)$row['total_penjualan'];
                ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><code><?= esc($row['kode_pelanggan']); ?></code></td>
                    <td><strong><?= esc($row['nama_pelanggan']); ?></strong></td>
                    <td class="text-center">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; background: #bee3f8; color: #2b6cb0;">
                            <?= $row['jumlah_transaksi']; ?> transaksi
                        </span>
                    </td>
                    <td class="text-right">Rp <?= number_format($row['total_penjualan'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <?php if (!empty($laporan)): ?>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total Keseluruhan:</td>
                <td class="text-center"><?= $sum_transaksi; ?> transaksi</td>
                <td class="text-right">Rp <?= number_format($sum_penjualan, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
<?= $this->endSection(); ?>
