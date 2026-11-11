<?php
/**
 * AWS SBG Call for Speakers — Submission Handler
 * File: cfs-handler.php
 *
 * Processes public session pitches, validates honeypot & nonces, inserts into DB,
 * and sends confirmation emails to the applicant and organizers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_cfs_redirect_back( $event_id, $args = array() ) {
	$redirect = '';
	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
		$redirect = wp_unslash( $_POST['_wp_http_referer'] );
	}

	if ( ! $redirect ) {
		$post = get_post( $event_id );
		$slug = ( $post && ! empty( $post->post_name ) ) ? $post->post_name : '';
		$redirect = home_url( '/speak/' . ( $slug ? '?event=' . $slug : '' ) );
	}

	// Remove any existing status query parameters
	$redirect = remove_query_arg( array( 'speak_status', 'speak_err', 'ref', 'speak_name' ), $redirect );

	if ( ! empty( $args ) ) {
		$redirect = add_query_arg( $args, $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;
}

function aws_sbg_cfs_handle_submission() {
	if ( ! isset( $_POST['cfs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cfs_nonce'] ) ), 'aws_sbg_cfs_submit' ) ) {
		wp_die( 'Security check failed. Please refresh the page and try again.', 'Security Check', array( 'response' => 403 ) );
	}

	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	if ( ! $event_id ) {
		wp_die( 'Invalid event.', 'Bad Request', array( 'response' => 400 ) );
	}

	// Honeypot check
	if ( ! empty( $_POST['website_url'] ) ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'spam',
		) );
	}

	// Check if event is open for CFS
	if ( ! aws_sbg_cfs_is_open( $event_id ) ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'closed',
		) );
	}

	// Extract and sanitize inputs
	$name            = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email           = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone           = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$city            = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$organization    = isset( $_POST['organization'] ) ? sanitize_text_field( wp_unslash( $_POST['organization'] ) ) : '';
	$linkedin_url    = isset( $_POST['linkedin_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['linkedin_url'] ) ) ) : '';
	$talk_title      = isset( $_POST['talk_title'] ) ? sanitize_text_field( wp_unslash( $_POST['talk_title'] ) ) : '';
	$talk_pitch      = isset( $_POST['talk_pitch'] ) ? sanitize_textarea_field( wp_unslash( $_POST['talk_pitch'] ) ) : '';
	$track           = isset( $_POST['track'] ) ? sanitize_key( wp_unslash( $_POST['track'] ) ) : '';
	$audience_level  = isset( $_POST['audience_level'] ) ? sanitize_key( wp_unslash( $_POST['audience_level'] ) ) : '100';
	$format          = isset( $_POST['format'] ) ? sanitize_key( wp_unslash( $_POST['format'] ) ) : 'lightning';
	$past_experience = isset( $_POST['past_experience'] ) ? sanitize_key( wp_unslash( $_POST['past_experience'] ) ) : 'no';
	$experience_link = isset( $_POST['experience_link'] ) ? esc_url_raw( trim( wp_unslash( $_POST['experience_link'] ) ) ) : '';
	$commitment      = isset( $_POST['commitment'] ) ? sanitize_key( wp_unslash( $_POST['commitment'] ) ) : '';

	// Validation
	if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $organization ) || empty( $linkedin_url ) || empty( $talk_title ) || empty( $talk_pitch ) || empty( $track ) ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'required',
		) );
	}

	if ( ! is_email( $email ) ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'email',
		) );
	}

	if ( 'yes' !== $commitment ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'commitment',
		) );
	}

	$tracks  = aws_sbg_cfs_tracks();
	$formats = aws_sbg_cfs_formats();
	$levels  = aws_sbg_cfs_levels();

	if ( ! array_key_exists( $track, $tracks ) ) {
		$track = 'other';
	}
	if ( ! array_key_exists( $format, $formats ) ) {
		$format = 'lightning';
	}
	if ( ! array_key_exists( $audience_level, $levels ) ) {
		$audience_level = '100';
	}

	// Generate Reference
	$reference = 'CFS-' . strtoupper( wp_generate_password( 6, false, false ) );

	$ip = '';
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	// Insert into DB
	global $wpdb;
	$table = $wpdb->prefix . 'aws_cfs_submissions';
	$inserted = $wpdb->insert(
		$table,
		array(
			'event_id'        => $event_id,
			'reference'       => $reference,
			'name'            => $name,
			'email'           => $email,
			'phone'           => $phone,
			'city'            => $city,
			'organization'    => $organization,
			'linkedin_url'    => $linkedin_url,
			'talk_title'      => $talk_title,
			'talk_pitch'      => $talk_pitch,
			'track'           => $track,
			'audience_level'  => $audience_level,
			'format'          => $format,
			'past_experience' => $past_experience,
			'experience_link' => $experience_link,
			'status'          => 'received',
			'admin_notes'     => '',
			'ip_address'      => $ip,
			'created_at'      => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( false === $inserted ) {
		aws_sbg_cfs_redirect_back( $event_id, array(
			'speak_status' => 'error',
			'speak_err'    => 'db',
		) );
	}

	// Event details for emails
	$event = get_post( $event_id );
	$event_title = $event ? $event->post_title : 'AWS Community Event';
	$track_label = isset( $tracks[ $track ] ) ? $tracks[ $track ] : $track;
	$format_label = isset( $formats[ $format ] ) ? $formats[ $format ] : $format;
	$level_label = isset( $levels[ $audience_level ] ) ? $levels[ $audience_level ] : $audience_level;

	// Send Submitter Confirmation Email
	if ( function_exists( 'aws_sbg_mail_send' ) ) {
		$subject = 'Pitch Received: ' . $talk_title . ' — ' . $event_title;
		$body_html = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#0f172a;">' . esc_html( $name ) . '</strong>,</p>'
			. '<p style="margin:0 0 12px 0;">Thank you for pitching your session for <strong style="color:#0f172a;">' . esc_html( $event_title ) . '</strong>!</p>'
			. '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #7928ca;border-radius:6px;padding:14px 18px;margin:16px 0;">'
			. '<p style="margin:0 0 6px 0;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Session Proposal</p>'
			. '<p style="margin:0 0 6px 0;font-size:15px;font-weight:700;color:#0f172a;">' . esc_html( $talk_title ) . '</p>'
			. '<p style="margin:0 0 4px 0;font-size:13px;color:#334155;"><strong>Track:</strong> ' . esc_html( $track_label ) . '</p>'
			. '<p style="margin:0 0 4px 0;font-size:13px;color:#334155;"><strong>Format:</strong> ' . esc_html( $format_label ) . ' &middot; <strong>Level:</strong> ' . esc_html( $level_label ) . '</p>'
			. '<p style="margin:8px 0 0 0;font-size:12px;color:#64748b;font-family:monospace;">Reference: ' . esc_html( $reference ) . '</p>'
			. '</div>'
			. '<p style="margin:0 0 12px 0;">Our selection committee reviews all proposals. If your pitch is shortlisted, our team will reach out directly with full speaker onboarding and logistics.</p>'
			. '<p style="margin:0;color:#64748b;font-size:13px;">Have questions in the meantime? Simply reply to this email.</p>';

		$closing = 'Looking forward to reviewing your session pitch!<br><strong>The AWS Student Builder Group Team</strong>';

		aws_sbg_mail_send( 'cfs', $email, $subject, 'Call for Speakers', 'Pitch Received', $body_html, '', '', '', '', $closing );
	}

	// Send Admin Notification Email
	$event_meta = aws_sbg_cfs_get_event_meta( $event_id );
	if ( 'yes' === $event_meta['notify_admin'] && function_exists( 'aws_sbg_mail_send' ) ) {
		$admin_email = get_option( 'admin_email' );
		if ( $admin_email ) {
			$admin_subject = 'New Speaker Pitch: ' . $name . ' — ' . $talk_title;
			$admin_body = '<p style="margin:0 0 12px 0;">A new session proposal was submitted for <strong style="color:#0f172a;">' . esc_html( $event_title ) . '</strong>:</p>'
				. '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:14px 18px;margin:16px 0;">'
				. '<p style="margin:0 0 6px 0;font-size:14px;"><strong>Speaker:</strong> ' . esc_html( $name ) . ' (' . esc_html( $organization ) . ')</p>'
				. '<p style="margin:0 0 6px 0;font-size:13px;"><strong>Email:</strong> ' . esc_html( $email ) . ' | <strong>Phone:</strong> ' . esc_html( $phone ) . '</p>'
				. '<p style="margin:0 0 6px 0;font-size:13px;"><strong>LinkedIn:</strong> <a href="' . esc_url( $linkedin_url ) . '" target="_blank">' . esc_html( $linkedin_url ) . '</a></p>'
				. '<p style="margin:10px 0 4px 0;font-size:15px;font-weight:700;color:#0f172a;">' . esc_html( $talk_title ) . '</p>'
				. '<p style="margin:0 0 4px 0;font-size:13px;color:#64748b;"><strong>Track:</strong> ' . esc_html( $track_label ) . ' &middot; <strong>Format:</strong> ' . esc_html( $format_label ) . '</p>'
				. '<p style="margin:8px 0 0 0;font-size:13px;color:#334155;background:#ffffff;border:1px solid #e2e8f0;padding:10px 12px;border-radius:4px;white-space:pre-wrap;">' . esc_html( $talk_pitch ) . '</p>'
				. '</div>';

			$admin_dashboard_url = admin_url( 'admin.php?page=aws-cfs-submissions&event_id=' . $event_id );
			aws_sbg_mail_send( 'cfs', $admin_email, $admin_subject, 'Call for Speakers', 'New Session Proposal', $admin_body, $admin_dashboard_url, 'Review in Dashboard' );
		}
	}

	// Redirect to success
	aws_sbg_cfs_redirect_back( $event_id, array(
		'speak_status' => 'success',
		'ref'          => $reference,
		'speak_name'   => $name,
	) );
}
add_action( 'admin_post_nopriv_aws_sbg_cfs_submit', 'aws_sbg_cfs_handle_submission' );
add_action( 'admin_post_aws_sbg_cfs_submit', 'aws_sbg_cfs_handle_submission' );
