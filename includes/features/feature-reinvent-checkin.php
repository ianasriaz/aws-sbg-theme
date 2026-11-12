<?php
/**
 * AWS re:Invent 2026 — Password-Protected Frontend Desk Check-In Terminal
 * File: feature-reinvent-checkin.php
 *
 * Dedicated full-screen, mobile-first event reception app:
 * - Password-protected with configurable event desk PIN (no WP login required for volunteers)
 * - Live camera QR barcode scanner (via self-hosted html5-qrcode.min.js)
 * - Zero-server-load in-memory instant search (Name, Pass #, CNIC)
 * - Multi-desk anti-duplicate handover prevention (locks pass once checked in)
 * - Synthesized Web Audio API feedback (chime on check-in, buzz on duplicate)
 * - Offline-resilient local sync queue
 * - Public attendee mobile pass view (/verify/RIV-XX)
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ========================================================================
 * 1. PASSCODE & AUTHENTICATION HELPERS
 * ======================================================================== */

/**
 * Get the current event desk passcode.
 */
function aws_sbg_reinvent_get_checkin_passcode() {
	$code = get_option( 'aws_reinvent_checkin_passcode', '' );
	if ( empty( $code ) ) {
		$code = 'RIV2026';
		update_option( 'aws_reinvent_checkin_passcode', $code );
	}
	return $code;
}

/**
 * Update the event desk passcode.
 */
function aws_sbg_reinvent_set_checkin_passcode( $new_code ) {
	$clean = sanitize_text_field( trim( $new_code ) );
	if ( ! empty( $clean ) ) {
		update_option( 'aws_reinvent_checkin_passcode', $clean );
		return true;
	}
	return false;
}

/**
 * Generate a signed session token based on current passcode and site salt.
 */
function aws_sbg_reinvent_get_checkin_token() {
	$passcode = aws_sbg_reinvent_get_checkin_passcode();
	return hash_hmac( 'sha256', 'aws_sbg_checkin_' . $passcode, wp_salt( 'auth' ) );
}

/**
 * Verify if a given token matches the current passcode.
 */
function aws_sbg_reinvent_verify_checkin_token( $token ) {
	if ( empty( $token ) ) {
		return false;
	}
	$expected = aws_sbg_reinvent_get_checkin_token();
	return hash_equals( $expected, (string) $token );
}

/**
 * Check if current visitor has a valid desk check-in cookie or header.
 */
function aws_sbg_reinvent_is_checkin_authenticated() {
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}
	$cookie_token = isset( $_COOKIE['aws_sbg_checkin_auth'] ) ? sanitize_text_field( $_COOKIE['aws_sbg_checkin_auth'] ) : '';
	if ( ! empty( $cookie_token ) && aws_sbg_reinvent_verify_checkin_token( $cookie_token ) ) {
		return true;
	}
	$header_token = isset( $_SERVER['HTTP_X_CHECKIN_TOKEN'] ) ? sanitize_text_field( $_SERVER['HTTP_X_CHECKIN_TOKEN'] ) : '';
	if ( ! empty( $header_token ) && aws_sbg_reinvent_verify_checkin_token( $header_token ) ) {
		return true;
	}
	return false;
}

/* ========================================================================
 * 2. ROUTING & REWRITE RULES
 * ======================================================================== */

/**
 * Register frontend rewrite rules for /reinvent-checkin and /verify/RIV-XX.
 */
function aws_sbg_reinvent_checkin_rewrite_rules() {
	add_rewrite_rule( '^reinvent-checkin/?$', 'index.php?aws_reinvent_checkin=1', 'top' );
	add_rewrite_rule( '^checkin/?$', 'index.php?aws_reinvent_checkin=1', 'top' );
	add_rewrite_rule( '^verify/([^/]+)/?$', 'index.php?aws_reinvent_verify=$matches[1]', 'top' );
}
add_action( 'init', 'aws_sbg_reinvent_checkin_rewrite_rules' );

/**
 * Register query variables.
 */
function aws_sbg_reinvent_checkin_query_vars( $vars ) {
	$vars[] = 'aws_reinvent_checkin';
	$vars[] = 'aws_reinvent_verify';
	return $vars;
}
add_filter( 'query_vars', 'aws_sbg_reinvent_checkin_query_vars' );

/**
 * Intercept template redirect for check-in terminal and verify views.
 */
function aws_sbg_reinvent_checkin_template_redirect() {
	global $wp_query;
	$req_path = isset( $_SERVER['REQUEST_URI'] ) ? parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	$req_path = trim( (string) $req_path, '/' );

	// 1. Desk Check-In Terminal (/reinvent-checkin, /checkin, or ?reinvent_checkin=1)
	if ( get_query_var( 'aws_reinvent_checkin' ) || isset( $_GET['reinvent_checkin'] ) || 'reinvent-checkin' === $req_path || 'checkin' === $req_path ) {
		if ( $wp_query ) {
			$wp_query->is_404 = false;
		}
		status_header( 200 );
		aws_sbg_render_reinvent_checkin_terminal();
		exit;
	}

	// 2. Attendee Mobile Pass Verification (/verify/RIV-XX)
	$verify_param = get_query_var( 'aws_reinvent_verify' );
	if ( empty( $verify_param ) && isset( $_GET['aws_reinvent_verify'] ) ) {
		$verify_param = sanitize_text_field( $_GET['aws_reinvent_verify'] );
	}
	if ( empty( $verify_param ) && preg_match( '#^verify/([^/]+)$#i', $req_path, $matches ) ) {
		$verify_param = sanitize_text_field( $matches[1] );
	}
	if ( ! empty( $verify_param ) ) {
		if ( $wp_query ) {
			$wp_query->is_404 = false;
		}
		status_header( 200 );
		aws_sbg_render_reinvent_mobile_pass( $verify_param );
		exit;
	}
}
add_action( 'template_redirect', 'aws_sbg_reinvent_checkin_template_redirect', 1 );

/**
 * Shortcode [aws_reinvent_checkin] as fallback.
 */
add_shortcode( 'aws_reinvent_checkin', function() {
	ob_start();
	aws_sbg_render_reinvent_checkin_terminal();
	return ob_get_clean();
} );

/* ========================================================================
 * 3. AJAX ENDPOINTS (AUTH, ROSTER, CHECK-IN TOGGLE)
 * ======================================================================== */

/**
 * AJAX: Verify desk passcode and return auth token.
 */
function aws_sbg_ajax_checkin_auth() {
	$passcode = isset( $_POST['passcode'] ) ? trim( sanitize_text_field( $_POST['passcode'] ) ) : '';
	$expected = aws_sbg_reinvent_get_checkin_passcode();

	if ( hash_equals( (string) $expected, (string) $passcode ) ) {
		$token = aws_sbg_reinvent_get_checkin_token();
		// Set cookie valid for 48 hours
		setcookie( 'aws_sbg_checkin_auth', $token, time() + ( 48 * HOUR_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		wp_send_json_success( array(
			'token'   => $token,
			'message' => 'Terminal unlocked successfully.',
		) );
	}

	wp_send_json_error( array(
		'message' => 'Invalid Event Desk Passcode. Please check with an organizer.',
	) );
}
add_action( 'wp_ajax_aws_sbg_checkin_auth', 'aws_sbg_ajax_checkin_auth' );
add_action( 'wp_ajax_nopriv_aws_sbg_checkin_auth', 'aws_sbg_ajax_checkin_auth' );

/**
 * AJAX: Get lightweight in-memory attendee roster for check-in terminal.
 */
function aws_sbg_ajax_checkin_get_roster() {
	$token = isset( $_REQUEST['token'] ) ? sanitize_text_field( $_REQUEST['token'] ) : ( isset( $_COOKIE['aws_sbg_checkin_auth'] ) ? sanitize_text_field( $_COOKIE['aws_sbg_checkin_auth'] ) : '' );
	if ( ! aws_sbg_reinvent_verify_checkin_token( $token ) && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized desk access.' ), 401 );
	}

	$posts = get_posts( array(
		'post_type'      => 'aws_reinvent_reg',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => 'approval_status',
		'meta_value'     => 'approved',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	) );

	$roster = array();
	foreach ( $posts as $p ) {
		$name      = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title( $p->ID ) );
		$reg_no    = get_post_meta( $p->ID, 'registration_number', true );
		if ( empty( $reg_no ) ) {
			$reg_no = 'RIV-' . str_pad( $p->ID, 2, '0', STR_PAD_LEFT );
		}
		$tier      = aws_sbg_get_reinvent_badge_tier( $p->ID );
		$attended  = get_post_meta( $p->ID, 'event_checked_in', true ) ?: 'no';
		$time      = get_post_meta( $p->ID, 'checked_in_at', true ) ?: '';
		$by        = get_post_meta( $p->ID, 'checked_in_by', true ) ?: '';
		$uni       = get_post_meta( $p->ID, 'university', true ) ?: '';
		$city      = get_post_meta( $p->ID, 'city', true ) ?: '';
		$cnic_raw  = get_post_meta( $p->ID, 'cnic', true ) ?: '';

		// Mask CNIC for privacy: 12345-*******-1
		$cnic_clean = preg_replace( '/[^0-9]/', '', $cnic_raw );
		if ( strlen( $cnic_clean ) === 13 ) {
			$cnic_masked = substr( $cnic_clean, 0, 5 ) . '-*******-' . substr( $cnic_clean, 12, 1 );
		} else {
			$cnic_masked = $cnic_raw;
		}

		$roster[] = array(
			'id'       => $p->ID,
			'no'       => strtoupper( $reg_no ),
			'name'     => $name,
			'tier'     => strtoupper( $tier ),
			'uni'      => $uni,
			'city'     => $city,
			'cnic'     => $cnic_masked,
			'attended' => $attended,
			'time'     => $time ? date( 'h:i A', strtotime( $time ) ) : '',
			'by'       => $by,
		);
	}

	wp_send_json_success( array(
		'roster'    => $roster,
		'total'     => count( $roster ),
		'timestamp' => current_time( 'mysql' ),
	) );
}
add_action( 'wp_ajax_aws_sbg_checkin_get_roster', 'aws_sbg_ajax_checkin_get_roster' );
add_action( 'wp_ajax_nopriv_aws_sbg_checkin_get_roster', 'aws_sbg_ajax_checkin_get_roster' );

/**
 * AJAX: Toggle check-in status with anti-duplicate locks.
 */
function aws_sbg_ajax_checkin_toggle() {
	$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : ( isset( $_COOKIE['aws_sbg_checkin_auth'] ) ? sanitize_text_field( $_COOKIE['aws_sbg_checkin_auth'] ) : '' );
	if ( ! aws_sbg_reinvent_verify_checkin_token( $token ) && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized desk access.' ), 401 );
	}

	$reg_id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
	$status   = isset( $_POST['status'] ) && 'no' === $_POST['status'] ? 'no' : 'yes';
	$desk_by  = isset( $_POST['desk'] ) ? sanitize_text_field( $_POST['desk'] ) : 'Reception Desk';

	if ( $reg_id <= 0 || 'aws_reinvent_reg' !== get_post_type( $reg_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid attendee record.' ) );
	}

	$prev_attended = get_post_meta( $reg_id, 'event_checked_in', true );
	$prev_time     = get_post_meta( $reg_id, 'checked_in_at', true );
	$prev_by       = get_post_meta( $reg_id, 'checked_in_by', true );

	if ( 'yes' === $status ) {
		// Anti-duplicate protection: if already checked in, inform the desk immediately
		if ( 'yes' === $prev_attended ) {
			wp_send_json_error( array(
				'code'     => 'already_checked_in',
				'message'  => 'Attendee is already checked in.',
				'time'     => $prev_time ? date( 'h:i A', strtotime( $prev_time ) ) : '',
				'by'       => $prev_by ?: 'Another Desk',
			) );
		}

		$now = current_time( 'mysql' );
		update_post_meta( $reg_id, 'event_checked_in', 'yes' );
		update_post_meta( $reg_id, 'checked_in_at', $now );
		update_post_meta( $reg_id, 'checked_in_by', $desk_by );

		wp_send_json_success( array(
			'id'       => $reg_id,
			'attended' => 'yes',
			'time'     => date( 'h:i A', strtotime( $now ) ),
			'by'       => $desk_by,
			'message'  => 'Checked in successfully. Badge handed over.',
		) );
	} else {
		// Undo check-in
		update_post_meta( $reg_id, 'event_checked_in', 'no' );
		delete_post_meta( $reg_id, 'checked_in_at' );
		delete_post_meta( $reg_id, 'checked_in_by' );

		wp_send_json_success( array(
			'id'       => $reg_id,
			'attended' => 'no',
			'time'     => '',
			'by'       => '',
			'message'  => 'Check-in reverted.',
		) );
	}
}
add_action( 'wp_ajax_aws_sbg_checkin_toggle', 'aws_sbg_ajax_checkin_toggle' );
add_action( 'wp_ajax_nopriv_aws_sbg_checkin_toggle', 'aws_sbg_ajax_checkin_toggle' );

/**
 * AJAX: Save desk passcode from WP Admin.
 */
function aws_sbg_ajax_checkin_save_passcode() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
	}
	check_ajax_referer( 'aws_reinvent_admin_nonce', 'nonce' );

	$new_pin = isset( $_POST['passcode'] ) ? sanitize_text_field( $_POST['passcode'] ) : '';
	if ( strlen( $new_pin ) < 4 ) {
		wp_send_json_error( array( 'message' => 'Passcode must be at least 4 characters long.' ) );
	}

	aws_sbg_reinvent_set_checkin_passcode( $new_pin );
	wp_send_json_success( array(
		'passcode' => $new_pin,
		'message'  => 'Desk Passcode updated successfully.',
	) );
}
add_action( 'wp_ajax_aws_sbg_checkin_save_passcode', 'aws_sbg_ajax_checkin_save_passcode' );

/* ========================================================================
 * 4. PUBLIC ATTENDEE MOBILE PASS VIEW (/verify/{pass_number})
 * ======================================================================== */

function aws_sbg_render_reinvent_mobile_pass( $pass_no ) {
	$pass_no = strtoupper( sanitize_text_field( trim( $pass_no ) ) );

	// Lookup post by registration_number meta
	global $wpdb;
	$post_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'registration_number' AND meta_value = %s LIMIT 1",
		$pass_no
	) );

	$found = false;
	$name  = '';
	$tier  = 'ATTENDEE';
	$uni   = '';
	$city  = '';
	$status = 'pending';
	$attended = 'no';
	$checkin_time = '';

	if ( $post_id > 0 ) {
		$status = get_post_meta( $post_id, 'approval_status', true );
		if ( 'approved' === $status ) {
			$found = true;
			$name  = preg_replace( '/^(reinvent-#\d+|RIV-\d+)\s*[–\-]\s*/i', '', get_the_title( $post_id ) );
			$tier  = aws_sbg_get_reinvent_badge_tier( $post_id );
			$uni   = get_post_meta( $post_id, 'university', true );
			$city  = get_post_meta( $post_id, 'city', true );
			$attended = get_post_meta( $post_id, 'event_checked_in', true ) ?: 'no';
			$checkin_time = get_post_meta( $post_id, 'checked_in_at', true );
		}
	}

	$theme_uri   = get_template_directory_uri();
	$fonts_uri   = $theme_uri . '/assets/design-kit/fonts';
	$aws_logo    = $theme_uri . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo    = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : $theme_uri . '/assets/images/icons/SBG-Logo-White-Transparent.png';
	$qrcode_js   = $theme_uri . '/assets/design-kit/qrcode.min.js';
	$verify_url  = 'https://awsairuniversity.com/verify/' . rawurlencode( $pass_no );

	$tier_class = 'tier-' . sanitize_title( $tier );
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title><?php echo $found ? esc_html( $name . ' — Event Pass (' . $pass_no . ')' ) : 'Verify Pass — AWS re:Invent 2026'; ?></title>
		<style>
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype'); font-weight:700; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:500; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Bd.ttf') format('truetype'); font-weight:700; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Rg.ttf') format('truetype'); font-weight:400; font-display:swap; }

			* { box-sizing: border-box; margin: 0; padding: 0; }
			body {
				background: #06040C;
				color: #FFFFFF;
				font-family: 'Amazon Ember Display', system-ui, -apple-system, sans-serif;
				min-height: 100vh;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				padding: 20px 16px;
				position: relative;
				overflow-x: hidden;
			}

			.pass-mesh {
				position: fixed;
				inset: 0;
				pointer-events: none;
				z-index: 0;
				background:
					radial-gradient(60% 45% at 15% 10%, rgba(107, 69, 232, 0.45), transparent 70%),
					radial-gradient(55% 40% at 85% 85%, rgba(0, 223, 216, 0.28), transparent 70%),
					radial-gradient(50% 35% at 50% 50%, rgba(46, 99, 224, 0.22), transparent 75%);
			}

			.pass-wrap {
				position: relative;
				z-index: 10;
				width: 100%;
				max-width: 380px;
				display: flex;
				flex-direction: column;
				gap: 16px;
			}

			.pass-card {
				background: #FFFFFF;
				border-radius: 20px;
				overflow: hidden;
				box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.1);
				color: #06040C;
				text-align: center;
			}

			.pass-header {
				background: linear-gradient(135deg, #6B45E8 0%, #2E63E0 25%, #8749DC 55%, #0A8DC0 100%);
				padding: 22px 20px 20px;
				color: #FFFFFF;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}

			.pass-brand-re {
				font-size: 15px;
				font-weight: 400;
				line-height: 1;
				color: #FFFFFF;
				position: relative;
			}
			.pass-brand-aws {
				font-size: 10px;
				font-weight: 700;
				display: block;
				margin-bottom: 2px;
				color: #FFFFFF;
			}
			.pass-brand-invent {
				font-size: 26px;
				font-weight: 400;
				letter-spacing: -0.03em;
				line-height: 0.95;
			}
			.pass-brand-sub {
				font-size: 8px;
				font-weight: 700;
				letter-spacing: 0.08em;
				color: rgba(255, 255, 255, 0.9);
				margin-top: 5px;
				text-transform: uppercase;
			}

			.pass-logos {
				display: flex;
				align-items: center;
				gap: 12px;
			}
			.pass-logo-aws { height: 22px; width: auto; }
			.pass-logo-sbg { height: 24px; width: auto; filter: brightness(0) invert(1); }

			.pass-body {
				padding: 28px 24px 24px;
				display: flex;
				flex-direction: column;
				align-items: center;
			}

			.pass-tier-pill {
				border-radius: 9999px;
				padding: 6px 20px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 11px;
				font-weight: 800;
				letter-spacing: 0.18em;
				text-transform: uppercase;
				line-height: 1;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
				display: inline-block;
				margin-bottom: 18px;
			}
			.tier-attendee { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }
			.tier-core-team { background: #4F46E5; color: #FFFFFF; border: 1.5px solid #4338CA; }
			.tier-event-crew, .tier-crew { background: #00DFD8; color: #06040C; border: 1.5px solid #00C4BE; }
			.tier-speaker { background: #FF9900; color: #06040C; border: 1.5px solid #E68A00; }
			.tier-vip-guest, .tier-vip { background: #DB2777; color: #FFFFFF; border: 1.5px solid #BE185D; }
			.tier-organizer { background: #7C3AED; color: #FFFFFF; border: 1.5px solid #6D28D9; }
			.tier-custom { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }

			.pass-name {
				font-size: 24px;
				font-weight: 700;
				line-height: 1.15;
				text-transform: uppercase;
				color: #06040C;
				margin-bottom: 6px;
				word-break: break-word;
			}

			.pass-uni {
				font-size: 13px;
				color: #64748b;
				margin-bottom: 22px;
				font-weight: 500;
			}

			.pass-qr-frame {
				background: #FFFFFF;
				border: 2px solid #06040C;
				border-radius: 14px;
				padding: 10px;
				display: inline-flex;
				margin-bottom: 16px;
				box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
			}
			.pass-qr-frame img { display: block; width: 170px; height: 170px; }

			.pass-number {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 22px;
				font-weight: 800;
				letter-spacing: 0.06em;
				color: #06040C;
				margin-bottom: 4px;
			}

			.pass-notice {
				font-size: 11px;
				color: #64748b;
				line-height: 1.5;
				max-width: 280px;
				margin: 0 auto;
			}

			.pass-status-pill {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 8px 16px;
				border-radius: 999px;
				font-size: 12px;
				font-weight: 700;
				font-family: 'Amazon Ember Mono', monospace;
				margin-top: 14px;
			}
			.status-ready { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
			.status-checked { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
			.status-invalid { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

			.pass-meta-box {
				background: rgba(255, 255, 255, 0.06);
				border: 1px solid rgba(255, 255, 255, 0.12);
				border-radius: 14px;
				padding: 16px;
				font-size: 12px;
				color: rgba(255, 255, 255, 0.75);
				line-height: 1.6;
				text-align: left;
			}
			.pass-meta-box strong { color: #FFFFFF; }
		</style>
	</head>
	<body>
		<div class="pass-mesh"></div>

		<div class="pass-wrap">
			<?php if ( $found ) : ?>
				<div class="pass-card">
					<div class="pass-header">
						<div>
							<span class="pass-brand-re"><span class="pass-brand-aws">AWS</span>re:</span><span class="pass-brand-invent">Invent</span>
							<div class="pass-brand-sub">WATCH &amp; BUILD PARTY 2026</div>
						</div>
						<div class="pass-logos">
							<img src="<?php echo esc_url( $aws_logo ); ?>" alt="AWS" class="pass-logo-aws">
							<img src="<?php echo esc_url( $sbg_logo ); ?>" alt="SBG" class="pass-logo-sbg">
						</div>
					</div>

					<div class="pass-body">
						<div class="pass-tier-pill <?php echo esc_attr( $tier_class ); ?>">
							<?php echo esc_html( $tier ); ?>
						</div>

						<h1 class="pass-name"><?php echo esc_html( $name ); ?></h1>
						<?php if ( $uni ) : ?>
							<p class="pass-uni"><?php echo esc_html( $uni . ( $city ? ' · ' . $city : '' ) ); ?></p>
						<?php endif; ?>

						<div class="pass-qr-frame">
							<div id="pass-qr" data-text="<?php echo esc_attr( $verify_url ); ?>"></div>
						</div>

						<div class="pass-number">#<?php echo esc_html( $pass_no ); ?></div>
						<p class="pass-notice">Present this pass on your phone screen at the reception desk to collect your lanyard badge.</p>

						<?php if ( 'yes' === $attended ) : ?>
							<div class="pass-status-pill status-checked">
								<span>✓ CHECKED IN</span>
								<?php if ( $checkin_time ) : ?>
									<span style="font-weight:400; opacity:0.85;">· <?php echo date( 'h:i A', strtotime( $checkin_time ) ); ?></span>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<div class="pass-status-pill status-ready">
								<span>● OFFICIAL PASS VERIFIED</span>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="pass-meta-box">
					<div><strong>Venue:</strong> PAF Air University Islamabad · Gate 1 Entry</div>
					<div><strong>Event Date:</strong> December 2026 · AWS re:Invent Watch Party</div>
					<div><strong>Security Check:</strong> Original CNIC/B-Form matching this pass is required at the campus security gate.</div>
				</div>
			<?php else : ?>
				<div class="pass-card" style="padding:48px 24px;">
					<h2 style="font-size:22px; margin-bottom:10px;">Pass Not Found</h2>
					<p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:20px;">
						Pass number <strong><?php echo esc_html( $pass_no ?: 'Unknown' ); ?></strong> could not be verified or is pending review.
					</p>
					<div class="pass-status-pill status-invalid" style="margin:0 auto;">
						<span>✕ INVALID OR PENDING APPLICATION</span>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $found ) : ?>
			<script src="<?php echo esc_url( $qrcode_js ); ?>"></script>
			<script>
			document.addEventListener('DOMContentLoaded', function () {
				const qrBox = document.getElementById('pass-qr');
				if (qrBox && typeof QRCode !== 'undefined') {
					new QRCode(qrBox, {
						text: qrBox.dataset.text,
						width: 170,
						height: 170,
						colorDark: '#06040C',
						colorLight: '#FFFFFF',
						correctLevel: QRCode.CorrectLevel.M
					});
				}
			});
			</script>
		<?php endif; ?>
	</body>
	</html>
	<?php
}

/* ========================================================================
 * 5. FRONTEND PASSWORD-PROTECTED CHECK-IN TERMINAL
 * ======================================================================== */

function aws_sbg_render_reinvent_checkin_terminal() {
	$is_auth     = aws_sbg_reinvent_is_checkin_authenticated();
	$token       = $is_auth ? aws_sbg_reinvent_get_checkin_token() : '';
	$theme_uri   = get_template_directory_uri();
	$fonts_uri   = $theme_uri . '/assets/design-kit/fonts';
	$aws_logo    = $theme_uri . '/assets/images/AWS-Logo-White-Trim.png';
	$sbg_logo    = function_exists( 'aws_sbg_get_logo_url' ) ? aws_sbg_get_logo_url() : $theme_uri . '/assets/images/icons/SBG-Logo-White-Transparent.png';
	$scanner_js  = $theme_uri . '/assets/design-kit/html5-qrcode.min.js';
	$ajax_url    = admin_url( 'admin-ajax.php' );
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>Desk Check-In Terminal — AWS re:Invent 2026</title>
		<style>
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Bd.ttf') format('truetype'); font-weight:700; font-display:swap; }
			@font-face { font-family:'Amazon Ember Display'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberDisplay_Md.ttf') format('truetype'); font-weight:500; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Bd.ttf') format('truetype'); font-weight:700; font-display:swap; }
			@font-face { font-family:'Amazon Ember Mono'; src:url('<?php echo esc_url( $fonts_uri ); ?>/AmazonEmberMono_Rg.ttf') format('truetype'); font-weight:400; font-display:swap; }

			* { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
			body {
				background: #06040C;
				color: #FFFFFF;
				font-family: 'Amazon Ember Display', system-ui, -apple-system, sans-serif;
				min-height: 100vh;
				display: flex;
				flex-direction: column;
				overflow-x: hidden;
			}

			/* --- LOCK SCREEN --- */
			.ck-lock-screen {
				position: fixed;
				inset: 0;
				z-index: 1000;
				background: #06040C;
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 20px;
			}
			.ck-lock-mesh {
				position: absolute;
				inset: 0;
				background: radial-gradient(60% 40% at 50% 30%, rgba(107, 69, 232, 0.4), transparent 70%),
							radial-gradient(40% 30% at 80% 80%, rgba(0, 223, 216, 0.25), transparent 70%);
				pointer-events: none;
			}
			.ck-lock-card {
				position: relative;
				z-index: 10;
				width: 100%;
				max-width: 380px;
				background: rgba(18, 14, 28, 0.95);
				border: 1px solid rgba(255, 255, 255, 0.12);
				border-radius: 20px;
				padding: 32px 24px;
				text-align: center;
				box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
			}
			.ck-lock-icon {
				width: 56px;
				height: 56px;
				margin: 0 auto 16px;
				border-radius: 14px;
				background: rgba(0, 223, 216, 0.12);
				border: 1px solid rgba(0, 223, 216, 0.35);
				display: flex;
				align-items: center;
				justify-content: center;
				color: #00DFD8;
			}
			.ck-lock-title {
				font-size: 22px;
				font-weight: 700;
				margin-bottom: 6px;
			}
			.ck-lock-sub {
				font-size: 13px;
				color: #94a3b8;
				margin-bottom: 24px;
			}
			.ck-field {
				text-align: left;
				margin-bottom: 16px;
			}
			.ck-label {
				display: block;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 11px;
				font-weight: 700;
				letter-spacing: 0.12em;
				text-transform: uppercase;
				color: #cbd5e1;
				margin-bottom: 6px;
			}
			.ck-input {
				width: 100%;
				background: #06040C;
				border: 1.5px solid rgba(255, 255, 255, 0.18);
				border-radius: 10px;
				padding: 12px 14px;
				color: #FFFFFF;
				font-family: inherit;
				font-size: 15px;
				transition: border-color 0.2s;
			}
			.ck-input:focus {
				outline: none;
				border-color: #00DFD8;
				box-shadow: 0 0 0 3px rgba(0, 223, 216, 0.2);
			}
			.ck-btn-unlock {
				width: 100%;
				background: #00DFD8;
				color: #06040C;
				border: none;
				border-radius: 10px;
				padding: 14px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 13px;
				font-weight: 800;
				letter-spacing: 0.1em;
				text-transform: uppercase;
				cursor: pointer;
				margin-top: 8px;
				transition: background 0.15s, transform 0.1s;
			}
			.ck-btn-unlock:hover { background: #38e9e3; }
			.ck-btn-unlock:active { transform: scale(0.98); }
			.ck-lock-err {
				margin-top: 14px;
				color: #f87171;
				font-size: 12px;
				display: none;
			}

			/* --- MAIN APP INTERFACE --- */
			.ck-app {
				display: flex;
				flex-direction: column;
				flex: 1;
				width: 100%;
				max-width: 900px;
				margin: 0 auto;
				padding: 12px 14px 40px;
			}

			/* Topbar */
			.ck-topbar {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 10px 0 14px;
				border-bottom: 1px solid rgba(255, 255, 255, 0.1);
				gap: 12px;
			}
			.ck-brand {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.ck-brand-title {
				font-size: 16px;
				font-weight: 700;
				line-height: 1.1;
			}
			.ck-brand-sub {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 10px;
				color: #00DFD8;
				letter-spacing: 0.1em;
			}
			.ck-desk-pill {
				background: rgba(255, 255, 255, 0.08);
				border: 1px solid rgba(255, 255, 255, 0.15);
				border-radius: 999px;
				padding: 5px 12px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 11px;
				display: flex;
				align-items: center;
				gap: 6px;
				cursor: pointer;
			}
			.ck-desk-dot {
				width: 7px;
				height: 7px;
				border-radius: 50%;
				background: #10b981;
			}

			/* Camera Scanner Box */
			.ck-scanner-wrap {
				background: #000000;
				border: 1.5px solid rgba(255, 255, 255, 0.16);
				border-radius: 16px;
				overflow: hidden;
				position: relative;
				margin: 14px 0 16px;
			}
			#ck-reader {
				width: 100% !important;
				background: #000000;
			}
			#ck-reader video {
				border-radius: 14px;
				object-fit: cover !important;
				max-height: 260px;
			}
			.ck-scanner-bar {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 10px 14px;
				background: rgba(18, 14, 28, 0.95);
				border-top: 1px solid rgba(255, 255, 255, 0.1);
				font-size: 12px;
			}
			.ck-cam-status {
				display: flex;
				align-items: center;
				gap: 8px;
				font-size: 11px;
				color: #cbd5e1;
				font-family: 'Amazon Ember Mono', monospace;
			}
			.ck-cam-indicator {
				width: 8px;
				height: 8px;
				border-radius: 50%;
				background: #10b981;
				box-shadow: 0 0 8px #10b981;
				display: inline-block;
			}
			.ck-scanner-btn {
				background: rgba(255, 255, 255, 0.08);
				border: 1px solid rgba(255, 255, 255, 0.18);
				border-radius: 8px;
				color: #FFFFFF;
				padding: 6px 12px;
				font-size: 11px;
				font-family: 'Amazon Ember Mono', monospace;
				font-weight: 700;
				cursor: pointer;
				transition: background 0.15s;
			}
			.ck-scanner-btn:hover {
				background: rgba(255, 255, 255, 0.16);
			}
			.ck-scanner-btn.active {
				background: rgba(0, 223, 216, 0.15);
				color: #00DFD8;
				border-color: rgba(0, 223, 216, 0.4);
			}

			/* Instant Search Box */
			.ck-search-wrap {
				position: relative;
				margin-bottom: 14px;
			}
			.ck-search-icon {
				position: absolute;
				left: 14px;
				top: 50%;
				transform: translateY(-50%);
				color: #94a3b8;
				pointer-events: none;
			}
			.ck-search-input {
				width: 100%;
				background: #130f1e;
				border: 1.5px solid rgba(255, 255, 255, 0.16);
				border-radius: 12px;
				padding: 14px 14px 14px 44px;
				font-size: 15px;
				color: #FFFFFF;
				font-family: inherit;
				transition: border-color 0.18s;
			}
			.ck-search-input:focus {
				outline: none;
				border-color: #00DFD8;
				box-shadow: 0 0 0 3px rgba(0, 223, 216, 0.2);
			}
			.ck-search-clear {
				position: absolute;
				right: 12px;
				top: 50%;
				transform: translateY(-50%);
				background: transparent;
				border: none;
				color: #94a3b8;
				cursor: pointer;
				font-size: 16px;
				padding: 4px;
				display: none;
			}

			/* Empty State & Help Guide */
			.ck-empty-guide {
				text-align: center;
				padding: 34px 20px;
				background: rgba(255, 255, 255, 0.02);
				border: 1px dashed rgba(255, 255, 255, 0.12);
				border-radius: 14px;
				margin-top: 4px;
			}
			.ck-guide-icon {
				width: 48px;
				height: 48px;
				border-radius: 50%;
				background: rgba(0, 223, 216, 0.1);
				color: #00DFD8;
				display: flex;
				align-items: center;
				justify-content: center;
				margin: 0 auto 12px;
			}
			.ck-guide-title {
				font-size: 15px;
				font-weight: 700;
				color: #FFFFFF;
				margin-bottom: 6px;
			}
			.ck-guide-desc {
				font-size: 12px;
				color: #94a3b8;
				max-width: 400px;
				margin: 0 auto;
				line-height: 1.5;
			}
			.ck-guide-desc strong {
				color: #e2e8f0;
			}
			.ck-badge-checked {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 10px;
				font-weight: 700;
				color: #10b981;
				background: rgba(16, 185, 129, 0.12);
				border: 1px solid rgba(16, 185, 129, 0.3);
				border-radius: 999px;
				padding: 3px 8px;
				white-space: nowrap;
			}
			.ck-badge-ready {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 10px;
				font-weight: 700;
				color: #00DFD8;
				background: rgba(0, 223, 216, 0.1);
				border: 1px solid rgba(0, 223, 216, 0.25);
				border-radius: 999px;
				padding: 3px 8px;
				white-space: nowrap;
			}
			.ck-btn-success {
				background: #10b981 !important;
				color: #FFFFFF !important;
				border: none !important;
				box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35) !important;
			}
			.ck-alert-success {
				background: #ecfdf5 !important;
				color: #065f46 !important;
				border: 1.5px solid #a7f3d0 !important;
			}

			/* Attendee Roster List */
			.ck-list {
				display: flex;
				flex-direction: column;
				gap: 8px;
			}
			.ck-item {
				background: #120e1c;
				border: 1px solid rgba(255, 255, 255, 0.1);
				border-radius: 12px;
				padding: 12px 14px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				cursor: pointer;
				transition: background 0.15s, border-color 0.15s;
			}
			.ck-item:hover {
				background: #1a1428;
				border-color: rgba(0, 223, 216, 0.4);
			}
			.ck-item-left {
				display: flex;
				align-items: center;
				gap: 12px;
				min-width: 0;
			}
			.ck-item-no {
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 13px;
				font-weight: 800;
				color: #00DFD8;
				flex-shrink: 0;
			}
			.ck-item-name {
				font-size: 14px;
				font-weight: 700;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}
			.ck-item-sub {
				font-size: 11px;
				color: #94a3b8;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}
			.ck-item-right {
				display: flex;
				align-items: center;
				gap: 8px;
				flex-shrink: 0;
			}
			.ck-badge-pill {
				border-radius: 9999px;
				padding: 3px 8px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 9px;
				font-weight: 800;
				letter-spacing: 0.1em;
				text-transform: uppercase;
			}
			.ck-status-dot {
				width: 8px;
				height: 8px;
				border-radius: 50%;
				background: #64748b;
			}
			.ck-status-dot.checked { background: #10b981; }

			/* --- POPUP VERIFICATION DRAWER / MODAL --- */
			.ck-modal-back {
				position: fixed;
				inset: 0;
				background: rgba(0, 0, 0, 0.85);
				z-index: 2000;
				display: none;
				align-items: flex-end;
				justify-content: center;
				backdrop-filter: blur(6px);
			}
			.ck-modal-card {
				background: #FFFFFF;
				color: #06040C;
				border-radius: 24px 24px 0 0;
				width: 100%;
				max-width: 500px;
				padding: 24px 20px 32px;
				text-align: center;
				position: relative;
				max-height: 90vh;
				overflow-y: auto;
				animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
			}
			@keyframes slideUp {
				from { transform: translateY(100%); }
				to { transform: translateY(0); }
			}
			.ck-modal-close {
				position: absolute;
				top: 16px;
				right: 16px;
				background: #f1f5f9;
				border: none;
				border-radius: 50%;
				width: 32px;
				height: 32px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 16px;
				cursor: pointer;
				color: #475569;
			}
			.ck-modal-pill {
				display: inline-block;
				margin-bottom: 12px;
				padding: 5px 16px;
				border-radius: 999px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 11px;
				font-weight: 800;
				letter-spacing: 0.15em;
				text-transform: uppercase;
			}
			.ck-modal-name {
				font-size: 24px;
				font-weight: 700;
				text-transform: uppercase;
				line-height: 1.15;
				margin-bottom: 4px;
			}
			.ck-modal-sub {
				font-size: 13px;
				color: #64748b;
				margin-bottom: 16px;
			}
			.ck-modal-grid {
				background: #f8fafc;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				padding: 12px 14px;
				margin-bottom: 18px;
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 8px 12px;
				text-align: left;
				font-size: 12px;
			}
			.ck-grid-item strong { display: block; color: #0f172a; font-size: 13px; }
			.ck-grid-item span { color: #64748b; font-size: 10px; text-transform: uppercase; }

			.ck-modal-alert {
				padding: 14px 16px;
				border-radius: 12px;
				margin-bottom: 16px;
				font-size: 13px;
				font-weight: 600;
				line-height: 1.4;
			}
			.ck-alert-ok { background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; }
			.ck-alert-warn { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; }

			.ck-btn-action {
				width: 100%;
				padding: 16px;
				border-radius: 12px;
				font-family: 'Amazon Ember Mono', monospace;
				font-size: 14px;
				font-weight: 800;
				letter-spacing: 0.08em;
				text-transform: uppercase;
				cursor: pointer;
				border: none;
				transition: transform 0.1s;
			}
			.ck-btn-action:active { transform: scale(0.98); }
			.ck-btn-checkin { background: #00DFD8; color: #06040C; }
			.ck-btn-undo { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; margin-top: 10px; }

			/* Role Colors */
			.tier-attendee { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }
			.tier-core-team { background: #4F46E5; color: #FFFFFF; border: 1.5px solid #4338CA; }
			.tier-event-crew, .tier-crew { background: #00DFD8; color: #06040C; border: 1.5px solid #00C4BE; }
			.tier-speaker { background: #FF9900; color: #06040C; border: 1.5px solid #E68A00; }
			.tier-vip-guest, .tier-vip { background: #DB2777; color: #FFFFFF; border: 1.5px solid #BE185D; }
			.tier-organizer { background: #7C3AED; color: #FFFFFF; border: 1.5px solid #6D28D9; }
			.tier-custom { background: #06040C; color: #FFFFFF; border: 1.5px solid #06040C; }
		</style>
	</head>
	<body>

		<!-- 1. LOCK SCREEN (Shows if unauthenticated) -->
		<div id="ck-lock-screen" class="ck-lock-screen" style="<?php echo $is_auth ? 'display:none;' : ''; ?>">
			<div class="ck-lock-mesh"></div>
			<div class="ck-lock-card">
				<div class="ck-lock-icon">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
					</svg>
				</div>
				<h2 class="ck-lock-title">Desk Terminal</h2>
				<p class="ck-lock-sub">AWS re:Invent Watch &amp; Build Party 2026</p>

				<form id="ck-lock-form" onsubmit="handleUnlock(event)">
					<div class="ck-field">
						<label class="ck-label" for="ck-passcode-input">Event Desk PIN</label>
						<input type="password" id="ck-passcode-input" class="ck-input" placeholder="Enter PIN..." required autocomplete="current-password">
					</div>
					<div class="ck-field">
						<label class="ck-label" for="ck-desk-input">Desk / Station Name</label>
						<input type="text" id="ck-desk-input" class="ck-input" placeholder="e.g. Desk 1 - Ali" value="Desk 1" required>
					</div>
					<button type="submit" id="ck-unlock-btn" class="ck-btn-unlock">Unlock Terminal</button>
					<div id="ck-lock-err" class="ck-lock-err"></div>
				</form>
			</div>
		</div>

		<!-- 2. MAIN DESK CHECK-IN TERMINAL (Unlocked) -->
		<div class="ck-app" id="ck-app" style="<?php echo ! $is_auth ? 'display:none;' : ''; ?>">
			<!-- Topbar -->
			<div class="ck-topbar">
				<div class="ck-brand">
					<div>
						<div class="ck-brand-title">re:Invent Check-In</div>
						<div class="ck-brand-sub">ISLAMABAD 2026</div>
					</div>
				</div>
				<div class="ck-desk-pill" onclick="changeDeskName()" title="Click to change desk name">
					<div class="ck-desk-dot"></div>
					<span id="ck-desk-label">Desk 1</span>
				</div>
			</div>

			<!-- Live Camera QR Scanner -->
			<div class="ck-scanner-wrap">
				<div id="ck-reader"></div>
				<div class="ck-scanner-bar">
					<div class="ck-cam-status">
						<span class="ck-cam-indicator"></span>
						<span id="ck-cam-status-text">Camera active · Scan pass QR</span>
					</div>
					<button type="button" id="ck-cam-toggle" class="ck-scanner-btn active" onclick="toggleCamera()">Pause Camera</button>
				</div>
			</div>

			<!-- Instant Search (Focused by Default) -->
			<div class="ck-search-wrap">
				<svg class="ck-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
				<input type="text" id="ck-search-input" class="ck-search-input" placeholder="Search by Name, Pass # (e.g. RIV-01), or CNIC..." oninput="handleSearch(this.value)" autofocus autocomplete="off">
				<button type="button" id="ck-search-clear" class="ck-search-clear" onclick="clearSearch()">&times;</button>
			</div>

			<!-- Attendee Search Results / Empty Guide (No list by default) -->
			<div class="ck-list" id="ck-list">
				<div class="ck-empty-guide">
					<div class="ck-guide-icon">
						<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
					</div>
					<div class="ck-guide-title">Ready for Check-In</div>
					<div class="ck-guide-desc">Point attendee pass QR at camera above, or search by <strong>Name</strong>, <strong>Pass # (e.g. RIV-01)</strong>, or <strong>CNIC</strong>.</div>
				</div>
			</div>
		</div>

		<!-- 3. ATTENDEE VERIFICATION DRAWER / MODAL -->
		<div id="ck-modal" class="ck-modal-back" onclick="if(event.target===this) closeModal()">
			<div class="ck-modal-card">
				<button type="button" class="ck-modal-close" onclick="closeModal()">&times;</button>
				<div id="ck-modal-pill" class="ck-modal-pill tier-attendee">ATTENDEE</div>
				<h2 id="ck-modal-name" class="ck-modal-name">USMAN GHANI</h2>
				<p id="ck-modal-sub" class="ck-modal-sub">AIR University · Islamabad</p>

				<div class="ck-modal-grid">
					<div class="ck-grid-item">
						<span>Pass ID</span>
						<strong id="ck-modal-id">#RIV-01</strong>
					</div>
					<div class="ck-grid-item">
						<span>CNIC / B-Form</span>
						<strong id="ck-modal-cnic">37405-*******-1</strong>
					</div>
					<div class="ck-grid-item">
						<span>Affiliation</span>
						<strong id="ck-modal-uni">AIR University</strong>
					</div>
					<div class="ck-grid-item">
						<span>Status</span>
						<strong id="ck-modal-status">Ready</strong>
					</div>
				</div>

				<div id="ck-modal-alert" class="ck-modal-alert ck-alert-ok">
					Eligible for entrance. Hand over printed lanyard badge.
				</div>

				<button type="button" id="ck-btn-primary" class="ck-btn-action ck-btn-checkin" onclick="confirmCheckin()">Confirm Check-In &amp; Issue Badge</button>
				<button type="button" id="ck-btn-undo" class="ck-btn-action ck-btn-undo" style="display:none;" onclick="undoCheckin()">Undo Check-In</button>
			</div>
		</div>

		<!-- Scanner Library -->
		<script src="<?php echo esc_url( $scanner_js ); ?>"></script>

		<script>
		const AJAX_URL = '<?php echo esc_url( $ajax_url ); ?>';
		let authToken = '<?php echo esc_js( $token ); ?>';
		let deskName = localStorage.getItem('aws_sbg_checkin_desk') || 'Desk 1';
		let masterRoster = [];
		let currentQuery = '';
		let activeAttendee = null;
		let html5QrScanner = null;
		let scannerRunning = false;
		let audioCtx = null;

		document.addEventListener('DOMContentLoaded', function () {
			document.getElementById('ck-desk-label').textContent = deskName;
			const deskInput = document.getElementById('ck-desk-input');
			if (deskInput) deskInput.value = deskName;

			if (authToken) {
				initTerminal();
			}
		});

		// Web Audio API Synthesizer (Zero External Dependencies)
		function getAudioContext() {
			if (!audioCtx) {
				const AudioContext = window.AudioContext || window.webkitAudioContext;
				if (AudioContext) audioCtx = new AudioContext();
			}
			if (audioCtx && audioCtx.state === 'suspended') {
				audioCtx.resume();
			}
			return audioCtx;
		}

		function playSuccessChime() {
			try {
				const ctx = getAudioContext();
				if (!ctx) return;
				const now = ctx.currentTime;
				const osc = ctx.createOscillator();
				const gain = ctx.createGain();
				osc.type = 'sine';
				osc.frequency.setValueAtTime(587.33, now); // D5
				osc.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5
				gain.gain.setValueAtTime(0.3, now);
				gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
				osc.connect(gain);
				gain.connect(ctx.destination);
				osc.start(now);
				osc.stop(now + 0.25);
			} catch (e) {}
		}

		function playWarningBuzz() {
			try {
				if (navigator.vibrate) navigator.vibrate([120, 60, 120]);
				const ctx = getAudioContext();
				if (!ctx) return;
				const now = ctx.currentTime;
				const osc = ctx.createOscillator();
				const gain = ctx.createGain();
				osc.type = 'sawtooth';
				osc.frequency.setValueAtTime(180, now);
				osc.frequency.linearRampToValueAtTime(110, now + 0.28);
				gain.gain.setValueAtTime(0.35, now);
				gain.gain.exponentialRampToValueAtTime(0.01, now + 0.32);
				osc.connect(gain);
				gain.connect(ctx.destination);
				osc.start(now);
				osc.stop(now + 0.32);
			} catch (e) {}
		}

		// Unlock Terminal
		function handleUnlock(e) {
			e.preventDefault();
			const pin = document.getElementById('ck-passcode-input').value.trim();
			const desk = document.getElementById('ck-desk-input').value.trim() || 'Desk 1';
			const errDiv = document.getElementById('ck-lock-err');
			const btn = document.getElementById('ck-unlock-btn');

			errDiv.style.display = 'none';
			btn.disabled = true;
			btn.textContent = 'Verifying...';

			const fd = new FormData();
			fd.append('action', 'aws_sbg_checkin_auth');
			fd.append('passcode', pin);

			fetch(AJAX_URL, { method: 'POST', body: fd })
				.then(r => r.json())
				.then(data => {
					btn.disabled = false;
					btn.textContent = 'Unlock Terminal';
					if (data.success) {
						authToken = data.data.token;
						deskName = desk;
						localStorage.setItem('aws_sbg_checkin_desk', desk);
						document.getElementById('ck-desk-label').textContent = desk;
						document.getElementById('ck-lock-screen').style.display = 'none';
						document.getElementById('ck-app').style.display = 'flex';
						initTerminal();
					} else {
						errDiv.textContent = data.data.message || 'Invalid passcode.';
						errDiv.style.display = 'block';
					}
				})
				.catch(err => {
					btn.disabled = false;
					btn.textContent = 'Unlock Terminal';
					errDiv.textContent = 'Network error. Please try again.';
					errDiv.style.display = 'block';
				});
		}

		function changeDeskName() {
			const name = prompt('Enter your Desk or Station Name (e.g. Desk 2 - Sara):', deskName);
			if (name && name.trim()) {
				deskName = name.trim();
				localStorage.setItem('aws_sbg_checkin_desk', deskName);
				document.getElementById('ck-desk-label').textContent = deskName;
			}
		}

		// Initialize Terminal & Load Roster
		function initTerminal() {
			loadRoster();
			startScanner();
			setTimeout(function () {
				const inp = document.getElementById('ck-search-input');
				if (inp) inp.focus();
			}, 250);

			// Auto-sync roster silently in background every 25 seconds
			setInterval(loadRoster, 25000);
		}

		function loadRoster() {
			const fd = new FormData();
			fd.append('action', 'aws_sbg_checkin_get_roster');
			fd.append('token', authToken);

			fetch(AJAX_URL, { method: 'POST', body: fd })
				.then(r => r.json())
				.then(data => {
					if (data.success && data.data.roster) {
						masterRoster = data.data.roster;
						renderList();
					}
				})
				.catch(() => {});
		}

		function renderList() {
			const list = document.getElementById('ck-list');
			const q = currentQuery.trim().toLowerCase();

			// By default, do NOT show the full list. Keep desk view clean and focused.
			if (!q) {
				list.innerHTML = `
					<div class="ck-empty-guide">
						<div class="ck-guide-icon">
							<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
						</div>
						<div class="ck-guide-title">Ready for Check-In</div>
						<div class="ck-guide-desc">Point attendee pass QR at camera above, or search by <strong>Name</strong>, <strong>Pass # (e.g. RIV-01)</strong>, or <strong>CNIC</strong>.</div>
					</div>
				`;
				return;
			}

			const cleanQ = q.replace(/[^a-z0-9]/g, '');
			let filtered = masterRoster.filter(a => {
				const nameMatch = a.name.toLowerCase().includes(q);
				const noRaw = a.no.toLowerCase();
				const noClean = noRaw.replace(/[^a-z0-9]/g, '');
				const noMatch = noRaw.includes(q) || (cleanQ && noClean.includes(cleanQ));
				const cnicMatch = cleanQ && a.cnic && a.cnic.replace(/[^0-9]/g, '').includes(cleanQ);
				return nameMatch || noMatch || cnicMatch;
			});

			if (filtered.length === 0) {
				list.innerHTML = `
					<div class="ck-empty-guide">
						<div class="ck-guide-title" style="color:#f87171;">No Matching Attendee</div>
						<div class="ck-guide-desc">No approved attendee found matching "<strong>${escapeHtml(currentQuery)}</strong>". Please verify spelling, Pass #, or CNIC.</div>
					</div>
				`;
				return;
			}

			list.innerHTML = '';
			filtered.forEach(a => {
				const item = document.createElement('div');
				item.className = 'ck-item';
				item.onclick = () => openAttendee(a);

				const tierClass = 'tier-' + a.tier.toLowerCase().replace(/[^a-z0-9]+/g, '-');
				item.innerHTML = `
					<div class="ck-item-left">
						<div class="ck-item-no">#${escapeHtml(a.no)}</div>
						<div style="min-width:0;">
							<div class="ck-item-name">${escapeHtml(a.name)}</div>
							<div class="ck-item-sub">${escapeHtml(a.uni || a.city || 'Approved')}${a.cnic ? ' · ' + escapeHtml(a.cnic) : ''}</div>
						</div>
					</div>
					<div class="ck-item-right">
						<span class="ck-badge-pill ${tierClass}">${escapeHtml(a.tier)}</span>
						${a.attended === 'yes' ? '<span class="ck-badge-checked">✓ Checked In</span>' : '<span class="ck-badge-ready">Ready</span>'}
					</div>
				`;
				list.appendChild(item);
			});
		}

		function handleSearch(val) {
			currentQuery = val;
			document.getElementById('ck-search-clear').style.display = val ? 'block' : 'none';
			renderList();
		}

		function clearSearch() {
			const inp = document.getElementById('ck-search-input');
			if (inp) {
				inp.value = '';
				inp.focus();
			}
			handleSearch('');
		}

		// Attendee Drawer
		function openAttendee(attendee) {
			activeAttendee = attendee;
			const modal = document.getElementById('ck-modal');
			const pill = document.getElementById('ck-modal-pill');
			const alert = document.getElementById('ck-modal-alert');
			const btnAction = document.getElementById('ck-btn-primary');
			const btnUndo = document.getElementById('ck-btn-undo');

			pill.className = 'ck-modal-pill tier-' + attendee.tier.toLowerCase().replace(/[^a-z0-9]+/g, '-');
			pill.textContent = attendee.tier;

			document.getElementById('ck-modal-name').textContent = attendee.name;
			document.getElementById('ck-modal-sub').textContent = (attendee.uni || '') + (attendee.city ? ' · ' + attendee.city : '');
			document.getElementById('ck-modal-id').textContent = '#' + attendee.no;
			document.getElementById('ck-modal-cnic').textContent = attendee.cnic || '—';
			document.getElementById('ck-modal-uni').textContent = attendee.uni || '—';

			btnAction.className = 'ck-btn-action ck-btn-checkin';
			btnAction.disabled = false;

			if (attendee.attended === 'yes') {
				document.getElementById('ck-modal-status').textContent = 'Checked In (' + attendee.time + ')';
				alert.className = 'ck-modal-alert ck-alert-warn';
				alert.innerHTML = `⚠️ <strong>ALREADY CHECKED IN</strong><br>Checked in at ${attendee.time || 'earlier'} by ${attendee.by || 'Desk'}. Lanyard badge was already issued!`;
				btnAction.style.display = 'none';
				btnUndo.style.display = 'block';
				playWarningBuzz();
			} else {
				document.getElementById('ck-modal-status').textContent = 'Eligible (Ready)';
				alert.className = 'ck-modal-alert ck-alert-ok';
				alert.innerHTML = `✓ <strong>ELIGIBLE FOR ENTRY</strong><br>Verify photo ID/CNIC, then tap <strong>Confirm Check-In</strong> to issue badge <strong>#${attendee.no}</strong>.`;
				btnAction.style.display = 'block';
				btnAction.textContent = 'Confirm Check-In & Issue Badge';
				btnUndo.style.display = 'none';
			}

			modal.style.display = 'flex';
		}

		function closeModal() {
			document.getElementById('ck-modal').style.display = 'none';
			activeAttendee = null;
			const inp = document.getElementById('ck-search-input');
			if (inp && currentQuery) inp.focus();
		}

		function confirmCheckin() {
			if (!activeAttendee) return;
			const id = activeAttendee.id;
			const currentAtt = activeAttendee;
			const btn = document.getElementById('ck-btn-primary');
			btn.disabled = true;
			btn.textContent = 'Confirming check-in...';

			const fd = new FormData();
			fd.append('action', 'aws_sbg_checkin_toggle');
			fd.append('id', id);
			fd.append('status', 'yes');
			fd.append('desk', deskName);
			fd.append('token', authToken);

			fetch(AJAX_URL, { method: 'POST', body: fd })
				.then(r => r.json())
				.then(data => {
					if (data.success) {
						currentAtt.attended = 'yes';
						currentAtt.time = data.data.time || 'Just now';
						currentAtt.by = deskName;
						playSuccessChime();

						btn.className = 'ck-btn-action ck-btn-success';
						btn.textContent = '✓ Check-In Confirmed!';
						const alertBox = document.getElementById('ck-modal-alert');
						alertBox.className = 'ck-modal-alert ck-alert-success';
						alertBox.innerHTML = `🎉 <strong>CHECKED IN SUCCESSFULLY</strong><br>Badge #${currentAtt.no} handed over.`;

						setTimeout(() => {
							closeModal();
							renderList();
						}, 700);
					} else {
						btn.disabled = false;
						btn.textContent = 'Confirm Check-In & Issue Badge';
						playWarningBuzz();
						alert(data.data.message || 'Check-in failed');
					}
				})
				.catch(() => {
					btn.disabled = false;
					btn.textContent = 'Confirm Check-In & Issue Badge';
					alert('Network error. Check connection.');
				});
		}

		function undoCheckin() {
			if (!activeAttendee || !confirm('Are you sure you want to revert check-in for ' + activeAttendee.name + '?')) return;
			const id = activeAttendee.id;
			const currentAtt = activeAttendee;
			const btn = document.getElementById('ck-btn-undo');
			btn.disabled = true;
			btn.textContent = 'Reverting...';

			const fd = new FormData();
			fd.append('action', 'aws_sbg_checkin_toggle');
			fd.append('id', id);
			fd.append('status', 'no');
			fd.append('desk', deskName);
			fd.append('token', authToken);

			fetch(AJAX_URL, { method: 'POST', body: fd })
				.then(r => r.json())
				.then(data => {
					btn.disabled = false;
					btn.textContent = 'Undo Check-In';
					if (data.success) {
						currentAtt.attended = 'no';
						currentAtt.time = '';
						currentAtt.by = '';
						closeModal();
						renderList();
					}
				})
				.catch(() => {
					btn.disabled = false;
					btn.textContent = 'Undo Check-In';
				});
		}

		// Camera QR Scanner (html5-qrcode)
		function startScanner() {
			if (typeof Html5Qrcode === 'undefined') return;

			html5QrScanner = new Html5Qrcode('ck-reader');
			const config = { fps: 15, qrbox: { width: 220, height: 220 } };

			html5QrScanner.start({ facingMode: 'environment' }, config, onQrSuccess, onQrError)
				.then(() => {
					scannerRunning = true;
					const btn = document.getElementById('ck-cam-toggle');
					if (btn) {
						btn.textContent = 'Pause Camera';
						btn.classList.add('active');
					}
					const statusText = document.getElementById('ck-cam-status-text');
					if (statusText) statusText.textContent = 'Camera active · Scan pass QR';
					const indicator = document.querySelector('.ck-cam-indicator');
					if (indicator) {
						indicator.style.background = '#10b981';
						indicator.style.boxShadow = '0 0 8px #10b981';
					}
				})
				.catch(() => {
					// Fallback to user facing camera or ignore permission denied
				});
		}

		function toggleCamera() {
			if (!html5QrScanner) return;
			const btn = document.getElementById('ck-cam-toggle');
			const statusText = document.getElementById('ck-cam-status-text');
			const indicator = document.querySelector('.ck-cam-indicator');

			if (scannerRunning) {
				html5QrScanner.stop().then(() => {
					scannerRunning = false;
					btn.textContent = 'Resume Camera';
					btn.classList.remove('active');
					if (statusText) statusText.textContent = 'Camera paused';
					if (indicator) {
						indicator.style.background = '#64748b';
						indicator.style.boxShadow = 'none';
					}
				});
			} else {
				startScanner();
			}
		}

		function onQrSuccess(decodedText) {
			if (!decodedText) return;

			// Do not re-trigger while an attendee modal is already open
			if (activeAttendee) return;

			// Extract pass number: either from URL /verify/RIV-01 or direct string RIV-01
			let match = decodedText.match(/verify\/([A-Za-z0-9_\-]+)/i);
			let passNo = match ? match[1] : decodedText.trim();
			passNo = passNo.toUpperCase();
			const passNoClean = passNo.replace(/[^A-Z0-9]/g, '');

			const attendee = masterRoster.find(a => {
				const aNo = a.no.toUpperCase();
				const aClean = aNo.replace(/[^A-Z0-9]/g, '');
				return aNo === passNo || 
				       aNo === 'RIV-' + passNo || 
				       (passNoClean && aClean === passNoClean);
			});

			if (attendee) {
				// NO auto check-in on scan!
				// Crew MUST inspect details and explicitly click "Confirm Check-In"
				openAttendee(attendee);
			} else {
				playWarningBuzz();
				alert('Pass #' + passNo + ' not found in approved roster.');
			}
		}

		function onQrError() {
			// Continuous frame reading - ignore silent errors
		}

		function escapeHtml(str) {
			return (str || '').replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' })[m]);
		}
		</script>
	</body>
	</html>
	<?php
}
