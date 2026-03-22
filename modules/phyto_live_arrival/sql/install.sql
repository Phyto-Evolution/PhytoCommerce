CREATE TABLE IF NOT EXISTS `PREFIX_phyto_lag_order` (
    `id_lag` INT AUTO_INCREMENT PRIMARY KEY,
    `id_order` INT NOT NULL,
    `lag_opted` TINYINT(1) NOT NULL DEFAULT 0,
    `fee_charged` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `date_add` DATETIME NOT NULL,
    UNIQUE KEY `uk_id_order` (`id_order`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_lag_claim` (
    `id_claim` INT AUTO_INCREMENT PRIMARY KEY,
    `id_order` INT NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `delivery_date` DATE NOT NULL,
    `issue_description` TEXT NOT NULL,
    `photo_filename` VARCHAR(255) DEFAULT NULL,
    `claim_status` ENUM('Received','Under Review','Approved','Rejected') NOT NULL DEFAULT 'Received',
    `store_notes` TEXT DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    KEY `idx_id_order` (`id_order`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
