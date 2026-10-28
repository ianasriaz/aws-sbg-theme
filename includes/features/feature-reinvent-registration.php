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

function aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location ) {
	$success = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'success';
	$reg_id  = isset( $_GET['reg_id'] ) ? sanitize_text_field( wp_unslash( $_GET['reg_id'] ) ) : '';

	ob_start();
	?>
	<div class="rv-wrap" x-data="reinventForm()">
		<style>
			.rv-wrap { box-sizing:border-box; min-height:100vh; background:#ffffff; color:#0f172a; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
			.rv-wrap *, .rv-wrap *::before, .rv-wrap *::after { box-sizing:border-box; }
			.rv-wrap [x-cloak] { display:none !important; }

			/* Neutralize the theme's dark global styles (style.css sets white headings/text). */
			.rv-wrap h1, .rv-wrap h2, .rv-wrap h3, .rv-wrap h4, .rv-wrap h5, .rv-wrap h6 { color:#0f172a; margin:0; }
			.rv-wrap p, .rv-wrap li { color:#64748b; margin:0; }
			.rv-field input, .rv-choice input, .rv-confirm input { margin:0; }
			.rv-choice input, .rv-confirm input { background:#ffffff; color:#0f172a; }
			.rv-container { max-width:1040px; margin:0 auto; padding:48px 24px 88px; }

			.rv-top { text-align:left; margin-bottom:56px; }
			.rv-top a { color:#0f172a; font-size:13px; font-weight:700; text-decoration:none; }

			.rv-title { font-size:clamp(30px,4.5vw,44px); font-weight:800; letter-spacing:-0.02em; line-height:1.15; margin:0 0 14px; }
			.rv-lede { font-size:16px; color:#64748b; line-height:1.65; max-width:580px; margin:0 auto; }
			.rv-meta { font-size:13px; color:#94a3b8; margin:14px 0 0; }

			.rv-perks-heading { font-size:20px; font-weight:800; margin:64px 0 24px; }
			.rv-perks { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin:0 0 44px; text-align:left; }
			.rv-perk { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:24px 20px; }
			.rv-perk svg { width:26px; height:26px; color:#ad5cff; margin-bottom:16px; display:block; }
			.rv-perk h3 { font-size:15px; font-weight:800; margin:0 0 6px; }
			.rv-perk p { font-size:13.5px; color:#64748b; line-height:1.55; margin:0; }

			.rv-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; border:none; border-radius:12px; padding:14px 32px; font-size:14px; font-weight:800; letter-spacing:0.04em; cursor:pointer; text-decoration:none; transition:background 0.15s ease, border-color 0.15s ease; }
			.rv-btn-primary { background:#ad5cff; color:#ffffff; }
			.rv-btn-primary:hover { background:#9a47ea; }
			.rv-btn-primary:disabled { opacity:0.7; cursor:default; }
			.rv-btn-ghost { background:#ffffff; color:#475569; border:1px solid #e2e8f0; }
			.rv-btn-ghost:hover { border-color:#cbd5e1; }

			.rv-form { max-width:760px; margin:0 auto; }
			.rv-step-label { display:block; font-size:11px; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#ad5cff; margin:0 0 10px; }
			.rv-step-title { font-size:26px; font-weight:800; margin:0 0 8px; }
			.rv-step-sub { font-size:14px; color:#64748b; margin:0 0 30px; }
			.rv-question-label { display:block; font-size:12px; font-weight:700; color:#475569; margin:0 0 12px; }
			.rv-question-label em { font-style:normal; color:#ad5cff; }

			.rv-fields { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
			.rv-field label { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:7px; }
			.rv-field label em { font-style:normal; color:#ad5cff; }
			.rv-field input { width:100%; padding:12px 14px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; font-size:15px; color:#0f172a; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field input:focus { border-color:#ad5cff; outline:none; box-shadow:0 0 0 3px rgba(173,92,255,0.12); }
			.rv-field input::placeholder { color:#a9b4c6; }

			.rv-choices { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
			.rv-choice { display:flex; align-items:center; gap:12px; background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:14px 16px; cursor:pointer; transition:border-color 0.15s ease; }
			.rv-choice:hover { border-color:#cbd5e1; }
			.rv-choice:has(input:checked) { border-color:#ad5cff; background:#faf5ff; }
			.rv-choice input { width:18px; height:18px; accent-color:#ad5cff; cursor:pointer; flex-shrink:0; }
			.rv-choice-title { font-size:14px; font-weight:800; color:#0f172a; display:block; }
			.rv-choice-hint { font-size:12px; color:#64748b; margin-top:2px; display:block; }

			.rv-confirm { display:flex; align-items:flex-start; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-top:22px; cursor:pointer; }
			.rv-confirm input { width:18px; height:18px; margin-top:2px; accent-color:#ad5cff; cursor:pointer; flex-shrink:0; }
			.rv-confirm-text { font-size:13.5px; color:#475569; line-height:1.6; font-weight:500; }

			.rv-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:32px; }

			@keyframes rv-spin { to { transform:rotate(360deg); } }
			.rv-spinner { width:16px; height:16px; border:2px solid rgba(255,255,255,0.4); border-top-color:#ffffff; border-radius:50%; display:inline-block; animation:rv-spin 0.7s linear infinite; }

			.rv-success { min-height:70vh; display:flex; align-items:center; justify-content:center; padding:40px 20px; }
			.rv-success-card { max-width:460px; width:100%; background:#ffffff; border:1px solid #e2e8f0; border-radius:20px; padding:48px 32px; text-align:center; }
			.rv-success-icon { width:64px; height:64px; border-radius:50%; background:#ecfdf5; color:#10b981; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; }
			.rv-success-icon svg { width:28px; height:28px; }
			.rv-code { display:inline-block; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; padding:12px 22px; margin:16px 0 6px; }
			.rv-code-label { font-size:11px; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#64748b; display:block; margin-bottom:4px; }
			.rv-code-value { font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:19px; font-weight:700; color:#0f172a; }

			@media (max-width:900px) {
				.rv-perks { grid-template-columns:repeat(2,1fr); }
			}
			@media (max-width:600px) {
				.rv-container { padding:40px 20px 64px; }
				.rv-perks { grid-template-columns:1fr; }
				.rv-fields { grid-template-columns:1fr; }
				.rv-choices { grid-template-columns:1fr; }
				.rv-actions { flex-direction:column-reverse; }
				.rv-actions .rv-btn { width:100%; }
			}
		</style>

		<?php if ( $success ) : ?>
		<div class="rv-success">
			<div class="rv-success-card">
				<div class="rv-success-icon">
					<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
				</div>
				<h2 style="font-size:24px; font-weight:800; margin:0 0 10px;">You're on the list</h2>
				<p style="font-size:14px; color:#64748b; line-height:1.6; margin:0;">Your RSVP has been received and is under review. We'll notify you by email if your seat is approved.</p>
				<div class="rv-code">
					<span class="rv-code-label">Registration number</span>
					<span class="rv-code-value"><?php echo esc_html( $reg_id ); ?></span>
				</div>
				<p style="margin:18px 0 0;"><a href="<?php echo esc_url( home_url() ); ?>" style="color:#ad5cff; font-weight:700; font-size:14px; text-decoration:none;">Back to home</a></p>
			</div>
		</div>
		<?php else : ?>

		<!-- Step 0: Intro -->
		<section x-show="currentStep === 0">
			<div class="rv-container">
				<header class="rv-top">
					<a href="<?php echo esc_url( home_url() ); ?>">AWS Student Builder Group</a>
				</header>

				<h1 class="rv-title" style="text-align:center;"><?php echo esc_html( $title ); ?></h1>
				<p class="rv-lede" style="text-align:center;"><?php echo esc_html( $subtitle ); ?></p>
				<?php if ( $date || $location ) : ?>
					<p class="rv-meta" style="text-align:center;"><?php echo esc_html( trim( $date . ( ( $date && $location ) ? ' · ' : '' ) . $location, ' ·' ) ); ?></p>
				<?php endif; ?>

				<h2 class="rv-perks-heading" style="text-align:center;">What's waiting for you at re:Invent</h2>
				<div class="rv-perks">
					<div class="rv-perk">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h12v5a4 4 0 01-4 4H8a4 4 0 01-4-4V9z"/><path d="M16 10h1.5a2.5 2.5 0 010 5H16"/></svg>
						<h3>Refreshments</h3>
						<p>Food, drinks, and coffee on the house.</p>
					</div>
					<div class="rv-perk">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
						<h3>Swags</h3>
						<p>Exclusive AWS swag to take home.</p>
					</div>
					<div class="rv-perk">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
						<h3>Networking</h3>
						<p>Meet builders, students, and cloud professionals.</p>
					</div>
					<div class="rv-perk">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
						<h3>Activities</h3>
						<p>Live watch party, quizzes, and hands-on builds.</p>
					</div>
				</div>

				<div style="text-align:center;">
					<button type="button" class="rv-btn rv-btn-primary" @click="nextStep()">RSVP INVITE</button>
				</div>
			</div>
		</section>

		<!-- Steps 1 & 2 -->
		<form class="rv-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" x-show="currentStep > 0" x-cloak @submit="isSubmitting = true">
			<input type="hidden" name="action" value="submit_aws_reinvent_registration">
			<?php wp_nonce_field( 'aws_reinvent_registration_action', 'aws_reinvent_registration_nonce' ); ?>

			<!-- Step 1: Personal details -->
			<section x-show="currentStep === 1">
				<div class="rv-container">
					<span class="rv-step-label">Step 1 of 2</span>
					<h2 class="rv-step-title">Tell us about yourself</h2>
					<p class="rv-step-sub">A few details so we can review your application.</p>

					<div class="rv-fields">
						<div class="rv-field">
							<label>Full Name <em>*</em></label>
							<input type="text" name="full_name" :required="currentStep === 1" placeholder="Muhammad Ali">
						</div>
						<div class="rv-field">
							<label>Email Address <em>*</em></label>
							<input type="email" name="email" :required="currentStep === 1" placeholder="ali@example.com">
						</div>
						<div class="rv-field">
							<label>WhatsApp Number <em>*</em></label>
							<input type="tel" name="whatsapp" :required="currentStep === 1" placeholder="03000000000">
						</div>
						<div class="rv-field">
							<label>University / Organization <em>*</em></label>
							<input type="text" name="university" :required="currentStep === 1" placeholder="Air University Islamabad">
						</div>
						<div class="rv-field">
							<label>City <em>*</em></label>
							<input type="text" name="city" :required="currentStep === 1" placeholder="Islamabad">
						</div>
						<div class="rv-field">
							<label>LinkedIn Profile URL <em>*</em></label>
							<input type="url" name="linkedin" :required="currentStep === 1" placeholder="https://linkedin.com/in/username">
						</div>
					</div>

					<div class="rv-actions">
						<button type="button" class="rv-btn rv-btn-ghost" @click="prevStep()">Back</button>
						<button type="button" class="rv-btn rv-btn-primary" @click="$el.form.reportValidity() && nextStep()">Continue</button>
					</div>
				</div>
			</section>

			<!-- Step 2: Finalize -->
			<section x-show="currentStep === 2">
				<div class="rv-container">
					<span class="rv-step-label">Step 2 of 2</span>
					<h2 class="rv-step-title">Almost done</h2>
					<p class="rv-step-sub">One last question and you're set.</p>

					<div style="margin-bottom:22px;">
						<span class="rv-question-label">Have you attended any AWS community event before? <em>*</em></span>
						<div class="rv-choices">
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="yes" :required="currentStep === 2">
								<span><span class="rv-choice-title">Yes</span><span class="rv-choice-hint">I've joined before</span></span>
							</label>
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="no" :required="currentStep === 2">
								<span><span class="rv-choice-title">No</span><span class="rv-choice-hint">First time</span></span>
							</label>
						</div>
					</div>

					<label class="rv-confirm">
						<input type="checkbox" name="confirm" :required="currentStep === 2">
						<span class="rv-confirm-text">I understand this is an invite-only event. I will be notified after review if my application is approved to attend AWS re:Invent.</span>
					</label>

					<div class="rv-actions">
						<button type="button" class="rv-btn rv-btn-ghost" @click="prevStep()">Back</button>
						<button type="submit" class="rv-btn rv-btn-primary" :disabled="isSubmitting">
							<span x-show="!isSubmitting">RSVP INVITE</span>
							<span x-show="isSubmitting" x-cloak style="gap:10px;"><span class="rv-spinner"></span> Submitting...</span>
						</button>
					</div>
				</div>
			</section>
		</form>

		<?php endif; ?>
	</div>

	<script>
		function reinventForm() {
			return {
				currentStep: 0,
				isSubmitting: false,
				nextStep() { this.currentStep++; },
				prevStep() { if ( this.currentStep > 0 ) { this.currentStep--; } },
			};
		}
	</script>
	<?php
	return ob_get_clean();
}

function aws_sbg_reinvent_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'title'    => 'AWS re:Invent Watch & Build Party Islamabad 2026',
		'subtitle' => 'An invite-only evening for builders, dreamers, and cloud enthusiasts. Apply below and we will notify you once your seat is approved.',
		'date'     => '',
		'location' => 'Islamabad, Pakistan',
	), $atts, 'aws_reinvent_form' );

	return aws_sbg_render_reinvent_form( $atts['title'], $atts['subtitle'], $atts['date'], $atts['location'] );
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

// Admin page is registered in admin-dashboard.php to keep the suite menu organized.

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
