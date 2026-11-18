<?php
/**
 * AWS SBG — Community Partner Application System (/community-partner/)
 *
 * Allows any student or tech community to apply as a Community Partner.
 * Approving an application automatically generates their unique tracking code
 * in `aws_reinvent_partners` and emails them the Partners WhatsApp group link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AWS_PARTNER_WHATSAPP_URL' ) ) {
	define( 'AWS_PARTNER_WHATSAPP_URL', 'https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l' );
}

/* ========================================================================
 * 1. CUSTOM POST TYPE
 * ======================================================================== */

function aws_sbg_register_partner_app_post_type() {
	register_post_type( 'aws_partner_app', array(
		'labels' => array(
			'name'          => 'Partner Applications',
			'singular_name' => 'Partner Application',
		),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'supports'     => array( 'title' ),
		'has_archive'  => false,
		'can_export'   => true,
	) );
}
add_action( 'init', 'aws_sbg_register_partner_app_post_type' );

/* ========================================================================
 * 2. FRONT-END FORM & SHORTCODE
 * ======================================================================== */

function aws_sbg_render_community_partner_form( $atts = array() ) {
	$atts = shortcode_atts( array(
		'event'    => 'AWS re:Invent 2026 Watch & Build Party Islamabad',
		'kicker'   => 'Community Partner Call',
		'title'    => 'Become a Community Partner',
		'subtitle' => '',
	), $atts, 'aws_community_partner_form' );

	$event_name   = sanitize_text_field( $atts['event'] );
	$kicker       = sanitize_text_field( $atts['kicker'] );
	$form_title   = sanitize_text_field( $atts['title'] );
	$subtitle     = sanitize_text_field( $atts['subtitle'] );
	if ( '' === $subtitle ) {
		$subtitle = 'Partner with AWS Student Builder Group to co-promote ' . $event_name . ' within your community and receive a dedicated referral tracking code.';
	}

	$status       = isset( $_GET['partner_status'] ) ? sanitize_key( wp_unslash( $_GET['partner_status'] ) ) : '';
	$err_code     = isset( $_GET['partner_error'] ) ? sanitize_key( wp_unslash( $_GET['partner_error'] ) ) : '';
	$success      = ( 'success' === $status );
	$exists       = ( 'exists' === $status );
	$expired      = ( 'expired' === $status );
	$has_error    = ( 'error' === $status );
	$success_name = '';
	if ( $success && ! empty( $_GET['cp_ref'] ) ) {
		$cp_ref   = preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_GET['cp_ref'] ) );
		$cp_cache = $cp_ref ? get_transient( 'aws_cp_success_' . $cp_ref ) : false;
		if ( is_string( $cp_cache ) && '' !== $cp_cache ) {
			$success_name = sanitize_text_field( $cp_cache );
		}
	}

	$fonts    = get_template_directory_uri() . '/assets/design-kit/fonts';
	$aws_logo = get_template_directory_uri() . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : get_template_directory_uri() . '/assets/images/icons/SBG-Logo-White-Transparent.png';

	$error_messages = array(
		'required' => 'Please fill in all required fields and accept the promotion commitment.',
		'email'    => 'Please enter a valid email address.',
		'url'      => 'Please enter valid URLs (starting with https://) for LinkedIn and your Community WhatsApp Group link.',
		'logo_req' => 'Please upload your community logo (PNG, JPG, or WEBP, max 2 MB).',
		'logo_ext' => 'Invalid logo file. Only PNG, JPG, or WEBP images up to 2 MB are allowed.',
		'save'     => 'Could not save your application. Please try again.',
	);
	$error_text = isset( $error_messages[ $err_code ] ) ? $error_messages[ $err_code ] : 'Something went wrong. Please check your details and try again.';

	ob_start();
	?>
	<div class="cp-wrap" x-data="partnerAppForm()">
		<style>
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Rg.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }

			.cp-wrap { position:relative; min-height:100vh; background:#06040C; color:#FFFFFF; font-family:'Amazon Ember Display',system-ui,-apple-system,'Segoe UI',sans-serif; overflow:hidden; }
			.cp-wrap *, .cp-wrap *::before, .cp-wrap *::after { box-sizing:border-box; }
			.cp-wrap [x-cloak] { display:none !important; }
			.cp-wrap h1, .cp-wrap h2, .cp-wrap h3 { color:#FFFFFF; margin:0; }
			.cp-wrap p { margin:0; }

			.cp-mesh { position:absolute; inset:0; z-index:0; pointer-events:none; background:
				radial-gradient(55% 42% at 12% -5%, rgba(107,69,232,0.48), transparent 68%),
				radial-gradient(48% 38% at 88% 8%, rgba(46,99,224,0.35), transparent 70%),
				radial-gradient(60% 46% at 82% 92%, rgba(10,141,192,0.30), transparent 70%); }

			.cp-topbar { position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px clamp(20px,5vw,48px); border-bottom:1px solid rgba(255,255,255,0.08); }
			.cp-topbar a { display:inline-flex; align-items:center; }
			.cp-logo-aws { height:30px; width:auto; display:block; }
			.cp-logo-sbg { height:44px; width:auto; display:block; }

			.cp-stage { position:relative; z-index:1; padding:clamp(40px,7vh,76px) clamp(20px,6vw,72px) clamp(72px,10vh,110px); }
			.cp-inner { max-width:660px; margin:0 auto; }
			.cp-kicker { font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#00DFD8; margin:0 0 14px 0; }
			.cp-wrap .cp-title { font-weight:700; font-size:clamp(28px,4vw,40px); line-height:1.15; letter-spacing:-0.02em; margin:0 0 14px 0; }
			.cp-sub { font-size:15.5px; line-height:1.65; color:rgba(255,255,255,0.65); margin:0 0 36px 0; }

			.cp-banner { padding:14px 18px; border-radius:10px; font-size:14px; line-height:1.55; margin-bottom:24px; }
			.cp-banner-info { border:1px solid rgba(0,223,216,0.4); background:rgba(0,223,216,0.08); color:#FFFFFF; }
			.cp-banner-warn { border:1px solid rgba(245,158,11,0.45); background:rgba(245,158,11,0.12); color:#fef3c7; }
			.cp-banner-err { border:1px solid rgba(239,68,68,0.45); background:rgba(239,68,68,0.12); color:#fecaca; }

			.cp-fields { display:grid; grid-template-columns:1fr 1fr; gap:24px 20px; }
			.cp-field label { display:block; font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.72); margin-bottom:10px; line-height:1.45; }
			.cp-field label em { font-style:normal; color:#00DFD8; }
			.cp-field input[type="text"],
			.cp-field input[type="email"],
			.cp-field input[type="tel"],
			.cp-field input[type="url"] { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.cp-field input:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.cp-field input::placeholder { color:rgba(255,255,255,0.32); }

			.cp-upload { position:relative; border:1px dashed rgba(255,255,255,0.24); border-radius:12px; padding:20px; background:rgba(255,255,255,0.03); text-align:center; cursor:pointer; transition:border-color 0.15s ease, background 0.15s ease; }
			.cp-upload:hover { border-color:#00DFD8; background:rgba(0,223,216,0.05); }
			.cp-upload input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
			.cp-upload-title { font-size:14px; font-weight:600; color:#FFFFFF; }
			.cp-upload-sub { font-size:12px; color:rgba(255,255,255,0.5); margin-top:4px; }
			.cp-upload-file { margin-top:10px; font-family:'Amazon Ember Mono',monospace; font-size:12px; color:#00DFD8; word-break:break-all; }

			.cp-commit { display:flex; align-items:flex-start; gap:14px; margin-top:28px; padding:20px 0; border-top:1px solid rgba(255,255,255,0.12); cursor:pointer; }
			.cp-commit input { width:18px; height:18px; margin-top:3px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; }
			.cp-commit-text { font-size:14.5px; line-height:1.6; color:rgba(255,255,255,0.82); }
			.cp-commit-sub { display:block; font-size:12.5px; line-height:1.55; color:rgba(255,255,255,0.45); margin-top:4px; }

			.cp-actions { margin-top:24px; display:flex; justify-content:flex-end; }
			.cp-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; border:0; border-radius:10px; padding:16px 38px; font-family:'Amazon Ember Mono',monospace; font-size:13px; font-weight:700; letter-spacing:0.1em; background:#00DFD8; color:#06040C; cursor:pointer; text-decoration:none; transition:background 0.18s ease, transform 0.18s ease; }
			.cp-btn:hover { background:#40E7E2; transform:translateY(-1px); }
			.cp-btn:disabled { opacity:0.65; cursor:default; transform:none; }

			@keyframes cp-spin { to { transform:rotate(360deg); } }
			.cp-spinner { width:16px; height:16px; border:2px solid rgba(6,4,12,0.3); border-top-color:#06040C; border-radius:50%; display:inline-block; animation:cp-spin 0.7s linear infinite; }

			.cp-success { position:relative; z-index:1; min-height:calc(100vh - 80px); display:flex; align-items:center; justify-content:center; padding:48px 24px; }
			.cp-success-card { max-width:480px; width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.12); border-radius:20px; padding:48px 32px; text-align:center; }
			.cp-success-icon { width:60px; height:60px; border:2px solid #00DFD8; border-radius:50%; color:#00DFD8; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
			.cp-success-icon svg { width:26px; height:26px; }
			.cp-success-link { display:inline-block; font-family:'Amazon Ember Mono',monospace; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#00DFD8; text-decoration:none; margin-top:24px; }

			@media (max-width:600px) {
				.cp-logo-aws { height:22px; }
				.cp-logo-sbg { height:34px; }
				.cp-fields { grid-template-columns:1fr; }
				.cp-actions .cp-btn { width:100%; }
			}
		</style>

		<div class="cp-mesh" aria-hidden="true"></div>

		<header class="cp-topbar">
			<a href="<?php echo esc_url( home_url() ); ?>">
				<img class="cp-logo-aws" src="<?php echo esc_url( $aws_logo ); ?>" alt="AWS">
			</a>
			<img class="cp-logo-sbg" src="<?php echo esc_url( $sbg_logo ); ?>" alt="AWS Student Builder Group">
		</header>

		<?php if ( $success ) : ?>
			<div class="cp-success">
				<div class="cp-success-card">
					<div class="cp-success-icon">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
					</div>
					<h2 style="font-weight:700;font-size:26px;margin:0 0 12px;"><?php echo $success_name ? 'Thank You, ' . esc_html( $success_name ) : 'Application Received'; ?></h2>
					<p style="font-size:15px;line-height:1.65;color:rgba(255,255,255,0.68);">Your Community Partner application for <strong><?php echo esc_html( $event_name ); ?></strong> has been submitted. Once reviewed and approved, you will receive an email with your unique referral code and the Community Partners WhatsApp group link.</p>
					<p><a class="cp-success-link" href="<?php echo esc_url( home_url() ); ?>">Back to home</a></p>
				</div>
			</div>
		<?php else : ?>
			<section class="cp-stage">
				<div class="cp-inner">
					<p class="cp-kicker"><?php echo esc_html( $kicker ); ?></p>
					<h1 class="cp-title"><?php echo esc_html( $form_title ); ?></h1>
					<div style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;margin:0 0 18px 0;border-radius:8px;border:1px solid rgba(0,223,216,0.32);background:rgba(0,223,216,0.08);font-family:'Amazon Ember Mono',monospace;font-size:clamp(10.5px,2.8vw,12px);font-weight:700;color:#00DFD8;line-height:1.4;max-width:100%;">
						<?php echo esc_html( $event_name ); ?>
					</div>
					<p class="cp-sub"><?php echo esc_html( $subtitle ); ?></p>

					<?php if ( $exists ) : ?>
						<div class="cp-banner cp-banner-info" role="status">An application with this email address has already been submitted. Our team will review it and reach out via email.</div>
					<?php elseif ( $expired ) : ?>
						<div class="cp-banner cp-banner-warn" role="status">Your session expired while the page was open. Please re-attach your logo (if needed) and click Submit again.</div>
					<?php elseif ( $has_error ) : ?>
						<div class="cp-banner cp-banner-err" role="alert"><?php echo esc_html( $error_text ); ?></div>
					<?php endif; ?>

					<form id="cp-partner-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" @input.debounce.250ms="saveDraft()" @submit="handleSubmit($event)">
						<input type="hidden" name="action" value="submit_aws_partner_app">
						<input type="hidden" name="event_name" value="<?php echo esc_attr( $event_name ); ?>">
						<?php wp_nonce_field( 'aws_partner_app_action', 'aws_partner_app_nonce' ); ?>
						<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
							<label for="cp-hp-check">Leave empty</label>
							<input id="cp-hp-check" type="text" name="cp_hp_check" value="" tabindex="-1" autocomplete="new-password">
						</div>

						<div class="cp-fields">
							<div class="cp-field">
								<label for="cp-community-name">Community Name <em>*</em></label>
								<input id="cp-community-name" type="text" name="community_name" required placeholder="e.g. GDG Islamabad / MLSA AU">
							</div>
							<div class="cp-field">
								<label for="cp-institute">Institute <em>*</em></label>
								<input id="cp-institute" type="text" name="institute" required placeholder="e.g. Air University Islamabad">
							</div>
							<div class="cp-field">
								<label for="cp-city">City <em>*</em></label>
								<input id="cp-city" type="text" name="city" required placeholder="e.g. Islamabad">
							</div>
							<div class="cp-field">
								<label for="cp-lead-name">Lead Name <em>*</em></label>
								<input id="cp-lead-name" type="text" name="lead_name" required placeholder="Community lead full name">
							</div>
							<div class="cp-field">
								<label for="cp-lead-whatsapp">Lead WhatsApp # <em>*</em></label>
								<input id="cp-lead-whatsapp" type="tel" name="lead_whatsapp" required placeholder="03000000000">
							</div>
							<div class="cp-field">
								<label for="cp-email">Email <em>*</em></label>
								<input id="cp-email" type="email" name="email" required placeholder="lead@community.org">
							</div>
							<div class="cp-field">
								<label for="cp-linkedin">LinkedIn URL <em>*</em></label>
								<input id="cp-linkedin" type="url" name="linkedin_url" required placeholder="https://linkedin.com/in/username">
							</div>
							<div class="cp-field">
								<label for="cp-community-wa">Community WhatsApp Group Link <em>*</em></label>
								<input id="cp-community-wa" type="url" name="community_whatsapp" required placeholder="https://chat.whatsapp.com/...">
							</div>
							<div class="cp-field" style="grid-column:1/-1;">
								<label for="cp-logo">Community Logo <em>*</em></label>
								<div class="cp-upload">
									<input id="cp-logo" type="file" name="community_logo" accept="image/png,image/jpeg,image/webp" required @change="onFileChange($event)">
									<div class="cp-upload-title">Click or drag to upload Community Logo</div>
									<div class="cp-upload-sub">PNG, JPG, or WEBP · Max 2 MB</div>
									<div class="cp-upload-file" x-show="fileName" x-text="fileName" x-cloak></div>
								</div>
							</div>
						</div>

						<label class="cp-commit">
							<input type="checkbox" name="promote_commit" value="yes" required>
							<span class="cp-commit-text">
								I will actively help promote the event within my community.
								<span class="cp-commit-sub">Share the event announcement and our dedicated partner registration link across your community groups, social channels, or campus.</span>
							</span>
						</label>

						<div class="cp-actions">
							<button type="submit" class="cp-btn" :disabled="isSubmitting">
								<span x-show="!isSubmitting">SUBMIT APPLICATION</span>
								<span x-show="isSubmitting" x-cloak><span class="cp-spinner"></span> Submitting</span>
							</button>
						</div>
					</form>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<script>
		function partnerAppForm() {
			const DRAFT_KEY = 'aws_cp_partner_draft_v1';
			return {
				fileName: '',
				isSubmitting: false,
				init() {
					<?php if ( $success ) : ?>
					try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
					<?php else : ?>
					this.$nextTick(() => this.restoreDraft());
					<?php endif; ?>
					window.addEventListener('pageshow', (e) => {
						if (e.persisted) {
							this.isSubmitting = false;
						}
					});
				},
				onFileChange(e) {
					const file = e.target.files && e.target.files[0];
					this.fileName = file ? file.name : '';
				},
				saveDraft() {
					const form = document.getElementById('cp-partner-form');
					if (!form) return;
					try {
						const data = {};
						['community_name', 'institute', 'city', 'lead_name', 'lead_whatsapp', 'email', 'linkedin_url', 'community_whatsapp'].forEach((name) => {
							const el = form.querySelector(`[name="${name}"]`);
							if (el) data[name] = el.value;
						});
						localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
					} catch (e) {}
				},
				restoreDraft() {
					const form = document.getElementById('cp-partner-form');
					if (!form) return;
					try {
						const raw = localStorage.getItem(DRAFT_KEY);
						if (!raw) return;
						const data = JSON.parse(raw);
						if (!data || typeof data !== 'object') return;
						Object.keys(data).forEach((name) => {
							const el = form.querySelector(`[name="${name}"]`);
							if (el && typeof data[name] === 'string') {
								el.value = data[name];
							}
						});
					} catch (e) {}
				},
				handleSubmit(e) {
					if (this.isSubmitting) {
						e.preventDefault();
						return false;
					}
					this.isSubmitting = true;
					return true;
				},
			};
		}
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_community_partner_form', 'aws_sbg_render_community_partner_form' );

/* ========================================================================
 * 3. SUBMISSION HANDLER
 * ======================================================================== */

function aws_sbg_partner_redirect_back( $params ) {
	$base = wp_get_referer() ? remove_query_arg( array( 'partner_status', 'partner_error', 'cp_ref' ), wp_get_referer() ) : home_url( '/community-partner/' );
	wp_safe_redirect( add_query_arg( $params, $base ) );
	exit;
}

function aws_sbg_handle_partner_app_submission() {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	if ( ! isset( $_POST['aws_partner_app_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['aws_partner_app_nonce'] ), 'aws_partner_app_action' ) ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'expired' ) );
	}

	if ( ! empty( $_POST['cp_hp_check'] ) ) {
		wp_die( 'Invalid submission.', 'Bad Request', array( 'response' => 400, 'back_link' => true ) );
	}

	$community_name = isset( $_POST['community_name'] ) ? sanitize_text_field( wp_unslash( $_POST['community_name'] ) ) : '';
	$event_name     = isset( $_POST['event_name'] ) && '' !== trim( $_POST['event_name'] ) ? sanitize_text_field( wp_unslash( $_POST['event_name'] ) ) : 'AWS re:Invent 2026 Watch & Build Party Islamabad';
	$institute      = isset( $_POST['institute'] ) ? sanitize_text_field( wp_unslash( $_POST['institute'] ) ) : '';
	$city           = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$lead_name      = isset( $_POST['lead_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_name'] ) ) : '';
	$lead_whatsapp      = isset( $_POST['lead_whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_whatsapp'] ) ) : '';
	$email              = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	$linkedin_raw       = isset( $_POST['linkedin_url'] ) ? trim( wp_unslash( $_POST['linkedin_url'] ) ) : '';
	$linkedin_url       = $linkedin_raw ? esc_url_raw( $linkedin_raw, array( 'http', 'https' ) ) : '';
	$comm_wa_raw        = isset( $_POST['community_whatsapp'] ) ? trim( wp_unslash( $_POST['community_whatsapp'] ) ) : '';
	$community_whatsapp = $comm_wa_raw ? esc_url_raw( $comm_wa_raw, array( 'http', 'https' ) ) : '';
	$promote_commit     = ( isset( $_POST['promote_commit'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['promote_commit'] ) ) );

	if ( empty( $community_name ) || empty( $institute ) || empty( $city ) || empty( $lead_name ) || empty( $lead_whatsapp ) || empty( $linkedin_raw ) || empty( $comm_wa_raw ) || ! $promote_commit ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'required' ) );
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'email' ) );
	}

	if ( empty( $linkedin_url ) || false === filter_var( $linkedin_url, FILTER_VALIDATE_URL ) || empty( $community_whatsapp ) || false === filter_var( $community_whatsapp, FILTER_VALIDATE_URL ) ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'url' ) );
	}

	// Validate logo upload strictly before touching DB
	if ( empty( $_FILES['community_logo'] ) || ! isset( $_FILES['community_logo']['error'] ) || UPLOAD_ERR_NO_FILE === (int) $_FILES['community_logo']['error'] ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'logo_req' ) );
	}

	$file = $_FILES['community_logo'];
	if ( UPLOAD_ERR_OK !== (int) $file['error'] || empty( $file['tmp_name'] ) || (int) $file['size'] <= 0 || (int) $file['size'] > ( 2 * 1024 * 1024 ) ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'logo_ext' ) );
	}

	$allowed_mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
	);
	$checked = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed_mimes );
	$img_info = @getimagesize( $file['tmp_name'] );
	if ( empty( $checked['ext'] ) || empty( $checked['type'] ) || false === $img_info ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'logo_ext' ) );
	}

	// Duplicate email check
	global $wpdb;
	$existing_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		 WHERE p.post_type = 'aws_partner_app'
		   AND p.post_status = 'publish'
		   AND pm.meta_key = 'email'
		   AND pm.meta_value = %s
		 LIMIT 1",
		$email
	) );
	if ( $existing_id > 0 ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'exists' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$logo_id = media_handle_upload( 'community_logo', 0 );
	if ( is_wp_error( $logo_id ) ) {
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'logo_ext' ) );
	}

	$post_id = wp_insert_post( array(
		'post_type'      => 'aws_partner_app',
		'post_title'     => $community_name,
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'meta_input'     => array(
			'event_name'     => $event_name,
			'community_name' => $community_name,
			'institute'      => $institute,
			'city'           => $city,
			'lead_name'          => $lead_name,
			'lead_whatsapp'      => $lead_whatsapp,
			'email'              => $email,
			'linkedin_url'       => $linkedin_url,
			'community_whatsapp' => $community_whatsapp,
			'logo_id'            => (int) $logo_id,
			'promote_commit' => 'yes',
			'approval_status'=> 'pending',
			'partner_code'   => '',
			'notified'       => 'no',
		),
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_delete_attachment( $logo_id, true );
		aws_sbg_partner_redirect_back( array( 'partner_status' => 'error', 'partner_error' => 'save' ) );
	}

	$cp_ref = wp_generate_password( 12, false, false );
	set_transient( 'aws_cp_success_' . $cp_ref, $lead_name, 10 * MINUTE_IN_SECONDS );

	aws_sbg_partner_redirect_back( array(
		'partner_status' => 'success',
		'cp_ref'         => $cp_ref,
	) );
}
add_action( 'admin_post_nopriv_submit_aws_partner_app', 'aws_sbg_handle_partner_app_submission' );
add_action( 'admin_post_submit_aws_partner_app', 'aws_sbg_handle_partner_app_submission' );

/* ========================================================================
 * 4. ADMIN MENU, ACTIONS & CSV EXPORT
 * ======================================================================== */

function aws_sbg_register_partner_apps_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'Partner Applications',
		'Partner Applications',
		'manage_options',
		'aws-community-partners',
		'aws_sbg_render_partner_apps_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_partner_apps_admin_menu', 11 );

function aws_sbg_partner_handle_review_action() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_GET['page'] ) || 'aws-community-partners' !== $_GET['page'] ) {
		return;
	}
	if ( ! isset( $_GET['action'], $_GET['app_id'], $_GET['_wpnonce'] ) || ! in_array( $_GET['action'], array( 'approve', 'reject' ), true ) ) {
		return;
	}

	$app_id = intval( $_GET['app_id'] );
	if ( $app_id <= 0 || 'aws_partner_app' !== get_post_type( $app_id ) ) {
		wp_die( 'Invalid application.' );
	}

	$action = sanitize_key( $_GET['action'] );
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'aws_partner_review_' . $action . '_' . $app_id ) ) {
		wp_die( 'Security check failed.' );
	}

	$prev_status = get_post_meta( $app_id, 'approval_status', true );
	$new_status  = ( 'approve' === $action ) ? 'approved' : 'rejected';
	update_post_meta( $app_id, 'approval_status', $new_status );

	if ( 'approved' === $new_status && 'approved' !== $prev_status ) {
		$community_name = get_post_meta( $app_id, 'community_name', true ) ?: get_the_title( $app_id );
		$event_name     = get_post_meta( $app_id, 'event_name', true ) ?: 'AWS re:Invent 2026 Watch & Build Party Islamabad';
		$lead_name      = get_post_meta( $app_id, 'lead_name', true );
		$to_email       = get_post_meta( $app_id, 'email', true );
		$partner_code   = get_post_meta( $app_id, 'partner_code', true );

		// Auto-register into re:Invent Community Partners tracker
		if ( function_exists( 'aws_sbg_get_reinvent_partners' ) && function_exists( 'aws_sbg_generate_reinvent_partner_code' ) ) {
			$partners = aws_sbg_get_reinvent_partners();
			if ( empty( $partner_code ) ) {
				$partner_code = aws_sbg_generate_reinvent_partner_code( $community_name, $partners );
				update_post_meta( $app_id, 'partner_code', $partner_code );
			}
			if ( ! isset( $partners[ $partner_code ] ) ) {
				$partners[ $partner_code ] = array(
					'code'       => $partner_code,
					'name'       => $community_name,
					'active'     => true,
					'created_at' => current_time( 'mysql' ),
				);
			} else {
				$partners[ $partner_code ]['active'] = true;
			}
			update_option( 'aws_reinvent_partners', $partners, false );
		}

		if ( $to_email && ( function_exists( 'aws_sbg_mail_send_raw' ) || function_exists( 'aws_sbg_mail_send' ) ) ) {
			$partner_link = $partner_code ? add_query_arg( 'partner', $partner_code, home_url( '/aws-reinvent/' ) ) : home_url( '/aws-reinvent/' );
			$subject      = 'Welcome as an Official Community Partner — AWS Student Builder Group';
			$html         = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>'
				. '<body style="margin:0;padding:24px;background-color:#ffffff;color:#111827;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.65;">'
				. '<p style="margin:0 0 16px 0;">Hi ' . esc_html( $lead_name ? $lead_name : $community_name ) . ',</p>'
				. '<p style="margin:0 0 16px 0;">Great news! <strong>' . esc_html( $community_name ) . '</strong> has been approved as an official <strong>Community Partner</strong> for <strong>' . esc_html( $event_name ) . '</strong>.</p>'
				. ( $partner_code ? '<p style="margin:0 0 8px 0;"><strong>Your Partner Referral Code:</strong> <code>' . esc_html( $partner_code ) . '</code></p><p style="margin:0 0 22px 0;"><strong>Your Shareable Registration Link:</strong><br><a href="' . esc_url( $partner_link ) . '" target="_blank" rel="noopener noreferrer" style="color:#2563eb;">' . esc_html( $partner_link ) . '</a></p>' : '' )
				. '<p style="margin:0 0 14px 0;">Please join our exclusive <strong>Community Partners WhatsApp Group</strong> below to coordinate announcements, creatives, and event updates:</p>'
				. '<p style="margin:0 0 22px 0;"><a href="' . esc_url( AWS_PARTNER_WHATSAPP_URL ) . '" target="_blank" rel="noopener noreferrer" style="display:inline-block;background-color:#16a34a;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:11px 22px;border-radius:6px;">Join Community Partners WhatsApp Group</a></p>'
				. '<p style="margin:0;font-size:13px;color:#475569;">Thank you for supporting the builder community!</p>'
				. '</body></html>';

			try {
				if ( function_exists( 'aws_sbg_mail_send_raw' ) ) {
					aws_sbg_mail_send_raw( 'general', $to_email, $subject, $html );
				} else {
					aws_sbg_mail_send( 'general', $to_email, $subject, '', '', $html );
				}
				update_post_meta( $app_id, 'notified', 'yes' );
			} catch ( \Throwable $e ) {
				error_log( 'Community partner approval email failed: ' . $e->getMessage() );
			}
		}
	}

	wp_safe_redirect( add_query_arg( 'notice', $new_status, admin_url( 'admin.php?page=aws-community-partners' ) ) );
	exit;
}
add_action( 'admin_init', 'aws_sbg_partner_handle_review_action' );

function aws_sbg_partner_handle_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$app_id = isset( $_GET['app_id'] ) ? intval( $_GET['app_id'] ) : 0;
	check_admin_referer( 'aws_partner_delete_' . $app_id );

	if ( $app_id > 0 && 'aws_partner_app' === get_post_type( $app_id ) ) {
		$logo_id = (int) get_post_meta( $app_id, 'logo_id', true );
		if ( $logo_id > 0 ) {
			wp_delete_attachment( $logo_id, true );
		}
		wp_delete_post( $app_id, true );
	}

	wp_safe_redirect( add_query_arg( 'notice', 'deleted', admin_url( 'admin.php?page=aws-community-partners' ) ) );
	exit;
}
add_action( 'admin_post_aws_sbg_partner_delete', 'aws_sbg_partner_handle_delete' );

function aws_sbg_export_partner_apps_csv() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_GET['page'], $_GET['export_csv'] ) || 'aws-community-partners' !== $_GET['page'] ) {
		return;
	}
	check_admin_referer( 'aws_partner_export_csv' );

	$apps = get_posts( array(
		'post_type'      => 'aws_partner_app',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=community-partners-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fwrite( $output, "\xEF\xBB\xBF" );
	fputcsv( $output, array( 'Event', 'Community Name', 'Institute', 'City', 'Lead Name', 'Lead WhatsApp', 'Email', 'LinkedIn URL', 'Community WhatsApp Group', 'Partner Code', 'Logo URL', 'Status', 'Submitted At' ) );

	$csv_safe = static function( $val ) {
		$str = (string) $val;
		if ( '' !== $str && in_array( $str[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $str;
		}
		return $str;
	};

	foreach ( $apps as $app ) {
		$logo_id  = (int) get_post_meta( $app->ID, 'logo_id', true );
		$logo_url = $logo_id ? wp_get_attachment_url( $logo_id ) : '';
		fputcsv( $output, array_map( $csv_safe, array(
			get_post_meta( $app->ID, 'event_name', true ) ?: 'AWS re:Invent 2026 Watch & Build Party Islamabad',
			get_post_meta( $app->ID, 'community_name', true ),
			get_post_meta( $app->ID, 'institute', true ),
			get_post_meta( $app->ID, 'city', true ),
			get_post_meta( $app->ID, 'lead_name', true ),
			get_post_meta( $app->ID, 'lead_whatsapp', true ),
			get_post_meta( $app->ID, 'email', true ),
			get_post_meta( $app->ID, 'linkedin_url', true ),
			get_post_meta( $app->ID, 'community_whatsapp', true ),
			get_post_meta( $app->ID, 'partner_code', true ),
			$logo_url,
			ucfirst( get_post_meta( $app->ID, 'approval_status', true ) ?: 'pending' ),
			get_the_date( 'Y-m-d H:i:s', $app->ID ),
		) ) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'aws_sbg_export_partner_apps_csv' );

function aws_sbg_render_partner_apps_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}

	$notice = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
	$apps   = get_posts( array(
		'post_type'      => 'aws_partner_app',
		'posts_per_page' => 200,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) {
		aws_sbg_render_admin_nav_tabs( 'partners' );
	}
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;max-width:1400px;">
		<?php if ( 'approved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong>Partner approved!</strong> Partner code created, synced to re:Invent Partners, and approval email sent with the WhatsApp group link.</p></div>
		<?php elseif ( 'rejected' === $notice ) : ?>
			<div class="notice notice-info is-dismissible"><p><strong>Partner application rejected.</strong></p></div>
		<?php elseif ( 'deleted' === $notice ) : ?>
			<div class="notice notice-info is-dismissible"><p><strong>Partner application deleted.</strong></p></div>
		<?php endif; ?>

		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin:14px 0 18px;">
			<div>
				<h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;">Community Partner Applications</h1>
				<p style="margin:4px 0 0;font-size:13px;color:#64748b;">Public form: <a href="<?php echo esc_url( home_url( '/community-partner/' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/community-partner/' ) ); ?></a></p>
			</div>
			<div style="display:flex;gap:8px;">
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aws-community-partners&export_csv=1' ), 'aws_partner_export_csv' ) ); ?>">Export CSV</a>
				<a class="button button-primary" href="<?php echo esc_url( home_url( '/community-partner/' ) ); ?>" target="_blank">Open Form ↗</a>
			</div>
		</div>

		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow-x:auto;">
			<table style="width:100%;border-collapse:collapse;font-size:13px;">
				<thead>
					<tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;">
						<th style="padding:12px 14px;">Logo</th>
						<th style="padding:12px 14px;">Community &amp; Institute</th>
						<th style="padding:12px 14px;">Event</th>
						<th style="padding:12px 14px;">City</th>
						<th style="padding:12px 14px;">Lead &amp; Contact</th>
						<th style="padding:12px 14px;">Partner Code</th>
						<th style="padding:12px 14px;">Status</th>
						<th style="padding:12px 14px;text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $apps ) ) : ?>
						<tr><td colspan="8" style="padding:36px;text-align:center;color:#64748b;">No community partner applications yet.</td></tr>
					<?php else : ?>
						<?php foreach ( $apps as $app ) :
							$id       = $app->ID;
							$c_name   = get_post_meta( $id, 'community_name', true );
							$ev_name  = get_post_meta( $id, 'event_name', true ) ?: 'AWS re:Invent 2026 Watch & Build Party Islamabad';
							$inst     = get_post_meta( $id, 'institute', true );
							$city     = get_post_meta( $id, 'city', true );
							$lead     = get_post_meta( $id, 'lead_name', true );
							$wa       = get_post_meta( $id, 'lead_whatsapp', true );
							$email    = get_post_meta( $id, 'email', true );
							$li_url   = get_post_meta( $id, 'linkedin_url', true );
							$comm_wa  = get_post_meta( $id, 'community_whatsapp', true );
							$logo_id  = (int) get_post_meta( $id, 'logo_id', true );
							$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
							$p_code   = get_post_meta( $id, 'partner_code', true );
							$status   = get_post_meta( $id, 'approval_status', true ) ?: 'pending';

							$wa_digits = preg_replace( '/[^0-9]/', '', (string) $wa );
							if ( substr( $wa_digits, 0, 1 ) === '0' ) {
								$wa_digits = '92' . substr( $wa_digits, 1 );
							}
							$app_url = wp_nonce_url( admin_url( 'admin.php?page=aws-community-partners&action=approve&app_id=' . $id ), 'aws_partner_review_approve_' . $id );
							$rej_url = wp_nonce_url( admin_url( 'admin.php?page=aws-community-partners&action=reject&app_id=' . $id ), 'aws_partner_review_reject_' . $id );
							$del_url = wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_partner_delete&app_id=' . $id ), 'aws_partner_delete_' . $id );
							?>
							<tr style="border-bottom:1px solid #f1f5f9;">
								<td style="padding:12px 14px;">
									<?php if ( $logo_url ) : ?>
										<a href="<?php echo esc_url( wp_get_attachment_url( $logo_id ) ); ?>" target="_blank" rel="noopener">
											<img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="width:44px;height:44px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;">
										</a>
									<?php else : ?>
										<span style="color:#cbd5e1;">—</span>
									<?php endif; ?>
								</td>
								<td style="padding:12px 14px;">
									<div style="font-weight:700;color:#0f172a;"><?php echo esc_html( $c_name ); ?></div>
									<div style="font-size:12px;color:#64748b;"><?php echo esc_html( $inst ); ?></div>
								</td>
								<td style="padding:12px 14px;font-size:12px;color:#475569;"><?php echo esc_html( $ev_name ); ?></td>
								<td style="padding:12px 14px;"><?php echo esc_html( $city ); ?></td>
								<td style="padding:12px 14px;">
									<div style="font-weight:600;color:#0f172a;"><?php echo esc_html( $lead ); ?></div>
									<div style="font-size:12px;color:#64748b;">
										<?php if ( $wa_digits ) : ?>
											<a href="<?php echo esc_url( 'https://wa.me/' . $wa_digits ); ?>" target="_blank" rel="noopener" style="color:#059669;text-decoration:none;"><?php echo esc_html( $wa ); ?></a> ·
										<?php endif; ?>
										<?php echo esc_html( $email ); ?>
									</div>
									<?php if ( $li_url || $comm_wa ) : ?>
										<div style="font-size:11.5px;margin-top:4px;display:flex;gap:10px;flex-wrap:wrap;">
											<?php if ( $li_url ) : ?>
												<a href="<?php echo esc_url( $li_url ); ?>" target="_blank" rel="noopener noreferrer" style="color:#2563eb;text-decoration:none;">LinkedIn ↗</a>
											<?php endif; ?>
											<?php if ( $comm_wa ) : ?>
												<a href="<?php echo esc_url( $comm_wa ); ?>" target="_blank" rel="noopener noreferrer" style="color:#16a34a;text-decoration:none;">Community WA Group ↗</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</td>
								<td style="padding:12px 14px;">
									<?php if ( $p_code ) : ?>
										<code><?php echo esc_html( $p_code ); ?></code>
									<?php else : ?>
										<span style="color:#94a3b8;">Pending</span>
									<?php endif; ?>
								</td>
								<td style="padding:12px 14px;">
									<?php
									$badge_style = 'background:#fef3c7;color:#b45309;';
									if ( 'approved' === $status ) {
										$badge_style = 'background:#dcfce7;color:#15803d;';
									} elseif ( 'rejected' === $status ) {
										$badge_style = 'background:#fee2e2;color:#b91c1c;';
									}
									?>
									<span style="display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;<?php echo esc_attr( $badge_style ); ?>"><?php echo esc_html( $status ); ?></span>
								</td>
								<td style="padding:12px 14px;text-align:right;white-space:nowrap;">
									<?php if ( 'pending' === $status ) : ?>
										<a class="button button-small button-primary" href="<?php echo esc_url( $app_url ); ?>">Approve</a>
										<a class="button button-small" href="<?php echo esc_url( $rej_url ); ?>">Reject</a>
									<?php endif; ?>
									<a class="button button-small" style="color:#dc2626;" href="<?php echo esc_url( $del_url ); ?>" onclick="return confirm('Delete this partner application and its logo?');">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
