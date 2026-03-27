-- Migration: phyto_tc_batch_tracker v1.0 → v1.1
-- Run once on existing installations.

ALTER TABLE `PREFIX_phyto_tc_batch`
    ADD COLUMN `parent_id_batch`  INT(11)    DEFAULT NULL COMMENT 'Mother batch for lineage tracking'
        AFTER `id_batch`,
    ADD COLUMN `low_stock_alerted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Alert already sent'
        AFTER `units_remaining`,
    ADD KEY `idx_parent_id_batch` (`parent_id_batch`);

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_tc_contamination_log` (
    `id_log`          INT(11)      NOT NULL AUTO_INCREMENT,
    `id_batch`        INT(11)      NOT NULL,
    `incident_date`   DATE         NOT NULL,
    `type`            ENUM('Bacterial','Fungal','Viral','Pest','Unknown','Other') NOT NULL DEFAULT 'Unknown',
    `affected_units`  INT(11)      NOT NULL DEFAULT 0,
    `description`     TEXT,
    `resolved`        TINYINT(1)   NOT NULL DEFAULT 0,
    `date_add`        DATETIME     NOT NULL,
    `date_upd`        DATETIME     NOT NULL,
    PRIMARY KEY (`id_log`),
    KEY `idx_id_batch` (`id_batch`),
    KEY `idx_incident_date` (`incident_date`),
    KEY `idx_resolved` (`resolved`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
