<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    if (!$module->registerHook('actionDispatcher')) {
        return false;
    }

    if (!$module->registerHook('actionNewsletterRegistrationBefore')) {
        return false;
    }

    return $module->installOverrides();
}
