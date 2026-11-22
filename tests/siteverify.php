<?php

if (extension_loaded('curl')) {
    throw new RuntimeException('Run this test with php -n so the HTTP transport can be simulated.');
}

$transport = $argv[1] ?? '';
if (!in_array($transport, ['curl', 'stream'], true)) {
    throw new RuntimeException('Specify curl or stream.');
}

class SiteverifyTestTransport
{
    public static $response = '{"success":true}';
    public static $status = 200;
    public static $requests = [];
}

if ($transport === 'curl') {
    foreach (['CURLOPT_POST', 'CURLOPT_POSTFIELDS', 'CURLOPT_HTTPHEADER', 'CURLOPT_RETURNTRANSFER', 'CURLOPT_CONNECTTIMEOUT', 'CURLOPT_TIMEOUT', 'CURLINFO_HTTP_CODE'] as $index => $constant) {
        define($constant, $index + 1);
    }

    function curl_init($url)
    {
        SiteverifyTestTransport::$requests[] = ['url' => $url];

        return new stdClass();
    }

    function curl_setopt_array($handle, $options)
    {
        SiteverifyTestTransport::$requests[count(SiteverifyTestTransport::$requests) - 1]['options'] = $options;

        return true;
    }

    function curl_exec($handle)
    {
        return SiteverifyTestTransport::$response;
    }

    function curl_getinfo($handle, $option)
    {
        return SiteverifyTestTransport::$status;
    }

    function curl_close($handle)
    {
    }
} else {
    class SiteverifyTestStream
    {
        public $context;
        private $position = 0;

        public function stream_open($url, $mode, $options, &$openedPath)
        {
            SiteverifyTestTransport::$requests[] = [
                'url' => $url,
                'options' => stream_context_get_options($this->context)['http'],
            ];

            return SiteverifyTestTransport::$response !== false;
        }

        public function stream_read($count)
        {
            $part = substr(SiteverifyTestTransport::$response, $this->position, $count);
            $this->position += strlen($part);

            return $part;
        }

        public function stream_eof()
        {
            return $this->position >= strlen(SiteverifyTestTransport::$response);
        }

        public function stream_stat()
        {
            return [];
        }
    }

    if (in_array('https', stream_get_wrappers(), true)) {
        checkWrapper(stream_wrapper_unregister('https'));
    }
    checkWrapper(stream_wrapper_register('https', SiteverifyTestStream::class));
}

function checkWrapper($success)
{
    if (!$success) {
        throw new RuntimeException('Failed to install the test HTTPS wrapper.');
    }
}

define('_PS_VERSION_', '9.0.0');

class Module
{
    public function l($message)
    {
        return $message;
    }
}

class Configuration
{
    public static $values = [
        'MCS_TURNSTILE_ENABLED' => 1,
        'MCS_TURNSTILE_SECRET_KEY' => 'test-secret',
        'MCS_TURNSTILE_FORMS' => '{"newsletter":1}',
    ];

    public static function get($key)
    {
        return self::$values[$key] ?? null;
    }
}

class Tools
{
    public static $values = [];

    public static function getValue($key)
    {
        return self::$values[$key] ?? null;
    }
}

class PrestaShopLogger
{
    public static $messages = [];

    public static function addLog($message, $severity)
    {
        self::$messages[] = $message;
    }
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require __DIR__ . '/../mcs_turnstile.php';
require __DIR__ . '/../classes/TurnstileHelper.php';

Tools::$values['cf-turnstile-response'] = 'test+token';
check(Mcs_Turnstile::validateTurnstile(), 'A successful Siteverify response must be accepted.');
$request = SiteverifyTestTransport::$requests[0];
check($request['url'] === 'https://challenges.cloudflare.com/turnstile/v0/siteverify', 'Wrong Siteverify URL.');
$options = $request['options'];
if ($transport === 'curl') {
    check($options[CURLOPT_POST] === true && $options[CURLOPT_TIMEOUT] === 10, 'Invalid cURL request settings.');
    $body = $options[CURLOPT_POSTFIELDS];
} else {
    check($options['method'] === 'POST' && $options['timeout'] === 10, 'Invalid stream request settings.');
    $body = $options['content'];
}
parse_str($body, $fields);
check($fields === ['secret' => 'test-secret', 'response' => 'test+token'], 'Incorrect Siteverify request body.');

$module = (new ReflectionClass(Mcs_Turnstile::class))->newInstanceWithoutConstructor();
$helper = new TurnstileHelper($module);
check($helper->validateToken(), 'The helper must use the same validation.');

SiteverifyTestTransport::$response = '{"success":false,"error-codes":["invalid-input-secret"]}';
check(!Mcs_Turnstile::validateTurnstile(), 'Cloudflare rejection must reject the token.');
check(strpos(end(PrestaShopLogger::$messages), 'invalid-input-secret') !== false, 'Configuration errors must be logged.');

SiteverifyTestTransport::$response = 'not-json';
check(!Mcs_Turnstile::validateTurnstile(), 'Malformed responses must be rejected.');
check(strpos(end(PrestaShopLogger::$messages), 'invalid response') !== false, 'Malformed responses must be logged.');

SiteverifyTestTransport::$response = false;
check(!Mcs_Turnstile::validateTurnstile(), 'Network failure must be rejected.');
check(strpos(end(PrestaShopLogger::$messages), 'request failed') !== false, 'Network failures must be logged.');

if ($transport === 'curl') {
    SiteverifyTestTransport::$response = '{"success":true}';
    SiteverifyTestTransport::$status = 500;
    check(!Mcs_Turnstile::validateTurnstile(), 'HTTP failures must be rejected.');
    SiteverifyTestTransport::$status = 200;
}

$before = count(SiteverifyTestTransport::$requests);
foreach (['', str_repeat('a', 2049), ['unexpected']] as $token) {
    Tools::$values['cf-turnstile-response'] = $token;
    check(!Mcs_Turnstile::validateTurnstile(), 'Invalid token must be rejected.');
}
check(count(SiteverifyTestTransport::$requests) === $before, 'Invalid token must not trigger a request.');

Tools::$values['cf-turnstile-response'] = 'test+token';
Configuration::$values['MCS_TURNSTILE_SECRET_KEY'] = '';
check(!Mcs_Turnstile::validateTurnstile(), 'Missing secret must be rejected.');
check(strpos(end(PrestaShopLogger::$messages), 'secret key is missing') !== false, 'Missing secret must be logged.');
Configuration::$values['MCS_TURNSTILE_SECRET_KEY'] = 'test-secret';

$_SERVER['REQUEST_METHOD'] = 'POST';
SiteverifyTestTransport::$response = '{"success":true}';
$hookError = null;
$params = ['action' => '0', 'hookError' => &$hookError];
$before = count(SiteverifyTestTransport::$requests);
$module->hookActionNewsletterRegistrationBefore($params);
check(count(SiteverifyTestTransport::$requests) === $before + 1 && $hookError === null, 'AJAX newsletter subscription must be verified.');

SiteverifyTestTransport::$response = '{"success":false}';
$module->hookActionNewsletterRegistrationBefore($params);
check($hookError !== null, 'An invalid AJAX newsletter subscription must be rejected.');

$hookError = null;
Tools::$values['cf-turnstile-response'] = '';
$module->hookActionNewsletterRegistrationBefore($params);
check($hookError !== null, 'A newsletter subscription without a token must be rejected.');

$params['action'] = '1';
$module->hookActionNewsletterRegistrationBefore($params);
check(count(SiteverifyTestTransport::$requests) === $before + 2, 'Unsubscription must not be verified.');

foreach (PrestaShopLogger::$messages as $message) {
    check(strpos($message, 'test-secret') === false && strpos($message, 'test+token') === false, 'Logs must not expose secrets or tokens.');
}

echo "Siteverify $transport tests passed\n";
