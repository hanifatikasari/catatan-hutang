<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/utang-form.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>
<div class="wadah">
<h1><?= esc($judul) ?></h1>
<div class="kotak">
<p><strong><?= esc($utang['nama_pelanggan']) ?></strong> • <?= esc($utang['nomor_transaksi']) ?></p>
<p>Sudah dibayar: <strong>Rp<?= number_format((int)$utang['total_bayar'],0,',','.') ?></strong>. Total baru tidak boleh kurang dari jumlah pembayaran yang sudah diterima.</p>
<?php if ($pesan = session()->getFlashdata('error')): ?><div class="alert"><?= esc($pesan) ?></div><?php endif ?>
<form method="post" action="<?= site_url('utang/perbarui/'.$utang['id']) ?>">
<?= csrf_field() ?>
<div class="grid">
<div><label for="tanggal_utang">Tanggal hutang *</label><input type="datetime-local" id="tanggal_utang" name="tanggal_utang" value="<?= esc(old('tanggal_utang', date('Y-m-d\TH:i',strtotime($utang['tanggal_utang'])))) ?>" required></div>
<div><label for="tanggal_jatuh_tempo">Jatuh tempo</label><input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= esc(old('tanggal_jatuh_tempo',$utang['tanggal_jatuh_tempo']??'')) ?>"></div>
</div>
<label for="catatan">Catatan transaksi</label><textarea id="catatan" name="catatan" rows="2"><?= esc(old('catatan',$utang['catatan']??'')) ?></textarea>
<h3>Barang yang dihutang</h3>
<div id="daftar-barang">
<?php foreach ($utang['rincian'] as $r): ?>
<div class="barang">
<div class="grid">
<div class="full"><label>Nama barang *</label><input name="nama_barang[]" maxlength="100" value="<?= esc($r['nama_barang']) ?>" required></div>
<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="<?= esc($r['jumlah']) ?>" required></div>
<div><label>Satuan *</label><input name="satuan[]" maxlength="20" value="<?= esc($r['satuan']) ?>" required></div>
<div class="full"><label>Harga satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="<?= esc($r['harga_satuan']) ?>" required></div>
</div>
<button type="button" class="tombol secondary" onclick="this.closest('.barang').remove()">Hapus baris</button>
</div>
<?php endforeach ?>
</div>
<button type="button" class="tombol secondary" onclick="tambahBarang()">+ Tambah Barang</button>
<button type="submit" class="tombol" onclick="return confirm('Simpan koreksi catatan hutang ini?')">Simpan Koreksi</button>
<a href="<?= site_url('utang/detail/'.$utang['id']) ?>" class="tombol secondary">Kembali</a>
</form>
</div></div>
<script>
function tambahBarang(){
 const container=document.getElementById('daftar-barang');
 const div=document.createElement('div');div.className='barang';
 div.innerHTML='<div class="grid">'+
 '<div class="full"><label>Nama barang *</label><input name="nama_barang[]" maxlength="100" required></div>'+
 '<div><label>Jumlah *</label><input name="jumlah[]" type="number" min="0.001" step="0.001" value="1" required></div>'+
 '<div><label>Satuan *</label><input name="satuan[]" maxlength="20" required></div>'+
 '<div class="full"><label>Harga satuan (Rp) *</label><input name="harga_satuan[]" type="number" min="0" step="1" value="0" required></div>'+
 '</div><button type="button" class="tombol secondary" onclick="this.closest(\'.barang\').remove()">Hapus baris</button>';
 container.appendChild(div);
}
</script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
