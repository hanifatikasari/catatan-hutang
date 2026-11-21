<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/utang-detail.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'utang', 'judul' => $judul]) ?>

<main class="wadah">
    <div class="detail-page-header">
        <div>
            <h1>Detail Hutang</h1>
            <p class="sub-text">Informasi transaksi, rincian barang, dan riwayat pembayaran.</p>
        </div>
        <a class="tombol tombol-kembali" href="<?= site_url('utang') ?>">← Kembali ke Daftar</a>
    </div>

    <?php if ($pesan = session()->getFlashdata('success')): ?>
        <div class="pesan"><?= esc($pesan) ?></div>
    <?php endif ?>
    <?php if ($pesan = session()->getFlashdata('error')): ?>
        <div class="pesan error"><?= esc($pesan) ?></div>
    <?php endif ?>

    <!-- Info Pelanggan & Transaksi -->
    <div class="kotak detail-info-card">
        <div class="detail-header-row">
            <div>
                <h2 class="customer-name"><?= esc($utang['nama_pelanggan']) ?></h2>
                <div class="trx-number">No. Transaksi: <strong><?= esc($utang['nomor_transaksi']) ?></strong></div>
            </div>
            <?php if ((int) $utang['dibatalkan'] !== 1): ?>
                <a class="tombol tombol-ubah" href="<?= site_url('utang/edit/' . $utang['id']) ?>">
                    ✏️ Koreksi Catatan Hutang
                </a>
            <?php endif ?>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Tanggal Hutang:</span>
                <span class="info-value"><?= esc(date('d-m-Y H:i', strtotime($utang['tanggal_utang']))) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Jatuh Tempo:</span>
                <span class="info-value"><?= !empty($utang['tanggal_jatuh_tempo']) ? esc(date('d-m-Y', strtotime($utang['tanggal_jatuh_tempo']))) : '-' ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Nomor HP / WhatsApp:</span>
                <span class="info-value"><?= esc($utang['no_hp'] ?? '-') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Alamat:</span>
                <span class="info-value"><?= esc($utang['alamat'] ?? '-') ?></span>
            </div>
            <?php if (!empty($utang['catatan'])): ?>
                <div class="info-item info-full">
                    <span class="info-label">Catatan:</span>
                    <span class="info-value"><?= esc($utang['catatan']) ?></span>
                </div>
            <?php endif ?>
        </div>
    </div>

    <!-- Rincian Barang & Rekapitulasi Nilai -->
    <div class="kotak">
        <h3>Rincian Barang yang Dihutang</h3>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th style="text-align: right;">Jumlah</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rincian as $r): ?>
                    <tr>
                        <td><strong><?= esc($r['nama_barang']) ?></strong></td>
                        <td style="text-align: right;"><?= esc(rtrim(rtrim(number_format((float)$r['jumlah'], 3, ',', '.'), '0'), ',')) ?> <?= esc($r['satuan']) ?></td>
                        <td style="text-align: right;">Rp<?= number_format((int)$r['harga_satuan'], 0, ',', '.') ?></td>
                        <td style="text-align: right;">Rp<?= number_format((int)$r['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Rekap Angka Hutang -->
        <div class="detail-summary-bar">
            <div class="sum-box">
                <span class="sum-label">Total Hutang:</span>
                <span class="sum-value">Rp<?= number_format((int)$utang['total_hutang'], 0, ',', '.') ?></span>
            </div>
            <div class="sum-box">
                <span class="sum-label">Total Dibayar:</span>
                <span class="sum-value text-success">Rp<?= number_format((int)$utang['total_bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="sum-box highlight">
                <span class="sum-label">Sisa Hutang:</span>
                <span class="sum-value text-danger">Rp<?= number_format((int)$utang['sisa_hutang'], 0, ',', '.') ?></span>
            </div>
            <div class="sum-box">
                <span class="sum-label">Status Transaksi:</span>
                <span class="sum-status">
                    <?php if ((int)$utang['dibatalkan'] === 1): ?>
                        <span class="status-badge badge-dibatalkan">Dibatalkan</span>
                    <?php elseif ((int)$utang['sisa_hutang'] === 0): ?>
                        <span class="status-badge badge-lunas">Lunas</span>
                    <?php elseif ((int)$utang['total_bayar'] > 0): ?>
                        <span class="status-badge badge-sebagian">Dibayar Sebagian</span>
                    <?php else: ?>
                        <span class="status-badge badge-belum">Belum Dibayar</span>
                    <?php endif ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="kotak">
        <h3>Riwayat Pembayaran</h3>
        <?php if (empty($pembayaran)): ?>
            <p style="color: #63776b; margin: 12px 0;">Belum ada pembayaran yang dicatat untuk transaksi ini.</p>
        <?php else: ?>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu Bayar</th>
                            <th style="text-align: right;">Jumlah Dibayar</th>
                            <th>Metode</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pembayaran as $b): ?>
                        <tr>
                            <td><?= esc(date('d-m-Y H:i', strtotime($b['tanggal_bayar']))) ?></td>
                            <td style="text-align: right;"><strong class="text-success">Rp<?= number_format((int)$b['jumlah_bayar'], 0, ',', '.') ?></strong></td>
                            <td><span class="metode-tag"><?= esc($b['cara_bayar']) ?></span></td>
                            <td><?= esc($b['catatan'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>

        <!-- Form Catat Pembayaran -->
        <?php if ((int) $utang['dibatalkan'] === 1): ?>
            <div class="alert-box" style="margin-top: 18px; color: #64748b; background: #f8fafc; padding: 12px; border-radius: 8px;">
                <strong>Transaksi dibatalkan.</strong> Pembayaran baru tidak dapat dicatat.
            </div>
        <?php elseif ((int) $utang['sisa_hutang'] > 0): ?>
            <div class="form-pembayaran-box">
                <h4>+ Catat Pembayaran Baru</h4>
                <form method="post" action="<?= site_url('utang/bayar/' . $utang['id']) ?>" class="form-bayar">
                    <?= csrf_field() ?>
                    <div class="form-grid-bayar">
                        <div class="form-field">
                            <label for="tanggal_bayar">Tanggal Pembayaran</label>
                            <input type="datetime-local" id="tanggal_bayar" name="tanggal_bayar" value="<?= esc(old('tanggal_bayar', date('Y-m-d\TH:i'))) ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="jumlah_bayar">Jumlah Dibayar (Rp) *</label>
                            <input type="number" id="jumlah_bayar" name="jumlah_bayar" min="1" max="<?= (int) $utang['sisa_hutang'] ?>" step="1" placeholder="Maks: Rp<?= number_format((int)$utang['sisa_hutang'], 0, ',', '.') ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="cara_bayar">Metode Pembayaran</label>
                            <select id="cara_bayar" name="cara_bayar" required>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-field full-width">
                            <label for="catatan_bayar">Catatan Pembayaran (Opsional)</label>
                            <input type="text" id="catatan_bayar" name="catatan" maxlength="255" placeholder="Contoh: Titip ke anak / via transfer BCA">
                        </div>
                    </div>
                    <button class="tombol" type="submit" onclick="return confirm('Simpan pembayaran ini?')">
                        💾 Simpan Pembayaran
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="pesan" style="margin-top: 16px;">
                ✓ <strong>Hutang sudah lunas.</strong> Tidak ada sisa tagihan yang harus dibayar.
            </div>
        <?php endif ?>

        <!-- Pembatalan Transaksi -->
        <?php if ((int) $utang['dibatalkan'] === 0 && (int) $utang['total_bayar'] === 0): ?>
            <div class="batal-transaksi-section">
                <h4>Batalkan Transaksi</h4>
                <p class="batal-notice">
                    Hanya gunakan jika transaksi salah input. Riwayat tetap tersimpan untuk audit.
                </p>
                <form method="post" action="<?= site_url('utang/batalkan/' . $utang['id']) ?>" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi hutang ini?')">
                    <?= csrf_field() ?>
                    <label for="alasan_batal">Alasan Pembatalan *</label>
                    <input type="text" id="alasan_batal" name="alasan_batal" maxlength="255" placeholder="Contoh: Salah pilih nama pelanggan / barang batal diambil" required>
                    <button class="tombol tombol-danger" type="submit" style="margin-top: 10px;">
                        Batalkan Transaksi
                    </button>
                </form>
            </div>
        <?php endif ?>
    </div>
</main>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
