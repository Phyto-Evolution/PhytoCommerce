CREATE TABLE IF NOT EXISTS `PREFIX_phyto_tc_cost_estimate` (
  `id_estimate` int(11) NOT NULL AUTO_INCREMENT,
  `id_batch`    int(11) NOT NULL DEFAULT 0,
  `estimate_label` varchar(200) DEFAULT NULL,
  `inputs_json` text,
  `results_json` text,
  `date_add`    datetime DEFAULT NULL,
  PRIMARY KEY (`id_estimate`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
