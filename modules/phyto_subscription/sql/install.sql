CREATE TABLE IF NOT EXISTS `PREFIX_phyto_subscription_plan` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(200) NOT NULL,
  `plan_type` enum('Mystery','Replenishment','Custom') NOT NULL DEFAULT 'Mystery',
  `frequency` enum('weekly','monthly','quarterly') NOT NULL DEFAULT 'monthly',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_cycles` int(11) NOT NULL DEFAULT 0,
  `description` text,
  `cashfree_plan_id` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime DEFAULT NULL,
  PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_subscription_customer` (
  `id_sub` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `cashfree_subscription_id` varchar(150) DEFAULT NULL,
  `status` enum('created','active','paused','cancelled','completed') NOT NULL DEFAULT 'created',
  `start_date` date DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_sub`),
  KEY `idx_id_customer` (`id_customer`),
  KEY `idx_id_plan` (`id_plan`),
  KEY `idx_cashfree_subscription_id` (`cashfree_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
