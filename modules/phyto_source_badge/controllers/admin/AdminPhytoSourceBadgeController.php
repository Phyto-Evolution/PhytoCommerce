<?php
/**
 * PhytoCommerce — AdminPhytoSourceBadgeController
 *
 * Back-office CRUD controller for badge definitions.
 * Lists all badges via HelperList; creates/edits via HelperForm.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/PhytoSourceBadgeDef.php';

class AdminPhytoSourceBadgeController extends ModuleAdminController
{
    /** @var string  DB table name without prefix */
    protected $table = 'phyto_source_badge_def';

    /** @var string  Primary-key field name */
    protected $identifier = 'id_badge';

    /** @var string  ObjectModel class name */
    protected $className = 'PhytoSourceBadgeDef';

    /** @var bool  Allow row deletion */
    protected $delete = true;

    /** @var bool  Allow list filtering */
    protected $list_no_link = false;

    /** @var bool  Bootstrap-themed markup */
    protected $bootstrap = true;

    /**
     * Constructor — configure list columns and form fields.
     */
    public function __construct()
    {
        // ── List columns ────────────────────────────────────────────
        $this->fields_list = [
            'id_badge'    => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'class'   => 'fixed-width-xs',
            ],
            'badge_label' => [
                'title'   => $this->l('Label'),
                'width'   => 200,
            ],
            'badge_slug'  => [
                'title'   => $this->l('Slug'),
                'width'   => 150,
            ],
            'badge_color' => [
                'title'   => $this->l('Color'),
                'align'   => 'center',
                'callback' => 'renderColorSwatch',
            ],
            'sort_order'  => [
                'title'   => $this->l('Sort order'),
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
                'orderby' => true,
            ],
        ];

        // ── Form fields ─────────────────────────────────────────────
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Badge Definition'),
                'icon'  => 'icon-tag',
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Badge label'),
                    'name'     => 'badge_label',
                    'required' => true,
                    'hint'     => $this->l('Human-readable name shown to shoppers, e.g. "TC Lab".'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Badge slug'),
                    'name'     => 'badge_slug',
                    'required' => true,
                    'hint'     => $this->l('URL/CSS-safe identifier (auto-generated from label; must be unique).'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Colour'),
                    'name'    => 'badge_color',
                    'options' => [
                        'query' => [
                            ['id' => 'green', 'name' => $this->l('Green')],
                            ['id' => 'blue',  'name' => $this->l('Blue')],
                            ['id' => 'amber', 'name' => $this->l('Amber')],
                            ['id' => 'red',   'name' => $this->l('Red')],
                            ['id' => 'gray',  'name' => $this->l('Gray')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Description'),
                    'name'  => 'description',
                    'rows'  => 4,
                    'cols'  => 40,
                    'hint'  => $this->l('Longer explanation of the sourcing method, shown in tooltips.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Sort order'),
                    'name'  => 'sort_order',
                    'class' => 'fixed-width-sm',
                    'hint'  => $this->l('Lower number = displayed first.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        parent::__construct();

        $this->_orderBy    = 'sort_order';
        $this->_orderWay   = 'ASC';
        $this->_defaultOrderBy  = 'sort_order';
        $this->_defaultOrderWay = 'ASC';
    }

    // ──────────────────────────────────────────────────────────────
    //  Object load / form population
    // ──────────────────────────────────────────────────────────────

    /**
     * Load object for the edit form, or create a new one.
     *
     * @param bool $opt  (unused, required by parent signature)
     *
     * @return PhytoSourceBadgeDef
     */
    public function loadObject($opt = false)
    {
        /** @var PhytoSourceBadgeDef $object */
        $object = parent::loadObject($opt);

        return $object;
    }

    // ──────────────────────────────────────────────────────────────
    //  Save / validate
    // ──────────────────────────────────────────────────────────────

    /**
     * Pre-process the form: auto-generate slug from label if empty.
     *
     * @return void
     */
    public function beforeAdd(PhytoSourceBadgeDef $object): void
    {
        $this->autoSlug($object);
    }

    /**
     * Pre-process the form on update.
     *
     * @return void
     */
    public function beforeUpdate(PhytoSourceBadgeDef $object): void
    {
        $this->autoSlug($object);
    }

    /**
     * Auto-fill the slug from the label when the slug field is empty.
     *
     * @param PhytoSourceBadgeDef $object
     *
     * @return void
     */
    protected function autoSlug(PhytoSourceBadgeDef $object): void
    {
        if (empty(trim((string) $object->badge_slug))) {
            $object->badge_slug = PhytoSourceBadgeDef::slugify((string) $object->badge_label);
        }
    }

    /**
     * Process the save action (both add and update).
     *
     * @return bool
     */
    public function processSave(): bool
    {
        /** @var PhytoSourceBadgeDef $object */
        $object = $this->loadObject(true);

        // Auto-generate slug if the submitted value is blank
        $slug = trim(Tools::getValue('badge_slug', ''));
        if ($slug === '') {
            $label = Tools::getValue('badge_label', '');
            $_POST['badge_slug'] = PhytoSourceBadgeDef::slugify($label);
        }

        return parent::processSave();
    }

    // ──────────────────────────────────────────────────────────────
    //  List callbacks
    // ──────────────────────────────────────────────────────────────

    /**
     * HelperList callback — render a colored swatch for the badge_color column.
     *
     * @param string $color  The color slug (green, blue, amber, red, gray)
     *
     * @return string  HTML
     */
    public function renderColorSwatch(string $color): string
    {
        $map = [
            'green' => '#2e7d32',
            'blue'  => '#1565c0',
            'amber' => '#e65100',
            'red'   => '#c62828',
            'gray'  => '#546e7a',
        ];

        $hex = isset($map[$color]) ? $map[$color] : '#546e7a';

        return sprintf(
            '<span style="display:inline-block;width:18px;height:18px;border-radius:3px;'
            . 'background:%s;vertical-align:middle;margin-right:4px;"></span>'
            . '<small>%s</small>',
            htmlspecialchars($hex),
            htmlspecialchars($color)
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  Inline JS — auto-slug on label keyup
    // ──────────────────────────────────────────────────────────────

    /**
     * Inject a small JS snippet that auto-fills the slug from the label.
     *
     * @return string
     */
    public function renderForm(): string
    {
        $html  = parent::renderForm();
        $html .= '<script>
(function () {
    "use strict";

    function slugify(str) {
        return str
            .toLowerCase()
            .replace(/[^\w\s-]/g, "")
            .replace(/[\s_]+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    var labelInput = document.querySelector("#badge_label");
    var slugInput  = document.querySelector("#badge_slug");

    if (labelInput && slugInput) {
        var userModifiedSlug = slugInput.value.length > 0;

        slugInput.addEventListener("input", function () {
            userModifiedSlug = true;
        });

        labelInput.addEventListener("keyup", function () {
            if (!userModifiedSlug) {
                slugInput.value = slugify(this.value);
            }
        });
    }
}());
</script>';

        return $html;
    }
}
