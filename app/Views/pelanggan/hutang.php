<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/pelanggan-hutang.css') ?>">
    <?= $this->include('layouts/partials/pwa_meta') ?>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="page-heading">
        <div>
            <h1>Riwayat Hutang Pelanggan</h1>
            <p class="customer-sub"><?= esc($pelanggan['nama']) ?><?= !empty($pelanggan['no_hp']) ? ' • ' . esc($pelanggan['no_hp']) : '' ?></p>
        </div>
        <a class="tombol tombol-kembali" href="<?= site_url('pelanggan') ?>">← Kembali ke Pelanggan</a>
    </div>

    <section class="summary-card">
        <span class="sum-card-label">Total Sisa Hutang Saat Ini</span>
        <strong class="sum-card-amount <?= (int)$totalSisa > 0 ? 'text-danger' : 'text-success' ?>">
            Rp<?= number_format((int) $totalSisa, 0, ',', '.') ?>
        </strong>
        <small class="sum-card-desc">Akumulasi transaksi yang belum lunas dan tidak dibatalkan</small>
    </section>

    <section class="kotak table-card">
        <h2 style="font-size: 17px; margin-top: 0; margin-bottom: 14px;">Daftar Transaksi Hutang</h2>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th style="text-align: right;">Total Hutang</th>
                        <th style="text-align: right;">Dibayar</th>
                        <th style="text-align: right;">Sisa</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($utang)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: #6d7d72;">
                            Pelanggan ini belum memiliki riwayat catatan hutang.
                        </td>
                    </tr>
                <?php else: foreach ($utang as $u): ?>
                    <tr>
                        <td><?= esc(date('d-m-Y', strtotime($u['tanggal_utang']))) ?></td>
                        <td><strong><?= esc($u['nomor_transaksi']) ?></strong></td>
                        <td style="text-align: right;">Rp<?= number_format((int) $u['total_hutang'], 0, ',', '.') ?></td>
                        <td style="text-align: right;">Rp<?= number_format((int) $u['total_bayar'], 0, ',', '.') ?></td>
                        <td style="text-align: right;">
                            <strong class="<?= (int)$u['sisa_hutang'] > 0 ? 'text-danger' : 'text-success' ?>">
                                Rp<?= number_format((int) $u['sisa_hutang'], 0, ',', '.') ?>
                            </strong>
                        </td>
                        <td style="text-align: center;">
                            <?php if ((int)$u['dibatalkan'] === 1): ?>
                                <span class="status-badge badge-dibatalkan">Dibatalkan</span>
                            <?php elseif ((int)$u['sisa_hutang'] === 0): ?>
                                <span class="status-badge badge-lunas">Lunas</span>
                            <?php elseif ((int)$u['total_bayar'] > 0): ?>
                                <span class="status-badge badge-sebagian">Sebagian</span>
                            <?php else: ?>
                                <span class="status-badge badge-belum">Belum Bayar</span>
                            <?php endif ?>
                        </td>
                        <td style="text-align: right;">
                            <a class="tombol tombol-sm" href="<?= site_url('utang/detail/' . $u['id']) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; endif ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="<?= base_url('js/db.js') ?>"></script>
<script src="<?= base_url('js/sync.js') ?>"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
