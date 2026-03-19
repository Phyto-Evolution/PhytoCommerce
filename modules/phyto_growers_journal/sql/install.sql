CREATE TABLE IF NOT EXISTS `PREFIX_phyto_journal_entry` (
    `id_entry`    INT(11)      NOT NULL AUTO_INCREMENT,
    `id_product`  INT(11)      NOT NULL,
    `id_customer` INT(11)      DEFAULT 0,
    `entry_date`  DATE         DEFAULT NULL,
    `title`       VARCHAR(255) DEFAULT NULL,
    `body`        TEXT,
    `photo1`      VARCHAR(255) DEFAULT NULL,
    `photo2`      VARCHAR(255) DEFAULT NULL,
    `photo3`      VARCHAR(255) DEFAULT NULL,
    `entry_type`  ENUM('Store','Customer','Milestone') DEFAULT 'Store',
    `approved`    TINYINT(1)   DEFAULT 1,
    `date_add`    DATETIME     NOT NULL,
    `date_upd`    DATETIME     NOT NULL,
    PRIMARY KEY (`id_entry`),
    KEY `idx_product` (`id_product`),
    KEY `idx_customer` (`id_customer`),
    KEY `idx_approved_product` (`approved`, `id_product`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
