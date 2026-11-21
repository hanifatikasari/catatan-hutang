<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .wadah { max-width: 650px; margin: auto; }
        .kotak { background: white; padding: 25px; border-radius: 8px; margin-top: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input, textarea { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; margin-bottom: 18px; font-family: Arial, sans-serif; }
        textarea { min-height: 90px; resize: vertical; }
        .tombol { display: inline-block; padding: 11px 16px; background: #287a4b; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .tombol-kembali { background: #777; }
        .pesan-error { background: #fbe5e5; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .pesan-error ul { margin: 8px 0 0; }
    </style>
</head>
<body>
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
</body>
</html>
