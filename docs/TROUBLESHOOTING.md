# AWS SBG Theme - Troubleshooting Guide

## 🚨 Styling Not Loading - Quick Fixes

### Issue: No CSS/Styling appearing on site

**Symptoms:**
- Page looks plain white/black without colors
- No gradients or animations
- Icons not displaying
- Layout is broken

---

## ✅ Solution 1: Clear Cache & Check CDN

### Step 1: Clear Browser Cache
1. Hard refresh browser: **Ctrl+Shift+R** (Windows) or **Cmd+Shift+R** (Mac)
2. Or open DevTools (F12) → Settings → Disable cache (while DevTools open)

### Step 2: Clear WordPress Cache
- If using a caching plugin (WP Super Cache, W3 Total Cache):
  1. Go to WordPress Admin Dashboard
  2. Find plugin settings and clear all cache
  3. Flush/Clear cache from the caching plugin

### Step 3: Test CDN Access
Open browser console (F12 → Console) and paste:
```javascript
// Test Tailwind CDN
fetch('https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.0.0')
  .then(r => console.log('Tailwind CDN: OK', r.status))
  .catch(e => console.error('Tailwind CDN: FAILED', e));

// Test Alpine.js CDN
fetch('https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js')
  .then(r => console.log('Alpine.js CDN: OK', r.status))
  .catch(e => console.error('Alpine.js CDN: FAILED', e));
```

---

## ✅ Solution 2: Verify Theme Activation

1. **Go to WordPress Admin** → Appearance → Themes
2. Look for "AWS Student Builder Group" theme
3. Click **Activate** (if not already active)
4. Refresh your site

### Check if activated:
- WordPress Admin → Appearance → Themes
- Should say "Active" under AWS SBG theme

---

## ✅ Solution 3: Fix CDN Block Issues

**If CDN is blocked by firewall/network:**

Create file: `/wp-content/themes/aws-sbg-theme/cdn-fallback.js`

```javascript
// Fallback if CDN fails
document.addEventListener('DOMContentLoaded', function() {
  // Check if Tailwind loaded
  if (!window.tailwind) {
    console.warn('Tailwind CDN failed - applying fallback styles');
    // Apply basic styles
    document.body.style.backgroundColor = '#0a0a0a';
    document.body.style.color = '#ffffff';
  }
  
  // Check if Alpine loaded
  if (!window.Alpine) {
    console.warn('Alpine.js CDN failed - interactivity may be limited');
  }
});
```

Then add to `functions.php` after line 70:
```php
wp_enqueue_script(
  'cdn-fallback',
  get_template_directory_uri() . '/cdn-fallback.js',
  array(),
  '1.0.0',
  true
);
```

---

## ✅ Solution 4: Check File Permissions

SSH into your server and run:
```bash
cd /wp-content/themes/aws-sbg-theme
chmod 755 *.php
chmod 755 *.css
chmod 644 *.md
ls -la
```

**Expected permissions:**
- PHP files: `-rw-r--r--` (644)
- Folders: `drwxr-xr-x` (755)

---

## ✅ Solution 5: Enable Debug Mode

1. Edit `/wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

2. Check errors at: `/wp-content/debug.log`

3. Look for PHP errors or script loading issues

---

## ✅ Solution 6: Test Individual Components

### Test Tailwind CSS
Add to any template temporarily:
```html
<div style="background-color: #ad5cff; padding: 20px; color: white;">
  If you see purple background, Tailwind is working
</div>
```

### Test Alpine.js
Add to template:
```html
<div x-data="{ test: true }" x-text="test ? 'Alpine.js is working!' : 'Failed'"></div>
```

### Test Custom Styles
Check browser DevTools → Elements → Styles tab:
- Should show `style.css` is loaded
- Should show Tailwind CDN stylesheet
- Should show inline styles from functions.php

---

## ✅ Solution 7: Deactivate Conflicting Plugins

1. WordPress Admin → Plugins
2. **Deactivate all plugins EXCEPT:**
   - Advanced Custom Fields (ACF)
   - Any security plugins
3. Refresh site - does styling load?

If yes, reactivate plugins one-by-one to find conflict.

**Common conflicts:**
- Elementor
- Divi
- Other page builders
- Asset optimization plugins (unless configured correctly)

---

## ✅ Solution 8: Check Network Tab (Browser DevTools)

1. Open DevTools (F12)
2. Go to **Network** tab
3. Refresh page
4. Look for:
   - `@tailwindcss/browser@4.0.0` - Should be **200 OK**
   - `alpinejs@3.x.x/dist/cdn.min.js` - Should be **200 OK**
   - `style.css` - Should be **200 OK**

**If showing 404 or failed:**
- CDN is blocked on your network
- Need to use alternative CDN or self-host
- Contact hosting provider

---

## ✅ Solution 9: Check WordPress Head Section

1. View page source (Ctrl+U or Cmd+U)
2. Look for in `<head>`:

```html
<link rel="stylesheet" id="aws-sbg-theme-style-css" href="...style.css">
<link rel="stylesheet" id="tailwind-css-css" href="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.0.0">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

If missing, the enqueue functions aren't running.

---

## ✅ Solution 10: Verify Template Hierarchy

Ensure pages use correct templates:
- Homepage → `front-page.php` OR `index.php`
- `/aircert/` page → `page-aircert.php` OR `page.php`
- `/contact/` page → `page-contact.php` OR `page.php`

Check WordPress:
1. Admin → Pages
2. Edit page
3. Scroll down → "Template" dropdown
4. Should see custom templates listed

If not listed, check `page-*.php` files exist in theme folder.

---

## 🔧 Advanced: Manual Style Testing

Add to `header.php` right after `<body>` tag temporarily:
```php
<style>
  body { background: #ad5cff !important; }
  h1 { color: #00ef88 !important; }
</style>
```

If colors show up, CSS is rendering. If not, there's a deeper issue.

---

## 📋 Checklist for Debugging

- [ ] Browser cache cleared (Ctrl+Shift+R)
- [ ] WordPress cache cleared
- [ ] Theme is activated
- [ ] CDN is accessible (check Network tab)
- [ ] No conflicting plugins active
- [ ] File permissions are correct (644 for files, 755 for folders)
- [ ] Debug log checked for errors
- [ ] Page template is set correctly
- [ ] wp_head() is in header.php
- [ ] wp_footer() is in footer.php

---

## 🆘 Still Not Working?

### Check Error Log
```bash
tail -50 /wp-content/debug.log
```

### Enable Temporary Debug Output
Add to `header.php` before `</head>`:
```php
<?php if ( current_user_can( 'manage_options' ) ) : ?>
  <?php aws_sbg_debug_info(); ?>
<?php endif; ?>
```

This will show theme status information.

### Check server.log
```bash
tail -100 /var/log/apache2/error.log
# or
tail -100 /var/log/nginx/error.log
```

### Contact Hosting Provider
Ask them to check:
- PHP version (should be 7.4+)
- CDN access blocked?
- Allow_url_fopen enabled?
- HTTP/HTTPS mixed content issues?

---

## 📞 Quick Support

**Issue:** "Styles load, then disappear"  
**Solution:** Likely FOUC (Flash of Unstyled Content) - normal, add preload tag to wp_head()

**Issue:** "Only works on homepage"  
**Solution:** Missing page templates - ensure page-*.php files exist

**Issue:** "Mobile styling broken"  
**Solution:** Check for Tailwind responsive prefixes (sm:, md:, lg:)

**Issue:** "Colors don't match brand colors"  
**Solution:** Edit `config-theme.php` and update color values, then clear cache

---

## 🎯 Last Resort: Direct CDN URLs

If jsdelivr is blocked, try alternative CDNs:

```php
// Unpkg CDN
wp_enqueue_style('tailwind-css', 'https://unpkg.com/@tailwindcss/browser@4', [], '4.0.0');
wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3/dist/cdn.min.js', [], '3.14.1', true);

// esm.sh CDN
wp_enqueue_style('tailwind-css', 'https://esm.sh/@tailwindcss/browser@4', [], '4.0.0');
```

Edit `functions.php` lines 57-65 to use these URLs.

---

**Last Updated:** July 22, 2026  
**Status:** Complete Troubleshooting Guide
