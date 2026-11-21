<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/laporan-index.css') ?>"></head><body>
<?= $this->include('layouts/partials/navigation', ['active' => 'laporan', 'judul' => $judul]) ?>

<div class="wadah">
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
</div></div><script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>