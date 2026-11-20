# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - Unreleased

### Added
- Server-side Turnstile enforcement for login, registration, password reset, contact, newsletter subscription, and back-in-stock requests.
- An upgrade routine to register the required hooks and install the new overrides.

### Fixed
- Validation now runs only when global Turnstile protection and the corresponding form are enabled.
- The generic form data handler no longer blocks unrelated forms.

## [1.0.0] - 2024-11-19

### Added
- Initial release of MCS Cloudflare Turnstile module
- Prestashop 9 full compatibility
- Support for 6 form types:
  - Login form
  - Registration form
  - Password reset form
  - Contact form
  - Newsletter subscription
  - Back in stock alerts
- Admin configuration panel with detailed settings
- Flexible form protection (enable/disable per form)
- Server-side token validation with Cloudflare API
- TurnstileHelper utility class for easy integration
- Front-end validation controller
- Form overrides for seamless integration
- Comprehensive documentation:
  - INSTALL.md - Installation and setup
  - TECHNICAL.md - Technical architecture
  - IMPLEMENTATION.md - Implementation examples
- Multi-language support ready
- Error handling and logging
- Security hardening (index.php files, header checks)
- Responsive Turnstile widget display
- Mobile-friendly implementation
- Performance optimizations
  - Async script loading
  - Configuration caching
  - Efficient token validation

### Technical Details
- Module class: Mcs_Turnstile
- Helper class: TurnstileHelper
- Validation controller: Mcs_TurnstileValidationModuleFrontController
- Hooks: 10 key Prestashop hooks
- Database: Configuration table (Prestashop native)
- API: Cloudflare Turnstile v0 API

### Module Characteristics
- Tab: Security
- Instance needed: No
- Bootstrap compatible: Yes
- Configurable: Yes
- Multi-shop compatible: Yes

### Documentation
- README.md - Project overview
- INSTALL.md - 8000+ words installation guide
- TECHNICAL.md - 9000+ words technical documentation
- IMPLEMENTATION.md - 13000+ words implementation guide
- CHANGELOG.md - Version history

### File Structure
```
mcs_turnstile/
├── mcs_turnstile.php (13KB)
├── config.xml
├── index.php
├── README.md
├── INSTALL.md
├── TECHNICAL.md
├── IMPLEMENTATION.md
├── CHANGELOG.md
├── classes/
│   ├── TurnstileHelper.php
│   └── index.php
├── controllers/
│   ├── front/
│   │   ├── validation.php
│   │   └── index.php
│   └── index.php
├── override/
│   └── classes/form/
│       ├── CustomerAccountForm.php
│       ├── FormDataHandler.php
│       └── index.php
└── views/templates/
    ├── front/turnstile_widget.tpl
    └── admin/configuration.tpl
```

### Known Limitations
- Newsletter protection requires theme integration for some templates
- Requires Cloudflare Turnstile account (free tier available)
- API calls require server internet connectivity
- Minimum PHP 7.4 requirement

### Browser Support
- ✅ Chrome/Chromium (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers
- ⚠️ IE11 not supported by Turnstile API

### Testing Status
- ✅ Prestashop 9.0.0 compatible
- ✅ PHP 7.4+ tested
- ✅ PHP 8.0+ tested
- ✅ PHP 8.1+ tested
- ✅ Configuration panel functional
- ✅ Form validation working
- ✅ Token verification working
- ✅ Error handling functional

### Roadmap (Future Versions)

#### v1.1.0 (Planned)
- Multi-language support for error messages
- Widget theme selection in admin UI
- Dashboard statistics (blocked attempts, success rate)
- AJAX form submission support
- Advanced logging and audit trail

#### v1.2.0 (Planned)
- API rate limiting for validation
- Token caching for performance
- Custom error message support
- Form-specific configuration
- Admin activity logging

#### v2.0.0 (Planned)
- Turnstile v1 API support
- Multiple CAPTCHA challenge types
- Advanced bot detection reporting
- Performance analytics
- Integration with antifraud modules

### Contributors
- MCS - Multicreations Services (Initial Development)

### Support
For support, issues, or feature requests:
- GitHub: https://github.com/mcityscreations/mcs_ps_turnstile
- Documentation: See included MD files

---

**Version:** 1.0.0
**Release Date:** November 19, 2024
**License:** AFL-3.0
