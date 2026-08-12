<?php
/**
 * AWS Mock Exam Core Feature Engine
 * 
 * Handles database table initialization, Custom Post Types (aws_question),
 * Admin dashboards for bulk member code generation & question bank import,
 * Purelymail SMTP integration, and secure REST API endpoints for exam execution.
 * 
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 0. PURELYMAIL SMTP INTEGRATION HOOK
 */
$aws_sbg_last_mail_error = '';

add_action( 'wp_mail_failed', function( $wp_error ) {
	if ( is_wp_error( $wp_error ) ) {
		$GLOBALS['aws_sbg_last_mail_error'] = $wp_error->get_error_message();
	}
} );

function aws_sbg_configure_smtp( $phpmailer ) {
	$smtp = get_option( 'aws_sbg_smtp_config', array() );

	if ( ! empty( $smtp['host'] ) && ! empty( $smtp['username'] ) && ! empty( $smtp['password'] ) ) {
		$phpmailer->isSMTP();
		$phpmailer->Host       = $smtp['host']; // Default: smtp.purelymail.com
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = trim( $smtp['username'] );
		$phpmailer->Password   = trim( $smtp['password'] );

		$encryption            = ! empty( $smtp['encryption'] ) ? strtolower( trim( $smtp['encryption'] ) ) : 'ssl';
		$port                  = intval( $smtp['port'] );

		if ( $port <= 0 ) {
			$port = ( $encryption === 'tls' ) ? 587 : 465;
		}

		$phpmailer->Port       = $port;
		$phpmailer->SMTPSecure = $encryption; // 'ssl' or 'tls'

		// Windows / LocalWP SSL certificate fix
		$phpmailer->SMTPOptions = array(
			'ssl' => array(
				'verify_peer'       => false,
				'verify_peer_name'  => false,
				'allow_self_signed' => true,
			),
		);

		// Purelymail requires envelope sender to match login username or owned domain
		$from_email = ! empty( $smtp['from_email'] ) ? trim( $smtp['from_email'] ) : trim( $smtp['username'] );
		$from_name  = ! empty( $smtp['from_name'] ) ? trim( $smtp['from_name'] ) : 'AWS Air University';

		$phpmailer->From     = $from_email;
		$phpmailer->FromName = $from_name;
		$phpmailer->Sender   = $from_email;
	}
}
add_action( 'phpmailer_init', 'aws_sbg_configure_smtp' );

/**
 * BEAUTIFUL HTML EMAIL GENERATOR & DISPATCHER
 */
function aws_sbg_send_member_exam_email( $code_id ) {
	global $wpdb;
	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$code_row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_codes} WHERE id = %d", $code_id ) );

	if ( ! $code_row || empty( $code_row->member_email ) ) {
		return false;
	}

	$portal_url   = home_url( '/exam-portal/' );
	$track_title  = strtolower( $code_row->exam_track ) === 'aif-c01' ? 'AWS Certified AI Practitioner (AIF-C01)' : 'AWS Certification Preparation Exam - Organized by AWS SBG Air University Islamabad';
	$member_name  = esc_html( $code_row->member_name );
	$access_code  = esc_html( $code_row->access_code );
	$member_email = esc_html( $code_row->member_email );
	$year         = date( 'Y' );

	$subject = "⚡ Your AWS Certification Exam Code: {$access_code} — AWS Air University";

	$html_body = "
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<title>Your AWS Certification Mock Exam Access Code</title>
	<style>
		@media only screen and (max-width: 600px) {
			.email-wrapper { padding: 10px 5px !important; }
			.email-card { border-radius: 16px !important; }
			.email-body { padding: 22px 18px !important; }
			.hero-title { font-size: 19px !important; }
			.code-box { padding: 16px 10px !important; border-radius: 14px !important; }
			.access-code-text { font-size: 25px !important; letter-spacing: 3px !important; }
			.cta-btn { padding: 14px 20px !important; font-size: 14px !important; display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
			.rules-box { padding: 14px !important; border-radius: 12px !important; }
		}
	</style>
</head>
<body style='margin:0; padding:0; background-color:#07040d; font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; color:#e2e8f0;'>
	<table width='100%' border='0' cellspacing='0' cellpadding='0' class='email-wrapper' style='background-color:#07040d; padding: 30px 10px;'>
		<tr>
			<td align='center'>
				<table width='600' border='0' cellspacing='0' cellpadding='0' class='email-card' style='background-color:#140c24; border: 1px solid rgba(173,92,255,0.3); border-radius:24px; overflow:hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.8); max-width: 600px; width: 100%;'>
					
					<!-- BRAND HEADER -->
					<tr>
						<td align='center' style='padding: 28px 24px 20px 24px; background: linear-gradient(135deg, #0f071f 0%, #1e0e3b 100%); border-bottom: 1px solid rgba(173,92,255,0.2);'>
							<table border='0' cellspacing='0' cellpadding='0'>
								<tr>
									<td style='background: linear-gradient(135deg, #ad5cff, #00ef88); padding: 2px; border-radius: 14px;'>
										<div style='background: #07040d; padding: 8px 16px; border-radius: 12px; font-family:\"Segoe UI\", sans-serif; font-weight:900; font-size:16px; color:#ffffff; letter-spacing:1px;'>
											⚡ AWS AIR UNIVERSITY
										</div>
									</td>
								</tr>
							</table>
							<div style='font-size: 11px; color: #a0aec0; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;'>
								AWS Certification Preparation Exam Portal
							</div>
						</td>
					</tr>

					<!-- HERO BODY -->
					<tr>
						<td class='email-body' style='padding: 30px 30px 22px 30px;'>
							<h1 class='hero-title' style='font-size: 21px; font-weight: 800; color: #ffffff; margin: 0 0 10px 0;'>
								Hello, <span style='color: #00ef88;'>{$member_name}</span> 👋
							</h1>
							<p style='font-size: 13px; line-height: 1.6; color: #cbd5e1; margin: 0 0 20px 0;'>
								You have been registered for the official <strong>{$track_title} Preparation Exam</strong> hosted by the <strong>AWS Student Builder Group Air University</strong>.
							</p>

							<!-- ACCESS CODE HIGHLIGHT BOX -->
							<table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 22px;'>
								<tr>
									<td align='center' class='code-box' style='background: #07040d; border: 2px dashed #ad5cff; border-radius: 18px; padding: 20px;'>
										<div style='font-size: 10px; font-weight: 800; color: #ad5cff; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px;'>
											YOUR PERSONAL ACCESS CODE
										</div>
										<div class='access-code-text' style='font-family: monospace; font-size: 28px; font-weight: 900; color: #00ef88; letter-spacing: 4px; margin: 4px 0;'>
											{$access_code}
										</div>
										<div style='font-size: 10px; color: #94a3b8;'>
											Single-use access code assigned to {$member_email}
										</div>
									</td>
								</tr>
							</table>

							<!-- CALL TO ACTION BUTTON -->
							<table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 24px;'>
								<tr>
									<td align='center'>
										<a href='{$portal_url}' target='_blank' class='cta-btn' style='background: linear-gradient(135deg, #00ef88 0%, #00c970 100%); color: #07040d; text-decoration: none; padding: 15px 32px; border-radius: 14px; font-weight: 900; font-size: 14px; display: inline-block; box-shadow: 0 8px 20px rgba(0, 239, 136, 0.3); font-family:\"Segoe UI\", sans-serif;'>
											🚀 Access Exam Portal ➔
										</a>
									</td>
								</tr>
							</table>

							<!-- REWARD BANNER -->
							<table width='100%' border='0' cellspacing='0' cellpadding='0' style='background: rgba(0, 239, 136, 0.08); border: 1px solid rgba(0, 239, 136, 0.3); border-radius: 14px; margin-bottom: 22px;'>
								<tr>
									<td style='padding: 16px;'>
										<div style='font-size: 14px; font-weight: 800; color: #00ef88; margin-bottom: 4px;'>
											🏆 AWS Exam Voucher Reward Opportunity
										</div>
										<div style='font-size: 12px; color: #cbd5e1; line-height: 1.5;'>
											Achieve a score of <strong>700 / 1000 or higher</strong> to become eligible for an official <strong>AWS Certification Exam Voucher (Worth $100 – $150 USD)</strong>!
										</div>
									</td>
								</tr>
							</table>

							<!-- EXAM RULES -->
							<div class='rules-box' style='background: #0c0717; border-radius: 14px; padding: 16px; border: 1px solid #1e1338;'>
								<div style='font-size: 11px; font-weight: 800; color: #ad5cff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;'>
									📌 Exam Guidelines & Integrity Protocol:
								</div>
								<ul style='font-size: 12px; color: #94a3b8; padding-left: 18px; margin: 0; line-height: 1.6;'>
									<li>⏱️ <strong>45s Per Question:</strong> Auto-advances when timer hits 0s.</li>
									<li>🔒 <strong>Single-Pass Mode:</strong> Backtracking is locked for security.</li>
									<li>📺 <strong>Mandatory Fullscreen:</strong> Fullscreen mode is required.</li>
									<li>🛡️ <strong>Anti-Cheating Protection:</strong> Tab switches & window blurs monitored.</li>
								</ul>
							</div>

						</td>
					</tr>

					<!-- FOOTER -->
					<tr>
						<td align='center' style='padding: 16px 20px; background: #07040d; border-top: 1px solid rgba(255,255,255,0.05);'>
							<div style='font-size: 10px; color: #64748b; font-weight: 500;'>
								© {$year} AWS Student Builder Group Air University. All Rights Reserved.
							</div>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	";

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	return wp_mail( $code_row->member_email, $subject, $html_body, $headers );
}

/**
 * 1. CUSTOM POST TYPES & TAXONOMIES (REGISTER FIRST ON INIT)
 */
function aws_sbg_register_question_cpt() {
	register_taxonomy( 'exam_track', array( 'aws_question' ), array(
		'labels'            => array( 'name' => 'Exam Tracks', 'singular_name' => 'Exam Track' ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
	) );

	register_taxonomy( 'exam_domain', array( 'aws_question' ), array(
		'labels'            => array( 'name' => 'Exam Domains', 'singular_name' => 'Exam Domain' ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
	) );

	register_post_type( 'aws_question', array(
		'labels'             => array(
			'name'               => 'AWS Questions',
			'singular_name'      => 'AWS Question',
			'add_new_item'       => 'Add New AWS Question',
			'edit_item'          => 'Edit AWS Question',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'supports'           => array( 'title', 'editor' ),
		'show_in_rest'       => true,
	) );
}
add_action( 'init', 'aws_sbg_register_question_cpt', 5 );

/**
 * 2. DATABASE TABLES & INIT
 */
function aws_sbg_init_exam_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$sql_codes = "CREATE TABLE IF NOT EXISTS {$table_codes} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		access_code VARCHAR(32) NOT NULL,
		member_name VARCHAR(255) NOT NULL,
		member_email VARCHAR(255) NOT NULL,
		exam_track VARCHAR(50) NOT NULL DEFAULT 'clf-c02',
		per_question_seconds INT(11) NOT NULL DEFAULT 45,
		allow_backtrack TINYINT(1) NOT NULL DEFAULT 0,
		max_attempts INT(11) NOT NULL DEFAULT 1,
		used_attempts INT(11) NOT NULL DEFAULT 0,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY access_code (access_code)
	) {$charset_collate};";

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	$sql_attempts = "CREATE TABLE IF NOT EXISTS {$table_attempts} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		access_code_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		member_name VARCHAR(255) NOT NULL,
		member_email VARCHAR(255) NOT NULL,
		exam_track VARCHAR(50) NOT NULL DEFAULT 'clf-c02',
		score INT(11) NOT NULL DEFAULT 0,
		passed TINYINT(1) NOT NULL DEFAULT 0,
		time_spent_seconds INT(11) NOT NULL DEFAULT 0,
		tab_switch_count INT(11) NOT NULL DEFAULT 0,
		violation_submitted TINYINT(1) NOT NULL DEFAULT 0,
		device_fingerprint VARCHAR(255) DEFAULT NULL,
		domain_scores LONGTEXT DEFAULT NULL,
		attempt_details LONGTEXT DEFAULT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY access_code_id (access_code_id),
		KEY exam_track (exam_track)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql_codes );
	dbDelta( $sql_attempts );

	// Ensure missing columns are added if table existed prior to schema update
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD COLUMN device_fingerprint VARCHAR(255) DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN per_question_seconds INT(11) NOT NULL DEFAULT 45" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ALTER COLUMN per_question_seconds SET DEFAULT 45" );
	@$wpdb->query( "UPDATE {$table_codes} SET per_question_seconds = 45 WHERE per_question_seconds = 60 OR per_question_seconds = 0 OR per_question_seconds IS NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN allow_backtrack TINYINT(1) NOT NULL DEFAULT 0" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN max_attempts INT(11) NOT NULL DEFAULT 1" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN used_attempts INT(11) NOT NULL DEFAULT 0" );

	aws_sbg_auto_seed_questions();
}
add_action( 'init', 'aws_sbg_init_exam_tables', 10 );

/**
 * 3. AUTO-SEED QUESTION BANK FROM SEED JSON
 */
function aws_sbg_auto_seed_questions( $force = false ) {
	if ( ! $force ) {
		$count = wp_count_posts( 'aws_question' )->publish;
		if ( $count >= 5 ) {
			return;
		}
	} else {
		// Purge old duplicate questions when force reseeding
		$old_posts = get_posts( array(
			'post_type'      => 'aws_question',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		) );
		if ( ! empty( $old_posts ) ) {
			foreach ( $old_posts as $old_id ) {
				wp_delete_post( $old_id, true );
			}
		}
	}

	$json_file = get_template_directory() . '/seed-foundational-questions.json';
	if ( ! file_exists( $json_file ) ) {
		return;
	}

	$json_data = file_get_contents( $json_file );
	$questions = json_decode( $json_data, true );

	if ( ! is_array( $questions ) ) {
		return;
	}

	foreach ( $questions as $q ) {
		aws_sbg_insert_single_question( $q );
	}

	update_option( 'aws_sbg_questions_seeded', true );
}

function aws_sbg_insert_single_question( $q ) {
	$post_id = wp_insert_post( array(
		'post_title'   => wp_strip_all_tags( substr( $q['question'], 0, 100 ) . '...' ),
		'post_content' => $q['question'],
		'post_type'    => 'aws_question',
		'post_status'  => 'publish',
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return false;
	}

	if ( ! empty( $q['exam_track'] ) ) {
		$term_track = strtolower( trim( $q['exam_track'] ) );
		$term_info  = term_exists( $term_track, 'exam_track' );
		if ( ! $term_info ) {
			$term_info = wp_insert_term( $term_track, 'exam_track', array( 'slug' => $term_track ) );
		}
		if ( ! is_wp_error( $term_info ) && isset( $term_info['term_id'] ) ) {
			wp_set_object_terms( $post_id, intval( $term_info['term_id'] ), 'exam_track' );
		} else {
			wp_set_object_terms( $post_id, $term_track, 'exam_track' );
		}
	}

	if ( ! empty( $q['domain'] ) ) {
		$term_domain = trim( $q['domain'] );
		$slug_domain = sanitize_title( $term_domain );
		$domain_info = term_exists( $slug_domain, 'exam_domain' );
		if ( ! $domain_info ) {
			$domain_info = wp_insert_term( $term_domain, 'exam_domain', array( 'slug' => $slug_domain ) );
		}
		if ( ! is_wp_error( $domain_info ) && isset( $domain_info['term_id'] ) ) {
			wp_set_object_terms( $post_id, intval( $domain_info['term_id'] ), 'exam_domain' );
		} else {
			wp_set_object_terms( $post_id, $term_domain, 'exam_domain' );
		}
	}

	update_post_meta( $post_id, '_aws_options', $q['options'] );
	update_post_meta( $post_id, '_aws_correct', $q['correct'] );
	update_post_meta( $post_id, '_aws_explanation', $q['explanation'] );
	update_post_meta( $post_id, '_aws_reference_url', isset( $q['doc_link'] ) ? $q['doc_link'] : '' );

	return $post_id;
}

/**
 * 4. ADMIN DASHBOARD & MENU
 */
/* Handled by Unified Suite in admin-dashboard.php
function aws_sbg_add_exam_admin_menu() {
	add_menu_page(
		'AWS Mock Exam',
		'AWS Mock Exam',
		'manage_options',
		'aws-mock-exam',
		'aws_sbg_render_exam_admin_page',
		'dashicons-welcome-learn-more',
		26
	);
}
add_action( 'admin_menu', 'aws_sbg_add_exam_admin_menu' );
*/

function aws_sbg_render_exam_admin_page() {
	global $wpdb;
	$table_codes    = $wpdb->prefix . 'aws_access_codes';
	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'codes';

	// Handle Actions
	$message = '';
	if ( isset( $_POST['action'] ) && check_admin_referer( 'aws_exam_admin_action', 'aws_exam_nonce' ) ) {
		if ( $_POST['action'] === 'generate_bulk_codes' ) {
			$raw_list   = isset( $_POST['member_list'] ) ? trim( $_POST['member_list'] ) : '';
			$track      = sanitize_text_field( $_POST['exam_track'] );
			$max_att    = intval( $_POST['max_attempts'] );
			$per_q_sec  = intval( $_POST['per_question_seconds'] );
			$auto_email = isset( $_POST['auto_email_members'] ) ? true : false;
			if ( $max_att < 1 ) $max_att = 1;
			if ( $per_q_sec < 10 ) $per_q_sec = 45;

			$lines = explode( "\n", $raw_list );
			$created_count = 0;
			$emailed_count = 0;

			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( empty( $line ) ) continue;

				$parts = array_map( 'trim', explode( ',', $line ) );
				$name  = isset( $parts[0] ) ? sanitize_text_field( $parts[0] ) : 'Community Member';
				$email = isset( $parts[1] ) ? sanitize_email( $parts[1] ) : '';

				if ( empty( $email ) ) {
					$email = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $name ) ) . '@awscommunity.local';
				}

				$code = 'AWS-' . strtoupper( wp_generate_password( 5, false, false ) );

				$inserted = $wpdb->insert(
					$table_codes,
					array(
						'access_code'          => $code,
						'member_name'          => $name,
						'member_email'         => $email,
						'exam_track'           => $track,
						'per_question_seconds' => $per_q_sec,
						'allow_backtrack'      => 0,
						'max_attempts'         => $max_att,
						'used_attempts'        => 0,
					),
					array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d' )
				);

				if ( false !== $inserted && $wpdb->insert_id ) {
					$new_id = $wpdb->insert_id;
					$created_count++;

					if ( $auto_email && $new_id ) {
						if ( aws_sbg_send_member_exam_email( $new_id ) ) {
							$emailed_count++;
						}
					}
				} else {
					$db_err = $wpdb->last_error;
				}
			}

			if ( $created_count > 0 ) {
				$message = "Successfully created {$created_count} access code(s)!";
				if ( $auto_email ) {
					$message .= " Emailed {$emailed_count} candidate(s) via Purelymail SMTP.";
				}
			} else {
				$err_msg = ! empty( $db_err ) ? $db_err : 'Database insertion error.';
				$message = "⚠️ Failed to create access code. DB Error: " . esc_html( $err_msg );
			}
		} elseif ( $_POST['action'] === 'save_smtp_config' ) {
			$smtp_data = array(
				'host'       => sanitize_text_field( $_POST['smtp_host'] ),
				'port'       => intval( $_POST['smtp_port'] ),
				'encryption' => sanitize_text_field( $_POST['smtp_encryption'] ),
				'username'   => trim( sanitize_text_field( $_POST['smtp_username'] ) ),
				'password'   => isset( $_POST['smtp_password'] ) ? wp_unslash( trim( $_POST['smtp_password'] ) ) : '',
				'from_name'  => sanitize_text_field( $_POST['smtp_from_name'] ),
				'from_email' => sanitize_email( $_POST['smtp_from_email'] ),
			);
			update_option( 'aws_sbg_smtp_config', $smtp_data );
			$message = "Purelymail SMTP Settings updated successfully!";
		} elseif ( $_POST['action'] === 'send_test_email' ) {
			$GLOBALS['aws_sbg_last_mail_error'] = '';
			$to_email = sanitize_email( $_POST['test_email_recipient'] );
			if ( ! empty( $to_email ) ) {
				$sent = wp_mail( $to_email, '🧪 Purelymail SMTP Connection Test — AWS Air University', 'Hello! Your Purelymail SMTP connection is configured and working perfectly!' );
				if ( $sent ) {
					$message = "Test email sent successfully to {$to_email} via Purelymail SMTP!";
				} else {
					$err_text = ! empty( $GLOBALS['aws_sbg_last_mail_error'] ) ? $GLOBALS['aws_sbg_last_mail_error'] : 'Unknown PHPMailer failure';
					$message = "⚠️ Failed to send test email. Error details: " . esc_html( $err_text );
				}
			}
		} elseif ( $_POST['action'] === 'send_single_member_email' ) {
			$GLOBALS['aws_sbg_last_mail_error'] = '';
			$code_id = intval( $_POST['code_id'] );
			$sent = aws_sbg_send_member_exam_email( $code_id );
			if ( $sent ) {
				$message = "Email sent successfully to candidate!";
			} else {
				$err_text = ! empty( $GLOBALS['aws_sbg_last_mail_error'] ) ? $GLOBALS['aws_sbg_last_mail_error'] : 'Unknown PHPMailer failure';
				$message = "⚠️ Failed to send email to candidate. Error details: " . esc_html( $err_text );
			}
		} elseif ( $_POST['action'] === 'send_all_members_email' ) {
			$GLOBALS['aws_sbg_last_mail_error'] = '';
			$all_codes = $wpdb->get_results( "SELECT id FROM {$table_codes}" );
			$sent_count = 0;
			foreach ( $all_codes as $c_item ) {
				if ( aws_sbg_send_member_exam_email( $c_item->id ) ) {
					$sent_count++;
				}
			}
			$message = "Dispatched exam code emails to {$sent_count} members via Purelymail SMTP!";
			if ( ! empty( $GLOBALS['aws_sbg_last_mail_error'] ) && $sent_count === 0 ) {
				$message .= " Last error: " . esc_html( $GLOBALS['aws_sbg_last_mail_error'] );
			}
		} elseif ( $_POST['action'] === 'seed_questions' ) {
			delete_option( 'aws_sbg_questions_seeded' );
			aws_sbg_auto_seed_questions( true );
			$message = "Reseeded question bank from JSON template!";
		} elseif ( $_POST['action'] === 'clear_all_attempts' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_attempts}" );
			$wpdb->query( "UPDATE {$table_codes} SET used_attempts = 0" );
			$message = "Successfully cleared all test attempt results and reset access code usage!";
		} elseif ( $_POST['action'] === 'clear_all_codes' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_codes}" );
			$message = "Successfully cleared all member access codes!";
		} elseif ( $_POST['action'] === 'reset_all_test_data' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_attempts}" );
			$wpdb->query( "TRUNCATE TABLE {$table_codes}" );
			$message = "Successfully reset all test data (cleared all access codes & test attempt history)!";
		} elseif ( $_POST['action'] === 'set_question_limit' ) {
			$new_limit = intval( $_POST['question_limit'] );
			update_option( 'aws_exam_question_limit', $new_limit );
			$message = "Exam question limit updated to {$new_limit} questions per attempt!";
		}
	}

	// Fetch Data
	$codes    = $wpdb->get_results( "SELECT * FROM {$table_codes} ORDER BY id DESC LIMIT 100" );
	$attempts = $wpdb->get_results( "SELECT * FROM {$table_attempts} ORDER BY id DESC LIMIT 200" );
	$question_count = wp_count_posts( 'aws_question' )->publish;
	if ( intval( $question_count ) < 5 ) {
		aws_sbg_auto_seed_questions( true );
		$question_count = wp_count_posts( 'aws_question' )->publish;
	}

	// SMTP Config
	$smtp_config = get_option( 'aws_sbg_smtp_config', array(
		'host'       => 'smtp.purelymail.com',
		'port'       => 465,
		'encryption' => 'ssl',
		'username'   => '',
		'password'   => '',
		'from_name'  => 'AWS Air University',
		'from_email' => '',
	) );

	// Results Stats
	$total_attempts   = count( $attempts );
	$passed_attempts  = 0;
	$violation_count  = 0;
	$sum_scores       = 0;

	foreach ( $attempts as $att ) {
		if ( $att->passed ) $passed_attempts++;
		if ( $att->violation_submitted ) $violation_count++;
		$sum_scores += intval( $att->score );
	}

	$pass_rate  = $total_attempts > 0 ? round( ( $passed_attempts / $total_attempts ) * 100 ) : 0;
	$avg_score  = $total_attempts > 0 ? round( $sum_scores / $total_attempts ) : 0;

	// Question Bank Stats
	$clf_count = 0;
	$aif_count = 0;
	$clf_term = get_term_by( 'slug', 'clf-c02', 'exam_track' );
	$aif_term = get_term_by( 'slug', 'aif-c01', 'exam_track' );
	if ( $clf_term ) $clf_count = $clf_term->count;
	if ( $aif_term ) $aif_count = $aif_term->count;
	$domain_terms_count = wp_count_terms( 'exam_domain' );

	$cur_limit = intval( get_option( 'aws_exam_question_limit', 65 ) );

	aws_sbg_render_admin_nav_tabs( 'mock_exam' );
	?>
	<style>
		.aws-card {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 8px;
			width: 100% !important;
			max-width: 100% !important;
			box-sizing: border-box !important;
		}
		.aws-kpi-card {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 8px;
			box-sizing: border-box !important;
		}
	</style>

	<div class="wrap">
		<h1 style="display:flex; align-items:center; justify-content:space-between; font-weight:700;">
			<span style="display:flex; align-items:center; gap:10px;">
				<span class="dashicons dashicons-welcome-learn-more" style="font-size:32px; width:32px; height:32px; color:#ad5cff;"></span>
				AWS Foundational Certification Mock Exam System
			</span>
			<div style="display:flex; align-items:center; gap:10px;">
				<form method="post" style="display:inline;">
					<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
					<input type="hidden" name="action" value="set_question_limit">
					<?php if ( $cur_limit === 10 ) : ?>
						<input type="hidden" name="question_limit" value="65">
						<button type="submit" class="button button-secondary" style="font-weight:700; background:#dcfce7; color:#15803d; border-color:#86efac;">⚡ Mode: 10 Questions (Click for 65)</button>
					<?php else : ?>
						<input type="hidden" name="question_limit" value="10">
						<button type="submit" class="button button-secondary" style="font-weight:700; background:#e0e7ff; color:#3730a3; border-color:#a5b4fc;">🎓 Mode: 65 Questions (Click for 10)</button>
					<?php endif; ?>
				</form>
				<button type="button" onclick="awsPreviewAdminCertificate()" class="button button-primary" style="background:#ad5cff; border-color:#8b31ff; font-weight:700; display:inline-flex; align-items:center; gap:6px;">📜 Preview Certificate Template</button>
				<form method="post" style="display:inline;" onsubmit="return confirm('WARNING: Are you sure you want to reset ALL test data? This will permanently delete all access codes and attempt history.');">
					<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
					<input type="hidden" name="action" value="reset_all_test_data">
					<button type="submit" class="button button-secondary" style="color:#dc2626; border-color:#fca5a5; font-weight:600;">🔄 Reset All Test Data</button>
				</form>
			</div>
		</h1>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<h2 class="nav-tab-wrapper" style="margin-top: 15px;">
			<a href="?page=aws-mock-exam&tab=codes" class="nav-tab <?php echo $active_tab === 'codes' ? 'nav-tab-active' : ''; ?>">Access Code Generator</a>
			<a href="?page=aws-mock-exam&tab=questions" class="nav-tab <?php echo $active_tab === 'questions' ? 'nav-tab-active' : ''; ?>">Question Bank (<?php echo $question_count; ?>)</a>
			<a href="?page=aws-mock-exam&tab=results" class="nav-tab <?php echo $active_tab === 'results' ? 'nav-tab-active' : ''; ?>">Results & Anti-Cheat Audit (<?php echo $total_attempts; ?>)</a>
			<a href="?page=aws-mock-exam&tab=smtp" class="nav-tab <?php echo $active_tab === 'smtp' ? 'nav-tab-active' : ''; ?>">📧 Purelymail SMTP Settings</a>
		</h2>

		<div style="margin-top: 20px;">
			<?php if ( $active_tab === 'codes' ) : ?>
				
				<?php
				$total_codes_count = count( $codes );
				$code_clf_count    = 0;
				$code_aif_count    = 0;
				$code_used_count   = 0;
				$code_total_attempts = 0;

				foreach ( $codes as $c ) {
					if ( strtolower($c->exam_track) === 'aif-c01' ) {
						$code_aif_count++;
					} else {
						$code_clf_count++;
					}
					$code_used_count += intval( $c->used_attempts );
					$code_total_attempts += intval( $c->max_attempts );
				}
				?>

				<!-- KPI Metric Cards for Access Codes -->
				<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #ad5cff;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Access Codes</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $total_codes_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #3b82f6;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Cloud Practitioner Codes</div>
						<div style="font-size: 24px; font-weight: 800; color: #3b82f6; margin-top: 4px;"><?php echo $code_clf_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #10b981;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">AI Practitioner Codes</div>
						<div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?php echo $code_aif_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #f59e0b;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Used Attempts Ratio</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $code_used_count; ?> <span style="font-size: 13px; color: #64748b; font-weight: 600;">/ <?php echo $code_total_attempts; ?> Used</span></div>
					</div>
				</div>

				<!-- Full Width Stacked Layout -->
				<div style="display: flex; flex-direction: column; gap: 20px;">
					
					<!-- 1. BULK GENERATOR FORM CARD -->
					<div class="aws-card" style="padding: 24px;">
						<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							⚡ Bulk Generate Member Access Codes
						</h3>
						<p style="color: #64748b; margin-bottom: 20px; font-size: 13px;">Generate unique 6-character access codes for your community members in bulk.</p>

						<form method="post">
							<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
							<input type="hidden" name="action" value="generate_bulk_codes">

							<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155; font-size: 13px;">
										Paste Members List (One member per line: Name, Email):
									</label>
									<textarea name="member_list" rows="6" class="large-text" placeholder="Anas Riaz, anas@example.com&#10;Jane Smith, jane@example.com&#10;Alex Johnson, alex@example.com" required style="width: 100%; border-radius: 6px; font-family: monospace; font-size: 13px; padding: 10px;"></textarea>
								</div>

								<div style="display: flex; flex-direction: column; justify-content: space-between; gap: 12px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
									<div>
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Certification Track:</label>
										<select name="exam_track" style="width: 100%; padding: 6px;">
											<option value="clf-c02">AWS Cloud Practitioner (CLF-C02)</option>
											<option value="aif-c01">AWS AI Practitioner (AIF-C01)</option>
										</select>
									</div>

									<div>
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Per-Question Time (Seconds):</label>
										<input type="number" name="per_question_seconds" value="60" min="10" max="300" style="width: 100%; padding: 6px;">
									</div>

									<div>
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Max Attempts per Code:</label>
										<input type="number" name="max_attempts" value="1" min="1" max="10" style="width: 100%; padding: 6px;">
									</div>

									<div style="padding-top: 6px; border-top: 1px solid #e2e8f0;">
										<label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0066cc; font-size: 12px; cursor: pointer;">
											<input type="checkbox" name="auto_email_members" value="1" checked>
											✉️ Auto-email code & instructions via Purelymail SMTP
										</label>
									</div>
								</div>
							</div>

							<div style="text-align: right;">
								<button type="submit" class="button button-primary button-large" style="padding: 6px 24px; font-size: 14px; font-weight: 700; background: #ad5cff; border-color: #8b31ff;">
									⚡ Generate Member Access Codes Now
								</button>
							</div>
						</form>
					</div>

					<!-- 2. GENERATED ACCESS CODES WIDE TABLE CARD -->
					<div class="aws-card" style="padding: 24px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
							<h3 style="margin: 0; font-size: 18px; color: #0f172a;">
								Generated Access Codes List (<?php echo count( $codes ); ?>)
							</h3>
							<div style="display: flex; gap: 10px;">
								<form method="post" style="display:inline;" onsubmit="return confirm('Send exam emails to ALL candidates in this list?');">
									<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
									<input type="hidden" name="action" value="send_all_members_email">
									<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857;">✉️ Email All Candidates</button>
								</form>

								<button onclick="copyCodesCSV()" class="button button-secondary">📋 Copy All CSV</button>
								
								<form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete all generated access codes?');">
									<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
									<input type="hidden" name="action" value="clear_all_codes">
									<button type="submit" class="button button-secondary" style="color:#dc2626; border-color:#fca5a5;">🗑️ Clear Codes</button>
								</form>
							</div>
						</div>
						
						<div style="overflow-x: auto;">
							<table class="wp-list-table widefat striped" style="border-collapse: collapse; width: 100%;">
								<thead>
									<tr>
										<th style="min-width: 130px;">Access Code</th>
										<th style="min-width: 160px;">Member Name</th>
										<th style="min-width: 200px;">Email</th>
										<th style="min-width: 110px; text-align: center;">Track</th>
										<th style="min-width: 90px; text-align: center;">Timer</th>
										<th style="min-width: 100px; text-align: center;">Attempts</th>
										<th style="min-width: 110px; text-align: center;">Send Email</th>
										<th style="min-width: 150px;">Created Date</th>
									</tr>
								</thead>
								<tbody>
									<?php if ( empty( $codes ) ) : ?>
										<tr><td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">No access codes generated yet. Use the bulk generator above!</td></tr>
									<?php else : ?>
										<?php foreach ( $codes as $c ) : ?>
											<tr style="vertical-align: middle;">
												<td><strong style="color: #6366f1; font-family: monospace; font-size: 15px; letter-spacing: 1px;"><?php echo esc_html( $c->access_code ); ?></strong></td>
												<td><strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $c->member_name ); ?></strong></td>
												<td style="font-family: monospace; color: #475569; font-size: 12px;"><?php echo esc_html( $c->member_email ); ?></td>
												<td style="text-align: center;">
													<span style="background: <?php echo strtolower($c->exam_track) === 'aif-c01' ? '#d1fae5' : '#e0e7ff'; ?>; color: <?php echo strtolower($c->exam_track) === 'aif-c01' ? '#065f46' : '#3730a3'; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-block;">
														<?php echo esc_html( strtoupper( $c->exam_track ) ); ?>
													</span>
												</td>
												<td style="text-align: center; font-weight: 600; color: #334155;">
													<?php echo esc_html( ( isset($c->per_question_seconds) ? $c->per_question_seconds : 60 ) . 's/q' ); ?>
												</td>
												<td style="text-align: center; font-weight: 600;">
													<span style="color: <?php echo $c->used_attempts >= $c->max_attempts ? '#dc2626' : '#059669'; ?>;">
														<?php echo esc_html( $c->used_attempts . ' / ' . $c->max_attempts ); ?>
													</span>
												</td>
												<td style="text-align: center;">
													<form method="post" style="display:inline;">
														<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
														<input type="hidden" name="action" value="send_single_member_email">
														<input type="hidden" name="code_id" value="<?php echo $c->id; ?>">
														<button type="submit" class="button button-small" style="font-size:11px;">✉️ Send Email</button>
													</form>
												</td>
												<td style="color: #64748b; font-size: 12px; white-space: nowrap;">
													<?php echo esc_html( date( 'M j, Y g:i a', strtotime( $c->created_at ) ) ); ?>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>

				</div>

				<script>
					function copyCodesCSV() {
						let csv = "Access Code,Member Name,Email,Track\n";
						<?php foreach ( $codes as $c ) : ?>
							csv += "<?php echo $c->access_code; ?>,<?php echo addslashes($c->member_name); ?>,<?php echo $c->member_email; ?>,<?php echo $c->exam_track; ?>\n";
						<?php endforeach; ?>
						navigator.clipboard.writeText(csv).then(() => {
							alert("Copied all codes to clipboard in CSV format!");
						});
					}
				</script>

			<?php elseif ( $active_tab === 'questions' ) : ?>
				
				<!-- KPI Metric Cards for Question Bank -->
				<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #ad5cff;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Questions</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $question_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #3b82f6;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Cloud Practitioner (CLF-C02)</div>
						<div style="font-size: 24px; font-weight: 800; color: #3b82f6; margin-top: 4px;"><?php echo $clf_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #10b981;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">AI Practitioner (AIF-C01)</div>
						<div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?php echo $aif_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #f59e0b;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Domains Covered</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo is_wp_error($domain_terms_count) ? 0 : $domain_terms_count; ?></div>
					</div>
				</div>

				<div class="aws-card" style="padding: 20px;">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
						<h3 style="margin:0; font-size:18px;">AWS Foundational Question Bank (<?php echo $question_count; ?> Questions)</h3>
						<div style="display:flex; gap:10px;">
							<a href="<?php echo admin_url('post-new.php?post_type=aws_question'); ?>" class="button button-secondary">➕ Add New Question</a>
							<form method="post" style="display:inline;">
								<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
								<input type="hidden" name="action" value="seed_questions">
								<button type="submit" class="button button-primary">🌱 Reseed Default Questions</button>
							</form>
						</div>
					</div>

					<p style="color: #64748b; margin-bottom: 15px;">Questions are stored as Custom Post Type <code>aws_question</code>. Manage or reseed them below.</p>
					
					<?php
					$questions_query = new WP_Query( array(
						'post_type'      => 'aws_question',
						'posts_per_page' => 150,
					) );
					?>

					<div style="overflow-x: auto;">
						<table class="wp-list-table widefat striped" style="border-collapse: collapse; width: 100%;">
							<thead>
								<tr>
									<th style="width:50px; text-align:center;">ID</th>
									<th style="min-width:320px;">Question Content Excerpt</th>
									<th style="min-width:110px; text-align:center;">Track</th>
									<th style="min-width:180px;">Domain</th>
									<th style="min-width:90px; text-align:center;">Options</th>
									<th style="min-width:80px; text-align:center;">Correct</th>
									<th style="min-width:80px; text-align:center;">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if ( $questions_query->have_posts() ) : ?>
									<?php while ( $questions_query->have_posts() ) : $questions_query->the_post(); 
										$q_id        = get_the_ID();
										$q_obj       = get_post( $q_id );
										$q_text      = ! empty( $q_obj->post_content ) ? $q_obj->post_content : $q_obj->post_title;
										$tracks      = wp_get_post_terms( $q_id, 'exam_track', array( 'fields' => 'names' ) );
										$domains     = wp_get_post_terms( $q_id, 'exam_domain', array( 'fields' => 'names' ) );
										$options     = get_post_meta( $q_id, '_aws_options', true );
										$correct     = get_post_meta( $q_id, '_aws_correct', true );
										$track_name  = ! empty( $tracks ) ? strtoupper( $tracks[0] ) : 'CLF-C02';
										$domain_name = ! empty( $domains ) ? $domains[0] : 'General Knowledge';
									?>
										<tr style="vertical-align: middle;">
											<td style="text-align:center; font-weight:600;">#<?php echo $q_id; ?></td>
											<td style="line-height: 1.5;">
												<strong style="color:#0f172a; font-size:13px;"><?php echo esc_html( $q_text ); ?></strong>
											</td>
											<td style="text-align:center;">
												<span style="background: <?php echo strtolower($track_name) === 'aif-c01' ? '#d1fae5' : '#e0e7ff'; ?>; color: <?php echo strtolower($track_name) === 'aif-c01' ? '#065f46' : '#3730a3'; ?>; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:700; display:inline-block;">
													<?php echo esc_html( $track_name ); ?>
												</span>
											</td>
											<td>
												<span style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;">
													<?php echo esc_html( $domain_name ); ?>
												</span>
											</td>
											<td style="text-align:center; font-weight:600; color:#334155;">
												<?php echo is_array($options) ? count($options) : 0; ?> choices
											</td>
											<td style="text-align:center;">
												<strong style="color:#059669; font-size:15px;"><?php echo is_array($correct) ? implode( ', ', $correct ) : ''; ?></strong>
											</td>
											<td style="text-align:center;">
												<a href="<?php echo get_edit_post_link( $q_id ); ?>" class="button button-small" style="font-size:11px;">Edit ✏️</a>
											</td>
										</tr>
									<?php endwhile; wp_reset_postdata(); ?>
								<?php else : ?>
									<tr><td colspan="7" style="text-align:center; padding:20px;">No questions found. Click "Reseed Default Questions" above!</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>

			<?php elseif ( $active_tab === 'results' ) : ?>
				
				<!-- KPI Metric Cards -->
				<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #ad5cff;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Attempts</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $total_attempts; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #10b981;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Voucher Eligible (Passed)</div>
						<div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?php echo $passed_attempts; ?> <span style="font-size: 13px; color: #64748b; font-weight: 600;">(<?php echo $pass_rate; ?>%)</span></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #ef4444;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Security Violations</div>
						<div style="font-size: 24px; font-weight: 800; color: #ef4444; margin-top: 4px;"><?php echo $violation_count; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #f59e0b;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Average Scaled Score</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $avg_score; ?> <span style="font-size: 13px; color: #64748b; font-weight: 600;">/ 1000</span></div>
					</div>
				</div>

				<div class="aws-card" style="padding: 20px;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
						<h3 style="margin: 0; font-size: 18px;">Exam Attempt Submissions & Anti-Cheat Audit Log</h3>
						<div style="display: flex; gap: 10px;">
							<button onclick="copyResultsCSV()" class="button button-secondary">📥 Export Attempts CSV</button>
							<form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to clear all exam attempt submissions and reset leaderboard scores?');">
								<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
								<input type="hidden" name="action" value="clear_all_attempts">
								<button type="submit" class="button button-secondary" style="color:#dc2626; border-color:#fca5a5;">🗑️ Clear Attempt History</button>
							</form>
						</div>
					</div>

					<!-- Wide Clean Table -->
					<div style="overflow-x: auto;">
						<table class="wp-list-table widefat striped" style="border-collapse: collapse; width: 100%;">
							<thead>
								<tr>
									<th style="width: 50px; text-align: center;">ID</th>
									<th style="min-width: 140px;">Candidate Name</th>
									<th style="min-width: 180px;">Email</th>
									<th style="min-width: 90px; text-align: center;">Track</th>
									<th style="min-width: 110px; text-align: center;">Scaled Score</th>
									<th style="min-width: 140px; text-align: center;">Status</th>
									<th style="min-width: 90px; text-align: center;">Time Spent</th>
									<th style="min-width: 120px; text-align: center;">Tab Switches</th>
									<th style="min-width: 150px;">Date & Time</th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $attempts ) ) : ?>
									<tr><td colspan="9" style="text-align: center; padding: 20px;">No exam attempts recorded yet.</td></tr>
								<?php else : ?>
									<?php foreach ( $attempts as $att ) : ?>
										<tr style="vertical-align: middle;">
											<td style="text-align: center; font-weight: 600;">#<?php echo esc_html( $att->id ); ?></td>
											<td>
												<strong style="font-size: 14px; color: #0f172a; display: block;"><?php echo esc_html( $att->member_name ); ?></strong>
												<?php if ( ! empty( $att->device_fingerprint ) ) : ?>
													<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; margin-top: 4px; font-family: monospace;">
														<?php echo esc_html( $att->device_fingerprint ); ?>
													</span>
												<?php endif; ?>
											</td>
											<td style="font-family: monospace; color: #475569; font-size: 12px;"><?php echo esc_html( $att->member_email ); ?></td>
											<td style="text-align: center;">
												<span style="background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-block;">
													<?php echo esc_html( strtoupper( $att->exam_track ) ); ?>
												</span>
											</td>
											<td style="text-align: center;">
												<strong style="font-size: 16px; color: <?php echo $att->passed ? '#059669' : '#dc2626'; ?>;">
													<?php echo esc_html( $att->score ); ?> / 1000
												</strong>
											</td>
											<td style="text-align: center;">
												<?php if ( $att->violation_submitted ) : ?>
													<span style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block;">⚠️ SECURITY VIOLATION</span>
												<?php elseif ( $att->passed ) : ?>
													<span style="background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block;">🎉 PASSED (Voucher Eligible)</span>
												<?php else : ?>
													<span style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block;">FAILED</span>
												<?php endif; ?>
											</td>
											<td style="text-align: center; font-weight: 600; color: #334155;">
												<?php echo esc_html( floor( $att->time_spent_seconds / 60 ) . 'm ' . ( $att->time_spent_seconds % 60 ) . 's' ); ?>
											</td>
											<td style="text-align: center;">
												<?php if ( $att->tab_switch_count > 0 ) : ?>
													<span style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">⚠️ <?php echo esc_html( $att->tab_switch_count ); ?> switches</span>
												<?php else : ?>
													<span style="background: #f0fdf4; color: #166534; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Clean (0)</span>
												<?php endif; ?>
											</td>
											<td style="color: #64748b; font-size: 12px; white-space: nowrap;">
												<?php echo esc_html( date( 'M j, Y g:i a', strtotime( $att->created_at ) ) ); ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>

				<script>
					function copyResultsCSV() {
						let csv = "ID,Candidate Name,Email,Track,Scaled Score,Status,Time Spent,Tab Switches,Date\n";
						<?php foreach ( $attempts as $att ) : 
							$status_str = $att->violation_submitted ? 'SECURITY VIOLATION' : ($att->passed ? 'PASSED (Voucher Eligible)' : 'FAILED');
						?>
							csv += "<?php echo $att->id; ?>,<?php echo addslashes($att->member_name); ?>,<?php echo $att->member_email; ?>,<?php echo $att->exam_track; ?>,<?php echo $att->score; ?>,<?php echo $status_str; ?>,<?php echo floor($att->time_spent_seconds/60).'m '.($att->time_spent_seconds%60).'s'; ?>,<?php echo $att->tab_switch_count; ?>,<?php echo date('Y-m-d H:i:s', strtotime($att->created_at)); ?>\n";
						<?php endforeach; ?>
						
						const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
						const url = URL.createObjectURL(blob);
						const link = document.createElement("a");
						link.setAttribute("href", url);
						link.setAttribute("download", "aws_mock_exam_results_" + new Date().toISOString().slice(0,10) + ".csv");
						document.body.appendChild(link);
						link.click();
						document.body.removeChild(link);
					}
				</script>

			<?php elseif ( $active_tab === 'smtp' ) : ?>
				
				<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
					<!-- 1. SMTP CONFIG FORM -->
					<div class="aws-card" style="padding: 24px;">
						<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							📧 Purelymail SMTP Configuration
						</h3>
						<p style="color: #64748b; margin-bottom: 20px; font-size: 13px;">Configure your Purelymail credentials to send candidate access codes and exam instructions automatically.</p>

						<form method="post">
							<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
							<input type="hidden" name="action" value="save_smtp_config">

							<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">SMTP Host:</label>
									<input type="text" name="smtp_host" value="<?php echo esc_attr( $smtp_config['host'] ); ?>" required style="width: 100%; padding: 8px;">
								</div>
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Port & Encryption:</label>
									<div style="display: flex; gap: 6px;">
										<input type="number" name="smtp_port" value="<?php echo esc_attr( $smtp_config['port'] ); ?>" style="width: 50%; padding: 8px;">
										<select name="smtp_encryption" style="width: 50%; padding: 8px;">
											<option value="ssl" <?php selected( $smtp_config['encryption'], 'ssl' ); ?>>SSL</option>
											<option value="tls" <?php selected( $smtp_config['encryption'], 'tls' ); ?>>TLS</option>
										</select>
									</div>
								</div>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Purelymail Username / Email:</label>
									<input type="text" name="smtp_username" value="<?php echo esc_attr( $smtp_config['username'] ); ?>" placeholder="yourname@domain.com" required style="width: 100%; padding: 8px;">
								</div>
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Purelymail Password:</label>
									<input type="password" name="smtp_password" value="<?php echo esc_attr( $smtp_config['password'] ); ?>" required style="width: 100%; padding: 8px;">
								</div>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Sender Name (From Name):</label>
									<input type="text" name="smtp_from_name" value="<?php echo esc_attr( $smtp_config['from_name'] ); ?>" required style="width: 100%; padding: 8px;">
								</div>
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Sender Email (From Email):</label>
									<input type="email" name="smtp_from_email" value="<?php echo esc_attr( $smtp_config['from_email'] ); ?>" placeholder="events@domain.com" required style="width: 100%; padding: 8px;">
								</div>
							</div>

							<button type="submit" class="button button-primary button-large" style="padding: 6px 20px; font-weight: 700; background: #ad5cff; border-color: #8b31ff;">
								💾 Save Purelymail Settings
							</button>
						</form>
					</div>

					<!-- 2. TEST EMAIL DISPATCHER -->
					<div class="aws-card" style="padding: 24px;">
						<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							🧪 Connection Tester
						</h3>
						<p style="color: #64748b; margin-bottom: 15px; font-size: 13px;">Send a test email to verify your Purelymail SMTP connection before emailing members.</p>

						<form method="post">
							<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
							<input type="hidden" name="action" value="send_test_email">

							<label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155; font-size: 12px;">Recipient Email:</label>
							<input type="email" name="test_email_recipient" value="<?php echo esc_attr( get_option('admin_email') ); ?>" required style="width: 100%; padding: 8px; margin-bottom: 15px;">

							<button type="submit" class="button button-secondary" style="width: 100%; font-weight: 700; padding: 6px;">
								🧪 Send Test Email Now
							</button>
						</form>

						<div style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 11px; color: #475569;">
							<strong>Purelymail Server Details:</strong><br>
							• Server: <code>smtp.purelymail.com</code><br>
							• SSL Port: <code>465</code><br>
							• TLS Port: <code>587</code>
						</div>
					</div>
				</div>

			<?php endif; ?>
		</div>
	</div>
	<!-- ADMIN CERTIFICATE PREVIEW MODAL -->
	<div id="aws-admin-cert-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(7,4,13,0.92); z-index:9999999; backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:20px; box-sizing:border-box;">
		<div style="background:#140c24; border:2px solid #ad5cff; border-radius:24px; max-width:960px; width:100%; max-height:90vh; overflow:auto; padding:25px; box-shadow:0 25px 60px rgba(0,0,0,0.8); text-align:center;">
			<div style="display:flex; align-items:center; justify-content:between; margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
				<h2 style="margin:0; font-size:18px; color:#ffffff; font-weight:800;">📜 Live Certificate Template Preview</h2>
				<button type="button" onclick="document.getElementById('aws-admin-cert-modal').style.display='none';" style="background:none; border:none; color:#cbd5e1; font-size:24px; cursor:pointer; font-weight:700;">✕</button>
			</div>
			<div style="margin-bottom:15px;">
				<canvas id="aws-admin-cert-canvas" style="width:100%; height:auto; border-radius:12px; border:1px solid rgba(173,92,255,0.4); box-shadow:0 10px 30px rgba(0,0,0,0.5);"></canvas>
			</div>
			<div style="display:flex; justify-content:center; gap:12px;">
				<button type="button" onclick="awsDownloadAdminCertPNG()" class="button button-primary" style="background:linear-gradient(135deg, #ad5cff, #00ef88); border:none; color:#07040d; font-weight:900; font-size:14px; padding:10px 24px; height:auto; border-radius:12px; cursor:pointer;">📥 Download High-Res Certificate (PNG)</button>
				<button type="button" onclick="document.getElementById('aws-admin-cert-modal').style.display='none';" class="button button-secondary" style="border-radius:12px; font-weight:600;">Close Preview</button>
			</div>
		</div>
	</div>

	<script>
	window.awsPreviewAdminCertificate = async function() {
		const modal = document.getElementById('aws-admin-cert-modal');
		
		const templateImg = await new Promise((resolve) => {
			const img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = () => resolve(img);
			img.onerror = () => resolve(null);
			img.src = '<?php echo esc_url( get_template_directory_uri() . '/Certificate Template/Certificate.png' ); ?>';
		});

		const canvas = document.getElementById('aws-admin-cert-canvas');
		const ctx = canvas.getContext('2d');

		if (templateImg) {
			canvas.width = templateImg.naturalWidth || 2560;
			canvas.height = templateImg.naturalHeight || 1810;
			ctx.drawImage(templateImg, 0, 0, canvas.width, canvas.height);
		} else {
			// Fallback blank background if template fails to load
			canvas.width = 1920;
			canvas.height = 1357;
			ctx.fillStyle = '#0b0e17';
			ctx.fillRect(0, 0, canvas.width, canvas.height);
		}

		const w = canvas.width;
		const h = canvas.height;

		const scaleX = w / 1920;
		const scaleY = h / 1357;

		// 1. Redesigned Top-Left Purple Box Typography (Prestigious Certificate Design)
		const purpleBoxX = 105 * scaleX;
		const purpleBoxY = 105 * scaleY;

		// Eyebrow Badge
		ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
		if ('letterSpacing' in ctx) ctx.letterSpacing = (2 * scaleX) + 'px';
		ctx.font = `700 ${Math.round(15 * scaleX)}px "Segoe UI", -apple-system, sans-serif`;
		ctx.fillText('★ AWS CERTIFICATION CHALLENGE 2026 ★', purpleBoxX + (70 * scaleX), purpleBoxY + (115 * scaleY));
		if ('letterSpacing' in ctx) ctx.letterSpacing = '0px';

		// Main Grand Headline: Certificate
		ctx.fillStyle = '#ffffff';
		ctx.font = `bold ${Math.round(72 * scaleX)}px "Georgia", "Times New Roman", serif`;
		ctx.fillText('Certificate', purpleBoxX + (70 * scaleX), purpleBoxY + (215 * scaleY));

		// Sub-headline: PASSED - AWS Certification Preparation Exam
		ctx.fillStyle = '#ffffff';
		ctx.font = `800 ${Math.round(24 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.fillText('PASSED - AWS Certification Preparation Exam', purpleBoxX + (70 * scaleX), purpleBoxY + (305 * scaleY));

		// Elegant Horizontal Accent Line
		ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
		ctx.lineWidth = 2;
		ctx.beginPath();
		ctx.moveTo(purpleBoxX + (70 * scaleX), purpleBoxY + (345 * scaleY));
		ctx.lineTo(purpleBoxX + (620 * scaleX), purpleBoxY + (345 * scaleY));
		ctx.stroke();

		// Organization Footer Label
		ctx.fillStyle = '#f3e8ff';
		ctx.font = `500 ${Math.round(21 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.fillText('Organized by AWS Student Builder Group at Air University Islamabad', purpleBoxX + (70 * scaleX), purpleBoxY + (400 * scaleY));

		// 2. Bottom-Right Participant Info Block
		const infoX = 1125 * scaleX;
		const infoY = 685 * scaleY;
		const infoW = (1920 - 1125 - 105) * scaleX;

		ctx.fillStyle = '#cbd5e1';
		ctx.font = `500 ${Math.round(22 * scaleX)}px "Courier New", Courier, monospace`;
		ctx.textAlign = 'center';
		ctx.fillText('Proudly presented to', infoX + infoW / 2, infoY + (55 * scaleY));

		ctx.fillStyle = '#ffffff';
		ctx.font = `bold ${Math.round(54 * scaleX)}px "Georgia", "Times New Roman", serif`;
		ctx.fillText('Sajeela Noor', infoX + infoW / 2, infoY + (145 * scaleY));

		const dateStr = new Date().toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });

		ctx.fillStyle = '#94a3b8';
		ctx.font = `400 ${Math.round(18 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.fillText('For successfully passing the AWS Cloud Certification Preparation Exam', infoX + infoW / 2, infoY + (215 * scaleY));
		ctx.fillText(`with a scaled score of 880 / 1000. (Code: AWS-8K9P • Issued: ${dateStr})`, infoX + infoW / 2, infoY + (250 * scaleY));

		// 3. Small Disclaimer at Bottom of Trophy Box (Bottom-Left Container)
		const trophyBoxX = 105 * scaleX;
		const trophyBoxY = (105 + 520 + 40) * scaleY;
		const trophyBoxW = 960 * scaleX;
		const trophyBoxH = (1357 - 665 - 75 - 30) * scaleY;

		ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
		ctx.font = `400 ${Math.round(13 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.textAlign = 'center';
		ctx.fillText('This is a community-issued preparation exam certificate and does not represent an official AWS certification.', trophyBoxX + trophyBoxW / 2, trophyBoxY + trophyBoxH - (25 * scaleY));

		ctx.textAlign = 'left';

		modal.style.display = 'flex';
	}

	function awsDownloadAdminCertPNG() {
		const canvas = document.getElementById('aws-admin-cert-canvas');
		canvas.toBlob((blob) => {
			const url = URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.download = 'AWS-Air-University-Certificate-Sample.png';
			link.href = url;
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			URL.revokeObjectURL(url);
		}, 'image/png');
	}
	</script>
	<?php
}

/**
 * 5. REST API ENDPOINTS
 */
function aws_sbg_register_exam_rest_routes() {
	register_rest_route( 'aws-sbg/v1', '/verify-code', array(
		'methods'             => 'POST',
		'callback'            => 'aws_sbg_rest_verify_code',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'aws-sbg/v1', '/exam/submit', array(
		'methods'             => 'POST',
		'callback'            => 'aws_sbg_rest_submit_exam',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'aws-sbg/v1', '/leaderboard', array(
		'methods'             => 'GET',
		'callback'            => 'aws_sbg_rest_get_leaderboard',
		'permission_callback' => '__return_true',
	) );
}
add_action( 'rest_api_init', 'aws_sbg_register_exam_rest_routes' );

/**
 * REST Callback: Verify Access Code & Load Exam Questions
 */
function aws_sbg_rest_verify_code( WP_REST_Request $request ) {
	global $wpdb;
	$code_str = strtoupper( trim( sanitize_text_field( $request->get_param( 'code' ) ) ) );

	if ( empty( $code_str ) ) {
		return new WP_Error( 'missing_code', 'Please enter your access code.', array( 'status' => 400 ) );
	}

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$code_row    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_codes} WHERE access_code = %s", $code_str ) );

	if ( ! $code_row ) {
		return new WP_Error( 'invalid_code', 'Invalid access code. Please verify and try again.', array( 'status' => 404 ) );
	}

	if ( $code_row->used_attempts >= $code_row->max_attempts ) {
		return new WP_Error( 'code_exhausted', 'This access code has reached its maximum allowed exam attempts.', array( 'status' => 403 ) );
	}

	$existing_count = wp_count_posts( 'aws_question' )->publish;
	if ( $existing_count < 5 ) {
		aws_sbg_auto_seed_questions( true );
	}

	$track = strtolower( trim( $code_row->exam_track ) );
	$limit = intval( get_option( 'aws_exam_question_limit', 65 ) );

	$args  = array(
		'post_type'      => 'aws_question',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'rand',
		'tax_query'      => array(
			array(
				'taxonomy' => 'exam_track',
				'field'    => 'slug',
				'terms'    => array( $track, strtoupper( $track ) ),
			),
		),
	);

	$q_query = new WP_Query( $args );

	if ( ! $q_query->have_posts() || empty( $q_query->posts ) ) {
		// Fallback query to get all published questions regardless of taxonomy
		$q_query = new WP_Query( array(
			'post_type'      => 'aws_question',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'rand',
		) );
	}

	$questions = array();

	if ( $q_query->have_posts() ) {
		foreach ( $q_query->posts as $post_obj ) {
			$q_id        = $post_obj->ID;
			$q_title     = $post_obj->post_title;
			$q_content   = ! empty( $post_obj->post_content ) ? $post_obj->post_content : $q_title;
			$options     = get_post_meta( $q_id, '_aws_options', true );
			$correct     = get_post_meta( $q_id, '_aws_correct', true );
			$domains     = wp_get_post_terms( $q_id, 'exam_domain', array( 'fields' => 'names' ) );
			$domain_name = ! empty( $domains ) ? $domains[0] : 'General Cloud Knowledge';

			$shuffled_options = is_array( $options ) ? $options : array();
			shuffle( $shuffled_options );

			$rekeyed_options = array();
			$display_keys    = array( 'A', 'B', 'C', 'D', 'E', 'F' );
			$key_index       = 0;

			foreach ( $shuffled_options as $opt ) {
				$new_key           = isset( $display_keys[ $key_index ] ) ? $display_keys[ $key_index ] : $opt['key'];
				$rekeyed_options[] = array(
					'key'          => $new_key,
					'original_key' => $opt['key'],
					'text'         => $opt['text'],
				);
				$key_index++;
			}

			$questions[] = array(
				'id'          => $q_id,
				'question'    => $q_content,
				'domain'      => $domain_name,
				'options'     => $rekeyed_options,
				'is_multi'    => is_array( $correct ) && count( $correct ) > 1,
				'multi_count' => is_array( $correct ) ? count( $correct ) : 1,
			);
		}
	}

	$session_token = wp_generate_password( 24, false );
	set_transient( 'aws_exam_session_' . $session_token, array(
		'access_code_id' => $code_row->id,
		'access_code'    => $code_row->access_code,
		'member_name'    => $code_row->member_name,
		'member_email'   => $code_row->member_email,
		'exam_track'     => $code_row->exam_track,
		'questions'      => $questions,
		'start_time'     => time(),
	), HOUR_IN_SECONDS * 3 );

	return rest_ensure_response( array(
		'success'               => true,
		'session_token'         => $session_token,
		'member_name'           => $code_row->member_name,
		'member_email'          => $code_row->member_email,
		'exam_track'            => $code_row->exam_track,
		'track_title'           => $code_row->exam_track === 'aif-c01' ? 'AWS Certified AI Practitioner (AIF-C01)' : 'AWS Certification Preparation Exam - Organized by AWS SBG Air University Islamabad',
		'total_questions'       => count( $questions ),
		'duration_minutes'      => 90,
		'per_question_seconds'  => ( isset($code_row->per_question_seconds) && intval($code_row->per_question_seconds) > 0 && intval($code_row->per_question_seconds) !== 60 ) ? intval($code_row->per_question_seconds) : 45,
		'allow_backtrack'       => isset($code_row->allow_backtrack) ? (bool) intval($code_row->allow_backtrack) : false,
		'questions'             => $questions,
	) );
}

/**
 * REST Callback: Submit & Grade Exam
 */
function aws_sbg_rest_submit_exam( WP_REST_Request $request ) {
	global $wpdb;
	$token           = sanitize_text_field( $request->get_param( 'session_token' ) );
	$user_answers    = $request->get_param( 'answers' );
	$time_spent      = intval( $request->get_param( 'time_spent_seconds' ) );
	$tab_switches    = intval( $request->get_param( 'tab_switch_count' ) );
	$is_violation    = (bool) $request->get_param( 'violation_submitted' );
	$device_fp       = sanitize_text_field( wp_unslash( $request->get_param( 'device_fingerprint' ) ) );

	$session_data = get_transient( 'aws_exam_session_' . $token );

	if ( ! $session_data ) {
		return new WP_Error( 'session_expired', 'Your exam session has expired or is invalid.', array( 'status' => 400 ) );
	}

	$questions = $session_data['questions'];
	$total_count = count( $questions );
	$correct_count = 0;
	$domain_stats = array();
	$item_results = array();

	foreach ( $questions as $q ) {
		$q_id = $q['id'];
		$correct_answers = get_post_meta( $q_id, '_aws_correct', true );
		$explanation     = get_post_meta( $q_id, '_aws_explanation', true );
		$ref_url         = get_post_meta( $q_id, '_aws_reference_url', true );
		$domain          = $q['domain'];

		if ( ! is_array( $correct_answers ) ) {
			$correct_answers = array();
		}

		if ( ! isset( $domain_stats[ $domain ] ) ) {
			$domain_stats[ $domain ] = array( 'correct' => 0, 'total' => 0 );
		}
		$domain_stats[ $domain ]['total']++;

		$user_selected_display = isset( $user_answers[ $q_id ] ) ? (array) $user_answers[ $q_id ] : array();
		
		$user_selected_original = array();
		if ( isset( $q['options'] ) && is_array( $q['options'] ) ) {
			foreach ( $user_selected_display as $sel_key ) {
				foreach ( $q['options'] as $opt ) {
					if ( isset( $opt['key'] ) && $opt['key'] === $sel_key ) {
						$user_selected_original[] = isset( $opt['original_key'] ) ? $opt['original_key'] : $opt['key'];
						break;
					}
				}
			}
		} else {
			$user_selected_original = $user_selected_display;
		}

		sort( $correct_answers );
		sort( $user_selected_original );

		$is_correct = ( $correct_answers === $user_selected_original );

		if ( $is_correct ) {
			$correct_count++;
			$domain_stats[ $domain ]['correct']++;
		}

		$item_results[] = array(
			'question_id'     => $q_id,
			'question'        => $q['question'],
			'domain'          => $domain,
			'user_selected'   => $user_selected_display,
			'correct_answers' => $correct_answers,
			'is_correct'      => $is_correct,
			'explanation'     => $explanation,
			'ref_url'         => $ref_url,
		);
	}

	$percentage = $total_count > 0 ? ( $correct_count / $total_count ) : 0;
	$scaled_score = round( 100 + ( $percentage * 900 ) );
	$passed = $scaled_score >= 700 ? 1 : 0;

	$domain_breakdown = array();
	foreach ( $domain_stats as $d_name => $d_data ) {
		$domain_breakdown[ $d_name ] = array(
			'correct'    => $d_data['correct'],
			'total'      => $d_data['total'],
			'percentage' => round( ( $d_data['correct'] / $d_data['total'] ) * 100 ),
		);
	}

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	$wpdb->insert(
		$table_attempts,
		array(
			'access_code_id'      => $session_data['access_code_id'],
			'member_name'         => $session_data['member_name'],
			'member_email'        => $session_data['member_email'],
			'exam_track'          => $session_data['exam_track'],
			'score'               => $scaled_score,
			'passed'              => $passed,
			'time_spent_seconds'  => $time_spent,
			'tab_switch_count'    => $tab_switches,
			'violation_submitted' => $is_violation ? 1 : 0,
			'device_fingerprint'  => $device_fp,
			'domain_scores'       => wp_json_encode( $domain_breakdown ),
			'attempt_details'     => wp_json_encode( $item_results ),
		),
		array( '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
	);

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$wpdb->query( $wpdb->prepare( "UPDATE {$table_codes} SET used_attempts = used_attempts + 1 WHERE id = %d", $session_data['access_code_id'] ) );

	delete_transient( 'aws_exam_session_' . $token );

	return rest_ensure_response( array(
		'success'             => true,
		'member_name'         => $session_data['member_name'],
		'exam_track'          => $session_data['exam_track'],
		'score'               => $scaled_score,
		'passed'              => (bool) $passed,
		'correct_count'       => $correct_count,
		'total_questions'     => $total_count,
		'percentage'          => round( $percentage * 100, 1 ),
		'time_spent_seconds'  => $time_spent,
		'tab_switch_count'    => $tab_switches,
		'violation_submitted' => $is_violation,
		'domain_breakdown'    => $domain_breakdown,
		'item_results'        => $item_results,
	) );
}

/**
 * REST Callback: Get Community Leaderboard
 */
function aws_sbg_rest_get_leaderboard( WP_REST_Request $request ) {
	global $wpdb;
	$track = sanitize_text_field( $request->get_param( 'track' ) );
	if ( empty( $track ) ) $track = 'clf-c02';

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	
	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT member_name, exam_track, score, passed, time_spent_seconds, created_at 
		 FROM {$table_attempts} 
		 WHERE exam_track = %s AND violation_submitted = 0 
		 ORDER BY score DESC, time_spent_seconds ASC 
		 LIMIT 20",
		$track
	) );

	$leaderboard = array();
	$rank = 1;

	foreach ( $results as $row ) {
		$leaderboard[] = array(
			'rank'               => $rank++,
			'member_name'        => $row->member_name,
			'score'              => intval( $row->score ),
			'passed'             => (bool) $row->passed,
			'time_spent_seconds' => intval( $row->time_spent_seconds ),
			'time_formatted'     => floor( $row->time_spent_seconds / 60 ) . 'm ' . ( $row->time_spent_seconds % 60 ) . 's',
			'date'               => date( 'M j, Y', strtotime( $row->created_at ) ),
		);
	}

	return rest_ensure_response( array(
		'success'     => true,
		'exam_track'  => $track,
		'leaderboard' => $leaderboard,
	) );
}
