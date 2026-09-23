<?php
/**
 * AWS SBG Admin Control Center & Dashboard Suite
 * Centralized Management for Registrations, Events, Certificates, Referrals, and Mock Exams.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. UNIFIED ADMIN MENU SUITE REGISTRATION
 */
function aws_sbg_register_admin_dashboard() {
	// Parent Suite Menu
	add_menu_page(
		'AWS SBG Suite',
		'AWS SBG Suite',
		'manage_options',
		'aws-sbg-suite',
		'aws_sbg_render_overview_dashboard',
		'dashicons-cloud',
		2
	);

	// Submenu 1: Command Center Overview
	add_submenu_page(
		'aws-sbg-suite',
		'Command Center Overview',
		'📊 Overview',
		'manage_options',
		'aws-sbg-suite',
		'aws_sbg_render_overview_dashboard'
	);

	// Submenu 2: Event Registrations
	add_submenu_page(
		'aws-sbg-suite',
		'Event Registrations',
		'🎫 Registrations',
		'manage_options',
		'aws-sbg-registrations',
		'aws_sbg_render_admin_dashboard'
	);

	// Submenu 3: Events & Certificates Setup
	add_submenu_page(
		'aws-sbg-suite',
		'Events & Certificate Setup',
		'📜 Events & Cert Setup',
		'manage_options',
		'aws-sbg-events',
		'aws_sbg_render_events_admin_page'
	);

	// Submenu 4: Certified Roster & Referrals
	add_submenu_page(
		'aws-sbg-suite',
		'Certified Roster & Referrals',
		'👥 Certified Roster & Referrals',
		'manage_options',
		'aws-sbg-members',
		'aws_sbg_render_members_admin_page'
	);

	// Submenu 5: Mock Exam Portal Manager
	add_submenu_page(
		'aws-sbg-suite',
		'Mock Exam Manager',
		'📝 Mock Exams',
		'manage_options',
		'aws-mock-exam',
		'aws_sbg_render_exam_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_admin_dashboard', 9 );

/**
 * 2. REUSABLE TOP NAVIGATION TAB BAR
 */
function aws_sbg_render_admin_nav_tabs( $active_tab = 'overview' ) {
	$tabs = array(
		'overview'      => array( 'title' => '📊 Overview', 'url' => admin_url( 'admin.php?page=aws-sbg-suite' ) ),
		'registrations' => array( 'title' => '🎫 Registrations', 'url' => admin_url( 'admin.php?page=aws-sbg-registrations' ) ),
		'events'        => array( 'title' => '📜 Events & Cert Setup', 'url' => admin_url( 'admin.php?page=aws-sbg-events' ) ),
		'members'       => array( 'title' => '👥 Certified Roster', 'url' => admin_url( 'admin.php?page=aws-sbg-members' ) ),
		'mock_exam'     => array( 'title' => '📝 Mock Exams', 'url' => admin_url( 'admin.php?page=aws-mock-exam' ) ),
		'swag_wheel'    => array( 'title' => '🎡 Swag Picker', 'url' => admin_url( 'admin.php?page=aws-swag-wheel' ) ),
		'giveaway'      => array( 'title' => '🎁 Campus Giveaway', 'url' => admin_url( 'admin.php?page=aws-campus-giveaway' ) ),
	);
	?>
	<div style="background:#ffffff; border-bottom:1px solid #e2e8f0; margin:-10px -20px 25px -20px; padding:15px 25px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 4px rgba(0,0,0,0.02); font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
		<div style="display:flex; align-items:center; gap:12px;">
			<div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, #ad5cff, #7000ff); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:18px; box-shadow:0 4px 10px rgba(173,92,255,0.3);">☁️</div>
			<div>
				<h2 style="margin:0; font-size:18px; font-weight:800; color:#0f172a; line-height:1.2;">AWS SBG Control Center</h2>
				<span style="font-size:11px; color:#64748b; font-weight:600;">Air University Community Platform • Unified Suite</span>
			</div>
		</div>
		<div style="display:flex; items-center; gap:6px; background:#f8fafc; padding:4px; border-radius:12px; border:1px solid #e2e8f0;">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<a href="<?php echo esc_url( $tab['url'] ); ?>" style="padding:8px 14px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none; transition:all 0.15s ease; <?php echo ( $active_tab === $key ) ? 'background:#ffffff; color:#ad5cff; box-shadow:0 2px 4px rgba(0,0,0,0.06); border:1px solid #e2e8f0;' : 'color:#64748b;'; ?>">
					<?php echo esc_html( $tab['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * 3. MAIN COMMAND CENTER OVERVIEW DASHBOARD
 */
function aws_sbg_render_overview_dashboard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	global $wpdb;
	$table_certs  = $wpdb->prefix . 'aws_sbg_event_issued_certs';
	$table_events = $wpdb->prefix . 'aws_sbg_events';

	// Count Metrics
	$total_registrations = wp_count_posts( 'aws_event_reg' )->publish;
	$total_certs         = $wpdb->get_var( "SELECT COUNT(*) FROM $table_certs" );
	$total_referrals     = $wpdb->get_var( "SELECT COUNT(*) FROM $table_certs WHERE is_new_builder = 1" );
	$total_events        = $wpdb->get_var( "SELECT COUNT(*) FROM $table_events WHERE status = 'active'" );

	// Fetch recent registrations
	$recent_regs = get_posts( array(
		'post_type'      => 'aws_event_reg',
		'posts_per_page' => 5,
		'post_status'    => 'publish',
	) );

	// Fetch recent certs
	$recent_certs = $wpdb->get_results( "SELECT c.*, e.title as event_title FROM $table_certs c LEFT JOIN $table_events e ON c.event_id = e.id ORDER BY c.id DESC LIMIT 5" );

	aws_sbg_render_admin_nav_tabs( 'overview' );
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		
		<!-- WELCOME BANNER -->
		<div style="background:linear-gradient(135deg, #0f172a, #1e293b); color:#ffffff; padding:25px; border-radius:16px; margin-bottom:25px; box-shadow:0 10px 25px rgba(0,0,0,0.1); position:relative; overflow:hidden;">
			<div style="position:absolute; right:-20px; top:-20px; width:200px; height:200px; background:rgba(173,92,255,0.15); border-radius:100px; blur:40px; pointer-events:none;"></div>
			<span style="background:rgba(173,92,255,0.2); color:#ad5cff; border:1px solid rgba(173,92,255,0.4); font-size:10px; font-weight:800; text-transform:uppercase; padding:4px 10px; border-radius:20px;">AWS SBG Command Center</span>
			<h1 style="margin:10px 0 6px 0; font-size:24px; font-weight:900; color:#ffffff;">Welcome back, Administrator 👋</h1>
			<p style="margin:0; font-size:13px; color:#94a3b8; max-width:600px;">Manage active workshop registrations, issue verified certificates, track community referral signups, and monitor exam performance from a single dashboard.</p>
		</div>

		<!-- GLOBAL KPI METRIC CARDS -->
		<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:18px; margin-bottom:25px;">
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Event Registrations</div>
				<div style="font-size:32px; font-weight:900; color:#0f172a; margin-top:4px;"><?php echo intval( $total_registrations ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-registrations' ) ); ?>" style="font-size:11px; color:#ad5cff; font-weight:700; text-decoration:none; margin-top:8px; display:inline-block;">Manage Registrations ➔</a>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Certificates Issued</div>
				<div style="font-size:32px; font-weight:900; color:#ad5cff; margin-top:4px;"><?php echo intval( $total_certs ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-members' ) ); ?>" style="font-size:11px; color:#ad5cff; font-weight:700; text-decoration:none; margin-top:8px; display:inline-block;">View Certified Roster ➔</a>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Referral Signups 🌟</div>
				<div style="font-size:32px; font-weight:900; color:#10b981; margin-top:4px;"><?php echo intval( $total_referrals ); ?></div>
				<span style="font-size:11px; color:#059669; font-weight:700; margin-top:8px; display:inline-block;">Community Supporters</span>
			</div>
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
				<div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Active Workshops</div>
				<div style="font-size:32px; font-weight:900; color:#0284c7; margin-top:4px;"><?php echo intval( $total_events ); ?></div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-events' ) ); ?>" style="font-size:11px; color:#0284c7; font-weight:700; text-decoration:none; margin-top:8px; display:inline-block;">Manage Setup ➔</a>
			</div>
		</div>

		<!-- QUICK MANAGEMENT ACTIONS -->
		<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:15px; margin-bottom:30px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-registrations' ) ); ?>" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:15px; text-decoration:none; color:#0f172a; transition:all 0.15s ease; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="font-size:20px; mb-2">🎫</div>
				<div style="font-weight:800; font-size:13px;">Registrations Hub</div>
				<div style="font-size:11px; color:#64748b; margin-top:2px;">Filter attendees &amp; payments</div>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-events' ) ); ?>" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:15px; text-decoration:none; color:#0f172a; transition:all 0.15s ease; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="font-size:20px; mb-2">📜</div>
				<div style="font-weight:800; font-size:13px;">Active Events &amp; Certs</div>
				<div style="font-size:11px; color:#64748b; margin-top:2px;">Configure survey &amp; referral links</div>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-members' ) ); ?>" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:15px; text-decoration:none; color:#0f172a; transition:all 0.15s ease; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="font-size:20px; mb-2">👥</div>
				<div style="font-weight:800; font-size:13px;">Certified Roster</div>
				<div style="font-size:11px; color:#64748b; margin-top:2px;">Track screenshots &amp; referral badges</div>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-mock-exam' ) ); ?>" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:15px; text-decoration:none; color:#0f172a; transition:all 0.15s ease; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="font-size:20px; mb-2">📝</div>
				<div style="font-weight:800; font-size:13px;">Mock Exam Manager</div>
				<div style="font-size:11px; color:#64748b; margin-top:2px;">Question banks &amp; student scores</div>
			</a>
		</div>

		<!-- RECENT ACTIVITY GRID -->
		<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
			
			<!-- RECENT REGISTRATIONS -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
					<h3 style="margin:0; font-size:14px; font-weight:800; color:#0f172a;">Recent Event Registrations</h3>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-registrations' ) ); ?>" style="font-size:11px; color:#ad5cff; font-weight:700; text-decoration:none;">View All ➔</a>
				</div>
				<?php if ( empty( $recent_regs ) ) : ?>
					<div style="font-size:12px; color:#64748b;">No recent registrations found.</div>
				<?php else : ?>
					<div style="display:flex; flex-direction:column; gap:10px;">
						<?php foreach ( $recent_regs as $reg ) : ?>
							<?php
							$email     = get_post_meta( $reg->ID, 'email', true );
							$reg_no    = get_post_meta( $reg->ID, 'registration_id', true );
							$supporter = get_post_meta( $reg->ID, 'is_supporter', true ) === '1';
							?>
							<div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f8fafc; border-radius:10px; font-size:12px;">
								<div>
									<strong style="color:#0f172a; display:block;"><?php echo esc_html( get_the_title( $reg->ID ) ); ?></strong>
									<span style="color:#64748b; font-family:monospace; font-size:11px;"><?php echo esc_html( $email ); ?></span>
								</div>
								<div style="text-align:right;">
									<span style="background:#e2e8f0; color:#334155; font-weight:800; padding:2px 8px; border-radius:12px; font-size:10px;">#<?php echo esc_html( $reg_no ); ?></span>
									<?php if ( $supporter ) : ?>
										<span style="background:#fef3c7; color:#d97706; font-weight:800; padding:2px 6px; border-radius:12px; font-size:10px; margin-left:4px;">⭐ SUPPORTER</span>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- RECENT CERTIFICATES ISSUED -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
					<h3 style="margin:0; font-size:14px; font-weight:800; color:#0f172a;">Recent Issued Certificates</h3>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-sbg-members' ) ); ?>" style="font-size:11px; color:#ad5cff; font-weight:700; text-decoration:none;">View Roster ➔</a>
				</div>
				<?php if ( empty( $recent_certs ) ) : ?>
					<div style="font-size:12px; color:#64748b;">No certificates issued yet.</div>
				<?php else : ?>
					<div style="display:flex; flex-direction:column; gap:10px;">
						<?php foreach ( $recent_certs as $cert ) : ?>
							<div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f8fafc; border-radius:10px; font-size:12px;">
								<div>
									<strong style="color:#0f172a; display:block;"><?php echo esc_html( $cert->participant_name ); ?></strong>
									<span style="color:#64748b; font-size:11px;"><?php echo esc_html( $cert->event_title ? $cert->event_title : 'Workshop' ); ?></span>
								</div>
								<div style="text-align:right;">
									<?php if ( ! empty( $cert->is_new_builder ) ) : ?>
										<span style="background:#f3e8ff; color:#7e22ce; font-weight:800; padding:2px 8px; border-radius:12px; font-size:10px;">🌟 SUPPORTER</span>
									<?php else : ?>
										<span style="background:#f1f5f9; color:#64748b; font-weight:700; padding:2px 8px; border-radius:12px; font-size:10px;">⚡ EXISTING</span>
									<?php endif; ?>
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
 * 4. AJAX PERK CLAIM TOGGLE HANDLER
 */
function aws_sbg_handle_perk_toggle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	$reg_id = isset( $_POST['reg_id'] ) ? intval( $_POST['reg_id'] ) : 0;
	$key    = isset( $_POST['perk_key'] ) ? sanitize_key( $_POST['perk_key'] ) : '';

	if ( $reg_id && in_array( $key, array( 'attended', 'lunch_claimed', 'swag_claimed', 'custom_perk_claimed' ) ) ) {
		$current = get_post_meta( $reg_id, $key, true );
		$new_val = ( $current === 'yes' ) ? 'no' : 'yes';
		update_post_meta( $reg_id, $key, $new_val );
		wp_send_json_success( array( 'new_val' => $new_val ) );
	}

	wp_send_json_error( 'Invalid Request' );
}
add_action( 'wp_ajax_aws_sbg_toggle_perk', 'aws_sbg_handle_perk_toggle' );

/**
 * Handle Real-Time Member Type Toggle (Paying vs General)
 */
function aws_sbg_handle_member_type_toggle() {
	check_ajax_referer( 'aws_sbg_admin_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	$reg_id = isset( $_POST['reg_id'] ) ? intval( $_POST['reg_id'] ) : 0;
	if ( $reg_id ) {
		$current = get_post_meta( $reg_id, 'member_type', true );
		if ( empty( $current ) ) {
			$tier = get_post_meta( $reg_id, 'ticket_tier', true );
			$current = ( $tier === 'free' ) ? 'general' : 'paying';
		}
		$new_val = ( $current === 'paying' ) ? 'general' : 'paying';
		update_post_meta( $reg_id, 'member_type', $new_val );

		// Real-time synchronization across access codes and exam attempts
		global $wpdb;
		$email = get_post_meta( $reg_id, 'email', true );
		if ( ! empty( $email ) ) {
			$table_codes    = $wpdb->prefix . 'aws_access_codes';
			$table_attempts = $wpdb->prefix . 'aws_exam_attempts';
			@$wpdb->update( $table_codes, array( 'member_type' => $new_val ), array( 'member_email' => $email ) );
			@$wpdb->update( $table_attempts, array( 'member_type' => $new_val ), array( 'member_email' => $email ) );
		}

		wp_send_json_success( array( 'new_val' => $new_val ) );
	}

	wp_send_json_error( 'Invalid Request' );
}
add_action( 'wp_ajax_aws_sbg_toggle_member_type', 'aws_sbg_handle_member_type_toggle' );

/**
 * 5. RENDER THE EVENT REGISTRATION ADMIN DASHBOARD
 */
function aws_sbg_render_admin_dashboard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	// Handle Manual Registration Submission
	$message = '';
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'manual_admin_event_registration' && check_admin_referer( 'aws_manual_reg_action', 'aws_manual_reg_nonce' ) ) {
		$m_name         = sanitize_text_field( $_POST['manual_name'] );
		$m_email        = sanitize_email( $_POST['manual_email'] );
		$m_phone        = sanitize_text_field( $_POST['manual_phone'] );
		$m_uni          = sanitize_text_field( $_POST['manual_university'] );
		$m_event_id     = intval( $_POST['manual_event_id'] );
		$m_tier         = sanitize_text_field( $_POST['manual_tier'] );
		$m_member_type  = sanitize_text_field( $_POST['manual_member_type'] ?? 'paying' );
		$m_tshirt       = sanitize_text_field( $_POST['manual_tshirt'] );
		$m_experience   = sanitize_text_field( $_POST['manual_experience'] );
		$m_is_supporter = ( $m_tier === 'supporter' ) ? 'yes' : 'no';

		if ( empty( $m_name ) || empty( $m_email ) || empty( $m_phone ) || empty( $m_uni ) || empty( $m_event_id ) ) {
			$message = '⚠️ Please fill in all required fields (Name, Email, Phone, University, and Event).';
		} else {
			$reg_number = function_exists( 'aws_sbg_generate_next_registration_number' ) ? aws_sbg_generate_next_registration_number() : '#' . ( wp_count_posts( 'aws_event_reg' )->publish + 1001 );
			$title_suffix = ( $m_is_supporter === 'yes' ) ? ' ⭐ Super Supporter' : '';

			$post_id = wp_insert_post( array(
				'post_type'   => 'aws_event_reg',
				'post_title'  => $reg_number . ' - ' . $m_name . $title_suffix,
				'post_status' => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) && $post_id ) {
				update_post_meta( $post_id, 'registration_id', $reg_number );
				update_post_meta( $post_id, 'event_id', $m_event_id );
				update_post_meta( $post_id, 'email', $m_email );
				update_post_meta( $post_id, 'whatsapp_number', $m_phone );
				update_post_meta( $post_id, 'phone_number', $m_phone );
				update_post_meta( $post_id, 'university_organization', $m_uni );
				update_post_meta( $post_id, 'ticket_tier', $m_tier );
				update_post_meta( $post_id, 'member_type', $m_member_type );
				update_post_meta( $post_id, 'is_supporter', $m_is_supporter );
				update_post_meta( $post_id, 'tshirt_size', $m_tshirt );
				update_post_meta( $post_id, 'experience_level', $m_experience );
				update_post_meta( $post_id, 'attended', 'no' );
				update_post_meta( $post_id, 'lunch_claimed', 'no' );
				update_post_meta( $post_id, 'swag_claimed', 'no' );
				update_post_meta( $post_id, 'custom_perk_claimed', 'no' );
				update_post_meta( $post_id, 'manual_admin_entry', 'yes' );

				$event_obj = get_post( $m_event_id );
				$event_title = $event_obj ? $event_obj->post_title : 'Event #' . $m_event_id;

				$message = "✅ Successfully registered {$m_name} ({$m_email}) for {$event_title} with Registration {$reg_number}!";
			} else {
				$message = '⚠️ Error creating manual registration.';
			}
		}
	}

	// Fetch registrations
	$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$args  = array(
		'post_type'      => 'aws_event_reg',
		'posts_per_page' => 50,
		'paged'          => $paged,
		'post_status'    => 'publish',
	);

	$meta_query = array( 'relation' => 'AND' );

	// Handle event filtering
	$selected_event = isset( $_GET['filter_event'] ) ? intval( $_GET['filter_event'] ) : 0;
	if ( $selected_event ) {
		$meta_query[] = array(
			'key'     => 'event_id',
			'value'   => $selected_event,
			'compare' => '=',
		);
	}

	// Handle Category (Paying vs General) Filtering
	$selected_member_type = isset( $_GET['filter_member_type'] ) ? sanitize_text_field( $_GET['filter_member_type'] ) : '';
	if ( $selected_member_type ) {
		if ( $selected_member_type === 'paying' ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'member_type',
					'value'   => 'paying',
					'compare' => '=',
				),
				array(
					'key'     => 'member_type',
					'compare' => 'NOT EXISTS',
				),
			);
		} else {
			$meta_query[] = array(
				'key'     => 'member_type',
				'value'   => 'general',
				'compare' => '=',
			);
		}
	}

	// Handle search by Registration ID or Email
	$search_reg = isset( $_GET['search_reg'] ) ? sanitize_text_field( $_GET['search_reg'] ) : '';
	if ( $search_reg ) {
		$meta_query[] = array(
			'key'     => 'registration_id',
			'value'   => $search_reg,
			'compare' => 'LIKE',
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	$registrations = new WP_Query( $args );

	// Fetch all events for the filter dropdown
	$events = get_posts( array(
		'post_type'      => 'aws_event',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	aws_sbg_render_admin_nav_tabs( 'registrations' );
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
		<style>
			@media print {
				#adminmenumain, #wpadminbar, #adminmenuwrap, #wpfooter, .update-nag, .notice { display: none !important; }
				html, body, #wpcontent, #wpwrap { background: #fff !important; color: #000 !important; margin: 0 !important; padding: 0 !important; margin-left: 0 !important; }
				.flex.justify-between.items-center.mb-10, .grid.grid-cols-1, form { display: none !important; }
				.no-print { display: none !important; }
				.print-only { display: block !important; }
			}
		</style>

		<?php if ( $message ) : ?>
			<div class="mb-6 p-4 rounded-2xl <?php echo strpos($message, '✅') !== false ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-amber-50 border border-amber-200 text-amber-900'; ?> font-bold text-xs">
				<?php echo esc_html( $message ); ?>
			</div>
		<?php endif; ?>

		<!-- HEADER & ACTION BAR -->
		<div class="flex flex-wrap justify-between items-center gap-4 mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
			<div>
				<h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
					<span>🎫 Event Registrations</span>
				</h1>
				<p class="text-slate-500 text-xs mt-1 font-medium">Verify attendee registrations, ticket tiers, payment proofs, and assign Leaderboard Eligibility (Paying vs General).</p>
			</div>

			<div class="flex items-center gap-2.5 flex-wrap">
				<button type="button" onclick="document.getElementById('manual-reg-card').classList.toggle('hidden');" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
					➕ Manually Register Attendee
				</button>

				<form method="GET" class="flex items-center gap-2 flex-wrap">
					<input type="hidden" name="page" value="aws-sbg-registrations">
					
					<select name="filter_event" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none">
						<option value="">-- All Events --</option>
						<?php foreach ( $events as $ev ) : ?>
							<option value="<?php echo $ev->ID; ?>" <?php selected( $selected_event, $ev->ID ); ?>>
								<?php echo esc_html( $ev->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<select name="filter_member_type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none">
						<option value="">-- All Categories --</option>
						<option value="paying" <?php selected( $selected_member_type, 'paying' ); ?>>💳 Paying (Prize Eligible)</option>
						<option value="general" <?php selected( $selected_member_type, 'general' ); ?>>🎟️ General (Aid / Pro)</option>
					</select>

					<input type="text" name="search_reg" value="<?php echo esc_attr( $search_reg ); ?>" placeholder="Reg # (e.g. 1004)" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none w-36">
					
					<button type="submit" class="bg-slate-900 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-xs hover:bg-slate-800 transition-all">Search</button>
				</form>
			</div>
		</div>

		<!-- COLLAPSIBLE MANUAL REGISTRATION FORM CARD -->
		<div id="manual-reg-card" class="<?php echo ( $message && strpos($message, '⚠️') !== false ) ? '' : 'hidden'; ?> mb-6 bg-slate-900 text-white p-6 rounded-2xl border border-slate-800 shadow-xl transition-all">
			<div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
				<div>
					<h3 class="text-base font-extrabold text-white flex items-center gap-2">
						<span>➕ Manual Attendee Registration</span>
						<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase">Offline / Closed Events</span>
					</h3>
					<p class="text-slate-400 text-xs mt-0.5">Register a participant manually for any event (including closed/ended events) to grant them an official Registration ID.</p>
				</div>
				<button type="button" onclick="document.getElementById('manual-reg-card').classList.add('hidden');" class="text-slate-400 hover:text-white text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700">
					✕ Close
				</button>
			</div>

			<form method="POST" class="space-y-4">
				<?php wp_nonce_field( 'aws_manual_reg_action', 'aws_manual_reg_nonce' ); ?>
				<input type="hidden" name="action" value="manual_admin_event_registration">

				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<!-- Event Target -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">Target Event <span class="text-purple-400">*</span></label>
						<select name="manual_event_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
							<option value="">-- Select Event --</option>
							<?php foreach ( $events as $ev ) : ?>
								<option value="<?php echo $ev->ID; ?>">
									<?php echo esc_html( $ev->post_title ); ?> (ID: #<?php echo $ev->ID; ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Full Name -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">Full Name <span class="text-purple-400">*</span></label>
						<input type="text" name="manual_name" required placeholder="e.g. Muhammad Ali" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
					</div>

					<!-- Email Address -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">Email Address <span class="text-purple-400">*</span></label>
						<input type="email" name="manual_email" required placeholder="e.g. candidate@university.edu" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
					<!-- Phone / WhatsApp -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">WhatsApp / Phone # <span class="text-purple-400">*</span></label>
						<input type="text" name="manual_phone" required placeholder="e.g. 0300-1234567" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
					</div>

					<!-- University / Organization -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">University / Org <span class="text-purple-400">*</span></label>
						<input type="text" name="manual_university" required placeholder="e.g. Air University" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
					</div>

					<!-- Pass Tier -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">Ticket Tier / Pass</label>
						<select name="manual_tier" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
							<option value="regular">Regular Pass (In-Person)</option>
							<option value="earlybird">Earlybird Pass</option>
							<option value="supporter">⭐ Super Supporter (VIP)</option>
							<option value="free">Free Pass</option>
						</select>
					</div>

					<!-- Category: Paying vs General -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">Leaderboard Category</label>
						<select name="manual_member_type" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
							<option value="paying">💳 Paying (Prize Eligible)</option>
							<option value="general">🎟️ General (Aid / Pro)</option>
						</select>
					</div>

					<!-- T-Shirt Size -->
					<div>
						<label class="block text-xs font-bold text-slate-300 mb-1">T-Shirt Size</label>
						<select name="manual_tshirt" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:border-purple-500">
							<option value="M">Medium (M)</option>
							<option value="L">Large (L)</option>
							<option value="XL">Extra Large (XL)</option>
							<option value="S">Small (S)</option>
							<option value="XXL">Double XL (XXL)</option>
						</select>
					</div>
				</div>

				<div class="flex justify-end items-center gap-3 pt-2">
					<button type="button" onclick="document.getElementById('manual-reg-card').classList.add('hidden');" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">
						Cancel
					</button>
					<button type="submit" class="px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-all">
						✓ Confirm & Register Attendee
					</button>
				</div>
			</form>
		</div>

		<!-- REGISTRATIONS TABLE -->
		<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
			<table class="w-full text-left text-xs border-collapse">
				<thead>
					<tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider">
						<th class="py-3.5 px-4">Reg #</th>
						<th class="py-3.5 px-4">Attendee Name</th>
						<th class="py-3.5 px-4">Phone #</th>
						<th class="py-3.5 px-4">Email</th>
						<th class="py-3.5 px-4">Tier / Pass</th>
						<th class="py-3.5 px-4 text-center">Category (Leaderboard)</th>
						<th class="py-3.5 px-4 text-center">Payment Screenshot</th>
						<th class="py-3.5 px-4">University / Org</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100">
					<?php if ( ! $registrations->have_posts() ) : ?>
						<tr>
							<td colspan="8" class="py-8 text-center text-slate-500 font-medium">No registrations found matching your criteria.</td>
						</tr>
					<?php else : ?>
						<?php while ( $registrations->have_posts() ) : $registrations->the_post(); ?>
							<?php
							$reg_id        = get_the_ID();
							$reg_num       = get_post_meta( $reg_id, 'registration_id', true );
							$reg_display   = $reg_num ? '#' . ltrim( $reg_num, '#' ) : '#' . $reg_id;

							// Strip #1047 - and Supporter tags to keep name clean
							$raw_title     = get_the_title();
							$clean_name    = preg_replace( '/^#\d+\s*-\s*/', '', $raw_title );
							$clean_name    = trim( str_replace( array( '⭐ Super Supporter', 'Super Supporter', '⭐' ), '', $clean_name ) );

							$email         = get_post_meta( $reg_id, 'email', true );
							$phone         = get_post_meta( $reg_id, 'whatsapp_number', true );
							if ( empty( $phone ) ) {
								$phone = get_post_meta( $reg_id, 'phone_number', true ) ?: get_post_meta( $reg_id, 'phone', true );
							}
							$event_id      = get_post_meta( $reg_id, 'event_id', true );
							$event_type    = get_post_meta( $event_id, 'aws_event_type', true );
							$ticket_tier   = get_post_meta( $reg_id, 'ticket_tier', true );
							$is_supporter  = get_post_meta( $reg_id, 'is_supporter', true ) === '1' || $ticket_tier === 'supporter';
							$payment_txn   = get_post_meta( $reg_id, 'payment_txn_id', true );
							$attended      = get_post_meta( $reg_id, 'attended', true ) === 'yes';

							// Member Type Category (Paying vs General)
							$member_type   = get_post_meta( $reg_id, 'member_type', true );
							if ( empty( $member_type ) ) {
								$member_type = ( $ticket_tier === 'free' ) ? 'general' : 'paying';
							}
							$is_paying     = ( $member_type === 'paying' );

							// Extract Payment Screenshot URL
							$payment_meta = get_post_meta( $reg_id, 'payment_screenshot', true );
							if ( empty( $payment_meta ) ) {
								$payment_meta = get_post_meta( $reg_id, 'payment_proof_url', true ) ?: get_post_meta( $reg_id, 'payment_proof', true );
							}

							$proof_url = '';
							if ( is_numeric( $payment_meta ) && intval( $payment_meta ) > 0 ) {
								$proof_url = wp_get_attachment_url( intval( $payment_meta ) );
							} elseif ( filter_var( $payment_meta, FILTER_VALIDATE_URL ) ) {
								$proof_url = $payment_meta;
							}
							?>
							<tr class="hover:bg-slate-50/80 transition-colors">
								<td class="py-3.5 px-4 font-mono font-bold text-slate-900"><?php echo esc_html( $reg_display ); ?></td>
								<td class="py-3.5 px-4 font-bold text-slate-900"><?php echo esc_html( $clean_name ); ?></td>
								<td class="py-3.5 px-4 font-mono text-slate-600">
									<?php if ( ! empty( $phone ) ) : ?>
										<span class="font-bold text-slate-800"><?php echo esc_html( $phone ); ?></span>
									<?php else : ?>
										<span class="text-slate-400">N/A</span>
									<?php endif; ?>
								</td>
								<td class="py-3.5 px-4 font-mono text-slate-500"><?php echo esc_html( $email ); ?></td>
								<td class="py-3.5 px-4">
									<?php if ( $is_supporter ) : ?>
										<span class="bg-amber-100 text-amber-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">⭐ SUPPORTER</span>
									<?php elseif ( $ticket_tier === 'earlybird' ) : ?>
										<span class="bg-purple-100 text-purple-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">PAID (EARLY)</span>
									<?php elseif ( $ticket_tier === 'regular' || $event_type === 'paid' || $event_type === 'paid_single' || $event_type === 'paid_tiered' || ! empty( $payment_txn ) ) : ?>
										<span class="bg-purple-100 text-purple-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">PAID</span>
									<?php else : ?>
										<span class="bg-emerald-100 text-emerald-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">FREE</span>
									<?php endif; ?>
								</td>
								<td class="py-3.5 px-4 text-center">
									<button type="button" onclick="awsToggleMemberType(<?php echo $reg_id; ?>, this)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black cursor-pointer transition-all border <?php echo $is_paying ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'; ?>" title="Click to toggle between Paying (Prize Eligible) and General (Voucher Only)">
										<span><?php echo $is_paying ? '💳 Paying (Prize)' : '🎟️ General (Aid/Pro)'; ?></span>
									</button>
								</td>
								<td class="py-3.5 px-4 text-center">
									<?php if ( ! empty( $proof_url ) ) : ?>
										<a href="<?php echo esc_url( $proof_url ); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-extrabold bg-[#f3e8ff] text-[#7e22ce] hover:bg-[#e9d5ff] transition-all border border-[#ad5cff]/30 shadow-xs">
											<span>🖼 View Receipt</span>
										</a>
									<?php else : ?>
										<span class="text-slate-400 font-semibold text-[11px]">No Screenshot</span>
									<?php endif; ?>
								</td>
								<td class="py-3.5 px-4">
									<span class="text-[12px] font-semibold text-slate-700">
										<?php 
										$university = get_post_meta( $reg_id, 'university_organization', true );
										echo esc_html( $university ? $university : '-' ); 
										?>
									</span>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- PAGINATION -->
		<?php if ( $registrations->max_num_pages > 1 ) : ?>
			<div class="mt-6 flex justify-center">
				<?php
				echo paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'current'   => $paged,
					'total'     => $registrations->max_num_pages,
					'prev_text' => '« Prev',
					'next_text' => 'Next »',
				) );
				?>
			</div>
		<?php endif; ?>

		<script>
		function awsToggleMemberType(regId, btn) {
			btn.style.opacity = '0.5';
			btn.disabled = true;
			jQuery.post(ajaxurl, {
				action: 'aws_sbg_toggle_member_type',
				reg_id: regId,
				nonce: '<?php echo wp_create_nonce( 'aws_sbg_admin_nonce' ); ?>'
			}, function(response) {
				btn.style.opacity = '1';
				btn.disabled = false;
				if (response.success) {
					const isPaying = (response.data.new_val === 'paying');
					if (isPaying) {
						btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black cursor-pointer transition-all border bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100';
						btn.innerHTML = '<span>💳 Paying (Prize)</span>';
					} else {
						btn.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black cursor-pointer transition-all border bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200';
						btn.innerHTML = '<span>🎟️ General (Aid/Pro)</span>';
					}
				} else {
					alert(response.data || 'Failed to update category');
				}
			});
		}

		function togglePerk(regId, key) {
			jQuery.post(ajaxurl, {
				action: 'aws_sbg_toggle_perk',
				reg_id: regId,
				perk_key: key
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data || 'Failed to update perk status');
				}
			});
		}
		</script>
	</div>
	<?php
}
