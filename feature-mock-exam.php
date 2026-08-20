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
	$member_name  = esc_html( $code_row->member_name );
	$access_code  = esc_html( $code_row->access_code );
	$member_email = esc_html( $code_row->member_email );
	$per_q_sec    = ( isset( $code_row->per_question_seconds ) && intval( $code_row->per_question_seconds ) > 0 ) ? intval( $code_row->per_question_seconds ) : 40;
	$year         = date( 'Y' );

	$slot_schedule_html = '';
	if ( ! empty( $code_row->slot_id ) ) {
		$table_slots = $wpdb->prefix . 'aws_exam_slots';
		$slot_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_slots} WHERE id = %d", $code_row->slot_id ) );
		if ( $slot_row ) {
			$slot_title = esc_html( $slot_row->slot_name );
			$start_f    = date( 'l, j M Y — g:i A', strtotime( $slot_row->start_time ) );
			$end_f      = date( 'g:i A', strtotime( $slot_row->end_time ) );
			$tz_str     = wp_timezone_string();
			$slot_schedule_html = '<div style="background-color: #111827; border: 1px solid #1e293b; border-left: 3px solid #7c3aed; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">'
				. '<div style="font-size: 10px; font-weight: 800; color: #8b5cf6; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">⏱️ ASSIGNED EXAM TIME SLOT (' . $slot_title . ')</div>'
				. '<div style="font-size: 13px; font-weight: 700; color: #ffffff; line-height: 1.4;">' . $start_f . ' to ' . $end_f . ' (' . $tz_str . ')</div>'
				. '</div>';
		}
	}

	$subject = '⚡ Your AWS Certification Preparation Exam Access Code: ' . $access_code . ' — AWS Air University';

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="color-scheme" content="dark">
	<title>Your AWS Certification Preparation Exam Access Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #070a12; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #cbd5e1; -webkit-text-size-adjust: 100%;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #070a12; padding: 20px 10px;">
		<tr>
			<td align="center">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #0d1322; border: 1px solid #1e293b; border-radius: 16px; max-width: 480px; width: 100%; box-sizing: border-box;">
					<tr>
						<td style="padding: 24px 20px;">
							
							<!-- GREETING & INTRO -->
							<div style="font-size: 15px; color: #cbd5e1; margin-bottom: 12px;">
								Hi, <strong style="color: #ffffff;"><?php echo $member_name; ?></strong> 👋
							</div>
							<p style="margin: 0 0 18px 0; font-size: 13px; color: #cbd5e1; line-height: 1.6;">
								Your <strong>AWS Certification Preparation Exam</strong> organized by <strong>AWS Student Builder Group at Air University Islamabad</strong> has been successfully scheduled.
							</p>

							<!-- TIME SLOT (IF CONFIGURED) -->
							<?php echo $slot_schedule_html; ?>

							<!-- ACCESS CODE BOX (SINGLE CENTERED) -->
							<div style="background-color: #060911; border: 1.5px dashed #7c3aed; border-radius: 14px; padding: 18px 14px; text-align: center; margin-bottom: 18px;">
								<div style="font-size: 10px; font-weight: 800; color: #8b5cf6; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">
									YOUR PERSONAL ACCESS CODE
								</div>
								<div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 26px; font-weight: 900; color: #38bdf8; letter-spacing: 3px; margin: 4px 0;">
									<?php echo $access_code; ?>
								</div>
								<div style="font-size: 11px; color: #64748b; margin-top: 4px;">
									Single-use personal code • Registered to <?php echo $member_email; ?>
								</div>
							</div>

							<!-- PRIMARY CTA BUTTON -->
							<div style="margin-bottom: 22px;">
								<a href="<?php echo esc_url( $portal_url ); ?>" target="_blank" style="background-color: #7c3aed; color: #ffffff; text-decoration: none; display: block; width: 100%; box-sizing: border-box; text-align: center; padding: 14px 18px; border-radius: 12px; font-weight: 800; font-size: 15px; letter-spacing: 0.3px;">
									Open Exam Portal &rarr;
								</a>
							</div>

							<!-- EXAM GUIDELINES -->
							<div style="background-color: #090e1a; border: 1px solid #1e293b; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
								<div style="font-size: 11px; font-weight: 800; color: #8b5cf6; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">
									📌 Important Guidelines:
								</div>
								<div style="font-size: 12px; color: #94a3b8; line-height: 1.6;">
									<div style="margin-bottom: 6px;">⏱️ <strong style="color: #e2e8f0;"><?php echo $per_q_sec; ?>s Per Question:</strong> Single-pass mode; timer auto-advances at 0s.</div>
									<div style="margin-bottom: 6px;">🔒 <strong style="color: #e2e8f0;">Single Device Lock:</strong> Access code binds to your device on entry.</div>
									<div style="margin-bottom: 6px;">💻 <strong style="color: #e2e8f0;">Fullscreen Required:</strong> Attempt on laptop/desktop in fullscreen.</div>
									<div style="margin-bottom: 6px;">👁️ <strong style="color: #e2e8f0;">Active Presence:</strong> Screen blurs on 3s idle to protect questions.</div>
									<div>🏆 <strong style="color: #e2e8f0;">Voucher Reward:</strong> Score 700+ (70%) to qualify for an official AWS Exam Voucher ($100–$150 USD)!</div>
								</div>
							</div>

							<!-- ELIGIBILITY & REWARDS POLICY NOTE -->
							<div style="background-color: #0b1220; border: 1px solid #1e293b; border-radius: 12px; padding: 13px 15px; margin-bottom: 18px;">
								<div style="font-size: 11px; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 5px;">
									🎁 Rewards & Eligibility Note:
								</div>
								<div style="font-size: 12px; color: #94a3b8; line-height: 1.55;">
									Please note that physical <strong>swag kits and top scorer prizes</strong> are exclusively designated for student participants. Working professionals and financial aid candidates are eligible for the <strong>official AWS Certification Exam Voucher</strong> (score 70%+) and an official <strong>Exam Passing Certificate</strong>.
								</div>
							</div>

							<!-- INSPIRATIONAL PRE-EXAM MOTIVATION CARD -->
							<div style="background: linear-gradient(135deg, rgba(124,58,237,0.12) 0%, rgba(16,185,129,0.08) 100%); border: 1px solid rgba(124,58,237,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;">
								<div style="font-size: 12px; font-weight: 800; color: #ffffff; margin-bottom: 4px;">
									🌟 We Believe in You!
								</div>
								<div style="font-size: 12px; color: #cbd5e1; line-height: 1.55;">
									Attempt this exam with confidence, focus, and honesty. Trust the knowledge you have gained through your own effort. A score is only one part of your learning journey—what truly matters is staying true to yourself and growing from every attempt.
								</div>
							</div>

							<!-- SIGN-OFF FOOTER -->
							<div style="border-top: 1px solid #1a2236; padding-top: 16px; font-size: 12px; color: #64748b;">
								<div style="color: #cbd5e1; font-weight: 700;">Best of luck,</div>
								<div style="color: #ad5cff; font-weight: 800; margin-top: 2px;">Team AWS Student Builder Group</div>
								<div style="font-size: 11px; margin-top: 1px;">Air University Islamabad Campus</div>
							</div>

						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	<?php
	$html_body = ob_get_clean();

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

	$table_slots = $wpdb->prefix . 'aws_exam_slots';
	$sql_slots = "CREATE TABLE IF NOT EXISTS {$table_slots} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		slot_name VARCHAR(100) NOT NULL,
		slot_code VARCHAR(32) DEFAULT NULL,
		exam_track VARCHAR(50) NOT NULL DEFAULT 'all',
		start_time DATETIME NOT NULL,
		end_time DATETIME NOT NULL,
		max_capacity INT(11) NOT NULL DEFAULT 0,
		is_active TINYINT(1) NOT NULL DEFAULT 1,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY is_active (is_active)
	) {$charset_collate};";

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$sql_codes = "CREATE TABLE IF NOT EXISTS {$table_codes} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		access_code VARCHAR(32) NOT NULL,
		slot_id BIGINT(20) UNSIGNED DEFAULT NULL,
		member_name VARCHAR(255) NOT NULL,
		member_email VARCHAR(255) NOT NULL,
		exam_track VARCHAR(50) NOT NULL DEFAULT 'clf-c02',
		per_question_seconds INT(11) NOT NULL DEFAULT 40,
		allow_backtrack TINYINT(1) NOT NULL DEFAULT 0,
		max_attempts INT(11) NOT NULL DEFAULT 1,
		used_attempts INT(11) NOT NULL DEFAULT 0,
		active_session_token VARCHAR(64) DEFAULT NULL,
		device_fingerprint VARCHAR(255) DEFAULT NULL,
		session_started_at DATETIME DEFAULT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY access_code (access_code),
		KEY slot_id (slot_id)
	) {$charset_collate};";

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	$sql_attempts = "CREATE TABLE IF NOT EXISTS {$table_attempts} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		access_code_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		slot_id BIGINT(20) UNSIGNED DEFAULT NULL,
		member_name VARCHAR(255) NOT NULL,
		member_email VARCHAR(255) NOT NULL,
		exam_track VARCHAR(50) NOT NULL DEFAULT 'clf-c02',
		score INT(11) NOT NULL DEFAULT 0,
		passed TINYINT(1) NOT NULL DEFAULT 0,
		time_spent_seconds DECIMAL(10,3) NOT NULL DEFAULT 0.000,
		time_spent_ms BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		tab_switch_count INT(11) NOT NULL DEFAULT 0,
		violation_submitted TINYINT(1) NOT NULL DEFAULT 0,
		device_fingerprint VARCHAR(255) DEFAULT NULL,
		domain_scores LONGTEXT DEFAULT NULL,
		attempt_details LONGTEXT DEFAULT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY access_code_id (access_code_id),
		KEY slot_id (slot_id),
		KEY exam_track (exam_track)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql_slots );
	dbDelta( $sql_codes );
	dbDelta( $sql_attempts );

	// Ensure missing columns are added if table existed prior to schema update
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD COLUMN device_fingerprint VARCHAR(255) DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD COLUMN slot_id BIGINT(20) UNSIGNED DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD COLUMN member_type VARCHAR(20) NOT NULL DEFAULT 'paying'" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD COLUMN time_spent_ms BIGINT(20) UNSIGNED NOT NULL DEFAULT 0" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} MODIFY COLUMN time_spent_seconds DECIMAL(10,3) NOT NULL DEFAULT 0.000" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN slot_id BIGINT(20) UNSIGNED DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN member_type VARCHAR(20) NOT NULL DEFAULT 'paying'" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN per_question_seconds INT(11) NOT NULL DEFAULT 40" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ALTER COLUMN per_question_seconds SET DEFAULT 40" );
	@$wpdb->query( "UPDATE {$table_codes} SET per_question_seconds = 40 WHERE per_question_seconds = 30 OR per_question_seconds = 35 OR per_question_seconds = 45 OR per_question_seconds = 60 OR per_question_seconds = 0 OR per_question_seconds IS NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN allow_backtrack TINYINT(1) NOT NULL DEFAULT 0" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN max_attempts INT(11) NOT NULL DEFAULT 1" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN used_attempts INT(11) NOT NULL DEFAULT 0" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN active_session_token VARCHAR(64) DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN device_fingerprint VARCHAR(255) DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD COLUMN session_started_at DATETIME DEFAULT NULL" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD INDEX idx_member_email (member_email)" );
	@$wpdb->query( "ALTER TABLE {$table_codes} ADD INDEX idx_member_type (member_type)" );
	@$wpdb->query( "ALTER TABLE {$table_slots} ADD INDEX idx_time_window (start_time, end_time)" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD INDEX idx_member_email (member_email)" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD INDEX idx_member_type (member_type)" );
	@$wpdb->query( "ALTER TABLE {$table_attempts} ADD INDEX idx_time_spent_ms (time_spent_ms)" );

	aws_sbg_auto_seed_questions();
}
add_action( 'init', 'aws_sbg_init_exam_tables', 10 );

/**
 * 3. AUTO-SEED QUESTION BANK FROM DEDICATED SEED JSON FILES
 */
function aws_sbg_auto_seed_questions( $force = false, $seed_type = 'live' ) {
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

	$theme_dir = get_template_directory();
	$files_to_seed = array();

	if ( $seed_type === 'test' ) {
		$files_to_seed[] = $theme_dir . '/seed-test-questions.json';
	} elseif ( $seed_type === 'live' ) {
		$files_to_seed[] = $theme_dir . '/seed-live-questions.json';
	} else { // 'both' or default
		$files_to_seed[] = $theme_dir . '/seed-test-questions.json';
		$files_to_seed[] = $theme_dir . '/seed-live-questions.json';
	}

	$seeded_total = 0;
	foreach ( $files_to_seed as $json_file ) {
		if ( file_exists( $json_file ) ) {
			$json_data = file_get_contents( $json_file );
			$questions = json_decode( $json_data, true );
			if ( is_array( $questions ) ) {
				foreach ( $questions as $q ) {
					if ( aws_sbg_insert_single_question( $q ) ) {
						$seeded_total++;
					}
				}
			}
		}
	}

	update_option( 'aws_sbg_questions_seeded', true );
	return $seeded_total;
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
/**
 * Handle Real-Time Access Code & Attempt Category Toggle (Paying vs General)
 */
function aws_sbg_handle_code_member_type_toggle() {
	check_ajax_referer( 'aws_exam_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	global $wpdb;
	$table_codes    = $wpdb->prefix . 'aws_access_codes';
	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';

	$code_id = isset( $_POST['code_id'] ) ? intval( $_POST['code_id'] ) : 0;
	if ( $code_id > 0 ) {
		$code_row = $wpdb->get_row( $wpdb->prepare( "SELECT member_email, member_type FROM {$table_codes} WHERE id = %d", $code_id ) );
		if ( $code_row ) {
			$cur = ! empty( $code_row->member_type ) ? $code_row->member_type : 'paying';
			$new_val = ( $cur === 'paying' ) ? 'general' : 'paying';
			$wpdb->update( $table_codes, array( 'member_type' => $new_val ), array( 'id' => $code_id ) );
			if ( ! empty( $code_row->member_email ) ) {
				$wpdb->update( $table_attempts, array( 'member_type' => $new_val ), array( 'member_email' => $code_row->member_email ) );
			}
			wp_send_json_success( array( 'new_val' => $new_val ) );
		}
	}

	$att_id = isset( $_POST['att_id'] ) ? intval( $_POST['att_id'] ) : 0;
	if ( $att_id > 0 ) {
		$att_row = $wpdb->get_row( $wpdb->prepare( "SELECT member_email, member_type FROM {$table_attempts} WHERE id = %d", $att_id ) );
		if ( $att_row ) {
			$cur = ! empty( $att_row->member_type ) ? $att_row->member_type : 'paying';
			$new_val = ( $cur === 'paying' ) ? 'general' : 'paying';
			$wpdb->update( $table_attempts, array( 'member_type' => $new_val ), array( 'id' => $att_id ) );
			if ( ! empty( $att_row->member_email ) ) {
				$wpdb->update( $table_codes, array( 'member_type' => $new_val ), array( 'member_email' => $att_row->member_email ) );
			}
			wp_send_json_success( array( 'new_val' => $new_val ) );
		}
	}

	wp_send_json_error( 'Invalid request' );
}
add_action( 'wp_ajax_aws_sbg_toggle_code_member_type', 'aws_sbg_handle_code_member_type_toggle' );

function aws_sbg_render_exam_admin_page() {
	global $wpdb;
	$table_slots    = $wpdb->prefix . 'aws_exam_slots';
	$table_codes    = $wpdb->prefix . 'aws_access_codes';
	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'codes';

	// Handle Actions
	$message = '';
	if ( isset( $_POST['action'] ) && check_admin_referer( 'aws_exam_admin_action', 'aws_exam_nonce' ) ) {
		if ( $_POST['action'] === 'create_slot' ) {
			$slot_name    = sanitize_text_field( $_POST['slot_name'] );
			$track        = sanitize_text_field( $_POST['exam_track'] );
			$start_time   = sanitize_text_field( $_POST['start_time'] );
			$end_time     = sanitize_text_field( $_POST['end_time'] );
			$max_capacity = intval( $_POST['max_capacity'] );

			if ( empty( $slot_name ) || empty( $start_time ) || empty( $end_time ) ) {
				$message = "⚠️ Please fill in all required slot fields (Slot Name, Start Time, and End Time).";
			} else {
				$inserted = $wpdb->insert(
					$table_slots,
					array(
						'slot_name'    => $slot_name,
						'exam_track'   => $track,
						'start_time'   => date( 'Y-m-d H:i:s', strtotime( $start_time ) ),
						'end_time'     => date( 'Y-m-d H:i:s', strtotime( $end_time ) ),
						'max_capacity' => $max_capacity,
						'is_active'    => 1,
					),
					array( '%s', '%s', '%s', '%s', '%d', '%d' )
				);

				if ( false !== $inserted ) {
					$message = "✅ Successfully created exam time slot: {$slot_name}!";
				} else {
					$message = "⚠️ Failed to create slot. DB Error: " . esc_html( $wpdb->last_error );
				}
			}
		} elseif ( $_POST['action'] === 'delete_slot' ) {
			$slot_id = intval( $_POST['slot_id'] );
			if ( $slot_id > 0 ) {
				$wpdb->delete( $table_slots, array( 'id' => $slot_id ), array( '%d' ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$table_codes} SET slot_id = NULL WHERE slot_id = %d", $slot_id ) );
				$message = "Exam slot deleted and access codes unbound successfully.";
			}
		} elseif ( $_POST['action'] === 'toggle_slot_status' ) {
			$slot_id = intval( $_POST['slot_id'] );
			if ( $slot_id > 0 ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$table_slots} SET is_active = IF(is_active = 1, 0, 1) WHERE id = %d", $slot_id ) );
				$message = "Slot status toggled successfully.";
			}
		} elseif ( $_POST['action'] === 'save_exam_event_link' ) {
			$linked_event_id = intval( $_POST['linked_event_id'] );
			$require_event   = isset( $_POST['require_event_reg'] ) ? '1' : '0';
			update_option( 'aws_exam_linked_event_id', $linked_event_id );
			update_option( 'aws_exam_require_event_registration', $require_event );
			$message = "Saved exam event eligibility settings!";
		} elseif ( $_POST['action'] === 'update_slot_capacity' ) {
			$slot_id = intval( $_POST['slot_id'] );
			$max_cap = intval( $_POST['max_capacity'] );
			if ( $slot_id > 0 ) {
				$wpdb->update( $table_slots, array( 'max_capacity' => $max_cap ), array( 'id' => $slot_id ), array( '%d' ), array( '%d' ) );
				$message = "Updated slot capacity to " . ( $max_cap > 0 ? "{$max_cap} seats" : "Unlimited" ) . "!";
			}
		} elseif ( $_POST['action'] === 'toggle_public_reservations' ) {
			$cur_pub = get_option( 'aws_exam_public_reservations_enabled', '1' );
			$new_pub = ( $cur_pub === '1' ) ? '0' : '1';
			update_option( 'aws_exam_public_reservations_enabled', $new_pub );
			$message = ( $new_pub === '1' ) ? "Public candidate slot reservations are now OPEN (/reserve-slot/)!" : "Public candidate slot reservations are now PAUSED.";
		} elseif ( $_POST['action'] === 'send_slot_members_email' ) {
			$slot_id = intval( $_POST['slot_id'] );
			$slot_codes = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$table_codes} WHERE slot_id = %d", $slot_id ) );
			$sent_count = 0;
			foreach ( $slot_codes as $c_item ) {
				if ( aws_sbg_send_member_exam_email( $c_item->id ) ) {
					$sent_count++;
				}
			}
			$message = "Dispatched exam code emails to {$sent_count} members assigned to this slot!";
		} elseif ( $_POST['action'] === 'clear_all_slots' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_slots}" );
			$wpdb->query( "UPDATE {$table_codes} SET slot_id = NULL" );
			$message = "Cleared all exam time slots successfully!";
		} elseif ( $_POST['action'] === 'generate_bulk_codes' ) {
			$raw_list         = isset( $_POST['member_list'] ) ? trim( $_POST['member_list'] ) : '';
			$track            = sanitize_text_field( $_POST['exam_track'] );
			$max_att          = intval( $_POST['max_attempts'] );
			$per_q_sec        = intval( $_POST['per_question_seconds'] );
			$auto_email       = isset( $_POST['auto_email_members'] ) ? true : false;
			$slot_assign_mode = isset( $_POST['slot_assign_mode'] ) ? sanitize_text_field( $_POST['slot_assign_mode'] ) : 'none';
			$assigned_slot_id = isset( $_POST['assigned_slot_id'] ) ? intval( $_POST['assigned_slot_id'] ) : 0;
			
			if ( $max_att < 1 ) $max_att = 1;
			if ( $per_q_sec < 10 ) $per_q_sec = 45;

			// Quick create slot inline if chosen
			if ( $slot_assign_mode === 'quick_create' ) {
				$q_slot_name  = sanitize_text_field( $_POST['quick_slot_name'] );
				$q_start_time = sanitize_text_field( $_POST['quick_start_time'] );
				$q_end_time   = sanitize_text_field( $_POST['quick_end_time'] );
				if ( ! empty( $q_slot_name ) && ! empty( $q_start_time ) && ! empty( $q_end_time ) ) {
					$wpdb->insert(
						$table_slots,
						array(
							'slot_name'    => $q_slot_name,
							'exam_track'   => $track,
							'start_time'   => date( 'Y-m-d H:i:s', strtotime( $q_start_time ) ),
							'end_time'     => date( 'Y-m-d H:i:s', strtotime( $q_end_time ) ),
							'max_capacity' => 0,
							'is_active'    => 1,
						),
						array( '%s', '%s', '%s', '%s', '%d', '%d' )
					);
					$assigned_slot_id = $wpdb->insert_id;
				}
			}

			// Active slots for auto-distribution
			$active_slots = array();
			if ( $slot_assign_mode === 'auto_distribute' ) {
				$active_slots = $wpdb->get_results( "SELECT id FROM {$table_slots} WHERE is_active = 1 ORDER BY start_time ASC" );
			}

			$lines = explode( "\n", $raw_list );
			$created_count = 0;
			$emailed_count = 0;
			$line_index    = 0;

			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( empty( $line ) ) continue;

				$parts = array_map( 'trim', explode( ',', $line ) );
				$name  = isset( $parts[0] ) ? sanitize_text_field( $parts[0] ) : 'Community Member';
				$email = isset( $parts[1] ) ? sanitize_email( $parts[1] ) : '';

				if ( empty( $email ) ) {
					$email = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $name ) ) . '@awscommunity.local';
				}

				$final_slot_id = null;
				if ( $slot_assign_mode === 'auto_distribute' && ! empty( $active_slots ) ) {
					$slot_pick_idx = $line_index % count( $active_slots );
					$final_slot_id = $active_slots[ $slot_pick_idx ]->id;
				} elseif ( ( $slot_assign_mode === 'specific' || $slot_assign_mode === 'quick_create' ) && $assigned_slot_id > 0 ) {
					$final_slot_id = $assigned_slot_id;
				}

				$code = 'AWS-' . strtoupper( wp_generate_password( 5, false, false ) );

				$inserted = $wpdb->insert(
					$table_codes,
					array(
						'access_code'          => $code,
						'slot_id'              => $final_slot_id,
						'member_name'          => $name,
						'member_email'         => $email,
						'exam_track'           => $track,
						'per_question_seconds' => $per_q_sec,
						'allow_backtrack'      => 0,
						'max_attempts'         => $max_att,
						'used_attempts'        => 0,
					),
					array( '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d' )
				);

				if ( false !== $inserted && $wpdb->insert_id ) {
					$new_id = $wpdb->insert_id;
					$created_count++;
					$line_index++;

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
				if ( $slot_assign_mode === 'auto_distribute' && ! empty( $active_slots ) ) {
					$message .= " Automatically distributed candidates across " . count( $active_slots ) . " active slots.";
				} elseif ( $final_slot_id ) {
					$message .= " Assigned to designated exam time slot.";
				}
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
		} elseif ( $_POST['action'] === 'seed_questions' || $_POST['action'] === 'seed_live_questions' ) {
			delete_option( 'aws_sbg_questions_seeded' );
			$seeded = aws_sbg_auto_seed_questions( true, 'live' );
			$message = "🌱 Reseeded Live Question Bank ({$seeded} questions from seed-live-questions.json)!";
		} elseif ( $_POST['action'] === 'seed_test_questions' ) {
			delete_option( 'aws_sbg_questions_seeded' );
			$seeded = aws_sbg_auto_seed_questions( true, 'test' );
			$message = "🧪 Reseeded Test Question Bank ({$seeded} questions from seed-test-questions.json)!";
		} elseif ( $_POST['action'] === 'seed_both_questions' ) {
			delete_option( 'aws_sbg_questions_seeded' );
			$seeded = aws_sbg_auto_seed_questions( true, 'both' );
			$message = "⚡ Reseeded Unified Question Bank ({$seeded} questions from both test & live seed files)!";
		} elseif ( $_POST['action'] === 'clear_all_attempts' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_attempts}" );
			$wpdb->query( "UPDATE {$table_codes} SET used_attempts = 0" );
			$message = "Successfully cleared all test attempt results and reset access code usage!";
		} elseif ( $_POST['action'] === 'clear_all_codes' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_codes}" );
			$message = "Successfully cleared all member access codes!";
		} elseif ( $_POST['action'] === 'unlock_code_session' ) {
			$code_id = intval( $_POST['code_id'] );
			$wpdb->update( $table_codes, array( 'active_session_token' => null, 'device_fingerprint' => null, 'session_started_at' => null ), array( 'id' => $code_id ) );
			$message = "🔓 Session lock cleared for access code ID #{$code_id}. Candidate may start a new device session.";
		} elseif ( $_POST['action'] === 'reset_all_test_data' ) {
			$wpdb->query( "TRUNCATE TABLE {$table_attempts}" );
			$wpdb->query( "TRUNCATE TABLE {$table_codes}" );
			$wpdb->query( "TRUNCATE TABLE {$table_slots}" );
			$message = "Successfully reset all test data (cleared all access codes, slots & test attempt history)!";
		} elseif ( $_POST['action'] === 'set_question_limit' || $_POST['action'] === 'set_exam_mode' ) {
			$new_mode = isset( $_POST['exam_mode'] ) ? sanitize_text_field( $_POST['exam_mode'] ) : '';
			if ( empty( $new_mode ) ) {
				$new_limit = isset( $_POST['question_limit'] ) ? intval( $_POST['question_limit'] ) : 50;
				$new_mode  = ( $new_limit === 10 ) ? 'test' : 'live';
			}

			if ( $new_mode === 'test' ) {
				update_option( 'aws_exam_operating_mode', 'test' );
				update_option( 'aws_exam_question_limit', 10 );
				$message = "🧪 Test Mode Activated: Exam portal is now set to fixed 10 basic foundational questions for rapid testing & evaluation.";
			} else {
				update_option( 'aws_exam_operating_mode', 'live' );
				update_option( 'aws_exam_question_limit', 50 );
				$message = "🚀 Live Exam Mode Activated: Exam portal will now deliver 50 randomized questions from the full dataset.";
			}
		}
	}

	// Mode State
	$cur_mode  = get_option( 'aws_exam_operating_mode', '' );
	$cur_limit = intval( get_option( 'aws_exam_question_limit', 50 ) );
	if ( empty( $cur_mode ) ) {
		$cur_mode = ( $cur_limit === 10 ) ? 'test' : 'live';
	}

	// Fetch Slots Data
	$all_slots = $wpdb->get_results( "SELECT * FROM {$table_slots} ORDER BY start_time ASC" );
	$slots_by_id = array();
	$active_slots_now = 0;
	$upcoming_slots   = 0;
	$expired_slots    = 0;
	$now_timestamp    = current_time( 'timestamp' );

	foreach ( $all_slots as $sl ) {
		$slots_by_id[ $sl->id ] = $sl;
		$s_start = strtotime( $sl->start_time );
		$s_end   = strtotime( $sl->end_time );
		if ( $now_timestamp >= $s_start && $now_timestamp <= $s_end ) {
			$active_slots_now++;
		} elseif ( $now_timestamp < $s_start ) {
			$upcoming_slots++;
		} else {
			$expired_slots++;
		}
	}

	// Fetch Codes with optional slot filter & member type filter
	$code_slot_filter = isset( $_GET['slot_filter'] ) ? intval( $_GET['slot_filter'] ) : 0;
	$code_type_filter = isset( $_GET['code_type_filter'] ) ? sanitize_text_field( $_GET['code_type_filter'] ) : '';

	$code_where = array();
	if ( $code_slot_filter > 0 ) {
		$code_where[] = $wpdb->prepare( "slot_id = %d", $code_slot_filter );
	}
	if ( $code_type_filter === 'paying' ) {
		$code_where[] = "(member_type = 'paying' OR member_type IS NULL OR member_type = '')";
	} elseif ( $code_type_filter === 'general' ) {
		$code_where[] = "member_type = 'general'";
	}
	$code_where_sql = ! empty( $code_where ) ? " WHERE " . implode( " AND ", $code_where ) : "";
	$codes = $wpdb->get_results( "SELECT * FROM {$table_codes} {$code_where_sql} ORDER BY id DESC LIMIT 200" );

	// Fetch Attempts with optional slot filter & leaderboard category filter
	$att_slot_filter = isset( $_GET['att_slot_filter'] ) ? intval( $_GET['att_slot_filter'] ) : 0;
	$att_type_filter = isset( $_GET['att_type_filter'] ) ? sanitize_text_field( $_GET['att_type_filter'] ) : '';

	$att_where = array();
	if ( $att_slot_filter > 0 ) {
		$att_where[] = $wpdb->prepare( "slot_id = %d", $att_slot_filter );
	}
	if ( $att_type_filter === 'paying' ) {
		$att_where[] = "(member_type = 'paying' OR member_type IS NULL OR member_type = '')";
	} elseif ( $att_type_filter === 'general' ) {
		$att_where[] = "member_type = 'general'";
	}
	$att_where_sql = ! empty( $att_where ) ? " WHERE " . implode( " AND ", $att_where ) : "";

	// When paying filter is active, sort by Top Scorer ranking (score DESC, time_spent_ms ASC, time_spent_seconds ASC)
	$att_order_sql = ( $att_type_filter === 'paying' ) ? " ORDER BY score DESC, time_spent_ms ASC, time_spent_seconds ASC, id DESC " : " ORDER BY id DESC ";
	$attempts = $wpdb->get_results( "SELECT * FROM {$table_attempts} {$att_where_sql} {$att_order_sql} LIMIT 200" );

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
		.aws-status-badge {
			padding: 4px 10px;
			border-radius: 12px;
			font-size: 11px;
			font-weight: 700;
			display: inline-flex;
			align-items: center;
			gap: 5px;
		}
	</style>

	<div class="wrap">
		<h1 style="display:flex; align-items:center; justify-content:space-between; font-weight:700; flex-wrap: wrap; gap: 12px;">
			<span style="display:flex; align-items:center; gap:10px;">
				<span class="dashicons dashicons-welcome-learn-more" style="font-size:32px; width:32px; height:32px; color:#ad5cff;"></span>
				AWS Foundational Certification Mock Exam System
			</span>
			<div style="display:flex; align-items:center; gap:10px; flex-wrap: wrap;">
				
				<!-- INTERACTIVE DUAL-PILL MODE SWITCHER -->
				<div style="display: inline-flex; align-items: center; background: #0f172a; border-radius: 9999px; padding: 4px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); border: 1px solid #1e293b;">
					<form method="post" style="display: inline; margin: 0;">
						<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
						<input type="hidden" name="action" value="set_exam_mode">
						<input type="hidden" name="exam_mode" value="test">
						<button type="submit" title="Switch to Test Mode (10 basic benchmark questions)" style="cursor: pointer; border: none; padding: 6px 14px; border-radius: 9999px; font-size: 12px; font-weight: 700; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; <?php echo ( $cur_mode === 'test' ) ? 'background: #f59e0b; color: #000000; font-weight: 800; box-shadow: 0 1px 4px rgba(245,158,11,0.4);' : 'background: transparent; color: #94a3b8;'; ?>">
							🧪 Test Mode (10 Qs)
						</button>
					</form>
					<form method="post" style="display: inline; margin: 0;">
						<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
						<input type="hidden" name="action" value="set_exam_mode">
						<input type="hidden" name="exam_mode" value="live">
						<button type="submit" title="Switch to Live Mode (50 randomized questions from bank)" style="cursor: pointer; border: none; padding: 6px 14px; border-radius: 9999px; font-size: 12px; font-weight: 700; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; <?php echo ( $cur_mode === 'live' ) ? 'background: #10b981; color: #ffffff; font-weight: 800; box-shadow: 0 1px 4px rgba(16,185,129,0.4);' : 'background: transparent; color: #94a3b8;'; ?>">
							🚀 Live Mode (50 Qs)
						</button>
					</form>
				</div>

				<?php $is_pub_open = get_option( 'aws_exam_public_reservations_enabled', '1' ) === '1'; ?>
				<form method="post" style="display:inline; margin:0;">
					<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
					<input type="hidden" name="action" value="toggle_public_reservations">
					<button type="submit" style="cursor: pointer; padding: 7px 14px; border-radius: 9999px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; <?php echo $is_pub_open ? 'color:#065f46; border:1px solid #6ee7b7; background:#d1fae5;' : 'color:#991b1b; border:1px solid #fca5a5; background:#fee2e2;'; ?>" title="Click to Pause/Open Public Slot Reservations">
						<?php echo $is_pub_open ? '🟢 Public Reservations: OPEN' : '🔴 Public Reservations: PAUSED'; ?>
					</button>
				</form>

				<button type="button" onclick="awsPreviewAdminCertificate()" style="cursor: pointer; background: #ad5cff; border: 1px solid #9333ea; color: #ffffff; padding: 7px 14px; border-radius: 9999px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(173,92,255,0.3); transition: all 0.2s;">
					📜 Preview Certificate Template
				</button>
				
				<form method="post" style="display:inline; margin: 0;" onsubmit="return confirm('WARNING: Are you sure you want to reset ALL test data? This will permanently delete all access codes, slots, and attempt history.');">
					<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
					<input type="hidden" name="action" value="reset_all_test_data">
					<button type="submit" style="cursor: pointer; background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48; padding: 7px 14px; border-radius: 9999px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;">
						🔄 Reset All Test Data
					</button>
				</form>
			</div>
		</h1>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<h2 class="nav-tab-wrapper" style="margin-top: 15px;">
			<a href="?page=aws-mock-exam&tab=codes" class="nav-tab <?php echo $active_tab === 'codes' ? 'nav-tab-active' : ''; ?>">Access Code Generator</a>
			<a href="?page=aws-mock-exam&tab=slots" class="nav-tab <?php echo $active_tab === 'slots' ? 'nav-tab-active' : ''; ?>">⏱️ Time Slots (<?php echo count($all_slots); ?>)</a>
			<a href="?page=aws-mock-exam&tab=questions" class="nav-tab <?php echo $active_tab === 'questions' ? 'nav-tab-active' : ''; ?>">Question Bank (<?php echo $question_count; ?>)</a>
			<a href="?page=aws-mock-exam&tab=results" class="nav-tab <?php echo $active_tab === 'results' ? 'nav-tab-active' : ''; ?>">🏆 Leaderboard (<?php echo $total_attempts; ?>)</a>
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
				$code_slotted_count = 0;

				foreach ( $codes as $c ) {
					if ( strtolower($c->exam_track) === 'aif-c01' ) {
						$code_aif_count++;
					} else {
						$code_clf_count++;
					}
					if ( ! empty( $c->slot_id ) ) {
						$code_slotted_count++;
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
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Assigned to Time Slots</div>
						<div style="font-size: 24px; font-weight: 800; color: #3b82f6; margin-top: 4px;"><?php echo $code_slotted_count; ?> <span style="font-size: 13px; color: #64748b; font-weight: 600;">/ <?php echo $total_codes_count; ?></span></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #10b981;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Active Slots Right Now</div>
						<div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?php echo $active_slots_now; ?> <span style="font-size: 13px; color: #64748b; font-weight: 600;">of <?php echo count($all_slots); ?></span></div>
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
							⚡ Bulk Generate Member Access Codes & Divide Load Across Slots
						</h3>
						<p style="color: #64748b; margin-bottom: 20px; font-size: 13px;">Generate unique 6-character access codes in bulk and assign them to scheduled time slots to divide server load.</p>

						<form method="post">
							<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
							<input type="hidden" name="action" value="generate_bulk_codes">

							<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155; font-size: 13px;">
										Paste Members List (One member per line: Name, Email):
									</label>
									<textarea name="member_list" rows="8" class="large-text" placeholder="Anas Riaz, anas@example.com&#10;Jane Smith, jane@example.com&#10;Alex Johnson, alex@example.com" required style="width: 100%; border-radius: 6px; font-family: monospace; font-size: 13px; padding: 10px;"></textarea>
								</div>

								<div style="display: flex; flex-direction: column; justify-content: space-between; gap: 10px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
									<div>
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">⏱️ Assign to Exam Slot:</label>
										<select name="slot_assign_mode" id="slot_assign_mode" onchange="toggleSlotFields(this.value)" style="width: 100%; padding: 6px; font-weight: 600;">
											<option value="none">🔓 No Slot / Open Access Window</option>
											<?php if ( ! empty( $all_slots ) ) : ?>
												<option value="specific" selected>🎯 Assign to Specific Slot</option>
												<option value="auto_distribute">⚡ Auto-Distribute across all Active Slots</option>
											<?php endif; ?>
											<option value="quick_create">➕ Quick Create New Slot Inline</option>
										</select>
									</div>

									<div id="specific_slot_container" style="<?php echo empty($all_slots) ? 'display:none;' : ''; ?>">
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Select Slot:</label>
										<select name="assigned_slot_id" style="width: 100%; padding: 6px;">
											<?php foreach ( $all_slots as $sl_opt ) : 
												$s_txt = $sl_opt->slot_name . ' (' . date('M j, g:i A', strtotime($sl_opt->start_time)) . ' - ' . date('g:i A', strtotime($sl_opt->end_time)) . ')';
											?>
												<option value="<?php echo $sl_opt->id; ?>"><?php echo esc_html( $s_txt ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>

									<div id="quick_slot_container" style="display: none; background: #ffffff; padding: 10px; border-radius: 6px; border: 1px dashed #ad5cff;">
										<label style="display: block; font-weight: 700; margin-bottom: 2px; color: #334155; font-size: 11px;">New Slot Name:</label>
										<input type="text" name="quick_slot_name" placeholder="Batch 1 - Morning" style="width: 100%; padding: 4px; font-size: 12px; margin-bottom: 6px;">
										
										<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
											<div>
												<label style="display: block; font-weight: 700; color: #334155; font-size: 10px;">Start Time:</label>
												<input type="datetime-local" name="quick_start_time" style="width: 100%; padding: 4px; font-size: 11px;">
											</div>
											<div>
												<label style="display: block; font-weight: 700; color: #334155; font-size: 10px;">End Time:</label>
												<input type="datetime-local" name="quick_end_time" style="width: 100%; padding: 4px; font-size: 11px;">
											</div>
										</div>
									</div>

									<div>
										<label style="display: block; font-weight: 700; margin-bottom: 4px; color: #334155; font-size: 12px;">Certification Track:</label>
										<select name="exam_track" style="width: 100%; padding: 6px;">
											<option value="clf-c02">AWS Cloud Practitioner (CLF-C02)</option>
											<option value="aif-c01">AWS AI Practitioner (AIF-C01)</option>
										</select>
									</div>

									<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
										<div>
											<label style="display: block; font-weight: 700; margin-bottom: 2px; color: #334155; font-size: 11px;">Time/Q (Sec):</label>
											<input type="number" name="per_question_seconds" value="40" min="10" max="300" style="width: 100%; padding: 4px;">
										</div>
										<div>
											<label style="display: block; font-weight: 700; margin-bottom: 2px; color: #334155; font-size: 11px;">Max Attempts:</label>
											<input type="number" name="max_attempts" value="1" min="1" max="10" style="width: 100%; padding: 4px;">
										</div>
									</div>

									<div style="padding-top: 6px; border-top: 1px solid #e2e8f0;">
										<label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0066cc; font-size: 12px; cursor: pointer;">
											<input type="checkbox" name="auto_email_members" value="1" checked>
											✉️ Auto-email code & slot schedule via SMTP
										</label>
									</div>
								</div>
							</div>

							<div style="text-align: right;">
								<button type="submit" class="button button-primary button-large" style="padding: 6px 24px; font-size: 14px; font-weight: 700; background: #ad5cff; border-color: #8b31ff;">
									⚡ Generate & Schedule Access Codes Now
								</button>
							</div>
						</form>
					</div>

					<!-- 2. GENERATED ACCESS CODES WIDE TABLE CARD -->
					<div class="aws-card" style="padding: 24px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
							<div style="display: flex; align-items: center; gap: 15px;">
								<h3 style="margin: 0; font-size: 18px; color: #0f172a;">
									Generated Access Codes (<?php echo count( $codes ); ?>)
								</h3>
								
								<!-- Slot Filter Dropdown -->
								<form method="get" style="display: inline-flex; align-items: center; gap: 6px;">
									<input type="hidden" name="page" value="aws-mock-exam">
									<input type="hidden" name="tab" value="codes">
									<label style="font-size: 12px; font-weight: 700; color: #64748b;">Filter Slot:</label>
									<select name="slot_filter" onchange="this.form.submit()" style="padding: 3px 8px; font-size: 12px;">
										<option value="0">All Slots & Open Codes</option>
										<?php foreach ( $all_slots as $sl_f ) : ?>
											<option value="<?php echo $sl_f->id; ?>" <?php selected( $code_slot_filter, $sl_f->id ); ?>>
												<?php echo esc_html( $sl_f->slot_name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</form>
							</div>

							<div style="display: flex; gap: 10px;">
								<form method="post" style="display:inline;" onsubmit="return confirm('Send exam emails to ALL candidates in this list?');">
									<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
									<input type="hidden" name="action" value="send_all_members_email">
									<button type="submit" class="button button-primary" style="background:#059669; border-color:#047857;">✉️ Email All Candidates</button>
								</form>

								<button onclick="copyCodesCSV()" class="button button-secondary">📋 Copy CSV</button>
								
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
										<th style="min-width: 120px;">Access Code</th>
										<th style="min-width: 150px;">Member Name</th>
										<th style="min-width: 180px;">Email</th>
										<th style="min-width: 120px; text-align: center;">Category</th>
										<th style="min-width: 160px;">Assigned Slot</th>
										<th style="min-width: 90px; text-align: center;">Track</th>
										<th style="min-width: 80px; text-align: center;">Timer</th>
										<th style="min-width: 90px; text-align: center;">Attempts</th>
										<th style="min-width: 110px; text-align: center;">Send Email</th>
										<th style="min-width: 140px;">Created Date</th>
									</tr>
								</thead>
								<tbody>
									<?php if ( empty( $codes ) ) : ?>
										<tr><td colspan="10" style="text-align: center; padding: 20px; color: #64748b;">No access codes found for this view.</td></tr>
									<?php else : ?>
										<?php foreach ( $codes as $c ) : 
											$slot_assigned = ( ! empty( $c->slot_id ) && isset( $slots_by_id[ $c->slot_id ] ) ) ? $slots_by_id[ $c->slot_id ] : null;
											$is_slot_active = false;
											$is_slot_upcoming = false;
											$is_slot_expired = false;
											if ( $slot_assigned ) {
												$s_start = strtotime( $slot_assigned->start_time );
												$s_end   = strtotime( $slot_assigned->end_time );
												if ( $now_timestamp >= $s_start && $now_timestamp <= $s_end ) {
													$is_slot_active = true;
												} elseif ( $now_timestamp < $s_start ) {
													$is_slot_upcoming = true;
												} else {
													$is_slot_expired = true;
												}
											}
											$c_type = ! empty( $c->member_type ) ? $c->member_type : 'paying';
											$c_is_pay = ( $c_type === 'paying' );
										?>
											<tr style="vertical-align: middle;">
												<td><strong style="color: #6366f1; font-family: monospace; font-size: 15px; letter-spacing: 1px;"><?php echo esc_html( $c->access_code ); ?></strong></td>
												<td><strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $c->member_name ); ?></strong></td>
												<td style="font-family: monospace; color: #475569; font-size: 12px;"><?php echo esc_html( $c->member_email ); ?></td>
												<td style="text-align: center;">
													<button type="button" onclick="awsToggleCodeMemberType(<?php echo $c->id; ?>, 'code', this)" style="border:1px solid <?php echo $c_is_pay ? '#6ee7b7' : '#cbd5e1'; ?>; background:<?php echo $c_is_pay ? '#ecfdf5' : '#f8fafc'; ?>; color:<?php echo $c_is_pay ? '#047857' : '#475569'; ?>; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:800; cursor:pointer;" title="Click to toggle between Paying (Prize Eligible) and General (Voucher Only)">
														<?php echo $c_is_pay ? '💳 Paying (Prize)' : '🎟️ General (Aid/Pro)'; ?>
													</button>
												</td>
												<td>
													<?php if ( $slot_assigned ) : ?>
														<div>
															<strong style="color: #0f172a; font-size: 12px;"><?php echo esc_html( $slot_assigned->slot_name ); ?></strong>
															<div style="font-size: 11px; color: #64748b;">
																<?php echo date( 'M j, g:i A', strtotime( $slot_assigned->start_time ) ) . ' - ' . date( 'g:i A', strtotime( $slot_assigned->end_time ) ); ?>
															</div>
															<?php if ( $is_slot_active ) : ?>
																<span style="display:inline-block; font-size:10px; font-weight:800; color:#059669; background:#d1fae5; padding:2px 6px; border-radius:4px; margin-top:2px;">🟢 ACTIVE NOW</span>
															<?php elseif ( $is_slot_upcoming ) : ?>
																<span style="display:inline-block; font-size:10px; font-weight:800; color:#d97706; background:#fef3c7; padding:2px 6px; border-radius:4px; margin-top:2px;">🟡 UPCOMING</span>
															<?php else : ?>
																<span style="display:inline-block; font-size:10px; font-weight:800; color:#dc2626; background:#fee2e2; padding:2px 6px; border-radius:4px; margin-top:2px;">🔴 EXPIRED</span>
															<?php endif; ?>
														</div>
													<?php else : ?>
														<span style="color: #94a3b8; font-size: 12px; font-style: italic;">Open Access (No Slot)</span>
													<?php endif; ?>
												</td>
												<td style="text-align: center;">
													<span style="background: <?php echo strtolower($c->exam_track) === 'aif-c01' ? '#d1fae5' : '#e0e7ff'; ?>; color: <?php echo strtolower($c->exam_track) === 'aif-c01' ? '#065f46' : '#3730a3'; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-block;">
														<?php echo esc_html( strtoupper( $c->exam_track ) ); ?>
													</span>
												</td>
												<td style="text-align: center; font-weight: 600; color: #334155;">
													<?php echo esc_html( ( isset($c->per_question_seconds) ? $c->per_question_seconds : 40 ) . 's/q' ); ?>
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
													<?php if ( ! empty( $c->active_session_token ) ) : ?>
														<div style="margin-top: 5px;">
															<form method="post" style="display:inline;" onsubmit="return confirm('Unlock active device session for this candidate?');">
																<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
																<input type="hidden" name="action" value="unlock_code_session">
																<input type="hidden" name="code_id" value="<?php echo $c->id; ?>">
																<button type="submit" class="button button-small" style="font-size:10px; padding: 0 6px; height: 22px; line-height: 20px; color: #b91c1c; background: #fef2f2; border-color: #fca5a5;" title="Clear active device session lock">🔓 Unlock Session</button>
															</form>
														</div>
													<?php endif; ?>
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
					function toggleSlotFields(mode) {
						document.getElementById('specific_slot_container').style.display = (mode === 'specific') ? 'block' : 'none';
						document.getElementById('quick_slot_container').style.display = (mode === 'quick_create') ? 'block' : 'none';
					}

					function copyCodesCSV() {
						let csv = "Access Code,Member Name,Email,Track,Slot\n";
						<?php foreach ( $codes as $c ) : 
							$slot_txt = ( ! empty( $c->slot_id ) && isset( $slots_by_id[ $c->slot_id ] ) ) ? addslashes($slots_by_id[ $c->slot_id ]->slot_name) : 'None';
						?>
							csv += "<?php echo $c->access_code; ?>,<?php echo addslashes($c->member_name); ?>,<?php echo $c->member_email; ?>,<?php echo $c->exam_track; ?>,<?php echo $slot_txt; ?>\n";
						<?php endforeach; ?>
						navigator.clipboard.writeText(csv).then(() => {
							alert("Copied all codes to clipboard in CSV format!");
						});
					}
				</script>

			<?php elseif ( $active_tab === 'slots' ) : ?>

				<!-- KPI Metric Cards for Exam Slots -->
				<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #ad5cff;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Configured Slots</div>
						<div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo count($all_slots); ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #10b981;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Active Slots Right Now</div>
						<div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;"><?php echo $active_slots_now; ?></div>
					</div>
					<div class="aws-kpi-card" style="padding: 16px; border-left: 4px solid #f59e0b;">
						<div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Upcoming Slots</div>
						<div style="font-size: 24px; font-weight: 800; color: #f59e0b; margin-top: 4px;"><?php echo $upcoming_slots; ?></div>
					</div>
				</div>

				<?php
				$linked_event_id   = intval( get_option( 'aws_exam_linked_event_id', 0 ) );
				$require_event_reg = get_option( 'aws_exam_require_event_registration', '1' ) === '1';
				$all_available_events = get_posts( array(
					'post_type'      => 'aws_event',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				) );
				?>
				<!-- EXAM ELIGIBILITY & LINKED EVENT GATE -->
				<div class="aws-card" style="padding: 16px 20px; margin-bottom: 20px; background: #ffffff; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
					<div>
						<div style="font-weight: 800; font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							<span>🛡️ Candidate Eligibility & Event Gate</span>
							<span style="font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 12px; <?php echo $require_event_reg ? 'background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;' : 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;'; ?>">
								<?php echo $require_event_reg ? 'GATE ACTIVE (VERIFIED ONLY)' : 'GATE DISABLED (OPEN TO ALL)'; ?>
							</span>
						</div>
						<p style="font-size: 12px; color: #64748b; margin: 3px 0 0 0;">Candidates reserving slots will be verified against this specific event's registered attendee roster.</p>
					</div>
					<form method="post" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
						<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
						<input type="hidden" name="action" value="save_exam_event_link">
						
						<label style="font-size: 12px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 5px; cursor: pointer;">
							<input type="checkbox" name="require_event_reg" value="1" <?php checked( $require_event_reg, true ); ?>>
							Enforce Verification
						</label>

						<select name="linked_event_id" style="font-size: 12px; padding: 5px 10px; border-radius: 6px; border: 1px solid #cbd5e1; max-width: 260px;">
							<option value="0">-- Any Registered Event Attendee --</option>
							<?php foreach ( $all_available_events as $ev_item ) : ?>
								<option value="<?php echo $ev_item->ID; ?>" <?php selected( $linked_event_id, $ev_item->ID ); ?>>
									<?php echo esc_html( $ev_item->post_title ); ?> (ID: #<?php echo $ev_item->ID; ?>)
								</option>
							<?php endforeach; ?>
						</select>

						<button type="submit" class="button button-primary" style="font-weight: 700; font-size: 12px; background: #ad5cff; border-color: #8b31ff;">💾 Save Gate</button>
					</form>
				</div>

				<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
					
					<!-- 1. CREATE NEW TIME SLOT FORM -->
					<div class="aws-card" style="padding: 24px;">
						<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							⏱️ Create Exam Time Slot
						</h3>
						<p style="color: #64748b; margin-bottom: 20px; font-size: 13px;">Define a scheduled time window. Access codes assigned to this slot will auto-activate at start time and auto-revoke upon slot end.</p>

						<form method="post">
							<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
							<input type="hidden" name="action" value="create_slot">

							<div style="margin-bottom: 14px;">
								<label style="display: block; font-weight: 700; margin-bottom: 5px; color: #334155; font-size: 13px;">Slot Name / Title:</label>
								<input type="text" name="slot_name" placeholder="e.g. Batch 1 — Morning Session" required style="width: 100%; padding: 8px; border-radius: 6px;">
							</div>

							<div style="margin-bottom: 14px;">
								<label style="display: block; font-weight: 700; margin-bottom: 5px; color: #334155; font-size: 13px;">Certification Track:</label>
								<select name="exam_track" style="width: 100%; padding: 8px; border-radius: 6px;">
									<option value="all">Any Track (All Access Codes)</option>
									<option value="clf-c02">AWS Cloud Practitioner (CLF-C02)</option>
									<option value="aif-c01">AWS AI Practitioner (AIF-C01)</option>
								</select>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 5px; color: #334155; font-size: 13px;">Start Time:</label>
									<input type="datetime-local" name="start_time" required style="width: 100%; padding: 8px; border-radius: 6px;">
								</div>
								<div>
									<label style="display: block; font-weight: 700; margin-bottom: 5px; color: #334155; font-size: 13px;">End Time:</label>
									<input type="datetime-local" name="end_time" required style="width: 100%; padding: 8px; border-radius: 6px;">
								</div>
							</div>

							<div style="margin-bottom: 18px;">
								<label style="display: block; font-weight: 700; margin-bottom: 5px; color: #334155; font-size: 13px;">Max Candidate Capacity:</label>
								<input type="number" name="max_capacity" value="50" min="1" max="1000" style="width: 100%; padding: 8px; border-radius: 6px;">
								<span style="font-size: 11px; color: #64748b;">Maximum candidate access codes that can be assigned/reserved in this slot.</span>
							</div>

							<button type="submit" class="button button-primary" style="width: 100%; background: #ad5cff; border-color: #8b31ff; font-weight: 700; padding: 8px; height: auto;">
								➕ Add Exam Time Slot
							</button>
						</form>
					</div>

					<!-- 2. TIME SLOTS LIST -->
					<div class="aws-card" style="padding: 24px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
							<h3 style="margin: 0; font-size: 18px; color: #0f172a;">
								Configured Exam Time Slots (<?php echo count( $all_slots ); ?>)
							</h3>
							<?php if ( ! empty( $all_slots ) ) : ?>
								<form method="post" style="display:inline;" onsubmit="return confirm('Clear ALL exam time slots?');">
									<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
									<input type="hidden" name="action" value="clear_all_slots">
									<button type="submit" class="button button-secondary" style="color:#dc2626; border-color:#fca5a5;">🗑️ Clear All Slots</button>
								</form>
							<?php endif; ?>
						</div>

						<div style="overflow-x: auto;">
							<table class="wp-list-table widefat striped" style="border-collapse: collapse; width: 100%;">
								<thead>
									<tr>
										<th style="min-width: 140px;">Slot Name</th>
										<th style="min-width: 90px;">Track</th>
										<th style="min-width: 160px;">Scheduled Window</th>
										<th style="min-width: 120px; text-align: center;">Frontend Visibility</th>
										<th style="min-width: 110px; text-align: center;">Time Window</th>
										<th style="min-width: 90px; text-align: center;">Assigned</th>
										<th style="min-width: 90px; text-align: center;">Attempts</th>
										<th style="min-width: 170px; text-align: center;">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php if ( empty( $all_slots ) ) : ?>
										<tr><td colspan="8" style="text-align: center; padding: 25px; color: #64748b;">No time slots created yet. Create your first slot on the left to divide candidate load!</td></tr>
									<?php else : ?>
										<?php foreach ( $all_slots as $sl_item ) : 
											$assigned_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_codes} WHERE slot_id = %d", $sl_item->id ) );
											$attempt_count  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_attempts} WHERE slot_id = %d", $sl_item->id ) );
											
											$s_start = strtotime( $sl_item->start_time );
											$s_end   = strtotime( $sl_item->end_time );
											$is_active_window = ( $now_timestamp >= $s_start && $now_timestamp <= $s_end );
											$is_upcoming      = ( $now_timestamp < $s_start );
											$is_expired       = ( $now_timestamp > $s_end );
											$is_visible       = ( intval( $sl_item->is_active ) === 1 );
										?>
											<tr style="vertical-align: middle; <?php echo ( ! $is_visible ) ? 'background-color: #f8fafc; opacity: 0.75;' : ''; ?>">
												<td>
													<strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $sl_item->slot_name ); ?></strong>
													<?php $max_cap = intval($sl_item->max_capacity); ?>
													<form method="post" style="display:inline-flex; align-items:center; gap:3px; margin-top:5px;">
														<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
														<input type="hidden" name="action" value="update_slot_capacity">
														<input type="hidden" name="slot_id" value="<?php echo $sl_item->id; ?>">
														<span style="font-size:10px; color:#64748b; font-weight:600;">Max:</span>
														<input type="number" name="max_capacity" value="<?php echo $max_cap; ?>" min="0" style="width:55px; height:22px; font-size:11px; padding:1px 4px; border-radius:4px;" title="Max Capacity (0 = unlimited)">
														<button type="submit" class="button button-small" style="height:22px; line-height:20px; padding:0 5px; font-size:10px; font-weight:600;" title="Save new capacity">💾</button>
													</form>
												</td>
												<td>
													<span style="font-size: 11px; font-weight: 700; color: #475569;">
														<?php echo esc_html( strtoupper($sl_item->exam_track) ); ?>
													</span>
												</td>
												<td>
													<div style="font-size: 12px; font-weight: 600; color: #0f172a;">
														📅 <?php echo date( 'M j, Y', $s_start ); ?>
													</div>
													<div style="font-size: 11px; color: #64748b; font-family: monospace;">
														⏱️ <?php echo date( 'g:i A', $s_start ) . ' – ' . date( 'g:i A', $s_end ); ?>
													</div>
												</td>
												<td style="text-align: center;">
													<?php if ( $is_visible ) : ?>
														<span class="aws-status-badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" title="Currently visible on /reserve-slot/ and portal">
															👁️ Visible on Frontend
														</span>
													<?php else : ?>
														<span class="aws-status-badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;" title="Hidden from candidate self-service portal">
															🔒 Hidden from Frontend
														</span>
													<?php endif; ?>
												</td>
												<td style="text-align: center;">
													<?php if ( $is_active_window ) : ?>
														<span class="aws-status-badge" style="background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">
															<span style="width:7px; height:7px; border-radius:50%; background:#10b981; display:inline-block;"></span> ACTIVE NOW
														</span>
													<?php elseif ( $is_upcoming ) : ?>
														<span class="aws-status-badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
															🟡 UPCOMING
														</span>
													<?php else : ?>
														<span class="aws-status-badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
															🔴 CONCLUDED
														</span>
													<?php endif; ?>
												</td>
												<td style="text-align: center;">
													<a href="?page=aws-mock-exam&tab=codes&slot_filter=<?php echo $sl_item->id; ?>" style="font-weight: 800; font-size: 13px; color: #6366f1; text-decoration: underline;">
														<?php echo $assigned_count; ?><?php echo ( $max_cap > 0 ) ? ' / ' . $max_cap : ''; ?>
													</a>
													<?php if ( $max_cap > 0 ) : ?>
														<?php if ( $assigned_count >= $max_cap ) : ?>
															<div style="font-size: 10px; font-weight: 800; color: #dc2626;">🔴 FULL (0 left)</div>
														<?php else : ?>
															<div style="font-size: 10px; font-weight: 700; color: #059669;"><?php echo ($max_cap - $assigned_count); ?> seats left</div>
														<?php endif; ?>
													<?php else : ?>
														<div style="font-size: 10px; color: #64748b;">(Unlimited)</div>
													<?php endif; ?>
												</td>
												<td style="text-align: center; font-weight: 700; color: #0f172a;">
													<a href="?page=aws-mock-exam&tab=results&att_slot_filter=<?php echo $sl_item->id; ?>" style="color: #0f172a; text-decoration: none;">
														<?php echo $attempt_count; ?> attempts
													</a>
												</td>
												<td style="text-align: center;">
													<div style="display: flex; gap: 4px; justify-content: center; align-items: center; flex-wrap: wrap;">
														<!-- 1-CLICK HIDE / SHOW TOGGLE (NO DELETION) -->
														<form method="post" style="display:inline;">
															<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
															<input type="hidden" name="action" value="toggle_slot_status">
															<input type="hidden" name="slot_id" value="<?php echo $sl_item->id; ?>">
															<?php if ( $is_visible ) : ?>
																<button type="submit" class="button button-small" style="color:#b45309; border-color:#fde68a; font-size:11px;" title="Hide this slot from frontend without deleting">
																	👁️ Hide
																</button>
															<?php else : ?>
																<button type="submit" class="button button-small" style="color:#047857; border-color:#a7f3d0; font-size:11px; font-weight:700;" title="Show this slot on frontend portal">
																	👁️ Show
																</button>
															<?php endif; ?>
														</form>

														<form method="post" style="display:inline;" onsubmit="return confirm('Email all <?php echo $assigned_count; ?> candidates scheduled in this slot?');">
															<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
															<input type="hidden" name="action" value="send_slot_members_email">
															<input type="hidden" name="slot_id" value="<?php echo $sl_item->id; ?>">
															<button type="submit" class="button button-small" style="font-size:11px;" title="Email All Candidates in this Slot">✉️ Email</button>
														</form>

														<form method="post" style="display:inline;" onsubmit="return confirm('Delete this slot permanently? Codes will remain valid but unbound.');">
															<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
															<input type="hidden" name="action" value="delete_slot">
															<input type="hidden" name="slot_id" value="<?php echo $sl_item->id; ?>">
															<button type="submit" class="button button-small" style="color:#dc2626;" title="Delete Slot Permanently">🗑️</button>
														</form>
													</div>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>

				</div>

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
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
						<h3 style="margin:0; font-size:18px;">AWS Foundational Question Bank (<?php echo $question_count; ?> Questions in DB)</h3>
						<div style="display:flex; gap:8px; flex-wrap: wrap;">
							<a href="<?php echo admin_url('post-new.php?post_type=aws_question'); ?>" class="button button-secondary">➕ Add Question</a>
							<form method="post" style="display:inline;" onsubmit="return confirm('Reseed 10 fixed foundational questions from seed-test-questions.json?');">
								<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
								<input type="hidden" name="action" value="seed_test_questions">
								<button type="submit" class="button button-secondary" style="font-weight: 700; color: #b45309; border-color: #fcd34d;">🧪 Seed Test Set (10 Qs)</button>
							</form>
							<form method="post" style="display:inline;" onsubmit="return confirm('Reseed 75+ full exam questions from seed-live-questions.json?');">
								<?php wp_nonce_field( 'aws_exam_admin_action', 'aws_exam_nonce' ); ?>
								<input type="hidden" name="action" value="seed_live_questions">
								<button type="submit" class="button button-primary" style="background:#10b981; border-color:#059669; font-weight: 700;">🚀 Seed Live Dataset (75+ Qs)</button>
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
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
						<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
							<h3 style="margin: 0; font-size: 18px;">🏆 Exam Leaderboard & Submissions</h3>
							
							<!-- Leaderboard Category Filter Pills -->
							<div style="display: inline-flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 8px;">
								<a href="?page=aws-mock-exam&tab=results&att_type_filter=paying<?php echo $att_slot_filter ? '&att_slot_filter=' . $att_slot_filter : ''; ?>" class="button <?php echo $att_type_filter === 'paying' ? 'button-primary' : 'button-secondary'; ?>" style="font-size: 11px; font-weight: 800; <?php echo $att_type_filter === 'paying' ? 'background:#059669; border-color:#047857;' : ''; ?>">
									🏆 Top Scorers (Paying Only)
								</a>
								<a href="?page=aws-mock-exam&tab=results<?php echo $att_slot_filter ? '&att_slot_filter=' . $att_slot_filter : ''; ?>" class="button <?php echo empty($att_type_filter) ? 'button-primary' : 'button-secondary'; ?>" style="font-size: 11px; font-weight: 800; <?php echo empty($att_type_filter) ? 'background:#ad5cff; border-color:#8b31ff;' : ''; ?>">
									📊 All Candidates
								</a>
								<a href="?page=aws-mock-exam&tab=results&att_type_filter=general<?php echo $att_slot_filter ? '&att_slot_filter=' . $att_slot_filter : ''; ?>" class="button <?php echo $att_type_filter === 'general' ? 'button-primary' : 'button-secondary'; ?>" style="font-size: 11px; font-weight: 800;">
									🎟️ General (Aid / Pro)
								</a>
							</div>

							<!-- Slot Filter Dropdown for Attempts -->
							<form method="get" style="display: inline-flex; align-items: center; gap: 6px;">
								<input type="hidden" name="page" value="aws-mock-exam">
								<input type="hidden" name="tab" value="results">
								<?php if ( ! empty( $att_type_filter ) ) : ?>
									<input type="hidden" name="att_type_filter" value="<?php echo esc_attr( $att_type_filter ); ?>">
								<?php endif; ?>
								<label style="font-size: 12px; font-weight: 700; color: #64748b;">Slot:</label>
								<select name="att_slot_filter" onchange="this.form.submit()" style="padding: 3px 8px; font-size: 12px;">
									<option value="0">All Slots & Open Attempts</option>
									<?php foreach ( $all_slots as $sl_f ) : ?>
										<option value="<?php echo $sl_f->id; ?>" <?php selected( $att_slot_filter, $sl_f->id ); ?>>
											<?php echo esc_html( $sl_f->slot_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</form>
						</div>

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
									<th style="min-width: 170px;">Email</th>
									<th style="min-width: 120px; text-align: center;">Category</th>
									<th style="min-width: 140px;">Exam Slot</th>
									<th style="min-width: 90px; text-align: center;">Track</th>
									<th style="min-width: 110px; text-align: center;">Scaled Score</th>
									<th style="min-width: 140px; text-align: center;">Status</th>
									<th style="min-width: 90px; text-align: center;">Time Spent</th>
									<th style="min-width: 110px; text-align: center;">Tab Switches</th>
									<th style="min-width: 140px;">Date & Time</th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $attempts ) ) : ?>
									<tr><td colspan="11" style="text-align: center; padding: 20px;">No exam attempts recorded yet for this view.</td></tr>
								<?php else : ?>
									<?php foreach ( $attempts as $att ) : 
										$att_slot = ( ! empty( $att->slot_id ) && isset( $slots_by_id[ $att->slot_id ] ) ) ? $slots_by_id[ $att->slot_id ] : null;
										$att_type = ! empty( $att->member_type ) ? $att->member_type : 'paying';
										$att_is_pay = ( $att_type === 'paying' );
									?>
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
												<button type="button" onclick="awsToggleCodeMemberType(<?php echo $att->id; ?>, 'attempt', this)" style="border:1px solid <?php echo $att_is_pay ? '#6ee7b7' : '#cbd5e1'; ?>; background:<?php echo $att_is_pay ? '#ecfdf5' : '#f8fafc'; ?>; color:<?php echo $att_is_pay ? '#047857' : '#475569'; ?>; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:800; cursor:pointer;" title="Click to toggle between Paying (Prize Eligible) and General (Voucher Only)">
													<?php echo $att_is_pay ? '💳 Paying (Prize)' : '🎟️ General (Aid/Pro)'; ?>
												</button>
											</td>
											<td>
												<?php if ( $att_slot ) : ?>
													<strong style="color: #0f172a; font-size: 12px; display: block;"><?php echo esc_html( $att_slot->slot_name ); ?></strong>
													<span style="font-size: 10px; color: #64748b; font-family: monospace;"><?php echo date( 'M j, g:i A', strtotime( $att_slot->start_time ) ); ?></span>
												<?php else : ?>
													<span style="color: #94a3b8; font-size: 11px; font-style: italic;">Open / Direct</span>
												<?php endif; ?>
											</td>
											<td style="text-align: center;">
												<span style="background: <?php echo strtolower($att->exam_track) === 'aif-c01' ? '#d1fae5' : '#e0e7ff'; ?>; color: <?php echo strtolower($att->exam_track) === 'aif-c01' ? '#065f46' : '#3730a3'; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-block;">
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
													<span style="background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block;">Passed</span>
												<?php else : ?>
													<span style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block;">Below 700</span>
												<?php endif; ?>
											</td>
											<?php
											$tot_sec = floatval( $att->time_spent_seconds );
											if ( empty( $tot_sec ) && ! empty( $att->time_spent_ms ) ) {
												$tot_sec = $att->time_spent_ms / 1000;
											}
											$t_min = floor( $tot_sec / 60 );
											$t_sec = fmod( $tot_sec, 60 );
											$time_spent_formatted = sprintf( '%02dm %06.3fs', $t_min, $t_sec );
											?>
											<td style="text-align: center; font-weight: 700; font-family: monospace; color: #1e293b; font-size: 13px;">
												<?php echo esc_html( $time_spent_formatted ); ?>
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
						let csv = "ID,Candidate Name,Email,Track,Scaled Score,Status,Time Spent (Formatted),Time Spent (Ms),Tab Switches,Date\n";
						<?php foreach ( $attempts as $att ) : 
							$status_str = $att->violation_submitted ? 'SECURITY VIOLATION' : ($att->passed ? 'Passed' : 'Below 700');
							$tot_sec_csv = floatval( $att->time_spent_seconds );
							if ( empty( $tot_sec_csv ) && ! empty( $att->time_spent_ms ) ) {
								$tot_sec_csv = $att->time_spent_ms / 1000;
							}
							$t_min_csv = floor( $tot_sec_csv / 60 );
							$t_sec_csv = fmod( $tot_sec_csv, 60 );
							$time_spent_csv = sprintf( '%02dm %06.3fs', $t_min_csv, $t_sec_csv );
							$time_ms_csv = ! empty( $att->time_spent_ms ) ? intval( $att->time_spent_ms ) : round( $tot_sec_csv * 1000 );
						?>
							csv += "<?php echo $att->id; ?>,<?php echo addslashes($att->member_name); ?>,<?php echo $att->member_email; ?>,<?php echo $att->exam_track; ?>,<?php echo $att->score; ?>,<?php echo $status_str; ?>,<?php echo $time_spent_csv; ?>,<?php echo $time_ms_csv; ?>,<?php echo $att->tab_switch_count; ?>,<?php echo date('Y-m-d H:i:s', strtotime($att->created_at)); ?>\n";
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
	<!-- GOOGLE FONT FOR SIGNATURE PREVIEW -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

	<!-- ADMIN CERTIFICATE PREVIEW MODAL -->
	<div id="aws-admin-cert-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(7,4,13,0.92); z-index:9999999; backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:20px; box-sizing:border-box;">
		<div style="background:#140c24; border:2px solid #ad5cff; border-radius:24px; max-width:960px; width:100%; max-height:90vh; overflow:auto; padding:25px; box-shadow:0 25px 60px rgba(0,0,0,0.8); text-align:center;">
			<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
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
		modal.style.display = 'flex';
		
		const templateImg = await new Promise((resolve) => {
			const img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = () => resolve(img);
			const templatePath = '<?php echo esc_url( get_template_directory_uri() . '/Certificate Template/AWS Certification Preparation Exam Pass Certificate Template.png' ); ?>';
			img.src = templatePath + '?v=<?php echo time(); ?>';
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

		// 1. High-Contrast Top-Left Green Box Typography (Prestigious Certificate Design)
		const greenBoxX = 105 * scaleX;
		const greenBoxY = 105 * scaleY;

		// Eyebrow Badge
		ctx.fillStyle = 'rgba(4, 32, 23, 0.9)';
		if ('letterSpacing' in ctx) ctx.letterSpacing = (2 * scaleX) + 'px';
		ctx.font = `700 ${Math.round(15 * scaleX)}px "Segoe UI", -apple-system, sans-serif`;
		ctx.fillText('★ AWS CERTIFICATION SELF-ASSESSMENT 2026 ★', greenBoxX + (70 * scaleX), greenBoxY + (115 * scaleY));
		if ('letterSpacing' in ctx) ctx.letterSpacing = '0px';

		// Main Grand Headline: Certificate
		ctx.fillStyle = '#042017';
		ctx.font = `bold ${Math.round(72 * scaleX)}px "Georgia", "Times New Roman", serif`;
		ctx.fillText('Certificate', greenBoxX + (70 * scaleX), greenBoxY + (215 * scaleY));

		// Sub-headline: PASSED — AWS Certification Self-Assessment
		ctx.fillStyle = '#042017';
		ctx.font = `800 ${Math.round(24 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.fillText('PASSED — AWS Certification Self-Assessment', greenBoxX + (70 * scaleX), greenBoxY + (305 * scaleY));

		// Elegant Horizontal Accent Line
		ctx.strokeStyle = 'rgba(4, 32, 23, 0.35)';
		ctx.lineWidth = Math.max(1, Math.round(2 * scaleX));
		ctx.beginPath();
		ctx.moveTo(greenBoxX + (70 * scaleX), greenBoxY + (345 * scaleY));
		ctx.lineTo(greenBoxX + (620 * scaleX), greenBoxY + (345 * scaleY));
		ctx.stroke();

		// Organization Footer Label
		ctx.fillStyle = '#064e3b';
		ctx.font = `600 ${Math.round(20 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.fillText('Organized by AWS Student Builder Group at Air University Islamabad', greenBoxX + (70 * scaleX), greenBoxY + (400 * scaleY));

		// 2. Bottom-Right Participant Info Block (Centered in Dark Panel above Signature)
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
		ctx.fillText('For successfully completing the AWS Certification Self-Assessment', infoX + infoW / 2, infoY + (215 * scaleY));
		ctx.fillText(`with a score of 88% (Certificate ID: AWS-8K9P • Issued: ${dateStr})`, infoX + infoW / 2, infoY + (250 * scaleY));

		// 3. Small Disclaimer at Bottom of Trophy Box (Bottom-Left Container)
		const trophyBoxX = 105 * scaleX;
		const trophyBoxY = (105 + 520 + 40) * scaleY;
		const trophyBoxW = 960 * scaleX;
		const trophyBoxH = (1357 - 665 - 75 - 30) * scaleY;

		ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
		ctx.font = `400 ${Math.round(13 * scaleX)}px "Segoe UI", sans-serif`;
		ctx.textAlign = 'center';
		ctx.fillText('This is a community-issued self-assessment certificate and does not represent an official AWS certification.', trophyBoxX + trophyBoxW / 2, trophyBoxY + trophyBoxH - (25 * scaleY));

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

	function awsToggleCodeMemberType(id, entityType, btn) {
		btn.style.opacity = '0.5';
		btn.disabled = true;
		const data = {
			action: 'aws_sbg_toggle_code_member_type',
			nonce: '<?php echo wp_create_nonce( 'aws_exam_admin_nonce' ); ?>'
		};
		if (entityType === 'code') {
			data.code_id = id;
		} else {
			data.att_id = id;
		}
		jQuery.post(ajaxurl, data, function(response) {
			btn.style.opacity = '1';
			btn.disabled = false;
			if (response.success) {
				const isPaying = (response.data.new_val === 'paying');
				if (isPaying) {
					btn.style.borderColor = '#6ee7b7';
					btn.style.background = '#ecfdf5';
					btn.style.color = '#047857';
					btn.innerText = '💳 Paying (Prize)';
				} else {
					btn.style.borderColor = '#cbd5e1';
					btn.style.background = '#f8fafc';
					btn.style.color = '#475569';
					btn.innerText = '🎟️ General (Aid/Pro)';
				}
			} else {
				alert(response.data || 'Failed to update category');
			}
		});
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

	register_rest_route( 'aws-sbg/v1', '/exam/available-slots', array(
		'methods'             => 'GET',
		'callback'            => 'aws_sbg_rest_get_available_slots',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'aws-sbg/v1', '/exam/reserve-slot', array(
		'methods'             => 'POST',
		'callback'            => 'aws_sbg_rest_reserve_slot',
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

	$client_session_token = sanitize_text_field( $request->get_param( 'session_token' ) );
	$client_device_fp     = sanitize_text_field( wp_unslash( $request->get_param( 'device_fingerprint' ) ) );

	if ( $code_row->used_attempts >= $code_row->max_attempts ) {
		return new WP_Error( 'code_exhausted', 'This access code has reached its maximum allowed exam attempts.', array( 'status' => 403 ) );
	}

	// 🔒 SINGLE-ACTIVE-SESSION & DEVICE LOCK VALIDATION
	if ( ! empty( $code_row->active_session_token ) ) {
		$session_age_sec = ! empty( $code_row->session_started_at ) ? ( current_time( 'timestamp' ) - strtotime( $code_row->session_started_at ) ) : 0;
		// Active session lock is maintained for 3 hours
		if ( $session_age_sec < ( 3 * HOUR_IN_SECONDS ) ) {
			if ( empty( $client_session_token ) || $client_session_token !== $code_row->active_session_token ) {
				return new WP_Error(
					'session_locked',
					'⚠️ ACCESS BLOCKED: This access code is currently active on another device or browser session. Concurrent sessions and proxy test-taking are strictly prohibited.',
					array( 'status' => 403 )
				);
			}
		}
	}

	// Slot Time Window Validation
	$slot_name_resp   = '';
	$slot_window_resp = '';

	if ( ! empty( $code_row->slot_id ) ) {
		$table_slots = $wpdb->prefix . 'aws_exam_slots';
		$slot = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_slots} WHERE id = %d", $code_row->slot_id ) );

		if ( $slot && intval( $slot->is_active ) === 1 ) {
			$now_ts   = current_time( 'timestamp' );
			$start_ts = strtotime( $slot->start_time );
			$end_ts   = strtotime( $slot->end_time );
			$slot_name_resp   = $slot->slot_name;
			$slot_window_resp = date( 'g:i A', $start_ts ) . ' – ' . date( 'g:i A', $end_ts );

			// 1. Slot not started yet -> Return waiting state with countdown
			if ( $now_ts < $start_ts ) {
				$diff_sec = $start_ts - $now_ts;
				return rest_ensure_response( array(
					'success'              => false,
					'status'               => 'slot_not_started',
					'message'              => 'Your scheduled exam slot has not started yet.',
					'slot_name'            => $slot->slot_name,
					'start_time'           => $slot->start_time,
					'end_time'             => $slot->end_time,
					'start_time_formatted' => date( 'l, M j, Y — g:i A', $start_ts ),
					'end_time_formatted'   => date( 'g:i A', $end_ts ),
					'start_timestamp'      => $start_ts,
					'server_timestamp'     => $now_ts,
					'remaining_seconds'    => $diff_sec,
					'timezone'             => wp_timezone_string(),
				) );
			}

			// 2. Slot has expired -> Auto-Revoked
			if ( $now_ts > $end_ts ) {
				return new WP_Error(
					'slot_expired',
					sprintf( 'Your assigned exam slot ("%s") expired on %s (%s). This access code is no longer valid.', esc_html( $slot->slot_name ), date( 'M j, Y g:i A', $end_ts ), wp_timezone_string() ),
					array( 'status' => 403 )
				);
			}
		}
	}

	$existing_count = wp_count_posts( 'aws_question' )->publish;
	if ( $existing_count < 5 ) {
		aws_sbg_auto_seed_questions( true );
	}

	$cur_mode = get_option( 'aws_exam_operating_mode', 'live' );
	$limit    = ( $cur_mode === 'test' ) ? 10 : intval( get_option( 'aws_exam_question_limit', 50 ) );
	if ( $limit < 5 ) {
		$limit = ( $cur_mode === 'test' ) ? 10 : 50;
	}

	$questions = array();

	// 1. If TEST MODE: Load directly from seed-test-questions.json for 100% deterministic test benchmark
	if ( $cur_mode === 'test' && file_exists( get_template_directory() . '/seed-test-questions.json' ) ) {
		$test_raw = json_decode( file_get_contents( get_template_directory() . '/seed-test-questions.json' ), true );
		if ( is_array( $test_raw ) && ! empty( $test_raw ) ) {
			$q_idx = 1000;
			foreach ( $test_raw as $tq ) {
				$q_idx++;
				$correct = is_array( $tq['correct'] ) ? $tq['correct'] : array( $tq['correct'] );
				$shuffled_options = is_array( $tq['options'] ) ? $tq['options'] : array();
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
					'id'          => $q_idx,
					'question'    => $tq['question'],
					'domain'      => ! empty( $tq['domain'] ) ? ucwords( str_replace( '-', ' ', $tq['domain'] ) ) : 'Cloud Concepts',
					'options'     => $rekeyed_options,
					'is_multi'    => count( $correct ) > 1,
					'multi_count' => count( $correct ),
					'correct'     => $correct,
					'explanation' => isset( $tq['explanation'] ) ? $tq['explanation'] : '',
					'doc_link'    => isset( $tq['doc_link'] ) ? $tq['doc_link'] : '',
				);
			}
		}
	}

	// 2. LIVE MODE: Load from seed-live-questions.json for instant, zero-DB-load delivery
	if ( empty( $questions ) && file_exists( get_template_directory() . '/seed-live-questions.json' ) ) {
		$live_raw = json_decode( file_get_contents( get_template_directory() . '/seed-live-questions.json' ), true );
		if ( is_array( $live_raw ) && ! empty( $live_raw ) ) {
			$track = strtolower( trim( $code_row->exam_track ) );
			$filtered = array();
			foreach ( $live_raw as $lq ) {
				$q_track = isset( $lq['exam_track'] ) ? strtolower( trim( $lq['exam_track'] ) ) : 'clf-c02';
				if ( $q_track === $track || $track === 'clf-c02' ) {
					$filtered[] = $lq;
				}
			}
			if ( empty( $filtered ) ) {
				$filtered = $live_raw;
			}
			shuffle( $filtered );
			$selected = array_slice( $filtered, 0, $limit );

			$q_idx = 2000;
			foreach ( $selected as $tq ) {
				$q_idx++;
				$correct = is_array( $tq['correct'] ) ? $tq['correct'] : array( $tq['correct'] );
				$shuffled_options = is_array( $tq['options'] ) ? $tq['options'] : array();
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
					'id'          => $q_idx,
					'question'    => $tq['question'],
					'domain'      => ! empty( $tq['domain'] ) ? ucwords( str_replace( '-', ' ', $tq['domain'] ) ) : 'Cloud Concepts',
					'options'     => $rekeyed_options,
					'is_multi'    => count( $correct ) > 1,
					'multi_count' => count( $correct ),
					'correct'     => $correct,
					'explanation' => isset( $tq['explanation'] ) ? $tq['explanation'] : '',
					'doc_link'    => isset( $tq['doc_link'] ) ? $tq['doc_link'] : '',
				);
			}
		}
	}

	// 3. Fallback: Query from published DB Question Bank if seed file missing
	if ( empty( $questions ) ) {
		$track = strtolower( trim( $code_row->exam_track ) );

		$args  = array(
			'post_type'      => 'aws_question',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => ( $cur_mode === 'test' ) ? 'ID' : 'rand',
			'order'          => 'ASC',
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
			$q_query = new WP_Query( array(
				'post_type'      => 'aws_question',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => ( $cur_mode === 'test' ) ? 'ID' : 'rand',
				'order'          => 'ASC',
			) );
		}

		if ( $q_query->have_posts() ) {
			foreach ( $q_query->posts as $post_obj ) {
				$q_id        = $post_obj->ID;
				$q_title     = $post_obj->post_title;
				$q_content   = ! empty( $post_obj->post_content ) ? $post_obj->post_content : $q_title;
				$options     = get_post_meta( $q_id, '_aws_options', true );
				$correct     = get_post_meta( $q_id, '_aws_correct', true );
				$domains     = wp_get_post_terms( $q_id, 'exam_domain', array( 'fields' => 'names' ) );
				$domain_name = ! empty( $domains ) ? $domains[0] : 'General Cloud Knowledge';
				$explanation = get_post_meta( $q_id, '_aws_explanation', true );
				$ref_url     = get_post_meta( $q_id, '_aws_reference_url', true );

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
					'correct'     => is_array( $correct ) ? $correct : array( $correct ),
					'explanation' => $explanation,
					'doc_link'    => $ref_url,
				);
			}
		}
	}

	$session_token = ( ! empty( $code_row->active_session_token ) && ! empty( $client_session_token ) && $client_session_token === $code_row->active_session_token ) 
		? $code_row->active_session_token 
		: wp_generate_password( 24, false );

	$wpdb->update(
		$table_codes,
		array(
			'active_session_token' => $session_token,
			'device_fingerprint'   => $client_device_fp,
			'session_started_at'   => current_time( 'mysql' ),
		),
		array( 'id' => $code_row->id )
	);

	set_transient( 'aws_exam_session_' . $session_token, array(
		'access_code_id' => $code_row->id,
		'slot_id'        => ! empty( $code_row->slot_id ) ? intval( $code_row->slot_id ) : 0,
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
		'exam_mode'             => $cur_mode,
		'slot_name'             => $slot_name_resp,
		'slot_window'           => $slot_window_resp,
		'track_title'           => 'AWS Certification Preparation Exam',
		'total_questions'       => count( $questions ),
		'duration_minutes'      => ( $cur_mode === 'test' ) ? 15 : 90,
		'per_question_seconds'  => ( isset($code_row->per_question_seconds) && intval($code_row->per_question_seconds) > 0 && intval($code_row->per_question_seconds) !== 60 && intval($code_row->per_question_seconds) !== 45 && intval($code_row->per_question_seconds) !== 35 && intval($code_row->per_question_seconds) !== 30 ) ? intval($code_row->per_question_seconds) : 40,
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
		$correct_answers = ( isset( $q['correct'] ) && is_array( $q['correct'] ) ) ? $q['correct'] : get_post_meta( $q_id, '_aws_correct', true );
		$explanation     = isset( $q['explanation'] ) ? $q['explanation'] : get_post_meta( $q_id, '_aws_explanation', true );
		$ref_url         = isset( $q['doc_link'] ) ? $q['doc_link'] : get_post_meta( $q_id, '_aws_reference_url', true );
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

		$user_selected_texts = array();
		$correct_answers_texts = array();
		if ( isset( $q['options'] ) && is_array( $q['options'] ) ) {
			foreach ( $q['options'] as $opt ) {
				$opt_orig = isset( $opt['original_key'] ) ? $opt['original_key'] : $opt['key'];
				$opt_display = isset( $opt['key'] ) ? $opt['key'] : $opt['original_key'];
				if ( in_array( $opt_display, $user_selected_display, true ) || in_array( $opt_orig, $user_selected_original, true ) ) {
					$user_selected_texts[] = $opt['text'];
				}
				if ( in_array( $opt_orig, $correct_answers, true ) ) {
					$correct_answers_texts[] = $opt['text'];
				}
			}
		}

		$item_results[] = array(
			'question_id'           => $q_id,
			'question'              => $q['question'],
			'domain'                => $domain,
			'user_selected'         => $user_selected_display,
			'user_selected_texts'   => $user_selected_texts,
			'correct_answers'       => $correct_answers,
			'correct_answers_texts' => $correct_answers_texts,
			'is_correct'            => $is_correct,
			'explanation'           => $explanation,
			'ref_url'               => $ref_url,
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

	$time_spent_ms  = isset( $request['time_spent_ms'] ) ? intval( $request['time_spent_ms'] ) : 0;
	$time_spent_sec = isset( $request['time_spent_seconds'] ) ? floatval( $request['time_spent_seconds'] ) : 0.0;
	if ( empty( $time_spent_ms ) && $time_spent_sec > 0 ) {
		$time_spent_ms = round( $time_spent_sec * 1000 );
	}
	if ( empty( $time_spent_sec ) && $time_spent_ms > 0 ) {
		$time_spent_sec = $time_spent_ms / 1000;
	}

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$code_row = $wpdb->get_row( $wpdb->prepare( "SELECT member_type FROM {$table_codes} WHERE id = %d", $session_data['access_code_id'] ) );
	$member_type = ( $code_row && ! empty( $code_row->member_type ) ) ? $code_row->member_type : 'paying';

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	$wpdb->insert(
		$table_attempts,
		array(
			'access_code_id'      => $session_data['access_code_id'],
			'slot_id'             => ! empty( $session_data['slot_id'] ) ? intval( $session_data['slot_id'] ) : null,
			'member_name'         => $session_data['member_name'],
			'member_email'        => $session_data['member_email'],
			'member_type'         => $member_type,
			'exam_track'          => $session_data['exam_track'],
			'score'               => $scaled_score,
			'passed'              => $passed,
			'time_spent_seconds'  => $time_spent_sec,
			'time_spent_ms'       => $time_spent_ms,
			'tab_switch_count'    => $tab_switches,
			'violation_submitted' => $is_violation ? 1 : 0,
			'device_fingerprint'  => $device_fp,
			'domain_scores'       => wp_json_encode( $domain_breakdown ),
			'attempt_details'     => wp_json_encode( $item_results ),
		),
		array( '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%d', '%s', '%s', '%s' )
	);

	$table_codes = $wpdb->prefix . 'aws_access_codes';
	$wpdb->query( $wpdb->prepare( "UPDATE {$table_codes} SET used_attempts = used_attempts + 1, active_session_token = NULL, device_fingerprint = NULL, session_started_at = NULL WHERE id = %d", $session_data['access_code_id'] ) );

	delete_transient( 'aws_exam_session_' . $token );

	$t_min = floor( $time_spent_sec / 60 );
	$t_sec = fmod( $time_spent_sec, 60 );
	$time_spent_formatted = sprintf( '%02dm %06.3fs', $t_min, $t_sec );

	return rest_ensure_response( array(
		'success'             => true,
		'member_name'         => $session_data['member_name'],
		'exam_track'          => $session_data['exam_track'],
		'score'               => $scaled_score,
		'passed'              => (bool) $passed,
		'correct_count'       => $correct_count,
		'total_questions'     => $total_count,
		'percentage'          => round( $percentage * 100, 1 ),
		'time_spent_seconds'  => $time_spent_sec,
		'time_spent_ms'       => $time_spent_ms,
		'time_formatted'      => $time_spent_formatted,
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
	$track  = sanitize_text_field( $request->get_param( 'track' ) );
	$filter = sanitize_text_field( $request->get_param( 'filter' ) );
	if ( empty( $track ) ) $track = 'clf-c02';

	$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
	
	$where_extra = "";
	if ( $filter === 'general' ) {
		$where_extra = " AND member_type = 'general' ";
	} elseif ( $filter === 'all' ) {
		$where_extra = "";
	} else {
		// Default: Paying members only (Top Scorer Prize Leaderboard)
		$where_extra = " AND (member_type = 'paying' OR member_type IS NULL OR member_type = '') ";
	}

	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT member_name, exam_track, score, passed, time_spent_seconds, time_spent_ms, created_at, member_type 
		 FROM {$table_attempts} 
		 WHERE exam_track = %s AND violation_submitted = 0 {$where_extra}
		 ORDER BY score DESC, time_spent_ms ASC, time_spent_seconds ASC, id DESC 
		 LIMIT 50",
		$track
	) );

	$leaderboard = array();
	$rank = 1;

	foreach ( $results as $row ) {
		$m_type = ! empty( $row->member_type ) ? $row->member_type : 'paying';
		$tot_sec = floatval( $row->time_spent_seconds );
		if ( empty( $tot_sec ) && ! empty( $row->time_spent_ms ) ) {
			$tot_sec = $row->time_spent_ms / 1000;
		}
		$m = floor( $tot_sec / 60 );
		$s_frac = fmod( $tot_sec, 60 );
		$time_formatted = sprintf( '%02dm %06.3fs', $m, $s_frac );

		$leaderboard[] = array(
			'rank'               => $rank++,
			'member_name'        => $row->member_name,
			'member_type'        => $m_type,
			'is_paying'          => ( $m_type === 'paying' ),
			'score'              => intval( $row->score ),
			'passed'             => (bool) $row->passed,
			'time_spent_seconds' => $tot_sec,
			'time_spent_ms'      => intval( $row->time_spent_ms ),
			'time_formatted'     => $time_formatted,
			'date'               => date( 'M j, Y', strtotime( $row->created_at ) ),
		);
	}

	return rest_ensure_response( array(
		'success'     => true,
		'exam_track'  => $track,
		'filter'      => $filter ?: 'paying',
		'leaderboard' => $leaderboard,
	) );
}

/**
 * REST Callback: Get Available Exam Slots for Public Self-Service
 */
function aws_sbg_rest_get_available_slots( WP_REST_Request $request ) {
	global $wpdb;
	$table_slots = $wpdb->prefix . 'aws_exam_slots';
	$table_codes = $wpdb->prefix . 'aws_access_codes';

	$is_public_enabled = get_option( 'aws_exam_public_reservations_enabled', '1' ) === '1';

	if ( ! $is_public_enabled ) {
		return rest_ensure_response( array(
			'success'        => true,
			'public_enabled' => false,
			'slots'          => array(),
		) );
	}

	$slots_raw = $wpdb->get_results(
		"SELECT * FROM {$table_slots} WHERE is_active = 1 ORDER BY start_time ASC"
	);

	$now_ts     = current_time( 'timestamp' );
	$slots_data = array();
	if ( ! empty( $slots_raw ) ) {
		foreach ( $slots_raw as $s ) {
			$assigned_count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_codes} WHERE slot_id = %d",
				$s->id
			) );

			$max_cap   = (int) $s->max_capacity;
			$is_full   = ( $max_cap > 0 && $assigned_count >= $max_cap );
			$s_start   = strtotime( $s->start_time );
			$s_end     = strtotime( $s->end_time );
			$is_ended  = ( $s_end < $now_ts );
			$is_active = ( ! $is_full && ! $is_ended );

			$slots_data[] = array(
				'id'             => (int) $s->id,
				'slot_name'      => $s->slot_name,
				'start_time'     => $s->start_time,
				'end_time'       => $s->end_time,
				'date_formatted' => date( 'l, M j, Y', $s_start ),
				'time_window'    => date( 'g:i A', $s_start ) . ' – ' . date( 'g:i A', $s_end ),
				'is_full'        => $is_full,
				'is_ended'       => $is_ended,
				'is_bookable'    => $is_active,
			);
		}
	}

	return rest_ensure_response( array(
		'success'        => true,
		'public_enabled' => true,
		'slots'          => $slots_data,
	) );
}

/**
 * REST Callback: Self-Service Reserve Slot & Auto Generate Access Code
 */
function aws_sbg_rest_reserve_slot( WP_REST_Request $request ) {
	global $wpdb;
	$table_slots = $wpdb->prefix . 'aws_exam_slots';
	$table_codes = $wpdb->prefix . 'aws_access_codes';

	$is_public_enabled = get_option( 'aws_exam_public_reservations_enabled', '1' ) === '1';
	if ( ! $is_public_enabled ) {
		return new WP_Error( 'reservations_closed', 'Public slot reservations are currently closed.', array( 'status' => 403 ) );
	}

	$slot_id = intval( $request->get_param( 'slot_id' ) );
	$name    = sanitize_text_field( $request->get_param( 'name' ) );
	$email   = sanitize_email( $request->get_param( 'email' ) );

	if ( empty( $slot_id ) || empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
		return new WP_Error( 'invalid_data', 'Please provide a valid name, email address, and select an exam slot.', array( 'status' => 400 ) );
	}

	// Enforce Event Attendee Eligibility Verification Gate (if enabled)
	$require_event_reg = get_option( 'aws_exam_require_event_registration', '1' ) === '1';
	$linked_event_id   = intval( get_option( 'aws_exam_linked_event_id', 0 ) );

	if ( $require_event_reg ) {
		$meta_query = array(
			array(
				'key'     => 'email',
				'value'   => $email,
				'compare' => '=',
			),
		);

		if ( $linked_event_id > 0 ) {
			$meta_query[] = array(
				'key'     => 'event_id',
				'value'   => $linked_event_id,
				'compare' => '=',
			);
		}

		$attendee_reg = get_posts( array(
			'post_type'               => 'aws_event_reg',
			'post_status'             => 'publish',
			'posts_per_page'          => 1,
			'fields'                  => 'ids',
			'no_found_rows'           => true,
			'update_post_meta_cache'  => false,
			'update_post_term_cache'  => false,
			'meta_query'              => $meta_query,
		) );

		if ( empty( $attendee_reg ) ) {
			$event_name_str = 'the official event';
			if ( $linked_event_id > 0 ) {
				$ev_post = get_post( $linked_event_id );
				if ( $ev_post ) {
					$event_name_str = '"' . $ev_post->post_title . '"';
				}
			}
			return new WP_Error(
				'not_registered',
				"Your email ({$email}) was not found in the verified attendee roster for {$event_name_str}. Only registered participants can reserve an exam slot.",
				array( 'status' => 403 )
			);
		}
	}

	// Verify Slot Existence & Status
	$slot_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_slots} WHERE id = %d AND is_active = 1", $slot_id ) );
	if ( ! $slot_row ) {
		return new WP_Error( 'invalid_slot', 'The selected exam slot does not exist or has ended.', array( 'status' => 404 ) );
	}

	// Check if Slot is Expired
	if ( strtotime( $slot_row->end_time ) < current_time( 'timestamp' ) ) {
		return new WP_Error( 'slot_concluded', 'This exam time slot is fully booked. Please choose an upcoming available slot.', array( 'status' => 400 ) );
	}

	// Check Seat Capacity Limit
	$assigned_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_codes} WHERE slot_id = %d", $slot_id ) );
	$max_cap = (int) $slot_row->max_capacity;

	if ( $max_cap > 0 && $assigned_count >= $max_cap ) {
		return new WP_Error( 'slot_full', 'This exam time slot is full (0 seats left). Please select another slot.', array( 'status' => 400 ) );
	}

	// Check if this candidate already has an active access code for ANY slot (prevent duplicate slot booking)
	$existing_code = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table_codes} WHERE LOWER(member_email) = LOWER(%s) ORDER BY id DESC LIMIT 1",
		$email
	) );

	if ( $existing_code ) {
		$assigned_slot_name = 'Assigned Slot';
		$s_date_str = 'Scheduled Window';
		$s_time_str = 'Live';

		if ( ! empty( $existing_code->slot_id ) ) {
			$assigned_slot = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_slots} WHERE id = %d", $existing_code->slot_id ) );
			if ( $assigned_slot ) {
				$assigned_slot_name = $assigned_slot->slot_name;
				$s_start            = strtotime( $assigned_slot->start_time );
				$s_end              = strtotime( $assigned_slot->end_time );
				$s_date_str         = date( 'l, M j, Y', $s_start );
				$s_time_str         = date( 'g:i A', $s_start ) . ' – ' . date( 'g:i A', $s_end );
			}
		}

		$is_same_slot = ( intval( $existing_code->slot_id ) === $slot_id );

		if ( ! $is_same_slot ) {
			// Re-dispatch email for their existing booked slot
			aws_sbg_send_member_exam_email( $existing_code->id );

			return new WP_Error(
				'already_booked_other_slot',
				"You have already reserved a seat in {$assigned_slot_name} ({$s_date_str} • {$s_time_str}). Candidates cannot book multiple slots. Your access code has been resent to your email.",
				array( 'status' => 400 )
			);
		}

		// Same slot: re-send confirmation email and return code retrieval response
		aws_sbg_send_member_exam_email( $existing_code->id );

		return rest_ensure_response( array(
			'success'        => true,
			'is_existing'    => true,
			'access_code'    => $existing_code->access_code,
			'member_name'    => $existing_code->member_name,
			'member_email'   => $existing_code->member_email,
			'slot_name'      => $assigned_slot_name,
			'date_formatted' => $s_date_str,
			'time_window'    => $s_time_str,
			'timezone'       => wp_timezone_string(),
			'portal_url'     => home_url( '/exam-portal/' ),
			'message'        => 'You were already registered for this slot! We have retrieved your access code.',
		) );
	}

	// Generate Unique Access Code
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$access_code = '';
	do {
		$random_str = '';
		for ( $c = 0; $c < 5; $c++ ) {
			$random_str .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		$access_code = 'AWS-' . $random_str;
		$dup_check   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_codes} WHERE access_code = %s", $access_code ) );
	} while ( ! empty( $dup_check ) );

	// Determine member_type from attendee registration
	$member_type = 'paying';
	if ( ! empty( $attendee_reg ) ) {
		$reg_meta_type = get_post_meta( $attendee_reg[0], 'member_type', true );
		if ( ! empty( $reg_meta_type ) ) {
			$member_type = $reg_meta_type;
		} else {
			$tier = get_post_meta( $attendee_reg[0], 'ticket_tier', true );
			$member_type = ( $tier === 'free' ) ? 'general' : 'paying';
		}
	}

	$inserted = $wpdb->insert(
		$table_codes,
		array(
			'access_code'          => $access_code,
			'member_name'          => $name,
			'member_email'         => $email,
			'member_type'          => $member_type,
			'exam_track'           => ! empty( $slot_row->exam_track ) ? $slot_row->exam_track : 'all',
			'slot_id'              => $slot_id,
			'max_attempts'         => 1,
			'used_attempts'        => 0,
			'per_question_seconds' => 40,
			'allow_backtrack'      => 0,
			'created_at'           => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' )
	);

	if ( false === $inserted ) {
		return new WP_Error( 'db_error', 'Failed to generate access code. Please try again.', array( 'status' => 500 ) );
	}

	$new_code_id = $wpdb->insert_id;

	// Automatically dispatch email invitation with slot schedule
	aws_sbg_send_member_exam_email( $new_code_id );

	$s_start = strtotime( $slot_row->start_time );
	$s_end   = strtotime( $slot_row->end_time );

	return rest_ensure_response( array(
		'success'        => true,
		'access_code'    => $access_code,
		'member_name'    => $name,
		'member_email'   => $email,
		'slot_name'      => $slot_row->slot_name,
		'date_formatted' => date( 'l, M j, Y', $s_start ),
		'time_window'    => date( 'g:i A', $s_start ) . ' – ' . date( 'g:i A', $s_end ),
		'timezone'       => wp_timezone_string(),
		'portal_url'     => home_url( '/exam-portal/' ),
	) );
}

/**
 * Template fallback and shortcode for /reserve-slot/
 */
function aws_sbg_reserve_slot_template( $template ) {
	if ( is_page( 'reserve-slot' ) || ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], '/reserve-slot' ) ) ) {
		$theme_file = get_template_directory() . '/page-reserve-slot.php';
		if ( file_exists( $theme_file ) ) {
			return $theme_file;
		}
	}
	return $template;
}
add_filter( 'template_include', 'aws_sbg_reserve_slot_template', 99 );

function aws_sbg_reserve_slot_shortcode() {
	ob_start();
	include get_template_directory() . '/page-reserve-slot.php';
	return ob_get_clean();
}
add_shortcode( 'aws_exam_slot_reservation', 'aws_sbg_reserve_slot_shortcode' );

