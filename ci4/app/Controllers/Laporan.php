<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LaporanModel;

class Laporan extends BaseController
{
    protected $laporanModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        helper(['url']);
    }

    /**
     * 1. Laporan JOIN Penjualan dan Pelanggan
     */
    public function penjualan()
    {
        $data = [
            'title'   => 'Laporan Penjualan (Query JOIN) - CodeIgniter 4',
            'laporan' => $this->laporanModel->getPenjualan(),
        ];

        return view('laporan/penjualan', $data);
    }

    /**
     * 2. Laporan Agregasi per Pelanggan
     */
    public function agregasi()
    {
        $data = [
            'title'   => 'Laporan Agregasi Penjualan per Pelanggan - CodeIgniter 4',
            'laporan' => $this->laporanModel->getAgregasiPelanggan(),
        ];

        return view('laporan/agregasi', $data);
    }

    /**
     * 3. Laporan Penjualan per Produk
     */
    public function perProduk()
    {
        $data = [
            'title'   => 'Laporan Penjualan per Produk - CodeIgniter 4',
            'laporan' => $this->laporanModel->getLaporanPerProduk(),
        ];

        return view('laporan/per_produk', $data);
    }
}
