<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;color:#333;margin:0;padding:20px}.wadah{max-width:1100px;margin:auto}
.kotak{background:#fff;padding:20px;border-radius:8px;margin:16px 0}.filter{display:flex;gap:12px;align-items:end;flex-wrap:wrap}
label{display:block;margin-bottom:6px;font-weight:bold}input{padding:10px;border:1px solid #ccc;border-radius:5px}
.tombol{display:inline-block;background:#287a4b;color:#fff;border:0;border-radius:5px;padding:10px 14px;text-decoration:none;cursor:pointer}
.ringkasan{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}.angka{font-size:22px;font-weight:bold;margin-top:8px}
table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}
@media(max-width:700px){table,tbody,tr,td{display:block}thead{display:none}tr{border:1px solid #ddd;margin-bottom:12px;padding:8px}td{border:0}}

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

</style></head><body>
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
<?php if ($pesan = session()->getFlashdata('error')): ?><p style="background:#fbe5e5;padding:12px"><?= esc($pesan) ?></p><?php endif ?>
<div class="kotak">
<form class="filter" method="get" action="<?= site_url('laporan') ?>">
<div><label for="mulai">Dari tanggal</label><input type="date" id="mulai" name="mulai" value="<?= esc($mulai) ?>" required></div>
<div><label for="sampai">Sampai tanggal</label><input type="date" id="sampai" name="sampai" value="<?= esc($sampai) ?>" required></div>
<button class="tombol" type="submit">Tampilkan</button>
</form>
</div>
<div class="ringkasan">
<div class="kotak"><div>Total Hutang Baru</div><div class="angka">Rp<?= number_format($totalHutang,0,',','.') ?></div><small>Transaksi dibuat pada rentang tanggal</small></div>
<div class="kotak"><div>Pembayaran Masuk</div><div class="angka">Rp<?= number_format($pembayaranPeriode,0,',','.') ?></div><small>Pembayaran diterima pada rentang tanggal</small></div>
<div class="kotak"><div>Total Pembayaran dari Hutang Periode</div><div class="angka">Rp<?= number_format($totalPembayaranUntukUtang,0,',','.') ?></div><small>Termasuk pembayaran sebelum / sesudah rentang</small></div>
<div class="kotak"><div>Sisa Piutang Hutang Periode</div><div class="angka">Rp<?= number_format($sisaPiutang,0,',','.') ?></div><small>Sisa saat ini dari transaksi dalam rentang</small></div>
</div>
<div class="kotak"><h2>Rincian Transaksi Hutang</h2>
<table><thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Pelanggan</th><th>Total Hutang</th><th>Total Dibayar</th><th>Sisa</th><th></th></tr></thead><tbody>
<?php if (empty($utang)): ?><tr><td colspan="7">Tidak ada transaksi pada rentang ini.</td></tr>
<?php else: foreach ($utang as $u): ?><tr>
<td><?= esc(date('d-m-Y',strtotime($u['tanggal_utang']))) ?></td><td><?= esc($u['nomor_transaksi']) ?></td><td><?= esc($u['nama_pelanggan']) ?></td>
<td>Rp<?= number_format((int)$u['total_hutang'],0,',','.') ?></td><td>Rp<?= number_format((int)$u['total_bayar'],0,',','.') ?></td><td>Rp<?= number_format((int)$u['sisa_hutang'],0,',','.') ?></td>
<td><a class="tombol" href="<?= site_url('utang/detail/'.$u['id']) ?>">Detail</a></td>
</tr><?php endforeach; endif ?>
</tbody></table>
<p><a class="tombol" href="<?= site_url('/') ?>">Beranda</a></p>
</div></div></body></html>