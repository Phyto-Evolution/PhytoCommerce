CREATE TABLE IF NOT EXISTS `PREFIX_phyto_pack_log` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `module_name`  VARCHAR(100) NOT NULL,
  `status`       ENUM('installed', 'failed', 'skipped') NOT NULL,
  `message`      TEXT,
  `installed_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_module` (`module_name`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
