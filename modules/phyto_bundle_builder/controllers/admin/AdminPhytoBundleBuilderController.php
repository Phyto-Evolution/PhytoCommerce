<?php
/**
 * AdminPhytoBundleBuilderController
 *
 * Back-office CRUD for bundle templates and their slots.
 *
 * List view   — shows all bundles with quick active toggle
 * Edit/Add    — form for bundle name, description, discount, active flag
 *               + embedded slot management (add / remove / reorder slots)
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_bundle_builder/classes/PhytoBundle.php';
require_once _PS_MODULE_DIR_ . 'phyto_bundle_builder/classes/PhytoBundleSlot.php';

class AdminPhytoBundleBuilderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'phyto_bundle';
        $this->className  = 'PhytoBundle';
        $this->identifier = 'id_bundle';
        $this->lang       = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->meta_title = $this->l('Bundle Builder');

        // -----------------------------------------------------------------------
        // List columns
        // -----------------------------------------------------------------------
        $this->fields_list = [
            'id_bundle' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title'      => $this->l('Bundle Name'),
                'filter_key' => 'b!name',
            ],
            'discount_type' => [
                'title'      => $this->l('Discount Type'),
                'type'       => 'select',
                'list'       => [
                    'percent' => $this->l('Percent (%)'),
                    'amount'  => $this->l('Fixed Amount'),
                ],
                'filter_key' => 'a!discount_type',
            ],
            'discount_value' => [
                'title'      => $this->l('Discount Value'),
                'type'       => 'price',
                'align'      => 'right',
                'filter_key' => 'a!discount_value',
            ],
            'active' => [
                'title'   => $this->l('Active'),
                'active'  => 'status',
                'type'    => 'bool',
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
                'orderby' => false,
            ],
            'date_add' => [
                'title'  => $this->l('Created'),
                'type'   => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected bundles?'),
                'icon'    => 'icon-trash',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Custom getList: join lang table for name column
    // -------------------------------------------------------------------------

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ): void {
        $this->_select = 'bl.`name`';
        $this->_join   = 'LEFT JOIN `' . _DB_PREFIX_ . 'phyto_bundle_lang` bl
            ON bl.`id_bundle` = a.`id_bundle`
            AND bl.`id_lang` = ' . (int) $id_lang;
        $this->_use_found_rows = true;

        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
    }

    // -------------------------------------------------------------------------
    // Toolbar: add "Preview" link when editing a bundle
    // -------------------------------------------------------------------------

    public function initPageHeaderToolbar(): void
    {
        parent::initPageHeaderToolbar();

        if ($this->display === 'edit') {
            $idBundle = (int) Tools::getValue('id_bundle');
            if ($idBundle > 0) {
                $previewUrl = Context::getContext()->link->getModuleLink(
                    'phyto_bundle_builder',
                    'builder',
                    ['id_bundle' => $idBundle]
                );
                $this->page_header_toolbar_btn['preview'] = [
                    'href'   => $previewUrl,
                    'desc'   => $this->l('Preview Bundle Page'),
                    'icon'   => 'process-icon-preview',
                    'target' => true,
                ];
            }
        }
    }

    // -------------------------------------------------------------------------
    // Edit form
    // -------------------------------------------------------------------------

    public function renderForm(): string
    {
        $idLang     = (int) $this->context->language->id;
        $idBundle   = (int) Tools::getValue('id_bundle');
        $slots      = $idBundle > 0 ? PhytoBundle::getSlots($idBundle) : [];

        // Build category options for slot form
        $categories = Category::getSimpleCategories($idLang);
        array_unshift($categories, ['id_category' => 0, 'name' => $this->l('Any category')]);

        // Pass slot data and helpers to Smarty for the configure template section
        $this->context->smarty->assign([
            'phyto_id_bundle'          => $idBundle,
            'phyto_slots'              => $slots,
            'phyto_categories'         => $categories,
            'phyto_max_slots'          => (int) Configuration::get('PHYTO_BUNDLE_MAX_SLOTS', null, null, null, 5),
            'phyto_add_slot_url'       => self::$currentIndex . '&id_bundle=' . $idBundle . '&addSlot=1&token=' . $this->token,
            'phyto_delete_slot_url'    => self::$currentIndex . '&id_bundle=' . $idBundle . '&deleteSlot=1&token=' . $this->token,
            'phyto_reorder_slot_url'   => self::$currentIndex . '&id_bundle=' . $idBundle . '&reorderSlots=1&token=' . $this->token,
            'phyto_admin_token'        => $this->token,
            'phyto_current_index'      => self::$currentIndex,
        ]);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Bundle Template'),
                'icon'  => 'icon-layers',
            ],
            'input' => [
                [
                    'type'     => 'hidden',
                    'name'     => 'id_bundle',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Bundle Name'),
                    'name'     => 'name',
                    'lang'     => true,
                    'required' => true,
                    'size'     => 255,
                    'desc'     => $this->l('Displayed to customers on the builder page.'),
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Description'),
                    'name'  => 'description',
                    'lang'  => true,
                    'rows'  => 3,
                    'desc'  => $this->l('Short description shown on the bundle listing.'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Discount Type'),
                    'name'    => 'discount_type',
                    'options' => [
                        'query' => [
                            ['id' => 'percent', 'name' => $this->l('Percentage (%)')],
                            ['id' => 'amount',  'name' => $this->l('Fixed Amount')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $this->l('How the bundle discount is calculated.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Discount Value'),
                    'name'  => 'discount_value',
                    'class' => 'fixed-width-sm',
                    'desc'  => $this->l('e.g. 10 for 10% off or ₹10 off depending on type.'),
                ],
                [
                    'type'    => 'switch',
                    'label'   => $this->l('Active'),
                    'name'    => 'active',
                    'is_bool' => true,
                    'values'  => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
                [
                    'type'   => 'free',
                    'label'  => $this->l('Slots'),
                    'name'   => 'phyto_slots_manager',
                    'desc'   => $this->l('Save the bundle first, then manage its slots below.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    // -------------------------------------------------------------------------
    // postProcess: handle slot add / delete / reorder AJAX actions
    // -------------------------------------------------------------------------

    public function postProcess(): void
    {
        // ---- Add slot ----
        if (Tools::isSubmit('addSlot')) {
            $this->processAddSlot();
            return;
        }

        // ---- Delete slot ----
        if (Tools::isSubmit('deleteSlot')) {
            $this->processDeleteSlot();
            return;
        }

        // ---- Reorder slots ----
        if (Tools::isSubmit('reorderSlots')) {
            $this->processReorderSlots();
            return;
        }

        parent::postProcess();
    }

    // -------------------------------------------------------------------------
    // Slot management helpers
    // -------------------------------------------------------------------------

    private function processAddSlot(): void
    {
        $idBundle = (int) Tools::getValue('id_bundle');
        if (!$idBundle) {
            $this->errors[] = $this->l('Invalid bundle ID.');
            return;
        }

        $maxSlots     = (int) Configuration::get('PHYTO_BUNDLE_MAX_SLOTS', null, null, null, 5);
        $currentCount = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_bundle_slot`
             WHERE `id_bundle` = ' . $idBundle
        );

        if ($currentCount >= $maxSlots) {
            $this->errors[] = sprintf(
                $this->l('Maximum of %d slots per bundle reached.'),
                $maxSlots
            );
            $this->redirect_after = self::$currentIndex . '&id_bundle=' . $idBundle . '&updatephyto_bundle=1&token=' . $this->token;
            return;
        }

        $slotName   = trim(Tools::getValue('slot_name', ''));
        $slotType   = trim(Tools::getValue('slot_type', ''));
        $idCategory = (int) Tools::getValue('id_category');
        $required   = (int) Tools::getValue('required', 1);
        $position   = $currentCount; // append at end

        if (empty($slotName)) {
            $this->errors[] = $this->l('Slot name is required.');
            $this->redirect_after = self::$currentIndex . '&id_bundle=' . $idBundle . '&updatephyto_bundle=1&token=' . $this->token;
            return;
        }

        $slot              = new PhytoBundleSlot();
        $slot->id_bundle   = $idBundle;
        $slot->slot_name   = pSQL($slotName);
        $slot->slot_type   = pSQL($slotType);
        $slot->id_category = $idCategory;
        $slot->required    = $required ? 1 : 0;
        $slot->position    = $position;

        if ($slot->add()) {
            $this->confirmations[] = $this->l('Slot added successfully.');
        } else {
            $this->errors[] = $this->l('Could not add slot.');
        }

        $this->redirect_after = self::$currentIndex . '&id_bundle=' . $idBundle . '&updatephyto_bundle=1&token=' . $this->token;
    }

    private function processDeleteSlot(): void
    {
        $idSlot   = (int) Tools::getValue('id_slot');
        $idBundle = (int) Tools::getValue('id_bundle');

        if (!$idSlot) {
            $this->errors[] = $this->l('Invalid slot ID.');
            $this->redirect_after = self::$currentIndex . '&id_bundle=' . $idBundle . '&updatephyto_bundle=1&token=' . $this->token;
            return;
        }

        $slot = new PhytoBundleSlot($idSlot);
        if (!Validate::isLoadedObject($slot)) {
            $this->errors[] = $this->l('Slot not found.');
        } elseif ($slot->delete()) {
            $this->confirmations[] = $this->l('Slot deleted.');
        } else {
            $this->errors[] = $this->l('Could not delete slot.');
        }

        $this->redirect_after = self::$currentIndex . '&id_bundle=' . $idBundle . '&updatephyto_bundle=1&token=' . $this->token;
    }

    private function processReorderSlots(): void
    {
        $idBundle   = (int) Tools::getValue('id_bundle');
        $orderRaw   = Tools::getValue('slot_order', '');  // comma-separated slot IDs

        if (!$idBundle || !$orderRaw) {
            $this->ajaxResponse(['success' => false, 'error' => 'Missing parameters']);
            return;
        }

        $ids = array_map('intval', explode(',', $orderRaw));
        foreach ($ids as $position => $idSlot) {
            if ($idSlot > 0) {
                Db::getInstance()->update(
                    'phyto_bundle_slot',
                    ['position' => (int) $position],
                    '`id_slot` = ' . $idSlot . ' AND `id_bundle` = ' . $idBundle
                );
            }
        }

        $this->ajaxResponse(['success' => true]);
    }

    /**
     * Output JSON and exit (used for AJAX slot actions).
     */
    private function ajaxResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
