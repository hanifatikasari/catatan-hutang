<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/utang-form.css ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<div class="wadah">
<h1><?= esc($judul) ?></h1>
<div class="kotak">
<?php if ($pesan = session()->getFlashdata('error')): ?><div class="alert"><?= esc($pesan) ?></div><?php endif ?>
<form method="post" action="<?= site_url('utang/simpan') ?>">
<?= csrf_field() ?>
<label for="pelanggan_id">Nama pelanggan *</label>
<select id="pelanggan_id" name="pelanggan_id" required>
<option value="">-- Pilih pelanggan --</option>
<?php foreach ($pelanggan as $p): ?>
<option value="<?= $p['id'] ?>" <?= old('pelanggan_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['nama']) ?><?= !empty($p['no_hp']) ? ' - ' . esc($p['no_hp']) : '' ?></option>
<?php endforeach ?>
</select>
<p><a href="<?= site_url('pelanggan/tambah') ?>">+ Tambah pelanggan baru</a></p>

<div class="grid">
<div><label for="tanggal_utang">Tanggal hutang *</label><input type="datetime-local" id="tanggal_utang" name="tanggal_utang" value="<?= esc(old('tanggal_utang', date('Y-m-d\TH:i'))) ?>" required></div>
<div><label for="tanggal_jatuh_tempo">Jatuh tempo</label><input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= esc(old('tanggal_jatuh_tempo')) ?>"></div>
</div>
<label for="catatan">Catatan transaksi</label><textarea id="catatan" name="catatan" rows="2"><?= esc(old('catatan')) ?></textarea>

<h3>Barang yang dihutang</h3>
<div id="daftar-barang">
<div class="barang">
<div class="grid">
<div class="full"><label>Nama barang *</label><input name="nama_barang[]" maxlength="100" placeholder="Contoh: Beras 1 kg" required></div>
<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required></div>
<div><label>Satuan *</label><input name="satuan[]" maxlength="20" placeholder="pcs/kg" required></div>
<div class="full"><label>Harga satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="0" required></div>
</div>
</div>
</div>
<button type="button" class="tombol secondary" onclick="tambahBarang()">+ Tambah Barang</button>
<button type="submit" class="tombol">Simpan Hutang</button>
<a href="<?= site_url('utang') ?>" class="tombol secondary">Batal</a>
</form>
</div></div>
<script>
function tambahBarang(){
 const container=document.getElementById('daftar-barang');
 const div=document.createElement('div');
 div.className='barang';
 div.innerHTML='<div class="grid">'+
 '<div class="full"><label>Nama barang *</label><input name="nama_barang[]" maxlength="100" required></div>'+
 '<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required></div>'+
 '<div><label>Satuan *</label><input name="satuan[]" maxlength="20" placeholder="pcs/kg" required></div>'+
 '<div class="full"><label>Harga satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="0" required></div>'+
 '</div><button type="button" class="tombol secondary" onclick="this.closest(\'.barang\').remove()">Hapus baris</button>';
 container.appendChild(div);
}
</script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
