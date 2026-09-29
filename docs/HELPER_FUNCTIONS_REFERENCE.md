# AWS SBG Theme - Helper Functions Quick Reference

## 📖 Color Helper Functions (helpers-colors.php)

### Main Color Access
```php
aws_sbg_get_color( $key, $opacity = 1 )
// Returns hex color with optional opacity
// $key: 'brand', 'brand-light', 'supporting-green', 'supporting-orange', 
//       'dark-bg', 'text-primary', 'text-secondary', etc.
// Example: aws_sbg_get_color( 'brand' ) → '#ad5cff'
// Example: aws_sbg_get_color( 'brand', 0.5 ) → 'rgba(173, 92, 255, 0.5)'
```

### Semantic Colors
```php
aws_sbg_get_semantic_color( $type )
// $type: 'success', 'error', 'warning', 'info', 'muted'
// Returns color for status messages
// Example: aws_sbg_get_semantic_color( 'success' ) → '#00D084'
```

### Component Colors
```php
aws_sbg_get_button_colors( $variant )
// $variant: 'primary', 'secondary', 'ghost', 'disabled'
// Returns array: ['bg' => '...', 'text' => '...', 'hover' => '...']
// Example: $colors = aws_sbg_get_button_colors( 'primary' );

aws_sbg_get_form_colors()
// Returns array: ['bg' => '...', 'border' => '...', 'focus' => '...']

aws_sbg_get_surface_colors()
// Returns array: ['bg' => '...', 'border' => '...', 'shadow' => '...']
```

### Gradients & Effects
```php
aws_sbg_get_gradient( $key )
// $key: 'hero-main', 'hero-secondary', 'button-primary', etc.
// Example: aws_sbg_get_gradient( 'hero-main' ) → 'linear-gradient(...)'

aws_sbg_get_shadow( $key )
// $key: 'sm', 'md', 'lg', 'glow-brand', 'glow-success'
// Example: aws_sbg_get_shadow( 'glow-brand' ) → 'box-shadow: 0 0 ...'
```

### Typography
```php
aws_sbg_get_text_style( $key )
// $key: 'heading-1', 'heading-2', 'heading-3', 'body-normal', 'body-small', etc.
// Returns array: ['font-size' => '...', 'font-weight' => '...', 'line-height' => '...']
// Example: $style = aws_sbg_get_text_style( 'heading-1' );

aws_sbg_get_font_family( $type )
// $type: 'heading', 'body', 'mono'
// Returns font stack (includes fallbacks)

aws_sbg_get_custom_font_stack( $type ) // See fonts section below
```

### Spacing & Layout
```php
aws_sbg_get_spacing( $size )
// $size: 'xs' (0.25rem), 'sm' (0.5rem), 'md' (1rem), 'lg' (1.5rem), 
//        'xl' (2rem), '2xl' (3rem), '3xl' (4rem), '4xl' (6rem)
// Example: aws_sbg_get_spacing( 'lg' ) → '1.5rem'

aws_sbg_get_border_radius( $size )
// $size: 'none', 'sm', 'md', 'lg', 'full'
// Example: aws_sbg_get_border_radius( 'lg' ) → '0.5rem'

aws_sbg_get_z_index( $layer )
// $layer: 'dropdown' (1000), 'sticky' (1010), 'fixed' (1020), 
//         'modal-backdrop' (1030), 'modal' (1040), 'popover' (1050), 'tooltip' (1070)
// Example: aws_sbg_get_z_index( 'modal' ) → 1040
```

### Utility Functions
```php
aws_sbg_generate_styles( $properties )
// Generates inline style attribute from array
// Example: aws_sbg_generate_styles( ['color' => '#fff', 'font-size' => '1rem'] )
// Returns: 'color: #fff; font-size: 1rem;'

aws_sbg_get_css_variables()
// Returns all colors as CSS custom properties
// Example: --brand: #ad5cff; --supporting-green: #00ef88; etc.

hex_to_rgba( $hex, $opacity = 1 )
// Converts hex to rgba
// Example: hex_to_rgba( '#ad5cff', 0.5 ) → 'rgba(173, 92, 255, 0.5)'
```

---

## 🎨 Font Helper Functions (helpers-fonts.php)

### Font Stack Access
```php
aws_sbg_get_custom_font_stack( $type = 'body' )
// Returns font stack with Amazon Ember + fallbacks
// $type: 'heading', 'body', 'mono'
// Example: aws_sbg_get_custom_font_stack( 'heading' )
// Returns: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif
```

### Font Weight Access
```php
aws_sbg_get_font_weight( $name )
// $name: 'light' (300), 'normal' (400), 'medium' (500), 'semibold' (600), 'bold' (700)
// Example: aws_sbg_get_font_weight( 'bold' ) → 700
```

### System Fonts
```php
aws_sbg_get_system_font_stack()
// Returns pure system fonts (no custom fonts)
// Example: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif
```

### Font Loading & Debug
```php
aws_sbg_register_custom_fonts()
// Registers @font-face rules for Amazon Ember
// Called automatically in wp_head
// No parameters needed

aws_sbg_register_google_fonts()
// Registers Google Fonts (Inter) if enabled
// Called automatically if filter set to true
// No parameters needed

aws_sbg_preload_critical_fonts()
// Preloads regular font weight in wp_head
// Called automatically for performance
// No parameters needed

aws_sbg_check_font_status()
// Returns array with font loading status for debugging
// Example: aws_sbg_check_font_status()
// Returns: [
//   'amazon-ember-files' => bool,
//   'fonts-folder' => bool,
//   'custom-fonts-enqueued' => bool,
//   'recommendation' => string
// ]
```

---

## 💡 Usage Examples

### Example 1: Style a Button
```php
<?php
$button_colors = aws_sbg_get_button_colors( 'primary' );
$spacing = aws_sbg_get_spacing( 'md' );
$radius = aws_sbg_get_border_radius( 'lg' );

echo '<button style="
    background-color: ' . esc_attr( $button_colors['bg'] ) . ';
    color: ' . esc_attr( $button_colors['text'] ) . ';
    padding: ' . esc_attr( $spacing ) . ';
    border-radius: ' . esc_attr( $radius ) . ';
">';
echo 'Click Me';
echo '</button>';
?>
```

### Example 2: Style a Heading
```php
<?php
$text_style = aws_sbg_get_text_style( 'heading-1' );
$font_stack = aws_sbg_get_custom_font_stack( 'heading' );
$color = aws_sbg_get_color( 'brand' );

echo '<h1 style="
    font-family: ' . esc_attr( $font_stack ) . ';
    font-size: ' . esc_attr( $text_style['font-size'] ) . ';
    font-weight: ' . esc_attr( $text_style['font-weight'] ) . ';
    color: ' . esc_attr( $color ) . ';
">';
echo 'Welcome to AWS SBG';
echo '</h1>';
?>
```

### Example 3: Success Message
```php
<?php
$success_color = aws_sbg_get_semantic_color( 'success' );
$padding = aws_sbg_get_spacing( 'lg' );
$radius = aws_sbg_get_border_radius( 'md' );

echo '<div style="
    background-color: ' . esc_attr( $success_color ) . ';
    padding: ' . esc_attr( $padding ) . ';
    border-radius: ' . esc_attr( $radius ) . ';
    color: white;
">';
echo 'Success! Your form has been submitted.';
echo '</div>';
?>
```

### Example 4: Form Input
```php
<?php
$form_colors = aws_sbg_get_form_colors();
$spacing = aws_sbg_get_spacing( 'md' );
$font = aws_sbg_get_custom_font_stack( 'body' );

echo '<input type="text" style="
    background-color: ' . esc_attr( $form_colors['bg'] ) . ';
    border: 1px solid ' . esc_attr( $form_colors['border'] ) . ';
    padding: ' . esc_attr( $spacing ) . ';
    font-family: ' . esc_attr( $font ) . ';
">';
?>
```

### Example 5: Tailwind Classes (Automatic)
```html
<!-- All using colors from config-theme.php -->
<button class="bg-brand hover:bg-brand-light text-white px-lg py-md rounded-lg">
    Primary Button
</button>

<div class="bg-supporting-green text-white p-lg rounded-md">
    Success Message
</div>

<input class="bg-form-input border border-form-border focus:border-brand p-md rounded-md">

<h1 class="text-4xl font-bold text-brand">
    Main Heading (uses Amazon Ember font)
</h1>
```

---

## 🔗 Related Files

- **Color Configuration**: `config-theme.php` (all color definitions)
- **Color Functions**: `helpers-colors.php` (20+ helper functions)
- **Font Functions**: `helpers-fonts.php` (6+ font helpers)
- **Color Reference**: `COLOR_REFERENCE.md` (comprehensive usage guide)
- **Font Reference**: `FONTS_IMPLEMENTATION.md` (comprehensive font setup)

---

## 📝 Summary

| Category | Functions | Status |
|----------|-----------|--------|
| **Colors** | 20+ functions | ✅ Ready to use |
| **Fonts** | 6+ functions | ✅ Ready to use |
| **Spacing** | 2 functions | ✅ Ready to use |
| **Z-index** | 1 function | ✅ Ready to use |
| **Utility** | 3 functions | ✅ Ready to use |
| **Debug** | 1 function | ✅ Ready to use |

**All functions are documented with @example tags in the source files.**

---

Last Updated: 2026-07-22  
Status: ✅ Complete & Production-Ready
