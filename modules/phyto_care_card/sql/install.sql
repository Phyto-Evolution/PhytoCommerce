CREATE TABLE IF NOT EXISTS `PREFIX_phyto_care_card` (
    `id_care` INT AUTO_INCREMENT PRIMARY KEY,
    `id_product` INT NOT NULL UNIQUE,
    `light` VARCHAR(50) DEFAULT NULL,
    `water_type` VARCHAR(50) DEFAULT NULL,
    `water_method` VARCHAR(50) DEFAULT NULL,
    `humidity` VARCHAR(50) DEFAULT NULL,
    `temperature` VARCHAR(100) DEFAULT NULL,
    `media` TEXT,
    `feed` TEXT,
    `dormancy` TEXT,
    `potting` TEXT,
    `problems` TEXT,
    `date_upd` DATETIME DEFAULT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
