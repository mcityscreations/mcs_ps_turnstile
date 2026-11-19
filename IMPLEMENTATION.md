# Implementation Guide - MCS Turnstile Module

## Quick Start (5 Minutes)

### Step 1: Get Cloudflare Turnstile Keys

1. Go to [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Sign in or create account (free)
3. Navigate to **AI & Security > Turnstile**
4. Click **Create Site**
5. Enter your domain (e.g., `mystore.com`)
6. Select **Managed** mode
7. Click **Create**
8. Copy **Site Key** and **Secret Key**

### Step 2: Install Module

1. Download `mcs_turnstile.zip` or clone repository
2. Upload to Prestashop `/modules/` directory
3. In Admin: **Modules > Module Manager**
4. Search for "Turnstile"
5. Click **Install**

### Step 3: Configure

1. Click **Configure** on Cloudflare Turnstile module
2. Enable module: Toggle **Enable Turnstile** to YES
3. Paste **Site Key**
4. Paste **Secret Key**
5. Check forms to protect
6. Click **Save**

**Done!** Forms are now protected.

---

## Protected Forms Detail

### 1. Login Form

**When Protected:** Customer login page requires Turnstile verification

**URL:** `/customer/login` or `/my-account`

**Widget Placement:** Above login button

**Integration:** Automatic via hook `displayLoginFormContent`

**Test:**
1. Go to login page
2. See Turnstile widget
3. Try login without verification → Should fail
4. Complete verification → Can login

### 2. Registration Form

**When Protected:** New customer registration requires verification

**URL:** `/customer/register` or `/register`

**Widget Placement:** At top of registration form

**Integration:** Automatic via hook `displayCustomerRegisterFormTop`

**Test:**
1. Go to registration page
2. Fill form details
3. Try submit without Turnstile → Should fail
4. See Turnstile widget
5. Complete verification → Can register

### 3. Reset Password Form

**When Protected:** Password reset requires verification

**URL:** `/customer/password-reset`

**Widget Placement:** At top of password reset form

**Integration:** Automatic via hook `displayCustomerAccountFormTop`

**Test:**
1. Go to login → Click "Forgot Password"
2. See Turnstile widget
3. Complete verification
4. Submit → Should work

### 4. Contact Form

**When Protected:** Contact form requires verification

**URL:** `/contact-us` or `/contact`

**Widget Placement:** In contact form

**Integration:** Automatic via hook `displayContactForm`

**Test:**
1. Go to Contact page
2. Fill contact form
3. See Turnstile widget
4. Complete verification
5. Submit → Email sent

### 5. Newsletter Subscription

**When Protected:** Newsletter signup requires verification

**Widget Placement:** Newsletter form (usually footer)

**URL:** Varies (usually footer or dedicated page)

**Integration:** Via JavaScript hook `displayBeforeBodyClosingTag`

**Test:**
1. Go to footer or newsletter page
2. See Turnstile widget
3. Complete verification
4. Subscribe → Email confirmed

### 6. Back in Stock Alert

**When Protected:** Out of stock products show alert form with Turnstile

**Widget Placement:** Product page, below stock status

**URL:** Product pages for out of stock items

**Integration:** Automatic via hook `displayProductExtraContent`

**Test:**
1. Find out-of-stock product
2. See "Notify me" button
3. Click → See Turnstile widget
4. Complete verification
5. Submit → Alert registered

---

## Configuration Examples

### Protect Only Critical Forms

**Recommended for most stores:**

```
✓ Login
✓ Registration
✗ Reset Password
✗ Contact Form
✗ Newsletter
✗ Back in Stock
```

**Benefit:** Protects account takeover, bot registrations

### Full Protection

**Recommended for high-spam environments:**

```
✓ Login
✓ Registration
✓ Reset Password
✓ Contact Form
✓ Newsletter
✓ Back in Stock
```

**Benefit:** Maximum bot prevention

### Moderate Protection

**Balanced approach:**

```
✓ Login
✓ Registration
✓ Contact Form
✗ Reset Password
✗ Newsletter
✗ Back in Stock
```

**Benefit:** Protects main vectors, doesn't annoy users

---

## Customization

### Change Widget Theme

Edit `mcs_turnstile.php`, find line with `data-theme`:

```php
// Change this:
return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="light"></div>';

// To dark theme:
return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="dark"></div>';

// Or auto (follows system preference):
return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '" data-theme="auto"></div>';
```

### Custom Form Protection

If you have a custom form in a custom module:

**In your module's form handler:**

```php
<?php
// At top of your form validation
$turnstile = Module::getInstanceByName('mcs_turnstile');
if ($turnstile && $turnstile->isEnabled()) {
    // Add Turnstile widget to form
    $turnstileHelper = new TurnstileHelper($turnstile);
    echo $turnstileHelper->getWidgetHtml();
    
    // Validate on submission
    if (Tools::isSubmit('submitForm')) {
        if (!$turnstileHelper->validateToken()) {
            $this->errors[] = 'Please verify that you are human';
            return false;
        }
    }
}
?>
```

### Styling Widget

Add to your theme's CSS file:

```css
/* Center and style Turnstile widget */
.cf-turnstile {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

/* Add border and padding */
.mcs-turnstile-widget {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 4px;
    background-color: #f9f9f9;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .cf-turnstile {
        transform: scale(0.9);
        transform-origin: 0 0;
    }
}
```

### Add to Custom Forms

**Method 1: Using Hook**

Add to your custom module or theme:

```php
public function hookDisplayMyCustomForm()
{
    $turnstile = Module::getInstanceByName('mcs_turnstile');
    if ($turnstile && $turnstile->isEnabled()) {
        return $turnstile->renderTurnstileWidget();
    }
    return '';
}
```

**Method 2: Direct Integration**

In your form template:

```smarty
{if Module::isInstalled('mcs_turnstile') && Module::isEnabled('mcs_turnstile')}
    <div class="cf-turnstile" data-sitekey="{Configuration::get('MCS_TURNSTILE_SITE_KEY')|escape:'html'}" data-theme="light"></div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
{/if}
```

---

## Best Practices

### For Store Owners

✅ **DO:**
- Protect login and registration (high bot activity)
- Use light theme for better UX
- Monitor error logs for validation issues
- Keep secret key confidential
- Test on mobile devices

❌ **DON'T:**
- Protect every single form (annoying to users)
- Share secret key in public
- Change keys frequently
- Use in development without updating Site Key

### For Developers

✅ **DO:**
- Validate token on server-side always
- Use HTTPS for API calls
- Cache configuration values
- Log validation failures
- Test with invalid tokens

❌ **DON'T:**
- Trust client-side validation only
- Hardcode API keys
- Skip error handling
- Expose secret key in JS code
- Call Cloudflare API on every page load

### Security Hardening

```php
// Always validate on server
if (Tools::isSubmit('submitForm')) {
    // Client-side widget is first layer
    // Server-side validation is essential
    if (!Mcs_Turnstile::validateTurnstile()) {
        // Log failed attempt
        PrestaShopLogger::addLog('Turnstile validation failed', 3);
        $this->errors[] = 'Verification failed';
        return false;
    }
}
```

---

## Testing Checklist

### Installation Test
- [ ] Module shows in Module Manager
- [ ] Can navigate to configuration page
- [ ] Configuration page loads without errors
- [ ] Can save settings

### Configuration Test
- [ ] Site Key field accepts input
- [ ] Secret Key field accepts input
- [ ] Can enable/disable module toggle
- [ ] Can select/deselect forms
- [ ] Save button works

### Functionality Test
- [ ] Login page shows Turnstile widget
- [ ] Registration page shows Turnstile widget
- [ ] Contact form shows Turnstile widget
- [ ] Widget appears correctly on mobile
- [ ] Widget loads in dark mode
- [ ] Widget loads in light mode

### Validation Test
- [ ] Form rejects without verification
- [ ] Form accepts with verification
- [ ] Invalid token is rejected
- [ ] Expired token is rejected
- [ ] Error messages display correctly
- [ ] No validation if module disabled

### Compatibility Test
- [ ] Works with Firefox
- [ ] Works with Chrome
- [ ] Works with Safari
- [ ] Works with Edge
- [ ] Works on mobile devices
- [ ] Works with screen readers
- [ ] No console JavaScript errors

---

## Troubleshooting Guide

### Module Not Showing

**Problem:** Module doesn't appear in Module Manager

**Solutions:**
1. Check file permissions (must be 644)
2. Clear Prestashop cache
3. Clear browser cache
4. Check PHP error logs
5. Verify folder name is `mcs_turnstile` (lowercase)

```bash
# Check permissions
ls -la /path/to/modules/mcs_turnstile/mcs_turnstile.php

# Should show: -rw-r--r--
```

### Widget Not Displaying

**Problem:** Turnstile widget doesn't show on forms

**Solutions:**
1. Verify module is enabled
2. Check form is selected in configuration
3. Clear template cache
4. Check browser console for JS errors
5. Verify Site Key is correct

```php
// Check in browser console
console.log(window.turnstile); // Should show Cloudflare API
```

### Verification Fails

**Problem:** "Turnstile verification failed" error appears

**Solutions:**
1. Verify Site Key matches Cloudflare dashboard
2. Verify Secret Key is exactly correct
3. Check server can reach Cloudflare (firewall rules)
4. Verify token is being sent
5. Check Turnstile script loaded

```bash
# Test server connectivity to Cloudflare
curl -v https://challenges.cloudflare.com/turnstile/v0/api.js
```

### Forms Keep Failing

**Problem:** Forms fail even after completing Turnstile

**Solutions:**
1. Check form submission method (POST required)
2. Verify hidden token field is present
3. Check PHP session is working
4. Test with Prestashop debug mode enabled
5. Check browser developer console

```php
// Enable debug mode
define('_PS_MODE_DEV_', true);
```

### Module Won't Uninstall

**Problem:** Uninstall fails or database errors

**Solutions:**
1. Clear override files manually
2. Delete folder via FTP
3. Clear database via phpMyAdmin
4. Reinstall clean

```sql
-- If needed to cleanup database
DELETE FROM ps_configuration WHERE name LIKE 'MCS_TURNSTILE%';
```

---

## Performance Optimization

### Lazy Load Turnstile Script

For pages with many forms, load script only when needed:

```php
// In your module hook
public function displayBeforeBodyClosingTag()
{
    // Only load script if needed
    if ($this->isTurnstileNeeded()) {
        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>';
    }
    return '';
}

private function isTurnstileNeeded()
{
    $controller = Tools::getValue('controller');
    $formsConfig = $this->getFormsConfig();
    
    $formMap = [
        'authentication' => ['login', 'registration'],
        'contact' => ['contact'],
        'password' => ['reset_password'],
    ];
    
    foreach ($formMap as $ctrl => $forms) {
        if ($controller === $ctrl) {
            foreach ($forms as $form) {
                if ($formsConfig[$form] ?? 0) {
                    return true;
                }
            }
        }
    }
    
    return false;
}
```

### Cache Token Validation

For high-traffic sites, consider caching validation results:

```php
// Add to TurnstileHelper class
private function getCachedValidation($token)
{
    $cacheKey = 'turnstile_' . md5($token);
    $cached = apc_fetch($cacheKey);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $result = $this->verifyToken($token, Configuration::get(Mcs_Turnstile::SECRET_KEY_CONFIG));
    
    // Cache for 1 hour
    apc_store($cacheKey, $result, 3600);
    
    return $result;
}
```

---

## Frequently Asked Questions

**Q: Is Turnstile free?**
A: Yes! Cloudflare Turnstile is free for unlimited sites with no CAPTCHA limit.

**Q: Can I use Turnstile without Cloudflare account?**
A: No, you need a Cloudflare account to get Site Key and Secret Key.

**Q: Does Turnstile affect SEO?**
A: No, Turnstile tokens are not indexed and don't impact SEO.

**Q: Can I remove Turnstile from specific pages?**
A: Yes, uncheck form in module configuration.

**Q: What if Cloudflare API is down?**
A: Module gracefully fails and rejects forms until API is back.

**Q: Can customers skip Turnstile?**
A: No, it's required for form submission if enabled.

**Q: Does module work with Prestashop 8?**
A: Partially - some hooks may differ, but core functionality works.

**Q: Can I customize widget appearance?**
A: Limited - Cloudflare controls widget styling. You can customize container.

**Q: Does module work with multi-shop?**
A: Yes - each shop can have different configuration.

**Q: Can I protect custom forms?**
A: Yes - see Customization section above.

---

## Support Resources

- **GitHub Issues:** https://github.com/mcityscreations/mcs_ps_turnstile/issues
- **Cloudflare Docs:** https://developers.cloudflare.com/turnstile/
- **Prestashop Docs:** https://devdocs.prestashop.com/

---

**Version:** 1.0.0
**Last Updated:** 2024-11-19
