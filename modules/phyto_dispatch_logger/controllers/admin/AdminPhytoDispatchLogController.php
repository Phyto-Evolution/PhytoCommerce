<?php
/**
 * AdminPhytoDispatchLogController
 *
 * Back-office controller for creating and editing dispatch log entries.
 * Provides a HelperList overview and a HelperForm for add/edit operations.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   https://opensource.org/licenses/AFL-3.0 AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/PhytoDispatchLog.php';

class AdminPhytoDispatchLogController extends ModuleAdminController
{
    /** @var int Maximum allowed photo file size in bytes (2 MB) */
    const MAX_PHOTO_SIZE = 2097152;

    /** @var string[] Allowed MIME types for photo uploads */
    const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'phyto_dispatch_log';
        $this->className   = 'PhytoDispatchLog';
        $this->identifier  = 'id_log';
        $this->lang        = false;
        $this->explicitSelect = true;
        $this->allow_export   = true;
        $this->deleted        = false;

        parent::__construct();

        $this->meta_title = $this->l('Dispatch Logs');

        // HelperList column definitions
        $this->fields_list = [
            'id_log' => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'class'   => 'fixed-width-xs',
            ],
            'id_order' => [
                'title'   => $this->l('Order'),
                'align'   => 'center',
                'callback' => 'renderOrderLink',
            ],
            'staff_name' => [
                'title'   => $this->l('Staff'),
            ],
            'dispatch_date' => [
                'title'   => $this->l('Dispatch Date'),
                'type'    => 'date',
                'align'   => 'center',
            ],
            'temp_celsius' => [
                'title'   => $this->l('Temp (°C)'),
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
            ],
            'humidity_pct' => [
                'title'   => $this->l('Humidity (%)'),
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
            ],
            'packing_method' => [
                'title'   => $this->l('Packing Method'),
            ],
            'transit_days' => [
                'title'   => $this->l('Transit (days)'),
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
            ],
        ];

        // Bulk actions
        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete the selected log entries?'),
                'icon'    => 'icon-trash',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // HelperList callback
    // -------------------------------------------------------------------------

    /**
     * Render a clickable link to the order in the list.
     *
     * @param mixed  $value  Raw id_order value
     * @param array  $row    Full row data
     *
     * @return string
     */
    public function renderOrderLink($value, array $row): string
    {
        $idOrder = (int) $value;
        $url     = $this->context->link->getAdminLink('AdminOrders')
            . '&id_order=' . $idOrder
            . '&vieworder';

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_blank">'
            . $this->l('Order') . ' #' . $idOrder
            . '</a>';
    }

    // -------------------------------------------------------------------------
    // HelperForm
    // -------------------------------------------------------------------------

    /**
     * Build and return the HelperForm for add/edit operations.
     *
     * @return string Rendered form HTML
     */
    public function renderForm(): string
    {
        $packingMethods = PhytoDispatchLog::getPackingMethods();

        // Pre-fill id_order when arriving from an order page deep-link
        $defaultIdOrder = (int) Tools::getValue('id_order', 0);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Dispatch Log Entry'),
                'icon'  => 'icon-truck',
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Order ID'),
                    'name'     => 'id_order',
                    'required' => true,
                    'class'    => 'fixed-width-md',
                    'hint'     => $this->l('The PrestaShop order ID this log belongs to.'),
                    'desc'     => $this->l('One log entry is allowed per order.'),
                ],
                [
                    'type'    => 'date',
                    'label'   => $this->l('Dispatch Date'),
                    'name'    => 'dispatch_date',
                    'class'   => 'fixed-width-md',
                    'hint'    => $this->l('Date the parcel was handed to the carrier.'),
                ],
                [
                    'type'    => 'text',
                    'label'   => $this->l('Temperature (°C)'),
                    'name'    => 'temp_celsius',
                    'class'   => 'fixed-width-sm',
                    'hint'    => $this->l('Ambient temperature at packing time, e.g. 18.5'),
                ],
                [
                    'type'    => 'text',
                    'label'   => $this->l('Humidity (%)'),
                    'name'    => 'humidity_pct',
                    'class'   => 'fixed-width-sm',
                    'hint'    => $this->l('Relative humidity percentage at packing time, e.g. 65'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Packing Method'),
                    'name'    => 'packing_method',
                    'options' => [
                        'query' => $packingMethods,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    'hint'    => $this->l('Primary packing method used for this shipment.'),
                ],
                [
                    'type'    => 'switch',
                    'label'   => $this->l('Gel Pack Included'),
                    'name'    => 'gel_pack',
                    'values'  => [
                        ['id' => 'gel_pack_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'gel_pack_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                    'hint'    => $this->l('Was a gel/ice pack included to keep the parcel cool?'),
                ],
                [
                    'type'    => 'switch',
                    'label'   => $this->l('Heat Pack Included'),
                    'name'    => 'heat_pack',
                    'values'  => [
                        ['id' => 'heat_pack_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'heat_pack_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                    'hint'    => $this->l('Was a heat pack included to keep the parcel warm?'),
                ],
                [
                    'type'    => 'text',
                    'label'   => $this->l('Estimated Transit Days'),
                    'name'    => 'transit_days',
                    'class'   => 'fixed-width-sm',
                    'hint'    => $this->l('Number of business days expected for delivery.'),
                ],
                [
                    'type'    => 'text',
                    'label'   => $this->l('Staff Name'),
                    'name'    => 'staff_name',
                    'class'   => 'fixed-width-lg',
                    'hint'    => $this->l('Name of the staff member who packed this order.'),
                ],
                [
                    'type'    => 'textarea',
                    'label'   => $this->l('Notes'),
                    'name'    => 'notes',
                    'rows'    => 5,
                    'cols'    => 60,
                    'hint'    => $this->l('Any additional notes about this dispatch.'),
                ],
                [
                    'type'    => 'file',
                    'label'   => $this->l('Dispatch Photo'),
                    'name'    => 'photo_filename',
                    'display_image' => true,
                    'hint'    => $this->l(
                        'Upload a photo of the packed parcel (JPG/PNG/WEBP, max 2 MB). '
                        . 'Existing photo is kept when no new file is selected.'
                    ),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        /** @var PhytoDispatchLog|null $object */
        $object = $this->loadObject(true);

        // Pre-fill the order ID coming from the URL parameter
        if (($object instanceof PhytoDispatchLog) && empty($object->id_order) && $defaultIdOrder > 0) {
            $object->id_order = $defaultIdOrder;
        }

        // Pre-fill dispatch_date with today when adding a new entry
        if (($object instanceof PhytoDispatchLog) && empty($object->id)) {
            if (empty($object->dispatch_date)) {
                $object->dispatch_date = date('Y-m-d');
            }
        }

        // Show existing photo thumbnail above the file field
        if (($object instanceof PhytoDispatchLog) && !empty($object->photo_filename)) {
            $photoUrl = $this->context->link->getBaseLink()
                . 'img/phyto_dispatch/'
                . rawurlencode($object->photo_filename);
            $this->tpl_form_vars['photo_thumbnail'] = $photoUrl;
            $this->tpl_form_vars['photo_filename']  = $object->photo_filename;
        }

        return parent::renderForm();
    }

    // -------------------------------------------------------------------------
    // Post-processing: save / photo upload
    // -------------------------------------------------------------------------

    /**
     * Handle form submission: validate, upload photo, delegate to parent.
     *
     * @return mixed
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddphyto_dispatch_logger')
            || Tools::isSubmit('submitEditphyto_dispatch_logger')
        ) {
            $this->handlePhotoUpload();
        }

        return parent::postProcess();
    }

    /**
     * Validate and save an uploaded dispatch photo.
     *
     * If validation fails, an error is recorded and the upload is aborted.
     * The filename is stored in $_POST['photo_filename'] so it is picked up
     * by the ObjectModel on save.
     *
     * @return void
     */
    protected function handlePhotoUpload(): void
    {
        // No file uploaded — keep existing value from the hidden field
        if (empty($_FILES['photo_filename']['name'])) {
            return;
        }

        $file = $_FILES['photo_filename'];

        // Validate file size
        if ($file['size'] > self::MAX_PHOTO_SIZE) {
            $this->errors[] = $this->l('The dispatch photo must not exceed 2 MB.');
            return;
        }

        // Validate that this is actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->errors[] = $this->l('The uploaded file is not a valid image.');
            return;
        }

        $mime = $imageInfo['mime'] ?? '';
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            $this->errors[] = $this->l(
                'Only JPG, PNG, WEBP or GIF images are accepted for dispatch photos.'
            );
            return;
        }

        // Build a unique, safe filename
        $originalName  = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension     = image_type_to_extension($imageInfo[2], false);
        $safeName      = preg_replace('/[^a-z0-9_\-]/i', '_', $originalName);
        $newFilename   = $safeName . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $destination   = PhytoDispatchLog::getPhotoDir() . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = $this->l(
                'Could not save the dispatch photo. Please check directory permissions.'
            );
            return;
        }

        // Set permissions: owner read/write, group/world read-only
        chmod($destination, 0644);

        // Inject the filename so the ObjectModel picks it up
        $_POST['photo_filename'] = $newFilename;
    }

    // -------------------------------------------------------------------------
    // List actions
    // -------------------------------------------------------------------------

    /**
     * Custom delete action — also removes the associated photo file.
     *
     * @return bool
     */
    public function processDelete(): bool
    {
        $idLog = (int) Tools::getValue($this->identifier);

        if ($idLog > 0) {
            $log = new PhytoDispatchLog($idLog);
            if (Validate::isLoadedObject($log) && !empty($log->photo_filename)) {
                $photoPath = PhytoDispatchLog::getPhotoDir() . $log->photo_filename;
                if (is_file($photoPath)) {
                    @unlink($photoPath);
                }
            }
        }

        return parent::processDelete();
    }

    /**
     * Custom bulk delete action — removes associated photo files.
     *
     * @return bool
     */
    protected function processBulkDelete(): bool
    {
        $ids = Tools::getValue($this->table . 'Box');

        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $idLog) {
                $log = new PhytoDispatchLog((int) $idLog);
                if (Validate::isLoadedObject($log) && !empty($log->photo_filename)) {
                    $photoPath = PhytoDispatchLog::getPhotoDir() . $log->photo_filename;
                    if (is_file($photoPath)) {
                        @unlink($photoPath);
                    }
                }
            }
        }

        return parent::processBulkDelete();
    }
}
