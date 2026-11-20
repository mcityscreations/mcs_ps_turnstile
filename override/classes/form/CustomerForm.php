<?php

class CustomerForm extends CustomerFormCore
{
    public function submit()
    {
        if (
            strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || !Tools::isSubmit('submitCreate')
        ) {
            return parent::submit();
        }

        $module = Module::getInstanceByName('mcs_turnstile');
        if (
            $module
            && Module::isEnabled('mcs_turnstile')
            && $module->isFormProtected('registration')
            && !Mcs_Turnstile::validateTurnstile()
        ) {
            $this->errors[''][] = $this->translator->trans(
                'Turnstile verification failed. Please try again.',
                [],
                'Shop.Notifications.Error'
            );

            return false;
        }

        return parent::submit();
    }
}
