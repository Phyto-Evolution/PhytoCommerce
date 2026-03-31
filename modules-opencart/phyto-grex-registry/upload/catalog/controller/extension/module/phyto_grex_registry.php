<?php
/**
 * Catalog controller — Phyto Grex Registry
 *
 * Hooks into the `catalog/view/product/product/before` event to inject a
 * "Grex Registry" tab into the product page tabs array when grex data exists
 * for the current product.
 *
 * Event registration (add via admin → System → Events or via install()):
 *   trigger: catalog/view/product/product/before
 *   action:  extension/module/phyto_grex_registry/injectTab
 *
 * @package PhytoGrexRegistry
 * @platform OpenCart 3.x
 */

class ControllerExtensionModulePhytoGrexRegistry extends Controller {

    /**
     * Event callback — injects the Grex Registry tab into product page data.
     *
     * @param string $route  The route that triggered the event (unused).
     * @param array  &$data  The product page data array passed by reference.
     */
    public function injectTab($route, &$data) {
        $this->load->language('extension/module/phyto_grex_registry');
        $this->load->model('extension/module/phyto_grex_registry');

        // Resolve product_id from route GET param or data array.
        $product_id = 0;
        if (isset($this->request->get['product_id'])) {
            $product_id = (int)$this->request->get['product_id'];
        } elseif (isset($data['product_id'])) {
            $product_id = (int)$data['product_id'];
        }

        if (!$product_id) {
            return;
        }

        $record = $this->model_extension_module_phyto_grex_registry->getRecordByProduct($product_id);

        if (!$record) {
            return;
        }

        // Build fields array — skip empty values.
        $language = $this->language;
        $status_map = array(
            'hybrid'   => $language->get('status_hybrid'),
            'species'  => $language->get('status_species'),
            'cultivar' => $language->get('status_cultivar'),
            'variety'  => $language->get('status_variety'),
        );

        $fields = array();

        if (!empty($record['grex_id'])) {
            $fields[] = array('label' => $language->get('label_grex_id'),        'value' => $record['grex_id']);
        }
        if (!empty($record['parent_a'])) {
            $fields[] = array('label' => $language->get('label_parent_a'),       'value' => $record['parent_a']);
        }
        if (!empty($record['parent_b'])) {
            $fields[] = array('label' => $language->get('label_parent_b'),       'value' => $record['parent_b']);
        }
        if (!empty($record['grex_year'])) {
            $fields[] = array('label' => $language->get('label_grex_year'),      'value' => $record['grex_year']);
        }
        if (!empty($record['registrant'])) {
            $fields[] = array('label' => $language->get('label_registrant'),     'value' => $record['registrant']);
        }
        if (!empty($record['species_status'])) {
            $status_label = isset($status_map[$record['species_status']])
                ? $status_map[$record['species_status']]
                : ucfirst($record['species_status']);
            $fields[] = array('label' => $language->get('label_species_status'), 'value' => $status_label);
        }
        if (!empty($record['taxonomy_pack'])) {
            $fields[] = array('label' => $language->get('label_taxonomy_pack'),  'value' => $record['taxonomy_pack']);
        }
        if (!empty($record['notes'])) {
            $fields[] = array('label' => $language->get('label_notes'),          'value' => $record['notes'], 'is_notes' => true);
        }

        if (empty($fields)) {
            return;
        }

        // Render the tab content via the dedicated view.
        $tab_data = array(
            'heading_grex_registry' => $language->get('heading_grex_registry'),
            'fields'                => $fields,
        );

        $tab_content = $this->load->view('extension/module/phyto_grex_registry', $tab_data);

        // Append tab to the data tabs array (OC3 product page tab structure).
        if (!isset($data['tabs'])) {
            $data['tabs'] = array();
        }

        $data['tabs'][] = array(
            'title'   => $language->get('tab_grex_registry'),
            'content' => $tab_content,
        );
    }
}
