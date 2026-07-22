<?php
/**
 * AWS Student Builder Group Theme Functions
 * 
 * This file handles theme setup, script enqueuing, custom post types,
 * ACF field registration, and form submission handlers.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load color and font helper functions
require_once get_template_directory() . '/helpers-colors.php';
require_once get_template_directory() . '/helpers-fonts.php';

/**
 * ============================================================================
 * 1. THEME SETUP & SUPPORT
 * ============================================================================
 */

function aws_sbg_theme_setup() {
	// Add theme support for essential features
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );

	// Register navigation menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'aws-sbg-theme' ),
	) );
}
add_action( 'after_setup_theme', 'aws_sbg_theme_setup' );

/**
 * ============================================================================
 * 2. ENQUEUE SCRIPTS & STYLES
 * ============================================================================
 */

function aws_sbg_enqueue_scripts() {
	// Enqueue theme stylesheet (style.css)
	wp_enqueue_style(
		'aws-sbg-theme-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' ),
		'all'
	);

	// Enqueue Tailwind CSS v4 via CDN with fallback
	wp_enqueue_style(
		'tailwind-css',
		'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.0.0',
		array(),
		'4.0.0',
		'all'
	);

	// Alpine.js (must load before Tailwind config script)
	wp_enqueue_script(
		'alpinejs',
		'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
		array(),
		'3.14.1',
		array(
			'strategy' => 'defer',
		)
	);

	// Initialize Tailwind with theme configuration
	wp_add_inline_script( 'alpinejs', aws_sbg_get_tailwind_config(), 'after' );

	// Add custom CSS reset and utilities
	wp_add_inline_style( 'tailwind-css', aws_sbg_get_custom_styles() );
}
add_action( 'wp_enqueue_scripts', 'aws_sbg_enqueue_scripts', 5 );

/**
 * Generate Tailwind configuration from master config
 * 
 * @return string JavaScript configuration
 */
function aws_sbg_get_tailwind_config() {
	$config = include get_template_directory() . '/config-theme.php';
	
	// Build colors object
	$colors_js = array();
	foreach ( $config['colors'] as $name => $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $sub_name => $sub_value ) {
				if ( ! is_array( $sub_value ) ) {
					$colors_js[ "{$name}-{$sub_name}" ] = $sub_value;
				}
			}
		} else {
			$colors_js[ $name ] = $value;
		}
	}

	// Build colors JSON
	$colors_json = json_encode( $colors_js, JSON_FORCE_OBJECT );
	
	// Return Tailwind configuration script
	return "
		if (typeof tailwind !== 'undefined') {
			tailwind.config = {
				theme: {
					extend: {
						colors: {$colors_json},
						boxShadow: {
							'glow': '0 0 20px rgba(173, 92, 255, 0.4)',
							'glow-soft': '0 0 15px rgba(173, 92, 255, 0.2)',
							'glow-success': '0 0 20px rgba(0, 239, 136, 0.3)',
							'card-subtle': '0 4px 15px rgba(0, 0, 0, 0.3)'
						},
						fontFamily: {
							'sans': ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
							'mono': ['Fira Code', 'Courier New', 'monospace']
						}
					}
				}
			};
		}
	";
}

/**
 * Get custom CSS for theme
 * 
 * @return string CSS rules
 */
function aws_sbg_get_custom_styles() {
	return "
		:root {
			--brand-color: #ad5cff;
			--green-color: #00ef88;
			--orange-color: #ffa000;
		}
		
		html {
			scroll-behavior: smooth;
		}
		
		body {
			background-color: #0a0a0a;
			color: #ffffff;
		}
		
		/* Smooth transitions */
		* {
			@apply transition-colors duration-300;
		}
		
		/* Link styling */
		a {
			color: var(--brand-color);
		}
		
		/* Scrollbar styling */
		::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}
		
		::-webkit-scrollbar-track {
			background: #1a1a1a;
		}
		
		::-webkit-scrollbar-thumb {
			background: #ad5cff;
			border-radius: 4px;
		}
		
		::-webkit-scrollbar-thumb:hover {
			background: #00ef88;
		}
		
		/* Button defaults */
		button, [role=\"button\"] {
			cursor: pointer;
		}
		
		/* Focus styles for accessibility */
		*:focus-visible {
			outline: 2px solid var(--brand-color);
			outline-offset: 2px;
		}
	";
}

/**
 * ============================================================================
 * 3. REGISTER CUSTOM POST TYPES
 * ============================================================================
 * 
 * Note: Color and font helper functions are in helpers-colors.php and
 * helpers-fonts.php respectively (loaded via require_once above)
 */

function aws_sbg_register_post_types() {
	// Register RSVP Post Type
	if ( function_exists( 'acf_register_post_type' ) ) {
		acf_register_post_type( array(
			'post_type' => 'rsvp',
			'description' => 'Event Registration Submissions',
			'public' => false,
			'show_in_ui' => true,
			'show_in_rest' => false,
			'supports' => array( 'title' ),
			'menu_position' => 20,
			'menu_icon' => 'dashicons-clipboard',
			'has_archive' => false,
			'can_export' => true,
		) );

		// Register Contact Submission Post Type
		acf_register_post_type( array(
			'post_type' => 'contact_submission',
			'description' => 'Contact Form Submissions',
			'public' => false,
			'show_in_ui' => true,
			'show_in_rest' => false,
			'supports' => array( 'title' ),
			'menu_position' => 21,
			'menu_icon' => 'dashicons-email',
			'has_archive' => false,
			'can_export' => true,
		) );
	}
}
add_action( 'init', 'aws_sbg_register_post_types' );

/**
 * ============================================================================
 * 4. REGISTER ACF FIELDS
 * ============================================================================
 */

function aws_sbg_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// RSVP Field Group
	acf_add_local_field_group( array(
		'key' => 'group_aircert_rsvp',
		'title' => 'AirCert RSVP Details',
		'fields' => array(
			array(
				'key' => 'field_aircert_email',
				'label' => 'Email',
				'name' => 'email',
				'type' => 'email',
				'required' => 1,
			),
			array(
				'key' => 'field_aircert_whatsapp',
				'label' => 'WhatsApp Number',
				'name' => 'whatsapp_number',
				'type' => 'text',
				'required' => 1,
			),
			array(
				'key' => 'field_aircert_status',
				'label' => 'Attendee Status',
				'name' => 'attendee_status',
				'type' => 'radio',
				'choices' => array(
					'student' => 'Student',
					'professional' => 'Professional',
				),
				'required' => 1,
			),
			array(
				'key' => 'field_aircert_institute',
				'label' => 'Institute/Organization Name',
				'name' => 'institute_organization',
				'type' => 'text',
				'required' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'rsvp',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
	) );

	// Contact Submission Field Group
	acf_add_local_field_group( array(
		'key' => 'group_contact_submission',
		'title' => 'Contact Details',
		'fields' => array(
			array(
				'key' => 'field_contact_email',
				'label' => 'Email',
				'name' => 'email',
				'type' => 'email',
				'required' => 1,
			),
			array(
				'key' => 'field_contact_message',
				'label' => 'Message',
				'name' => 'message',
				'type' => 'textarea',
				'rows' => 5,
				'required' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'contact_submission',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
	) );
}
add_action( 'acf/init', 'aws_sbg_register_acf_fields' );

/**
 * ============================================================================
 * 5. FORM SUBMISSION HANDLERS
 * ============================================================================
 */

/**
 * Handle AirCert RSVP Form Submission
 */
function aws_sbg_handle_aircert_rsvp() {
	// Verify nonce
	if ( ! isset( $_POST['aircert_rsvp_nonce'] ) || ! wp_verify_nonce( $_POST['aircert_rsvp_nonce'], 'aircert_rsvp_action' ) ) {
		wp_redirect( home_url( '/aircert/?status=error' ) );
		exit;
	}

	// Sanitize input fields
	$full_name = isset( $_POST['full_name'] ) ? sanitize_text_field( $_POST['full_name'] ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$whatsapp = isset( $_POST['whatsapp'] ) ? sanitize_text_field( $_POST['whatsapp'] ) : '';
	$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
	$institute = isset( $_POST['institute'] ) ? sanitize_text_field( $_POST['institute'] ) : '';

	// Validate required fields
	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $status ) || empty( $institute ) ) {
		wp_redirect( home_url( '/aircert/?status=error' ) );
		exit;
	}

	// Validate email
	if ( ! is_email( $email ) ) {
		wp_redirect( home_url( '/aircert/?status=error' ) );
		exit;
	}

	// Create the RSVP post
	$post_id = wp_insert_post( array(
		'post_type' => 'rsvp',
		'post_status' => 'publish',
		'post_title' => wp_kses_post( $full_name . ' - AirCert' ),
	) );

	if ( ! is_wp_error( $post_id ) ) {
		// Save meta fields using ACF
		update_field( 'email', $email, $post_id );
		update_field( 'whatsapp_number', $whatsapp, $post_id );
		update_field( 'attendee_status', $status, $post_id );
		update_field( 'institute_organization', $institute, $post_id );

		// Optional: Send email notification to admin
		aws_sbg_send_aircert_notification( $full_name, $email, $whatsapp, $status, $institute );
	}

	// Redirect with success status
	wp_redirect( home_url( '/aircert/?status=success' ) );
	exit;
}
add_action( 'admin_post_submit_aircert_rsvp', 'aws_sbg_handle_aircert_rsvp' );

/**
 * Handle Contact Form Submission
 */
function aws_sbg_handle_contact_submission() {
	// Verify nonce
	if ( ! isset( $_POST['contact_nonce'] ) || ! wp_verify_nonce( $_POST['contact_nonce'], 'contact_action' ) ) {
		wp_redirect( home_url( '/contact/?status=error' ) );
		exit;
	}

	// Sanitize input fields
	$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

	// Validate required fields
	if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
		wp_redirect( home_url( '/contact/?status=error' ) );
		exit;
	}

	// Validate email
	if ( ! is_email( $email ) ) {
		wp_redirect( home_url( '/contact/?status=error' ) );
		exit;
	}

	// Create the contact submission post
	$post_id = wp_insert_post( array(
		'post_type' => 'contact_submission',
		'post_status' => 'publish',
		'post_title' => wp_kses_post( $name . ' - ' . $subject ),
	) );

	if ( ! is_wp_error( $post_id ) ) {
		// Save meta fields using ACF
		update_field( 'email', $email, $post_id );
		update_field( 'message', $message, $post_id );

		// Optional: Send email notification to admin
		aws_sbg_send_contact_notification( $name, $email, $subject, $message );
	}

	// Redirect with success status
	wp_redirect( home_url( '/contact/?status=success' ) );
	exit;
}
add_action( 'admin_post_submit_contact', 'aws_sbg_handle_contact_submission' );

/**
 * ============================================================================
 * 6. EMAIL NOTIFICATIONS
 * ============================================================================
 */

/**
 * Send AirCert RSVP notification email to admin
 */
function aws_sbg_send_aircert_notification( $name, $email, $whatsapp, $status, $institute ) {
	$admin_email = get_option( 'admin_email' );
	$subject = 'New AirCert RSVP Registration: ' . $name;
	$message = "
		<h2>New AirCert RSVP Registration</h2>
		<p><strong>Name:</strong> {$name}</p>
		<p><strong>Email:</strong> {$email}</p>
		<p><strong>WhatsApp:</strong> {$whatsapp}</p>
		<p><strong>Status:</strong> {$status}</p>
		<p><strong>Institute/Organization:</strong> {$institute}</p>
	";

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	wp_mail( $admin_email, $subject, $message, $headers );
}

/**
 * Send contact form notification email to admin
 */
function aws_sbg_send_contact_notification( $name, $email, $subject, $message ) {
	$admin_email = get_option( 'admin_email' );
	$email_subject = 'New Contact Form Submission: ' . $subject;
	$email_message = "
		<h2>New Contact Form Submission</h2>
		<p><strong>Name:</strong> {$name}</p>
		<p><strong>Email:</strong> {$email}</p>
		<p><strong>Subject:</strong> {$subject}</p>
		<p><strong>Message:</strong></p>
		<p>" . nl2br( $message ) . "</p>
	";

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	wp_mail( $admin_email, $email_subject, $email_message, $headers );
}

/**
 * ============================================================================
 * 7. DEBUG & DIAGNOSTICS
 * ============================================================================
 */

/**
 * Display theme debug information (only for admins, temporary)
 * Add to template temporarily: <?php aws_sbg_debug_info(); ?>
 */
function aws_sbg_debug_info() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$debug = array(
		'Theme Active' => wp_get_theme()->get( 'Name' ),
		'Theme Version' => wp_get_theme()->get( 'Version' ),
		'WordPress Version' => get_bloginfo( 'version' ),
		'PHP Version' => phpversion(),
		'Tailwind CDN' => '✓ Enqueued',
		'Alpine.js CDN' => '✓ Enqueued',
		'Config File' => file_exists( get_template_directory() . '/config-theme.php' ) ? '✓ Found' : '✗ Missing',
		'Helpers Colors' => file_exists( get_template_directory() . '/helpers-colors.php' ) ? '✓ Found' : '✗ Missing',
		'Helpers Fonts' => file_exists( get_template_directory() . '/helpers-fonts.php' ) ? '✓ Found' : '✗ Missing',
	);

	echo '<!-- AWS SBG Theme Debug Info -->';
	echo '<pre style="background: #000; color: #0f0; padding: 15px; margin: 20px; font-size: 12px; border: 1px solid #333; overflow-x: auto;">';
	foreach ( $debug as $key => $value ) {
		echo $key . ': ' . $value . "\n";
	}
	echo '</pre>';
}

/**
 * ============================================================================
 * 8. UTILITY FUNCTIONS
 * ============================================================================
 */

/**
 * Get the mobile menu state
 */
function aws_sbg_is_mobile_menu_open() {
	return false;
}
