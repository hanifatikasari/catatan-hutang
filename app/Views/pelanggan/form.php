<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/pelanggan-form.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => $judul]) ?>


<div class="wadah">
    <h1><?= esc($judul) ?></h1>
    <div class="kotak">
        <?php if ($errors = session()->getFlashdata('errors')): ?>
            <div class="pesan-error">
                <strong>Periksa kembali data berikut:</strong>
                <ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul>
            </div>
        <?php endif ?>

        <?php
        $isEdit = !empty($pelanggan['id']);
        $alamatForm = $isEdit ? 'pelanggan/perbarui/' . $pelanggan['id'] : 'pelanggan/simpan';
        ?>
        <form action="<?= site_url($alamatForm) ?>" method="post">
            <?= csrf_field() ?>

            <label for="nama">Nama pelanggan *</label>
            <input type="text" id="nama" name="nama" maxlength="100" required
                   value="<?= esc(old('nama', $pelanggan['nama'])) ?>" placeholder="Contoh: Bu Siti">

            <label for="no_hp">Nomor HP / WhatsApp</label>
            <input type="tel" id="no_hp" name="no_hp" maxlength="15"
                   value="<?= esc(old('no_hp', $pelanggan['no_hp'] ?? '')) ?>" placeholder="Contoh: 081234567890">

            <label for="alamat">Alamat / Lokasi rumah</label>
            <textarea id="alamat" name="alamat" maxlength="255"
                      placeholder="Contoh: Gang Melati, rumah dekat mushola"><?= esc(old('alamat', $pelanggan['alamat'] ?? '')) ?></textarea>

            <label for="catatan">Keterangan pelanggan</label>
            <textarea id="catatan" name="catatan"
                      placeholder="Contoh: Anak Pak Budi, biasanya belanja sore"><?= esc(old('catatan', $pelanggan['catatan'] ?? '')) ?></textarea>

            <button type="submit" class="tombol">Simpan</button>
            <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-kembali">Kembali</a>
        </form>
    </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
