<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/pelanggan-index.css') ?>">
    <?= $this->include('layouts/partials/pwa_meta') ?>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="kepala">
        <div>
            <h1>Daftar Pelanggan</h1>
            <p class="sub-text">Catatan orang yang memiliki transaksi hutang warung.</p>
        </div>
        <div class="kepala-actions">
            <div class="status-pills">
                <a href="<?= site_url('pelanggan?status=aktif') ?>" class="tombol-pill <?= ($status ?? '') !== 'tidak_aktif' ? 'active' : '' ?>">Pelanggan Aktif</a>
                <a href="<?= site_url('pelanggan?status=tidak_aktif') ?>" class="tombol-pill <?= ($status ?? '') === 'tidak_aktif' ? 'active' : '' ?>">Tidak Aktif</a>
            </div>
            <a href="<?= site_url('pelanggan/tambah') ?>" class="tombol">+ Tambah Pelanggan</a>
        </div>
    </div>

    <?php if ($pesan = session()->getFlashdata('success')): ?>
        <div class="pesan"><?= esc($pesan) ?></div>
    <?php endif ?>
    <?php if ($pesan = session()->getFlashdata('error')): ?>
        <div class="pesan pesan-error"><?= esc($pesan) ?></div>
    <?php endif ?>

    <div class="kotak">
        <form action="<?= site_url('pelanggan') ?>" method="get" class="pencarian">
            <input type="hidden" name="status" value="<?= esc($status) ?>">
            <input type="text" name="cari" value="<?= esc($cari ?? '') ?>" placeholder="Cari nama, nomor HP, atau alamat..." aria-label="Cari pelanggan">
            <div class="pencarian-buttons">
                <button type="submit" class="tombol">Cari</button>
                <a href="<?= site_url('pelanggan?status=' . esc($status)) ?>" class="tombol tombol-kembali">Bersihkan</a>
            </div>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Nomor HP / WA</th>
                        <th>Alamat & Keterangan</th>
                        <th style="text-align: right;">Sisa Hutang</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pelanggan)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px; color: #6d7d72;">
                            Belum ada data pelanggan yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pelanggan as $p): ?>
                        <tr>
                            <td>
                                <strong><?= esc($p['nama']) ?></strong>
                                <?php if ((int)($p['aktif'] ?? 1) === 0): ?>
                                    <span class="status-badge badge-dibatalkan" style="margin-left: 4px;">Nonaktif</span>
                                <?php endif ?>
                            </td>
                            <td><?= esc($p['no_hp'] ?? '-') ?></td>
                            <td>
                                <?= esc($p['alamat'] ?? '-') ?>
                                <?php if (!empty($p['catatan'])): ?>
                                    <br><small style="color: #63776b;"><?= esc($p['catatan']) ?></small>
                                <?php endif ?>
                            </td>
                            <td style="text-align: right;">
                                <strong class="<?= (int)($p['total_sisa_hutang'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                    Rp<?= number_format((int) ($p['total_sisa_hutang'] ?? 0), 0, ',', '.') ?>
                                </strong>
                                <br>
                                <a href="<?= site_url('pelanggan/hutang/' . $p['id']) ?>" style="font-size: 12px; color: #236940; text-decoration: underline;">
                                    Riwayat hutang
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <div class="aksi">
                                    <a href="<?= site_url('pelanggan/edit/' . $p['id']) ?>" class="tombol tombol-ubah tombol-sm">Ubah</a>
                                    <?php if ((int) $p['aktif'] === 1): ?>
                                        <form action="<?= site_url('pelanggan/nonaktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Jadikan pelanggan ini tidak aktif?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="tombol tombol-nonaktif tombol-sm">Nonaktifkan</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= site_url('pelanggan/aktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Aktifkan kembali pelanggan ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="tombol tombol-sm">Aktifkan</button>
                                        </form>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="<?= base_url('js/db.js') ?>"></script>
<script src="<?= base_url('js/sync.js') ?>"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
