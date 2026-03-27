<?php
if (!defined('_PS_VERSION_')) exit;

/**
 * PhytoCommerce Pack — 1-click installer for the full PhytoCommerce module suite.
 *
 * Upload this module to PrestaShop, click Install, and all PhytoCommerce modules
 * are copied into place and installed automatically.
 *
 * For standalone zip deployment: place the other phytocommerce module directories
 * inside phytocommerce_pack/bundled/ before zipping.
 * When deploying from a full repo checkout (all modules already in /modules/),
 * the pack detects them automatically — no bundling needed.
 */
class Phytocommerce_Pack extends Module {

    // Install order matters for soft dependencies
    // (e.g. acclimation_bundler reads growth_stage data)
    const MODULES = [
        // Foundation
        'phytocommercefooter',
        'phytoquickadd',
        'phytoerpconnector',
        'phytoseobooster',
        // Plant Science
        'phyto_grex_registry',
        'phyto_tc_batch_tracker',
        'phyto_growth_stage',
        'phyto_seasonal_availability',
        'phyto_care_card',
        'phyto_climate_zone',
        'phyto_acclimation_bundler',
        'phyto_live_arrival',
        // Customer & Community
        'phyto_growers_journal',
        'phyto_collection_widget',
        'phyto_source_badge',
        // Operations & Compliance
        'phyto_dispatch_logger',
        'phyto_phytosanitary',
        'phyto_tc_cost_calculator',
        // Commerce
        'phyto_wholesale_portal',
        'phyto_subscription',
        // Security
        'phyto_image_sec',
    ];

    public function __construct() {
        $this->name          = 'phytocommerce_pack';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'Phyto Evolution';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = 'PhytoCommerce Pack';
        $this->description = '1-click installer for the complete PhytoCommerce module suite — 21 modules for specialty plant e-commerce.';
    }

    public function install() {
        if (!parent::install()
            || !$this->executeSqlFile('install')
            || !$this->installTab()
        ) {
            return false;
        }
        // Best-effort: install all sub-modules; failures are logged, not blocking
        $this->installAllModules();
        return true;
    }

    public function uninstall() {
        return $this->uninstallTab()
            && $this->executeSqlFile('uninstall')
            && parent::uninstall();
    }

    public function getContent() {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoPack')
        );
    }

    // ── Tab ───────────────────────────────────────────────────────────────────

    private function installTab() {
        $tab = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoPack';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'PhytoCommerce Pack';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminAdminPreferences');
        $tab->module    = $this->name;
        return $tab->add();
    }

    private function uninstallTab() {
        $id_tab = (int)Tab::getIdFromClassName('AdminPhytoPack');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    // ── SQL ───────────────────────────────────────────────────────────────────

    private function executeSqlFile($type) {
        $sql_file = __DIR__ . '/sql/' . $type . '.sql';
        if (!file_exists($sql_file)) return true;
        $sql = file_get_contents($sql_file);
        $sql = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt && !Db::getInstance()->execute($stmt)) return false;
        }
        return true;
    }

    // ── Module installer ──────────────────────────────────────────────────────

    private function installAllModules() {
        foreach (self::MODULES as $module_name) {
            $this->ensureModulePresent($module_name);
            $module = Module::getInstanceByName($module_name);
            if (!$module) {
                $this->logResult($module_name, 'failed', 'Could not load module class');
                continue;
            }
            if ($module->isInstalled()) {
                $this->logResult($module_name, 'skipped', 'Already installed');
                continue;
            }
            if ($module->install()) {
                $this->logResult($module_name, 'installed', '');
            } else {
                $this->logResult($module_name, 'failed', 'install() returned false');
            }
        }
    }

    /**
     * Ensure the module directory exists in the PS modules folder.
     * Checks:
     *   1. Already in _PS_MODULE_DIR_           (full repo checkout)
     *   2. In this pack's bundled/ directory   (standalone zip deployment)
     */
    private function ensureModulePresent($module_name) {
        $dest = _PS_MODULE_DIR_ . $module_name;
        if (is_dir($dest)) return; // Already present

        $src = __DIR__ . '/bundled/' . $module_name;
        if (is_dir($src)) {
            $this->copyDir($src, $dest);
        }
        // If neither — install will fail gracefully and be logged
    }

    private function copyDir($src, $dest) {
        if (!is_dir($dest)) mkdir($dest, 0755, true);
        foreach (scandir($src) as $file) {
            if ($file === '.' || $file === '..') continue;
            $s = $src . '/' . $file;
            $d = $dest . '/' . $file;
            is_dir($s) ? $this->copyDir($s, $d) : copy($s, $d);
        }
    }

    private function logResult($module_name, $status, $message) {
        Db::getInstance()->insert('phyto_pack_log', [
            'module_name'  => pSQL($module_name),
            'status'       => pSQL($status),
            'message'      => pSQL($message),
            'installed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Public helpers (used by admin controller) ─────────────────────────────

    public static function getModuleList() {
        return self::MODULES;
    }

    public static function getModuleStatus() {
        $statuses = [];
        foreach (self::MODULES as $module_name) {
            $module = Module::getInstanceByName($module_name);
            $statuses[$module_name] = [
                'name'       => $module_name,
                'loaded'     => (bool)$module,
                'installed'  => $module ? $module->isInstalled() : false,
                'active'     => $module ? $module->active : false,
                'version'    => $module ? $module->version : '—',
                'present'    => is_dir(_PS_MODULE_DIR_ . $module_name),
            ];
        }
        return $statuses;
    }
}
