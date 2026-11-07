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

function aws_sbg_allocate_reinvent_registration_number() {
	global $wpdb;
	$table = $wpdb->prefix . 'aws_reinvent_numbers';
	$wpdb->insert( $table, array( 'created_at' => current_time( 'mysql' ) ), array( '%s' ) );
	$num = (int) $wpdb->insert_id;
	return 'reinvent-#' . str_pad( $num, 3, '0', STR_PAD_LEFT );
}

/* ========================================================================
 * 3. FRONT-END FORM SHORTCODE
 * ======================================================================== */

function aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location ) {
	$success = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'success';
	$exists  = isset( $_GET['reinvent_registration'] ) && $_GET['reinvent_registration'] === 'exists';
	$fonts   = get_template_directory_uri() . '/assets/design-kit/fonts';
	$skyline = get_template_directory_uri() . '/assets/design-kit/Islamabad Landmark Footer.png';
	$aws_logo = get_template_directory_uri() . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo = 'https://awsairuniversity.com/wp-content/uploads/2026/09/SBG-Logo-White-Transparent.png';

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
			.rv-step-label { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#00DFD8; }
			.rv-step-title { font-weight:700; font-size:clamp(26px,3.4vw,34px); letter-spacing:-0.01em; margin:14px 0 34px; }
			.rv-progress { height:4px; background:rgba(255,255,255,0.1); border-radius:99px; overflow:hidden; margin:12px 0 26px; }
			.rv-progress-fill { height:100%; background:#00DFD8; border-radius:99px; transition:width 0.45s cubic-bezier(0.22,0.61,0.36,1); }

			.rv-fields { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
			.rv-field label { display:block; font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.62); margin-bottom:8px; }
			.rv-field label em, .rv-question-label em { font-style:normal; color:#00DFD8; }
			.rv-field input { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field input:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rv-field input::placeholder { color:rgba(255,255,255,0.32); }
			.rv-field textarea { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; line-height:1.65; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; resize:vertical; min-height:150px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
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

			.rv-commit { display:flex; align-items:flex-start; gap:14px; padding:18px 0; border-top:1px solid rgba(255,255,255,0.12); cursor:pointer; }
			.rv-commit input { width:18px; height:18px; margin-top:3px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; }
			.rv-commit-text { font-size:14.5px; line-height:1.6; color:rgba(255,255,255,0.75); }
			.rv-commit-sub { display:block; font-size:12.5px; line-height:1.55; color:rgba(255,255,255,0.42); margin-top:4px; }

			.rv-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:36px; }

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

		<?php if ( $success ) : ?>
		<div class="rv-success" x-data="reinventForm()" style="display:flex;">
			<div class="rv-success-card">
				<div class="rv-success-icon" style="animation:none;">
					<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
				</div>
				<h2 style="font-weight:700; font-size:26px; margin:0 0 10px; letter-spacing:0.02em;">Application Received</h2>
				<p style="font-size:15px; line-height:1.65; color:rgba(255,255,255,0.62);">Thanks for your interest in AWS re:Invent Watch &amp; Build Party Islamabad 2026. Only approved attendees will be informed by email.</p>
				<div style="margin-top:26px;">
					<a href="<?php echo esc_url( AWS_REINVENT_WHATSAPP_URL ); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; justify-content:center; gap:10px; background:#00DFD8; color:#06040C; font-family:'Amazon Ember Mono',monospace; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; text-decoration:none; padding:14px 26px; border-radius:10px;">Join WhatsApp community</a>
					<p style="font-size:12.5px; color:rgba(255,255,255,0.45); margin:10px 0 0; line-height:1.6;">For updates &amp; announcements.</p>
				</div>
				<p style="margin:22px 0 0;"><a class="rv-success-link" href="<?php echo esc_url( home_url() ); ?>">Back to home</a></p>
			</div>
		</div>
		<?php else : ?>

		<?php if ( $exists ) : ?>
		<div style="position:relative;z-index:2;padding:16px 20px;border:1px solid rgba(0,223,216,0.4);border-radius:10px;background:rgba(0,223,216,0.08);color:#FFFFFF;font-size:14px;line-height:1.6;margin-bottom:20px;">You've already submitted an application. We'll email you once your seat is approved.</div>
		<?php endif; ?>

		<!-- Step 0: Intro -->
		<section class="rv-intro" x-show="currentStep === 0">
			<img class="rv-skyline" src="<?php echo esc_url( $skyline ); ?>" alt="" aria-hidden="true">
			<div class="rv-intro-inner">
				<p class="rv-kicker">AWS Community Islamabad is bringing</p>
				<h1 class="rv-title"><?php echo esc_html( $title ); ?></h1>
				<p class="rv-lede"><?php echo esc_html( $subtitle ); ?></p>
				<p class="rv-lede-cta">Register below to express your interest. Approved attendees will receive a confirmation email with event ticket details.</p>

				<div class="rv-perks">
					<div class="rv-perk">
						<span class="rv-perk-idx">01</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.83z"/><path d="M7 7h.01"/></svg>Free Access</h3>
						<p>Complimentary entry for approved attendees.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">02</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h12v5a4 4 0 01-4 4H8a4 4 0 01-4-4V9z"/><path d="M16 10h1.5a2.5 2.5 0 010 5H16"/></svg>Refreshments</h3>
						<p>Food and beverages available throughout the event.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">03</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>AWS Swag</h3>
						<p>Exclusive AWS merchandise and giveaways for attendees.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">04</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>Networking</h3>
						<p>Meet builders, students, industry leaders, and cloud professionals.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">05</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><path d="M12 19v4"/><path d="M8 23h8"/></svg>Expert Sessions</h3>
						<p>Hear from industry experts on Cloud, AI, Cybersecurity, and Emerging Technologies.</p>
					</div>
					<div class="rv-perk">
						<span class="rv-perk-idx">06</span>
						<h3><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>Activities</h3>
						<p>Live watch party, interactive challenges, hands-on builds, and community experiences.</p>
					</div>
				</div>

				<button type="button" class="rv-btn rv-btn-primary" @click="nextStep()">REGISTER NOW</button>
			</div>
		</section>

		<!-- Steps 1 & 2 -->
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" x-show="currentStep > 0" x-cloak @submit="isSubmitting = true">
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
							<label class="rv-has-tip" for="rv-cnic">
								CNIC / B-Form <em>*</em>
								<span class="rv-tip-mark" tabindex="0" aria-label="Why we ask: checked at the gate for security verification."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="11" x2="12" y2="16"/><circle cx="12" cy="8" r="1" fill="currentColor" stroke="none"/></svg></span>
								<span class="rv-tip-bubble" role="tooltip">Checked at the gate for security verification.</span>
							</label>
							<input id="rv-cnic" type="text" name="cnic" inputmode="numeric" :required="currentStep === 1" pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" maxlength="15" title="Format: 00000-0000000-0" placeholder="00000-0000000-0">
						</div>
						<div class="rv-field" style="grid-column:1/-1;">
							<label id="rv-audience-label">I am a <em>*</em></label>
							<div class="rv-choices rv-choices-3" role="radiogroup" aria-labelledby="rv-audience-label">
								<label class="rv-choice">
									<input type="radio" name="audience_type" value="student" x-model="audienceType" :required="currentStep === 1">
									<span class="rv-choice-title">Student</span>
								</label>
								<label class="rv-choice">
									<input type="radio" name="audience_type" value="professional" x-model="audienceType" :required="currentStep === 1">
									<span class="rv-choice-title">Professional</span>
								</label>
								<label class="rv-choice">
									<input type="radio" name="audience_type" value="job_seeker" x-model="audienceType" :required="currentStep === 1">
									<span class="rv-choice-title">Job Seeker</span>
								</label>
							</div>
						</div>
						<div class="rv-field" x-show="audienceType" x-cloak>
							<label for="rv-affiliation" x-text="affiliationLabel()">Institute / Organization <em>*</em></label>
							<input id="rv-affiliation" type="text" name="affiliation" :required="currentStep === 1 && audienceType" :placeholder="affiliationPlaceholder()">
						</div>
						<div class="rv-field">
							<label for="rv-city">City <em>*</em></label>
							<input id="rv-city" type="text" name="city" :required="currentStep === 1" placeholder="Your city">
						</div>
						<div class="rv-field">
							<label for="rv-linkedin">LinkedIn Profile URL <em>*</em></label>
							<input id="rv-linkedin" type="url" name="linkedin" :required="currentStep === 1" placeholder="https://linkedin.com/in/username">
						</div>
						<div class="rv-field" style="grid-column:1/-1;">
							<label>Bringing a vehicle? <span style="font-weight:400;">(optional)</span></label>
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
							<p style="margin:6px 0 0; font-size:12.5px; line-height:1.5; color:rgba(255,255,255,0.5);">In the first phase, we will prioritize attendees with no vehicle.</p>
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
						<label id="rv-attended-label">Have you attended any AWS community event before? <em>*</em></label>
						<div class="rv-choices" role="radiogroup" aria-labelledby="rv-attended-label">
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="yes" :required="currentStep === 2">
								<span class="rv-choice-title">Yes</span>
							</label>
							<label class="rv-choice">
								<input type="radio" name="attended_before" value="no" :required="currentStep === 2">
								<span class="rv-choice-title">No</span>
							</label>
						</div>
					</div>

					<div class="rv-field">
						<label for="rv-why">Why do you want to be invited to AWS re:Invent Islamabad 2026? <em>*</em></label>
						<textarea id="rv-why" name="why_invite" rows="6" :required="currentStep === 2" placeholder="Tell us about your experience with AWS, community involvement, and what you're hoping to gain from the event."></textarea>
					</div>

					<label class="rv-commit">
						<input type="checkbox" name="confirm" :required="currentStep === 2">
						<span class="rv-commit-text">I understand this is an invite-only event. I will be notified after review if my application is approved.</span>
					</label>
					<label class="rv-commit">
						<input type="checkbox" name="will_attend" value="yes" :required="currentStep === 2">
						<span class="rv-commit-text">I will attend the event if accepted.<span class="rv-commit-sub">Seats are limited and resources are planned per attendee, so a no-show takes a seat from another builder.</span></span>
					</label>
					<label class="rv-commit">
						<input type="checkbox" name="parking_policy" value="yes" :required="currentStep === 2">
						<span class="rv-commit-text">I understand that due to limited parking capacity, attendees with no vehicle will be prioritized.<span class="rv-commit-sub">We encourage using public transport (InDrive, Yango, Bykea).</span></span>
					</label>

					<div class="rv-actions" style="margin-top:32px;">
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
	</div>

	<script>
		function reinventForm() {
			return {
				currentStep: 0,
				audienceType: '',
				hasVehicle: 'no',
				isSubmitting: false,
				nextStep() { this.currentStep++; },
				prevStep() { if ( this.currentStep > 0 ) { this.currentStep--; } },
				affiliationLabel() {
					const labels = {
						student: 'Institute Name',
						professional: 'Organization Name',
						job_seeker: 'Most Recent Organization / Institute',
					};
					return labels[ this.audienceType ] || 'Institute / Organization';
				},
				affiliationPlaceholder() {
					const placeholders = {
						student: 'Your institute name',
						professional: 'Company name',
						job_seeker: 'Where did you last study or work?',
					};
					return placeholders[ this.audienceType ] || '';
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
	$cnic       = isset( $_POST['cnic'] ) ? sanitize_text_field( wp_unslash( $_POST['cnic'] ) ) : '';
	$university = isset( $_POST['affiliation'] ) ? sanitize_text_field( wp_unslash( $_POST['affiliation'] ) ) : '';
	$audience   = isset( $_POST['audience_type'] ) ? sanitize_key( wp_unslash( $_POST['audience_type'] ) ) : '';
	$city       = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$linkedin   = isset( $_POST['linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['linkedin'] ) ) : '';
	$attended   = isset( $_POST['attended_before'] ) ? sanitize_text_field( wp_unslash( $_POST['attended_before'] ) ) : '';
	$why        = isset( $_POST['why_invite'] ) ? sanitize_textarea_field( wp_unslash( $_POST['why_invite'] ) ) : '';
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

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $cnic ) || ! preg_match( '/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', $cnic ) || empty( $university ) || empty( $city ) || empty( $linkedin ) || empty( $attended ) || empty( $why ) || 'yes' !== $will_attend || ! $parking_policy || ! $valid_audience ) {
		wp_die( 'Please fill in all required fields.' );
	}

	// Duplicate guard by email.
	$existing = get_posts( array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'meta_key'       => 'email',
		'meta_value'     => $email,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		$redirect = add_query_arg( array( 'reinvent_registration' => 'exists' ), $redirect );
		wp_safe_redirect( $redirect );
		exit;
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'aws_reinvent_reg',
		'post_title'  => $full_name,
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_die( 'Failed to save registration. Please try again.' );
	}

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

	/* Applicant confirmation (under review) */
	if ( function_exists( 'aws_sbg_mail_send' ) ) {
		$confirm_body = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#FFFFFF;">' . esc_html( $full_name ) . '</strong>,</p>'
			. '<p style="margin:0 0 14px 0;">Thank you for your interest in <strong style="color:#FFFFFF;">AWS re:Invent Watch &amp; Build Party 2026</strong>, hosted by the AWS Student Builder Group at Air University Islamabad.</p>'
			. '<p style="margin:0 0 14px 0;">Your application has been received and is now under review. If approved, you\'ll receive your re:Invent pass and event details.</p>'
			. '<p style="margin:0 0 8px 0; font-weight:700; color:#FFFFFF;">What happens next</p>'
			. '<ul style="margin:0 0 14px 20px; padding:0;">'
			. '<li style="margin:0 0 7px 0;">Our team reviews your application.</li>'
			. '<li style="margin:0;">You\'ll be notified by email once your seat is confirmed.</li>'
			. '</ul>'
			. '<p style="margin:16px 0 0 0;">AWS Student Builder Group at Air University Islamabad<br>info@awsairuniversity.com · +923492449953</p>';
		aws_sbg_mail_send( 'reinvent', $email, 'Your AWS re:Invent application is under review', 'Application Received', 'Thank you for your interest', $confirm_body, AWS_REINVENT_WHATSAPP_URL, 'Join WhatsApp Community' );

		/* Admin notification (optional) */
		if ( function_exists( 'aws_sbg_mail_get_settings' ) ) {
			$mail_settings = aws_sbg_mail_get_settings();
			if ( 'yes' === ( $mail_settings['notify_admin_rsvp'] ?? 'yes' ) ) {
				$admin_to   = ! empty( $mail_settings['admin_email'] ) ? $mail_settings['admin_email'] : get_option( 'admin_email' );
				$admin_body = '<p style="margin:0 0 12px 0;">New re:Invent application:</p>'
					. '<p style="margin:0 0 6px 0;"><strong style="color:#FFFFFF;">' . esc_html( $full_name ) . '</strong> · ' . esc_html( ucfirst( str_replace( '_', ' ', $audience ) ) ) . '</p>'
					. '<p style="margin:0 0 6px 0;">' . esc_html( $university ) . ' · ' . esc_html( $city ) . '</p>'
					. '<p style="margin:0 0 6px 0;">Attended before: ' . esc_html( ucfirst( $attended ) ) . '</p>'
					. '<p style="margin:0;">Why: ' . esc_html( wp_trim_words( $why, 40, '…' ) ) . '</p>';
				aws_sbg_mail_send( 'reinvent', $admin_to, 'New re:Invent application · ' . esc_html( $full_name ), 'New Application', 'Review needed', $admin_body, admin_url( 'admin.php?page=aws-reinvent-registrations' ), 'Open registrations' );
			}
		}
	}

	$redirect = wp_get_referer() ? wp_get_referer() : home_url();
	$redirect = add_query_arg( array( 'reinvent_registration' => 'success' ), $redirect );
	wp_safe_redirect( $redirect );
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

	if ( isset( $_GET['roster'] ) ) {
		aws_sbg_render_reinvent_roster();
	}

	if ( isset( $_GET['action'], $_GET['reg_id'], $_GET['_wpnonce'] ) && in_array( $_GET['action'], array( 'approve', 'reject' ), true ) ) {
		$reg_id = intval( $_GET['reg_id'] );
		if ( wp_verify_nonce( $_GET['_wpnonce'], 'aws_reinvent_review_' . $reg_id ) ) {
			$status      = 'approve' === $_GET['action'] ? 'approved' : 'rejected';
			$prev_status = get_post_meta( $reg_id, 'notified_status', true );
			update_post_meta( $reg_id, 'approval_status', $status );

			if ( 'approved' === $status && $prev_status !== 'approved' && function_exists( 'aws_sbg_mail_send' ) ) {
				$name   = preg_replace( '/^reinvent-#\d+\s*-\s*/', '', get_the_title( $reg_id ) );
				$reg_no = get_post_meta( $reg_id, 'registration_number', true );
				if ( empty( $reg_no ) ) {
					$reg_no = aws_sbg_allocate_reinvent_registration_number();
					update_post_meta( $reg_id, 'registration_number', $reg_no );
					wp_update_post( array( 'ID' => $reg_id, 'post_title' => $reg_no . ' - ' . $name ) );
				}

				$to_email = get_post_meta( $reg_id, 'email', true );
				if ( $to_email ) {
					$body = '<p style="margin:0 0 12px 0;">Hi <strong style="color:#FFFFFF;">' . esc_html( $name ) . '</strong>,</p>'
						. '<p style="margin:0 0 14px 0;">Congratulations, your application for <strong style="color:#FFFFFF;">AWS re:Invent Watch &amp; Build Party 2026</strong> has been approved.</p>'
						. '<div style="margin:16px 0 20px 0;border:1px dashed #00DFD8;border-radius:10px;padding:14px 20px;">'
						. '<div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:#00DFD8;margin-bottom:6px;">Event Pass</div>'
						. '<div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:22px;font-weight:700;color:#FFFFFF;letter-spacing:0.03em;">' . esc_html( $reg_no ) . '</div>'
						. '</div>'
						. '<p style="margin:0 0 8px 0; font-weight:700; color:#FFFFFF;">What we have for you</p>'
						. '<ul style="margin:0 0 16px 20px; padding:0;">'
						. '<li style="margin:0 0 7px 0;">Expert sessions and talks</li>'
						. '<li style="margin:0 0 7px 0;">Refreshments (snacks, food, drinks)</li>'
						. '<li style="margin:0;">Swags (subject to availability and activity participation)</li>'
						. '</ul>'
						. '<p style="margin:16px 0 0 0;">AWS Student Builder Group at Air University Islamabad<br>info@awsairuniversity.com · +923492449953</p>';
					aws_sbg_mail_send( 'reinvent', $to_email, 'You\'re approved for AWS re:Invent Watch & Build Party', 'Application Approved', 'Congratulations', $body );
					update_post_meta( $reg_id, 'notified', 'yes' );
				}
			}

			update_post_meta( $reg_id, 'notified_status', $status );
		}
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

	aws_sbg_render_admin_nav_tabs( 'reinvent' );
	?>
	<div class="wrap rvadm" style="max-width:1200px;">
		<style>
			.rvadm{--ink:#0f172a;--muted:#64748b;--line:#e2e8f0;--surface:#ffffff;--surface2:#f8fafc;--brand:#7928ca;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:var(--ink);}
			.rvadm *{box-sizing:border-box;}
			.rvadm h1{margin:0;font-size:22px;font-weight:700;letter-spacing:-0.01em;color:var(--ink);}
			.rvadm .rvadm-sub{font-size:13px;color:var(--muted);margin-top:4px;}

			.rvadm-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:20px;}
			.rvadm-actions{display:flex;gap:8px;flex-wrap:wrap;}

			.rvadm-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:6px;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;transition:border-color .15s ease,background .15s ease;}
			.rvadm-btn:hover{border-color:#cbd5e1;background:var(--surface2);}

			.rvadm-filters{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:14px 16px;margin-bottom:16px;}
			.rvadm-filters form{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;}
			.rvadm-field{display:flex;flex-direction:column;gap:5px;}
			.rvadm-field label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--muted);}
			.rvadm-field select,.rvadm-field input{padding:8px 10px;border:1px solid var(--line);border-radius:6px;font-size:13px;color:var(--ink);background:var(--surface);}
			.rvadm-field select{min-width:160px;}
			.rvadm-field input{min-width:240px;}
			.rvadm-field select:focus,.rvadm-field input:focus{outline:2px solid var(--brand);outline-offset:1px;border-color:var(--brand);}
			.rvadm-search-btn{padding:8px 16px;border-radius:6px;border:1px solid var(--brand);background:var(--brand);color:#ffffff;font-size:13px;font-weight:600;cursor:pointer;}
			.rvadm-search-btn:hover{background:#6b22c0;border-color:#6b22c0;}

			.rvadm-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
			.rvadm-stat{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:16px 18px;}
			.rvadm-stat-num{font-size:28px;font-weight:700;color:var(--ink);line-height:1;}
			.rvadm-stat-label{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--muted);margin-top:8px;}
			.rvadm-dot{width:8px;height:8px;border-radius:50%;background:#cbd5e1;flex-shrink:0;}
			.rvadm-dot-pending{background:#d97706;}
			.rvadm-dot-approved{background:#059669;}
			.rvadm-dot-rejected{background:#dc2626;}

			.rvadm-table-wrap{background:var(--surface);border:1px solid var(--line);border-radius:10px;overflow-x:auto;}
			.rvadm-table-wrap table{width:100%;border-collapse:collapse;font-size:13px;min-width:980px;}
			.rvadm-table-wrap thead th{background:var(--surface2);text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--muted);padding:11px 14px;border-bottom:1px solid var(--line);white-space:nowrap;}
			.rvadm-table-wrap tbody td{padding:13px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;white-space:nowrap;}
			.rvadm-table-wrap tbody tr:last-child td{border-bottom:0;}
			.rvadm-table-wrap tbody tr:hover{background:#fafafa;}
			.rvadm-reg{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;color:var(--brand);}
			.rvadm-name{font-weight:600;color:var(--ink);white-space:normal;}
			.rvadm-muted{color:var(--muted);}
			.rvadm-mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:var(--muted);}
			.rvadm-null{color:#cbd5e1;}

			.rvadm-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:9999px;font-size:11px;font-weight:700;}
			.rvadm-badge-pending{background:#fef3c7;color:#b45309;}
			.rvadm-badge-approved{background:#d1fae5;color:#047857;}
			.rvadm-badge-rejected{background:#fee2e2;color:#b91c1c;}

			.rvadm-row-actions{display:flex;gap:8px;justify-content:flex-end;}
			.rvadm-approve{padding:6px 12px;border-radius:6px;border:1px solid #059669;background:#059669;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;}
			.rvadm-approve:hover{background:#047857;border-color:#047857;}
			.rvadm-reject{padding:6px 12px;border-radius:6px;border:1px solid #fca5a5;background:#ffffff;color:#b91c1c;font-size:12px;font-weight:600;text-decoration:none;}
			.rvadm-reject:hover{background:#fef2f2;}

			.rvadm-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 16px;border-top:1px solid var(--line);font-size:12px;color:var(--muted);flex-wrap:wrap;}
			.rvadm-pages{display:flex;gap:6px;}
			.rvadm-pages a,.rvadm-pages span{display:inline-block;padding:4px 10px;border:1px solid var(--line);border-radius:6px;text-decoration:none;color:var(--ink);font-size:12px;}
			.rvadm-pages .current{background:var(--brand);border-color:var(--brand);color:#ffffff;}

			@media (max-width:782px){.rvadm-stats{grid-template-columns:1fr 1fr;}.rvadm-field select,.rvadm-field input{min-width:100%;}}
		</style>

		<div class="rvadm-head">
			<div>
				<h1>re:Invent Watch &amp; Build Party Registrations</h1>
				<p class="rvadm-sub">Review, approve, or reject applications. Use the roster for a printable event-day list.</p>
			</div>
			<div class="rvadm-actions">
				<a class="rvadm-btn" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'export_csv' => '1' ) ) ); ?>">Export CSV</a>
				<a class="rvadm-btn" href="<?php echo esc_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'roster' => '1' ) ) ); ?>" target="_blank">Print Roster</a>
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
					<input id="rvadm-search" type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="Name, email, phone, or reg #">
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
						<th>Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Institute / City</th>
						<th>Vehicle</th>
						<th>Parking</th>
						<th>Status</th>
						<th>Submitted</th>
						<th style="text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $query->have_posts() ) : ?>
						<tr>
							<td colspan="10" class="rvadm-muted" style="padding:40px; text-align:center;">No registrations found.</td>
						</tr>
					<?php else : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php
							$reg_id      = get_the_ID();
							$name        = preg_replace( '/^reinvent-#\d+\s*[–\-]\s*/', '', get_the_title() );
							$reg_number  = get_post_meta( $reg_id, 'registration_number', true );
							$email       = get_post_meta( $reg_id, 'email', true );
							$phone       = get_post_meta( $reg_id, 'whatsapp', true );
							$university  = get_post_meta( $reg_id, 'university', true );
							$city        = get_post_meta( $reg_id, 'city', true );
							$approval    = get_post_meta( $reg_id, 'approval_status', true ) ?: 'pending';
							$has_vehicle = get_post_meta( $reg_id, 'has_vehicle', true );
							$vehicle     = 'No';
							if ( 'yes' === $has_vehicle ) {
								$vehicle = ucfirst( get_post_meta( $reg_id, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $reg_id, 'vehicle_number', true );
							}
							$parking_ok = 'yes' === get_post_meta( $reg_id, 'parking_policy', true );
							$org_city   = trim( $university . ( $city ? ' · ' . $city : '' ) );
							?>
							<tr>
								<td class="rvadm-reg"><?php echo $reg_number ? esc_html( $reg_number ) : '<span class="rvadm-null">—</span>'; ?></td>
								<td class="rvadm-name"><?php echo esc_html( $name ); ?></td>
								<td class="rvadm-mono"><?php echo esc_html( $email ); ?></td>
								<td class="rvadm-mono"><?php echo esc_html( $phone ); ?></td>
								<td class="rvadm-muted"><?php echo esc_html( $org_city ); ?></td>
								<td class="rvadm-muted"><?php echo esc_html( $vehicle ); ?></td>
								<td><?php echo $parking_ok ? '<span style="color:#059669;font-weight:700;">Yes</span>' : '<span class="rvadm-null">—</span>'; ?></td>
								<td>
									<?php if ( 'approved' === $approval ) : ?>
										<span class="rvadm-badge rvadm-badge-approved">Approved</span>
									<?php elseif ( 'rejected' === $approval ) : ?>
										<span class="rvadm-badge rvadm-badge-rejected">Rejected</span>
									<?php else : ?>
										<span class="rvadm-badge rvadm-badge-pending">Pending</span>
									<?php endif; ?>
								</td>
								<td class="rvadm-muted"><?php echo esc_html( get_the_date( 'M j, Y', $reg_id ) ); ?></td>
								<td>
									<?php if ( 'pending' === $approval ) : ?>
										<div class="rvadm-row-actions">
											<a class="rvadm-approve" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'action' => 'approve', 'reg_id' => $reg_id ) ), 'aws_reinvent_review_' . $reg_id ) ); ?>">Approve</a>
											<a class="rvadm-reject" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'aws-reinvent-registrations', 'action' => 'reject', 'reg_id' => $reg_id ) ), 'aws_reinvent_review_' . $reg_id ) ); ?>">Reject</a>
										</div>
									<?php else : ?>
										<span class="rvadm-null">—</span>
									<?php endif; ?>
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
	</div>
	<?php
}

function aws_sbg_render_reinvent_roster() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	$args = array(
		'post_type'      => 'aws_reinvent_reg',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	$query = new WP_Query( $args );

	$priority = array( 'pending' => 0, 'approved' => 1, 'rejected' => 2 );
	$rows     = array();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$id          = get_the_ID();
			$reg_no      = get_post_meta( $id, 'registration_number', true );
			$name        = preg_replace( '/^reinvent-#\d+\s*-\s*/', '', get_the_title() );
			$phone       = get_post_meta( $id, 'whatsapp', true );
			$has_vehicle = get_post_meta( $id, 'has_vehicle', true );
			$vehicle     = 'No';
			if ( 'yes' === $has_vehicle ) {
				$vehicle = ucfirst( get_post_meta( $id, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $id, 'vehicle_number', true );
			}
			$parking  = 'yes' === get_post_meta( $id, 'parking_policy', true );
			$approval = get_post_meta( $id, 'approval_status', true ) ?: 'pending';
			$rows[]   = compact( 'reg_no', 'name', 'phone', 'vehicle', 'parking', 'approval' );
		}
		wp_reset_postdata();
	}
	usort( $rows, function ( $a, $b ) use ( $priority ) {
		return $priority[ $a['approval'] ] <=> $priority[ $b['approval'] ];
	} );
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>re:Invent Event Roster</title>
		<style>
			body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; margin: 24px; }
			table { width: 100%; border-collapse: collapse; font-size: 13px; }
			th, td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
			th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; border-bottom: 2px solid #cbd5e1; }
			.status-pending { color: #b45309; font-weight: 700; }
			.status-approved { color: #047857; font-weight: 700; }
			.status-rejected { color: #b91c1c; font-weight: 700; }
			@media print { body { margin: 0; } }
		</style>
	</head>
	<body>
		<h1 style="font-size:20px; margin:0 0 4px;">AWS re:Invent Watch &amp; Build Party 2026</h1>
		<p style="font-size:13px; color:#475569; margin:0 0 18px;">Event roster</p>
		<table>
			<thead>
				<tr>
					<th>Pass</th>
					<th>Name</th>
					<th>Phone</th>
					<th>Vehicle</th>
					<th>Parking</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr>
					<td style="font-family:monospace; font-weight:700;"><?php echo $r['reg_no'] ? esc_html( $r['reg_no'] ) : ''; ?></td>
					<td><?php echo esc_html( $r['name'] ); ?></td>
					<td><?php echo esc_html( $r['phone'] ); ?></td>
					<td><?php echo esc_html( $r['vehicle'] ); ?></td>
					<td><?php echo $r['parking'] ? '✓' : ''; ?></td>
					<td class="status-<?php echo esc_attr( $r['approval'] ); ?>"><?php echo esc_html( ucfirst( $r['approval'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<script>window.onload = function(){ window.print(); };</script>
	</body>
	</html>
	<?php
	exit;
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
	fputcsv( $output, array( 'Registration #', 'Name', 'Email', 'WhatsApp', 'CNIC/B-Form', 'Institute/Org', 'Audience Type', 'City', 'LinkedIn', 'Attended AWS Event Before', 'Why Invite', 'Will Attend', 'Vehicle', 'Parking Policy', 'Approval Status', 'Registered At' ) );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $reg ) {
			$title       = preg_replace( '/^reinvent-#\d+\s*-\s*/', '', get_the_title( $reg->ID ) );
			$reg_number  = get_post_meta( $reg->ID, 'registration_number', true );
			$email       = get_post_meta( $reg->ID, 'email', true );
			$whatsapp    = get_post_meta( $reg->ID, 'whatsapp', true );
			$cnic        = get_post_meta( $reg->ID, 'cnic', true );
			$university  = get_post_meta( $reg->ID, 'university', true );
			$audience    = get_post_meta( $reg->ID, 'audience_type', true );
			$city        = get_post_meta( $reg->ID, 'city', true );
			$linkedin    = get_post_meta( $reg->ID, 'linkedin', true );
			$attended    = get_post_meta( $reg->ID, 'attended_before', true );
			$why         = get_post_meta( $reg->ID, 'why_invite', true );
			$will_attend = get_post_meta( $reg->ID, 'will_attend', true );
			$has_vehicle = get_post_meta( $reg->ID, 'has_vehicle', true );
			$vehicle     = 'No';
			if ( 'yes' === $has_vehicle ) {
				$vehicle = ucfirst( get_post_meta( $reg->ID, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $reg->ID, 'vehicle_number', true );
			}
			$parking    = 'yes' === get_post_meta( $reg->ID, 'parking_policy', true ) ? 'Yes' : 'No';
			$approval   = get_post_meta( $reg->ID, 'approval_status', true ) ?: 'pending';
			$registered = get_the_date( 'Y-m-d H:i:s', $reg->ID );

			fputcsv( $output, array( $reg_number, $title, $email, $whatsapp, $cnic, $university, ucfirst( str_replace( '_', ' ', $audience ) ), $city, $linkedin, ucfirst( $attended ), $why, ucfirst( $will_attend ), $vehicle, $parking, ucfirst( $approval ), $registered ) );
		}
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'aws_sbg_export_reinvent_csv' );
