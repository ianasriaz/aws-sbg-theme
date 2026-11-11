<?php
/**
 * AWS SBG Call for Speakers — Core
 * File: cfs-core.php
 *
 * Database schema, tracks, formats, levels, event resolver, and shared helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AWS_SBG_CFS_DBV', '1' );

/* ============================================================================
 * 1. DATABASE SCHEMA
 * ============================================================================ */

function aws_sbg_cfs_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table = $wpdb->prefix . 'aws_cfs_submissions';
	dbDelta( "CREATE TABLE $table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		event_id bigint(20) NOT NULL DEFAULT 0,
		reference varchar(50) NOT NULL DEFAULT '',
		name varchar(255) NOT NULL DEFAULT '',
		email varchar(255) NOT NULL DEFAULT '',
		phone varchar(50) NOT NULL DEFAULT '',
		city varchar(100) NOT NULL DEFAULT '',
		organization varchar(255) NOT NULL DEFAULT '',
		linkedin_url varchar(255) NOT NULL DEFAULT '',
		talk_title varchar(255) NOT NULL DEFAULT '',
		talk_pitch text,
		track varchar(50) NOT NULL DEFAULT '',
		audience_level varchar(20) NOT NULL DEFAULT '100',
		format varchar(30) NOT NULL DEFAULT 'lightning',
		past_experience varchar(20) NOT NULL DEFAULT 'no',
		experience_link text,
		status varchar(30) NOT NULL DEFAULT 'received',
		admin_notes text,
		ip_address varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY event_idx (event_id),
		KEY reference_idx (reference),
		KEY status_idx (status),
		KEY track_idx (track),
		KEY email_idx (email)
	) $charset_collate;" );
}
add_action( 'after_switch_theme', 'aws_sbg_cfs_init_db' );
add_action( 'admin_init', 'aws_sbg_cfs_init_db', 10 );

/* ============================================================================
 * 2. TAXONOMIES & SELECTORS SCHEMA
 * ============================================================================ */

function aws_sbg_cfs_tracks() {
	return array(
		'genai_bedrock'      => 'Generative AI & Amazon Bedrock',
		'cloud_architecture' => 'Cloud Architecture & Serverless',
		'devops_containers'  => 'DevOps, CI/CD & Containers',
		'cloud_security'     => 'Cloud Security & Governance',
		'student_career'     => 'Student Builder Journey & Cloud Career',
		'other'              => 'Other AWS / Cloud Topic',
	);
}

function aws_sbg_cfs_formats() {
	return array(
		'lightning' => '15-min Lightning Talk',
		'session'   => '30-min Deep Dive Session',
		'workshop'  => '45-min Interactive Demo / Workshop',
	);
}

function aws_sbg_cfs_levels() {
	return array(
		'100' => 'Level 100 — Beginner / Foundational',
		'200' => 'Level 200 — Intermediate / Practitioner',
		'300' => 'Level 300 — Advanced / Deep Dive',
	);
}

function aws_sbg_cfs_statuses() {
	return array(
		'received'     => 'Received',
		'under_review' => 'Under Review',
		'shortlisted'  => 'Shortlisted',
		'accepted'     => 'Accepted',
		'declined'     => 'Declined',
	);
}

/* ============================================================================
 * 3. EVENT RESOLUTION & METADATA
 * ============================================================================ */

function aws_sbg_cfs_resolve_event( $ref = '' ) {
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

function aws_sbg_cfs_get_event_meta( $event_id ) {
	$defaults = array(
		'cfs_status'     => 'open',
		'cfs_deadline'   => '',
		'cfs_closed_msg' => 'Call for speakers for this event is currently closed. Thank you to everyone who pitched their sessions!',
		'notify_admin'   => 'yes',
	);

	$meta = array();
	foreach ( $defaults as $key => $default_val ) {
		$v = get_post_meta( $event_id, $key, true );
		$meta[ $key ] = ( '' !== $v && false !== $v ) ? $v : $default_val;
	}

	return $meta;
}

function aws_sbg_cfs_is_open( $event_id ) {
	$post = get_post( $event_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return false;
	}

	$meta = aws_sbg_cfs_get_event_meta( $event_id );
	if ( 'closed' === $meta['cfs_status'] ) {
		return false;
	}

	if ( ! empty( $meta['cfs_deadline'] ) ) {
		$deadline_ts = strtotime( $meta['cfs_deadline'] );
		if ( $deadline_ts && $deadline_ts < current_time( 'timestamp' ) ) {
			return false;
		}
	}

	return true;
}

/* ============================================================================
 * 4. STATS & COUNTS HELPER
 * ============================================================================ */

function aws_sbg_cfs_get_counts( $event_id = 0 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_cfs_submissions';

	$where = '1=1';
	$args  = array();

	if ( $event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $event_id;
	}

	$statuses = array_keys( aws_sbg_cfs_statuses() );
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
