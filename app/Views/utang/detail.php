<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($judul) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;color:#333;margin:0;padding:20px}.wadah{max-width:900px;margin:auto}.kotak{background:#fff;padding:20px;border-radius:8px;margin:16px 0}
table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}
.tombol{display:inline-block;background:#287a4b;color:white;text-decoration:none;padding:10px 14px;border-radius:5px}
</style></head><body><div class="wadah">
<h1>Detail Hutang</h1>
<?php if ($pesan = session()->getFlashdata('success')): ?><p><?= esc($pesan) ?></p><?php endif ?>
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
</div>
<div class="kotak"><h3>Riwayat Pembayaran</h3>
<?php if (empty($pembayaran)): ?><p>Belum ada pembayaran yang dicatat.</p><?php else: ?>
<table><thead><tr><th>Tanggal</th><th>Jumlah</th><th>Cara</th><th>Catatan</th></tr></thead><tbody>
<?php foreach ($pembayaran as $b): ?><tr><td><?= esc(date('d-m-Y H:i', strtotime($b['tanggal_bayar']))) ?></td><td>Rp<?= number_format((int)$b['jumlah_bayar'],0,',','.') ?></td><td><?= esc($b['cara_bayar']) ?></td><td><?= esc($b['catatan'] ?? '-') ?></td></tr><?php endforeach ?>
</tbody></table><?php endif ?>
<p>Fitur pencatatan pembayaran akan tersedia pada tahap berikutnya.</p>
</div>
<p><a class="tombol" href="<?= site_url('utang') ?>">Kembali ke Daftar Hutang</a></p>
</div></body></html>
