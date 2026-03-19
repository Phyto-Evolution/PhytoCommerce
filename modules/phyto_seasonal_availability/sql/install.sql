CREATE TABLE IF NOT EXISTS `PREFIX_phyto_seasonal_product` (
    `id_seasonal`       INT          AUTO_INCREMENT PRIMARY KEY,
    `id_product`        INT          NOT NULL,
    `ship_months`       VARCHAR(50)  DEFAULT NULL,
    `dormancy_months`   VARCHAR(50)  DEFAULT NULL,
    `block_purchase`    TINYINT(1)   DEFAULT 0,
    `out_of_season_msg` VARCHAR(255) DEFAULT NULL,
    `enable_notify`     TINYINT(1)   DEFAULT 1,
    UNIQUE KEY `uk_product` (`id_product`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_seasonal_notify` (
    `id_notify`   INT          AUTO_INCREMENT PRIMARY KEY,
    `id_product`  INT          NOT NULL,
    `email`       VARCHAR(150) NOT NULL,
    `name`        VARCHAR(100) DEFAULT NULL,
    `notified`    TINYINT(1)   DEFAULT 0,
    `date_add`    DATETIME     NOT NULL,
    KEY `idx_product` (`id_product`),
    KEY `idx_email`   (`email`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
