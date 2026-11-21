-- Catatan Hutang SQL backup
-- Created: 2026-11-22 01:47:37
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `pelanggan`;
CREATE TABLE `pelanggan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pelanggan_nama` (`nama`),
  KEY `idx_pelanggan_no_hp` (`no_hp`),
  KEY `idx_pelanggan_aktif` (`aktif`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pelanggan` (`id`, `nama`, `no_hp`, `alamat`, `catatan`, `aktif`, `created_at`, `updated_at`) VALUES ('1', 'lek sutriyah', NULL, 'bawang lor', 'ibue mb atun', '1', '2026-11-21 07:03:52', '2026-11-21 08:29:07');

DROP TABLE IF EXISTS `pembayaran`;
CREATE TABLE `pembayaran` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utang_id` int unsigned NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `jumlah_bayar` int unsigned NOT NULL,
  `cara_bayar` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tunai',
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pembayaran_utang` (`utang_id`),
  KEY `idx_pembayaran_tanggal` (`tanggal_bayar`),
  CONSTRAINT `fk_pembayaran_utang` FOREIGN KEY (`utang_id`) REFERENCES `utang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pembayaran` (`id`, `utang_id`, `tanggal_bayar`, `jumlah_bayar`, `cara_bayar`, `catatan`, `created_at`) VALUES ('1', '1', '2026-11-21 15:53:00', '5000', 'Tunai', 'bayar beras', '2026-11-21 15:53:51');

DROP TABLE IF EXISTS `rincian_utang`;
CREATE TABLE `rincian_utang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utang_id` int unsigned NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(10,3) NOT NULL,
  `satuan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_satuan` int unsigned NOT NULL,
  `total_harga` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rincian_utang` (`utang_id`),
  CONSTRAINT `fk_rincian_utang` FOREIGN KEY (`utang_id`) REFERENCES `utang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rincian_utang` (`id`, `utang_id`, `nama_barang`, `jumlah`, `satuan`, `harga_satuan`, `total_harga`) VALUES ('5', '1', 'beras 1kg', '1.000', 'kg', '15000', '15000');

DROP TABLE IF EXISTS `utang`;
CREATE TABLE `utang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pelanggan_id` int unsigned NOT NULL,
  `nomor_transaksi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_utang` datetime NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dibatalkan` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_batal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_utang_nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_utang_pelanggan` (`pelanggan_id`),
  KEY `idx_utang_tanggal` (`tanggal_utang`),
  KEY `idx_utang_jatuh_tempo` (`tanggal_jatuh_tempo`),
  KEY `idx_utang_dibatalkan` (`dibatalkan`),
  CONSTRAINT `fk_utang_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utang` (`id`, `pelanggan_id`, `nomor_transaksi`, `tanggal_utang`, `tanggal_jatuh_tempo`, `catatan`, `dibatalkan`, `alasan_batal`, `created_at`, `updated_at`) VALUES ('1', '1', 'HTG-261121-D3A785', '2026-11-21 08:48:00', '2026-11-28', 'beli beras 1kg', '0', NULL, '2026-11-21 08:49:33', '2026-11-21 21:00:24');

SET FOREIGN_KEY_CHECKS = 1;
