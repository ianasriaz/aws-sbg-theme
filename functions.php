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
require_once get_template_directory() . '/includes/helpers/helpers-colors.php';
require_once get_template_directory() . '/includes/helpers/helpers-fonts.php';
require_once get_template_directory() . '/includes/emails/aws-emails.php';

// Load universal event registration system & dashboard
require_once get_template_directory() . '/includes/features/feature-event-registration.php';
require_once get_template_directory() . '/includes/features/feature-reinvent-registration.php';
require_once get_template_directory() . '/includes/admin/admin-dashboard.php';
require_once get_template_directory() . '/includes/admin/admin-design-kit.php';
require_once get_template_directory() . '/includes/admin/admin-email-center.php';

// Load AWS Foundational Certification Mock Exam Feature
require_once get_template_directory() . '/includes/features/feature-mock-exam.php';

// Load AWS Event & Self-Service Certificate Management System
require_once get_template_directory() . '/includes/features/feature-events.php';

// Load Random Swag Picker Wheel Feature
require_once get_template_directory() . '/includes/features/feature-swag-wheel.php';

// Load Modular On-Campus Giveaway Engine
require_once get_template_directory() . '/includes/features/feature-giveaway.php';

/**
 * Filter template path to support templates in subdirectory
 * This handles templates stored in templates/pages/ while WP looks in root
 */
add_filter( 'template_include', 'aws_sbg_template_include', 99 );
function aws_sbg_template_include( $template ) {
	// If already using a template from templates/pages/, use it
	if ( strpos( $template, 'templates/pages/' ) !== false ) {
		return $template;
	}
	
	// Handle custom page templates stored in templates/pages/
	if ( is_page() ) {
		$template_slug = get_page_template_slug();
		if ( $template_slug ) {
			$subdir_template = get_template_directory() . '/templates/pages/' . $template_slug;
			if ( file_exists( $subdir_template ) ) {
				return $subdir_template;
			}
		}
	}
	
	// Get template basename
	$basename = basename( $template );
	
	// Check if template exists in templates/pages/
	$subdir_template = get_template_directory() . '/templates/pages/' . $basename;
	if ( file_exists( $subdir_template ) ) {
		return $subdir_template;
	}
	
	return $template;
}

/**
 * Filter template hierarchy for front-page and other special templates
 * WordPress looks for front-page.php in root, redirect to templates/pages/
 */
add_filter( 'frontpage_template', 'aws_sbg_frontpage_template' );
function aws_sbg_frontpage_template( $template ) {
	$subdir_template = get_template_directory() . '/templates/pages/front-page.php';
	if ( file_exists( $subdir_template ) ) {
		return $subdir_template;
	}
	return $template;
}

/**
 * Filter template hierarchy for home (blog) template
 */
add_filter( 'home_template', 'aws_sbg_home_template' );
function aws_sbg_home_template( $template ) {
	$subdir_template = get_template_directory() . '/templates/pages/index.php';
	if ( file_exists( $subdir_template ) ) {
		return $subdir_template;
	}
	return $template;
}

/**
 * Filter template hierarchy for single posts
 */
add_filter( 'single_template', 'aws_sbg_single_template' );
function aws_sbg_single_template( $template ) {
	$basename = basename( $template );
	$subdir_template = get_template_directory() . '/templates/pages/' . $basename;
	if ( file_exists( $subdir_template ) ) {
		return $subdir_template;
	}
	return $template;
}

/**
 * Filter template hierarchy for archives
 */
add_filter( 'archive_template', 'aws_sbg_archive_template' );
function aws_sbg_archive_template( $template ) {
	$basename = basename( $template );
	$subdir_template = get_template_directory() . '/templates/pages/' . $basename;
	if ( file_exists( $subdir_template ) ) {
		return $subdir_template;
	}
	return $template;
}

/**
 * Register templates from templates/pages/ directory
 * Makes them appear in WP Admin page template dropdown
 */
add_filter( 'theme_page_templates', 'aws_sbg_add_page_templates' );
function aws_sbg_add_page_templates( $templates ) {
	$template_dir = get_template_directory() . '/templates/pages/';
	
	// Get all page templates from templates/pages/
	$template_files = glob( $template_dir . 'page-*.php' );
	
	foreach ( $template_files as $template_file ) {
		$basename = basename( $template_file );
		
		// Get template name from file header
		$template_data = get_file_data( $template_file, array( 'Template Name' => 'Template Name' ) );
		$template_name = $template_data['Template Name'] ?: ucwords( str_replace( array( 'page-', '.php', '-' ), array( '', '', ' ' ), $basename ) );
		
		// Register with simple basename (WP looks in root, we redirect in template_include)
		$templates[ $basename ] = $template_name;
	}
	
	return $templates;
}

/**
 * Auto-create Mock Exam & Certificate pages if missing
 */
function aws_sbg_auto_create_exam_page() {
	if ( ! is_admin() ) return;
	
	// 1. Exam Portal Page
	$page_slug = 'exam-portal';
	$page_check = get_page_by_path( $page_slug );
	if ( ! isset( $page_check->ID ) ) {
		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'AWS Exam Portal',
			'post_content' => '<!-- AWS Exam Portal -->',
			'post_status'  => 'publish',
			'post_name'    => $page_slug,
		) );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-exam-portal.php' );
		}
	}

	// 2. Self-Service Event Certificate Generator Page
	$cert_slug = 'claim-certificate';
	$cert_check = get_page_by_path( $cert_slug );
	if ( ! isset( $cert_check->ID ) ) {
		$cert_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'Claim Event Certificate',
			'post_content' => '<!-- Claim Event Certificate -->',
			'post_status'  => 'publish',
			'post_name'    => $cert_slug,
		) );
		if ( $cert_id && ! is_wp_error( $cert_id ) ) {
			update_post_meta( $cert_id, '_wp_page_template', 'page-certificate-generator.php' );
		}
	}

	// 3. Dedicated Events Portal Page
	$events_slug = 'events';
	$events_check = get_page_by_path( $events_slug );
	if ( ! isset( $events_check->ID ) ) {
		$events_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'Activities & Events',
			'post_content' => '<!-- AWS Activities & Events Portal -->',
			'post_status'  => 'publish',
			'post_name'    => $events_slug,
		) );
		if ( $events_id && ! is_wp_error( $events_id ) ) {
			update_post_meta( $events_id, '_wp_page_template', 'page-events.php' );
		}
	}

	// 4. Dedicated Community Hub & Student Rewards Page (/community/)
	$community_slug = 'community';
	$community_check = get_page_by_path( $community_slug );
	if ( ! isset( $community_check->ID ) ) {
		$comm_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'Community',
			'post_content' => '<!-- AWS Student Community Hub & Rewards -->',
			'post_status'  => 'publish',
			'post_name'    => $community_slug,
		) );
		if ( $comm_id && ! is_wp_error( $comm_id ) ) {
			update_post_meta( $comm_id, '_wp_page_template', 'page-community.php' );
		}
	}
	// 5. Dedicated On-Campus Giveaway Page (/giveaway/)
	$giveaway_slug = 'giveaway';
	$giveaway_check = get_page_by_path( $giveaway_slug );
	if ( ! isset( $giveaway_check->ID ) ) {
		$giveaway_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'Campus Giveaway',
			'post_content' => '<!-- AWS SBG Campus Giveaway -->',
			'post_status'  => 'publish',
			'post_name'    => $giveaway_slug,
		) );
		if ( $giveaway_id && ! is_wp_error( $giveaway_id ) ) {
			update_post_meta( $giveaway_id, '_wp_page_template', 'page-giveaway.php' );
		}
	}

	// 6. Dedicated AWS re:Invent Watch & Build Party Registration Page (/aws-reinvent/)
	$reinvent_slug = 'aws-reinvent';
	$reinvent_check = get_page_by_path( $reinvent_slug );
	if ( ! isset( $reinvent_check->ID ) ) {
		$reinvent_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => 'AWS re:Invent Watch & Build Party',
			'post_content' => '<!-- AWS re:Invent Registration -->',
			'post_status'  => 'publish',
			'post_name'    => $reinvent_slug,
		) );
		if ( $reinvent_id && ! is_wp_error( $reinvent_id ) ) {
			update_post_meta( $reinvent_id, '_wp_page_template', 'page-reinvent.php' );
		}
	}

	flush_rewrite_rules();
}
add_action( 'admin_init', 'aws_sbg_auto_create_exam_page' );

/**
 * Fix template paths for existing pages after folder reorganization
 * One-time fix to update old template paths to new structure
 */
function aws_sbg_fix_page_template_paths() {
	// Only run once - check if we've already fixed
	$fixed = get_option( 'aws_sbg_template_paths_fixed', false );
	if ( $fixed ) {
		return;
	}
	
	// Pages that need template path updates
	$pages_to_fix = array(
		'exam-portal'       => 'page-exam-portal.php',
		'claim-certificate' => 'page-certificate-generator.php',
		'events'            => 'page-events.php',
		'community'         => 'page-community.php',
		'giveaway'          => 'page-giveaway.php',
	);
	
	foreach ( $pages_to_fix as $slug => $old_template ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$current_template = get_post_meta( $page->ID, '_wp_page_template', true );
			// Template path is correct (wrapper in root)
			// No change needed - wrapper files handle includes
		}
	}
	
	// Mark as fixed
	update_option( 'aws_sbg_template_paths_fixed', true );
}
add_action( 'admin_init', 'aws_sbg_fix_page_template_paths', 20 );

/**
 * Clean alias redirects for /campus, /students, /rewards, and /perks -> /community/
 */
function aws_sbg_community_slug_aliases() {
	if ( is_admin() ) return;
	
	$request_uri = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$aliases = array( 'campus', 'students', 'rewards', 'perks' );
	$giveaway_aliases = array( 'win', 'swag' );
	if ( in_array( strtolower( $request_uri ), $giveaway_aliases, true ) ) {
		wp_safe_redirect( home_url( '/giveaway/' ), 301 );
		exit;
	}
	
	if ( in_array( strtolower( $request_uri ), $aliases, true ) ) {
		wp_safe_redirect( home_url( '/community/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'aws_sbg_community_slug_aliases' );



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

	// Enqueue Tailwind CSS v3 via CDN
	wp_enqueue_script(
		'tailwind-css-script',
		'https://cdn.tailwindcss.com?plugins=typography',
		array(),
		'3.4.16',
		false
	);

	// Alpine.js
	wp_enqueue_script(
		'alpinejs',
		'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
		array(),
		'3.14.1',
		false // Load in head, defer added via filter below
	);

	// Initialize Tailwind with theme configuration
	wp_add_inline_script( 'tailwind-css-script', aws_sbg_get_tailwind_config(), 'before' );

	// Add custom CSS reset and utilities
	wp_add_inline_style( 'aws-sbg-theme-style', aws_sbg_get_custom_styles() );
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

	// Add charcoal and brand color palette
	$colors_js['brand'] = '#9353ff';
	$colors_js['charcoal'] = array(
		'50'  => '#f8fafc',
		'100' => '#f1f5f9',
		'200' => '#e2e8f0',
		'300' => '#cbd5e1',
		'400' => '#94a3b8',
		'500' => '#64748b',
		'600' => '#475569',
		'700' => '#334155',
		'800' => '#1e293b',
		'900' => '#0f172a',
		'950' => '#020617',
	);

	// Build colors JSON
	$colors_json = json_encode( $colors_js, JSON_FORCE_OBJECT );
	
	// Return Tailwind configuration script
	return "
		window.tailwind = window.tailwind || {};
		window.tailwind.config = {
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
		a, button, .btn-launch, .btn-orange, .btn-ghost, .reward-card, .channel-card, .faq-item {
			transition: color 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
		}
		
		/* Link styling */
		a {
			color: var(--brand-color);
		}
		
		a:hover {
			color: var(--green-color);
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
	register_post_type( 'rsvp', array(
		'labels' => array(
			'name' => 'RSVPs',
			'singular_name' => 'RSVP',
		),
		'description' => 'Event Registration Submissions',
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => 'aws-sbg-suite',
		'show_in_rest' => false,
		'supports' => array( 'title', 'custom-fields' ),
		'menu_position' => 20,
		'menu_icon' => 'dashicons-clipboard',
		'has_archive' => false,
		'can_export' => true,
	) );

	// Register Contact Submission Post Type
	register_post_type( 'contact_submission', array(
		'labels' => array(
			'name' => 'Contacts',
			'singular_name' => 'Contact',
		),
		'description' => 'Contact Form Submissions',
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => 'aws-sbg-suite',
		'show_in_rest' => false,
		'supports' => array( 'title', 'custom-fields' ),
		'menu_position' => 21,
		'menu_icon' => 'dashicons-email',
		'has_archive' => false,
		'can_export' => true,
	) );
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

/**
 * Add defer attribute to Alpine.js
 */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
    if ( 'alpinejs' !== $handle ) {
        return $tag;
    }
    return str_replace( ' src', ' defer src', $tag );
}, 10, 2 );

