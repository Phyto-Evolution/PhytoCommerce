-- Phyto Source Badge — install.sql
-- Creates the two module tables and seeds five default badge definitions.
-- The token PREFIX_ is replaced at runtime with _DB_PREFIX_.

-- ---------------------------------------------------------------
-- Table: badge definitions
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `PREFIX_phyto_source_badge_def` (
  `id_badge`    int(11)      NOT NULL AUTO_INCREMENT,
  `badge_label` varchar(100) NOT NULL,
  `badge_slug`  varchar(50)  NOT NULL,
  `badge_color` varchar(10)  NOT NULL DEFAULT 'gray',
  `description` text,
  `sort_order`  int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_badge`),
  UNIQUE KEY `badge_slug` (`badge_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Table: product ↔ badge assignments
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `PREFIX_phyto_source_badge_product` (
  `id_link`        int(11)      NOT NULL AUTO_INCREMENT,
  `id_product`     int(11)      NOT NULL,
  `id_badge`       int(11)      NOT NULL,
  `permit_ref`     varchar(100) DEFAULT NULL,
  `origin_country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_link`),
  KEY `idx_product` (`id_product`),
  KEY `idx_badge`   (`id_badge`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------
-- Default badge definitions (5 seeds)
-- ---------------------------------------------------------------
INSERT IGNORE INTO `PREFIX_phyto_source_badge_def`
  (`badge_label`, `badge_slug`, `badge_color`, `description`, `sort_order`)
VALUES
  ('TC Lab',      'tc-lab',      'green', 'Tissue-culture propagated in a certified laboratory environment.', 10),
  ('Division',    'division',    'blue',  'Propagated by vegetative division from established mother stock.',  20),
  ('Seed-grown',  'seed-grown',  'amber', 'Grown from ethically collected or commercially sourced seed.',      30),
  ('Wild Rescue', 'wild-rescue', 'red',   'Rescued from wild populations under an approved collection permit.', 40),
  ('Import',      'import',      'gray',  'Sourced via licensed international importation.',                    50)
