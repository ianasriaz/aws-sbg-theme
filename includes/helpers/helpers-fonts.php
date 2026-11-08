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
 * OPTION 1: Self-Hosted Amazon Ember Fonts (from assets/design-kit/fonts/)
 */

/**
 * Register custom fonts via @font-face
 * Enqueue in wp_head for optimal loading
 */
function aws_sbg_register_custom_fonts() {
	$font_dir_path = get_template_directory() . '/assets/design-kit/fonts';
	$font_dir_uri  = get_template_directory_uri() . '/assets/design-kit/fonts';

	if ( ! is_dir( $font_dir_path ) ) {
		return;
	}

	$fonts = array(
		// Amazon Ember Display (Brand Display Font)
		array(
			'family' => 'Amazon Ember Display',
			'weight' => 300,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Lt.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Lt.ttf',
		),
		array(
			'family' => 'Amazon Ember Display',
			'weight' => 500,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Md.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Md.ttf',
		),
		array(
			'family' => 'Amazon Ember Display',
			'weight' => 700,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Bd.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Bd.ttf',
		),
		// Amazon Ember Mono (Technical Code & Details)
		array(
			'family' => 'Amazon Ember Mono',
			'weight' => 400,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberMono_Rg.ttf',
			'path'   => $font_dir_path . '/AmazonEmberMono_Rg.ttf',
		),
		array(
			'family' => 'Amazon Ember Mono',
			'weight' => 700,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberMono_Bd.ttf',
			'path'   => $font_dir_path . '/AmazonEmberMono_Bd.ttf',
		),
		// Aliases for 'Amazon Ember' family so global body/headings resolve locally
		array(
			'family' => 'Amazon Ember',
			'weight' => 300,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Lt.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Lt.ttf',
		),
		array(
			'family' => 'Amazon Ember',
			'weight' => 400,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Md.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Md.ttf',
		),
		array(
			'family' => 'Amazon Ember',
			'weight' => 500,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Md.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Md.ttf',
		),
		array(
			'family' => 'Amazon Ember',
			'weight' => 700,
			'style'  => 'normal',
			'file'   => $font_dir_uri . '/AmazonEmberDisplay_Bd.ttf',
			'path'   => $font_dir_path . '/AmazonEmberDisplay_Bd.ttf',
		),
	);

	// Generate @font-face rules
	$css = '';
	foreach ( $fonts as $font ) {
		if ( ! file_exists( $font['path'] ) ) {
			continue;
		}
		$css .= sprintf(
			"@font-face { font-family: '%s'; src: url('%s') format('truetype'); font-weight: %d; font-style: %s; font-display: swap; }\n",
			esc_attr( $font['family'] ),
			esc_url( $font['file'] ),
			intval( $font['weight'] ),
			esc_attr( $font['style'] )
		);
	}

	if ( empty( $css ) ) {
		return;
	}

	// Inline CSS in wp_head for critical rendering path
	if ( wp_style_is( 'aws-sbg-theme-style', 'registered' ) || wp_style_is( 'aws-sbg-theme-style', 'enqueued' ) ) {
		wp_add_inline_style( 'aws-sbg-theme-style', $css );
	} elseif ( wp_style_is( 'wp-block-library', 'registered' ) ) {
		wp_add_inline_style( 'wp-block-library', $css );
	} else {
		add_action( 'wp_head', function() use ( $css ) {
			echo '<style id="aws-sbg-theme-fonts">' . $css . '</style>';
		}, 1 );
	}
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
	$font_dir_path = get_template_directory() . '/assets/design-kit/fonts';
	$status = array(
		'amazon-ember-files'    => file_exists( $font_dir_path . '/AmazonEmberDisplay_Md.ttf' ),
		'fonts-folder'          => is_dir( $font_dir_path ),
		'custom-fonts-enqueued' => true,
		'recommendation'        => 'Amazon Ember Display and Mono fonts hosted in assets/design-kit/fonts/',
	);
	
	return $status;
}

/**
 * Font Loading Performance Optimization
 * 
 * Adds preload links for critical fonts to improve FCP (First Contentful Paint)
 */
function aws_sbg_preload_critical_fonts() {
	$font_file_path = get_template_directory() . '/assets/design-kit/fonts/AmazonEmberDisplay_Md.ttf';
	if ( ! file_exists( $font_file_path ) ) {
		return;
	}

	$font_file_uri = get_template_directory_uri() . '/assets/design-kit/fonts/AmazonEmberDisplay_Md.ttf';
	echo '<link rel="preload" as="font" type="font/ttf" href="' . esc_url( $font_file_uri ) . '" crossorigin>' . "\n";
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
