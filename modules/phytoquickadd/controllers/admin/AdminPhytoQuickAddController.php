<?php
if (!defined('_PS_VERSION_')) exit;

class AdminPhytoQuickAddController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->display = 'view';
    }

    public function init() {
        parent::init();
        if (Tools::isSubmit('phyto_ajax')) {
            $action = Tools::getValue('phyto_action');
            if ($action === 'generate_description') {
                $this->ajaxGenerateDescription();
            }
        }
    }

    public function postProcess() {
        if (Tools::isSubmit('saveSettings')) {
            Configuration::updateValue('PHYTO_OPENAI_KEY', Tools::getValue('openai_key'));
            $this->confirmations[] = 'Settings saved.';
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
        $categories = Category::getCategories($id_lang, true, false);
        $category_tree = $this->buildCategoryTree($categories);
        $flat_categories = [];
        $this->flattenCategories($category_tree, $flat_categories);

        $this->context->smarty->assign([
            'category_tree'   => $category_tree,
            'flat_categories' => $flat_categories,
            'openai_key'      => Configuration::get('PHYTO_OPENAI_KEY') ?: '',
            'ajax_url'        => $this->context->link->getAdminLink('AdminPhytoQuickAdd'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phytoquickadd/views/templates/admin/quickadd.tpl'
        );
    }

    private function buildCategoryTree($categories, $id_parent = 1, $depth = 0) {
        $tree = [];
        if (!isset($categories[$id_parent])) return $tree;
        foreach ($categories[$id_parent] as $cat) {
            if (!is_array($cat) || !isset($cat['id_category'])) continue;
            $item = [
                'id'       => $cat['id_category'],
                'name'     => str_repeat('— ', $depth) . $cat['name'],
                'depth'    => $depth,
                'children' => $this->buildCategoryTree($categories, $cat['id_category'], $depth + 1),
            ];
            $tree[] = $item;
        }
        return $tree;
    }

    private function flattenCategories($tree, &$flat) {
        foreach ($tree as $item) {
            $flat[] = ['id' => $item['id'], 'name' => $item['name']];
            if (!empty($item['children'])) {
                $this->flattenCategories($item['children'], $flat);
            }
        }
    }

    private function processAddCategory() {
        $name      = trim(Tools::getValue('category_name'));
        $id_parent = (int)Tools::getValue('parent_category');

        if (empty($name)) { $this->errors[] = 'Category name is required.'; return; }
        if ($id_parent <= 0) { $this->errors[] = 'Please select a parent category.'; return; }

        $id_lang = $this->context->language->id;
        $category = new Category();
        $category->name         = [$id_lang => $name];
        $category->link_rewrite = [$id_lang => Tools::link_rewrite($name)];
        $category->id_parent    = $id_parent;
        $category->active       = 1;

        if ($category->add()) {
            $this->confirmations[] = 'Category "' . $name . '" added successfully!';
        } else {
            $this->errors[] = 'Failed to add category.';
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
                $this->uploadProductImage($product->id, $_FILES['product_image']['tmp_name']);
            }
            $this->confirmations[] = 'Product "' . $name . '" added successfully!';
        } else {
            $this->errors[] = 'Failed to save product.';
        }
    }

    protected function uploadProductImage($id_product, $tmp_file) {
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
        header('Content-Type: application/json');
        $plant_name = Tools::getValue('plant_name');
        $openai_key = Configuration::get('PHYTO_OPENAI_KEY');

        if (empty($openai_key)) {
            echo json_encode(['error' => 'OpenAI API key not set. Go to Settings tab.']);
            exit;
        }
        if (empty($plant_name)) {
            echo json_encode(['error' => 'Plant name is required.']);
            exit;
        }

        $prompt = "Write a compelling ecommerce product description for a rare/exotic plant called '$plant_name'. "
                . "Include what makes it special, care difficulty, size, ideal conditions, why someone should buy it. "
                . "Under 200 words. Also a 2-sentence short description. "
                . "Return ONLY valid JSON with keys: description, short_description";

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

        if ($err) { echo json_encode(['error' => 'cURL error: ' . $err]); exit; }

        $data = json_decode($result, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $parsed  = json_decode($content, true);
            echo json_encode($parsed ?: ['description' => $content, 'short_description' => '']);
        } else {
            echo json_encode(['error' => 'OpenAI returned no content. Check your API key and quota.']);
        }
        exit;
    }
}
