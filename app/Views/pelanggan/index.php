<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/pelanggan-index.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => $judul]) ?>


<div class="wadah">
    <div class="kepala">
        <div><h1>Daftar Pelanggan</h1><p>Catatan orang yang memiliki transaksi utang.</p></div>
        <div class="status">
            <a href="<?= site_url('pelanggan?status=aktif') ?>">Pelanggan Aktif</a> |
            <a href="<?= site_url('pelanggan?status=tidak_aktif') ?>">Pelanggan Tidak Aktif</a>
        </div>
        <a href="<?= site_url('pelanggan/tambah') ?>" class="tombol">+ Tambah Pelanggan</a>
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
            <input type="text" name="cari" value="<?= esc($cari ?? '') ?>" placeholder="Cari nama, nomor HP, atau alamat" aria-label="Cari pelanggan">
            <button type="submit" class="tombol">Cari</button>
            <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-ubah">Bersihkan</a>
        </form>

        <table>
            <thead><tr><th>Nama</th><th>Nomor HP</th><th>Alamat / Keterangan</th><th>Sisa Hutang</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($pelanggan)): ?>
                <tr><td colspan="5">Belum ada data pelanggan.</td></tr>
            <?php else: ?>
                <?php foreach ($pelanggan as $p): ?>
                    <tr>
                        <td><strong><?= esc($p['nama']) ?></strong></td>
                        <td><?= esc($p['no_hp'] ?? '-') ?></td>
                        <td>
                            <?= esc($p['alamat'] ?? '-') ?>
                            <?php if (!empty($p['catatan'])): ?><br><small><?= esc($p['catatan']) ?></small><?php endif ?>
                        </td>
                        <td>
                            <strong>Rp<?= number_format((int) ($p['total_sisa_hutang'] ?? 0), 0, ',', '.') ?></strong>
                            <?php if ((int) ($p['total_sisa_hutang'] ?? 0) > 0): ?>
                                <br><a href="<?= site_url('pelanggan/hutang/' . $p['id']) ?>">Rincian hutang</a>
                            <?php endif ?>
                        </td>
                        <td>
                            <div class="aksi">
                                <a href="<?= site_url('pelanggan/edit/' . $p['id']) ?>" class="tombol tombol-ubah">Ubah</a>
                                <?php if ((int) $p['aktif'] === 1): ?>
                                    <form action="<?= site_url('pelanggan/nonaktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Jadikan pelanggan ini tidak aktif?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="tombol tombol-nonaktif">Nonaktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= site_url('pelanggan/aktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Aktifkan kembali pelanggan ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="tombol">Aktifkan Kembali</button>
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
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
