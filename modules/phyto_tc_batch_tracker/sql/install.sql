CREATE TABLE IF NOT EXISTS `PREFIX_phyto_tc_batch` (
    `id_batch`            INT(11)      NOT NULL AUTO_INCREMENT,
    `batch_code`          VARCHAR(50)  NOT NULL,
    `species_name`        VARCHAR(200) NOT NULL DEFAULT '',
    `generation`          ENUM('G0','G1','G2','G3+','Acclimated','Hardened') NOT NULL DEFAULT 'G0',
    `date_initiation`     DATE         DEFAULT NULL,
    `date_deflask`        DATE         DEFAULT NULL,
    `date_certified`      DATE         DEFAULT NULL,
    `sterility_protocol`  TEXT,
    `units_produced`      INT(11)      NOT NULL DEFAULT 0,
    `units_remaining`     INT(11)      NOT NULL DEFAULT 0,
    `batch_status`        ENUM('Active','Depleted','Quarantined','Archived') NOT NULL DEFAULT 'Active',
    `notes`               TEXT,
    `date_add`            DATETIME     NOT NULL,
    `date_upd`            DATETIME     NOT NULL,
    PRIMARY KEY (`id_batch`),
    UNIQUE KEY `batch_code` (`batch_code`),
    KEY `idx_batch_status` (`batch_status`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_tc_batch_product` (
    `id_link`               INT(11) NOT NULL AUTO_INCREMENT,
    `id_product`            INT(11) NOT NULL,
    `id_product_attribute`  INT(11) NOT NULL DEFAULT 0,
    `id_batch`              INT(11) NOT NULL,
    PRIMARY KEY (`id_link`),
    UNIQUE KEY `product_attribute_batch` (`id_product`, `id_product_attribute`),
    KEY `idx_id_batch` (`id_batch`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
