<?php
/**
 * Cloudflare Turnstile Integration Module for Prestashop
 *
 * @author MCS - Multicreations Services
 * @version 1.0.0
 * @license Academic Free License 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mcs_Turnstile extends Module
{
    public const SITE_KEY_CONFIG = 'MCS_TURNSTILE_SITE_KEY';
    public const SECRET_KEY_CONFIG = 'MCS_TURNSTILE_SECRET_KEY';
    public const ENABLED_CONFIG = 'MCS_TURNSTILE_ENABLED';
    public const FORMS_CONFIG = 'MCS_TURNSTILE_FORMS';

    public function __construct()
    {
        $this->name = 'mcs_turnstile';
        $this->tab = 'security';
        $this->version = '1.0.0';
        $this->author = 'MCS - Mcitys';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->controllers = ['validation'];
        $this->bootstrap = true;
        $this->module_key = '';

        parent::__construct();

        $this->displayName = $this->l('Cloudflare Turnstile');
        $this->description = $this->l('Integrate Cloudflare Turnstile CAPTCHA into your Prestashop forms');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $this->registerHooks();
        $this->setDefaultConfiguration();

        return true;
    }

    public function uninstall()
    {
        $this->uninstallConfiguration();

        return parent::uninstall();
    }

    private function registerHooks()
    {
        $hooks = [
            'displayCustomerAccountForm',
            'displayCustomerAccountFormTop',
            'displayLoginFormContent',
            'displayBeforeBodyClosingTag',
            'displayCustomerRegisterForm',
            'displayCustomerRegisterFormTop',
            'actionBeforeSubmitAccount',
            'actionPasswordReset',
            'displayContactForm',
            'displayProductExtraContent',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    private function setDefaultConfiguration()
    {
        $defaultForms = [
            'newsletter' => 0,
            'reset_password' => 0,
            'contact' => 0,
            'login' => 0,
            'registration' => 0,
            'back_in_stock' => 0,
        ];

        Configuration::updateValue(self::ENABLED_CONFIG, 0);
        Configuration::updateValue(self::SITE_KEY_CONFIG, '');
        Configuration::updateValue(self::SECRET_KEY_CONFIG, '');
        Configuration::updateValue(self::FORMS_CONFIG, json_encode($defaultForms));
    }

    private function uninstallConfiguration()
    {
        Configuration::deleteByName(self::ENABLED_CONFIG);
        Configuration::deleteByName(self::SITE_KEY_CONFIG);
        Configuration::deleteByName(self::SECRET_KEY_CONFIG);
        Configuration::deleteByName(self::FORMS_CONFIG);
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->processConfiguration();
        }

        $output .= $this->renderConfigurationForm();

        return $output;
    }

    private function processConfiguration()
    {
        $enabled = (int)Tools::getValue(self::ENABLED_CONFIG);
        $siteKey = Tools::getValue(self::SITE_KEY_CONFIG);
        $secretKey = Tools::getValue(self::SECRET_KEY_CONFIG);

        $forms = [];
        foreach ($this->getAvailableForms() as $formKey => $formName) {
            $forms[$formKey] = (int)Tools::getValue('form_' . $formKey);
        }

        if (empty($siteKey) || empty($secretKey)) {
            return $this->displayError($this->l('Site Key and Secret Key are required'));
        }

        Configuration::updateValue(self::ENABLED_CONFIG, $enabled);
        Configuration::updateValue(self::SITE_KEY_CONFIG, $siteKey);
        Configuration::updateValue(self::SECRET_KEY_CONFIG, $secretKey);
        Configuration::updateValue(self::FORMS_CONFIG, json_encode($forms));

        return $this->displayConfirmation($this->l('Settings updated successfully'));
    }

    private function renderConfigurationForm()
    {
        $inputs = [
            [
                'type' => 'switch',
                'label' => $this->l('Enable Turnstile'),
                'name' => self::ENABLED_CONFIG,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Site Key'),
                'name' => self::SITE_KEY_CONFIG,
                'required' => true,
                'hint' => $this->l('Get your Site Key from Cloudflare Dashboard'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Secret Key'),
                'name' => self::SECRET_KEY_CONFIG,
                'required' => true,
                'hint' => $this->l('Get your Secret Key from Cloudflare Dashboard'),
            ],
        ];

        foreach ($this->getAvailableForms() as $formKey => $formName) {
            $inputs[] = [
                'type' => 'switch',
                'label' => $formName,
                'name' => 'form_' . $formKey,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'form_' . $formKey . '_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'form_' . $formKey . '_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
            ];
        }

        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Cloudflare Turnstile Configuration'),
                ],
                'input' => $inputs,
                'tabs' => [
                    'forms' => $this->l('Protected Forms'),
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;

        $helper->fields_value = [
            self::ENABLED_CONFIG => Configuration::get(self::ENABLED_CONFIG),
            self::SITE_KEY_CONFIG => Configuration::get(self::SITE_KEY_CONFIG),
            self::SECRET_KEY_CONFIG => Configuration::get(self::SECRET_KEY_CONFIG),
        ];

        $formsConfig = json_decode(Configuration::get(self::FORMS_CONFIG), true) ?? [];
        foreach ($this->getAvailableForms() as $formKey => $formName) {
            $helper->fields_value['form_' . $formKey] = $formsConfig[$formKey] ?? 0;
        }

        return $helper->generateForm([$fields]);
    }

    private function getAvailableForms()
    {
        return [
            'newsletter' => $this->l('Newsletter Subscription'),
            'login' => $this->l('Login'),
            'registration' => $this->l('Registration'),
            'reset_password' => $this->l('Reset Password'),
            'contact' => $this->l('Contact Form'),
            'back_in_stock' => $this->l('Back in Stock Alert'),
        ];
    }

    public function isTurnstileEnabled()
    {
        return (int)Configuration::get(self::ENABLED_CONFIG) === 1;
    }

    public function getFormsConfig()
    {
        return json_decode(Configuration::get(self::FORMS_CONFIG), true) ?? [];
    }

    public function displayCustomerAccountFormTop()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['reset_password'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function displayCustomerAccountForm()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['login'])) {
            return '';
        }

        return $this->getTurnstileScript();
    }

    public function displayLoginFormContent()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['login'])) {
            return '';
        }

        return $this->renderTurnstileWidget() . $this->getTurnstileScript();
    }

    public function displayCustomerRegisterFormTop()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['registration'])) {
            return '';
        }

        return $this->renderTurnstileWidget() . $this->getTurnstileScript();
    }

    public function displayContactForm()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['contact'])) {
            return '';
        }

        return $this->renderTurnstileWidget() . $this->getTurnstileScript();
    }

    public function displayBeforeBodyClosingTag()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        return $this->getTurnstileScript();
    }

    public function displayProductExtraContent()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['back_in_stock'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    private function renderTurnstileWidget()
    {
        $siteKey = Configuration::get(self::SITE_KEY_CONFIG);

        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="light"></div>';
    }

    private function getTurnstileScript()
    {
        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    public static function validateTurnstile()
    {
        $token = Tools::getValue('cf-turnstile-response');

        if (empty($token)) {
            return false;
        }

        $secretKey = Configuration::get(self::SECRET_KEY_CONFIG);
        if (empty($secretKey)) {
            return false;
        }

        return self::verifyToken($token, $secretKey);
    }

    private static function verifyToken($token, $secretKey)
    {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return false;
        }

        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] === true;
    }
}
