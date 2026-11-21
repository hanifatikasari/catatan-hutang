<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .wadah { max-width: 1000px; margin: auto; }
        .kepala { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .tombol { display: inline-block; padding: 10px 14px; background: #287a4b; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .tombol-ubah { background: #356ac3; }
        .tombol-nonaktif { background: #b33a3a; }
        .kotak { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
        input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; }
        .pencarian { display: flex; gap: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .pesan { padding: 12px; background: #e4f5e9; margin-top: 15px; border-radius: 5px; }
        .pesan-error { background: #fbe5e5; }
        .aksi { display: flex; gap: 6px; flex-wrap: wrap; }
        .status { display: flex; gap: 8px; margin-bottom: 15px; }
        @media (max-width: 650px) {
            table, tbody, tr, td { display: block; }
            thead { display: none; }
            tr { border: 1px solid #ddd; margin-bottom: 15px; padding: 10px; border-radius: 5px; }
            td { border: none; padding: 6px; }
            .pencarian { flex-direction: column; }
        }
    </style>
</head>
<body>
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
            <button type="submit" class="tombol">Cari</button>
            <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-ubah">Bersihkan</a>
        </form>

        <table>
            <thead><tr><th>Nama</th><th>Nomor HP</th><th>Alamat / Keterangan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($pelanggan)): ?>
                <tr><td colspan="4">Belum ada data pelanggan.</td></tr>
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
</body>
</html>
