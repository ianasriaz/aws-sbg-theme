<?php
/**
 * AWS SBG Team Hiring & Volunteer System — Submission Handler
 * File: team-handler.php
 *
 * Processes candidate applications for Event Crew & Core Team,
 * validates roles against event scope, and sends confirmation emails.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_team_redirect_back( $event_id, $args = array() ) {
	$redirect = '';
	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
		$redirect = wp_unslash( $_POST['_wp_http_referer'] );
	}

	if ( ! $redirect ) {
		$post = get_post( $event_id );
		$slug = ( $post && ! empty( $post->post_name ) ) ? $post->post_name : '';
		$redirect = home_url( '/join/' . ( $slug ? '?event=' . $slug : '' ) );
	}

	// Remove status query arguments
	$redirect = remove_query_arg( array( 'join_status', 'join_err', 'ref', 'join_name' ), $redirect );

	if ( ! empty( $args ) ) {
		$redirect = add_query_arg( $args, $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;
}

function aws_sbg_team_handle_submission() {
	if ( ! isset( $_POST['team_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['team_nonce'] ) ), 'aws_sbg_team_submit' ) ) {
		wp_die( 'Security check failed. Please refresh the page and try again.', 'Security Check', array( 'response' => 403 ) );
	}

	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	if ( ! $event_id ) {
		wp_die( 'Invalid event.', 'Bad Request', array( 'response' => 400 ) );
	}

	// Honeypot check
	if ( ! empty( $_POST['website_url'] ) ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'spam',
		) );
	}

	$role = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : 'event_crew';
	if ( ! in_array( $role, array( 'event_crew', 'core_team' ), true ) ) {
		$role = 'event_crew';
	}

	// Check if role is allowed and open
	if ( ! aws_sbg_team_is_role_allowed( $role, $event_id ) ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'role_closed',
		) );
	}

	// Extract and sanitize inputs
	$name                 = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email                = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone                = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$city                 = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$university           = isset( $_POST['university'] ) ? sanitize_text_field( wp_unslash( $_POST['university'] ) ) : '';
	$linkedin_url         = isset( $_POST['linkedin_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['linkedin_url'] ) ) ) : '';
	$primary_department   = isset( $_POST['primary_department'] ) ? sanitize_key( wp_unslash( $_POST['primary_department'] ) ) : '';
	$secondary_department = isset( $_POST['secondary_department'] ) ? sanitize_key( wp_unslash( $_POST['secondary_department'] ) ) : '';
	$past_experience      = isset( $_POST['past_experience'] ) ? sanitize_key( wp_unslash( $_POST['past_experience'] ) ) : 'no';
	$experience_details   = isset( $_POST['experience_details'] ) ? sanitize_text_field( wp_unslash( $_POST['experience_details'] ) ) : '';
	$pitch                = isset( $_POST['pitch'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pitch'] ) ) : '';
	$commitment           = isset( $_POST['commitment'] ) ? sanitize_key( wp_unslash( $_POST['commitment'] ) ) : '';

	// Validation
	if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $city ) || empty( $university ) || empty( $linkedin_url ) || empty( $primary_department ) || empty( $pitch ) ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'required',
		) );
	}

	if ( ! is_email( $email ) ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'email',
		) );
	}

	if ( 'yes' !== $commitment ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'commitment',
		) );
	}

	// Generate Reference
	$reference = 'TEAM-' . strtoupper( wp_generate_password( 6, false, false ) );

	$ip = '';
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	// Insert into DB
	global $wpdb;
	$table = $wpdb->prefix . 'aws_team_applications';
	$inserted = $wpdb->insert(
		$table,
		array(
			'event_id'             => $event_id,
			'reference'            => $reference,
			'role'                 => $role,
			'name'                 => $name,
			'email'                => $email,
			'phone'                => $phone,
			'city'                 => $city,
			'university'           => $university,
			'linkedin_url'         => $linkedin_url,
			'primary_department'   => $primary_department,
			'secondary_department' => $secondary_department,
			'past_experience'      => $past_experience,
			'experience_details'   => $experience_details,
			'pitch'                => $pitch,
			'status'               => 'received',
			'admin_notes'          => '',
			'ip_address'           => $ip,
			'created_at'           => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( false === $inserted ) {
		aws_sbg_team_redirect_back( $event_id, array(
			'join_status' => 'error',
			'join_err'    => 'db',
		) );
	}

	$event = get_post( $event_id );
	$event_title = $event ? $event->post_title : 'AWS Event';
	$roles_meta = aws_sbg_team_roles();
	$role_label = isset( $roles_meta[ $role ]['label'] ) ? $roles_meta[ $role ]['label'] : $role;
	$depts = aws_sbg_team_departments( $role );
	$primary_dept_label = isset( $depts[ $primary_department ] ) ? $depts[ $primary_department ] : $primary_department;

	// Send Applicant Confirmation Email
	if ( function_exists( 'aws_sbg_mail_send' ) ) {
		$subject = 'Application Received: ' . $role_label . ' — ' . $event_title;
		$body_html = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#0f172a;">' . esc_html( $name ) . '</strong>,</p>'
			. '<p style="margin:0 0 12px 0;">Thank you for applying to join the <strong style="color:#0f172a;">' . esc_html( $role_label ) . '</strong> team for <strong style="color:#0f172a;">' . esc_html( $event_title ) . '</strong>!</p>'
			. '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #7928ca;border-radius:6px;padding:14px 18px;margin:16px 0;">'
			. '<p style="margin:0 0 6px 0;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Application Summary</p>'
			. '<p style="margin:0 0 4px 0;font-size:14px;color:#0f172a;"><strong>Track:</strong> ' . esc_html( $role_label ) . '</p>'
			. '<p style="margin:0 0 4px 0;font-size:13px;color:#334155;"><strong>Department:</strong> ' . esc_html( $primary_dept_label ) . '</p>'
			. '<p style="margin:0 0 4px 0;font-size:13px;color:#334155;"><strong>Campus / Org:</strong> ' . esc_html( $university ) . '</p>'
			. '<p style="margin:8px 0 0 0;font-size:12px;color:#64748b;font-family:monospace;">Reference: ' . esc_html( $reference ) . '</p>'
			. '</div>'
			. '<p style="margin:0 0 12px 0;">Our leadership team reviews all applications. Shortlisted candidates will be contacted via WhatsApp/Email for an informal orientation chat and next steps.</p>'
			. '<p style="margin:0;color:#64748b;font-size:13px;">Have questions? Feel free to reply to this email.</p>';

		$closing = 'Looking forward to building an unforgettable event together!<br><strong>The AWS Student Builder Group Team</strong>';

		aws_sbg_mail_send( 'team', $email, $subject, 'Team Hiring', 'Application Received', $body_html, '', '', '', '', $closing );
	}

	// Send Admin Notification Email
	$event_meta = aws_sbg_team_get_event_meta( $event_id );
	if ( 'yes' === $event_meta['notify_admin'] && function_exists( 'aws_sbg_mail_send' ) ) {
		$admin_email = get_option( 'admin_email' );
		if ( $admin_email ) {
			$admin_subject = 'New ' . $role_label . ' Application: ' . $name . ' (' . $university . ')';
			$admin_body = '<p style="margin:0 0 12px 0;">A new candidate applied for <strong style="color:#0f172a;">' . esc_html( $event_title ) . '</strong>:</p>'
				. '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:14px 18px;margin:16px 0;">'
				. '<p style="margin:0 0 6px 0;font-size:14px;"><strong>Candidate:</strong> ' . esc_html( $name ) . ' (' . esc_html( $city ) . ')</p>'
				. '<p style="margin:0 0 6px 0;font-size:13px;"><strong>Campus / University:</strong> ' . esc_html( $university ) . '</p>'
				. '<p style="margin:0 0 6px 0;font-size:13px;"><strong>Email:</strong> ' . esc_html( $email ) . ' | <strong>Phone:</strong> ' . esc_html( $phone ) . '</p>'
				. '<p style="margin:0 0 6px 0;font-size:13px;"><strong>LinkedIn:</strong> <a href="' . esc_url( $linkedin_url ) . '" target="_blank">' . esc_html( $linkedin_url ) . '</a></p>'
				. '<p style="margin:10px 0 4px 0;font-size:14px;font-weight:700;color:#0f172a;">Applied For: ' . esc_html( $role_label ) . ' &middot; ' . esc_html( $primary_dept_label ) . '</p>'
				. '<p style="margin:8px 0 0 0;font-size:13px;color:#334155;background:#ffffff;border:1px solid #e2e8f0;padding:10px 12px;border-radius:4px;white-space:pre-wrap;">' . esc_html( $pitch ) . '</p>'
				. '</div>';

			$admin_dashboard_url = admin_url( 'admin.php?page=aws-team-hiring&event_id=' . $event_id );
			aws_sbg_mail_send( 'team', $admin_email, $admin_subject, 'Team Hiring', 'New Application', $admin_body, $admin_dashboard_url, 'Review in Dashboard' );
		}
	}

	// Redirect to success
	aws_sbg_team_redirect_back( $event_id, array(
		'join_status' => 'success',
		'ref'         => $reference,
		'join_name'   => $name,
	) );
}
add_action( 'admin_post_nopriv_aws_sbg_team_submit', 'aws_sbg_team_handle_submission' );
add_action( 'admin_post_aws_sbg_team_submit', 'aws_sbg_team_handle_submission' );
