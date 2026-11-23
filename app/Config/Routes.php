<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);

// Authentication routes are intentionally outside the protected group.
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate', ['filter' => 'csrf']);

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);

    $routes->group('pelanggan', static function ($routes) {
        $routes->get('/', 'Pelanggan::index');
        $routes->get('cari-ajax', 'Pelanggan::cariAjax');
        $routes->get('tambah', 'Pelanggan::tambah');
        $routes->post('simpan', 'Pelanggan::simpan');
        $routes->get('edit/(:num)', 'Pelanggan::edit/$1');
        $routes->get('hutang/(:num)', 'Pelanggan::hutang/$1');
        $routes->post('perbarui/(:num)', 'Pelanggan::perbarui/$1');
        $routes->post('nonaktifkan/(:num)', 'Pelanggan::nonaktifkan/$1');
        $routes->post('aktifkan/(:num)', 'Pelanggan::aktifkan/$1');
        $routes->post('simpan-tetap', 'Pelanggan::simpanTetap');
    });

    $routes->group('utang', static function ($routes) {
        $routes->get('/', 'Utang::index');
        $routes->get('tambah', 'Utang::tambah');
        $routes->post('simpan', 'Utang::simpan');
        $routes->get('detail/(:num)', 'Utang::detail/$1');
        $routes->get('edit/(:num)', 'Utang::edit/$1');
        $routes->post('perbarui/(:num)', 'Utang::perbarui/$1');
        $routes->post('bayar/(:num)', 'Utang::bayar/$1');
        $routes->post('batalkan/(:num)', 'Utang::batalkan/$1');
        $routes->get('data-offline', 'Utang::dataOffline');
        $routes->post('sinkronisasi-offline', 'Utang::sinkronisasiOffline', ['filter' => 'csrf']);
    });

    $routes->get('laporan', 'Laporan::index');

    $routes->get('backup-restore', 'BackupRestore::index');
    $routes->post('backup-restore/unlock', 'BackupRestore::unlock', ['filter' => 'csrf']);
    $routes->post('backup-restore/lock', 'BackupRestore::lock', ['filter' => 'csrf']);
    $routes->get('backup-restore/download', 'BackupRestore::download');
    $routes->post('backup-restore/restore', 'BackupRestore::restore', ['filter' => 'csrf']);
});
