CREATE TABLE IF NOT EXISTS `PREFIX_phyto_restock_alert` (
  `id_alert`            INT(11)      NOT NULL AUTO_INCREMENT,
  `id_product`          INT(11)      NOT NULL,
  `id_product_attribute` INT(11)     NOT NULL DEFAULT 0,
  `id_customer`         INT(11)      NOT NULL DEFAULT 0,
  `email`               VARCHAR(255) NOT NULL,
  `firstname`           VARCHAR(100) DEFAULT NULL,
  `date_add`            DATETIME     DEFAULT NULL,
  `notified`            TINYINT(1)   NOT NULL DEFAULT 0,
  `date_notified`       DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_alert`),
  UNIQUE KEY `uniq_product_email` (`id_product`, `id_product_attribute`, `email`),
  KEY `idx_product_attr_notified` (`id_product`, `id_product_attribute`, `notified`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
