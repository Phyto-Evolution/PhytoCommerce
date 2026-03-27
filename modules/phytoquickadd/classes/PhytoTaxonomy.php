<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoTaxonomy {

    const GITHUB_BASE = 'https://raw.githubusercontent.com/Phyto-Evolution/PhytoCommerce/main/taxonomy/';
    const CACHE_TTL   = 3600;

    public static function fetchIndex() {
        return self::fetchJson(self::GITHUB_BASE . 'index.json');
    }

    public static function fetchCategoryIndex($category_id) {
        return self::fetchJson(self::GITHUB_BASE . $category_id . '/index.json');
    }

    public static function fetchPack($file_path) {
        return self::fetchJson(self::GITHUB_BASE . $file_path);
    }

    private static function fetchJson($url) {
        $cache_key = 'phyto_' . md5($url);
        $cached    = Configuration::get($cache_key);

        if ($cached) {
            $data = json_decode($cached, true);
            if ($data && isset($data['_cached_at']) && (time() - $data['_cached_at']) < self::CACHE_TTL) {
                unset($data['_cached_at']);
                return $data;
            }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'PhytoCommerce/3.0',
        ]);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200 || !$result) return null;

        $data = json_decode($result, true);
        if (!$data) return null;

        $data['_cached_at'] = time();
        Configuration::updateValue($cache_key, json_encode($data), false, null, null, true);
        unset($data['_cached_at']);
        return $data;
    }

    public static function importPack($pack_file, $id_lang) {
        $pack = self::fetchPack($pack_file);
        if (!$pack) return ['success' => false, 'error' => 'Could not fetch pack from GitHub.'];

        $imported = 0;
        $skipped  = 0;
        $log      = [];

        $family_meta = [
            'meta_title'       => $pack['meta_title']       ?? '',
            'meta_description' => $pack['meta_description'] ?? '',
            'meta_keywords'    => $pack['meta_keywords']    ?? '',
        ];

        foreach ($pack['genera'] as $genus_data) {
            $family_id = self::ensureCategory(
                $pack['family'],
                $pack['description'] ?? $pack['common_name'],
                2,
                $id_lang,
                $family_meta
            );
            if (!$family_id) { $skipped++; continue; }
            $log[] = 'Family: ' . $pack['family'];

            $genus_meta = [
                'meta_title'       => $genus_data['meta_title']       ?? '',
                'meta_description' => $genus_data['meta_description'] ?? '',
                'meta_keywords'    => $genus_data['meta_keywords']    ?? '',
            ];
            $genus_id = self::ensureCategory(
                $genus_data['genus'],
                $genus_data['description'] ?? $genus_data['common_name'] ?? $genus_data['genus'],
                $family_id,
                $id_lang,
                $genus_meta
            );
            if (!$genus_id) { $skipped++; continue; }
            $log[] = '  Genus: ' . $genus_data['genus'];
            $imported++;

            if (!empty($genus_data['species'])) {
                foreach ($genus_data['species'] as $species) {
                    $species_meta = [
                        'meta_title'       => $species['meta_title']       ?? '',
                        'meta_description' => $species['meta_description'] ?? '',
                        'meta_keywords'    => $species['meta_keywords']    ?? '',
                    ];
                    $species_id = self::ensureCategory(
                        $species['full_name'],
                        $species['description'] ?? $species['full_name'],
                        $genus_id,
                        $id_lang,
                        $species_meta
                    );
                    if ($species_id) {
                        $log[] = '    Species: ' . $species['full_name'];
                        $imported++;
                        if (!empty($species['cultivars'])) {
                            foreach ($species['cultivars'] as $cultivar) {
                                $cultivar_name = $species['full_name'] . " '" . $cultivar['cultivar'] . "'";
                                $cultivar_meta = [
                                    'meta_description' => $cultivar['description'] ?? '',
                                ];
                                $cid = self::ensureCategory(
                                    $cultivar_name,
                                    $cultivar['description'] ?? $cultivar_name,
                                    $species_id,
                                    $id_lang,
                                    $cultivar_meta
                                );
                                if ($cid) { $log[] = '      Cultivar: ' . $cultivar_name; $imported++; }
                            }
                        }
                    }
                }
            }
        }

        Configuration::updateValue('PHYTO_PACK_' . md5($pack_file), json_encode([
            'file'        => $pack_file,
            'family'      => $pack['family'],
            'imported_at' => date('Y-m-d H:i:s'),
            'count'       => $imported,
        ]));

        return ['success' => true, 'imported' => $imported, 'skipped' => $skipped, 'log' => $log];
    }

    public static function ensureCategory($name, $description, $id_parent, $id_lang, $meta = []) {
        $existing = Db::getInstance()->getValue(
            'SELECT cl.id_category FROM ' . _DB_PREFIX_ . 'category_lang cl
             JOIN ' . _DB_PREFIX_ . 'category c ON c.id_category = cl.id_category
             WHERE cl.name = \'' . pSQL($name) . '\'
             AND c.id_parent = ' . (int)$id_parent . '
             AND cl.id_lang = ' . (int)$id_lang
        );
        if ($existing) return (int)$existing;

        $category = new Category();
        $category->name             = [$id_lang => $name];
        $category->description      = [$id_lang => $description];
        $category->link_rewrite     = [$id_lang => Tools::link_rewrite($name)];
        $category->id_parent        = $id_parent;
        $category->active           = 1;
        $category->is_root_category = false;
        if (!empty($meta['meta_title']))       $category->meta_title       = [$id_lang => $meta['meta_title']];
        if (!empty($meta['meta_description'])) $category->meta_description = [$id_lang => $meta['meta_description']];
        if (!empty($meta['meta_keywords']))    $category->meta_keywords    = [$id_lang => $meta['meta_keywords']];
        return $category->add() ? (int)$category->id : false;
    }

    public static function getImportedPacks() {
        $imported = [];
        $result = Db::getInstance()->executeS(
            'SELECT name, value FROM ' . _DB_PREFIX_ . 'configuration WHERE name LIKE \'PHYTO_PACK_%\''
        );
        foreach ($result as $row) {
            $data = json_decode($row['value'], true);
            if ($data) $imported[$data['file']] = $data;
        }
        return $imported;
    }

    public static function getSuggestions($term, $id_lang) {
        $term = pSQL(strtolower($term));
        return Db::getInstance()->executeS(
            'SELECT c.id_category, cl.name,
                    (SELECT cl2.name FROM ' . _DB_PREFIX_ . 'category_lang cl2
                     WHERE cl2.id_category = c.id_parent AND cl2.id_lang = ' . (int)$id_lang . ') as parent_name
             FROM ' . _DB_PREFIX_ . 'category c
             JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = c.id_category
             WHERE cl.id_lang = ' . (int)$id_lang . '
             AND LOWER(cl.name) LIKE \'%' . $term . '%\'
             AND c.active = 1
             LIMIT 10'
        );
    }

    public static function clearCache($url = null) {
        if ($url) {
            Configuration::deleteByName('phyto_' . md5(self::GITHUB_BASE . $url));
        } else {
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'configuration WHERE name LIKE \'phyto_%\' AND name NOT LIKE \'PHYTO_PACK_%\' AND name NOT LIKE \'PHYTO_OPENAI_%\''
            );
        }
    }
}
