<?php
/**
 * AdminPhytoImageSecController — AJAX batch-watermark endpoint.
 *
 * Hidden from the menu (id_parent = -1). Called exclusively by admin.js
 * to process product images in chunks of 20 to avoid PHP time-out.
 *
 * Request flow:
 *   1. JS POSTs  action=Init   → returns { total: N }
 *   2. JS POSTs  action=Chunk  offset=0   → processes images 0–19,  returns progress
 *   3. JS POSTs  action=Chunk  offset=20  → processes images 20–39, returns progress
 *   …until done=true
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoImageSecController extends ModuleAdminController
{
    const CHUNK_SIZE = 20;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        // Load the watermarker class if not already loaded
        require_once dirname(__FILE__) . '/../../classes/PhytoImageWatermarker.php';
    }

    /**
     * No list view — this controller is AJAX-only.
     */
    public function initContent(): void
    {
        // intentionally blank
    }

    // ──────────────────────────────────────────────────────────────
    //  AJAX: Init — count total product images
    // ──────────────────────────────────────────────────────────────

    public function ajaxProcessInit(): void
    {
        $total = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'image`'
        );

        $this->sendJson(['success' => true, 'total' => $total]);
    }

    // ──────────────────────────────────────────────────────────────
    //  AJAX: Chunk — watermark the next slice of images
    // ──────────────────────────────────────────────────────────────

    public function ajaxProcessChunk(): void
    {
        $offset = max(0, (int) Tools::getValue('offset', 0));

        $logoPath = _PS_IMG_DIR_ . Configuration::get('PS_LOGO');

        if (!file_exists($logoPath)) {
            $this->sendJson([
                'success' => false,
                'error'   => 'Shop logo not found at: ' . $logoPath,
            ]);
        }

        if (!Configuration::get('PHYTO_IMGSEC_WATERMARK_ENABLED')) {
            $this->sendJson([
                'success' => false,
                'error'   => 'Watermarking is disabled in module settings.',
            ]);
        }

        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $images = Db::getInstance()->executeS(
            'SELECT i.`id_image`, i.`id_product`,
                    COALESCE(pl.`name`, \'\') AS product_name
             FROM `' . _DB_PREFIX_ . 'image` i
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON pl.`id_product` = i.`id_product`
                   AND pl.`id_lang` = ' . $defaultLang . '
             ORDER BY i.`id_image` ASC
             LIMIT ' . self::CHUNK_SIZE . ' OFFSET ' . $offset
        );

        if (!$images) {
            $this->sendJson([
                'success'   => true,
                'processed' => 0,
                'offset'    => $offset,
                'total'     => $this->countImages(),
                'done'      => true,
            ]);
        }

        $watermarker = $this->module->buildWatermarker($logoPath);
        $imageTypes  = ImageType::getImagesTypes('products');
        $processed   = 0;

        foreach ($images as $row) {
            $idImage     = (int) $row['id_image'];
            $productName = (string) ($row['product_name'] ?? '');
            $folder      = Image::getImgFolderStatic($idImage);
            $baseDir     = _PS_PROD_IMG_DIR_ . $folder;

            // Watermark all thumbnail sizes
            foreach ($imageTypes as $type) {
                foreach (['.jpg', '.webp'] as $ext) {
                    $path = $baseDir . $idImage . '-' . $type['name'] . $ext;

                    if (file_exists($path)) {
                        $watermarker->apply($path, $productName);
                    }
                }
            }

            // Watermark the original (first extension that exists)
            foreach (['.jpg', '.png', '.webp'] as $ext) {
                $path = $baseDir . $idImage . $ext;

                if (file_exists($path)) {
                    $watermarker->apply($path, $productName);
                    break;
                }
            }

            $processed++;
        }

        $total    = $this->countImages();
        $newOffset = $offset + $processed;

        $this->sendJson([
            'success'   => true,
            'processed' => $processed,
            'offset'    => $newOffset,
            'total'     => $total,
            'done'      => ($newOffset >= $total),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    private function countImages(): int
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'image`'
        );
    }

    private function sendJson(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($data));
    }
}
