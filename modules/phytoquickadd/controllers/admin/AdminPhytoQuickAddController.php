<?php
if (!defined('_PS_VERSION_')) exit;

require_once _PS_MODULE_DIR_ . 'phytoquickadd/classes/PhytoTaxonomy.php';

class AdminPhytoQuickAddController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->display   = 'view';
    }

    public function init() {
        ob_start();
        parent::init();
        if (Tools::isSubmit('phyto_ajax')) {
            header('Content-Type: application/json');
            $action = Tools::getValue('phyto_action');
            ob_clean();
            switch ($action) {
                case 'generate_description': $this->ajaxGenerateDescription(); break;
                case 'search_categories':    $this->ajaxSearchCategories(); break;
                case 'fetch_packs':          $this->ajaxFetchPacks(); break;
                case 'import_pack':          $this->ajaxImportPack(); break;
                case 'sync_pack':            $this->ajaxSyncPack(); break;
                case 'get_subcategories':    $this->ajaxGetSubcategories(); break;
                case 'get_categories':       $this->ajaxGetCategories(); break;
                case 'add_category':         $this->ajaxAddCategory(); break;
                default: echo json_encode(['error' => 'Unknown action']); exit;
            }
        }
    }

    public function postProcess() {
        if (Tools::isSubmit('saveSettings')) {
            Configuration::updateValue('PHYTO_OPENAI_KEY', Tools::getValue('openai_key'));
            $this->confirmations[] = 'Settings saved successfully.';
        }
        if (Tools::isSubmit('submitAddCategory')) {
            $this->processAddCategory();
        }
        if (Tools::isSubmit('submitQuickAdd')) {
            $this->processAddProduct();
        }
    }

    public function renderView() {
        $id_lang = $this->context->language->id;
        $flat_categories = $this->getFlatCategories($id_lang);
        $imported_packs  = PhytoTaxonomy::getImportedPacks();

        $this->context->smarty->assign([
            'flat_categories' => $flat_categories,
            'imported_packs'  => $imported_packs,
            'openai_key'      => Configuration::get('PHYTO_OPENAI_KEY') ?: '',
            'ajax_url'        => $this->context->link->getAdminLink('AdminPhytoQuickAdd'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phytoquickadd/views/templates/admin/quickadd.tpl'
        );
    }

    private function getFlatCategories($id_lang) {
        $flat = [];
        $flat[] = ['id' => 2, 'name' => 'Home (Top Level)'];
        $result = Db::getInstance()->executeS(
            'SELECT c.id_category, c.id_parent, cl.name
              FROM ' . _DB_PREFIX_ . 'category c
              JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = c.id_category
              WHERE cl.id_lang = ' . (int)$id_lang . '
              AND c.active = 1
              AND c.id_category != 1
              AND c.id_category != 2
              ORDER BY c.nleft ASC'
        );
        if (!$result) return $flat;
        $map = [];
        foreach ($result as $row) {
            $map[$row['id_category']] = $row;
        }
        $this->buildFlatFromMap($map, 2, $flat, 0);
        return $flat;
    }

    private function buildFlatFromMap($map, $id_parent, &$flat, $depth) {
        foreach ($map as $cat) {
            if ((int)$cat['id_parent'] !== (int)$id_parent) continue;
            $prefix = $depth > 0 ? str_repeat('  ', $depth) . '└ ' : '';
            $flat[] = ['id' => $cat['id_category'], 'name' => $prefix . $cat['name']];
            $this->buildFlatFromMap($map, $cat['id_category'], $flat, $depth + 1);
        }
    }

    private function processAddCategory() {
        $name      = trim(Tools::getValue('category_name'));
        $id_parent = (int)Tools::getValue('parent_category');
        $id_lang   = $this->context->language->id;

        if (empty($name))    { $this->errors[] = 'Category name is required.'; return; }
        if ($id_parent <= 0) { $this->errors[] = 'Please select a parent category.'; return; }

        $result = PhytoTaxonomy::ensureCategory($name, $name, $id_parent, $id_lang);
        if ($result) {
            $this->confirmations[] = 'Category "' . $name . '" added successfully!';
        } else {
            $this->errors[] = 'Failed to add category. It may already exist.';
        }
    }

    private function processAddProduct() {
        $name        = trim(Tools::getValue('product_name'));
        $price       = (float)Tools::getValue('product_price');
        $quantity    = (int)Tools::getValue('product_quantity');
        $id_category = (int)Tools::getValue('product_category');

        if (empty($name))      { $this->errors[] = 'Product name is required.'; }
        if ($price <= 0)       { $this->errors[] = 'Price must be greater than 0.'; }
        if ($id_category <= 0) { $this->errors[] = 'Please select a category.'; }
        if (!empty($this->errors)) return;

        $id_lang = $this->context->language->id;
        $product = new Product();
        $product->name                = [$id_lang => $name];
        $product->description         = [$id_lang => Tools::getValue('product_description')];
        $product->description_short   = [$id_lang => Tools::getValue('product_short_description')];
        $product->price               = $price;
        $product->id_category_default = $id_category;
        $product->active              = 1;
        $product->visibility          = 'both';
        $product->link_rewrite        = [$id_lang => Tools::link_rewrite($name)];

        if ($product->add()) {
            StockAvailable::setQuantity($product->id, 0, $quantity);
            $product->addToCategories([$id_category]);
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
                $this->handleProductImage($product->id, $_FILES['product_image']['tmp_name']);
            }
            $this->confirmations[] = 'Product "' . $name . '" added! <a href="'
                . $this->context->link->getAdminLink('AdminProducts')
                . '&id_product=' . $product->id . '&updateproduct" target="_blank">Edit full details →</a>';
        } else {
            $this->errors[] = 'Failed to save product.';
        }
    }

    protected function handleProductImage($id_product, $tmp_file) {
        $image = new Image();
        $image->id_product = $id_product;
        $image->position   = Image::getHighestPosition($id_product) + 1;
        $image->cover      = true;
        if ($image->add()) {
            $path = $image->getPathForCreation();
            ImageManager::resize($tmp_file, $path . '.jpg');
            foreach (ImageType::getImagesTypes('products') as $type) {
                ImageManager::resize($tmp_file, $path . '-' . $type['name'] . '.jpg', $type['width'], $type['height']);
            }
        }
    }

    private function ajaxGenerateDescription() {
        $plant_name = Tools::getValue('plant_name');
        $openai_key = Configuration::get('PHYTO_OPENAI_KEY');

        if (empty($openai_key)) { echo json_encode(['error' => 'OpenAI API key not set. Go to Settings tab.']); exit; }
        if (empty($plant_name)) { echo json_encode(['error' => 'Plant name is required.']); exit; }

        $prompt = "Write a compelling ecommerce product description for a rare/exotic plant called '$plant_name'. "
                . "Include what makes it special, care difficulty, size, ideal conditions, why someone should buy it. "
                . "Under 200 words. Also provide a 2-sentence short description. "
                . "Return ONLY valid JSON: {\"description\": \"...\", \"short_description\": \"...\"}";

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openai_key,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => 'gpt-3.5-turbo',
                'messages'   => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 500,
            ]),
        ]);

        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) { echo json_encode(['error' => 'Connection error: ' . $err]); exit; }

        $data = json_decode($result, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $parsed  = json_decode($content, true);
            echo json_encode($parsed ?: ['description' => $content, 'short_description' => '']);
        } else {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            echo json_encode(['error' => 'OpenAI error: ' . $msg]);
        }
        exit;
    }

    private function ajaxSearchCategories() {
        $term    = Tools::getValue('term');
        $id_lang = $this->context->language->id;
        echo json_encode(PhytoTaxonomy::getSuggestions($term, $id_lang) ?: []);
        exit;
    }

    private function ajaxFetchPacks() {
        $index = PhytoTaxonomy::fetchIndex();
        if (!$index) {
            echo json_encode(['error' => 'Could not fetch taxonomy index from GitHub.']);
            exit;
        }
        $imported = PhytoTaxonomy::getImportedPacks();
        foreach ($index['packs'] as &$pack) {
            $pack['imported'] = isset($imported[$pack['file']]);
            if ($pack['imported']) {
                $pack['imported_at'] = $imported[$pack['file']]['imported_at'];
                $pack['count']       = $imported[$pack['file']]['count'];
            }
        }
        echo json_encode($index);
        exit;
    }

    private function ajaxImportPack() {
        $pack_file = Tools::getValue('pack_file');
        if (empty($pack_file)) { echo json_encode(['error' => 'No pack file specified.']); exit; }
        echo json_encode(PhytoTaxonomy::importPack($pack_file, $this->context->language->id));
        exit;
    }

    private function ajaxSyncPack() {
        $pack_file = Tools::getValue('pack_file');
        PhytoTaxonomy::clearCache($pack_file);
        echo json_encode(PhytoTaxonomy::importPack($pack_file, $this->context->language->id));
        exit;
    }

    private function ajaxAddCategory() {
        $name      = trim(Tools::getValue('category_name'));
        $id_parent = (int)Tools::getValue('parent_category');
        $id_lang   = $this->context->language->id;

        if (empty($name))    { echo json_encode(['error' => 'Category name is required.']); exit; }
        if ($id_parent <= 0) { echo json_encode(['error' => 'Please select a parent category.']); exit; }

        $result = PhytoTaxonomy::ensureCategory($name, $name, $id_parent, $id_lang);
        if ($result) {
            echo json_encode(['success' => true, 'id' => $result, 'name' => $name]);
        } else {
            echo json_encode(['error' => 'Failed to add category. It may already exist.']);
        }
        exit;
    }

    private function ajaxGetCategories() {
        $id_lang = $this->context->language->id;
        $cats = $this->getFlatCategories($id_lang);
        echo json_encode($cats ?: []);
        exit;
    }

    private function ajaxGetSubcategories() {
        $id_parent = (int)Tools::getValue('id_parent');
        $id_lang   = $this->context->language->id;
        echo json_encode(Category::getChildren($id_parent, $id_lang, true) ?: []);
        exit;
    }
}
