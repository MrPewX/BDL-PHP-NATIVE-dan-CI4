<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'CodeIgniter 4 - Praktikum Basis Data Lanjut'); ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; margin: 0; padding: 20px; background: #f7f9fc; color: #333; }
        .container { max-width: 1050px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .navbar { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px; }
        .navbar .brand { font-size: 18px; font-weight: bold; color: #c53030; text-decoration: none; }
        .navbar .nav-links { display: flex; gap: 15px; }
        .navbar .nav-links a { color: #4a5568; text-decoration: none; font-size: 14px; font-weight: 500; }
        .navbar .nav-links a:hover, .navbar .nav-links a.active { color: #3182ce; text-decoration: underline; }
        h2 { margin-top: 0; color: #1a202c; }
        .alert { padding: 10px 14px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .alert-danger { background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; }
        .btn { display: inline-block; padding: 7px 12px; background: #3182ce; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .btn:hover { background: #2b6cb0; }
        .btn-edit { background: #d69e2e; }
        .btn-edit:hover { background: #b7791f; }
        .btn-hapus { background: #e53e3e; }
        .btn-hapus:hover { background: #c53030; }
        .btn-reset { background: #718096; }
        .btn-reset:hover { background: #4a5568; }
        .btn-green { background: #38a169; }
        .btn-green:hover { background: #2f855a; }
        .btn-red { background: #e53e3e; }
        .btn-red:hover { background: #c53030; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #e2e8f0; padding: 9px 12px; text-align: left; }
        th { background: #edf2f7; color: #2d3748; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot tr { background: #edf2f7; font-weight: bold; }
        .nav-tabs { display: flex; gap: 8px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .tab-btn { padding: 6px 12px; font-size: 13px; text-decoration: none; color: #4a5568; border-radius: 4px; }
        .tab-btn.active { background: #3182ce; color: #fff; font-weight: bold; }
        .tab-btn:hover:not(.active) { background: #edf2f7; }
        footer { margin-top: 30px; text-align: center; font-size: 12px; color: #a0aec0; border-top: 1px solid #edf2f7; padding-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <a href="<?= base_url(); ?>" class="brand">&#9889; CI4 Praktikum Basis Data Lanjut</a>
        <div class="nav-links">
            <a href="<?= base_url('produk'); ?>">1. CRUD Produk</a>
            <a href="<?= base_url('laporan/penjualan'); ?>">2. JOIN Penjualan</a>
            <a href="<?= base_url('laporan/agregasi'); ?>">3. Agregasi Pelanggan</a>
            <a href="<?= base_url('laporan/per-produk'); ?>">4. Laporan Produk</a>
            <a href="<?= base_url('transaksi'); ?>" style="color: #dd6b20; font-weight: bold;">5. Transaksi ACID</a>
        </div>
    </div>

    <!-- Alert Flashdata -->
    <?php if (session()->getFlashdata('sukses')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('sukses'); ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
    <?php endif; ?>

    <!-- Render Section Content -->
    <?= $this->renderSection('content'); ?>

    <footer>
        Praktikum Basis Data Lanjut &bull; Perbandingan PHP Native (PDO) vs CodeIgniter 4
    </footer>
</div>
</body>
</html>
