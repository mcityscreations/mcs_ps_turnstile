<?php
/**
 * Turnstile Helper Class
 * Provides utility methods for Turnstile integration
 */

class TurnstileHelper
{
    private $module;

    public function __construct(Mcs_Turnstile $module)
    {
        $this->module = $module;
    }

    /**
     * Check if Turnstile is enabled
     */
    public function isTurnstileEnabled()
    {
        return $this->module->isTurnstileEnabled();
    }

    /**
     * Check if a specific form has Turnstile protection enabled
     */
    public function isFormProtected($formKey)
    {
        return $this->module->isFormProtected($formKey);
    }

    /**
     * Get the Turnstile widget HTML
     */
    public function getWidgetHtml()
    {
        $siteKey = Configuration::get(Mcs_Turnstile::SITE_KEY_CONFIG);

        if (empty($siteKey)) {
            return '';
        }

        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="light"></div>';
    }

    /**
     * Get the Turnstile script tag
     */
    public function getScriptTag()
    {
        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    /**
     * Render complete Turnstile widget with script
     */
    public function render()
    {
        return $this->getWidgetHtml() . "\n" . $this->getScriptTag();
    }

    /**
     * Validate Turnstile token from POST request
     */
    public function validateToken()
    {
        return Mcs_Turnstile::validateTurnstile();
    }

    /**
     * Get all available forms
     */
    public function getAvailableForms()
    {
        return [
            'newsletter' => $this->module->l('Newsletter Subscription'),
            'login' => $this->module->l('Login'),
            'registration' => $this->module->l('Registration'),
            'reset_password' => $this->module->l('Reset Password'),
            'contact' => $this->module->l('Contact Form'),
            'back_in_stock' => $this->module->l('Back in Stock Alert'),
        ];
    }

    /**
     * Get protected forms list
     */
    public function getProtectedForms()
    {
        $protected = [];
        $formsConfig = $this->module->getFormsConfig();
        $availableForms = $this->getAvailableForms();

        foreach ($availableForms as $key => $name) {
            if (isset($formsConfig[$key]) && $formsConfig[$key]) {
                $protected[$key] = $name;
            }
        }

        return $protected;
    }
}
