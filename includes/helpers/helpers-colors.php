<?php
/**
 * AWS SBG Theme - Color & Font Helper Functions
 * 
 * Use these functions in templates to access theme colors and fonts with consistency.
 * All values are pulled from config-theme.php
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the master theme configuration array (cached statically)
 *
 * @return array Master theme configuration
 */
function aws_sbg_get_theme_config() {
	static $config = null;
	if ( null === $config ) {
		$file = get_template_directory() . '/config/config-theme.php';
		if ( file_exists( $file ) ) {
			$config = include $file;
		} elseif ( file_exists( get_template_directory() . '/config-theme.php' ) ) {
			$config = include get_template_directory() . '/config-theme.php';
		} else {
			$config = array();
		}
	}
	return is_array( $config ) ? $config : array();
}

/**
 * Get the official AWS SBG Logo URL
 * Prioritizes local transparent logo in assets/images/icons/ with remote CDN fallback.
 *
 * @return string Logo image URL
 */
function aws_sbg_get_logo_url() {
	$local_path = get_template_directory() . '/assets/images/icons/SBG-Logo-White-Transparent.png';
	if ( file_exists( $local_path ) ) {
		return get_template_directory_uri() . '/assets/images/icons/SBG-Logo-White-Transparent.png';
	}
	return 'https://awsairuniversity.com/wp-content/uploads/2026/09/SBG-Logo-White-Transparent.png';
}

/**
 * Get a color from the config with optional opacity
 * 
 * @param string $color_key The color key from config-theme.php
 * @param float $opacity Optional opacity (0-1)
 * @return string Hex color or rgba value
 * 
 * @example
 *   echo 'style="color: ' . esc_attr( aws_sbg_get_color( 'brand' ) ) . '"';
 *   echo 'style="background: ' . esc_attr( aws_sbg_get_color( 'dark-bg-secondary' ) ) . '"';
 */
function aws_sbg_get_color( $color_key = 'brand', $opacity = null ) {
	$config = aws_sbg_get_theme_config();
	
	// Handle nested color keys (e.g., 'buttons-primary-bg')
	if ( strpos( $color_key, '-' ) !== false ) {
		$parts = explode( '-', $color_key, 2 );
		if ( isset( $config['colors'][ $parts[0] ][ $parts[1] ] ) ) {
			$color = $config['colors'][ $parts[0] ][ $parts[1] ];
		} else {
			$color = $config['colors'][ $color_key ] ?? '#ad5cff';
		}
	} else {
		$color = $config['colors'][ $color_key ] ?? '#ad5cff';
	}
	
	// Apply opacity if provided
	if ( null !== $opacity ) {
		$color = aws_sbg_hex_to_rgba( $color, $opacity );
	}
	
	return $color;
}

/**
 * Convert hex color to RGBA
 * 
 * @param string $hex Hex color code (#RRGGBB)
 * @param float $opacity Opacity value (0-1)
 * @return string RGBA color value
 */
function aws_sbg_hex_to_rgba( $hex, $opacity = 1 ) {
	$hex = str_replace( '#', '', $hex );
	
	if ( strlen( $hex ) === 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}
	
	$rgb = array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
	
	return 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
}

/**
 * Get a semantic color (success, error, warning, info)
 * 
 * @param string $type The semantic type: success, error, warning, info
 * @return string Hex color code
 * 
 * @example
 *   echo 'style="color: ' . esc_attr( aws_sbg_get_semantic_color( 'success' ) ) . '"';
 */
function aws_sbg_get_semantic_color( $type = 'info' ) {
	$config = aws_sbg_get_theme_config();
	$valid_types = array( 'success', 'error', 'warning', 'info', 'muted' );
	
	if ( ! in_array( $type, $valid_types, true ) ) {
		$type = 'info';
	}
	
	return $config['colors']['semantic'][ $type ] ?? '#ad5cff';
}

/**
 * Get button styling (background and text color)
 * 
 * @param string $variant The button variant: primary, secondary, ghost, disabled
 * @return array Array with 'bg', 'text', 'border' keys
 * 
 * @example
 *   $button_colors = aws_sbg_get_button_colors( 'primary' );
 *   echo 'style="background: ' . esc_attr( $button_colors['bg'] ) . '; color: ' . esc_attr( $button_colors['text'] ) . '"';
 */
function aws_sbg_get_button_colors( $variant = 'primary' ) {
	$config = aws_sbg_get_theme_config();
	$buttons = $config['colors']['buttons'];
	
	switch ( $variant ) {
		case 'secondary':
			return array(
				'bg' => $buttons['secondary-bg'],
				'bg-hover' => $buttons['secondary-bg-hover'],
				'text' => $buttons['secondary-text'],
				'border' => $buttons['secondary-border'],
			);
		case 'ghost':
			return array(
				'bg' => 'transparent',
				'bg-hover' => $buttons['ghost-hover-bg'],
				'text' => $buttons['ghost-text'],
				'border' => $buttons['ghost-border'],
			);
		case 'disabled':
			return array(
				'bg' => $buttons['disabled-bg'],
				'text' => $buttons['disabled-text'],
				'border' => $buttons['disabled-text'],
			);
		case 'primary':
		default:
			return array(
				'bg' => $buttons['primary-bg'],
				'bg-hover' => $buttons['primary-bg-hover'],
				'text' => $buttons['primary-text'],
				'border' => $buttons['primary-bg'],
			);
	}
}

/**
 * Get form input styling
 * 
 * @return array Array with form styling values
 */
function aws_sbg_get_form_colors() {
	$config = aws_sbg_get_theme_config();
	return $config['colors']['forms'];
}

/**
 * Get card/surface styling
 * 
 * @return array Array with card styling values
 */
function aws_sbg_get_surface_colors() {
	$config = aws_sbg_get_theme_config();
	return $config['colors']['surfaces'];
}

/**
 * Get a gradient
 * 
 * @param string $gradient_key The gradient key: brand-to-dark, brand-to-green, dark-to-darker, overlay-dark
 * @return string CSS gradient value
 * 
 * @example
 *   echo 'style="background: ' . esc_attr( aws_sbg_get_gradient( 'brand-to-dark' ) ) . '"';
 */
function aws_sbg_get_gradient( $gradient_key = 'brand-to-dark' ) {
	$config = aws_sbg_get_theme_config();
	return $config['colors']['gradients'][ $gradient_key ] ?? $config['colors']['gradients']['brand-to-dark'];
}

/**
 * Get a shadow (for glow effects)
 * 
 * @param string $shadow_key The shadow key: brand-glow, brand-glow-soft, success-glow, card-subtle
 * @return string CSS box-shadow value
 */
function aws_sbg_get_shadow( $shadow_key = 'brand-glow-soft' ) {
	$config = aws_sbg_get_theme_config();
	return $config['colors']['shadows'][ $shadow_key ] ?? $config['colors']['shadows']['brand-glow-soft'];
}

/**
 * Get typography style
 * 
 * @param string $style_key The style key: heading-1, heading-2, body-normal, button, label, etc.
 * @return array Array with CSS properties
 */
function aws_sbg_get_text_style( $style_key = 'body-normal' ) {
	$config = aws_sbg_get_theme_config();
	return $config['typography']['styles'][ $style_key ] ?? $config['typography']['styles']['body-normal'];
}

/**
 * Get font family
 * 
 * @param string $type The font type: heading, body, mono
 * @return string Font family CSS value
 */
function aws_sbg_get_font_family( $type = 'body' ) {
	$config = aws_sbg_get_theme_config();
	return $config['typography']['fonts'][ $type ] ?? $config['typography']['fonts']['body'];
}

/**
 * Get spacing value
 * 
 * @param string $size The size key: xs, sm, md, lg, xl, 2xl, 3xl, 4xl
 * @return string Spacing value in rem
 */
function aws_sbg_get_spacing( $size = 'md' ) {
	$config = aws_sbg_get_theme_config();
	return $config['spacing'][ $size ] ?? $config['spacing']['md'];
}

/**
 * Get border radius
 * 
 * @param string $size The size key: none, sm, md, lg, xl, full
 * @return string Border radius value
 */
function aws_sbg_get_border_radius( $size = 'md' ) {
	$config = aws_sbg_get_theme_config();
	return $config['border-radius'][ $size ] ?? $config['border-radius']['md'];
}

/**
 * Get z-index value
 * 
 * @param string $layer The layer: dropdown, sticky, fixed, modal-backdrop, modal, popover, tooltip
 * @return int Z-index value
 */
function aws_sbg_get_z_index( $layer = 'modal' ) {
	$config = aws_sbg_get_theme_config();
	return $config['z-index'][ $layer ] ?? 1050;
}

/**
 * Generate inline styles from a color key
 * 
 * @param array $properties Array of CSS properties and their color keys
 * @return string Inline style attribute
 * 
 * @example
 *   $styles = aws_sbg_generate_styles( array(
 *       'color' => 'dark-text-primary',
 *       'background' => 'dark-bg-secondary',
 *       'border' => '1px solid',
 *       'border-color' => 'borders-light'
 *   ) );
 */
function aws_sbg_generate_styles( $properties = array() ) {
	$styles = array();
	
	foreach ( $properties as $css_prop => $value ) {
		if ( strpos( $value, '-' ) === 0 || strpos( $value, '#' ) === 0 ) {
			// It's a color key, resolve it
			$value = aws_sbg_get_color( $value );
		}
		
		$styles[] = esc_attr( $css_prop ) . ': ' . esc_attr( $value );
	}
	
	return 'style="' . implode( '; ', $styles ) . '"';
}

/**
 * Create a color palette CSS custom properties
 * Use this in a <style> tag or inline script to make colors available to CSS
 * 
 * @return string CSS custom properties
 * 
 * @example
 *   echo '<style>' . aws_sbg_get_css_variables() . '</style>';
 */
function aws_sbg_get_css_variables() {
	$config = aws_sbg_get_theme_config();
	$css = ":root {\n";
	
	foreach ( $config['colors'] as $name => $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $sub_name => $sub_value ) {
				$var_name = '--color-' . str_replace( '_', '-', $name ) . '-' . str_replace( '_', '-', $sub_name );
				$css .= "  $var_name: $sub_value;\n";
			}
		} else {
			$var_name = '--color-' . str_replace( '_', '-', $name );
			$css .= "  $var_name: $value;\n";
		}
	}
	
	$css .= "}\n";
	return $css;
}
