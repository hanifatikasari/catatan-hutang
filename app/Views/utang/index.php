<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/utang-index.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<div class="wadah">
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
</div></div><script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
