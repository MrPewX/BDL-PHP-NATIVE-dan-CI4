<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;
use Exception;

class TransaksiModel extends Model
{
    protected $dbTest;

    public function __construct()
    {
        parent::__construct();
        // Terhubung ke database uji transaksi 'db_penjualan_test'
        $this->dbTest = Database::connect('test');
    }

    /**
     * Mengambil daftar pelanggan dari db_penjualan_test
     */
    public function getPelangganTest()
    {
        return $this->dbTest->table('pelanggan')
            ->select('kode_pelanggan, nama_pelanggan')
            ->orderBy('kode_pelanggan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil daftar produk dari db_penjualan_test
     */
    public function getProdukTest()
    {
        return $this->dbTest->table('produk')
            ->select('kode_produk, nama_produk, harga_jual')
            ->orderBy('kode_produk', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Menghitung jumlah baris tabel penjualan dan detil_penjualan di db_penjualan_test
     */
    public function hitungBarisTest()
    {
        $penjualan = $this->dbTest->table('penjualan')->countAllResults();
        $detail    = $this->dbTest->table('detil_penjualan')->countAllResults();

        return [
            'penjualan'       => $penjualan,
            'detil_penjualan' => $detail,
        ];
    }

    /**
     * Mengambil bukti transaksi dari db_penjualan_test
     */
    public function getBuktiTransaksi(9router)
    {
        $header = $this->dbTest->table('penjualan')
            ->where('no_penjualan', $noPenjualan)
            ->get()
            ->getRowArray();

        $detail = $this->dbTest->table('detil_penjualan')
            ->where('no_penjualan', $noPenjualan)
            ->orderBy('no_urut', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'header' => $header,
            'detail' => $detail,
        ];
    }

    /**
     * Eksekusi transaksi penjualan ACID (transBegin, transCommit, transRollback)
     * 
     * @param array $header Data header (no_penjualan, tgl_penjualan, kode_pelanggan, keterangan)
     * @param array $items Array baris detail (kode_produk, jumlah, harga)
     * @return array Status dan pesan hasil transaksi
     */
    public function simpanTransaksi(array $header, array $items)
    {
        // Hitung total di sisi SERVER
        $total = 0;
        $detailSiapSimpan = [];

        foreach ($items as $idx => $item) {
            $qty = (int)$item['jumlah'];
            $hrg = (float)$item['harga'];
            $sub = $qty * $hrg;
            $total += $sub;

            $detailSiapSimpan[] = [
                'no_penjualan' => $header['no_penjualan'],
                'no_urut'      => $idx + 1, // no_urut berurutan mulai 1
                'kode_produk'  => trim($item['kode_produk']),
                'jumlah'       => $qty,
                'harga'        => $hrg,
                'subtotal'     => $sub,
            ];
        }

        // ==========================================
        // ALUR TRANSAKSI DATABASE (CodeIgniter 4)
        // ==========================================
        $this->dbTest->transBegin();

        try {
            // 1. Insert Header Penjualan
            $this->dbTest->table('penjualan')->insert([
                'no_penjualan'   => $header['no_penjualan'],
                'tgl_penjualan'  => $header['tgl_penjualan'],
                'kode_pelanggan' => $header['kode_pelanggan'],
                'total'          => $total,
                'keterangan'     => !empty($header['keterangan']) ? $header['keterangan'] : null,
            ]);

            // 2. Insert Setiap Baris Detil Penjualan   
            foreach ($detailSiapSimpan as $detil) {
                $this->dbTest->table('detil_penjualan')->insert($detil);
            }

            // 3. Cek status transaksi: jika ada query error, lemparkan exception
            if ($this->dbTest->transStatus() === false) {
                $error = $this->dbTest->error();
                throw new Exception($error['message'] ?? 'Kegagalan query pada transaksi.');
            }

            // 4. COMMIT jika seluruh operasi sukses
            $this->dbTest->transCommit();

            return [
                'sukses'       => true,
                'pesan'        => "TRANSAKSI BERHASIL DICOMMIT! Header dan " . count($detailSiapSimpan) . " detail berhasil disimpan secara atomik ke db_penjualan_test.",
                'no_penjualan' => $header['no_penjualan'],
                'total'        => $total,
            ];

        } catch (Exception $e) {
            // 5. ROLLBACK jika terjadi kesalahan
            $this->dbTest->transRollback();

            return [
                'sukses'       => false,
                'pesan'        => "TRANSAKSI GAGAL DAN DIROLLBACK! Data dibatalkan. Penyebab error: " . $e->getMessage(),
                'no_penjualan' => $header['no_penjualan'],
                'total'        => $total,
            ];
        }
    }
}
9