<?php

class CustomerLoginForm extends CustomerLoginFormCore
{
    public function submit()
    {
        $module = Module::getInstanceByName('mcs_turnstile');
        if (
            $module
            && Module::isEnabled('mcs_turnstile')
            && $module->isFormProtected('login')
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
