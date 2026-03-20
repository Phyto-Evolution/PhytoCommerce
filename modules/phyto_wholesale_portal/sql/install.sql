CREATE TABLE IF NOT EXISTS `PREFIX_phyto_wholesale_application` (
  `id_app` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL DEFAULT 0,
  `business_name` varchar(200) DEFAULT NULL,
  `gst_number` varchar(30) DEFAULT NULL,
  `address` text,
  `phone` varchar(30) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `message` text,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `admin_notes` text,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_app`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_wholesale_product` (
  `id_ws` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `moq` int(11) NOT NULL DEFAULT 0,
  `price_tiers` text,
  `wholesale_only` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ws`),
  UNIQUE KEY `id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
