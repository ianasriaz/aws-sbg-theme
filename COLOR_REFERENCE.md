# AWS SBG Theme - Color & Font Master Configuration

## 📋 Overview

This document explains the new master color and font system that centralizes all theme styling in a single source of truth.

**Key Files:**
- `config-theme.php` - Master configuration file (colors, fonts, spacing, etc.)
- `helpers-colors.php` - Helper functions to access colors throughout the theme
- All colors automatically available in Tailwind CSS

---

## 🎨 Color System

### Core Brand Colors

| Name | Hex Code | Usage | Notes |
|------|----------|-------|-------|
| **Brand** | `#ad5cff` | Primary CTAs, brand identity | Premium purple |
| **Brand Light** | `#c97bff` | Hover states, accents | Lighter purple |
| **Brand Dark** | `#8a3fc9` | Active states, pressed | Darker purple |
| **Supporting Green** | `#00ef88` | Success, confirmations, positive actions | Vibrant green |
| **Supporting Green Light** | `#4dff9f` | Hover states for green | Lighter green |
| **Supporting Green Dark** | `#00c96f` | Active states for green | Darker green |
| **Supporting Orange** | `#ffa000` | Warnings, secondary CTAs | Warm orange |
| **Supporting Orange Light** | `#ffb84d` | Hover states for orange | Lighter orange |
| **Supporting Orange Dark** | `#cc8200` | Active states for orange | Darker orange |

### Neutral Colors (Dark Theme)

| Name | Hex Code | Usage | Contrast |
|------|----------|-------|----------|
| **Dark BG Primary** | `#0a0a0a` | Main background, page level | Darkest |
| **Dark BG Secondary** | `#1a1a1a` | Cards, elevated surfaces | -15% brightness |
| **Dark BG Tertiary** | `#2a2a2a` | Hover states, subtle accents | -25% brightness |
| **Dark Text Primary** | `#ffffff` | Main text, headings | WCAG AAA |
| **Dark Text Secondary** | `#d0d0d0` | Secondary text, descriptions | WCAG AA |
| **Dark Text Tertiary** | `#999999` | Muted text, placeholders | WCAG AA |

### Semantic Colors

```javascript
// In templates, use:
aws_sbg_get_semantic_color( 'success' )   // #00ef88 - Green for positive
aws_sbg_get_semantic_color( 'error' )     // #ff4757 - Red for errors
aws_sbg_get_semantic_color( 'warning' )   // #ffa000 - Orange for warnings
aws_sbg_get_semantic_color( 'info' )      // #ad5cff - Purple for info
aws_sbg_get_semantic_color( 'muted' )     // #666666 - Gray for disabled
```

### Component-Specific Colors

**Buttons:**
- Primary: `#ad5cff` (brand) → `#c97bff` (hover) → `#8a3fc9` (active)
- Secondary: `#2a2a2a` (bg) → `#3a3a3a` (hover), border: brand
- Ghost: transparent → `#1a1a1a` (hover), text: secondary, border: `#666666`
- Disabled: `#2a2a2a` (bg), `#666666` (text)

**Forms:**
- Input background: `#1a1a1a`
- Input border: `#3a3a3a` → `#ad5cff` (focus)
- Input text: `#ffffff`
- Placeholder: `#999999`

**Cards & Surfaces:**
- Background: `#1a1a1a`
- Border: `#2a2a2a` → `#ad5cff` (hover)
- Shadow: `rgba(173, 92, 255, 0.15)`

### Gradients

Available gradients:
```javascript
aws_sbg_get_gradient( 'brand-to-dark' )     // Purple to dark purple
aws_sbg_get_gradient( 'brand-to-green' )    // Purple to green
aws_sbg_get_gradient( 'dark-to-darker' )    // Dark gray to darkest
aws_sbg_get_gradient( 'overlay-dark' )      // Dark overlay for images
```

### Shadow & Glow Effects

```javascript
aws_sbg_get_shadow( 'brand-glow' )          // 0 0 20px rgba(173, 92, 255, 0.4)
aws_sbg_get_shadow( 'brand-glow-soft' )     // 0 0 15px rgba(173, 92, 255, 0.2)
aws_sbg_get_shadow( 'success-glow' )        // 0 0 20px rgba(0, 239, 136, 0.3)
aws_sbg_get_shadow( 'card-subtle' )         // 0 4px 15px rgba(0, 0, 0, 0.3)
```

---

## 🖋️ Typography

### Font Families

All fonts use system fonts (no external requests):
```javascript
aws_sbg_get_font_family( 'heading' )  // 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
aws_sbg_get_font_family( 'body' )     // 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
aws_sbg_get_font_family( 'mono' )     // 'Fira Code', 'Courier New', monospace
```

### Font Sizes

```javascript
'xs'    => '0.75rem'    // 12px
'sm'    => '0.875rem'   // 14px
'base'  => '1rem'       // 16px
'lg'    => '1.125rem'   // 18px
'xl'    => '1.25rem'    // 20px
'2xl'   => '1.5rem'     // 24px
'3xl'   => '1.875rem'   // 30px
'4xl'   => '2.25rem'    // 36px
'5xl'   => '3rem'       // 48px
'6xl'   => '3.75rem'    // 60px
```

### Font Weights

```javascript
'light'      => 300
'normal'     => 400
'medium'     => 500
'semibold'   => 600
'bold'       => 700
'extrabold'  => 800
```

### Pre-composed Text Styles

```php
$style = aws_sbg_get_text_style( 'heading-1' );
// Returns: array with font-size, font-weight, line-height, letter-spacing

// Available styles:
aws_sbg_get_text_style( 'heading-1' )    // 3.75rem, bold, tight
aws_sbg_get_text_style( 'heading-2' )    // 2.25rem, bold, tight
aws_sbg_get_text_style( 'heading-3' )    // 1.875rem, semibold
aws_sbg_get_text_style( 'heading-4' )    // 1.5rem, semibold
aws_sbg_get_text_style( 'body-large' )   // 1.125rem, normal
aws_sbg_get_text_style( 'body-normal' )  // 1rem, normal
aws_sbg_get_text_style( 'body-small' )   // 0.875rem, normal
aws_sbg_get_text_style( 'label' )        // 0.875rem, bold, uppercase
aws_sbg_get_text_style( 'button' )       // 1rem, bold
```

---

## 📏 Spacing & Layout

### Spacing Scale

```javascript
'xs'    => '0.25rem'   // 4px
'sm'    => '0.5rem'    // 8px
'md'    => '1rem'      // 16px
'lg'    => '1.5rem'    // 24px
'xl'    => '2rem'      // 32px
'2xl'   => '3rem'      // 48px
'3xl'   => '4rem'      // 64px
'4xl'   => '6rem'      // 96px
```

Access via:
```php
aws_sbg_get_spacing( 'lg' )  // Returns '1.5rem'
```

### Border Radius

```javascript
'none'   => '0'
'sm'     => '0.25rem'  // 4px
'md'     => '0.5rem'   // 8px
'lg'     => '1rem'     // 16px
'xl'     => '1.5rem'   // 24px
'full'   => '9999px'   // Fully rounded
```

Access via:
```php
aws_sbg_get_border_radius( 'lg' )  // Returns '1rem'
```

---

## 🔧 How to Use in Templates

### Method 1: Tailwind Classes (Recommended for HTML)

Since all colors are loaded into Tailwind config:

```html
<!-- Using brand color -->
<button class="bg-brand text-white hover:bg-brand-light">
    Click Me
</button>

<!-- Using semantic colors -->
<div class="text-supporting-green">Success!</div>
<div class="border border-supporting-orange">Warning</div>

<!-- Using neutral colors -->
<div class="bg-dark-bg-primary text-dark-text-primary">
    Content
</div>

<!-- Using semantic shorthand -->
<div class="bg-buttons-primary-bg hover:bg-buttons-primary-bg-hover">
    Primary Button
</div>
```

### Method 2: PHP Helper Functions (For Dynamic Values)

```php
<?php
// Get a simple color
$brand_color = aws_sbg_get_color( 'brand' );
// Returns: #ad5cff

// Get with opacity
$brand_with_opacity = aws_sbg_get_color( 'brand', 0.3 );
// Returns: rgba(173, 92, 255, 0.3)

// Get semantic color
$success_color = aws_sbg_get_semantic_color( 'success' );
// Returns: #00ef88

// Get button colors
$btn_colors = aws_sbg_get_button_colors( 'primary' );
// Returns: array with 'bg', 'bg-hover', 'text', 'border'
// Usage: $btn_colors['bg'] => '#ad5cff'

// Get form colors
$form_colors = aws_sbg_get_form_colors();
// Returns: array with all form-related colors

// Get surface/card colors
$surface = aws_sbg_get_surface_colors();
// Returns: array with card background, border, shadow, etc.

// Get gradient
$gradient = aws_sbg_get_gradient( 'brand-to-dark' );
// Returns: linear-gradient(135deg, #ad5cff 0%, #8a3fc9 100%)

// Get shadow
$shadow = aws_sbg_get_shadow( 'brand-glow' );
// Returns: 0 0 20px rgba(173, 92, 255, 0.4)

// Get font family
$font = aws_sbg_get_font_family( 'heading' );
// Returns: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
?>
```

### Method 3: Inline Styles (When Dynamic)

```php
<div style="background: <?php echo esc_attr( aws_sbg_get_color( 'dark-bg-secondary' ) ); ?>; 
           color: <?php echo esc_attr( aws_sbg_get_color( 'dark-text-primary' ) ); ?>">
    Content
</div>
```

### Method 4: Generate Styles Helper

```php
<?php
$styles = aws_sbg_generate_styles( array(
    'color' => 'dark-text-primary',
    'background' => 'dark-bg-secondary',
    'border-color' => 'brand',
    'border-width' => '2px',
    'border-style' => 'solid'
) );
?>

<div <?php echo $styles; ?>>
    Content
</div>
```

### Method 5: CSS Variables

Include in header.php for CSS access:

```php
<style>
    <?php echo aws_sbg_get_css_variables(); ?>
</style>
```

Then use in CSS:
```css
.my-element {
    color: var(--color-dark-text-primary);
    background: var(--color-brand);
}
```

---

## 📝 Examples by Component

### Button Component

```html
<!-- Tailwind Approach -->
<button class="px-4 py-2 rounded-md bg-brand hover:bg-brand-light text-white font-semibold transition-colors">
    Primary Button
</button>

<!-- Dynamic Approach -->
<?php
$btn = aws_sbg_get_button_colors( 'primary' );
?>
<button style="background: <?php echo esc_attr( $btn['bg'] ); ?>; color: <?php echo esc_attr( $btn['text'] ); ?>;">
    Primary Button
</button>
```

### Form Input

```html
<!-- Tailwind Approach -->
<input type="text" class="w-full px-4 py-2 rounded-lg bg-forms-input-bg border border-forms-input-border text-forms-input-text placeholder-forms-input-placeholder focus:ring-2 focus:ring-brand focus:border-brand" placeholder="Enter text...">

<!-- PHP Helper Approach -->
<?php $forms = aws_sbg_get_form_colors(); ?>
<input type="text" 
       style="background: <?php echo esc_attr( $forms['input-bg'] ); ?>; 
              border: 1px solid <?php echo esc_attr( $forms['input-border'] ); ?>; 
              color: <?php echo esc_attr( $forms['input-text'] ); ?>"
       placeholder="Enter text...">
```

### Card Component

```html
<!-- Tailwind Approach -->
<div class="bg-dark-bg-secondary border border-surfaces-card-border rounded-lg p-6 hover:border-brand transition-colors shadow-card-subtle">
    <h3 class="text-2xl font-bold text-brand mb-3">Card Title</h3>
    <p class="text-dark-text-secondary">Card description here</p>
</div>

<!-- PHP Helper Approach -->
<?php $surface = aws_sbg_get_surface_colors(); ?>
<div style="background: <?php echo esc_attr( $surface['card-bg'] ); ?>; 
            border: 1px solid <?php echo esc_attr( $surface['card-border'] ); ?>; 
            box-shadow: <?php echo esc_attr( $surface['card-shadow'] ); ?>">
    Content here
</div>
```

### Success/Error Messages

```php
<!-- Success Banner -->
<div style="background: <?php echo esc_attr( aws_sbg_get_semantic_color( 'success' ) ); ?>/20; 
            border: 1px solid <?php echo esc_attr( aws_sbg_get_semantic_color( 'success' ) ); ?>">
    ✓ Success message!
</div>

<!-- Error Banner -->
<div style="background: <?php echo esc_attr( aws_sbg_get_semantic_color( 'error' ) ); ?>/20; 
            border: 1px solid <?php echo esc_attr( aws_sbg_get_semantic_color( 'error' ) ); ?>">
    ✗ Error message!
</div>
```

---

## 🎨 Changing Colors

### To update a color globally:

**Step 1:** Edit `config-theme.php`

```php
'brand' => '#ad5cff',  // Change this value
```

**Step 2:** All references automatically update:
- ✅ Tailwind utility classes
- ✅ All `aws_sbg_get_color()` calls
- ✅ All component-specific colors
- ✅ Buttons, forms, cards automatically updated

**No need to search & replace anywhere else!**

---

## 🔍 Z-Index Management

```php
aws_sbg_get_z_index( 'dropdown' )        // 1000
aws_sbg_get_z_index( 'sticky' )          // 1020
aws_sbg_get_z_index( 'fixed' )           // 1030
aws_sbg_get_z_index( 'modal-backdrop' )  // 1040
aws_sbg_get_z_index( 'modal' )           // 1050
aws_sbg_get_z_index( 'popover' )         // 1060
aws_sbg_get_z_index( 'tooltip' )         // 1070
```

---

## ✨ Best Practices

1. **Prefer Tailwind Classes** - Use `bg-brand` instead of inline styles when possible
2. **Use Semantic Colors** - Use `aws_sbg_get_semantic_color( 'success' )` instead of hardcoding `#00ef88`
3. **Use Helpers for Dynamic Values** - When you need opacity or dynamic values in PHP
4. **Keep it DRY** - Don't hardcode hex values in templates
5. **Test Contrast** - Brand colors have been tested for WCAG AA compliance
6. **Document Components** - When creating new components, note which colors they use

---

## 🧪 Testing Color Changes

To test a new color scheme:
1. Open `config-theme.php`
2. Change brand color: `'brand' => '#your-color'`
3. Save and refresh the browser
4. All brand elements automatically update
5. Test contrast ratios at [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

## 📚 File Reference

- **config-theme.php** - Master configuration (650+ lines, all colors & fonts)
- **helpers-colors.php** - Helper functions (400+ lines, easy access)
- **functions.php** - Updated to load helpers and generate Tailwind config
- **COLOR_REFERENCE.md** - This file (usage documentation)

---

**Last Updated:** 2026-07-22  
**Theme Version:** 1.0.0  
**Status:** ✅ Production Ready
