<?php
/**
 * AWS re:Invent 2026 — VIP Guests & Community Leaders Form (/aws-reinvent-guests/)
 *
 * Dedicated, separate submission flow and admin manager (`aws_reinvent_guest` CPT)
 * for community leaders and invited guests, keeping `aws_reinvent_reg` untouched.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ========================================================================
 * 1. CUSTOM POST TYPE
 * ======================================================================== */

function aws_sbg_register_reinvent_guest_post_type() {
	register_post_type( 'aws_reinvent_guest', array(
		'labels' => array(
			'name'          => 're:Invent Guests',
			'singular_name' => 're:Invent Guest',
		),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'supports'     => array( 'title' ),
		'has_archive'  => false,
		'can_export'   => true,
	) );
}
add_action( 'init', 'aws_sbg_register_reinvent_guest_post_type' );

/* ========================================================================
 * 2. FRONT-END FORM & SHORTCODE
 * ======================================================================== */

function aws_sbg_render_reinvent_guests_form() {
	$status       = isset( $_GET['guest_status'] ) ? sanitize_key( wp_unslash( $_GET['guest_status'] ) ) : '';
	$err_code     = isset( $_GET['guest_error'] ) ? sanitize_key( wp_unslash( $_GET['guest_error'] ) ) : '';
	$success      = ( 'success' === $status );
	$exists       = ( 'exists' === $status );
	$expired      = ( 'expired' === $status );
	$has_error    = ( 'error' === $status );
	$success_name = '';
	if ( $success && ! empty( $_GET['gst_ref'] ) ) {
		$gst_ref   = preg_replace( '/[^a-zA-Z0-9]/', '', wp_unslash( $_GET['gst_ref'] ) );
		$gst_cache = $gst_ref ? get_transient( 'aws_gst_success_' . $gst_ref ) : false;
		if ( is_string( $gst_cache ) && '' !== $gst_cache ) {
			$success_name = sanitize_text_field( $gst_cache );
		}
	}

	$fonts    = get_template_directory_uri() . '/assets/design-kit/fonts';
	$skyline  = get_template_directory_uri() . '/assets/design-kit/Islamabad Landmark Footer.png';
	$aws_logo = get_template_directory_uri() . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : get_template_directory_uri() . '/assets/images/icons/SBG-Logo-White-Transparent.png';

	$error_messages = array(
		'required' => 'Please fill in all required fields and a valid 13-digit CNIC (e.g. 12345-1234567-1).',
		'email'    => 'Please enter a valid email address.',
		'vehicle'  => 'Please provide your vehicle type and number.',
		'save'     => 'Could not save your details. Please try again.',
	);
	$error_text = isset( $error_messages[ $err_code ] ) ? $error_messages[ $err_code ] : 'Please check your details and try again.';

	ob_start();
	?>
	<div class="rvg-wrap" x-data="reinventGuestForm()">
		<style>
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Rg.ttf') format('truetype'); font-weight:400; font-style:normal; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts ); ?>/AmazonEmberMono_Bd.ttf') format('truetype'); font-weight:700; font-style:normal; font-display:swap; }

			.rvg-wrap { position:relative; min-height:100vh; background:#06040C; color:#FFFFFF; font-family:'Amazon Ember Display',system-ui,-apple-system,'Segoe UI',sans-serif; overflow:hidden; }
			.rvg-wrap *, .rvg-wrap *::before, .rvg-wrap *::after { box-sizing:border-box; }
			.rvg-wrap [x-cloak] { display:none !important; }
			.rvg-wrap :focus-visible { outline:2px solid #00DFD8; outline-offset:3px; border-radius:4px; }
			.rvg-wrap h1, .rvg-wrap h2, .rvg-wrap h3 { color:#FFFFFF; margin:0; }
			.rvg-wrap p { margin:0; }

			.rvg-mesh { position:absolute; inset:0; z-index:0; pointer-events:none; background:
				radial-gradient(55% 42% at 12% -5%, rgba(107,69,232,0.5), transparent 68%),
				radial-gradient(48% 38% at 88% 8%, rgba(46,99,224,0.38), transparent 70%),
				radial-gradient(60% 46% at 82% 92%, rgba(10,141,192,0.34), transparent 70%),
				radial-gradient(50% 40% at 8% 96%, rgba(135,73,220,0.30), transparent 72%); }

			.rvg-topbar { position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px clamp(20px,5vw,48px); border-bottom:1px solid rgba(255,255,255,0.08); }
			.rvg-topbar a { display:inline-flex; align-items:center; }
			.rvg-logo-aws { height:30px; width:auto; display:block; }
			.rvg-logo-sbg { height:44px; width:auto; display:block; }

			.rvg-stage { position:relative; z-index:1; padding:clamp(44px,7vh,84px) clamp(20px,6vw,72px) clamp(120px,16vh,180px); }
			.rvg-skyline { position:absolute; left:0; bottom:0; width:100%; height:auto; opacity:0.32; pointer-events:none; user-select:none; z-index:0; }
			.rvg-inner { position:relative; z-index:1; max-width:660px; margin:0 auto; }

			.rvg-kicker-meta { display:flex; flex-wrap:wrap; align-items:center; gap:6px 0; margin:0 0 16px 0; font-family:'Amazon Ember Mono',monospace; font-size:clamp(10.5px,2.8vw,11.5px); font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:rgba(255,255,255,0.72); line-height:1.5; }
			.rvg-kicker-item { white-space:nowrap; }
			.rvg-kicker-sep { color:rgba(255,255,255,0.24); margin:0 10px; }
			.rvg-wrap .rvg-title { font-weight:700; font-size:clamp(30px,4.5vw,44px); line-height:1.12; letter-spacing:-0.02em; margin:0 0 16px 0; }
			.rvg-sub { font-size:16px; line-height:1.7; color:rgba(255,255,255,0.68); margin:0 0 38px 0; max-width:56ch; }

			.rvg-banner { padding:14px 18px; border-radius:10px; font-size:14px; line-height:1.55; margin-bottom:24px; }
			.rvg-banner-info { border:1px solid rgba(0,223,216,0.4); background:rgba(0,223,216,0.08); color:#FFFFFF; }
			.rvg-banner-warn { border:1px solid rgba(245,158,11,0.45); background:rgba(245,158,11,0.12); color:#fef3c7; }
			.rvg-banner-err { border:1px solid rgba(239,68,68,0.45); background:rgba(239,68,68,0.12); color:#fecaca; }

			.rvg-fields { display:grid; grid-template-columns:1fr 1fr; gap:24px 20px; }
			.rvg-field label { display:block; font-family:'Amazon Ember Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:rgba(255,255,255,0.7); margin-bottom:10px; line-height:1.45; }
			.rvg-field label em { font-style:normal; color:#00DFD8; }
			.rvg-field input { width:100%; padding:14px 16px; font-family:inherit; font-size:16px; color:#FFFFFF; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.16); border-radius:10px; transition:border-color 0.15s ease, box-shadow 0.15s ease; }
			.rvg-field input:focus { outline:none; border-color:#00DFD8; box-shadow:0 0 0 3px rgba(0,223,216,0.14); }
			.rvg-field input::placeholder { color:rgba(255,255,255,0.32); }

			.rvg-choices { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
			.rvg-choice { display:flex; align-items:center; gap:12px; padding:13px 16px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.14); border-radius:12px; cursor:pointer; transition:border-color 0.15s ease, background 0.15s ease; }
			.rvg-choice:hover { border-color:rgba(255,255,255,0.4); }
			.rvg-choice:has(input:checked) { border-color:#00DFD8; background:rgba(0,223,216,0.08); }
			.rvg-choice input { width:18px; height:18px; accent-color:#00DFD8; cursor:pointer; flex-shrink:0; margin:0; }
			.rvg-choice-title { display:block; font-size:14px; font-weight:400; color:#FFFFFF; }

			.rvg-actions { margin-top:36px; display:flex; justify-content:flex-end; }
			.rvg-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; border:0; border-radius:10px; padding:16px 38px; font-family:'Amazon Ember Mono',monospace; font-size:13px; font-weight:700; letter-spacing:0.1em; background:#00DFD8; color:#06040C; cursor:pointer; text-decoration:none; transition:background 0.18s ease, transform 0.18s ease; }
			.rvg-btn:hover { background:#40E7E2; transform:translateY(-1px); }
			.rvg-btn:disabled { opacity:0.65; cursor:default; transform:none; }

			@keyframes rvg-spin { to { transform:rotate(360deg); } }
			.rvg-spinner { width:16px; height:16px; border:2px solid rgba(6,4,12,0.3); border-top-color:#06040C; border-radius:50%; display:inline-block; animation:rvg-spin 0.7s linear infinite; }

			.rvg-success { position:relative; z-index:1; min-height:calc(100vh - 80px); display:flex; align-items:center; justify-content:center; padding:48px 24px; }
			.rvg-success-card { max-width:480px; width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.12); border-radius:20px; padding:48px 32px; text-align:center; }
			.rvg-success-icon { width:60px; height:60px; border:2px solid #00DFD8; border-radius:50%; color:#00DFD8; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
			.rvg-success-icon svg { width:26px; height:26px; }
			.rvg-success-link { display:inline-block; font-family:'Amazon Ember Mono',monospace; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#00DFD8; text-decoration:none; margin-top:24px; }

			@media (max-width:600px) {
				.rvg-logo-aws { height:22px; }
				.rvg-logo-sbg { height:34px; }
				.rvg-kicker-meta { gap:6px 16px; letter-spacing:0.1em; }
				.rvg-kicker-sep { display:none; }
				.rvg-fields, .rvg-choices { grid-template-columns:1fr; }
				.rvg-actions .rvg-btn { width:100%; }
			}
		</style>

		<div class="rvg-mesh" aria-hidden="true"></div>

		<header class="rvg-topbar">
			<a href="<?php echo esc_url( home_url() ); ?>">
				<img class="rvg-logo-aws" src="<?php echo esc_url( $aws_logo ); ?>" alt="AWS">
			</a>
			<img class="rvg-logo-sbg" src="<?php echo esc_url( $sbg_logo ); ?>" alt="AWS Student Builder Group">
		</header>

		<?php if ( $success ) : ?>
			<div class="rvg-success">
				<div class="rvg-success-card">
					<div class="rvg-success-icon">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
					</div>
					<h2 style="font-weight:700;font-size:26px;margin:0 0 12px;"><?php echo $success_name ? 'Thank You, ' . esc_html( $success_name ) : 'Details Received'; ?></h2>
					<p style="font-size:15px;line-height:1.65;color:rgba(255,255,255,0.68);">We're honored to host you at <strong>AWS re:Invent 2026 Watch &amp; Build Party Islamabad</strong>. Our team will share your official guest pass and event-day details via email shortly.</p>
					<p><a class="rvg-success-link" href="<?php echo esc_url( home_url() ); ?>">Back to home</a></p>
				</div>
			</div>
		<?php else : ?>
			<section class="rvg-stage">
				<img class="rvg-skyline" src="<?php echo esc_url( $skyline ); ?>" alt="" aria-hidden="true">
				<div class="rvg-inner">
					<div class="rvg-kicker-meta">
						<span class="rvg-kicker-item" style="color:#00DFD8;">November 30, 2026</span>
						<span class="rvg-kicker-sep" aria-hidden="true">/</span>
						<span class="rvg-kicker-item">Time: 10 AM - 4 PM</span>
						<span class="rvg-kicker-sep" aria-hidden="true">/</span>
						<span class="rvg-kicker-item">Venue: Islamabad</span>
					</div>
					<h1 class="rvg-title">Invited Guest &amp; Community Leader Pass</h1>
					<p class="rvg-sub">Thank you for being a valued part of the builder community. Share your details below so we can prepare your guest pass and venue security clearance for AWS re:Invent 2026 Watch &amp; Build Party Islamabad.</p>

					<?php if ( $exists ) : ?>
						<div class="rvg-banner rvg-banner-info" role="status">Your guest details have already been received with this email address. Our team will send your pass via email shortly.</div>
					<?php elseif ( $expired ) : ?>
						<div class="rvg-banner rvg-banner-warn" role="status">Your session expired while the page was open. Your details have been restored&mdash;please click Submit again.</div>
					<?php elseif ( $has_error ) : ?>
						<div class="rvg-banner rvg-banner-err" role="alert"><?php echo esc_html( $error_text ); ?></div>
					<?php endif; ?>

					<form id="rvg-guest-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" @input.debounce.250ms="saveDraft()" @change="saveDraft()" @submit="handleSubmit($event)">
						<input type="hidden" name="action" value="submit_aws_reinvent_guest">
						<?php wp_nonce_field( 'aws_reinvent_guest_action', 'aws_reinvent_guest_nonce' ); ?>
						<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
							<label for="rvg-hp-check">Leave empty</label>
							<input id="rvg-hp-check" type="text" name="rvg_hp_check" value="" tabindex="-1" autocomplete="new-password">
						</div>

						<div class="rvg-fields">
							<div class="rvg-field">
								<label for="rvg-full-name">Full Name <em>*</em></label>
								<input id="rvg-full-name" type="text" name="full_name" required placeholder="Your full name">
							</div>
							<div class="rvg-field">
								<label for="rvg-email">Email Address <em>*</em></label>
								<input id="rvg-email" type="email" name="email" required placeholder="youremail@domain.com">
							</div>
							<div class="rvg-field">
								<label for="rvg-whatsapp">WhatsApp Number <em>*</em></label>
								<input id="rvg-whatsapp" type="tel" name="whatsapp" required placeholder="030000000">
							</div>
							<div class="rvg-field">
								<label for="rvg-cnic">CNIC # (For security clearance) <em>*</em></label>
								<input id="rvg-cnic" type="text" name="cnic" inputmode="numeric" required pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" maxlength="15" title="Format: 00000-0000000-0" placeholder="00000-0000000-0" @input="formatCnic($event)">
							</div>
							<div class="rvg-field">
								<label for="rvg-affiliation">Community / Organization / Institute <em>*</em></label>
								<input id="rvg-affiliation" type="text" name="affiliation" required placeholder="Your community or organization">
							</div>
							<div class="rvg-field">
								<label for="rvg-city">City <em>*</em></label>
								<input id="rvg-city" type="text" name="city" required placeholder="Your city">
							</div>

							<div class="rvg-field" style="grid-column:1/-1;margin-top:6px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.1);">
								<label>Bringing a vehicle? <span style="font-weight:400;">(optional)</span></label>
								<div class="rvg-choices" role="radiogroup" aria-label="Vehicle">
									<label class="rvg-choice">
										<input type="radio" name="has_vehicle" value="no" x-model="hasVehicle">
										<span class="rvg-choice-title">No</span>
									</label>
									<label class="rvg-choice">
										<input type="radio" name="has_vehicle" value="yes" x-model="hasVehicle">
										<span class="rvg-choice-title">Yes</span>
									</label>
								</div>
							</div>
							<div class="rvg-field" style="grid-column:1/-1;" x-show="hasVehicle === 'yes'" x-cloak>
								<label>Vehicle type</label>
								<div class="rvg-choices" role="radiogroup" aria-label="Vehicle type">
									<label class="rvg-choice">
										<input type="radio" name="vehicle_type" value="bike" :required="hasVehicle === 'yes'" :disabled="hasVehicle !== 'yes'">
										<span class="rvg-choice-title">Bike</span>
									</label>
									<label class="rvg-choice">
										<input type="radio" name="vehicle_type" value="car" :required="hasVehicle === 'yes'" :disabled="hasVehicle !== 'yes'">
										<span class="rvg-choice-title">Car</span>
									</label>
								</div>
							</div>
							<div class="rvg-field" style="grid-column:1/-1;" x-show="hasVehicle === 'yes'" x-cloak>
								<label for="rvg-vehicle-number">Vehicle number</label>
								<input id="rvg-vehicle-number" type="text" name="vehicle_number" placeholder="ABC-123" :required="hasVehicle === 'yes'" :disabled="hasVehicle !== 'yes'">
							</div>
						</div>

						<div class="rvg-actions">
							<button type="submit" class="rvg-btn" :disabled="isSubmitting">
								<span x-show="!isSubmitting">CONFIRM DETAILS</span>
								<span x-show="isSubmitting" x-cloak><span class="rvg-spinner"></span> Submitting</span>
							</button>
						</div>
					</form>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<script>
		function reinventGuestForm() {
			const DRAFT_KEY = 'aws_rvg_guest_draft_v1';
			return {
				hasVehicle: 'no',
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
				saveDraft() {
					const form = document.getElementById('rvg-guest-form');
					if (!form) return;
					try {
						const data = {};
						['full_name', 'email', 'whatsapp', 'cnic', 'affiliation', 'city', 'has_vehicle', 'vehicle_type', 'vehicle_number'].forEach((name) => {
							const els = form.querySelectorAll(`[name="${name}"]`);
							if (!els.length) return;
							if (els[0].type === 'radio') {
								const checked = Array.from(els).find((el) => el.checked);
								if (checked) data[name] = checked.value;
							} else {
								data[name] = els[0].value;
							}
						});
						localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
					} catch (e) {}
				},
				restoreDraft() {
					const form = document.getElementById('rvg-guest-form');
					if (!form) return;
					try {
						const raw = localStorage.getItem(DRAFT_KEY);
						if (!raw) return;
						const data = JSON.parse(raw);
						if (!data || typeof data !== 'object') return;
						Object.keys(data).forEach((name) => {
							const val = data[name];
							if (typeof val !== 'string') return;
							const els = form.querySelectorAll(`[name="${name}"]`);
							if (!els.length) return;
							if (els[0].type === 'radio') {
								els.forEach((el) => { el.checked = (el.value === val); });
							} else {
								els[0].value = val;
							}
						});
						if (data.has_vehicle) this.hasVehicle = data.has_vehicle;
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
				formatCnic(e) {
					let val = e.target.value.replace(/[^0-9]/g, '');
					if (val.length > 13) val = val.substring(0, 13);
					if (val.length > 5) {
						e.target.value = val.substring(0, 5) + '-' + (val.length > 12 ? val.substring(5, 12) + '-' + val.substring(12) : val.substring(5));
					} else {
						e.target.value = val;
					}
				},
			};
		}
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_reinvent_guests_form', 'aws_sbg_render_reinvent_guests_form' );

/* ========================================================================
 * 3. SUBMISSION HANDLER
 * ======================================================================== */

function aws_sbg_guest_redirect_back( $params ) {
	$base = wp_get_referer() ? remove_query_arg( array( 'guest_status', 'guest_error', 'gst_ref' ), wp_get_referer() ) : home_url( '/aws-reinvent-guests/' );
	wp_safe_redirect( add_query_arg( $params, $base ) );
	exit;
}

function aws_sbg_handle_reinvent_guest_submission() {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	if ( ! isset( $_POST['aws_reinvent_guest_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['aws_reinvent_guest_nonce'] ), 'aws_reinvent_guest_action' ) ) {
		aws_sbg_guest_redirect_back( array( 'guest_status' => 'expired' ) );
	}

	if ( ! empty( $_POST['rvg_hp_check'] ) ) {
		wp_die( 'Invalid submission.', 'Bad Request', array( 'response' => 400, 'back_link' => true ) );
	}

	$full_name   = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	$whatsapp    = isset( $_POST['whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp'] ) ) : '';
	$cnic        = isset( $_POST['cnic'] ) ? sanitize_text_field( wp_unslash( $_POST['cnic'] ) ) : '';
	$cnic_digits = preg_replace( '/[^0-9]/', '', $cnic );
	if ( 13 === strlen( $cnic_digits ) ) {
		$cnic = substr( $cnic_digits, 0, 5 ) . '-' . substr( $cnic_digits, 5, 7 ) . '-' . substr( $cnic_digits, 12, 1 );
	}
	$affiliation = isset( $_POST['affiliation'] ) ? sanitize_text_field( wp_unslash( $_POST['affiliation'] ) ) : '';
	$city        = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';

	$has_vehicle    = ( isset( $_POST['has_vehicle'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['has_vehicle'] ) ) ) ? 'yes' : 'no';
	$vehicle_type   = '';
	$vehicle_number = '';
	if ( 'yes' === $has_vehicle ) {
		$vehicle_type   = isset( $_POST['vehicle_type'] ) ? sanitize_key( wp_unslash( $_POST['vehicle_type'] ) ) : '';
		$vehicle_number = isset( $_POST['vehicle_number'] ) ? sanitize_text_field( wp_unslash( $_POST['vehicle_number'] ) ) : '';
		if ( ! in_array( $vehicle_type, array( 'bike', 'car' ), true ) || '' === trim( $vehicle_number ) ) {
			aws_sbg_guest_redirect_back( array( 'guest_status' => 'error', 'guest_error' => 'vehicle' ) );
		}
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		aws_sbg_guest_redirect_back( array( 'guest_status' => 'error', 'guest_error' => 'email' ) );
	}

	if ( empty( $full_name ) || empty( $whatsapp ) || ! preg_match( '/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', $cnic ) || empty( $affiliation ) || empty( $city ) ) {
		aws_sbg_guest_redirect_back( array( 'guest_status' => 'error', 'guest_error' => 'required' ) );
	}

	global $wpdb;
	$existing_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		 WHERE p.post_type = 'aws_reinvent_guest'
		   AND p.post_status = 'publish'
		   AND pm.meta_key = 'email'
		   AND pm.meta_value = %s
		 LIMIT 1",
		$email
	) );
	if ( $existing_id > 0 ) {
		aws_sbg_guest_redirect_back( array( 'guest_status' => 'exists' ) );
	}

	$post_id = wp_insert_post( array(
		'post_type'      => 'aws_reinvent_guest',
		'post_title'     => $full_name,
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'meta_input'     => array(
			'full_name'       => $full_name,
			'email'           => $email,
			'whatsapp'        => $whatsapp,
			'cnic'            => $cnic,
			'affiliation'     => $affiliation,
			'city'            => $city,
			'has_vehicle'     => $has_vehicle,
			'vehicle_type'    => $vehicle_type,
			'vehicle_number'  => $vehicle_number,
			'_badge_tier'     => 'VIP',
			'merged_reg_id'   => 0,
			'merged_reg_no'   => '',
		),
	) );

	if ( is_wp_error( $post_id ) ) {
		aws_sbg_guest_redirect_back( array( 'guest_status' => 'error', 'guest_error' => 'save' ) );
	}

	$gst_ref = wp_generate_password( 12, false, false );
	set_transient( 'aws_gst_success_' . $gst_ref, $full_name, 10 * MINUTE_IN_SECONDS );

	aws_sbg_guest_redirect_back( array(
		'guest_status' => 'success',
		'gst_ref'      => $gst_ref,
	) );
}
add_action( 'admin_post_nopriv_submit_aws_reinvent_guest', 'aws_sbg_handle_reinvent_guest_submission' );
add_action( 'admin_post_submit_aws_reinvent_guest', 'aws_sbg_handle_reinvent_guest_submission' );

/* ========================================================================
 * 4. ADMIN MENU, ACTIONS & CSV EXPORT
 * ======================================================================== */

function aws_sbg_register_reinvent_guests_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		're:Invent Guests',
		're:Invent Guests',
		'manage_options',
		'aws-reinvent-guests',
		'aws_sbg_render_reinvent_guests_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_reinvent_guests_admin_menu', 12 );

/**
 * Save Role / Badge Tier tag on a guest record (and sync to merged registration if already merged).
 */
function aws_sbg_guest_handle_save_tag() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$guest_id = isset( $_POST['guest_id'] ) ? intval( $_POST['guest_id'] ) : 0;
	check_admin_referer( 'aws_guest_save_tag_' . $guest_id, 'aws_guest_tag_nonce' );

	if ( $guest_id > 0 && 'aws_reinvent_guest' === get_post_type( $guest_id ) ) {
		$tag = isset( $_POST['badge_tier'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['badge_tier'] ) ) ) : 'VIP';
		if ( '' === $tag ) {
			$tag = 'VIP';
		}
		update_post_meta( $guest_id, '_badge_tier', $tag );

		$merged_id = (int) get_post_meta( $guest_id, 'merged_reg_id', true );
		if ( $merged_id > 0 && 'aws_reinvent_reg' === get_post_type( $merged_id ) ) {
			update_post_meta( $merged_id, '_badge_tier', $tag );
		}
	}

	wp_safe_redirect( add_query_arg( 'notice', 'tag_saved', admin_url( 'admin.php?page=aws-reinvent-guests' ) ) );
	exit;
}
add_action( 'admin_post_aws_sbg_guest_save_tag', 'aws_sbg_guest_handle_save_tag' );

/**
 * Merge a single guest record into `aws_reinvent_reg` (approved for check-in & badge printing, no email sent).
 *
 * @return string 'merged' | 'already' | 'duplicate' | 'error'
 */
function aws_sbg_guest_merge_single( $guest_id ) {
	if ( $guest_id <= 0 || 'aws_reinvent_guest' !== get_post_type( $guest_id ) ) {
		return 'error';
	}
	if ( (int) get_post_meta( $guest_id, 'merged_reg_id', true ) > 0 ) {
		return 'already';
	}

	$full_name   = get_post_meta( $guest_id, 'full_name', true ) ?: get_the_title( $guest_id );
	$email       = strtolower( (string) get_post_meta( $guest_id, 'email', true ) );
	$whatsapp    = (string) get_post_meta( $guest_id, 'whatsapp', true );
	$cnic        = (string) get_post_meta( $guest_id, 'cnic', true );
	$affiliation = (string) get_post_meta( $guest_id, 'affiliation', true );
	$city        = (string) get_post_meta( $guest_id, 'city', true );
	$has_vehicle = (string) get_post_meta( $guest_id, 'has_vehicle', true ) ?: 'no';
	$veh_type    = (string) get_post_meta( $guest_id, 'vehicle_type', true );
	$veh_num     = (string) get_post_meta( $guest_id, 'vehicle_number', true );
	$badge_tier  = get_post_meta( $guest_id, '_badge_tier', true ) ?: 'VIP';

	global $wpdb;
	if ( $email ) {
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
			return 'duplicate';
		}
	}

	$reg_no = function_exists( 'aws_sbg_allocate_reinvent_registration_number' )
		? aws_sbg_allocate_reinvent_registration_number()
		: 'RIV-' . str_pad( $guest_id, 2, '0', STR_PAD_LEFT );

	$reg_id = wp_insert_post( array(
		'post_type'      => 'aws_reinvent_reg',
		'post_title'     => $full_name,
		'post_status'    => 'publish',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'meta_input'     => array(
			'full_name'            => $full_name,
			'email'                => $email,
			'whatsapp'             => $whatsapp,
			'cnic'                 => $cnic,
			'university'           => $affiliation,
			'audience_type'        => 'professional',
			'city'                 => $city,
			'linkedin'             => '',
			'attended_before'      => 'no',
			'why_invite'           => '',
			'will_attend'          => 'yes',
			'has_vehicle'          => $has_vehicle,
			'vehicle_type'         => $veh_type,
			'vehicle_number'       => $veh_num,
			'parking_policy'       => 'yes',
			'approval_status'      => 'approved',
			'registration_number'  => $reg_no,
			'_badge_tier'          => $badge_tier,
			'_registration_source' => 'guest_form',
			'notified'             => 'no',
			'notified_status'      => '',
		),
	) );

	if ( is_wp_error( $reg_id ) ) {
		return 'error';
	}

	update_post_meta( $guest_id, 'merged_reg_id', (int) $reg_id );
	update_post_meta( $guest_id, 'merged_reg_no', $reg_no );
	return 'merged';
}

/**
 * Handle single or bulk merge of guests into `aws_reinvent_reg`.
 */
function aws_sbg_guest_handle_merge() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}

	$base_redirect = admin_url( 'admin.php?page=aws-reinvent-guests' );

	if ( ! empty( $_GET['bulk'] ) ) {
		check_admin_referer( 'aws_guest_merge_all' );
		$guests = get_posts( array(
			'post_type'      => 'aws_reinvent_guest',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		) );
		$merged_cnt = 0;
		$dup_cnt    = 0;
		foreach ( $guests as $gid ) {
			$res = aws_sbg_guest_merge_single( (int) $gid );
			if ( 'merged' === $res ) {
				$merged_cnt++;
			} elseif ( 'duplicate' === $res ) {
				$dup_cnt++;
			}
		}
		wp_safe_redirect( add_query_arg( array( 'notice' => 'bulk_merged', 'count' => $merged_cnt, 'dups' => $dup_cnt ), $base_redirect ) );
		exit;
	}

	$guest_id = isset( $_GET['guest_id'] ) ? intval( $_GET['guest_id'] ) : 0;
	check_admin_referer( 'aws_guest_merge_' . $guest_id );
	$res = aws_sbg_guest_merge_single( $guest_id );

	wp_safe_redirect( add_query_arg( 'notice', $res, $base_redirect ) );
	exit;
}
add_action( 'admin_post_aws_sbg_guest_merge', 'aws_sbg_guest_handle_merge' );

function aws_sbg_guest_handle_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$guest_id = isset( $_GET['guest_id'] ) ? intval( $_GET['guest_id'] ) : 0;
	check_admin_referer( 'aws_guest_delete_' . $guest_id );

	if ( $guest_id > 0 && 'aws_reinvent_guest' === get_post_type( $guest_id ) ) {
		wp_delete_post( $guest_id, true );
	}

	wp_safe_redirect( add_query_arg( 'notice', 'deleted', admin_url( 'admin.php?page=aws-reinvent-guests' ) ) );
	exit;
}
add_action( 'admin_post_aws_sbg_guest_delete', 'aws_sbg_guest_handle_delete' );

function aws_sbg_export_reinvent_guests_csv() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_GET['page'], $_GET['export_csv'] ) || 'aws-reinvent-guests' !== $_GET['page'] ) {
		return;
	}
	check_admin_referer( 'aws_reinvent_guests_export_csv' );

	$guests = get_posts( array(
		'post_type'      => 'aws_reinvent_guest',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=aws-reinvent-guests-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fwrite( $output, "\xEF\xBB\xBF" );
	fputcsv( $output, array( 'Role Tag', 'Full Name', 'Email', 'WhatsApp', 'CNIC', 'Community / Organization', 'City', 'Vehicle', 'Merged Pass #', 'Submitted At' ) );

	$csv_safe = static function( $val ) {
		$str = (string) $val;
		if ( '' !== $str && in_array( $str[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $str;
		}
		return $str;
	};

	foreach ( $guests as $g ) {
		$has_v = get_post_meta( $g->ID, 'has_vehicle', true );
		$veh   = ( 'yes' === $has_v ) ? ucfirst( get_post_meta( $g->ID, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $g->ID, 'vehicle_number', true ) : 'No';
		fputcsv( $output, array_map( $csv_safe, array(
			get_post_meta( $g->ID, '_badge_tier', true ) ?: 'VIP',
			get_post_meta( $g->ID, 'full_name', true ) ?: get_the_title( $g->ID ),
			get_post_meta( $g->ID, 'email', true ),
			get_post_meta( $g->ID, 'whatsapp', true ),
			get_post_meta( $g->ID, 'cnic', true ),
			get_post_meta( $g->ID, 'affiliation', true ),
			get_post_meta( $g->ID, 'city', true ),
			$veh,
			get_post_meta( $g->ID, 'merged_reg_no', true ) ?: 'Not Merged',
			get_the_date( 'Y-m-d H:i:s', $g->ID ),
		) ) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'aws_sbg_export_reinvent_guests_csv' );

function aws_sbg_render_reinvent_guests_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}

	$notice   = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
	$n_count  = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
	$n_dups   = isset( $_GET['dups'] ) ? intval( $_GET['dups'] ) : 0;
	$guests   = get_posts( array(
		'post_type'      => 'aws_reinvent_guest',
		'posts_per_page' => 250,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$unmerged_count = 0;
	foreach ( $guests as $g_item ) {
		if ( (int) get_post_meta( $g_item->ID, 'merged_reg_id', true ) <= 0 ) {
			$unmerged_count++;
		}
	}

	if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) {
		aws_sbg_render_admin_nav_tabs( 'reinvent_guests' );
	}
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;max-width:1400px;">
		<?php if ( 'tag_saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong>Role tag updated.</strong></p></div>
		<?php elseif ( 'merged' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong>Guest merged into re:Invent Registrations!</strong> Ready for Check-In and Badge printing (no email was sent).</p></div>
		<?php elseif ( 'bulk_merged' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong>Bulk merge complete:</strong> <?php echo intval( $n_count ); ?> guest(s) merged into re:Invent Registrations<?php echo $n_dups ? ' (' . intval( $n_dups ) . ' skipped due to duplicate email)' : ''; ?>.</p></div>
		<?php elseif ( 'duplicate' === $notice ) : ?>
			<div class="notice notice-warning is-dismissible"><p><strong>Already in re:Invent Registrations:</strong> A registration with this email already exists in the main sheet.</p></div>
		<?php elseif ( 'deleted' === $notice ) : ?>
			<div class="notice notice-info is-dismissible"><p><strong>Guest entry deleted.</strong></p></div>
		<?php endif; ?>

		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin:14px 0 18px;">
			<div>
				<h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;">re:Invent Invited Guests &amp; Community Leaders</h1>
				<p style="margin:4px 0 0;font-size:13px;color:#64748b;">Confirmed guest details for check-in &amp; badge printing (no approval emails). Form: <a href="<?php echo esc_url( home_url( '/aws-reinvent-guests/' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/aws-reinvent-guests/' ) ); ?></a></p>
			</div>
			<div style="display:flex;gap:8px;flex-wrap:wrap;">
				<?php if ( $unmerged_count > 0 ) : ?>
					<a class="button button-primary" style="background:#7c3aed;border-color:#6d28d9;" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_guest_merge&bulk=1' ), 'aws_guest_merge_all' ) ); ?>" onclick="return confirm('Merge all <?php echo intval( $unmerged_count ); ?> unmerged guests into re:Invent Registrations for check-in & badge printing? No emails will be sent.');">Merge All Unmerged (<?php echo intval( $unmerged_count ); ?>)</a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aws-reinvent-registrations&badges=1' ) ); ?>" target="_blank">🪪 Print Badges (PDF)</a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aws-reinvent-guests&export_csv=1' ), 'aws_reinvent_guests_export_csv' ) ); ?>">Export CSV</a>
				<a class="button" href="<?php echo esc_url( home_url( '/aws-reinvent-guests/' ) ); ?>" target="_blank">Open Guest Form ↗</a>
			</div>
		</div>

		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow-x:auto;">
			<table style="width:100%;border-collapse:collapse;font-size:13px;">
				<thead>
					<tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b;">
						<th style="padding:12px 14px;">Role / Tag</th>
						<th style="padding:12px 14px;">Name &amp; Organization</th>
						<th style="padding:12px 14px;">Contact</th>
						<th style="padding:12px 14px;">CNIC</th>
						<th style="padding:12px 14px;">City</th>
						<th style="padding:12px 14px;">Vehicle</th>
						<th style="padding:12px 14px;">Sheet Status</th>
						<th style="padding:12px 14px;text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $guests ) ) : ?>
						<tr><td colspan="8" style="padding:36px;text-align:center;color:#64748b;">No guest submissions yet.</td></tr>
					<?php else : ?>
						<?php
						$tag_options = array( 'VIP', 'SPEAKER', 'CORE TEAM', 'EVENT CREW', 'ORGANIZER', 'ATTENDEE' );
						foreach ( $guests as $g ) :
							$id        = $g->ID;
							$tag       = get_post_meta( $id, '_badge_tier', true ) ?: 'VIP';
							$name      = get_post_meta( $id, 'full_name', true ) ?: get_the_title( $id );
							$aff       = get_post_meta( $id, 'affiliation', true );
							$wa        = get_post_meta( $id, 'whatsapp', true );
							$email     = get_post_meta( $id, 'email', true );
							$cnic      = get_post_meta( $id, 'cnic', true );
							$city      = get_post_meta( $id, 'city', true );
							$has_v     = get_post_meta( $id, 'has_vehicle', true );
							$veh       = ( 'yes' === $has_v ) ? ucfirst( get_post_meta( $id, 'vehicle_type', true ) ) . ' · ' . get_post_meta( $id, 'vehicle_number', true ) : '—';
							$merged_id = (int) get_post_meta( $id, 'merged_reg_id', true );
							$merged_no = get_post_meta( $id, 'merged_reg_no', true );

							$wa_digits = preg_replace( '/[^0-9]/', '', (string) $wa );
							if ( substr( $wa_digits, 0, 1 ) === '0' ) {
								$wa_digits = '92' . substr( $wa_digits, 1 );
							}
							$merge_url = wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_guest_merge&guest_id=' . $id ), 'aws_guest_merge_' . $id );
							$del_url   = wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_guest_delete&guest_id=' . $id ), 'aws_guest_delete_' . $id );
							?>
							<tr style="border-bottom:1px solid #f1f5f9;">
								<td style="padding:12px 14px;">
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;display:inline-flex;gap:4px;align-items:center;">
										<?php wp_nonce_field( 'aws_guest_save_tag_' . $id, 'aws_guest_tag_nonce' ); ?>
										<input type="hidden" name="action" value="aws_sbg_guest_save_tag">
										<input type="hidden" name="guest_id" value="<?php echo intval( $id ); ?>">
										<select name="badge_tier" onchange="this.form.submit()" style="font-size:11.5px;font-weight:700;padding:3px 6px;border-radius:6px;border:1px solid #cbd5e1;">
											<?php foreach ( $tag_options as $opt ) : ?>
												<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( strtoupper( $tag ), $opt ); ?>><?php echo esc_html( $opt ); ?></option>
											<?php endforeach; ?>
											<?php if ( ! in_array( strtoupper( $tag ), $tag_options, true ) ) : ?>
												<option value="<?php echo esc_attr( $tag ); ?>" selected><?php echo esc_html( $tag ); ?></option>
											<?php endif; ?>
										</select>
									</form>
								</td>
								<td style="padding:12px 14px;">
									<div style="font-weight:700;color:#0f172a;"><?php echo esc_html( $name ); ?></div>
									<div style="font-size:12px;color:#64748b;"><?php echo esc_html( $aff ); ?></div>
								</td>
								<td style="padding:12px 14px;">
									<div style="font-size:12px;color:#0f172a;">
										<?php if ( $wa_digits ) : ?>
											<a href="<?php echo esc_url( 'https://wa.me/' . $wa_digits ); ?>" target="_blank" rel="noopener" style="color:#059669;text-decoration:none;"><?php echo esc_html( $wa ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $wa ); ?>
										<?php endif; ?>
									</div>
									<div style="font-size:12px;color:#64748b;"><?php echo esc_html( $email ); ?></div>
								</td>
								<td style="padding:12px 14px;font-family:monospace;font-size:12px;"><?php echo esc_html( $cnic ); ?></td>
								<td style="padding:12px 14px;"><?php echo esc_html( $city ); ?></td>
								<td style="padding:12px 14px;font-size:12px;color:#475569;"><?php echo esc_html( $veh ); ?></td>
								<td style="padding:12px 14px;">
									<?php if ( $merged_id > 0 ) : ?>
										<span style="display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;background:#dcfce7;color:#15803d;">Merged (<?php echo esc_html( $merged_no ?: 'Yes' ); ?>)</span>
									<?php else : ?>
										<span style="display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700;background:#f1f5f9;color:#475569;">Separate</span>
									<?php endif; ?>
								</td>
								<td style="padding:12px 14px;text-align:right;white-space:nowrap;">
									<?php if ( $merged_id <= 0 ) : ?>
										<a class="button button-small button-primary" href="<?php echo esc_url( $merge_url ); ?>">Merge to Sheet</a>
									<?php endif; ?>
									<a class="button button-small" style="color:#dc2626;" href="<?php echo esc_url( $del_url ); ?>" onclick="return confirm('Delete this guest record?');">Delete</a>
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
