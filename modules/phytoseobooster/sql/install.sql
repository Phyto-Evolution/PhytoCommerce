CREATE TABLE IF NOT EXISTS `PREFIX_phyto_seo_audit` (
    `id_audit`    int(11)      NOT NULL AUTO_INCREMENT,
    `id_product`  int(11)      NOT NULL,
    `id_lang`     int(11)      NOT NULL DEFAULT 1,
    `score`       tinyint(3)   NOT NULL DEFAULT 0 COMMENT '0-100 SEO score',
    `issues_json` text         COMMENT 'JSON array of issue codes',
    `date_audited` datetime    NOT NULL,
    PRIMARY KEY (`id_audit`),
    UNIQUE KEY `product_lang` (`id_product`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
