<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/laporan-index.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/pages/laporan-print.css') ?>">
</head>
<body>
<?= $this->include('layouts/partials/navigation', ['active' => 'laporan', 'judul' => $judul]) ?>

<main class="wadah">
    <!-- ====================================================================
         1. TAMPILAN DI MENU (Interactive Screen View)
         Khusus tampil di antarmuka web, responsif untuk semua layar (Oppo A53 dll)
         ==================================================================== -->
    <div class="laporan-screen-view no-print">
        <div class="laporan-page-header">
            <div>
                <h1>Laporan Hutang & Piutang</h1>
                <p class="sub-text">Rekapitulasi pencatatan hutang dan transaksi pembayaran pelanggan.</p>
            </div>
            <button type="button" class="tombol tombol-cetak" onclick="window.print()">
                <span class="btn-icon">🖨️</span> Cetak / Simpan PDF
            </button>
        </div>

        <?php if ($pesan = session()->getFlashdata('error')): ?>
            <div class="pesan-error"><?= esc($pesan) ?></div>
        <?php endif ?>

        <!-- Form Filter Tanggal -->
        <div class="kotak filter-card">
            <form method="get" action="<?= site_url('laporan') ?>" class="laporan-filter-form">
                <div class="filter-inputs">
                    <div class="filter-field">
                        <label for="mulai">Dari Tanggal</label>
                        <input type="date" id="mulai" name="mulai" value="<?= esc($mulai) ?>" required>
                    </div>
                    <div class="filter-field">
                        <label for="sampai">Sampai Tanggal</label>
                        <input type="date" id="sampai" name="sampai" value="<?= esc($sampai) ?>" required>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="tombol">
                        <span>🔍</span> Terapkan Filter
                    </button>
                    <div class="quick-shortcuts">
                        <a href="<?= site_url('laporan?mulai=' . date('Y-m-d') . '&sampai=' . date('Y-m-d')) ?>" class="tombol-pill <?= $mulai === date('Y-m-d') && $sampai === date('Y-m-d') ? 'active' : '' ?>">Hari Ini</a>
                        <a href="<?= site_url('laporan?mulai=' . date('Y-m-01') . '&sampai=' . date('Y-m-t')) ?>" class="tombol-pill <?= $mulai === date('Y-m-01') && $sampai === date('Y-m-t') ? 'active' : '' ?>">Bulan Ini</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Kartu Ringkasan Statistik di Menu -->
        <div class="laporan-stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-badge-icon badge-icon-orange">💳</span>
                    <span class="stat-title">Hutang Baru Dicatat</span>
                </div>
                <div class="stat-value">Rp<?= number_format((int)$totalHutang, 0, ',', '.') ?></div>
                <div class="stat-desc">Total hutang dibuat di rentang ini</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-badge-icon badge-icon-green">✓</span>
                    <span class="stat-title">Terbayar (Dari Hutang Ini)</span>
                </div>
                <div class="stat-value text-success">Rp<?= number_format((int)$totalPembayaranUntukUtang, 0, ',', '.') ?></div>
                <div class="stat-desc">Pembayaran untuk transaksi periode ini</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-badge-icon badge-icon-red">⏳</span>
                    <span class="stat-title">Sisa Piutang Berjalan</span>
                </div>
                <div class="stat-value text-danger">Rp<?= number_format((int)$sisaPiutang, 0, ',', '.') ?></div>
                <div class="stat-desc">Tagihan belum lunas pada periode ini</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-badge-icon badge-icon-blue">💰</span>
                    <span class="stat-title">Total Kas Pembayaran</span>
                </div>
                <div class="stat-value">Rp<?= number_format((int)$pembayaranPeriode, 0, ',', '.') ?></div>
                <div class="stat-desc">Seluruh uang masuk diterima di rentang ini</div>
            </div>
        </div>

        <!-- Tabel Rincian Data di Menu -->
        <div class="kotak laporan-table-box">
            <div class="table-header-row">
                <h2>Rincian Transaksi Hutang</h2>
                <span class="badge-transaksi-count"><?= count($utang) ?> transaksi</span>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Transaksi</th>
                            <th>Pelanggan</th>
                            <th style="text-align: right;">Total Hutang</th>
                            <th style="text-align: right;">Dibayar</th>
                            <th style="text-align: right;">Sisa</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utang)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding: 28px; color: #6d7d72;">
                                    Tidak ada transaksi hutang pada rentang tanggal ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($utang as $u): ?>
                                <tr>
                                    <td><?= esc(date('d-m-Y', strtotime($u['tanggal_utang']))) ?></td>
                                    <td><strong><?= esc($u['nomor_transaksi']) ?></strong></td>
                                    <td><?= esc($u['nama_pelanggan']) ?></td>
                                    <td style="text-align: right;">Rp<?= number_format((int)$u['total_hutang'], 0, ',', '.') ?></td>
                                    <td style="text-align: right;">Rp<?= number_format((int)$u['total_bayar'], 0, ',', '.') ?></td>
                                    <td style="text-align: right;">
                                        <strong class="<?= (int)$u['sisa_hutang'] > 0 ? 'text-danger' : 'text-success' ?>">
                                            Rp<?= number_format((int)$u['sisa_hutang'], 0, ',', '.') ?>
                                        </strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ((int)$u['sisa_hutang'] === 0): ?>
                                            <span class="status-badge badge-lunas">Lunas</span>
                                        <?php elseif ((int)$u['total_bayar'] > 0): ?>
                                            <span class="status-badge badge-sebagian">Sebagian</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-belum">Belum Bayar</span>
                                        <?php endif ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a class="tombol tombol-sm" href="<?= site_url('utang/detail/' . $u['id']) ?>">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>

            <div class="laporan-foot-actions">
                <a class="tombol tombol-kembali" href="<?= site_url('/') ?>">← Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         2. TAMPILAN FORMAT CETAK (Formal Print Document)
         Format dokumen resmi kertas kop surat, rapi dan khusus saat diprint/PDF
         ==================================================================== -->
    <div class="laporan-print-document print-only">
        <!-- Kop Surat Toko -->
        <div class="print-kop">
            <h2 class="print-toko-name">TOKO IBU ZULAIKHAH</h2>
            <p class="print-toko-alamat">Jl. Sunan Ampel RT.12/RW.04 Desa Bawang, Kec. Bawang, Kab. Batang 51274</p>
            <p class="print-toko-telp">Sistem Administrasi & Pembukuan Hutang Warung</p>
            <div class="print-kop-line"></div>
        </div>

        <!-- Judul Dokumen & Metadata -->
        <div class="print-doc-header">
            <h1 class="print-doc-title">LAPORAN REKAPITULASI PIUTANG</h1>
            <table class="print-meta-table">
                <tr>
                    <td class="meta-label">Periode Transaksi</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value"><?= esc(date('d/m/Y', strtotime($mulai))) ?> s/d <?= esc(date('d/m/Y', strtotime($sampai))) ?></td>
                    <td class="meta-label">Waktu Cetak</td>
                    <td class="meta-separator">:</td>
                    <td class="meta-value"><?= esc(date('d/m/Y H:i')) ?> WIB</td>
                </tr>
            </table>
        </div>

        <!-- Ringkasan Angka untuk Cetak -->
        <div class="print-summary-box">
            <table class="print-summary-table">
                <thead>
                    <tr>
                        <th>Total Hutang Dicatat</th>
                        <th>Pembayaran Diterima</th>
                        <th>Sisa Saldo Piutang</th>
                        <th>Total Kas Masuk Periode</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rp<?= number_format((int)$totalHutang, 0, ',', '.') ?></td>
                        <td>Rp<?= number_format((int)$totalPembayaranUntukUtang, 0, ',', '.') ?></td>
                        <td><strong>Rp<?= number_format((int)$sisaPiutang, 0, ',', '.') ?></strong></td>
                        <td>Rp<?= number_format((int)$pembayaranPeriode, 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Data Rincian untuk Cetak -->
        <table class="print-data-table">
            <thead>
                <tr>
                    <th style="width: 28px; text-align: center;">No</th>
                    <th style="width: 80px;">Tanggal</th>
                    <th style="width: 140px;">No. Transaksi</th>
                    <th>Nama Pelanggan</th>
                    <th style="width: 110px; text-align: right;">Total Hutang</th>
                    <th style="width: 105px; text-align: right;">Terbayar</th>
                    <th style="width: 110px; text-align: right;">Sisa Hutang</th>
                    <th style="width: 85px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($utang)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 18px;">
                            Tidak ada transaksi yang tercatat pada rentang tanggal ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($utang as $u): ?>
                        <tr>
                            <td style="text-align: center;"><?= $no++ ?></td>
                            <td><?= esc(date('d/m/Y', strtotime($u['tanggal_utang']))) ?></td>
                            <td><?= esc($u['nomor_transaksi']) ?></td>
                            <td><?= esc($u['nama_pelanggan']) ?></td>
                            <td style="text-align: right;">Rp<?= number_format((int)$u['total_hutang'], 0, ',', '.') ?></td>
                            <td style="text-align: right;">Rp<?= number_format((int)$u['total_bayar'], 0, ',', '.') ?></td>
                            <td style="text-align: right;">Rp<?= number_format((int)$u['sisa_hutang'], 0, ',', '.') ?></td>
                            <td style="text-align: center;">
                                <?= (int)$u['sisa_hutang'] === 0 ? 'LUNAS' : ((int)$u['total_bayar'] > 0 ? 'SEBAGIAN' : 'BELUM') ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;">TOTAL AKUMULASI:</th>
                    <th style="text-align: right;">Rp<?= number_format((int)$totalHutang, 0, ',', '.') ?></th>
                    <th style="text-align: right;">Rp<?= number_format((int)$totalPembayaranUntukUtang, 0, ',', '.') ?></th>
                    <th style="text-align: right;">Rp<?= number_format((int)$sisaPiutang, 0, ',', '.') ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <!-- Kolom Tanda Tangan Cetak -->
        <div class="print-signatures-section">
            <div class="print-sig-col-left">
                <p><strong>Catatan:</strong></p>
                <p class="print-sig-notice">
                    Dokumen ini merupakan rekaman data sah dari Sistem Pembukuan Toko Ibu Zulaikhah.
                </p>
            </div>
            <div class="print-sig-col-right">
                <p>Bawang, <?= esc(date('d/m/Y')) ?></p>
                <p class="print-sig-role">Pemilik Toko / Penanggung Jawab,</p>
                <div class="print-sig-space"></div>
                <p class="print-sig-name"><strong>( Ibu Zulaikhah )</strong></p>
            </div>
        </div>
    </div>
</main>

<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
