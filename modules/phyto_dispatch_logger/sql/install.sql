CREATE TABLE IF NOT EXISTS `PREFIX_phyto_dispatch_log` (
  `id_log`         int(11)          NOT NULL AUTO_INCREMENT,
  `id_order`       int(11)          NOT NULL,
  `dispatch_date`  date             DEFAULT NULL,
  `temp_celsius`   decimal(4,1)     DEFAULT NULL,
  `humidity_pct`   int(11)          DEFAULT NULL,
  `packing_method` varchar(100)     DEFAULT NULL,
  `gel_pack`       tinyint(1)       NOT NULL DEFAULT 0,
  `heat_pack`      tinyint(1)       NOT NULL DEFAULT 0,
  `transit_days`   int(11)          DEFAULT NULL,
  `staff_name`     varchar(100)     DEFAULT NULL,
  `notes`          text,
  `photo_filename` varchar(255)     DEFAULT NULL,
  `date_add`       datetime         DEFAULT NULL,
  `date_upd`       datetime         DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  UNIQUE KEY `id_order` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
