-- Phyto Source Badge — uninstall.sql
-- Drops all tables created by install.sql.
-- The token PREFIX_ is replaced at runtime with _DB_PREFIX_.

DROP TABLE IF EXISTS `PREFIX_phyto_source_badge_product`;
DROP TABLE IF EXISTS `PREFIX_phyto_source_badge_def`
