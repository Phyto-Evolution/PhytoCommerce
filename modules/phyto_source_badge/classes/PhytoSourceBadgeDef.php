<?php
/**
 * PhytoCommerce — PhytoSourceBadgeDef
 *
 * ObjectModel for badge definitions stored in PREFIX_phyto_source_badge_def.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Represents a single sourcing-origin badge definition.
 *
 * Fields
 * ------
 * id_badge     — auto-increment primary key
 * badge_label  — human-readable name shown to shoppers
 * badge_slug   — URL/CSS-safe identifier (unique)
 * badge_color  — one of: green, blue, amber, red, gray
 * description  — longer text explaining the sourcing method
 * sort_order   — display ordering (lower = earlier)
 */
class PhytoSourceBadgeDef extends ObjectModel
{
    /** @var int */
    public $id_badge;

    /** @var string */
    public $badge_label;

    /** @var string */
    public $badge_slug;

    /** @var string */
    public $badge_color = 'gray';

    /** @var string */
    public $description;

    /** @var int */
    public $sort_order = 0;

    /**
     * ObjectModel definition.
     *
     * @var array
     */
    public static $definition = [
        'table'   => 'phyto_source_badge_def',
        'primary' => 'id_badge',
        'fields'  => [
            'badge_label' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 100,
            ],
            'badge_slug'  => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true,
                'size'     => 50,
            ],
            'badge_color' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 10,
            ],
            'description' => [
                'type'     => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'sort_order'  => [
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
        ],
    ];

    // ──────────────────────────────────────────────────────────────
    //  Static helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Return all badge definitions ordered by sort_order.
     *
     * @return array  Array of associative rows (raw DB result)
     */
    public static function getAll(): array
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_source_badge_def`
             ORDER BY `sort_order` ASC, `id_badge` ASC'
        ) ?: [];
    }

    /**
     * Return the list of allowed color identifiers.
     *
     * @return string[]
     */
    public static function getAllowedColors(): array
    {
        return ['green', 'blue', 'amber', 'red', 'gray'];
    }

    /**
     * Convert a string to a URL/CSS-safe slug.
     *
     * Strips accents, lower-cases, replaces spaces/special characters with
     * hyphens and trims leading/trailing hyphens.
     *
     * @param string $str
     *
     * @return string
     */
    public static function slugify(string $str): string
    {
        // Transliterate accented characters
        if (function_exists('transliterator_transliterate')) {
            $str = (string) transliterator_transliterate(
                'Any-Latin; Latin-ASCII; Lower()',
                $str
            );
        } else {
            $str = mb_strtolower($str, 'UTF-8');
        }

        // Keep only alphanumeric and hyphens
        $str = preg_replace('/[^a-z0-9\-]+/', '-', $str);
        $str = trim($str, '-');

        return $str;
    }
}
