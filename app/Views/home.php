
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($judul) ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .wadah {
            max-width: 900px;
            margin: auto;
        }

        .kotak {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
        }

        a {
            display: inline-block;
            padding: 12px 16px;
            background: #287a4b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
            .ringkasan { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:20px; }
        .angka { font-size:24px; font-weight:bold; margin:8px 0; }
        .menu { display:flex; gap:10px; flex-wrap:wrap; }
    </style>
</head>
<body>

<div class="wadah">
    <h1><?= esc($judul) ?></h1>
    <p>Ringkasan kondisi hutang dan piutang warung.</p>
    <div class="ringkasan">
        <div class="kotak"><div>Pelanggan Aktif</div><div class="angka"><?= number_format((int) $pelangganAktif, 0, ',', '.') ?></div></div>
        <div class="kotak"><div>Total Piutang Belum Lunas</div><div class="angka">Rp<?= number_format((int) $totalPiutang, 0, ',', '.') ?></div></div>
        <div class="kotak"><div>Transaksi Belum Lunas</div><div class="angka"><?= number_format((int) $transaksiBelumLunas, 0, ',', '.') ?></div></div>
        <div class="kotak"><div>Lewat Jatuh Tempo</div><div class="angka"><?= number_format((int) $jatuhTempo, 0, ',', '.') ?></div></div>
    </div>

    <div class="kotak">
        <h2>Menu Utama</h2>
        <p>Kelola pelanggan dan catat transaksi hutang.</p>
        <a href="<?= site_url('pelanggan') ?>">
            Daftar Pelanggan
        </a>
        <p><a href="<?= site_url('utang') ?>">Catatan Hutang</a></p>
    </div>
</div>

</body>
</html>
