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
| `isFormProtected($key)` | Check global and per-form settings |
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
- `POST /module/mcs_turnstile/validation` - Validate a token for a protected form

### 4. Form Overrides (`override/classes/form/`)

**Responsibilities:**
- Inject Turnstile validation into form processing
- Maintain backward compatibility

**Files:**
- `CustomerForm.php` - Registration, before customer persistence
- `CustomerLoginForm.php` - Login, before authentication
- `PasswordController.php` - Password reset, before email or password changes
- `CustomerAccountForm.php` - Legacy compatibility, when that core class exists
- `FormDataHandler.php` - No generic token validation; unrelated forms remain unaffected

## Integration Points

### Hook: displayBeforeBodyClosingTag
**Location:** All pages
**Purpose:** Load the integration script and Turnstile JavaScript library
**Called On:** Every page load

The integration script targets the Classic theme's login, registration,
password-reset, contact, newsletter, and back-in-stock form markup. The
`ps_emailalerts` back-in-stock request is AJAX-based; the script forwards its
token and the dispatcher hook verifies it before the module processes the request.

## Data Flow

```
For each form selected in configuration:

User completes widget
        ↓
Turnstile API returns a cf-turnstile-response token
        ↓
The form submits the token
        ↓
Server calls Cloudflare Siteverify
        ↓
Accept the submission or return a form-specific error before processing
```

## Security Implementation

### Token Validation

`Mcs_Turnstile::validateTurnstile()` sends the submitted token and configured
secret to Cloudflare Siteverify. It uses PHP cURL when available, otherwise
HTTPS streams, with a 10-second timeout. Missing or invalid tokens, failed
requests, malformed responses, and responses without `success: true` are
rejected. Configuration and transport errors are recorded in PrestaShop logs
without logging keys or tokens. The helper uses the same validation path.

The standalone validation endpoint consumes the token. Do not call it before
submitting a protected form: Cloudflare tokens are single-use.

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
        'actionDispatcher',
        'actionNewsletterRegistrationBefore',
    ];
}
```

## Form Protection Strategy

### By Form Type

**Login Form**
- Widget: `#login-form` via `views/js/front.js`
- Server-side validation: `CustomerLoginForm::submit()` runs before authentication

**Registration Form**
- Widget: `displayCustomerAccountForm` with a front-end fallback
- Server-side validation: `CustomerForm::submit()` runs before customer persistence

**Contact Form**
- Widget: Contact form containing `submitMessage` via `views/js/front.js`
- Server-side validation: `actionDispatcher` adds a controller error before the contact module sends

**Newsletter Form**
- Widget: `.block_newsletter form` via `views/js/front.js`
- Server-side validation: `actionNewsletterRegistrationBefore` rejects subscriptions; unsubscriptions are unaffected

**Password Reset**
- Widget: Password-reset form via `views/js/front.js`
- Server-side validation: `PasswordController::postProcess()` runs before reset email or password update

**Back in Stock Alert**
- Widget: `.js-mailalert` via `views/js/front.js`
- Server-side validation: `actionDispatcher` verifies the AJAX request before `ps_emailalerts::processAdd()`

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

**Version**: 1.1.0
**Last Updated**: 2024-11-19
