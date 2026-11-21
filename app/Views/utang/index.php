<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/utang-index.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="kepala">
        <div>
            <h1>Catatan Hutang</h1>
            <p class="sub-text">Kelola dan pantau seluruh transaksi hutang pelanggan warung.</p>
        </div>
        <a class="tombol" href="<?= site_url('utang/tambah') ?>">+ Catat Hutang Baru</a>
    </div>

    <?php if ($pesan = session()->getFlashdata('success')): ?>
        <div class="pesan"><?= esc($pesan) ?></div>
    <?php endif ?>
    <?php if ($pesan = session()->getFlashdata('error')): ?>
        <div class="pesan error"><?= esc($pesan) ?></div>
    <?php endif ?>

    <div class="kotak">
        <form class="pencarian" method="get" action="<?= site_url('utang') ?>">
            <input name="cari" value="<?= esc($cari ?? '') ?>" placeholder="Cari nama pelanggan atau nomor transaksi..." aria-label="Cari transaksi">
            <div class="pencarian-buttons">
                <button class="tombol" type="submit">Cari</button>
                <a class="tombol tombol-kembali" href="<?= site_url('utang') ?>">Bersihkan</a>
            </div>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Nama Pelanggan</th>
                        <th style="text-align: right;">Total Hutang</th>
                        <th style="text-align: right;">Terbayar</th>
                        <th style="text-align: right;">Sisa Hutang</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($utang)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 24px; color: #6d7d72;">
                            Belum ada catatan transaksi hutang yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($utang as $u):
                        $sisa = max(0, (int)$u['total_hutang'] - (int)$u['total_bayar']);
                    ?>
                        <tr>
                            <td><?= esc(date('d-m-Y', strtotime($u['tanggal_utang']))) ?></td>
                            <td><strong><?= esc($u['nomor_transaksi']) ?></strong></td>
                            <td><?= esc($u['nama_pelanggan']) ?></td>
                            <td style="text-align: right;">Rp<?= number_format((int)$u['total_hutang'], 0, ',', '.') ?></td>
                            <td style="text-align: right;">Rp<?= number_format((int)$u['total_bayar'], 0, ',', '.') ?></td>
                            <td style="text-align: right;">
                                <strong class="<?= $sisa > 0 ? 'text-danger' : 'text-success' ?>">
                                    Rp<?= number_format($sisa, 0, ',', '.') ?>
                                </strong>
                            </td>
                            <td style="text-align: center;">
                                <?php if ((int)$u['dibatalkan'] === 1): ?>
                                    <span class="status-badge badge-dibatalkan">Dibatalkan</span>
                                <?php elseif ($sisa === 0): ?>
                                    <span class="status-badge badge-lunas">Lunas</span>
                                <?php elseif ((int)$u['total_bayar'] > 0): ?>
                                    <span class="status-badge badge-sebagian">Sebagian</span>
                                <?php else: ?>
                                    <span class="status-badge badge-belum">Belum Bayar</span>
                                <?php endif ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="aksi">
                                    <a class="tombol tombol-sm" href="<?= site_url('utang/detail/' . $u['id']) ?>">Detail</a>
                                    <?php if ((int)$u['dibatalkan'] === 0): ?>
                                        <a class="tombol tombol-ubah tombol-sm" href="<?= site_url('utang/edit/' . $u['id']) ?>">Koreksi</a>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                </tbody>
            </table>
        </div>

        <div class="utang-foot-nav">
            <a class="tombol tombol-kembali tombol-sm" href="<?= site_url('/') ?>">← Dashboard</a>
            <a class="tombol tombol-kembali tombol-sm" href="<?= site_url('pelanggan') ?>">Daftar Pelanggan</a>
        </div>
    </div>
</main>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
