<?php
/**
 * Turnstile Validation Controller
 */

class Mcs_TurnstileValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::postProcess();

        if (
            strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && Tools::isSubmit('validate_turnstile')
        ) {
            $module = Module::getInstanceByName('mcs_turnstile');
            $formKey = Tools::getValue('form');
            $result = $module
                && Module::isEnabled('mcs_turnstile')
                && $module->isFormProtected($formKey)
                && Mcs_Turnstile::validateTurnstile();

            if (is_array($result)) {
                die(json_encode($result));
            } else {
                die(json_encode(['success' => $result]));
            }
        }
    }

    public function display()
    {
        return parent::display();
    }
}
