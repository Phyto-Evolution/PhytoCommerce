CREATE TABLE IF NOT EXISTS `PREFIX_phyto_collection_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `id_customer` INT NOT NULL,
    `id_product` INT NOT NULL,
    `id_order` INT DEFAULT 0,
    `personal_note` TEXT,
    `is_public` TINYINT(1) DEFAULT 0,
    `date_acquired` DATE,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    KEY `idx_customer` (`id_customer`),
    KEY `idx_product` (`id_product`),
    KEY `idx_customer_product` (`id_customer`, `id_product`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
