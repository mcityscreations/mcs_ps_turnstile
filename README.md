# MCS Cloudflare Turnstile - Prestashop 9 Module

A production-ready Prestashop 9 module that seamlessly integrates Cloudflare Turnstile CAPTCHA into your store's forms for enhanced security without compromising user experience.

## 🚀 Features

- **Easy Configuration** - Admin dashboard with simple setup
- **Multiple Form Protection** - Secure 6 key forms:
  - 🔐 Login
  - 👤 Registration
  - 🔑 Password Reset
  - 📧 Newsletter
  - 💬 Contact Form
  - 🛍️ Back in Stock Alert
- **Flexible Control** - Enable/disable protection per form
- **Non-Intrusive** - No core Prestashop modifications
- **Server-Side Validation** - Secure token verification
- **User-Friendly** - Modern, accessible CAPTCHA
- **Free** - Cloudflare Turnstile free tier included

## 📦 Installation

### Quick Install (3 Steps)

1. **Download** the module to `/modules/mcs_turnstile/`
2. **Enable** in Prestashop Admin > Modules > Module Manager
3. **Configure** with your Cloudflare Turnstile keys

See [INSTALL.md](INSTALL.md) for detailed setup instructions.

## ⚙️ Configuration

1. Get free keys from [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Enter Site Key and Secret Key in module settings
3. Select which forms to protect
4. Click Save

**That's it!** Your forms are now protected.

## 📚 Documentation

- **[INSTALL.md](INSTALL.md)** - Installation and setup guide
- **[IMPLEMENTATION.md](IMPLEMENTATION.md)** - Detailed implementation examples
- **[TECHNICAL.md](TECHNICAL.md)** - Technical architecture and API reference

## ✨ What's Included

```
mcs_turnstile/
├── mcs_turnstile.php              # Main module class
├── config.xml                      # Module configuration
├── classes/
│   └── TurnstileHelper.php         # Helper utilities
├── controllers/
│   └── front/validation.php        # Validation endpoint
├── override/classes/form/          # Form validation overrides
└── views/templates/                # Admin & front templates
```

## 🔒 Security

✅ Server-side token validation with Cloudflare API
✅ Private keys never sent to client
✅ Prevents spam and bot attacks
✅ No user data storage
✅ HTTPS required for API calls

## 🛠️ Customization

Add Turnstile to custom forms:

```php
$module = Module::getInstanceByName('mcs_turnstile');
if ($module && Module::isEnabled('mcs_turnstile')) {
    $helper = new TurnstileHelper($module);
    echo $helper->getWidgetHtml();
    
    // Validate on submit
    if (!$helper->validateToken()) {
        die('Verification failed');
    }
}
```

## 📱 Compatibility

- **Prestashop:** 9.0.0+
- **PHP:** 7.4+
- **Browsers:** All modern browsers + mobile
- **Multi-shop:** ✅ Supported

## 🐛 Troubleshooting

**Widget not showing?**
- Verify module is enabled
- Check form is selected in configuration
- Clear template cache

**Verification fails?**
- Confirm Site Key and Secret Key
- Check that PHP can reach `https://challenges.cloudflare.com/turnstile/v0/siteverify`
  over HTTPS using cURL or, if cURL is unavailable, `allow_url_fopen`
- Review PrestaShop logs for messages beginning with `Turnstile:`
- Confirm the module is enabled, the form is selected, and the installed hooks
  and overrides are active; clear the PrestaShop cache after upgrading

The widget alone does not call Siteverify: submit a protected form with a
completed widget to trigger validation. Cloudflare tokens are single-use; do not
pre-validate a token using the validation endpoint before submitting the form.

See [IMPLEMENTATION.md](IMPLEMENTATION.md#troubleshooting-guide) for detailed solutions.

## 📞 Support

- 📖 Read the documentation files
- 🐛 Report issues on GitHub
- 💡 Check [FAQ](IMPLEMENTATION.md#frequently-asked-questions)

## 📄 License

Academic Free License 3.0 (AFL-3.0)

## 👨‍💼 Author

**MCS - Multicreations Services**

---

**Current Version:** 1.1.0
**Prestashop Version:** 9.0.0+
**Last Updated:** November 2024

**Ready to get started?** → [See Installation Guide](INSTALL.md)