-- ====================================================
-- Skema Database: db_penjualan
-- Mata Kuliah: Basis Data Lanjut
-- ====================================================

CREATE DATABASE IF NOT EXISTS `db_penjualan`;
USE `db_penjualan`;

-- 1. Tabel Produk
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_produk` VARCHAR(50) NOT NULL UNIQUE,
  `nama_produk` VARCHAR(150) NOT NULL,
  `harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `stok` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Pelanggan
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pelanggan` VARCHAR(50) NOT NULL UNIQUE,
  `nama_pelanggan` VARCHAR(100) NOT NULL,
  `telepon` VARCHAR(20) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Transaksi (Header)
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
  `no_transaksi` VARCHAR(50) NOT NULL UNIQUE,
  `id_pelanggan` INT DEFAULT NULL,
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `total_bayar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id_detail` INT AUTO_INCREMENT PRIMARY KEY,
  `id_transaksi` INT NOT NULL,
  `id_produk` INT NOT NULL,
  `jumlah` INT NOT NULL,
  `harga_satuan` DECIMAL(12,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================================
-- Data Sampel / Seed Data
-- ====================================================

-- Data Produk
INSERT INTO `produk` (`kode_produk`, `nama_produk`, `harga`, `stok`) VALUES
('PRD001', 'Laptop ASUS Vivobook', 8500000.00, 10),
('PRD002', 'Mouse Wireless Logitech', 175000.00, 25),
('PRD003', 'Keyboard Mechanical RGB', 450000.00, 15),
('PRD004', 'Monitor LED LG 24 Inch', 1650000.00, 8),
('PRD005', 'Flashdisk Sandisk 64GB', 85000.00, 30)
ON DUPLICATE KEY UPDATE `nama_produk`=VALUES(`nama_produk`);

-- Data Pelanggan
INSERT INTO `pelanggan` (`kode_pelanggan`, `nama_pelanggan`, `telepon`, `alamat`) VALUES
('CUST001', 'Ahmad Fawaid', '081234567891', 'Surabaya'),
('CUST002', 'Budi Santoso', '081234567892', 'Sidoarjo'),
('CUST003', 'Citra Dewi', '081234567893', 'Gresik')
ON DUPLICATE KEY UPDATE `nama_pelanggan`=VALUES(`nama_pelanggan`);

-- Data Transaksi
INSERT INTO `transaksi` (`id_transaksi`, `no_transaksi`, `id_pelanggan`, `tanggal`, `total_bayar`) VALUES
(1, 'TRX-20261101-001', 1, '2026-11-01 10:15:00', 8850000.00),
(2, 'TRX-20261102-002', 2, '2026-11-02 14:30:00', 625000.00)
ON DUPLICATE KEY UPDATE `no_transaksi`=VALUES(`no_transaksi`);

-- Data Detail Transaksi
INSERT INTO `detail_transaksi` (`id_transaksi`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 8500000.00, 8500000.00),
(1, 2, 2, 175000.00, 350000.00),
(2, 2, 1, 175000.00, 175000.00),
(2, 3, 1, 450000.00, 450000.00)
ON DUPLICATE KEY UPDATE `jumlah`=VALUES(`jumlah`);
