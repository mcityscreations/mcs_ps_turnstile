<?php
/**
 * Cloudflare Turnstile Integration Module for Prestashop
 *
 * @author MCS - Multicreations Services
 * @version 1.1.0
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
        $this->version = '1.1.0';
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

        if (!$this->registerHooks()) {
            return false;
        }

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
            'actionDispatcher',
            'actionNewsletterRegistrationBefore',
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
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false,
            [],
            ['configure' => $this->name]
        );
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

    public function isFormProtected($formKey)
    {
        if (!$this->isTurnstileEnabled()) {
            return false;
        }

        $formsConfig = $this->getFormsConfig();

        return isset($formsConfig[$formKey]) && (int)$formsConfig[$formKey] === 1;
    }

    public function hookActionNewsletterRegistrationBefore($params)
    {
        if (
            strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || !isset($params['action'])
            || (string)$params['action'] !== '0'
            || !$this->isFormProtected('newsletter')
        ) {
            return;
        }

        if (!self::validateTurnstile()) {
            $params['hookError'] = $this->l('Turnstile verification failed. Please try again.');
        }
    }

    public function hookActionDispatcher($params)
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return;
        }

        $controllerClass = strtolower($params['controller_class'] ?? '');
        if (
            Tools::isSubmit('submitMessage')
            && strpos($controllerClass, 'contactcontroller') !== false
            && $this->isFormProtected('contact')
            && !self::validateTurnstile()
        ) {
            $this->context->controller->errors[] = $this->l('Turnstile verification failed. Please try again.');
        }

        if (
            !empty($params['is_module'])
            && $controllerClass === 'ps_emailalertsactionsmodulefrontcontroller'
            && Tools::getValue('process') === 'add'
            && $this->isFormProtected('back_in_stock')
            && !self::validateTurnstile()
        ) {
            exit(json_encode([
                'error' => true,
                'message' => $this->l('Turnstile verification failed. Please try again.'),
            ]));
        }
    }

    private function isRegistrationPage()
    {
        $controller = $this->context->controller;
        $pageName = isset($controller->php_self) ? $controller->php_self : '';

        return $pageName === 'registration' || (int)Tools::getValue('create_account') === 1;
    }

    public function hookDisplayCustomerAccountFormTop()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        if ($this->isRegistrationPage()) {
            $formsConfig = $this->getFormsConfig();
            if (empty($formsConfig['registration'])) {
                return '';
            }

            return $this->renderRegistrationWidgetFallback();
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['reset_password'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function hookDisplayCustomerAccountForm()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        $formKey = $this->isRegistrationPage() ? 'registration' : 'login';
        if (empty($formsConfig[$formKey])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    private function renderRegistrationWidgetFallback()
    {
        $html = '<div id="mcs-turnstile-registration-fallback">';
        $html .= $this->renderTurnstileWidget();
        $html .= '</div>';
        $html .= '<script>';
        $html .= '(function() {';
        $html .= 'var moveWidget = function() {';
        $html .= 'var fallback = document.getElementById("mcs-turnstile-registration-fallback");';
        $html .= 'var form = document.querySelector("#customer-form, .js-customer-form");';
        $html .= 'if (!fallback || !form) { return; }';
        $html .= 'if (form.querySelector(".cf-turnstile")) { fallback.remove(); return; }';
        $html .= 'var footer = form.querySelector(".form-footer");';
        $html .= 'form.insertBefore(fallback, footer);';
        $html .= '};';
        $html .= 'if (document.readyState === "loading") {';
        $html .= 'document.addEventListener("DOMContentLoaded", moveWidget);';
        $html .= '} else { moveWidget(); }';
        $html .= '})();';
        $html .= '</script>';

        return $html;
    }

    public function hookDisplayLoginFormContent()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['login'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function hookDisplayCustomerRegisterFormTop()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['registration'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function hookDisplayCustomerRegisterForm()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['registration'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function hookDisplayContactForm()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        $formsConfig = $this->getFormsConfig();
        if (empty($formsConfig['contact'])) {
            return '';
        }

        return $this->renderTurnstileWidget();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        if (!$this->isTurnstileEnabled()) {
            return '';
        }

        return $this->getTurnstileScript();
    }

    public function hookDisplayProductExtraContent()
    {
        return [];
    }

    private function renderTurnstileWidget()
    {
        $siteKey = Configuration::get(self::SITE_KEY_CONFIG);

        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="light"></div>';
    }

    private function getTurnstileScript()
    {
        $frontConfig = json_encode(
            [
                'siteKey' => (string)Configuration::get(self::SITE_KEY_CONFIG),
                'forms' => $this->getFormsConfig(),
            ],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );

        return '<script>window.mcsTurnstileConfig = ' . $frontConfig . ';</script>'
            . '<script src="' . $this->_path . 'views/js/front.js"></script>'
            . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    public static function validateTurnstile()
    {
        $token = Tools::getValue('cf-turnstile-response');

        if (!is_string($token) || $token === '' || strlen($token) > 2048) {
            return false;
        }

        $secretKey = Configuration::get(self::SECRET_KEY_CONFIG);
        if (!is_string($secretKey) || $secretKey === '') {
            PrestaShopLogger::addLog('Turnstile: Siteverify secret key is missing.', 3);

            return false;
        }

        return self::verifyToken($token, $secretKey);
    }

    private static function verifyToken($token, $secretKey)
    {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = http_build_query([
            'secret' => $secretKey,
            'response' => $token,
        ]);

        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            if ($handle === false) {
                PrestaShopLogger::addLog('Turnstile: could not initialize Siteverify request.', 3);

                return false;
            }
            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
            ]);
            $response = curl_exec($handle);
            $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            if ($response === false || $status !== 200) {
                PrestaShopLogger::addLog('Turnstile: Siteverify request failed (HTTP ' . (int)$status . ').', 3);

                return false;
            }
        } else {
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $data,
                    'timeout' => 10,
                ],
            ];
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);
        }

        if ($response === false) {
            PrestaShopLogger::addLog('Turnstile: Siteverify request failed; check HTTPS connectivity and PHP configuration.', 3);

            return false;
        }

        $result = json_decode($response, true);
        if (!is_array($result) || !isset($result['success']) || !is_bool($result['success'])) {
            PrestaShopLogger::addLog('Turnstile: Siteverify returned an invalid response.', 3);

            return false;
        }

        $errors = isset($result['error-codes']) && is_array($result['error-codes']) ? $result['error-codes'] : [];
        foreach (['missing-input-secret', 'invalid-input-secret', 'internal-error', 'bad-request'] as $error) {
            if (in_array($error, $errors, true)) {
                PrestaShopLogger::addLog('Turnstile: Siteverify error: ' . $error . '.', 3);
            }
        }

        return $result['success'] === true;
    }
}
