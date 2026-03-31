<?php
/**
 * Admin model — Phyto Grex Registry
 *
 * Handles DB install/uninstall and all CRUD operations
 * against the `oc_phyto_grex_registry` table.
 *
 * @package PhytoGrexRegistry
 * @platform OpenCart 3.x
 */

class ModelExtensionModulePhytoGrexRegistry extends Model {

    /**
     * Create the grex registry table on module install.
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "phyto_grex_registry` (
                `grex_registry_id` INT(11)      NOT NULL AUTO_INCREMENT,
                `product_id`       INT(11)      NOT NULL,
                `grex_id`          VARCHAR(100) NOT NULL DEFAULT '',
                `parent_a`         VARCHAR(200) NOT NULL DEFAULT '',
                `parent_b`         VARCHAR(200) NOT NULL DEFAULT '',
                `grex_year`        INT(4)       DEFAULT NULL,
                `registrant`       VARCHAR(200) NOT NULL DEFAULT '',
                `species_status`   VARCHAR(50)  NOT NULL DEFAULT 'hybrid',
                `taxonomy_pack`    VARCHAR(100) NOT NULL DEFAULT '',
                `notes`            TEXT,
                `date_added`       DATETIME     NOT NULL,
                `date_modified`    DATETIME     NOT NULL,
                PRIMARY KEY (`grex_registry_id`),
                KEY `idx_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    /**
     * Drop the grex registry table on module uninstall.
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "phyto_grex_registry`");
    }

    /**
     * Retrieve all grex records, newest first.
     *
     * @return array
     */
    public function getAllRecords() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "phyto_grex_registry`
            ORDER BY `date_modified` DESC
        ");
        return $query->rows;
    }

    /**
     * Retrieve a single grex record by its primary key.
     *
     * @param int $grex_registry_id
     * @return array|false
     */
    public function getRecord($grex_registry_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "phyto_grex_registry`
            WHERE `grex_registry_id` = '" . (int)$grex_registry_id . "'
            LIMIT 1
        ");
        return $query->row ?: false;
    }

    /**
     * Retrieve a grex record by product ID.
     *
     * @param int $product_id
     * @return array|false
     */
    public function getRecordByProduct($product_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "phyto_grex_registry`
            WHERE `product_id` = '" . (int)$product_id . "'
            LIMIT 1
        ");
        return $query->row ?: false;
    }

    /**
     * Insert a new grex record.
     *
     * @param array $data Associative array of field values.
     * @return int Last insert ID.
     */
    public function addRecord(array $data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "phyto_grex_registry`
                (`product_id`, `grex_id`, `parent_a`, `parent_b`, `grex_year`,
                 `registrant`, `species_status`, `taxonomy_pack`, `notes`,
                 `date_added`, `date_modified`)
            VALUES (
                '" . (int)$data['product_id'] . "',
                '" . $this->db->escape($data['grex_id']) . "',
                '" . $this->db->escape($data['parent_a']) . "',
                '" . $this->db->escape($data['parent_b']) . "',
                " . ($data['grex_year'] ? (int)$data['grex_year'] : 'NULL') . ",
                '" . $this->db->escape($data['registrant']) . "',
                '" . $this->db->escape($data['species_status']) . "',
                '" . $this->db->escape($data['taxonomy_pack']) . "',
                '" . $this->db->escape($data['notes']) . "',
                NOW(),
                NOW()
            )
        ");
        return $this->db->getLastId();
    }

    /**
     * Update an existing grex record.
     *
     * @param int   $grex_registry_id
     * @param array $data Associative array of field values.
     */
    public function editRecord($grex_registry_id, array $data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "phyto_grex_registry` SET
                `product_id`     = '" . (int)$data['product_id'] . "',
                `grex_id`        = '" . $this->db->escape($data['grex_id']) . "',
                `parent_a`       = '" . $this->db->escape($data['parent_a']) . "',
                `parent_b`       = '" . $this->db->escape($data['parent_b']) . "',
                `grex_year`      = " . ($data['grex_year'] ? (int)$data['grex_year'] : 'NULL') . ",
                `registrant`     = '" . $this->db->escape($data['registrant']) . "',
                `species_status` = '" . $this->db->escape($data['species_status']) . "',
                `taxonomy_pack`  = '" . $this->db->escape($data['taxonomy_pack']) . "',
                `notes`          = '" . $this->db->escape($data['notes']) . "',
                `date_modified`  = NOW()
            WHERE `grex_registry_id` = '" . (int)$grex_registry_id . "'
        ");
    }

    /**
     * Delete a grex record.
     *
     * @param int $grex_registry_id
     */
    public function deleteRecord($grex_registry_id) {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "phyto_grex_registry`
            WHERE `grex_registry_id` = '" . (int)$grex_registry_id . "'
        ");
    }
}
