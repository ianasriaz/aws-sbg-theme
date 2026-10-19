<?php
/**
 * Template Name: Campus Giveaway Page
 * File: page-giveaway.php
 *
 * Public multi-giveaway landing page. Leads with the active giveaway (brief +
 * entry panel), lists any others, then published winners. Each form enforces the
 * giveaway's own requirements (tick / link-or-text proof / optimised upload) and
 * email rule.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$open_giveaways      = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'open' ) ) : array();
$announced_giveaways = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'announced' ) ) : array();
$source_tag          = isset( $_GET['src'] ) ? sanitize_text_field( $_GET['src'] ) : 'web';

$featured = ! empty( $open_giveaways ) ? $open_giveaways[0] : null;
$others   = array_slice( $open_giveaways, 1 );

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

/** Meta line: entries · winners · closes. */
$render_meta = function ( $meta, $count ) {
	?>
	<div class="sbg-meta">
		<span><strong><?php echo esc_html( number_format( $count ) ); ?></strong> entries</span>
		<span class="sbg-meta-sep"></span>
		<span><strong><?php echo esc_html( (int) $meta['winner_count'] ); ?></strong> winners</span>
		<?php if ( ! empty( $meta['closes_at'] ) ) : ?>
			<span class="sbg-meta-sep"></span>
			<span>Closes <strong><?php echo esc_html( date_i18n( 'j M', strtotime( $meta['closes_at'] ) ) ); ?></strong></span>
		<?php endif; ?>
	</div>
	<?php
};

/** Prize block. */
$render_prize = function ( $meta ) {
	if ( empty( $meta['prize_title'] ) ) {
		return;
	}
	?>
	<div class="sbg-prize">
		<span class="sbg-prize-mark" aria-hidden="true">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
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

/** Entry form: requirements (left) + details panel (right). */
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

		<div class="sbg-entry-grid">
			<div class="sbg-reqs-col">
				<?php if ( ! empty( $reqs ) ) : ?>
					<div class="sbg-label">Requirements</div>
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
									<label class="sbg-req-title" for="gw_file_<?php echo esc_attr( $rid ); ?>"><?php echo esc_html( $req['label'] ) . $star; ?>
										<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
									</label>
									<div class="sbg-drop" data-state="idle">
										<input type="file" id="gw_file_<?php echo esc_attr( $rid ); ?>" name="gw_file_<?php echo esc_attr( $rid ); ?>" accept="image/*" class="sbg-drop-input" <?php echo esc_attr( $reqd ); ?>>
										<label class="sbg-drop-inner" for="gw_file_<?php echo esc_attr( $rid ); ?>">
											<span class="sbg-drop-icon" aria-hidden="true">
												<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m0 0L8 8m4-4l4 4"/><path d="M20 16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2"/></svg>
											</span>
											<span class="sbg-drop-title">Drop an image or <span class="sbg-drop-browse">browse</span></span>
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
								<div class="sbg-req sbg-req-proof" data-type="proof">
									<label class="sbg-req-head">
										<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
										<span class="sbg-req-title"><?php echo esc_html( $req['label'] ) . $star; ?>
											<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
										</span>
									</label>
									<input type="text" class="sbg-proof-input" name="gw_text[<?php echo esc_attr( $rid ); ?>]" placeholder="Paste your link / proof" <?php echo ! empty( $req['required'] ) ? 'required' : ''; ?>>
								</div>
							<?php else : ?>
								<label class="sbg-req" data-type="check">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
									<span class="sbg-req-title"><?php echo esc_html( $req['label'] ) . $star; ?>
										<?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?>
									</span>
								</label>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<aside class="sbg-details">
				<div class="sbg-details-head">
					<span class="sbg-label">Your details</span>
				</div>
				<div class="sbg-fields">
					<?php
					foreach ( $fields as $field ) :
						$fid     = $field['id'];
						$type    = $field['type'];
						$wide    = in_array( $type, array( 'textarea' ), true ) ? ' full' : '';
						$reqd    = ! empty( $field['required'] ) ? 'required' : '';
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
								<span class="sbg-field-msg" aria-live="polite"></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="sbg-submit">
					<button type="submit" class="sbg-btn">Enter giveaway</button>
					<span class="sbg-form-error" aria-live="polite"></span>
					<p class="sbg-note">One entry per person. Your details are only used to contact winners.</p>
				</div>
			</aside>
		</div>
	</form>
	<?php
};
?>

<style>
	.sbg-giveaway-root {
		--ink: #0e0e13;
		--ink2: #4b4b57;
		--muted: #8b8b96;
		--line: #e8e8ee;
		--line2: #f2f2f6;
		--paper: #ffffff;
		--bg: #f6f6f9;
		--accent: #6d28d9;
		--accent-soft: #f5f3ff;
		--gold: #b45309;
		--gold-soft: #fffbf4;
		--gold-line: #f0e2cd;
		--ok: #059669;
		--err: #dc2626;

		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		background: var(--bg) !important;
		color: var(--ink) !important;
		width: 100%;
		min-height: 100vh;
		-webkit-font-smoothing: antialiased;
	}
	.sbg-giveaway-root * { box-sizing: border-box; }
	.sbg-giveaway-root h1, .sbg-giveaway-root h2, .sbg-giveaway-root h3 { color: var(--ink) !important; margin: 0; font-weight: 800; }
	.sbg-giveaway-root p { color: var(--ink2) !important; margin: 0; }
	.sbg-giveaway-root a { text-decoration: none !important; }

	.sbg-giveaway-root .sbg-shell { max-width: 1120px; margin: 0 auto; padding: 40px 24px 96px; }

	/* Shared bits */
	.sbg-giveaway-root .sbg-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em; color: var(--muted); }
	.sbg-giveaway-root .sbg-star { color: var(--err); }
	.sbg-giveaway-root .sbg-hp { position: absolute !important; left: -9999px !important; width: 1px; height: 1px; opacity: 0; }

	.sbg-giveaway-root .sbg-eyebrow { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
	.sbg-giveaway-root .sbg-status { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; color: var(--ok); }
	.sbg-giveaway-root .sbg-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--ok); box-shadow: 0 0 0 0 rgba(5,150,105,0.5); animation: sbgPulse 2s infinite; }
	@keyframes sbgPulse { 0% { box-shadow: 0 0 0 0 rgba(5,150,105,0.45); } 70% { box-shadow: 0 0 0 7px rgba(5,150,105,0); } 100% { box-shadow: 0 0 0 0 rgba(5,150,105,0); } }

	/* Alerts */
	.sbg-giveaway-root .sbg-alert { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 26px; padding: 14px 16px; border-radius: 12px; border: 1px solid; }
	.sbg-giveaway-root .sbg-alert-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: #fff; }
	.sbg-giveaway-root .sbg-alert-title { font-size: 13.5px; font-weight: 800; }
	.sbg-giveaway-root .sbg-alert-text { font-size: 13px; margin-top: 2px; }
	.sbg-giveaway-root .sbg-alert.error { background: #fef2f2; border-color: #fbd5d5; color: var(--err); }
	.sbg-giveaway-root .sbg-alert.error .sbg-alert-icon { background: var(--err); }

	/* Hero brief */
	.sbg-giveaway-root .sbg-brief { max-width: 760px; margin-bottom: 34px; }
	.sbg-giveaway-root .sbg-brief h1 { font-size: clamp(36px, 6vw, 62px); font-weight: 800; letter-spacing: -0.035em; line-height: 1.02; margin-bottom: 16px; }
	.sbg-giveaway-root .sbg-lead { font-size: 17px; line-height: 1.65; max-width: 620px; }

	.sbg-giveaway-root .sbg-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 20px; font-size: 13.5px; color: var(--ink2); }
	.sbg-giveaway-root .sbg-meta strong { color: var(--ink); font-weight: 800; }
	.sbg-giveaway-root .sbg-meta-sep { width: 3px; height: 3px; border-radius: 50%; background: #c9c9d2; }

	/* Prize */
	.sbg-giveaway-root .sbg-prize { display: flex; align-items: center; gap: 14px; margin-top: 26px; padding: 16px 18px; background: var(--gold-soft); border: 1px solid var(--gold-line); border-radius: 14px; }
	.sbg-giveaway-root .sbg-prize-mark { flex-shrink: 0; width: 40px; height: 40px; border-radius: 11px; background: #fff; border: 1px solid var(--gold-line); color: var(--gold); display: flex; align-items: center; justify-content: center; }
	.sbg-giveaway-root .sbg-prize-label { display: block; font-size: 10.5px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--gold); margin-bottom: 3px; }
	.sbg-giveaway-root .sbg-prize-title { font-size: 16.5px; font-weight: 800; color: var(--ink) !important; line-height: 1.35; }
	.sbg-giveaway-root .sbg-prize-desc { font-size: 13.5px; color: var(--ink2) !important; margin-top: 3px; line-height: 1.5; }

	/* Entry grid */
	.sbg-giveaway-root .sbg-form { margin-top: 38px; }
	.sbg-giveaway-root .sbg-entry-grid { display: grid; grid-template-columns: 1fr 384px; gap: 44px; align-items: start; }
	@media (max-width: 900px) { .sbg-giveaway-root .sbg-entry-grid { grid-template-columns: 1fr; gap: 28px; } }

	/* Requirements */
	.sbg-giveaway-root .sbg-reqs { display: flex; flex-direction: column; gap: 10px; margin-top: 14px; }
	.sbg-giveaway-root .sbg-req { display: flex; align-items: flex-start; gap: 13px; padding: 15px 16px; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; cursor: pointer; transition: border-color .16s ease, background .16s ease; }
	.sbg-giveaway-root .sbg-req:hover { border-color: #d6d6de; }
	.sbg-giveaway-root .sbg-req.is-error { border-color: #f2b8b8; background: #fef7f7; }
	.sbg-giveaway-root .sbg-req-head { display: flex; align-items: flex-start; gap: 13px; cursor: pointer; }
	.sbg-giveaway-root .sbg-req-title { font-size: 14.5px; font-weight: 700; color: var(--ink); line-height: 1.45; }
	.sbg-giveaway-root .sbg-req-help { display: block; font-size: 12.5px; font-weight: 500; color: var(--muted); margin-top: 3px; }
	.sbg-giveaway-root .sbg-req-block { padding: 15px 16px; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; }
	.sbg-giveaway-root .sbg-req-block > .sbg-req-title { display: block; margin-bottom: 12px; }
	.sbg-giveaway-root .sbg-req-proof { flex-direction: column; gap: 12px; }
	.sbg-giveaway-root .sbg-proof-input { width: 100%; padding: 10px 13px; border: 1px solid var(--line); border-radius: 10px; font-size: 13.5px; color: var(--ink); background: var(--paper); outline: none; }

	/* Custom checkbox */
	.sbg-giveaway-root input[type="checkbox"] { appearance: none; -webkit-appearance: none; flex-shrink: 0; width: 20px; height: 20px; margin: 1px 0 0; border: 1.5px solid #cfcfd8; border-radius: 6px; background: #fff; cursor: pointer; position: relative; transition: background .15s ease, border-color .15s ease; }
	.sbg-giveaway-root input[type="checkbox"]:hover { border-color: var(--accent); }
	.sbg-giveaway-root input[type="checkbox"]:checked { background: var(--accent); border-color: var(--accent); }
	.sbg-giveaway-root input[type="checkbox"]:checked::after { content: ''; position: absolute; left: 6px; top: 2px; width: 5px; height: 10px; border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg); }

	/* Dropzone */
	.sbg-giveaway-root .sbg-drop { position: relative; border: 1.5px dashed #d3d3dc; border-radius: 12px; background: #fbfbfd; transition: border-color .16s ease, background .16s ease; }
	.sbg-giveaway-root .sbg-drop.is-drag { border-color: var(--accent); background: var(--accent-soft); }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] { border-style: solid; border-color: #c9ead8; background: #fff; }
	.sbg-giveaway-root .sbg-drop.is-error { border-color: #f2b8b8; background: #fef7f7; }
	.sbg-giveaway-root .sbg-drop-input { position: absolute; width: 1px; height: 1px; padding: 0; margin: 0; opacity: 0; overflow: hidden; clip: rect(0 0 0 0); border: 0; }
	.sbg-giveaway-root .sbg-drop-inner { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 7px; padding: 26px 18px; cursor: pointer; text-align: center; }
	.sbg-giveaway-root .sbg-drop-icon { width: 42px; height: 42px; border-radius: 11px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; }
	.sbg-giveaway-root .sbg-drop-title { font-size: 13.5px; font-weight: 700; color: var(--ink); }
	.sbg-giveaway-root .sbg-drop-browse { color: var(--accent); text-decoration: underline; text-underline-offset: 2px; }
	.sbg-giveaway-root .sbg-drop-hint { font-size: 12px; color: var(--muted); }
	.sbg-giveaway-root .sbg-drop-preview { display: none; align-items: center; gap: 14px; padding: 14px; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] .sbg-drop-inner { display: none; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] .sbg-drop-preview { display: flex; }
	.sbg-giveaway-root .sbg-drop-thumb { width: 58px; height: 58px; border-radius: 10px; object-fit: cover; border: 1px solid var(--line); background: #f1f1f5; flex-shrink: 0; }
	.sbg-giveaway-root .sbg-drop-meta { flex: 1 1 auto; min-width: 0; }
	.sbg-giveaway-root .sbg-drop-filename { font-size: 13.5px; font-weight: 700; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.sbg-giveaway-root .sbg-drop-sizes { font-size: 12px; color: var(--muted); margin-top: 3px; }
	.sbg-giveaway-root .sbg-drop-sizes .saved { color: var(--ok); font-weight: 800; }
	.sbg-giveaway-root .sbg-drop-remove { flex-shrink: 0; background: #fff; border: 1px solid var(--line); color: var(--err); font-size: 12px; font-weight: 700; padding: 7px 13px; border-radius: 9px; cursor: pointer; }
	.sbg-giveaway-root .sbg-drop-remove:hover { border-color: #f2b8b8; background: #fff5f5; }
	.sbg-giveaway-root .sbg-drop-busy { display: none; position: absolute; inset: 0; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.9); border-radius: 12px; font-size: 13px; font-weight: 700; color: var(--accent); }
	.sbg-giveaway-root .sbg-drop[data-state="busy"] .sbg-drop-busy { display: flex; }
	.sbg-giveaway-root .sbg-drop-spin { width: 15px; height: 15px; border: 2px solid #ddd6fe; border-top-color: var(--accent); border-radius: 50%; animation: sbgSpin .7s linear infinite; }
	@keyframes sbgSpin { to { transform: rotate(360deg); } }

	/* Details panel */
	.sbg-giveaway-root .sbg-details { position: sticky; top: 90px; background: var(--paper); border: 1px solid var(--line); border-radius: 18px; padding: 22px; box-shadow: 0 1px 2px rgba(16,24,40,0.04), 0 24px 48px -32px rgba(16,24,40,0.4); }
	@media (max-width: 900px) { .sbg-giveaway-root .sbg-details { position: static; } }
	.sbg-giveaway-root .sbg-details-head { padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px solid var(--line2); }
	.sbg-giveaway-root .sbg-fields { display: flex; flex-direction: column; gap: 14px; }
	.sbg-giveaway-root .sbg-field { display: flex; flex-direction: column; gap: 6px; }
	.sbg-giveaway-root .sbg-field > label { font-size: 12.5px; font-weight: 700; color: var(--ink); }
	.sbg-giveaway-root .sbg-field input, .sbg-giveaway-root .sbg-field textarea, .sbg-giveaway-root .sbg-field select { width: 100%; padding: 11px 13px; border: 1px solid var(--line); border-radius: 10px; font-size: 14px; color: var(--ink); background: var(--paper); outline: none; font-family: inherit; transition: border-color .16s ease, box-shadow .16s ease; }
	.sbg-giveaway-root .sbg-field input::placeholder, .sbg-giveaway-root .sbg-field textarea::placeholder { color: #a9a9b4; }
	.sbg-giveaway-root .sbg-field input:focus, .sbg-giveaway-root .sbg-field textarea:focus, .sbg-giveaway-root .sbg-field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(109,40,217,0.12); }
	.sbg-giveaway-root .sbg-field input.is-ok { border-color: #a7e0c2; }
	.sbg-giveaway-root .sbg-field input.is-error { border-color: #f0b0b0; box-shadow: 0 0 0 3px rgba(220,38,38,0.10); }
	.sbg-giveaway-root .sbg-field-msg { font-size: 11.5px; font-weight: 700; }
	.sbg-giveaway-root .sbg-field-msg.ok { color: var(--ok); }
	.sbg-giveaway-root .sbg-field-msg.bad { color: var(--err); }

	.sbg-giveaway-root .sbg-submit { margin-top: 18px; }
	.sbg-giveaway-root .sbg-btn { width: 100%; display: inline-flex; align-items: center; justify-content: center; padding: 14px 24px; border-radius: 11px; background: var(--ink); color: #fff; font-weight: 800; font-size: 14.5px; letter-spacing: 0.01em; border: none; cursor: pointer; transition: background .16s ease, transform .16s ease; }
	.sbg-giveaway-root .sbg-btn:hover { background: #26262e; transform: translateY(-1px); }
	.sbg-giveaway-root .sbg-btn:disabled { opacity: .7; cursor: not-allowed; transform: none; }
	.sbg-giveaway-root .sbg-form-error { display: block; text-align: center; margin-top: 10px; font-size: 12.5px; font-weight: 700; color: var(--err); }
	.sbg-giveaway-root .sbg-note { font-size: 11.5px; color: var(--muted) !important; text-align: center; margin-top: 10px; line-height: 1.5; }

	/* Other giveaways */
	.sbg-giveaway-root .sbg-others { margin-top: 72px; }
	.sbg-giveaway-root .sbg-others-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.13em; color: var(--muted); padding-bottom: 16px; border-bottom: 1px solid var(--line); }
	.sbg-giveaway-root .sbg-other { padding: 34px 0; border-bottom: 1px solid var(--line); }
	.sbg-giveaway-root .sbg-other-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
	.sbg-giveaway-root .sbg-other h2 { font-size: clamp(24px, 3.2vw, 32px); font-weight: 800; letter-spacing: -0.025em; }
	.sbg-giveaway-root .sbg-chip { flex-shrink: 0; font-size: 12px; font-weight: 700; color: var(--ink2); background: var(--paper); border: 1px solid var(--line); padding: 5px 12px; border-radius: 999px; white-space: nowrap; }
	.sbg-giveaway-root .sbg-other .sbg-entry-grid { grid-template-columns: 1fr 384px; margin-top: 26px; }
	.sbg-giveaway-root .sbg-other .sbg-details { position: static; }

	/* Winners */
	.sbg-giveaway-root .sbg-winners { margin-top: 72px; }
	.sbg-giveaway-root .sbg-winners h2 { font-size: clamp(26px, 3.4vw, 36px); font-weight: 800; letter-spacing: -0.025em; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-winners-sub { font-size: 14px; color: var(--ink2); margin-bottom: 26px; }
	.sbg-giveaway-root .sbg-win-group + .sbg-win-group { margin-top: 34px; }
	.sbg-giveaway-root .sbg-win-group-title { font-size: 13px; font-weight: 800; color: var(--ink); margin-bottom: 12px; }
	.sbg-giveaway-root .sbg-win-list { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: var(--paper); }
	.sbg-giveaway-root .sbg-win { display: flex; align-items: center; gap: 18px; padding: 16px 20px; }
	.sbg-giveaway-root .sbg-win + .sbg-win { border-top: 1px solid var(--line2); }
	.sbg-giveaway-root .sbg-win-rank { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; font-weight: 700; color: var(--muted); width: 24px; }
	.sbg-giveaway-root .sbg-win-info { flex: 1 1 auto; min-width: 0; }
	.sbg-giveaway-root .sbg-win-name { font-size: 14.5px; font-weight: 800; color: var(--ink); }
	.sbg-giveaway-root .sbg-win-ticket { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; color: var(--muted); margin-top: 2px; }
	.sbg-giveaway-root .sbg-win-badge { flex-shrink: 0; font-size: 11px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--ok); }

	.sbg-giveaway-root .sbg-empty { text-align: center; padding: 80px 24px; border: 1px dashed #d9d9e0; border-radius: 16px; margin-top: 24px; }
	.sbg-giveaway-root .sbg-empty h3 { font-size: 19px; font-weight: 800; margin-bottom: 6px; }

	/* Success pop-up */
	.sbg-giveaway-root .sbg-modal { position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 20px; }
	.sbg-giveaway-root .sbg-modal.is-open { display: flex; }
	.sbg-giveaway-root .sbg-modal-backdrop { position: absolute; inset: 0; background: rgba(14,14,19,0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
	.sbg-giveaway-root .sbg-modal-card { position: relative; width: 100%; max-width: 392px; background: var(--paper); border-radius: 18px; overflow: hidden; box-shadow: 0 40px 90px -30px rgba(14,14,19,0.55); animation: sbgModalPop .3s cubic-bezier(.16,1,.3,1) both; text-align: center; }
	@keyframes sbgModalPop { from { opacity: 0; transform: translateY(16px) scale(.97); } to { opacity: 1; transform: none; } }
	.sbg-giveaway-root .sbg-modal-bar { height: 4px; background: var(--accent); }
	.sbg-giveaway-root .sbg-modal-x { position: absolute; top: 14px; right: 14px; width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--ink2); font-size: 12px; font-weight: 700; cursor: pointer; }
	.sbg-giveaway-root .sbg-modal-x:hover { background: var(--line2); }
	.sbg-giveaway-root .sbg-modal-body { padding: 30px 26px 26px; }
	.sbg-giveaway-root .sbg-modal-icon { width: 52px; height: 52px; border-radius: 50%; background: #ecfdf5; color: var(--ok); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
	.sbg-giveaway-root .sbg-modal-title { font-size: 23px; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-modal-sub { font-size: 13.5px; line-height: 1.6; color: var(--ink2) !important; margin-bottom: 20px; }
	.sbg-giveaway-root .sbg-ticket { background: #fbfbfd; border: 1px solid var(--line); border-radius: 13px; padding: 16px; margin-bottom: 20px; }
	.sbg-giveaway-root .sbg-ticket-label { display: block; font-size: 10.5px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
	.sbg-giveaway-root .sbg-ticket-num { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 25px; font-weight: 800; color: var(--accent); letter-spacing: 0.01em; }
	.sbg-giveaway-root .sbg-ticket-name { font-size: 13px; font-weight: 700; color: var(--ink2); margin-top: 5px; }
	body.sbg-noscroll { overflow: hidden; }

	@media (prefers-reduced-motion: reduce) { .sbg-giveaway-root * { animation: none !important; } }
	@media (max-width: 720px) {
		.sbg-giveaway-root .sbg-other-head { flex-direction: column; }
		.sbg-giveaway-root .sbg-other .sbg-entry-grid { grid-template-columns: 1fr; }
	}
</style>

<div class="sbg-giveaway-root" data-error="<?php echo esc_attr( $gw_error ); ?>" data-error-gw="<?php echo esc_attr( $gw_error_gid ); ?>">
	<div class="sbg-shell">
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
			$meta  = aws_sbg_get_giveaway_meta( $featured->ID );
			$rule  = $gw_email_rule( $featured->ID );
			$count = aws_sbg_get_giveaway_live_count( $featured->ID );
		?>
			<div class="sbg-brief">
				<div class="sbg-eyebrow"><span class="sbg-status"><span class="sbg-dot"></span> Open now</span></div>
				<h1><?php echo esc_html( $featured->post_title ); ?></h1>
				<?php if ( ! empty( $meta['giveaway_description'] ) ) : ?>
					<p class="sbg-lead"><?php echo esc_html( $meta['giveaway_description'] ); ?></p>
				<?php endif; ?>
				<?php $render_meta( $meta, $count ); ?>
				<?php $render_prize( $meta ); ?>
			</div>
			<?php $render_form( $featured, $rule ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $others ) ) : ?>
			<div class="sbg-others">
				<div class="sbg-others-label">More giveaways</div>
				<?php foreach ( $others as $giveaway ) :
					$meta  = aws_sbg_get_giveaway_meta( $giveaway->ID );
					$rule  = $gw_email_rule( $giveaway->ID );
					$count = aws_sbg_get_giveaway_live_count( $giveaway->ID );
				?>
					<section class="sbg-other">
						<div class="sbg-other-head">
							<div>
								<h2><?php echo esc_html( $giveaway->post_title ); ?></h2>
								<?php $render_meta( $meta, $count ); ?>
							</div>
							<span class="sbg-chip"><?php echo esc_html( number_format( $count ) ); ?> entered</span>
						</div>
						<?php if ( ! empty( $meta['giveaway_description'] ) ) : ?>
							<p class="sbg-lead"><?php echo esc_html( $meta['giveaway_description'] ); ?></p>
						<?php endif; ?>
						<?php $render_prize( $meta ); ?>
						<?php $render_form( $giveaway, $rule ); ?>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $announced_giveaways ) ) : ?>
			<div class="sbg-winners">
				<h2>Winners</h2>
				<p class="sbg-winners-sub">Drawn at random from all valid entries.</p>
				<?php
				global $wpdb;
				foreach ( $announced_giveaways as $giveaway ) :
					$gid     = $giveaway->ID;
					$table   = $wpdb->prefix . 'aws_giveaway_entries';
					$winners = $wpdb->get_results( $wpdb->prepare(
						"SELECT ticket_number, full_name, email FROM $table WHERE giveaway_id = %d AND is_winner = 1 ORDER BY id ASC",
						$gid
					), ARRAY_A );
					if ( empty( $winners ) ) {
						continue;
					}
					?>
					<div class="sbg-win-group">
						<div class="sbg-win-group-title"><?php echo esc_html( $giveaway->post_title ); ?></div>
						<div class="sbg-win-list">
							<?php
							foreach ( $winners as $idx => $win ) :
								$parts  = explode( '@', $win['email'] );
								$user   = $parts[0] ?? '';
								$domain = $parts[1] ?? '';
								$masked = ( strlen( $user ) > 3 ) ? substr( $user, 0, 3 ) . '***@' . $domain : '***@' . $domain;
								?>
								<div class="sbg-win">
									<span class="sbg-win-rank"><?php echo esc_html( str_pad( $idx + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
									<div class="sbg-win-info">
										<div class="sbg-win-name"><?php echo esc_html( $win['full_name'] ); ?></div>
										<div class="sbg-win-ticket">#<?php echo esc_html( $win['ticket_number'] ); ?> · <?php echo esc_html( $masked ); ?></div>
									</div>
									<span class="sbg-win-badge">Winner</span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( empty( $open_giveaways ) && empty( $announced_giveaways ) ) : ?>
			<div class="sbg-empty">
				<h3>No active giveaways right now</h3>
				<p>Check back soon — new giveaways are announced regularly.</p>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $show_modal ) : ?>
		<div class="sbg-modal" id="sbg-success" role="dialog" aria-modal="true" aria-labelledby="sbg-modal-title">
			<div class="sbg-modal-backdrop" data-close></div>
			<div class="sbg-modal-card">
				<div class="sbg-modal-bar"></div>
				<button type="button" class="sbg-modal-x" data-close aria-label="Close">✕</button>
				<div class="sbg-modal-body">
					<span class="sbg-modal-icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
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

		function setMsg(text, cls) {
			if (!msg) { return; }
			msg.textContent = text;
			msg.className = 'sbg-field-msg' + (cls ? ' ' + cls : '');
		}
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
			if (form.checkValidity && !form.checkValidity()) { return; }
			if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }
		});
	});
})();
</script>

<?php
get_footer();
