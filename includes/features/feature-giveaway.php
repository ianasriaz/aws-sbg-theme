<?php
/**
 * AWS SBG Modular Campus Giveaway Engine
 * File: feature-giveaway.php
 *
 * Lightweight, zero-server-overload giveaway system with configurable email domains,
 * modular action tasks, cached live counters, compound duplicate prevention,
 * and an interactive admin random winner selection tool.
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. DATABASE SCHEMA INITIALIZATION
 * Uses a dedicated single flat table for 100% zero wp_postmeta overhead.
 */
function aws_sbg_giveaway_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name      = $wpdb->prefix . 'aws_giveaway_entries';

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		campaign_id varchar(50) NOT NULL DEFAULT 'campus-campaign',
		ticket_number varchar(50) NOT NULL,
		full_name varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		phone varchar(50) NOT NULL,
		source varchar(100) NOT NULL DEFAULT 'campus_qr',
		is_winner tinyint(1) NOT NULL DEFAULT 0,
		winner_drawn_at datetime DEFAULT NULL,
		ip_address varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY unique_ticket (ticket_number),
		UNIQUE KEY unique_campaign_email (campaign_id, email),
		UNIQUE KEY unique_campaign_phone (campaign_id, phone),
		KEY campaign_idx (campaign_id),
		KEY is_winner_idx (is_winner)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'aws_sbg_giveaway_init_db' );
add_action( 'admin_init', 'aws_sbg_giveaway_init_db' );

/**
 * 2. CAMPAIGN SETTINGS HELPER
 * Retrieves modular campaign configuration with sensible Air University defaults.
 */
function aws_sbg_get_giveaway_settings() {
	$defaults = array(
		'campaign_id'           => 'campus-campaign',
		'campaign_title'        => 'AWS SBG Campus Giveaway',
		'campaign_subtitle'     => 'Air University Chapter • Enter to win 1 of 5 exclusive AWS Swag Bundles',
		'campaign_description'  => 'Complete the quick steps to claim your official Lucky Ticket. 5 random Air University students will win exclusive AWS Swag Bundles in this giveaway.',
		'campaign_status'       => 'active', // active, announced, paused
		'allowed_email_domains' => 'students.au.edu.pk, au.edu.pk',
		'ticket_prefix'         => 'AU-AWS',
		'winner_count'          => 5,
		'prize_title'           => '5 Official AWS Swag Bundles',
		'prize_description'     => 'Official AWS T-Shirts, Cloud Badges, Stickers & Cloud Learning Perks',
		'booth_claim_location'  => 'AWS SBG Campus Desk (Ground Floor, Main Cafeteria) — Bring your AU Student ID Card',
		'tasks'                 => array(
			array(
				'id'          => 'linkedin',
				'title'       => 'Follow on LinkedIn',
				'subtitle'    => 'AWS Student Builder Group Air University',
				'icon'        => 'linkedin',
				'url'         => 'https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/',
				'button_text' => 'Follow on LinkedIn',
				'is_required' => 1,
			),
			array(
				'id'          => 'instagram',
				'title'       => 'Follow on Instagram',
				'subtitle'    => '@awssbgairuniversity',
				'icon'        => 'instagram',
				'url'         => 'https://www.instagram.com/awssbgairuniversity/',
				'button_text' => 'Follow @awssbgairuniversity',
				'is_required' => 1,
			),
			array(
				'id'          => 'whatsapp',
				'title'       => 'Join WhatsApp Community',
				'subtitle'    => 'Official announcements & discussion hub',
				'icon'        => 'whatsapp',
				'url'         => 'https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI',
				'button_text' => 'Join WhatsApp Community',
				'is_required' => 1,
			),
		),
	);

	$saved = get_option( 'aws_sbg_giveaway_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * 3. LIVE COUNTER CACHING (Zero MySQL Hammering)
 * Reads participant count from transient memory or executes a fast indexed query.
 */
function aws_sbg_get_giveaway_live_count( $campaign_id = 'campus-campaign' ) {
	$cache_key = 'aws_sbg_giveaway_count_' . sanitize_key( $campaign_id );
	$count     = get_transient( $cache_key );

	if ( false === $count ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aws_giveaway_entries';
		$count      = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE campaign_id = %s",
			$campaign_id
		) );
		set_transient( $cache_key, $count, 60 ); // Cache for 60 seconds
	}

	return (int) $count;
}

/**
 * Invalidate cached live counter on new entry or purge.
 */
function aws_sbg_invalidate_giveaway_count( $campaign_id = 'campus-campaign' ) {
	$cache_key = 'aws_sbg_giveaway_count_' . sanitize_key( $campaign_id );
	delete_transient( $cache_key );
}

/**
 * 4. PUBLIC REST API ENDPOINTS
 * Lightweight REST endpoints for live counter and form submission.
 */
add_action( 'rest_api_init', function () {
	// Endpoint 1: Get Live Stats & Config
	register_rest_route( 'aws-sbg/v1', '/giveaway/stats', array(
		'methods'             => 'GET',
		'callback'            => 'aws_sbg_rest_giveaway_stats',
		'permission_callback' => '__return_true',
	) );

	// Endpoint 2: Submit Giveaway Entry
	register_rest_route( 'aws-sbg/v1', '/giveaway/enter', array(
		'methods'             => 'POST',
		'callback'            => 'aws_sbg_rest_giveaway_enter',
		'permission_callback' => '__return_true',
	) );

	// Endpoint 3: Check My Ticket / Status
	register_rest_route( 'aws-sbg/v1', '/giveaway/check-ticket', array(
		'methods'             => 'GET',
		'callback'            => 'aws_sbg_rest_giveaway_check_ticket',
		'permission_callback' => '__return_true',
	) );
} );

/**
 * REST Callback: Stats
 */
function aws_sbg_rest_giveaway_stats( WP_REST_Request $request ) {
	$settings    = aws_sbg_get_giveaway_settings();
	$campaign_id = $settings['campaign_id'];
	$count       = aws_sbg_get_giveaway_live_count( $campaign_id );

	return rest_ensure_response( array(
		'success'        => true,
		'count'          => $count,
		'status'         => $settings['campaign_status'],
		'campaign_title' => $settings['campaign_title'],
		'winner_count'   => (int) $settings['winner_count'],
	) );
}

/**
 * Helper: Validate Email Domain against configurable allowed domains
 */
function aws_sbg_validate_giveaway_domain( $email, $allowed_domains_str ) {
	$allowed_domains_str = trim( $allowed_domains_str );
	if ( empty( $allowed_domains_str ) || '*' === $allowed_domains_str ) {
		return true;
	}

	$domains = array_map( 'trim', explode( ',', strtolower( $allowed_domains_str ) ) );
	$parts   = explode( '@', strtolower( $email ) );
	if ( count( $parts ) !== 2 ) {
		return false;
	}

	$email_domain = $parts[1];

	foreach ( $domains as $domain ) {
		if ( empty( $domain ) ) {
			continue;
		}
		// Exact match or sub-domain match
		if ( $email_domain === $domain || str_ends_with( $email_domain, '.' . $domain ) ) {
			return true;
		}
	}

	return false;
}

/**
 * REST Callback: Enter Giveaway
 */
function aws_sbg_rest_giveaway_enter( WP_REST_Request $request ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();

	// Check campaign status
	if ( 'active' !== $settings['campaign_status'] ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Entries for this giveaway are currently closed.',
		), 400 );
	}

	// 1. Anti-Spam Honeypot check (hidden field)
	$honeypot = $request->get_param( 'website_url' );
	if ( ! empty( $honeypot ) ) {
		// Silent success for bots
		return rest_ensure_response( array(
			'success' => true,
			'ticket'  => 'AU-AWS-0000',
			'message' => 'Entry received.',
		) );
	}

	// 2. Sanitize & Validate Inputs
	$full_name = sanitize_text_field( $request->get_param( 'full_name' ) );
	$email     = sanitize_email( strtolower( trim( (string) $request->get_param( 'email' ) ) ) );
	$phone     = sanitize_text_field( trim( (string) $request->get_param( 'phone' ) ) );
	$source    = sanitize_text_field( $request->get_param( 'source' ) );
	if ( empty( $source ) ) {
		$source = 'campus_qr';
	}

	if ( empty( $full_name ) || empty( $email ) || empty( $phone ) ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Please provide your Full Name, AU Student Email, and WhatsApp Number.',
		), 400 );
	}

	if ( ! is_email( $email ) ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Please enter a valid email address.',
		), 400 );
	}

	// Clean up phone number (remove extra spaces/dashes for uniform comparison)
	$clean_phone = preg_replace( '/[^0-9+]/', '', $phone );
	if ( strlen( $clean_phone ) < 10 ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Please enter a valid WhatsApp phone number.',
		), 400 );
	}

	// 3. Domain Enforcement (defaults to @students.au.edu.pk)
	if ( ! aws_sbg_validate_giveaway_domain( $email, $settings['allowed_email_domains'] ) ) {
		$domain_display = implode( ', ', array_map( function( $d ) {
			return '@' . trim( $d );
		}, explode( ',', $settings['allowed_email_domains'] ) ) );

		return new WP_REST_Response( array(
			'success' => false,
			'message' => sprintf( 'Only institutional student emails (%s) are eligible for this on-campus giveaway.', $domain_display ),
		), 400 );
	}

	$campaign_id = $settings['campaign_id'];

	// 4. Polite "Already Entered" Check (Compound check on email OR phone)
	$existing = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, created_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND (email = %s OR phone = %s) 
		 LIMIT 1",
		$campaign_id,
		$email,
		$clean_phone
	) );

	if ( $existing ) {
		$current_count = aws_sbg_get_giveaway_live_count( $campaign_id );
		return rest_ensure_response( array(
			'success'         => true,
			'already_entered' => true,
			'ticket_number'   => $existing->ticket_number,
			'full_name'       => $existing->full_name,
			'email'           => $existing->email,
			'created_at'      => mysql2date( 'M j, Y • g:i a', $existing->created_at ),
			'live_count'      => $current_count,
			'message'         => 'You are already registered in this giveaway! Keep your ticket safe.',
		) );
	}

	// 5. Generate Sequential Clean Ticket Number (e.g. AU-AWS-1042)
	$prefix     = ! empty( $settings['ticket_prefix'] ) ? trim( $settings['ticket_prefix'] ) : 'AU-AWS';
	$max_id     = (int) $wpdb->get_var( "SELECT MAX(id) FROM $table_name" );
	$next_num   = 1000 + $max_id + 1;
	$ticket_num = $prefix . '-' . $next_num;

	// 6. Insert Entry
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$inserted = $wpdb->insert(
		$table_name,
		array(
			'campaign_id'   => $campaign_id,
			'ticket_number' => $ticket_num,
			'full_name'     => $full_name,
			'email'         => $email,
			'phone'         => $clean_phone,
			'source'        => $source,
			'is_winner'     => 0,
			'ip_address'    => $ip,
			'created_at'    => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	if ( false === $inserted ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Unable to register your entry. Please try again.',
		), 500 );
	}

	// Invalidate counter cache
	aws_sbg_invalidate_giveaway_count( $campaign_id );
	$new_count = aws_sbg_get_giveaway_live_count( $campaign_id );

	return rest_ensure_response( array(
		'success'         => true,
		'already_entered' => false,
		'ticket_number'   => $ticket_num,
		'full_name'       => $full_name,
		'email'           => $email,
		'created_at'      => current_time( 'M j, Y • g:i a' ),
		'live_count'      => $new_count,
		'message'         => 'Lucky Ticket successfully generated!',
	) );
}

/**
 * REST Callback: Check Ticket (Used in Winners Mode or student lookup)
 */
function aws_sbg_rest_giveaway_check_ticket( WP_REST_Request $request ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();

	$query_str = sanitize_text_field( strtolower( trim( (string) $request->get_param( 'q' ) ) ) );
	if ( empty( $query_str ) ) {
		return new WP_REST_Response( array(
			'success' => false,
			'message' => 'Please enter your Ticket # or AU Student Email.',
		), 400 );
	}

	$entry = $wpdb->get_row( $wpdb->prepare(
		"SELECT ticket_number, full_name, email, is_winner, winner_drawn_at, created_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND (LOWER(ticket_number) = %s OR LOWER(email) = %s) 
		 LIMIT 1",
		$settings['campaign_id'],
		$query_str,
		$query_str
	) );

	if ( ! $entry ) {
		return rest_ensure_response( array(
			'found'   => false,
			'message' => 'No participant found with this Ticket # or Email.',
		) );
	}

	return rest_ensure_response( array(
		'found'         => true,
		'ticket_number' => $entry->ticket_number,
		'full_name'     => $entry->full_name,
		'is_winner'     => (bool) $entry->is_winner,
		'created_at'    => mysql2date( 'M j, Y', $entry->created_at ),
	) );
}

/**
 * 5. ADMIN MENU & AJAX ACTIONS
 */

// Register Admin Submenu under AWS SBG Suite
function aws_sbg_register_giveaway_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'🎁 Campus Giveaway',
		'🎁 Campus Giveaway',
		'manage_options',
		'aws-campus-giveaway',
		'aws_sbg_render_giveaway_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_giveaway_admin_menu', 22 );

/**
 * AJAX Handler: Pick Random Winners
 */
function aws_sbg_ajax_pick_giveaway_winners() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();
	$count      = isset( $_POST['count'] ) ? intval( $_POST['count'] ) : (int) $settings['winner_count'];
	if ( $count < 1 ) {
		$count = 5;
	}

	// Pick random unique non-winners
	$candidates = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, phone, source, created_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND is_winner = 0 
		 ORDER BY RAND() LIMIT %d",
		$settings['campaign_id'],
		$count
	), ARRAY_A );

	if ( empty( $candidates ) ) {
		wp_send_json_error( array( 'message' => 'No eligible non-winner entries found in this campaign.' ) );
	}

	wp_send_json_success( array(
		'winners' => $candidates,
	) );
}
add_action( 'wp_ajax_aws_sbg_pick_giveaway_winners', 'aws_sbg_ajax_pick_giveaway_winners' );

/**
 * AJAX Handler: Re-roll 1 Individual Winner Slot
 */
function aws_sbg_ajax_reroll_giveaway_winner() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();

	$exclude_ids = isset( $_POST['exclude_ids'] ) ? array_map( 'intval', (array) $_POST['exclude_ids'] ) : array();

	$exclude_sql = '';
	if ( ! empty( $exclude_ids ) ) {
		$exclude_sql = " AND id NOT IN (" . implode( ',', $exclude_ids ) . ") ";
	}

	$new_candidate = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, phone, source, created_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND is_winner = 0 $exclude_sql 
		 ORDER BY RAND() LIMIT 1",
		$settings['campaign_id']
	), ARRAY_A );

	if ( ! $new_candidate ) {
		wp_send_json_error( array( 'message' => 'No additional eligible candidates available to re-roll.' ) );
	}

	wp_send_json_success( array(
		'winner' => $new_candidate,
	) );
}
add_action( 'wp_ajax_aws_sbg_reroll_giveaway_winner', 'aws_sbg_ajax_reroll_giveaway_winner' );

/**
 * AJAX Handler: Save & Publish Confirmed Winners
 */
function aws_sbg_ajax_publish_giveaway_winners() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();
	$winner_ids = isset( $_POST['winner_ids'] ) ? array_map( 'intval', (array) $_POST['winner_ids'] ) : array();

	if ( empty( $winner_ids ) ) {
		wp_send_json_error( array( 'message' => 'No winners selected to publish.' ) );
	}

	// 1. Reset existing winners for this campaign if requested
	$wpdb->update(
		$table_name,
		array( 'is_winner' => 0, 'winner_drawn_at' => null ),
		array( 'campaign_id' => $settings['campaign_id'] )
	);

	// 2. Mark confirmed winners
	$now = current_time( 'mysql' );
	foreach ( $winner_ids as $id ) {
		$wpdb->update(
			$table_name,
			array( 'is_winner' => 1, 'winner_drawn_at' => $now ),
			array( 'id' => $id )
		);
	}

	// 3. Update campaign status to 'announced' if requested
	if ( ! empty( $_POST['set_status_announced'] ) ) {
		$settings['campaign_status'] = 'announced';
		update_option( 'aws_sbg_giveaway_settings', $settings );
	}

	aws_sbg_invalidate_giveaway_count( $settings['campaign_id'] );

	wp_send_json_success( array(
		'message' => 'Winners confirmed and published successfully!',
		'count'   => count( $winner_ids ),
	) );
}
add_action( 'wp_ajax_aws_sbg_publish_giveaway_winners', 'aws_sbg_ajax_publish_giveaway_winners' );

/**
 * AJAX Handler: Save Campaign Settings
 */
function aws_sbg_ajax_save_giveaway_settings() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	$existing = aws_sbg_get_giveaway_settings();

	$updated = array(
		'campaign_id'           => sanitize_key( $_POST['campaign_id'] ?? $existing['campaign_id'] ),
		'campaign_title'        => sanitize_text_field( $_POST['campaign_title'] ?? $existing['campaign_title'] ),
		'campaign_subtitle'     => sanitize_text_field( $_POST['campaign_subtitle'] ?? $existing['campaign_subtitle'] ),
		'campaign_description'  => sanitize_textarea_field( $_POST['campaign_description'] ?? $existing['campaign_description'] ),
		'campaign_status'       => sanitize_text_field( $_POST['campaign_status'] ?? $existing['campaign_status'] ),
		'allowed_email_domains' => sanitize_text_field( $_POST['allowed_email_domains'] ?? $existing['allowed_email_domains'] ),
		'ticket_prefix'         => sanitize_text_field( $_POST['ticket_prefix'] ?? $existing['ticket_prefix'] ),
		'winner_count'          => intval( $_POST['winner_count'] ?? $existing['winner_count'] ),
		'prize_title'           => sanitize_text_field( $_POST['prize_title'] ?? $existing['prize_title'] ),
		'prize_description'     => sanitize_text_field( $_POST['prize_description'] ?? $existing['prize_description'] ),
		'booth_claim_location'  => sanitize_text_field( $_POST['booth_claim_location'] ?? $existing['booth_claim_location'] ),
		'tasks'                 => $existing['tasks'],
	);

	// Parse modular tasks if submitted
	if ( isset( $_POST['tasks_json'] ) ) {
		$decoded = json_decode( wp_unslash( $_POST['tasks_json'] ), true );
		if ( is_array( $decoded ) ) {
			$clean_tasks = array();
			foreach ( $decoded as $t ) {
				if ( empty( $t['title'] ) || empty( $t['url'] ) ) continue;
				$clean_tasks[] = array(
					'id'          => sanitize_key( $t['id'] ?? uniqid( 'task_' ) ),
					'title'       => sanitize_text_field( $t['title'] ),
					'subtitle'    => sanitize_text_field( $t['subtitle'] ?? '' ),
					'icon'        => sanitize_key( $t['icon'] ?? 'link' ),
					'url'         => esc_url_raw( $t['url'] ),
					'button_text' => sanitize_text_field( $t['button_text'] ?? 'Open Link' ),
					'is_required' => ! empty( $t['is_required'] ) ? 1 : 0,
				);
			}
			if ( ! empty( $clean_tasks ) ) {
				$updated['tasks'] = $clean_tasks;
			}
		}
	}

	update_option( 'aws_sbg_giveaway_settings', $updated );
	aws_sbg_invalidate_giveaway_count( $updated['campaign_id'] );

	wp_send_json_success( array( 'message' => 'Campaign settings saved successfully!' ) );
}
add_action( 'wp_ajax_aws_sbg_save_giveaway_settings', 'aws_sbg_ajax_save_giveaway_settings' );

/**
 * AJAX Handler: Purge Test Entries
 */
function aws_sbg_ajax_purge_test_giveaway_entries() {
	check_ajax_referer( 'aws_giveaway_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();

	$wpdb->delete( $table_name, array( 'campaign_id' => $settings['campaign_id'] ) );
	aws_sbg_invalidate_giveaway_count( $settings['campaign_id'] );

	wp_send_json_success( array( 'message' => 'All test entries for this campaign have been cleanly purged.' ) );
}
add_action( 'wp_ajax_aws_sbg_purge_giveaway_entries', 'aws_sbg_ajax_purge_test_giveaway_entries' );

/**
 * AJAX Handler: Export to CSV
 */
function aws_sbg_ajax_export_giveaway_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();

	$entries = $wpdb->get_results( $wpdb->prepare(
		"SELECT ticket_number, full_name, email, phone, source, is_winner, winner_drawn_at, created_at 
		 FROM $table_name 
		 WHERE campaign_id = %s 
		 ORDER BY id ASC",
		$settings['campaign_id']
	), ARRAY_A );

	$filename = 'giveaway-entries-' . sanitize_key( $settings['campaign_id'] ) . '-' . gmdate( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Ticket Number', 'Full Name', 'AU Student Email', 'WhatsApp Number', 'Source', 'Winner Status', 'Giveaway Win Date', 'Registered At' ) );

	if ( ! empty( $entries ) ) {
		foreach ( $entries as $row ) {
			fputcsv( $output, array(
				$row['ticket_number'],
				$row['full_name'],
				$row['email'],
				$row['phone'],
				$row['source'],
				$row['is_winner'] ? 'WINNER' : 'Participant',
				$row['winner_drawn_at'] ? $row['winner_drawn_at'] : '-',
				$row['created_at'],
			) );
		}
	}

	fclose( $output );
	exit;
}
add_action( 'wp_ajax_aws_sbg_export_giveaway_csv', 'aws_sbg_ajax_export_giveaway_csv' );

/**
 * 6. RENDER ADMIN CONTROL PAGE
 */
function aws_sbg_render_giveaway_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$settings   = aws_sbg_get_giveaway_settings();
	$nonce      = wp_create_nonce( 'aws_giveaway_admin_nonce' );

	// Metrics
	$total_entries = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table_name WHERE campaign_id = %s",
		$settings['campaign_id']
	) );

	$total_winners = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table_name WHERE campaign_id = %s AND is_winner = 1",
		$settings['campaign_id']
	) );

	// Existing Published Winners
	$published_winners = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, ticket_number, full_name, email, phone, source, winner_drawn_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND is_winner = 1 
		 ORDER BY id ASC",
		$settings['campaign_id']
	), ARRAY_A );

	// Pagination for entries table
	$per_page     = 50;
	$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$offset       = ( $current_page - 1 ) * $per_page;
	$search_query = isset( $_GET['s'] ) ? sanitize_text_field( trim( $_GET['s'] ) ) : '';

	$where_sql = $wpdb->prepare( " WHERE campaign_id = %s", $settings['campaign_id'] );
	if ( ! empty( $search_query ) ) {
		$like       = '%' . $wpdb->esc_like( $search_query ) . '%';
		$where_sql .= $wpdb->prepare( " AND (ticket_number LIKE %s OR full_name LIKE %s OR email LIKE %s OR phone LIKE %s)", $like, $like, $like, $like );
	}

	$entries = $wpdb->get_results(
		"SELECT id, ticket_number, full_name, email, phone, source, is_winner, created_at 
		 FROM $table_name 
		 $where_sql 
		 ORDER BY id DESC 
		 LIMIT $per_page OFFSET $offset",
		ARRAY_A
	);

	$total_filtered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where_sql" );
	$total_pages    = ceil( $total_filtered / $per_page );

	$active_subtab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
	?>
	<div class="wrap" style="max-width:1200px; margin:20px auto 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

		<!-- Reusable SBG Suite Header -->
		<?php
		if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) {
			aws_sbg_render_admin_nav_tabs( 'giveaway' );
		}
		?>

		<!-- Page Header & Metrics Row -->
		<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px 28px; margin-bottom:24px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
			<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:20px;">
				<div>
					<div style="display:inline-flex; align-items:center; gap:8px; background:#f5f3ff; border:1px solid #ddd6fe; color:#7c3aed; font-size:11px; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">
						Campus Campaign Engine
					</div>
					<h1 style="margin:0; font-size:22px; font-weight:800; color:#0f172a; line-height:1.2;">
						🎁 <?php echo esc_html( $settings['campaign_title'] ); ?>
					</h1>
					<p style="margin:4px 0 0; color:#64748b; font-size:13px;">
						Clean, zero-overload giveaway system with configurable email domains, modular tasks, and winner selection tools.
					</p>
				</div>
				<div style="display:flex; align-items:center; gap:10px;">
					<a href="<?php echo esc_url( home_url( '/giveaway/' ) ); ?>" target="_blank" style="display:inline-flex; align-items:center; gap:6px; background:#ffffff; border:1.5px solid #e2e8f0; color:#0f172a; font-weight:700; font-size:13px; padding:10px 18px; border-radius:10px; text-decoration:none; transition:all .2s ease;">
						↗ View Live Page
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=aws_sbg_export_giveaway_csv' ) ); ?>" style="display:inline-flex; align-items:center; gap:6px; background:#0f172a; color:#ffffff; font-weight:700; font-size:13px; padding:10px 18px; border-radius:10px; text-decoration:none; transition:all .2s ease;">
						📥 Export CSV
					</a>
				</div>
			</div>

			<!-- Metrics Cards -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px;">
					<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:4px;">Total Participants</div>
					<div style="font-size:28px; font-weight:900; color:#0f172a; line-height:1;"><?php echo esc_html( number_format( $total_entries ) ); ?></div>
					<div style="font-size:11px; color:#10b981; font-weight:600; margin-top:6px;">✓ Clean unique students</div>
				</div>

				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px;">
					<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:4px;">Winners to Pick</div>
					<div style="font-size:28px; font-weight:900; color:#9353ff; line-height:1;"><?php echo esc_html( $settings['winner_count'] ); ?></div>
					<div style="font-size:11px; color:#64748b; margin-top:6px;"><?php echo esc_html( $total_winners ); ?> published</div>
				</div>

				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px;">
					<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:4px;">Campaign Status</div>
					<div style="margin-top:4px;">
						<?php if ( 'active' === $settings['campaign_status'] ) : ?>
							<span style="display:inline-flex; align-items:center; gap:6px; background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; font-weight:800; font-size:12px; padding:4px 10px; border-radius:8px;">
								<span style="width:6px; height:6px; border-radius:50%; background:#10b981;"></span> Active (Accepting Entries)
							</span>
						<?php elseif ( 'announced' === $settings['campaign_status'] ) : ?>
							<span style="display:inline-flex; align-items:center; gap:6px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; font-weight:800; font-size:12px; padding:4px 10px; border-radius:8px;">
								🏆 Winners Announced
							</span>
						<?php else : ?>
							<span style="display:inline-flex; align-items:center; gap:6px; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-weight:800; font-size:12px; padding:4px 10px; border-radius:8px;">
								⏸ Paused
							</span>
						<?php endif; ?>
					</div>
					<div style="font-size:11px; color:#64748b; margin-top:6px;">URL: <code>/giveaway/</code></div>
				</div>

				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px;">
					<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:4px;">Eligible Email Domain</div>
					<div style="font-size:13px; font-weight:800; color:#0f172a; margin-top:2px;">
						<code>@<?php echo esc_html( explode( ',', $settings['allowed_email_domains'] )[0] ); ?></code>
					</div>
					<div style="font-size:11px; color:#64748b; margin-top:6px;">1 entry per student enforced</div>
				</div>
			</div>
		</div>

		<!-- Inner Navigation Sub-Tabs -->
		<div style="display:flex; gap:8px; border-bottom:1px solid #e2e8f0; margin-bottom:24px; padding-bottom:12px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-campus-giveaway&tab=overview' ) ); ?>" style="padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; <?php echo ( 'overview' === $active_subtab ) ? 'background:#9353ff; color:#ffffff;' : 'background:#ffffff; color:#64748b; border:1px solid #e2e8f0;'; ?>">
				👥 Participants (<?php echo esc_html( $total_entries ); ?>)
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-campus-giveaway&tab=draw' ) ); ?>" style="padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; <?php echo ( 'draw' === $active_subtab ) ? 'background:#9353ff; color:#ffffff;' : 'background:#ffffff; color:#64748b; border:1px solid #e2e8f0;'; ?>">
				🎁 Pick 5 Winners
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-campus-giveaway&tab=settings' ) ); ?>" style="padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; <?php echo ( 'settings' === $active_subtab ) ? 'background:#9353ff; color:#ffffff;' : 'background:#ffffff; color:#64748b; border:1px solid #e2e8f0;'; ?>">
				⚙️ Campaign Settings
			</a>
		</div>

		<!-- TAB 1: PARTICIPANTS ROSTER -->
		<?php if ( 'overview' === $active_subtab ) : ?>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="padding:18px 24px; border-bottom:1px solid #e2e8f0; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
					<h3 style="margin:0; font-size:16px; font-weight:800; color:#0f172a;">All Valid Campus Entries</h3>

					<form method="get" style="display:flex; align-items:center; gap:8px;">
						<input type="hidden" name="page" value="aws-campus-giveaway">
						<input type="hidden" name="tab" value="overview">
						<input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search name, email, ticket..." style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; width:220px;">
						<button type="submit" class="button" style="padding:5px 14px; font-weight:600;">Search</button>
						<?php if ( ! empty( $search_query ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-campus-giveaway&tab=overview' ) ); ?>" style="color:#ef4444; font-size:12px; text-decoration:none; margin-left:4px;">Clear</a>
						<?php endif; ?>
					</form>
				</div>

				<?php if ( empty( $entries ) ) : ?>
					<div style="padding:60px 20px; text-align:center;">
						<div style="font-size:36px; margin-bottom:8px;">🎟️</div>
						<h4 style="margin:0; font-size:16px; font-weight:800; color:#0f172a;">No entries recorded yet</h4>
						<p style="margin:6px 0 0; color:#64748b; font-size:13px;">When students scan the QR code on campus, their Lucky Tickets will appear here.</p>
					</div>
				<?php else : ?>
					<table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
						<thead>
							<tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:0.06em;">
								<th style="padding:12px 18px;">Ticket #</th>
								<th style="padding:12px 18px;">Student Name</th>
								<th style="padding:12px 18px;">AU Email</th>
								<th style="padding:12px 18px;">WhatsApp</th>
								<th style="padding:12px 18px;">Source</th>
								<th style="padding:12px 18px;">Status</th>
								<th style="padding:12px 18px;">Registered At</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $entries as $r ) : ?>
								<tr style="border-bottom:1px solid #f1f5f9;">
									<td style="padding:12px 18px; font-family:monospace; font-weight:800; color:#9353ff;">
										#<?php echo esc_html( $r['ticket_number'] ); ?>
									</td>
									<td style="padding:12px 18px; font-weight:700; color:#0f172a;">
										<?php echo esc_html( $r['full_name'] ); ?>
									</td>
									<td style="padding:12px 18px; color:#475569;">
										<?php echo esc_html( $r['email'] ); ?>
									</td>
									<td style="padding:12px 18px;">
										<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $r['phone'] ) ); ?>" target="_blank" style="display:inline-flex; align-items:center; gap:4px; color:#059669; font-weight:700; text-decoration:none;">
											💬 <?php echo esc_html( $r['phone'] ); ?>
										</a>
									</td>
									<td style="padding:12px 18px; color:#64748b; font-size:12px;">
										<code><?php echo esc_html( $r['source'] ); ?></code>
									</td>
									<td style="padding:12px 18px;">
										<?php if ( $r['is_winner'] ) : ?>
											<span style="background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px;">
												🏆 WINNER
											</span>
										<?php else : ?>
											<span style="background:#f1f5f9; color:#64748b; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px;">
												Participant
											</span>
										<?php endif; ?>
									</td>
									<td style="padding:12px 18px; color:#64748b; font-size:12px;">
										<?php echo esc_html( mysql2date( 'M j, g:i a', $r['created_at'] ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<!-- Pagination -->
					<?php if ( $total_pages > 1 ) : ?>
						<div style="padding:14px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#64748b;">
							<div>Showing page <?php echo esc_html( $current_page ); ?> of <?php echo esc_html( $total_pages ); ?></div>
							<div style="display:flex; gap:6px;">
								<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
									<a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" style="padding:4px 10px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; <?php echo ( $i === $current_page ) ? 'background:#9353ff; color:#fff; border-color:#9353ff;' : 'color:#0f172a;'; ?>">
										<?php echo esc_html( $i ); ?>
									</a>
								<?php endfor; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<!-- Clean Purge Box for testing -->
			<div style="margin-top:24px; background:#fff1f2; border:1px solid #fecdd3; border-radius:12px; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
				<div>
					<h4 style="margin:0; color:#9f1239; font-size:14px; font-weight:800;">Purge Test Entries</h4>
					<p style="margin:2px 0 0; color:#e11d48; font-size:12px;">Wipes test entries for this campaign before placing physical posters on campus.</p>
				</div>
				<button type="button" id="aws-sbg-purge-btn" style="background:#e11d48; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
					Purge Test Entries
				</button>
			</div>

		<!-- TAB 2: PICK 5 WINNERS TOOL -->
		<?php elseif ( 'draw' === $active_subtab ) : ?>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:28px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:18px;">
					<div>
						<h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">🎁 Live Winner Selection</h3>
						<p style="margin:4px 0 0; color:#64748b; font-size:13px;">
							Select 5 random giveaway winners. You can 1-click verify their WhatsApp and re-roll individual candidates if unverified.
						</p>
					</div>
					<div style="display:flex; align-items:center; gap:10px;">
						<button type="button" id="aws-sbg-draw-btn" style="background:linear-gradient(135deg, #7928ca 0%, #9353ff 100%); color:#ffffff; border:none; padding:12px 26px; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:0 4px 14px rgba(147,83,255,0.35);">
							🎁 Pick 5 Giveaway Winners
						</button>
					</div>
				</div>

				<!-- Results Container -->
				<div id="aws-draw-results-container">
					<?php if ( ! empty( $published_winners ) ) : ?>
						<div style="margin-bottom:16px; padding:12px 18px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; color:#1e40af; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
							<span>🏆 <strong>Currently Published Winners</strong> on <code>/giveaway/</code></span>
							<span style="font-size:11px; background:#dbeafe; padding:3px 8px; border-radius:6px;"><?php echo count( $published_winners ); ?> Winners Active</span>
						</div>

						<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;" id="aws-winners-grid">
							<?php foreach ( $published_winners as $idx => $w ) : ?>
								<div class="aws-winner-card" data-id="<?php echo esc_attr( $w['id'] ); ?>" style="background:#ffffff; border:2px solid #10b981; border-radius:14px; padding:20px; box-shadow:0 4px 12px rgba(16,185,129,0.08);">
									<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
										<span style="background:#ecfdf5; color:#059669; font-weight:900; font-size:12px; padding:3px 10px; border-radius:8px;">
											Winner #<?php echo esc_html( $idx + 1 ); ?>
										</span>
										<span style="font-family:monospace; font-weight:800; color:#9353ff; font-size:13px;">
											#<?php echo esc_html( $w['ticket_number'] ); ?>
										</span>
									</div>

									<h4 style="margin:0 0 4px; font-size:16px; font-weight:800; color:#0f172a;"><?php echo esc_html( $w['full_name'] ); ?></h4>
									<div style="font-size:12px; color:#64748b; margin-bottom:14px;"><?php echo esc_html( $w['email'] ); ?></div>

									<div style="display:flex; gap:8px;">
										<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $w['phone'] ) ); ?>" target="_blank" style="flex:1; text-align:center; background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; font-weight:700; font-size:12px; padding:8px 12px; border-radius:8px; text-decoration:none;">
											💬 WhatsApp
										</a>
										<button type="button" class="aws-reroll-btn" data-id="<?php echo esc_attr( $w['id'] ); ?>" style="background:#f8fafc; border:1px solid #cbd5e1; color:#475569; font-size:12px; font-weight:700; padding:8px 12px; border-radius:8px; cursor:pointer;">
											🔄 Re-roll
										</button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div style="text-align:center; padding:60px 20px; border:2px dashed #e2e8f0; border-radius:14px;">
							<div style="font-size:42px; margin-bottom:10px;">🎁</div>
							<h4 style="margin:0; font-size:16px; font-weight:800; color:#0f172a;">Ready for the Giveaway</h4>
							<p style="margin:6px 0 18px; color:#64748b; font-size:13px;">
								Click the button above to select 5 random unique students from the pool of <?php echo esc_html( $total_entries ); ?> entries.
							</p>
						</div>
					<?php endif; ?>
				</div>

				<!-- Publish Action Footer (revealed when winners are selected) -->
				<div id="aws-publish-section" style="<?php echo empty( $published_winners ) ? 'display:none;' : ''; ?> margin-top:24px; padding-top:20px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
					<div style="font-size:13px; color:#64748b;">
						Confirming will update <code>/giveaway/</code> to display these 5 winners.
					</div>
					<button type="button" id="aws-publish-btn" style="background:#10b981; color:#ffffff; border:none; padding:12px 24px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.3);">
						📢 Publish Winners to Giveaway Page
					</button>
				</div>
			</div>

		<!-- TAB 3: CAMPAIGN SETTINGS -->
		<?php elseif ( 'settings' === $active_subtab ) : ?>
			<form id="aws-giveaway-settings-form" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:28px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<h3 style="margin:0 0 20px; font-size:18px; font-weight:800; color:#0f172a; border-bottom:1px solid #e2e8f0; padding-bottom:14px;">
					⚙️ Campaign Configuration
				</h3>

				<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:24px;">
					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Campaign Name</label>
						<input type="text" name="campaign_title" value="<?php echo esc_attr( $settings['campaign_title'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" required>
					</div>

					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Campaign Status</label>
						<select name="campaign_status" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
							<option value="active" <?php selected( $settings['campaign_status'], 'active' ); ?>>🟢 Active (Accepting Entries)</option>
							<option value="announced" <?php selected( $settings['campaign_status'], 'announced' ); ?>>🏆 Winners Announced (Showcase Mode)</option>
							<option value="paused" <?php selected( $settings['campaign_status'], 'paused' ); ?>>⏸ Paused</option>
						</select>
					</div>

					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
							Allowed Email Domain(s) <span style="font-weight:400; color:#64748b;">(comma-separated, or * for any)</span>
						</label>
						<input type="text" name="allowed_email_domains" value="<?php echo esc_attr( $settings['allowed_email_domains'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" required>
						<span style="font-size:11px; color:#64748b; margin-top:4px; display:block;">Default: <code>students.au.edu.pk, au.edu.pk</code></span>
					</div>

					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Ticket Number Prefix</label>
						<input type="text" name="ticket_prefix" value="<?php echo esc_attr( $settings['ticket_prefix'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" required>
						<span style="font-size:11px; color:#64748b; margin-top:4px; display:block;">Outputs: <code>AU-AWS-1001</code></span>
					</div>

					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Number of Giveaway Winners</label>
						<input type="number" name="winner_count" min="1" max="50" value="<?php echo esc_attr( $settings['winner_count'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;" required>
					</div>

					<div>
						<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Prize Headline</label>
						<input type="text" name="prize_title" value="<?php echo esc_attr( $settings['prize_title'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
					</div>
				</div>

				<div style="margin-bottom:20px;">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Campaign Description (Displayed on Giveaway Page)</label>
					<textarea name="campaign_description" rows="3" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; font-family:inherit;"><?php echo esc_textarea( $settings['campaign_description'] ?? '' ); ?></textarea>
					<span style="font-size:11px; color:#64748b; margin-top:4px; display:block;">Explains the campaign rules/instructions to students scanning on campus.</span>
				</div>

				<div style="margin-bottom:24px;">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Booth Claim Location Instructions (Shown in Winners Mode)</label>
					<input type="text" name="booth_claim_location" value="<?php echo esc_attr( $settings['booth_claim_location'] ); ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
				</div>

				<!-- Modular Tasks Section -->
				<h4 style="margin:28px 0 12px; font-size:15px; font-weight:800; color:#0f172a;">📋 Modular Action Tasks</h4>
				<p style="margin:0 0 16px; color:#64748b; font-size:13px;">Students complete these tasks before claiming their ticket.</p>

				<div id="aws-tasks-list" style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
					<?php foreach ( $settings['tasks'] as $idx => $task ) : ?>
						<div class="aws-task-row" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; display:grid; grid-template-columns:120px 1fr 1fr 140px 40px; gap:12px; align-items:center;">
							<input type="hidden" class="task-id" value="<?php echo esc_attr( $task['id'] ); ?>">
							
							<select class="task-icon" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px;">
								<option value="linkedin" <?php selected( $task['icon'], 'linkedin' ); ?>>LinkedIn</option>
								<option value="instagram" <?php selected( $task['icon'], 'instagram' ); ?>>Instagram</option>
								<option value="whatsapp" <?php selected( $task['icon'], 'whatsapp' ); ?>>WhatsApp</option>
								<option value="meetup" <?php selected( $task['icon'], 'meetup' ); ?>>Meetup</option>
								<option value="link" <?php selected( $task['icon'], 'link' ); ?>>Custom Link</option>
							</select>

							<input type="text" class="task-title" value="<?php echo esc_attr( $task['title'] ); ?>" placeholder="Task Title" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
							<input type="url" class="task-url" value="<?php echo esc_attr( $task['url'] ); ?>" placeholder="Destination URL" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
							<input type="text" class="task-btn" value="<?php echo esc_attr( $task['button_text'] ); ?>" placeholder="Button Text" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px;">

							<button type="button" class="aws-remove-task-btn" style="background:none; border:none; color:#ef4444; font-size:16px; cursor:pointer;" title="Remove">✕</button>
						</div>
					<?php endforeach; ?>
				</div>

				<button type="button" id="aws-add-task-btn" style="background:#ffffff; border:1.5px dashed #cbd5e1; color:#475569; font-weight:700; font-size:12px; padding:8px 16px; border-radius:8px; cursor:pointer; margin-bottom:28px;">
					+ Add Another Task
				</button>

				<div style="border-top:1px solid #e2e8f0; padding-top:20px; display:flex; justify-content:flex-end;">
					<button type="submit" id="aws-save-settings-btn" style="background:#0f172a; color:#ffffff; border:none; padding:12px 28px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer;">
						Save Campaign Settings
					</button>
				</div>
			</form>
		<?php endif; ?>

	</div>

	<!-- Admin JavaScript Engine -->
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
		const nonce   = '<?php echo esc_js( $nonce ); ?>';

		// 1. Pick Giveaway Winners
		const drawBtn = document.getElementById('aws-sbg-draw-btn');
		if (drawBtn) {
			drawBtn.addEventListener('click', function() {
				drawBtn.disabled = true;
				drawBtn.innerText = '🎁 Selecting Winners...';

				const formData = new FormData();
				formData.append('action', 'aws_sbg_pick_giveaway_winners');
				formData.append('nonce', nonce);

				fetch(ajaxUrl, { method: 'POST', body: formData })
					.then(r => r.json())
					.then(res => {
						drawBtn.disabled = false;
						drawBtn.innerText = '🎁 Re-select All Winners';
						if (res.success && res.data.winners) {
							renderDrawnWinners(res.data.winners);
						} else {
							alert(res.data && res.data.message ? res.data.message : 'Error selecting winners.');
						}
					})
					.catch(err => {
						drawBtn.disabled = false;
						drawBtn.innerText = '🎁 Pick 5 Giveaway Winners';
						alert('Network error selecting candidates.');
					});
			});
		}

		function renderDrawnWinners(winners) {
			const container = document.getElementById('aws-draw-results-container');
			const publishSec = document.getElementById('aws-publish-section');

			let html = '<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;" id="aws-winners-grid">';
			winners.forEach((w, idx) => {
				const cleanPhone = (w.phone || '').replace(/[^0-9]/g, '');
				html += `
					<div class="aws-winner-card" data-id="${w.id}" style="background:#ffffff; border:2px solid #9353ff; border-radius:14px; padding:20px; box-shadow:0 4px 12px rgba(147,83,255,0.12); animation:awsCardPop .3s ease;">
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
							<span style="background:#f5f3ff; color:#7c3aed; font-weight:900; font-size:12px; padding:3px 10px; border-radius:8px;">
								Selected Winner #${idx + 1}
							</span>
							<span style="font-family:monospace; font-weight:800; color:#9353ff; font-size:13px;">
								#${w.ticket_number}
							</span>
						</div>
						<h4 style="margin:0 0 4px; font-size:16px; font-weight:800; color:#0f172a;">${w.full_name}</h4>
						<div style="font-size:12px; color:#64748b; margin-bottom:14px;">${w.email}</div>
						<div style="display:flex; gap:8px;">
							<a href="https://wa.me/${cleanPhone}" target="_blank" style="flex:1; text-align:center; background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; font-weight:700; font-size:12px; padding:8px 12px; border-radius:8px; text-decoration:none;">
								💬 WhatsApp
							</a>
							<button type="button" class="aws-reroll-btn" data-id="${w.id}" style="background:#f8fafc; border:1px solid #cbd5e1; color:#475569; font-size:12px; font-weight:700; padding:8px 12px; border-radius:8px; cursor:pointer;">
								🔄 Re-roll
							</button>
						</div>
					</div>
				`;
			});
			html += '</div>';

			container.innerHTML = html;
			if (publishSec) publishSec.style.display = 'flex';
			bindRerollButtons();
		}

		// 2. Individual Re-roll
		function bindRerollButtons() {
			document.querySelectorAll('.aws-reroll-btn').forEach(btn => {
				btn.addEventListener('click', function() {
					const card = this.closest('.aws-winner-card');
					const currentId = card.getAttribute('data-id');

					const allCards = document.querySelectorAll('.aws-winner-card');
					const currentIds = Array.from(allCards).map(c => parseInt(c.getAttribute('data-id')));

					this.disabled = true;
					this.innerText = '...';

					const formData = new FormData();
					formData.append('action', 'aws_sbg_reroll_giveaway_winner');
					formData.append('nonce', nonce);
					currentIds.forEach(id => formData.append('exclude_ids[]', id));

					fetch(ajaxUrl, { method: 'POST', body: formData })
						.then(r => r.json())
						.then(res => {
							if (res.success && res.data.winner) {
								const nw = res.data.winner;
								const cleanPhone = (nw.phone || '').replace(/[^0-9]/g, '');
								card.setAttribute('data-id', nw.id);
								card.querySelector('h4').innerText = nw.full_name;
								card.querySelector('div[style*="margin-bottom:14px"]').innerText = nw.email;
								card.querySelector('span[style*="monospace"]').innerText = '#' + nw.ticket_number;
								const waLink = card.querySelector('a[href^="https://wa.me"]');
								if (waLink) waLink.href = 'https://wa.me/' + cleanPhone;
								btn.disabled = false;
								btn.innerText = '🔄 Re-roll';
							} else {
								alert(res.data && res.data.message ? res.data.message : 'No replacement candidate available.');
								btn.disabled = false;
								btn.innerText = '🔄 Re-roll';
							}
						});
				});
			});
		}
		bindRerollButtons();

		// 3. Publish Confirmed Winners
		const publishBtn = document.getElementById('aws-publish-btn');
		if (publishBtn) {
			publishBtn.addEventListener('click', function() {
				const cards = document.querySelectorAll('.aws-winner-card');
				const winnerIds = Array.from(cards).map(c => parseInt(c.getAttribute('data-id')));

				if (!winnerIds.length) {
					alert('No winners selected.');
					return;
				}

				if (!confirm('Publish these ' + winnerIds.length + ' winners to the public page? This will set /giveaway/ to Winners Announced mode.')) {
					return;
				}

				publishBtn.disabled = true;
				publishBtn.innerText = 'Publishing...';

				const formData = new FormData();
				formData.append('action', 'aws_sbg_publish_giveaway_winners');
				formData.append('nonce', nonce);
				formData.append('set_status_announced', '1');
				winnerIds.forEach(id => formData.append('winner_ids[]', id));

				fetch(ajaxUrl, { method: 'POST', body: formData })
					.then(r => r.json())
					.then(res => {
						publishBtn.disabled = false;
						publishBtn.innerText = '📢 Publish Winners to Giveaway Page';
						if (res.success) {
							alert('Winners successfully published to /giveaway/!');
							window.location.reload();
						} else {
							alert(res.data && res.data.message ? res.data.message : 'Error publishing winners.');
						}
					});
			});
		}

		// 4. Purge Test Entries
		const purgeBtn = document.getElementById('aws-sbg-purge-btn');
		if (purgeBtn) {
			purgeBtn.addEventListener('click', function() {
				if (!confirm('Are you sure you want to permanently delete all entries in this campaign? This cannot be undone.')) {
					return;
				}
				purgeBtn.disabled = true;
				purgeBtn.innerText = 'Purging...';

				const formData = new FormData();
				formData.append('action', 'aws_sbg_purge_giveaway_entries');
				formData.append('nonce', nonce);

				fetch(ajaxUrl, { method: 'POST', body: formData })
					.then(r => r.json())
					.then(res => {
						if (res.success) {
							alert('Test entries purged successfully.');
							window.location.reload();
						} else {
							alert('Error purging entries.');
							purgeBtn.disabled = false;
						}
					});
			});
		}

		// 5. Settings & Modular Tasks
		const addTaskBtn = document.getElementById('aws-add-task-btn');
		const tasksList = document.getElementById('aws-tasks-list');
		if (addTaskBtn && tasksList) {
			addTaskBtn.addEventListener('click', function() {
				const row = document.createElement('div');
				row.className = 'aws-task-row';
				row.style = 'background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; display:grid; grid-template-columns:120px 1fr 1fr 140px 40px; gap:12px; align-items:center;';
				row.innerHTML = `
					<input type="hidden" class="task-id" value="task_${Date.now()}">
					<select class="task-icon" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px;">
						<option value="link">Custom Link</option>
						<option value="linkedin">LinkedIn</option>
						<option value="instagram">Instagram</option>
						<option value="whatsapp">WhatsApp</option>
						<option value="meetup">Meetup</option>
					</select>
					<input type="text" class="task-title" placeholder="Task Title" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
					<input type="url" class="task-url" placeholder="Destination URL" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
					<input type="text" class="task-btn" value="Open Link" placeholder="Button Text" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px;">
					<button type="button" class="aws-remove-task-btn" style="background:none; border:none; color:#ef4444; font-size:16px; cursor:pointer;" title="Remove">✕</button>
				`;
				tasksList.appendChild(row);
				bindRemoveButtons();
			});
		}

		function bindRemoveButtons() {
			document.querySelectorAll('.aws-remove-task-btn').forEach(btn => {
				btn.onclick = function() {
					this.closest('.aws-task-row').remove();
				};
			});
		}
		bindRemoveButtons();

		const settingsForm = document.getElementById('aws-giveaway-settings-form');
		if (settingsForm) {
			settingsForm.addEventListener('submit', function(e) {
				e.preventDefault();
				const submitBtn = document.getElementById('aws-save-settings-btn');
				submitBtn.disabled = true;
				submitBtn.innerText = 'Saving...';

				const taskRows = document.querySelectorAll('.aws-task-row');
				const tasks = [];
				taskRows.forEach(r => {
					tasks.push({
						id: r.querySelector('.task-id').value,
						icon: r.querySelector('.task-icon').value,
						title: r.querySelector('.task-title').value,
						url: r.querySelector('.task-url').value,
						button_text: r.querySelector('.task-btn').value,
						is_required: 1,
					});
				});

				const formData = new FormData(settingsForm);
				formData.append('action', 'aws_sbg_save_giveaway_settings');
				formData.append('nonce', nonce);
				formData.append('tasks_json', JSON.stringify(tasks));

				fetch(ajaxUrl, { method: 'POST', body: formData })
					.then(r => r.json())
					.then(res => {
						submitBtn.disabled = false;
						submitBtn.innerText = 'Save Campaign Settings';
						if (res.success) {
							alert('Settings saved successfully!');
							window.location.reload();
						} else {
							alert(res.data && res.data.message ? res.data.message : 'Error saving settings.');
						}
					});
			});
		}
	});
	</script>
	<?php
}
