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
	$fonts   = get_template_directory_uri() . '/assets/design-kit/fonts';
	$skyline = get_template_directory_uri() . '/assets/design-kit/Islamabad Landmark Footer.png';

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

			.rv-topbar { position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px clamp(20px,5vw,48px); border-bottom:1px solid rgba(255,255,255,0.08); }
			.rv-topbar a, .rv-topbar span { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; text-decoration:none; }
			.rv-topbar a { color:rgba(255,255,255,0.72); }
			.rv-topbar a:hover { color:#00DFD8; }
			.rv-topbar span { color:rgba(255,255,255,0.35); }

			/* Step 0 - intro */
			.rv-intro { position:relative; z-index:1; display:flex; align-items:center; padding:clamp(48px,9vh,110px) clamp(20px,6vw,72px) clamp(180px,26vh,260px); min-height:82vh; }
			.rv-skyline { position:absolute; left:0; bottom:0; width:100%; height:auto; opacity:0.42; pointer-events:none; user-select:none; z-index:0; }
			.rv-intro-inner { position:relative; z-index:1; max-width:700px; }
			.rv-kicker { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.22em; text-transform:uppercase; color:#00DFD8; margin:0 0 22px; }
			.rv-title { font-weight:700; font-size:clamp(34px,5.2vw,58px); line-height:1.06; letter-spacing:-0.02em; margin:0 0 24px; }
			.rv-lede { font-size:17px; line-height:1.75; color:rgba(255,255,255,0.66); max-width:52ch; margin:0 0 12px; }
			.rv-lede-cta { font-size:16px; line-height:1.7; font-weight:700; color:rgba(255,255,255,0.66); max-width:52ch; margin:0; }
			.rv-perks { display:grid; grid-template-columns:1fr 1fr; gap:0 clamp(28px,5vw,64px); margin:clamp(44px,7vh,64px) 0 clamp(40px,6vh,56px); }
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
			.rv-step-title { font-weight:700; font-size:clamp(26px,3.4vw,34px); letter-spacing:-0.01em; margin:14px 0 10px; }
			.rv-step-sub { font-size:15px; line-height:1.6; color:rgba(255,255,255,0.58); margin:0 0 36px; }

			.rv-fields { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
			.rv-field label { display:block; font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.62); margin-bottom:8px; }
			.rv-field label em, .rv-question-label em { font-style:normal; color:#00DFD8; }
			.rv-field input { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field input:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rv-field input::placeholder { color:rgba(255,255,255,0.32); }
			.rv-field textarea { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; line-height:1.65; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; resize:vertical; min-height:150px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rv-field textarea:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rv-field textarea::placeholder { color:rgba(255,255,255,0.32); }
			.rv-field-hint { font-size:13px; line-height:1.6; color:rgba(255,255,255,0.5); margin:0 0 10px; }

			.rv-choices { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
			.rv-choice { display:flex; align-items:center; gap:12px; padding:16px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.14); border-radius:12px; cursor:pointer; transition:border-color 0.15s ease, background 0.15s ease; }
			.rv-choice:hover { border-color:rgba(255,255,255,0.4); }
			.rv-choice:has(input:checked) { border-color:#00DFD8; background:rgba(0,223,216,0.08); }
			.rv-choice input { width:18px; height:18px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; }
			.rv-choice-title { display:block; font-size:15px; font-weight:500; color:#FFFFFF; }
			.rv-choice-hint { display:block; font-size:12px; color:rgba(255,255,255,0.5); margin-top:3px; }

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
				.rv-intro { padding-bottom:190px; min-height:auto; }
				.rv-fields, .rv-choices { grid-template-columns:1fr; }
				.rv-actions { flex-direction:column-reverse; }
				.rv-actions .rv-btn { width:100%; }
			}
		</style>

		<div class="rv-mesh" aria-hidden="true"></div>

		<header class="rv-topbar">
			<a href="<?php echo esc_url( home_url() ); ?>">AWS Student Builder Group</a>
			<span>re:Invent 2026</span>
		</header>

		<?php if ( $success ) : ?>
		<div class="rv-success" x-data="reinventForm()" style="display:flex;">
			<div class="rv-success-card">
				<div class="rv-success-icon" style="animation:none;">
					<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
				</div>
				<h2 style="font-weight:700; font-size:26px; margin:0 0 10px;">RSVP received</h2>
				<p style="font-size:15px; line-height:1.65; color:rgba(255,255,255,0.62);">Your application is under review. We'll notify you by email if your seat is approved for AWS re:Invent.</p>
				<div class="rv-code">
					<span class="rv-code-label">Registration number</span>
					<span class="rv-code-value"><?php echo esc_html( $reg_id ); ?></span>
				</div>
				<p style="margin:6px 0 0;"><a class="rv-success-link" href="<?php echo esc_url( home_url() ); ?>">Back to home</a></p>
			</div>
		</div>
		<?php else : ?>

		<!-- Step 0: Intro -->
		<section class="rv-intro" x-show="currentStep === 0">
			<img class="rv-skyline" src="<?php echo esc_url( $skyline ); ?>" alt="" aria-hidden="true">
			<div class="rv-intro-inner">
				<p class="rv-kicker">AWS Community Islamabad is bringing</p>
				<h1 class="rv-title"><?php echo esc_html( $title ); ?></h1>
				<p class="rv-lede"><?php echo esc_html( $subtitle ); ?></p>
				<p class="rv-lede-cta">Apply below to express your interest. Approved attendees will receive a confirmation email with event ticket details.</p>

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

				<button type="button" class="rv-btn rv-btn-primary" @click="nextStep()">RSVP INVITE</button>
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
					<h2 class="rv-step-title">Tell us about yourself</h2>
					<p class="rv-step-sub">A few details so we can review your application.</p>

					<div class="rv-fields">
						<div class="rv-field">
							<label for="rv-full-name">Full Name <em>*</em></label>
							<input id="rv-full-name" type="text" name="full_name" :required="currentStep === 1" placeholder="Muhammad Ali">
						</div>
						<div class="rv-field">
							<label for="rv-email">Email Address <em>*</em></label>
							<input id="rv-email" type="email" name="email" :required="currentStep === 1" placeholder="ali@example.com">
						</div>
						<div class="rv-field">
							<label for="rv-whatsapp">WhatsApp Number <em>*</em></label>
							<input id="rv-whatsapp" type="tel" name="whatsapp" :required="currentStep === 1" placeholder="03000000000">
						</div>
						<div class="rv-field">
							<label for="rv-university">University / Organization <em>*</em></label>
							<input id="rv-university" type="text" name="university" :required="currentStep === 1" placeholder="Air University Islamabad">
						</div>
						<div class="rv-field">
							<label for="rv-city">City <em>*</em></label>
							<input id="rv-city" type="text" name="city" :required="currentStep === 1" placeholder="Islamabad">
						</div>
						<div class="rv-field">
							<label for="rv-linkedin">LinkedIn Profile URL <em>*</em></label>
							<input id="rv-linkedin" type="url" name="linkedin" :required="currentStep === 1" placeholder="https://linkedin.com/in/username">
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
					<h2 class="rv-step-title">Almost done</h2>
					<p class="rv-step-sub">A few final details to complete your application.</p>

					<div class="rv-field" style="margin-bottom:36px;">
						<label for="rv-why">Why do you want to be invited to AWS re:Invent Islamabad 2026? <em>*</em></label>
						<p class="rv-field-hint">This event is designed for passionate builders, learners, and technology enthusiasts. Tell us about your experience with AWS, community involvement, and what you're hoping to gain from the event.</p>
						<textarea id="rv-why" name="why_invite" rows="6" :required="currentStep === 2" placeholder="Share your AWS experience, certifications, projects, community involvement, or why you'd like to attend (100-300 words)"></textarea>
					</div>

					<div class="rv-field" style="margin-bottom:40px;">
						<label id="rv-attended-label">Have you attended any AWS community event before? <em>*</em></label>
						<div class="rv-choices" role="radiogroup" aria-labelledby="rv-attended-label">
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

					<label class="rv-commit">
						<input type="checkbox" name="confirm" :required="currentStep === 2">
						<span class="rv-commit-text">I understand this is an invite-only event. I will be notified after review if my application is approved.</span>
					</label>
					<label class="rv-commit">
						<input type="checkbox" name="will_attend" value="yes" :required="currentStep === 2">
						<span class="rv-commit-text">I will attend the event if accepted.<span class="rv-commit-sub">Seats are limited and resources are planned per attendee, so a no-show takes a seat from another builder.</span></span>
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
	$why        = isset( $_POST['why_invite'] ) ? sanitize_textarea_field( wp_unslash( $_POST['why_invite'] ) ) : '';
	$will_attend = isset( $_POST['will_attend'] ) ? sanitize_text_field( wp_unslash( $_POST['will_attend'] ) ) : '';

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $university ) || empty( $city ) || empty( $linkedin ) || empty( $attended ) || empty( $why ) || 'yes' !== $will_attend ) {
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
	update_post_meta( $post_id, 'why_invite', $why );
	update_post_meta( $post_id, 'will_attend', $will_attend );
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
