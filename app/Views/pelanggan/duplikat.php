<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nama Pelanggan Sudah Tercatat</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 30px auto; padding: 15px; }
        .kotak { border: 1px solid #ddd; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .peringatan { background: #fff3cd; border: 1px solid #ffe69c; padding: 15px; border-radius: 8px; }
        input, textarea, button { width: 100%; padding: 10px; margin-top: 8px; box-sizing: border-box; }
        button { cursor: pointer; }
    </style>
</head>
<body>
<h2>Nama pelanggan sudah tercatat</h2>
<div class="peringatan">
    Nama <strong><?= esc($pelangganBaru['nama']) ?></strong> pernah disimpan sebelumnya.<br>
    Periksa apakah ini pelanggan yang sama atau orang berbeda.
</div>

<h3>Data yang sudah tercatat</h3>
<?php foreach ($duplikat as $pelanggan): ?>
    <div class="kotak">
        <strong><?= esc($pelanggan['nama']) ?></strong>
        <p>Status: <?= (int) $pelanggan['aktif'] === 1 ? 'Aktif' : 'Tidak Aktif' ?></p>
        <p>No. HP: <?= esc($pelanggan['no_hp'] ?? '-') ?></p>
        <p>Alamat: <?= esc($pelanggan['alamat'] ?? '-') ?></p>

        <?php if ((int) $pelanggan['aktif'] === 0): ?>
            <form action="<?= site_url('pelanggan/aktifkan/' . $pelanggan['id']) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit">Aktifkan Kembali Pelanggan Ini</button>
            </form>
        <?php else: ?>
            <p>Pelanggan ini sudah aktif. Jika orangnya sama, gunakan data yang sudah ada.</p>
        <?php endif ?>
    </div>
<?php endforeach ?>

<hr>
<h3>Ini orang berbeda? Tambahkan pelanggan baru</h3>
<p>Gunakan nama pembeda, misalnya Pak Joko 2, Pak Joko RT 02, atau Pak Joko dekat toko.</p>

<form action="<?= site_url('pelanggan/simpan-tetap') ?>" method="post">
    <?= csrf_field() ?>

    <label>Nama pelanggan</label>
    <input type="text" name="nama" value="<?= esc($pelangganBaru['nama']) ?>" maxlength="100" required>

    <label>No. HP</label>
    <input type="text" name="no_hp" value="<?= esc($pelangganBaru['no_hp']) ?>" maxlength="15">

    <label>Alamat</label>
    <textarea name="alamat"><?= esc($pelangganBaru['alamat']) ?></textarea>

    <label>Catatan</label>
    <textarea name="catatan"><?= esc($pelangganBaru['catatan']) ?></textarea>

    <button type="submit">Tambahkan sebagai Pelanggan Baru</button>
</form>
<p><a href="<?= site_url('pelanggan/tambah') ?>">Kembali</a></p>
</body>
</html>
