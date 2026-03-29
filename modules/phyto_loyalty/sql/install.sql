CREATE TABLE IF NOT EXISTS `PREFIX_phyto_loyalty_account` (
  `id_loyalty` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `points_balance` int(11) NOT NULL DEFAULT 0,
  `points_lifetime` int(11) NOT NULL DEFAULT 0,
  `points_redeemed` int(11) NOT NULL DEFAULT 0,
  `tier` enum('seed','sprout','bloom','rare') NOT NULL DEFAULT 'seed',
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_loyalty`),
  UNIQUE KEY `id_customer` (`id_customer`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_loyalty_transaction` (
  `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `id_order` int(11) NOT NULL DEFAULT 0,
  `type` enum('earn','redeem','expire','adjust','refund') NOT NULL,
  `points` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  PRIMARY KEY (`id_transaction`),
  KEY `idx_customer_date` (`id_customer`, `date_add`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
