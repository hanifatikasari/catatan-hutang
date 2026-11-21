<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/pelanggan-hutang.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => $judul]) ?>
<main class="wadah">
    <div class="page-heading">
        <div><h1>Riwayat Hutang Pelanggan</h1><p><?= esc($pelanggan['nama']) ?><?= !empty($pelanggan['no_hp']) ? ' • ' . esc($pelanggan['no_hp']) : '' ?></p></div>
        <a class="tombol tombol-kembali" href="<?= site_url('pelanggan') ?>">Kembali ke Pelanggan</a>
    </div>
    <section class="summary-card">
        <span>Total sisa hutang</span>
        <strong>Rp<?= number_format((int) $totalSisa, 0, ',', '.') ?></strong>
        <small>Transaksi yang belum lunas dan tidak dibatalkan</small>
    </section>
    <section class="table-card">
        <h2>Daftar Transaksi</h2>
        <div class="table-scroll">
        <table>
            <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Total Hutang</th><th>Dibayar</th><th>Sisa</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($utang)): ?>
                <tr><td colspan="7">Pelanggan ini belum memiliki catatan hutang.</td></tr>
            <?php else: foreach ($utang as $u): ?>
                <tr>
                    <td><?= esc(date('d-m-Y', strtotime($u['tanggal_utang']))) ?></td>
                    <td><strong><?= esc($u['nomor_transaksi']) ?></strong></td>
                    <td>Rp<?= number_format((int) $u['total_hutang'], 0, ',', '.') ?></td>
                    <td>Rp<?= number_format((int) $u['total_bayar'], 0, ',', '.') ?></td>
                    <td><strong>Rp<?= number_format((int) $u['sisa_hutang'], 0, ',', '.') ?></strong></td>
                    <td><?= (int) $u['dibatalkan'] === 1 ? 'Dibatalkan' : ((int) $u['sisa_hutang'] === 0 ? 'Lunas' : ((int) $u['total_bayar'] > 0 ? 'Sebagian dibayar' : 'Belum dibayar')) ?></td>
                    <td><a class="tombol" href="<?= site_url('utang/detail/' . $u['id']) ?>">Detail</a></td>
                </tr>
            <?php endforeach; endif ?>
            </tbody>
        </table>
        </div>
    </section>
</main>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
