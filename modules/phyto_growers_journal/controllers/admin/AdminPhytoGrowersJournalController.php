<?php
/**
 * AdminPhytoGrowersJournalController
 *
 * Back-office controller for managing Grower's Journal entries.
 * Provides a HelperList of all entries (filterable by product) and a
 * HelperForm for create / edit operations including photo uploads.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/PhytoJournalEntry.php';

class AdminPhytoGrowersJournalController extends ModuleAdminController
{
    /** @var string Upload directory for journal photos */
    protected $uploadDir;

    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'phyto_journal_entry';
        $this->className   = 'PhytoJournalEntry';
        $this->identifier  = 'id_entry';
        $this->lang        = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->allow_export = true;

        parent::__construct();

        $this->uploadDir = PhytoJournalEntry::getUploadDir();
        $this->meta_title = $this->l("Grower's Journal Entries");

        // ---------------------------------------------------------------
        // List columns
        // ---------------------------------------------------------------
        $this->fields_list = array(
            'id_entry'   => array(
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
            ),
            'id_product' => array(
                'title'   => $this->l('Product'),
                'filter_key' => 'a!id_product',
            ),
            'entry_date' => array(
                'title' => $this->l('Date'),
                'type'  => 'date',
            ),
            'title'      => array(
                'title'   => $this->l('Title'),
                'havingFilter' => true,
            ),
            'entry_type' => array(
                'title'  => $this->l('Type'),
                'type'   => 'select',
                'list'   => array(
                    'Store'     => $this->l('Store'),
                    'Customer'  => $this->l('Customer'),
                    'Milestone' => $this->l('Milestone'),
                ),
                'filter_key'   => 'a!entry_type',
                'filter_type'  => 'string',
            ),
            'approved' => array(
                'title'   => $this->l('Approved'),
                'active'  => 'approved',
                'type'    => 'bool',
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
            ),
            'date_add' => array(
                'title' => $this->l('Created'),
                'type'  => 'datetime',
            ),
        );

        // Pre-filter by product if requested via URL
        $idProductFilter = (int) Tools::getValue('id_product_filter');
        if ($idProductFilter) {
            $this->_filter .= ' AND a.id_product = ' . $idProductFilter;
        }
    }

    // -------------------------------------------------------------------------
    // HelperForm field definitions
    // -------------------------------------------------------------------------

    /**
     * Build input fields for the create/edit form.
     *
     * @return array
     */
    protected function getFormFields()
    {
        // Build product dropdown options
        $products     = Product::getProducts(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            0,
            0,
            'name',
            'ASC',
            false,
            true
        );
        $productOptions = array();
        foreach ($products as $p) {
            $productOptions[] = array(
                'id_product' => $p['id_product'],
                'name'       => $p['name'],
            );
        }

        return array(
            array(
                'type'     => 'select',
                'label'    => $this->l('Product'),
                'name'     => 'id_product',
                'required' => true,
                'options'  => array(
                    'query' => $productOptions,
                    'id'    => 'id_product',
                    'name'  => 'name',
                ),
            ),
            array(
                'type'    => 'date',
                'label'   => $this->l('Entry Date'),
                'name'    => 'entry_date',
                'desc'    => $this->l('Date of the journal entry.'),
            ),
            array(
                'type'     => 'text',
                'label'    => $this->l('Title'),
                'name'     => 'title',
                'required' => true,
                'col'      => 6,
            ),
            array(
                'type'  => 'textarea',
                'label' => $this->l('Body'),
                'name'  => 'body',
                'rows'  => 8,
                'cols'  => 60,
                'desc'  => $this->l('HTML is allowed and will be purified on save.'),
            ),
            array(
                'type'  => 'file',
                'label' => $this->l('Photo 1'),
                'name'  => 'photo1',
                'desc'  => $this->l('Max 2 MB. JPG / PNG / GIF / WebP.'),
            ),
            array(
                'type'  => 'file',
                'label' => $this->l('Photo 2'),
                'name'  => 'photo2',
                'desc'  => $this->l('Max 2 MB. JPG / PNG / GIF / WebP.'),
            ),
            array(
                'type'  => 'file',
                'label' => $this->l('Photo 3'),
                'name'  => 'photo3',
                'desc'  => $this->l('Max 2 MB. JPG / PNG / GIF / WebP.'),
            ),
            array(
                'type'    => 'select',
                'label'   => $this->l('Entry Type'),
                'name'    => 'entry_type',
                'options' => array(
                    'query' => array(
                        array('id' => 'Store',     'name' => $this->l('Store')),
                        array('id' => 'Customer',  'name' => $this->l('Customer')),
                        array('id' => 'Milestone', 'name' => $this->l('Milestone')),
                    ),
                    'id'   => 'id',
                    'name' => 'name',
                ),
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Approved'),
                'name'    => 'approved',
                'is_bool' => true,
                'values'  => array(
                    array('id' => 'approved_on',  'value' => 1, 'label' => $this->l('Yes')),
                    array('id' => 'approved_off', 'value' => 0, 'label' => $this->l('No')),
                ),
            ),
        );
    }

    // -------------------------------------------------------------------------
    // renderForm — HelperForm
    // -------------------------------------------------------------------------

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Journal Entry'),
                'icon'  => 'icon-leaf',
            ),
            'input'  => $this->getFormFields(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Provide default date for new entries
        if (!$this->object->id) {
            $this->object->entry_date = date('Y-m-d');
            $this->object->approved   = 1;
            $this->object->entry_type = 'Store';
        }

        // Show existing photos as thumbnails
        foreach (array('photo1', 'photo2', 'photo3') as $field) {
            $filename = $this->object->{$field};
            if ($filename && file_exists($this->uploadDir . $filename)) {
                $this->context->smarty->assign(
                    'phyto_thumb_' . $field,
                    $this->context->link->getBaseLink() . 'img/phyto_journal/' . $filename
                );
            }
        }

        return parent::renderForm();
    }

    // -------------------------------------------------------------------------
    // postProcess — handle form submission including photo uploads
    // -------------------------------------------------------------------------

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddphyto_growers_journal') || Tools::isSubmit('submitAddphyto_growers_journalAndStay')) {
            // Handle photo uploads before calling parent postProcess
            foreach (array('photo1', 'photo2', 'photo3') as $field) {
                $uploadedFilename = $this->processPhotoUpload($field);
                if ($uploadedFilename === false) {
                    // Error already added via $this->errors
                    return false;
                }
                if ($uploadedFilename !== null) {
                    // New file uploaded — delete old one if editing
                    $idEntry = (int) Tools::getValue('id_entry');
                    if ($idEntry) {
                        $entry = new PhytoJournalEntry($idEntry);
                        if ($entry->id && $entry->{$field}) {
                            $oldPath = $this->uploadDir . $entry->{$field};
                            if (file_exists($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                    }
                    $_POST[$field] = $uploadedFilename;
                } else {
                    // No new upload — keep existing value
                    $idEntry = (int) Tools::getValue('id_entry');
                    if ($idEntry) {
                        $entry = new PhytoJournalEntry($idEntry);
                        $_POST[$field] = $entry->id ? (string) $entry->{$field} : '';
                    } else {
                        $_POST[$field] = '';
                    }
                }
            }

            // Purify body HTML
            $body         = Tools::getValue('body', '');
            $_POST['body'] = strip_tags(
                $body,
                '<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote><img>'
            );
        }

        if (Tools::isSubmit('deletephyto_growers_journal') || Tools::isSubmit('bulkdeletephyto_growers_journal')) {
            $this->deleteAssociatedPhotos();
        }

        parent::postProcess();
    }

    /**
     * Process a single photo upload field.
     *
     * @param string $field  Field name (photo1, photo2, photo3)
     * @return string|null|false  Filename on success, null if no upload, false on error
     */
    protected function processPhotoUpload($field)
    {
        if (!isset($_FILES[$field]) || empty($_FILES[$field]['name'])) {
            return null;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->errors[] = sprintf(
                    $this->l('Upload error for %s (code %d).'),
                    $field,
                    $file['error']
                );
                return false;
            }
            return null;
        }

        // Validate via getimagesize
        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            $this->errors[] = sprintf($this->l('File %s is not a valid image.'), $field);
            return false;
        }

        $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($imgInfo['mime'], $allowedMime)) {
            $this->errors[] = sprintf(
                $this->l('File %s has an unsupported MIME type: %s'),
                $field,
                $imgInfo['mime']
            );
            return false;
        }

        // Max 2 MB
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->errors[] = sprintf($this->l('File %s exceeds the 2 MB size limit.'), $field);
            return false;
        }

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'journal_' . time() . '_' . $field . '_' . Tools::passwdGen(6) . '.' . Tools::strtolower($ext);
        $dest     = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->errors[] = sprintf($this->l('Could not save uploaded file for %s.'), $field);
            return false;
        }

        chmod($dest, 0644);

        return $filename;
    }

    /**
     * Remove photos associated with an entry being deleted.
     */
    protected function deleteAssociatedPhotos()
    {
        $idEntry = (int) Tools::getValue('id_entry');
        if (!$idEntry) {
            return;
        }
        $entry = new PhytoJournalEntry($idEntry);
        if (!$entry->id) {
            return;
        }
        foreach (array('photo1', 'photo2', 'photo3') as $field) {
            if ($entry->{$field}) {
                $path = $this->uploadDir . $entry->{$field};
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Toggle approved status via AJAX (active column in list)
    // -------------------------------------------------------------------------

    public function ajaxProcessStatusapproved()
    {
        if (!$this->access('edit')) {
            die(json_encode(array('success' => false, 'error' => $this->l('Access denied.'))));
        }

        $idEntry = (int) Tools::getValue('id_entry');
        $entry   = new PhytoJournalEntry($idEntry);
        if (!$entry->id) {
            die(json_encode(array('success' => false, 'error' => $this->l('Entry not found.'))));
        }

        $entry->approved = $entry->approved ? 0 : 1;
        $result          = $entry->save();

        die(json_encode(array('success' => $result)));
    }
}
