<?php
/**
 * AWS SBG Team Hiring & Volunteer System — Form Renderer
 * File: team-render.php
 *
 * Scoped application form (Event Crew, Core Team, or Both) with cross-campus
 * support, Alpine.js draft persistence, dynamic departments, and success confetti.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_team_render_form( $event_id = 0 ) {
	$event = aws_sbg_team_resolve_event( $event_id );
	if ( ! $event ) {
		return '<div class="tm-missing"><p>No event found for Team Hiring.</p></div>';
	}

	$event_id    = $event->ID;
	$meta        = aws_sbg_team_get_event_meta( $event_id );
	$scope       = $meta['team_scope']; // 'both', 'event_crew', or 'core_team'
	$roles       = aws_sbg_team_roles();
	$departments = aws_sbg_team_departments();

	$crew_open = ( 'open' === $meta['role_status_event_crew'] );
	$core_open = ( 'open' === $meta['role_status_core_team'] );

	$role_statuses = array(
		'event_crew' => $meta['role_status_event_crew'],
		'core_team'  => $meta['role_status_core_team'],
	);
	$role_messages = array(
		'event_crew' => $meta['role_msg_event_crew'],
		'core_team'  => $meta['role_msg_core_team'],
	);

	// Check if entire form is effectively closed
	$all_closed = false;
	if ( 'event_crew' === $scope && ! $crew_open ) {
		$all_closed = true;
	} elseif ( 'core_team' === $scope && ! $core_open ) {
		$all_closed = true;
	} elseif ( 'both' === $scope && ! $crew_open && ! $core_open ) {
		$all_closed = true;
	}

	// Default role
	$default_role = 'event_crew';
	if ( 'core_team' === $scope || ( 'both' === $scope && ! $crew_open && $core_open ) ) {
		$default_role = 'core_team';
	}

	$status    = isset( $_GET['join_status'] ) ? sanitize_key( wp_unslash( $_GET['join_status'] ) ) : '';
	$ref       = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
	$join_name = isset( $_GET['join_name'] ) ? sanitize_text_field( wp_unslash( $_GET['join_name'] ) ) : '';
	$err       = isset( $_GET['join_err'] ) ? sanitize_key( wp_unslash( $_GET['join_err'] ) ) : '';

	$error_messages = array(
		'closed'      => 'Team applications for this event are currently closed.',
		'role_closed' => 'Applications for the selected role are currently closed.',
		'required'    => 'Please fill in all required fields.',
		'email'       => 'Please enter a valid email address.',
		'commitment'  => 'Please confirm your availability and commitment.',
		'spam'        => 'Submission blocked.',
		'db'          => 'We could not save your application. Please try again.',
	);

	ob_start();
	?>
	<div class="tm-root" x-data="teamForm(<?php echo intval( $event_id ); ?>, '<?php echo esc_js( $scope ); ?>', '<?php echo esc_js( $default_role ); ?>', <?php echo esc_attr( wp_json_encode( $role_statuses ) ); ?>, <?php echo esc_attr( wp_json_encode( $role_messages ) ); ?>, <?php echo esc_attr( wp_json_encode( $departments ) ); ?>)">
		<style>
			.tm-root {
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
			.tm-root *,.tm-root *::before,.tm-root *::after { box-sizing: border-box; }
			.tm-root [x-cloak] { display: none !important; }
			.tm-root h1,.tm-root h2,.tm-root h3,.tm-root p { margin: 0; }
			.tm-root a { color: var(--brand); text-decoration: none; }
			.tm-root a:hover { color: #9353FF; }
			.tm-root :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; border-radius: 4px; }

			.tm-page { max-width: 680px; margin: 0 auto; padding: 36px clamp(16px, 4vw, 36px) 64px; }
			.tm-ticket { position: relative; background: var(--paper); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 1px 3px rgba(15,20,31,0.04), 0 16px 40px rgba(15,20,31,0.06); }

			.tm-stub { padding: 30px 32px 24px; }
			.tm-kicker { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--brand); margin-bottom: 12px; }
			.tm-badge { background: var(--brand-soft); color: var(--brand); border: 1px solid rgba(121,40,202,0.2); padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; }
			.tm-title { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-weight: 700; font-size: clamp(28px, 4.5vw, 38px); line-height: 1.12; letter-spacing: -0.01em; color: var(--ink); margin-bottom: 10px; }
			.tm-event-pill { display: inline-block; font-size: 13.5px; font-weight: 600; color: #475569; background: var(--surface); border: 1px solid var(--line); padding: 4px 12px; border-radius: 6px; margin-bottom: 12px; }
			.tm-desc { font-size: 14.5px; line-height: 1.6; color: var(--text); }

			.tm-perf { position: relative; height: 1px; border-top: 1px dashed #cbd5e1; }
			.tm-notch { position: absolute; top: -11px; width: 22px; height: 22px; background: var(--surface); border: 1px solid var(--line); border-radius: 50%; }
			.tm-notch-l { left: -12px; }
			.tm-notch-r { right: -12px; }

			.tm-body { padding: 26px 32px 32px; }
			.tm-section { padding: 22px 0; border-top: 1px solid var(--line); }
			.tm-section:first-child { border-top: 0; padding-top: 0; }
			.tm-section-label { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 14px; }

			.tm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
			.tm-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
			.tm-field label em { font-style: normal; color: var(--brand); }
			.tm-field .tm-help { display: block; font-size: 12px; color: var(--muted); margin-top: 5px; }
			.tm-field input[type="text"],.tm-field input[type="email"],.tm-field input[type="tel"],.tm-field input[type="url"],.tm-field textarea,.tm-field select {
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
			.tm-field input::placeholder,.tm-field textarea::placeholder { color: #94a3b8; }
			.tm-field input:focus,.tm-field textarea:focus,.tm-field select:focus {
				outline: none;
				border-color: var(--brand);
				box-shadow: 0 0 0 3px rgba(121,40,202,0.12);
			}
			.tm-field textarea { min-height: 90px; resize: vertical; line-height: 1.5; }
			.tm-span-2 { grid-column: 1 / -1; }

			.tm-role-pills { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
			.tm-role-card {
				border: 1.5px solid var(--line);
				border-radius: 6px;
				padding: 14px 16px;
				cursor: pointer;
				background: var(--paper);
				transition: all .15s ease;
				position: relative;
			}
			.tm-role-card:hover { border-color: var(--brand); background: var(--brand-soft); }
			.tm-role-card.is-active { border-color: var(--brand); background: var(--brand-soft); box-shadow: 0 0 0 1px var(--brand); }
			.tm-role-card.is-closed { opacity: 0.65; cursor: not-allowed; border-color: #cbd5e1; background: #f8fafc; }
			.tm-role-title { font-size: 14.5px; font-weight: 700; color: var(--ink); display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
			.tm-role-sub { font-size: 12px; color: var(--muted); line-height: 1.45; }

			.tm-select-wrap { position: relative; }
			.tm-select-wrap select { appearance: none; -webkit-appearance: none; padding-right: 36px; cursor: pointer; }
			.tm-select-arrow { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b; }

			.tm-pitch-counter { display: flex; justify-content: flex-end; font-size: 11.5px; color: var(--muted); margin-top: 4px; }
			.tm-pitch-counter.is-limit { color: #dc2626; font-weight: 600; }

			.tm-commit { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; }
			.tm-commit input[type="checkbox"] { accent-color: var(--brand); margin-top: 3px; }
			.tm-commit-text { font-size: 13.5px; color: var(--text); line-height: 1.5; }

			.tm-submit { display: flex; align-items: center; justify-content: flex-end; padding-top: 8px; }
			.tm-btn {
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
			.tm-btn-primary { background: var(--accent); color: var(--ink); }
			.tm-btn-primary:hover { background: #ffad33; transform: translateY(-1px); }
			.tm-btn-primary:disabled { opacity: 0.6; cursor: default; transform: none; }

			.tm-notice { padding: 13px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
			.tm-notice-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
			.tm-notice-closed { background: #FFFBEB; border: 1px solid #FDE68A; border-left: 4px solid #F59E0B; color: #92400E; margin-top: 14px; }
			.tm-notice-closed strong { display: block; margin-bottom: 3px; color: #78350F; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; }

			.tm-body-success { text-align: center; padding: 44px 32px; }
			.tm-success-icon { width: 56px; height: 56px; border: 2px solid #16A34A; border-radius: 50%; color: #16A34A; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; font-weight: 900; }
			.tm-success-title { font-family: 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif; font-weight: 700; font-size: 26px; margin-bottom: 8px; color: var(--ink); }
			.tm-ref-box { display: inline-block; background: var(--surface); border: 1px dashed #cbd5e1; border-radius: 6px; padding: 12px 24px; margin: 18px 0; }
			.tm-ref-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); display: block; margin-bottom: 4px; }
			.tm-ref-val { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 18px; font-weight: 700; color: var(--brand); letter-spacing: 0.03em; }

			@media (max-width: 600px) {
				.tm-grid,.tm-role-pills { grid-template-columns: 1fr; }
				.tm-stub,.tm-body { padding-left: 20px; padding-right: 20px; }
				.tm-submit .tm-btn { width: 100%; }
			}
		</style>

		<div class="tm-page">
			<?php if ( 'success' === $status ) : ?>
				<div class="tm-ticket">
					<div class="tm-stub" style="text-align:center;">
						<div class="tm-kicker" style="justify-content:center;">
							<span>Team Hiring</span>
							<span class="tm-badge">Application Logged</span>
						</div>
						<h1 class="tm-title">Application Received</h1>
						<div class="tm-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
					</div>
					<div class="tm-perf" aria-hidden="true"><span class="tm-notch tm-notch-l"></span><span class="tm-notch tm-notch-r"></span></div>
					<div class="tm-body tm-body-success">
						<div class="tm-success-icon">&#10003;</div>
						<h2 class="tm-success-title">Thank You<?php echo $join_name ? ', ' . esc_html( $join_name ) : ''; ?>!</h2>
						<p class="tm-desc" style="max-width:48ch;margin:0 auto 16px;">Your application has been received by the AWS Student Builder Group leadership team.</p>

						<?php if ( $ref ) : ?>
							<div class="tm-ref-box">
								<span class="tm-ref-label">Application Reference</span>
								<span class="tm-ref-val"><?php echo esc_html( $ref ); ?></span>
							</div>
						<?php endif; ?>

						<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px 20px;max-width:520px;margin:16px auto;text-align:left;font-size:13.5px;color:#334155;line-height:1.6;">
							<strong style="display:block;margin-bottom:6px;color:#0f172a;font-size:13px;text-transform:uppercase;letter-spacing:0.04em;">What happens next?</strong>
							<p style="margin:0 0 6px 0;">1. <strong>Application Review:</strong> Department leads will review your responses and campus background.</p>
							<p style="margin:0 0 6px 0;">2. <strong>Screening &amp; Orientation:</strong> Shortlisted candidates will be contacted via WhatsApp/Email for an informal orientation chat.</p>
							<p style="margin:0;">3. <strong>Crew Onboarding:</strong> Selected members will be added to the official Event Crew / Core Team WhatsApp group with badge and schedule details.</p>
						</div>

						<div style="margin-top:24px;">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tm-btn tm-btn-primary" style="display:inline-block;">Back to Homepage</a>
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
				<div class="tm-ticket">
					<div class="tm-stub">
						<div class="tm-kicker">
							<span>Join the Team</span>
							<span class="tm-badge">Backstage Crew Access</span>
						</div>
						<h1 class="tm-title" x-text="getHeaderTitle()">Be the Force Behind the Event</h1>
						<div class="tm-event-pill"><?php echo esc_html( $event->post_title ); ?></div>
						<p class="tm-desc">
							Be part of the builder crew bringing together student builders and cloud enthusiasts across all universities. Gain hands-on leadership experience, official certificates, and exclusive team perks!
						</p>
					</div>

					<div class="tm-perf" aria-hidden="true"><span class="tm-notch tm-notch-l"></span><span class="tm-notch tm-notch-r"></span></div>

					<div class="tm-body">
						<?php if ( $all_closed ) : ?>
							<div class="tm-notice tm-notice-closed">
								<strong>Applications Closed</strong>
								<span>Team applications for this event are currently closed. Thank you to everyone who applied!</span>
							</div>
						<?php else : ?>

							<?php if ( 'error' === $status && isset( $error_messages[ $err ] ) ) : ?>
								<div class="tm-notice tm-notice-error"><?php echo esc_html( $error_messages[ $err ] ); ?></div>
							<?php endif; ?>

							<div class="tm-notice tm-notice-error" x-show="clientError" x-text="clientError" x-cloak></div>

							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" @submit="handleSubmit($event)" @input="saveDraft()" @change="saveDraft()">
								<input type="hidden" name="action" value="aws_sbg_team_submit">
								<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
								<?php wp_nonce_field( 'aws_sbg_team_submit', 'team_nonce' ); ?>
								<input type="text" name="website_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">

								<!-- Role Scope Selection (Only shown when scope === 'both') -->
								<?php if ( 'both' === $scope ) : ?>
									<div class="tm-section">
										<p class="tm-section-label">Choose Your Role</p>
										<div class="tm-role-pills">
											<div class="tm-role-card" :class="{ 'is-active': role === 'event_crew', 'is-closed': roleStatuses.event_crew === 'closed' }" @click="selectRole('event_crew')">
												<div class="tm-role-title">
													<span>Event Crew</span>
													<span style="font-size:11px;font-weight:600;color:var(--brand);" x-show="roleStatuses.event_crew !== 'closed'">Event Day</span>
													<span style="font-size:11px;font-weight:600;color:#dc2626;" x-show="roleStatuses.event_crew === 'closed'" x-cloak>Closed</span>
												</div>
												<div class="tm-role-sub">Registration, check-in, stage AV, attendee hospitality &amp; live media on event day.</div>
											</div>
											<div class="tm-role-card" :class="{ 'is-active': role === 'core_team', 'is-closed': roleStatuses.core_team === 'closed' }" @click="selectRole('core_team')">
												<div class="tm-role-title">
													<span>Core Team</span>
													<span style="font-size:11px;font-weight:600;color:var(--brand);" x-show="roleStatuses.core_team !== 'closed'">Lead-Up Planning</span>
													<span style="font-size:11px;font-weight:600;color:#dc2626;" x-show="roleStatuses.core_team === 'closed'" x-cloak>Closed</span>
												</div>
												<div class="tm-role-sub">Operations, technical hands-on leads, design, marketing &amp; community outreach.</div>
											</div>
										</div>
										<input type="hidden" name="role" :value="role">

										<!-- Closed Role Notice if selected role is closed -->
										<div class="tm-notice tm-notice-closed" x-show="isRoleClosed()" x-cloak>
											<strong>Applications Closed for this Role</strong>
											<span x-text="getRoleClosedMessage()"></span>
										</div>
									</div>
								<?php else : ?>
									<input type="hidden" name="role" value="<?php echo esc_attr( $default_role ); ?>">
								<?php endif; ?>

								<div x-show="!isRoleClosed()">
									<!-- Part 1: About You -->
									<div class="tm-section">
										<p class="tm-section-label">1. About You</p>
										<div class="tm-grid">
											<div class="tm-field">
												<label for="tm-name">Full Name <em>*</em></label>
												<input id="tm-name" type="text" name="name" x-model="name" placeholder="e.g. Anas Riaz" required>
											</div>
											<div class="tm-field">
												<label for="tm-email">Email Address <em>*</em></label>
												<input id="tm-email" type="email" name="email" x-model="email" placeholder="you@example.com" required>
											</div>
											<div class="tm-field">
												<label for="tm-phone">WhatsApp / Phone Number <em>*</em></label>
												<input id="tm-phone" type="tel" name="phone" x-model="phone" placeholder="0300-0000000" required>
												<span class="tm-help">Used for quick screening &amp; team onboarding group.</span>
											</div>
											<div class="tm-field">
												<label for="tm-city">City / Current Location <em>*</em></label>
												<input id="tm-city" type="text" name="city" x-model="city" placeholder="e.g. Islamabad / Rawalpindi" required>
											</div>
											<div class="tm-field tm-span-2">
												<label for="tm-univ">University / Institute &amp; Degree <em>*</em></label>
												<input id="tm-univ" type="text" name="university" x-model="university" placeholder="e.g. NUST (SEECS) - BSCS 6th Sem OR Air University - BSAI" required>
												<span class="tm-help">Students from all universities and campuses across Islamabad/Rawalpindi are warmly welcome!</span>
											</div>
											<div class="tm-field tm-span-2">
												<label for="tm-linkedin">LinkedIn Profile URL <em>*</em></label>
												<input id="tm-linkedin" type="url" name="linkedin_url" x-model="linkedinUrl" placeholder="https://linkedin.com/in/username" required>
												<span class="tm-help">Designers, media or tech leads may also include their portfolio/GitHub link here.</span>
											</div>
										</div>
									</div>

									<!-- Part 2: Department Preferences -->
									<div class="tm-section">
										<p class="tm-section-label">2. Department Preferences</p>
										<div class="tm-grid">
											<div class="tm-field tm-span-2">
												<label for="tm-dept-primary">Primary Department Preference <em>*</em></label>
												<div class="tm-select-wrap">
													<select id="tm-dept-primary" name="primary_department" x-model="primaryDepartment" required>
														<option value="" disabled selected>Select your preferred department…</option>
														<template x-for="(label, key) in currentDepartments" :key="key">
															<option :value="key" x-text="label"></option>
														</template>
													</select>
													<span class="tm-select-arrow" aria-hidden="true">
														<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
															<polyline points="6 9 12 15 18 9"></polyline>
														</svg>
													</span>
												</div>
											</div>
											<div class="tm-field tm-span-2">
												<label for="tm-dept-secondary">Secondary Department Preference <span style="font-weight:400;color:var(--muted);">(Optional)</span></label>
												<div class="tm-select-wrap">
													<select id="tm-dept-secondary" name="secondary_department" x-model="secondaryDepartment">
														<option value="">None / Open to any role where needed</option>
														<template x-for="(label, key) in currentDepartments" :key="key">
															<option :value="key" x-text="label"></option>
														</template>
													</select>
													<span class="tm-select-arrow" aria-hidden="true">
														<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
															<polyline points="6 9 12 15 18 9"></polyline>
														</svg>
													</span>
												</div>
											</div>
										</div>
									</div>

									<!-- Part 3: Skills, Experience & Commitment -->
									<div class="tm-section">
										<p class="tm-section-label">3. Skills, Experience &amp; Commitment</p>
										<div class="tm-grid">
											<div class="tm-field tm-span-2">
												<label>Have you organized or volunteered at community/tech events before?</label>
												<div style="display:flex;gap:12px;">
													<label class="tm-role-card" style="flex:1;padding:10px 14px;" :class="{ 'is-active': pastExperience === 'yes' }">
														<div style="display:flex;align-items:center;gap:8px;">
															<input type="radio" name="past_experience" value="yes" x-model="pastExperience">
															<span style="font-size:13.5px;font-weight:600;">Yes</span>
														</div>
													</label>
													<label class="tm-role-card" style="flex:1;padding:10px 14px;" :class="{ 'is-active': pastExperience === 'no' }">
														<div style="display:flex;align-items:center;gap:8px;">
															<input type="radio" name="past_experience" value="no" x-model="pastExperience">
															<span style="font-size:13.5px;font-weight:600;">No (Excited to contribute!)</span>
														</div>
													</label>
												</div>
											</div>

											<div class="tm-field tm-span-2" x-show="pastExperience === 'yes'" x-cloak>
												<label for="tm-exp-details">Where and in what capacity? <span style="font-weight:400;color:var(--muted);">(Brief 1-line note)</span></label>
												<input id="tm-exp-details" type="text" name="experience_details" x-model="experienceDetails" placeholder="e.g. Lead check-in desk at DevFest / Volunteer at University Tech Week">
											</div>

											<div class="tm-field tm-span-2">
												<label for="tm-pitch">Why do you want to join the team? <em>*</em></label>
												<textarea id="tm-pitch" name="pitch" x-model="pitch" maxlength="300" placeholder="In 2–3 sentences, what excites you about this event and what key skill or strength do you bring to the crew?" required></textarea>
												<div class="tm-pitch-counter" :class="{ 'is-limit': pitch.length >= 280 }">
													<span x-text="pitch.length">0</span> / 300 characters
												</div>
											</div>

											<div class="tm-field tm-span-2" style="margin-top:6px;">
												<label class="tm-commit">
													<input type="checkbox" name="commitment" value="yes" x-model="commitment" required>
													<span class="tm-commit-text" x-text="getCommitmentText()"></span>
												</label>
											</div>
										</div>
									</div>

									<div class="tm-submit">
										<button type="submit" class="tm-btn tm-btn-primary" :disabled="isSubmitting || isRoleClosed()">
											<span x-show="!isSubmitting">Submit Application</span>
											<span x-show="isSubmitting" x-cloak>Submitting Application…</span>
										</button>
									</div>
								</div>
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
		function teamForm(eventId, scope, defaultRole, roleStatuses, roleMessages, departments) {
			return {
				eventId: eventId,
				scope: scope,
				role: defaultRole,
				roleStatuses: roleStatuses || {},
				roleMessages: roleMessages || {},
				departments: departments || {},
				name: '',
				email: '',
				phone: '',
				city: '',
				university: '',
				linkedinUrl: '',
				primaryDepartment: '',
				secondaryDepartment: '',
				pastExperience: 'no',
				experienceDetails: '',
				pitch: '',
				commitment: false,
				isSubmitting: false,
				clientError: '',
				get currentDepartments() {
					return this.departments[this.role] || {};
				},
				selectRole: function(r) {
					this.role = r;
					this.primaryDepartment = '';
					this.secondaryDepartment = '';
				},
				isRoleClosed: function() {
					return !!(this.roleStatuses && this.roleStatuses[this.role] === 'closed');
				},
				getRoleClosedMessage: function() {
					if (this.roleMessages && this.roleMessages[this.role]) {
						return this.roleMessages[this.role];
					}
					return 'Applications for this role are currently closed.';
				},
				getHeaderTitle: function() {
					if (this.scope === 'event_crew' || this.role === 'event_crew') {
						return 'Join the Event Crew';
					}
					if (this.scope === 'core_team' || this.role === 'core_team') {
						return 'Join the Core Team';
					}
					return 'Be the Force Behind the Event';
				},
				getCommitmentText: function() {
					if (this.role === 'core_team') {
						return 'I confirm that I can dedicate 3–5 hours/week for planning syncs leading up to the event and will be present in Islamabad.';
					}
					return 'I confirm that I am available in-person in Islamabad on event day for the full duration plus pre-event briefing.';
				},
				init: function() {
					try {
						// Clean draft if success
						<?php if ( 'success' === $status ) : ?>
							if (typeof localStorage !== 'undefined') {
								localStorage.removeItem('aws_team_draft_' + this.eventId);
							}
							return;
						<?php endif; ?>

						// Restore draft
						if (typeof localStorage !== 'undefined') {
							var raw = localStorage.getItem('aws_team_draft_' + this.eventId);
							if (raw) {
								var data = JSON.parse(raw);
								if (data.role && (this.scope === 'both')) this.role = data.role;
								if (data.name) this.name = data.name;
								if (data.email) this.email = data.email;
								if (data.phone) this.phone = data.phone;
								if (data.city) this.city = data.city;
								if (data.university) this.university = data.university;
								if (data.linkedinUrl) this.linkedinUrl = data.linkedinUrl;
								if (data.primaryDepartment) this.primaryDepartment = data.primaryDepartment;
								if (data.secondaryDepartment) this.secondaryDepartment = data.secondaryDepartment;
								if (data.pastExperience) this.pastExperience = data.pastExperience;
								if (data.experienceDetails) this.experienceDetails = data.experienceDetails;
								if (data.pitch) this.pitch = data.pitch;
							}
						}
					} catch(e) {}
				},
				saveDraft: function() {
					try {
						if (typeof localStorage === 'undefined') return;
						var draft = {
							role: this.role,
							name: this.name,
							email: this.email,
							phone: this.phone,
							city: this.city,
							university: this.university,
							linkedinUrl: this.linkedinUrl,
							primaryDepartment: this.primaryDepartment,
							secondaryDepartment: this.secondaryDepartment,
							pastExperience: this.pastExperience,
							experienceDetails: this.experienceDetails,
							pitch: this.pitch
						};
						localStorage.setItem('aws_team_draft_' + this.eventId, JSON.stringify(draft));
					} catch(e) {}
				},
				handleSubmit: function(e) {
					this.clientError = '';
					if (this.isRoleClosed()) {
						e.preventDefault();
						this.clientError = this.getRoleClosedMessage();
						return false;
					}
					if (!this.commitment) {
						e.preventDefault();
						this.clientError = 'Please confirm your availability and commitment.';
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

function aws_sbg_team_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'event' => '',
	), $atts, 'aws_team_form' );

	$event = aws_sbg_team_resolve_event( $atts['event'] );
	if ( ! $event ) {
		return '<p>No event found for Team Hiring.</p>';
	}

	return aws_sbg_team_render_form( $event->ID );
}
add_shortcode( 'aws_team_form', 'aws_sbg_team_shortcode' );
