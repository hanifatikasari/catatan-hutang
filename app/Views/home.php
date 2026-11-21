<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($judul) ?> - Dashboard</title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/dashboard.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'dashboard', 'judul' => $judul]) ?>


<main class="main">
<div class="eyebrow">Ikhtisar usaha</div>
<h1>Selamat datang di Catatan Hutang</h1>
<p class="intro">Pantau piutang dan kelola catatan pelanggan dalam satu tempat.</p>
<section class="cards">
<div class="card"><div class="card-label">Pelanggan Aktif</div><div class="card-value"><?= number_format((int) $pelangganAktif, 0, ',', '.') ?></div><div class="card-foot">Pelanggan yang masih aktif</div></div>
<div class="card"><div class="card-label">Total Piutang</div><div class="card-value">Rp<?= number_format((int) $totalPiutang, 0, ',', '.') ?></div><div class="card-foot">Sisa hutang yang belum lunas</div></div>
<div class="card"><div class="card-label">Transaksi Belum Lunas</div><div class="card-value"><?= number_format((int) $transaksiBelumLunas, 0, ',', '.') ?></div><div class="card-foot">Termasuk pembayaran sebagian</div></div>
<div class="card"><div class="card-label">Lewat Jatuh Tempo</div><div class="card-value"><?= number_format((int) $jatuhTempo, 0, ',', '.') ?></div><div class="card-foot">Transaksi belum lunas yang terlambat</div></div>
</section>
<h2 class="section-title">Akses cepat</h2>
<section class="actions">
<a class="action" href="<?= site_url('utang/tambah') ?>"><div class="action-icon">＋</div><h3>Catat Hutang Baru</h3><p>Masukkan transaksi dan barang yang dihutang pelanggan.</p></a>
<a class="action" href="<?= site_url('pelanggan/tambah') ?>"><div class="action-icon">♙</div><h3>Tambah Pelanggan</h3><p>Simpan data pelanggan baru atau periksa data yang sudah ada.</p></a>
<a class="action" href="<?= site_url('utang') ?>"><div class="action-icon">▤</div><h3>Kelola Hutang</h3><p>Lihat transaksi, buka detail, dan catat pembayaran.</p></a>
<a class="action" href="<?= site_url('pelanggan') ?>"><div class="action-icon">☷</div><h3>Daftar Pelanggan</h3><p>Cari pelanggan aktif maupun pelanggan tidak aktif.</p></a>
<a class="action" href="<?= site_url('laporan') ?>"><div class="action-icon">▥</div><h3>Laporan</h3><p>Periksa ringkasan transaksi dan pembayaran berdasarkan tanggal.</p></a>
</section>
<div class="note">Tips: catat pembayaran melalui halaman detail transaksi agar jumlah yang dibayar dan sisa hutang selalu tercatat dalam riwayat.</div>
</main>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>