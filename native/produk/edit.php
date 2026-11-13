<?php
/**
 * Modul Produk - Ubah Produk
 * PHP Native (PDO) - Database: db_penjualan
 */
require_once __DIR__ . '/../config/koneksi.php';

$pdo  = getKoneksi('db_penjualan');
$kode = trim($_GET['kode'] ?? '');
$error = '';

if ($kode === '') {
    header("Location: index.php");
    exit;
}

// Ambil data produk berdasarkan kode_produk
$stmt = $pdo->prepare("SELECT * FROM produk WHERE kode_produk = :kode");
$stmt->execute([':kode' => $kode]);
$produk = $stmt->fetch();

if (!$produk) {
    header("Location: index.php?error=" . urlencode("Produk dengan kode '{$kode}' tidak ditemukan."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $satuan      = trim($_POST['satuan'] ?? '');
    $harga_jual  = trim($_POST['harga_jual'] ?? '0');
    $harga_beli  = trim($_POST['harga_beli'] ?? '0');
    $stok        = trim($_POST['stok'] ?? '0');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    // Validasi
    if ($nama_produk === '') {
        $error = 'Nama produk wajib diisi!';
    } elseif ($satuan === '') {
        $error = 'Satuan produk wajib diisi!';
    } elseif (!is_numeric($harga_jual) || (float)$harga_jual < 0) {
        $error = 'Harga jual harus berupa angka >= 0!';
    } elseif (!is_numeric($harga_beli) || (float)$harga_beli < 0) {
        $error = 'Harga beli harus berupa angka >= 0!';
    } elseif (!is_numeric($stok) || (int)$stok < 0) {
        $error = 'Stok harus berupa bilangan bulat >= 0!';
    } else {
        try {
            // Update data (kode_produk TIDAK BOLEH diubah)
            $sql = "UPDATE produk SET 
                        nama_produk = :nama,
                        satuan      = :satuan,
                        harga_jual  = :harga_jual,
                        harga_beli  = :harga_beli,
                        stok        = :stok,
                        deskripsi   = :deskripsi
                    WHERE kode_produk = :kode";
            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute([
                ':nama'       => $nama_produk,
                ':satuan'     => $satuan,
                ':harga_jual' => (float)$harga_jual,
                ':harga_beli' => (float)$harga_beli,
                ':stok'       => (int)$stok,
                ':deskripsi'  => $deskripsi !== '' ? $deskripsi : null,
                ':kode'       => $kode,
            ]);

            header("Location: index.php?sukses=" . urlencode("Data produk [{$kode}] berhasil diperbarui."));
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui produk: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Produk - PHP Native</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .nav-link { display: inline-block; margin-bottom: 15px; color: #4a5568; text-decoration: none; font-size: 14px; }
        .nav-link:hover { text-decoration: underline; }
        h2 { margin-top: 0; color: #1a202c; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #2d3748; }
        input[type="text"], input[type="number"], textarea { width: 100%; box-sizing: border-box; padding: 8px 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px; }
        input[readonly] { background-color: #edf2f7; cursor: not-allowed; color: #4a5568; }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        .btn { padding: 9px 16px; background: #d69e2e; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #b7791f; }
        .btn-batal { background: #718096; margin-left: 8px; text-decoration: none; display: inline-block; }
        .btn-batal:hover { background: #4a5568; }
        .alert-danger { padding: 10px 14px; background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .note { font-size: 12px; color: #718096; margin-top: 4px; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="nav-link">&larr; Kembali ke Daftar Produk</a>
    <h2>Ubah Data Produk</h2>

    <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kode Produk (Primary Key - Tidak dapat diubah)</label>
            <input type="text" value="<?= htmlspecialchars($produk['kode_produk']); ?>" readonly>
            <div class="note">Kode produk tidak boleh diubah karena merupakan identitas primary key.</div>
        </div>

        <div class="form-group">
            <label>Nama Produk *</label>
            <input type="text" name="nama_produk" maxlength="100" required value="<?= htmlspecialchars($_POST['nama_produk'] ?? $produk['nama_produk']); ?>">
        </div>

        <div class="form-group">
            <label>Satuan *</label>
            <input type="text" name="satuan" maxlength="20" required value="<?= htmlspecialchars($_POST['satuan'] ?? $produk['satuan']); ?>">
        </div>

        <div class="row">
            <div class="form-group">
                <label>Harga Jual (Rp) *</label>
                <input type="number" step="100" min="0" name="harga_jual" required value="<?= htmlspecialchars($_POST['harga_jual'] ?? $produk['harga_jual']); ?>">
            </div>
            <div class="form-group">
                <label>Harga Beli (Rp) *</label>
                <input type="number" step="100" min="0" name="harga_beli" required value="<?= htmlspecialchars($_POST['harga_beli'] ?? $produk['harga_beli']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Stok *</label>
            <input type="number" min="0" name="stok" required value="<?= htmlspecialchars($_POST['stok'] ?? $produk['stok']); ?>">
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? $produk['deskripsi'] ?? ''); ?></textarea>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-batal">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
