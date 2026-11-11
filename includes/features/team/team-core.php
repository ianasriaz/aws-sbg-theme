<?php
/**
 * AWS SBG Team Hiring & Volunteer System — Core
 * File: team-core.php
 *
 * Database schema, roles, departments, status definitions, event resolver, and helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AWS_SBG_TEAM_DBV', '1' );

/* ============================================================================
 * 1. DATABASE SCHEMA
 * ============================================================================ */

function aws_sbg_team_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table = $wpdb->prefix . 'aws_team_applications';
	dbDelta( "CREATE TABLE $table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		event_id bigint(20) NOT NULL DEFAULT 0,
		reference varchar(50) NOT NULL DEFAULT '',
		role varchar(30) NOT NULL DEFAULT 'event_crew',
		name varchar(255) NOT NULL DEFAULT '',
		email varchar(255) NOT NULL DEFAULT '',
		phone varchar(50) NOT NULL DEFAULT '',
		city varchar(100) NOT NULL DEFAULT '',
		university varchar(255) NOT NULL DEFAULT '',
		linkedin_url varchar(255) NOT NULL DEFAULT '',
		primary_department varchar(100) NOT NULL DEFAULT '',
		secondary_department varchar(100) NOT NULL DEFAULT '',
		past_experience varchar(20) NOT NULL DEFAULT 'no',
		experience_details text,
		pitch text,
		status varchar(30) NOT NULL DEFAULT 'received',
		admin_notes text,
		ip_address varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY event_idx (event_id),
		KEY reference_idx (reference),
		KEY role_idx (role),
		KEY status_idx (status),
		KEY university_idx (university(50)),
		KEY email_idx (email)
	) $charset_collate;" );
}
add_action( 'after_switch_theme', 'aws_sbg_team_init_db' );
add_action( 'admin_init', 'aws_sbg_team_init_db', 10 );

/* ============================================================================
 * 2. ROLES, DEPARTMENTS & STATUS TAXONOMIES
 * ============================================================================ */

function aws_sbg_team_roles() {
	return array(
		'event_crew' => array(
			'label'       => 'Event Crew',
			'badge'       => 'Day-of-Event Crew',
			'subtitle'    => 'Event-day operational execution, attendee hospitality, check-in, media & backstage flow.',
			'commitment'  => 'Available in-person on event day for the full duration plus pre-event briefing.',
		),
		'core_team'  => array(
			'label'       => 'Core Team',
			'badge'       => 'Lead-Up Planning & Leads',
			'subtitle'    => 'Lead-up planning, departmental ownership (Design, Tech, Operations, Outreach), and coordination.',
			'commitment'  => 'Available to dedicate 3–5 hours/week for planning syncs leading up to the event.',
		),
	);
}

function aws_sbg_team_departments( $role = '' ) {
	$crew_depts = array(
		'checkin'     => 'Registration & Attendee Check-in Desk',
		'stage_av'    => 'Stage Management, AV & Speaker Support',
		'media'       => 'Photography, Reels & Social Media Coverage',
		'hospitality' => 'Attendee Hospitality, Food & Crowd Flow',
		'swag'        => 'Swag & Giveaways Distribution',
		'general'     => 'General On-Ground Operations',
	);

	$core_depts = array(
		'lead_operations'   => 'Operations & Logistics Lead',
		'lead_technical'    => 'Technical & Workshop Lead (Hands-on Builds)',
		'lead_creative'     => 'Creative Director / Graphic Design Lead',
		'lead_partnerships' => 'Community Partnerships & Outreach Lead',
		'lead_marketing'    => 'Marketing & Social Media Lead',
		'lead_colead'       => 'Overall Event Co-Lead',
	);

	if ( 'event_crew' === $role ) {
		return $crew_depts;
	}
	if ( 'core_team' === $role ) {
		return $core_depts;
	}

	return array(
		'event_crew' => $crew_depts,
		'core_team'  => $core_depts,
	);
}

function aws_sbg_team_statuses() {
	return array(
		'received'    => 'Received',
		'screening'   => 'Screening',
		'shortlisted' => 'Shortlisted',
		'selected'    => 'Selected / Hired',
		'declined'    => 'Declined',
	);
}

/* ============================================================================
 * 3. EVENT RESOLUTION & METADATA
 * ============================================================================ */

function aws_sbg_team_resolve_event( $ref = '' ) {
	$ref = trim( (string) $ref );

	if ( '' !== $ref ) {
		// First check aws_invite_event
		$post = is_numeric( $ref ) ? get_post( (int) $ref ) : get_page_by_path( $ref, OBJECT, 'aws_invite_event' );
		if ( $post && 'aws_invite_event' === $post->post_type ) {
			return $post;
		}

		// Next check standard aws_event
		$post = is_numeric( $ref ) ? get_post( (int) $ref ) : get_page_by_path( $ref, OBJECT, 'aws_event' );
		if ( $post && 'aws_event' === $post->post_type ) {
			return $post;
		}
	}

	// Fallback to latest published aws_invite_event
	$latest = get_posts( array(
		'post_type'      => 'aws_invite_event',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	if ( ! empty( $latest[0] ) ) {
		return $latest[0];
	}

	return null;
}

function aws_sbg_team_get_event_meta( $event_id ) {
	$defaults = array(
		'team_scope'            => 'both', // 'both', 'event_crew', or 'core_team'
		'role_status_event_crew'=> 'open',
		'role_msg_event_crew'   => 'Event Crew slots for this event are currently full. Thank you for your interest!',
		'role_status_core_team' => 'open',
		'role_msg_core_team'    => 'Core Team applications for this event are now closed. You can still apply to join our Event Crew!',
		'team_deadline'         => '',
		'notify_admin'          => 'yes',
	);

	$meta = array();
	foreach ( $defaults as $key => $default_val ) {
		$v = get_post_meta( $event_id, $key, true );
		$meta[ $key ] = ( '' !== $v && false !== $v ) ? $v : $default_val;
	}

	return $meta;
}

function aws_sbg_team_is_role_allowed( $role, $event_id ) {
	$meta = aws_sbg_team_get_event_meta( $event_id );
	$scope = $meta['team_scope'];

	if ( 'both' !== $scope && $scope !== $role ) {
		return false;
	}

	$status_key = 'role_status_' . $role;
	if ( isset( $meta[ $status_key ] ) && 'closed' === $meta[ $status_key ] ) {
		return false;
	}

	if ( ! empty( $meta['team_deadline'] ) ) {
		$ts = strtotime( $meta['team_deadline'] );
		if ( $ts && $ts < current_time( 'timestamp' ) ) {
			return false;
		}
	}

	return true;
}

/* ============================================================================
 * 4. STATS & COUNTS HELPER
 * ============================================================================ */

function aws_sbg_team_get_counts( $event_id = 0, $role_filter = 'all' ) {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_team_applications';

	$where = '1=1';
	$args  = array();

	if ( $event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $event_id;
	}

	if ( 'all' !== $role_filter && in_array( $role_filter, array( 'event_crew', 'core_team' ), true ) ) {
		$where  .= ' AND role = %s';
		$args[] = $role_filter;
	}

	$statuses = array_keys( aws_sbg_team_statuses() );
	$counts   = array( 'all' => 0 );
	foreach ( $statuses as $st ) {
		$counts[ $st ] = 0;
	}

	$sql = "SELECT status, COUNT(*) as cnt FROM $table WHERE $where GROUP BY status";
	$rows = ! empty( $args ) ? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_results( $sql );

	if ( $rows ) {
		foreach ( $rows as $row ) {
			$st = $row->status;
			$c  = intval( $row->cnt );
			if ( isset( $counts[ $st ] ) ) {
				$counts[ $st ] = $c;
			}
			$counts['all'] += $c;
		}
	}

	return $counts;
}
