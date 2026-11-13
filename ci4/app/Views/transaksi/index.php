<?= $this->extend('layout'); ?>

<?= $this->section('content'); ?>
<h2>Simulasi Transaksi Penjualan ACID (CodeIgniter 4)</h2>

<div style="background: #ebf8ff; border-left: 4px solid #3182ce; padding: 12px 15px; margin-bottom: 20px; font-size: 13px;">
    <strong>Ketentuan Uji:</strong> Dijalankan pada grup koneksi <strong>'test'</strong> (database <code>db_penjualan_test</code>).<br>
    Menguji atomisitas transaksi: Menyimpan Header <code>penjualan</code> + Minimal 2 Detail <code>detil_penjualan</code>. Menggunakan <code>transBegin()</code>, <code>transCommit()</code>, dan <code>transRollback()</code>.
</div>

<!-- Tabel Bukti Baris Sebelum vs Sesudah -->
<table style="margin-bottom: 20px; font-size: 13px;">
    <thead>
        <tr>
            <th>Tabel di db_penjualan_test</th>
            <th class="text-center">Jumlah Baris Sebelum</th>
            <th class="text-center">Jumlah Baris Sesudah</th>
            <th class="text-center">Perubahan / Status Bukti</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>penjualan</strong> (Header)</td>
            <td class="text-center"><?= $statsAwal['penjualan'] ?? '-'; ?></td>
            <td class="text-center"><?= $statsAkhir['penjualan'] ?? '-'; ?></td>
            <td class="text-center">
                <?php 
                $diff_h = ($statsAkhir['penjualan'] ?? 0) - ($statsAwal['penjualan'] ?? 0);
                if ($diff_h > 0) echo "<span style='color:green;font-weight:bold;'>+{$diff_h} (Bertambah / Commit)</span>";
                elseif (!empty($hasilTransaksi)) echo "<span style='color:red;font-weight:bold;'>0 (Utuh / Rollback)</span>";
                else echo "Siap diuji";
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>detil_penjualan</strong> (Detail)</td>
            <td class="text-center"><?= $statsAwal['detil_penjualan'] ?? '-'; ?></td>
            <td class="text-center"><?= $statsAkhir['detil_penjualan'] ?? '-'; ?></td>
            <td class="text-center">
                <?php 
                $diff_d = ($statsAkhir['detil_penjualan'] ?? 0) - ($statsAwal['detil_penjualan'] ?? 0);
                if ($diff_d > 0) echo "<span style='color:green;font-weight:bold;'>+{$diff_d} (Bertambah / Commit)</span>";
                elseif (!empty($hasilTransaksi)) echo "<span style='color:red;font-weight:bold;'>0 (Utuh / Rollback)</span>";
                else echo "Siap diuji";
                ?>
            </td>
        </tr>
    </tbody>
</table>

<!-- Status Hasil Transaksi -->
<?php if (!empty($hasilTransaksi)): ?>
    <div class="alert <?= $hasilTransaksi['sukses'] ? 'alert-success' : 'alert-danger'; ?>">
        <strong>Status: <?= $hasilTransaksi['sukses'] ? 'BERHASIL (COMMITTED)' : 'GAGAL (ROLLED BACK)'; ?></strong><br>
        <?= esc($hasilTransaksi['pesan']); ?>
    </div>

    <!-- Bukti Data di Database -->
    <div style="background: #fafafa; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <h4 style="margin-top: 0;">Bukti Data di db_penjualan_test untuk No Penjualan: <code><?= esc($hasilTransaksi['no_penjualan']); ?></code></h4>
        <?php if (!empty($buktiTransaksi['header'])): ?>
            <p style="margin: 4px 0; font-size: 13px;">
                <strong>Header Ditemukan:</strong> No: <?= esc($buktiTransaksi['header']['no_penjualan']); ?> | 
                Tgl: <?= esc($buktiTransaksi['header']['tgl_penjualan']); ?> | 
                Pelanggan: <?= esc($buktiTransaksi['header']['kode_pelanggan']); ?> | 
                Total: Rp <?= number_format($buktiTransaksi['header']['total'], 0, ',', '.'); ?>
            </p>
            <table style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th class="text-center">No Urut</th>
                        <th>Kode Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buktiTransaksi['detail'] as $d): ?>
                        <tr>
                            <td class="text-center"><?= $d['no_urut']; ?></td>
                            <td><code><?= esc($d['kode_produk']); ?></code></td>
                            <td class="text-center"><?= $d['jumlah']; ?></td>
                            <td class="text-right">Rp <?= number_format($d['harga'], 0, ',', '.'); ?></td>
                            <td class="text-right">Rp <?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="margin: 4px 0; font-size: 13px; color: #c53030; font-weight: bold;">
                &times; Data transaksi TIDAK DITEMUKAN / 0 baris di db_penjualan_test (Membuktikan bahwa Rollback transRollback() berhasil secara penuh, tidak ada data parsial tersisa).
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Tombol Autofill Pengujian -->
<div style="display: flex; gap: 10px; margin-bottom: 15px;">
    <button type="button" class="btn btn-green" onclick="isiContohBerhasil()">
        &#10003; Isi Contoh Data Berhasil (P001 &amp; P003)
    </button>
    <button type="button" class="btn btn-red" onclick="isiContohGagal()">
        &#10007; Isi Contoh Data Gagal (P001 &amp; P999 Non-existent)
    </button>
</div>

<!-- Form Transaksi -->
<?= form_open('transaksi/simpan', ['id' => 'formTransaksi']); ?>
<?= csrf_field(); ?>

<div style="display: flex; gap: 15px; margin-bottom: 12px;">
    <div style="flex: 1;">
        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;">Nomor Penjualan *</label>
        <input type="text" id="no_penjualan" name="no_penjualan" maxlength="15" required placeholder="Contoh: J20261099" style="width: 100%; box-sizing: border-box; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;">
    </div>
    <div style="flex: 1;">
        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;">Tanggal Penjualan *</label>
        <input type="date" id="tgl_penjualan" name="tgl_penjualan" required value="<?= date('Y-m-d'); ?>" style="width: 100%; box-sizing: border-box; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;">
    </div>
    <div style="flex: 1;">
        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;">Pelanggan *</label>
        <select id="kode_pelanggan" name="kode_pelanggan" required style="width: 100%; box-sizing: border-box; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;">
            <option value="">-- Pilih Pelanggan --</option>
            <?php foreach ($list_pelanggan as $pel): ?>
                <option value="<?= esc($pel['kode_pelanggan']); ?>">
                    <?= esc($pel['kode_pelanggan']); ?> - <?= esc($pel['nama_pelanggan']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div style="margin-bottom: 15px;">
    <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;">Keterangan</label>
    <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Simulasi transaksi penjualan CI4" style="width: 100%; box-sizing: border-box; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;">
</div>

<h4 style="margin-bottom: 8px;">Tabel Detail Barang (Minimal 2 Baris)</h4>
<table id="tabelDetail">
    <thead>
        <tr>
            <th class="text-center" style="width: 40px;">#</th>
            <th>Kode Produk (Dropdown atau Ketik Kode)</th>
            <th style="width: 120px;" class="text-center">Jumlah</th>
            <th style="width: 160px;" class="text-right">Harga Satuan (Rp)</th>
            <th style="width: 80px;" class="text-center">Aksi</th>
        </tr>
    </thead>
    <tbody id="detailBody">
        <!-- Diisi secara dinamis dengan JavaScript -->
    </tbody>
</table>

<div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
    <button type="button" class="btn btn-reset" onclick="tambahBaris()">+ Tambah Baris Detail</button>
    <div style="font-size: 13px; color: #718096;">* Server menghitung subtotal = qty * harga dan total akhir.</div>
</div>

<div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
    <button type="submit" class="btn" style="padding: 10px 22px; font-size: 14px; font-weight: bold;">
        Simpan Transaksi (Uji ACID CI4)
    </button>
</div>

<?= form_close(); ?>

<script>
const listProduk = <?= json_encode($list_produk); ?>;

function getProdukOptionsHtml(selectedKode = '') {
    let html = `<option value="">-- Pilih / Ketik Kode --</option>`;
    listProduk.forEach(p => {
        const sel = p.kode_produk === selectedKode ? 'selected' : '';
        html += `<option value="${p.kode_produk}" ${sel}>${p.kode_produk} - ${p.nama_produk} (Rp ${Number(p.harga_jual).toLocaleString('id-ID')})</option>`;
    });
    if (selectedKode && !listProduk.some(p => p.kode_produk === selectedKode)) {
        html += `<option value="${selectedKode}" selected>${selectedKode} (Produk Tidak Terdaftar - Uji Gagal)</option>`;
    }
    return html;
}

function tambahBaris(kode = '', qty = 1, hrg = 0) {
    const tbody = document.getElementById('detailBody');
    const rowCount = tbody.rows.length + 1;
    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td style="text-align:center;">${rowCount}</td>
        <td>
            <select name="detail_kode_produk[]" style="width:100%; padding:6px; font-size:13px;" required onchange="onPilihProduk(this)">
                ${getProdukOptionsHtml(kode)}
            </select>
            <input type="text" placeholder="Atau override kode manual" style="width:100%; margin-top:4px; padding:4px 6px; font-size:12px; box-sizing:border-box;" value="${kode}" oninput="onInputKodeManual(this)">
        </td>
        <td>
            <input type="number" name="detail_jumlah[]" min="1" value="${qty}" required style="width:100%; box-sizing:border-box; padding:6px; font-size:13px;">
        </td>
        <td>
            <input type="number" step="100" min="0" name="detail_harga[]" value="${hrg}" required style="width:100%; box-sizing:border-box; padding:6px; font-size:13px;">
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-red" style="padding:4px 8px; font-size:11px;" onclick="hapusBaris(this)">Hapus</button>
        </td>
    `;
    tbody.appendChild(tr);
    updateNomor();
}

function onPilihProduk(sel) {
    const val = sel.value;
    const tr = sel.closest('tr');
    const inputManual = tr.querySelector('input[type="text"]');
    inputManual.value = val;

    const prd = listProduk.find(p => p.kode_produk === val);
    if (prd) {
        tr.querySelector('input[name="detail_harga[]"]').value = prd.harga_jual;
    }
}

function onInputKodeManual(input) {
    const tr = input.closest('tr');
    const sel = tr.querySelector('select');
    sel.innerHTML = getProdukOptionsHtml(input.value);
}

function hapusBaris(btn) {
    const tbody = document.getElementById('detailBody');
    if (tbody.rows.length <= 2) {
        alert("Minimal harus ada 2 baris detail untuk memenuhi syarat pengujian transaksi!");
        return;
    }
    btn.closest('tr').remove();
    updateNomor();
}

function updateNomor() {
    const tbody = document.getElementById('detailBody');
    for (let i = 0; i < tbody.rows.length; i++) {
        tbody.rows[i].cells[0].innerText = i + 1;
    }
}

function resetFormDengan(no, tgl, pel, ket, details) {
    document.getElementById('no_penjualan').value = no;
    document.getElementById('tgl_penjualan').value = tgl;
    document.getElementById('kode_pelanggan').value = pel;
    document.getElementById('keterangan').value = ket;

    const tbody = document.getElementById('detailBody');
    tbody.innerHTML = '';
    details.forEach(d => {
        tambahBaris(d.kode, d.qty, d.harga);
    });
}

function isiContohBerhasil() {
    resetFormDengan(
        'J20261099',
        '<?= date('Y-m-d'); ?>',
        'C001',
        'Uji Transaksi CI4 Berhasil (Commit)',
        [
            { kode: 'P001', qty: 2, harga: 58000 },
            { kode: 'P003', qty: 1, harga: 6000 }
        ]
    );
}

function isiContohGagal() {
    resetFormDengan(
        'J20261098',
        '<?= date('Y-m-d'); ?>',
        'C001',
        'Uji Transaksi CI4 Error Sengaja (Rollback)',
        [
            { kode: 'P001', qty: 1, harga: 58000 },
            { kode: 'P999', qty: 1, harga: 10000 }
        ]
    );
}

window.addEventListener('DOMContentLoaded', () => {
    <?php if (empty($hasilTransaksi)): ?>
        isiContohBerhasil();
    <?php endif; ?>
});
</script>
<?= $this->endSection(); ?>
