<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Produk::index');

// Rute Modul Produk (CRUD)
$routes->group('produk', static function ($routes) {
    $routes->get('/', 'Produk::index');
    $routes->get('tambah', 'Produk::tambah');
    $routes->post('simpan', 'Produk::simpan');
    $routes->get('edit/(:segment)', 'Produk::edit/$1');
    $routes->post('update/(:segment)', 'Produk::update/$1');
    $routes->get('hapus/(:segment)', 'Produk::hapus/$1');
});

// Rute Modul Laporan (JOIN & Agregasi)
$routes->group('laporan', static function ($routes) {
    $routes->get('penjualan', 'Laporan::penjualan');
    $routes->get('agregasi', 'Laporan::agregasi');
    $routes->get('per-produk', 'Laporan::perProduk');
});

// Rute Modul Transaksi ACID (db_penjualan_test)
$routes->group('transaksi', static function ($routes) {
    $routes->get('/', 'Transaksi::index');
    $routes->post('simpan', 'Transaksi::simpan');
});
