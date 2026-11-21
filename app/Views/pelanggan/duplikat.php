<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nama Pelanggan Sudah Tercatat</title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <?= $this->include('layouts/partials/pwa_meta') ?>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'pelanggan', 'judul' => 'Konfirmasi Nama Pelanggan']) ?>

<main class="wadah">
    <div style="max-width: 760px;">
        <h1 style="font-size: 24px; color: #173b2b; margin: 0 0 12px;">Nama Pelanggan Sudah Pernah Disimpan</h1>
        
        <div class="backup-warning" style="margin-bottom: 20px;">
            ⚠️ Nama <strong><?= esc($pelangganBaru['nama']) ?></strong> sudah tercatat sebelumnya di database.<br>
            Periksa apakah pelanggan ini orang yang sama atau orang berbeda.
        </div>

        <h3 style="margin-bottom: 12px;">Data yang Sudah Tercatat:</h3>
        <?php foreach ($duplikat as $pelanggan): ?>
            <div class="kotak" style="margin: 12px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 16px;"><?= esc($pelanggan['nama']) ?></strong>
                    <span class="status-badge <?= (int)$pelanggan['aktif'] === 1 ? 'badge-lunas' : 'badge-dibatalkan' ?>">
                        <?= (int) $pelanggan['aktif'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                    </span>
                </div>
                <p style="margin: 4px 0; font-size: 14px;"><strong>No. HP:</strong> <?= esc($pelanggan['no_hp'] ?? '-') ?></p>
                <p style="margin: 4px 0 12px; font-size: 14px;"><strong>Alamat:</strong> <?= esc($pelanggan['alamat'] ?? '-') ?></p>

                <?php if ((int) $pelanggan['aktif'] === 0): ?>
                    <form action="<?= site_url('pelanggan/aktifkan/' . $pelanggan['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="tombol">Aktifkan Kembali Pelanggan Ini</button>
                    </form>
                <?php else: ?>
                    <p style="margin: 0; font-size: 13.5px; color: #4b6354; background: #eaf3ed; padding: 10px; border-radius: 6px;">
                        Pelanggan ini sudah aktif. Jika orangnya sama, Anda dapat langsung mencatat hutang menggunakan pelanggan ini.
                    </p>
                <?php endif ?>
            </div>
        <?php endforeach ?>

        <hr style="border: 0; border-top: 1px solid #dce5df; margin: 28px 0 20px;">

        <div class="kotak">
            <h3 style="margin-top: 0; color: #173b2b;">Ini Orang Berbeda? Simpan sebagai Pelanggan Baru</h3>
            <p style="color: #63776b; font-size: 13.5px; margin-bottom: 18px;">
                Gunakan nama pembeda, misalnya <em>Pak Joko 2</em>, <em>Pak Joko RT 02</em>, atau <em>Pak Joko dekat masjid</em>.
            </p>

            <form action="<?= site_url('pelanggan/simpan-tetap') ?>" method="post">
                <?= csrf_field() ?>

                <div style="margin-bottom: 14px;">
                    <label for="nama_tetap">Nama Pelanggan Pembeda *</label>
                    <input type="text" id="nama_tetap" name="nama" value="<?= esc($pelangganBaru['nama']) ?>" maxlength="100" required>
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="no_hp_tetap">No. HP / WhatsApp</label>
                    <input type="text" id="no_hp_tetap" name="no_hp" value="<?= esc($pelangganBaru['no_hp']) ?>" maxlength="15">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="alamat_tetap">Alamat / Lokasi</label>
                    <textarea id="alamat_tetap" name="alamat"><?= esc($pelangganBaru['alamat']) ?></textarea>
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="catatan_tetap">Catatan</label>
                    <textarea id="catatan_tetap" name="catatan"><?= esc($pelangganBaru['catatan']) ?></textarea>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" class="tombol">Simpan Sebagai Pelanggan Baru</button>
                    <a href="<?= site_url('pelanggan/tambah') ?>" class="tombol tombol-kembali">Batal / Kembali</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="<?= base_url('js/db.js') ?>"></script>
<script src="<?= base_url('js/sync.js') ?>"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
