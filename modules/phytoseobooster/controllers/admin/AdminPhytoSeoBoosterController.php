<?php
if (!defined('_PS_VERSION_')) exit;

require_once _PS_MODULE_DIR_ . 'phytoseobooster/classes/PhytoSeo.php';

class AdminPhytoSeoBoosterController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->display   = 'view';
    }

    public function init() {
        ob_start();
        parent::init();
        if (Tools::isSubmit('phyto_seo_ajax')) {
            header('Content-Type: application/json');
            $action = Tools::getValue('seo_action');
            ob_clean();
            switch ($action) {
                case 'audit':         $this->ajaxAudit();        break;
                case 'generate_meta': $this->ajaxGenerateMeta(); break;
                case 'bulk_generate': $this->ajaxBulkGenerate(); break;
                default: echo json_encode(['error' => 'Unknown action']); exit;
            }
        }
    }

    public function postProcess() {
        if (Tools::isSubmit('saveSeoSettings')) {
            Configuration::updateValue('PHYTO_AI_KEY',        Tools::getValue('ai_key'));
            Configuration::updateValue('PHYTO_SEO_AUTO_META', (int)Tools::getValue('auto_meta'));
            $this->confirmations[] = 'SEO Booster settings saved.';
        }
    }

    public function renderView() {
        $id_lang = $this->context->language->id;

        $this->context->smarty->assign([
            'ai_key'        => Configuration::get('PHYTO_AI_KEY') ?: '',
            'auto_meta'     => (int)Configuration::get('PHYTO_SEO_AUTO_META'),
            'ajax_url'      => $this->context->link->getAdminLink('AdminPhytoSeoBooster'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phytoseobooster/views/templates/admin/seobooster.tpl'
        );
    }

    // ── AJAX handlers ────────────────────────────────────────────────────────

    private function ajaxAudit() {
        $id_lang = $this->context->language->id;
        $issues  = PhytoSeo::auditProducts($id_lang);
        echo json_encode(['success' => true, 'issues' => $issues, 'count' => count($issues)]);
        exit;
    }

    private function ajaxGenerateMeta() {
        $id_product = (int)Tools::getValue('id_product');
        $ai_key     = Configuration::get('PHYTO_AI_KEY');
        if (!$ai_key) { echo json_encode(['error' => 'Claude AI key not set in Settings.']); exit; }
        $result = PhytoSeo::bulkGenerateMeta($id_product, $this->context->language->id, $ai_key);
        echo json_encode($result);
        exit;
    }

    private function ajaxBulkGenerate() {
        $ai_key = Configuration::get('PHYTO_AI_KEY');
        if (!$ai_key) { echo json_encode(['error' => 'Claude AI key not set in Settings.']); exit; }

        $id_lang = $this->context->language->id;
        $issues  = PhytoSeo::auditProducts($id_lang);
        $done    = 0;
        $errors  = 0;

        foreach ($issues as $issue) {
            if (!in_array('no_meta_title', $issue['flags']) && !in_array('no_meta_desc', $issue['flags'])) continue;
            $result = PhytoSeo::bulkGenerateMeta((int)$issue['id_product'], $id_lang, $ai_key);
            if (isset($result['success'])) $done++;
            else $errors++;
            // Throttle to respect API rate limits
            usleep(300000); // 300ms between requests
        }

        echo json_encode([
            'success' => true,
            'generated' => $done,
            'errors'    => $errors,
            'message'   => $done . ' products updated, ' . $errors . ' errors.',
        ]);
        exit;
    }
}
