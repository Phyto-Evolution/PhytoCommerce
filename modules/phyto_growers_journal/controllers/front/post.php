<?php
/**
 * FrontController: Grower's Journal customer post submission.
 *
 * GET  — display submission form for a given product.
 * POST — validate, spam-check, save entry as unapproved, redirect with
 *        a success confirmation message.
 *
 * Requires the customer to be logged in ($this->auth = true).
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/PhytoJournalEntry.php';

class Phyto_Growers_JournalPostModuleFrontController extends ModuleFrontController
{
    /** @var bool Require customer login */
    public $auth = true;

    /** @var bool Display column layout */
    public $display_column_left  = false;
    public $display_column_right = false;

    /** @var string Auth redirect page */
    public $authRedirection = 'my-account';

    // -------------------------------------------------------------------------
    // Initialisation
    // -------------------------------------------------------------------------

    public function initContent()
    {
        parent::initContent();

        $idProduct = (int) Tools::getValue('id_product');

        // Verify that customer posts are allowed by configuration
        $allowCustomerPosts = (bool) Configuration::get('PHYTO_JOURNAL_ALLOW_CUSTOMER_POSTS');
        if (!$allowCustomerPosts) {
            Tools::redirect($this->context->link->getPageLink('index'));
            return;
        }

        $product = null;
        if ($idProduct) {
            $product = new Product($idProduct, false, $this->context->language->id);
            if (!$product->id) {
                $product = null;
            }
        }

        if (Tools::isSubmit('submitJournalPost')) {
            $this->processPost($idProduct, $product);
            return;
        }

        // GET — render form
        $this->context->smarty->assign(array(
            'phyto_product'     => $product,
            'phyto_id_product'  => $idProduct,
            'phyto_form_action' => $this->context->link->getModuleLink(
                $this->module->name,
                'post',
                array('id_product' => $idProduct)
            ),
            'phyto_token'       => Tools::getToken('phyto_journal_post'),
        ));

        $this->setTemplate('module:phyto_growers_journal/views/templates/front/post_form.tpl');
    }

    // -------------------------------------------------------------------------
    // POST processing
    // -------------------------------------------------------------------------

    /**
     * Validate and persist the submitted journal entry.
     *
     * @param int      $idProduct
     * @param Product|null $product
     */
    protected function processPost($idProduct, $product)
    {
        $errors = array();

        // CSRF token check
        if (!Tools::getValue('token') || Tools::getValue('token') !== Tools::getToken('phyto_journal_post')) {
            $errors[] = $this->module->l('Invalid form token. Please try again.', 'post');
        }

        $idCustomer = (int) $this->context->customer->id;

        if (!$idProduct || !$product) {
            $errors[] = $this->module->l('No valid product selected.', 'post');
        }

        // Require purchase
        if ($idProduct && !PhytoJournalEntry::customerHasPurchased($idCustomer, $idProduct)) {
            $errors[] = $this->module->l(
                'You can only submit a journal entry for a product you have purchased.',
                'post'
            );
        }

        // Spam / rate-limit check
        if ($idProduct && PhytoJournalEntry::hasRecentPost($idCustomer, $idProduct)) {
            $errors[] = $this->module->l(
                'You have already submitted an entry for this product within the last 7 days.',
                'post'
            );
        }

        // Field validation
        $title = Tools::getValue('title', '');
        $body  = Tools::getValue('body', '');

        if (empty(trim($title))) {
            $errors[] = $this->module->l('Please provide a title for your entry.', 'post');
        }

        if (mb_strlen($title) > 255) {
            $errors[] = $this->module->l('The title must not exceed 255 characters.', 'post');
        }

        if (empty(trim($body))) {
            $errors[] = $this->module->l('Please provide a description in the body field.', 'post');
        }

        $entryDate = Tools::getValue('entry_date', date('Y-m-d'));
        if (!Validate::isDate($entryDate)) {
            $entryDate = date('Y-m-d');
        }

        // Photo uploads (max 3)
        $photoFilenames = array('photo1' => '', 'photo2' => '', 'photo3' => '');
        if (empty($errors)) {
            foreach (array_keys($photoFilenames) as $field) {
                $filename = $this->handlePhotoUpload($field, $errors);
                if ($filename !== null) {
                    $photoFilenames[$field] = $filename;
                }
            }
        }

        if (!empty($errors)) {
            $this->context->smarty->assign(array(
                'phyto_errors'      => $errors,
                'phyto_product'     => $product,
                'phyto_id_product'  => $idProduct,
                'phyto_form_action' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'post',
                    array('id_product' => $idProduct)
                ),
                'phyto_token'       => Tools::getToken('phyto_journal_post'),
                'phyto_old_values'  => array(
                    'title'      => htmlspecialchars($title, ENT_QUOTES),
                    'body'       => htmlspecialchars($body, ENT_QUOTES),
                    'entry_date' => htmlspecialchars($entryDate, ENT_QUOTES),
                ),
            ));
            $this->setTemplate('module:phyto_growers_journal/views/templates/front/post_form.tpl');
            return;
        }

        // Purify HTML body (strip to safe subset)
        $safeBody = strip_tags(
            $body,
            '<p><br><strong><em><ul><ol><li><a><blockquote>'
        );

        // Save entry as unapproved
        $entry              = new PhytoJournalEntry();
        $entry->id_product  = $idProduct;
        $entry->id_customer = $idCustomer;
        $entry->entry_date  = $entryDate;
        $entry->title       = $title;
        $entry->body        = $safeBody;
        $entry->photo1      = $photoFilenames['photo1'];
        $entry->photo2      = $photoFilenames['photo2'];
        $entry->photo3      = $photoFilenames['photo3'];
        $entry->entry_type  = 'Customer';
        $entry->approved    = 0; // pending moderation

        if (!$entry->add()) {
            $this->context->smarty->assign(array(
                'phyto_errors'      => array($this->module->l('An error occurred while saving your entry. Please try again.', 'post')),
                'phyto_product'     => $product,
                'phyto_id_product'  => $idProduct,
                'phyto_form_action' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'post',
                    array('id_product' => $idProduct)
                ),
                'phyto_token'       => Tools::getToken('phyto_journal_post'),
            ));
            $this->setTemplate('module:phyto_growers_journal/views/templates/front/post_form.tpl');
            return;
        }

        // Success — redirect back to the product page with a flash message
        $successMsg = urlencode(
            $this->module->l(
                'Your journal entry has been submitted and is pending approval. Thank you!',
                'post'
            )
        );

        $redirectUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'post',
            array('id_product' => $idProduct, 'phyto_success' => 1)
        );

        if ($product && $product->id) {
            $redirectUrl = $this->context->link->getProductLink($product)
                . '?phyto_success=1&phyto_msg=' . $successMsg;
        }

        Tools::redirect($redirectUrl);
    }

    /**
     * Handle upload for a single photo field.
     *
     * @param string $field   Field name: photo1, photo2, photo3
     * @param array  &$errors Error accumulator
     * @return string|null  Saved filename or null if no file submitted
     */
    protected function handlePhotoUpload($field, array &$errors)
    {
        if (!isset($_FILES[$field]) || empty($_FILES[$field]['name'])) {
            return null;
        }

        $file = $_FILES[$field];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = sprintf(
                $this->module->l('Upload error for %s (code %d).', 'post'),
                $field,
                $file['error']
            );
            return null;
        }

        // Validate via getimagesize
        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            $errors[] = $this->module->l('One or more uploaded files are not valid images.', 'post');
            return null;
        }

        $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($imgInfo['mime'], $allowedMime)) {
            $errors[] = $this->module->l('Unsupported image type. Please use JPG, PNG, GIF, or WebP.', 'post');
            return null;
        }

        // Max 2 MB
        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = $this->module->l('Each photo must not exceed 2 MB.', 'post');
            return null;
        }

        $uploadDir = PhytoJournalEntry::getUploadDir();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $errors[] = $this->module->l('Could not create the upload directory.', 'post');
                return null;
            }
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'cust_' . (int) $this->context->customer->id
            . '_' . time()
            . '_' . $field
            . '_' . Tools::passwdGen(6)
            . '.' . Tools::strtolower($ext);
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = $this->module->l('Could not save an uploaded photo. Please try again.', 'post');
            return null;
        }

        chmod($dest, 0644);

        return $filename;
    }

    // -------------------------------------------------------------------------
    // Breadcrumb
    // -------------------------------------------------------------------------

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->module->l("Grower's Journal", 'post'),
            'url'   => $this->context->link->getModuleLink($this->module->name, 'post'),
        );

        return $breadcrumb;
    }
}
