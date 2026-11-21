<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;color:#333;margin:0;padding:20px}
.wadah{max-width:850px;margin:auto}.kotak{background:#fff;padding:22px;border-radius:8px;margin-top:20px}
label{display:block;font-weight:bold;margin:12px 0 6px}input,select,textarea{width:100%;box-sizing:border-box;padding:10px;border:1px solid #ccc;border-radius:5px}
.barang{border:1px solid #ddd;border-radius:8px;padding:14px;margin:12px 0}
.grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px}
.tombol{display:inline-block;background:#287a4b;color:#fff;border:0;border-radius:5px;padding:10px 14px;text-decoration:none;cursor:pointer;margin-top:12px}
.secondary{background:#777}.alert{padding:12px;background:#fbe5e5;border-radius:5px}
@media(max-width:650px){.grid{grid-template-columns:1fr 1fr}.grid .full{grid-column:1/-1}}

/* Navigasi utama Catatan Hutang */
body{padding-top:86px!important}
.app-sidebar{position:fixed;left:0;top:0;bottom:0;width:238px;background:#173b2b;color:#fff;padding:24px 16px;box-sizing:border-box;z-index:100;display:flex;flex-direction:column}
.app-brand{font-size:20px;font-weight:700;letter-spacing:.2px;margin:2px 10px 8px}
.app-subbrand{font-size:12px;color:#b8d1c3;margin:0 10px 25px}
.app-nav{display:flex;flex-direction:column;gap:6px}
.app-nav a{display:flex;align-items:center;gap:10px;padding:12px 13px;border-radius:8px;color:#eaf4ee;text-decoration:none;font-size:14px;background:transparent}
.app-nav a:hover,.app-nav a.active{background:#2d6147;color:#fff}
.app-nav a .nav-icon{width:20px;text-align:center}
.app-sidebar-foot{margin-top:auto;padding:12px 10px;color:#b8d1c3;font-size:12px}
.app-topbar{position:fixed;left:238px;right:0;top:0;height:66px;background:#fff;border-bottom:1px solid #e7ece8;z-index:90;display:flex;align-items:center;justify-content:space-between;padding:0 28px;box-sizing:border-box}
.app-topbar-title{font-weight:700;color:#244534}
.app-topbar-note{font-size:13px;color:#738178}
.wadah{margin-left:260px!important;max-width:calc(100% - 280px)!important;padding-top:6px}
@media(max-width:760px){
 body{padding:0!important;padding-top:0!important}
 .app-sidebar{position:relative;width:100%;height:auto;bottom:auto;padding:13px 14px;display:block}
 .app-brand{font-size:18px;margin:0 4px}
 .app-subbrand{margin:3px 4px 12px}
 .app-nav{flex-direction:row;overflow-x:auto;gap:5px}
 .app-nav a{white-space:nowrap;padding:10px;font-size:13px}
 .app-sidebar-foot{display:none}
 .app-topbar{position:relative;left:auto;right:auto;top:auto;height:46px;padding:0 16px}
 .app-topbar-note{font-size:11px}
 .wadah{margin:0!important;max-width:none!important;width:auto!important;padding:16px!important}
}

</style>
</head>
<body>
<aside class="app-sidebar">
<div class="app-brand">Catatan Hutang</div><div class="app-subbrand">Pembukuan warung sederhana</div>
<nav class="app-nav">
<a href="<?= site_url('/') ?>"><span class="nav-icon">⌂</span>Dashboard</a>
<a href="<?= site_url('pelanggan') ?>"><span class="nav-icon">♙</span>Pelanggan</a>
<a href="<?= site_url('utang') ?>"><span class="nav-icon">▤</span>Catatan Hutang</a>
<a href="<?= site_url('laporan') ?>"><span class="nav-icon">▥</span>Laporan</a>
</nav><div class="app-sidebar-foot">Pencatatan • Pembayaran • Piutang</div>
</aside>
<header class="app-topbar"><div class="app-topbar-title">Ringkasan &amp; Pengelolaan</div><div class="app-topbar-note">Sistem pencatatan warung</div></header><div class="wadah">
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
</body></html>
