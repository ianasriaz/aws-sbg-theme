<?php
/**
 * AWS SBG Invitation Confirmation — Renderer
 * File: invite-render.php
 *
 * Shortcode + single-page form renderer (Alpine conditional reveals) and
 * the success/error states. Scoped, self-contained CSS; no Tailwind/React.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_invite_render_form( $event_id ) {
	$event = get_post( $event_id );
	if ( ! $event || 'aws_invite_event' !== $event->post_type ) {
		return '<p class="inv-missing">No invitation event found.</p>';
	}

	$meta    = aws_sbg_invite_get_event_meta( $event_id );
	$roles   = aws_sbg_invite_roles();
	$fields  = aws_sbg_invite_role_fields();
	$options = array(
		'programs' => aws_sbg_invite_programs(),
		'slots'    => aws_sbg_invite_slot_options(),
	);

	$status = isset( $_GET['invite_status'] ) ? sanitize_key( wp_unslash( $_GET['invite_status'] ) ) : '';
	$ref    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
	$err    = isset( $_GET['invite_error'] ) ? sanitize_key( wp_unslash( $_GET['invite_error'] ) ) : '';

	$photo_required = ( 'yes' === $meta['photo_required'] );
	$allow_guests   = ( 'yes' === $meta['allow_guests'] );

	$error_messages = array(
		'closed'    => 'This invitation is no longer accepting confirmations.',
		'required'  => 'Please fill in all required fields.',
		'email'     => 'Please enter a valid email address.',
		'consent'   => 'Please confirm your invitation and consent to the name/photo usage.',
		'vehicle'   => 'Please provide your vehicle type and number.',
		'photo'     => 'Please upload your photo.',
		'role'      => 'Please choose a valid role.',
		'filesize'  => 'Your photo is too large. Please upload a smaller image.',
		'filetype'  => 'Please upload a JPG, PNG, or WebP image.',
		'upload'    => 'We could not upload your photo. Please try again.',
		'spam'      => 'Submission blocked.',
		'db'        => 'We could not save your confirmation. Please try again.',
	);

	ob_start();
	?>
	<div class="inv-root" x-data="inviteForm(<?php echo $photo_required ? 'true' : 'false'; ?>)">
		<style>
			.inv-root{--ink:#0F141F;--paper:#FFFFFF;--surface:#F8FAFC;--line:#E2E8F0;--text:#334155;--muted:#475569;--brand:#7928CA;--accent:#FF9900;font-family:'Amazon Ember','Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:var(--ink);background:var(--paper);line-height:1.6;font-size:16px;}
			.inv-root *,.inv-root *::before,.inv-root *::after{box-sizing:border-box;}
			.inv-root [x-cloak]{display:none !important;}
			.inv-root h1,.inv-root h2,.inv-root h3,.inv-root p{margin:0;}
			.inv-root a{color:var(--brand);}
			.inv-root a:hover{color:#9353FF;}
			.inv-root :focus-visible{outline:2px solid var(--brand);outline-offset:2px;border-radius:4px;}
			.inv-wrap{max-width:640px;margin:0 auto;padding:48px clamp(20px,5vw,48px) 80px;}
			.inv-kicker{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:var(--brand);margin:0 0 14px;}
			.inv-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:clamp(28px,4.5vw,40px);line-height:1.1;letter-spacing:-0.01em;margin:0 0 14px;}
			.inv-desc{font-size:16px;line-height:1.7;color:var(--text);max-width:56ch;}
			.inv-desc p{margin:0 0 12px;}
			.inv-desc p:last-child{margin-bottom:0;}
			.inv-form{margin-top:32px;}
			.inv-section{border-top:1px solid var(--line);padding:26px 0;}
			.inv-section-label{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin:0 0 16px;}
			.inv-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
			.inv-field label{display:block;font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:var(--muted);margin-bottom:7px;}
			.inv-field label em{font-style:normal;color:var(--brand);}
			.inv-field .inv-help{display:block;font-size:12.5px;color:var(--muted);margin-top:6px;}
			.inv-field input[type="text"],.inv-field input[type="email"],.inv-field input[type="tel"],.inv-field textarea,.inv-field select{width:100%;padding:12px 14px;font-family:inherit;font-size:16px;color:var(--ink);background:var(--surface);border:1px solid #cbd5e1;border-radius:10px;transition:border-color .15s ease,box-shadow .15s ease;}
			.inv-field input::placeholder,.inv-field textarea::placeholder{color:#94a3b8;}
			.inv-field input:focus,.inv-field textarea:focus,.inv-field select:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(121,40,202,0.12);}
			.inv-field textarea{min-height:120px;resize:vertical;}
			.inv-span-2{grid-column:1/-1;}

			.inv-roles{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;align-items:stretch;}
			.inv-role{display:flex;align-items:center;gap:12px;min-height:60px;padding:14px 16px;background:var(--surface);border:1px solid #cbd5e1;border-radius:12px;cursor:pointer;transition:border-color .15s ease,background .15s ease;}
			.inv-role:hover{border-color:#94a3b8;}
			.inv-role:has(input:checked){border-color:var(--brand);background:#F5F3FF;}
			.inv-role input{width:18px;height:18px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;margin:0;}
			.inv-role-title{font-size:14px;font-weight:700;color:var(--ink);line-height:1.3;}

			.inv-choice-row{display:flex;gap:14px;align-items:center;min-height:52px;padding:13px 16px;background:var(--surface);border:1px solid #cbd5e1;border-radius:12px;cursor:pointer;}
			.inv-choice-row:has(input:checked){border-color:var(--brand);background:#F5F3FF;}
			.inv-choice-row input{width:18px;height:18px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;margin:0;}
			.inv-choice-title{font-size:14px;color:var(--ink);}

			.inv-commit{display:flex;align-items:flex-start;gap:14px;padding:18px 0;border-top:1px solid var(--line);cursor:pointer;}
			.inv-commit input{width:18px;height:18px;margin-top:3px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;}
			.inv-commit-text{font-size:14.5px;line-height:1.6;color:var(--text);}

			.inv-photo-zone{display:flex;align-items:center;gap:16px;padding:16px;background:var(--surface);border:1px dashed #94a3b8;border-radius:12px;}
			.inv-photo-zone input{flex:1;font-size:13px;color:var(--muted);}
			.inv-photo-preview{width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid var(--line);display:none;}
			.inv-photo-preview.is-visible{display:block;}

			.inv-submit{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:30px;}
			.inv-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:10px;padding:15px 34px;font-family:inherit;font-size:14px;font-weight:800;letter-spacing:0.02em;cursor:pointer;text-decoration:none;transition:background .18s ease,transform .18s ease;}
			.inv-btn-primary{background:var(--accent);color:#0F141F;}
			.inv-btn-primary:hover{background:#ffad33;transform:translateY(-1px);}
			.inv-btn-primary:disabled{opacity:0.6;cursor:default;transform:none;}

			.inv-notice{padding:14px 18px;border-radius:12px;font-size:14px;margin:0 0 24px;}
			.inv-notice-error{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}
			.inv-notice-success{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;}

			.inv-success-card{background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:40px 30px;text-align:center;}
			.inv-success-icon{width:60px;height:60px;border:2px solid #16A34A;border-radius:50%;color:#16A34A;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;font-weight:900;}
			.inv-ref{display:inline-block;border:1px dashed #94a3b8;border-radius:12px;padding:13px 22px;margin:20px 0 6px;}
			.inv-ref-label{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;}
			.inv-ref-value{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:18px;font-weight:700;color:var(--brand);letter-spacing:0.04em;}

			@media (max-width:600px){
				.inv-grid,.inv-roles{grid-template-columns:1fr;}
				.inv-submit{flex-direction:column-reverse;}
				.inv-submit .inv-btn{width:100%;}
			}
		</style>

		<div class="inv-wrap">
			<?php if ( 'success' === $status ) : ?>
				<div class="inv-success-card">
					<div class="inv-success-icon">&#10003;</div>
					<h2 style="font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:26px;margin:0 0 10px;">Invitation confirmed</h2>
					<p style="font-size:15px;line-height:1.65;color:var(--muted);"><?php echo esc_html( $meta['confirm_message'] ); ?></p>
					<?php if ( $ref ) : ?>
						<div class="inv-ref">
							<span class="inv-ref-label">Reference</span>
							<span class="inv-ref-value"><?php echo esc_html( $ref ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $meta['whatsapp_url'] ) ) : ?>
						<div style="margin-top:24px;">
							<a class="inv-btn inv-btn-primary" href="<?php echo esc_url( $meta['whatsapp_url'] ); ?>" target="_blank" rel="noopener">Join WhatsApp</a>
						</div>
					<?php endif; ?>
					<p style="margin:22px 0 0;"><a href="<?php echo esc_url( home_url() ); ?>" style="font-size:13px;font-weight:700;">Back to home</a></p>
				</div>
			<?php else : ?>
				<p class="inv-kicker">Invitation confirmation</p>
				<h1 class="inv-title"><?php echo esc_html( $event->post_title ); ?></h1>
				<?php if ( $event->post_content ) : ?>
					<div class="inv-desc"><?php echo wp_kses_post( wpautop( $event->post_content ) ); ?></div>
				<?php endif; ?>

				<?php if ( 'error' === $status && isset( $error_messages[ $err ] ) ) : ?>
					<div class="inv-notice inv-notice-error" style="margin-top:24px;"><?php echo esc_html( $error_messages[ $err ] ); ?></div>
				<?php endif; ?>

				<form class="inv-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
					<input type="hidden" name="action" value="aws_sbg_invite_submit">
					<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
					<?php wp_nonce_field( 'aws_sbg_invite_submit', 'invite_nonce' ); ?>
					<input type="text" name="website_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">

					<div class="inv-section">
						<p class="inv-section-label">I am joining as</p>
						<div class="inv-roles" role="radiogroup" aria-label="Role">
							<?php foreach ( $roles as $role_key => $role_label ) : ?>
								<?php if ( 'guest' === $role_key && ! $allow_guests ) { continue; } ?>
								<label class="inv-role">
									<input type="radio" name="role" value="<?php echo esc_attr( $role_key ); ?>" x-model="role" required>
									<span class="inv-role-title"><?php echo esc_html( $role_label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="inv-section">
						<p class="inv-section-label">Your details</p>
						<div class="inv-grid">
							<div class="inv-field">
								<label for="inv-name">Name <em>*</em></label>
								<input id="inv-name" type="text" name="name" placeholder="As printed on recognition shield" required>
							</div>
							<div class="inv-field">
								<label for="inv-email">Email <em>*</em></label>
								<input id="inv-email" type="email" name="email" placeholder="you@example.com" required>
							</div>
							<div class="inv-field inv-span-2">
								<label for="inv-phone">Contact number <em>*</em></label>
								<input id="inv-phone" type="tel" name="phone" placeholder="0300-0000000" required>
							</div>
							<div class="inv-field inv-span-2">
								<label>Bringing a vehicle? <span style="font-weight:400;text-transform:none;">(optional)</span></label>
								<div style="display:flex;gap:14px;">
									<label class="inv-choice-row" style="flex:1;">
										<input type="radio" name="has_vehicle" value="no" x-model="hasVehicle">
										<span class="inv-choice-title">No</span>
									</label>
									<label class="inv-choice-row" style="flex:1;">
										<input type="radio" name="has_vehicle" value="yes" x-model="hasVehicle">
										<span class="inv-choice-title">Yes</span>
									</label>
								</div>
							</div>
							<div class="inv-field inv-span-2" x-show="hasVehicle === 'yes'" x-cloak>
								<label>Vehicle type</label>
								<div style="display:flex;gap:14px;">
									<label class="inv-choice-row" style="flex:1;">
										<input type="radio" name="vehicle_type" value="bike">
										<span class="inv-choice-title">Bike</span>
									</label>
									<label class="inv-choice-row" style="flex:1;">
										<input type="radio" name="vehicle_type" value="car">
										<span class="inv-choice-title">Car</span>
									</label>
								</div>
							</div>
							<div class="inv-field inv-span-2" x-show="hasVehicle === 'yes'" x-cloak>
								<label for="inv-vehicle-number">Vehicle number</label>
								<input id="inv-vehicle-number" type="text" name="vehicle_number" placeholder="ABC-123">
							</div>
						</div>
					</div>

					<div class="inv-section">
						<p class="inv-section-label">Photo</p>
						<div class="inv-field">
							<label for="inv-photo">Fresh photo <em>*</em></label>
							<div class="inv-photo-zone">
								<img class="inv-photo-preview" id="inv-photo-preview" alt="">
								<input id="inv-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?php echo $photo_required ? 'required' : ''; ?>>
							</div>
							<span class="inv-help">Used for recognition and event creatives. JPG, PNG, or WebP (up to 8 MB).</span>
						</div>
					</div>

					<?php if ( ! empty( $fields['speaker'] ) ) : ?>
					<div class="inv-section" x-show="role === 'speaker'" x-cloak>
						<p class="inv-section-label">Speaker details</p>
						<div class="inv-grid">
							<div class="inv-field inv-span-2">
								<label for="inv-topic">Topic / Session title <em>*</em></label>
								<input id="inv-topic" type="text" name="role_field[topic]" :required="role === 'speaker'">
							</div>
							<div class="inv-field">
								<label for="inv-slot">Preferred slot <em>*</em></label>
								<select id="inv-slot" name="role_field[slot]" :required="role === 'speaker'">
									<option value="">Select…</option>
									<?php foreach ( $options['slots'] as $slot_key => $slot_label ) : ?>
										<option value="<?php echo esc_attr( $slot_key ); ?>"><?php echo esc_html( $slot_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="inv-field inv-span-2">
								<label for="inv-comment">Comment for the organizer <span style="font-weight:400;text-transform:none;">(optional)</span></label>
								<textarea id="inv-comment" name="role_field[comment]"></textarea>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $fields['community_leader'] ) ) : ?>
					<div class="inv-section" x-show="role === 'community_leader'" x-cloak>
						<p class="inv-section-label">AWS Community Leader</p>
						<div class="inv-grid">
							<div class="inv-field">
								<label for="inv-program">Program <em>*</em></label>
								<select id="inv-program" name="role_field[program]" :required="role === 'community_leader'">
									<option value="">Select…</option>
									<?php foreach ( $options['programs'] as $prog_key => $prog_label ) : ?>
										<option value="<?php echo esc_attr( $prog_key ); ?>"><?php echo esc_html( $prog_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="inv-field">
								<label for="inv-term">Term / Tenure <em>*</em></label>
								<input id="inv-term" type="text" name="role_field[term]" placeholder="e.g. 2025–26" :required="role === 'community_leader'">
								<span class="inv-help">SBG Leaders: your one-year leadership term (e.g. 2025–26). AWS Community Builders: your current or most recent term (e.g. 2025–26).</span>
							</div>
							<div class="inv-field inv-span-2">
								<label for="inv-about">About you <em>*</em></label>
								<textarea id="inv-about" name="role_field[about]" :required="role === 'community_leader'"></textarea>
								<span class="inv-help">Your AWS community journey, the institute or organization you represent, and your contribution.</span>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<div class="inv-section">
						<label class="inv-commit">
							<input type="checkbox" name="consent" value="yes" required>
							<span class="inv-commit-text">I confirm my invitation and consent to my name and photo being used for the recognition shield and event creatives.</span>
						</label>
					</div>

					<div class="inv-submit">
						<button type="submit" class="inv-btn inv-btn-primary" :disabled="isSubmitting">
							<span x-show="!isSubmitting">Confirm invitation</span>
							<span x-show="isSubmitting" x-cloak>Submitting…</span>
						</button>
					</div>
				</form>
			<?php endif; ?>
		</div>
	</div>

	<script>
		function inviteForm(photoRequired) {
			return {
				role: '',
				hasVehicle: 'no',
				isSubmitting: false,
				photoRequired: photoRequired
			};
		}
		document.addEventListener('DOMContentLoaded', function () {
			var input = document.getElementById('inv-photo');
			var preview = document.getElementById('inv-photo-preview');
			if (input && preview) {
				input.addEventListener('change', function () {
					if (input.files && input.files[0]) {
						preview.src = URL.createObjectURL(input.files[0]);
						preview.classList.add('is-visible');
					} else {
						preview.classList.remove('is-visible');
					}
				});
			}
		});
	</script>
	<?php
	return ob_get_clean();
}

function aws_sbg_invite_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'event' => '',
	), $atts, 'aws_invite_form' );

	$event = aws_sbg_invite_resolve_event( $atts['event'] );
	if ( ! $event ) {
		return '<p>Invitation event not found.</p>';
	}
	return aws_sbg_invite_render_form( $event->ID );
}
add_shortcode( 'aws_invite_form', 'aws_sbg_invite_form_shortcode' );
