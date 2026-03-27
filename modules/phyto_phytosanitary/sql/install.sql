CREATE TABLE IF NOT EXISTS `PREFIX_phyto_phytosanitary_doc` (
  `id_doc` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL DEFAULT 0,
  `doc_type` varchar(50) NOT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_doc`),
  KEY `idx_id_product` (`id_product`),
  KEY `idx_expiry_date` (`expiry_date`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
