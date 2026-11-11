-- ============================================================
-- XyronNews – Setup SQL untuk Database `xnn`
-- Jalankan query ini di phpMyAdmin pada database `xnn`
-- ============================================================

-- Buat tabel users jika belum ada
CREATE TABLE IF NOT EXISTS `users` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `username`   varchar(50) NOT NULL,
  `password`   varchar(255) NOT NULL,
  `name`       varchar(100) NOT NULL,
  `role`       enum('admin','editor','author') NOT NULL DEFAULT 'author',
  `email`      varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert users
-- ID 1 = Admin (sesuai posts dengan author_id=1)
-- ID 7 = Redaktur (sesuai posts dengan author_id=7, paling banyak)
-- ============================================================

-- Admin utama | Username: admin | Password: admin123
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `name`, `role`, `email`) VALUES (
  1,
  'admin',
  '$2y$12$M3vKvnpsHvFgImOaLvqfBO.ItVh.YyR/q4Sz1RpMqBVPVLgVSwnNS',
  'Administrator',
  'admin',
  'admin@xyronnews.com'
);

-- Redaktur utama | Username: redaktur | Password: redaktur123
-- (author_id=7 dipakai di hampir semua artikel yang ada)
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `name`, `role`, `email`) VALUES (
  7,
  'redaktur',
  '$2y$12$xPMqDJaIjGpCcbHB3N.cW.TxAqX3qiR.kXwcz7Lnvw.RLp4oQhfge',
  'Redaktur XNN',
  'editor',
  'redaktur@xyronnews.com'
);

-- ============================================================
-- CATATAN PASSWORD:
--   admin     → password: admin123
--   redaktur  → password: redaktur123
--
-- Segera ganti password setelah login pertama!
-- Untuk generate hash baru, jalankan di PHP:
--   echo password_hash('password_baru_anda', PASSWORD_BCRYPT);
-- ============================================================

-- Pastikan AUTO_INCREMENT tidak bentrok dengan id yang sudah ada
ALTER TABLE `users` AUTO_INCREMENT = 100;
