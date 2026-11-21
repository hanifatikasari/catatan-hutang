# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library


## Backup, restore, dan cetak laporan

Pada branch feature/report-backup-restore tersedia menu cetak laporan serta backup/restore database.

- Cetak laporan: buka menu Laporan, pilih rentang tanggal, lalu klik Cetak / Simpan PDF. Pada dialog browser pilih printer atau Save as PDF.
- Password backup: tambahkan password khusus minimal 16 karakter ke file .env lokal (file ini tidak di-commit):

  ```dotenv
  BACKUP_RESTORE_PASSWORD = "buat-password-panjang-dan-rahasia"
  ```

- Backup: menu Backup & Restore menyediakan unduhan SQL dari struktur dan isi database.
- Restore: unggah file SQL hasil backup aplikasi. Restore mengganti data tabel; aplikasi menyimpan backup pengaman sebelum pemulihan di writable/backups/before_restore_latest.sql.
- Akses: password diperlukan untuk membuka menu selama 15 menit. Restore dibatasi ke perintah DROP/CREATE/INSERT untuk tabel yang sudah ada di database dan dilindungi CSRF.

Sebelum mencoba restore, lakukan backup dan uji terlebih dahulu pada database salinan. Backup berisi data pelanggan dan transaksi, jadi simpan file secara privat. Fitur backup menggunakan generator SQL berbasis MySQLi karena metode backup bawaan Database Utilities CI4 belum diimplementasikan untuk driver MySQLi. Dump mencakup struktur tabel dan data; view, trigger, procedure, dan event database tidak termasuk.


## PWA dan transaksi offline

Branch `feature/pwa-dasar` menambahkan manifest, ikon aplikasi, service worker, halaman fallback offline, cache data pelanggan/transaksi ke IndexedDB, serta antrean transaksi hutang yang dibuat saat perangkat offline.

Sebelum menggunakan sinkronisasi offline, jalankan migration `AddOfflineSyncIdToUtang` pada database aplikasi:

```bash
php spark migrate
```

Migration menambahkan kolom nullable `utang.offline_id` dengan unique index. Kolom ini dipakai sebagai idempotency key agar pengiriman ulang transaksi lokal yang sama tidak membuat transaksi server duplikat. Pastikan migration sudah diterapkan pada database lokal dan database hosting sebelum mengaktifkan input offline.

Jika hosting tidak menyediakan terminal, terapkan perubahan skema yang sama melalui phpMyAdmin:

```sql
ALTER TABLE `utang`
    ADD `offline_id` VARCHAR(80) NULL DEFAULT NULL AFTER `nomor_transaksi`;

CREATE UNIQUE INDEX `utang_offline_id_unique`
    ON `utang` (`offline_id`);
```

Catatan: data pelanggan dan maksimal 150 transaksi terbaru disimpan pada perangkat yang pernah memuat data saat online. Data offline tidak langsung tersedia di perangkat lain sampai berhasil disinkronkan. Browser perlu mendukung Service Worker dan IndexedDB, serta website harus diakses melalui HTTPS (localhost untuk pengembangan). Backup perangkat dan server tetap perlu dilakukan; cache offline bukan pengganti backup.


## Login privat (satu akun)

Aplikasi Catatan Hutang dilindungi login native CodeIgniter 4. Semua route aplikasi dan endpoint data/sinkronisasi memerlukan session login. Registrasi publik tidak tersedia.

Sebelum menjalankan aplikasi, atur kredensial di file `.env` (jangan commit file ini):

```ini
CATATAN_LOGIN_USERNAME = zulaikhah
CATATAN_LOGIN_PASSWORD_HASH = 'PASTE_HASH_PASSWORD_DI_SINI'
```

Buat hash password sendiri lalu ganti placeholder di atas. Source code hanya membaca hash, bukan password teks biasa. Untuk mengganti password, buat hash baru di komputer/server dengan PHP:

```bash
php -r 'echo password_hash(readline("Password baru: "), PASSWORD_DEFAULT), PHP_EOL;'
```

Salin hasil hash ke `CATATAN_LOGIN_PASSWORD_HASH` di file `.env` lokal dan hosting. Jangan memasukkan hash atau password ke file yang di-commit. Gunakan password unik yang panjang; aktifkan HTTPS saat hosting.

Login menerapkan CSRF dan mengunci percobaan berulang dalam session. Halaman privat mengirim header `no-store`, dan service worker tidak menyimpan HTML halaman login/data. Karena halaman privat tidak di-cache, halaman catat transaksi tidak dapat dibuka saat offline; antrean offline yang sudah tersimpan di IndexedDB tetap berada di perangkat sampai disinkronkan.
