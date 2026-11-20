# Quick Start Guide - 5 Minutes to Live Protection

## ⏱️ Time Required: ~5 Minutes

## Step 1: Get Your Cloudflare Keys (2 min)

1. Go to https://dash.cloudflare.com/ 
2. Sign in (create free account if needed)
3. Click **AI & Security** → **Turnstile** in left menu
4. Click **Create Site** button
5. Fill form:
   - **Site name:** `My Prestashop Store`
   - **Domain:** `yourdomain.com`
   - **Mode:** Select "Managed"
6. Click **Create**
7. ✅ Copy your **Site Key** (long alphanumeric string)
8. ✅ Copy your **Secret Key** (long alphanumeric string)

**Keep these safe!** You'll need them in next step.

---

## Step 2: Install Module in Prestashop (2 min)

### Option A: Via Admin Panel (Easiest)

1. Upload `mcs_turnstile` folder to `/modules/` directory via FTP
2. Go to Prestashop Admin → **Modules > Module Manager**
3. Search for "Turnstile" in search box
4. Click **Install** button
5. Click **Configure** button

### Option B: Via Git

```bash
cd /path/to/prestashop/modules/
git clone https://github.com/mcityscreations/mcs_ps_turnstile.git mcs_turnstile
```

Then follow Option A steps 2-5

---

## Step 3: Configure Module (1 min)

You should now see the configuration page. Fill it out:

**Form Fields:**

1. **Enable Turnstile:** 
   - Toggle switch to **YES** ✅

2. **Site Key:**
   - Paste the Site Key from Step 1

3. **Secret Key:**
   - Paste the Secret Key from Step 1

4. **Protected Forms:**
   - ✅ Check: **Login**
   - ✅ Check: **Registration**
   - ✅ Check: **Contact Form**
   - (Optional) Check others

5. Click **Save** button

**Done!** Your forms are now protected. 🎉

---

## Step 4: Test It (1 min)

1. Go to your store's login page: `/customer/login`
2. You should see **Turnstile widget** appear
3. Click the widget to verify
4. Try to login

**If you see the widget:** ✅ Success! Module is working.

**If you don't see it:** 
- Clear browser cache (Ctrl+Shift+Del)
- Clear Prestashop cache (Admin → Performance → Clear)
- Refresh page

---

## 🔥 Common Issues & Fixes

### "Module not showing in list"
```
Solution: Clear Prestashop cache
Admin → Advanced Parameters → Performance → Clear Cache
```

### "Turnstile widget not visible"
```
Solution 1: Verify Site Key is correct
Admin → Configure Module → Check Site Key matches Cloudflare

Solution 2: Clear template cache
Admin → Performance → Smarty Cache → Clear Cache

Solution 3: Check form protection is enabled
Admin → Configure Module → Check form checkboxes
```

### "Verification keeps failing"
```
Solution: Check Secret Key
Admin → Configure Module → Verify Secret Key exactly matches Cloudflare
```

### "Form won't submit with Turnstile"
```
Solution: Ensure widget is marked as complete
- Widget should have green checkmark
- If not, complete the verification challenge again
```

---

## 📋 Checklist - Are you done?

- [ ] Got Site Key from Cloudflare
- [ ] Got Secret Key from Cloudflare
- [ ] Module installed in Prestashop
- [ ] Module configured with both keys
- [ ] Selected forms to protect
- [ ] Clicked Save
- [ ] Tested on login form
- [ ] Saw Turnstile widget appear

**All checked?** You're done! 🚀

---

## 🛡️ What's Now Protected?

Your store now has anti-bot protection on:

- 🔐 **Login Form** - Stops account takeover attempts
- 👤 **Registration** - Blocks bulk fake account creation
- 💬 **Contact Form** - Reduces spam messages
- 🔑 **Password Reset** (if enabled) - Prevents account hijacking
- 📧 **Newsletter** (if enabled) - Stops email spam signups
- 🛍️ **Back in Stock** (if enabled) - Prevents alert spam

---

## 📚 Need More Help?

- **Detailed Setup:** See [INSTALL.md](INSTALL.md)
- **Implementation Examples:** See [IMPLEMENTATION.md](IMPLEMENTATION.md)
- **Technical Details:** See [TECHNICAL.md](TECHNICAL.md)
- **Full Changelog:** See [CHANGELOG.md](CHANGELOG.md)

---

## 🎓 Next Steps

### Recommended Reading

1. **[INSTALLATION.md](INSTALL.md)** - Complete setup guide
2. **[IMPLEMENTATION.md](IMPLEMENTATION.md)** - Real-world usage examples
3. **[TECHNICAL.md](TECHNICAL.md)** - For developers

### Advanced Customization

After basic setup, you can:
- Change widget theme (light/dark/auto)
- Protect custom forms
- Adjust widget styling
- Monitor blocked attempts

See [IMPLEMENTATION.md - Customization](IMPLEMENTATION.md#customization) section.

---

## ⚡ Performance Impact

**Good News!** The module is very lightweight:

- **Page Load:** +20-50ms (async loading)
- **Storage:** <5MB total
- **Database:** <1KB config storage
- **User Experience:** No delays, non-intrusive

---

## 🔐 Security Notes

✅ **Your secret key is safe:**
- Stored securely on your server
- Never sent to browsers
- Only used for verification

✅ **User data is not stored:**
- Tokens are verified in real-time
- No CAPTCHA data saved
- Anonymous verification

✅ **Protects against:**
- Bot account registrations
- Automated login attacks
- Spam form submissions
- DDoS-style spam

---

## 📞 Need Support?

1. **Check the docs** - Most answers are in the .md files
2. **GitHub Issues** - https://github.com/mcityscreations/mcs_ps_turnstile
3. **Cloudflare Docs** - https://developers.cloudflare.com/turnstile/

---

**Congratulations!** Your Prestashop store is now protected by Cloudflare Turnstile! 🎉

**Version:** 1.1.0
**Last Updated:** November 2024
