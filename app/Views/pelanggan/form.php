<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; color: #333; }
        .wadah { max-width: 650px; margin: auto; }
        .kotak { background: white; padding: 25px; border-radius: 8px; margin-top: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input, textarea { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; margin-bottom: 18px; font-family: Arial, sans-serif; }
        textarea { min-height: 90px; resize: vertical; }
        .tombol { display: inline-block; padding: 11px 16px; background: #287a4b; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .tombol-kembali { background: #777; }
        .pesan-error { background: #fbe5e5; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .pesan-error ul { margin: 8px 0 0; }
    
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
    <h1><?= esc($judul) ?></h1>
    <div class="kotak">
        <?php if ($errors = session()->getFlashdata('errors')): ?>
            <div class="pesan-error">
                <strong>Periksa kembali data berikut:</strong>
                <ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul>
            </div>
        <?php endif ?>

        <?php
        $isEdit = !empty($pelanggan['id']);
        $alamatForm = $isEdit ? 'pelanggan/perbarui/' . $pelanggan['id'] : 'pelanggan/simpan';
        ?>
        <form action="<?= site_url($alamatForm) ?>" method="post">
            <?= csrf_field() ?>

            <label for="nama">Nama pelanggan *</label>
            <input type="text" id="nama" name="nama" maxlength="100" required
                   value="<?= esc(old('nama', $pelanggan['nama'])) ?>" placeholder="Contoh: Bu Siti">

            <label for="no_hp">Nomor HP / WhatsApp</label>
            <input type="tel" id="no_hp" name="no_hp" maxlength="15"
                   value="<?= esc(old('no_hp', $pelanggan['no_hp'] ?? '')) ?>" placeholder="Contoh: 081234567890">

            <label for="alamat">Alamat / Lokasi rumah</label>
            <textarea id="alamat" name="alamat" maxlength="255"
                      placeholder="Contoh: Gang Melati, rumah dekat mushola"><?= esc(old('alamat', $pelanggan['alamat'] ?? '')) ?></textarea>

            <label for="catatan">Keterangan pelanggan</label>
            <textarea id="catatan" name="catatan"
                      placeholder="Contoh: Anak Pak Budi, biasanya belanja sore"><?= esc(old('catatan', $pelanggan['catatan'] ?? '')) ?></textarea>

            <button type="submit" class="tombol">Simpan</button>
            <a href="<?= site_url('pelanggan') ?>" class="tombol tombol-kembali">Kembali</a>
        </form>
    </div>
</div>
</body>
</html>
