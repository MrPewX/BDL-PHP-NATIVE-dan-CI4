<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'produk';
    protected $primaryKey       = 'kode_produk';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_produk',
        'nama_produk',
        'satuan',
        'harga_jual',
        'harga_beli',
        'stok',
        'deskripsi',
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation Rules
    protected $validationRules = [
        'kode_produk' => 'required|max_length[10]',
        'nama_produk' => 'required|max_length[100]',
        'satuan'      => 'required|max_length[20]',
        'harga_jual'  => 'required|numeric|greater_than_equal_to[0]',
        'harga_beli'  => 'required|numeric|greater_than_equal_to[0]',
        'stok'        => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'kode_produk' => [
            'required'   => 'Kode produk wajib diisi.',
            'max_length' => 'Kode produk maksimal 10 karakter.',
        ],
        'nama_produk' => [
            'required'   => 'Nama produk wajib diisi.',
            'max_length' => 'Nama produk maksimal 100 karakter.',
        ],
        'satuan' => [
            'required'   => 'Satuan produk wajib diisi.',
        ],
        'harga_jual' => [
            'required'               => 'Harga jual wajib diisi.',
            'numeric'                => 'Harga jual harus berupa angka.',
            'greater_than_equal_to'  => 'Harga jual tidak boleh bernilai negatif.',
        ],
        'harga_beli' => [
            'required'               => 'Harga beli wajib diisi.',
            'numeric'                => 'Harga beli harus berupa angka.',
            'greater_than_equal_to'  => 'Harga beli tidak boleh bernilai negatif.',
        ],
        'stok' => [
            'required'               => 'Stok wajib diisi.',
            'integer'                => 'Stok harus berupa bilangan bulat.',
            'greater_than_equal_to'  => 'Stok tidak boleh bernilai negatif.',
        ],
    ];

    /**
     * Pencarian produk dengan case-insensitive ILIKE via Query Builder
     * 
     * @param string|null $q Kata kunci pencarian
     * @return array
     */
    public function cariProduk(?string $q = null)
    {
        if (!empty($q)) {
            // like('nama_produk', $q, 'both', null, true) membuat pencarian case-insensitive di PostgreSQL
            return $this->like('nama_produk', $q, 'both', null, true)
                        ->orderBy('kode_produk', 'ASC')
                        ->findAll();
        }

        return $this->orderBy('kode_produk', 'ASC')->findAll();
    }
}
