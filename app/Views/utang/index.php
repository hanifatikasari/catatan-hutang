<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;color:#333;margin:0;padding:20px}
.wadah{max-width:1100px;margin:auto}.kepala,.pencarian{display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.kotak{background:#fff;padding:20px;border-radius:8px;margin-top:20px}
.tombol{display:inline-block;background:#287a4b;color:#fff;text-decoration:none;border:0;border-radius:5px;padding:10px 14px;cursor:pointer}
input{padding:10px;border:1px solid #ccc;border-radius:5px;flex:1}
table{width:100%;border-collapse:collapse}th,td{padding:11px;border-bottom:1px solid #ddd;text-align:left}
.pesan{background:#e4f5e9;padding:12px;border-radius:5px;margin-top:12px}.error{background:#fbe5e5}
@media(max-width:700px){table,tbody,tr,td{display:block}thead{display:none}tr{border:1px solid #ddd;margin-bottom:12px;padding:8px}td{border:0}}
</style>
</head>
<body><div class="wadah">
<div class="kepala">
<div><h1>Catatan Hutang</h1><p>Daftar transaksi hutang pelanggan.</p></div>
<a class="tombol" href="<?= site_url('utang/tambah') ?>">+ Catat Hutang</a>
</div>
<?php if ($pesan = session()->getFlashdata('success')): ?><div class="pesan"><?= esc($pesan) ?></div><?php endif ?>
<?php if ($pesan = session()->getFlashdata('error')): ?><div class="pesan error"><?= esc($pesan) ?></div><?php endif ?>
<div class="kotak">
<form class="pencarian" method="get" action="<?= site_url('utang') ?>">
<input name="cari" value="<?= esc($cari) ?>" placeholder="Cari nama pelanggan atau nomor transaksi">
<button class="tombol" type="submit">Cari</button>
<a class="tombol" href="<?= site_url('utang') ?>">Bersihkan</a>
</form>
<table><thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Pelanggan</th><th>Total</th><th>Sudah Bayar</th><th>Sisa</th><th></th></tr></thead><tbody>
<?php if (empty($utang)): ?><tr><td colspan="7">Belum ada transaksi hutang.</td></tr>
<?php else: foreach ($utang as $u): $sisa = max(0, (int)$u['total_hutang'] - (int)$u['total_bayar']); ?>
<tr>
<td><?= esc(date('d-m-Y', strtotime($u['tanggal_utang']))) ?></td>
<td><?= esc($u['nomor_transaksi']) ?></td>
<td><?= esc($u['nama_pelanggan']) ?></td>
<td>Rp<?= number_format((int)$u['total_hutang'], 0, ',', '.') ?></td>
<td>Rp<?= number_format((int)$u['total_bayar'], 0, ',', '.') ?></td>
<td><strong>Rp<?= number_format($sisa, 0, ',', '.') ?></strong></td>
<td><a class="tombol" href="<?= site_url('utang/detail/' . $u['id']) ?>">Detail</a></td>
</tr>
<?php endforeach; endif ?>
</tbody></table>
<p><a href="<?= site_url('/') ?>">Kembali ke Beranda</a> | <a href="<?= site_url('pelanggan') ?>">Daftar Pelanggan</a></p>
</div></div></body></html>
