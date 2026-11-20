# Testing Guide - MCS Turnstile Module

Complete testing procedures to verify the module works correctly.

## 📋 Test Environment Setup

### Prerequisites

- Prestashop 9.0+ installed and running
- Working Prestashop installation with sample data
- Cloudflare Turnstile account with Site Key and Secret Key
- Access to Prestashop Admin panel
- Access to FTP or admin file manager

### Test Credentials

- **Admin User:** Your Prestashop admin account
- **Test Customer:** Create test account for testing registration
- **Test Email:** Use a test email (e.g., test@example.com)

---

## ✅ Installation Tests

### Test I1: Module Discovery

**Objective:** Verify module appears in Module Manager

**Steps:**
1. Login to Prestashop Admin
2. Navigate to **Modules > Module Manager**
3. Search for "Turnstile" in search box

**Expected Result:**
- Module "Cloudflare Turnstile" appears in list
- Status shows "Installed" or "Not Installed"
- Configure button is available

**Result:** ☐ PASS ☐ FAIL

**Notes:** _______________

---

### Test I2: Module Installation

**Objective:** Successfully install the module

**Steps:**
1. Find "Cloudflare Turnstile" in Module Manager
2. Click "Install" button
3. Wait for installation to complete

**Expected Result:**
- Installation completes without errors
- Status changes to "Installed"
- "Configure" button appears
- No error messages shown

**Result:** ☐ PASS ☐ FAIL

**Error Messages (if any):** _______________

---

### Test I3: Module Activation

**Objective:** Verify module can be activated

**Steps:**
1. Click "Configure" on the module
2. Configuration page loads
3. Toggle "Enable Turnstile" switch to YES
4. Leave other fields empty for now
5. Click "Save"

**Expected Result:**
- Configuration page loads without errors
- Settings can be toggled
- Save button works (even with empty keys for testing)
- No server errors in logs

**Result:** ☐ PASS ☐ FAIL

---

## ⚙️ Configuration Tests

### Test C1: Configuration Page Load

**Objective:** Verify admin configuration panel loads correctly

**Steps:**
1. Navigate to Modules > Module Manager
2. Find "Cloudflare Turnstile"
3. Click "Configure"
4. Wait for page to fully load

**Expected Result:**
- Configuration page loads without errors
- All form fields are visible
- Input boxes are active
- Save button is clickable
- No JavaScript console errors

**Result:** ☐ PASS ☐ FAIL

---

### Test C2: Save Configuration

**Objective:** Verify settings can be saved

**Prerequisites:**
- Have valid Cloudflare Turnstile Site Key and Secret Key

**Steps:**
1. Open module configuration
2. Enable Turnstile (toggle to YES)
3. Enter valid Site Key
4. Enter valid Secret Key
5. Check "Login" and "Registration" forms
6. Click "Save"

**Expected Result:**
- Configuration saves without errors
- Success message appears: "Settings updated successfully"
- Refreshing page shows saved values
- Settings persist after logout/login

**Result:** ☐ PASS ☐ FAIL

**Success Message:** _______________

---

### Test C3: Validation - Empty Keys

**Objective:** Verify module requires Site Key and Secret Key

**Steps:**
1. Open module configuration
2. Enable Turnstile
3. Leave Site Key and Secret Key empty
4. Check form protection options
5. Click "Save"

**Expected Result:**
- Error message appears: "Site Key and Secret Key are required"
- Settings are not saved
- Must enter keys to proceed

**Result:** ☐ PASS ☐ FAIL

**Error Message:** _______________

---

### Test C4: Form Selection

**Objective:** Verify forms can be selected/deselected

**Prerequisites:**
- Module configured with valid keys

**Steps:**
1. Open module configuration
2. Check "Login" form
3. Uncheck "Newsletter" form
4. Check "Contact" form
5. Save configuration
6. Reload configuration page
7. Verify selections are retained

**Expected Result:**
- Each form can be independently selected
- Selections are saved
- Selections persist after page reload
- Configuration matches what was selected

**Result:** ☐ PASS ☐ FAIL

---

## 🎨 Frontend Display Tests

### Test F1: Widget on Login Form

**Objective:** Verify Turnstile widget displays on login page

**Prerequisites:**
- Module installed and configured
- Module enabled
- "Login" form is selected for protection

**Steps:**
1. Logout from customer account
2. Navigate to customer login: `/customer/login`
3. Wait for page to fully load
4. Look for Turnstile widget

**Expected Result:**
- Turnstile widget is visible on login form
- Widget shows "I'm not a robot" or similar
- Widget is properly styled and responsive
- No JavaScript console errors
- Form is functional

**Result:** ☐ PASS ☐ FAIL

**Widget Appearance:** _______________

---

### Test F2: Widget on Registration Form

**Objective:** Verify Turnstile widget displays on registration page

**Prerequisites:**
- Module installed and configured
- Module enabled
- "Registration" form is selected for protection

**Steps:**
1. Navigate to customer registration: `/customer/register` or `/register`
2. Wait for page to fully load
3. Look for Turnstile widget

**Expected Result:**
- Turnstile widget is visible at top of form
- Widget is fully functional
- Registration form is below widget
- No layout issues
- Widget loads Cloudflare script successfully

**Result:** ☐ PASS ☐ FAIL

---

### Test F3: Widget on Contact Form

**Objective:** Verify Turnstile widget displays on contact form

**Prerequisites:**
- Module installed and configured
- Module enabled
- "Contact" form is selected for protection

**Steps:**
1. Navigate to contact form: `/contact-us` or `/contact`
2. Wait for page to fully load
3. Look for Turnstile widget in form

**Expected Result:**
- Turnstile widget is visible in contact form
- Widget is integrated naturally with form
- Contact form fields are accessible
- No conflicts with form layout

**Result:** ☐ PASS ☐ FAIL

---

### Test F4: Widget Responsiveness

**Objective:** Verify widget displays correctly on mobile

**Steps:**
1. Open any protected form in desktop browser
2. Open Developer Tools (F12)
3. Toggle Device Toolbar (Ctrl+Shift+M)
4. Test on iPhone 12 viewport (390x844)
5. Test on iPad viewport (768x1024)
6. Verify widget displays correctly

**Expected Result:**
- Widget displays correctly on mobile sizes
- Widget is fully clickable on mobile
- Form is accessible below widget
- No layout distortions
- Widget doesn't overlap form fields

**Result:** ☐ PASS ☐ FAIL

---

### Test F5: Widget Themes

**Objective:** Verify widget theme can be changed

**Steps:**
1. Edit module configuration
2. Change theme from 'light' to 'dark' (edit mcs_turnstile.php)
3. Clear browser cache
4. View protected form
5. Verify widget uses dark theme

**Expected Result:**
- Widget responds to theme changes
- Dark theme is visually distinct from light
- Widget remains readable in both themes
- No functionality affected by theme

**Result:** ☐ PASS ☐ FAIL

---

## 🔐 Validation Tests

### Test V1: Successful Validation

**Objective:** Verify form submission succeeds after completing widget

**Prerequisites:**
- Turnstile widget visible on form
- Module configured correctly

**Steps:**
1. Navigate to login form
2. Wait for Turnstile widget to load
3. Click the widget
4. Complete verification (may require checking box or clicking)
5. Fill in login credentials
6. Click "Sign In"

**Expected Result:**
- Widget verification completes without error
- Form submits successfully
- User is logged in (if credentials correct)
- No validation errors related to Turnstile

**Result:** ☐ PASS ☐ FAIL

**Verification Time:** _____ seconds

---

### Test V2: Failed Validation

**Objective:** Verify form submission fails without widget completion

**Prerequisites:**
- Turnstile widget visible on form

**Steps:**
1. Navigate to login form
2. Wait for Turnstile widget to load
3. Do NOT click/complete the widget
4. Fill in login credentials
5. Click "Sign In"
6. Observe error

**Expected Result:**
- Form submission is blocked
- Error message appears: "Turnstile verification failed" or similar
- Form data is not processed
- User remains on login page
- Widget is still visible for retry

**Result:** ☐ PASS ☐ FAIL

**Error Message:** _______________

---

### Test V3: Token Validation Timeout

**Objective:** Verify token doesn't work after timeout

**Prerequisites:**
- Module configured with valid keys
- Turnstile widget loading and functional

**Steps:**
1. Complete Turnstile widget verification
2. Wait 10+ minutes without submitting form
3. Try to submit form with expired token
4. Observe result

**Expected Result:**
- Form submission fails with token validation error
- Error message guides user to retry widget
- User must complete widget again
- System correctly rejects stale tokens

**Result:** ☐ PASS ☐ FAIL

---

### Test V4: Multiple Attempts

**Objective:** Verify user can retry after validation failure

**Prerequisites:**
- Module configured correctly

**Steps:**
1. Try to submit form without widget completion
2. See error message
3. Widget is still visible
4. Click widget and complete verification
5. Try submission again
6. Form should now succeed (if data is valid)

**Expected Result:**
- Multiple attempts are supported
- User can complete widget multiple times
- No "attempt limit" errors
- System allows retry after failure

**Result:** ☐ PASS ☐ FAIL

---

## 🚀 Registration Flow Tests

### Test R1: Complete Registration

**Objective:** New user can register with Turnstile protection

**Steps:**
1. Navigate to registration form
2. See Turnstile widget
3. Fill registration form:
   - First Name: "Test"
   - Last Name: "User"
   - Email: "testuser123@example.com"
   - Password: "SecurePass123!"
4. Complete Turnstile widget verification
5. Click "Create"

**Expected Result:**
- Registration form submits successfully
- Turnstile token is validated
- New account is created
- Confirmation email is sent (if configured)
- User can login with new account

**Result:** ☐ PASS ☐ FAIL

**New Account Email:** _______________

---

### Test R2: Registration Validation

**Objective:** Verify invalid registration data is rejected

**Steps:**
1. Navigate to registration form
2. Complete Turnstile
3. Enter invalid data:
   - Email: (leave empty)
   - Password: "123" (too short)
4. Try to submit
5. Observe validation

**Expected Result:**
- Prestashop validation catches errors (not Turnstile)
- Appropriate error messages shown
- The customer account is not created
- The invalid submission is rejected

**Result:** ☐ PASS ☐ FAIL

---

## Server-Side Enforcement Tests

For each form, enable global Turnstile and select that form in module
configuration. Submit without completing the widget and confirm the request is
rejected before its side effect:

- **Login:** no customer session is opened.
- **Registration:** no customer account is created.
- **Password reset:** no reset email is sent and no password is changed.
- **Contact:** no contact message is sent; a PrestaShop error is displayed.
- **Newsletter:** no subscription is recorded; unsubscription remains available.
- **Back in stock:** no alert is recorded and the AJAX response displays an error.

Then disable the global setting or deselect one form and confirm that form is
not rejected by Turnstile. Repeat with a valid token and confirm normal
processing succeeds.

For back-in-stock alerts, also submit an invalid email after completing the
widget, then correct it and retry after the widget refreshes. The alert should
be recorded without a duplicate-token error. The JavaScript regression tests can
be run with `node --test tests/front-mailalert.test.js`.

---

## 📧 Email Form Tests

### Test E1: Contact Form Submission

**Objective:** Contact form works with Turnstile protection

**Steps:**
1. Navigate to contact form
2. Fill form:
   - Subject: "Test Subject"
   - Message: "This is a test message"
   - Email: "test@example.com"
3. See Turnstile widget
4. Complete widget verification
5. Click "Send"

**Expected Result:**
- Form submits successfully
- Contact message is received
- Email confirmation is sent (if configured)
- No validation errors
- Success message appears

**Result:** ☐ PASS ☐ FAIL

**Confirmation Message:** _______________

---

## 🔄 Admin Panel Tests

### Test A1: Enable/Disable Module

**Objective:** Module can be enabled and disabled

**Steps:**
1. Open module configuration
2. Toggle "Enable Turnstile" to OFF
3. Save
4. Navigate to login form
5. Turnstile widget should NOT appear
6. Toggle "Enable Turnstile" to ON
7. Save
8. Refresh login form
9. Turnstile widget should appear again

**Expected Result:**
- Module toggles work correctly
- Widget disappears when disabled
- Widget reappears when enabled
- Form functionality unaffected by toggle
- Save persists state

**Result:** ☐ PASS ☐ FAIL

---

### Test A2: Clear Settings

**Objective:** Module can be uninstalled cleanly

**Steps:**
1. Open Modules > Module Manager
2. Find "Cloudflare Turnstile"
3. Click "Uninstall"
4. Confirm uninstall
5. Verify module no longer appears
6. Check database that configuration is cleaned up

**Expected Result:**
- Module uninstalls without errors
- Configuration entries are removed
- Database is clean
- Module can be reinstalled fresh
- No leftover files or settings

**Result:** ☐ PASS ☐ FAIL

---

## 🛡️ Security Tests

### Test S1: Secret Key Protection

**Objective:** Secret key is never exposed to client

**Steps:**
1. Open protected form page
2. Open Browser Developer Tools (F12)
3. Go to Network tab
4. Submit form
5. Check all requests and responses
6. Search for secret key (should not appear)
7. Check page source code (Ctrl+U)
8. Search for secret key (should not appear)

**Expected Result:**
- Secret key never appears in network requests
- Secret key not in HTML source
- Secret key not in JavaScript
- Secret key only used server-side
- Site Key is public (can appear)

**Result:** ☐ PASS ☐ FAIL

**Verification Method:** _______________

---

### Test S2: Token Validation

**Objective:** Invalid tokens are rejected

**Steps:**
1. Enable browser developer tools (F12)
2. Submit form with Turnstile widget
3. Intercept the token value
4. Modify token value slightly (change one character)
5. Submit form
6. Observe validation error

**Expected Result:**
- Tampered token is rejected
- Validation error message appears
- Form is not processed
- System logs validation failure (check logs)

**Result:** ☐ PASS ☐ FAIL

---

## 🐛 Error Handling Tests

### Test H1: API Unreachable

**Objective:** Module gracefully handles Cloudflare API unavailability

**Steps:**
1. Configure module with valid keys
2. Add firewall rule to block Cloudflare API
   - OR disable internet temporarily
3. Try to submit protected form
4. Observe handling

**Expected Result:**
- Form submission fails gracefully
- Error message: "Verification failed"
- No system crash or 500 error
- Error is logged
- User can retry

**Result:** ☐ PASS ☐ FAIL

**Error Message:** _______________

---

### Test H2: Invalid Keys

**Objective:** Module handles incorrect keys

**Steps:**
1. Configure module with WRONG Secret Key
2. Try to submit protected form
3. Complete Turnstile widget
4. Observe validation

**Expected Result:**
- Form submission fails
- Validation error appears
- Error message guides to check configuration
- Logs show validation failure
- No system crash

**Result:** ☐ PASS ☐ FAIL

---

## 📊 Performance Tests

### Test P1: Page Load Time

**Objective:** Module doesn't significantly impact page load

**Prerequisites:**
- Module installed and enabled
- Access to browser DevTools

**Steps:**
1. Open Network tab in DevTools
2. Clear cache (Ctrl+Shift+Del)
3. Reload login page
4. Wait for full load
5. Note total load time
6. Disable module
7. Clear cache again
8. Reload login page
9. Compare times

**Expected Result:**
- Difference: <200ms additional load time
- Turnstile script loads asynchronously
- Page is interactive before Turnstile loads
- No blocking on page render

**Result:** ☐ PASS ☐ FAIL

**With Module:** _____ ms
**Without Module:** _____ ms
**Difference:** _____ ms

---

### Test P2: Memory Usage

**Objective:** Module doesn't consume excessive memory

**Steps:**
1. Open DevTools > Performance
2. Record performance during form use
3. Navigate to protected form
4. Interact with Turnstile widget
5. Submit form
6. Analyze memory usage

**Expected Result:**
- Memory usage stays below 50MB additional
- No memory leaks during repeated interactions
- Garbage collection works normally
- Performance timeline is smooth

**Result:** ☐ PASS ☐ FAIL

---

## ✨ Browser Compatibility Tests

Test on multiple browsers to ensure compatibility.

### Test B1: Chrome/Edge

**Browser:** Chrome or Edge (latest)

**Steps:**
1. Install/update browser
2. Test all major flows:
   - Load login form
   - Verify widget displays
   - Complete verification
   - Submit form
3. Check DevTools console for errors

**Expected Result:**
- ✅ All features work
- ✅ No console errors
- ✅ Widget displays correctly
- ✅ Validation succeeds

**Result:** ☐ PASS ☐ FAIL

**Browser Version:** _______________

---

### Test B2: Firefox

**Browser:** Firefox (latest)

**Steps:** (Same as Test B1)

**Result:** ☐ PASS ☐ FAIL

**Browser Version:** _______________

---

### Test B3: Safari

**Browser:** Safari (latest, macOS or iOS)

**Steps:** (Same as Test B1)

**Result:** ☐ PASS ☐ FAIL

**Browser Version:** _______________

---

### Test B4: Mobile Browser

**Browser:** Mobile Chrome or Safari

**Device:** iPhone, Android, or mobile emulator

**Steps:**
1. Open protected form on mobile
2. Verify widget displays correctly
3. Complete verification on mobile
4. Submit form
5. Verify submission works

**Expected Result:**
- Widget adapts to mobile viewport
- Touch interaction works
- No horizontal scrolling needed
- Verification completes successfully

**Result:** ☐ PASS ☐ FAIL

**Device:** _______________

---

## 📝 Final Verification Checklist

Before considering testing complete, verify:

- [ ] All installation tests passed
- [ ] All configuration tests passed
- [ ] All frontend display tests passed
- [ ] All validation tests passed
- [ ] All registration/email tests passed
- [ ] All admin panel tests passed
- [ ] All security tests passed
- [ ] All error handling tests passed
- [ ] All performance tests passed
- [ ] All browser compatibility tests passed
- [ ] No console errors observed
- [ ] No database errors in logs
- [ ] Module can be installed/uninstalled cleanly
- [ ] Settings persist after logout/login
- [ ] Protected forms actually reject invalid submissions

---

## 📋 Test Summary Report

**Tested By:** _______________
**Date:** _______________
**Prestashop Version:** _______________
**PHP Version:** _______________
**Browser(s):** _______________

**Total Tests:** 40+
**Passed:** _____
**Failed:** _____
**Skipped:** _____

**Overall Result:** ☐ APPROVED ☐ NEEDS FIXES ☐ REJECTED

**Issues Found:**
1. _______________
2. _______________
3. _______________

**Recommendations:**
_______________________________________________

**Sign-off:**
_______________________________________________

---

**Version:** 1.1.0
**Last Updated:** November 2024
