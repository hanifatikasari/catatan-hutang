
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
    </style>
</head>
<body>

<div class="wadah">
    <h1><?= esc($judul) ?></h1>
    <p>Selamat datang di sistem pencatatan utang warung.</p>

    <div class="kotak">
        <h2>Menu Utama</h2>
        <p>Mulai dengan mengelola data pelanggan.</p>
        <a href="<?= site_url('pelanggan') ?>">
            Daftar Pelanggan
        </a>
    </div>
</div>

</body>
</html>
