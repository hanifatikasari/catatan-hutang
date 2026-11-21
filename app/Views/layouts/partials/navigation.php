<?php
$active = $active ?? '';
$judul = $judul ?? 'Catatan Hutang';
?>
<div class="app-sidebar-backdrop" id="appSidebarBackdrop" aria-hidden="true"></div>

<aside class="app-sidebar" id="appSidebar" aria-label="Menu Utama">
    <div class="app-sidebar-header">
        <a class="app-brand" href="<?= site_url('/') ?>">
            <span class="app-brand-icon">📒</span>
            <span class="app-brand-text">Catatan Hutang</span>
        </a>
        <button type="button" class="app-sidebar-close" id="appSidebarClose" aria-label="Tutup Menu Navigasi" title="Tutup Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="app-subbrand">Pembukuan warung sederhana</div>

    <nav class="app-nav">
        <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('/') ?>">
            <span class="nav-icon">⌂</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a class="<?= $active === 'pelanggan' ? 'active' : '' ?>" href="<?= site_url('pelanggan') ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Pelanggan</span>
        </a>
        <a class="<?= $active === 'utang' ? 'active' : '' ?>" href="<?= site_url('utang') ?>">
            <span class="nav-icon">📝</span>
            <span class="nav-text">Catatan Hutang</span>
        </a>
        <a class="<?= $active === 'laporan' ? 'active' : '' ?>" href="<?= site_url('laporan') ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Laporan</span>
        </a>
        <a class="<?= $active === 'backup' ? 'active' : '' ?>" href="<?= site_url('backup-restore') ?>">
            <span class="nav-icon">💾</span>
            <span class="nav-text">Backup & Restore</span>
        </a>
    </nav>

    <div class="app-sidebar-foot">
        <div class="app-sidebar-foot-title">Toko Ibu Zulaikhah</div>
        <div class="app-sidebar-foot-desc">Pencatatan • Pembayaran • Piutang</div>
    </div>
</aside>

<header class="app-topbar">
    <div class="app-topbar-left">
        <button type="button" class="app-sidebar-toggle" id="appSidebarToggle" aria-label="Buka/Tutup Menu Navigasi" title="Buka/Tutup Menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <div class="app-topbar-title-group">
            <div class="app-topbar-title"><?= esc($judul) ?></div>
            <div class="app-topbar-note">Sistem pencatatan warung</div>
        </div>
    </div>
    <div class="app-topbar-right">
        <span class="store-badge">Toko Ibu Zulaikhah</span>
    </div>
</header>
