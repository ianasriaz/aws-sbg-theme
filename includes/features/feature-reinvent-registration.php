<?php
/**
 * AWS re:Invent Watch & Build Party — Dedicated Registration Form & Admin Manager
 *
 * Free, first come first served event registration for "AWS re:Invent Watch & Build Party Islamabad 2026".
 * Registration numbers are allocated as RIV-01, RIV-02, etc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AWS_REINVENT_WHATSAPP_URL' ) ) {
	define( 'AWS_REINVENT_WHATSAPP_URL', 'https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI' );
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
 * 2. REGISTRATION NUMBER ALLOCATION (atomic, approval-time)
 * ======================================================================== */

function aws_sbg_reinvent_numbers_init_db() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table = $wpdb->prefix . 'aws_reinvent_numbers';
	dbDelta( "CREATE TABLE $table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) {$wpdb->get_charset_collate()};" );

	if ( ! get_option( 'aws_reinvent_numbers_seeded' ) ) {
		$legacy_next = (int) get_option( 'aws_reinvent_next_number', 1 );
		$count       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		if ( 0 === $count && $legacy_next > 1 ) {
			$wpdb->query( "ALTER TABLE $table AUTO_INCREMENT = " . intval( $legacy_next ) );
		}
		update_option( 'aws_reinvent_numbers_seeded', 1 );
	}
}
add_action( 'after_switch_theme', 'aws_sbg_reinvent_numbers_init_db' );
add_action( 'admin_init', 'aws_sbg_reinvent_numbers_init_db', 10 );

/**
 * Synchronize the numbers tracking table with actual registrations in postmeta.
 * Ensures that if test registrations were deleted, the counter resets back to 1
 * or to max_existing + 1, eliminating phantom gaps.
 *
 * @return int The highest existing registration sequence number in postmeta (0 if none).
 */
function aws_sbg_resync_reinvent_numbers_table() {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_reinvent_numbers';

	// Calculate highest registration number actually existing in active postmeta
	$max_existing = 0;
	$existing = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'registration_number' AND meta_value LIKE 'RIV-%'" );
	if ( ! empty( $existing ) ) {
		foreach ( $existing as $val ) {
			if ( preg_match( '/RIV-(\d+)/i', $val, $m ) ) {
				$max_existing = max( $max_existing, intval( $m[1] ) );
			}
		}
	}

	if ( 0 === $max_existing ) {
		// Clean slate: completely reset the numbers table so next insert starts at 1
		$wpdb->query( "TRUNCATE TABLE $table" );
		delete_option( 'aws_reinvent_next_number' );
		delete_option( 'aws_reinvent_numbers_seeded' );
		update_option( 'aws_reinvent_numbers_seeded', 1 );
	} else {
		// Test entries were deleted: purge phantom rows higher than max_existing and reset auto-increment
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id > %d", $max_existing ) );
		$wpdb->query( "ALTER TABLE $table AUTO_INCREMENT = " . intval( $max_existing + 1 ) );
	}

	return $max_existing;
}

function aws_sbg_allocate_reinvent_registration_number() {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_reinvent_numbers';

	// Auto-resync to catch any test entries deleted before allocation
	$max_existing = aws_sbg_resync_reinvent_numbers_table();

	$wpdb->insert( $table, array( 'created_at' => current_time( 'mysql' ) ), array( '%s' ) );
	$num = (int) $wpdb->insert_id;

	if ( $num <= 0 || $num <= $max_existing ) {
		$num = $max_existing + 1;
	}

	return 'RIV-' . str_pad( $num, 2, '0', STR_PAD_LEFT );
}

/* ========================================================================
 * 2.5 PUBLIC VISIBILITY HELPER FUNCTIONS
 * ======================================================================== */

/**
 * Check whether the public re:Invent registration form is hidden from visitors.
 *
 * @return bool True if hidden/closed, false if visible/open.
 */
function aws_sbg_is_reinvent_registration_hidden() {
	return '1' === (string) get_option( 'aws_reinvent_form_hidden', '0' );
}

/**
 * Get the custom or fallback closed notice message shown when registration is hidden.
 *
 * @return string
 */
function aws_sbg_get_reinvent_closed_message() {
	$msg = trim( (string) get_option( 'aws_reinvent_closed_message', '' ) );
	if ( empty( $msg ) ) {
		$msg = 'Public registrations for AWS re:Invent Watch & Build Party Islamabad 2026 are currently closed. Follow our official WhatsApp community for announcements and future ticket releases.';
	}
	return $msg;
}

/* ========================================================================
 * 3. FRONT-END FORM SHORTCODE
 * ======================================================================== */

function aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location ) {
	$success        = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'success';
	$exists         = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'exists';
	$is_hidden      = aws_sbg_is_reinvent_registration_hidden();
	$closed_message = aws_sbg_get_reinvent_closed_message();
	$is_admin       = current_user_can( 'manage_options' );
	$fonts          = get_template_directory_uri() . '/assets/design-kit/fonts';
	$skyline        = get_template_directory_uri() . '/assets/design-kit/Islamabad Landmark Footer.png';
	$aws_logo       = get_template_directory_uri() . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo       = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : get_template_directory_uri() . '/assets/images/icons/SBG-Logo-White-Transparent.png';

	ob_start();
	?>
	<div class="rv-wrap" x-data="reinventForm()">
		<style>
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:500; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Rg.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }

			.rv-wrap { position:relative; min-height:100vh; background:#06040C; color:#FFFFFF; font-family:'Amazon Ember Display',system-ui,-apple-system,'Segoe UI',sans-serif; overflow:hidden; }
			.rv-wrap *, .rv-wrap *::before, .rv-wrap *::after { box-sizing:border-box; }
			.rv-wrap [x-cloak] { display:none !important; }
			.rv-wrap :focus-visible { outline:2px solid #00DFD8; outline-offset:3px; border-radius:4px; }

			/* Diffused purple-to-cyan mesh, matching the kit's canvas */
			.rv-mesh { position:absolute; inset:0; z-index:0; pointer-events:none; background:
				radial-gradient(55% 42% at 12% -5%, rgba(107,69,232,0.5), transparent 68%),
				radial-gradient(48% 38% at 88% 8%, rgba(46,99,224,0.38), transparent 70%),
				radial-gradient(60% 46% at 82% 92%, rgba(10,141,192,0.34), transparent 70%),
				radial-gradient(50% 40% at 8% 96%, rgba(135,73,220,0.30), transparent 72%); }

			/* Theme style.css resets applied to this surface */
			.rv-wrap h1, .rv-wrap h2, .rv-wrap h3, .rv-wrap h4 { color:#FFFFFF; margin:0; }
			.rv-wrap p { margin:0; }
			.rv-field input, .rv-choice input, .rv-confirm input { margin:0; }

			.rv-topbar { position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px clamp(20px,5vw,48px); border-bottom:1px solid rgba(255,255,255,0.08); }
			.rv-topbar a { display:inline-flex; align-items:center; }
			.rv-logo-aws { height:30px; width:auto; display:block; }
			.rv-logo-sbg { height:44px; width:auto; display:block; }

			/* Step 0 - intro */
			.rv-intro { position:relative; z-index:1; display:flex; align-items:center; padding:clamp(48px,9vh,110px) clamp(20px,6vw,72px) clamp(180px,26vh,260px); min-height:82vh; }
			.rv-skyline { position:absolute; left:0; bottom:0; width:100%; height:auto; opacity:0.42; pointer-events:none; user-select:none; z-index:0; }
			.rv-intro-inner { position:relative; z-index:1; max-width:700px; }
			.rv-kicker { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.22em; text-transform:uppercase; color:#00DFD8; margin:0 0 26px; }
			.rv-title { font-weight:700; font-size:clamp(34px,5.2vw,58px); line-height:1.06; letter-spacing:-0.02em; margin:0 0 30px; }
			.rv-lede { font-size:17px; line-height:1.75; color:rgba(255,255,255,0.66); max-width:52ch; margin:0 0 16px; }
			.rv-lede-cta { font-size:16px; line-height:1.7; font-weight:700; color:rgba(255,255,255,0.66); max-width:52ch; margin:0; }
			.rv-perks { display:grid; grid-template-columns:1fr 1fr; gap:0 clamp(28px,5vw,64px); margin:clamp(48px,8vh,68px) 0 clamp(40px,6vh,56px); }
			.rv-perk { border-top:1px solid rgba(255,255,255,0.14); padding:18px 0 24px; }
			.rv-perk-idx { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.15em; color:#00DFD8; display:block; margin:0 0 10px; }
			.rv-perk h3 { font-size:17px; font-weight:500; margin:0 0 6px; display:flex; align-items:center; gap:9px; }
			.rv-perk h3 svg { width:18px; height:18px; color:#00DFD8; flex-shrink:0; }
			.rv-perk p { font-size:14px; line-height:1.6; color:rgba(255,255,255,0.56); }

			.rv-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; border:0; border-radius:10px; padding:16px 38px; font-family:'Amazon Ember Mono',monospace; font-size:13px; font-weight:700; letter-spacing:0.1em; cursor:pointer; text-decoration:none; transition:background 0.18s ease, border-color 0.18s ease, transform 0.18s ease; }
			.rv-btn-primary { background:#00DFD8; color:#06040C; }
			.rv-btn-primary:hover { background:#40E7E2; transform:translateY(-1px); }
			.rv-btn-primary:disabled { opacity:0.65; cursor:default; transform:none; }
			.rv-btn-ghost { background:transparent; color:rgba(255,255,255,0.75); border:1px solid rgba(255,255,255,0.22); padding:15px 32px; }
			.rv-btn-ghost:hover { border-color:rgba(255,255,255,0.5); color:#FFFFFF; }

			/* Steps 1 & 2 */
			.rv-stage { position:relative; z-index:1; padding:clamp(48px,10vh,110px) clamp(20px,6vw,72px) clamp(72px,12vh,120px); }
			.rv-stage-inner { max-width:640px; margin:0 auto; }
			.rv-step-label { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#00DFD8; display:inline-block; }
			.rv-step-title { font-weight:700; font-size:clamp(26px,3.4vw,34px); line-height:1.2; letter-spacing:-0.01em; margin:0 0 clamp(36px,5vh,48px) 0; }
			.rv-progress { height:4px; background:rgba(255,255,255,0.1); border-radius:99px; overflow:hidden; margin:14px 0 28px; }
			.rv-progress-fill { height:100%; background:#00DFD8; border-radius:99px; transition:width 0.45s cubic-bezier(0.22,0.61,0.36,1); }

			.rv-fields { display:grid; grid-template-columns:1fr 1fr; gap:26px 20px; }
			.rv-field label { display:block; font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.68); margin-bottom:10px; line-height:1.45; }
			.rv-field label em, .rv-question-label em { font-style:normal; color:#00DFD8; }
			.rv-field input, .rv-field select { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field input:focus, .rv-field select:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rv-field input::placeholder { color:rgba(255,255,255,0.32); }
			.rv-field select { appearance:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.6)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; padding-right:40px; cursor:pointer; }
			.rv-field select:invalid, .rv-field select option[value=""] { color:rgba(255,255,255,0.38); }
			.rv-field select option { background:#0d1322; color:#FFFFFF; }
			.rv-field textarea { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; line-height:1.65; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; resize:vertical; min-height:130px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field textarea:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rv-field textarea::placeholder { color:rgba(255,255,255,0.32); }

			/* Info tooltip (hover on desktop, tap/focus on mobile) */
			.rv-field { position:relative; }
			.rv-has-tip { position:relative; }
			.rv-tip-mark { display:inline-flex; width:15px; height:15px; margin-left:7px; color:rgba(255,255,255,0.5); vertical-align:middle; cursor:help; transition:color 0.15s ease; }
			.rv-tip-mark svg { width:100%; height:100%; display:block; }
			.rv-tip-mark:hover, .rv-tip-mark:focus { color:#00DFD8; outline:none; }
			.rv-tip-bubble { position:absolute; left:0; top:calc(100% + 8px); z-index:30; width:min(240px, 100%); padding:10px 13px; background:#0d1322; border:1px solid rgba(255,255,255,0.16); border-radius:10px; font-family:'Amazon Ember Display',system-ui,sans-serif; font-size:12.5px; font-weight:400; line-height:1.55; color:rgba(255,255,255,0.8); text-transform:none; letter-spacing:normal; text-align:left; box-shadow:0 10px 30px rgba(0,0,0,0.4); opacity:0; visibility:hidden; transform:translateY(-4px); transition:opacity 0.15s ease, transform 0.15s ease; pointer-events:none; }
			.rv-has-tip:hover .rv-tip-bubble, .rv-field:focus-within .rv-tip-bubble { opacity:1; visibility:visible; transform:none; }

			.rv-choices { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
			.rv-choice { display:flex; align-items:center; gap:12px; padding:13px 16px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.14); border-radius:12px; cursor:pointer; transition:border-color 0.15s ease, background 0.15s ease; }
			.rv-choice:hover { border-color:rgba(255,255,255,0.4); }
			.rv-choice:has(input:checked) { border-color:#00DFD8; background:rgba(0,223,216,0.08); }
			.rv-choice input { width:18px; height:18px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; }
			.rv-choice-title { display:block; font-size:14px; font-weight:400; color:#FFFFFF; }
			.rv-choices-3 { grid-template-columns:repeat(3,1fr); }

			.rv-commit { display:flex; align-items:flex-start; gap:14px; padding:20px 0; border-top:1px solid rgba(255,255,255,0.12); cursor:pointer; }
			.rv-commit input { width:18px; height:18px; margin-top:3px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; }
			.rv-commit-text { font-size:14.5px; line-height:1.6; color:rgba(255,255,255,0.75); }
			.rv-commit-sub { display:block; font-size:12.5px; line-height:1.55; color:rgba(255,255,255,0.42); margin-top:4px; }

			.rv-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:44px; }

			@keyframes rv-spin { to { transform:rotate(360deg); } }
			.rv-spinner { width:16px; height:16px; border:2px solid rgba(6,4,12,0.3); border-top-color:#06040C; border-radius:50%; display:inline-block; animation:rv-spin 0.7s linear infinite; }

			/* Success */
			.rv-success { position:relative; z-index:1; min-height:calc(100vh - 64px); display:flex; align-items:center; justify-content:center; padding:48px 24px; }
			.rv-success-card { max-width:460px; width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.12); border-radius:20px; padding:48px 32px; text-align:center; }
			.rv-success-icon { width:60px; height:60px; border:2px solid #00DFD8; border-radius:50%; color:#00DFD8; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
			.rv-success-icon svg { width:26px; height:26px; }
			.rv-code { display:inline-block; border:1px dashed rgba(0,223,216,0.4); border-radius:12px; padding:14px 24px; margin:20px 0 4px; }
			.rv-code-label { font-family:'Amazon Ember Mono',monospace; font-size:10px; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:rgba(255,255,255,0.5); display:block; margin-bottom:6px; }
			.rv-code-value { font-family:'Amazon Ember Mono',monospace; font-size:18px; font-weight:700; color:#00DFD8; letter-spacing:0.04em; }
			.rv-success-link { display:inline-block; font-family:'Amazon Ember Mono',monospace; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#00DFD8; text-decoration:none; margin-top:22px; border-bottom:1px solid transparent; }
			.rv-success-link:hover { border-bottom-color:#00DFD8; }

			@media (prefers-reduced-motion:no-preference) {
				@keyframes rvRise { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }
				.rv-intro-inner, .rv-stage-inner, .rv-success-card { animation:rvRise 0.5s cubic-bezier(0.22,0.61,0.36,1) both; }
			}

			@media (max-width:700px) {
				.rv-perks { grid-template-columns:1fr; }
			}
			@media (max-width:600px) {
				.rv-logo-aws { height:22px; }
				.rv-logo-sbg { height:34px; }
				.rv-intro { padding-bottom:190px; min-height:auto; }
				.rv-fields, .rv-choices { grid-template-columns:1fr; }
				.rv-actions { flex-direction:column-reverse; }
				.rv-actions .rv-btn { width:100%; }
			}
		</style>

		<div class="rv-mesh" aria-hidden="true"></div>

		<header class="rv-topbar">
			<a href="<?php echo esc_url( home_url() ); ?>">
				<img class="rv-logo-aws" src="<?php echo esc_url( $aws_logo ); ?>" alt="AWS">
			</a>
			<img class="rv-logo-sbg" src="<?php echo esc_url( $sbg_logo ); ?>" alt="AWS Student Builder Group">
		</header>

		<?php if ( $is_hidden && $is_admin ) : ?>
		<div style="position:relative;z-index:3;margin:12px clamp(20px,5vw,48px) 0;padding:12px 18px;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.35);border-radius:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;font-size:13px;color:#fef3c7;">
			<div style="display:flex;align-items:center;gap:10px;">
				<span style="font-weight:700;color:#f59e0b;text-transform:uppercase;font-size:11px;letter-spacing:0.06em;background:rgba(245,158,11,0.2);padding:3px 8px;border-radius:4px;">Admin Notice</span>
				<span>Public registration form is currently <strong>HIDDEN from the public</strong>. Non-admin visitors cannot register.</span>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-reinvent-registrations' ) ); ?>" style="font-family:'Amazon Ember Mono',monospace;font-size:11px;font-weight:700;text-decoration:none;padding:7px 14px;background:#f59e0b;color:#06040c;border-radius:6px;white-space:nowrap;">Open Admin Dashboard &rarr;</a>
		</div>
		<?php endif; ?>

		<?php if ( $success ) : ?>
		<div class="rv-success" x-data="reinventForm()" style="display:flex;">
			<div class="rv-success-card">
				<div class="rv-success-icon" style="animation:none;">
					<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
				</div>
				<h2 style="font-weight:700; font-size:26px; margin:0 0 10px; letter-spacing:0.02em;">Application Received</h2>
				<p style="font-size:15px; line-height:1.65; color:rgba(255,255,255,0.62);">Thanks for registering for AWS re:Invent Watch &amp; Build Party Islamabad 2026. Your event pass and details will be shared by email.</p>
				<div style="margin-top:26px;">
					<a href="<?php echo esc_url( AWS_REINVENT_WHATSAPP_URL ); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; justify-content:center; gap:10px; background:#00DFD8; color:#06040C; font-family:'Amazon Ember Mono',monospace; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; text-decoration:none; padding:14px 26px; border-radius:10px;">Join WhatsApp community</a>
					<p style="font-size:12.5px; color:rgba(255,255,255,0.45); margin:10px 0 0; line-height:1.6;">For updates &amp; announcements.</p>
				</div>
				<p style="margin:22px 0 0;"><a class="rv-success-link" href="<?php echo esc_url( home_url() ); ?>">Back to home</a></p>
			</div>
		</div>
		<?php else : ?>

		<?php if ( $exists ) : ?>
		<div style="position:relative;z-index:2;padding:16px 20px;border:1px solid rgba(0,223,216,0.4);border-radius:10px;background:rgba(0,223,216,0.08);color:#FFFFFF;font-size:14px;line-height:1.6;margin-bottom:20px;">You're already registered with this email. Your event pass and details will be shared by email.</div>
		<?php endif; ?>

		<!-- Step 0: Intro -->
		<section class="rv-intro" x-show="currentStep === 0">
			<img class="rv-skyline" src="<?php echo esc_url( $skyline ); ?>" alt="" aria-hidden="true">
			<div class="rv-intro-inner">
				<p class="rv-kicker">AWS Community Islamabad is bringing</p>
				<h1 class="rv-title"><?php echo esc_html( $title ); ?></h1>
				<p class="rv-lede"><?php echo esc_html( $subtitle ); ?></p>
				<?php if ( ! $is_hidden ) : ?>
					<p class="rv-lede-cta">Registration is free and seats are limited. Register now to secure your spot.</p>
				<?php endif; ?>

				<div class="rv-perks">
					<div class="rv-perk">
						<span class="rv-perk-idx">01</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.83z"/><path d="M7 7h.01"/></svg>Free Registration</h3>
						<p>First come, first served. Seats are limited, so register early.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">02</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h12v5a4 4 0 01-4 4H8a4 4 0 01-4-4V9z"/><path d="M16 10h1.5a2.5 2.5 0 010 5H16"/></svg>Snacks, Food &amp; Drinks</h3>
						<p>Enjoy complimentary snacks, food, and beverages.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">03</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>AWS Swag</h3>
						<p>Swag and giveaways, subject to availability and participation criteria.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">04</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>Networking</h3>
						<p>Connect with builders, students, and cloud professionals.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">05</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><path d="M12 19v4"/><path d="M8 23h8"/></svg>Expert Sessions</h3>
						<p>Watch streamed re:Invent sessions from Las Vegas and attend in-person talks.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">06</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>Activities</h3>
						<p>Join interactive challenges, hands-on builds, and community experiences.</p>
					</div>
				</div>

				<?php if ( $is_hidden ) : ?>
					<div class="rv-closed-box" style="margin-top:32px;padding:24px 28px;border-radius:14px;border:1px solid rgba(255,255,255,0.16);background:rgba(255,255,255,0.04);max-width:580px;box-shadow:0 12px 30px rgba(0,0,0,0.3);">
						<div style="display:inline-flex;align-items:center;gap:8px;font-family:'Amazon Ember Mono',monospace;font-size:11px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#f59e0b;margin-bottom:12px;">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
							Registrations Closed
						</div>
						<p style="font-size:15px;line-height:1.65;color:rgba(255,255,255,0.76);margin:0 0 20px 0;">
							<?php echo esc_html( $closed_message ); ?>
						</p>
						<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
							<a href="<?php echo esc_url( AWS_REINVENT_WHATSAPP_URL ); ?>" target="_blank" rel="noopener" class="rv-btn rv-btn-primary" style="padding:13px 26px;font-size:12px;">
								Join WhatsApp Community
							</a>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rv-btn rv-btn-ghost" style="padding:13px 22px;font-size:12px;">
								Back to Home
							</a>
						</div>
					</div>
				<?php else : ?>
					<button type="button" class="rv-btn rv-btn-primary" @click="nextStep()">REGISTER NOW</button>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( ! $is_hidden ) : ?>
		<!-- Steps 1 & 2 -->
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" x-show="currentStep > 0" x-cloak @submit="handleSubmit($event)">
			<input type="hidden" name="action" value="submit_aws_reinvent_registration">
			<?php wp_nonce_field( 'aws_reinvent_registration_action', 'aws_reinvent_registration_nonce' ); ?>

			<!-- Step 1: Personal details -->
			<section class="rv-stage" x-show="currentStep === 1">
				<div class="rv-stage-inner">
					<span class="rv-step-label">Step 1 of 2</span>
					<div class="rv-progress" aria-hidden="true"><div class="rv-progress-fill" :style="'width:' + ( ( currentStep / 2 ) * 100 ) + '%'"></div></div>
					<h2 class="rv-step-title">Tell us about yourself</h2>

					<div class="rv-fields">
						<div class="rv-field">
							<label for="rv-full-name">Full Name <em>*</em></label>
							<input id="rv-full-name" type="text" name="full_name" :required="currentStep === 1" placeholder="Your full name">
						</div>
						<div class="rv-field">
							<label for="rv-email">Email Address <em>*</em></label>
							<input id="rv-email" type="email" name="email" :required="currentStep === 1" placeholder="youremail@domain.com">
						</div>
						<div class="rv-field">
							<label for="rv-whatsapp">WhatsApp Number <em>*</em></label>
							<input id="rv-whatsapp" type="tel" name="whatsapp" :required="currentStep === 1" placeholder="0300-0000000">
						</div>
						<div class="rv-field">
							<label for="rv-cnic">CNIC (For check-in) <em>*</em></label>
							<input id="rv-cnic" type="text" name="cnic" inputmode="numeric" :required="currentStep === 1" pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" maxlength="15" title="Format: 00000-0000000-0" placeholder="00000-0000000-0" @input="formatCnic($event)">
						</div>
						<div class="rv-field">
							<label for="rv-audience-type">I am a <em>*</em></label>
							<select id="rv-audience-type" name="audience_type" x-model="audienceType" :required="currentStep === 1">
								<option value="" disabled selected>Select an option</option>
								<option value="student">Student</option>
								<option value="professional">Professional</option>
								<option value="job_seeker">Job Seeker</option>
							</select>
						</div>
						<div class="rv-field">
							<label for="rv-affiliation">Institute/Company <em>*</em></label>
							<input id="rv-affiliation" type="text" name="affiliation" :required="currentStep === 1" placeholder="Air University Islamabad/Nayatel/Jazz">
						</div>
						<div class="rv-field">
							<label for="rv-city">City (Travelling from on event day) <em>*</em></label>
							<input id="rv-city" type="text" name="city" :required="currentStep === 1" placeholder="Your city">
						</div>
						<div class="rv-field">
							<label for="rv-linkedin">LinkedIn Profile URL <em>*</em></label>
							<input id="rv-linkedin" type="url" name="linkedin" :required="currentStep === 1" placeholder="https://linkedin.com/in/username">
						</div>
						<div class="rv-field" style="grid-column:1/-1; margin-top:8px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.1);">
							<label>Bringing a vehicle? <span style="font-weight:400;">(optional)</span></label>
							<p style="margin:2px 0 12px; font-size:12.5px; line-height:1.55; color:rgba(255,255,255,0.5);">Parking on campus is very limited. We encourage using public transport or ride-hailing (InDrive, Yango, Bykea). If you're driving, you can park at F-9 Park Gate 3 (Khyber Gate), which is nearest to the venue.</p>
							<div class="rv-choices" role="radiogroup" aria-label="Vehicle">
								<label class="rv-choice">
									<input type="radio" name="has_vehicle" value="no" x-model="hasVehicle">
									<span class="rv-choice-title">No</span>
								</label>
								<label class="rv-choice">
									<input type="radio" name="has_vehicle" value="yes" x-model="hasVehicle">
									<span class="rv-choice-title">Yes</span>
								</label>
							</div>
						</div>
						<div class="rv-field" style="grid-column:1/-1;" x-show="hasVehicle === 'yes'" x-cloak>
							<label>Vehicle type</label>
							<div class="rv-choices" role="radiogroup" aria-label="Vehicle type">
								<label class="rv-choice">
									<input type="radio" name="vehicle_type" value="bike" :required="currentStep === 1 && hasVehicle === 'yes'">
									<span class="rv-choice-title">Bike</span>
								</label>
								<label class="rv-choice">
									<input type="radio" name="vehicle_type" value="car" :required="currentStep === 1 && hasVehicle === 'yes'">
									<span class="rv-choice-title">Car</span>
								</label>
							</div>
						</div>
						<div class="rv-field" style="grid-column:1/-1;" x-show="hasVehicle === 'yes'" x-cloak>
							<label for="rv-vehicle-number">Vehicle number</label>
							<input id="rv-vehicle-number" type="text" name="vehicle_number" placeholder="ABC-123" :required="currentStep === 1 && hasVehicle === 'yes'">
						</div>
					</div>

					<div class="rv-actions">
						<button type="button" class="rv-btn rv-btn-ghost" @click="prevStep()">Back</button>
						<button type="button" class="rv-btn rv-btn-primary" @click="$el.form.reportValidity() && nextStep()">Continue</button>
					</div>
				</div>
			</section>

			<!-- Step 2: Finalize -->
			<section class="rv-stage" x-show="currentStep === 2">
				<div class="rv-stage-inner">
					<span class="rv-step-label">Step 2 of 2</span>
					<div class="rv-progress" aria-hidden="true"><div class="rv-progress-fill" :style="'width:' + ( ( currentStep / 2 ) * 100 ) + '%'"></div></div>
					<h2 class="rv-step-title">Almost done</h2>

					<div class="rv-field" style="margin-bottom:40px;">
						<label id="rv-attended-label">Have you attended AWS or any community event before? <em>*</em></label>
						<p style="margin:2px 0 12px; font-size:12.5px; line-height:1.55; color:rgba(255,255,255,0.5);">First time? You're welcome too.</p>
						<div class="rv-choices" role="radiogroup" aria-labelledby="rv-attended-label">
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="yes" x-model="attendedBefore" :required="currentStep === 2">
								<span class="rv-choice-title">Yes</span>
							</label>
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="no" x-model="attendedBefore" :required="currentStep === 2">
								<span class="rv-choice-title">No</span>
							</label>
						</div>
					</div>

					<div class="rv-field" style="margin-bottom:38px;" x-show="attendedBefore === 'yes'" x-cloak>
						<div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:10px;">
							<label for="rv-why" style="margin-bottom:0;">Tell us about your last attended AWS or any community event (250 CHAR LIMIT) <em>*</em></label>
							<span style="font-family:'Amazon Ember Mono',monospace; font-size:11px; color:rgba(255,255,255,0.45);" x-text="(250 - (whyInviteText ? whyInviteText.length : 0)) + ' left'"></span>
						</div>
						<textarea id="rv-why" name="why_invite" rows="5" maxlength="250" x-model="whyInviteText" :required="currentStep === 2 && attendedBefore === 'yes'" :disabled="attendedBefore !== 'yes'" placeholder="Tell us about the event, what you learned, or your community participation..."></textarea>
					</div>

					<label class="rv-commit">
						<input type="checkbox" name="will_attend" value="yes" :required="currentStep === 2">
						<span class="rv-commit-text">I will attend the event.<span class="rv-commit-sub">Seats are limited and resources are planned per attendee, so a no-show takes a seat from another builder.</span></span>
					</label>
					<label class="rv-commit">
						<input type="checkbox" name="parking_policy" value="yes" :required="currentStep === 2">
						<span class="rv-commit-text">I understand campus parking is very limited.<span class="rv-commit-sub" style="color:rgba(255,255,255,0.45);">Public transport or ride-hailing is recommended. Drivers can park at F-9 Park Gate 3 (Khyber Gate), nearest to the venue.</span></span>
					</label>

					<div class="rv-actions" style="margin-top:44px;">
						<button type="button" class="rv-btn rv-btn-ghost" @click="prevStep()">Back</button>
						<button type="submit" class="rv-btn rv-btn-primary" :disabled="isSubmitting">
							<span x-show="!isSubmitting">SUBMIT</span>
							<span x-show="isSubmitting" x-cloak><span class="rv-spinner"></span> Submitting</span>
						</button>
					</div>
				</div>
			</section>
		</form>
		<?php endif; ?>

		<?php endif; ?>
	</div>

	<script>
		function reinventForm() {
			return {
				currentStep: 0,
				audienceType: '',
				whyInviteText: '',
				attendedBefore: '',
				hasVehicle: 'no',
				isSubmitting: false,
				handleSubmit(e) {
					if (this.isSubmitting) {
						e.preventDefault();
						return false;
					}
					this.isSubmitting = true;
					return true;
				},
				nextStep() { this.currentStep++; },
				prevStep() { if ( this.currentStep > 0 ) { this.currentStep--; } },
				formatCnic(e) {
					let val = e.target.value.replace(/[^0-9]/g, '');
					if (val.length > 13) val = val.substring(0, 13);
					let formatted = '';
					if (val.length > 5) {
						formatted = val.substring(0, 5) + '-';
						if (val.length > 12) {
							formatted += val.substring(5, 12) + '-' + val.substring(12);
						} else {
							formatted += val.substring(5);
						}
					} else {
						formatted = val;
					}
					e.target.value = formatted;
				},
			};
		}
	</script>
	<?php
	return ob_get_clean();
}

function aws_sbg_reinvent_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'title'    => 'AWS re:Invent Watch & Build Party Islamabad 2026',
		'subtitle' => 'Experience AWS re:Invent in Islamabad.',
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

	// Check if public registration is hidden/closed
	if ( aws_sbg_is_reinvent_registration_hidden() && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( aws_sbg_get_reinvent_closed_message() ), 'Registration Closed', array( 'response' => 403, 'back_link' => true ) );
	}

	$full_name  = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$whatsapp   = isset( $_POST['whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp'] ) ) : '';
	$cnic       = isset( $_POST['cnic'] ) ? sanitize_text_field( wp_unslash( $_POST['cnic'] ) ) : '';
	$cnic_digits = preg_replace( '/[^0-9]/', '', $cnic );
	if ( 13 === strlen( $cnic_digits ) ) {
		$cnic = substr( $cnic_digits, 0, 5 ) . '-' . substr( $cnic_digits, 5, 7 ) . '-' . substr( $cnic_digits, 12, 1 );
	}
	$university = isset( $_POST['affiliation'] ) ? sanitize_text_field( wp_unslash( $_POST['affiliation'] ) ) : '';
	$audience   = isset( $_POST['audience_type'] ) ? sanitize_key( wp_unslash( $_POST['audience_type'] ) ) : '';
	$city       = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$linkedin   = isset( $_POST['linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['linkedin'] ) ) : '';
	$attended   = isset( $_POST['attended_before'] ) ? sanitize_text_field( wp_unslash( $_POST['attended_before'] ) ) : '';
	$why        = isset( $_POST['why_invite'] ) ? sanitize_textarea_field( wp_unslash( $_POST['why_invite'] ) ) : '';
	if ( function_exists( 'mb_substr' ) ) {
		$why = mb_substr( $why, 0, 250 );
	} else {
		$why = substr( $why, 0, 250 );
	}
	$will_attend = isset( $_POST['will_attend'] ) ? sanitize_text_field( wp_unslash( $_POST['will_attend'] ) ) : '';

	$has_vehicle    = ( isset( $_POST['has_vehicle'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['has_vehicle'] ) ) ) ? 'yes' : 'no';
	$vehicle_type   = '';
	$vehicle_number = '';
	if ( 'yes' === $has_vehicle ) {
		$vehicle_type   = isset( $_POST['vehicle_type'] ) ? sanitize_key( wp_unslash( $_POST['vehicle_type'] ) ) : '';
		$vehicle_number = isset( $_POST['vehicle_number'] ) ? sanitize_text_field( wp_unslash( $_POST['vehicle_number'] ) ) : '';
		if ( ! in_array( $vehicle_type, array( 'bike', 'car' ), true ) || '' === trim( $vehicle_number ) ) {
			wp_die( 'Please provide your vehicle type and number.' );
		}
	}

	$parking_policy = ( isset( $_POST['parking_policy'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['parking_policy'] ) ) );

	$valid_audience = in_array( $audience, array( 'student', 'professional', 'job_seeker' ), true );

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $cnic ) || ! preg_match( '/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', $cnic ) || empty( $university ) || empty( $city ) || empty( $linkedin ) || empty( $attended ) || ( 'yes' === $attended && empty( $why ) ) || 'yes' !== $will_attend || ! $parking_policy || ! $valid_audience ) {
		wp_die( 'Please fill in all required fields. Ensure your CNIC contains 13 digits (e.g. 12345-1234567-1).' );
	}

	global $wpdb;

	// 1. In-flight transient lock to prevent duplicate rapid clicks
	$lock_key = 'aws_sbg_reg_lock_' . md5( strtolower( $email ) );
	if ( get_transient( $lock_key ) ) {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		wp_safe_redirect( add_query_arg( array( 'reinvent_registration' => 'success' ), $redirect ) );
		exit;
	}
	set_transient( $lock_key, 1, 20 );

	// 2. High-performance indexed duplicate check by email
	$existing_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		 WHERE p.post_type = 'aws_reinvent_reg'
		   AND p.post_status = 'publish'
		   AND pm.meta_key = 'email'
		   AND pm.meta_value = %s
		 LIMIT 1",
		$email
	) );
	if ( $existing_id > 0 ) {
		delete_transient( $lock_key );
		$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		$redirect = add_query_arg( array( 'reinvent_registration' => 'exists' ), $redirect );
		wp_safe_redirect( $redirect );
		exit;
	}

	// 3. Fast post insertion without ping/comment overhead
	$post_id = wp_insert_post( array(
		'post_type'      => 'aws_reinvent_reg',
		'post_title'     => $full_name,
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	) );

	if ( is_wp_error( $post_id ) ) {
		delete_transient( $lock_key );
		wp_die( 'Failed to save registration. Please try again.' );
	}

	update_post_meta( $post_id, 'full_name', $full_name );
	update_post_meta( $post_id, 'email', $email );
	update_post_meta( $post_id, 'whatsapp', $whatsapp );
	update_post_meta( $post_id, 'cnic', $cnic );
	update_post_meta( $post_id, 'university', $university );
	update_post_meta( $post_id, 'audience_type', $audience );
	update_post_meta( $post_id, 'city', $city );
	update_post_meta( $post_id, 'linkedin', $linkedin );
	update_post_meta( $post_id, 'attended_before', $attended );
	update_post_meta( $post_id, 'why_invite', $why );
	update_post_meta( $post_id, 'will_attend', $will_attend );
	update_post_meta( $post_id, 'has_vehicle', $has_vehicle );
	update_post_meta( $post_id, 'vehicle_type', $vehicle_type );
	update_post_meta( $post_id, 'vehicle_number', $vehicle_number );
	update_post_meta( $post_id, 'parking_policy', $parking_policy ? 'yes' : 'no' );
	update_post_meta( $post_id, 'approval_status', 'pending' );
	update_post_meta( $post_id, 'notified', 'no' );
	update_post_meta( $post_id, 'notified_status', '' );

	$redirect = wp_get_referer() ? wp_get_referer() : home_url();
	$redirect = add_query_arg( array( 'reinvent_registration' => 'success' ), $redirect );

	// 4. Performance optimization:
	// If PHP FastCGI is active, flush the HTTP redirect immediately so the applicant
	// sees the success screen in < 250ms, while emails are sent in the background.
	if ( function_exists( 'fastcgi_finish_request' ) ) {
		header( 'Location: ' . $redirect );
		fastcgi_finish_request();
	}

	/* Applicant confirmation (under review) */
	if ( function_exists( 'aws_sbg_mail_send' ) ) {
		$confirm_body = '<p style="margin:0 0 12px 0; color:#334155;">Hi <strong style="color:#0f172a;">' . esc_html( $full_name ) . '</strong>,</p>'
			. '<p style="margin:0 0 14px 0; color:#334155;">Thank you for your interest in <strong style="color:#0f172a;">AWS re:Invent Watch &amp; Build Party 2026</strong>, hosted by the AWS Student Builder Group at Air University Islamabad.</p>'
			. '<p style="margin:0 0 14px 0; color:#334155;">Your application has been received and is now under review. If approved, you\'ll receive your official re:Invent pass and event clearance details.</p>'
			. '<p style="margin:0 0 8px 0; font-weight:700; color:#0f172a;">What happens next</p>'
			. '<ul style="margin:0 0 14px 20px; padding:0; color:#334155;">'
			. '<li style="margin:0 0 7px 0;">Our organizing committee reviews your registration details.</li>'
			. '<li style="margin:0;">You will receive an email once your seat and pass are confirmed.</li>'
			. '</ul>'
			. '<p style="margin:16px 0 0 0; color:#64748b; font-size:13px;">AWS Student Builder Group at Air University Islamabad<br>info@awsairuniversity.com · +923492449953</p>';
		try {
			aws_sbg_mail_send( 'reinvent', $email, 'Your AWS re:Invent application is under review', 'Application Received', 'Thank you for your interest', $confirm_body, AWS_REINVENT_WHATSAPP_URL, 'Join WhatsApp Community' );
		} catch ( \Throwable $e ) {
			error_log( 're:Invent applicant review email failed: ' . $e->getMessage() );
		}

		/* Admin notification (optional) */
		if ( function_exists( 'aws_sbg_mail_get_settings' ) ) {
			$mail_settings = aws_sbg_mail_get_settings();
			if ( 'yes' === ( $mail_settings['notify_admin_rsvp'] ?? 'yes' ) ) {
				$admin_to   = ! empty( $mail_settings['admin_email'] ) ? $mail_settings['admin_email'] : get_option( 'admin_email' );
				$admin_body = '<p style="margin:0 0 12px 0; color:#334155;">New re:Invent application:</p>'
					. '<p style="margin:0 0 6px 0; color:#334155;"><strong style="color:#0f172a;">' . esc_html( $full_name ) . '</strong> · ' . esc_html( ucfirst( str_replace( '_', ' ', $audience ) ) ) . '</p>'
					. '<p style="margin:0 0 6px 0; color:#334155;">' . esc_html( $university ) . ' · ' . esc_html( $city ) . '</p>'
					. '<p style="margin:0 0 6px 0; color:#334155;">Attended before: ' . esc_html( ucfirst( $attended ) ) . '</p>'
					. '<p style="margin:0; color:#334155;">Why: ' . esc_html( wp_trim_words( $why, 40, '…' ) ) . '</p>';
				try {
					aws_sbg_mail_send( 'reinvent', $admin_to, 'New re:Invent application · ' . esc_html( $full_name ), 'New Application', 'Review needed', $admin_body, admin_url( 'admin.php?page=aws-reinvent-registrations' ), 'Open registrations' );
				} catch ( \Throwable $e ) {
					error_log( 're:Invent admin notification email failed: ' . $e->getMessage() );
				}
			}
		}
	}

	if ( ! headers_sent() ) {
		wp_safe_redirect( $redirect );
	}
	exit;
}
add_action( 'admin_post_nopriv_submit_aws_reinvent_registration', 'aws_sbg_handle_reinvent_registration' );
add_action( 'admin_post_submit_aws_reinvent_registration', 'aws_sbg_handle_reinvent_registration' );

/* ========================================================================
 * 5. ADMIN HANDLERS (Clear, Delete, Admin Note, Review)
 * ======================================================================== */

/**
 * Handle bulk clearance of all test registrations with typed confirmation & snapshot backup.
 */
function aws_sbg_reinvent_handle_clear_registrations() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	check_admin_referer( 'aws_sbg_reinvent_clear_action' );

	$confirm_code  = isset( $_POST['confirm_code'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['confirm_code'] ) ) ) : '';
	$reset_counter = ! empty( $_POST['reset_counter'] );
	$redirect_base = admin_url( 'admin.php?page=aws-reinvent-registrations' );

	if ( 'CLEAR' !== $confirm_code ) {
		wp_safe_redirect( add_query_arg( 'notice', 'error_code_mismatch', $redirect_base ) );
		exit;
	}

	global $wpdb;

	$all_posts = get_posts( array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	) );

	$count = count( $all_posts );

	if ( $count > 0 ) {
		$snapshot_rows = array();
		foreach ( $all_posts as $p ) {
			$snapshot_rows[] = array(
				'ID'         => $p->ID,
				'post_title' => $p->post_title,
				'post_date'  => $p->post_date,
				'meta'       => get_post_meta( $p->ID ),
			);
		}

		$backup_key  = 'aws_reinvent_backup_' . current_time( 'Y-m-d_His' );
		$backup_data = array(
			'backed_up_at' => current_time( 'mysql' ),
			'admin_user'   => get_current_user_id(),
			'count'        => $count,
			'records'      => $snapshot_rows,
		);
		update_option( $backup_key, $backup_data, false );

		foreach ( $all_posts as $p ) {
			wp_delete_post( $p->ID, true );
		}
	}

	if ( $reset_counter ) {
		$numbers_table = $wpdb->prefix . 'aws_reinvent_numbers';
		$wpdb->query( "TRUNCATE TABLE $numbers_table" );
		delete_option( 'aws_reinvent_next_number' );
		delete_option( 'aws_reinvent_numbers_seeded' );
		update_option( 'aws_reinvent_numbers_seeded', 1 );
	}

	wp_safe_redirect( add_query_arg( array( 'notice' => 'reinvent_cleared', 'count' => $count ), $redirect_base ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_clear_registrations', 'aws_sbg_reinvent_handle_clear_registrations' );

/**
 * Handle single entry deletion.
 */
function aws_sbg_reinvent_handle_delete_reg() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	$reg_id = isset( $_GET['reg_id'] ) ? intval( $_GET['reg_id'] ) : 0;
	if ( ! $reg_id || ! wp_verify_nonce( $_GET['_wpnonce'], 'aws_reinvent_delete_reg_' . $reg_id ) ) {
		wp_die( 'Security check failed.' );
	}

	wp_delete_post( $reg_id, true );

	// Auto-resync sequence tracking table so deletions eliminate phantom gaps
	aws_sbg_resync_reinvent_numbers_table();

	$redirect = remove_query_arg( array( 'action', 'reg_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( 'notice', 'entry_deleted', $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_delete_reg', 'aws_sbg_reinvent_handle_delete_reg' );

/**
 * Handle saving internal Admin Note (text not from user).
 */
function aws_sbg_reinvent_handle_save_admin_note() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	$reg_id = isset( $_POST['reg_id'] ) ? intval( $_POST['reg_id'] ) : 0;
	if ( ! $reg_id || ! check_admin_referer( 'aws_reinvent_admin_note_' . $reg_id ) ) {
		wp_die( 'Security check failed.' );
	}

	$note = isset( $_POST['admin_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ) ) : '';
	update_post_meta( $reg_id, '_admin_note', $note );

	$redirect = remove_query_arg( array( 'action', 'reg_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( 'notice', 'note_saved', $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_save_admin_note', 'aws_sbg_reinvent_handle_save_admin_note' );

/**
 * Handle quick toggle of public re:Invent registration visibility.
 */
function aws_sbg_reinvent_handle_toggle_visibility() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	check_admin_referer( 'aws_sbg_reinvent_toggle_visibility_nonce' );

	$new_state = isset( $_POST['new_state'] ) && '1' === $_POST['new_state'] ? '1' : '0';
	update_option( 'aws_reinvent_form_hidden', $new_state );

	$notice   = '1' === $new_state ? 'visibility_hidden' : 'visibility_live';
	$redirect = isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : admin_url( 'admin.php?page=aws-reinvent-registrations' );
	$redirect = remove_query_arg( array( 'notice' ), $redirect );

	wp_safe_redirect( add_query_arg( 'notice', $notice, $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_toggle_visibility', 'aws_sbg_reinvent_handle_toggle_visibility' );

/**
 * Handle saving re:Invent registration settings (visibility + closed message).
 */
function aws_sbg_reinvent_handle_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	check_admin_referer( 'aws_sbg_reinvent_save_settings_nonce' );

	$is_hidden = isset( $_POST['form_hidden'] ) && '1' === $_POST['form_hidden'] ? '1' : '0';
	$message   = isset( $_POST['closed_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['closed_message'] ) ) : '';

	update_option( 'aws_reinvent_form_hidden', $is_hidden );
	if ( ! empty( $message ) ) {
		update_option( 'aws_reinvent_closed_message', $message );
	} else {
		delete_option( 'aws_reinvent_closed_message' );
	}

	$redirect = remove_query_arg( array( 'notice' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( 'notice', 'settings_saved', $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_save_settings', 'aws_sbg_reinvent_handle_save_settings' );

/**
 * Retrieve the badge tier / role for a re:Invent attendee.
 * Defaults to 'ATTENDEE' if not explicitly assigned.
 *
 * @param int $post_id Registration post ID.
 * @return string Badge tier (e.g. ATTENDEE, CORE TEAM, EVENT CREW, SPEAKER, VIP GUEST, ORGANIZER).
 */
function aws_sbg_get_reinvent_badge_tier( $post_id ) {
	$tier = get_post_meta( $post_id, '_badge_tier', true );
	if ( empty( $tier ) ) {
		return 'ATTENDEE';
	}
	return strtoupper( trim( (string) $tier ) );
}

/**
 * Handle saving badge tier / role from WP Admin.
 */
function aws_sbg_reinvent_handle_save_badge_tier() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	$reg_id = isset( $_POST['reg_id'] ) ? intval( $_POST['reg_id'] ) : 0;
	if ( ! $reg_id || ! check_admin_referer( 'aws_reinvent_badge_tier_' . $reg_id ) ) {
		wp_die( 'Security check failed.' );
	}

	$raw_tier = isset( $_POST['badge_tier'] ) ? sanitize_text_field( wp_unslash( $_POST['badge_tier'] ) ) : 'ATTENDEE';
	$raw_tier = trim( strtoupper( $raw_tier ) );
	if ( empty( $raw_tier ) ) {
		$raw_tier = 'ATTENDEE';
	}
	update_post_meta( $reg_id, '_badge_tier', $raw_tier );

	$redirect = remove_query_arg( array( 'action', 'reg_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( 'notice', 'tier_saved', $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_save_badge_tier', 'aws_sbg_reinvent_handle_save_badge_tier' );

/**
 * Handle manually saving / editing registration pass number from WP Admin.
 */
function aws_sbg_reinvent_handle_save_reg_number() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	$reg_id = isset( $_POST['reg_id'] ) ? intval( $_POST['reg_id'] ) : 0;
	if ( ! $reg_id || ! check_admin_referer( 'aws_reinvent_save_reg_no_' . $reg_id ) ) {
		wp_die( 'Security check failed.' );
	}

	$raw_no = isset( $_POST['reg_number'] ) ? sanitize_text_field( wp_unslash( $_POST['reg_number'] ) ) : '';
	$raw_no = trim( strtoupper( $raw_no ) );

	if ( ! empty( $raw_no ) ) {
		// Ensure standard RIV- prefix if numeric only was entered
		if ( ! preg_match( '/^RIV-/i', $raw_no ) && is_numeric( $raw_no ) ) {
			$raw_no = 'RIV-' . str_pad( intval( $raw_no ), 2, '0', STR_PAD_LEFT );
		}
		update_post_meta( $reg_id, 'registration_number', $raw_no );
		aws_sbg_resync_reinvent_numbers_table();
	}

	$redirect = remove_query_arg( array( 'action', 'reg_id', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( 'notice', 'reg_no_saved', $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_save_reg_number', 'aws_sbg_reinvent_handle_save_reg_number' );

/**
 * Handle renumbering and resynchronizing all approved pass numbers sequentially starting from RIV-01.
 */
function aws_sbg_reinvent_handle_resync_numbers() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	check_admin_referer( 'aws_reinvent_resync_numbers' );

	global $wpdb;
	$table = $wpdb->prefix . 'aws_reinvent_numbers';

	// Retrieve all approved registrations ordered chronologically by ID
	$approved_posts = get_posts( array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'     => 'approval_status',
				'value'   => 'approved',
				'compare' => '=',
			),
		),
		'orderby'        => 'ID',
		'order'          => 'ASC',
	) );

	// Truncate the sequence tracking table
	$wpdb->query( "TRUNCATE TABLE $table" );
	delete_option( 'aws_reinvent_next_number' );
	delete_option( 'aws_reinvent_numbers_seeded' );
	update_option( 'aws_reinvent_numbers_seeded', 1 );

	$seq = 0;
	if ( ! empty( $approved_posts ) ) {
		foreach ( $approved_posts as $post ) {
			$seq++;
			$new_no = 'RIV-' . str_pad( $seq, 2, '0', STR_PAD_LEFT );
			update_post_meta( $post->ID, 'registration_number', $new_no );
			$wpdb->insert( $table, array( 'created_at' => current_time( 'mysql' ) ), array( '%s' ) );
		}
	}

	$next_auto = max( 1, $seq + 1 );
	$wpdb->query( "ALTER TABLE $table AUTO_INCREMENT = " . intval( $next_auto ) );

	$redirect = remove_query_arg( array( 'action', '_wpnonce' ), wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=aws-reinvent-registrations' ) );
	wp_safe_redirect( add_query_arg( array( 'notice' => 'numbers_resynced', 'count' => $seq ), $redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_reinvent_resync_numbers', 'aws_sbg_reinvent_handle_resync_numbers' );

/**
 * Handle Approve and Reject review actions early on admin_init before headers are sent.
 */
function aws_sbg_reinvent_handle_review_action() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_GET['page'] ) || 'aws-reinvent-registrations' !== $_GET['page'] ) {
		return;
	}

	if ( ! isset( $_GET['action'], $_GET['reg_id'], $_GET['_wpnonce'] ) || ! in_array( $_GET['action'], array( 'approve', 'reject' ), true ) ) {
		return;
	}

	$reg_id = intval( $_GET['reg_id'] );
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'aws_reinvent_review_' . $reg_id ) ) {
		wp_die( 'Security check failed. Please refresh the page and try again.' );
	}

	$status      = 'approve' === $_GET['action'] ? 'approved' : 'rejected';
	$prev_status = get_post_meta( $reg_id, 'notified_status', true );
	update_post_meta( $reg_id, 'approval_status', $status );

	if ( 'approved' === $status && $prev_status !== 'approved' ) {
		$name   = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title( $reg_id ) );
		$reg_no = get_post_meta( $reg_id, 'registration_number', true );
		if ( empty( $reg_no ) ) {
			$reg_no = aws_sbg_allocate_reinvent_registration_number();
			update_post_meta( $reg_id, 'registration_number', $reg_no );
		}
		if ( get_the_title( $reg_id ) !== $name ) {
			wp_update_post( array( 'ID' => $reg_id, 'post_title' => $name ) );
		}

		$to_email = get_post_meta( $reg_id, 'email', true );
		if ( $to_email && function_exists( 'aws_sbg_mail_send' ) ) {
			$body = '<p style="margin:0 0 12px 0; color:#334155;">Hi <strong style="color:#0f172a;">' . esc_html( $name ) . '</strong>,</p>'
				. '<p style="margin:0 0 14px 0; color:#334155;">Congratulations, your application for <strong style="color:#0f172a;">AWS re:Invent Watch &amp; Build Party 2026</strong> has been approved.</p>'
				. '<div style="margin:18px 0 22px 0; background:#f8fafc; border:2px dashed #00b4d8; border-radius:12px; padding:18px 24px; text-align:center;">'
				. '<div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:11px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#0284c7; margin-bottom:6px;">Official Event Pass</div>'
				. '<div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:26px; font-weight:800; color:#0f172a; letter-spacing:0.04em;">' . esc_html( $reg_no ) . '</div>'
				. '<div style="font-size:12px; color:#64748b; margin-top:6px;">Please present this pass at the registration desk for check-in.</div>'
				. '</div>'
				. '<p style="margin:0 0 8px 0; font-weight:700; color:#0f172a;">What we have for you</p>'
				. '<ul style="margin:0 0 16px 20px; padding:0; color:#334155;">'
				. '<li style="margin:0 0 7px 0;">Expert sessions and keynote watch party</li>'
				. '<li style="margin:0 0 7px 0;">Refreshments (snacks, food, drinks)</li>'
				. '<li style="margin:0;">Exclusive AWS swags (subject to participation)</li>'
				. '</ul>'
				. '<p style="margin:16px 0 0 0; color:#64748b; font-size:13px;">AWS Student Builder Group at Air University Islamabad<br>info@awsairuniversity.com · +923492449953</p>';
			try {
				aws_sbg_mail_send( 'reinvent', $to_email, 'You\'re approved for AWS re:Invent Watch & Build Party', 'Application Approved', 'Congratulations', $body, AWS_REINVENT_WHATSAPP_URL, 'Join WhatsApp Community' );
				update_post_meta( $reg_id, 'notified', 'yes' );
			} catch ( \Throwable $e ) {
				error_log( 're:Invent approval email failed: ' . $e->getMessage() );
			}
		}
	}

	update_post_meta( $reg_id, 'notified_status', $status );

	$notice_key = ( 'approved' === $status ) ? 'approved' : 'rejected';
	$base_url   = admin_url( 'admin.php?page=aws-reinvent-registrations' );
	$redirect   = add_query_arg( 'notice', $notice_key, $base_url );

	if ( headers_sent() ) {
		echo '<script>window.location.replace(' . wp_json_encode( $redirect ) . ');</script>';
		echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url( $redirect ) . '"></noscript>';
		exit;
	}

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'aws_sbg_reinvent_handle_review_action' );

/* ========================================================================
 * 6. ADMIN: SEPARATE REGISTRATIONS TABLE
 * ======================================================================== */

// Admin page is registered in admin-dashboard.php to keep the suite menu organized.

function aws_sbg_render_reinvent_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	if ( isset( $_GET['roster'] ) ) {
		aws_sbg_render_reinvent_roster();
	}

	if ( isset( $_GET['badges'] ) ) {
		aws_sbg_render_reinvent_badges();
	}

	// Fallback in case review action reached page render callback
	if ( isset( $_GET['action'], $_GET['reg_id'], $_GET['_wpnonce'] ) && in_array( $_GET['action'], array( 'approve', 'reject' ), true ) ) {
		aws_sbg_reinvent_handle_review_action();
	}

	$notice   = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
	$n_count  = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
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

	if ( $status ) {
		$args['meta_query'] = array(
			array(
				'key'     => 'approval_status',
				'value'   => $status,
				'compare' => '=',
			),
		);
	}

	if ( $search ) {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		$matched_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = 'aws_reinvent_reg'
			   AND p.post_status = 'publish'
			   AND (
			       p.post_title LIKE %s
			       OR pm.meta_value LIKE %s
			   )",
			$like,
			$like
		) );
		$args['post__in'] = ! empty( $matched_ids ) ? $matched_ids : array( 0 );
	}

	$query = new WP_Query( $args );

	$counts = array( 'total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0 );
	$counts['total'] = (int) wp_count_posts( 'aws_reinvent_reg' )->publish;

	global $wpdb;
	$count_rows = $wpdb->get_results(
		"SELECT pm.meta_value AS status, COUNT(*) AS n
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.post_type = 'aws_reinvent_reg' AND p.post_status = 'publish' AND pm.meta_key = 'approval_status'
		 GROUP BY pm.meta_value"
	);
	foreach ( $count_rows as $cr ) {
		if ( isset( $counts[ $cr->status ] ) ) {
			$counts[ $cr->status ] = (int) $cr->n;
		}
	}

	$total_results = (int) $query->found_posts;
	$start         = $total_results ? ( ( $paged - 1 ) * $per_page ) + 1 : 0;
	$end           = min( $start + $per_page - 1, $total_results );

	$is_hidden      = aws_sbg_is_reinvent_registration_hidden();
	$closed_message = aws_sbg_get_reinvent_closed_message();

	aws_sbg_render_admin_nav_tabs( 'reinvent' );
	?>
	<div class="wrap rvadm">
		<style>
			.rvadm{--ink:#0f172a;--muted:#64748b;--line:#e2e8f0;--surface:#ffffff;--surface2:#f8fafc;--brand:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:var(--ink);width:100%;max-width:none;margin:10px 0 30px 0;padding-right:20px;box-sizing:border-box;}
			.rvadm *{box-sizing:border-box;}
			.rvadm h1{margin:0;font-size:20px;font-weight:700;letter-spacing:-0.01em;color:var(--ink);}
			.rvadm .rvadm-sub{font-size:13px;color:var(--muted);margin-top:3px;}

			.rvadm-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:18px;}
			.rvadm-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}

			.rvadm-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:6px;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-size:12.5px;font-weight:600;text-decoration:none;cursor:pointer;transition:border-color .15s ease,background .15s ease;}
			.rvadm-btn:hover{border-color:#cbd5e1;background:var(--surface2);}
			.rvadm-btn-danger{color:#b91c1c;border-color:#fca5a5;background:#fef2f2;}
			.rvadm-btn-danger:hover{background:#fee2e2;border-color:#f87171;color:#991b1b;}

			.rvadm-visibility-card{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:12px 18px;margin-bottom:16px;}
			.rvadm-vis-pill{display:inline-flex;align-items:center;gap:7px;padding:4px 10px;border-radius:9999px;font-size:11.5px;font-weight:700;letter-spacing:0.02em;white-space:nowrap;}
			.rvadm-vis-live{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;}
			.rvadm-vis-hidden{background:#fef3c7;color:#b45309;border:1px solid #fde68a;}
			.rvadm-dot-pulse{width:7px;height:7px;border-radius:50%;display:inline-block;flex-shrink:0;}
			.rvadm-dot-pulse-live{background:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,0.25);}
			.rvadm-dot-pulse-hidden{background:#d97706;}

			.rvadm-filters{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:12px 16px;margin-bottom:16px;width:100%;}
			.rvadm-filters form{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;}
			.rvadm-field{display:flex;flex-direction:column;gap:5px;}
			.rvadm-field label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--muted);}
			.rvadm-field select,.rvadm-field input{padding:7px 10px;border:1px solid var(--line);border-radius:6px;font-size:13px;color:var(--ink);background:var(--surface);}
			.rvadm-field select{min-width:160px;}
			.rvadm-field input{min-width:260px;}
			.rvadm-field select:focus,.rvadm-field input:focus{outline:2px solid var(--ink);outline-offset:1px;border-color:var(--ink);}
			.rvadm-search-btn{padding:7px 16px;border-radius:6px;border:1px solid var(--ink);background:var(--ink);color:#ffffff;font-size:12.5px;font-weight:600;cursor:pointer;}
			.rvadm-search-btn:hover{background:#1e293b;border-color:#1e293b;}

			.rvadm-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;width:100%;}
			.rvadm-stat{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:14px 18px;}
			.rvadm-stat-num{font-size:26px;font-weight:700;color:var(--ink);line-height:1;}
			.rvadm-stat-label{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--muted);margin-top:6px;}
			.rvadm-dot{width:8px;height:8px;border-radius:50%;background:#cbd5e1;flex-shrink:0;}
			.rvadm-dot-pending{background:#d97706;}
			.rvadm-dot-approved{background:#059669;}
			.rvadm-dot-rejected{background:#dc2626;}

			.rvadm-table-wrap{background:var(--surface);border:1px solid var(--line);border-radius:10px;overflow-x:auto;width:100%;}
			.rvadm-table-wrap table{width:100%;border-collapse:collapse;font-size:13px;}
			.rvadm-table-wrap thead th{background:var(--surface2);text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--muted);padding:11px 14px;border-bottom:1px solid var(--line);white-space:nowrap;}
			.rvadm-table-wrap tbody td{padding:12px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;}
			.rvadm-table-wrap tbody tr:last-child td{border-bottom:0;}
			.rvadm-table-wrap tbody tr:hover{background:#fafafa;}
			.rvadm-reg{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;padding:2px 6px;border-radius:4px;white-space:nowrap;font-size:12px;}
			.rvadm-name{font-weight:700;color:var(--ink);font-size:13.5px;line-height:1.3;}
			.rvadm-muted{color:var(--muted);font-size:12px;}
			.rvadm-mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:#334155;}
			.rvadm-null{color:#cbd5e1;}

			.rvadm-chip{display:inline-block;padding:2px 7px;border-radius:4px;font-size:10.5px;font-weight:700;letter-spacing:0.02em;margin-top:3px;}
			.rvadm-chip-student{background:#e0f2fe;color:#0369a1;}
			.rvadm-chip-professional{background:#f3e8ff;color:#6b21a8;}
			.rvadm-chip-job_seeker{background:#fef3c7;color:#92400e;}
			.rvadm-chip-cnic{background:#f1f5f9;color:#475569;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;}
			.rvadm-link{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;color:#0284c7;text-decoration:none;margin-top:4px;}
			.rvadm-link:hover{text-decoration:underline;}

			.rvadm-statement-details{max-width:320px;white-space:normal;font-size:12px;}
			.rvadm-statement-summary{cursor:pointer;list-style:none;display:flex;align-items:center;gap:6px;color:#334155;}
			.rvadm-statement-summary::-webkit-details-marker{display:none;}
			.rvadm-view-btn{font-size:10px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;padding:2px 6px;border-radius:4px;flex-shrink:0;}
			.rvadm-statement-details[open] .rvadm-view-btn{background:#e2e8f0;color:#0f172a;}
			.rvadm-statement-full{margin-top:6px;padding:8px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;line-height:1.5;color:#1e293b;}

			.rvadm-admin-note-cell{max-width:200px;white-space:normal;}
			.rvadm-note-bubble{display:inline-flex;align-items:flex-start;gap:6px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:4px 8px;font-size:12px;color:#92400e;line-height:1.4;}
			.rvadm-note-text{max-width:150px;word-break:break-word;}
			.rvadm-note-edit-btn{background:none;border:none;cursor:pointer;color:#b45309;padding:0 2px;font-size:12px;font-weight:700;}
			.rvadm-note-edit-btn:hover{color:#78350f;}
			.rvadm-add-note-btn{background:none;border:1px dashed #cbd5e1;border-radius:6px;padding:3px 8px;color:#64748b;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s ease;}
			.rvadm-add-note-btn:hover{border-color:#94a3b8;color:#0f172a;background:#f8fafc;}

			.rvadm-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:9999px;font-size:11px;font-weight:700;white-space:nowrap;}
			.rvadm-badge-pending{background:#fef3c7;color:#b45309;}
			.rvadm-badge-approved{background:#d1fae5;color:#047857;}
			.rvadm-badge-rejected{background:#fee2e2;color:#b91c1c;}

			.rvadm-tier-cell{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
			.rvadm-tier-chip{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;letter-spacing:0.04em;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;text-transform:uppercase;white-space:nowrap;line-height:1.2;}
			.rvadm-tier-attendee{background:#0f172a;color:#ffffff;}
			.rvadm-tier-core-team{background:#4f46e5;color:#ffffff;}
			.rvadm-tier-event-crew,.rvadm-tier-crew{background:#0d9488;color:#ffffff;}
			.rvadm-tier-speaker{background:#d97706;color:#ffffff;}
			.rvadm-tier-vip-guest,.rvadm-tier-vip{background:#db2777;color:#ffffff;}
			.rvadm-tier-organizer{background:#7c3aed;color:#ffffff;}
			.rvadm-tier-edit-btn{background:none;border:none;cursor:pointer;font-size:12px;color:var(--muted);padding:2px 4px;border-radius:4px;transition:all .15s ease;}
			.rvadm-tier-edit-btn:hover{color:var(--ink);background:#f1f5f9;}
			.rvadm-tier-preset-btn{background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:11.5px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;color:#0f172a;cursor:pointer;transition:all .15s ease;}
			.rvadm-tier-preset-btn:hover{background:#0f172a;color:#ffffff;border-color:#0f172a;}

			.rvadm-row-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap;}
			.rvadm-approve{padding:5px 10px;border-radius:6px;border:1px solid #059669;background:#059669;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;}
			.rvadm-approve:hover{background:#047857;border-color:#047857;}
			.rvadm-reject{padding:5px 10px;border-radius:6px;border:1px solid #fca5a5;background:#ffffff;color:#b91c1c;font-size:12px;font-weight:600;text-decoration:none;}
			.rvadm-reject:hover{background:#fef2f2;}
			.rvadm-delete{padding:5px 8px;border-radius:6px;border:1px solid #fee2e2;background:#ffffff;color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s ease;}
			.rvadm-delete:hover{background:#fee2e2;border-color:#fca5a5;color:#991b1b;}

			.rvadm-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 16px;border-top:1px solid var(--line);font-size:12px;color:var(--muted);flex-wrap:wrap;}
			.rvadm-pages{display:flex;gap:6px;}
			.rvadm-pages a,.rvadm-pages span{display:inline-block;padding:4px 10px;border:1px solid var(--line);border-radius:6px;text-decoration:none;color:var(--ink);font-size:12px;}
			.rvadm-pages .current{background:var(--ink);border-color:var(--ink);color:#ffffff;}

			@media (max-width:1024px){.rvadm-stats{grid-template-columns:1fr 1fr;}}
			@media (max-width:782px){.rvadm-field select,.rvadm-field input{min-width:100%;}}
			@media (max-width:640px){.rvadm-stats{grid-template-columns:1fr;}}
		</style>

		<?php if ( 'visibility_hidden' === $notice ) : ?>
			<div class="notice notice-warning is-dismissible" style="border-left-color:#f59e0b;margin-bottom:16px;">
				<p><strong>Public registration form is now HIDDEN:</strong> Visitors to the registration page will see the closed notice and cannot submit registrations.</p>
			</div>
		<?php elseif ( 'visibility_live' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Public registration form is now LIVE:</strong> Registration is open and accepting applications from the public.</p>
			</div>
		<?php elseif ( 'settings_saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Public form visibility settings and closed notice updated successfully.</strong></p>
			</div>
		<?php elseif ( 'reinvent_cleared' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Successfully cleared <?php echo esc_html( $n_count ); ?> registration entries.</strong> A safety snapshot backup was preserved in WordPress options (<code>aws_reinvent_backup_...</code>).</p>
			</div>
		<?php elseif ( 'entry_deleted' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Registration entry deleted successfully.</strong></p>
			</div>
		<?php elseif ( 'note_saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Admin internal note saved successfully.</strong></p>
			</div>
		<?php elseif ( 'tier_saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Badge tier / role updated successfully.</strong></p>
			</div>
		<?php elseif ( 'reg_no_saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Registration pass number updated successfully.</strong> The sequence counter has been recalibrated.</p>
			</div>
		<?php elseif ( 'numbers_resynced' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Pass numbers successfully resynchronized:</strong> <?php echo esc_html( $n_count ); ?> approved registration(s) renumbered sequentially starting from <code>RIV-01</code>. The next approved attendee will receive <code>RIV-<?php echo esc_html( str_pad( $n_count + 1, 2, '0', STR_PAD_LEFT ) ); ?></code>.</p>
			</div>
		<?php elseif ( 'approved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Application approved successfully.</strong> Attendee has been assigned their pass number and sent an approval email.</p>
			</div>
		<?php elseif ( 'rejected' === $notice ) : ?>
			<div class="notice notice-info is-dismissible" style="margin-bottom:16px;">
				<p><strong>Application marked as rejected.</strong></p>
			</div>
		<?php elseif ( 'error_code_mismatch' === $notice ) : ?>
			<div class="notice notice-error is-dismissible" style="margin-bottom:16px;">
				<p><strong>Clear operation cancelled:</strong> You must type <code>CLEAR</code> exactly in capital letters to confirm clearing entries. No data was deleted.</p>
			</div>
		<?php endif; ?>

		<div class="rvadm-head">
			<div>
				<h1>re:Invent Watch &amp; Build Party Registrations</h1>
				<p class="rvadm-sub">Review, approve, or reject applications. Use the roster for a print-ready gate clearance sheet.</p>
			</div>
			<div class="rvadm-actions">
				<a class="rvadm-btn" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_reinvent_resync_numbers' ), 'aws_reinvent_resync_numbers' ) ); ?>" onclick="return confirm('Resynchronize all approved pass numbers sequentially starting from RIV-01? This will cleanly renumber approved attendees and eliminate any gaps from deleted test submissions.');" title="Renumber approved attendees consecutively starting from RIV-01">🔄 Resync Pass Numbers</a>
				<?php if ( $counts['total'] > 0 ) : ?>
					<button type="button" class="rvadm-btn rvadm-btn-danger" onclick="document.getElementById('aws-reinvent-clear-modal').style.display='flex'">Clear Entries…</button>
				<?php endif; ?>
				<a class="rvadm-btn" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'export_csv' => '1' ) ) ); ?>">Export CSV</a>
				<a class="rvadm-btn" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'roster' => '1' ) ) ); ?>" target="_blank">Print Roster</a>
				<a class="rvadm-btn" style="background:#0f172a;color:#ffffff;border-color:#0f172a;" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'badges' => '1' ) ) ); ?>" target="_blank" title="Generate print-ready attendee cards on A4 sheets for print vendor">🪪 Print Attendee Badges (PDF)</a>
			</div>
		</div>

		<div class="rvadm-visibility-card">
			<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
				<?php if ( $is_hidden ) : ?>
					<span class="rvadm-vis-pill rvadm-vis-hidden">
						<span class="rvadm-dot-pulse rvadm-dot-pulse-hidden"></span> Public Form: Hidden (Closed)
					</span>
					<span style="font-size:12.5px;color:var(--muted);">Visitors cannot see registration form steps or submit applications.</span>
				<?php else : ?>
					<span class="rvadm-vis-pill rvadm-vis-live">
						<span class="rvadm-dot-pulse rvadm-dot-pulse-live"></span> Public Form: Live &amp; Open
					</span>
					<span style="font-size:12.5px;color:var(--muted);">Registration is active and open to the public.</span>
				<?php endif; ?>
			</div>
			<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<input type="hidden" name="action" value="aws_sbg_reinvent_toggle_visibility">
					<?php wp_nonce_field( 'aws_sbg_reinvent_toggle_visibility_nonce' ); ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations' ), admin_url( 'admin.php' ) ) ); ?>">
					<?php if ( $is_hidden ) : ?>
						<input type="hidden" name="new_state" value="0">
						<button type="submit" class="rvadm-btn" style="border-color:#16a34a;color:#16a34a;background:#f0fdf4;">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
							Unhide / Open Public Form
						</button>
					<?php else : ?>
						<input type="hidden" name="new_state" value="1">
						<button type="submit" class="rvadm-btn" style="border-color:#fca5a5;color:#b91c1c;background:#fef2f2;" onclick="return confirm('Hide the re:Invent registration form from public visitors?');">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
							Hide Form from Public
						</button>
					<?php endif; ?>
				</form>
				<button type="button" class="rvadm-btn" onclick="document.getElementById('aws-reinvent-settings-modal').style.display='flex'">Notice Settings…</button>
				<a class="rvadm-btn" href="<?php echo esc_url( home_url( '/reinvent/' ) ); ?>" target="_blank" title="Preview public page">View Page &#8599;</a>
			</div>
		</div>

		<div class="rvadm-filters">
			<form method="GET">
				<input type="hidden" name="page" value="aws-reinvent-registrations">
				<div class="rvadm-field">
					<label for="rvadm-status">Status</label>
					<select id="rvadm-status" name="status_filter" onchange="this.form.submit()">
						<option value="">All statuses</option>
						<option value="pending" <?php selected( $status, 'pending' ); ?>>Pending</option>
						<option value="approved" <?php selected( $status, 'approved' ); ?>>Approved</option>
						<option value="rejected" <?php selected( $status, 'rejected' ); ?>>Rejected</option>
					</select>
				</div>
				<div class="rvadm-field">
					<label for="rvadm-search">Search</label>
					<input id="rvadm-search" type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="Name, CNIC, email, phone, university, or reg #">
				</div>
				<button type="submit" class="rvadm-search-btn">Search</button>
			</form>
		</div>

		<div class="rvadm-stats">
			<div class="rvadm-stat">
				<div class="rvadm-stat-num"><?php echo intval( $counts['total'] ); ?></div>
				<div class="rvadm-stat-label"><span class="rvadm-dot"></span>Total</div>
			</div>
			<div class="rvadm-stat">
				<div class="rvadm-stat-num"><?php echo intval( $counts['pending'] ); ?></div>
				<div class="rvadm-stat-label"><span class="rvadm-dot rvadm-dot-pending"></span>Pending</div>
			</div>
			<div class="rvadm-stat">
				<div class="rvadm-stat-num"><?php echo intval( $counts['approved'] ); ?></div>
				<div class="rvadm-stat-label"><span class="rvadm-dot rvadm-dot-approved"></span>Approved</div>
			</div>
			<div class="rvadm-stat">
				<div class="rvadm-stat-num"><?php echo intval( $counts['rejected'] ); ?></div>
				<div class="rvadm-stat-label"><span class="rvadm-dot rvadm-dot-rejected"></span>Rejected</div>
			</div>
		</div>

		<div class="rvadm-table-wrap">
			<table>
				<thead>
					<tr>
						<th>Reg #</th>
						<th>Applicant</th>
						<th>Badge Tier</th>
						<th>Contact</th>
						<th>Institute / City</th>
						<th>Last Attended Event (User Note)</th>
						<th>Admin Note (Internal)</th>
						<th>Vehicle &amp; Parking</th>
						<th>Status</th>
						<th>Submitted</th>
						<th style="text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $query->have_posts() ) : ?>
						<tr>
							<td colspan="11" class="rvadm-muted" style="padding:40px; text-align:center;">No registrations found.</td>
						</tr>
					<?php else : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php
							$reg_id      = get_the_ID();
							$name        = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title() );
							$reg_number  = get_post_meta( $reg_id, 'registration_number', true );
							$email       = get_post_meta( $reg_id, 'email', true );
							$phone       = get_post_meta( $reg_id, 'whatsapp', true );
							$cnic        = get_post_meta( $reg_id, 'cnic', true );
							$audience    = get_post_meta( $reg_id, 'audience_type', true );
							$university  = get_post_meta( $reg_id, 'university', true );
							$city        = get_post_meta( $reg_id, 'city', true );
							$linkedin    = get_post_meta( $reg_id, 'linkedin', true );
							$attended    = get_post_meta( $reg_id, 'attended_before', true );
							$why         = get_post_meta( $reg_id, 'why_invite', true );
							$admin_note  = get_post_meta( $reg_id, '_admin_note', true );
							$badge_tier  = aws_sbg_get_reinvent_badge_tier( $reg_id );
							$approval    = get_post_meta( $reg_id, 'approval_status', true ) ?: 'pending';
							$has_vehicle = get_post_meta( $reg_id, 'has_vehicle', true );
							$vehicle     = 'No vehicle';
							if ( 'yes' === $has_vehicle ) {
								$vehicle = ucfirst( get_post_meta( $reg_id, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $reg_id, 'vehicle_number', true );
							}
							$parking_ok  = 'yes' === get_post_meta( $reg_id, 'parking_policy', true );
							$note_nonce   = wp_create_nonce( 'aws_reinvent_admin_note_' . $reg_id );
							$tier_nonce   = wp_create_nonce( 'aws_reinvent_badge_tier_' . $reg_id );
							$reg_no_nonce = wp_create_nonce( 'aws_reinvent_save_reg_no_' . $reg_id );
							?>
							<tr>
								<td class="rvadm-reg">
									<div style="display:inline-flex;align-items:center;gap:6px;">
										<span><?php echo $reg_number ? esc_html( $reg_number ) : '<span class="rvadm-null">—</span>'; ?></span>
										<?php if ( 'approved' === $approval || $reg_number ) : ?>
											<button type="button" class="rvadm-tier-edit-btn" onclick="openRegNumberModal(<?php echo $reg_id; ?>, <?php echo esc_attr( wp_json_encode( $name ) ); ?>, <?php echo esc_attr( wp_json_encode( $reg_number ? $reg_number : 'RIV-01' ) ); ?>, <?php echo esc_attr( wp_json_encode( $reg_no_nonce ) ); ?>)" title="Edit pass number">✎</button>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<div class="rvadm-name"><?php echo esc_html( $name ); ?></div>
									<div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;margin-top:4px;">
										<?php if ( $audience ) : ?>
											<span class="rvadm-chip rvadm-chip-<?php echo esc_attr( $audience ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $audience ) ) ); ?></span>
										<?php endif; ?>
										<?php if ( $cnic ) : ?>
											<span class="rvadm-chip rvadm-chip-cnic">CNIC: <?php echo esc_html( $cnic ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $linkedin ) : ?>
										<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener" class="rvadm-link">LinkedIn ↗</a>
									<?php endif; ?>
								</td>
								<td>
									<div class="rvadm-tier-cell">
										<span class="rvadm-tier-chip rvadm-tier-<?php echo esc_attr( sanitize_title( $badge_tier ) ); ?>">
											<?php echo esc_html( $badge_tier ); ?>
										</span>
										<button type="button" class="rvadm-tier-edit-btn" onclick="openBadgeTierModal(<?php echo $reg_id; ?>, <?php echo esc_attr( wp_json_encode( $name ) ); ?>, <?php echo esc_attr( wp_json_encode( $badge_tier ) ); ?>, <?php echo esc_attr( wp_json_encode( $tier_nonce ) ); ?>)" title="Change badge role / tier">✎</button>
									</div>
								</td>
								<td>
									<div class="rvadm-mono"><?php echo esc_html( $email ); ?></div>
									<div class="rvadm-mono" style="margin-top:3px;"><?php echo esc_html( $phone ); ?></div>
								</td>
								<td>
									<div style="font-weight:600;color:var(--ink);"><?php echo esc_html( $university ); ?></div>
									<div class="rvadm-muted"><?php echo esc_html( $city ); ?></div>
								</td>
								<td>
									<?php if ( ! empty( $why ) ) : ?>
										<details class="rvadm-statement-details">
											<summary class="rvadm-statement-summary">
												<span><?php echo esc_html( wp_trim_words( $why, 6, '…' ) ); ?></span>
												<span class="rvadm-view-btn">Read Note</span>
											</summary>
											<div class="rvadm-statement-full">
												<p style="margin:0 0 8px;"><?php echo nl2br( esc_html( $why ) ); ?></p>
												<div style="font-size:11px;color:#64748b;border-top:1px dashed #cbd5e1;padding-top:6px;">
													<strong>Attended AWS or community event:</strong> <?php echo 'yes' === strtolower( $attended ) ? '<span style="color:#059669;font-weight:700;">Yes</span>' : 'No'; ?>
												</div>
											</div>
										</details>
									<?php else : ?>
										<span class="rvadm-null">—</span>
									<?php endif; ?>
								</td>
								<td>
									<div class="rvadm-admin-note-cell">
										<?php if ( ! empty( $admin_note ) ) : ?>
											<div class="rvadm-note-bubble">
												<span class="rvadm-note-text"><?php echo esc_html( $admin_note ); ?></span>
												<button type="button" class="rvadm-note-edit-btn" onclick="openAdminNoteModal(<?php echo $reg_id; ?>, <?php echo esc_attr( wp_json_encode( $name ) ); ?>, <?php echo esc_attr( wp_json_encode( $admin_note ) ); ?>, <?php echo esc_attr( wp_json_encode( $note_nonce ) ); ?>)" title="Edit internal note">✎</button>
											</div>
										<?php else : ?>
											<button type="button" class="rvadm-add-note-btn" onclick="openAdminNoteModal(<?php echo $reg_id; ?>, <?php echo esc_attr( wp_json_encode( $name ) ); ?>, '', <?php echo esc_attr( wp_json_encode( $note_nonce ) ); ?>)">+ Add Note</button>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<div class="rvadm-muted" style="font-weight:600;"><?php echo esc_html( $vehicle ); ?></div>
									<div style="font-size:11.5px;margin-top:3px;">
										Policy: <?php echo $parking_ok ? '<span style="color:#059669;font-weight:700;">Agreed ✓</span>' : '<span class="rvadm-null">—</span>'; ?>
									</div>
								</td>
								<td>
									<?php if ( 'approved' === $approval ) : ?>
										<span class="rvadm-badge rvadm-badge-approved">Approved</span>
									<?php elseif ( 'rejected' === $approval ) : ?>
										<span class="rvadm-badge rvadm-badge-rejected">Rejected</span>
									<?php else : ?>
										<span class="rvadm-badge rvadm-badge-pending">Pending</span>
									<?php endif; ?>
								</td>
								<td class="rvadm-muted" style="white-space:nowrap;"><?php echo esc_html( get_the_date( 'M j, Y', $reg_id ) ); ?></td>
								<td>
									<div class="rvadm-row-actions">
										<?php if ( 'pending' === $approval ) : ?>
											<?php
											$approve_url = wp_nonce_url(
												add_query_arg(
													array(
														'page'   => 'aws-reinvent-registrations',
														'action' => 'approve',
														'reg_id' => $reg_id,
													),
													admin_url( 'admin.php' )
												),
												'aws_reinvent_review_' . $reg_id
											);
											$reject_url  = wp_nonce_url(
												add_query_arg(
													array(
														'page'   => 'aws-reinvent-registrations',
														'action' => 'reject',
														'reg_id' => $reg_id,
													),
													admin_url( 'admin.php' )
												),
												'aws_reinvent_review_' . $reg_id
											);
											?>
											<a class="rvadm-approve" href="<?php echo esc_url( $approve_url ); ?>">Approve</a>
											<a class="rvadm-reject" href="<?php echo esc_url( $reject_url ); ?>">Reject</a>
										<?php endif; ?>
										<a class="rvadm-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_reinvent_delete_reg&reg_id=' . $reg_id ), 'aws_reinvent_delete_reg_' . $reg_id ) ); ?>" onclick="return confirm('Permanently delete this registration entry?');" title="Delete entry">Delete</a>
									</div>
								</td>
							</tr>
						<?php endwhile; wp_reset_postdata(); ?>
					<?php endif; ?>
				</tbody>
			</table>
			<div class="rvadm-foot">
				<span><?php echo $total_results ? 'Showing ' . $start . ' to ' . $end . ' of ' . $total_results : 'No results'; ?></span>
				<?php if ( $query->max_num_pages > 1 ) : ?>
					<div class="rvadm-pages">
						<?php
						echo paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $query->max_num_pages,
							'prev_text' => 'Prev',
							'next_text' => 'Next',
						) );
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Modal 1: Safe Clear Registrations -->
		<div id="aws-reinvent-clear-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
			<div style="background:#ffffff;border-radius:16px;max-width:520px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #fee2e2;">
				<div style="background:#fef2f2;border-bottom:1px solid #fee2e2;padding:20px 24px;display:flex;align-items:center;gap:12px;">
					<div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;border:2px solid #fca5a5;flex-shrink:0;">&#9888;</div>
					<div>
						<h3 style="margin:0;font-size:17px;font-weight:800;color:#991b1b;">Clear re:Invent Test Registrations</h3>
						<div style="font-size:12px;color:#b91c1c;margin-top:2px;">Safe testing cleanup with automated snapshot backup</div>
					</div>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:24px;">
					<?php wp_nonce_field( 'aws_sbg_reinvent_clear_action' ); ?>
					<input type="hidden" name="action" value="aws_sbg_reinvent_clear_registrations">

					<p style="margin:0 0 14px;font-size:13px;line-height:1.6;color:#334155;">
						You are about to delete <strong>all <?php echo intval( $counts['total'] ); ?> registrations</strong>. This is intended for cleaning up test submissions before official registration rollout.
					</p>

					<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#475569;line-height:1.5;">
						<strong style="color:#0f172a;">&#128274; Safety Snapshot:</strong> A complete snapshot of all applicant records, metadata, and statements will be preserved in your WordPress options table (<code>aws_reinvent_backup_...</code>) prior to deletion so data can be restored if needed.
					</div>

					<label style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:#334155;margin-bottom:16px;cursor:pointer;">
						<input type="checkbox" name="reset_counter" value="1" checked style="border-radius:4px;margin-top:2px;">
						<span><strong>Reset sequence counter back to #01</strong><br><span style="font-size:11.5px;color:#64748b;">Truncates table <code>aws_reinvent_numbers</code> so the next approved registration begins at RIV-01.</span></span>
					</label>

					<div style="margin-bottom:20px;">
						<label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">
							Type <span style="color:#dc2626;font-family:monospace;font-size:14px;background:#fee2e2;padding:2px 6px;border-radius:4px;">CLEAR</span> to confirm:
						</label>
						<input type="text" name="confirm_code" autocomplete="off" required placeholder="Type CLEAR here" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:monospace;font-size:14px;letter-spacing:2px;font-weight:700;">
					</div>

					<div style="display:flex;justify-content:flex-end;gap:10px;">
						<button type="button" class="button" onclick="document.getElementById('aws-reinvent-clear-modal').style.display='none'">Cancel</button>
						<button type="submit" class="button" style="background:#dc2626;color:#ffffff;border-color:#b91c1c;font-weight:700;padding:4px 16px;">Permanently Clear Registrations</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Modal 2: Admin Internal Note (Text not from user) -->
		<div id="aws-reinvent-note-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
			<div style="background:#ffffff;border-radius:16px;max-width:500px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;">
				<div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
					<div>
						<h3 id="rvadm-note-modal-title" style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Admin Note</h3>
						<div id="rvadm-note-modal-sub" style="font-size:12px;color:#64748b;margin-top:2px;">Internal remarks &amp; status notes (not visible to applicant)</div>
					</div>
					<button type="button" onclick="closeAdminNoteModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1;">&times;</button>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:22px 24px;">
					<input type="hidden" name="action" value="aws_sbg_reinvent_save_admin_note">
					<input type="hidden" id="rvadm-note-reg-id" name="reg_id" value="">
					<input type="hidden" id="rvadm-note-nonce" name="_wpnonce" value="">

					<div style="margin-bottom:16px;">
						<label for="rvadm-note-textarea" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:6px;">Internal Remarks</label>
						<textarea id="rvadm-note-textarea" name="admin_note" rows="5" placeholder="e.g. Verified student badge, VIP guest, confirmed attendance via phone call, allowed parking..." style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;line-height:1.5;color:#0f172a;background:#ffffff;box-sizing:border-box;"></textarea>
					</div>

					<div style="display:flex;justify-content:flex-end;gap:10px;">
						<button type="button" class="button" onclick="closeAdminNoteModal()">Cancel</button>
						<button type="submit" class="button button-primary" style="font-weight:600;">Save Note</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Modal 3: re:Invent Visibility & Notice Settings -->
		<div id="aws-reinvent-settings-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
			<div style="background:#ffffff;border-radius:16px;max-width:520px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;">
				<div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
					<div>
						<h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Public Form Visibility Settings</h3>
						<div style="font-size:12px;color:#64748b;margin-top:2px;">Control public access and the closed notice message</div>
					</div>
					<button type="button" onclick="document.getElementById('aws-reinvent-settings-modal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1;">&times;</button>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:22px 24px;">
					<input type="hidden" name="action" value="aws_sbg_reinvent_save_settings">
					<?php wp_nonce_field( 'aws_sbg_reinvent_save_settings_nonce' ); ?>

					<div style="margin-bottom:18px;">
						<label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:8px;">Form Visibility</label>
						<label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;cursor:pointer;background:<?php echo ! $is_hidden ? '#f0fdf4' : '#ffffff'; ?>;">
							<input type="radio" name="form_hidden" value="0" <?php checked( ! $is_hidden ); ?> style="accent-color:#16a34a;">
							<div>
								<strong style="display:block;font-size:13px;color:#0f172a;">Open &amp; Visible to Public</strong>
								<span style="font-size:12px;color:#64748b;">Visitors can see all form steps and submit applications.</span>
							</div>
						</label>
						<label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;background:<?php echo $is_hidden ? '#fef2f2' : '#ffffff'; ?>;">
							<input type="radio" name="form_hidden" value="1" <?php checked( $is_hidden ); ?> style="accent-color:#dc2626;">
							<div>
								<strong style="display:block;font-size:13px;color:#0f172a;">Hidden from Public (Closed)</strong>
								<span style="font-size:12px;color:#64748b;">Form is hidden and visitors see the closed notice message.</span>
							</div>
						</label>
					</div>

					<div style="margin-bottom:20px;">
						<label for="rvadm-closed-msg" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:6px;">Closed Notice Message</label>
						<textarea id="rvadm-closed-msg" name="closed_message" rows="4" placeholder="Message shown to public visitors when registrations are closed..." style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;line-height:1.5;color:#0f172a;background:#ffffff;box-sizing:border-box;"><?php echo esc_textarea( $closed_message ); ?></textarea>
						<span style="display:block;font-size:11.5px;color:#64748b;margin-top:4px;">Displayed to visitors when registration is hidden.</span>
					</div>

					<div style="display:flex;justify-content:flex-end;gap:10px;">
						<button type="button" class="button" onclick="document.getElementById('aws-reinvent-settings-modal').style.display='none'">Cancel</button>
						<button type="submit" class="button button-primary" style="font-weight:700;padding:4px 16px;">Save Settings</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Modal 4: Badge Tier / Role Customization -->
		<div id="aws-reinvent-tier-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
			<div style="background:#ffffff;border-radius:16px;max-width:480px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;">
				<div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
					<div>
						<h3 id="rvadm-tier-modal-title" style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Change Badge Tier</h3>
						<div style="font-size:12px;color:#64748b;margin-top:2px;">Printed on attendee lanyard badge (front side)</div>
					</div>
					<button type="button" onclick="closeBadgeTierModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1;">&times;</button>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:22px 24px;">
					<input type="hidden" name="action" value="aws_sbg_reinvent_save_badge_tier">
					<input type="hidden" id="rvadm-tier-reg-id" name="reg_id" value="">
					<input type="hidden" id="rvadm-tier-nonce" name="_wpnonce" value="">

					<div style="margin-bottom:14px;">
						<label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:8px;">Quick Presets</label>
						<div style="display:flex;flex-wrap:wrap;gap:6px;">
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('ATTENDEE')">ATTENDEE</button>
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('CORE TEAM')">CORE TEAM</button>
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('EVENT CREW')">EVENT CREW</button>
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('SPEAKER')">SPEAKER</button>
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('VIP GUEST')">VIP GUEST</button>
							<button type="button" class="rvadm-tier-preset-btn" onclick="selectTierPreset('ORGANIZER')">ORGANIZER</button>
						</div>
					</div>

					<div style="margin-bottom:18px;">
						<label for="rvadm-tier-input" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:6px;">Badge Role / Tier Text</label>
						<input type="text" id="rvadm-tier-input" name="badge_tier" placeholder="ATTENDEE, CORE TEAM, EVENT CREW, or custom role..." style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13.5px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#0f172a;background:#ffffff;box-sizing:border-box;" required>
						<span style="display:block;font-size:11.5px;color:#64748b;margin-top:4px;">Printed in bold caps on top of the badge card.</span>
					</div>

					<div style="display:flex;justify-content:flex-end;gap:10px;">
						<button type="button" class="button" onclick="closeBadgeTierModal()">Cancel</button>
						<button type="submit" class="button button-primary" style="font-weight:700;padding:4px 16px;">Save Tier</button>
					</div>
				</form>
			</div>
		</div>

		<!-- Modal 5: Edit Pass Number -->
		<div id="aws-reinvent-regno-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
			<div style="background:#ffffff;border-radius:16px;max-width:440px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;">
				<div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
					<div>
						<h3 id="rvadm-regno-modal-title" style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Edit Pass Number</h3>
						<div style="font-size:12px;color:#64748b;margin-top:2px;">Unique event check-in pass ID</div>
					</div>
					<button type="button" onclick="closeRegNumberModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1;">&times;</button>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:22px 24px;">
					<input type="hidden" name="action" value="aws_sbg_reinvent_save_reg_number">
					<input type="hidden" id="rvadm-regno-reg-id" name="reg_id" value="">
					<input type="hidden" id="rvadm-regno-nonce" name="_wpnonce" value="">

					<div style="margin-bottom:18px;">
						<label for="rvadm-regno-input" style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#475569;margin-bottom:6px;">Registration Pass Number</label>
						<input type="text" id="rvadm-regno-input" name="reg_number" placeholder="e.g. RIV-01" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#0f172a;background:#ffffff;box-sizing:border-box;" required>
						<span style="display:block;font-size:11.5px;color:#64748b;margin-top:5px;">Must follow standard <code>RIV-XX</code> format (e.g. <code>RIV-01</code>).</span>
					</div>

					<div style="display:flex;justify-content:flex-end;gap:10px;">
						<button type="button" class="button" onclick="closeRegNumberModal()">Cancel</button>
						<button type="submit" class="button button-primary" style="font-weight:700;padding:4px 16px;">Save Pass Number</button>
					</div>
				</form>
			</div>
		</div>

		<script>
		function openAdminNoteModal(regId, name, note, nonce) {
			document.getElementById('rvadm-note-reg-id').value = regId;
			document.getElementById('rvadm-note-nonce').value = nonce;
			document.getElementById('rvadm-note-textarea').value = note || '';
			document.getElementById('rvadm-note-modal-title').textContent = 'Admin Note: ' + name;
			document.getElementById('aws-reinvent-note-modal').style.display = 'flex';
			document.getElementById('rvadm-note-textarea').focus();
		}
		function closeAdminNoteModal() {
			document.getElementById('aws-reinvent-note-modal').style.display = 'none';
		}
		function openBadgeTierModal(regId, name, tier, nonce) {
			document.getElementById('rvadm-tier-reg-id').value = regId;
			document.getElementById('rvadm-tier-nonce').value = nonce;
			document.getElementById('rvadm-tier-input').value = tier || 'ATTENDEE';
			document.getElementById('rvadm-tier-modal-title').textContent = 'Badge Tier: ' + name;
			document.getElementById('aws-reinvent-tier-modal').style.display = 'flex';
			document.getElementById('rvadm-tier-input').focus();
		}
		function closeBadgeTierModal() {
			document.getElementById('aws-reinvent-tier-modal').style.display = 'none';
		}
		function selectTierPreset(val) {
			document.getElementById('rvadm-tier-input').value = val;
		}
		function openRegNumberModal(regId, name, regNo, nonce) {
			document.getElementById('rvadm-regno-reg-id').value = regId;
			document.getElementById('rvadm-regno-nonce').value = nonce;
			document.getElementById('rvadm-regno-input').value = regNo || 'RIV-01';
			document.getElementById('rvadm-regno-modal-title').textContent = 'Edit Pass: ' + name;
			document.getElementById('aws-reinvent-regno-modal').style.display = 'flex';
			document.getElementById('rvadm-regno-input').focus();
		}
		function closeRegNumberModal() {
			document.getElementById('aws-reinvent-regno-modal').style.display = 'none';
		}
		</script>
	</div>
	<?php
}

/* ========================================================================
 * 7. PRINTABLE EVENT ROSTER & GATE CHECK-IN SHEET
 * ======================================================================== */

function aws_sbg_render_reinvent_roster() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	$status_filter = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : ( isset( $_GET['status_filter'] ) ? sanitize_key( $_GET['status_filter'] ) : 'approved' );
	if ( ! in_array( $status_filter, array( 'approved', 'all', 'pending' ), true ) ) {
		$status_filter = 'approved';
	}

	$args = array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'ASC',
	);

	if ( 'approved' === $status_filter ) {
		$args['meta_query'] = array(
			array( 'key' => 'approval_status', 'value' => 'approved' ),
		);
	} elseif ( 'pending' === $status_filter ) {
		$args['meta_query'] = array(
			array( 'key' => 'approval_status', 'value' => 'pending' ),
		);
	}

	$query = new WP_Query( $args );

	$rows           = array();
	$vehicle_count  = 0;
	$approved_count = 0;

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$id          = get_the_ID();
			$reg_no      = get_post_meta( $id, 'registration_number', true );
			$name        = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title() );
			$cnic        = get_post_meta( $id, 'cnic', true );
			$phone       = get_post_meta( $id, 'whatsapp', true );
			$email       = get_post_meta( $id, 'email', true );
			$audience    = get_post_meta( $id, 'audience_type', true );
			$university  = get_post_meta( $id, 'university', true );
			$city        = get_post_meta( $id, 'city', true );
			$has_vehicle = get_post_meta( $id, 'has_vehicle', true );
			$vehicle     = 'None';
			if ( 'yes' === $has_vehicle ) {
				$vehicle_count++;
				$vehicle = ucfirst( get_post_meta( $id, 'vehicle_type', true ) ) . ' (' . get_post_meta( $id, 'vehicle_number', true ) . ')';
			}
			$approval = get_post_meta( $id, 'approval_status', true ) ?: 'pending';
			if ( 'approved' === $approval ) {
				$approved_count++;
			}

			$rows[] = compact( 'id', 'reg_no', 'name', 'cnic', 'phone', 'email', 'audience', 'university', 'city', 'vehicle', 'approval' );
		}
		wp_reset_postdata();
	}

	// Sort: sort by registration number ascending if available, then by name
	usort( $rows, function ( $a, $b ) {
		if ( ! empty( $a['reg_no'] ) && ! empty( $b['reg_no'] ) ) {
			return strnatcasecmp( $a['reg_no'], $b['reg_no'] );
		}
		if ( ! empty( $a['reg_no'] ) ) {
			return -1;
		}
		if ( ! empty( $b['reg_no'] ) ) {
			return 1;
		}
		return strcasecmp( $a['name'], $b['name'] );
	} );

	$total_rows = count( $rows );
	$now_pkt    = current_time( 'j M Y, h:i A' );
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>AWS re:Invent Watch &amp; Build Party 2026 — Event Check-in Roster</title>
		<style>
			@page {
				size: A4 landscape;
				margin: 10mm 12mm 10mm 12mm;
			}
			* { box-sizing: border-box; }
			body {
				margin: 0;
				padding: 0;
				font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
				color: #0f172a;
				background: #f1f5f9;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			/* Screen Toolbar */
			.roster-screen-bar {
				position: sticky;
				top: 0;
				z-index: 1000;
				background: #0f172a;
				color: #ffffff;
				padding: 12px 24px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;
				box-shadow: 0 4px 14px rgba(0,0,0,0.2);
			}
			.roster-brand {
				display: flex;
				align-items: center;
				gap: 10px;
				font-size: 13.5px;
				font-weight: 700;
				letter-spacing: 0.02em;
			}
			.roster-pill-badge {
				background: #00DFD8;
				color: #06040C;
				font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
				font-size: 10.5px;
				font-weight: 800;
				padding: 2px 7px;
				border-radius: 4px;
				letter-spacing: 0.08em;
			}
			.roster-filters {
				display: flex;
				gap: 6px;
				background: rgba(255,255,255,0.08);
				padding: 3px;
				border-radius: 8px;
			}
			.roster-filter-btn {
				color: #94a3b8;
				text-decoration: none;
				padding: 6px 12px;
				font-size: 12px;
				font-weight: 600;
				border-radius: 6px;
				transition: all 0.15s ease;
			}
			.roster-filter-btn:hover {
				color: #ffffff;
				background: rgba(255,255,255,0.08);
			}
			.roster-filter-btn.active {
				background: #ffffff;
				color: #0f172a;
			}
			.roster-screen-actions {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.roster-btn-print {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #00DFD8;
				color: #06040C;
				border: none;
				border-radius: 6px;
				padding: 8px 16px;
				font-size: 13px;
				font-weight: 700;
				cursor: pointer;
				transition: background 0.15s ease;
			}
			.roster-btn-print:hover { background: #38efea; }
			.roster-btn-back {
				color: #cbd5e1;
				text-decoration: none;
				font-size: 12.5px;
				font-weight: 600;
				padding: 7px 12px;
				border: 1px solid rgba(255,255,255,0.2);
				border-radius: 6px;
			}
			.roster-btn-back:hover { color: #ffffff; border-color: rgba(255,255,255,0.4); }

			/* Paper Sheet Container */
			.roster-sheet {
				max-width: 1280px;
				margin: 24px auto 40px;
				background: #ffffff;
				padding: 32px 36px;
				border-radius: 8px;
				box-shadow: 0 4px 20px rgba(0,0,0,0.06);
				border: 1px solid #e2e8f0;
			}

			/* Document Header */
			.roster-header {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				padding-bottom: 16px;
				border-bottom: 2px solid #0f172a;
				margin-bottom: 16px;
			}
			.roster-org {
				font-size: 11px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.12em;
				color: #0284c7;
				margin-bottom: 4px;
			}
			.roster-title {
				font-size: 20px;
				font-weight: 800;
				color: #0f172a;
				margin: 0 0 4px;
				letter-spacing: -0.01em;
			}
			.roster-sub {
				font-size: 13px;
				font-weight: 600;
				color: #475569;
				margin: 0;
			}
			.roster-meta-box {
				text-align: right;
				font-size: 11.5px;
				color: #475569;
				line-height: 1.6;
			}
			.roster-meta-box strong { color: #0f172a; }
			.roster-scope-pill {
				display: inline-block;
				background: #f1f5f9;
				color: #0f172a;
				padding: 2px 8px;
				border-radius: 4px;
				font-weight: 700;
				font-size: 11px;
				margin-top: 4px;
				border: 1px solid #cbd5e1;
			}

			/* Table */
			.roster-table {
				width: 100%;
				border-collapse: collapse;
				font-size: 11px;
			}
			.roster-table thead {
				display: table-header-group;
			}
			.roster-table th {
				background: #f8fafc;
				color: #334155;
				font-size: 10px;
				font-weight: 800;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				padding: 8px 10px;
				border: 1px solid #cbd5e1;
				text-align: left;
			}
			.roster-table td {
				padding: 7px 10px;
				border: 1px solid #e2e8f0;
				vertical-align: middle;
				color: #0f172a;
			}
			.roster-table tbody tr {
				page-break-inside: avoid;
				break-inside: avoid;
			}
			.roster-table tbody tr:nth-child(even) td {
				background: #fafafa;
			}
			.roster-pass {
				font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
				font-weight: 800;
				color: #7928ca;
				white-space: nowrap;
				font-size: 11.5px;
			}
			.roster-name-cell {
				font-weight: 700;
				color: #0f172a;
				font-size: 12px;
			}
			.roster-cnic {
				font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
				font-weight: 700;
				color: #0f172a;
				white-space: nowrap;
				font-size: 11.5px;
			}
			.roster-tag {
				display: inline-block;
				font-size: 9.5px;
				font-weight: 700;
				text-transform: uppercase;
				padding: 1px 5px;
				border-radius: 3px;
				margin-left: 4px;
				vertical-align: middle;
			}
			.roster-tag-student { background: #e0f2fe; color: #0369a1; }
			.roster-tag-professional { background: #f3e8ff; color: #6b21a8; }
			.roster-tag-job_seeker { background: #fef3c7; color: #92400e; }

			.roster-check-box {
				width: 18px;
				height: 18px;
				border: 1.5px solid #0f172a;
				border-radius: 3px;
				display: inline-block;
				vertical-align: middle;
				margin-right: 8px;
				background: #ffffff;
			}
			.roster-sig-line {
				display: inline-block;
				border-bottom: 1px solid #94a3b8;
				width: 75px;
				height: 14px;
				vertical-align: middle;
			}

			/* Footer */
			.roster-footer {
				margin-top: 18px;
				padding-top: 12px;
				border-top: 1px solid #cbd5e1;
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 10.5px;
				color: #64748b;
			}
			.roster-security-note {
				font-weight: 600;
				color: #334155;
			}

			/* Print Media Rules */
			@media print {
				.roster-screen-bar {
					display: none !important;
				}
				body {
					background: #ffffff !important;
					color: #000000 !important;
					font-size: 10.5px !important;
				}
				.roster-sheet {
					box-shadow: none !important;
					border: none !important;
					max-width: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
				}
				.roster-table th {
					background: #f1f5f9 !important;
					border-color: #94a3b8 !important;
					color: #000000 !important;
				}
				.roster-table td {
					border-color: #cbd5e1 !important;
					color: #000000 !important;
					padding: 6px 8px !important;
				}
				.roster-header {
					border-bottom-color: #000000 !important;
				}
			}
		</style>
	</head>
	<body>
		<!-- Sticky Screen Controls -->
		<div class="roster-screen-bar">
			<div class="roster-brand">
				<span class="roster-pill-badge">AWS RE:INVENT</span>
				<span>Check-in Sheet Preview</span>
			</div>
			<div class="roster-filters">
				<a class="roster-filter-btn <?php echo 'approved' === $status_filter ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'roster' => '1', 'status' => 'approved' ), admin_url( 'admin.php' ) ) ); ?>">Approved (Gate Clearance)</a>
				<a class="roster-filter-btn <?php echo 'all' === $status_filter ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'roster' => '1', 'status' => 'all' ), admin_url( 'admin.php' ) ) ); ?>">All Applicants</a>
				<a class="roster-filter-btn <?php echo 'pending' === $status_filter ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'roster' => '1', 'status' => 'pending' ), admin_url( 'admin.php' ) ) ); ?>">Pending Review</a>
			</div>
			<div class="roster-screen-actions">
				<a class="roster-btn-back" href="<?php echo esc_url( admin_url( 'admin.php?page=aws-reinvent-registrations' ) ); ?>">← Back to Registrations</a>
				<button type="button" class="roster-btn-print" onclick="window.print()">
					<svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
					Print Roster
				</button>
			</div>
		</div>

		<!-- Document Sheet -->
		<div class="roster-sheet">
			<div class="roster-header">
				<div>
					<div class="roster-org">AWS Student Builder Group · Air University Islamabad</div>
					<h1 class="roster-title">AWS re:Invent Watch &amp; Build Party Islamabad 2026</h1>
					<p class="roster-sub">Official Security Gate Clearance &amp; Attendee Check-in Sheet</p>
				</div>
				<div class="roster-meta-box">
					<div><strong>Venue:</strong> Air University (Sector E-9 Campus), Islamabad</div>
					<div><strong>Date:</strong> Tuesday, 1 December 2026</div>
					<div><strong>Generated:</strong> <?php echo esc_html( $now_pkt ); ?></div>
					<div class="roster-scope-pill">
						Filter: <?php echo esc_html( ucfirst( $status_filter ) ); ?> · Total: <?php echo intval( $total_rows ); ?> Attendees (Vehicles: <?php echo intval( $vehicle_count ); ?>)
					</div>
				</div>
			</div>

			<table class="roster-table">
				<thead>
					<tr>
						<th style="width:34px; text-align:center;">#</th>
						<th style="width:115px;">Pass #</th>
						<th>Attendee Name</th>
						<th style="width:145px;">CNIC / B-Form</th>
						<th style="width:140px;">Phone / WhatsApp</th>
						<th>Affiliation / Institute</th>
						<th style="width:130px;">Vehicle</th>
						<th style="width:135px; text-align:center;">Gate Check-in</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td colspan="8" style="padding:28px; text-align:center; color:#64748b;">No registrations found for the selected status.</td>
						</tr>
					<?php else : ?>
						<?php $idx = 1; foreach ( $rows as $r ) : ?>
							<tr>
								<td style="text-align:center; font-weight:700; color:#64748b;"><?php echo $idx++; ?></td>
								<td class="roster-pass"><?php echo $r['reg_no'] ? esc_html( $r['reg_no'] ) : '<span style="color:#94a3b8;">Pending</span>'; ?></td>
								<td>
									<span class="roster-name-cell"><?php echo esc_html( $r['name'] ); ?></span>
									<?php if ( ! empty( $r['audience'] ) ) : ?>
										<span class="roster-tag roster-tag-<?php echo esc_attr( $r['audience'] ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $r['audience'] ) ) ); ?></span>
									<?php endif; ?>
								</td>
								<td class="roster-cnic"><?php echo esc_html( $r['cnic'] ); ?></td>
								<td>
									<div><?php echo esc_html( $r['phone'] ); ?></div>
									<div style="font-size:10px; color:#64748b;"><?php echo esc_html( $r['email'] ); ?></div>
								</td>
								<td>
									<div style="font-weight:600;"><?php echo esc_html( $r['university'] ); ?></div>
									<div style="font-size:10px; color:#64748b;"><?php echo esc_html( $r['city'] ); ?></div>
								</td>
								<td style="font-size:10.5px;"><?php echo esc_html( $r['vehicle'] ); ?></td>
								<td style="text-align:center; white-space:nowrap;">
									<span class="roster-check-box"></span>
									<span class="roster-sig-line"></span>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<div class="roster-footer">
				<div class="roster-security-note">
					PAF Air University Security: Original CNIC/B-Form matching this official roster is mandatory for campus entry at Gate 1.
				</div>
				<div>
					Official AWS SBG Roster · Page 1 of 1
				</div>
			</div>
		</div>
	</body>
	</html>
	<?php
	exit;
}

/* ========================================================================
 * 7B. PRINTABLE ATTENDEE BADGES (A4 SHEET PRINT STUDIO)
 * ======================================================================== */

function aws_sbg_render_reinvent_badges() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	$tier_filter = isset( $_GET['tier'] ) ? sanitize_text_field( wp_unslash( $_GET['tier'] ) ) : '';

	$args = array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'ASC',
		'meta_query'     => array(
			array( 'key' => 'approval_status', 'value' => 'approved' ),
		),
	);

	$query = new WP_Query( $args );

	$all_badges  = array();
	$tier_counts = array( 'ALL' => 0 );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$id          = get_the_ID();
			$reg_no      = get_post_meta( $id, 'registration_number', true );
			$name        = trim( preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title() ) );
			$tier        = aws_sbg_get_reinvent_badge_tier( $id );
			$display_num = ! empty( $reg_no ) ? $reg_no : sprintf( 'RIV-%02d', $id );
			$verify_url  = 'https://awsairuniversity.com/verify/' . rawurlencode( $display_num );

			$tier_counts['ALL']++;
			if ( ! isset( $tier_counts[ $tier ] ) ) {
				$tier_counts[ $tier ] = 0;
			}
			$tier_counts[ $tier ]++;

			// Apply tier filter if requested
			if ( ! empty( $tier_filter ) && 'ALL' !== strtoupper( $tier_filter ) && strtoupper( $tier_filter ) !== strtoupper( $tier ) ) {
				continue;
			}

			$all_badges[] = array(
				'id'         => $id,
				'reg_no'     => $display_num,
				'name'       => $name,
				'tier'       => $tier,
				'verify_url' => $verify_url,
			);
		}
		wp_reset_postdata();
	}

	// Natural sort by registration number ascending, then name
	usort( $all_badges, function ( $a, $b ) {
		if ( ! empty( $a['reg_no'] ) && ! empty( $b['reg_no'] ) ) {
			return strnatcasecmp( $a['reg_no'], $b['reg_no'] );
		}
		if ( ! empty( $a['reg_no'] ) ) {
			return -1;
		}
		if ( ! empty( $b['reg_no'] ) ) {
			return 1;
		}
		return strcasecmp( $a['name'], $b['name'] );
	} );

	$total_badges = count( $all_badges );
	$theme_uri    = get_template_directory_uri();
	$fonts_uri    = $theme_uri . '/assets/design-kit/fonts';
	$skyline_uri  = $theme_uri . '/assets/design-kit/Islamabad Landmark Footer.png';
	$aws_logo_uri = $theme_uri . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo_uri = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : $theme_uri . '/assets/images/icons/SBG-Logo-White-Transparent.png';
	$qrcode_js    = $theme_uri . '/assets/design-kit/qrcode.min.js';
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>AWS re:Invent 2026 — Attendee Badges Print Studio (A4)</title>
		<style>
			@font-face {
				font-family: 'Amazon Ember Display';
				src: url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype');
				font-weight: 400;
				font-style: normal;
				font-display: swap;
			}
			@font-face {
				font-family: 'Amazon Ember Display';
				src: url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype');
				font-weight: 700;
				font-style: normal;
				font-display: swap;
			}
			@font-face {
				font-family: 'Amazon Ember Mono';
				src: url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Rg.ttf') format('truetype');
				font-weight: 400;
				font-style: normal;
				font-display: swap;
			}
			@font-face {
				font-family: 'Amazon Ember Mono';
				src: url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Bd.ttf') format('truetype');
				font-weight: 700;
				font-style: normal;
				font-display: swap;
			}

			@page {
				size: A4 portrait;
				margin: 0;
			}

			*, *::before, *::after {
				box-sizing: border-box;
				margin: 0;
				padding: 0;
			}

			body {
				margin: 0;
				padding: 0;
				background: #0b0f17;
				color: #0f172a;
				font-family: 'Amazon Ember Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}

			/* Sticky Screen Control Bar */
			.badge-screen-bar {
				position: sticky;
				top: 0;
				z-index: 9999;
				background: #0f172a;
				color: #ffffff;
				padding: 12px 24px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;
				flex-wrap: wrap;
				box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
				border-bottom: 1px solid rgba(255, 255, 255, 0.1);
			}

			.badge-bar-left {
				display: flex;
				align-items: center;
				gap: 12px;
			}

			.badge-bar-pill {
				background: #00DFD8;
				color: #06040C;
				font-family: 'Amazon Ember Mono', ui-monospace, monospace;
				font-size: 11px;
				font-weight: 800;
				letter-spacing: 0.1em;
				padding: 3px 8px;
				border-radius: 4px;
			}

			.badge-bar-title {
				font-size: 14px;
				font-weight: 700;
				color: #ffffff;
				letter-spacing: 0.01em;
			}

			.badge-count-tag {
				font-size: 12px;
				color: #94a3b8;
				font-family: 'Amazon Ember Mono', monospace;
			}

			/* Layout Toggle Buttons */
			.badge-layout-switcher {
				display: flex;
				background: rgba(255, 255, 255, 0.08);
				padding: 3px;
				border-radius: 8px;
				gap: 4px;
			}

			.badge-layout-btn {
				background: transparent;
				border: none;
				color: #94a3b8;
				font-size: 12px;
				font-weight: 600;
				padding: 6px 14px;
				border-radius: 6px;
				cursor: pointer;
				transition: all 0.15s ease;
				display: inline-flex;
				align-items: center;
				gap: 6px;
			}

			.badge-layout-btn:hover {
				color: #ffffff;
				background: rgba(255, 255, 255, 0.05);
			}

			.badge-layout-btn.active {
				background: #00DFD8;
				color: #06040C;
				font-weight: 800;
				box-shadow: 0 2px 8px rgba(0, 223, 216, 0.3);
			}

			/* Tier Filter Dropdown */
			.badge-tier-filter-wrap {
				display: flex;
				align-items: center;
				gap: 8px;
				font-size: 12px;
				color: #cbd5e1;
			}

			.badge-tier-select {
				background: #1e293b;
				color: #ffffff;
				border: 1px solid rgba(255, 255, 255, 0.2);
				border-radius: 6px;
				padding: 5px 10px;
				font-size: 12px;
				font-weight: 600;
				cursor: pointer;
			}

			.badge-tier-select:focus {
				outline: 2px solid #00DFD8;
			}

			/* Actions */
			.badge-bar-actions {
				display: flex;
				align-items: center;
				gap: 10px;
			}

			.badge-btn-back {
				color: #cbd5e1;
				text-decoration: none;
				font-size: 12px;
				font-weight: 600;
				padding: 7px 12px;
				border: 1px solid rgba(255, 255, 255, 0.2);
				border-radius: 6px;
				transition: all 0.15s ease;
			}

			.badge-btn-back:hover {
				color: #ffffff;
				border-color: rgba(255, 255, 255, 0.4);
				background: rgba(255, 255, 255, 0.05);
			}

			.badge-btn-print {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #00DFD8;
				color: #06040C;
				border: none;
				border-radius: 6px;
				padding: 7px 18px;
				font-size: 12.5px;
				font-weight: 800;
				cursor: pointer;
				box-shadow: 0 2px 10px rgba(0, 223, 216, 0.35);
				transition: all 0.15s ease;
			}

			.badge-btn-print:hover {
				background: #38efea;
				transform: translateY(-1px);
			}

			/* Print Instructions Banner */
			.badge-vendor-tip {
				max-width: 1000px;
				margin: 14px auto 0;
				background: rgba(15, 23, 42, 0.85);
				border: 1px solid rgba(0, 223, 216, 0.3);
				border-radius: 10px;
				padding: 10px 18px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 14px;
				color: #e2e8f0;
				font-size: 12px;
			}

			.badge-vendor-tip strong {
				color: #00DFD8;
			}

			/* Printable A4 Sheets Container */
			.badges-sheet-root {
				padding: 20px 0 60px;
			}

			.a4-sheet {
				width: 210mm;
				min-height: 297mm;
				height: 297mm;
				margin: 24px auto;
				background: #ffffff;
				box-shadow: 0 10px 40px rgba(0, 0, 0, 0.45);
				display: grid;
				justify-content: center;
				align-content: center;
				position: relative;
				box-sizing: border-box;
				page-break-after: always;
				break-after: page;
				overflow: hidden;
			}

			/* Layout 4-Up (VIP Lanyard: 85mm × 119mm) */
			.mode-4up .a4-sheet {
				grid-template-columns: 85mm 85mm;
				grid-template-rows: 119mm 119mm;
				column-gap: 10mm;
				row-gap: 10mm;
				padding: 24.5mm 15mm;
			}

			.mode-4up .badge-card {
				width: 85mm;
				height: 119mm;
			}

			/* Layout 6-Up (Compact: 68mm × 95.2mm) */
			.mode-6up .a4-sheet {
				grid-template-columns: 68mm 68mm;
				grid-template-rows: 95.2mm 95.2mm 95.2mm;
				column-gap: 10mm;
				row-gap: 4.5mm;
				padding: 3.3mm 32mm;
			}

			.mode-6up .badge-card {
				width: 68mm;
				height: 95.2mm;
			}

			/* Margin crop notice outside printable area */
			.sheet-margin-label {
				position: absolute;
				bottom: 5mm;
				right: 10mm;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 7.5pt;
				color: #94a3b8;
				letter-spacing: 0.05em;
				text-transform: uppercase;
				pointer-events: none;
			}

			/* Badge Card Core Container (Aspect Ratio 5:7) */
			.badge-card {
				container-type: inline-size;
				position: relative;
				overflow: hidden;
				user-select: none;
				background: #ffffff;
				border-radius: 6px;
				border: 1px dashed rgba(100, 116, 139, 0.4);
				display: flex;
				flex-direction: column;
				justify-content: space-between;
			}

			/* Corner Cutting Crop Marks */
			.badge-crop {
				position: absolute;
				width: 4mm;
				height: 4mm;
				pointer-events: none;
				z-index: 30;
			}
			.badge-crop-tl {
				top: -1px; left: -1px;
				border-top: 1.5px solid rgba(15, 23, 42, 0.35);
				border-left: 1.5px solid rgba(15, 23, 42, 0.35);
			}
			.badge-crop-tr {
				top: -1px; right: -1px;
				border-top: 1.5px solid rgba(15, 23, 42, 0.35);
				border-right: 1.5px solid rgba(15, 23, 42, 0.35);
			}
			.badge-crop-bl {
				bottom: -1px; left: -1px;
				border-bottom: 1.5px solid rgba(15, 23, 42, 0.35);
				border-left: 1.5px solid rgba(15, 23, 42, 0.35);
			}
			.badge-crop-br {
				bottom: -1px; right: -1px;
				border-bottom: 1.5px solid rgba(15, 23, 42, 0.35);
				border-right: 1.5px solid rgba(15, 23, 42, 0.35);
			}

			/* Lanyard Punch Hole Indicator */
			.badge-punch-guide {
				position: absolute;
				top: 2.5mm;
				left: 50%;
				transform: translateX(-50%);
				width: 12mm;
				height: 3.2mm;
				border: 0.8px dashed rgba(255, 255, 255, 0.45);
				border-radius: 9999px;
				pointer-events: none;
				z-index: 20;
			}

			/* --- 1. Badge Top Canvas (25%) --- */
			.badge-header {
				position: relative;
				height: 25%;
				width: 100%;
				overflow: hidden;
				border-bottom: 1.5px solid rgba(6, 4, 12, 0.08);
				background: linear-gradient(135deg, #6B45E8 0%, #2E63E0 22%, #8749DC 52%, #9462E0 70%, #1F8CC8 86%, #0A8DC0 100%);
			}

			.badge-header-mesh {
				position: absolute;
				inset: 0;
				pointer-events: none;
				background:
					radial-gradient(120% 90% at 8% -6%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 52%),
					radial-gradient(90% 70% at 96% 104%, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 55%),
					repeating-linear-gradient(115deg, rgba(255, 255, 255, 0.045) 0px, rgba(255, 255, 255, 0.045) 1px, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0) 7px);
				mix-blend-mode: screen;
			}

			.badge-header-inner {
				position: relative;
				z-index: 10;
				display: flex;
				align-items: flex-start;
				justify-content: space-between;
				width: 100%;
				height: 100%;
				padding: clamp(8px, 3.8cqi, 16px) clamp(10px, 4.2cqi, 20px) 0;
			}

			/* re:Invent Wordmark */
			.badge-brand-stack {
				display: inline-flex;
				flex-direction: column;
				color: #ffffff;
				font-family: 'Amazon Ember Mono', monospace;
			}

			.badge-brand-row {
				display: inline-flex;
				align-items: baseline;
				gap: clamp(2px, 0.8cqi, 4px);
			}

			.badge-brand-re {
				position: relative;
				display: inline-block;
				font-size: clamp(11px, 4.2cqi, 15px);
				font-weight: 400;
				letter-spacing: -0.01em;
				line-height: 1;
				color: #ffffff;
			}

			.badge-brand-aws {
				position: absolute;
				left: 0;
				bottom: calc(100% - 0.2cqi);
				font-size: clamp(8px, 3.2cqi, 11px);
				font-weight: 700;
				letter-spacing: 0.04em;
				line-height: 1;
				color: #ffffff;
			}

			.badge-brand-invent {
				font-size: clamp(19px, 7.2cqi, 27px);
				font-weight: 400;
				letter-spacing: -0.03em;
				line-height: 0.95;
				color: #ffffff;
			}

			.badge-brand-sub {
				font-size: clamp(6.5px, 2.3cqi, 8.5px);
				font-weight: 700;
				letter-spacing: 0.03em;
				color: rgba(255, 255, 255, 0.9);
				text-transform: uppercase;
				margin-top: clamp(2px, 0.7cqi, 4px);
				line-height: 1;
			}

			/* Logos in Header */
			.badge-header-logos {
				display: flex;
				align-items: center;
				gap: clamp(8px, 2.8cqi, 14px);
			}

			.badge-logo-aws {
				height: clamp(16px, 5.8cqi, 24px);
				width: auto;
				display: block;
				object-fit: contain;
			}

			.badge-logo-sbg {
				height: clamp(16px, 6.2cqi, 26px);
				width: auto;
				display: block;
				object-fit: contain;
				filter: brightness(0) invert(1);
			}

			/* --- 2. Badge Center Body (60%) --- */
			.badge-body {
				position: relative;
				flex: 1;
				background: #ffffff;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: space-between;
				padding: clamp(16px, 5.8cqi, 28px) clamp(12px, 5cqi, 24px) clamp(6px, 2cqi, 10px);
				text-align: center;
			}

			/* Tier Role Pill Wrapper (Positioned gracefully down from header gradient) */
			.badge-tier-wrap {
				width: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				margin: clamp(6px, 2.2cqi, 12px) 0 clamp(8px, 2.8cqi, 14px);
			}

			/* Tier Role Pill */
			.badge-tier-pill {
				border-radius: 9999px;
				padding: clamp(4.5px, 1.8cqi, 8px) clamp(14px, 4.8cqi, 26px);
				font-family: 'Amazon Ember Mono', monospace;
				font-size: clamp(9.5px, 3.4cqi, 13px);
				font-weight: 800;
				letter-spacing: 0.18em;
				text-transform: uppercase;
				line-height: 1.15;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
				white-space: nowrap;
				display: inline-block;
			}

			.tier-attendee { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }
			.tier-core-team { background: #4F46E5; color: #FFFFFF; border: 1.5px solid #4338CA; }
			.tier-event-crew, .tier-crew { background: #00DFD8; color: #06040C; border: 1.5px solid #00C4BE; }
			.tier-speaker { background: #FF9900; color: #06040C; border: 1.5px solid #E68A00; }
			.tier-vip-guest, .tier-vip { background: #DB2777; color: #FFFFFF; border: 1.5px solid #BE185D; }
			.tier-organizer { background: #7C3AED; color: #FFFFFF; border: 1.5px solid #6D28D9; }
			.tier-custom { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }

			/* Attendee Name Plate with Double-Rule Frame */
			.badge-name-frame {
				width: 90%;
				border-top: 2.2px solid rgba(107, 111, 118, 0.45);
				border-bottom: 2.2px solid rgba(107, 111, 118, 0.45);
				padding: clamp(6px, 2.4cqi, 14px) 0;
				margin: clamp(3px, 1.2cqi, 6px) 0;
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: clamp(38px, 15cqi, 56px);
			}

			.badge-name {
				font-family: 'Amazon Ember Display', sans-serif;
				font-size: clamp(14px, 5.8cqi, 20px);
				font-weight: 700;
				letter-spacing: -0.015em;
				line-height: 1.1;
				color: #06040C;
				text-transform: uppercase;
				margin: 0;
				max-width: 100%;
				word-break: break-word;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
				overflow: hidden;
			}

			.badge-name.name-long {
				font-size: clamp(11.5px, 4.6cqi, 16px);
				line-height: 1.08;
			}

			/* Bottom Row: #RIV-XX and Vector QR Code */
			.badge-bottom-row {
				display: flex;
				align-items: flex-end;
				justify-content: space-between;
				width: 92%;
				gap: 8px;
			}

			.badge-id {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: clamp(12px, 4.4cqi, 18px);
				font-weight: 700;
				letter-spacing: 0.04em;
				color: #06040C;
				line-height: 1;
				text-align: left;
				flex-shrink: 0;
				padding-bottom: 3px;
			}

			.badge-qr-box {
				width: clamp(44px, 17cqi, 62px);
				height: clamp(44px, 17cqi, 62px);
				padding: 3px;
				background: #FFFFFF;
				border: 1.8px solid #06040C;
				border-radius: clamp(5px, 1.8cqi, 8px);
				flex-shrink: 0;
				display: flex;
				align-items: center;
				justify-content: center;
				overflow: hidden;
			}

			/* Prevent qrcode.js double-rendering: hide canvas, display only <img> */
			.badge-qr-box canvas {
				display: none !important;
			}

			.badge-qr-box img,
			.badge-qr-box svg {
				width: 100% !important;
				height: 100% !important;
				display: block !important;
				image-rendering: -webkit-optimize-contrast;
				image-rendering: pixelated;
			}

			/* --- 3. Badge Footer Skyline Watermark (15%) --- */
			.badge-footer {
				position: relative;
				height: 15%;
				width: 100%;
				overflow: hidden;
				background: #FFFFFF;
			}

			.badge-skyline {
				position: absolute;
				bottom: 0;
				left: 0;
				right: 0;
				width: 100% !important;
				min-width: 100% !important;
				height: auto !important;
				display: block !important;
				opacity: 0.28;
				filter: brightness(0);
				pointer-events: none;
			}

			/* --- Print Media Formatting --- */
			@media print {
				body {
					background: #ffffff !important;
					padding: 0 !important;
					margin: 0 !important;
				}

				.badge-screen-bar,
				.badge-vendor-tip {
					display: none !important;
				}

				.badges-sheet-root {
					padding: 0 !important;
					margin: 0 !important;
				}

				.a4-sheet {
					margin: 0 !important;
					box-shadow: none !important;
					page-break-after: always !important;
					break-after: page !important;
				}

				.a4-sheet:last-of-type {
					page-break-after: auto !important;
					break-after: auto !important;
				}
			}
		</style>
	</head>
	<body>

		<!-- Sticky Screen Controls -->
		<div class="badge-screen-bar">
			<div class="badge-bar-left">
				<span class="badge-bar-pill">AWS RE:INVENT 2026</span>
				<span class="badge-bar-title">Attendee Badges Print Studio</span>
				<span class="badge-count-tag" id="sheet-count-indicator"><?php echo intval( $total_badges ); ?> Badges</span>
			</div>

			<!-- 4-Up vs 6-Up Layout Switcher -->
			<div class="badge-layout-switcher">
				<button type="button" class="badge-layout-btn active" data-layout="4up" onclick="setLayout('4up')">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
					4 Per Page (VIP Lanyard · 85×119mm)
				</button>
				<button type="button" class="badge-layout-btn" data-layout="6up" onclick="setLayout('6up')">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="2" width="8" height="5.5" rx="1"/><rect x="13" y="2" width="8" height="5.5" rx="1"/><rect x="3" y="9.25" width="8" height="5.5" rx="1"/><rect x="13" y="9.25" width="8" height="5.5" rx="1"/><rect x="3" y="16.5" width="8" height="5.5" rx="1"/><rect x="13" y="16.5" width="8" height="5.5" rx="1"/></svg>
					6 Per Page (Compact · 68×95mm)
				</button>
			</div>

			<!-- Tier Filter Dropdown -->
			<div class="badge-tier-filter-wrap">
				<label for="badge-tier-filter">Filter Role:</label>
				<select id="badge-tier-filter" class="badge-tier-select" onchange="filterByTier(this.value)">
					<option value="ALL" <?php selected( empty( $tier_filter ) || 'ALL' === strtoupper( $tier_filter ) ); ?>>All Roles (<?php echo intval( $tier_counts['ALL'] ); ?>)</option>
					<?php foreach ( $tier_counts as $t_name => $t_cnt ) : ?>
						<?php if ( 'ALL' === $t_name ) continue; ?>
						<option value="<?php echo esc_attr( $t_name ); ?>" <?php selected( strtoupper( $tier_filter ), strtoupper( $t_name ) ); ?>>
							<?php echo esc_html( $t_name ); ?> (<?php echo intval( $t_cnt ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Actions -->
			<div class="badge-bar-actions">
				<a class="badge-btn-back" href="<?php echo esc_url( admin_url( 'admin.php?page=aws-reinvent-registrations' ) ); ?>">← Registrations</a>
				<button type="button" class="badge-btn-print" onclick="window.print()">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
					Print / Save PDF (Ctrl + P)
				</button>
			</div>
		</div>

		<!-- Print Vendor Guidance Tip -->
		<div class="badge-vendor-tip">
			<div>
				<strong>🖨️ Print Vendor Tip:</strong> In your browser's Print Dialog, select Destination <strong>Save as PDF</strong>, Paper Size <strong>A4</strong>, Margins <strong>None</strong> (or 0), and make sure <strong>Background graphics</strong> is <strong>CHECKED</strong>.
			</div>
			<div style="color:#94a3b8; font-size:11px;">
				Front side only · Cutting guides and corner crosshairs included
			</div>
		</div>

		<!-- Hidden Master Pool of Pre-rendered Badges -->
		<div id="badges-pool" style="display:none;">
			<?php foreach ( $all_badges as $b ) : ?>
				<?php
				$tier_class = 'tier-' . sanitize_title( $b['tier'] );
				if ( ! in_array( $tier_class, array( 'tier-attendee', 'tier-core-team', 'tier-event-crew', 'tier-crew', 'tier-speaker', 'tier-vip-guest', 'tier-vip', 'tier-organizer' ), true ) ) {
					$tier_class .= ' tier-custom';
				}
				$is_name_long = mb_strlen( $b['name'] ) > 20;
				?>
				<div class="badge-card" data-tier="<?php echo esc_attr( strtoupper( $b['tier'] ) ); ?>">
					<!-- Corner Crop Marks for Cutter -->
					<div class="badge-crop badge-crop-tl"></div>
					<div class="badge-crop badge-crop-tr"></div>
					<div class="badge-crop badge-crop-bl"></div>
					<div class="badge-crop badge-crop-br"></div>

					<!-- Faint Lanyard Punch Capsule Guide -->
					<div class="badge-punch-guide" title="Lanyard Punch Guide"></div>

					<!-- 1. Header Canvas (26%) -->
					<div class="badge-header">
						<div class="badge-header-mesh"></div>
						<div class="badge-header-inner">
							<div class="badge-brand-stack">
								<div class="badge-brand-row">
									<span class="badge-brand-re"><span class="badge-brand-aws">AWS</span>re:</span><span class="badge-brand-invent">Invent</span>
								</div>
								<div class="badge-brand-sub">WATCH &amp; BUILD PARTY 2026</div>
							</div>
							<div class="badge-header-logos">
								<img src="<?php echo esc_url( $aws_logo_uri ); ?>" alt="AWS" class="badge-logo-aws">
								<img src="<?php echo esc_url( $sbg_logo_uri ); ?>" alt="SBG" class="badge-logo-sbg">
							</div>
						</div>
					</div>

					<!-- 2. Center Body (60%) -->
					<div class="badge-body">
						<div class="badge-tier-wrap">
							<div class="badge-tier-pill <?php echo esc_attr( $tier_class ); ?>">
								<?php echo esc_html( $b['tier'] ); ?>
							</div>
						</div>
						<div class="badge-name-frame">
							<h2 class="badge-name <?php echo $is_name_long ? 'name-long' : ''; ?>">
								<?php echo esc_html( $b['name'] ); ?>
							</h2>
						</div>
						<div class="badge-bottom-row">
							<div class="badge-id">#<?php echo esc_html( $b['reg_no'] ); ?></div>
							<div class="badge-qr-box" data-qr="<?php echo esc_attr( $b['verify_url'] ); ?>" title="Verify badge #<?php echo esc_attr( $b['reg_no'] ); ?>"></div>
						</div>
					</div>

					<!-- 3. Footer Skyline Watermark (15%) -->
					<div class="badge-footer">
						<img src="<?php echo esc_url( $skyline_uri ); ?>" alt="Islamabad Skyline" class="badge-skyline">
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Dynamic A4 Sheet Container -->
		<div id="badges-sheet-root" class="badges-sheet-root mode-4up">
			<?php if ( empty( $all_badges ) ) : ?>
				<div class="a4-sheet" style="display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px; color:#64748b;">
					<h2 style="font-size:18px; color:#0f172a;">No Approved Registrations Found</h2>
					<p style="font-size:13px;">Approve attendee applications first to print their badges.</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-reinvent-registrations' ) ); ?>" class="badge-btn-back" style="color:#0f172a; border-color:#cbd5e1;">Return to Registrations</a>
				</div>
			<?php endif; ?>
		</div>

		<!-- Offline Pure Vector QRCode Library -->
		<script src="<?php echo esc_url( $qrcode_js ); ?>"></script>

		<script>
		let masterBadgePool = [];
		let currentLayoutMode = '4up';

		document.addEventListener('DOMContentLoaded', function () {
			const pool = document.getElementById('badges-pool');
			if (!pool) return;

			masterBadgePool = Array.from(pool.querySelectorAll('.badge-card'));

			// Restore saved layout mode or default to 4up
			const savedMode = localStorage.getItem('aws_sbg_badges_layout');
			if (savedMode === '6up' || savedMode === '4up') {
				currentLayoutMode = savedMode;
			}

			// Generate vector QR codes once for all elements in memory
			generateAllQRCodes();

			// Initial pagination render
			setLayout(currentLayoutMode);
		});

		function setLayout(mode) {
			currentLayoutMode = mode;
			localStorage.setItem('aws_sbg_badges_layout', mode);

			// Update button states
			document.querySelectorAll('.badge-layout-btn').forEach(btn => {
				btn.classList.toggle('active', btn.dataset.layout === mode);
			});

			renderPages();
		}

		function filterByTier(tier) {
			const url = new URL(window.location.href);
			if (!tier || tier === 'ALL') {
				url.searchParams.delete('tier');
			} else {
				url.searchParams.set('tier', tier);
			}
			window.location.href = url.toString();
		}

		function renderPages() {
			const root = document.getElementById('badges-sheet-root');
			if (!root || masterBadgePool.length === 0) return;

			root.className = 'badges-sheet-root mode-' + currentLayoutMode;
			root.innerHTML = '';

			const perPage = currentLayoutMode === '6up' ? 6 : 4;
			const totalSheets = Math.ceil(masterBadgePool.length / perPage);

			for (let i = 0; i < totalSheets; i++) {
				const sheet = document.createElement('div');
				sheet.className = 'a4-sheet';

				const slice = masterBadgePool.slice(i * perPage, (i + 1) * perPage);
				slice.forEach(card => sheet.appendChild(card));

				const label = document.createElement('div');
				label.className = 'sheet-margin-label';
				label.textContent = 'Sheet ' + (i + 1) + ' of ' + totalSheets + ' · ' + (currentLayoutMode === '4up' ? '4-Up (VIP Lanyard · 85×119mm)' : '6-Up (Compact · 68×95mm)');
				sheet.appendChild(label);

				root.appendChild(sheet);
			}

			const indicator = document.getElementById('sheet-count-indicator');
			if (indicator) {
				indicator.textContent = masterBadgePool.length + ' Badges on ' + totalSheets + ' A4 ' + (totalSheets === 1 ? 'Sheet' : 'Sheets');
			}
		}

		function generateAllQRCodes() {
			if (typeof QRCode === 'undefined') return;

			masterBadgePool.forEach(card => {
				const qrBox = card.querySelector('.badge-qr-box');
				if (!qrBox || qrBox.dataset.rendered === '1') return;

				const text = qrBox.dataset.qr;
				if (!text) return;

				new QRCode(qrBox, {
					text: text,
					width: 180,
					height: 180,
					colorDark: '#06040C',
					colorLight: '#FFFFFF',
					correctLevel: QRCode.CorrectLevel.M
				});

				// Defense-in-depth: hide canvas so only the <img> displays
				const canvas = qrBox.querySelector('canvas');
				if (canvas) {
					canvas.style.display = 'none';
				}

				qrBox.dataset.rendered = '1';
			});
		}
		</script>
	</body>
	</html>
	<?php
	exit;
}

/* ========================================================================
 * 8. CSV EXPORT
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
	fputcsv( $output, array( 'Registration #', 'Name', 'Badge Tier', 'Email', 'WhatsApp', 'CNIC', 'Institute/Company', 'Audience Type', 'City', 'LinkedIn', 'Attended AWS or Community Event Before', 'Applicant Note (Last Attended Event)', 'Admin Note (Internal)', 'Will Attend', 'Vehicle', 'Parking Policy', 'Approval Status', 'Registered At' ) );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $reg ) {
			$title       = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title( $reg->ID ) );
			$reg_number  = get_post_meta( $reg->ID, 'registration_number', true );
			$badge_tier  = aws_sbg_get_reinvent_badge_tier( $reg->ID );
			$email       = get_post_meta( $reg->ID, 'email', true );
			$whatsapp    = get_post_meta( $reg->ID, 'whatsapp', true );
			$cnic        = get_post_meta( $reg->ID, 'cnic', true );
			$university  = get_post_meta( $reg->ID, 'university', true );
			$audience    = get_post_meta( $reg->ID, 'audience_type', true );
			$city        = get_post_meta( $reg->ID, 'city', true );
			$linkedin    = get_post_meta( $reg->ID, 'linkedin', true );
			$attended    = get_post_meta( $reg->ID, 'attended_before', true );
			$why         = get_post_meta( $reg->ID, 'why_invite', true );
			$admin_note  = get_post_meta( $reg->ID, '_admin_note', true );
			$will_attend = get_post_meta( $reg->ID, 'will_attend', true );
			$has_vehicle = get_post_meta( $reg->ID, 'has_vehicle', true );
			$vehicle     = 'No';
			if ( 'yes' === $has_vehicle ) {
				$vehicle = ucfirst( get_post_meta( $reg->ID, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $reg->ID, 'vehicle_number', true );
			}
			$parking    = 'yes' === get_post_meta( $reg->ID, 'parking_policy', true ) ? 'Yes' : 'No';
			$approval   = get_post_meta( $reg->ID, 'approval_status', true ) ?: 'pending';
			$registered = get_the_date( 'Y-m-d H:i:s', $reg->ID );

			fputcsv( $output, array( $reg_number, $title, $badge_tier, $email, $whatsapp, $cnic, $university, ucfirst( str_replace( '_', ' ', $audience ) ), $city, $linkedin, ucfirst( $attended ), $why, $admin_note, ucfirst( $will_attend ), $vehicle, $parking, ucfirst( $approval ), $registered ) );
		}
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'aws_sbg_export_reinvent_csv' );
