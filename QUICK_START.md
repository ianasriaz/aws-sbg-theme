# AWS SBG Theme - Quick Start Guide for New Installation

## 🚀 Getting Styling to Load - 3-Step Fix

### Step 1: Verify Theme Files (5 minutes)

Check that all essential files exist:
```
wp-content/themes/aws-sbg-theme/
├── style.css ✓
├── header.php ✓
├── footer.php ✓
├── functions.php ✓
├── config-theme.php ✓
├── helpers-colors.php ✓
├── helpers-fonts.php ✓
└── front-page.php ✓
```

**SSH Command to verify:**
```bash
cd wp-content/themes/aws-sbg-theme
ls -la *.php *.css | grep -E "(style|header|footer|functions|config|helpers)"
```

### Step 2: Activate Theme (2 minutes)

1. **WordPress Admin Dashboard**
2. Go to: **Appearance → Themes**
3. Find: **AWS Student Builder Group**
4. Click: **Activate**
5. **Refresh page** - styling should now load

### Step 3: Clear All Caches (2 minutes)

**Browser:**
- Press: **Ctrl+Shift+R** (Windows) or **Cmd+Shift+R** (Mac)

**WordPress Plugins** (if using):
- Admin → Settings → WP Super Cache → **Delete Cache**
- Or Admin → Settings → W3 Total Cache → **Purge All Caches**

**Server (if via SSH):**
```bash
# Clear Cloudflare cache (if using)
# Contact hosting provider to do this

# Clear WordPress cache directory
rm -rf wp-content/cache/*
```

---

## ✅ Verify Styling is Loading

### Quick Visual Check
1. Visit homepage
2. Should see:
   - ✅ Dark background (#0a0a0a)
   - ✅ Purple accents (#ad5cff)
   - ✅ Green highlights (#00ef88)
   - ✅ Proper spacing and layout
   - ✅ Smooth animations on hover

### Browser DevTools Check (F12)

1. Press **F12** to open DevTools
2. Go to **Network** tab
3. Refresh page
4. Look for these files (should show **200 OK**):
   - `style.css`
   - `@tailwindcss/browser`
   - `alpinejs...cdn.min.js`

If all show **200**, styling is loading correctly! ✅

### Check Page Source (Ctrl+U)

Look in `<head>` section for:
```html
<!-- Should see these lines: -->
<link rel="stylesheet" id="aws-sbg-theme-style-css" href="...">
<link rel="stylesheet" id="tailwind-css-css" href="...cdn.jsdelivr.net...">
<script id="alpinejs-js" src="...alpinejs..."></script>
```

If present, all scripts are loading! ✅

---

## 🔴 If Styling Still Not Loading

### Quick Diagnostic

1. **Check theme is actually active:**
   ```bash
   # SSH - Check which theme is active
   wp theme get 2>/dev/null | grep active
   ```

2. **Check for PHP errors:**
   ```bash
   # SSH - View error log
   tail -20 wp-content/debug.log
   ```

3. **Test CDN accessibility:**
   - Open a new tab
   - Visit: `https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.0.0`
   - Should download a file (if blocked, contact hosting)

### Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| **404 errors in Network tab** | CDN blocked - contact hosting provider |
| **Theme not active** | Go to Appearance → Themes → Activate |
| **Cache preventing updates** | Clear browser cache (Ctrl+Shift+R) |
| **PHP errors in debug.log** | Check file permissions: `chmod 644 *.php` |
| **Blank page** | Check PHP version (need 7.4+): `php -v` |

---

## 📝 Post-Installation Checklist

After styling loads, verify:

- [ ] Styling loads (colors visible)
- [ ] Mobile menu works (click hamburger on mobile)
- [ ] Links are clickable
- [ ] Forms on /aircert and /contact pages work
- [ ] Fonts display correctly
- [ ] No JavaScript errors in DevTools Console
- [ ] Page loads within 3 seconds

---

## 🎯 Next Steps

Once styling is working:

1. **Set up fonts** - See [FONTS_IMPLEMENTATION.md](FONTS_IMPLEMENTATION.md)
2. **Configure ACF** - Advanced Custom Fields (ACF) plugin required
3. **Create pages** - Create /aircert and /contact pages
4. **Test forms** - Submit test forms to verify email notifications
5. **Monitor performance** - Check Google PageSpeed

---

## 🆘 Need Help?

1. **See full guide:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. **Check configurations:** [.instructions.md](.instructions.md)
3. **Review colors:** [COLOR_REFERENCE.md](COLOR_REFERENCE.md)
4. **Review fonts:** [FONTS_IMPLEMENTATION.md](FONTS_IMPLEMENTATION.md)

---

## 💡 Pro Tips

### Tip 1: Test Individual Components
```html
<!-- Add to any page to test styling -->
<div class="bg-brand p-4 rounded-lg text-white">
  If this is purple, styling is working!
</div>
```

### Tip 2: Enable Debug Mode
Edit `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Check `/wp-content/debug.log` for errors.

### Tip 3: Test with Default Theme First
1. Switch to default WordPress theme (Twenty Twenty-Three)
2. If that works, issue is with AWS SBG theme
3. Switch back to AWS SBG and debug

### Tip 4: Check Plugins
Deactivate all plugins except ACF:
- If styling loads → plugin conflict exists
- Find and deactivate conflicting plugin

---

## 📊 File Size Check

**Theme files should be roughly:**
- `functions.php` - 10-15 KB
- `style.css` - 5-10 KB  
- `config-theme.php` - 8-12 KB
- All helpers combined - 15-20 KB

If sizes are drastically different, files may be corrupted.

**Check file sizes:**
```bash
du -h wp-content/themes/aws-sbg-theme/*.php
```

---

## 🎉 Success Indicators

✅ Theme successfully installed when you see:
- Dark professional background
- Purple and green accent colors
- Smooth animations and transitions
- Responsive design on mobile
- All text readable and properly spaced
- No console errors (F12 → Console)
- Pages load in < 3 seconds

---

**Time to get started:** 10 minutes  
**Difficulty level:** Easy  
**Support:** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) if issues arise

🚀 **Ready to go live!**
