<?php
/**
 * AdminPhytoPhytosanitaryController
 *
 * Back-office CRUD controller for phytosanitary regulatory documents.
 * Provides a HelperList overview with expiry warnings and a HelperForm with
 * file upload support.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_phytosanitary/classes/PhytoPhytosanitaryDoc.php';

class AdminPhytoPhytosanitaryController extends ModuleAdminController
{
    /** @var int Maximum allowed upload size in bytes (5 MB) */
    const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'phyto_phytosanitary_doc';
        $this->className   = 'PhytoPhytosanitaryDoc';
        $this->identifier  = 'id_doc';
        $this->lang        = false;
        $this->allow_export = true;

        parent::__construct();

        $this->meta_title = $this->l('Phytosanitary Documents');

        $this->_defaultOrderBy  = 'expiry_date';
        $this->_defaultOrderWay = 'ASC';

        // Custom JOIN to retrieve the product name
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (pl.`id_product` = a.`id_product`
                    AND pl.`id_lang` = ' . (int) $this->context->language->id . '
                    AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')
        ';

        $this->_select = 'a.*, IFNULL(pl.`name`, \'' . $this->l('Store-level') . '\') AS product_name';

        $this->initList();
        $this->initForm();
    }

    // -------------------------------------------------------------------------
    // HelperList definition
    // -------------------------------------------------------------------------

    /**
     * Define list columns and actions.
     */
    protected function initList(): void
    {
        $this->fields_list = [
            'id_doc' => [
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'filter_key' => 'a!id_doc',
            ],
            'doc_type' => [
                'title'      => $this->l('Type'),
                'type'       => 'select',
                'list'       => PhytoPhytosanitaryDoc::getDocTypeLabels(),
                'filter_key' => 'a!doc_type',
                'callback'   => 'renderDocTypeLabel',
            ],
            'product_name' => [
                'title'      => $this->l('Product'),
                'filter_key' => 'pl!name',
            ],
            'issuing_authority' => [
                'title'      => $this->l('Issuing Authority'),
                'filter_key' => 'a!issuing_authority',
            ],
            'reference_number' => [
                'title'      => $this->l('Reference #'),
                'filter_key' => 'a!reference_number',
            ],
            'issue_date' => [
                'title'      => $this->l('Issue Date'),
                'type'       => 'date',
                'filter_key' => 'a!issue_date',
                'align'      => 'center',
            ],
            'expiry_date' => [
                'title'      => $this->l('Expiry Date'),
                'type'       => 'date',
                'filter_key' => 'a!expiry_date',
                'align'      => 'center',
                'callback'   => 'renderExpiryDate',
            ],
            'is_public' => [
                'title'      => $this->l('Public'),
                'type'       => 'bool',
                'align'      => 'center',
                'class'      => 'fixed-width-sm',
                'filter_key' => 'a!is_public',
                'active'     => 'is_public',
            ],
            'filename' => [
                'title'    => $this->l('File'),
                'callback' => 'renderDownloadLink',
                'search'   => false,
                'orderby'  => false,
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->l('Delete selected documents?'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // HelperForm definition
    // -------------------------------------------------------------------------

    /**
     * Define form fields for add / edit.
     */
    protected function initForm(): void
    {
        // Build product options list
        $products = Product::getProducts(
            (int) $this->context->language->id,
            0,
            0,
            'name',
            'ASC',
            false,
            true
        );

        $productOptions = [
            ['id_product' => 0, 'name' => $this->l('— Store-level (all products) —')],
        ];

        foreach ($products as $p) {
            $productOptions[] = [
                'id_product' => (int) $p['id_product'],
                'name'       => $p['name'],
            ];
        }

        // Doc-type options
        $docTypeOptions = [];
        foreach (PhytoPhytosanitaryDoc::getDocTypeLabels() as $key => $label) {
            $docTypeOptions[] = ['id' => $key, 'name' => $label];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Phytosanitary Document'),
                'icon'  => 'icon-file-text',
            ],
            'input' => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Document Type'),
                    'name'     => 'doc_type',
                    'required' => true,
                    'options'  => [
                        'query' => $docTypeOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Product'),
                    'name'    => 'id_product',
                    'options' => [
                        'query' => $productOptions,
                        'id'    => 'id_product',
                        'name'  => 'name',
                    ],
                    'desc' => $this->l('Select "Store-level" to attach this document to the entire store.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Issuing Authority'),
                    'name'  => 'issuing_authority',
                    'size'  => 200,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Reference Number'),
                    'name'  => 'reference_number',
                    'size'  => 100,
                    'desc'  => $this->l('Certificate or permit number as printed on the document.'),
                ],
                [
                    'type'  => 'date',
                    'label' => $this->l('Issue Date'),
                    'name'  => 'issue_date',
                ],
                [
                    'type'  => 'date',
                    'label' => $this->l('Expiry Date'),
                    'name'  => 'expiry_date',
                    'desc'  => $this->l('Leave blank if the document does not expire.'),
                ],
                [
                    'type'     => 'file',
                    'label'    => $this->l('Document File (PDF)'),
                    'name'     => 'filename',
                    'desc'     => $this->l('PDF only. Maximum size: 5 MB.'),
                    'display_image' => false,
                ],
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Visible to Customers'),
                    'name'   => 'is_public',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'is_public_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'is_public_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                    'desc' => $this->l('Public documents show a download link on the product page.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // List callbacks
    // -------------------------------------------------------------------------

    /**
     * Render the doc_type column as a human-readable label.
     *
     * @param string               $value
     * @param array<string, mixed> $row
     *
     * @return string
     */
    public function renderDocTypeLabel(string $value, array $row): string
    {
        return htmlspecialchars(
            PhytoPhytosanitaryDoc::getDocTypeLabel($value),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Render the expiry date column with an orange warning badge when
     * the document expires within 30 days (or is already expired in red).
     *
     * @param string|null          $value
     * @param array<string, mixed> $row
     *
     * @return string
     */
    public function renderExpiryDate(?string $value, array $row): string
    {
        if (empty($value) || $value === '0000-00-00') {
            return '<span class="label label-default">' . $this->l('No expiry') . '</span>';
        }

        $formatted = htmlspecialchars(
            Tools::displayDate($value, false),
            ENT_QUOTES,
            'UTF-8'
        );

        $expiry = strtotime($value);
        $now    = time();
        $soon   = strtotime('+30 days');

        if ($expiry < $now) {
            return '<span class="label label-danger phyto-expiry-badge phyto-expired">'
                . $formatted
                . ' <i class="icon-warning-sign"></i>'
                . '</span>';
        }

        if ($expiry <= $soon) {
            return '<span class="label label-warning phyto-expiry-badge phyto-expiring-soon">'
                . $formatted
                . ' <i class="icon-time"></i>'
                . '</span>';
        }

        return '<span class="label label-success phyto-expiry-badge">' . $formatted . '</span>';
    }

    /**
     * Render a download link for the stored file (if any).
     *
     * @param string|null          $value
     * @param array<string, mixed> $row
     *
     * @return string
     */
    public function renderDownloadLink(?string $value, array $row): string
    {
        if (empty($value)) {
            return '—';
        }

        $url = $this->context->link->getAdminLink('AdminPhytoPhytosanitary')
            . '&action=downloadDoc&id_doc=' . (int) $row['id_doc'];

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" '
            . 'target="_blank" class="btn btn-xs btn-default">'
            . '<i class="icon-download"></i> '
            . $this->l('Download')
            . '</a>';
    }

    // -------------------------------------------------------------------------
    // renderList override – add expiry CSS class to rows
    // -------------------------------------------------------------------------

    /**
     * After the list data is loaded, tag rows that are expired or expiring soon
     * so the template can style the entire row if desired.
     *
     * @return string
     */
    public function renderList(): string
    {
        // Inject custom admin CSS
        $this->addCSS(
            _PS_MODULE_DIR_ . 'phyto_phytosanitary/views/css/admin.css',
            'all',
            null,
            false
        );

        // Post-process loaded list rows
        if (!empty($this->_list)) {
            $soon = strtotime('+30 days');
            $now  = time();

            foreach ($this->_list as &$row) {
                if (!empty($row['expiry_date']) && $row['expiry_date'] !== '0000-00-00') {
                    $expiry = strtotime($row['expiry_date']);

                    if ($expiry < $now) {
                        $row['_phyto_row_class'] = 'phyto-row-expired';
                    } elseif ($expiry <= $soon) {
                        $row['_phyto_row_class'] = 'phyto-row-expiring-soon';
                    }
                }
            }
            unset($row);
        }

        return parent::renderList();
    }

    // -------------------------------------------------------------------------
    // Post-process: file upload + custom download action
    // -------------------------------------------------------------------------

    /**
     * Handle form submissions and the custom downloadDoc action.
     */
    public function postProcess()
    {
        // Custom download action
        if (Tools::getValue('action') === 'downloadDoc') {
            $this->processDownloadDoc();

            return;
        }

        // Handle file upload before the ObjectModel is saved
        if (Tools::isSubmit('submitAddphyto_phytosanitary_doc')
            || Tools::isSubmit('submitAdd' . $this->table)
        ) {
            $this->processFileUpload();
        }

        parent::postProcess();
    }

    /**
     * Validate and move the uploaded PDF to the upload directory.
     * Stores the generated filename back into $_POST so the ObjectModel
     * picks it up when it reads the POST data.
     */
    protected function processFileUpload(): void
    {
        if (!isset($_FILES['filename']) || empty($_FILES['filename']['name'])) {
            // No new file – keep the existing one if editing
            $idDoc = (int) Tools::getValue('id_doc');

            if ($idDoc > 0) {
                $existing = new PhytoPhytosanitaryDoc($idDoc);

                if (Validate::isLoadedObject($existing)) {
                    $_POST['filename'] = $existing->filename;
                }
            }

            return;
        }

        $file = $_FILES['filename'];

        // Validate upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->l('File upload failed. Error code: ') . (int) $file['error'];

            return;
        }

        // Validate size
        if ($file['size'] > self::MAX_UPLOAD_SIZE) {
            $this->errors[] = $this->l('File is too large. Maximum size is 5 MB.');

            return;
        }

        // Validate extension
        $originalName = $file['name'];
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            $this->errors[] = $this->l('Only PDF files are accepted.');

            return;
        }

        // Validate MIME type as a secondary check
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            $this->errors[] = $this->l('Invalid file type. Only PDF documents are allowed.');

            return;
        }

        // Generate a unique filename to prevent collisions / path traversal
        $uniqueName = sprintf(
            '%s_%s.pdf',
            uniqid('phyto_', true),
            preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME))
        );

        $uploadDir  = PhytoPhytosanitaryDoc::getUploadDir();
        $targetPath = $uploadDir . $uniqueName;

        // Delete old file if we are editing an existing record
        $idDoc = (int) Tools::getValue('id_doc');

        if ($idDoc > 0) {
            $existing = new PhytoPhytosanitaryDoc($idDoc);

            if (Validate::isLoadedObject($existing) && !empty($existing->filename)) {
                $oldPath = $uploadDir . $existing->filename;

                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->errors[] = $this->l('Could not move uploaded file. Check upload directory permissions.');

            return;
        }

        @chmod($targetPath, 0644);

        // Pass filename into POST so ObjectModel can persist it
        $_POST['filename'] = $uniqueName;
    }

    /**
     * Stream a stored document to the browser (admin-only).
     */
    protected function processDownloadDoc(): void
    {
        $idDoc = (int) Tools::getValue('id_doc');

        if ($idDoc <= 0) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminPhytoPhytosanitary'));
        }

        $doc = new PhytoPhytosanitaryDoc($idDoc);

        if (!Validate::isLoadedObject($doc) || empty($doc->filename)) {
            $this->errors[] = $this->l('Document not found.');

            return;
        }

        $filePath = PhytoPhytosanitaryDoc::getUploadDir() . $doc->filename;

        if (!file_exists($filePath)) {
            $this->errors[] = $this->l('Physical file missing from upload directory.');

            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $doc->filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, no-cache');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    // -------------------------------------------------------------------------
    // Delete override – remove physical file too
    // -------------------------------------------------------------------------

    /**
     * Override delete to also remove the physical PDF file.
     */
    public function processDelete(): bool
    {
        $idDoc = (int) Tools::getValue($this->identifier);

        if ($idDoc > 0) {
            $doc = new PhytoPhytosanitaryDoc($idDoc);

            if (Validate::isLoadedObject($doc) && !empty($doc->filename)) {
                $filePath = PhytoPhytosanitaryDoc::getUploadDir() . $doc->filename;

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        return parent::processDelete();
    }

    /**
     * Override bulk delete to remove physical files too.
     */
    protected function processBulkDelete(): bool
    {
        $ids = Tools::getValue($this->table . 'Box');

        if (!is_array($ids) || empty($ids)) {
            return parent::processBulkDelete();
        }

        $uploadDir = PhytoPhytosanitaryDoc::getUploadDir();

        foreach ($ids as $idDoc) {
            $doc = new PhytoPhytosanitaryDoc((int) $idDoc);

            if (Validate::isLoadedObject($doc) && !empty($doc->filename)) {
                $filePath = $uploadDir . $doc->filename;

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        return parent::processBulkDelete();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Add a confirmation message to the page.
     *
     * @param string $message
     */
    protected function confirmations(string $message): void
    {
        $this->confirmations[] = $message;
    }
}
