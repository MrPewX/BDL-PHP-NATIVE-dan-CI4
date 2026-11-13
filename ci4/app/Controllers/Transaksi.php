<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransaksiModel;

class Transaksi extends BaseController
{
    protected $transaksiModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Hitung baris awal di db_penjualan_test
        $statsAwal = $this->transaksiModel->hitungBarisTest();

        $data = [
            'title'           => 'Simulasi Transaksi ACID (CodeIgniter 4)',
            'list_pelanggan'  => $this->transaksiModel->getPelangganTest(),
            'list_produk'     => $this->transaksiModel->getProdukTest(),
            'statsAwal'       => $statsAwal,
            'hasilTransaksi'  => session()->getFlashdata('hasilTransaksi'),
            'buktiTransaksi'  => session()->getFlashdata('buktiTransaksi'),
            'statsAkhir'      => session()->getFlashdata('statsAkhir') ?? $statsAwal,
        ];

        return view('transaksi/index', $data);
    }

    public function simpan()
    {
        $header = [
            'no_penjualan'   => trim($this->request->getPost('no_penjualan') ?? ''),
            'tgl_penjualan'  => trim($this->request->getPost('tgl_penjualan') ?? ''),
            'kode_pelanggan' => trim($this->request->getPost('kode_pelanggan') ?? ''),
            'keterangan'     => trim($this->request->getPost('keterangan') ?? ''),
        ];

        $detailProduk = $this->request->getPost('detail_kode_produk') ?? [];
        $detailJumlah = $this->request->getPost('detail_jumlah') ?? [];
        $detailHarga  = $this->request->getPost('detail_harga') ?? [];

        // Validasi
        if (empty($header['no_penjualan']) || empty($header['tgl_penjualan']) || empty($header['kode_pelanggan'])) {
            return redirect()->to(base_url('transaksi'))->with('error', 'Nomor penjualan, tanggal penjualan, dan pelanggan wajib diisi!');
        }

        if (count($detailProduk) < 2) {
            return redirect()->to(base_url('transaksi'))->with('error', 'Transaksi minimal harus memiliki 2 baris detail!');
        }

        $items = [];
        for ($i = 0; $i < count($detailProduk); $i++) {
            $items[] = [
                'kode_produk' => $detailProduk[$i],
                'jumlah'      => $detailJumlah[$i],
                'harga'       => $detailHarga[$i],
            ];
        }

        // Jalankan transaksi ACID di db_penjualan_test
        $hasil = $this->transaksiModel->simpanTransaksi($header, $items);

        // Ambil bukti data di db_penjualan_test
        $bukti = $this->transaksiModel->getBuktiTransaksi($header['no_penjualan']);

        // Ambil jumlah baris terkini
        $statsAkhir = $this->transaksiModel->hitungBarisTest();

        return redirect()->to(base_url('transaksi'))
            ->with('hasilTransaksi', $hasil)
            ->with('buktiTransaksi', $bukti)
            ->with('statsAkhir', $statsAkhir);
    }
}
