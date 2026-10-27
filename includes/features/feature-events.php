<?php
/**
 * AWS SBG Theme - Events & Self-Service Certification System
 * File: feature-events.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. DATABASE TABLES INITIALIZATION
 */
function aws_sbg_events_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	// Table 1: Events (Universal Modular Certificate Architecture)
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$sql_events   = "CREATE TABLE $table_events (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		title varchar(255) NOT NULL,
		track varchar(100) NOT NULL DEFAULT 'CLF-C02',
		event_date date NOT NULL,
		survey_url text NOT NULL,
		referral_url text NOT NULL,
		cert_top_heading varchar(255) NOT NULL DEFAULT '★ CERTIFICATE OF ACHIEVEMENT ★',
		cert_main_heading varchar(255) NOT NULL DEFAULT 'Certificate',
		cert_sub_heading varchar(255) NOT NULL DEFAULT 'PASSED — AWS Certification Self-Assessment',
		cert_purple_footer varchar(255) NOT NULL DEFAULT 'Organized by AWS Student Builder Group at Air University Islamabad',
		cert_description text NOT NULL,
		status varchar(50) NOT NULL DEFAULT 'active',
		created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	// Table 2: Issued Certificates Log
	$table_certs = $wpdb->prefix . 'aws_sbg_event_issued_certs';
	$sql_certs   = "CREATE TABLE $table_certs (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		event_id bigint(20) NOT NULL,
		participant_name varchar(255) NOT NULL,
		participant_email varchar(255) NOT NULL,
		student_id varchar(100) DEFAULT '' NOT NULL,
		builder_profile_url varchar(255) DEFAULT '' NOT NULL,
		is_new_builder tinyint(1) DEFAULT 0 NOT NULL,
		verification_code varchar(100) NOT NULL,
		survey_completed tinyint(1) DEFAULT 1 NOT NULL,
		referral_completed tinyint(1) DEFAULT 1 NOT NULL,
		survey_screenshot longtext DEFAULT '' NOT NULL,
		issued_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id),
		KEY event_id (event_id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql_events );
	dbDelta( $sql_certs );

	// Auto-migrate old survey URLs if table exists
	$wpdb->query( "UPDATE $table_events SET survey_url = 'https://pulse.amazon/survey/UPCU4UQO' WHERE survey_url LIKE '%pulse.aws%'" );
}
add_action( 'after_switch_theme', 'aws_sbg_events_init_db' );
add_action( 'admin_init', 'aws_sbg_events_init_db' );

/**
 * 2. ADMIN MENU FOR EVENT MANAGEMENT & CERTIFIED MEMBERS
 */
// Admin pages are registered in admin-dashboard.php to keep the suite menu organized.

/**
 * 3. ADMIN PAGE RENDERER & ACTIONS
 */
function aws_sbg_render_events_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$table_certs  = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	$message = '';
	$action  = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';

	// Handle CSV Export
	if ( isset( $_GET['export_event_id'] ) ) {
		$event_id = intval( $_GET['export_event_id'] );
		aws_sbg_export_certs_csv( $event_id );
		exit;
	}

	// Handle Admin Actions
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['aws_events_nonce'] ) && wp_verify_nonce( $_POST['aws_events_nonce'], 'aws_events_admin_action' ) ) {
		if ( $action === 'create_event' ) {
			$title              = sanitize_text_field( $_POST['title'] );
			$track              = sanitize_text_field( $_POST['track'] );
			$event_date         = sanitize_text_field( $_POST['event_date'] );
			$survey_url         = esc_url_raw( $_POST['survey_url'] );
			$referral_url       = esc_url_raw( $_POST['referral_url'] );
			$cert_top_heading   = sanitize_text_field( $_POST['cert_top_heading'] ?? '★ CERTIFICATE OF ACHIEVEMENT ★' );
			$cert_main_heading  = sanitize_text_field( $_POST['cert_main_heading'] ?? 'Certificate' );
			$cert_sub_heading   = sanitize_text_field( $_POST['cert_sub_heading'] ?? 'PASSED — AWS Certification Self-Assessment' );
			$cert_purple_footer = sanitize_text_field( $_POST['cert_purple_footer'] ?? 'Organized by AWS Student Builder Group at Air University Islamabad' );
			$cert_description   = sanitize_text_field( $_POST['cert_description'] ?? 'For successfully completing the AWS Certification Self-Assessment' );

			$wpdb->insert(
				$table_events,
				array(
					'title'              => $title,
					'track'              => $track,
					'event_date'         => $event_date,
					'survey_url'         => $survey_url,
					'referral_url'       => $referral_url,
					'cert_top_heading'   => $cert_top_heading,
					'cert_main_heading'  => $cert_main_heading,
					'cert_sub_heading'   => $cert_sub_heading,
					'cert_purple_footer' => $cert_purple_footer,
					'cert_description'   => $cert_description,
					'status'             => 'active',
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			$event_id = $wpdb->insert_id;
			$message  = "✅ Event created successfully! Participants can now claim certificates with Event ID: <strong>#{$event_id}</strong>";
		}
	}
	
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['aws_events_nonce'] ) && wp_verify_nonce( $_POST['aws_events_nonce'], 'aws_events_admin_action' ) ) {
		if ( $action === 'toggle_status' ) {
			$event_id   = intval( $_POST['event_id'] );
			$cur_status = sanitize_text_field( $_POST['current_status'] );
			$new_status = ( $cur_status === 'active' ) ? 'closed' : 'active';
			$wpdb->update( $table_events, array( 'status' => $new_status ), array( 'id' => $event_id ) );
			$message = 'Event status updated to ' . strtoupper( $new_status ) . '!';
		} elseif ( $action === 'delete_event' ) {
			$event_id = intval( $_POST['event_id'] );
			$wpdb->delete( $table_events, array( 'id' => $event_id ) );
			$wpdb->delete( $table_certs, array( 'event_id' => $event_id ) );
			$message = 'Event and associated certificate logs deleted.';
		}
	}

	$events = $wpdb->get_results( "SELECT * FROM $table_events ORDER BY id DESC" );
	aws_sbg_render_admin_nav_tabs( 'events' );
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		<h1 style="display:flex; align-items:center; gap:10px; font-weight:700;">
			<span class="dashicons dashicons-groups" style="font-size:32px; width:32px; height:32px; color:#ad5cff;"></span>
			AWS SBG Event & Self-Service Certificate Management
		</h1>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid; grid-template-columns: 420px 1fr; gap:25px; margin-top:20px;">
			
			<!-- CREATE EVENT FORM -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); height:fit-content;">
				<h3 style="margin-top:0; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">➕ Create New Session / Event</h3>
				
				<!-- PRESET QUICK-FILL BUTTONS -->
				<div style="margin-bottom:15px; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
					<span style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">⚡ Quick-Fill Wording Presets:</span>
					<div style="display:flex; flex-wrap:wrap; gap:5px;">
						<button type="button" onclick="applyCertPreset('exam')" class="button button-small" style="font-size:10px;">🎓 Exam Prep</button>
						<button type="button" onclick="applyCertPreset('session')" class="button button-small" style="font-size:10px;">🎤 Session</button>
						<button type="button" onclick="applyCertPreset('event')" class="button button-small" style="font-size:10px;">🤝 Flagship Event</button>
						<button type="button" onclick="applyCertPreset('bootcamp')" class="button button-small" style="font-size:10px;">🚀 Bootcamp</button>
					</div>
				</div>

				<form method="post" id="aws-create-event-form">
					<?php wp_nonce_field( 'aws_events_admin_action', 'aws_events_nonce' ); ?>
					<input type="hidden" name="action" value="create_event">

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">Event / Session Title</label>
						<input type="text" id="ev_title" name="title" required placeholder="Session #02: AWS Cloud Security Deep Dive" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">1. Top Eyebrow Heading</label>
						<input type="text" id="cert_top_heading" name="cert_top_heading" required value="★ CERTIFICATE OF ACHIEVEMENT ★" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">2. Main Heading</label>
						<input type="text" id="cert_main_heading" name="cert_main_heading" required value="Certificate" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">3. Sub-Heading (Purple Section)</label>
						<input type="text" id="cert_sub_heading" name="cert_sub_heading" required value="PASSED — AWS Certification Self-Assessment" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">4. Purple Section Footer Text</label>
						<input type="text" id="cert_purple_footer" name="cert_purple_footer" required value="Organized by AWS Student Builder Group at Air University Islamabad" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">5. Certificate Description Text (Dark Section)</label>
						<textarea id="cert_description" name="cert_description" required rows="2" class="regular-text" style="width:100%; font-size:12px;">For successfully completing the AWS Certification Self-Assessment</textarea>
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">Event Date</label>
						<input type="date" name="event_date" required value="<?php echo date( 'Y-m-d' ); ?>" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">Official AWS Survey URL</label>
						<input type="url" name="survey_url" required value="https://pulse.amazon/survey/UPCU4UQO" class="regular-text" style="width:100%;">
					</p>

					<p>
						<label style="font-weight:600; font-size:12px; display:block; margin-bottom:4px;">AWS Builder Center Referral URL</label>
						<input type="url" name="referral_url" required value="https://builder.aws.com/start?trk=8e356433-7e2c-4ee9-8c3a-5bccc682a4f5&sc_channel=el" class="regular-text" style="width:100%;">
					</p>

					<button type="submit" class="button button-primary" style="background:#ad5cff; border-color:#8b31ff; width:100%; font-weight:700; padding:6px; height:auto; margin-top:10px;">🚀 Publish Event & Activate Certificates</button>
				</form>
			</div>

			<script>
			function applyCertPreset(type) {
				const top = document.getElementById('cert_top_heading');
				const main = document.getElementById('cert_main_heading');
				const sub = document.getElementById('cert_sub_heading');
				const foot = document.getElementById('cert_purple_footer');
				const desc = document.getElementById('cert_description');
				const title = document.getElementById('ev_title').value || 'AWS Event';

				if (type === 'exam') {
					top.value = '★ AWS CERTIFICATION SELF-ASSESSMENT 2026 ★';
					main.value = 'Certificate';
					sub.value = 'PASSED — AWS Certification Self-Assessment';
					foot.value = 'Organized by AWS Student Builder Group at Air University Islamabad';
					desc.value = 'For successfully completing the AWS Certification Self-Assessment';
				} else if (type === 'session') {
					top.value = '★ CERTIFICATE OF PARTICIPATION ★';
					main.value = 'Certificate';
					sub.value = 'ATTENDED - ' + title;
					foot.value = 'Organized by AWS Student Builder Group at Air University Islamabad';
					desc.value = 'For actively participating in the community session ' + title;
				} else if (type === 'event') {
					top.value = '★ CERTIFICATE OF ATTENDANCE & PARTICIPATION ★';
					main.value = 'Certificate';
					sub.value = 'PARTICIPATED IN - ' + title;
					foot.value = 'Organized by AWS Student Builder Group at Air University Islamabad';
					desc.value = 'For active participation, hands-on activities, and networking at ' + title;
				} else if (type === 'bootcamp') {
					top.value = '★ CERTIFICATE OF COMPLETION ★';
					main.value = 'Certificate';
					sub.value = 'COMPLETED - ' + title;
					foot.value = 'Organized by AWS Student Builder Group at Air University Islamabad';
					desc.value = 'For successful completion of the intensive curriculum and hands-on project for ' + title;
				}
			}
			</script>

			<!-- EVENTS LIST & CERTIFICATE AUDIT LOGS -->
			<div>
				<h3 style="margin-top:0; font-weight:700; color:#0f172a;">Active & Past Sessions</h3>

				<?php if ( empty( $events ) ) : ?>
					<div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">No events created yet. Use the form on the left to create your first event!</div>
				<?php else : ?>
					<div style="display:flex; flex-direction:column; gap:15px;">
						<?php foreach ( $events as $ev ) : ?>
							<?php
							$cert_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_certs WHERE event_id = %d", $ev->id ) );
							$is_active  = ( $ev->status === 'active' );
							?>
							<div style="background:#ffffff; border:1px solid #e2e8f0; border-left:4px solid <?php echo $is_active ? '#00ef88' : '#64748b'; ?>; border-radius:12px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
								<div style="display:flex; justify-content:space-between; align-items:flex-start;">
									<div>
										<span style="font-size:10px; font-weight:800; text-transform:uppercase; padding:3px 8px; border-radius:20px; background:<?php echo $is_active ? '#dcfce7; color:#15803d;' : '#f1f5f9; color:#64748b;'; ?>">
											<?php echo $is_active ? '● Active Session' : 'Closed'; ?>
										</span>
										<h4 style="margin:8px 0 4px; font-size:18px; font-weight:800; color:#0f172a;"><?php echo esc_html( $ev->title ); ?></h4>
										<div style="font-size:12px; color:#64748b; display:flex; gap:15px; margin-bottom:10px;">
											<span>📅 <?php echo esc_html( $ev->event_date ); ?></span>
											<span>🏷️ <?php echo esc_html( $ev->cert_sub_heading ); ?></span>
											<span>📜 <strong><?php echo intval( $cert_count ); ?></strong> Certificates Self-Generated</span>
										</div>
									</div>

									<div style="display:flex; gap:8px;">
										<form method="post" style="display:inline;">
											<?php wp_nonce_field( 'aws_events_admin_action', 'aws_events_nonce' ); ?>
											<input type="hidden" name="action" value="toggle_status">
											<input type="hidden" name="event_id" value="<?php echo $ev->id; ?>">
											<input type="hidden" name="current_status" value="<?php echo esc_attr( $ev->status ); ?>">
											<button type="submit" class="button button-secondary" style="font-size:11px;"><?php echo $is_active ? 'Close Event' : 'Reactivate'; ?></button>
										</form>

										<a href="?page=aws-sbg-events&export_event_id=<?php echo $ev->id; ?>" class="button button-secondary" style="font-size:11px; font-weight:700; color:#0284c7;">📥 Export CSV</a>

										<form method="post" style="display:inline;" onsubmit="return confirm('Delete event and all its certificate logs?');">
											<?php wp_nonce_field( 'aws_events_admin_action', 'aws_events_nonce' ); ?>
											<input type="hidden" name="action" value="delete_event">
											<input type="hidden" name="event_id" value="<?php echo $ev->id; ?>">
											<button type="submit" class="button button-link-delete" style="font-size:11px;">Delete</button>
										</form>
									</div>
								</div>

								<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px; font-size:11px; font-family:monospace; color:#475569; margin-top:8px;">
									<div>📋 <strong>Survey URL:</strong> <a href="<?php echo esc_url( $ev->survey_url ); ?>" target="_blank"><?php echo esc_html( $ev->survey_url ); ?></a></div>
									<div>🚀 <strong>Referral URL:</strong> <a href="<?php echo esc_url( $ev->referral_url ); ?>" target="_blank"><?php echo esc_html( $ev->referral_url ); ?></a></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
	<?php
}

/**
 * 4. CSV EXPORT FOR EVENT CERTIFICATES
 */
function aws_sbg_export_certs_csv( $event_id ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$table_certs  = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_events WHERE id = %d", $event_id ) );
	if ( ! $event ) {
		return;
	}

	$certs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_certs WHERE event_id = %d ORDER BY id DESC", $event_id ) );

	$filename = 'AWS-SBG-Certificates-' . sanitize_title( $event->title ) . '-' . date( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Certificate ID', 'Participant Name', 'Email', 'Student ID / Department', 'Verification Code', 'Issued Date' ) );

	foreach ( $certs as $c ) {
		fputcsv(
			$output,
			array(
				$c->id,
				$c->participant_name,
				$c->participant_email,
				$c->student_id,
				$c->verification_code,
				$c->issued_at,
			)
		);
	}
	fclose( $output );
}

/**
 * 5. REST API ENDPOINTS FOR FRONTEND CERTIFICATE GENERATOR
 */
function aws_sbg_register_events_rest_routes() {
	register_rest_route(
		'aws-sbg/v1',
		'/events/active',
		array(
			'methods'             => 'GET',
			'callback'            => 'aws_sbg_rest_get_active_events',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'aws-sbg/v1',
		'/events/check-email',
		array(
			'methods'             => 'POST',
			'callback'            => 'aws_sbg_rest_check_email',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'aws-sbg/v1',
		'/events/generate-certificate',
		array(
			'methods'             => 'POST',
			'callback'            => 'aws_sbg_rest_issue_certificate',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'aws_sbg_register_events_rest_routes' );

function aws_sbg_rest_check_email( $request ) {
	global $wpdb;
	$table_certs = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	$params = $request->get_json_params();
	$email  = sanitize_email( $params['email'] ?? '' );

	if ( empty( $email ) ) {
		return rest_ensure_response( array( 'has_builder_account' => false ) );
	}

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_certs WHERE participant_email = %s", $email ) );

	return rest_ensure_response(
		array(
			'has_builder_account' => ( intval( $count ) > 0 ),
		)
	);
}

function aws_sbg_rest_get_active_events() {
	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';

	// Auto-ensure DB tables exist
	aws_sbg_events_init_db();

	$events = $wpdb->get_results( "SELECT id, title, track, event_date, survey_url, referral_url, cert_top_heading, cert_main_heading, cert_sub_heading, cert_purple_footer, cert_description FROM $table_events WHERE status = 'active' ORDER BY id DESC" );

	return rest_ensure_response(
		array(
			'success' => true,
			'events'  => $events,
		)
	);
}

function aws_sbg_rest_issue_certificate( $request ) {
	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$table_certs  = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	$params            = $request->get_json_params();
	$event_id            = intval( $params['event_id'] ?? 0 );
	$participant_name    = sanitize_text_field( $params['participant_name'] ?? '' );
	$participant_email   = sanitize_email( $params['participant_email'] ?? '' );
	$student_id          = sanitize_text_field( $params['student_id'] ?? '' );
	$builder_profile_url = sanitize_text_field( $params['builder_profile_url'] ?? '' );
	$is_new_builder      = ! empty( $params['is_new_builder'] ) ? 1 : 0;
	$survey_screenshot   = sanitize_text_field( $params['survey_screenshot'] ?? '' );

	if ( ! $event_id || empty( $participant_name ) || empty( $participant_email ) ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Please provide event, full name, and email.',
			)
		);
	}

	$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_events WHERE id = %d AND status = 'active'", $event_id ) );
	if ( ! $event ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Selected event is no longer active.',
			)
		);
	}

	// Generate Unique Verification Code
	$verification_code = 'AWS-CERT-' . strtoupper( wp_generate_password( 6, false ) );

	$wpdb->insert(
		$table_certs,
		array(
			'event_id'            => $event_id,
			'participant_name'    => $participant_name,
			'participant_email'   => $participant_email,
			'student_id'          => $student_id,
			'builder_profile_url' => $builder_profile_url,
			'is_new_builder'      => $is_new_builder,
			'verification_code'   => $verification_code,
			'survey_completed'    => 1,
			'referral_completed'  => 1,
			'survey_screenshot'   => $survey_screenshot,
		)
	);

	return rest_ensure_response(
		array(
			'success'            => true,
			'verification_code'  => $verification_code,
			'event_title'        => $event->title,
			'event_track'        => $event->track,
			'event_date'         => $event->event_date,
			'cert_top_heading'   => ! empty( $event->cert_top_heading ) ? $event->cert_top_heading : '★ AWS CERTIFICATION SELF-ASSESSMENT 2026 ★',
			'cert_main_heading'  => ! empty( $event->cert_main_heading ) ? $event->cert_main_heading : 'Certificate',
			'cert_sub_heading'   => ! empty( $event->cert_sub_heading ) ? $event->cert_sub_heading : 'PASSED — AWS Certification Self-Assessment',
			'cert_purple_footer' => ! empty( $event->cert_purple_footer ) ? $event->cert_purple_footer : 'Organized by AWS Student Builder Group at Air University Islamabad',
			'cert_description'   => ! empty( $event->cert_description ) ? $event->cert_description : 'For successfully completing the AWS Certification Self-Assessment',
		)
	);
}

/**
 * 6. CERTIFIED MEMBERS DIRECTORY ADMIN RENDERER
 */
function aws_sbg_render_members_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$table_certs  = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	// Handle Members CSV Export
	if ( isset( $_GET['export_members_csv'] ) ) {
		aws_sbg_export_all_members_csv();
		exit;
	}

	// Group by participant_email to get certified members roster
	$members = $wpdb->get_results( "
		SELECT 
			participant_email, 
			participant_name, 
			student_id, 
			COUNT(*) as cert_count, 
			MAX(issued_at) as latest_issue_date,
			GROUP_CONCAT(verification_code SEPARATOR ', ') as all_codes
		FROM $table_certs 
		GROUP BY participant_email 
		ORDER BY latest_issue_date DESC
	" );

	$total_certs     = $wpdb->get_var( "SELECT COUNT(*) FROM $table_certs" );
	$total_members   = count( $members );
	$total_referrals = $wpdb->get_var( "SELECT COUNT(*) FROM $table_certs WHERE is_new_builder = 1" );
	aws_sbg_render_admin_nav_tabs( 'members' );
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
			<h1 style="display:flex; align-items:center; gap:10px; font-weight:700; margin:0;">
				<span class="dashicons dashicons-id" style="font-size:32px; width:32px; height:32px; color:#ad5cff;"></span>
				AWS SBG Certified Members Roster
			</h1>
			<a href="?page=aws-sbg-members&export_members_csv=1" class="button button-primary" style="background:#0284c7; border-color:#0369a1; font-weight:700; padding:6px 14px; height:auto;">📥 Export Certified Members CSV</a>
		</div>

		<!-- METRICS HIGHLIGHT BOXES -->
		<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:15px; margin-bottom:20px;">
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Total Unique Members</div>
				<div style="font-size:32px; font-weight:800; color:#0f172a; margin-top:4px;"><?php echo intval( $total_members ); ?></div>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Total Certificates Issued</div>
				<div style="font-size:32px; font-weight:800; color:#ad5cff; margin-top:4px;"><?php echo intval( $total_certs ); ?></div>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Referral Signups Generated</div>
				<div style="font-size:32px; font-weight:800; color:#10b981; margin-top:4px;"><?php echo intval( $total_referrals ); ?> 🌟</div>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Server Overhead</div>
				<div style="font-size:32px; font-weight:800; color:#64748b; margin-top:4px;">0% (Canvas)</div>
			</div>
		</div>

		<!-- MEMBERS TABLE -->
		<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
			<table class="wp-list-table widefat fixed striped" style="border:none;">
				<thead>
					<tr>
						<th style="font-weight:700; padding:12px 15px;">Member Name</th>
						<th style="font-weight:700; padding:12px 15px;">Email Address</th>
						<th style="font-weight:700; padding:12px 15px;">Referral Status</th>
						<th style="font-weight:700; padding:12px 15px; text-align:center;">Certificates Earned</th>
						<th style="font-weight:700; padding:12px 15px;">Latest Issue Date</th>
						<th style="font-weight:700; padding:12px 15px; text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $members ) ) : ?>
						<tr>
							<td colspan="6" style="padding:20px; text-align:center; color:#64748b;">No certified members recorded yet. Members will automatically populate when they claim certificates!</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $members as $m ) : ?>
							<?php
							$member_certs = $wpdb->get_results( $wpdb->prepare( "SELECT c.*, e.title as event_title FROM $table_certs c LEFT JOIN $table_events e ON c.event_id = e.id WHERE c.participant_email = %s ORDER BY c.id DESC", $m->participant_email ) );
							$latest_cert = ! empty( $member_certs ) ? $member_certs[0] : null;
							$used_referral = $latest_cert && ! empty( $latest_cert->is_new_builder );
							?>
							<tr>
								<td style="font-weight:700; color:#0f172a; padding:12px 15px;"><?php echo esc_html( $m->participant_name ); ?></td>
								<td style="font-family:monospace; color:#475569; padding:12px 15px;"><?php echo esc_html( $m->participant_email ); ?></td>
								<td style="padding:12px 15px;">
									<?php if ( $used_referral ) : ?>
										<span style="background:#f3e8ff; color:#7e22ce; font-weight:800; padding:4px 10px; border-radius:20px; font-size:10px; text-transform:uppercase;">🌟 REFERRAL USED</span>
										<?php if ( ! empty( $latest_cert->builder_profile_url ) ) : ?>
											<div style="font-size:11px; color:#64748b; margin-top:3px;"><?php echo esc_html( $latest_cert->builder_profile_url ); ?></div>
										<?php endif; ?>
									<?php else : ?>
										<span style="background:#f1f5f9; color:#64748b; font-weight:700; padding:4px 10px; border-radius:20px; font-size:10px; text-transform:uppercase;">⚡ SKIPPED (EXISTING)</span>
									<?php endif; ?>
								</td>
								<td style="text-align:center; padding:12px 15px;">
									<span style="background:#f3e8ff; color:#7e22ce; font-weight:800; padding:4px 10px; border-radius:20px; font-size:11px;">
										🏆 <?php echo intval( $m->cert_count ); ?> Cert<?php echo intval( $m->cert_count ) > 1 ? 's' : ''; ?>
									</span>
								</td>
								<td style="font-size:12px; color:#64748b; padding:12px 15px;"><?php echo esc_html( date( 'M d, Y', strtotime( $m->latest_issue_date ) ) ); ?></td>
								<td style="text-align:right; padding:12px 15px;">
									<button type="button" onclick="openMemberCertsModal(<?php echo esc_attr( json_encode( $member_certs ) ); ?>)" class="button button-secondary button-small" style="font-weight:600; font-size:11px;">🔍 View Certificates & Screenshots</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- CERTIFICATE DETAILS & SCREENSHOT MODAL -->
	<div id="aws-member-certs-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); z-index:99999; justify-content:center; align-items:center; padding:20px;">
		<div style="background:#ffffff; border-radius:16px; width:100%; max-width:700px; max-height:85vh; overflow-y:auto; padding:25px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative;">
			<button type="button" onclick="closeMemberCertsModal()" style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:24px; cursor:pointer; color:#64748b;">✕</button>
			<h3 style="margin-top:0; font-weight:800; color:#0f172a; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">📜 Member Certificate History & Survey Screenshots</h3>
			<div id="aws-modal-certs-content" style="margin-top:15px;"></div>
		</div>
	</div>

	<script>
	function openMemberCertsModal(certs) {
		const modal = document.getElementById('aws-member-certs-modal');
		const content = document.getElementById('aws-modal-certs-content');
		
		let html = '<div style="display:flex; flex-direction:column; gap:15px;">';
		certs.forEach(c => {
			html += `
				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:15px;">
					<div style="display:flex; justify-content:space-between; align-items:center;">
						<div>
							<h4 style="margin:0; font-weight:700; color:#0f172a; font-size:15px;">${c.event_title || 'AWS Event'}</h4>
							<div style="font-size:11px; color:#64748b; margin-top:2px;">Issued: ${c.issued_at} • Code: <strong style="color:#0284c7;">${c.verification_code}</strong></div>
						</div>
					</div>
					${c.survey_screenshot ? `
						<div style="margin-top:10px; border-top:1px solid #e2e8f0; padding-top:10px;">
							<div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:4px;">📷 Survey Submission Screenshot:</div>
							<a href="${c.survey_screenshot}" target="_blank">
								<img src="${c.survey_screenshot}" style="max-height:200px; max-width:100%; border-radius:8px; border:1px solid #cbd5e1; object-fit:contain;">
							</a>
						</div>
					` : '<div style="font-size:11px; color:#94a3b8; margin-top:6px;">No screenshot attached.</div>'}
				</div>
			`;
		});
		html += '</div>';
		content.innerHTML = html;
		modal.style.display = 'flex';
	}

	function closeMemberCertsModal() {
		document.getElementById('aws-member-certs-modal').style.display = 'none';
	}
	</script>
	<?php
}

/**
 * 7. EXPORT ALL CERTIFIED MEMBERS CSV
 */
function aws_sbg_export_all_members_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table_certs = $wpdb->prefix . 'aws_sbg_event_issued_certs';

	$members = $wpdb->get_results( "
		SELECT 
			participant_email, 
			participant_name, 
			student_id, 
			COUNT(*) as cert_count, 
			MAX(issued_at) as latest_issue_date,
			GROUP_CONCAT(verification_code SEPARATOR '; ') as all_codes
		FROM $table_certs 
		GROUP BY participant_email 
		ORDER BY latest_issue_date DESC
	" );

	$filename = 'AWS-SBG-Certified-Members-Roster-' . date( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Member Name', 'Email Address', 'Student ID / Department', 'Total Certificates Earned', 'Latest Issue Date', 'All Verification Codes' ) );

	foreach ( $members as $m ) {
		fputcsv(
			$output,
			array(
				$m->participant_name,
				$m->participant_email,
				$m->student_id,
				$m->cert_count,
				$m->latest_issue_date,
				$m->all_codes,
			)
		);
	}
	fclose( $output );
}
