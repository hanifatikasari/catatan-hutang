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

    <div class="sidebar-pwa-action" style="padding: 10px 8px 0;">
        <button type="button" class="btn-install-pwa sidebar-install-btn" style="display: none;">
            📲 Install Aplikasi ke HP
        </button>
    </div>

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
        <!-- Install PWA Button on Topbar -->
        <button type="button" class="btn-install-pwa topbar-install-btn" style="display: none;" title="Pasang aplikasi di layar utama HP">
            📲 Pasang Aplikasi
        </button>

        <!-- Status Koneksi PWA (Online / Offline) -->
        <div class="status-pill-online" id="connectionStatusBadge" title="Status koneksi internet">
            <span class="status-indicator-dot online"></span>
            <span class="status-text">Online</span>
        </div>

        <!-- Antrean Transaksi Offline -->
        <span id="syncQueueBadge" class="sync-queue-badge" style="display: none;" title="Transaksi offline yang belum dikirim ke server">
            ⏳ 0 Antrean
        </span>

        <!-- Tombol Sinkronisasi Manual -->
        <button type="button" id="manualSyncBtn" class="btn-manual-sync" style="display: none;" onclick="window.SyncManager && window.SyncManager.syncPendingTransactions(true)" title="Kirim data offline ke server sekarang">
            🔄 Sinkronkan
        </button>

        <span class="store-badge">Toko Ibu Zulaikhah</span>
        <form method="post" action="<?= site_url('logout') ?>" style="display:inline-flex; margin:0;">
            <?= csrf_field() ?>
            <button type="submit" style="border:1px solid #d5e0d8; background:#fff; color:#31513d; border-radius:9px; padding:8px 12px; font:inherit; font-size:13px; font-weight:600; cursor:pointer;">Keluar</button>
        </form>
    </div>
</header>
