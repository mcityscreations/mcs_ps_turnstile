<?php

class PasswordController extends PasswordControllerCore
{
    public function postProcess(): void
    {
        $module = Module::getInstanceByName('mcs_turnstile');
        $isPost = strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        $isPasswordSubmission = Tools::isSubmit('email') || Tools::isSubmit('passwd');

        if (
            $isPost
            && $isPasswordSubmission
            && $module
            && Module::isEnabled('mcs_turnstile')
            && $module->isFormProtected('reset_password')
            && !Mcs_Turnstile::validateTurnstile()
        ) {
            $this->setTemplate('customer/password-email');

            if (Tools::isSubmit('passwd')) {
                $idCustomer = (int)Tools::getValue('id_customer');
                $customerToken = Tools::getValue('token');
                $resetToken = Tools::getValue('reset_token');
                $customerEmail = Db::getInstance()->getValue(
                    'SELECT `email` FROM ' . _DB_PREFIX_ . 'customer c WHERE c.`secure_key` = \''
                    . pSQL($customerToken) . '\' AND c.id_customer = ' . $idCustomer
                );

                $this->context->smarty->assign([
                    'customer_email' => $customerEmail,
                    'customer_token' => $customerToken,
                    'id_customer' => $idCustomer,
                    'reset_token' => $resetToken,
                ]);
                $this->setTemplate('customer/password-new');
            }

            $this->errors[] = $this->trans(
                'Turnstile verification failed. Please try again.',
                [],
                'Shop.Notifications.Error'
            );

            return;
        }

        parent::postProcess();
    }
}
