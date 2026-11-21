<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('pelanggan', static function ($routes) {
    $routes->get('/', 'Pelanggan::index');
    $routes->get('tambah', 'Pelanggan::tambah');
    $routes->post('simpan', 'Pelanggan::simpan');

    $routes->get('edit/(:num)', 'Pelanggan::edit/$1');
    $routes->post('perbarui/(:num)', 'Pelanggan::perbarui/$1');

    $routes->post('nonaktifkan/(:num)', 'Pelanggan::nonaktifkan/$1');
     // Tambahan
    $routes->post(
        'aktifkan/(:num)',
        'Pelanggan::aktifkan/$1'
    );

    $routes->post(
        'simpan-tetap',
        'Pelanggan::simpanTetap'
    );
});

$routes->group('utang', static function ($routes) {
    $routes->get('/', 'Utang::index');
    $routes->get('tambah', 'Utang::tambah');
    $routes->post('simpan', 'Utang::simpan');
    $routes->get('detail/(:num)', 'Utang::detail/$1');
    $routes->post('bayar/(:num)', 'Utang::bayar/$1');
});
