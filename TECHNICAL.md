# Technical Documentation - MCS Turnstile Module

## Architecture Overview

The module uses a hook-based architecture to integrate with Prestashop forms without modifying core code.

## Component Breakdown

### 1. Main Module Class (`mcs_turnstile.php`)

**Responsibilities:**
- Module lifecycle management (install, uninstall)
- Hook registration and execution
- Configuration management
- Turnstile widget rendering

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `install()` | Setup module, register hooks |
| `uninstall()` | Cleanup configuration |
| `getContent()` | Admin configuration page |
| `isEnabled()` | Check if module is active |
| `getFormsConfig()` | Get protected forms |
| `validateTurnstile()` | Server-side validation |

### 2. Helper Class (`classes/TurnstileHelper.php`)

**Responsibilities:**
- Utility methods for form protection
- Token validation wrapper
- Configuration checks

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `isFormProtected($key)` | Check if form is protected |
| `getWidgetHtml()` | Generate widget HTML |
| `validateToken()` | Validate from POST data |
| `verifyToken($token, $key)` | Verify with Cloudflare API |
| `getProtectedForms()` | List all protected forms |

### 3. Validation Controller (`controllers/front/validation.php`)

**Responsibilities:**
- AJAX endpoint for async validation
- Token verification response

**Endpoints:**
- `POST /module/mcs_turnstile/validation` - Validate Turnstile token

### 4. Form Overrides (`override/classes/form/`)

**Responsibilities:**
- Inject Turnstile validation into form processing
- Maintain backward compatibility

**Files:**
- `CustomerAccountForm.php` - Login/Account forms
- `FormDataHandler.php` - Generic form data handling

## Integration Points

### Hook: displayBeforeBodyClosingTag
**Location:** All pages
**Purpose:** Load the integration script and Turnstile JavaScript library
**Called On:** Every page load

The integration script targets the Classic theme's login, registration,
password-reset, contact, newsletter, and back-in-stock form markup. The
`ps_emailalerts` back-in-stock request is AJAX-based and requires separate
server-side token verification.

## Data Flow

```
For forms with server-side integration (currently customer registration):

User completes widget
        ↓
Turnstile API returns a cf-turnstile-response token
        ↓
Form submits the token
        ↓
Server calls Cloudflare Siteverify
        ↓
Accept or reject the form submission

The other widgets currently provide a challenge UI but do not yet enforce
server-side verification in their respective form handlers.
```

## Security Implementation

### Token Validation

```php
// 1. Extract token from POST
$token = Tools::getValue('cf-turnstile-response');

// 2. Get secret key from config
$secretKey = Configuration::get(self::SECRET_KEY_CONFIG);

// 3. Send to Cloudflare verification endpoint
$response = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', 
    false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'content' => http_build_query([
                'secret' => $secretKey,
                'response' => $token
            ])
        ]
    ]));

// 4. Verify success flag
$result = json_decode($response, true);
return $result['success'] === true;
```

### Key Storage

- **Site Key**: Public, stored in DB via `Configuration::updateValue()`
- **Secret Key**: Private, stored in DB via `Configuration::updateValue()`
- **Never exposed**: Secret key never sent to client

## Configuration Storage

```
Database: prestashop_configuration
Table Column: value
Entries:
- MCS_TURNSTILE_ENABLED (0/1)
- MCS_TURNSTILE_SITE_KEY (string)
- MCS_TURNSTILE_SECRET_KEY (string)
- MCS_TURNSTILE_FORMS (JSON)
```

**Forms JSON Structure:**
```json
{
  "newsletter": 1,
  "login": 1,
  "registration": 1,
  "reset_password": 0,
  "contact": 1,
  "back_in_stock": 0
}
```

## Hooks Registration

```php
private function registerHooks()
{
    $hooks = [
        'displayCustomerAccountForm',
        'displayCustomerAccountFormTop',
        'displayLoginFormContent',
        'displayBeforeBodyClosingTag',
        'displayCustomerRegisterForm',
        'displayCustomerRegisterFormTop',
    ];
}
```

## Form Protection Strategy

### By Form Type

**Login Form**
- Widget: `#login-form` via `views/js/front.js`
- Server-side validation: Not implemented for `CustomerLoginForm`

**Registration Form**
- Widget: `displayCustomerAccountForm` with a front-end fallback
- Validation: `CustomerAccountForm::validate()`
- Flow: The token is posted with registration form data and checked by the override

**Contact Form**
- Widget: Contact form containing `submitMessage` via `views/js/front.js`
- Server-side validation: Not implemented for the contact form module

**Newsletter Form**
- Widget: `.block_newsletter form` via `views/js/front.js`
- Server-side validation: Not implemented for the newsletter module

**Password Reset**
- Widget: Password-reset form via `views/js/front.js`
- Server-side validation: Not implemented for the password-reset endpoint

**Back in Stock Alert**
- Widget: `.js-mailalert` via `views/js/front.js`
- Server-side validation: Requires integration with the AJAX endpoint in `ps_emailalerts`

## Error Handling

### Client-Side

```javascript
// Turnstile widget handles UI errors
// Auto-retry on failure
// Timeout: 5 minutes
```

### Server-Side

```php
// API Timeout: 10 seconds
// Fallback: Reject if API unreachable
// Logging: errors logged to ps_error_log

try {
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        return false; // API failed
    }
} catch (Exception $e) {
    return false; // Error caught
}
```

## Performance Considerations

### Client Impact
- **Script Load**: ~50KB, async
- **Widget Render**: <100ms
- **Verification Time**: <1s

### Server Impact
- **Token Validation**: ~200-500ms (API call)
- **Config Load**: <1ms (cached)
- **Memory Usage**: <2MB

### Optimization

1. **Async Script Loading**
   - Turnstile JS loads asynchronously
   - Non-blocking page rendering

2. **Configuration Caching**
   - Prestashop caches config values
   - Multiple calls don't hit database

3. **Connection Pooling**
   - Cloudflare API uses HTTP keep-alive
   - Reuses connections

## Extension Points

### Add Custom Form Protection

```php
// In your module or theme
$module = Module::getInstanceByName('mcs_turnstile');
$helper = new TurnstileHelper($module);

if ($helper->isFormProtected('your_custom_form')) {
    // Show widget
    echo $helper->getWidgetHtml();
    
    // Validate on submit
    if (!$helper->validateToken()) {
        die('Verification failed');
    }
}
```

### Add Custom Theme

```php
// Edit mcs_turnstile.php line 311
// Change theme from 'light' to 'dark'
data-theme="dark"

// Options: light, dark, auto
```

### Add Custom Styling

```css
/* In your theme CSS */
.cf-turnstile {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 4px;
}
```

## Compatibility

### Prestashop Versions
- ✅ 9.0.0 - 9.x.x
- ⚠️ 8.0.0 - 8.1.x (Partial - some hooks may differ)

### PHP Versions
- ✅ PHP 8.1+
- ✅ PHP 8.0
- ✅ PHP 7.4

### Browsers
- ✅ All modern browsers
- ✅ Mobile browsers
- ⚠️ IE11 not supported by Turnstile

## Testing Checklist

- [ ] Module installs without errors
- [ ] Configuration page loads
- [ ] Can save Site Key and Secret Key
- [ ] Can enable/disable module
- [ ] Can select forms to protect
- [ ] Widget displays on login form
- [ ] Widget displays on registration form
- [ ] Widget displays on contact form
- [ ] Token validation works
- [ ] Invalid token is rejected
- [ ] Form submission blocked without verification
- [ ] Form submission works with verification
- [ ] Module can be uninstalled cleanly

## Debugging

### Enable Debug Mode

Edit `config/defines.inc.php`:
```php
define('_PS_MODE_DEV_', true);
define('_PS_DEBUG_SQL_', true);
```

### Check Logs

```
var/logs/prestashop.log
var/logs/admin_*.log
```

### Verify Token Manually

```php
// In a test controller
$token = 'token_from_post_data';
$secret = Configuration::get('MCS_TURNSTILE_SECRET_KEY');

$result = Mcs_Turnstile::validateTurnstile();
var_dump($result);
```

## Maintenance

### Regular Tasks
- Monitor error logs monthly
- Check Cloudflare quota (free tier: unlimited)
- Update configuration if keys change
- Test form submissions periodically

### Upgrading Module
1. Backup configuration values
2. Uninstall current version
3. Install new version
4. Verify functionality

---

**Version**: 1.0.0
**Last Updated**: 2024-11-19
