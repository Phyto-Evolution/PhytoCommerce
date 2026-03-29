<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoSeo {

    // Prevent recursive hook calls during auto-meta update
    private static $generating = false;

    // ── Schema Markup ────────────────────────────────────────────────────────

    public static function generateSchemaMarkup(Product $product, $id_lang, Context $context) {
        $name = is_array($product->name)
            ? ($product->name[$id_lang] ?? reset($product->name))
            : $product->name;
        $desc = is_array($product->description_short)
            ? ($product->description_short[$id_lang] ?? reset($product->description_short))
            : $product->description_short;
        $desc = strip_tags($desc);

        $price     = number_format((float)Product::getPriceStatic($product->id, true), 2, '.', '');
        $available = StockAvailable::getQuantityAvailableByProduct($product->id) > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';

        $cover = Product::getCover($product->id);
        $image_url = '';
        if ($cover) {
            $image_obj  = new Image($cover['id_image']);
            $image_url  = $context->link->getImageLink($product->link_rewrite, $image_obj->id . '-' . $image_obj->id_product);
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $name,
            'description' => $desc,
            'sku'      => $product->reference ?: 'PS-' . $product->id,
            'brand'    => [
                '@type' => 'Brand',
                'name'  => Configuration::get('PS_SHOP_NAME') ?: 'Phyto Evolution',
            ],
            'offers'   => [
                '@type'         => 'Offer',
                'price'         => $price,
                'priceCurrency' => 'INR',
                'availability'  => $available,
                'seller'        => [
                    '@type' => 'Organization',
                    'name'  => Configuration::get('PS_SHOP_NAME') ?: 'Phyto Evolution',
                ],
            ],
        ];

        if ($image_url) $schema['image'] = $image_url;

        // Add botanical meta if stored in product description as structured hint
        $meta_title = is_array($product->meta_title)
            ? ($product->meta_title[$id_lang] ?? '')
            : $product->meta_title;
        if ($meta_title) $schema['alternateName'] = $meta_title;

        $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    // ── Auto Meta Generation ─────────────────────────────────────────────────

    public static function autoGenerateMeta(Product $product, $id_lang) {
        if (self::$generating) return;

        $meta_title = is_array($product->meta_title)
            ? ($product->meta_title[$id_lang] ?? '')
            : (string)$product->meta_title;
        $meta_desc = is_array($product->meta_description)
            ? ($product->meta_description[$id_lang] ?? '')
            : (string)$product->meta_description;

        if (!empty(trim($meta_title)) && !empty(trim($meta_desc))) return;

        $ai_key = Configuration::get('PHYTO_AI_KEY');
        if (!$ai_key) return;

        $name = is_array($product->name)
            ? ($product->name[$id_lang] ?? reset($product->name))
            : $product->name;
        if (empty($name)) return;

        $generated = self::callClaudeForMeta($name, $ai_key);
        if (!$generated) return;

        self::$generating = true;

        if (empty(trim($meta_title)) && !empty($generated['meta_title'])) {
            $product->meta_title = array_merge(
                (array)$product->meta_title,
                [$id_lang => $generated['meta_title']]
            );
        }
        if (empty(trim($meta_desc)) && !empty($generated['meta_description'])) {
            $product->meta_description = array_merge(
                (array)$product->meta_description,
                [$id_lang => $generated['meta_description']]
            );
        }
        $product->update();

        self::$generating = false;
    }

    // ── Alt Text Generation ──────────────────────────────────────────────────

    public static function generateAltText($product_name, $ai_key) {
        // Sanitize before interpolating into prompt
        $safe_name = preg_replace('/[^\p{L}\p{N}\s\.\-\'×]/u', '', (string) $product_name);
        $safe_name = mb_substr(trim($safe_name), 0, 100);
        if (empty($safe_name)) { return (string) $product_name; }

        $prompt = "Write a concise, SEO-friendly alt text (under 125 chars) for a product image of a plant called: '$safe_name'. "
                . "Return ONLY the alt text string, no quotes, no extra text.";
        $result = self::callClaude($prompt, $ai_key, 80);
        return $result ? trim($result) : $product_name;
    }

    // ── Bulk SEO Audit ───────────────────────────────────────────────────────

    public static function auditProducts($id_lang) {
        $products = Db::getInstance()->executeS(
            'SELECT p.id_product, p.reference, pl.name, pl.meta_title, pl.meta_description,
                    pl.description_short,
                    (SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'image i WHERE i.id_product = p.id_product) as img_count
             FROM ' . _DB_PREFIX_ . 'product p
             JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = p.id_product
             WHERE pl.id_lang = ' . (int)$id_lang . '
             AND p.active = 1
             ORDER BY p.id_product ASC'
        ) ?: [];

        $issues = [];
        foreach ($products as $p) {
            $flags = [];
            if (empty(trim($p['meta_title'])))       $flags[] = 'no_meta_title';
            if (empty(trim($p['meta_description'])))  $flags[] = 'no_meta_desc';
            if (strlen(strip_tags($p['description_short'])) < 50) $flags[] = 'short_desc';
            if ((int)$p['img_count'] === 0)           $flags[] = 'no_image';
            if (!empty($flags)) {
                $issues[] = array_merge($p, ['flags' => $flags]);
            }
        }
        return $issues;
    }

    // ── Bulk Meta Generation ─────────────────────────────────────────────────

    public static function bulkGenerateMeta($id_product, $id_lang, $ai_key) {
        $product = new Product($id_product, false, $id_lang);
        if (!Validate::isLoadedObject($product)) return ['error' => 'Product not found'];

        $name = is_array($product->name)
            ? ($product->name[$id_lang] ?? reset($product->name))
            : $product->name;

        $generated = self::callClaudeForMeta($name, $ai_key);
        if (!$generated) return ['error' => 'Claude API call failed'];

        $product->meta_title       = array_merge((array)$product->meta_title,       [$id_lang => $generated['meta_title']]);
        $product->meta_description = array_merge((array)$product->meta_description, [$id_lang => $generated['meta_description']]);

        self::$generating = true;
        $ok = $product->update();
        self::$generating = false;

        return $ok
            ? ['success' => true, 'meta_title' => $generated['meta_title'], 'meta_description' => $generated['meta_description']]
            : ['error' => 'Failed to save product'];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private static function callClaudeForMeta($plant_name, $ai_key) {
        $prompt = "Generate SEO metadata for a plant product called '$plant_name' sold by a botanical e-commerce store in India. "
                . "Return ONLY valid JSON: {\"meta_title\": \"...\", \"meta_description\": \"...\"}. "
                . "meta_title must be under 60 chars. meta_description must be under 155 chars. "
                . "Include the plant name and buying intent. Do not use quotes inside the values.";

        $text = self::callClaude($prompt, $ai_key, 200);
        if (!$text) return null;

        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);
        return json_decode($text, true);
    }

    private static function callClaude($prompt, $ai_key, $max_tokens = 300) {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $ai_key,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => $max_tokens,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result, true);
        return $data['content'][0]['text'] ?? null;
    }
}
