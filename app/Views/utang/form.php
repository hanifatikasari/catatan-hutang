<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/utang-form.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="form-page-header">
        <h1><?= esc($judul) ?></h1>
        <p class="sub-text">Catat transaksi hutang baru beserta rincian barang yang diambil pelanggan.</p>
    </div>

    <div class="kotak">
        <?php if ($pesan = session()->getFlashdata('error')): ?>
            <div class="alert"><?= esc($pesan) ?></div>
        <?php endif ?>

        <form method="post" action="<?= site_url('utang/simpan') ?>">
            <?= csrf_field() ?>

            <div style="margin-bottom: 14px;">
                <label for="pelanggan_id">Nama Pelanggan *</label>
                <select id="pelanggan_id" name="pelanggan_id" required>
                    <option value="">-- Pilih pelanggan --</option>
                    <?php foreach ($pelanggan as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= old('pelanggan_id') == $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nama']) ?><?= !empty($p['no_hp']) ? ' (' . esc($p['no_hp']) . ')' : '' ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <div style="margin-top: 6px;">
                    <a href="<?= site_url('pelanggan/tambah') ?>" style="font-size: 13px; color: #236940; font-weight: 500;">
                        + Tambah data pelanggan baru
                    </a>
                </div>
            </div>

            <div class="form-dates-grid">
                <div>
                    <label for="tanggal_utang">Tanggal Hutang *</label>
                    <input type="datetime-local" id="tanggal_utang" name="tanggal_utang" value="<?= esc(old('tanggal_utang', date('Y-m-d\TH:i'))) ?>" required>
                </div>
                <div>
                    <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo (Opsional)</label>
                    <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= esc(old('tanggal_jatuh_tempo')) ?>">
                </div>
            </div>

            <div style="margin-top: 14px; margin-bottom: 20px;">
                <label for="catatan">Catatan Transaksi</label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan bila ada..."><?= esc(old('catatan')) ?></textarea>
            </div>

            <h3>Rincian Barang yang Dihutang</h3>
            <div id="daftar-barang">
                <div class="barang">
                    <div class="grid">
                        <div class="full">
                            <label>Nama Barang *</label>
                            <input name="nama_barang[]" maxlength="100" placeholder="Contoh: Beras Ramos 5 kg / Minyak Goreng 2L" required>
                        </div>
                        <div>
                            <label>Jumlah *</label>
                            <input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required>
                        </div>
                        <div>
                            <label>Satuan *</label>
                            <input name="satuan[]" maxlength="20" placeholder="kg, pcs, bks" required>
                        </div>
                        <div class="full">
                            <label>Harga Satuan (Rp) *</label>
                            <input name="harga_satuan[]" type="number" min="0" step="1" value="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin: 14px 0;">
                <button type="button" class="tombol secondary" onclick="tambahBarang()">+ Tambah Baris Barang</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="tombol">💾 Simpan Hutang</button>
                <a href="<?= site_url('utang') ?>" class="tombol secondary">Batal</a>
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
        '<div class="full"><label>Nama Barang *</label><input name="nama_barang[]" maxlength="100" placeholder="Contoh: Gula Pasir 1 kg" required></div>' +
        '<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required></div>' +
        '<div><label>Satuan *</label><input name="satuan[]" maxlength="20" placeholder="pcs/kg" required></div>' +
        '<div class="full"><label>Harga Satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="0" required></div>' +
        '</div><button type="button" class="tombol secondary btn-hapus-barang" onclick="this.closest(\'.barang\').remove()">Hapus Baris</button>';
    container.appendChild(div);
}
</script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
