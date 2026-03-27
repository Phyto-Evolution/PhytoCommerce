CREATE TABLE IF NOT EXISTS `PREFIX_phyto_erp_sync_log` (
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `sync_type`  varchar(32)  NOT NULL COMMENT 'order|customer|product|invoice',
    `direction`  varchar(8)   NOT NULL DEFAULT 'push',
    `ps_id`      int(11)               DEFAULT NULL COMMENT 'PrestaShop object ID',
    `erp_name`   varchar(255)          DEFAULT NULL COMMENT 'ERPNext document name',
    `status`     varchar(16)  NOT NULL DEFAULT 'success' COMMENT 'success|error|skipped',
    `message`    text,
    `created_at` datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `sync_type` (`sync_type`),
    KEY `ps_id` (`ps_id`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
