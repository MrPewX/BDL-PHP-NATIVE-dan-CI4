<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    /**
     * 1. Query JOIN Penjualan dengan Pelanggan
     * Tampilkan no_penjualan, tgl_penjualan, nama_pelanggan, total
     */
    public function getPenjualan()
    {
        return $this->db->table('penjualan p')
            ->select('p.no_penjualan, p.tgl_penjualan, c.nama_pelanggan, p.total')
            ->join('pelanggan c', 'p.kode_pelanggan = c.kode_pelanggan', 'inner')
            ->orderBy('p.tgl_penjualan', 'ASC')
            ->orderBy('p.no_penjualan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 2. Agregasi Penjualan per Pelanggan
     * Menggunakan LEFT JOIN dari pelanggan ke penjualan, COUNT, COALESCE(SUM, 0), GROUP BY
     */
    public function getAgregasiPelanggan()
    {
        return $this->db->table('pelanggan c')
            ->select('c.kode_pelanggan, c.nama_pelanggan, COUNT(p.no_penjualan) AS jumlah_transaksi, COALESCE(SUM(p.total), 0) AS total_penjualan')
            ->join('penjualan p', 'c.kode_pelanggan = p.kode_pelanggan', 'left')
            ->groupBy('c.kode_pelanggan, c.nama_pelanggan')
            ->orderBy('c.kode_pelanggan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 3. Laporan Penjualan per Produk
     * Menggunakan LEFT JOIN dari produk ke detil_penjualan, COALESCE(SUM), GROUP BY
     */
    public function getLaporanPerProduk()
    {
        return $this->db->table('produk pr')
            ->select('pr.kode_produk, pr.nama_produk, COALESCE(SUM(dp.jumlah), 0) AS total_qty, COALESCE(SUM(dp.subtotal), 0) AS total_nilai')
            ->join('detil_penjualan dp', 'pr.kode_produk = dp.kode_produk', 'left')
            ->groupBy('pr.kode_produk, pr.nama_produk')
            ->orderBy('pr.kode_produk', 'ASC')
            ->get()
            ->getResultArray();
    }
}
