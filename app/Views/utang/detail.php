<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($judul) ?></title>
<link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/pages/utang-detail.css ?>"></head><body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<div class="wadah">
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
</div><script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
