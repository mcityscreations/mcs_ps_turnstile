# Cloudflare Turnstile Prestashop Module

A production-ready Prestashop 9 module that integrates Cloudflare Turnstile CAPTCHA into your store forms for enhanced security without compromising user experience.

## Features

✅ **Easy Integration** - Works seamlessly with Prestashop 9
✅ **Multiple Form Support** - Protect key forms:
  - Newsletter Subscription
  - Login
  - Registration
  - Reset Password
  - Contact Form
  - Back in Stock Alert

✅ **Flexible Configuration** - Enable/disable Turnstile per form
✅ **Non-Intrusive Design** - Doesn't interfere with core Prestashop
✅ **Server-Side Validation** - Secure token verification with Cloudflare API
✅ **User-Friendly** - Modern, accessible CAPTCHA experience
✅ **Admin Dashboard** - Easy configuration in Prestashop back office

## Installation

### 1. Prerequisites

- Prestashop 9.0+
- PHP 7.4+
- Cloudflare Turnstile account (free tier available)

### 2. Getting Cloudflare Turnstile Keys

1. Visit [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Navigate to **AI & Security > Turnstile**
3. Click **Create Site**
4. Fill in your domain information:
   - **Site name**: Your store name
   - **Domain**: Your Prestashop domain
   - **Mode**: Managed (recommended for best UX)
5. Copy the **Site Key** and **Secret Key**

### 3. Install the Module

#### Method A: Upload via Prestashop Admin

1. Go to **Modules > Module Manager** in Prestashop Admin
2. Click **Upload a module**
3. Select the `mcs_turnstile.zip` file
4. Click **Upload**
5. Locate **Cloudflare Turnstile** in the modules list
6. Click **Configure**

#### Method B: Manual Installation

1. Extract the module to your Prestashop installation:
   ```
   /modules/mcs_turnstile/
   ```
2. Go to **Modules > Module Manager**
3. Find **Cloudflare Turnstile** and click **Install**

### 4. Configuration

1. In Prestashop Admin, go to **Modules > Module Manager**
2. Find **Cloudflare Turnstile** and click **Configure**
3. Enable the module by toggling **Enable Turnstile**
4. Enter your **Site Key** from Cloudflare
5. Enter your **Secret Key** from Cloudflare
6. Select which forms to protect by checking the boxes
7. Click **Save**

## Configuration Options

### Module Settings

- **Enable Turnstile**: Toggle module on/off globally
- **Site Key**: Your Cloudflare Turnstile Site Key (public)
- **Secret Key**: Your Cloudflare Turnstile Secret Key (private)

### Protected Forms

You can selectively protect the following forms:

1. **Newsletter Subscription** - Protect your newsletter signup
2. **Login** - Secure customer login
3. **Registration** - Prevent bot registrations
4. **Reset Password** - Secure password recovery
5. **Contact Form** - Reduce spam on contact form
6. **Back in Stock Alert** - Prevent bot alerts

## How It Works

### For Users
1. When a customer submits a protected form, they'll see the Turnstile widget
2. They complete the verification (usually just a click)
3. Form submission continues normally
4. No impact on user experience or store speed

### For Store Owner
- Module hooks into Prestashop form submission points
- Automatically validates Turnstile tokens server-side
- Prevents spam and bot attacks
- Configuration is centralized in Prestashop admin
- No external dependencies or database migrations

## Security Considerations

✅ **Server-Side Validation** - All tokens verified with Cloudflare API
✅ **No Bot Traffic** - Turnstile blocks automated attacks
✅ **No User Data Stored** - Only tokens validated in real-time
✅ **Private Keys Protected** - Secret key stored securely in Prestashop config
✅ **API Timeout Protection** - Graceful fallback if Cloudflare API unreachable

## Performance Impact

- **Frontend**: Minimal - Turnstile loads asynchronously
- **Backend**: Negligible - Token verification is fast and cached
- **SEO**: No impact - Turnstile tokens not indexed
- **Page Load**: <100ms additional validation time

## Troubleshooting

### "Turnstile verification failed"

1. **Check Keys**: Verify Site Key and Secret Key are correct
   - Site Key must match in both config and Cloudflare dashboard
   - Secret Key must be exact match (copy from Cloudflare)

2. **Check Network**: Ensure server can reach Cloudflare API
   - Test: `curl https://challenges.cloudflare.com/turnstile/v0/api.js`

3. **Check Configuration**: Verify module is enabled
   - Go to Module Manager and check status

4. **Check Logs**: Review Prestashop error logs
   - Location: `/var/log/prestashop.log`

### Widget Not Displaying

1. Clear Prestashop cache
   - Admin > Advanced Parameters > Performance > Clear cache
2. Verify form protection is enabled
   - Check Module Configuration
3. Check browser console for JavaScript errors
   - Open Developer Tools (F12) > Console tab

### Form Submissions Failing

1. Ensure Turnstile widget is visible on the form
2. Check if customer completes verification
3. Review server logs for validation errors
4. Verify Secret Key is not empty or corrupted

## Module Architecture

```
mcs_turnstile/
├── mcs_turnstile.php           # Main module class
├── config.xml                   # Module metadata
├── classes/
│   └── TurnstileHelper.php      # Helper utilities
├── controllers/
│   └── front/
│       └── validation.php       # Token validation controller
├── override/
│   └── classes/form/
│       ├── CustomerAccountForm.php   # Form validation override
│       └── FormDataHandler.php       # Data handler override
└── views/
    └── templates/
        ├── front/
        │   └── turnstile_widget.tpl  # Widget template
        └── admin/
            └── configuration.tpl      # Admin config template
```

## Front-end Integration

The module loads `views/js/front.js` and the Turnstile API through
`displayBeforeBodyClosingTag`. The script places a widget in the Classic theme's
login, registration, password-reset, contact, newsletter, and back-in-stock
forms when each form is enabled in the module configuration.

The back-in-stock alert is submitted asynchronously by `ps_emailalerts`; showing
a widget does not by itself make that module's server endpoint verify the token.

## API Reference

### Mcs_Turnstile Class

```php
// Check if module is enabled
Mcs_Turnstile::isEnabled();

// Get forms configuration
Mcs_Turnstile::getFormsConfig();

// Validate Turnstile token from POST
Mcs_Turnstile::validateTurnstile();
```

### TurnstileHelper Class

```php
$module = Module::getInstanceByName('mcs_turnstile');
$helper = new TurnstileHelper($module);

// Check if form is protected
$helper->isFormProtected('login');

// Get widget HTML
$html = $helper->getWidgetHtml();

// Render complete widget with script
$html = $helper->render();

// Validate token
$valid = $helper->validateToken();

// Get protected forms
$forms = $helper->getProtectedForms();
```

## Customization

### Adding Custom Forms

To protect custom forms, add validation in your form handler:

```php
$module = Module::getInstanceByName('mcs_turnstile');
if ($module && $module->isEnabled()) {
    if (!Mcs_Turnstile::validateTurnstile()) {
        $this->errors[] = 'CAPTCHA verification failed';
        return false;
    }
}
```

### Styling the Widget

Override CSS in your theme:

```css
.cf-turnstile {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}
```

### Changing Widget Theme

Edit `mcs_turnstile.php` line 311 to change theme:

```php
// Options: 'light', 'dark'
data-theme="light"
```

## Support & Issues

For issues, feature requests, or bug reports, please visit:
https://github.com/mcityscreations/mcs_ps_turnstile

## License

Academic Free License 3.0 (AFL-3.0)

## Author

**MCS - Multicreations Services**

## Changelog

### Version 1.0.0 (2024-11-19)
- Initial release
- Full Prestashop 9 compatibility
- Support for 6 form types
- Complete admin configuration
- Server-side token validation

---

**Last Updated**: 2024-11-19
**Version**: 1.0.0
