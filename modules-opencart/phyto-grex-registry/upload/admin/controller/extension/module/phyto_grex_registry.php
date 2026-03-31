<?php
/**
 * Admin controller — Phyto Grex Registry
 *
 * Provides a standalone module settings page that lists all grex records
 * and exposes an Add / Edit form. Called from Extensions > Modules.
 *
 * @package PhytoGrexRegistry
 * @platform OpenCart 3.x
 */

class ControllerExtensionModulePhytoGrexRegistry extends Controller {

    /** @var array Validation errors */
    private $error = array();

    /** @var array Allowed species_status values */
    private $valid_statuses = array('hybrid', 'species', 'cultivar', 'variety');

    /**
     * Module index / list page.
     */
    public function index() {
        $this->load->language('extension/module/phyto_grex_registry');
        $this->load->model('extension/module/phyto_grex_registry');

        $this->document->setTitle($this->language->get('heading_title'));

        // Handle form submission (add or edit).
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $data_post = $this->request->post;

            $record_data = array(
                'product_id'     => (int)$data_post['product_id'],
                'grex_id'        => trim($data_post['grex_id']),
                'parent_a'       => trim($data_post['parent_a']),
                'parent_b'       => trim($data_post['parent_b']),
                'grex_year'      => !empty($data_post['grex_year']) ? (int)$data_post['grex_year'] : null,
                'registrant'     => trim($data_post['registrant']),
                'species_status' => in_array($data_post['species_status'], $this->valid_statuses, true)
                                        ? $data_post['species_status'] : 'hybrid',
                'taxonomy_pack'  => trim($data_post['taxonomy_pack']),
                'notes'          => trim($data_post['notes']),
            );

            if (!empty($data_post['grex_registry_id'])) {
                $this->model_extension_module_phyto_grex_registry->editRecord(
                    (int)$data_post['grex_registry_id'],
                    $record_data
                );
            } else {
                $this->model_extension_module_phyto_grex_registry->addRecord($record_data);
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link(
                'extension/module/phyto_grex_registry', 'user_token=' . $this->session->data['user_token'], true
            ));
        }

        // Build view data.
        $data = array();

        $data['heading_title'] = $this->language->get('heading_title');

        // Breadcrumbs.
        $data['breadcrumbs'] = array(
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ),
            array(
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/phyto_grex_registry', 'user_token=' . $this->session->data['user_token'], true),
            ),
        );

        // Success/error flash.
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_product_id'] = isset($this->error['product_id']) ? $this->error['product_id'] : '';
        $data['error_grex_id']    = isset($this->error['grex_id'])    ? $this->error['grex_id']    : '';

        // Form action URL.
        $data['action'] = $this->url->link(
            'extension/module/phyto_grex_registry', 'user_token=' . $this->session->data['user_token'], true
        );
        $data['cancel'] = $this->url->link(
            'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
        );
        $data['delete_url'] = $this->url->link(
            'extension/module/phyto_grex_registry/delete', 'user_token=' . $this->session->data['user_token'], true
        );

        // Pre-populate form if editing.
        $edit_id = isset($this->request->get['edit_id']) ? (int)$this->request->get['edit_id'] : 0;
        if ($edit_id) {
            $edit_record = $this->model_extension_module_phyto_grex_registry->getRecord($edit_id);
        } else {
            $edit_record = false;
        }

        $data['edit_record']    = $edit_record;
        $data['valid_statuses'] = $this->valid_statuses;

        // Language strings for the template.
        $lang_keys = array(
            'text_add_record', 'text_all_records', 'text_none', 'text_confirm_delete',
            'column_grex_registry_id', 'column_product_id', 'column_grex_id',
            'column_parent_a', 'column_parent_b', 'column_grex_year', 'column_registrant',
            'column_species_status', 'column_taxonomy_pack', 'column_date_modified', 'column_action',
            'entry_product_id', 'entry_grex_id', 'entry_parent_a', 'entry_parent_b',
            'entry_grex_year', 'entry_registrant', 'entry_species_status',
            'entry_taxonomy_pack', 'entry_notes',
            'option_hybrid', 'option_species', 'option_cultivar', 'option_variety',
            'button_save', 'button_cancel', 'button_add', 'button_delete',
        );
        foreach ($lang_keys as $key) {
            $data[$key] = $this->language->get($key);
        }

        // All records for the table listing.
        $data['records'] = $this->model_extension_module_phyto_grex_registry->getAllRecords();

        // User token for edit links inside the table.
        $data['user_token'] = $this->session->data['user_token'];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/phyto_grex_registry', $data));
    }

    /**
     * Delete a grex record via GET param `delete_id`.
     */
    public function delete() {
        $this->load->language('extension/module/phyto_grex_registry');
        $this->load->model('extension/module/phyto_grex_registry');

        if (!$this->user->hasPermission('modify', 'extension/module/phyto_grex_registry')) {
            $this->session->data['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->get['delete_id'])) {
            $this->model_extension_module_phyto_grex_registry->deleteRecord((int)$this->request->get['delete_id']);
            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link(
            'extension/module/phyto_grex_registry', 'user_token=' . $this->session->data['user_token'], true
        ));
    }

    /**
     * Called by OpenCart when the module is enabled from the Extensions list.
     */
    public function install() {
        $this->load->model('extension/module/phyto_grex_registry');
        $this->model_extension_module_phyto_grex_registry->install();
    }

    /**
     * Called by OpenCart when the module is disabled/removed.
     */
    public function uninstall() {
        $this->load->model('extension/module/phyto_grex_registry');
        $this->model_extension_module_phyto_grex_registry->uninstall();
    }

    /**
     * Validate POST data before saving.
     *
     * @return bool
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/phyto_grex_registry')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['product_id']) || (int)$this->request->post['product_id'] < 1) {
            $this->error['product_id'] = $this->language->get('error_product_id');
        }

        if (empty(trim($this->request->post['grex_id']))) {
            $this->error['grex_id'] = $this->language->get('error_grex_id');
        }

        return empty($this->error);
    }
}
