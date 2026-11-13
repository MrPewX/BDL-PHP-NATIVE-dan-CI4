<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use Exception;

class Produk extends BaseController
{
    protected $produkModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');
        $data = [
            'title'   => 'Daftar Produk (CodeIgniter 4)',
            'q'       => $keyword,
            'produk'  => $this->produkModel->cariProduk($keyword),
        ];

        return view('produk/index', $data);
    }

    public function tambah()
    {
        $data = [
            'title'      => 'Tambah Produk Baru',
            'validation' => session()->getFlashdata('errors') ?? [],
        ];

        return view('produk/tambah', $data);
    }

    public function simpan()
    {
        $data = [
            'kode_produk' => trim($this->request->getPost('kode_produk') ?? ''),
            'nama_produk' => trim($this->request->getPost('nama_produk') ?? ''),
            'satuan'      => trim($this->request->getPost('satuan') ?? ''),
            'harga_jual'  => $this->request->getPost('harga_jual'),
            'harga_beli'  => $this->request->getPost('harga_beli'),
            'stok'        => $this->request->getPost('stok'),
            'deskripsi'   => trim($this->request->getPost('deskripsi') ?? '') ?: null,
        ];

        // Validasi input
        if (!$this->produkModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->produkModel->errors());
        }

        // Cek duplikasi primary key kode_produk
        if ($this->produkModel->find($data['kode_produk'])) {
            return redirect()->back()->withInput()->with('error', "Kode produk '{$data['kode_produk']}' sudah ada di database!");
        }

        try {
            $this->produkModel->insert($data);
            return redirect()->to(base_url('produk'))->with('sukses', "Produk [{$data['kode_produk']}] berhasil ditambahkan.");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', "Gagal menyimpan produk: " . $e->getMessage());
        }
    }

    public function edit($kode)
    {
        $produk = $this->produkModel->find($kode);
        if (!$produk) {
            return redirect()->to(base_url('produk'))->with('error', "Produk dengan kode '{$kode}' tidak ditemukan.");
        }

        $data = [
            'title'  => 'Ubah Data Produk',
            'produk' => $produk,
            'errors' => session()->getFlashdata('errors') ?? [],
        ];

        return view('produk/edit', $data);
    }

    public function update($kode)
    {
        $produk = $this->produkModel->find($kode);
        if (!$produk) {
            return redirect()->to(base_url('produk'))->with('error', "Produk tidak ditemukan.");
        }

        $data = [
            'nama_produk' => trim($this->request->getPost('nama_produk') ?? ''),
            'satuan'      => trim($this->request->getPost('satuan') ?? ''),
            'harga_jual'  => $this->request->getPost('harga_jual'),
            'harga_beli'  => $this->request->getPost('harga_beli'),
            'stok'        => $this->request->getPost('stok'),
            'deskripsi'   => trim($this->request->getPost('deskripsi') ?? '') ?: null,
        ];

        // Validasi tanpa cek ulang kode_produk karena kode_produk tidak diubah
        $rules = [
            'nama_produk' => 'required|max_length[100]',
            'satuan'      => 'required|max_length[20]',
            'harga_jual'  => 'required|numeric|greater_than_equal_to[0]',
            'harga_beli'  => 'required|numeric|greater_than_equal_to[0]',
            'stok'        => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->produkModel->update($kode, $data);
            return redirect()->to(base_url('produk'))->with('sukses', "Data produk [{$kode}] berhasil diperbarui.");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', "Gagal memperbarui data: " . $e->getMessage());
        }
    }

    public function hapus($kode)
    {
        try {
            $deleted = $this->produkModel->delete($kode);
            if ($deleted) {
                return redirect()->to(base_url('produk'))->with('sukses', "Produk [{$kode}] berhasil dihapus.");
            }
            return redirect()->to(base_url('produk'))->with('error', "Produk [{$kode}] tidak ditemukan.");

        } catch (Exception $e) {
            // Tangkap Foreign Key Constraint Violation (PostgreSQL 23503)
            $msg = $e->getMessage();
            if (strpos($msg, '23503') !== false || strpos($msg, 'foreign key') !== false || strpos($msg, 'violates foreign key constraint') !== false) {
                $pesanRamah = "Produk [{$kode}] TIDAK DAPAT DIHAPUS karena sudah digunakan pada data transaksi penjualan atau pembelian.";
            } else {
                $pesanRamah = "Gagal menghapus produk: Terjadi kesalahan basis data.";
            }

            return redirect()->to(base_url('produk'))->with('error', $pesanRamah);
        }
    }
}
