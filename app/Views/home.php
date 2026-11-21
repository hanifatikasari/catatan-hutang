<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?> - Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/dashboard.css') ?>">
    <?= $this->include('layouts/partials/pwa_meta') ?>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'dashboard', 'judul' => $judul]) ?>

<main class="main">
    <div class="eyebrow">Ikhtisar Toko Ibu Zulaikhah</div>
    <h1>Selamat datang di Catatan Hutang</h1>
    <p class="intro">Pantau piutang warung, kelola data pelanggan, dan pantau pembayaran dalam satu tempat.</p>

    <section class="cards">
        <div class="card">
            <div class="card-label">Pelanggan Aktif</div>
            <div class="card-value"><?= number_format((int) $pelangganAktif, 0, ',', '.') ?></div>
            <div class="card-foot">Pelanggan yang terdaftar aktif</div>
        </div>
        <div class="card">
            <div class="card-label">Total Saldo Piutang</div>
            <div class="card-value text-danger">Rp<?= number_format((int) $totalPiutang, 0, ',', '.') ?></div>
            <div class="card-foot">Sisa hutang yang belum dilunasi</div>
        </div>
        <div class="card">
            <div class="card-label">Transaksi Belum Lunas</div>
            <div class="card-value"><?= number_format((int) $transaksiBelumLunas, 0, ',', '.') ?></div>
            <div class="card-foot">Termasuk transaksi cicilan sebagian</div>
        </div>
        <div class="card">
            <div class="card-label">Lewat Jatuh Tempo</div>
            <div class="card-value <?= (int)$jatuhTempo > 0 ? 'text-danger' : '' ?>"><?= number_format((int) $jatuhTempo, 0, ',', '.') ?></div>
            <div class="card-foot">Transaksi yang terlambat dibayar</div>
        </div>
    </section>

    <h2 class="section-title">Menu Cepat</h2>
    <section class="actions">
        <a class="action" href="<?= site_url('utang/tambah') ?>">
            <div class="action-icon">📝</div>
            <h3>Catat Hutang Baru</h3>
            <p>Masukkan transaksi hutang baru dan rincian barang belanjaan pelanggan.</p>
        </a>
        <a class="action" href="<?= site_url('utang') ?>">
            <div class="action-icon">📋</div>
            <h3>Kelola Transaksi Hutang</h3>
            <p>Lihat daftar seluruh transaksi, buka detail, dan catat pembayaran cicilan.</p>
        </a>
        <a class="action" href="<?= site_url('pelanggan/tambah') ?>">
            <div class="action-icon">➕</div>
            <h3>Tambah Pelanggan</h3>
            <p>Simpan data orang atau pelanggan baru warung.</p>
        </a>
        <a class="action" href="<?= site_url('pelanggan') ?>">
            <div class="action-icon">👥</div>
            <h3>Daftar Pelanggan</h3>
            <p>Cari data pelanggan, cek riwayat total hutang, atau kelola status aktif.</p>
        </a>
        <a class="action" href="<?= site_url('laporan') ?>">
            <div class="action-icon">📊</div>
            <h3>Laporan & Cetak Rekap</h3>
            <p>Periksa ringkasan transaksi & kas masuk per rentang tanggal dan cetak dokumen.</p>
        </a>
        <a class="action" href="<?= site_url('backup-restore') ?>">
            <div class="action-icon">💾</div>
            <h3>Backup & Restore Database</h3>
            <p>Amankan data transaksi warung dengan mengunduh salinan cadangan SQL.</p>
        </a>
    </section>

    <div class="note">
        💡 <strong>Tips Pembukuan:</strong> Catat pembayaran cicilan langsung melalui menu detail transaksi agar riwayat dan waktu pembayaran tersimpan rapi dan sisa hutang diperbarui otomatis.
    </div>
</main>

<script src="<?= base_url('js/db.js') ?>"></script>
<script src="<?= base_url('js/sync.js') ?>"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
