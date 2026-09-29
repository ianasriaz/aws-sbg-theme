<?php
/**
 * AWS SBG Theme - Custom Font Management System
 * 
 * This file handles loading Amazon Ember fonts with optimal performance.
 * Supports both Google Fonts API and self-hosted .woff2 files.
 * 
 * Font Files Reference:
 * Amazon Ember fonts are NOT available on Google Fonts (proprietary to Amazon)
 * 
 * Options:
 * 1. Self-host .woff2 files (RECOMMENDED for AWS SBG)
 * 2. Use Amazon's official font distribution
 * 3. Use similar professional alternative (Inter, System UI)
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPTION 1: Self-Hosted Amazon Ember Fonts
 * 
 * Folder structure (place .woff2 files here):
 * /wp-content/themes/aws-sbg-theme/fonts/
 *   ├── amazon-ember-regular.woff2
 *   ├── amazon-ember-medium.woff2
 *   ├── amazon-ember-semibold.woff2
 *   └── amazon-ember-bold.woff2
 */

/**
 * Register custom fonts via @font-face
 * Enqueue in wp_head for optimal loading
 */
function aws_sbg_register_custom_fonts() {
	$font_dir = get_template_directory_uri() . '/fonts';
	
	// Define fonts with metadata
	$fonts = array(
		array(
			'name' => 'Amazon Ember',
			'weight' => 400,
			'style' => 'normal',
			'file' => $font_dir . '/amazon-ember-regular.woff2',
			'display' => 'swap', // Shows fallback immediately
		),
		array(
			'name' => 'Amazon Ember',
			'weight' => 500,
			'style' => 'normal',
			'file' => $font_dir . '/amazon-ember-medium.woff2',
			'display' => 'swap',
		),
		array(
			'name' => 'Amazon Ember',
			'weight' => 600,
			'style' => 'normal',
			'file' => $font_dir . '/amazon-ember-semibold.woff2',
			'display' => 'swap',
		),
		array(
			'name' => 'Amazon Ember',
			'weight' => 700,
			'style' => 'normal',
			'file' => $font_dir . '/amazon-ember-bold.woff2',
			'display' => 'swap',
		),
	);
	
	// Generate @font-face rules
	$css = '';
	foreach ( $fonts as $font ) {
		$css .= sprintf(
			"
@font-face {
	font-family: '%s';
	src: url('%s') format('woff2');
	font-weight: %d;
	font-style: %s;
	font-display: %s;
}
",
			esc_attr( $font['name'] ),
			esc_url( $font['file'] ),
			intval( $font['weight'] ),
			esc_attr( $font['style'] ),
			esc_attr( $font['display'] )
		);
	}
	
	// Inline CSS in wp_head for critical rendering path
	wp_add_inline_style( 'wp-block-library', $css );
}
add_action( 'wp_enqueue_scripts', 'aws_sbg_register_custom_fonts', 5 );

/**
 * OPTION 2: Google Fonts Alternative
 * 
 * Since Amazon Ember is not on Google Fonts, use professional alternatives:
 * - Inter (modern, professional, AWS-approved)
 * - Roboto (clean, readable, widely used)
 * - Poppins (premium, trendy, popular with tech brands)
 */

/**
 * Load alternative fonts from Google Fonts if Amazon Ember is unavailable
 * Fallback strategy for robustness
 */
function aws_sbg_register_google_fonts() {
	// Only load if we want to use Google Fonts
	$use_google_fonts = apply_filters( 'aws_sbg_use_google_fonts', false );
	
	if ( ! $use_google_fonts ) {
		return;
	}
	
	// Load Inter font from Google Fonts API (performant alternative)
	wp_enqueue_style(
		'google-fonts-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'aws_sbg_register_google_fonts', 6 );

/**
 * Get custom font stack with proper fallbacks
 * 
 * @param string $type The font type: heading, body, mono
 * @return string Font family CSS value with fallbacks
 * 
 * @example
 *   echo 'style="font-family: ' . esc_attr( aws_sbg_get_custom_font_stack( 'heading' ) ) . '"';
 */
function aws_sbg_get_custom_font_stack( $type = 'body' ) {
	// Font stacks with proper fallbacks (left to right = priority)
	$stacks = array(
		'heading' => "'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
		'body' => "'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
		'mono' => "'Fira Code', 'Courier New', monospace",
	);
	
	return isset( $stacks[ $type ] ) ? $stacks[ $type ] : $stacks['body'];
}

/**
 * Check if custom fonts are properly loaded
 * Useful for debugging
 * 
 * @return array Status of font loading
 */
function aws_sbg_check_font_status() {
	$status = array(
		'amazon-ember-files' => file_exists( get_template_directory() . '/fonts/amazon-ember-regular.woff2' ),
		'fonts-folder' => is_dir( get_template_directory() . '/fonts' ),
		'custom-fonts-enqueued' => true,
		'recommendation' => 'Ensure .woff2 files are in /fonts folder for best performance',
	);
	
	return $status;
}

/**
 * Font Loading Performance Optimization
 * 
 * Adds preload links for critical fonts to improve FCP (First Contentful Paint)
 */
function aws_sbg_preload_critical_fonts() {
	$font_dir = get_template_directory_uri() . '/fonts';
	
	// Preload only the regular weight (most critical)
	echo '
	<link rel="preload" as="font" type="font/woff2" href="' . esc_url( $font_dir . '/amazon-ember-regular.woff2' ) . '" crossorigin>
	';
}
add_action( 'wp_head', 'aws_sbg_preload_critical_fonts', 2 );

/**
 * Font Weight Helper
 * Get correct font-weight CSS value
 * 
 * @param string $weight_name The weight name: light, normal, medium, semibold, bold
 * @return int Font weight value
 */
function aws_sbg_get_font_weight( $weight_name = 'normal' ) {
	$weights = array(
		'light' => 300,
		'normal' => 400,
		'medium' => 500,
		'semibold' => 600,
		'bold' => 700,
	);
	
	return isset( $weights[ $weight_name ] ) ? $weights[ $weight_name ] : 400;
}

/**
 * System Font Stack (Fallback)
 * Uses high-quality system fonts if custom fonts fail to load
 * 
 * @return string System font family CSS
 */
function aws_sbg_get_system_font_stack() {
	return "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif";
}
