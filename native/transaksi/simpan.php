<?php
/**
 * Modul Transaksi Penjualan ACID (Header + Minimal 2 Detail)
 * PHP Native (PDO) - Database: db_penjualan_test
 * 
 * Menguji commit & rollback atomik pada PostgreSQL menggunakan PDO transaction.
 */
require_once __DIR__ . '/../config/koneksi.php';

$error_db = '';
$hasil_transaksi = null;
$bukti_header = null;
$bukti_detail = [];

try {
    $pdo = getKoneksi('db_penjualan_test');

    // Ambil data pelanggan untuk dropdown
    $stmt_pelanggan = $pdo->query("SELECT kode_pelanggan, nama_pelanggan FROM pelanggan ORDER BY kode_pelanggan ASC");
    $list_pelanggan = $stmt_pelanggan->fetchAll();

    // Ambil data produk untuk referensi
    $stmt_produk = $pdo->query("SELECT kode_produk, nama_produk, harga_jual FROM produk ORDER BY kode_produk ASC");
    $list_produk = $stmt_produk->fetchAll();

    // Hitung jumlah baris awal di db_penjualan_test
    $count_penjualan_awal = (int)$pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
    $count_detail_awal    = (int)$pdo->query("SELECT COUNT(*) FROM detil_penjualan")->fetchColumn();

} catch (Exception $e) {
    $error_db = "Koneksi ke db_penjualan_test gagal: " . $e->getMessage();
}

// Proses submit transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_db)) {
    $no_penjualan   = trim($_POST['no_penjualan'] ?? '');
    $tgl_penjualan  = trim($_POST['tgl_penjualan'] ?? '');
    $kode_pelanggan = trim($_POST['kode_pelanggan'] ?? '');
    $keterangan     = trim($_POST['keterangan'] ?? '');

    $detail_produk = $_POST['detail_kode_produk'] ?? [];
    $detail_jumlah = $_POST['detail_jumlah'] ?? [];
    $detail_harga  = $_POST['detail_harga'] ?? [];

    $status_sukses = false;
    $pesan_status  = '';

    // Validasi input form
    if ($no_penjualan === '' || $tgl_penjualan === '' || $kode_pelanggan === '') {
        $pesan_status = "Nomor penjualan, tanggal penjualan, dan pelanggan wajib diisi!";
    } elseif (count($detail_produk) < 2) {
        $pesan_status = "Transaksi minimal harus memiliki 2 baris detail barang!";
    } else {
        // Hitung total dan subtotal di sisi SERVER (tidak mempercayai input total form)
        $total_hitung = 0;
        $items = [];
        for ($i = 0; $i < count($detail_produk); $i++) {
            $k_prd = trim($detail_produk[$i]);
            $qty   = (int)($detail_jumlah[$i] ?? 0);
            $hrg   = (float)($detail_harga[$i] ?? 0);
            $sub   = $qty * $hrg;
            $total_hitung += $sub;

            $items[] = [
                'no_urut'     => $i + 1,
                'kode_produk' => $k_prd,
                'jumlah'      => $qty,
                'harga'       => $hrg,
                'subtotal'    => $sub
            ];
        }

        // ==========================================
        // ALUR TRANSAKSI DATABASE (ACID)
        // ==========================================
        try {
            // 1. Mulai transaksi
            $pdo->beginTransaction();

            // 2. Insert Header Penjualan
            $sqlHeader = "INSERT INTO penjualan (no_penjualan, tgl_penjualan, kode_pelanggan, total, keterangan) 
                          VALUES (:no_penjualan, :tgl, :kode_pelanggan, :total, :keterangan)";
            $stmtHeader = $pdo->prepare($sqlHeader);
            $stmtHeader->execute([
                ':no_penjualan'   => $no_penjualan,
                ':tgl'            => $tgl_penjualan,
                ':kode_pelanggan' => $kode_pelanggan,
                ':total'          => $total_hitung,
                ':keterangan'     => $keterangan !== '' ? $keterangan : null
            ]);

            // 3. Insert Semua Detail Penjualan (no_urut berurutan mulai 1)
            $sqlDetail = "INSERT INTO detil_penjualan (no_penjualan, no_urut, kode_produk, jumlah, harga, subtotal)
                          VALUES (:no_penjualan, :no_urut, :kode_produk, :jumlah, :harga, :subtotal)";
            $stmtDetail = $pdo->prepare($sqlDetail);

            foreach ($items as $item) {
                $stmtDetail->execute([
                    ':no_penjualan' => $no_penjualan,
                    ':no_urut'      => $item['no_urut'],
                    ':kode_produk'  => $item['kode_produk'],
                    ':jumlah'       => $item['jumlah'],
                    ':harga'        => $item['harga'],
                    ':subtotal'     => $item['subtotal']
                ]);
            }

            // 4. COMMIT jika seluruh query sukses
            $pdo->commit();
            $status_sukses = true;
            $pesan_status  = "TRANSAKSI BERHASIL DICOMMIT! Data header dan " . count($items) . " detail berhasil disimpan secara atomik ke db_penjualan_test.";

        } catch (Exception $e) {
            // 5. ROLLBACK jika ada salah satu query yang gagal
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $status_sukses = false;
            $pesan_status  = "TRANSAKSI GAGAL DAN DIROLLBACK! Seluruh perubahan dibatalkan. Penyebab error: " . $e->getMessage();
        }

        // Ambil bukti data transaksi dari db_penjualan_test untuk verifikasi
        $stmtBuktiH = $pdo->prepare("SELECT * FROM penjualan WHERE no_penjualan = :no");
        $stmtBuktiH->execute([':no' => $no_penjualan]);
        $bukti_header = $stmtBuktiH->fetch();

        $stmtBuktiD = $pdo->prepare("SELECT * FROM detil_penjualan WHERE no_penjualan = :no ORDER BY no_urut ASC");
        $stmtBuktiD->execute([':no' => $no_penjualan]);
        $bukti_detail = $stmtBuktiD->fetchAll();

        $hasil_transaksi = [
            'sukses'       => $status_sukses,
            'pesan'        => $pesan_status,
            'no_penjualan' => $no_penjualan,
            'total_hitung' => $total_hitung,
        ];
    }

    // Hitung jumlah baris akhir di db_penjualan_test setelah transaksi
    $count_penjualan_akhir = (int)$pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
    $count_detail_akhir    = (int)$pdo->query("SELECT COUNT(*) FROM detil_penjualan")->fetchColumn();
} else {
    $count_penjualan_akhir = $count_penjualan_awal ?? 0;
    $count_detail_akhir    = $count_detail_awal ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Transaksi Penjualan - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 950px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .nav-link { display: inline-block; margin-bottom: 15px; color: #4a5568; text-decoration: none; font-size: 14px; }
        .nav-link:hover { text-decoration: underline; }
        h2 { margin-top: 0; color: #1a202c; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; line-height: 1.4; }
        .alert-success { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .alert-danger { background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; }
        .info-box { background: #ebf8ff; border-left: 4px solid #3182ce; padding: 12px 15px; margin-bottom: 20px; font-size: 13px; }
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        .stats-table th, .stats-table td { border: 1px solid #cbd5e0; padding: 8px 12px; text-align: center; }
        .stats-table th { background: #edf2f7; }
        .form-row { display: flex; gap: 15px; margin-bottom: 12px; }
        .form-group { flex: 1; }
        label { display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px; color: #2d3748; }
        input[type="text"], input[type="date"], input[type="number"], select, textarea { width: 100%; box-sizing: border-box; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px; }
        table.detail-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        table.detail-table th, table.detail-table td { border: 1px solid #e2e8f0; padding: 8px 10px; }
        table.detail-table th { background: #edf2f7; text-align: left; }
        .btn { padding: 8px 14px; background: #3182ce; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn:hover { background: #2b6cb0; }
        .btn-green { background: #38a169; }
        .btn-green:hover { background: #2f855a; }
        .btn-red { background: #e53e3e; }
        .btn-red:hover { background: #c53030; }
        .btn-gray { background: #718096; }
        .btn-gray:hover { background: #4a5568; }
        .btn-action-group { display: flex; gap: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <a href="../index.php" class="nav-link">&larr; Kembali ke Menu Utama</a>
    <h2>Simulasi Transaksi ACID (PHP Native PDO)</h2>
    <div class="info-box">
        <strong>Ketentuan:</strong> Fitur ini dijalankan khusus pada database <strong>db_penjualan_test</strong>.<br>
        Menguji atomisitas transaksi: Header <code>penjualan</code> + Minimal 2 baris <code>detil_penjualan</code>. Jika ada satu saja baris yang salah (misal: foreign key produk tidak valid), transaksi di-rollback seluruhnya sehingga tidak ada data sampah tersimpan.
    </div>

    <!-- Tabel Bukti Jumlah Baris Sebelum vs Sesudah -->
    <table class="stats-table">
        <thead>
            <tr>
                <th>Tabel di db_penjualan_test</th>
                <th>Jumlah Baris Sebelum</th>
                <th>Jumlah Baris Sesudah</th>
                <th>Perubahan / Status Bukti</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;"><strong>penjualan</strong> (Header)</td>
                <td><?= $count_penjualan_awal ?? '-'; ?></td>
                <td><?= $count_penjualan_akhir ?? '-'; ?></td>
                <td>
                    <?php 
                    $diff_h = ($count_penjualan_akhir ?? 0) - ($count_penjualan_awal ?? 0);
                    if ($diff_h > 0) echo "<span style='color:green;font-weight:bold;'>+{$diff_h} (Bertambah / Commit)</span>";
                    elseif (isset($hasil_transaksi)) echo "<span style='color:red;font-weight:bold;'>0 (Utuh / Rollback)</span>";
                    else echo "Siap diuji";
                    ?>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;"><strong>detil_penjualan</strong> (Detail)</td>
                <td><?= $count_detail_awal ?? '-'; ?></td>
                <td><?= $count_detail_akhir ?? '-'; ?></td>
                <td>
                    <?php 
                    $diff_d = ($count_detail_akhir ?? 0) - ($count_detail_awal ?? 0);
                    if ($diff_d > 0) echo "<span style='color:green;font-weight:bold;'>+{$diff_d} (Bertambah / Commit)</span>";
                    elseif (isset($hasil_transaksi)) echo "<span style='color:red;font-weight:bold;'>0 (Utuh / Rollback)</span>";
                    else echo "Siap diuji";
                    ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tampilkan Status Hasil Transaksi -->
    <?php if ($hasil_transaksi): ?>
        <div class="alert <?= $hasil_transaksi['sukses'] ? 'alert-success' : 'alert-danger'; ?>">
            <strong>Status: <?= $hasil_transaksi['sukses'] ? 'BERHASIL (COMMITTED)' : 'GAGAL (ROLLED BACK)'; ?></strong><br>
            <?= htmlspecialchars($hasil_transaksi['pesan']); ?>
        </div>

        <!-- Bukti Data di Database -->
        <div style="background: #fafafa; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="margin-top: 0;">Bukti Data di db_penjualan_test untuk No Penjualan: <code><?= htmlspecialchars($hasil_transaksi['no_penjualan']); ?></code></h4>
            <?php if ($bukti_header): ?>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Header Ditemukan:</strong> No: <?= htmlspecialchars($bukti_header['no_penjualan']); ?> | 
                    Tgl: <?= htmlspecialchars($bukti_header['tgl_penjualan']); ?> | 
                    Pelanggan: <?= htmlspecialchars($bukti_header['kode_pelanggan']); ?> | 
                    Total: Rp <?= number_format($bukti_header['total'], 0, ',', '.'); ?>
                </p>
                <table class="detail-table" style="margin-top: 8px;">
                    <thead>
                        <tr>
                            <th>No Urut</th>
                            <th>Kode Produk</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bukti_detail as $d): ?>
                            <tr>
                                <td><?= $d['no_urut']; ?></td>
                                <td><code><?= htmlspecialchars($d['kode_produk']); ?></code></td>
                                <td><?= $d['jumlah']; ?></td>
                                <td>Rp <?= number_format($d['harga'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin: 4px 0; font-size: 13px; color: #c53030; font-weight: bold;">
                    &times; Data transaksi TIDAK ADA / 0 baris di database db_penjualan_test (Membuktikan bahwa Rollback berhasil secara sempurna, tidak ada data parsial tersisa).
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tombol Autofill Pengujian -->
    <div class="btn-action-group">
        <button type="button" class="btn btn-green" onclick="isiContohBerhasil()">
            &#10003; Isi Contoh Data Berhasil (P001 &amp; P003)
        </button>
        <button type="button" class="btn btn-red" onclick="isiContohGagal()">
            &#10007; Isi Contoh Data Gagal (P001 &amp; P999 Non-existent)
        </button>
    </div>

    <!-- Form Input Transaksi -->
    <form method="POST" id="formTransaksi">
        <div class="form-row">
            <div class="form-group">
                <label>Nomor Penjualan *</label>
                <input type="text" id="no_penjualan" name="no_penjualan" maxlength="15" required placeholder="Contoh: J20261099">
            </div>
            <div class="form-group">
                <label>Tanggal Penjualan *</label>
                <input type="date" id="tgl_penjualan" name="tgl_penjualan" required value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label>Pelanggan *</label>
                <select id="kode_pelanggan" name="kode_pelanggan" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach ($list_pelanggan as $pel): ?>
                        <option value="<?= htmlspecialchars($pel['kode_pelanggan']); ?>">
                            <?= htmlspecialchars($pel['kode_pelanggan']); ?> - <?= htmlspecialchars($pel['nama_pelanggan']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label>Keterangan</label>
            <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Simulasi transaksi penjualan">
        </div>

        <h4 style="margin-bottom: 8px;">Tabel Detail Barang (Minimal 2 Baris)</h4>
        <table class="detail-table" id="tabelDetail">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Kode Produk (Dropdown atau Ketik Kode)</th>
                    <th style="width: 120px;">Jumlah</th>
                    <th style="width: 160px;">Harga Satuan (Rp)</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="detailBody">
                <!-- Baris detail akan diisi oleh JavaScript -->
            </tbody>
        </table>

        <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-gray" onclick="tambahBaris()">+ Tambah Baris Detail</button>
            <div style="font-size: 13px; color: #718096;">* Server akan menghitung ulang subtotal = qty * harga dan total akhir.</div>
        </div>

        <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
            <button type="submit" class="btn" style="padding: 10px 22px; font-size: 14px; font-weight: bold;">
                Simpan Transaksi (Uji ACID)
            </button>
        </div>
    </form>
</div>

<script>
// Data referensi produk dari PHP
const listProduk = <?= json_encode($list_produk); ?>;

function getProdukOptionsHtml(selectedKode = '') {
    let html = `<option value="">-- Pilih / Ketik Kode --</option>`;
    listProduk.forEach(p => {
        const sel = p.kode_produk === selectedKode ? 'selected' : '';
        html += `<option value="${p.kode_produk}" ${sel}>${p.kode_produk} - ${p.nama_produk} (Rp ${Number(p.harga_jual).toLocaleString('id-ID')})</option>`;
    });
    // Jika selectedKode tidak ada di daftar (misal P999), tetap buatkan option
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
            <select name="detail_kode_produk[]" class="select-produk" required onchange="onPilihProduk(this)">
                ${getProdukOptionsHtml(kode)}
            </select>
            <input type="text" placeholder="Atau override kode manual" style="margin-top:4px;" value="${kode}" oninput="onInputKodeManual(this)">
        </td>
        <td>
            <input type="number" name="detail_jumlah[]" min="1" value="${qty}" required>
        </td>
        <td>
            <input type="number" step="100" min="0" name="detail_harga[]" value="${hrg}" required>
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

// Inisialisasi awal 2 baris
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
        'Uji Transaksi Normal (Commit)',
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
        'Uji Transaksi Error Sengaja (Rollback)',
        [
            { kode: 'P001', qty: 1, harga: 58000 },
            { kode: 'P999', qty: 1, harga: 10000 } // P999 tidak ada di tabel produk -> picu FK error
        ]
    );
}

// Saat pertama kali load, buat 2 baris default
window.addEventListener('DOMContentLoaded', () => {
    <?php if (!$hasil_transaksi): ?>
        isiContohBerhasil();
    <?php endif; ?>
});
</script>
</body>
</html>
