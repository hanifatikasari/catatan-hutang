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

<main class="wadah">
    <div class="form-page-header">
        <h1><?= esc($judul) ?></h1>
        <p class="sub-text">Isi data pelanggan warung untuk pencatatan transaksi hutang.</p>
    </div>

    <div class="kotak form-card">
        <?php if ($errors = session()->getFlashdata('errors')): ?>
            <div class="pesan-error">
                <strong>Periksa kembali data berikut:</strong>
                <ul style="margin: 6px 0 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <?php
        $isEdit = !empty($pelanggan['id']);
        $alamatForm = $isEdit ? 'pelanggan/perbarui/' . $pelanggan['id'] : 'pelanggan/simpan';
        ?>
        <form action="<?= site_url($alamatForm) ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nama">Nama Pelanggan *</label>
                <input type="text" id="nama" name="nama" maxlength="100" required
                       value="<?= esc(old('nama', $pelanggan['nama'])) ?>" placeholder="Contoh: Bu Siti / Mas Joko">
            </div>

            <div class="form-group">
                <label for="no_hp">Nomor HP / WhatsApp (Opsional)</label>
                <input type="tel" id="no_hp" name="no_hp" maxlength="15"
                       value="<?= esc(old('no_hp', $pelanggan['no_hp'] ?? '')) ?>" placeholder="Contoh: 081234567890">
            </div>

            <div class="form-group">
                <label for="alamat">Alamat / Lokasi Rumah (Opsional)</label>
                <textarea id="alamat" name="alamat" maxlength="255"
                          placeholder="Contoh: Gang Melati RT 03, rumah dekat mushola"><?= esc(old('alamat', $pelanggan['alamat'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label for="catatan">Keterangan / Catatan Pelanggan (Opsional)</label>
                <textarea id="catatan" name="catatan"
                          placeholder="Contoh: Biasanya belanja sore, langganan warung"><?= esc(old('catatan', $pelanggan['catatan'] ?? '')) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="tombol">💾 Simpan Data Pelanggan</button>
                <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-kembali">Kembali</a>
            </div>
        </form>
    </div>
</main>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
