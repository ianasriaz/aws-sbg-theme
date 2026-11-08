<?php
/**
 * AWS SBG Invitation Confirmation — Renderer
 * File: invite-render.php
 *
 * Shortcode + single-page form renderer (Alpine conditional reveals) and
 * the success/error states. Scoped, self-contained CSS; no Tailwind/React.
 *
 * Visual language: a printed campus notice. Serif display headings, hairline
 * rules, and a ticket-stub object with a perforation and punched notches.
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
	$role_param = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '';

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
		'filesize'  => 'Your photo is too large (max 8 MB). Please choose a smaller image.',
		'filetype'  => 'Please upload a JPG, PNG, or WebP image.',
		'upload'    => 'We could not process your photo upload. Please try again.',
		'spam'      => 'Submission blocked.',
		'db'        => 'We could not save your confirmation. Please try again.',
	);

	ob_start();
	?>
	<div class="inv-root" x-data="inviteForm(<?php echo $photo_required ? 'true' : 'false'; ?>, <?php echo intval( $event_id ); ?>)">
		<style>
			.inv-root{
				--ink:#0F141F;--paper:#FFFFFF;--surface:#F8FAFC;--line:#E2E8F0;--text:#334155;--muted:#475569;--brand:#7928CA;--brand-soft:#F5F3FF;--accent:#FF9900;
				font-family:'Amazon Ember','Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
				color:var(--ink);background:transparent;line-height:1.6;font-size:16px;
			}
			.inv-root *,.inv-root *::before,.inv-root *::after{box-sizing:border-box;}
			.inv-root [x-cloak]{display:none !important;}
			.inv-root h1,.inv-root h2,.inv-root h3,.inv-root p{margin:0;}
			.inv-root a{color:var(--brand);text-decoration:none;}
			.inv-root a:hover{color:#9353FF;}
			.inv-root :focus-visible{outline:2px solid var(--brand);outline-offset:2px;border-radius:3px;}

			.inv-page{max-width:680px;margin:0 auto;padding:40px clamp(16px,4vw,40px) 64px;}
			.inv-ticket{position:relative;background:var(--paper);border:1px solid var(--line);border-radius:6px;box-shadow:0 1px 2px rgba(15,20,31,0.04),0 16px 40px rgba(15,20,31,0.07);overflow:visible;}

			.inv-stub{padding:28px 32px 26px;}
			.inv-kicker{font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--brand);margin:0 0 12px;}
			.inv-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:clamp(30px,5vw,42px);line-height:1.08;letter-spacing:-0.01em;color:var(--ink);margin:0 0 14px;}
			.inv-desc{font-size:16px;line-height:1.7;color:var(--text);max-width:58ch;}
			.inv-desc p{margin:0 0 12px;}
			.inv-desc p:last-child{margin-bottom:0;}

			.inv-perf{position:relative;height:1px;border-top:1px dashed #cbd5e1;}
			.inv-notch{position:absolute;top:-11px;width:22px;height:22px;background:var(--surface);border:1px solid var(--line);border-radius:50%;}
			.inv-notch-l{left:-12px;}
			.inv-notch-r{right:-12px;}

			.inv-body{padding:26px 32px 32px;}
			.inv-section{padding:22px 0;border-top:1px solid var(--line);}
			.inv-section:first-child{border-top:0;padding-top:0;}
			.inv-section-label{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-size:17px;font-weight:700;color:var(--ink);margin:0 0 16px;}

			.inv-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
			.inv-field label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;}
			.inv-field label em{font-style:normal;color:var(--brand);}
			.inv-field .inv-help{display:block;font-size:13px;color:var(--muted);margin-top:6px;}
			.inv-field input[type="text"],.inv-field input[type="email"],.inv-field input[type="tel"],.inv-field textarea,.inv-field select{width:100%;padding:12px 14px;font-family:inherit;font-size:16px;color:var(--ink);background:var(--paper);border:1px solid var(--line);border-radius:4px;transition:border-color .15s ease,box-shadow .15s ease;}
			.inv-field input::placeholder,.inv-field textarea::placeholder{color:#64748b;}
			.inv-field input:focus,.inv-field textarea:focus,.inv-field select:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(121,40,202,0.12);}
			.inv-field textarea{min-height:120px;resize:vertical;}
			.inv-span-2{grid-column:1/-1;}

			.inv-roles{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;align-items:stretch;}
			.inv-role{display:flex;align-items:center;gap:12px;min-height:56px;padding:14px 16px;background:var(--surface);border:1px solid var(--line);border-radius:4px;cursor:pointer;transition:border-color .15s ease,background .15s ease;}
			.inv-role:hover{border-color:#94a3b8;}
			.inv-role:has(input:checked){border-color:var(--brand);background:var(--brand-soft);}
			.inv-role input{width:18px;height:18px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;margin:0;}
			.inv-role-title{font-size:14px;font-weight:600;color:var(--ink);line-height:1.3;}

			.inv-select-wrap{position:relative;width:100%;}
			.inv-select{width:100%;height:48px;padding:0 38px 0 14px;font-family:inherit;font-size:15px;font-weight:500;color:var(--ink);background:var(--paper);border:1px solid var(--line);border-radius:4px;appearance:none;-webkit-appearance:none;cursor:pointer;transition:border-color .15s ease,box-shadow .15s ease;}
			.inv-select:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(121,40,202,0.12);}
			.inv-select-arrow{position:absolute;right:14px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--muted);display:flex;align-items:center;justify-content:center;}

			.inv-choice-row{display:flex;gap:12px;align-items:center;min-height:48px;padding:12px 16px;background:var(--paper);border:1px solid var(--line);border-radius:4px;cursor:pointer;}
			.inv-choice-row:has(input:checked){border-color:var(--brand);background:var(--brand-soft);}
			.inv-choice-row input{width:18px;height:18px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;margin:0;}
			.inv-choice-title{font-size:14px;font-weight:600;color:var(--ink);}

			.inv-commit{display:flex;align-items:flex-start;gap:14px;cursor:pointer;}
			.inv-commit input{width:18px;height:18px;margin-top:3px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;}
			.inv-commit-text{font-size:14.5px;line-height:1.6;color:var(--text);}

			.inv-upload-zone{position:relative;border:1px dashed #cbd5e1;border-radius:6px;background:var(--surface);padding:22px 18px;text-align:center;cursor:pointer;transition:border-color .15s ease,background-color .15s ease;outline:none;}
			.inv-upload-zone:hover,.inv-upload-zone.is-dragover{border-color:var(--brand);background:var(--brand-soft);}
			.inv-upload-zone:focus-visible{border-color:var(--brand);box-shadow:0 0 0 3px rgba(121,40,202,0.12);}
			.inv-upload-input{position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:2;}
			.inv-upload-empty{display:flex;flex-direction:column;align-items:center;gap:6px;}
			.inv-upload-icon-circle{width:38px;height:38px;border-radius:50%;background:var(--paper);border:1px solid var(--line);color:var(--muted);display:flex;align-items:center;justify-content:center;margin-bottom:2px;transition:color .15s ease,border-color .15s ease;}
			.inv-upload-zone:hover .inv-upload-icon-circle{color:var(--brand);border-color:var(--brand);}
			.inv-upload-prompt{font-size:13.5px;color:var(--text);}
			.inv-upload-browse{font-weight:700;color:var(--brand);}
			.inv-upload-drag{color:var(--muted);}
			.inv-upload-meta{font-size:12px;color:#94a3b8;}
			.inv-upload-filled{display:flex;align-items:center;gap:14px;text-align:left;}
			.inv-upload-thumb{width:52px;height:52px;border-radius:6px;object-fit:cover;border:1px solid var(--line);background:var(--paper);flex-shrink:0;}
			.inv-upload-info{flex:1;min-width:0;}
			.inv-upload-filename{font-size:13.5px;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
			.inv-upload-filesize{font-size:12px;color:var(--muted);margin-top:2px;}
			.inv-upload-change{font-family:inherit;font-size:12.5px;font-weight:600;color:var(--brand);background:var(--paper);border:1px solid var(--line);border-radius:4px;padding:6px 12px;cursor:pointer;flex-shrink:0;position:relative;z-index:3;transition:background-color .15s ease,border-color .15s ease;}
			.inv-upload-change:hover{background:var(--brand-soft);border-color:var(--brand);}

			.inv-submit{display:flex;align-items:center;justify-content:flex-end;gap:12px;padding-top:8px;}
			.inv-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:4px;padding:14px 30px;font-family:inherit;font-size:14px;font-weight:700;letter-spacing:0.01em;cursor:pointer;text-decoration:none;transition:background .18s ease,transform .18s ease;}
			.inv-btn-primary{background:var(--accent);color:var(--ink);}
			.inv-btn-primary:hover{background:#ffad33;transform:translateY(-1px);}
			.inv-btn-primary:disabled{opacity:0.6;cursor:default;transform:none;}

			.inv-notice{padding:14px 18px;border-radius:4px;font-size:14px;margin:0 0 22px;}
			.inv-notice-error{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}

			.inv-body-center{text-align:center;padding:40px 32px;}
			.inv-success-icon{width:60px;height:60px;border:2px solid #16A34A;border-radius:50%;color:#16A34A;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;font-weight:900;}
			.inv-success-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:26px;margin:0 0 10px;}
			.inv-ref{display:inline-block;border:1px dashed #94a3b8;border-radius:4px;padding:13px 22px;margin:20px 0 6px;}
			.inv-ref-label{font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;}
			.inv-ref-value{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:18px;font-weight:700;color:var(--brand);letter-spacing:0.02em;}

			@media (max-width:600px){
				.inv-grid,.inv-roles{grid-template-columns:1fr;}
				.inv-stub,.inv-body{padding-left:22px;padding-right:22px;}
				.inv-submit{justify-content:stretch;}
				.inv-submit .inv-btn{width:100%;}
			}
		</style>

		<div class="inv-page">
			<?php if ( 'success' === $status ) : ?>
				<?php
				$submitted_role = $role_param;
				$submitted_name = isset( $_GET['invite_name'] ) ? sanitize_text_field( wp_unslash( rawurldecode( $_GET['invite_name'] ) ) ) : '';
				if ( ( ! $submitted_role || ! $submitted_name ) && $ref ) {
					global $wpdb;
					$table = $wpdb->prefix . 'aws_invite_submissions';
					$sub_row = $wpdb->get_row( $wpdb->prepare( "SELECT name, role FROM $table WHERE reference = %s LIMIT 1", $ref ), ARRAY_A );
					if ( $sub_row ) {
						if ( ! $submitted_role ) {
							$submitted_role = $sub_row['role'];
						}
						if ( ! $submitted_name ) {
							$submitted_name = $sub_row['name'];
						}
					}
				}
				$is_partner = ( 'community_partner' === $submitted_role || 'community_leader' === $submitted_role );
				?>
				<div class="inv-ticket">
					<header class="inv-stub">
						<p class="inv-kicker">AWS Student Builder Group</p>
						<h1 class="inv-title"><?php echo esc_html( $event->post_title ); ?></h1>
					</header>
					<div class="inv-perf" aria-hidden="true"><span class="inv-notch inv-notch-l"></span><span class="inv-notch inv-notch-r"></span></div>
					<div class="inv-body inv-body-center">
						<div class="inv-success-icon">&#10003;</div>
						<h2 class="inv-success-title"><?php echo $submitted_name ? 'Thank You ' . esc_html( $submitted_name ) : 'Thank You'; ?></h2>
						<p style="font-size:15px;line-height:1.6;color:var(--muted);max-width:480px;margin:0 auto 16px;">
							Your details have been received.
						</p>
						<?php if ( $is_partner ) : ?>
							<p style="font-size:14px;line-height:1.6;color:var(--ink);max-width:480px;margin:0 auto 6px;font-weight:500;">
								You are the official AWS community partner collaborating on the AWS re:Invent 2026 Watch &amp; Build Party in Islamabad. Let's grow and represent AWS together!
							</p>
							<p style="font-size:13px;color:var(--muted);margin:0 0 16px;">
								Event pass will be shared separately.
							</p>
							<div style="margin:16px auto 8px;max-width:380px;padding:16px;background:var(--surface);border:1px solid var(--line);border-radius:6px;text-align:center;">
								<p style="font-size:13px;color:var(--text);margin:0 0 12px;line-height:1.5;">
									Join for updates &amp; announcements:
								</p>
								<a class="inv-btn" href="https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l?s=cl&p=a&iam=2" target="_blank" rel="noopener noreferrer" style="background:#25D366;color:#FFFFFF;padding:9px 18px;font-size:13px;font-weight:700;border-radius:5px;box-shadow:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
									<span>Join WhatsApp Group</span>
									<span>&rarr;</span>
								</a>
							</div>
						<?php else : ?>
							<?php if ( ! empty( $meta['confirm_message'] ) ) : ?>
								<p style="font-size:14px;line-height:1.6;color:var(--muted);margin-bottom:14px;"><?php echo esc_html( $meta['confirm_message'] ); ?></p>
							<?php endif; ?>
							<?php if ( $ref ) : ?>
								<div class="inv-ref" style="margin-top:14px;">
									<span class="inv-ref-label">Reference</span>
									<span class="inv-ref-value"><?php echo esc_html( $ref ); ?></span>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $meta['whatsapp_url'] ) ) : ?>
								<div style="margin-top:20px;">
									<a class="inv-btn inv-btn-primary" href="<?php echo esc_url( $meta['whatsapp_url'] ); ?>" target="_blank" rel="noopener" style="padding:9px 18px;font-size:13px;white-space:nowrap;">Join WhatsApp Group</a>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<p style="margin:22px 0 0;"><a href="<?php echo esc_url( home_url() ); ?>" style="font-size:13px;font-weight:700;">Back to home</a></p>
					</div>
				</div>
			<?php else : ?>
				<div class="inv-ticket">
					<header class="inv-stub">
						<p class="inv-kicker">AWS Student Builder Group</p>
						<h1 class="inv-title"><?php echo esc_html( $event->post_title ); ?></h1>
						<?php if ( $event->post_content ) : ?>
							<div class="inv-desc"><?php echo wp_kses_post( wpautop( $event->post_content ) ); ?></div>
						<?php endif; ?>
					</header>
					<div class="inv-perf" aria-hidden="true"><span class="inv-notch inv-notch-l"></span><span class="inv-notch inv-notch-r"></span></div>
					<div class="inv-body">
						<?php if ( 'error' === $status && isset( $error_messages[ $err ] ) ) : ?>
							<div class="inv-notice inv-notice-error"><?php echo esc_html( $error_messages[ $err ] ); ?></div>
						<?php endif; ?>

						<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" @submit="handleSubmit($event)" @input="saveDraft()" @change="saveDraft()">
							<input type="hidden" name="action" value="aws_sbg_invite_submit">
							<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
							<?php wp_nonce_field( 'aws_sbg_invite_submit', 'invite_nonce' ); ?>
							<input type="text" name="website_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">

							<div class="inv-notice inv-notice-error" x-show="clientError" x-text="clientError" x-cloak style="margin-bottom:18px;"></div>

							<div class="inv-section">
								<label for="inv-role" class="inv-section-label" style="display:block;margin-bottom:8px;">I am joining as <em>*</em></label>
								<div class="inv-select-wrap">
									<select id="inv-role" name="role" x-model="role" required class="inv-select">
										<option value="" disabled selected>Select your role…</option>
										<?php foreach ( $roles as $role_key => $role_label ) : ?>
											<?php if ( 'guest' === $role_key && ! $allow_guests ) { continue; } ?>
											<option value="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $role_label ); ?></option>
										<?php endforeach; ?>
									</select>
									<span class="inv-select-arrow" aria-hidden="true">
										<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
											<polyline points="6 9 12 15 18 9"></polyline>
										</svg>
									</span>
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
									<div class="inv-field">
										<label for="inv-phone">Contact number <em>*</em></label>
										<input id="inv-phone" type="tel" name="phone" placeholder="0300-0000000" required>
									</div>
									<div class="inv-field">
										<label for="inv-cnic">CNIC # <em>*</em> <span style="font-weight:400;color:var(--muted);">(For gate clearance)</span></label>
										<input id="inv-cnic" type="text" name="cnic" placeholder="00000-0000000-0" required>
									</div>
									<div class="inv-field inv-span-2">
										<label>Bringing a vehicle? <span style="font-weight:400;">(optional)</span></label>
										<p x-show="role === 'community_partner' || role === 'community_leader'" style="font-size:12.5px;color:#b45309;margin:0 0 8px 0;" x-cloak>
											Due to limited parking space, we encourage using public transport (InDrive, Yango, Bykea).
										</p>
										<div style="display:flex;gap:12px;">
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
										<div style="display:flex;gap:12px;">
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
									<label for="inv-photo">Fresh portrait photo <em>*</em></label>
									<div class="inv-upload-zone" id="inv-upload-zone" tabindex="0" role="button" aria-label="Upload photo">
										<input id="inv-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?php echo $photo_required ? 'required' : ''; ?> class="inv-upload-input">
										
										<div class="inv-upload-empty" id="inv-upload-empty">
											<div class="inv-upload-icon-circle">
												<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
													<rect x="3" y="3" width="18" height="18" rx="3" ry="3"/>
													<circle cx="8.5" cy="8.5" r="1.5"/>
													<polyline points="21 15 16 10 5 21"/>
												</svg>
											</div>
											<div class="inv-upload-prompt">
												<span class="inv-upload-browse">Choose a photo</span> <span class="inv-upload-drag">or drag &amp; drop here</span>
											</div>
											<div class="inv-upload-meta">JPG, PNG or WebP · Up to 8 MB</div>
										</div>

										<div class="inv-upload-filled" id="inv-upload-filled" style="display:none;">
											<img class="inv-upload-thumb" id="inv-photo-preview" alt="Preview">
											<div class="inv-upload-info">
												<div class="inv-upload-filename" id="inv-upload-filename">photo.jpg</div>
												<div class="inv-upload-filesize" id="inv-upload-filesize">1.2 MB</div>
											</div>
											<button type="button" class="inv-upload-change" id="inv-upload-change-btn">Change</button>
										</div>
									</div>
									<span class="inv-help">Used for recognition shield and event creatives. High-resolution portrait recommended.</span>
								</div>
							</div>

							<?php if ( ! empty( $fields['speaker'] ) ) : ?>
							<div class="inv-section" x-show="role === 'speaker'" x-cloak>
								<p class="inv-section-label">Speaker details</p>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-topic">Topic or session title <em>*</em></label>
										<input id="inv-topic" type="text" name="role_field[topic]" :required="role === 'speaker'">
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-slot">Preferred slot <em>*</em></label>
										<select id="inv-slot" name="role_field[slot]" :required="role === 'speaker'">
											<option value="">Select…</option>
											<?php foreach ( $options['slots'] as $slot_key => $slot_label ) : ?>
												<option value="<?php echo esc_attr( $slot_key ); ?>"><?php echo esc_html( $slot_label ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-comment">Comment for the organizer <span style="font-weight:400;">(optional)</span></label>
										<textarea id="inv-comment" name="role_field[comment]"></textarea>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $fields['guest'] ) ) : ?>
							<div class="inv-section" x-show="role === 'guest'" x-cloak>
								<p class="inv-section-label">Guest details</p>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-guest-about">Short intro about you <em>*</em></label>
										<textarea id="inv-guest-about" name="role_field[about]" placeholder="Write a short intro about you, especially about your AWS community leadership, professional experience, and achievements…" :required="role === 'guest'"></textarea>
										<span class="inv-help">Please share a short intro about your background, especially regarding AWS community leadership, professional experience, and key achievements.</span>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $fields['community_partner'] ) || ! empty( $fields['community_leader'] ) ) : ?>
							<div class="inv-section" x-show="role === 'community_partner' || role === 'community_leader'" x-cloak>
								<p class="inv-section-label">AWS Community Partner details</p>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-university">University / Institute Name <em>*</em></label>
										<input id="inv-university" type="text" name="role_field[university]" placeholder="e.g. Air University Islamabad" :required="role === 'community_partner' || role === 'community_leader'">
										<span class="inv-help">The university or institute chapter you represent.</span>
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-partner-about">Short intro about you &amp; AWS leadership term <em>*</em></label>
										<textarea id="inv-partner-about" name="role_field[about]" placeholder="Write a short intro about you and your AWS leadership term…" :required="role === 'community_partner' || role === 'community_leader'"></textarea>
										<span class="inv-help">Write a short intro about you and your AWS leadership term (e.g. your AWS community journey, your role, and achievements).</span>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<div class="inv-section">
								<label class="inv-commit">
									<input type="checkbox" name="consent" value="yes" required>
									<span class="inv-commit-text">I confirm my invitation and consent to my name and photo being used for the recognition shield and event creatives.</span>
								</label>
								<div x-show="role === 'community_partner' || role === 'community_leader'" style="margin-top:14px;" x-cloak>
									<label class="inv-commit">
										<input type="checkbox" name="partner_promo_consent" value="yes" :required="role === 'community_partner' || role === 'community_leader'">
										<span class="inv-commit-text">I will help to promote event within my community as possible.</span>
									</label>
								</div>
							</div>

							<div class="inv-submit">
								<button type="submit" class="inv-btn inv-btn-primary" :disabled="isSubmitting">
									<span x-show="!isSubmitting">Confirm invitation</span>
									<span x-show="isSubmitting" x-cloak>Submitting…</span>
								</button>
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
		function inviteForm(photoRequired, eventId) {
			return {
				role: '',
				hasVehicle: 'no',
				isSubmitting: false,
				photoRequired: photoRequired,
				eventId: eventId,
				clientError: '',
				init: function() {
					try {
						// Clean draft if success
						<?php if ( 'success' === $status ) : ?>
							if (typeof localStorage !== 'undefined') {
								localStorage.removeItem('aws_invite_draft_' + this.eventId);
							}
							return;
						<?php endif; ?>

						// Restore draft on load so no user effort is lost
						if (typeof localStorage !== 'undefined') {
							var draftRaw = localStorage.getItem('aws_invite_draft_' + this.eventId);
							if (draftRaw) {
								var data = JSON.parse(draftRaw);
								if (data.role) this.role = data.role;
								if (data.hasVehicle) this.hasVehicle = data.hasVehicle;
								var self = this;
								setTimeout(function() {
									['name', 'email', 'phone', 'cnic', 'vehicle_type', 'vehicle_number'].forEach(function(f) {
										var el = document.querySelector('[name="' + f + '"]');
										if (el && data[f]) el.value = data[f];
									});
									var elUni = document.getElementById('inv-university');
									if (elUni && data.university) elUni.value = data.university;
									var elAboutP = document.getElementById('inv-partner-about');
									if (elAboutP && data.partner_about) elAboutP.value = data.partner_about;
									var elAboutG = document.getElementById('inv-guest-about');
									if (elAboutG && data.guest_about) elAboutG.value = data.guest_about;
									var elTopic = document.getElementById('inv-topic');
									if (elTopic && data.topic) elTopic.value = data.topic;
									var elSlot = document.getElementById('inv-slot');
									if (elSlot && data.slot) elSlot.value = data.slot;
									var elComment = document.getElementById('inv-comment');
									if (elComment && data.comment) elComment.value = data.comment;
									var elPromo = document.querySelector('[name="partner_promo_consent"]');
									if (elPromo && data.partner_promo_consent === 'yes') elPromo.checked = true;
								}, 50);
							}
						}
					} catch(e) {}
				},
				saveDraft: function() {
					try {
						if (typeof localStorage === 'undefined') return;
						var draft = {
							role: this.role,
							hasVehicle: this.hasVehicle,
							name: (document.getElementById('inv-name') || {}).value || '',
							email: (document.getElementById('inv-email') || {}).value || '',
							phone: (document.getElementById('inv-phone') || {}).value || '',
							cnic: (document.getElementById('inv-cnic') || {}).value || '',
							vehicle_type: (document.querySelector('[name="vehicle_type"]:checked') || {}).value || '',
							vehicle_number: (document.getElementById('inv-vehicle-number') || {}).value || '',
							university: (document.getElementById('inv-university') || {}).value || '',
							partner_about: (document.getElementById('inv-partner-about') || {}).value || '',
							guest_about: (document.getElementById('inv-guest-about') || {}).value || '',
							topic: (document.getElementById('inv-topic') || {}).value || '',
							slot: (document.getElementById('inv-slot') || {}).value || '',
							comment: (document.getElementById('inv-comment') || {}).value || '',
							partner_promo_consent: (document.querySelector('[name="partner_promo_consent"]') || {}).checked ? 'yes' : ''
						};
						localStorage.setItem('aws_invite_draft_' + this.eventId, JSON.stringify(draft));
					} catch(e) {}
				},
				handleSubmit: function(e) {
					this.clientError = '';
					var fileInput = document.getElementById('inv-photo');
					if (fileInput && fileInput.files && fileInput.files[0]) {
						var file = fileInput.files[0];
						if (file.size > 8 * 1024 * 1024) {
							e.preventDefault();
							this.clientError = 'Your photo is too large (' + (file.size / (1024*1024)).toFixed(1) + ' MB). Please choose an image under 8 MB.';
							this.isSubmitting = false;
							return false;
						}
					} else if (this.photoRequired) {
						e.preventDefault();
						this.clientError = 'Please upload a photo for your event creatives.';
						this.isSubmitting = false;
						return false;
					}
					this.saveDraft();
					this.isSubmitting = true;
				}
			};
		}
		document.addEventListener('DOMContentLoaded', function () {
			var input = document.getElementById('inv-photo');
			var zone = document.getElementById('inv-upload-zone');
			var emptyState = document.getElementById('inv-upload-empty');
			var filledState = document.getElementById('inv-upload-filled');
			var preview = document.getElementById('inv-photo-preview');
			var nameEl = document.getElementById('inv-upload-filename');
			var sizeEl = document.getElementById('inv-upload-filesize');
			var changeBtn = document.getElementById('inv-upload-change-btn');

			function formatSize(bytes) {
				if (!bytes) return '';
				if (bytes < 1024 * 1024) {
					return (bytes / 1024).toFixed(0) + ' KB';
				}
				return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
			}

			function updateFileDisplay() {
				if (input && input.files && input.files[0]) {
					var f = input.files[0];
					if (preview) preview.src = URL.createObjectURL(f);
					if (nameEl) nameEl.textContent = f.name;
					if (sizeEl) sizeEl.textContent = formatSize(f.size);
					if (emptyState) emptyState.style.display = 'none';
					if (filledState) filledState.style.display = 'flex';
				} else {
					if (preview) preview.src = '';
					if (emptyState) emptyState.style.display = 'flex';
					if (filledState) filledState.style.display = 'none';
				}
			}

			if (input) {
				input.addEventListener('change', updateFileDisplay);
			}

			if (changeBtn && input) {
				changeBtn.addEventListener('click', function(e) {
					e.preventDefault();
					input.click();
				});
			}

			if (zone) {
				['dragenter', 'dragover'].forEach(function(evt) {
					zone.addEventListener(evt, function(e) {
						e.preventDefault();
						e.stopPropagation();
						zone.classList.add('is-dragover');
					});
				});
				['dragleave', 'drop'].forEach(function(evt) {
					zone.addEventListener(evt, function(e) {
						e.preventDefault();
						e.stopPropagation();
						zone.classList.remove('is-dragover');
					});
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
