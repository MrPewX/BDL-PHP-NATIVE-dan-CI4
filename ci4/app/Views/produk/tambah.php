<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<div style="max-width: 600px; margin: 0 auto;">
    <a href="<?= base_url('produk'); ?>" style="color: #4a5568; text-decoration: none; font-size: 14px;">&larr; Kembali ke Daftar Produk</a>
    <h2 style="margin-top: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Tambah Produk Baru</h2>

    <?php $errors = session()->getFlashdata('errors') ?? []; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= form_open('produk/simpan'); ?>
    <?= csrf_field(); ?>

    <div style="margin-bottom: 14px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Kode Produk * (Manual, contoh: P006)</label>
        <input type="text" name="kode_produk" maxlength="10" required value="<?= old('kode_produk'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
    </div>

    <div style="margin-bottom: 14px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Nama Produk *</label>
        <input type="text" name="nama_produk" maxlength="100" required value="<?= old('nama_produk'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
    </div>

    <div style="margin-bottom: 14px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Satuan * (contoh: buah, rim, botol)</label>
        <input type="text" name="satuan" maxlength="20" required value="<?= old('satuan'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 14px;">
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Harga Jual (Rp) *</label>
            <input type="number" step="100" min="0" name="harga_jual" required value="<?= old('harga_jual', '0'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
        </div>
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Harga Beli (Rp) *</label>
            <input type="number" step="100" min="0" name="harga_beli" required value="<?= old('harga_beli', '0'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
        </div>
    </div>

    <div style="margin-bottom: 14px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Stok Awal *</label>
        <input type="number" min="0" name="stok" required value="<?= old('stok', '0'); ?>" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
    </div>

    <div style="margin-bottom: 14px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Deskripsi</label>
        <textarea name="deskripsi" rows="3" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;"><?= old('deskripsi'); ?></textarea>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Simpan Produk</button>
        <a href="<?= base_url('produk'); ?>" class="btn btn-reset" style="margin-left: 8px;">Batal</a>
    </div>

    <?= form_close(); ?>
</div>
<?= $this->endSection(); ?>
