<?php
/**
 * AWS SBG Invitation Confirmation — Core
 * File: invite-core.php
 *
 * Event CPT, fixed field schema, database schema, and shared helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AWS_SBG_INVITE_DBV', '1' );
define( 'AWS_SBG_INVITE_MAX_UPLOAD', 8 * 1024 * 1024 ); // 8 MB — original quality kept for creatives

/* ============================================================================
 * 1. DATABASE SCHEMA
 * ============================================================================ */

function aws_sbg_invite_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table = $wpdb->prefix . 'aws_invite_submissions';
	dbDelta( "CREATE TABLE $table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		event_id bigint(20) NOT NULL DEFAULT 0,
		role varchar(30) NOT NULL DEFAULT '',
		name varchar(255) NOT NULL DEFAULT '',
		email varchar(255) NOT NULL DEFAULT '',
		phone varchar(50) NOT NULL DEFAULT '',
		shield_line varchar(255) NOT NULL DEFAULT '',
		reference varchar(50) NOT NULL DEFAULT '',
		photo_id bigint(20) NOT NULL DEFAULT 0,
		payload longtext,
		consent longtext,
		status varchar(20) NOT NULL DEFAULT 'received',
		ip_address varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY event_idx (event_id),
		KEY role_idx (role),
		KEY name_idx (name),
		KEY email_idx (email)
	) $charset_collate;" );
}
add_action( 'after_switch_theme', 'aws_sbg_invite_init_db' );
add_action( 'admin_init', 'aws_sbg_invite_init_db', 10 );

/* ============================================================================
 * 2. EVENT CUSTOM POST TYPE
 * ============================================================================ */

function aws_sbg_invite_register_cpt() {
	register_post_type( 'aws_invite_event', array(
		'labels'          => array(
			'name'          => 'Invitation Events',
			'singular_name' => 'Invitation Event',
			'add_new_item'  => 'Add New Invitation Event',
			'edit_item'     => 'Edit Invitation Event',
			'all_items'     => 'All Invitation Events',
			'menu_name'     => 'Invitation Events',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'aws-sbg-suite',
		'supports'        => array( 'title', 'editor' ),
		'has_archive'     => false,
		'rewrite'         => false,
		'menu_icon'       => 'dashicons-id-alt',
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'aws_sbg_invite_register_cpt', 5 );

/* ============================================================================
 * 3. FIXED FIELD SCHEMA
 * ============================================================================ */

function aws_sbg_invite_roles() {
	return array(
		'speaker'          => 'Speaker',
		'guest'            => 'Guest',
		'community_leader' => 'AWS Community Leader',
	);
}

function aws_sbg_invite_programs() {
	return array(
		'community_builder' => 'AWS Community Builder',
		'sbg_leader'        => 'SBG Leader',
	);
}

function aws_sbg_invite_slot_options() {
	return array(
		'early' => 'Early',
		'mid'   => 'Mid',
		'last'  => 'Last',
	);
}

/**
 * Role-specific fields. Every role shares the common fields (name, email,
 * phone, vehicle, photo, consent); these are the conditional extras.
 */
function aws_sbg_invite_role_fields() {
	return array(
		'speaker'          => array(
			array( 'id' => 'topic',   'label' => 'Topic / Session title', 'type' => 'text',     'required' => 1 ),
			array( 'id' => 'slot',    'label' => 'Preferred slot',        'type' => 'select',   'required' => 1, 'options' => array( 'early' => 'Early', 'mid' => 'Mid', 'last' => 'Last' ) ),
			array( 'id' => 'comment', 'label' => 'Comment for the organizer', 'type' => 'textarea', 'required' => 0 ),
		),
		'guest'            => array(),
		'community_leader' => array(
			array( 'id' => 'program', 'label' => 'Program',       'type' => 'select', 'required' => 1, 'options' => array( 'community_builder' => 'AWS Community Builder', 'sbg_leader' => 'SBG Leader' ) ),
			array( 'id' => 'term',    'label' => 'Term / Tenure', 'type' => 'text',   'required' => 1, 'placeholder' => 'e.g. 2025–26', 'help' => 'SBG Leaders: your one-year leadership term (e.g. 2025–26). AWS Community Builders: your current or most recent term (e.g. 2025–26).' ),
			array( 'id' => 'about',   'label' => 'About you',     'type' => 'textarea', 'required' => 1, 'help' => 'Your AWS community journey, the institute or organization you represent, and your contribution.' ),
		),
	);
}

/* ============================================================================
 * 4. HELPERS
 * ============================================================================ */

function aws_sbg_invite_get_event_meta( $event_id ) {
	$defaults = array(
		'event_status'    => 'open',
		'photo_required'  => 'yes',
		'allow_guests'    => 'yes',
		'confirm_message' => 'Thank you — your invitation is confirmed.',
		'whatsapp_url'    => '',
		'deadline'        => '',
		'notify_admin'    => 'yes',
	);

	$meta = array();
	foreach ( $defaults as $key => $default ) {
		$val          = get_post_meta( $event_id, $key, true );
		$meta[ $key ] = ( '' === $val || null === $val ) ? $default : $val;
	}
	return $meta;
}

function aws_sbg_invite_resolve_event( $ref ) {
	$ref = trim( (string) $ref );
	if ( '' === $ref ) {
		return null;
	}
	$post = is_numeric( $ref ) ? get_post( (int) $ref ) : get_page_by_path( $ref, OBJECT, 'aws_invite_event' );
	if ( ! $post || 'aws_invite_event' !== $post->post_type ) {
		return null;
	}
	return $post;
}

function aws_sbg_invite_is_open( $event_id ) {
	$post = get_post( $event_id );
	if ( ! $post || 'aws_invite_event' !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}
	$meta = aws_sbg_invite_get_event_meta( $event_id );
	if ( 'open' !== $meta['event_status'] ) {
		return false;
	}
	if ( ! empty( $meta['deadline'] ) && strtotime( $meta['deadline'] ) < current_time( 'timestamp' ) ) {
		return false;
	}
	return true;
}

function aws_sbg_invite_role_allowed( $role, $event_id ) {
	$roles = aws_sbg_invite_roles();
	if ( ! array_key_exists( $role, $roles ) ) {
		return false;
	}
	$meta = aws_sbg_invite_get_event_meta( $event_id );
	if ( 'guest' === $role && 'yes' !== $meta['allow_guests'] ) {
		return false;
	}
	return true;
}

function aws_sbg_invite_sanitize_field( $type, $value ) {
	switch ( $type ) {
		case 'email':
			return sanitize_email( $value );
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'select':
		case 'radio':
			return sanitize_key( $value );
		default:
			return sanitize_text_field( $value );
	}
}

function aws_sbg_invite_build_shield_line( $name, $role, $payload ) {
	$name = trim( (string) $name );

	if ( 'community_leader' === $role ) {
		$programs = aws_sbg_invite_programs();
		$program  = isset( $payload['program'], $programs[ $payload['program'] ] ) ? $programs[ $payload['program'] ] : 'AWS Community Leader';
		$term     = isset( $payload['term'] ) ? trim( (string) $payload['term'] ) : '';
		return trim( $name . ' · ' . $program . ( $term ? ' · ' . $term : '' ) );
	}

	$roles = aws_sbg_invite_roles();
	return trim( $name . ' · ' . ( $roles[ $role ] ?? 'Invitee' ) );
}

function aws_sbg_invite_get_count( $event_id ) {
	$event_id  = intval( $event_id );
	$cache_key = 'aws_sbg_invite_count_' . $event_id;
	$count     = get_transient( $cache_key );

	if ( false === $count ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_invite_submissions';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE event_id = %d", $event_id ) );
		set_transient( $cache_key, $count, 60 );
	}
	return (int) $count;
}

function aws_sbg_invite_invalidate_count( $event_id ) {
	delete_transient( 'aws_sbg_invite_count_' . intval( $event_id ) );
}
