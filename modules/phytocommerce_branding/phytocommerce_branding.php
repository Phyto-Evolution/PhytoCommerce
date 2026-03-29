<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Phytocommerce_Branding extends Module
{
    public const CFG_BRAND_NAME = 'PHYTO_BRAND_NAME';
    public const CFG_TAGLINE = 'PHYTO_BRAND_TAGLINE';
    public const CFG_PRIMARY = 'PHYTO_BRAND_PRIMARY';
    public const CFG_SECONDARY = 'PHYTO_BRAND_SECONDARY';
    public const CFG_ACCENT = 'PHYTO_BRAND_ACCENT';
    public const CFG_LOGO_URL = 'PHYTO_BRAND_LOGO_URL';
    public const CFG_CONTACT_EMAIL = 'PHYTO_BRAND_CONTACT_EMAIL';
    public const CFG_CONTACT_PHONE = 'PHYTO_BRAND_CONTACT_PHONE';
    public const CFG_CONTACT_ADDRESS = 'PHYTO_BRAND_CONTACT_ADDRESS';

    public function __construct()
    {
        $this->name = 'phytocommerce_branding';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Phyto Commerce';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Commerce Theme Branding');
        $this->description = $this->l('Applies your brand identity — name, tagline, primary/secondary/accent colours, logo URL, and contact details — as CSS custom-property tokens across compatible PrestaShop themes. Configure once in the admin and all theme components pick up the palette automatically. Designed for plant nurseries and tissue-culture stores wanting a cohesive, on-brand storefront without editing template files.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayAfterBodyOpeningTag')
            && $this->registerHook('displayHeader')
            && $this->installDefaults();
    }

    public function uninstall()
    {
        return $this->deleteConfigs() && parent::uninstall();
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitPhytoBranding')) {
            Configuration::updateValue(self::CFG_BRAND_NAME, (string) Tools::getValue(self::CFG_BRAND_NAME));
            Configuration::updateValue(self::CFG_TAGLINE, (string) Tools::getValue(self::CFG_TAGLINE));
            Configuration::updateValue(self::CFG_PRIMARY, (string) Tools::getValue(self::CFG_PRIMARY));
            Configuration::updateValue(self::CFG_SECONDARY, (string) Tools::getValue(self::CFG_SECONDARY));
            Configuration::updateValue(self::CFG_ACCENT, (string) Tools::getValue(self::CFG_ACCENT));
            Configuration::updateValue(self::CFG_LOGO_URL, (string) Tools::getValue(self::CFG_LOGO_URL));
            Configuration::updateValue(self::CFG_CONTACT_EMAIL, (string) Tools::getValue(self::CFG_CONTACT_EMAIL));
            Configuration::updateValue(self::CFG_CONTACT_PHONE, (string) Tools::getValue(self::CFG_CONTACT_PHONE));
            Configuration::updateValue(self::CFG_CONTACT_ADDRESS, (string) Tools::getValue(self::CFG_CONTACT_ADDRESS), true);

            return $this->displayConfirmation($this->l('Branding settings updated.')) . $this->renderForm();
        }

        return $this->renderForm();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'module-phytocommerce-branding',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );
    }

    public function hookDisplayHeader()
    {
        $this->context->smarty->assign([
            'phyto_brand_name' => (string) Configuration::get(self::CFG_BRAND_NAME),
            'phyto_brand_tagline' => (string) Configuration::get(self::CFG_TAGLINE),
            'phyto_brand_primary' => (string) Configuration::get(self::CFG_PRIMARY),
            'phyto_brand_secondary' => (string) Configuration::get(self::CFG_SECONDARY),
            'phyto_brand_accent' => (string) Configuration::get(self::CFG_ACCENT),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/brand_tokens.tpl');
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        $this->context->smarty->assign([
            'phyto_brand_name' => (string) Configuration::get(self::CFG_BRAND_NAME),
            'phyto_brand_tagline' => (string) Configuration::get(self::CFG_TAGLINE),
            'phyto_brand_logo_url' => (string) Configuration::get(self::CFG_LOGO_URL),
            'phyto_brand_contact_email' => (string) Configuration::get(self::CFG_CONTACT_EMAIL),
            'phyto_brand_contact_phone' => (string) Configuration::get(self::CFG_CONTACT_PHONE),
            'phyto_brand_contact_address' => (string) Configuration::get(self::CFG_CONTACT_ADDRESS),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/brand_banner.tpl');
    }

    private function installDefaults()
    {
        return Configuration::updateValue(self::CFG_BRAND_NAME, 'Phyto Commerce')
            && Configuration::updateValue(self::CFG_TAGLINE, 'Rare plant tissue culture, delivered healthy')
            && Configuration::updateValue(self::CFG_PRIMARY, '#1f6f4a')
            && Configuration::updateValue(self::CFG_SECONDARY, '#0f2f23')
            && Configuration::updateValue(self::CFG_ACCENT, '#9bd46b')
            && Configuration::updateValue(self::CFG_LOGO_URL, '')
            && Configuration::updateValue(self::CFG_CONTACT_EMAIL, 'aphytoevolution@gmail.com')
            && Configuration::updateValue(self::CFG_CONTACT_PHONE, '+91 82489 84778')
            && Configuration::updateValue(self::CFG_CONTACT_ADDRESS, 'Phyto Evolution Private Limited, Forest Studio Labs, Chennai - 603103.');
    }

    private function deleteConfigs()
    {
        return Configuration::deleteByName(self::CFG_BRAND_NAME)
            && Configuration::deleteByName(self::CFG_TAGLINE)
            && Configuration::deleteByName(self::CFG_PRIMARY)
            && Configuration::deleteByName(self::CFG_SECONDARY)
            && Configuration::deleteByName(self::CFG_ACCENT)
            && Configuration::deleteByName(self::CFG_LOGO_URL)
            && Configuration::deleteByName(self::CFG_CONTACT_EMAIL)
            && Configuration::deleteByName(self::CFG_CONTACT_PHONE)
            && Configuration::deleteByName(self::CFG_CONTACT_ADDRESS);
    }

    private function renderForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => ['title' => $this->l('Phyto Commerce Branding')],
                'input' => [
                    [
                        'type' => 'text',
                        'name' => self::CFG_BRAND_NAME,
                        'label' => $this->l('Brand name'),
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CFG_TAGLINE,
                        'label' => $this->l('Tagline'),
                    ],
                    [
                        'type' => 'color',
                        'name' => self::CFG_PRIMARY,
                        'label' => $this->l('Primary color'),
                    ],
                    [
                        'type' => 'color',
                        'name' => self::CFG_SECONDARY,
                        'label' => $this->l('Secondary color'),
                    ],
                    [
                        'type' => 'color',
                        'name' => self::CFG_ACCENT,
                        'label' => $this->l('Accent color'),
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CFG_LOGO_URL,
                        'label' => $this->l('Logo URL (optional)'),
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CFG_CONTACT_EMAIL,
                        'label' => $this->l('Contact email'),
                    ],
                    [
                        'type' => 'text',
                        'name' => self::CFG_CONTACT_PHONE,
                        'label' => $this->l('Contact phone'),
                    ],
                    [
                        'type' => 'textarea',
                        'name' => self::CFG_CONTACT_ADDRESS,
                        'label' => $this->l('Address'),
                        'autoload_rte' => false,
                        'rows' => 3,
                        'cols' => 40,
                    ],
                ],
                'submit' => [
                    'name' => 'submitPhytoBranding',
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-primary pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPhytoBranding';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => [
                self::CFG_BRAND_NAME => (string) Configuration::get(self::CFG_BRAND_NAME),
                self::CFG_TAGLINE => (string) Configuration::get(self::CFG_TAGLINE),
                self::CFG_PRIMARY => (string) Configuration::get(self::CFG_PRIMARY),
                self::CFG_SECONDARY => (string) Configuration::get(self::CFG_SECONDARY),
                self::CFG_ACCENT => (string) Configuration::get(self::CFG_ACCENT),
                self::CFG_LOGO_URL => (string) Configuration::get(self::CFG_LOGO_URL),
                self::CFG_CONTACT_EMAIL => (string) Configuration::get(self::CFG_CONTACT_EMAIL),
                self::CFG_CONTACT_PHONE => (string) Configuration::get(self::CFG_CONTACT_PHONE),
                self::CFG_CONTACT_ADDRESS => (string) Configuration::get(self::CFG_CONTACT_ADDRESS),
            ],
        ];

        return $helper->generateForm([$fieldsForm]);
    }
}
