<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<style>
.backup-card {
    background: #fff;
    border: 1px solid #e3ebe5;
    border-radius: 12px;
    padding: 22px;
    margin: 18px 0;
    box-shadow: 0 2px 8px rgba(20, 45, 30, 0.03);
}
.backup-card h2 {
    margin-top: 0;
    font-size: 18px;
    color: #173b2b;
}
.backup-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 14px;
}
.backup-note {
    color: #63776b;
    font-size: 13.5px;
    line-height: 1.6;
}
pre {
    background: #212c24;
    color: #e5f0e8;
    padding: 12px;
    border-radius: 6px;
    overflow-x: auto;
    font-size: 13px;
    white-space: pre-wrap;
    word-break: break-all;
}
@media (max-width: 480px) {
    .backup-card {
        padding: 16px 14px;
    }
    .backup-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .backup-actions .backup-button {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'backup', 'judul' => $judul]) ?>

<main class="backup-wrap">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 24px; color: #173b2b; margin: 0 0 6px;"><?= esc($judul) ?></h1>
        <p class="backup-note" style="margin: 0;">Unduh salinan cadangan database atau pulihkan database dari file backup SQL.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="backup-error"><?= esc($error) ?></div>
    <?php endif ?>
    <?php if (!empty($success)): ?>
        <div class="backup-success"><?= esc($success) ?></div>
    <?php endif ?>

    <?php if (!$passwordConfigured): ?>
        <div class="backup-warning">
            <div>
                <strong>Akses belum dikonfigurasi.</strong>
                <p style="margin: 6px 0;">Buat password khusus minimal 16 karakter di file <code>.env</code> proyek:</p>
                <pre>BACKUP_RESTORE_PASSWORD = "gunakan-password-panjang-yang-rahasia"</pre>
                <p style="margin: 6px 0 0;">Setelah itu muat ulang halaman ini.</p>
            </div>
        </div>
    <?php elseif (!$authorized): ?>
        <section class="backup-card">
            <h2>Buka akses pengelolaan backup</h2>
            <p class="backup-note">Password diperlukan setiap kali membuka sesi backup/restore. Akses otomatis terkunci setelah 15 menit.</p>
            <form action="<?= site_url('backup-restore/unlock') ?>" method="post">
                <?= csrf_field() ?>
                <div style="margin-bottom: 14px;">
                    <label for="password">Password backup</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan password backup..." required>
                </div>
                <button class="backup-button" type="submit">Buka Akses</button>
            </form>
        </section>
    <?php else: ?>
        <section class="backup-card">
            <h2>Backup Database</h2>
            <p class="backup-note">Backup berisi struktur tabel dan seluruh data transaksi, termasuk pelanggan, utang, rincian barang, dan pembayaran.</p>
            <div class="backup-actions">
                <a class="backup-button" href="<?= site_url('backup-restore/download') ?>">⬇️ Unduh Backup SQL</a>
                <form action="<?= site_url('backup-restore/lock') ?>" method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <button class="backup-button secondary" type="submit">Kunci Akses</button>
                </form>
            </div>
        </section>

        <section class="backup-card">
            <h2>Restore Database</h2>
            <div class="backup-warning">
                ⚠️ <strong>Perhatian:</strong> Restore akan menimpa data tabel saat ini dengan data dari file backup.
            </div>
            <p class="backup-note">Maksimal ukuran file 20 MB. Sebelum restore, aplikasi secara otomatis membuat backup pengaman di folder internal <code>writable/backups/</code>.</p>
            <form action="<?= site_url('backup-restore/restore') ?>" method="post" enctype="multipart/form-data" onsubmit="return confirm('Restore akan mengganti data database saat ini. Lanjutkan hanya jika Anda yakin file backup benar.');">
                <?= csrf_field() ?>
                <div style="margin-bottom: 14px;">
                    <label for="backup_file">File backup (.sql)</label>
                    <input type="file" id="backup_file" name="backup_file" accept=".sql,text/plain,application/sql" required>
                </div>
                <button class="backup-button danger" type="submit">Pulihkan Database Sekarang</button>
            </form>
        </section>
    <?php endif ?>

    <p style="margin-top: 24px;">
        <a href="<?= site_url('/') ?>" class="tombol tombol-kembali tombol-sm">← Kembali ke Beranda</a>
    </p>
</main>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
