<?php
/**
 * Turnstile Validation Controller
 */

class Mcs_TurnstileValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('validate_turnstile')) {
            $result = Mcs_Turnstile::validateTurnstile();

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
