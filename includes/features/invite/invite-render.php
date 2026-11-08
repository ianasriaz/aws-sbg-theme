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
		'cnic'      => 'Please provide your CNIC # for Air University gate clearance.',
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
				--ink:#0F141F;--paper:#FFFFFF;--surface:#F8FAFC;--line:#E2E8F0;--line-dark:#CBD5E1;--text:#334155;--muted:#475569;--brand:#7928CA;--brand-soft:#FAF5FF;--brand-border:#D8B4FE;--accent:#FF9900;--accent-hover:#F59000;
				font-family:'Amazon Ember','Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
				color:var(--ink);background:transparent;line-height:1.6;font-size:16px;
			}
			.inv-root *,.inv-root *::before,.inv-root *::after{box-sizing:border-box;}
			.inv-root [x-cloak]{display:none !important;}
			.inv-root h1,.inv-root h2,.inv-root h3,.inv-root p{margin:0;}
			.inv-root a{color:var(--brand);text-decoration:none;}
			.inv-root a:hover{color:#5B1D99;}
			.inv-root :focus-visible{outline:2px solid var(--brand);outline-offset:2px;border-radius:4px;}

			.inv-page{max-width:700px;margin:0 auto;padding:40px clamp(16px,4vw,36px) 64px;}
			.inv-ticket{position:relative;background:var(--paper);border:1px solid var(--line);border-radius:8px;box-shadow:0 1px 3px rgba(15,20,31,0.04),0 20px 48px -10px rgba(15,20,31,0.08);overflow:visible;}

			.inv-stub{padding:32px 36px 28px;background:linear-gradient(180deg,#FFFFFF 0%,#F8FAFC 100%);border-top-left-radius:8px;border-top-right-radius:8px;}
			.inv-kicker{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--brand);margin:0 0 12px;}
			.inv-kicker::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--brand);}
			.inv-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:clamp(28px,4.5vw,38px);line-height:1.15;letter-spacing:-0.015em;color:var(--ink);margin:0 0 14px;}
			.inv-desc{font-size:15.5px;line-height:1.7;color:var(--text);max-width:60ch;}
			.inv-desc p{margin:0 0 12px;}
			.inv-desc p:last-child{margin-bottom:0;}

			.inv-perf{position:relative;height:1px;border-top:1.5px dashed #CBD5E1;margin:0;}
			.inv-notch{position:absolute;top:-11px;width:22px;height:22px;background:#F8FAFC;border:1px solid var(--line);border-radius:50%;}
			.inv-notch-l{left:-12px;}
			.inv-notch-r{right:-12px;}

			.inv-body{padding:32px 36px 36px;}
			.inv-section{padding:26px 0;border-top:1px solid var(--line);}
			.inv-section:first-child{border-top:0;padding-top:4px;}
			
			.inv-section-header{display:flex;align-items:baseline;gap:12px;margin-bottom:16px;}
			.inv-step-badge{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--brand);background:var(--brand-soft);border:1px solid var(--brand-border);padding:2px 8px;border-radius:4px;}
			.inv-section-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-size:18px;font-weight:700;color:var(--ink);margin:0;}

			.inv-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
			.inv-field{display:flex;flex-direction:column;}
			.inv-field label{display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:700;color:var(--ink);margin-bottom:7px;}
			.inv-field label em{font-style:normal;color:var(--brand);margin-left:3px;}
			.inv-badge-tag{font-size:11px;font-weight:600;color:var(--muted);background:var(--surface);border:1px solid var(--line);padding:1px 7px;border-radius:3px;letter-spacing:0.02em;}
			.inv-field .inv-help{display:block;font-size:12.5px;color:var(--muted);line-height:1.5;margin-top:6px;}

			.inv-field input[type="text"],.inv-field input[type="email"],.inv-field input[type="tel"],.inv-field textarea,.inv-field select{
				width:100%;padding:11px 14px;font-family:inherit;font-size:15px;color:var(--ink);background:var(--paper);border:1.5px solid var(--line);border-radius:6px;transition:border-color .15s ease,box-shadow .15s ease,background .15s ease;
			}
			.inv-field input::placeholder,.inv-field textarea::placeholder{color:#94A3B8;}
			.inv-field input:focus,.inv-field textarea:focus,.inv-field select:focus{
				outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(121,40,202,0.12);background:#FFFFFF;
			}
			.inv-field textarea{min-height:110px;resize:vertical;line-height:1.6;}
			.inv-span-2{grid-column:1/-1;}

			/* Tactile Role Selection Cards */
			.inv-roles{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;align-items:stretch;}
			.inv-role-card{
				position:relative;display:flex;flex-direction:column;gap:6px;padding:16px 14px;background:#FFFFFF;border:1.5px solid var(--line);border-radius:6px;cursor:pointer;transition:border-color .15s ease,box-shadow .15s ease,background .15s ease;
			}
			.inv-role-card:hover{border-color:var(--line-dark);background:var(--surface);}
			.inv-role-card.is-active,.inv-role-card:has(input:checked){border-color:var(--brand);background:var(--brand-soft);box-shadow:0 2px 8px rgba(121,40,202,0.08);}
			.inv-role-header{display:flex;align-items:center;justify-content:space-between;}
			.inv-role-card input{width:17px;height:17px;accent-color:var(--brand);cursor:pointer;margin:0;}
			.inv-role-title{font-size:14px;font-weight:700;color:var(--ink);line-height:1.3;}
			.inv-role-sub{font-size:12px;color:var(--muted);line-height:1.45;}

			/* Tactile Choice Buttons (Vehicle etc) */
			.inv-choices{display:flex;gap:12px;}
			.inv-choice-row{
				display:flex;gap:10px;align-items:center;min-height:48px;padding:10px 16px;background:#FFFFFF;border:1.5px solid var(--line);border-radius:6px;cursor:pointer;transition:border-color .15s ease,background .15s ease;
			}
			.inv-choice-row:hover{border-color:var(--line-dark);background:var(--surface);}
			.inv-choice-row:has(input:checked){border-color:var(--brand);background:var(--brand-soft);}
			.inv-choice-row input{width:16px;height:16px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;margin:0;}
			.inv-choice-title{font-size:13.5px;font-weight:600;color:var(--ink);}

			/* Campus Parking Notice */
			.inv-parking-notice{
				display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;padding:12px 16px;background:#FFFBEB;border:1px solid #FCD34D;border-radius:6px;font-size:13px;color:#78350F;line-height:1.55;
			}
			.inv-parking-notice svg{flex-shrink:0;margin-top:2px;color:#D97706;}

			/* Headshot Upload */
			.inv-photo-zone{display:flex;align-items:center;gap:16px;padding:16px;background:var(--surface);border:1.5px dashed var(--line-dark);border-radius:6px;transition:border-color .15s ease;}
			.inv-photo-zone:hover{border-color:var(--brand);}
			.inv-photo-zone input{flex:1;font-size:13px;color:var(--text);}
			.inv-photo-preview{width:60px;height:60px;object-fit:cover;border-radius:6px;border:1.5px solid var(--line);background:#FFFFFF;display:none;}
			.inv-photo-preview.is-visible{display:block;}

			/* Consent & Submit */
			.inv-commit{display:flex;align-items:flex-start;gap:12px;cursor:pointer;padding:12px 14px;background:var(--surface);border:1px solid var(--line);border-radius:6px;}
			.inv-commit input{width:18px;height:18px;margin-top:3px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;}
			.inv-commit-text{font-size:13.5px;line-height:1.6;color:var(--text);}

			.inv-submit{display:flex;align-items:center;justify-content:flex-end;gap:14px;padding-top:10px;}
			.inv-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:6px;padding:14px 32px;font-family:inherit;font-size:14.5px;font-weight:700;letter-spacing:0.01em;cursor:pointer;text-decoration:none;transition:background .15s ease,transform .15s ease,box-shadow .15s ease;}
			.inv-btn-primary{background:var(--accent);color:#0F141F;box-shadow:0 2px 6px rgba(255,153,0,0.25);}
			.inv-btn-primary:hover{background:var(--accent-hover);transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,153,0,0.3);}
			.inv-btn-primary:disabled{opacity:0.65;cursor:not-allowed;transform:none;box-shadow:none;}

			.inv-notice{padding:14px 18px;border-radius:6px;font-size:14px;margin:0 0 22px;line-height:1.5;}
			.inv-notice-error{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}

			.inv-body-center{text-align:center;padding:44px 36px;}
			.inv-success-icon{width:64px;height:64px;border:2.5px solid #16A34A;border-radius:50%;color:#16A34A;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:30px;font-weight:900;}
			.inv-success-title{font-family:'Iowan Old Style','Palatino Linotype',Palatino,'Book Antiqua',Georgia,serif;font-weight:700;font-size:28px;margin:0 0 10px;color:var(--ink);}
			.inv-ref{display:inline-block;background:var(--surface);border:1.5px dashed var(--brand);border-radius:6px;padding:14px 26px;margin:22px 0 8px;}
			.inv-ref-label{font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;}
			.inv-ref-value{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:20px;font-weight:800;color:var(--brand);letter-spacing:0.04em;}

			@media (max-width:640px){
				.inv-grid,.inv-roles{grid-template-columns:1fr;}
				.inv-choices{flex-direction:column;}
				.inv-stub,.inv-body{padding-left:22px;padding-right:22px;}
				.inv-submit{justify-content:stretch;}
				.inv-submit .inv-btn{width:100%;}
			}
		</style>

		<div class="inv-page">
			<?php if ( 'success' === $status ) : ?>
				<?php
				$submitted_role = $role_param;
				if ( ! $submitted_role && $ref ) {
					global $wpdb;
					$table = $wpdb->prefix . 'aws_invite_submissions';
					$submitted_role = (string) $wpdb->get_var( $wpdb->prepare( "SELECT role FROM $table WHERE reference = %s LIMIT 1", $ref ) );
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
						<h2 class="inv-success-title">Invitation confirmed</h2>
						<p style="font-size:15px;line-height:1.65;color:var(--muted);"><?php echo esc_html( $meta['confirm_message'] ); ?></p>
						<?php if ( $ref ) : ?>
							<div class="inv-ref">
								<span class="inv-ref-label">Reference</span>
								<span class="inv-ref-value"><?php echo esc_html( $ref ); ?></span>
							</div>
						<?php endif; ?>
						<?php if ( $is_partner ) : ?>
							<div class="inv-partner-whatsapp-box" style="margin:26px auto 10px;max-width:520px;padding:22px;background:#FAF5FF;border:1.5px solid #D8B4FE;border-radius:8px;text-align:left;box-shadow:0 4px 14px rgba(121,40,202,0.08);">
								<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
									<span style="display:inline-block;padding:3px 9px;background:#7928CA;color:#FFFFFF;border-radius:4px;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;">Community Partner</span>
									<span style="font-size:15px;font-weight:700;color:#0F141F;">WhatsApp Group</span>
								</div>
								<p style="font-size:13.5px;color:#334155;margin:0 0 14px;line-height:1.55;">
									Please join the official AWS Community Partner WhatsApp group for updates, announcements, and event coordination:
								</p>
								<a class="inv-btn" href="https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l?s=cl&p=a&iam=2" target="_blank" rel="noopener noreferrer" style="background:#25D366;color:#FFFFFF;display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:700;padding:12px 22px;border-radius:6px;text-decoration:none;box-shadow:0 2px 8px rgba(37,211,102,0.25);">
									<span>Join Partner WhatsApp Group</span>
									<span>&rarr;</span>
								</a>
							</div>
						<?php elseif ( ! empty( $meta['whatsapp_url'] ) ) : ?>
							<div style="margin-top:24px;">
								<a class="inv-btn inv-btn-primary" href="<?php echo esc_url( $meta['whatsapp_url'] ); ?>" target="_blank" rel="noopener">Join WhatsApp</a>
							</div>
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
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 01</span>
									<h2 class="inv-section-title">Designation &amp; Role</h2>
								</div>
								<?php
								$role_descriptions = array(
									'speaker'           => 'Presenting a keynote or technical session',
									'guest'             => 'Invited mentor, faculty, or industry guest',
									'community_partner' => 'University chapter lead or community partner',
								);
								?>
								<div class="inv-roles" role="radiogroup" aria-label="Role">
									<?php foreach ( $roles as $role_key => $role_label ) : ?>
										<?php if ( 'guest' === $role_key && ! $allow_guests ) { continue; } ?>
										<label class="inv-role-card" :class="{ 'is-active': role === '<?php echo esc_attr( $role_key ); ?>' }">
											<div class="inv-role-header">
												<span class="inv-role-title"><?php echo esc_html( $role_label ); ?></span>
												<input type="radio" name="role" value="<?php echo esc_attr( $role_key ); ?>" x-model="role" required>
											</div>
											<span class="inv-role-sub"><?php echo esc_html( $role_descriptions[ $role_key ] ?? '' ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="inv-section">
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 02</span>
									<h2 class="inv-section-title">Credentials &amp; Gate Clearance</h2>
								</div>
								<div class="inv-grid">
									<div class="inv-field">
										<label for="inv-name"><span>Full Name <em>*</em></span></label>
										<input id="inv-name" type="text" name="name" placeholder="As printed on recognition shield" required>
									</div>
									<div class="inv-field">
										<label for="inv-email"><span>Email Address <em>*</em></span></label>
										<input id="inv-email" type="email" name="email" placeholder="you@example.com" required>
									</div>
									<div class="inv-field">
										<label for="inv-phone"><span>Contact Number <em>*</em></span></label>
										<input id="inv-phone" type="tel" name="phone" placeholder="0300-1234567" required>
									</div>
									<div class="inv-field">
										<label for="inv-cnic">
											<span>CNIC # <em>*</em></span>
											<span class="inv-badge-tag">AU Gate Clearance</span>
										</label>
										<input id="inv-cnic" type="text" name="cnic" placeholder="61101-1234567-1" maxlength="15" pattern="^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$" @input="formatCnic($event)" required>
									</div>
									<div class="inv-field inv-span-2" style="margin-top:-6px;">
										<span class="inv-help" style="margin-top:0;">Air University security requires your 13-digit CNIC for issuance of campus entry clearance.</span>
									</div>
								</div>
							</div>

							<div class="inv-section">
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 03</span>
									<h2 class="inv-section-title">Campus Logistics &amp; Transport</h2>
								</div>

								<!-- Public transit advisory for Community Partners -->
								<div x-show="role === 'community_partner' || role === 'community_leader'" class="inv-parking-notice" x-cloak>
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
									<div>
										<strong>Campus Parking Advisory:</strong> Due to strictly limited campus parking space, Community Partners are strongly encouraged to use ride-hailing / public transport options (<strong>InDrive</strong>, <strong>Yango</strong>, <strong>Bykea</strong>).
									</div>
								</div>

								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label><span>Bringing personal vehicle?</span> <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
										<div class="inv-choices">
											<label class="inv-choice-row" style="flex:1;">
												<input type="radio" name="has_vehicle" value="no" x-model="hasVehicle">
												<span class="inv-choice-title">No (Public transport / Ride-hailing)</span>
											</label>
											<label class="inv-choice-row" style="flex:1;">
												<input type="radio" name="has_vehicle" value="yes" x-model="hasVehicle">
												<span class="inv-choice-title">Yes (Bringing vehicle)</span>
											</label>
										</div>
									</div>
									<div class="inv-field inv-span-2" x-show="hasVehicle === 'yes'" x-cloak>
										<label><span>Vehicle type</span></label>
										<div class="inv-choices">
											<label class="inv-choice-row" style="flex:1;">
												<input type="radio" name="vehicle_type" value="bike">
												<span class="inv-choice-title">Motorcycle / Bike</span>
											</label>
											<label class="inv-choice-row" style="flex:1;">
												<input type="radio" name="vehicle_type" value="car">
												<span class="inv-choice-title">Car</span>
											</label>
										</div>
									</div>
									<div class="inv-field inv-span-2" x-show="hasVehicle === 'yes'" x-cloak>
										<label for="inv-vehicle-number"><span>Vehicle registration number</span></label>
										<input id="inv-vehicle-number" type="text" name="vehicle_number" placeholder="e.g. ICT-ABC-1234">
										<span class="inv-help">Required by gate security to issue your vehicle entry clearance token.</span>
									</div>
								</div>
							</div>

							<?php if ( ! empty( $fields['speaker'] ) ) : ?>
							<div class="inv-section" x-show="role === 'speaker'" x-cloak>
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 04</span>
									<h2 class="inv-section-title">Speaker Session Details</h2>
								</div>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-topic"><span>Topic or session title <em>*</em></span></label>
										<input id="inv-topic" type="text" name="role_field[topic]" placeholder="e.g. Architecting Resilient Serverless Systems on AWS" :required="role === 'speaker'">
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-slot"><span>Preferred slot <em>*</em></span></label>
										<select id="inv-slot" name="role_field[slot]" :required="role === 'speaker'">
											<option value="">Select slot timing…</option>
											<?php foreach ( $options['slots'] as $slot_key => $slot_label ) : ?>
												<option value="<?php echo esc_attr( $slot_key ); ?>"><?php echo esc_html( $slot_label ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-comment"><span>Note for the organizing team</span> <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
										<textarea id="inv-comment" name="role_field[comment]" placeholder="AV requirements, slide deck links, or special logistics…"></textarea>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $fields['guest'] ) ) : ?>
							<div class="inv-section" x-show="role === 'guest'" x-cloak>
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 04</span>
									<h2 class="inv-section-title">Guest Bio &amp; Achievements</h2>
								</div>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-guest-about"><span>Short intro about you <em>*</em></span></label>
										<textarea id="inv-guest-about" name="role_field[about]" placeholder="Write a short intro about you, especially highlighting your AWS community leadership, professional experience, and key achievements…" :required="role === 'guest'"></textarea>
										<span class="inv-help">Shared in the event programme and introduction notes for distinguished guests.</span>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $fields['community_partner'] ) || ! empty( $fields['community_leader'] ) ) : ?>
							<div class="inv-section" x-show="role === 'community_partner' || role === 'community_leader'" x-cloak>
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 04</span>
									<h2 class="inv-section-title">Community Partner Credentials</h2>
								</div>
								<div class="inv-grid">
									<div class="inv-field inv-span-2">
										<label for="inv-university"><span>University / Institute Name <em>*</em></span></label>
										<input id="inv-university" type="text" name="role_field[university]" placeholder="e.g. Air University Islamabad" :required="role === 'community_partner' || role === 'community_leader'">
										<span class="inv-help">The academic institution or student community chapter you lead.</span>
									</div>
									<div class="inv-field inv-span-2">
										<label for="inv-partner-about"><span>Short intro &amp; AWS leadership term <em>*</em></span></label>
										<textarea id="inv-partner-about" name="role_field[about]" placeholder="Write a short intro about you and your AWS leadership term (e.g. your AWS community journey, your chapter's milestones, and term duration)…" :required="role === 'community_partner' || role === 'community_leader'"></textarea>
										<span class="inv-help">Used for your recognition shield citation and institutional partner announcement.</span>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<div class="inv-section">
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 05</span>
									<h2 class="inv-section-title">Recognition Headshot</h2>
								</div>
								<div class="inv-field">
									<label for="inv-photo"><span>Recent photo <em>*</em></span></label>
									<div class="inv-photo-zone">
										<img class="inv-photo-preview" id="inv-photo-preview" alt="Preview">
										<input id="inv-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?php echo $photo_required ? 'required' : ''; ?>>
									</div>
									<span class="inv-help">Printed on your event shield and featured on official creatives. Formats: JPG, PNG, WebP (up to 8 MB).</span>
								</div>
							</div>

							<div class="inv-section">
								<div class="inv-section-header">
									<span class="inv-step-badge">Part 06</span>
									<h2 class="inv-section-title">Formal Confirmation</h2>
								</div>
								<label class="inv-commit">
									<input type="checkbox" name="consent" value="yes" required>
									<span class="inv-commit-text">I confirm my attendance and authorize AWS Student Builder Group to use my name, designation, and photograph for the event recognition shield, promotional banners, and campus security clearance.</span>
								</label>
							</div>

							<div class="inv-submit">
								<button type="submit" class="inv-btn inv-btn-primary" :disabled="isSubmitting">
									<span x-show="!isSubmitting">Confirm invitation &amp; reserve pass &rarr;</span>
									<span x-show="isSubmitting" x-cloak>Submitting confirmation…</span>
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
								}, 50);
							}
						}
					} catch(e) {}
				},
				formatCnic: function(e) {
					var v = e.target.value.replace(/\D/g, '').slice(0, 13);
					if (v.length > 12) {
						v = v.slice(0, 5) + '-' + v.slice(5, 12) + '-' + v.slice(12);
					} else if (v.length > 5) {
						v = v.slice(0, 5) + '-' + v.slice(5);
					}
					e.target.value = v;
					this.saveDraft();
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
							comment: (document.getElementById('inv-comment') || {}).value || ''
						};
						localStorage.setItem('aws_invite_draft_' + this.eventId, JSON.stringify(draft));
					} catch(e) {}
				},
				handleSubmit: function(e) {
					this.clientError = '';
					var cnicEl = document.getElementById('inv-cnic');
					if (cnicEl && !cnicEl.value.trim()) {
						e.preventDefault();
						this.clientError = 'Please provide your CNIC # for Air University gate clearance.';
						this.isSubmitting = false;
						return false;
					}
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
