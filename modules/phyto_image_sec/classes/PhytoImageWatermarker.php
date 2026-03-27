<?php
/**
 * PhytoImageWatermarker — pure GD watermark engine.
 *
 * No PrestaShop dependencies — takes file paths and scalar config only.
 *
 * Supports: JPEG, PNG, GIF, WebP (WebP requires GD compiled with --with-webp).
 * Alpha channel transparency is handled correctly for PNG logos on JPEG targets.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoImageWatermarker
{
    /** @var string Absolute path to the watermark logo file */
    private string $logoPath;

    /** @var string center | bottom-right | bottom-left | tiled */
    private string $position;

    /** @var int 0–100 */
    private int $opacityPct;

    /** @var int watermark width as % of base image width (5–75) */
    private int $sizePct;

    public function __construct(
        string $logoPath,
        string $position,
        int    $opacityPct,
        int    $sizePct
    ) {
        $this->logoPath   = $logoPath;
        $this->position   = $position;
        $this->opacityPct = max(0, min(100, $opacityPct));
        $this->sizePct    = max(5, min(75, $sizePct));
    }

    // ──────────────────────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────────────────────

    /**
     * Apply watermark to a single image file (in-place).
     *
     * @param string $imagePath Absolute path to the image to watermark.
     *
     * @return bool True on success, false on any error.
     */
    public function apply(string $imagePath): bool
    {
        if (!file_exists($imagePath) || !is_writable($imagePath)) {
            return false;
        }

        $baseMime = $this->detectMime($imagePath);

        if ($baseMime === null) {
            return false;
        }

        $base = $this->loadImage($imagePath, $baseMime);

        if (!$base) {
            return false;
        }

        $logoMime = $this->detectMime($this->logoPath);
        $logo     = $this->loadImage($this->logoPath, $logoMime ?? 'image/png');

        if (!$logo) {
            imagedestroy($base);

            return false;
        }

        $baseW    = imagesx($base);
        $baseH    = imagesy($base);
        $logoOrigW = imagesx($logo);
        $logoOrigH = imagesy($logo);

        // Scale watermark proportionally
        $wmW = max(8, (int) ($baseW * $this->sizePct / 100));
        $wmH = max(8, (int) ($logoOrigH * ($wmW / $logoOrigW)));

        $wm = imagescale($logo, $wmW, $wmH, IMG_BICUBIC);
        imagedestroy($logo);

        if (!$wm) {
            imagedestroy($base);

            return false;
        }

        // Adjust opacity (pixel-level alpha manipulation — handles PNG transparency)
        if ($this->opacityPct < 100) {
            $wm = $this->adjustOpacity($wm, $wmW, $wmH);
        }

        // Enable alpha blending on base so PNG watermarks composite correctly
        imagealphablending($base, true);

        if ($this->position === 'tiled') {
            $this->applyTiled($base, $wm, $baseW, $baseH, $wmW, $wmH);
        } else {
            [$x, $y] = $this->calcPosition($baseW, $baseH, $wmW, $wmH);
            imagecopy($base, $wm, $x, $y, 0, 0, $wmW, $wmH);
        }

        imagedestroy($wm);

        $result = $this->saveImage($base, $imagePath, $baseMime);
        imagedestroy($base);

        return $result;
    }

    // ──────────────────────────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Detect MIME type via getimagesize() — does not rely on file extensions.
     */
    private function detectMime(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        $info = @getimagesize($path);

        if (!$info) {
            return null;
        }

        return $info['mime'];
    }

    /**
     * Load an image resource from disk.
     *
     * @return resource|GdImage|false
     */
    private function loadImage(string $path, string $mime)
    {
        switch ($mime) {
            case 'image/jpeg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                $img = @imagecreatefrompng($path);
                if ($img) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                }
                return $img;
            case 'image/gif':
                return @imagecreatefromgif($path);
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return @imagecreatefromwebp($path);
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Save an image resource back to disk, preserving format.
     *
     * @param resource|GdImage $img
     */
    private function saveImage($img, string $path, string $mime): bool
    {
        switch ($mime) {
            case 'image/jpeg':
                return imagejpeg($img, $path, 90);
            case 'image/png':
                imagesavealpha($img, true);
                return imagepng($img, $path, 9);
            case 'image/gif':
                return imagegif($img, $path);
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    return imagewebp($img, $path, 85);
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Per-pixel alpha adjustment — the only correct way to set opacity for a
     * PNG-with-alpha watermark in GD (no native layer opacity function exists).
     *
     * GD alpha: 0 = fully opaque, 127 = fully transparent.
     *
     * @param resource|GdImage $img
     * @return resource|GdImage  New image resource (input is destroyed)
     */
    private function adjustOpacity($img, int $w, int $h)
    {
        $adjusted = imagecreatetruecolor($w, $h);
        imagealphablending($adjusted, false);
        imagesavealpha($adjusted, true);

        $factor = $this->opacityPct / 100.0;

        for ($px = 0; $px < $w; $px++) {
            for ($py = 0; $py < $h; $py++) {
                $color = imagecolorat($img, $px, $py);
                $a     = ($color >> 24) & 0x7F;
                $r     = ($color >> 16) & 0xFF;
                $g     = ($color >> 8)  & 0xFF;
                $b     = $color         & 0xFF;

                // opaqueness ∈ [0,1]: how solid this pixel already is
                $opaqueness    = (127 - $a) / 127.0;
                $newOpaqueness = $opaqueness * $factor;
                $newAlpha      = (int) round(127 - ($newOpaqueness * 127));

                imagesetpixel(
                    $adjusted,
                    $px,
                    $py,
                    imagecolorallocatealpha($adjusted, $r, $g, $b, $newAlpha)
                );
            }
        }

        imagedestroy($img);

        return $adjusted;
    }

    /**
     * Calculate the top-left pixel coordinate for the watermark given position.
     *
     * @return int[] [$x, $y]
     */
    private function calcPosition(int $bW, int $bH, int $wW, int $wH): array
    {
        $pad = 10;

        switch ($this->position) {
            case 'center':
                return [(int) (($bW - $wW) / 2), (int) (($bH - $wH) / 2)];

            case 'bottom-left':
                return [$pad, $bH - $wH - $pad];

            case 'top-right':
                return [$bW - $wW - $pad, $pad];

            case 'top-left':
                return [$pad, $pad];

            case 'bottom-right':
            default:
                return [$bW - $wW - $pad, $bH - $wH - $pad];
        }
    }

    /**
     * Tile the watermark across the entire base image in a diagonal-ish grid.
     *
     * @param resource|GdImage $base
     * @param resource|GdImage $wm
     */
    private function applyTiled($base, $wm, int $bW, int $bH, int $wW, int $wH): void
    {
        $stepX = (int) ($wW * 1.6);  // ~60% gap between tiles
        $stepY = (int) ($wH * 1.6);

        for ($x = 0; $x < $bW; $x += $stepX) {
            for ($y = 0; $y < $bH; $y += $stepY) {
                imagecopy($base, $wm, $x, $y, 0, 0, $wW, $wH);
            }
        }
    }
}
