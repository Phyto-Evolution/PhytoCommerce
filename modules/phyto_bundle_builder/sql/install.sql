-- phyto_bundle_builder install SQL

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_bundle` (
  `id_bundle` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `discount_type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bundle`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_bundle_slot` (
  `id_slot` int(11) NOT NULL AUTO_INCREMENT,
  `id_bundle` int(11) NOT NULL,
  `slot_name` varchar(100) NOT NULL DEFAULT '',
  `slot_type` varchar(50) NOT NULL DEFAULT '',
  `id_category` int(11) NOT NULL DEFAULT 0,
  `required` tinyint(1) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_slot`),
  KEY `id_bundle` (`id_bundle`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_bundle_lang` (
  `id_bundle` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  PRIMARY KEY (`id_bundle`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
