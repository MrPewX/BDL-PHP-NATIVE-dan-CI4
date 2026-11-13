<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<h2>Laporan Penjualan per Produk</h2>

<div class="nav-tabs">
    <a href="<?= base_url('laporan/penjualan'); ?>" class="tab-btn">1. JOIN Penjualan</a>
    <a href="<?= base_url('laporan/agregasi'); ?>" class="tab-btn">2. Agregasi per Pelanggan</a>
    <a href="<?= base_url('laporan/per-produk'); ?>" class="tab-btn active">3. Penjualan per Produk</a>
</div>

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
        <?php if (empty($laporan)): ?>
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
            <?php foreach ($laporan as $idx => $row): ?>
                <?php 
                $grand_qty += (int)$row['total_qty'];
                $grand_nilai += (float)$row['total_nilai'];
                ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><code><?= esc($row['kode_produk']); ?></code></td>
                    <td><strong><?= esc($row['nama_produk']); ?></strong></td>
                    <td class="text-center"><?= number_format($row['total_qty'], 0, ',', '.'); ?> unit</td>
                    <td class="text-right">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <?php if (!empty($laporan)): ?>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Grand Total:</td>
                <td class="text-center"><?= number_format($grand_qty, 0, ',', '.'); ?> unit</td>
                <td class="text-right">Rp <?= number_format($grand_nilai, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
<?= $this->endSection(); ?>
