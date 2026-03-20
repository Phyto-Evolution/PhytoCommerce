CREATE TABLE IF NOT EXISTS `PREFIX_phyto_climate_product` (
    `id_climate` INT AUTO_INCREMENT PRIMARY KEY,
    `id_product` INT NOT NULL UNIQUE,
    `suitable_zones` TEXT,
    `min_temp` INT DEFAULT NULL,
    `max_temp` INT DEFAULT NULL,
    `cannot_tolerate` TEXT,
    `outdoor_notes` TEXT
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
