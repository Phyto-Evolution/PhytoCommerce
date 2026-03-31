<?php
/**
 * Catalog model — Phyto Grex Registry
 *
 * Provides a read-only lookup of grex data for the frontend product page.
 *
 * @package PhytoGrexRegistry
 * @platform OpenCart 3.x
 */

class ModelExtensionModulePhytoGrexRegistry extends Model {

    /**
     * Retrieve the grex record for a given product.
     *
     * @param int $product_id OpenCart product ID.
     * @return array|false Row array or false if not found.
     */
    public function getRecordByProduct($product_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "phyto_grex_registry`
            WHERE `product_id` = '" . (int)$product_id . "'
            LIMIT 1
        ");
        return $query->row ?: false;
    }
}
