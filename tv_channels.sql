-- ============================================================
-- TV Channels table untuk fitur Live TV
-- Jalankan di phpMyAdmin pada database yang sama
-- ============================================================

CREATE TABLE IF NOT EXISTS `tv_channels` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `slug`        varchar(100) NOT NULL,
  `youtube_id`  varchar(50)  NOT NULL,
  `logo_letter` varchar(5)   NOT NULL DEFAULT 'TV',
  `color`       varchar(10)  NOT NULL DEFAULT '#E8000D',
  `description` varchar(255) DEFAULT NULL,
  `is_active`   tinyint(1)   NOT NULL DEFAULT 1,
  `sort_order`  int(11)      NOT NULL DEFAULT 0,
  `created_at`  datetime     DEFAULT current_timestamp(),
  `updated_at`  datetime     DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data awal (channel contoh — ganti youtube_id sesuai kebutuhan)
INSERT IGNORE INTO `tv_channels` (`name`, `slug`, `youtube_id`, `logo_letter`, `color`, `description`, `sort_order`) VALUES
('METRO TV',     'metro',  'y60wDzZt71A', 'M', '#005BAA', 'Berita terkini dan terpercaya dari MetroTV', 1),
('TV ONE',       'tvone',  'bPNIuRIjJ7Y', 'T', '#E8000D', 'Berita dan informasi terkini dari TVOne',    2),
('KOMPAS TV',    'kompas', 'aaRqBYyKU7s', 'K', '#CC0000', 'Streaming langsung Kompas TV 24 jam',        3),
('CNN INDONESIA','cnn',    'xzBH00EMJVQ', 'C', '#CC0000', 'Berita breaking news CNN Indonesia',         4);
