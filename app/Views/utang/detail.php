<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($judul) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;color:#333;margin:0;padding:20px}.wadah{max-width:900px;margin:auto}.kotak{background:#fff;padding:20px;border-radius:8px;margin:16px 0}
table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}
.tombol{display:inline-block;background:#287a4b;color:white;text-decoration:none;padding:10px 14px;border-radius:5px}

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
<h1>Detail Hutang</h1>
<?php if ($pesan = session()->getFlashdata('success')): ?><p style="background:#e4f5e9;padding:12px;border-radius:5px"><?= esc($pesan) ?></p><?php endif ?>
<?php if ($pesan = session()->getFlashdata('error')): ?><p style="background:#fbe5e5;padding:12px;border-radius:5px"><?= esc($pesan) ?></p><?php endif ?>
<div class="kotak">
<h2><?= esc($utang['nama_pelanggan']) ?></h2>
<p>Nomor transaksi: <strong><?= esc($utang['nomor_transaksi']) ?></strong></p>
<p>Tanggal hutang: <?= esc(date('d-m-Y H:i', strtotime($utang['tanggal_utang']))) ?></p>
<p>Jatuh tempo: <?= !empty($utang['tanggal_jatuh_tempo']) ? esc(date('d-m-Y', strtotime($utang['tanggal_jatuh_tempo']))) : '-' ?></p>
<p>No. HP: <?= esc($utang['no_hp'] ?? '-') ?></p>
<p>Alamat: <?= esc($utang['alamat'] ?? '-') ?></p>
<?php if (!empty($utang['catatan'])): ?><p>Catatan: <?= esc($utang['catatan']) ?></p><?php endif ?>
</div>
<div class="kotak"><h3>Rincian Barang</h3>
<table><thead><tr><th>Barang</th><th>Jumlah</th><th>Harga Satuan</th><th>Total</th></tr></thead><tbody>
<?php foreach ($rincian as $r): ?><tr>
<td><?= esc($r['nama_barang']) ?></td><td><?= esc(rtrim(rtrim(number_format((float)$r['jumlah'], 3, ',', '.'), '0'), ',')) ?> <?= esc($r['satuan']) ?></td>
<td>Rp<?= number_format((int)$r['harga_satuan'], 0, ',', '.') ?></td><td>Rp<?= number_format((int)$r['total_harga'], 0, ',', '.') ?></td>
</tr><?php endforeach ?>
</tbody></table>
<h3>Total Hutang: Rp<?= number_format((int)$utang['total_hutang'], 0, ',', '.') ?></h3>
<h3>Total Dibayar: Rp<?= number_format((int)$utang['total_bayar'], 0, ',', '.') ?></h3>
<h2>Sisa Hutang: Rp<?= number_format((int)$utang['sisa_hutang'], 0, ',', '.') ?></h2>
<p>Status: <strong><?= (int)$utang['dibatalkan'] === 1 ? 'Dibatalkan' : ((int)$utang['sisa_hutang'] === 0 ? 'Lunas' : ((int)$utang['total_bayar'] > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar')) ?></strong></p>
</div>
<div class="kotak"><h3>Riwayat Pembayaran</h3>
<?php if (empty($pembayaran)): ?><p>Belum ada pembayaran yang dicatat.</p><?php else: ?>
<table><thead><tr><th>Tanggal</th><th>Jumlah</th><th>Cara</th><th>Catatan</th></tr></thead><tbody>
<?php foreach ($pembayaran as $b): ?><tr><td><?= esc(date('d-m-Y H:i', strtotime($b['tanggal_bayar']))) ?></td><td>Rp<?= number_format((int)$b['jumlah_bayar'],0,',','.') ?></td><td><?= esc($b['cara_bayar']) ?></td><td><?= esc($b['catatan'] ?? '-') ?></td></tr><?php endforeach ?>
</tbody></table><?php endif ?>
<?php if ((int) $utang['dibatalkan'] === 1): ?>
<p><strong>Transaksi ini dibatalkan.</strong> Pembayaran baru tidak dapat dicatat.</p>
<?php elseif ((int) $utang['sisa_hutang'] > 0): ?>
<h4>Catat Pembayaran</h4>
<form method="post" action="<?= site_url('utang/bayar/' . $utang['id']) ?>">
<?= csrf_field() ?>
<label for="tanggal_bayar">Tanggal pembayaran</label>
<input type="datetime-local" id="tanggal_bayar" name="tanggal_bayar" value="<?= esc(old('tanggal_bayar', date('Y-m-d\TH:i'))) ?>">
<label for="jumlah_bayar">Jumlah dibayar (Rp)</label>
<input type="number" id="jumlah_bayar" name="jumlah_bayar" min="1" max="<?= (int) $utang['sisa_hutang'] ?>" step="1" required>
<label for="cara_bayar">Cara pembayaran</label>
<select id="cara_bayar" name="cara_bayar" required>
<option value="Tunai">Tunai</option><option value="Transfer">Transfer</option><option value="QRIS">QRIS</option><option value="Lainnya">Lainnya</option>
</select>
<label for="catatan_bayar">Catatan</label>
<input type="text" id="catatan_bayar" name="catatan" maxlength="255">
<button class="tombol" type="submit" onclick="return confirm('Simpan pembayaran ini?')">Simpan Pembayaran</button>
</form>
<?php else: ?>
<p><strong>Hutang sudah lunas.</strong> Tidak ada sisa pembayaran.</p>
<?php endif ?>

<?php if ((int) $utang['dibatalkan'] === 0 && (int) $utang['total_bayar'] === 0): ?>
<hr>
<h4>Batalkan Transaksi</h4>
<p>Gunakan hanya jika transaksi salah dicatat. Transaksi tidak dihapus agar riwayat tetap tersimpan.</p>
<form method="post" action="<?= site_url('utang/batalkan/' . $utang['id']) ?>" onsubmit="return confirm('Batalkan transaksi hutang ini?')">
<?= csrf_field() ?>
<label for="alasan_batal">Alasan pembatalan *</label>
<input type="text" id="alasan_batal" name="alasan_batal" maxlength="255" required>
<button class="tombol" type="submit">Batalkan Transaksi</button>
</form>
<?php endif ?>
</div>
<p><a class="tombol" href="<?= site_url('utang') ?>">Kembali ke Daftar Hutang</a></p>
</div></body></html>
