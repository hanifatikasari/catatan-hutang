<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/utang-form.css') ?>">
    <?= $this->include('layouts/partials/pwa_meta') ?>
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="form-page-header">
        <h1><?= esc($judul) ?></h1>
        <p class="sub-text">Koreksi transaksi hutang pelanggan jika ada kekeliruan pencatatan.</p>
    </div>

    <div class="kotak">
        <div style="background: #f7faf8; border: 1px solid #e1ebe5; padding: 14px 16px; border-radius: 8px; margin-bottom: 18px;">
            <p style="margin: 0 0 4px; font-size: 15px;">Pelanggan: <strong><?= esc($utang['nama_pelanggan']) ?></strong> • No. Transaksi: <strong><?= esc($utang['nomor_transaksi']) ?></strong></p>
            <p style="margin: 0; font-size: 13.5px; color: #526659;">
                Sudah dibayar: <strong>Rp<?= number_format((int)$utang['total_bayar'], 0, ',', '.') ?></strong>. Total baru tidak boleh lebih kecil dari jumlah pembayaran yang sudah diterima.
            </p>
        </div>

        <?php if ($pesan = session()->getFlashdata('error')): ?>
            <div class="alert"><?= esc($pesan) ?></div>
        <?php endif ?>

        <form method="post" action="<?= site_url('utang/perbarui/' . $utang['id']) ?>">
            <?= csrf_field() ?>

            <div class="form-dates-grid">
                <div>
                    <label for="tanggal_utang">Tanggal Hutang *</label>
                    <input type="datetime-local" id="tanggal_utang" name="tanggal_utang" value="<?= esc(old('tanggal_utang', date('Y-m-d\TH:i', strtotime($utang['tanggal_utang'])))) ?>" required>
                </div>
                <div>
                    <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
                    <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= esc(old('tanggal_jatuh_tempo', $utang['tanggal_jatuh_tempo'] ?? '')) ?>">
                </div>
            </div>

            <div style="margin: 14px 0 20px;">
                <label for="catatan">Catatan Transaksi</label>
                <textarea id="catatan" name="catatan" rows="2"><?= esc(old('catatan', $utang['catatan'] ?? '')) ?></textarea>
            </div>

            <h3>Barang yang Dihutang</h3>
            <div id="daftar-barang">
                <?php foreach ($utang['rincian'] as $r): ?>
                    <div class="barang">
                        <div class="grid">
                            <div class="full">
                                <label>Nama Barang *</label>
                                <input name="nama_barang[]" maxlength="100" value="<?= esc($r['nama_barang']) ?>" required>
                            </div>
                            <div>
                                <label>Jumlah *</label>
                                <input name="jumlah[]" type="number" min="0.001" step="0.001" value="<?= esc($r['jumlah']) ?>" required>
                            </div>
                            <div>
                                <label>Satuan *</label>
                                <input name="satuan[]" maxlength="20" value="<?= esc($r['satuan']) ?>" required>
                            </div>
                            <div class="full">
                                <label>Harga Satuan (Rp) *</label>
                                <input name="harga_satuan[]" type="number" min="0" step="1" value="<?= esc($r['harga_satuan']) ?>" required>
                            </div>
                        </div>
                        <button type="button" class="tombol secondary btn-hapus-barang" onclick="this.closest('.barang').remove()">Hapus Baris</button>
                    </div>
                <?php endforeach ?>
            </div>

            <div style="margin: 14px 0;">
                <button type="button" class="tombol secondary" onclick="tambahBarang()">+ Tambah Baris Barang</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="tombol" onclick="return confirm('Simpan koreksi catatan hutang ini?')">💾 Simpan Koreksi</button>
                <a href="<?= site_url('utang/detail/' . $utang['id']) ?>" class="tombol secondary">Kembali</a>
            </div>
        </form>
    </div>
</main>

<script>
function tambahBarang() {
    const container = document.getElementById('daftar-barang');
    const div = document.createElement('div');
    div.className = 'barang';
    div.innerHTML = '<div class="grid">' +
        '<div class="full"><label>Nama Barang *</label><input name="nama_barang[]" maxlength="100" required></div>' +
        '<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required></div>' +
        '<div><label>Satuan *</label><input name="satuan[]" maxlength="20" required></div>' +
        '<div class="full"><label>Harga Satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="0" required></div>' +
        '</div><button type="button" class="tombol secondary btn-hapus-barang" onclick="this.closest(\'.barang\').remove()">Hapus Baris</button>';
    container.appendChild(div);
}
</script>
<script src="<?= base_url('js/db.js') ?>"></script>
<script src="<?= base_url('js/sync.js') ?>"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
