CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `description` text,
  `url` varchar(1000) NOT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_unique` (`url`),
  KEY `idx_category` (`category`),
  KEY `idx_published` (`published_at`)
)