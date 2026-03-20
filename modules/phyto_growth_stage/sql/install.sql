CREATE TABLE IF NOT EXISTS `PREFIX_phyto_growth_stage_def` (
    `id_stage`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `stage_name`     VARCHAR(100) NOT NULL,
    `stage_code`     VARCHAR(50) NOT NULL,
    `difficulty`     ENUM('Beginner','Intermediate','Advanced','Expert') NOT NULL DEFAULT 'Beginner',
    `weeks_to_next`  INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `description`    TEXT,
    `sort_order`     INT(10) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_stage`),
    UNIQUE KEY `stage_code` (`stage_code`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_growth_stage_product` (
    `id_link`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product`            INT(10) UNSIGNED NOT NULL,
    `id_product_attribute`  INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_stage`              INT(10) UNSIGNED NOT NULL,
    `weeks_override`        INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id_link`),
    UNIQUE KEY `product_attribute_stage` (`id_product`, `id_product_attribute`),
    KEY `idx_product` (`id_product`),
    KEY `idx_stage` (`id_stage`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
