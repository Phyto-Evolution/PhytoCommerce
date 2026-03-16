<?php
if (!defined('_PS_VERSION_')) exit;

class AdminPhytoQuickAddController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent() {
        parent::initContent();

        if (Tools::isSubmit('saveOpenAIKey')) {
            Configuration::updateValue('PHYTO_OPENAI_KEY', Tools::getValue('openai_key'));
            $this->confirmations[] = 'OpenAI API key saved successfully';
        }

        if (Tools::isSubmit('submitQuickAdd')) {
            $this->processQuickAdd();
        }

        if (Tools::isSubmit('generateDescription')) {
            $this->generateAIDescription();
        }

        $categories = Category::getSimpleCategories($this->context->language->id);
        
        $this->context->smarty->assign([
            'categories' => is_array($categories) ? $categories : [],
            'openai_key' => Configuration::get('PHYTO_OPENAI_KEY') ?: '',
            'token' => $this->token,
            'current_index' => self::$currentIndex,
        ]);

        $this->setTemplate('module:phytoquickadd/views/templates/admin/quickadd.tpl');
    }

    private function processQuickAdd() {
        $errors = [];
        $name = Tools::getValue('product_name');
        $price = (float)Tools::getValue('product_price');
        $quantity = (int)Tools::getValue('product_quantity');
        $id_category = (int)Tools::getValue('product_category');
        $description = Tools::getValue('product_description');
        $short_description = Tools::getValue('product_short_description');

        if (empty($name)) $errors[] = 'Product name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than 0';
        if ($id_category <= 0) $errors[] = 'Please select a category';

        if (empty($errors)) {
            $product = new Product();
            $product->name = [$this->context->language->id => $name];
            $product->description = [$this->context->language->id => $description];
            $product->description_short = [$this->context->language->id => $short_description];
            $product->price = $price;
            $product->id_category_default = $id_category;
            $product->active = 1;
            $product->visibility = 'both';
            $product->link_rewrite = [$this->context->language->id => Tools::link_rewrite($name)];

            if ($product->add()) {
                StockAvailable::setQuantity($product->id, 0, $quantity);
                $product->addToCategories([$id_category]);

                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                    $this->uploadProductImage($product->id, $_FILES['product_image']);
                }

                $this->confirmations[] = 'Product "' . $name . '" added successfully!';
            } else {
                $errors[] = 'Failed to add product. Please try again.';
            }
        }

        $this->errors = array_merge($this->errors, $errors);
    }

    private function uploadProductImage($id_product, $file) {
        $image = new Image();
        $image->id_product = $id_product;
        $image->position = Image::getHighestPosition($id_product) + 1;
        $image->cover = true;
        if ($image->add()) {
            $image_path = $image->getPathForCreation();
            ImageManager::resize($file['tmp_name'], $image_path . '.jpg');
            foreach (ImageType::getImagesTypes('products') as $image_type) {
                ImageManager::resize(
                    $file['tmp_name'],
                    $image_path . '-' . $image_type['name'] . '.jpg',
                    $image_type['width'],
                    $image_type['height']
                );
            }
        }
    }

    private function generateAIDescription() {
        $plant_name = Tools::getValue('plant_name');
        $openai_key = Configuration::get('PHYTO_OPENAI_KEY');

        if (empty($openai_key)) {
            die(json_encode(['error' => 'OpenAI API key not configured. Please save your key first.']));
        }

        $prompt = "Write a compelling ecommerce product description for a rare/carnivorous plant called '$plant_name'. Include: what makes it special, care difficulty, size, ideal conditions, and why someone should buy it. Keep under 200 words. Also provide a short 2-sentence summary. Return ONLY valid JSON: {\"description\": \"...\", \"short_description\": \"...\"}";

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openai_key,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 400,
        ]));

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
            $parsed = json_decode($content, true);
            die(json_encode($parsed ?? ['description' => $content, 'short_description' => '']));
        }
        die(json_encode(['error' => 'Failed to generate description']));
    }
}
