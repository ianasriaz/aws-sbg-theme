<?php
/**
 * AWS SBG Call for Speakers — Form Renderer
 * File: cfs-render.php
 *
 * Clean, modern speaker proposal form with Alpine.js draft persistence,
 * live character counter, elegant responsive selector cards, and celebratory success state.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_cfs_render_form( $event_id = 0 ) {
	$event = aws_sbg_cfs_resolve_event( $event_id );
	if ( ! $event ) {
		return '<div class="cfs-missing"><p>No event found for Call for Speakers.</p></div>';
	}

	$event_id = $event->ID;
	$meta     = aws_sbg_cfs_get_event_meta( $event_id );
	$is_open  = aws_sbg_cfs_is_open( $event_id );
	$tracks   = aws_sbg_cfs_tracks();
	$formats  = aws_sbg_cfs_formats();
	$levels   = aws_sbg_cfs_levels();

	$status     = isset( $_GET['speak_status'] ) ? sanitize_key( wp_unslash( $_GET['speak_status'] ) ) : '';
	$ref        = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
	$speak_name = isset( $_GET['speak_name'] ) ? sanitize_text_field( wp_unslash( $_GET['speak_name'] ) ) : '';
	$err        = isset( $_GET['speak_err'] ) ? sanitize_key( wp_unslash( $_GET['speak_err'] ) ) : '';

	$error_messages = array(
		'closed'     => ! empty( $meta['cfs_closed_msg'] ) ? $meta['cfs_closed_msg'] : 'Call for speakers for this event is currently closed.',
		'required'   => 'Please fill in all required fields.',
		'email'      => 'Please enter a valid email address.',
		'commitment' => 'Please confirm your in-person availability on the event day.',
		'spam'       => 'Submission blocked.',
		'db'         => 'We could not save your proposal. Please try again.',
	);

	ob_start();
	?>
	<div class="cfs-root" x-data="cfsForm(<?php echo intval( $event_id ); ?>)">
		<style>
			.cfs-root {
				--ink: #0F172A;
				--paper: #FFFFFF;
				--surface: #F8FAFC;
				--line: #E2E8F0;
				--line-focus: #CBD5E1;
				--text: #334155;
				--muted: #64748B;
				--brand: #7928CA;
				--brand-soft: #F5F3FF;
				--brand-border: #DDD6FE;
				--accent: #FF9900;
				--accent-hover: #FFAC33;
				font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
				color: var(--ink);
				background: transparent;
				line-height: 1.6;
				font-size: 15px;
			}
			.cfs-root *,.cfs-root *::before,.cfs-root *::after { box-sizing: border-box; }
			.cfs-root [x-cloak] { display: none !important; }
			.cfs-root h1,.cfs-root h2,.cfs-root h3,.cfs-root p { margin: 0; }
			.cfs-root a { color: var(--brand); text-decoration: none; }
			.cfs-root a:hover { color: #9353FF; }
			.cfs-root :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; border-radius: 4px; }

			.cfs-page {
				max-width: 740px;
				margin: 0 auto;
				padding: 48px clamp(16px, 4vw, 36px) 80px;
			}
			.cfs-card {
				position: relative;
				background: var(--paper);
				border: 1px solid var(--line);
				border-radius: 12px;
				box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.04), 0 20px 25px -5px rgba(15, 23, 42, 0.05);
				overflow: hidden;
			}

			.cfs-header {
				padding: 40px 44px 28px;
				border-bottom: 1px solid var(--line);
				background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFD 100%);
			}
			.cfs-kicker {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				font-size: 11.5px;
				font-weight: 700;
				letter-spacing: 0.08em;
				text-transform: uppercase;
				color: var(--brand);
				margin-bottom: 10px;
			}
			.cfs-title {
				font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
				font-weight: 700;
				font-size: clamp(26px, 4.2vw, 34px);
				line-height: 1.2;
				letter-spacing: -0.015em;
				color: var(--ink);
				margin-bottom: 12px;
			}
			.cfs-event-pill {
				display: inline-flex;
				align-items: center;
				font-size: 13px;
				font-weight: 600;
				color: #475569;
				background: var(--surface);
				border: 1px solid var(--line);
				padding: 4px 12px;
				border-radius: 999px;
				margin-bottom: 14px;
			}
			.cfs-desc {
				font-size: 15px;
				line-height: 1.6;
				color: var(--text);
				max-width: 62ch;
			}

			.cfs-body {
				padding: 32px 44px 44px;
			}
			.cfs-section {
				padding: 28px 0;
				border-top: 1px solid #F1F5F9;
			}
			.cfs-section:first-child {
				border-top: 0;
				padding-top: 0;
			}
			.cfs-section-header {
				margin-bottom: 20px;
			}
			.cfs-section-title {
				font-size: 16.5px;
				font-weight: 700;
				color: var(--ink);
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.cfs-section-num {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 24px;
				height: 24px;
				border-radius: 50%;
				background: var(--brand-soft);
				color: var(--brand);
				font-size: 12px;
				font-weight: 700;
			}
			.cfs-section-subtitle {
				font-size: 13px;
				color: var(--muted);
				margin-top: 3px;
				margin-left: 34px;
			}

			.cfs-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 20px 20px;
			}
			.cfs-field > label {
				display: block;
				font-size: 13px;
				font-weight: 600;
				color: #1E293B;
				margin-bottom: 7px;
			}
			.cfs-field label em {
				font-style: normal;
				color: #DC2626;
				margin-left: 2px;
			}
			.cfs-field .cfs-help {
				display: block;
				font-size: 12px;
				color: var(--muted);
				margin-top: 6px;
				line-height: 1.45;
			}
			.cfs-field input[type="text"],
			.cfs-field input[type="email"],
			.cfs-field input[type="tel"],
			.cfs-field input[type="url"],
			.cfs-field textarea,
			.cfs-field select {
				width: 100%;
				padding: 11px 14px;
				font-family: inherit;
				font-size: 14.5px;
				color: var(--ink);
				background: var(--paper);
				border: 1px solid var(--line-focus);
				border-radius: 7px;
				transition: border-color .15s ease, box-shadow .15s ease;
				box-sizing: border-box;
			}
			.cfs-field input::placeholder,
			.cfs-field textarea::placeholder {
				color: #94A3B8;
			}
			.cfs-field input:focus,
			.cfs-field textarea:focus,
			.cfs-field select:focus {
				outline: none;
				border-color: var(--brand);
				box-shadow: 0 0 0 3px rgba(121, 40, 202, 0.12);
			}
			.cfs-field textarea {
				min-height: 105px;
				resize: vertical;
				line-height: 1.55;
			}
			.cfs-span-2 {
				grid-column: 1 / -1;
			}

			.cfs-select-wrap {
				position: relative;
			}
			.cfs-select-wrap select {
				appearance: none;
				-webkit-appearance: none;
				padding-right: 38px;
				cursor: pointer;
				background-color: var(--paper);
			}
			.cfs-select-arrow {
				position: absolute;
				right: 14px;
				top: 50%;
				transform: translateY(-50%);
				pointer-events: none;
				color: #64748B;
				display: flex;
				align-items: center;
			}

			.cfs-pitch-counter {
				display: flex;
				justify-content: flex-end;
				font-size: 12px;
				color: var(--muted);
				margin-top: 5px;
			}
			.cfs-pitch-counter.is-limit {
				color: #DC2626;
				font-weight: 600;
			}

			/* Experience Selector Cards */
			.cfs-exp-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 14px;
			}
			.cfs-exp-card {
				position: relative;
				display: flex;
				align-items: flex-start;
				gap: 12px;
				padding: 14px 16px;
				background: var(--paper);
				border: 1.5px solid var(--line);
				border-radius: 8px;
				cursor: pointer;
				transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
				user-select: none;
			}
			.cfs-exp-card:hover {
				border-color: var(--line-focus);
				background: var(--surface);
			}
			.cfs-exp-card.is-active {
				border-color: var(--brand);
				background: var(--brand-soft);
				box-shadow: 0 0 0 1px var(--brand);
			}
			.cfs-sr-only {
				position: absolute;
				width: 1px;
				height: 1px;
				padding: 0;
				margin: -1px;
				overflow: hidden;
				clip: rect(0, 0, 0, 0);
				white-space: nowrap;
				border-width: 0;
			}
			.cfs-exp-indicator {
				width: 18px;
				height: 18px;
				border-radius: 50%;
				border: 2px solid #CBD5E1;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;
				margin-top: 2px;
				background: #FFFFFF;
				transition: border-color .15s ease, background-color .15s ease;
			}
			.cfs-exp-card.is-active .cfs-exp-indicator {
				border-color: var(--brand);
				background: var(--brand);
			}
			.cfs-exp-dot {
				width: 6px;
				height: 6px;
				border-radius: 50%;
				background: #FFFFFF;
				display: block;
			}
			.cfs-exp-text {
				flex: 1;
			}
			.cfs-exp-title {
				display: block;
				font-size: 14px;
				font-weight: 600;
				color: var(--ink);
				line-height: 1.35;
				margin-bottom: 2px;
			}
			.cfs-exp-desc {
				display: block;
				font-size: 12px;
				color: var(--muted);
				line-height: 1.4;
			}

			/* In-Person Availability */
			.cfs-commit-box {
				background: #F8FAFC;
				border: 1px solid var(--line);
				border-radius: 8px;
				padding: 14px 16px;
				transition: border-color .15s ease;
			}
			.cfs-commit-box:hover {
				border-color: #CBD5E1;
			}
			.cfs-commit {
				display: flex !important;
				align-items: flex-start !important;
				gap: 12px !important;
				cursor: pointer;
				margin: 0 !important;
				padding: 0 !important;
			}
			.cfs-commit input[type="checkbox"] {
				width: 18px !important;
				height: 18px !important;
				margin: 2px 0 0 0 !important;
				accent-color: var(--brand);
				flex-shrink: 0 !important;
				cursor: pointer;
			}
			.cfs-commit-text {
				font-size: 13.5px;
				line-height: 1.55;
				color: #334155;
				flex: 1;
				text-align: left;
			}

			/* Buttons & Actions */
			.cfs-submit {
				display: flex;
				align-items: center;
				justify-content: flex-end;
				padding-top: 12px;
			}
			.cfs-btn {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				border: 0;
				border-radius: 7px;
				padding: 13px 32px;
				font-family: inherit;
				font-size: 15px;
				font-weight: 700;
				cursor: pointer;
				text-decoration: none;
				transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
			}
			.cfs-btn-primary {
				background: var(--accent);
				color: #0F172A;
				box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
			}
			.cfs-btn-primary:hover {
				background: var(--accent-hover);
				transform: translateY(-1px);
				box-shadow: 0 4px 12px rgba(255, 153, 0, 0.25);
			}
			.cfs-btn-primary:disabled {
				opacity: 0.6;
				cursor: not-allowed;
				transform: none;
				box-shadow: none;
			}

			/* Notices */
			.cfs-notice {
				padding: 14px 18px;
				border-radius: 8px;
				font-size: 14px;
				margin-bottom: 22px;
				line-height: 1.5;
			}
			.cfs-notice-error {
				background: #FEF2F2;
				border: 1px solid #FECACA;
				color: #991B1B;
			}
			.cfs-notice-closed {
				background: #FFFBEB;
				border: 1px solid #FDE68A;
				border-left: 4px solid #F59E0B;
				color: #92400E;
			}
			.cfs-notice-closed strong {
				display: block;
				margin-bottom: 3px;
				color: #78350F;
				font-size: 12.5px;
				text-transform: uppercase;
				letter-spacing: 0.04em;
			}

			/* Success State */
			.cfs-body-success {
				text-align: center;
				padding: 48px 36px 52px;
			}
			.cfs-success-icon {
				width: 58px;
				height: 58px;
				border: 2px solid #16A34A;
				border-radius: 50%;
				color: #16A34A;
				display: flex;
				align-items: center;
				justify-content: center;
				margin: 0 auto 18px;
				font-size: 26px;
				font-weight: 900;
				background: #F0FDF4;
			}
			.cfs-success-title {
				font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
				font-weight: 700;
				font-size: 28px;
				margin-bottom: 8px;
				color: var(--ink);
			}
			.cfs-ref-box {
				display: inline-block;
				background: var(--surface);
				border: 1px dashed #CBD5E1;
				border-radius: 8px;
				padding: 12px 24px;
				margin: 20px 0;
			}
			.cfs-ref-label {
				font-size: 11px;
				font-weight: 700;
				letter-spacing: 0.08em;
				text-transform: uppercase;
				color: var(--muted);
				display: block;
				margin-bottom: 4px;
			}
			.cfs-ref-val {
				font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
				font-size: 18px;
				font-weight: 700;
				color: var(--brand);
				letter-spacing: 0.03em;
			}

			@media (max-width: 640px) {
				.cfs-page {
					padding: 24px 16px 48px;
				}
				.cfs-header {
					padding: 28px 20px 20px;
				}
				.cfs-body {
					padding: 24px 20px 32px;
				}
				.cfs-grid {
					grid-template-columns: 1fr;
					gap: 16px;
				}
				.cfs-exp-grid {
					grid-template-columns: 1fr;
					gap: 10px;
				}
				.cfs-submit .cfs-btn {
					width: 100%;
				}
			}
		</style>

		<div class="cfs-page">
			<?php if ( 'success' === $status ) : ?>
				<div class="cfs-card">
					<div class="cfs-header" style="text-align:center;">
						<div class="cfs-kicker" style="justify-content:center;">
							<span>Call for Speakers</span>
						</div>
						<h1 class="cfs-title">Proposal Received</h1>
						<div class="cfs-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
					</div>
					<div class="cfs-body cfs-body-success">
						<div class="cfs-success-icon">&#10003;</div>
						<h2 class="cfs-success-title">Thank You<?php echo $speak_name ? ', ' . esc_html( $speak_name ) : ''; ?>!</h2>
						<p class="cfs-desc" style="max-width:48ch;margin:0 auto 16px;">We have received your talk pitch and our team will get back to you soon regarding the selection and next steps.</p>

						<?php if ( $ref ) : ?>
							<div class="cfs-ref-box">
								<span class="cfs-ref-label">Proposal Reference</span>
								<span class="cfs-ref-val"><?php echo esc_html( $ref ); ?></span>
							</div>
						<?php endif; ?>

						<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:18px 22px;max-width:520px;margin:20px auto;text-align:left;font-size:13.5px;color:#334155;line-height:1.6;">
							<strong style="display:block;margin-bottom:8px;color:#0F172A;font-size:12.5px;text-transform:uppercase;letter-spacing:0.05em;">What happens next?</strong>
							<p style="margin:0 0 6px 0;">1. Our selection committee evaluates all session proposals based on technical depth, practical value, and originality.</p>
							<p style="margin:0 0 6px 0;">2. We will contact shortlisted speakers via email/WhatsApp to confirm scheduling, speaker shield names, and logistics.</p>
							<p style="margin:0;">3. A confirmation email with your proposal reference has been dispatched to your inbox.</p>
						</div>

						<div style="margin-top:28px;">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cfs-btn cfs-btn-primary" style="display:inline-block;">Back to Homepage</a>
						</div>
					</div>
				</div>

				<script>
					document.addEventListener('DOMContentLoaded', function() {
						if (typeof confetti === 'function') {
							confetti({ particleCount: 80, spread: 70, origin: { y: 0.6 } });
						}
					});
				</script>
			<?php else : ?>
				<div class="cfs-card">
					<div class="cfs-header">
						<div class="cfs-kicker">
							<span>Call for Speakers</span>
						</div>
						<h1 class="cfs-title">Share Your Knowledge on the AWS Stage</h1>
						<div class="cfs-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
						<p class="cfs-desc">
							Have a project, cloud architecture, or real-world lesson to share? Submit your session proposal below. Our team reviews all submissions and will get back to you soon.
						</p>
					</div>

					<div class="cfs-body">
						<?php if ( ! $is_open ) : ?>
							<div class="cfs-notice cfs-notice-closed">
								<strong>Call for Speakers Closed</strong>
								<span><?php echo esc_html( $meta['cfs_closed_msg'] ); ?></span>
							</div>
						<?php else : ?>

							<?php if ( 'error' === $status && isset( $error_messages[ $err ] ) ) : ?>
								<div class="cfs-notice cfs-notice-error"><?php echo esc_html( $error_messages[ $err ] ); ?></div>
							<?php endif; ?>

							<div class="cfs-notice cfs-notice-error" x-show="clientError" x-text="clientError" x-cloak></div>

							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" @submit="handleSubmit($event)" @input="saveDraft()" @change="saveDraft()">
								<input type="hidden" name="action" value="aws_sbg_cfs_submit">
								<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
								<?php wp_nonce_field( 'aws_sbg_cfs_submit', 'cfs_nonce' ); ?>
								<input type="text" name="website_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">

								<!-- Section 1: Speaker Profile -->
								<div class="cfs-section">
									<div class="cfs-section-header">
										<p class="cfs-section-title"><span class="cfs-section-num">1</span> Speaker Profile</p>
										<p class="cfs-section-subtitle">Tell us about yourself and how to reach you.</p>
									</div>
									<div class="cfs-grid">
										<div class="cfs-field">
											<label for="cfs-name">Full Name <em>*</em></label>
											<input id="cfs-name" type="text" name="name" x-model="name" placeholder="e.g. Alex Morgan" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-email">Email Address <em>*</em></label>
											<input id="cfs-email" type="email" name="email" x-model="email" placeholder="you@example.com" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-phone">WhatsApp / Phone <em>*</em></label>
											<input id="cfs-phone" type="tel" name="phone" x-model="phone" placeholder="0300-1234567" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-city">City <em>*</em></label>
											<input id="cfs-city" type="text" name="city" x-model="city" placeholder="e.g. Islamabad / Rawalpindi" required>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-org">Organization / University &amp; Role <em>*</em></label>
											<input id="cfs-org" type="text" name="organization" x-model="organization" placeholder="e.g. Cloud Engineer @ Tech Corp or Student @ Air University" required>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-linkedin">LinkedIn Profile URL <em>*</em></label>
											<input id="cfs-linkedin" type="url" name="linkedin_url" x-model="linkedinUrl" placeholder="https://linkedin.com/in/username" required>
											<span class="cfs-help">Helps our selection committee review your professional or technical background.</span>
										</div>
									</div>
								</div>

								<!-- Section 2: Session Details -->
								<div class="cfs-section">
									<div class="cfs-section-header">
										<p class="cfs-section-title"><span class="cfs-section-num">2</span> Session Details</p>
										<p class="cfs-section-subtitle">What would you like to present to the community?</p>
									</div>
									<div class="cfs-grid">
										<div class="cfs-field cfs-span-2">
											<label for="cfs-title">Proposed Talk Title <em>*</em></label>
											<input id="cfs-title" type="text" name="talk_title" x-model="talkTitle" placeholder="e.g. Building Production AI Workflows with Amazon Bedrock" required>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-track">Primary Topic Track <em>*</em></label>
											<div class="cfs-select-wrap">
												<select id="cfs-track" name="track" x-model="track" required>
													<option value="" disabled selected>Select a primary track…</option>
													<?php foreach ( $tracks as $track_key => $track_label ) : ?>
														<option value="<?php echo esc_attr( $track_key ); ?>"><?php echo esc_html( $track_label ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="cfs-select-arrow" aria-hidden="true">
													<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
														<polyline points="6 9 12 15 18 9"></polyline>
													</svg>
												</span>
											</div>
										</div>
										<div class="cfs-field">
											<label for="cfs-format">Preferred Format <em>*</em></label>
											<div class="cfs-select-wrap">
												<select id="cfs-format" name="format" x-model="format" required>
													<?php foreach ( $formats as $format_key => $format_label ) : ?>
														<option value="<?php echo esc_attr( $format_key ); ?>"><?php echo esc_html( $format_label ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="cfs-select-arrow" aria-hidden="true">
													<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
														<polyline points="6 9 12 15 18 9"></polyline>
													</svg>
												</span>
											</div>
										</div>
										<div class="cfs-field">
											<label for="cfs-level">Audience Level <em>*</em></label>
											<div class="cfs-select-wrap">
												<select id="cfs-level" name="audience_level" x-model="audienceLevel" required>
													<?php foreach ( $levels as $level_key => $level_label ) : ?>
														<option value="<?php echo esc_attr( $level_key ); ?>"><?php echo esc_html( $level_label ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="cfs-select-arrow" aria-hidden="true">
													<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
														<polyline points="6 9 12 15 18 9"></polyline>
													</svg>
												</span>
											</div>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-pitch">Session Summary (2–3 sentences) <em>*</em></label>
											<textarea id="cfs-pitch" name="talk_pitch" x-model="talkPitch" maxlength="400" placeholder="What core problem or concept does your talk address, and what practical lessons or demo will attendees take away?" required></textarea>
											<div class="cfs-pitch-counter" :class="{ 'is-limit': talkPitch.length >= 380 }">
												<span x-text="talkPitch.length">0</span> / 400 characters
											</div>
										</div>
									</div>
								</div>

								<!-- Section 3: Experience & Availability -->
								<div class="cfs-section">
									<div class="cfs-section-header">
										<p class="cfs-section-title"><span class="cfs-section-num">3</span> Experience &amp; In-Person Availability</p>
										<p class="cfs-section-subtitle">We warmly welcome both experienced and first-time speakers.</p>
									</div>
									<div class="cfs-grid">
										<div class="cfs-field cfs-span-2">
											<label style="margin-bottom:10px;">Have you spoken at a community/tech event before?</label>
											<div class="cfs-exp-grid">
												<label class="cfs-exp-card" :class="{ 'is-active': pastExperience === 'yes' }">
													<input type="radio" name="past_experience" value="yes" x-model="pastExperience" class="cfs-sr-only">
													<span class="cfs-exp-indicator" aria-hidden="true">
														<span class="cfs-exp-dot" x-show="pastExperience === 'yes'"></span>
													</span>
													<span class="cfs-exp-text">
														<span class="cfs-exp-title">Yes, I've spoken before</span>
														<span class="cfs-exp-desc">Meetups, conferences, workshops, or tech talks</span>
													</span>
												</label>
												<label class="cfs-exp-card" :class="{ 'is-active': pastExperience === 'no' }">
													<input type="radio" name="past_experience" value="no" x-model="pastExperience" class="cfs-sr-only">
													<span class="cfs-exp-indicator" aria-hidden="true">
														<span class="cfs-exp-dot" x-show="pastExperience === 'no'"></span>
													</span>
													<span class="cfs-exp-text">
														<span class="cfs-exp-title">No, this is my first talk</span>
														<span class="cfs-exp-desc">First-time speakers are warmly welcome &amp; supported</span>
													</span>
												</label>
											</div>
										</div>

										<div class="cfs-field cfs-span-2" x-show="pastExperience === 'yes'" x-cloak>
											<label for="cfs-link">Link to a past talk, slides, or recording <span style="font-weight:400;color:var(--muted);">(Optional)</span></label>
											<input id="cfs-link" type="url" name="experience_link" x-model="experienceLink" placeholder="e.g. YouTube video, SlideShare, or GitHub repo">
										</div>

										<div class="cfs-span-2" style="margin-top:6px;">
											<div class="cfs-commit-box">
												<label class="cfs-commit">
													<input type="checkbox" name="commitment" value="yes" x-model="commitment" required>
													<span class="cfs-commit-text">I confirm that I am available to deliver this session in-person in Islamabad on event day if selected.</span>
												</label>
											</div>
										</div>
									</div>
								</div>

								<div class="cfs-submit">
									<button type="submit" class="cfs-btn cfs-btn-primary" :disabled="isSubmitting">
										<span x-show="!isSubmitting">Submit Session Proposal</span>
										<span x-show="isSubmitting" x-cloak>Submitting Proposal…</span>
									</button>
								</div>
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
		function cfsForm(eventId) {
			return {
				eventId: eventId,
				name: '',
				email: '',
				phone: '',
				city: '',
				organization: '',
				linkedinUrl: '',
				talkTitle: '',
				track: '',
				format: 'lightning',
				audienceLevel: '100',
				talkPitch: '',
				pastExperience: 'no',
				experienceLink: '',
				commitment: false,
				isSubmitting: false,
				clientError: '',
				init: function() {
					try {
						// Clean draft on success
						<?php if ( 'success' === $status ) : ?>
							if (typeof localStorage !== 'undefined') {
								localStorage.removeItem('aws_cfs_draft_' + this.eventId);
							}
							return;
						<?php endif; ?>

						// Restore draft
						if (typeof localStorage !== 'undefined') {
							var raw = localStorage.getItem('aws_cfs_draft_' + this.eventId);
							if (raw) {
								var data = JSON.parse(raw);
								if (data.name) this.name = data.name;
								if (data.email) this.email = data.email;
								if (data.phone) this.phone = data.phone;
								if (data.city) this.city = data.city;
								if (data.organization) this.organization = data.organization;
								if (data.linkedinUrl) this.linkedinUrl = data.linkedinUrl;
								if (data.talkTitle) this.talkTitle = data.talkTitle;
								if (data.track) this.track = data.track;
								if (data.format) this.format = data.format;
								if (data.audienceLevel) this.audienceLevel = data.audienceLevel;
								if (data.talkPitch) this.talkPitch = data.talkPitch;
								if (data.pastExperience) this.pastExperience = data.pastExperience;
								if (data.experienceLink) this.experienceLink = data.experienceLink;
							}
						}
					} catch(e) {}
				},
				saveDraft: function() {
					try {
						if (typeof localStorage === 'undefined') return;
						var draft = {
							name: this.name,
							email: this.email,
							phone: this.phone,
							city: this.city,
							organization: this.organization,
							linkedinUrl: this.linkedinUrl,
							talkTitle: this.talkTitle,
							track: this.track,
							format: this.format,
							audienceLevel: this.audienceLevel,
							talkPitch: this.talkPitch,
							pastExperience: this.pastExperience,
							experienceLink: this.experienceLink
						};
						localStorage.setItem('aws_cfs_draft_' + this.eventId, JSON.stringify(draft));
					} catch(e) {}
				},
				handleSubmit: function(e) {
					this.clientError = '';
					if (!this.commitment) {
						e.preventDefault();
						this.clientError = 'Please confirm your in-person availability on the event day.';
						return false;
					}
					this.saveDraft();
					this.isSubmitting = true;
				}
			};
		}
	</script>
	<?php
	return ob_get_clean();
}

function aws_sbg_cfs_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'event' => '',
	), $atts, 'aws_cfs_form' );

	$event = aws_sbg_cfs_resolve_event( $atts['event'] );
	if ( ! $event ) {
		return '<p>No event found for Call for Speakers.</p>';
	}

	return aws_sbg_cfs_render_form( $event->ID );
}
add_shortcode( 'aws_cfs_form', 'aws_sbg_cfs_shortcode' );
