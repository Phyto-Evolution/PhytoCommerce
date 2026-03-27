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

    // Supported providers: id => label
    private static $AI_PROVIDERS = [
        'claude'   => 'Claude (Anthropic)',
        'openai'   => 'GPT-4o mini (OpenAI)',
        'gemini'   => 'Gemini Flash (Google)',
        'mistral'  => 'Mistral Small',
        'cohere'   => 'Command R (Cohere)',
    ];

    public function postProcess() {
        if (Tools::isSubmit('saveSettings')) {
            $provider = Tools::getValue('ai_provider');
            if (!array_key_exists($provider, self::$AI_PROVIDERS)) {
                $provider = 'claude';
            }
            Configuration::updateValue('PHYTO_AI_PROVIDER', $provider);
            Configuration::updateValue('PHYTO_AI_KEY', Tools::getValue('ai_key'));
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
            'ai_key'          => Configuration::get('PHYTO_AI_KEY') ?: '',
            'ai_provider'     => Configuration::get('PHYTO_AI_PROVIDER') ?: 'claude',
            'ai_providers'    => self::$AI_PROVIDERS,
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
        $name         = trim(Tools::getValue('product_name'));
        $price        = (float)Tools::getValue('product_price');
        $quantity     = (int)Tools::getValue('product_quantity');
        $category_ids = Tools::getValue('product_categories', []);

        if (!is_array($category_ids)) $category_ids = [$category_ids];
        $category_ids = array_values(array_filter(array_map('intval', $category_ids)));
        $id_category  = !empty($category_ids) ? $category_ids[0] : 0;

        if (empty($name))      { $this->errors[] = 'Product name is required.'; }
        if ($price <= 0)       { $this->errors[] = 'Price must be greater than 0.'; }
        if (empty($category_ids)) { $this->errors[] = 'Please select at least one category.'; }
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
            $product->addToCategories($category_ids);

            // Extract #hashtags from notes and save as PS product tags
            $notes = trim(Tools::getValue('product_notes', ''));
            if (!empty($notes)) {
                preg_match_all('/#([a-zA-Z0-9_\-]+)/', $notes, $matches);
                if (!empty($matches[1])) {
                    Tag::addTags($id_lang, $product->id, implode(',', $matches[1]));
                }
            }

            // Handle one or more uploaded images
            if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['error'])) {
                foreach ($_FILES['product_images']['error'] as $idx => $error) {
                    if ($error === UPLOAD_ERR_OK) {
                        $this->handleProductImage(
                            $product->id,
                            $_FILES['product_images']['tmp_name'][$idx],
                            $idx === 0  // first image is the cover
                        );
                    }
                }
            }
            $cat_count = count($category_ids);
            $this->confirmations[] = 'Product "' . $name . '" added to ' . $cat_count
                . ' ' . ($cat_count === 1 ? 'category' : 'categories') . '! <a href="'
                . $this->context->link->getAdminLink('AdminProducts')
                . '&id_product=' . $product->id . '&updateproduct" target="_blank">Edit full details →</a>';
        } else {
            $this->errors[] = 'Failed to save product.';
        }
    }

    protected function handleProductImage($id_product, $tmp_file, $isCover = true) {
        $image = new Image();
        $image->id_product = $id_product;
        $image->position   = Image::getHighestPosition($id_product) + 1;
        $image->cover      = $isCover;
        if ($image->add()) {
            $path = $image->getPathForCreation();
            ImageManager::resize($tmp_file, $path . '.jpg');
            foreach (ImageType::getImagesTypes('products') as $type) {
                ImageManager::resize($tmp_file, $path . '-' . $type['name'] . '.jpg', $type['width'], $type['height']);
            }
            // Fire the standard PS watermark hook so phyto_image_sec (and any
            // other watermark module) processes these thumbnails automatically.
            Hook::exec('actionWatermark', ['id_image' => $image->id, 'id_product' => $id_product]);
        }
    }

    private function ajaxGenerateDescription() {
        $plant_name = trim(Tools::getValue('plant_name'));
        $ai_key     = Configuration::get('PHYTO_AI_KEY');
        $provider   = Configuration::get('PHYTO_AI_PROVIDER') ?: 'claude';

        ob_clean();
        if (empty($ai_key))     { echo json_encode(['error' => 'AI key not set. Go to Settings tab.']); exit; }
        if (empty($plant_name)) { echo json_encode(['error' => 'Plant name is required.']); exit; }

        // Strip control characters and limit length to prevent prompt injection
        $plant_name = preg_replace('/[^\p{L}\p{N}\s\.\-\'×]/u', '', $plant_name);
        $plant_name = mb_substr($plant_name, 0, 100);
        if (empty($plant_name)) { echo json_encode(['error' => 'Invalid plant name.']); exit; }

        $prompt = "Write a compelling ecommerce product description for a rare/exotic plant called '$plant_name'. "
                . "Include what makes it special, care difficulty, size, ideal conditions, why someone should buy it. "
                . "Under 200 words. Also provide a 2-sentence short description. "
                . "Return ONLY valid JSON: {\"description\": \"...\", \"short_description\": \"...\"}";

        switch ($provider) {
            case 'openai':   $raw = $this->callOpenAI($prompt, $ai_key);   break;
            case 'gemini':   $raw = $this->callGemini($prompt, $ai_key);   break;
            case 'mistral':  $raw = $this->callMistral($prompt, $ai_key);  break;
            case 'cohere':   $raw = $this->callCohere($prompt, $ai_key);   break;
            default:         $raw = $this->callClaude($prompt, $ai_key);   break;
        }

        if (isset($raw['error'])) { echo json_encode($raw); exit; }

        // Unwrap markdown code fences if the model wrapped its JSON
        $content = preg_replace('/^```json\s*/i', '', trim($raw['text']));
        $content = preg_replace('/\s*```$/', '', $content);
        $parsed  = json_decode($content, true);
        echo json_encode($parsed ?: ['description' => $content, 'short_description' => '']);
        exit;
    }

    // ── AI provider implementations ───────────────────────────────────────────

    private function callClaude(string $prompt, string $key): array {
        $resp = $this->curlPost(
            'https://api.anthropic.com/v1/messages',
            json_encode([
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 500,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
            [
                'Content-Type: application/json',
                'x-api-key: ' . $key,
                'anthropic-version: 2023-06-01',
            ]
        );
        if ($resp['err']) return ['error' => 'Connection error: ' . $resp['err']];
        $d = json_decode($resp['body'], true);
        if (isset($d['content'][0]['text'])) return ['text' => $d['content'][0]['text']];
        return ['error' => 'Claude error: ' . ($d['error']['message'] ?? 'Unknown')];
    }

    private function callOpenAI(string $prompt, string $key): array {
        $resp = $this->curlPost(
            'https://api.openai.com/v1/chat/completions',
            json_encode([
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 500,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ]
        );
        if ($resp['err']) return ['error' => 'Connection error: ' . $resp['err']];
        $d = json_decode($resp['body'], true);
        if (isset($d['choices'][0]['message']['content'])) return ['text' => $d['choices'][0]['message']['content']];
        return ['error' => 'OpenAI error: ' . ($d['error']['message'] ?? 'Unknown')];
    }

    private function callGemini(string $prompt, string $key): array {
        $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($key);
        $resp = $this->curlPost(
            $url,
            json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]),
            ['Content-Type: application/json']
        );
        if ($resp['err']) return ['error' => 'Connection error: ' . $resp['err']];
        $d = json_decode($resp['body'], true);
        if (isset($d['candidates'][0]['content']['parts'][0]['text'])) return ['text' => $d['candidates'][0]['content']['parts'][0]['text']];
        return ['error' => 'Gemini error: ' . ($d['error']['message'] ?? 'Unknown')];
    }

    private function callMistral(string $prompt, string $key): array {
        $resp = $this->curlPost(
            'https://api.mistral.ai/v1/chat/completions',
            json_encode([
                'model'      => 'mistral-small-latest',
                'max_tokens' => 500,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ]
        );
        if ($resp['err']) return ['error' => 'Connection error: ' . $resp['err']];
        $d = json_decode($resp['body'], true);
        if (isset($d['choices'][0]['message']['content'])) return ['text' => $d['choices'][0]['message']['content']];
        return ['error' => 'Mistral error: ' . ($d['error']['message'] ?? 'Unknown')];
    }

    private function callCohere(string $prompt, string $key): array {
        $resp = $this->curlPost(
            'https://api.cohere.com/v2/chat',
            json_encode([
                'model'      => 'command-r-plus',
                'max_tokens' => 500,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ]
        );
        if ($resp['err']) return ['error' => 'Connection error: ' . $resp['err']];
        $d = json_decode($resp['body'], true);
        if (isset($d['message']['content'][0]['text'])) return ['text' => $d['message']['content'][0]['text']];
        return ['error' => 'Cohere error: ' . ($d['error']['message'] ?? 'Unknown')];
    }

    private function curlPost(string $url, string $body, array $headers): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $body,
        ]);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);
        return ['body' => $result, 'err' => $err];
    }

    private function ajaxSearchCategories() {
        $term    = Tools::getValue('term');
        $id_lang = $this->context->language->id;
        ob_clean();
        echo json_encode(PhytoTaxonomy::getSuggestions($term, $id_lang) ?: []);
        exit;
    }

    private function ajaxFetchPacks() {
        $index = PhytoTaxonomy::fetchIndex();
        if (!$index || empty($index['categories'])) {
            ob_clean();
            echo json_encode(['error' => 'Could not fetch taxonomy index from GitHub.']);
            exit;
        }

        $imported  = PhytoTaxonomy::getImportedPacks();
        $all_packs = [];

        // Root index.json has 'categories', not 'packs'.
        // Each category has its own index with a 'packs' array.
        foreach ($index['categories'] as $category) {
            $cat_index = PhytoTaxonomy::fetchCategoryIndex($category['id']);
            if (!$cat_index || empty($cat_index['packs'])) continue;

            foreach ($cat_index['packs'] as $pack) {
                // Prefix bare filename with category path so importPack()
                // builds the correct GitHub URL:
                // "nepenthaceae.json" → "carnivorous/nepenthaceae.json"
                $pack['file']        = $category['id'] . '/' . $pack['file'];
                $pack['category_id'] = $category['id'];
                $pack['category']    = $category['name'];
                $pack['imported']    = isset($imported[$pack['file']]);
                if ($pack['imported']) {
                    $pack['imported_at'] = $imported[$pack['file']]['imported_at'];
                    $pack['count']       = $imported[$pack['file']]['count'];
                }
                $all_packs[] = $pack;
            }
        }

        ob_clean(); // Flush any PHP warnings before writing JSON
        echo json_encode([
            'categories' => $index['categories'],
            'packs'      => $all_packs,
            'summary'    => $index['summary'] ?? [],
        ]);
        exit;
    }

    private function ajaxImportPack() {
        $pack_file = Tools::getValue('pack_file');
        if (empty($pack_file)) { ob_clean(); echo json_encode(['error' => 'No pack file specified.']); exit; }
        ob_clean();
        echo json_encode(PhytoTaxonomy::importPack($pack_file, $this->context->language->id));
        exit;
    }

    private function ajaxSyncPack() {
        $pack_file = Tools::getValue('pack_file');
        PhytoTaxonomy::clearCache($pack_file);
        ob_clean();
        echo json_encode(PhytoTaxonomy::importPack($pack_file, $this->context->language->id));
        exit;
    }

    private function ajaxAddCategory() {
        $name      = trim(Tools::getValue('category_name'));
        $id_parent = (int)Tools::getValue('parent_category');
        $id_lang   = $this->context->language->id;

        ob_clean();
        if (empty($name))    { echo json_encode(['error' => 'Category name is required.']); exit; }
        if ($id_parent <= 0) { echo json_encode(['error' => 'Please select a parent category.']); exit; }

        $result = PhytoTaxonomy::ensureCategory($name, $name, $id_parent, $id_lang);
        echo json_encode($result
            ? ['success' => true, 'id' => $result, 'name' => $name]
            : ['error' => 'Failed to add category. It may already exist.']
        );
        exit;
    }

    private function ajaxGetCategories() {
        $id_lang = $this->context->language->id;
        ob_clean();
        echo json_encode($this->getFlatCategories($id_lang) ?: []);
        exit;
    }

    private function ajaxGetSubcategories() {
        $id_parent = (int)Tools::getValue('id_parent');
        $id_lang   = $this->context->language->id;
        ob_clean();
        echo json_encode(Category::getChildren($id_parent, $id_lang, true) ?: []);
        exit;
    }
}
