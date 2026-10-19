<?php
/**
 * Template Name: Campus Giveaway Page
 * File: page-giveaway.php
 *
 * Public multi-giveaway landing page. Lists active giveaways, each with its own
 * requirements (tick / link-or-text proof / image upload) and detail fields.
 * Announce giveaways show their published winners.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$open_giveaways      = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'open' ) ) : array();
$announced_giveaways = function_exists( 'aws_sbg_get_giveaways' ) ? aws_sbg_get_giveaways( array( 'announced' ) ) : array();
$source_tag          = isset( $_GET['src'] ) ? sanitize_text_field( $_GET['src'] ) : 'web';

// Flash notices
$gw_status = isset( $_GET['gw_status'] ) ? sanitize_key( $_GET['gw_status'] ) : '';
$gw_ticket = isset( $_GET['gw_ticket'] ) ? sanitize_text_field( $_GET['gw_ticket'] ) : '';
$gw_error  = isset( $_GET['gw_error'] ) ? sanitize_key( $_GET['gw_error'] ) : '';

$error_messages = array(
	'closed'   => 'This giveaway is no longer accepting entries.',
	'spam'     => 'Your submission could not be processed.',
	'required' => 'Please fill in all required fields.',
	'email'    => 'Please enter a valid email address.',
	'domain'   => 'That email domain is not eligible for this giveaway.',
	'db'       => 'We could not save your entry. Please try again.',
	'filesize' => 'An uploaded image exceeds the 2 MB limit.',
	'filetype' => 'Uploads must be JPG, PNG or WebP images.',
	'upload'   => 'There was a problem saving your upload. Please try again.',
	'reqfile'  => 'Please upload the required image.',
	'reqproof' => 'Please complete the required proof field.',
	'reqcheck' => 'Please confirm all required items.',
);

?>

<style>
	.sbg-giveaway-root {
		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		background-color: #ffffff !important;
		color: #0f172a !important;
		width: 100%;
		overflow-x: hidden;
	}
	.sbg-giveaway-root h1,
	.sbg-giveaway-root h2,
	.sbg-giveaway-root h3,
	.sbg-giveaway-root h4 { color: #020617 !important; margin: 0; }
	.sbg-giveaway-root p { color: #475569 !important; margin: 0; }
	.sbg-giveaway-root a { text-decoration: none !important; }
	.sbg-giveaway-root * { box-sizing: border-box; }

	.sbg-giveaway-root .aurora-canvas { position: relative; overflow: hidden; background: #ffffff; }
	.sbg-giveaway-root .aurora-blob { position: absolute; border-radius: 9999px; filter: blur(80px); pointer-events: none; will-change: transform; z-index: 0; }
	.sbg-giveaway-root .aurora-purple { width: 520px; height: 520px; top: -150px; right: -110px; background: radial-gradient(circle, rgba(147,83,255,0.25), transparent 65%); animation: sbgAuroraFloat 12s ease-in-out infinite; }
	.sbg-giveaway-root .aurora-orange { width: 460px; height: 460px; top: 12%; left: -140px; background: radial-gradient(circle, rgba(255,153,0,0.18), transparent 65%); animation: sbgAuroraFloat 14s ease-in-out infinite reverse; }
	.sbg-giveaway-root .aurora-green { width: 420px; height: 420px; bottom: 4%; right: 12%; background: radial-gradient(circle, rgba(0,239,136,0.16), transparent 65%); animation: sbgAuroraFloat 10s ease-in-out infinite 2s; }
	@keyframes sbgAuroraFloat { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(26px,-20px) scale(1.07); } }
	.sbg-giveaway-root .sbg-grid-lines {
		position: absolute; inset: 0; z-index: 1; pointer-events: none;
		background-size: 38px 38px;
		background-image:
			linear-gradient(to right, rgba(147,83,255,0.06) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(147,83,255,0.06) 1px, transparent 1px);
		mask-image: radial-gradient(ellipse 85% 75% at 50% 35%, #000 40%, transparent 92%);
		-webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 35%, #000 40%, transparent 92%);
	}

	.sbg-giveaway-root .sbg-gw-inner { max-width: 1080px; margin: 0 auto; padding: 52px 20px 80px; position: relative; z-index: 10; }
	.sbg-giveaway-root .sbg-gw-head { text-align: center; margin-bottom: 40px; }
	.sbg-giveaway-root .sbg-gw-head h1 { font-size: clamp(34px, 5vw, 54px); font-weight: 900; letter-spacing: -0.03em; line-height: 1.05; }

	.sbg-giveaway-root .sbg-notice { max-width: 720px; margin: 0 auto 28px; padding: 14px 20px; border-radius: 14px; font-size: 14px; font-weight: 700; }
	.sbg-giveaway-root .sbg-notice.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; }
	.sbg-giveaway-root .sbg-notice.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
	.sbg-giveaway-root .sbg-notice.error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

	.sbg-giveaway-root .sbg-gw-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 30px; margin-bottom: 28px; box-shadow: 0 14px 40px -18px rgba(15,23,42,0.14); }
	.sbg-giveaway-root .sbg-gw-card-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
	.sbg-giveaway-root .sbg-gw-card h2 { font-size: clamp(22px, 3vw, 30px); font-weight: 900; letter-spacing: -0.02em; }
	.sbg-giveaway-root .sbg-gw-count { display: inline-flex; align-items: center; gap: 6px; background: #f5f3ff; border: 1px solid #ede9fe; color: #7928ca; font-size: 12px; font-weight: 800; padding: 5px 12px; border-radius: 999px; white-space: nowrap; }
	.sbg-giveaway-root .sbg-gw-desc { font-size: 15px; line-height: 1.7; margin-bottom: 8px; }
	.sbg-giveaway-root .sbg-gw-prize { display: flex; align-items: flex-start; gap: 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px 18px; margin: 16px 0 4px; max-width: 600px; box-shadow: 0 1px 2px rgba(15,23,42,0.03); }
	.sbg-giveaway-root .sbg-gw-prize-mark { flex-shrink: 0; width: 40px; height: 40px; border-radius: 12px; background: #f5f3ff; border: 1px solid #ede9fe; color: #7928ca; display: flex; align-items: center; justify-content: center; }
	.sbg-giveaway-root .sbg-gw-prize-label { display: block; font-size: 10.5px; font-weight: 900; letter-spacing: 0.12em; text-transform: uppercase; color: #94a3b8; margin-bottom: 3px; }
	.sbg-giveaway-root .sbg-gw-prize-title { font-size: 16.5px; font-weight: 800; color: #020617 !important; line-height: 1.35; }
	.sbg-giveaway-root .sbg-gw-prize-desc { font-size: 13px; color: #64748b !important; margin-top: 3px; line-height: 1.5; }

	.sbg-giveaway-root .sbg-sec-title { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; margin: 22px 0 12px; }

	.sbg-giveaway-root .sbg-req { display: flex; align-items: flex-start; gap: 11px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 13px 15px; margin-bottom: 10px; transition: all .18s ease; }
	.sbg-giveaway-root .sbg-req:hover { border-color: #cbd5e1; background: #ffffff; }
	.sbg-giveaway-root .sbg-req input[type=checkbox] { width: 18px; height: 18px; min-width: 18px; margin-top: 2px; accent-color: #10b981; cursor: pointer; }
	.sbg-giveaway-root .sbg-req-label { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.45; }
	.sbg-giveaway-root .sbg-req-help { display: block; font-size: 12px; font-weight: 500; color: #64748b; margin-top: 3px; }
	.sbg-giveaway-root .sbg-req-block { margin-bottom: 14px; }
	.sbg-giveaway-root .sbg-req-block .sbg-req-label { display: block; margin-bottom: 8px; }

	.sbg-giveaway-root .sbg-drop { position: relative; border: 1.5px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; transition: border-color .18s ease, background .18s ease; }
	.sbg-giveaway-root .sbg-drop.is-drag { border-color: #9353ff; background: #faf5ff; }
	.sbg-giveaway-root .sbg-drop[data-state="ready"] { border-style: solid; border-color: #a7f3d0; background: #ffffff; }
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
	.sbg-giveaway-root .sbg-drop-remove { flex-shrink: 0; background: #ffffff; border: 1px solid #e2e8f0; color: #dc2626; font-size: 12px; font-weight: 700; padding: 7px 13px; border-radius: 9px; cursor: pointer; transition: all .15s ease; }
	.sbg-giveaway-root .sbg-drop-remove:hover { background: #fef2f2; border-color: #fecaca; }
	.sbg-giveaway-root .sbg-drop-busy { display: none; position: absolute; inset: 0; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.86); border-radius: 14px; font-size: 13px; font-weight: 800; color: #7928ca; }
	.sbg-giveaway-root .sbg-drop[data-state="busy"] .sbg-drop-busy { display: flex; }
	.sbg-giveaway-root .sbg-drop-spin { width: 16px; height: 16px; border: 2px solid #e9d5ff; border-top-color: #7928ca; border-radius: 50%; animation: sbgSpin .7s linear infinite; }
	@keyframes sbgSpin { to { transform: rotate(360deg); } }

	.sbg-giveaway-root .sbg-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
	.sbg-giveaway-root .sbg-field { display: flex; flex-direction: column; gap: 5px; }
	.sbg-giveaway-root .sbg-field.full { grid-column: 1 / -1; }
	.sbg-giveaway-root .sbg-field label { font-size: 12px; font-weight: 800; color: #334155; }
	.sbg-giveaway-root .sbg-field label .req { color: #ef4444; }
	.sbg-giveaway-root .sbg-field input,
	.sbg-giveaway-root .sbg-field textarea,
	.sbg-giveaway-root .sbg-field select {
		width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px;
		font-size: 14px; color: #0f172a; background: #ffffff; outline: none; font-family: inherit;
		transition: border-color .18s ease, box-shadow .18s ease;
	}
	.sbg-giveaway-root .sbg-field input:focus,
	.sbg-giveaway-root .sbg-field textarea:focus,
	.sbg-giveaway-root .sbg-field select:focus { border-color: #9353ff; box-shadow: 0 0 0 3px rgba(147,83,255,0.14); }
	@media (max-width: 640px) { .sbg-giveaway-root .sbg-fields { grid-template-columns: 1fr; } }

	.sbg-giveaway-root .btn-launch {
		display: inline-flex; align-items: center; justify-content: center; gap: 10px;
		padding: 15px 30px; border-radius: 14px;
		background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%) !important;
		color: #ffffff !important; font-weight: 800; font-size: 15px; letter-spacing: 0.02em;
		box-shadow: 0 12px 28px -6px rgba(147,83,255,0.45);
		transition: transform .2s ease, box-shadow .2s ease;
		border: none; cursor: pointer; text-decoration: none !important;
	}
	.sbg-giveaway-root .btn-launch:hover { transform: translateY(-2px); box-shadow: 0 16px 36px -6px rgba(147,83,255,0.55); }
	.sbg-giveaway-root .btn-launch:disabled { opacity: .75; cursor: not-allowed; transform: none; }

	.sbg-giveaway-root .sbg-winner-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
	.sbg-giveaway-root .sbg-winner-card { background: #ffffff; border: 1.5px solid #a7f3d0; border-radius: 16px; padding: 20px; box-shadow: 0 10px 26px -14px rgba(16,185,129,0.3); }
	.sbg-giveaway-root .sbg-winner-card .ticket { font-family: monospace; font-weight: 900; color: #7928ca; font-size: 18px; margin-bottom: 6px; }
	.sbg-giveaway-root .sbg-winner-card .name { font-weight: 800; color: #0f172a; font-size: 15px; }
	.sbg-giveaway-root .sbg-winner-card .email { font-size: 12.5px; color: #64748b; margin-top: 2px; }

	.sbg-giveaway-root .sbg-empty { text-align: center; padding: 70px 24px; border: 2px dashed #e2e8f0; border-radius: 22px; }
</style>

<div class="sbg-giveaway-root">
	<div class="aurora-canvas">
		<div class="aurora-blob aurora-purple"></div>
		<div class="aurora-blob aurora-orange"></div>
		<div class="aurora-blob aurora-green"></div>
		<div class="sbg-grid-lines"></div>

		<div class="sbg-gw-inner">
			<div class="sbg-gw-head">
				<h1>Giveaways &amp; Rewards</h1>
			</div>

			<?php if ( 'success' === $gw_status ) : ?>
				<div class="sbg-notice success">You're in — your entry is confirmed with ticket <strong><?php echo esc_html( $gw_ticket ); ?></strong>. Keep it safe.</div>
			<?php elseif ( 'exists' === $gw_status ) : ?>
				<div class="sbg-notice info">You have already entered this giveaway — ticket <strong><?php echo esc_html( $gw_ticket ); ?></strong>.</div>
			<?php elseif ( 'error' === $gw_status ) : ?>
				<div class="sbg-notice error"><?php echo esc_html( $error_messages[ $gw_error ] ?? 'Something went wrong. Please try again.' ); ?></div>
			<?php endif; ?>

			<?php
			if ( ! empty( $open_giveaways ) ) :
				foreach ( $open_giveaways as $giveaway ) :
					$gid    = $giveaway->ID;
					$meta   = aws_sbg_get_giveaway_meta( $gid );
					$reqs   = aws_sbg_get_giveaway_requirements( $gid );
					$fields = aws_sbg_get_giveaway_fields( $gid );
					$count  = aws_sbg_get_giveaway_live_count( $gid );
					?>
					<section class="sbg-gw-card">
						<div class="sbg-gw-card-head">
							<h2><?php echo esc_html( $giveaway->post_title ); ?></h2>
							<span class="sbg-gw-count"><?php echo esc_html( number_format( $count ) ); ?> entered</span>
						</div>

						<?php if ( ! empty( $meta['giveaway_description'] ) ) : ?>
							<p class="sbg-gw-desc"><?php echo esc_html( $meta['giveaway_description'] ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $meta['prize_title'] ) ) : ?>
							<div class="sbg-gw-prize">
								<span class="sbg-gw-prize-mark" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
								</span>
								<div>
									<span class="sbg-gw-prize-label">Prize</span>
									<div class="sbg-gw-prize-title"><?php echo esc_html( $meta['prize_title'] ); ?></div>
									<?php if ( ! empty( $meta['prize_description'] ) ) : ?>
										<div class="sbg-gw-prize-desc"><?php echo esc_html( $meta['prize_description'] ); ?></div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>

						<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="aws_sbg_giveaway_submit">
							<input type="hidden" name="giveaway_id" value="<?php echo esc_attr( $gid ); ?>">
							<input type="hidden" name="gw_source" value="<?php echo esc_attr( $source_tag ); ?>">
							<input type="text" name="website_url" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">
							<?php wp_nonce_field( 'aws_sbg_giveaway_submit', 'giveaway_nonce' ); ?>

							<?php if ( ! empty( $reqs ) ) : ?>
								<div class="sbg-sec-title">Requirements</div>
								<?php
								foreach ( $reqs as $req ) :
									$rid  = $req['id'];
									$name = 'gw_check[' . $rid . ']';
									$reqd = ! empty( $req['required'] ) ? 'required' : '';
									?>
									<?php if ( 'upload' === $req['type'] ) : ?>
										<div class="sbg-req-block">
											<label class="sbg-req-label" for="gw_file_<?php echo esc_attr( $rid ); ?>"><?php echo esc_html( $req['label'] ); ?><?php echo ! empty( $req['required'] ) ? ' <span style="color:#ef4444;">*</span>' : ''; ?>
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
										<div class="sbg-req" style="flex-direction:column;">
											<label style="display:flex;align-items:flex-start;gap:11px;cursor:pointer;">
												<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
												<span class="sbg-req-label"><?php echo esc_html( $req['label'] ); ?><?php echo ! empty( $req['required'] ) ? ' <span style="color:#ef4444;">*</span>' : ''; ?><?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?></span>
											</label>
											<input type="text" name="gw_text[<?php echo esc_attr( $rid ); ?>]" placeholder="Paste your link / proof here" style="margin-top:10px;width:100%;padding:10px 13px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:13.5px;" <?php echo ! empty( $req['required'] ) ? 'required' : ''; ?>>
										</div>
									<?php else : ?>
										<label class="sbg-req">
											<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo esc_attr( $reqd ); ?>>
											<span class="sbg-req-label"><?php echo esc_html( $req['label'] ); ?><?php echo ! empty( $req['required'] ) ? ' <span style="color:#ef4444;">*</span>' : ''; ?><?php if ( ! empty( $req['help'] ) ) : ?><span class="sbg-req-help"><?php echo esc_html( $req['help'] ); ?></span><?php endif; ?></span>
										</label>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>

							<div class="sbg-sec-title">Your Details</div>
							<div class="sbg-fields">
								<?php
								foreach ( $fields as $field ) :
									$fid  = $field['id'];
									$type = $field['type'];
									$wide = in_array( $type, array( 'textarea' ), true ) ? ' full' : '';
									$reqd = ! empty( $field['required'] ) ? 'required' : '';
									?>
									<div class="sbg-field<?php echo esc_attr( $wide ); ?>">
										<label for="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>"><?php echo esc_html( $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' <span class="req">*</span>' : ''; ?></label>
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
											<input type="<?php echo esc_attr( 'tel' === $type ? 'tel' : ( 'email' === $type ? 'email' : 'text' ) ); ?>" id="gw-<?php echo esc_attr( $gid . '-' . $fid ); ?>" name="gw_field[<?php echo esc_attr( $fid ); ?>]" <?php echo esc_attr( $reqd ); ?>>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>

							<div style="margin-top:22px;">
								<button type="submit" class="btn-launch" style="width:100%;">Enter Giveaway</button>
							</div>
						</form>
					</section>
					<?php
				endforeach;
			endif;

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
				<section class="sbg-gw-card">
					<div class="sbg-gw-card-head">
						<h2><?php echo esc_html( $giveaway->post_title ); ?></h2>
						<span class="sbg-gw-count" style="background:#ecfdf5;border-color:#a7f3d0;color:#059669;">Winners announced</span>
					</div>
					<?php if ( ! empty( $meta['prize_title'] ) ) : ?>
						<div class="sbg-gw-prize">
							<span class="sbg-gw-prize-mark" aria-hidden="true">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
							</span>
							<div>
								<span class="sbg-gw-prize-label">Prize</span>
								<div class="sbg-gw-prize-title"><?php echo esc_html( $meta['prize_title'] ); ?></div>
							</div>
						</div>
					<?php endif; ?>

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
									<div style="font-size:11px;font-weight:900;color:#059669;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Winner #<?php echo esc_html( $idx + 1 ); ?></div>
									<div class="ticket">#<?php echo esc_html( $win['ticket_number'] ); ?></div>
									<div class="name"><?php echo esc_html( $win['full_name'] ); ?></div>
									<div class="email"><?php echo esc_html( $masked ); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="sbg-gw-desc">Results will be published here shortly.</p>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>

			<?php if ( empty( $open_giveaways ) && empty( $announced_giveaways ) ) : ?>
				<div class="sbg-empty">
					<h3 style="font-size:20px;font-weight:900;margin-bottom:6px;">No active giveaways right now</h3>
					<p style="color:#64748b;">Check back soon — new giveaways are announced regularly.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
	document.querySelectorAll('.sbg-giveaway-root form').forEach(function (form) {
		form.addEventListener('submit', function () {
			var btn = form.querySelector('button[type="submit"]');
			if (btn) { btn.disabled = true; btn.innerHTML = 'Submitting…'; }
		});
	});

	/* Client-side image optimisation: resize + convert to WebP before upload,
	   so the server receives a small file and does no image processing. */
	(function () {
		var MAX_BYTES = 2 * 1024 * 1024;
		var MAX_DIM   = 1600;

		function fmt(bytes) {
			return bytes >= 1048576 ? (bytes / 1048576).toFixed(2) + ' MB' : Math.round(bytes / 1024) + ' KB';
		}
		function readDataURL(file) {
			return new Promise(function (res, rej) {
				var r = new FileReader();
				r.onload = function (e) { res(e.target.result); };
				r.onerror = rej;
				r.readAsDataURL(file);
			});
		}
		function loadImg(src) {
			return new Promise(function (res, rej) {
				var i = new Image();
				i.onload = function () { res(i); };
				i.onerror = rej;
				i.src = src;
			});
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
				if (blob && blob.size <= MAX_BYTES) break;
			}
			if (blob && blob.size > MAX_BYTES) {
				var smaller = drawResized(canvas, MAX_DIM * 0.7);
				blob = await toWebp(smaller, 0.72);
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

			function reset() {
				input.value = '';
				thumb.removeAttribute('src');
				drop.setAttribute('data-state', 'idle');
			}

			async function handle(file) {
				if (!file) { return; }
				if (!file.type || file.type.indexOf('image/') !== 0) {
					alert('Please choose an image file (JPG, PNG or WebP).');
					return;
				}
				drop.setAttribute('data-state', 'busy');
				var original = file.size;
				var out;
				try {
					out = await compress(file);
				} catch (e) {
					out = file;
				}
				if (out.size > MAX_BYTES) {
					alert('This image is still larger than 2 MB after optimisation. Please pick a smaller one.');
					reset();
					return;
				}

				var dt = new DataTransfer();
				dt.items.add(out);
				input.files = dt.files;

				thumb.src = URL.createObjectURL(out);
				fname.textContent = out.name;
				sizes.innerHTML = '<span style="text-decoration:line-through;opacity:.7;">' + fmt(original) + '</span> &rarr; <span class="saved">' + fmt(out.size) + '</span>' + (out.type === 'image/webp' ? ' · WebP' : '');
				drop.setAttribute('data-state', 'ready');
			}

			input.addEventListener('change', function () { if (input.files[0]) { handle(input.files[0]); } });

			['dragenter', 'dragover'].forEach(function (ev) {
				drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
			});
			['dragleave', 'dragend'].forEach(function (ev) {
				drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-drag'); });
			});
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
	})();
</script>

<?php
get_footer();
