<?php
/**
 * AWS Student Builder Group - Master Theme Configuration
 * 
 * This file serves as the single source of truth for all theme colors and fonts.
 * Change values here and they automatically propagate throughout the theme.
 * 
 * Usage in functions.php:
 *   $theme_config = include get_template_directory() . '/config-theme.php';
 *   
 * Usage in templates:
 *   $colors = include get_template_directory() . '/config-theme.php';
 *   echo 'style="color: ' . esc_attr( $colors['brand'] ) . '"';
 */

return array(
	/**
	 * =========================================================================
	 * COLOR PALETTE - CORE COLORS
	 * =========================================================================
	 */
	'colors' => array(
		// Primary Brand Color - Use for main CTAs, primary actions, brand identity
		'brand' => '#ad5cff',
		'brand-light' => '#c97bff',
		'brand-dark' => '#8a3fc9',

		// Supporting Green - Use for success states, positive actions, confirmations
		'supporting-green' => '#00ef88',
		'supporting-green-light' => '#4dff9f',
		'supporting-green-dark' => '#00c96f',

		// Supporting Orange - Use for warnings, secondary CTAs, highlights
		'supporting-orange' => '#ffa000',
		'supporting-orange-light' => '#ffb84d',
		'supporting-orange-dark' => '#cc8200',

		// Neutral - Dark Backgrounds & Light Text (Premium Dark Theme)
		'dark-bg-primary' => '#0a0a0a',    // Darkest background
		'dark-bg-secondary' => '#1a1a1a',  // Cards, elevated surfaces
		'dark-bg-tertiary' => '#2a2a2a',   // Hover states, subtle backgrounds

		'dark-text-primary' => '#ffffff',  // Main text
		'dark-text-secondary' => '#d0d0d0', // Secondary text
		'dark-text-tertiary' => '#999999',  // Placeholder, muted text

		// Semantic Colors - For consistent messaging across the app
		'semantic' => array(
			'success' => '#00ef88',      // Positive actions, confirmations
			'error' => '#ff4757',        // Errors, destructive actions
			'warning' => '#ffa000',      // Warnings, cautions
			'info' => '#ad5cff',         // Information, notifications
			'muted' => '#666666',        // Disabled, inactive elements
		),

		// Component-Specific Colors
		'buttons' => array(
			'primary-bg' => '#ad5cff',
			'primary-bg-hover' => '#c97bff',
			'primary-bg-active' => '#8a3fc9',
			'primary-text' => '#ffffff',

			'secondary-bg' => '#2a2a2a',
			'secondary-bg-hover' => '#3a3a3a',
			'secondary-border' => '#ad5cff',
			'secondary-text' => '#ad5cff',

			'ghost-border' => '#666666',
			'ghost-text' => '#d0d0d0',
			'ghost-hover-bg' => '#1a1a1a',

			'disabled-bg' => '#2a2a2a',
			'disabled-text' => '#666666',
		),

		// Form Elements
		'forms' => array(
			'input-bg' => '#1a1a1a',
			'input-border' => '#3a3a3a',
			'input-border-focus' => '#ad5cff',
			'input-text' => '#ffffff',
			'input-placeholder' => '#999999',
			'input-bg-disabled' => '#0a0a0a',
		),

		// Cards & Surfaces
		'surfaces' => array(
			'card-bg' => '#1a1a1a',
			'card-border' => '#2a2a2a',
			'card-hover-bg' => '#2a2a2a',
			'card-shadow' => 'rgba(173, 92, 255, 0.15)',
		),

		// Borders & Dividers
		'borders' => array(
			'light' => '#3a3a3a',
			'medium' => '#2a2a2a',
			'dark' => '#1a1a1a',
			'brand' => '#ad5cff',
		),

		// Gradients (for hero sections, CTAs, etc.)
		'gradients' => array(
			'brand-to-dark' => 'linear-gradient(135deg, #ad5cff 0%, #8a3fc9 100%)',
			'brand-to-green' => 'linear-gradient(135deg, #ad5cff 0%, #00ef88 100%)',
			'dark-to-darker' => 'linear-gradient(180deg, #1a1a1a 0%, #0a0a0a 100%)',
			'overlay-dark' => 'linear-gradient(180deg, rgba(10, 10, 10, 0.8) 0%, rgba(10, 10, 10, 0.95) 100%)',
		),

		// Shadow Colors (for depth and elevation)
		'shadows' => array(
			'brand-glow' => '0 0 20px rgba(173, 92, 255, 0.4)',
			'brand-glow-soft' => '0 0 15px rgba(173, 92, 255, 0.2)',
			'success-glow' => '0 0 20px rgba(0, 239, 136, 0.3)',
			'card-subtle' => '0 4px 15px rgba(0, 0, 0, 0.3)',
		),
	),

	/**
	 * =========================================================================
	 * TYPOGRAPHY - FONTS & TEXT STYLES
	 * =========================================================================
	 * 
	 * IMPORTANT: Font families are managed dynamically via helpers-fonts.php
	 * This provides:
	 * - Support for custom Amazon Ember fonts
	 * - Automatic fallback to system fonts
	 * - Performance optimization (preload, font-display)
	 * - Seamless switching between font sources
	 */
	'typography' => array(
		// Font Families - Use helpers-fonts.php functions for dynamic loading
		// aws_sbg_get_custom_font_stack( 'heading' ) - Returns Amazon Ember + fallbacks
		// aws_sbg_get_custom_font_stack( 'body' )    - Returns Amazon Ember + fallbacks
		// aws_sbg_get_system_font_stack()            - Returns system fonts fallback
		'fonts' => array(
			'heading' => "Use aws_sbg_get_custom_font_stack( 'heading' )",
			'body' => "Use aws_sbg_get_custom_font_stack( 'body' )",
			'mono' => "'Fira Code', 'Courier New', monospace",
		),

		// Font Sizes
		'sizes' => array(
			'xs' => '0.75rem',      // 12px
			'sm' => '0.875rem',     // 14px
			'base' => '1rem',       // 16px
			'lg' => '1.125rem',     // 18px
			'xl' => '1.25rem',      // 20px
			'2xl' => '1.5rem',      // 24px
			'3xl' => '1.875rem',    // 30px
			'4xl' => '2.25rem',     // 36px
			'5xl' => '3rem',        // 48px
			'6xl' => '3.75rem',     // 60px
		),

		// Font Weights
		'weights' => array(
			'light' => 300,
			'normal' => 400,
			'medium' => 500,
			'semibold' => 600,
			'bold' => 700,
			'extrabold' => 800,
		),

		// Line Heights
		'line-heights' => array(
			'tight' => 1.2,
			'normal' => 1.5,
			'relaxed' => 1.75,
			'loose' => 2,
		),

		// Letter Spacing
		'letter-spacing' => array(
			'tight' => '-0.02em',
			'normal' => '0em',
			'wide' => '0.02em',
			'wider' => '0.05em',
		),

		// Text Styles (Pre-composed combinations for consistency)
		'styles' => array(
			'heading-1' => array(
				'font-size' => '3.75rem',
				'font-weight' => 700,
				'line-height' => 1.2,
				'letter-spacing' => '-0.02em',
			),
			'heading-2' => array(
				'font-size' => '2.25rem',
				'font-weight' => 700,
				'line-height' => 1.2,
				'letter-spacing' => '-0.01em',
			),
			'heading-3' => array(
				'font-size' => '1.875rem',
				'font-weight' => 600,
				'line-height' => 1.3,
				'letter-spacing' => '0em',
			),
			'heading-4' => array(
				'font-size' => '1.5rem',
				'font-weight' => 600,
				'line-height' => 1.3,
				'letter-spacing' => '0em',
			),
			'body-large' => array(
				'font-size' => '1.125rem',
				'font-weight' => 400,
				'line-height' => 1.75,
				'letter-spacing' => '0em',
			),
			'body-normal' => array(
				'font-size' => '1rem',
				'font-weight' => 400,
				'line-height' => 1.5,
				'letter-spacing' => '0em',
			),
			'body-small' => array(
				'font-size' => '0.875rem',
				'font-weight' => 400,
				'line-height' => 1.5,
				'letter-spacing' => '0.01em',
			),
			'label' => array(
				'font-size' => '0.875rem',
				'font-weight' => 600,
				'line-height' => 1.5,
				'letter-spacing' => '0.02em',
				'text-transform' => 'uppercase',
			),
			'button' => array(
				'font-size' => '1rem',
				'font-weight' => 600,
				'line-height' => 1.5,
				'letter-spacing' => '0.01em',
			),
		),
	),

	/**
	 * =========================================================================
	 * SPACING - CONSISTENT RHYTHM
	 * =========================================================================
	 */
	'spacing' => array(
		'xs' => '0.25rem',  // 4px
		'sm' => '0.5rem',   // 8px
		'md' => '1rem',     // 16px
		'lg' => '1.5rem',   // 24px
		'xl' => '2rem',     // 32px
		'2xl' => '3rem',    // 48px
		'3xl' => '4rem',    // 64px
		'4xl' => '6rem',    // 96px
	),

	/**
	 * =========================================================================
	 * BORDER RADIUS - FOR ROUNDED CORNERS
	 * =========================================================================
	 */
	'border-radius' => array(
		'none' => '0',
		'sm' => '0.25rem',   // 4px
		'md' => '0.5rem',    // 8px
		'lg' => '1rem',      // 16px
		'xl' => '1.5rem',    // 24px
		'full' => '9999px',
	),

	/**
	 * =========================================================================
	 * TRANSITIONS & ANIMATIONS
	 * =========================================================================
	 */
	'transitions' => array(
		'fast' => '150ms',
		'normal' => '300ms',
		'slow' => '500ms',
		'easing' => 'cubic-bezier(0.4, 0, 0.2, 1)',
	),

	/**
	 * =========================================================================
	 * Z-INDEX LAYERS - FOR STACKING CONTEXT
	 * =========================================================================
	 */
	'z-index' => array(
		'dropdown' => 1000,
		'sticky' => 1020,
		'fixed' => 1030,
		'modal-backdrop' => 1040,
		'modal' => 1050,
		'popover' => 1060,
		'tooltip' => 1070,
	),
);
