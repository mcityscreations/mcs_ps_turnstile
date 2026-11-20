<?php

if (class_exists('CustomerAccountFormCore')) {
    class CustomerAccountForm extends CustomerAccountFormCore
    {
        public function validate()
        {
            $module = Module::getInstanceByName('mcs_turnstile');
            if (
                $module
                && Module::isEnabled('mcs_turnstile')
                && $module->isFormProtected('registration')
                && !Mcs_Turnstile::validateTurnstile()
            ) {
                $this->errors[] = $module->l('Turnstile verification failed. Please try again.');

                return false;
            }

            return parent::validate();
        }
    }
}
