<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <h2>Daftar Produk (CodeIgniter 4 Model)</h2>
    <a href="<?= base_url('produk/tambah'); ?>" class="btn">+ Tambah Produk</a>
</div>

<!-- Form Pencarian Case-Insensitive -->
<form method="GET" action="<?= base_url('produk'); ?>" style="display: flex; gap: 8px; margin-bottom: 15px;">
    <input type="text" name="q" placeholder="Cari nama produk (case-insensitive)..." value="<?= esc($q ?? ''); ?>" style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
    <button type="submit" class="btn">Cari</button>
    <?php if (!empty($q)): ?>
        <a href="<?= base_url('produk'); ?>" class="btn btn-reset">Reset</a>
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
        <?php if (empty($produk)): ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #718096;">
                    Data produk tidak ditemukan.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($produk as $idx => $p): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><strong><?= esc($p['kode_produk']); ?></strong></td>
                    <td><?= esc($p['nama_produk']); ?></td>
                    <td><?= esc($p['satuan']); ?></td>
                    <td class="text-right">Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($p['harga_beli'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= number_format($p['stok'], 0, ',', '.'); ?></td>
                    <td><?= esc($p['deskripsi'] ?? '-'); ?></td>
                    <td class="text-center">
                        <a href="<?= base_url('produk/edit/' . esc($p['kode_produk'])); ?>" class="btn btn-edit" style="padding: 4px 8px; font-size: 12px;">Ubah</a>
                        <a href="<?= base_url('produk/hapus/' . esc($p['kode_produk'])); ?>" class="btn btn-hapus" style="padding: 4px 8px; font-size: 12px;" onclick="return confirm('Yakin ingin menghapus produk <?= esc($p['kode_produk']); ?>?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?= $this->endSection(); ?>
