<?php
/**
 * Template Name: Campus Giveaway Page
 * File: page-giveaway.php
 *
 * Public multi-giveaway landing page. Leads with the active giveaway, then lists
 * any others, then published winners. Each form enforces the giveaway's own
 * requirements (tick / link-or-text proof / optimised image upload) and email rule.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$open_giveaways      = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'open' ) ) : array();
$announced_giveaways = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'announced' ) ) : array();
$source_tag          = isset( $_GET['src'] ) ? sanitize_text_field( $_GET['src'] ) : 'web';

$featured = ! empty( $open_giveaways ) ? $open_giveaways[0] : null;
$others   = array_slice( $open_giveaways, 1 );

// Flash state
$gw_status    = isset( $_GET['gw_status'] ) ? sanitize_key( $_GET['gw_status'] ) : '';
$gw_ticket    = isset( $_GET['gw_ticket'] ) ? sanitize_text_field( $_GET['gw_ticket'] ) : '';
$gw_error     = isset( $_GET['gw_error'] ) ? sanitize_key( $_GET['gw_error'] ) : '';
$gw_error_gid = isset( $_GET['gw_id'] ) ? intval( $_GET['gw_id'] ) : 0;

$error_messages = array(
	'closed'   => 'This giveaway is no longer accepting entries.',
	'spam'     => 'Your submission could not be processed.',
	'required' => 'Please fill in all required fields.',
	'email'    => 'That email address does not look valid.',
	'domain'   => 'That email domain is not eligible for this giveaway.',
	'db'       => 'We could not save your entry. Please try again.',
	'filesize' => 'An uploaded image exceeds the 2 MB limit.',
	'filetype' => 'Uploads must be a JPG, PNG or WebP image.',
	'upload'   => 'There was a problem saving your upload. Please try again.',
	'reqfile'  => 'Please upload the required image.',
	'reqproof' => 'Please complete the required proof field.',
	'reqcheck' => 'Please confirm all required items.',
);

// Success / already-entered pop-up (shown instead of an inline banner).
$show_modal  = in_array( $gw_status, array( 'success', 'exists' ), true ) && '' !== $gw_ticket;
$modal_title = ( 'exists' === $gw_status ) ? "You're already in" : "You're in!";
$modal_sub   = ( 'exists' === $gw_status )
	? 'This email is already registered for this giveaway. Your ticket is below.'
	: 'Your entry is confirmed. Keep this ticket safe — winners are drawn from it.';
$modal_name  = isset( $_GET['gw_name'] ) ? sanitize_text_field( $_GET['gw_name'] ) : '';

/** Email rule for a giveaway. */
$gw_email_rule = function ( $giveaway_id ) {
	$meta    = aws_sbg_get_giveaway_meta( $giveaway_id );
	$domains = array_filter( array_map( 'trim', explode( ',', (string) $meta['allowed_email_domains'] ) ) );
	if ( empty( $domains ) || in_array( '*', $domains, true ) ) {
		return array( 'restricted' => false, 'domain' => '' );
	}
	return array( 'restricted' => true, 'domain' => strtolower( $domains[0] ) );
};

/** Prize panel (single source so featured + cards match). */
$render_prize = function ( $meta ) {
	if ( empty( $meta['prize_title'] ) ) {
		return;
	}
	?>
	<div class="sbg-prize">
		<span class="sbg-prize-mark" aria-hidden="true">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
		</span>
		<div class="sbg-prize-body">
			<span class="sbg-prize-label">Prize</span>
			<div class="sbg-prize-title"><?php echo esc_html( $meta['prize_title'] ); ?></div>
			<?php if ( ! empty( $meta['prize_description'] ) ) : ?>
				<div class="sbg-prize-desc"><?php echo esc_html( $meta['prize_description'] ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
};

/** The entry form, shared by the featured giveaway and the secondary cards. */
$render_form = function ( $giveaway, $rule ) use ( $source_tag ) {
	$gid    = $giveaway->ID;
	$reqs   = aws_sbg_get_giveaway_requirements( $gid );
	$fields = aws_sbg_get_giveaway_fields( $gid );
	?>
	<form class="sbg-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="aws_sbg_giveaway_submit">
		<input type="hidden" name="giveaway_id" value="<?php echo esc_attr( $gid ); ?>">
		<input type="hidden" name="gw_source" value="<?php echo esc_attr( $source_tag ); ?>">
		<input type="text" name="website_url" tabindex="-1" autocomplete="off" class="sbg-hp" aria-hidden="true">
		<?php wp_nonce_field( 'aws_sbg_giveaway_submit', 'giveaway_nonce' ); ?>

		<?php if ( ! empty( $reqs ) ) : ?>
			<div class="sbg-sec-title">Requirements</div>
			<div class="sbg-reqs">
				<?php
				foreach ( $reqs as $req ) :
					$rid  = $req['id'];
					$name = 'gw_check[' . $rid . ']';
					$reqd = ! empty( $req['required'] ) ? 'required' : '';
					$star = ! empty( $req['required'] ) ? ' <span class="sbg-star">*</span>' : '';
					?>
					<?php if ( 'upload' === $req['type'] ) : ?>
						<div class="sbg-req-block" data-type="upload">
							<label class="sbg-req-label" for="gw_file_<?php echo esc_attr( $rid ); ?>"><?php echo esc_html( $req['label'] ) . $star; ?>
								<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
							</label>
							<div class="sbg-drop" data-state="idle">
								<input type="file" id="gw_file_<?php echo esc_attr( $rid ); ?>" name="gw_file_<?php echo esc_attr( $rid ); ?>" accept="image/*" class="sbg-drop-input" <?php echo esc_attr( $reqd ); ?>>
								<label class="sbg-drop-inner" for="gw_file_<?php echo esc_attr( $rid ); ?>">
									<span class="sbg-drop-icon" aria-hidden="true">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m0 0L8 8m4-4l4 4"/><path d="M20 16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2"/></svg>
									</span>
									<span class="sbg-drop-title">Drag an image here or <span class="sbg-drop-browse">browse</span></span>
									<span class="sbg-drop-hint">JPG, PNG or WebP · optimised to WebP on your device · max 2 MB</span>
								</label>
								<div class="sbg-drop-preview">
									<img class="sbg-drop-thumb" alt="">
									<div class="sbg-drop-meta">
										<div class="sbg-drop-filename"></div>
										<div class="sbg-drop-sizes"></div>
									</div>
									<button type="button" class="sbg-drop-remove">Remove</button>
								</div>
								<div class="sbg-drop-busy"><span class="sbg-drop-spin"></span> Optimising image…</div>
							</div>
						</div>
					<?php elseif ( 'proof' === $req['type'] ) : ?>
						<div class="sbg-req-block" data-type="proof">
							<label class="sbg-req-label"><?php echo esc_html( $req['label'] ) . $star; ?>
								<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
							</label>
							<div class="sbg-req sbg-req-proof">
								<label class="sbg-check">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
									<span>I've completed this</span>
								</label>
								<input type="text" class="sbg-proof-input" name="gw_text[<?php echo esc_attr( $rid ); ?>]" placeholder="Paste your link / proof here" <?php echo ! empty( $req['required'] ) ? 'required' : ''; ?>>
							</div>
						</div>
					<?php else : ?>
						<label class="sbg-req" data-type="check">
							<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
							<span class="sbg-req-label"><?php echo esc_html( $req['label'] ) . $star; ?>
								<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
							</span>
						</label>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="sbg-sec-title">Your details</div>
		<div class="sbg-fields">
			<?php
			foreach ( $fields as $field ) :
				$fid    = $field['id'];
				$type   = $field['type'];
				$wide   = in_array( $type, array( 'textarea' ), true ) ? ' full' : '';
				$reqd   = ! empty( $field['required'] ) ? 'required' : '';
				$is_mail = ( 'email' === $type );
				$dom     = ( $is_mail && $rule['restricted'] ) ? $rule['domain'] : '';
				$attrs   = '';
				if ( $dom ) {
					$attrs = ' data-domain="' . esc_attr( $dom ) . '" pattern="^[^@\\s]+@' . esc_attr( str_replace( '.', '\\.', $dom ) ) . '$" placeholder="id@' . esc_attr( $dom ) . '"';
				}
				?>
				<div class="sbg-field<?php echo esc_attr( $wide ); ?><?php echo $is_mail ? ' sbg-field-email' : ''; ?>" data-field="<?php echo esc_attr( $fid ); ?>">
					<label for="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>"><?php echo esc_html( $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' <span class="sbg-star">*</span>' : ''; ?></label>
					<?php if ( 'textarea' === $type ) : ?>
						<textarea id="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>" name="gw_field[<?php echo esc_attr( $fid ); ?>]" rows="3" <?php echo esc_attr( $reqd ); ?>></textarea>
					<?php elseif ( 'select' === $type ) : ?>
						<select id="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>" name="gw_field[<?php echo esc_attr( $fid ); ?>]" <?php echo esc_attr( $reqd ); ?>>
							<option value="">Select…</option>
							<?php foreach ( array_filter( array_map( 'trim', explode( ',', (string) $field['options'] ) ) ) as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="<?php echo esc_attr( 'tel' === $type ? 'tel' : ( $is_mail ? 'email' : 'text' ) ); ?>" id="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>" name="gw_field[<?php echo esc_attr( $fid ); ?>]" <?php echo $attrs; ?> <?php echo $reqd; ?>>
					<?php endif; ?>
					<?php if ( $dom ) : ?>
						<span class="sbg-field-msg" data-live aria-live="polite"></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="sbg-submit-row">
			<button type="submit" class="sbg-btn">Enter giveaway</button>
			<span class="sbg-form-error" aria-live="polite"></span>
		</div>
	</form>
	<?php
};
?>

<style>
	.sbg-giveaway-root { font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #ffffff !important; color: #0f172a !important; width: 100%; overflow-x: hidden; }
	.sbg-giveaway-root h1, .sbg-giveaway-root h2, .sbg-giveaway-root h3 { color: #020617 !important; margin: 0; }
	.sbg-giveaway-root p { color: #475569 !important; margin: 0; }
	.sbg-giveaway-root a { text-decoration: none !important; }
	.sbg-giveaway-root * { box-sizing: border-box; }

	.sbg-giveaway-root .aurora-canvas { position: relative; overflow: hidden; background: linear-gradient(180deg, #ffffff 0%, #f6f6fc 55%, #ffffff 100%); }
	.sbg-giveaway-root .aurora-blob { position: absolute; border-radius: 9999px; filter: blur(80px); pointer-events: none; z-index: 0; }
	.sbg-giveaway-root .aurora-purple { width: 520px; height: 520px; top: -170px; right: -110px; background: radial-gradient(circle, rgba(147,83,255,0.26), transparent 65%); animation: sbgAuroraFloat 12s ease-in-out infinite; }
	.sbg-giveaway-root .aurora-orange { width: 440px; height: 440px; top: 16%; left: -150px; background: radial-gradient(circle, rgba(255,153,0,0.16), transparent 65%); animation: sbgAuroraFloat 14s ease-in-out infinite reverse; }
	.sbg-giveaway-root .aurora-green { width: 420px; height: 420px; bottom: 2%; right: 10%; background: radial-gradient(circle, rgba(0,239,136,0.15), transparent 65%); animation: sbgAuroraFloat 10s ease-in-out infinite 2s; }
	@keyframes sbgAuroraFloat { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(26px,-20px) scale(1.07); } }
	.sbg-giveaway-root .sbg-grid-lines { position: absolute; inset: 0; z-index: 1; pointer-events: none; background-size: 38px 38px;
		background-image: linear-gradient(to right, rgba(147,83,255,0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(147,83,255,0.05) 1px, transparent 1px);
		mask-image: radial-gradient(ellipse 85% 70% at 50% 30%, #000 40%, transparent 92%);
		-webkit-mask-image: radial-gradient(ellipse 85% 70% at 50% 30%, #000 40%, transparent 92%); }

	.sbg-giveaway-root .sbg-inner { max-width: 940px; margin: 0 auto; padding: 48px 20px 80px; position: relative; z-index: 10; }
	.sbg-giveaway-root .sbg-hp { position: absolute !important; left: -9999px !important; width: 1px; height: 1px; opacity: 0; }

	/* Alerts */
	.sbg-giveaway-root .sbg-alert { display: flex; gap: 12px; align-items: flex-start; max-width: 760px; margin: 0 auto 22px; padding: 14px 18px; border-radius: 14px; border: 1px solid; }
	.sbg-giveaway-root .sbg-alert-icon { flex-shrink: 0; width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; color: #fff; }
	.sbg-giveaway-root .sbg-alert-title { font-size: 14px; font-weight: 900; }
	.sbg-giveaway-root .sbg-alert-text { font-size: 13px; font-weight: 600; margin-top: 2px; }
	.sbg-giveaway-root .sbg-alert.success { background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
	.sbg-giveaway-root .sbg-alert.success .sbg-alert-icon { background: #10b981; }
	.sbg-giveaway-root .sbg-alert.info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
	.sbg-giveaway-root .sbg-alert.info .sbg-alert-icon { background: #2563eb; }
	.sbg-giveaway-root .sbg-alert.error { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
	.sbg-giveaway-root .sbg-alert.error .sbg-alert-icon { background: #ef4444; }

	/* Featured giveaway */
	.sbg-giveaway-root .sbg-feature { position: relative; background: #ffffff; border: 1px solid #e8e8f2; border-radius: 26px; padding: 30px; box-shadow: 0 30px 70px -34px rgba(76,29,149,0.34); overflow: hidden; animation: sbgRise .6s cubic-bezier(.16,1,.3,1) both; }
	.sbg-giveaway-root .sbg-feature::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #7928ca, #9353ff, #c4b5fd); }
	@keyframes sbgRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
	.sbg-giveaway-root .sbg-feature-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
	.sbg-giveaway-root .sbg-live { display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 12px; font-weight: 800; padding: 5px 12px; border-radius: 999px; }
	.sbg-giveaway-root .sbg-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: sbgLive 1.8s infinite; }
	@keyframes sbgLive { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.55); } 70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
	.sbg-giveaway-root .sbg-entries { font-size: 12.5px; font-weight: 800; color: #7928ca; background: #f5f3ff; border: 1px solid #ede9fe; padding: 5px 12px; border-radius: 999px; white-space: nowrap; }
	.sbg-giveaway-root .sbg-feature h1 { font-size: clamp(28px, 4vw, 42px); font-weight: 900; letter-spacing: -0.03em; line-height: 1.06; margin-bottom: 10px; }
	.sbg-giveaway-root .sbg-feature-desc { font-size: 16px; line-height: 1.7; max-width: 720px; }

	/* Prize panel */
	.sbg-giveaway-root .sbg-prize { position: relative; display: flex; align-items: center; gap: 16px; background: linear-gradient(135deg, #211a4d 0%, #4c1d95 55%, #7c3aed 100%); border-radius: 18px; padding: 18px 20px; margin: 20px 0 4px; overflow: hidden; }
	.sbg-giveaway-root .sbg-prize::after { content: ''; position: absolute; right: -50px; top: -50px; width: 170px; height: 170px; background: radial-gradient(circle, rgba(255,255,255,0.20), transparent 70%); pointer-events: none; }
	.sbg-giveaway-root .sbg-prize-mark { flex-shrink: 0; width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22); color: #fbbf24; display: flex; align-items: center; justify-content: center; }
	.sbg-giveaway-root .sbg-prize-body { min-width: 0; position: relative; z-index: 1; }
	.sbg-giveaway-root .sbg-prize-label { display: block; font-size: 10.5px; font-weight: 900; letter-spacing: 0.16em; text-transform: uppercase; color: #c4b5fd; margin-bottom: 3px; }
	.sbg-giveaway-root .sbg-prize-title { font-size: 19px; font-weight: 800; color: #ffffff !important; line-height: 1.3; }
	.sbg-giveaway-root .sbg-prize-desc { font-size: 13px; color: rgba(255,255,255,0.74) !important; margin-top: 4px; line-height: 1.5; }

	/* Secondary cards */
	.sbg-giveaway-root .sbg-more-title { text-align: center; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.14em; color: #94a3b8; margin: 46px 0 20px; }
	.sbg-giveaway-root .sbg-card { background: #ffffff; border: 1px solid #e8e8f2; border-radius: 22px; padding: 26px; margin-bottom: 18px; box-shadow: 0 16px 44px -30px rgba(15,23,42,0.3); }
	.sbg-giveaway-root .sbg-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
	.sbg-giveaway-root .sbg-card h2 { font-size: clamp(21px, 2.6vw, 27px); font-weight: 900; letter-spacing: -0.02em; }
	.sbg-giveaway-root .sbg-card-desc { font-size: 14.5px; line-height: 1.7; }

	/* Section labels */
	.sbg-giveaway-root .sbg-sec-title { font-size: 11.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin: 26px 0 12px; }
	.sbg-giveaway-root .sbg-star { color: #ef4444; }

	/* Requirements */
	.sbg-giveaway-root .sbg-req { display: flex; align-items: flex-start; gap: 11px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 13px 15px; margin-bottom: 10px; transition: border-color .18s ease, background .18s ease; }
	.sbg-giveaway-root .sbg-req:hover { border-color: #cbd5e1; background: #ffffff; }
	.sbg-giveaway-root .sbg-req.is-error { border-color: #fca5a5; background: #fff5f5; }
	.sbg-giveaway-root .sbg-req input[type=checkbox] { width: 18px; height: 18px; min-width: 18px; margin-top: 2px; accent-color: #10b981; cursor: pointer; }
	.sbg-giveaway-root .sbg-req-label { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.45; }
	.sbg-giveaway-root .sbg-req-help { display: block; font-size: 12px; font-weight: 500; color: #64748b; margin-top: 3px; }
	.sbg-giveaway-root .sbg-req-block { margin-bottom: 14px; }
	.sbg-giveaway-root .sbg-req-block > .sbg-req-label { display: block; margin-bottom: 8px; }
	.sbg-giveaway-root .sbg-req-proof { flex-direction: column; gap: 10px; }
	.sbg-giveaway-root .sbg-check { display: flex; align-items: flex-start; gap: 11px; cursor: pointer; font-size: 14px; font-weight: 700; color: #0f172a; }
	.sbg-giveaway-root .sbg-proof-input { width: 100%; padding: 10px 13px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 13.5px; }

	/* Dropzone */
	.sbg-giveaway-root .sbg-drop { position: relative; border: 1.5px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; transition: border-color .18s ease, background .18s ease; }
	.sbg-giveaway-root .sbg-drop.is-drag { border-color: #9353ff; background: #faf5ff; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] { border-style: solid; border-color: #a7f3d0; background: #ffffff; }
	.sbg-giveaway-root .sbg-drop.is-error { border-color: #fca5a5; background: #fff5f5; }
	.sbg-giveaway-root .sbg-drop-input { position: absolute; width: 1px; height: 1px; padding: 0; margin: 0; opacity: 0; overflow: hidden; clip: rect(0 0 0 0); border: 0; }
	.sbg-giveaway-root .sbg-drop-inner { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 7px; padding: 26px 18px; cursor: pointer; text-align: center; }
	.sbg-giveaway-root .sbg-drop-icon { width: 44px; height: 44px; border-radius: 12px; background: #f5f3ff; border: 1px solid #ede9fe; color: #7928ca; display: flex; align-items: center; justify-content: center; }
	.sbg-giveaway-root .sbg-drop-title { font-size: 14px; font-weight: 700; color: #0f172a; }
	.sbg-giveaway-root .sbg-drop-browse { color: #7928ca; text-decoration: underline; text-underline-offset: 2px; }
	.sbg-giveaway-root .sbg-drop-hint { font-size: 12px; color: #94a3b8; }
	.sbg-giveaway-root .sbg-drop-preview { display: none; align-items: center; gap: 14px; padding: 14px; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] .sbg-drop-inner { display: none; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] .sbg-drop-preview { display: flex; }
	.sbg-giveaway-root .sbg-drop-thumb { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0; background: #f1f5f9; flex-shrink: 0; }
	.sbg-giveaway-root .sbg-drop-meta { flex: 1 1 auto; min-width: 0; }
	.sbg-giveaway-root .sbg-drop-filename { font-size: 13.5px; font-weight: 800; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.sbg-giveaway-root .sbg-drop-sizes { font-size: 12px; color: #64748b; margin-top: 3px; }
	.sbg-giveaway-root .sbg-drop-sizes .saved { color: #059669; font-weight: 800; }
	.sbg-giveaway-root .sbg-drop-remove { flex-shrink: 0; background: #ffffff; border: 1px solid #e2e8f0; color: #dc2626; font-size: 12px; font-weight: 700; padding: 7px 13px; border-radius: 9px; cursor: pointer; }
	.sbg-giveaway-root .sbg-drop-remove:hover { background: #fef2f2; border-color: #fecaca; }
	.sbg-giveaway-root .sbg-drop-busy { display: none; position: absolute; inset: 0; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.86); border-radius: 14px; font-size: 13px; font-weight: 800; color: #7928ca; }
	.sbg-giveaway-root .sbg-drop[data-state="busy"] .sbg-drop-busy { display: flex; }
	.sbg-giveaway-root .sbg-drop-spin { width: 16px; height: 16px; border: 2px solid #e9d5ff; border-top-color: #7928ca; border-radius: 50%; animation: sbgSpin .7s linear infinite; }
	@keyframes sbgSpin { to { transform: rotate(360deg); } }

	/* Fields */
	.sbg-giveaway-root .sbg-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
	.sbg-giveaway-root .sbg-field { display: flex; flex-direction: column; gap: 5px; }
	.sbg-giveaway-root .sbg-field.full { grid-column: 1 / -1; }
	.sbg-giveaway-root .sbg-field > label { font-size: 12px; font-weight: 800; color: #334155; }
	.sbg-giveaway-root .sbg-field input, .sbg-giveaway-root .sbg-field textarea, .sbg-giveaway-root .sbg-field select { width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; color: #0f172a; background: #ffffff; outline: none; font-family: inherit; transition: border-color .18s ease, box-shadow .18s ease; }
	.sbg-giveaway-root .sbg-field input:focus, .sbg-giveaway-root .sbg-field textarea:focus, .sbg-giveaway-root .sbg-field select:focus { border-color: #9353ff; box-shadow: 0 0 0 3px rgba(147,83,255,0.14); }
	.sbg-giveaway-root .sbg-field input.is-ok { border-color: #86efac; }
	.sbg-giveaway-root .sbg-field input.is-error { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }
	.sbg-giveaway-root .sbg-field-msg { font-size: 11.5px; font-weight: 700; min-height: 0; }
	.sbg-giveaway-root .sbg-field-msg.ok { color: #059669; }
	.sbg-giveaway-root .sbg-field-msg.bad { color: #dc2626; }
	@media (max-width: 640px) { .sbg-giveaway-root .sbg-fields { grid-template-columns: 1fr; } }

	.sbg-giveaway-root .sbg-submit-row { margin-top: 22px; }
	.sbg-giveaway-root .sbg-btn { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; border-radius: 14px; background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%); color: #ffffff; font-weight: 800; font-size: 15px; letter-spacing: 0.02em; box-shadow: 0 14px 30px -10px rgba(147,83,255,0.6); border: none; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
	.sbg-giveaway-root .sbg-btn:hover { transform: translateY(-2px); box-shadow: 0 18px 38px -10px rgba(147,83,255,0.7); }
	.sbg-giveaway-root .sbg-btn:disabled { opacity: .75; cursor: not-allowed; transform: none; }
	.sbg-giveaway-root .sbg-form-error { display: block; text-align: center; margin-top: 10px; font-size: 13px; font-weight: 700; color: #dc2626; }

	/* Winners */
	.sbg-giveaway-root .sbg-winners-head { text-align: center; margin: 46px 0 22px; }
	.sbg-giveaway-root .sbg-winners-head h2 { font-size: clamp(24px, 3.4vw, 34px); font-weight: 900; letter-spacing: -0.02em; }
	.sbg-giveaway-root .sbg-winner-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
	.sbg-giveaway-root .sbg-winner-card { background: #ffffff; border: 1.5px solid #a7f3d0; border-radius: 16px; padding: 20px; box-shadow: 0 12px 30px -18px rgba(16,185,129,0.35); }
	.sbg-giveaway-root .sbg-winner-rank { font-size: 11px; font-weight: 900; color: #059669; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-winner-ticket { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 900; color: #7928ca; font-size: 18px; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-winner-name { font-weight: 800; color: #0f172a; font-size: 15px; }
	.sbg-giveaway-root .sbg-winner-email { font-size: 12.5px; color: #64748b; margin-top: 2px; }

	.sbg-giveaway-root .sbg-empty { text-align: center; padding: 70px 24px; border: 2px dashed #e2e8f0; border-radius: 22px; }

	/* Success pop-up */
	.sbg-giveaway-root .sbg-modal { position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 20px; }
	.sbg-giveaway-root .sbg-modal.is-open { display: flex; }
	.sbg-giveaway-root .sbg-modal-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
	.sbg-giveaway-root .sbg-modal-card { position: relative; width: 100%; max-width: 400px; background: #ffffff; border-radius: 22px; overflow: hidden; box-shadow: 0 40px 90px -30px rgba(15,23,42,0.55); animation: sbgModalPop .3s cubic-bezier(.16,1,.3,1) both; text-align: center; }
	@keyframes sbgModalPop { from { opacity: 0; transform: translateY(16px) scale(.96); } to { opacity: 1; transform: none; } }
	.sbg-giveaway-root .sbg-modal-bar { height: 5px; background: linear-gradient(90deg, #7928ca, #9353ff, #c4b5fd); }
	.sbg-giveaway-root .sbg-modal-x { position: absolute; top: 14px; right: 14px; width: 30px; height: 30px; border-radius: 9px; border: none; background: #f1f5f9; color: #64748b; font-size: 13px; font-weight: 700; cursor: pointer; }
	.sbg-giveaway-root .sbg-modal-x:hover { background: #e2e8f0; color: #0f172a; }
	.sbg-giveaway-root .sbg-modal-body { padding: 26px 24px 24px; }
	.sbg-giveaway-root .sbg-modal-icon { width: 56px; height: 56px; border-radius: 50%; background: #ecfdf5; border: 2px solid #a7f3d0; color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
	.sbg-giveaway-root .sbg-modal-title { font-size: 24px; font-weight: 900; letter-spacing: -0.02em; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-modal-sub { font-size: 14px; line-height: 1.6; color: #64748b !important; margin-bottom: 18px; }
	.sbg-giveaway-root .sbg-ticket { background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 16px; padding: 16px; margin-bottom: 20px; }
	.sbg-giveaway-root .sbg-ticket-label { display: block; font-size: 10.5px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px; }
	.sbg-giveaway-root .sbg-ticket-num { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 26px; font-weight: 900; color: #7928ca; letter-spacing: 0.02em; }
	.sbg-giveaway-root .sbg-ticket-name { font-size: 13px; font-weight: 700; color: #475569; margin-top: 4px; }
	body.sbg-noscroll { overflow: hidden; }

	@media (prefers-reduced-motion: reduce) { .sbg-giveaway-root * { animation: none !important; } }
</style>

<div class="sbg-giveaway-root" data-error="<?php echo esc_attr( $gw_error ); ?>" data-error-gw="<?php echo esc_attr( $gw_error_gid ); ?>">
	<div class="aurora-canvas">
		<div class="aurora-blob aurora-purple"></div>
		<div class="aurora-blob aurora-orange"></div>
		<div class="aurora-blob aurora-green"></div>
		<div class="sbg-grid-lines"></div>

		<div class="sbg-inner">
			<?php if ( 'error' === $gw_status ) : ?>
				<div class="sbg-alert error">
					<span class="sbg-alert-icon">!</span>
					<div>
						<div class="sbg-alert-title">We couldn't submit your entry</div>
						<div class="sbg-alert-text"><?php echo esc_html( $error_messages[ $gw_error ] ?? 'Something went wrong. Please try again.' ); ?></div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $featured ) :
				$meta = aws_sbg_get_giveaway_meta( $featured->ID );
				$rule = $gw_email_rule( $featured->ID );
				$count = aws_sbg_get_giveaway_live_count( $featured->ID );
			?>
				<section class="sbg-feature">
					<div class="sbg-feature-top">
						<span class="sbg-live"><span class="sbg-live-dot"></span> Live now</span>
						<span class="sbg-entries"><?php echo esc_html( number_format( $count ) ); ?> entered</span>
					</div>
					<h1><?php echo esc_html( $featured->post_title ); ?></h1>
					<?php if ( ! empty( $meta['giveaway_description'] ) ) : ?>
						<p class="sbg-feature-desc"><?php echo esc_html( $meta['giveaway_description'] ); ?></p>
					<?php endif; ?>
					<?php $render_prize( $meta ); ?>
					<?php $render_form( $featured, $rule ); ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $others ) ) : ?>
				<div class="sbg-more-title">More giveaways</div>
				<?php foreach ( $others as $giveaway ) :
					$meta  = aws_sbg_get_giveaway_meta( $giveaway->ID );
					$rule  = $gw_email_rule( $giveaway->ID );
					$count = aws_sbg_get_giveaway_live_count( $giveaway->ID );
				?>
					<section class="sbg-card">
						<div class="sbg-card-top">
							<h2><?php echo esc_html( $giveaway->post_title ); ?></h2>
							<span class="sbg-entries"><?php echo esc_html( number_format( $count ) ); ?> entered</span>
						</div>
						<?php if ( ! empty( $meta['giveaway_description'] ) ) : ?>
							<p class="sbg-card-desc"><?php echo esc_html( $meta['giveaway_description'] ); ?></p>
						<?php endif; ?>
						<?php $render_prize( $meta ); ?>
						<?php $render_form( $giveaway, $rule ); ?>
					</section>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( ! empty( $announced_giveaways ) ) : ?>
				<div class="sbg-winners-head"><h2>Winners</h2></div>
				<?php
				global $wpdb;
				foreach ( $announced_giveaways as $giveaway ) :
					$gid     = $giveaway->ID;
					$meta    = aws_sbg_get_giveaway_meta( $gid );
					$table   = $wpdb->prefix . 'aws_giveaway_entries';
					$winners = $wpdb->get_results( $wpdb->prepare(
						"SELECT ticket_number, full_name, email FROM $table WHERE giveaway_id = %d AND is_winner = 1 ORDER BY id ASC",
						$gid
					), ARRAY_A );
					?>
					<section class="sbg-card">
						<div class="sbg-card-top">
							<h2><?php echo esc_html( $giveaway->post_title ); ?></h2>
							<span class="sbg-entries" style="background:#ecfdf5;border-color:#a7f3d0;color:#059669;">Winners announced</span>
						</div>
						<?php if ( ! empty( $winners ) ) : ?>
							<div class="sbg-winner-grid">
								<?php
								foreach ( $winners as $idx => $win ) :
									$parts  = explode( '@', $win['email'] );
									$user   = $parts[0] ?? '';
									$domain = $parts[1] ?? '';
									$masked = ( strlen( $user ) > 3 ) ? substr( $user, 0, 3 ) . '***@' . $domain : '***@' . $domain;
									?>
									<div class="sbg-winner-card">
										<div class="sbg-winner-rank">Winner #<?php echo esc_html( $idx + 1 ); ?></div>
										<div class="sbg-winner-ticket">#<?php echo esc_html( $win['ticket_number'] ); ?></div>
										<div class="sbg-winner-name"><?php echo esc_html( $win['full_name'] ); ?></div>
										<div class="sbg-winner-email"><?php echo esc_html( $masked ); ?></div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="sbg-card-desc">Results will be published here shortly.</p>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( empty( $open_giveaways ) && empty( $announced_giveaways ) ) : ?>
				<div class="sbg-empty">
					<h3 style="font-size:20px;font-weight:900;margin-bottom:6px;">No active giveaways right now</h3>
					<p style="color:#64748b;">Check back soon — new giveaways are announced regularly.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $show_modal ) : ?>
		<div class="sbg-modal" id="sbg-success" role="dialog" aria-modal="true" aria-labelledby="sbg-modal-title">
			<div class="sbg-modal-backdrop" data-close></div>
			<div class="sbg-modal-card">
				<div class="sbg-modal-bar"></div>
				<button type="button" class="sbg-modal-x" data-close aria-label="Close">✕</button>
				<div class="sbg-modal-body">
					<span class="sbg-modal-icon" aria-hidden="true">
						<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
					</span>
					<h3 class="sbg-modal-title" id="sbg-modal-title"><?php echo esc_html( $modal_title ); ?></h3>
					<p class="sbg-modal-sub"><?php echo esc_html( $modal_sub ); ?></p>
					<div class="sbg-ticket">
						<span class="sbg-ticket-label">Your ticket</span>
						<div class="sbg-ticket-num">#<?php echo esc_html( $gw_ticket ); ?></div>
						<?php if ( '' !== $modal_name ) : ?>
							<div class="sbg-ticket-name"><?php echo esc_html( $modal_name ); ?></div>
						<?php endif; ?>
					</div>
					<button type="button" class="sbg-btn" data-close>Done</button>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
(function () {
	var MAX_BYTES = 2 * 1024 * 1024;
	var MAX_DIM   = 1600;

	/* ---------- Live email-domain validation ---------- */
	function initEmailField(input) {
		var domain = (input.getAttribute('data-domain') || '').toLowerCase();
		if (!domain) { return; }
		var msg = input.closest('.sbg-field').querySelector('.sbg-field-msg');

		function check(strict) {
			var v = input.value.trim();
			if (!v) { input.setCustomValidity(''); setMsg('', ''); input.classList.remove('is-ok', 'is-error'); return; }
			var at = v.lastIndexOf('@');
			var ok = at > 0 && v.slice(at + 1).toLowerCase() === domain;
			if (ok) {
				input.setCustomValidity('');
				input.classList.add('is-ok'); input.classList.remove('is-error');
				setMsg('✓ @' + domain, 'ok');
			} else if (strict) {
				var m = 'Only @' + domain + ' emails are accepted.';
				input.setCustomValidity(m);
				input.classList.add('is-error'); input.classList.remove('is-ok');
				setMsg(m, 'bad');
			} else {
				input.setCustomValidity('');
				input.classList.remove('is-ok', 'is-error');
				setMsg('Must end with @' + domain, '');
			}
		}
		function setMsg(text, cls) {
			if (!msg) { return; }
			msg.textContent = text;
			msg.className = 'sbg-field-msg' + (cls ? ' ' + cls : '');
		}

		input.addEventListener('input', function () { check(false); });
		input.addEventListener('blur', function () { check(true); });
	}
	document.querySelectorAll('.sbg-giveaway-root input[data-domain]').forEach(initEmailField);

	/* ---------- Inline error highlighting after a failed submit ---------- */
	var root = document.querySelector('.sbg-giveaway-root');
	var code = root ? root.getAttribute('data-error') : '';
	var gid  = root ? root.getAttribute('data-error-gw') : '';
	if (code && gid) {
		var idField = document.querySelector('.sbg-form input[name="giveaway_id"][value="' + gid + '"]');
		var form = idField ? idField.closest('form') : null;
		if (form) {
			if (code === 'domain' || code === 'email') {
				var em = form.querySelector('.sbg-field-email input');
				if (em) {
					em.classList.add('is-error');
					if (em.getAttribute('data-domain')) {
						var emsg = em.closest('.sbg-field').querySelector('.sbg-field-msg');
						if (emsg) { emsg.textContent = 'Only @' + em.getAttribute('data-domain') + ' emails are accepted.'; emsg.className = 'sbg-field-msg bad'; }
					}
					em.scrollIntoView({ behavior: 'smooth', block: 'center' });
					em.focus({ preventScroll: true });
				}
			} else if (code.indexOf('req') === 0) {
				var reqs = form.querySelector('.sbg-reqs');
				if (reqs) {
					reqs.scrollIntoView({ behavior: 'smooth', block: 'center' });
					reqs.querySelectorAll('.sbg-req, .sbg-drop').forEach(function (el) { el.classList.add('is-error'); });
				}
			}
			var formErr = form.querySelector('.sbg-form-error');
			if (formErr) { formErr.textContent = 'Please review the highlighted fields and try again.'; }
		}
	}

	/* ---------- Image optimisation (resize + WebP) ---------- */
	function fmt(bytes) {
		return bytes >= 1048576 ? (bytes / 1048576).toFixed(2) + ' MB' : Math.round(bytes / 1024) + ' KB';
	}
	function readDataURL(file) {
		return new Promise(function (res, rej) { var r = new FileReader(); r.onload = function (e) { res(e.target.result); }; r.onerror = rej; r.readAsDataURL(file); });
	}
	function loadImg(src) {
		return new Promise(function (res, rej) { var i = new Image(); i.onload = function () { res(i); }; i.onerror = rej; i.src = src; });
	}
	function toWebp(canvas, q) {
		return new Promise(function (res) { canvas.toBlob(function (b) { res(b); }, 'image/webp', q); });
	}
	function drawResized(source, maxDim) {
		var w = source.naturalWidth || source.width;
		var h = source.naturalHeight || source.height;
		var scale = Math.min(1, maxDim / Math.max(w, h));
		var canvas = document.createElement('canvas');
		canvas.width = Math.round(w * scale);
		canvas.height = Math.round(h * scale);
		canvas.getContext('2d').drawImage(source, 0, 0, canvas.width, canvas.height);
		return canvas;
	}
	async function compress(file) {
		var img = await loadImg(await readDataURL(file));
		var canvas = drawResized(img, MAX_DIM);
		var blob = null;
		var qualities = [0.82, 0.72, 0.62, 0.5];
		for (var i = 0; i < qualities.length; i++) {
			blob = await toWebp(canvas, qualities[i]);
			if (blob && blob.size <= MAX_BYTES) { break; }
		}
		if (blob && blob.size > MAX_BYTES) {
			blob = await toWebp(drawResized(canvas, MAX_DIM * 0.7), 0.72);
		}
		var isWebp = blob && blob.type === 'image/webp';
		var base = file.name.replace(/\.[^/.]+$/, '');
		return new File([blob], base + (isWebp ? '.webp' : '.png'), { type: blob.type || 'image/jpeg' });
	}
	function initDrop(drop) {
		var input  = drop.querySelector('.sbg-drop-input');
		var thumb  = drop.querySelector('.sbg-drop-thumb');
		var fname  = drop.querySelector('.sbg-drop-filename');
		var sizes  = drop.querySelector('.sbg-drop-sizes');
		var remove = drop.querySelector('.sbg-drop-remove');

		function reset() { input.value = ''; thumb.removeAttribute('src'); drop.setAttribute('data-state', 'idle'); drop.classList.remove('is-error'); }

		async function handle(file) {
			if (!file) { return; }
			if (!file.type || file.type.indexOf('image/') !== 0) { alert('Please choose an image file (JPG, PNG or WebP).'); return; }
			drop.setAttribute('data-state', 'busy');
			drop.classList.remove('is-error');
			var original = file.size;
			var out;
			try { out = await compress(file); } catch (e) { out = file; }
			if (out.size > MAX_BYTES) { alert('This image is still larger than 2 MB after optimisation. Please pick a smaller one.'); reset(); return; }
			var dt = new DataTransfer();
			dt.items.add(out);
			input.files = dt.files;
			thumb.src = URL.createObjectURL(out);
			fname.textContent = out.name;
			sizes.innerHTML = '<span style="text-decoration:line-through;opacity:.7;">' + fmt(original) + '</span> &rarr; <span class="saved">' + fmt(out.size) + '</span>' + (out.type === 'image/webp' ? ' · WebP' : '');
			drop.setAttribute('data-state', 'ready');
		}

		input.addEventListener('change', function () { if (input.files[0]) { handle(input.files[0]); } });
		['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-drag'); }); });
		['dragleave', 'dragend'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-drag'); }); });
		drop.addEventListener('dragover', function (e) { e.preventDefault(); });
		drop.addEventListener('drop', function (e) {
			e.preventDefault();
			drop.classList.remove('is-drag');
			var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
			if (f) { handle(f); }
		});
		if (remove) { remove.addEventListener('click', function (e) { e.preventDefault(); reset(); }); }
	}
	document.querySelectorAll('.sbg-giveaway-root .sbg-drop').forEach(initDrop);

	/* ---------- Success pop-up ---------- */
	var modal = document.getElementById('sbg-success');
	if (modal) {
		function closeModal() {
			modal.classList.remove('is-open');
			document.body.classList.remove('sbg-noscroll');
		}
		modal.querySelectorAll('[data-close]').forEach(function (el) { el.addEventListener('click', closeModal); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('is-open')) { closeModal(); } });

		modal.classList.add('is-open');
		document.body.classList.add('sbg-noscroll');
		if (window.history && window.history.replaceState) {
			window.history.replaceState(null, '', window.location.pathname);
		}
		var doneBtn = modal.querySelector('.sbg-btn');
		if (doneBtn) { setTimeout(function () { doneBtn.focus(); }, 60); }
	}

	/* ---------- Submit feedback ---------- */
	document.querySelectorAll('.sbg-giveaway-root form').forEach(function (form) {
		form.addEventListener('submit', function () {
			var btn = form.querySelector('.sbg-btn');
			var err = form.querySelector('.sbg-form-error');
			if (err) { err.textContent = ''; }
			if (form.checkValidity && !form.checkValidity()) {
				return; // browser shows the first invalid field
			}
			if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }
		});
	});
})();
</script>

<?php
get_footer();
