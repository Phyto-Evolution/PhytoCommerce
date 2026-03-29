CREATE TABLE IF NOT EXISTS `PREFIX_phyto_kyc_profile` (
  `id_kyc_profile`   int(11)      NOT NULL AUTO_INCREMENT,
  `id_customer`      int(11)      NOT NULL,
  `kyc_level`        tinyint(1)   NOT NULL DEFAULT 0,
  `level1_status`    enum('NotStarted','Pending','Verified','Rejected') NOT NULL DEFAULT 'NotStarted',
  `level2_status`    enum('NotStarted','Pending','Verified','Rejected') NOT NULL DEFAULT 'NotStarted',
  `pan_number`       varchar(20)  DEFAULT NULL,
  `pan_name`         varchar(200) DEFAULT NULL,
  `gst_number`       varchar(20)  DEFAULT NULL,
  `business_pan`     varchar(20)  DEFAULT NULL,
  `business_name`    varchar(200) DEFAULT NULL,
  `api_response_l1`  text,
  `api_response_l2`  text,
  `admin_notes`      text,
  `reviewed_by`      int(11)      DEFAULT NULL,
  `date_add`         datetime     DEFAULT NULL,
  `date_upd`         datetime     DEFAULT NULL,
  PRIMARY KEY (`id_kyc_profile`),
  UNIQUE KEY `id_customer` (`id_customer`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_phyto_kyc_document` (
  `id_document`     int(11)      NOT NULL AUTO_INCREMENT,
  `id_kyc_profile`  int(11)      NOT NULL,
  `id_customer`     int(11)      NOT NULL,
  `kyc_level`       tinyint(1)   NOT NULL DEFAULT 1,
  `doc_type`        varchar(50)  NOT NULL,
  `file_path`       varchar(500) NOT NULL,
  `file_name`       varchar(255) NOT NULL,
  `mime_type`       varchar(100) DEFAULT NULL,
  `date_add`        datetime     DEFAULT NULL,
  PRIMARY KEY (`id_document`),
  KEY `id_kyc_profile` (`id_kyc_profile`),
  KEY `id_customer` (`id_customer`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
