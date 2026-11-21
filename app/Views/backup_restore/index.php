<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<style>
.backup-wrap{max-width:920px;margin:24px auto;padding:0 20px}.backup-card{background:#fff;border:1px solid #e6e8eb;border-radius:12px;padding:24px;margin:16px 0}
.backup-card h2{margin-top:0}.backup-warning{background:#fff4df;border:1px solid #f1d7a7;padding:14px;border-radius:8px;color:#6b4a0d}
.backup-error{background:#fde8e7;color:#8b2520;padding:12px;border-radius:8px}.backup-success{background:#e8f6ec;color:#22653b;padding:12px;border-radius:8px}
.backup-button{display:inline-block;background:#287a4b;color:#fff;border:0;border-radius:6px;padding:11px 16px;text-decoration:none;cursor:pointer;font:inherit}
.backup-button.secondary{background:#4b5563}.backup-button.danger{background:#a82f2f}input[type=password],input[type=file]{display:block;width:100%;max-width:560px;padding:11px;border:1px solid #cbd0d6;border-radius:6px;box-sizing:border-box;margin:8px 0 14px}
.backup-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.backup-note{color:#666;line-height:1.6}.backup-wrap label{font-weight:600}
</style>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'backup', 'judul' => $judul]) ?>
<main class="backup-wrap">
<h1><?= esc($judul) ?></h1>
<p>Unduh salinan database atau pulihkan database dari file backup SQL.</p>
<?php if (!empty($error)): ?><div class="backup-error"><?= esc($error) ?></div><?php endif ?>
<?php if (!empty($success)): ?><div class="backup-success"><?= esc($success) ?></div><?php endif ?>

<?php if (!$passwordConfigured): ?>
<div class="backup-warning">
<strong>Akses belum dikonfigurasi.</strong>
<p>Buat password khusus minimal 16 karakter di file <code>.env</code> proyek (jangan commit file ini):</p>
<pre>BACKUP_RESTORE_PASSWORD = "gunakan-password-panjang-yang-rahasia"</pre>
<p>Setelah itu muat ulang halaman ini.</p>
</div>
<?php elseif (!$authorized): ?>
<section class="backup-card">
<h2>Buka akses pengelolaan backup</h2>
<p class="backup-note">Password diperlukan setiap kali membuka sesi backup/restore. Akses otomatis terkunci setelah 15 menit.</p>
<form action="<?= site_url('backup-restore/unlock') ?>" method="post">
<?= csrf_field() ?>
<label for="password">Password backup</label>
<input type="password" id="password" name="password" autocomplete="current-password" required>
<button class="backup-button" type="submit">Buka Akses</button>
</form>
</section>
<?php else: ?>
<section class="backup-card">
<h2>Backup database</h2>
<p>Backup berisi struktur tabel dan data database aktif, termasuk data pelanggan, transaksi hutang, rincian barang, dan pembayaran.</p>
<div class="backup-actions">
<a class="backup-button" href="<?= site_url('backup-restore/download') ?>">Unduh Backup SQL</a>
<form action="<?= site_url('backup-restore/lock') ?>" method="post">
<?= csrf_field() ?>
<button class="backup-button secondary" type="submit">Kunci Akses</button>
</form>
</div>
</section>

<section class="backup-card">
<h2>Restore database</h2>
<div class="backup-warning"><strong>Perhatian:</strong> restore akan mengganti data tabel yang ada dengan isi backup. Pilih file backup dari aplikasi ini dan pastikan database yang dipilih benar.</div>
<p class="backup-note">Maksimal ukuran file 20 MB. Sebelum restore, aplikasi membuat backup pengaman di folder <code>writable/backups/</code> yang tidak berada di folder publik.</p>
<form action="<?= site_url('backup-restore/restore') ?>" method="post" enctype="multipart/form-data" onsubmit="return confirm('Restore akan mengganti data database saat ini. Lanjutkan hanya jika Anda yakin file backup benar.');">
<?= csrf_field() ?>
<label for="backup_file">File backup (.sql)</label>
<input type="file" id="backup_file" name="backup_file" accept=".sql,text/plain,application/sql" required>
<button class="backup-button danger" type="submit">Pulihkan Database</button>
</form>
</section>
<?php endif ?>
<p><a href="<?= site_url('/') ?>">Kembali ke beranda</a></p>
</main>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
