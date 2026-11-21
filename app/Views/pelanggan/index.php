<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .wadah { max-width: 1000px; margin: auto; }
        .kepala { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .tombol { display: inline-block; padding: 10px 14px; background: #287a4b; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .tombol-ubah { background: #356ac3; }
        .tombol-nonaktif { background: #b33a3a; }
        .kotak { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
        input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; }
        .pencarian { display: flex; gap: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .pesan { padding: 12px; background: #e4f5e9; margin-top: 15px; border-radius: 5px; }
        .pesan-error { background: #fbe5e5; }
        .aksi { display: flex; gap: 6px; flex-wrap: wrap; }
        .status { display: flex; gap: 8px; margin-bottom: 15px; }
        @media (max-width: 650px) {
            table, tbody, tr, td { display: block; }
            thead { display: none; }
            tr { border: 1px solid #ddd; margin-bottom: 15px; padding: 10px; border-radius: 5px; }
            td { border: none; padding: 6px; }
            .pencarian { flex-direction: column; }
        }
    
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
<header class="app-topbar"><div class="app-topbar-title">Ringkasan &amp; Pengelolaan</div><div class="app-topbar-note">Sistem pencatatan warung</div></header>
<div class="wadah">
    <div class="kepala">
        <div><h1>Daftar Pelanggan</h1><p>Catatan orang yang memiliki transaksi utang.</p></div>
        <div class="status">
            <a href="<?= site_url('pelanggan?status=aktif') ?>">Pelanggan Aktif</a> |
            <a href="<?= site_url('pelanggan?status=tidak_aktif') ?>">Pelanggan Tidak Aktif</a>
        </div>
        <a href="<?= site_url('pelanggan/tambah') ?>" class="tombol">+ Tambah Pelanggan</a>
    </div>

    <?php if ($pesan = session()->getFlashdata('success')): ?>
        <div class="pesan"><?= esc($pesan) ?></div>
    <?php endif ?>
    <?php if ($pesan = session()->getFlashdata('error')): ?>
        <div class="pesan pesan-error"><?= esc($pesan) ?></div>
    <?php endif ?>

    <div class="kotak">
        <form action="<?= site_url('pelanggan') ?>" method="get" class="pencarian">
            <input type="hidden" name="status" value="<?= esc($status) ?>">
            <button type="submit" class="tombol">Cari</button>
            <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-ubah">Bersihkan</a>
        </form>

        <table>
            <thead><tr><th>Nama</th><th>Nomor HP</th><th>Alamat / Keterangan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($pelanggan)): ?>
                <tr><td colspan="4">Belum ada data pelanggan.</td></tr>
            <?php else: ?>
                <?php foreach ($pelanggan as $p): ?>
                    <tr>
                        <td><strong><?= esc($p['nama']) ?></strong></td>
                        <td><?= esc($p['no_hp'] ?? '-') ?></td>
                        <td>
                            <?= esc($p['alamat'] ?? '-') ?>
                            <?php if (!empty($p['catatan'])): ?><br><small><?= esc($p['catatan']) ?></small><?php endif ?>
                        </td>
                        <td>
                            <div class="aksi">
                                <a href="<?= site_url('pelanggan/edit/' . $p['id']) ?>" class="tombol tombol-ubah">Ubah</a>
                                <?php if ((int) $p['aktif'] === 1): ?>
                                    <form action="<?= site_url('pelanggan/nonaktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Jadikan pelanggan ini tidak aktif?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="tombol tombol-nonaktif">Nonaktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= site_url('pelanggan/aktifkan/' . $p['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Aktifkan kembali pelanggan ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="tombol">Aktifkan Kembali</button>
                                    </form>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
