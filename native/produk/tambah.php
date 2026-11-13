<?php
/**
 * Modul Produk - Tambah Produk Baru
 * PHP Native (PDO) - Database: db_penjualan
 */
require_once __DIR__ . '/../config/koneksi.php';

$pdo = getKoneksi('db_penjualan');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_produk = trim($_POST['kode_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $satuan      = trim($_POST['satuan'] ?? '');
    $harga_jual  = trim($_POST['harga_jual'] ?? '0');
    $harga_beli  = trim($_POST['harga_beli'] ?? '0');
    $stok        = trim($_POST['stok'] ?? '0');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    // 1. Validasi input
    if ($kode_produk === '' || $nama_produk === '') {
        $error = 'Kode produk dan nama produk wajib diisi!';
    } elseif ($satuan === '') {
        $error = 'Satuan produk wajib diisi!';
    } elseif (!is_numeric($harga_jual) || (float)$harga_jual < 0) {
        $error = 'Harga jual harus berupa angka >= 0!';
    } elseif (!is_numeric($harga_beli) || (float)$harga_beli < 0) {
        $error = 'Harga beli harus berupa angka >= 0!';
    } elseif (!is_numeric($stok) || (int)$stok < 0) {
        $error = 'Stok harus berupa bilangan bulat >= 0!';
    } else {
        // 2. Cek apakah kode_produk sudah terdaftar
        $cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kode_produk = :kode");
        $cek->execute([':kode' => $kode_produk]);
        if ($cek->fetchColumn() > 0) {
            $error = "Kode produk '{$kode_produk}' sudah terdaftar! Gunakan kode lain.";
        } else {
            // 3. Simpan dengan Prepared Statement
            try {
                $sql = "INSERT INTO produk (kode_produk, nama_produk, satuan, harga_jual, harga_beli, stok, deskripsi) 
                        VALUES (:kode, :nama, :satuan, :harga_jual, :harga_beli, :stok, :deskripsi)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':kode'       => $kode_produk,
                    ':nama'       => $nama_produk,
                    ':satuan'     => $satuan,
                    ':harga_jual' => (float)$harga_jual,
                    ':harga_beli' => (float)$harga_beli,
                    ':stok'       => (int)$stok,
                    ':deskripsi'  => $deskripsi !== '' ? $deskripsi : null,
                ]);

                header("Location: index.php?sukses=" . urlencode("Produk [{$kode_produk}] berhasil ditambahkan."));
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan produk: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .nav-link { display: inline-block; margin-bottom: 15px; color: #4a5568; text-decoration: none; font-size: 14px; }
        .nav-link:hover { text-decoration: underline; }
        h2 { margin-top: 0; color: #1a202c; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #2d3748; }
        input[type="text"], input[type="number"], textarea { width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px; }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        .btn { padding: 9px 16px; background: #3182ce; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #2b6cb0; }
        .btn-batal { background: #718096; margin-left: 8px; text-decoration: none; display: inline-block; }
        .btn-batal:hover { background: #4a5568; }
        .alert-danger { padding: 10px 14px; background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="nav-link">&larr; Kembali ke Daftar Produk</a>
    <h2>Tambah Produk Baru</h2>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kode Produk * (Manual, contoh: P006)</label>
            <input type="text" name="kode_produk" maxlength="10" required value="<?= htmlspecialchars($_POST['kode_produk'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Nama Produk *</label>
            <input type="text" name="nama_produk" maxlength="100" required value="<?= htmlspecialchars($_POST['nama_produk'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Satuan * (contoh: buah, rim, botol)</label>
            <input type="text" name="satuan" maxlength="20" required value="<?= htmlspecialchars($_POST['satuan'] ?? ''); ?>">
        </div>

        <div class="row">
            <div class="form-group">
                <label>Harga Jual (Rp) *</label>
                <input type="number" step="100" min="0" name="harga_jual" required value="<?= htmlspecialchars($_POST['harga_jual'] ?? '0'); ?>">
            </div>
            <div class="form-group">
                <label>Harga Beli (Rp) *</label>
                <input type="number" step="100" min="0" name="harga_beli" required value="<?= htmlspecialchars($_POST['harga_beli'] ?? '0'); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Stok Awal *</label>
            <input type="number" min="0" name="stok" required value="<?= htmlspecialchars($_POST['stok'] ?? '0'); ?>">
        </div>

        <div class="form-group">
            <label>Deskripsi (Opsional)</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn">Simpan Produk</button>
            <a href="index.php" class="btn btn-batal">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
