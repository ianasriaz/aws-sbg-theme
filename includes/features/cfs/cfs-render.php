<?php
/**
 * AWS SBG Call for Speakers — Form Renderer
 * File: cfs-render.php
 *
 * Fast 90-second speaker pitch form with Alpine.js draft persistence,
 * live character counter, ticket styling, and celebratory success state.
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
				--ink: #0F141F;
				--paper: #FFFFFF;
				--surface: #F8FAFC;
				--line: #E2E8F0;
				--text: #334155;
				--muted: #64748B;
				--brand: #7928CA;
				--brand-soft: #F5F3FF;
				--accent: #FF9900;
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

			.cfs-page { max-width: 680px; margin: 0 auto; padding: 36px clamp(16px, 4vw, 36px) 64px; }
			.cfs-ticket { position: relative; background: var(--paper); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 1px 3px rgba(15,20,31,0.04), 0 16px 40px rgba(15,20,31,0.06); }

			.cfs-stub { padding: 30px 32px 24px; }
			.cfs-kicker { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--brand); margin-bottom: 12px; }
			.cfs-badge-fast { background: var(--brand-soft); color: var(--brand); border: 1px solid rgba(121,40,202,0.2); padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; }
			.cfs-title { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-weight: 700; font-size: clamp(28px, 4.5vw, 38px); line-height: 1.12; letter-spacing: -0.01em; color: var(--ink); margin-bottom: 10px; }
			.cfs-event-pill { display: inline-block; font-size: 13.5px; font-weight: 600; color: #475569; background: var(--surface); border: 1px solid var(--line); padding: 4px 12px; border-radius: 6px; margin-bottom: 12px; }
			.cfs-desc { font-size: 14.5px; line-height: 1.6; color: var(--text); }

			.cfs-perf { position: relative; height: 1px; border-top: 1px dashed #cbd5e1; }
			.cfs-notch { position: absolute; top: -11px; width: 22px; height: 22px; background: var(--surface); border: 1px solid var(--line); border-radius: 50%; }
			.cfs-notch-l { left: -12px; }
			.cfs-notch-r { right: -12px; }

			.cfs-body { padding: 26px 32px 32px; }
			.cfs-section { padding: 22px 0; border-top: 1px solid var(--line); }
			.cfs-section:first-child { border-top: 0; padding-top: 0; }
			.cfs-section-label { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 14px; }

			.cfs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
			.cfs-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
			.cfs-field label em { font-style: normal; color: var(--brand); }
			.cfs-field .cfs-help { display: block; font-size: 12px; color: var(--muted); margin-top: 5px; }
			.cfs-field input[type="text"],.cfs-field input[type="email"],.cfs-field input[type="tel"],.cfs-field input[type="url"],.cfs-field textarea,.cfs-field select {
				width: 100%;
				padding: 10px 13px;
				font-family: inherit;
				font-size: 15px;
				color: var(--ink);
				background: var(--paper);
				border: 1px solid var(--line);
				border-radius: 5px;
				transition: border-color .15s ease, box-shadow .15s ease;
			}
			.cfs-field input::placeholder,.cfs-field textarea::placeholder { color: #94a3b8; }
			.cfs-field input:focus,.cfs-field textarea:focus,.cfs-field select:focus {
				outline: none;
				border-color: var(--brand);
				box-shadow: 0 0 0 3px rgba(121,40,202,0.12);
			}
			.cfs-field textarea { min-height: 95px; resize: vertical; line-height: 1.5; }
			.cfs-span-2 { grid-column: 1 / -1; }

			.cfs-select-wrap { position: relative; }
			.cfs-select-wrap select { appearance: none; -webkit-appearance: none; padding-right: 36px; cursor: pointer; }
			.cfs-select-arrow { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b; }

			.cfs-pitch-counter { display: flex; justify-content: flex-end; font-size: 11.5px; color: var(--muted); margin-top: 4px; }
			.cfs-pitch-counter.is-limit { color: #dc2626; font-weight: 600; }

			.cfs-radio-group { display: flex; gap: 12px; }
			.cfs-radio-pill {
				flex: 1;
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 9px 12px;
				background: var(--paper);
				border: 1px solid var(--line);
				border-radius: 5px;
				cursor: pointer;
				font-size: 13.5px;
				font-weight: 500;
				color: var(--text);
				transition: border-color .15s ease, background-color .15s ease;
			}
			.cfs-radio-pill:hover { border-color: var(--brand); background: var(--brand-soft); }
			.cfs-radio-pill input[type="radio"] { accent-color: var(--brand); margin: 0; }

			.cfs-commit { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; }
			.cfs-commit input[type="checkbox"] { accent-color: var(--brand); margin-top: 3px; }
			.cfs-commit-text { font-size: 13.5px; color: var(--text); line-height: 1.5; }

			.cfs-submit { display: flex; align-items: center; justify-content: flex-end; padding-top: 8px; }
			.cfs-btn {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				border: 0;
				border-radius: 5px;
				padding: 12px 28px;
				font-family: inherit;
				font-size: 14.5px;
				font-weight: 700;
				cursor: pointer;
				text-decoration: none;
				transition: background .18s ease, transform .18s ease;
			}
			.cfs-btn-primary { background: var(--accent); color: var(--ink); }
			.cfs-btn-primary:hover { background: #ffad33; transform: translateY(-1px); }
			.cfs-btn-primary:disabled { opacity: 0.6; cursor: default; transform: none; }

			.cfs-notice { padding: 13px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
			.cfs-notice-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
			.cfs-notice-closed { background: #FFFBEB; border: 1px solid #FDE68A; border-left: 4px solid #F59E0B; color: #92400E; }
			.cfs-notice-closed strong { display: block; margin-bottom: 3px; color: #78350F; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; }

			.cfs-body-success { text-align: center; padding: 44px 32px; }
			.cfs-success-icon { width: 56px; height: 56px; border: 2px solid #16A34A; border-radius: 50%; color: #16A34A; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; font-weight: 900; }
			.cfs-success-title { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-weight: 700; font-size: 26px; margin-bottom: 8px; color: var(--ink); }
			.cfs-ref-box { display: inline-block; background: var(--surface); border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px 24px; margin: 18px 0; }
			.cfs-ref-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); display: block; margin-bottom: 4px; }
			.cfs-ref-val { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 18px; font-weight: 700; color: var(--brand); letter-spacing: 0.03em; }

			@media (max-width: 600px) {
				.cfs-grid { grid-template-columns: 1fr; }
				.cfs-stub,.cfs-body { padding-left: 20px; padding-right: 20px; }
				.cfs-submit .cfs-btn { width: 100%; }
			}
		</style>

		<div class="cfs-page">
			<?php if ( 'success' === $status ) : ?>
				<div class="cfs-ticket">
					<div class="cfs-stub" style="text-align:center;">
						<div class="cfs-kicker" style="justify-content:center;">
							<span>Call for Speakers</span>
							<span class="cfs-badge-fast">Proposal Logged</span>
						</div>
						<h1 class="cfs-title">Pitch Received</h1>
						<div class="cfs-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
					</div>
					<div class="cfs-perf" aria-hidden="true"><span class="cfs-notch cfs-notch-l"></span><span class="cfs-notch cfs-notch-r"></span></div>
					<div class="cfs-body cfs-body-success">
						<div class="cfs-success-icon">&#10003;</div>
						<h2 class="cfs-success-title">Thank You<?php echo $speak_name ? ', ' . esc_html( $speak_name ) : ''; ?>!</h2>
						<p class="cfs-desc" style="max-width:48ch;margin:0 auto 16px;">Your session pitch has been successfully received by the AWS Student Builder Group organizing team.</p>

						<?php if ( $ref ) : ?>
							<div class="cfs-ref-box">
								<span class="cfs-ref-label">Proposal Reference</span>
								<span class="cfs-ref-val"><?php echo esc_html( $ref ); ?></span>
							</div>
						<?php endif; ?>

						<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px 20px;max-width:520px;margin:16px auto;text-align:left;font-size:13.5px;color:#334155;line-height:1.6;">
							<strong style="display:block;margin-bottom:6px;color:#0f172a;font-size:13px;text-transform:uppercase;letter-spacing:0.04em;">What happens next?</strong>
							<p style="margin:0 0 6px 0;">1. Our selection committee reviews all talk pitches based on technical depth, relevance, and originality.</p>
							<p style="margin:0 0 6px 0;">2. Shortlisted speakers will be contacted via WhatsApp/Email with the official speaker confirmation form to collect final shield name, badge details, and slot preferences.</p>
							<p style="margin:0;">3. A confirmation email with your reference has been dispatched.</p>
						</div>

						<div style="margin-top:24px;">
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
				<div class="cfs-ticket">
					<div class="cfs-stub">
						<div class="cfs-kicker">
							<span>Call for Speakers</span>
							<span class="cfs-badge-fast">&sim; 90-Sec Pitch</span>
						</div>
						<h1 class="cfs-title">Share Your Story on the AWS Stage</h1>
						<div class="cfs-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
						<p class="cfs-desc">
							Have an interesting AWS build, cloud architecture, or real-world lesson to share? Pitch your talk idea below. If shortlisted, our team will reach out with speaker onboarding and logistics.
						</p>
					</div>

					<div class="cfs-perf" aria-hidden="true"><span class="cfs-notch cfs-notch-l"></span><span class="cfs-notch cfs-notch-r"></span></div>

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

								<!-- Part 1: About You -->
								<div class="cfs-section">
									<p class="cfs-section-label">1. About You</p>
									<div class="cfs-grid">
										<div class="cfs-field">
											<label for="cfs-name">Full Name <em>*</em></label>
											<input id="cfs-name" type="text" name="name" x-model="name" placeholder="e.g. Anas Riaz" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-email">Email Address <em>*</em></label>
											<input id="cfs-email" type="email" name="email" x-model="email" placeholder="you@example.com" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-phone">WhatsApp / Phone <em>*</em></label>
											<input id="cfs-phone" type="tel" name="phone" x-model="phone" placeholder="0300-0000000" required>
										</div>
										<div class="cfs-field">
											<label for="cfs-city">City <em>*</em></label>
											<input id="cfs-city" type="text" name="city" x-model="city" placeholder="e.g. Islamabad / Rawalpindi" required>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-org">Organization / Institute &amp; Role <em>*</em></label>
											<input id="cfs-org" type="text" name="organization" x-model="organization" placeholder="e.g. Cloud Engineer @ Tech Corp OR Student @ Air University" required>
										</div>
										<div class="cfs-field cfs-span-2">
											<label for="cfs-linkedin">LinkedIn Profile URL <em>*</em></label>
											<input id="cfs-linkedin" type="url" name="linkedin_url" x-model="linkedinUrl" placeholder="https://linkedin.com/in/username" required>
											<span class="cfs-help">Helps the review committee learn about your background without requiring a lengthy bio.</span>
										</div>
									</div>
								</div>

								<!-- Part 2: Your Session Pitch -->
								<div class="cfs-section">
									<p class="cfs-section-label">2. Your Session Pitch</p>
									<div class="cfs-grid">
										<div class="cfs-field cfs-span-2">
											<label for="cfs-title">Proposed Talk Title <em>*</em></label>
											<input id="cfs-title" type="text" name="talk_title" x-model="talkTitle" placeholder="e.g. Building Production-Ready RAG with Amazon Bedrock" required>
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
											<label for="cfs-pitch">Talk Pitch / Summary <em>*</em></label>
											<textarea id="cfs-pitch" name="talk_pitch" x-model="talkPitch" maxlength="400" placeholder="In 2–3 sentences, what core problem does your talk address, and what practical lessons or demo will attendees take away?" required></textarea>
											<div class="cfs-pitch-counter" :class="{ 'is-limit': talkPitch.length >= 380 }">
												<span x-text="talkPitch.length">0</span> / 400 characters
											</div>
										</div>
									</div>
								</div>

								<!-- Part 3: Experience & In-Person Availability -->
								<div class="cfs-section">
									<p class="cfs-section-label">3. Experience &amp; In-Person Availability</p>
									<div class="cfs-grid">
										<div class="cfs-field cfs-span-2">
											<label>Have you spoken at a community/tech event before?</label>
											<div class="cfs-radio-group">
												<label class="cfs-radio-pill">
													<input type="radio" name="past_experience" value="yes" x-model="pastExperience">
													<span>Yes</span>
												</label>
												<label class="cfs-radio-pill">
													<input type="radio" name="past_experience" value="no" x-model="pastExperience">
													<span>No (First-time speaker)</span>
												</label>
											</div>
										</div>

										<div class="cfs-field cfs-span-2" x-show="pastExperience === 'yes'" x-cloak>
											<label for="cfs-link">Link to past talk, slides, or recording <span style="font-weight:400;color:var(--muted);">(Optional)</span></label>
											<input id="cfs-link" type="url" name="experience_link" x-model="experienceLink" placeholder="https://youtube.com/... or https://slideshare.net/...">
										</div>

										<div class="cfs-field cfs-span-2" style="margin-top:6px;">
											<label class="cfs-commit">
												<input type="checkbox" name="commitment" value="yes" x-model="commitment" required>
												<span class="cfs-commit-text">I confirm that I am available to attend and deliver this session in-person in Islamabad on event day if selected.</span>
											</label>
										</div>
									</div>
								</div>

								<div class="cfs-submit">
									<button type="submit" class="cfs-btn cfs-btn-primary" :disabled="isSubmitting">
										<span x-show="!isSubmitting">Pitch My Session</span>
										<span x-show="isSubmitting" x-cloak>Submitting Pitch…</span>
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
