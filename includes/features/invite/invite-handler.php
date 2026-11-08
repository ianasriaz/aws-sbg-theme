<?php
/**
 * AWS SBG Invitation Confirmation — Submission Handler
 * File: invite-handler.php
 *
 * Native admin-post.php POST: nonce + honeypot, per-type sanitization,
 * role/consent/vehicle validation, photo upload, email, PRG redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_invite_submission_error( $event_id, $code ) {
	$url = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$url = add_query_arg( array( 'invite_status' => 'error', 'invite_error' => $code, 'invite_event' => intval( $event_id ) ), $url );
	wp_safe_redirect( $url );
	exit;
}

function aws_sbg_invite_handle_submission() {
	if ( ! isset( $_POST['invite_nonce'] ) || ! wp_verify_nonce( $_POST['invite_nonce'], 'aws_sbg_invite_submit' ) ) {
		wp_die( 'Security check failed.', 'Error', array( 'response' => 403 ) );
	}

	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	if ( ! aws_sbg_invite_is_open( $event_id ) ) {
		aws_sbg_invite_submission_error( $event_id, 'closed' );
	}

	if ( ! empty( $_POST['website_url'] ) ) { // honeypot
		aws_sbg_invite_submission_error( $event_id, 'spam' );
	}

	$role = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : '';
	if ( 'community_leader' === $role ) {
		$role = 'community_partner';
	}
	if ( ! aws_sbg_invite_role_allowed( $role, $event_id ) ) {
		aws_sbg_invite_submission_error( $event_id, 'role' );
	}

	$meta  = aws_sbg_invite_get_event_meta( $event_id );
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$cnic  = isset( $_POST['cnic'] ) ? sanitize_text_field( wp_unslash( $_POST['cnic'] ) ) : '';

	if ( '' === trim( $name ) || '' === trim( $phone ) || '' === trim( $cnic ) ) {
		aws_sbg_invite_submission_error( $event_id, 'required' );
	}
	if ( ! is_email( $email ) ) {
		aws_sbg_invite_submission_error( $event_id, 'email' );
	}

	// Consent is always required.
	$consent = ( isset( $_POST['consent'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['consent'] ) ) );
	if ( ! $consent ) {
		aws_sbg_invite_submission_error( $event_id, 'consent' );
	}

	$is_partner = ( 'community_partner' === $role || 'community_leader' === $role );
	$partner_promo_consent = ( isset( $_POST['partner_promo_consent'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['partner_promo_consent'] ) ) );
	if ( $is_partner && ! $partner_promo_consent ) {
		aws_sbg_invite_submission_error( $event_id, 'consent' );
	}

	// Vehicle (optional, but if present the type + number are required).
	$has_vehicle   = ( isset( $_POST['has_vehicle'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['has_vehicle'] ) ) ) ? 'yes' : 'no';
	$vehicle_type  = '';
	$vehicle_number = '';
	if ( 'yes' === $has_vehicle ) {
		$vehicle_type   = isset( $_POST['vehicle_type'] ) ? sanitize_key( wp_unslash( $_POST['vehicle_type'] ) ) : '';
		$vehicle_number = isset( $_POST['vehicle_number'] ) ? sanitize_text_field( wp_unslash( $_POST['vehicle_number'] ) ) : '';
		if ( ! in_array( $vehicle_type, array( 'bike', 'car' ), true ) || '' === trim( $vehicle_number ) ) {
			aws_sbg_invite_submission_error( $event_id, 'vehicle' );
		}
	}

	// Role-specific fields, sanitized and validated against the fixed schema.
	$role_fields = aws_sbg_invite_role_fields()[ $role ];
	$payload     = array(
		'cnic'                  => $cnic,
		'vehicle'               => $has_vehicle,
		'vehicle_type'          => $vehicle_type,
		'vehicle_number'        => $vehicle_number,
		'partner_promo_consent' => $partner_promo_consent ? 'yes' : 'no',
	);
	$raw_role = isset( $_POST['role_field'] ) ? (array) wp_unslash( $_POST['role_field'] ) : array();
	foreach ( $role_fields as $field ) {
		$fid = $field['id'];
		$val = isset( $raw_role[ $fid ] ) ? $raw_role[ $fid ] : '';
		$val = aws_sbg_invite_sanitize_field( $field['type'], $val );

		if ( ! empty( $field['required'] ) && '' === trim( (string) $val ) ) {
			aws_sbg_invite_submission_error( $event_id, 'required' );
		}
		if ( 'select' === $field['type'] && '' !== $val && ! array_key_exists( $val, $field['options'] ) ) {
			aws_sbg_invite_submission_error( $event_id, 'role' );
		}
		$payload[ $fid ] = $val;
	}

	// Fresh photo (used for creatives). Original is kept; intermediate sizes are skipped.
	$photo_id   = 0;
	$photo_required = ( 'yes' === $meta['photo_required'] );
	if ( isset( $_FILES['photo'] ) && ! empty( $_FILES['photo']['name'] ) ) {
		$file = $_FILES['photo'];
		if ( $file['size'] > AWS_SBG_INVITE_MAX_UPLOAD ) {
			aws_sbg_invite_submission_error( $event_id, 'filesize' );
		}
		$allowed_mimes = array( 'image/jpeg', 'image/png', 'image/webp' );
		$check         = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( ! in_array( $check['type'], $allowed_mimes, true ) ) {
			aws_sbg_invite_submission_error( $event_id, 'filetype' );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$skip_sizes = function () { return array(); };
		add_filter( 'intermediate_image_sizes_advanced', $skip_sizes );
		$attach_id = media_handle_upload( 'photo', 0 );
		remove_filter( 'intermediate_image_sizes_advanced', $skip_sizes );

		if ( is_wp_error( $attach_id ) ) {
			aws_sbg_invite_submission_error( $event_id, 'upload' );
		}
		$photo_id = (int) $attach_id;
	}
	if ( $photo_required && ! $photo_id ) {
		aws_sbg_invite_submission_error( $event_id, 'photo' );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_invite_submissions';

	// Ensure table exists (self-healing architecture)
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
		aws_sbg_invite_init_db();
	}

	$shield_line = aws_sbg_invite_build_shield_line( $name, $role, $payload );
	$reference   = aws_sbg_invite_generate_reference( $event_id );
	$ip          = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$insert_data = array(
		'event_id'    => $event_id,
		'role'        => $role,
		'name'        => $name,
		'email'       => $email,
		'phone'       => $phone,
		'shield_line' => $shield_line,
		'reference'   => $reference,
		'photo_id'    => $photo_id,
		'payload'     => wp_json_encode( $payload ),
		'consent'     => wp_json_encode( array( 'agreed' => true, 'agreed_at' => current_time( 'mysql' ) ) ),
		'status'      => 'received',
		'ip_address'  => $ip,
		'created_at'  => current_time( 'mysql' ),
	);
	$insert_format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );

	$inserted = $wpdb->insert( $table, $insert_data, $insert_format );

	if ( false === $inserted ) {
		error_log( 'AWS SBG Invite DB Insert Error: ' . $wpdb->last_error . ' | Payload: ' . wp_json_encode( $insert_data ) );

		// Attempt self-healing migration and retry insert once
		aws_sbg_invite_init_db();
		$inserted = $wpdb->insert( $table, $insert_data, $insert_format );

		if ( false === $inserted ) {
			// Emergency persistent backup in wp_options so NO SUBMISSION IS EVER LOST
			$backups   = get_option( 'aws_invite_emergency_backups', array() );
			$backups[] = array_merge( $insert_data, array( 'error' => $wpdb->last_error, 'saved_at' => current_time( 'mysql' ) ) );
			update_option( 'aws_invite_emergency_backups', $backups, false );
			aws_sbg_invite_submission_error( $event_id, 'db' );
		}
	}

	aws_sbg_invite_invalidate_count( $event_id );
	aws_sbg_invite_send_notifications( $event_id, $email, $name, $role, $reference, $payload, $meta );

	$url = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$url = add_query_arg( array(
		'invite_status' => 'success',
		'ref'           => $reference,
		'invite_event'  => $event_id,
		'role'          => $role,
		'invite_name'   => rawurlencode( $name ),
	), $url );
	wp_safe_redirect( $url );
	exit;
}
add_action( 'admin_post_nopriv_aws_sbg_invite_submit', 'aws_sbg_invite_handle_submission' );
add_action( 'admin_post_aws_sbg_invite_submit', 'aws_sbg_invite_handle_submission' );

function aws_sbg_invite_generate_reference( $event_id ) {
	global $wpdb;
	$table  = $wpdb->prefix . 'aws_invite_submissions';
	$max_id = (int) $wpdb->get_var( "SELECT MAX(id) FROM $table" );

	$event  = get_post( $event_id );
	$prefix = 'INV';
	if ( $event ) {
		$prefix = strtoupper( substr( sanitize_title( $event->post_title ), 0, 4 ) );
		if ( '' === $prefix ) {
			$prefix = 'INV';
		}
	}
	return $prefix . '-' . ( 100 + $max_id + 1 );
}

function aws_sbg_invite_send_notifications( $event_id, $to, $name, $role, $reference, $payload, $meta ) {
	if ( ! function_exists( 'aws_sbg_mail_send' ) ) {
		return;
	}

	$roles            = aws_sbg_invite_roles();
	$role_label       = $roles[ $role ] ?? 'Invitee';
	$event            = get_post( $event_id );
	$event_name       = $event ? wp_strip_all_tags( $event->post_title ) : 'the event';
	$clean_event_name = html_entity_decode( $event_name, ENT_QUOTES, 'UTF-8' );
	$is_partner       = ( 'community_partner' === $role || 'community_leader' === $role );
	$partner_wa       = 'https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l?s=cl&p=a&iam=2';

	// Submitter confirmation.
	if ( $is_partner ) {
		$email_kicker = 'AWS Community Partner';
		$email_title  = "You're officially part of the team!";
		$subject      = "You're officially part of the team! — " . $clean_event_name;

		$confirm_body = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#0f172a;">' . esc_html( $name ) . '</strong>,</p>'
			. '<p style="margin:0 0 12px 0;">Thank you for accepting our invitation!</p>'
			. '<p style="margin:0 0 12px 0;">As an AWS Community Leader and official AWS Community Partner, you\'re invited to represent your community at the AWS re:Invent 2026 Watch &amp; Build Party in Islamabad.</p>'
			. '<p style="margin:0 0 12px 0;">We\'ll come together as community leaders and partners, each representing our respective communities while collectively representing AWS.</p>'
			. '<p style="margin:0 0 12px 0;color:#64748b;">Your event pass will be shared separately.</p>'
			. '<p style="margin:0 0 6px 0;">Join our WhatsApp group for event updates and announcements.</p>';

		$closing_html = '<p style="margin:0;color:#475569;">Looking forward to bringing the AWS community together!</p>';
	} else {
		$email_kicker = 'Invitation Confirmed';
		$email_title  = 'Thank you for confirming';
		$subject      = 'Invitation confirmed — ' . $clean_event_name;

		$confirm_body = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#0f172a;">' . esc_html( $name ) . '</strong>,</p>'
			. '<p style="margin:0 0 12px 0;">Thank you for accepting our invitation!</p>'
			. '<p style="margin:0 0 12px 0;">Your invitation to <strong style="color:#0f172a;">' . esc_html( $clean_event_name ) . '</strong> is confirmed.</p>'
			. '<p style="margin:0 0 12px 0;">Reference: <strong style="color:#7928ca;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">' . esc_html( $reference ) . '</strong></p>'
			. ( ! empty( $payload['cnic'] ) ? '<p style="margin:0 0 12px 0;">CNIC for Gate Clearance: <strong style="color:#0f172a;">' . esc_html( $payload['cnic'] ) . '</strong></p>' : '' )
			. ( ! empty( $meta['confirm_message'] ) ? '<p style="margin:0 0 12px 0;">' . esc_html( $meta['confirm_message'] ) . '</p>' : '' );

		$closing_html = '<p style="margin:0;color:#475569;">Looking forward to bringing the AWS community together!</p>';
	}

	$cta_url   = $is_partner ? $partner_wa : ( ! empty( $meta['whatsapp_url'] ) ? $meta['whatsapp_url'] : '' );
	$cta_label = ( $is_partner || $cta_url ) ? 'Join WhatsApp Group' : '';

	aws_sbg_mail_send( 'invite', $to, $subject, $email_kicker, $email_title, $confirm_body, $cta_url, $cta_label, '', '', $closing_html );

	// Admin notification.
	if ( 'yes' === $meta['notify_admin'] ) {
		$mail_settings = function_exists( 'aws_sbg_mail_get_settings' ) ? aws_sbg_mail_get_settings() : array();
		$admin_to      = ! empty( $mail_settings['admin_email'] ) ? $mail_settings['admin_email'] : get_option( 'admin_email' );

		$extra = '';
		if ( 'speaker' === $role ) {
			$extra = '<p style="margin:0 0 6px 0;">Topic: ' . esc_html( $payload['topic'] ?? '' ) . ' · Slot: ' . esc_html( ucfirst( $payload['slot'] ?? '' ) ) . '</p>';
			if ( ! empty( $payload['comment'] ) ) {
				$extra .= '<p style="margin:0 0 6px 0;">Comment: ' . esc_html( $payload['comment'] ) . '</p>';
			}
		} elseif ( $is_partner ) {
			$uni = ! empty( $payload['university'] ) ? $payload['university'] : ( $payload['program'] ?? '' );
			$extra = '<p style="margin:0 0 6px 0;">University: ' . esc_html( $uni ) . '</p>';
			if ( ! empty( $payload['about'] ) ) {
				$extra .= '<p style="margin:0 0 6px 0;">About &amp; Term: ' . esc_html( $payload['about'] ) . '</p>';
			}
		} elseif ( 'guest' === $role ) {
			if ( ! empty( $payload['about'] ) ) {
				$extra = '<p style="margin:0 0 6px 0;">About: ' . esc_html( $payload['about'] ) . '</p>';
			}
		}

		$admin_body = '<p style="margin:0 0 12px 0;">New invitation confirmation:</p>'
			. '<p style="margin:0 0 6px 0;"><strong style="color:#0f172a;">' . esc_html( $name ) . '</strong> — ' . esc_html( $role_label ) . '</p>'
			. '<p style="margin:0 0 6px 0;">CNIC (Gate Clearance): <strong style="color:#0f172a;">' . esc_html( $payload['cnic'] ?? '—' ) . '</strong></p>'
			. '<p style="margin:0 0 6px 0;">Ref: ' . esc_html( $reference ) . ' · Vehicle: ' . esc_html( $payload['vehicle'] ?? 'no' ) . ( ! empty( $payload['vehicle_number'] ) ? ' (' . esc_html( $payload['vehicle_number'] ) . ')' : '' ) . '</p>'
			. $extra;
		aws_sbg_mail_send( 'invite', $admin_to, 'New invitation confirmation — ' . esc_html( $reference ), 'New Confirmation', 'Review confirmation', $admin_body, admin_url( 'admin.php?page=aws-invite-submissions&event_id=' . $event_id ), 'Open confirmations' );
	}
}
