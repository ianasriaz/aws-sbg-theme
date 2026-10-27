<?php
/**
 * AWS re:Invent Watch & Build Party — Dedicated Registration Form & Admin Manager
 *
 * Invite-only event registration for "AWS re:Invent Watch & Build Party Islamabad 2026".
 * Registration numbers are allocated as reinvent-#001, reinvent-#002, etc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ========================================================================
 * 1. CUSTOM POST TYPE FOR RE:INVENT REGISTRATIONS
 * ======================================================================== */

function aws_sbg_register_reinvent_reg_post_type() {
	register_post_type( 'aws_reinvent_reg', array(
		'labels' => array(
			'name'          => 're:Invent Registrations',
			'singular_name' => 're:Invent Registration',
			'add_new'       => 'Add New Registration',
		),
		'description'   => 'Registrations for AWS re:Invent Watch & Build Party',
		'public'        => false,
		'show_ui'       => false,
		'show_in_menu'  => false,
		'supports'      => array( 'title' ),
		'has_archive'   => false,
		'can_export'    => true,
	) );
}
add_action( 'init', 'aws_sbg_register_reinvent_reg_post_type' );

/* ========================================================================
 * 2. REGISTRATION NUMBER GENERATOR
 * ======================================================================== */

function aws_sbg_generate_reinvent_registration_number() {
	$next = (int) get_option( 'aws_reinvent_next_number', 1 );
	$formatted = 'reinvent-#' . str_pad( $next, 3, '0', STR_PAD_LEFT );
	update_option( 'aws_reinvent_next_number', $next + 1 );
	return $formatted;
}

/* ========================================================================
 * 3. FRONT-END FORM SHORTCODE
 * ======================================================================== */

function aws_sbg_reinvent_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'title'    => 'AWS re:Invent Watch & Build Party Islamabad 2026',
		'subtitle' => 'An invite-only evening for builders, dreamers, and cloud enthusiasts. Apply below and we will notify you once your seat is approved.',
		'date'     => '',
		'location' => 'Islamabad, Pakistan',
	), $atts, 'aws_reinvent_form' );

	$success = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'success';
	$reg_id  = isset( $_GET['reg_id'] ) ? sanitize_text_field( wp_unslash( $_GET['reg_id'] ) ) : '';

	ob_start();
	?>
	<div class="reinvent-form-wrap" x-data="reinventForm()">
		<style>
			.reinvent-form-wrap * { box-sizing: border-box; }
			@keyframes reinventPulse {
				0%, 100% { box-shadow: 0 0 0 0 rgba(173, 92, 255, 0.4); }
				50% { box-shadow: 0 0 0 12px rgba(173, 92, 255, 0); }
			}
			@keyframes spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
			.reinvent-pulse { animation: reinventPulse 2.5s infinite; }
			.reinvent-form-wrap input:focus,
			.reinvent-form-wrap select:focus,
			.reinvent-form-wrap textarea:focus {
				border-color: #ad5cff !important;
				outline: none;
				box-shadow: 0 0 0 3px rgba(173, 92, 255, 0.15);
			}
		</style>

		<div style="width:100%; max-width:720px; margin:0 auto; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

			<?php if ( $success ) : ?>
				<div style="background:#ffffff; border:2px solid #10b981; border-radius:24px; padding:40px 28px; text-align:center; box-shadow:0 12px 35px rgba(16,185,129,0.12);">
					<div style="width:64px; height:64px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
						<svg style="width:32px; height:32px; color:#10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
							<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
						</svg>
					</div>
					<h3 style="font-size:22px; font-weight:900; color:#0f172a; margin:0 0 6px;">Application Received!</h3>
					<p style="font-size:14px; color:#64748b; margin:0 0 16px;">Your registration number is <strong style="color:#0f172a; font-family:monospace;"><?php echo esc_html( $reg_id ); ?></strong>. We will review your application and notify you if you are approved to attend AWS re:Invent.</p>
					<a href="<?php echo esc_url( home_url() ); ?>" style="display:inline-flex; align-items:center; gap:6px; padding:12px 22px; background:#ad5cff; color:#fff; font-weight:800; font-size:13px; border-radius:12px; text-decoration:none;">Back to Home</a>
				</div>
			<?php else : ?>

			<!-- Hero -->
			<div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius:24px; padding:32px 28px 28px; margin-bottom:24px; color:#fff; position:relative; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
				<div style="position:absolute; top:-40px; right:-40px; width:180px; height:180px; background:rgba(173,92,255,0.18); border-radius:50%; filter:blur(40px);"></div>
				<div style="position:relative; z-index:1;">
					<div style="display:inline-flex; align-items:center; gap:8px; background:rgba(173,92,255,0.2); color:#e9d5ff; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:0.08em; padding:6px 14px; border-radius:100px; margin-bottom:14px; border:1px solid rgba(173,92,255,0.35);">
						<span class="reinvent-pulse" style="width:8px; height:8px; background:#ad5cff; border-radius:50%; display:inline-block;"></span>
						Invite-Only Event
					</div>
					<h2 style="font-size:26px; font-weight:900; margin:0 0 10px; line-height:1.2;"><?php echo esc_html( $atts['title'] ); ?></h2>
					<p style="font-size:14px; color:#cbd5e1; margin:0 0 18px; line-height:1.5;"><?php echo esc_html( $atts['subtitle'] ); ?></p>
					<div style="display:flex; flex-wrap:wrap; gap:12px; font-size:13px; color:#e2e8f0;">
						<?php if ( $atts['date'] ) : ?>
							<div style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.1); padding:8px 14px; border-radius:10px;">
								<svg style="width:16px; height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
								<span><?php echo esc_html( $atts['date'] ); ?></span>
							</div>
						<?php endif; ?>
						<div style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.1); padding:8px 14px; border-radius:10px;">
							<svg style="width:16px; height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
							<span><?php echo esc_html( $atts['location'] ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Form -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:24px; padding:28px; box-shadow:0 10px 40px rgba(0,0,0,0.06);">
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" @submit="isSubmitting = true">
					<input type="hidden" name="action" value="submit_aws_reinvent_registration">
					<?php wp_nonce_field( 'aws_reinvent_registration_action', 'aws_reinvent_registration_nonce' ); ?>

					<div style="display:flex; flex-wrap:wrap; gap:16px;">
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Full Name <span style="color:#ef4444;">*</span></label>
							<input type="text" name="full_name" required placeholder="Muhammad Ali" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Email Address <span style="color:#ef4444;">*</span></label>
							<input type="email" name="email" required placeholder="ali@example.com" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">WhatsApp Number <span style="color:#ef4444;">*</span></label>
							<input type="tel" name="whatsapp" required placeholder="03000000000" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">University / Organization <span style="color:#ef4444;">*</span></label>
							<input type="text" name="university" required placeholder="Air University Islamabad" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">City <span style="color:#ef4444;">*</span></label>
							<input type="text" name="city" required placeholder="Islamabad" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 280px;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">LinkedIn Profile URL <span style="color:#ef4444;">*</span></label>
							<input type="url" name="linkedin" required placeholder="https://linkedin.com/in/username" style="width:100%; padding:13px 14px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; font-size:14px; color:#0f172a;">
						</div>
						<div style="flex:1 1 100%;">
							<label style="display:block; font-size:11px; font-weight:800; color:#475569; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.05em;">Have you attended any AWS community event before? <span style="color:#ef4444;">*</span></label>
							<div style="display:flex; flex-wrap:wrap; gap:16px;">
								<label style="flex:1 1 140px; cursor:pointer; display:flex; align-items:center; gap:10px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; transition:all 0.2s;" onmouseenter="this.style.borderColor='#ad5cff';" onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='#e2e8f0';}">
									<input type="radio" name="attended_before" value="yes" required style="width:18px; height:18px; accent-color:#ad5cff; flex-shrink:0;">
									<span style="font-weight:700; color:#0f172a; font-size:14px;">Yes</span>
								</label>
								<label style="flex:1 1 140px; cursor:pointer; display:flex; align-items:center; gap:10px; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; transition:all 0.2s;" onmouseenter="this.style.borderColor='#ad5cff';" onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='#e2e8f0';}">
									<input type="radio" name="attended_before" value="no" required style="width:18px; height:18px; accent-color:#ad5cff; flex-shrink:0;">
									<span style="font-weight:700; color:#0f172a; font-size:14px;">No</span>
								</label>
							</div>
						</div>
					</div>

					<div style="margin-top:24px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
						<label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; margin:0;">
							<input type="checkbox" name="confirm" required style="width:20px; height:20px; margin-top:2px; accent-color:#ad5cff; flex-shrink:0;">
							<span style="font-size:14px; color:#475569; font-weight:600; line-height:1.5;">I understand this is an invite-only event. I will be notified after review if my application is approved to attend AWS re:Invent.</span>
						</label>
					</div>

					<div style="margin-top:24px;">
						<button type="submit" :disabled="isSubmitting" style="display:flex; align-items:center; justify-content:center; gap:12px; width:100%; padding:18px 24px; background:linear-gradient(135deg, #ad5cff, #7000ff); color:#ffffff; font-size:16px; font-weight:900; border:none; border-radius:16px; cursor:pointer; box-shadow:0 10px 30px rgba(173,92,255,0.3); transition:opacity 0.2s;">
							<span x-show="!isSubmitting">Request Invite</span>
							<span x-show="isSubmitting" style="display:none; align-items:center; gap:10px;">
								<span style="width:20px; height:20px; border:3px solid rgba(255,255,255,0.3); border-top-color:#ffffff; border-radius:50%; animation:spin 0.8s linear infinite; display:inline-block;"></span>
								<span>Processing...</span>
							</span>
						</button>
					</div>
				</form>
			</div>

			<?php endif; ?>
		</div>
	</div>

	<script>
		function reinventForm() {
			return { isSubmitting: false };
		}
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_reinvent_form', 'aws_sbg_reinvent_form_shortcode' );

/**
 * Simple test shortcode to verify the file is loaded.
 */
function aws_sbg_reinvent_test_shortcode() {
	return '<p style="padding:10px; background:#dcfce7; border-radius:8px; color:#166534; font-weight:bold;">re:Invent feature file is loaded and shortcodes are working.</p>';
}
add_shortcode( 'aws_reinvent_test', 'aws_sbg_reinvent_test_shortcode' );

/* ========================================================================
 * 4. SUBMISSION HANDLER
 * ======================================================================== */

function aws_sbg_handle_reinvent_registration() {
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_POST['action'] ) || $_POST['action'] !== 'submit_aws_reinvent_registration' ) {
		return;
	}

	if ( ! isset( $_POST['aws_reinvent_registration_nonce'] ) || ! wp_verify_nonce( $_POST['aws_reinvent_registration_nonce'], 'aws_reinvent_registration_action' ) ) {
		wp_die( 'Security check failed. Please refresh and try again.' );
	}

	$full_name  = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$whatsapp   = isset( $_POST['whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp'] ) ) : '';
	$university = isset( $_POST['university'] ) ? sanitize_text_field( wp_unslash( $_POST['university'] ) ) : '';
	$city       = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$linkedin   = isset( $_POST['linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['linkedin'] ) ) : '';
	$attended   = isset( $_POST['attended_before'] ) ? sanitize_text_field( wp_unslash( $_POST['attended_before'] ) ) : '';

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $university ) || empty( $city ) || empty( $linkedin ) || empty( $attended ) ) {
		wp_die( 'Please fill in all required fields.' );
	}

	$reg_number = aws_sbg_generate_reinvent_registration_number();

	$post_id = wp_insert_post( array(
		'post_type'   => 'aws_reinvent_reg',
		'post_title'  => $reg_number . ' - ' . $full_name,
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_die( 'Failed to save registration. Please try again.' );
	}

	update_post_meta( $post_id, 'registration_number', $reg_number );
	update_post_meta( $post_id, 'email', $email );
	update_post_meta( $post_id, 'whatsapp', $whatsapp );
	update_post_meta( $post_id, 'university', $university );
	update_post_meta( $post_id, 'city', $city );
	update_post_meta( $post_id, 'linkedin', $linkedin );
	update_post_meta( $post_id, 'attended_before', $attended );
	update_post_meta( $post_id, 'approval_status', 'pending' );
	update_post_meta( $post_id, 'notified', 'no' );

	$redirect = wp_get_referer() ? wp_get_referer() : home_url();
	$redirect = add_query_arg( array(
		'reinvent_registration' => 'success',
		'reg_id'                => urlencode( $reg_number ),
	), $redirect );

	wp_redirect( $redirect );
	exit;
}
add_action( 'admin_post_nopriv_submit_aws_reinvent_registration', 'aws_sbg_handle_reinvent_registration' );
add_action( 'admin_post_submit_aws_reinvent_registration', 'aws_sbg_handle_reinvent_registration' );

/* ========================================================================
 * 5. ADMIN: SEPARATE REGISTRATIONS TABLE
 * ======================================================================== */

function aws_sbg_register_reinvent_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		're:Invent Registrations',
		'🎉 re:Invent Regs',
		'manage_options',
		'aws-reinvent-registrations',
		'aws_sbg_render_reinvent_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_reinvent_admin_menu', 12 );

function aws_sbg_render_reinvent_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	if ( isset( $_GET['action'], $_GET['reg_id'] ) && $_GET['action'] === 'approve' ) {
		$reg_id = intval( $_GET['reg_id'] );
		update_post_meta( $reg_id, 'approval_status', 'approved' );
	}
	if ( isset( $_GET['action'], $_GET['reg_id'] ) && $_GET['action'] === 'reject' ) {
		$reg_id = intval( $_GET['reg_id'] );
		update_post_meta( $reg_id, 'approval_status', 'rejected' );
	}

	$search   = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
	$status   = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
	$paged    = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$per_page = 50;

	$args = array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$meta_query = array( 'relation' => 'AND' );

	if ( $status ) {
		$meta_query[] = array(
			'key'     => 'approval_status',
			'value'   => $status,
			'compare' => '=',
		);
	}

	if ( $search ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array( 'key' => 'registration_number', 'value' => $search, 'compare' => 'LIKE' ),
			array( 'key' => 'email', 'value' => $search, 'compare' => 'LIKE' ),
			array( 'key' => 'whatsapp', 'value' => $search, 'compare' => 'LIKE' ),
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	$query = new WP_Query( $args );

	$counts = array('total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0);
	$all = new WP_Query( array( 'post_type' => 'aws_reinvent_reg', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
	if ( $all->have_posts() ) {
		foreach ( $all->posts as $reg ) {
			$counts['total']++;
			$s = get_post_meta( $reg->ID, 'approval_status', true );
			if ( $s === 'approved' ) $counts['approved']++;
			elseif ( $s === 'rejected' ) $counts['rejected']++;
			else $counts['pending']++;
		}
	}

	aws_sbg_render_admin_nav_tabs( 'reinvent' );
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

		<div class="flex flex-wrap justify-between items-center gap-4 mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
			<div>
				<h1 class="text-2xl font-black text-slate-900 tracking-tight">🎉 re:Invent Watch & Build Party Registrations</h1>
				<p class="text-slate-500 text-xs mt-1 font-medium">Review, approve, or reject invite requests. Export the list at any time.</p>
			</div>
			<div class="flex items-center gap-2 flex-wrap">
				<form method="GET" class="flex items-center gap-2 flex-wrap">
					<input type="hidden" name="page" value="aws-reinvent-registrations">
					<select name="status_filter" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none">
						<option value="">All Statuses</option>
						<option value="pending" <?php selected( $status, 'pending' ); ?>>⏳ Pending</option>
						<option value="approved" <?php selected( $status, 'approved' ); ?>>✅ Approved</option>
						<option value="rejected" <?php selected( $status, 'rejected' ); ?>>❌ Rejected</option>
					</select>
					<input type="text" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="Reg #, email, phone" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none w-44">
					<button type="submit" class="bg-slate-900 text-white font-bold text-xs px-4 py-2 rounded-xl hover:bg-slate-800 transition-all">Search</button>
				</form>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'export_csv' => '1' ) ) ); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all">⬇ Export CSV</a>
			</div>
		</div>

		<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
			<div class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-xs">
				<div class="text-3xl font-black text-slate-900"><?php echo intval( $counts['total'] ); ?></div>
				<div class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Total</div>
			</div>
			<div class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-xs">
				<div class="text-3xl font-black text-amber-600"><?php echo intval( $counts['pending'] ); ?></div>
				<div class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Pending</div>
			</div>
			<div class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-xs">
				<div class="text-3xl font-black text-emerald-600"><?php echo intval( $counts['approved'] ); ?></div>
				<div class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Approved</div>
			</div>
			<div class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-xs">
				<div class="text-3xl font-black text-red-600"><?php echo intval( $counts['rejected'] ); ?></div>
				<div class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Rejected</div>
			</div>
		</div>

		<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
			<table class="w-full text-left text-xs border-collapse">
				<thead>
					<tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider">
						<th class="py-3.5 px-4">Reg #</th>
						<th class="py-3.5 px-4">Name</th>
						<th class="py-3.5 px-4">Email</th>
						<th class="py-3.5 px-4">WhatsApp</th>
						<th class="py-3.5 px-4">University</th>
						<th class="py-3.5 px-4">City</th>
						<th class="py-3.5 px-4">LinkedIn</th>
						<th class="py-3.5 px-4 text-center">AWS Event Before?</th>
						<th class="py-3.5 px-4 text-center">Status</th>
						<th class="py-3.5 px-4 text-center">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-100">
					<?php if ( ! $query->have_posts() ) : ?>
						<tr>
							<td colspan="10" class="py-8 text-center text-slate-500 font-medium">No registrations found.</td>
						</tr>
					<?php else : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php
							$reg_id      = get_the_ID();
							$raw_title   = get_the_title();
							$name        = preg_replace( '/^reinvent-#\d+\s*-\s*/', '', $raw_title );
							$reg_number  = get_post_meta( $reg_id, 'registration_number', true );
							$email       = get_post_meta( $reg_id, 'email', true );
							$whatsapp    = get_post_meta( $reg_id, 'whatsapp', true );
							$university  = get_post_meta( $reg_id, 'university', true );
							$city        = get_post_meta( $reg_id, 'city', true );
							$linkedin    = get_post_meta( $reg_id, 'linkedin', true );
							$attended    = get_post_meta( $reg_id, 'attended_before', true );
							$approval    = get_post_meta( $reg_id, 'approval_status', true ) ?: 'pending';
							?>
							<tr class="hover:bg-slate-50/80 transition-colors">
								<td class="py-3.5 px-4 font-mono font-bold text-slate-900"><?php echo esc_html( $reg_number ); ?></td>
								<td class="py-3.5 px-4 font-bold text-slate-900"><?php echo esc_html( $name ); ?></td>
								<td class="py-3.5 px-4 font-mono text-slate-600"><?php echo esc_html( $email ); ?></td>
								<td class="py-3.5 px-4 font-mono text-slate-600"><?php echo esc_html( $whatsapp ); ?></td>
								<td class="py-3.5 px-4 text-slate-700"><?php echo esc_html( $university ); ?></td>
								<td class="py-3.5 px-4 text-slate-700"><?php echo esc_html( $city ); ?></td>
								<td class="py-3.5 px-4">
									<?php if ( $linkedin ) : ?>
										<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" class="text-purple-600 font-bold hover:underline">View</a>
									<?php else : ?>
										<span class="text-slate-400">—</span>
									<?php endif; ?>
								</td>
								<td class="py-3.5 px-4 text-center font-bold text-slate-700"><?php echo esc_html( ucfirst( $attended ) ); ?></td>
								<td class="py-3.5 px-4 text-center">
									<?php if ( $approval === 'approved' ) : ?>
										<span class="bg-emerald-100 text-emerald-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">Approved</span>
									<?php elseif ( $approval === 'rejected' ) : ?>
										<span class="bg-red-100 text-red-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">Rejected</span>
									<?php else : ?>
										<span class="bg-amber-100 text-amber-800 font-black px-2.5 py-1 rounded-full text-[10px] uppercase">Pending</span>
									<?php endif; ?>
								</td>
								<td class="py-3.5 px-4 text-center">
									<div class="flex items-center justify-center gap-2">
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'action' => 'approve', 'reg_id' => $reg_id ) ) ); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all">Approve</a>
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'action' => 'reject', 'reg_id' => $reg_id ) ) ); ?>" class="bg-red-600 hover:bg-red-700 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg transition-all">Reject</a>
									</div>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<div class="mt-6 flex justify-center">
				<?php
				echo paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'current'   => $paged,
					'total'     => $query->max_num_pages,
					'prev_text' => '« Prev',
					'next_text' => 'Next »',
				) );
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* ========================================================================
 * 6. CSV EXPORT
 * ======================================================================== */

function aws_sbg_export_reinvent_csv() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_GET['export_csv'] ) ) {
		return;
	}

	$args = array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);

	$query = new WP_Query( $args );
	$filename = 'reinvent-registrations-' . date( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Registration #', 'Name', 'Email', 'WhatsApp', 'University/Org', 'City', 'LinkedIn', 'Attended AWS Event Before', 'Approval Status', 'Registered At' ) );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $reg ) {
			$title      = preg_replace( '/^reinvent-#\d+\s*-\s*/', '', get_the_title( $reg->ID ) );
			$reg_number = get_post_meta( $reg->ID, 'registration_number', true );
			$email      = get_post_meta( $reg->ID, 'email', true );
			$whatsapp   = get_post_meta( $reg->ID, 'whatsapp', true );
			$university = get_post_meta( $reg->ID, 'university', true );
			$city       = get_post_meta( $reg->ID, 'city', true );
			$linkedin   = get_post_meta( $reg->ID, 'linkedin', true );
			$attended   = get_post_meta( $reg->ID, 'attended_before', true );
			$approval   = get_post_meta( $reg->ID, 'approval_status', true ) ?: 'pending';
			$registered = get_the_date( 'Y-m-d H:i:s', $reg->ID );

			fputcsv( $output, array( $reg_number, $title, $email, $whatsapp, $university, $city, $linkedin, ucfirst( $attended ), ucfirst( $approval ), $registered ) );
		}
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'aws_sbg_export_reinvent_csv' );
