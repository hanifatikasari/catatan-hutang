<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Login - Catatan Hutang</title>
    <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; padding: 20px; background: #f4f7f5; }
        .login-card { width: min(100%, 420px); padding: 30px; background: #fff; border: 1px solid #dce8e0; border-radius: 20px; box-shadow: 0 12px 40px rgba(20, 50, 35, .08); }
        .login-brand { text-align: center; margin-bottom: 26px; }
        .login-icon { display: grid; place-items: center; width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 18px; background: #e8f2eb; font-size: 30px; }
        .login-brand h1 { margin: 0 0 8px; color: #173b2b; font-size: 25px; }
        .login-brand p { margin: 0; color: #68796f; font-size: 14px; line-height: 1.5; }
        .login-field { margin: 18px 0; }
        .login-field label { display: block; margin-bottom: 8px; color: #31483a; font-size: 14px; font-weight: 600; }
        .login-field input { box-sizing: border-box; width: 100%; min-height: 48px; padding: 12px 14px; border: 1px solid #cbd8ce; border-radius: 10px; font: inherit; }
        .login-field input:focus { outline: 3px solid rgba(65, 130, 86, .15); border-color: #4f8a61; }
        .login-submit { width: 100%; min-height: 48px; margin-top: 8px; border: 0; border-radius: 10px; background: #173b2b; color: white; font: inherit; font-weight: 700; cursor: pointer; }
        .login-submit:hover { background: #24563d; }
        .login-message { margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; background: #fff1ed; color: #a33a24; font-size: 14px; }
        .login-success { background: #eaf5ec; color: #24613a; }
        .login-foot { margin-top: 20px; text-align: center; color: #758278; font-size: 12px; }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="login-brand">
            <div class="login-icon" aria-hidden="true">📒</div>
            <h1>Catatan Hutang</h1>
            <p>Masuk untuk melanjutkan pembukuan<br>warung Ibu Zulaikhah.</p>
        </div>

        <?php if (! empty($error)): ?>
            <div class="login-message" role="alert"><?= esc($error) ?></div>
        <?php endif ?>
        <?php if ($message = session()->getFlashdata('success')): ?>
            <div class="login-message login-success" role="status"><?= esc($message) ?></div>
        <?php endif ?>

        <form method="post" action="<?= site_url('login') ?>" autocomplete="on">
            <?= csrf_field() ?>
            <div class="login-field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" required maxlength="60" autofocus>
            </div>
            <div class="login-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="login-submit" type="submit">Masuk</button>
        </form>

        <div class="login-foot">Halaman ini hanya untuk pengguna yang diberi akses.</div>
    </main>
</body>
</html>
