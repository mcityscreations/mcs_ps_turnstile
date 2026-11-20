<?php

class Module
{
    public static $enabled = true;
    public static $instance;

    public static function getInstanceByName($name)
    {
        return self::$instance;
    }

    public static function isEnabled($name)
    {
        if ($name !== 'mcs_turnstile') {
            throw new RuntimeException('Unexpected module name');
        }

        return self::$enabled;
    }
}

class Mcs_Turnstile extends Module
{
    public static $checks = 0;
    public $protected = true;

    public function isFormProtected($form)
    {
        return $this->protected;
    }

    public static function validateTurnstile()
    {
        ++self::$checks;

        return false;
    }

    public function l($message)
    {
        return $message;
    }
}

class Tools
{
    public static function isSubmit($key)
    {
        return isset($_POST[$key]);
    }
}

class TestTranslator
{
    public function trans($message, $parameters = [], $domain = '')
    {
        return $message;
    }
}

class CustomerFormCore
{
    protected $errors = ['' => []];
    protected $translator;
    public $processed = false;

    public function __construct()
    {
        $this->translator = new TestTranslator();
    }

    public function submit()
    {
        $this->processed = true;

        return true;
    }

    public function errors()
    {
        return $this->errors;
    }
}

class CustomerLoginFormCore extends CustomerFormCore
{
}

class CustomerAccountFormCore
{
    public $errors = [];
    public $processed = false;

    public function validate()
    {
        $this->processed = true;

        return true;
    }
}

class PasswordControllerCore
{
    public $errors = [];
    public $processed = false;

    public function postProcess(): void
    {
        $this->processed = true;
    }

    public function setTemplate($template)
    {
    }

    public function trans($message, $parameters, $domain)
    {
        return $message;
    }
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require __DIR__ . '/../override/classes/form/CustomerLoginForm.php';
require __DIR__ . '/../override/classes/form/CustomerForm.php';
require __DIR__ . '/../override/classes/form/CustomerAccountForm.php';
require __DIR__ . '/../override/controllers/front/PasswordController.php';

Module::$instance = new Mcs_Turnstile();
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['submitCreate' => '1', 'email' => 'test@example.invalid'];

foreach ([
    [CustomerLoginForm::class, 'submit'],
    [CustomerForm::class, 'submit'],
    [CustomerAccountForm::class, 'validate'],
    [PasswordController::class, 'postProcess'],
] as $case) {
    [$class, $method] = $case;

    Module::$enabled = true;
    Module::$instance->protected = true;
    $form = new $class();
    $before = Mcs_Turnstile::$checks;
    $form->$method();
    check(!$form->processed && Mcs_Turnstile::$checks === $before + 1, $class . ': protected request must be rejected');
    $errors = method_exists($form, 'errors') ? $form->errors() : $form->errors;
    check(!empty($errors[''] ?? $errors), $class . ': rejection must expose an error');

    Module::$enabled = false;
    $form = new $class();
    $before = Mcs_Turnstile::$checks;
    $form->$method();
    check($form->processed && Mcs_Turnstile::$checks === $before, $class . ': disabled module must not verify');

    Module::$enabled = true;
    Module::$instance->protected = false;
    $form = new $class();
    $before = Mcs_Turnstile::$checks;
    $form->$method();
    check($form->processed && Mcs_Turnstile::$checks === $before, $class . ': unprotected form must not verify');
}

echo "Module enabled regression tests passed\n";
