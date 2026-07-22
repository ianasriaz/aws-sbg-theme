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
	// Enqueue Tailwind CSS v4 via CDN
	wp_enqueue_style(
		'tailwind-css',
		'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4',
		array(),
		null
	);

	// Enqueue Alpine.js v3 via CDN
	wp_enqueue_script(
		'alpine-js',
		'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
		array(),
		'3.x.x',
		true
	);

	// Load master theme config
	$config = include get_template_directory() . '/config-theme.php';

	// Build Tailwind config from master config
	$tailwind_colors = $config['colors'];
	
	// Convert to JavaScript object notation
	$colors_js = '';
	foreach ( $tailwind_colors as $name => $value ) {
		if ( is_array( $value ) ) {
			$colors_js .= "'" . esc_attr( $name ) . "': {\n";
			foreach ( $value as $sub_name => $sub_value ) {
				if ( is_array( $sub_value ) ) {
					// Skip nested arrays like 'semantic' > 'success'
					continue;
				}
				$colors_js .= "'" . esc_attr( $sub_name ) . "': '" . esc_attr( $sub_value ) . "',\n";
			}
			$colors_js .= "},\n";
		} else {
			$colors_js .= "'" . esc_attr( $name ) . "': '" . esc_attr( $value ) . "',\n";
		}
	}

	// Inline Tailwind config with colors from master config
	$tailwind_config = "
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						" . $colors_js . "
					},
					boxShadow: {
						'glow': '0 0 20px rgba(173, 92, 255, 0.4)',
						'glow-soft': '0 0 15px rgba(173, 92, 255, 0.2)',
						'glow-success': '0 0 20px rgba(0, 239, 136, 0.3)',
						'card-subtle': '0 4px 15px rgba(0, 0, 0, 0.3)'
					}
				}
			}
		};
	";
	wp_add_inline_script( 'alpine-js', $tailwind_config );
}
add_action( 'wp_enqueue_scripts', 'aws_sbg_enqueue_scripts' );

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
 * 7. UTILITY FUNCTIONS
 * ============================================================================
 */

/**
 * Get the mobile menu state
 */
function aws_sbg_is_mobile_menu_open() {
	return false;
}
