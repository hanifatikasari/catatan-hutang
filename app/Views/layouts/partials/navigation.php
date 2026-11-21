<?php
$active = $active ?? '';
$judul = $judul ?? 'Catatan Hutang';
?>
<aside class="app-sidebar">
    <a class="app-brand" href="<?= site_url('/') ?>">Catatan Hutang</a>
    <div class="app-subbrand">Pembukuan warung sederhana</div>
    <nav class="app-nav">
        <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('/') ?>"><span class="nav-icon">⌂</span>Dashboard</a>
        <a class="<?= $active === 'pelanggan' ? 'active' : '' ?>" href="<?= site_url('pelanggan') ?>"><span class="nav-icon">♙</span>Pelanggan</a>
        <a class="<?= $active === 'utang' ? 'active' : '' ?>" href="<?= site_url('utang') ?>"><span class="nav-icon">▤</span>Catatan Hutang</a>
        <a class="<?= $active === 'laporan' ? 'active' : '' ?>" href="<?= site_url('laporan') ?>"><span class="nav-icon">▥</span>Laporan</a>
    </nav>
    <div class="app-sidebar-foot">Pencatatan • Pembayaran • Piutang</div>
</aside>
<header class="app-topbar">
    <div class="app-topbar-title"><?= esc($judul) ?></div>
    <div class="app-topbar-note">Sistem pencatatan warung</div>
</header>
