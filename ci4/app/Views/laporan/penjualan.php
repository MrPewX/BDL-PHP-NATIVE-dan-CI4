<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<h2>Laporan Data Penjualan (Query JOIN)</h2>

<div class="nav-tabs">
    <a href="<?= base_url('laporan/penjualan'); ?>" class="tab-btn active">1. JOIN Penjualan</a>
    <a href="<?= base_url('laporan/agregasi'); ?>" class="tab-btn">2. Agregasi per Pelanggan</a>
    <a href="<?= base_url('laporan/per-produk'); ?>" class="tab-btn">3. Penjualan per Produk</a>
</div>

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
        <?php if (empty($laporan)): ?>
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #718096;">
                    Belum ada data penjualan.
                </td>
            </tr>
        <?php else: ?>
            <?php $grand_total = 0; ?>
            <?php foreach ($laporan as $idx => $row): ?>
                <?php $grand_total += (float)$row['total']; ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><code><?= esc($row['no_penjualan']); ?></code></td>
                    <td><?= esc($row['tgl_penjualan']); ?></td>
                    <td><strong><?= esc($row['nama_pelanggan']); ?></strong></td>
                    <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <?php if (!empty($laporan)): ?>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Grand Total:</td>
                <td class="text-right">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
<?= $this->endSection(); ?>
