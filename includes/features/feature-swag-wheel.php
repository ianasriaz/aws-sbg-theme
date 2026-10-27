<?php
/**
 * AWS SBG Random Swag Picker Wheel Engine
 * File: feature-swag-wheel.php
 * 
 * Ultra-lightweight, high-engagement spinning wheel for live event giveaways,
 * filtering paying participants, passed qualifiers, and event attendees with zero server overhead.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. REGISTER ADMIN SUBMENU
 */
function aws_sbg_register_swag_wheel_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'Swag Picker Wheel',
		'Swag Picker',
		'manage_options',
		'aws-swag-wheel',
		'aws_sbg_render_swag_wheel_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_register_swag_wheel_menu', 25 );

/**
 * 2. AJAX ENDPOINT TO FETCH CANDIDATES
 */
function aws_sbg_ajax_get_wheel_candidates() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	global $wpdb;
	$event_id    = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
	$filter_type = isset( $_GET['filter_type'] ) ? sanitize_text_field( $_GET['filter_type'] ) : 'paying';

	$candidates = array();

	if ( $filter_type === 'passed_certs' ) {
		// Fetch participants from issued certificates table
		$table_certs = $wpdb->prefix . 'aws_sbg_event_issued_certs';
		$query = "SELECT id, participant_name as name, participant_email as email, verification_code as code FROM $table_certs";
		if ( $event_id > 0 ) {
			$query .= $wpdb->prepare( " WHERE event_id = %d", $event_id );
		}
		$query .= " ORDER BY id DESC LIMIT 500";
		$rows = $wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $r ) {
				$candidates[] = array(
					'id'     => 'cert_' . $r['id'],
					'name'   => trim( $r['name'] ),
					'email'  => trim( $r['email'] ),
					'code'   => $r['code'],
					'tag'    => 'Passed Qualifier',
				);
			}
		}
	} else {
		// Fetch from event registrations post type
		$args = array(
			'post_type'      => 'aws_event_reg',
			'post_status'    => 'publish',
			'posts_per_page' => 500,
		);

		$meta_query = array( 'relation' => 'AND' );

		if ( $event_id > 0 ) {
			$meta_query[] = array(
				'key'     => 'event_id',
				'value'   => $event_id,
				'compare' => '=',
			);
		}

		if ( $filter_type === 'paying' ) {
			$meta_query[] = array(
				'relation' => 'OR',
				'tier_clause' => array(
					'key'     => 'member_type',
					'value'   => 'paying',
					'compare' => '=',
				),
				'supporter_clause' => array(
					'key'     => 'is_supporter',
					'value'   => 'yes',
					'compare' => '=',
				),
				'pro_clause' => array(
					'key'     => 'ticket_tier',
					'value'   => array( 'supporter', 'vip', 'pro' ),
					'compare' => 'IN',
				),
			);
		} elseif ( $filter_type === 'attended' ) {
			$meta_query[] = array(
				'key'     => 'attended',
				'value'   => 'yes',
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$pid = get_the_ID();
				$title = get_the_title();
				
				// Extract cleaner name
				$name = $title;
				if ( strpos( $title, ' - ' ) !== false ) {
					$parts = explode( ' - ', $title );
					$name = trim( $parts[1] ?? $title );
				}
				$name = str_replace( '⭐ Super Supporter', '', $name );
				$name = trim( $name );

				$email = get_post_meta( $pid, 'email', true );
				$reg_id = get_post_meta( $pid, 'registration_id', true );
				$member_type = get_post_meta( $pid, 'member_type', true );

				$candidates[] = array(
					'id'     => 'reg_' . $pid,
					'name'   => ! empty( $name ) ? $name : 'Participant #' . $pid,
					'email'  => $email,
					'code'   => ! empty( $reg_id ) ? $reg_id : '#' . $pid,
					'tag'    => ( $member_type === 'paying' ) ? 'Pro Member' : 'Attendee',
				);
			}
			wp_reset_postdata();
		}
	}

	wp_send_json_success( array(
		'count'      => count( $candidates ),
		'candidates' => $candidates,
	) );
}
add_action( 'wp_ajax_aws_sbg_get_wheel_candidates', 'aws_sbg_ajax_get_wheel_candidates' );

/**
 * 3. RENDER ADMIN SWAG PICKER WHEEL PAGE
 */
function aws_sbg_render_swag_wheel_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}

	// Fetch events for filter dropdown
	$events = get_posts( array(
		'post_type'      => 'aws_event',
		'posts_per_page' => 50,
		'post_status'    => 'publish',
	) );

	global $wpdb;
	$table_events = $wpdb->prefix . 'aws_sbg_events';
	$sql_events = $wpdb->get_results( "SELECT id, title FROM $table_events ORDER BY id DESC LIMIT 50", ARRAY_A );
	?>
	<div class="wrap" style="max-width:1400px; margin:20px auto; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
		
		<?php if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) : ?>
			<?php aws_sbg_render_admin_nav_tabs( 'swag_wheel' ); ?>
		<?php endif; ?>

		<!-- Header Banner -->
		<div style="background:linear-gradient(135deg, #150b2e 0%, #0a0814 100%); border:1px solid #1d2636; border-radius:18px; padding:24px 30px; margin-bottom:25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
			<div>
				<div style="display:inline-flex; align-items:center; gap:8px; background:rgba(147,83,255,0.15); border:1px solid rgba(147,83,255,0.35); padding:4px 12px; border-radius:8px; font-size:11px; font-weight:700; color:#b57aff; font-family:monospace; text-transform:uppercase; margin-bottom:8px;">
					<span>🎡 AWS SBG STAGE ENGINE</span>
				</div>
				<h1 style="margin:0; font-size:24px; font-weight:800; color:#ffffff; line-height:1.2;">
					Random Swag Picker Wheel
				</h1>
				<p style="margin:4px 0 0 0; font-size:13px; color:#94a3b8;">
					Live lucky draw engine for AWS certification challenges, paying participants, and event giveaways.
				</p>
			</div>
			<div style="display:flex; gap:10px;">
				<button id="fullscreen-btn" style="background:#1e1b4b; color:#c7d2fe; border:1px solid #3730a3; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px;">
					<span>📺 Projector Mode</span>
				</button>
			</div>
		</div>

		<!-- Main Layout -->
		<div style="display:grid; grid-template-columns: 360px 1fr; gap:25px;" id="wheel-container-wrapper">
			
			<!-- Left Control Sidebar -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:22px; box-shadow:0 4px 6px rgba(0,0,0,0.02); height:fit-content; space-y:18px;">
				
				<h3 style="margin:0 0 15px 0; font-size:15px; font-weight:800; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; align-items:center; justify-content:space-between;">
					<span>⚙️ Event &amp; Swag Filters</span>
					<span id="candidate-count-badge" style="background:#f1f5f9; color:#475569; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:700;">0 Loaded</span>
				</h3>

				<!-- Filter Source -->
				<div style="margin-bottom:15px;">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
						Audience Target:
					</label>
					<select id="filter-type-select" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid #cbd5e1; font-size:13px; font-weight:600; background:#f8fafc;">
						<option value="paying">👑 Paying / Pro Participants (Challenge)</option>
						<option value="passed_certs">📜 Passed Qualifiers (Certificate Claimed)</option>
						<option value="all_regs">🎟️ All Registered Attendees</option>
						<option value="attended">✅ Checked-In Attendees Only</option>
						<option value="manual">✍️ Manual / Custom Names</option>
					</select>
				</div>

				<!-- Event Selection -->
				<div style="margin-bottom:15px;" id="event-select-container">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
						Select Activity / Event:
					</label>
					<select id="event-id-select" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid #cbd5e1; font-size:13px; font-weight:600; background:#f8fafc;">
						<option value="0">⚡ All Events &amp; Activities</option>
						<?php foreach ( $events as $ev ) : ?>
							<option value="<?php echo esc_attr( $ev->ID ); ?>"><?php echo esc_html( $ev->post_title ); ?></option>
						<?php endforeach; ?>
						<?php if ( ! empty( $sql_events ) ) : ?>
							<?php foreach ( $sql_events as $sev ) : ?>
								<option value="<?php echo esc_attr( $sev['id'] ); ?>"><?php echo esc_html( $sev['title'] ); ?> (Cert Track)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<!-- Swag Prize Setting -->
				<div style="margin-bottom:15px;">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
						Swag Prize Being Drawn:
					</label>
					<input type="text" id="swag-prize-input" value="Official AWS T-Shirt & Swag Kit 🎁" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid #cbd5e1; font-size:13px; font-weight:600; background:#ffffff;">
				</div>

				<!-- Manual Paste Container -->
				<div id="manual-paste-container" style="display:none; margin-bottom:15px;">
					<label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
						Enter Names (One per line):
					</label>
					<textarea id="manual-names-input" rows="6" placeholder="Ali Ahmed&#10;Fatima Khan&#10;Hamza Tariq" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid #cbd5e1; font-size:12px; font-family:monospace;"></textarea>
				</div>

				<!-- Spin Settings -->
				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-bottom:18px;">
					<label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#334155; cursor:pointer;">
						<input type="checkbox" id="remove-winner-toggle" checked style="border-radius:4px; accent-color:#9353ff;">
						<span>Remove winner from next spin</span>
					</label>
					<label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#334155; margin-top:8px; cursor:pointer;">
						<input type="checkbox" id="sound-effects-toggle" checked style="border-radius:4px; accent-color:#9353ff;">
						<span>Sound &amp; ticker effects 🔊</span>
					</label>
				</div>

				<!-- Refresh Button -->
				<button id="fetch-candidates-btn" style="width:100%; background:#9353ff; color:#ffffff; border:none; padding:12px; border-radius:12px; font-weight:800; font-size:13px; cursor:pointer; box-shadow:0 4px 12px rgba(147,83,255,0.25); transition:all 0.15s ease;">
					🔄 Refresh Participant List
				</button>

				<!-- Winner History Log -->
				<div style="margin-top:20px; border-top:1px solid #f1f5f9; padding-top:15px;">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
						<h4 style="margin:0; font-size:13px; font-weight:800; color:#0f172a;">🏆 Winner History</h4>
						<button id="clear-winners-btn" style="background:none; border:none; color:#94a3b8; font-size:11px; font-weight:700; cursor:pointer; padding:0;">Clear</button>
					</div>
					<div id="winners-history-list" style="max-height:160px; overflow-y:auto; font-size:12px; color:#475569; space-y:6px;">
						<div style="color:#94a3b8; font-style:italic; font-size:11px;">No winners drawn yet.</div>
					</div>
				</div>

			</div>

			<!-- Right: Canvas Stage Arena -->
			<div id="stage-arena" style="background:radial-gradient(ellipse at center, #181033 0%, #0c081a 60%, #06040d 100%); border:1px solid #1d2636; border-radius:22px; padding:35px; display:flex; flex-direction:column; align-items:center; justify-content:center; position:relative; min-height:620px; box-shadow:0 20px 50px rgba(0,0,0,0.5); overflow:hidden;">
				
				<!-- Subtle Ambient Background Grid -->
				<div style="position:absolute; inset:0; background-image:linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px); background-size:36px 36px; pointer-events:none;"></div>

				<!-- Wheel Outer Container -->
				<div style="position:relative; width:480px; height:480px; max-width:90vw; max-height:90vw; display:flex; align-items:center; justify-content:center; z-index:10;">
					
					<!-- Top Center Pointer Indicator -->
					<div style="position:absolute; top:-14px; left:50%; transform:translateX(-50%); z-index:30; filter:drop-shadow(0 6px 12px rgba(0,0,0,0.6));">
						<svg width="34" height="42" viewBox="0 0 34 42" fill="none">
							<path d="M17 42L2 10C-1 5 2 0 8 0H26C32 0 35 5 32 10L17 42Z" fill="#ffcc00" />
							<path d="M17 36L6 10C4 6 6 3 10 3H24C28 3 30 6 28 10L17 36Z" fill="#ffffff" />
						</svg>
					</div>

					<!-- HTML5 Physics Canvas -->
					<canvas id="wheel-canvas" width="960" height="960" style="width:480px; height:480px; border-radius:50%; box-shadow:0 0 50px rgba(147,83,255,0.35), 0 0 0 10px rgba(147,83,255,0.2), 0 0 0 14px #1d2636;"></canvas>

					<!-- Center Action Spin Button -->
					<button id="spin-wheel-btn" style="position:absolute; width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg, #9353ff 0%, #6d28d9 100%); color:#ffffff; border:4px solid #ffffff; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; font-weight:900; font-size:16px; cursor:pointer; z-index:25; box-shadow:0 6px 20px rgba(0,0,0,0.6), 0 0 25px rgba(147,83,255,0.6); transition:transform 0.15s ease; text-transform:uppercase; letter-spacing:1px;">
						SPIN
					</button>

				</div>

				<!-- Current Prize Banner under Wheel -->
				<div style="margin-top:25px; text-align:center; z-index:10;">
					<div style="font-size:11px; font-family:monospace; color:#a855f7; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:2px;">
						CURRENT DRAW PRIZE
					</div>
					<div id="stage-prize-title" style="font-size:18px; font-weight:800; color:#ffffff;">
						Official AWS T-Shirt &amp; Swag Kit 🎁
					</div>
				</div>

			</div>

		</div>

		<!-- WINNER CELEBRATION MODAL -->
		<div id="winner-modal" style="display:none; position:fixed; inset:0; background:rgba(6,4,13,0.88); backdrop-filter:blur(12px); z-index:999999; align-items:center; justify-content:center; padding:20px;">
			<div style="background:#ffffff; border:3px solid #9353ff; border-radius:28px; padding:35px; max-width:480px; width:100%; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.5); position:relative;">
				
				<div style="width:70px; height:70px; border-radius:22px; background:linear-gradient(135deg, #ad5cff, #7928ca); color:#ffffff; font-size:34px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto; box-shadow:0 10px 25px rgba(147,83,255,0.4);">
					🎉
				</div>

				<div style="font-family:monospace; font-size:11px; font-weight:800; color:#7928ca; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
					WINNER ANNOUNCEMENT
				</div>

				<h2 id="winner-name-text" style="margin:0 0 6px 0; font-size:26px; font-weight:900; color:#0f172a;">
					Candidate Name
				</h2>

				<div id="winner-detail-text" style="font-size:13px; color:#64748b; margin-bottom:18px; font-weight:600;">
					Registration Code: #1024 • Pro Member
				</div>

				<!-- Prize Ribbon Box -->
				<div style="background:#f5f3ff; border:2px dashed #9353ff; border-radius:16px; padding:14px; margin-bottom:22px;">
					<div style="font-size:11px; font-weight:700; color:#7928ca; text-transform:uppercase; margin-bottom:2px;">Awarded Swag</div>
					<div id="winner-swag-title" style="font-size:16px; font-weight:800; color:#1e1b4b;">AWS T-Shirt & Swag Kit</div>
				</div>

				<div style="display:flex; gap:10px;">
					<button id="close-winner-modal-btn" style="flex:1; background:#0f172a; color:#ffffff; border:none; padding:14px; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; transition:all 0.15s ease;">
						Awesome! Continue Draws ➔
					</button>
				</div>
			</div>
		</div>

	</div>

	<!-- Canvas Wheel Physics & Web Audio Engine Script -->
	<script>
	(function() {
		const canvas = document.getElementById('wheel-canvas');
		if (!canvas) return;
		const ctx = canvas.getContext('2d');
		
		let candidates = [];
		let currentRotation = 0;
		let isSpinning = false;
		let spinVelocity = 0;
		let deceleration = 0.985;
		let audioCtx = null;

		// Sound FX Generator via Web Audio API (0 KB audio dependencies)
		function playTickSound() {
			const soundOn = document.getElementById('sound-effects-toggle').checked;
			if (!soundOn) return;
			try {
				if (!audioCtx) {
					audioCtx = new (window.AudioContext || window.webkitAudioContext)();
				}
				const osc = audioCtx.createOscillator();
				const gain = audioCtx.createGain();
				osc.type = 'triangle';
				osc.frequency.setValueAtTime(450, audioCtx.currentTime);
				osc.frequency.exponentialRampToValueAtTime(120, audioCtx.currentTime + 0.04);
				gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
				gain.gain.linearRampToValueAtTime(0.01, audioCtx.currentTime + 0.04);
				osc.connect(gain);
				gain.connect(audioCtx.destination);
				osc.start();
				osc.stop(audioCtx.currentTime + 0.04);
			} catch(e) {}
		}

		function playWinSound() {
			const soundOn = document.getElementById('sound-effects-toggle').checked;
			if (!soundOn) return;
			try {
				if (!audioCtx) {
					audioCtx = new (window.AudioContext || window.webkitAudioContext)();
				}
				const freqs = [330, 392, 493, 587, 659];
				freqs.forEach((freq, idx) => {
					setTimeout(() => {
						const osc = audioCtx.createOscillator();
						const gain = audioCtx.createGain();
						osc.type = 'sine';
						osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
						gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
						gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
						osc.connect(gain);
						gain.connect(audioCtx.destination);
						osc.start();
						osc.stop(audioCtx.currentTime + 0.35);
					}, idx * 75);
				});
			} catch(e) {}
		}

		// Color Palette for Slices
		const sliceColors = [
			'#9353ff', '#1f1638', '#7928ca', '#18122c',
			'#ad5cff', '#140e26', '#6d28d9', '#241a45'
		];

		// Draw Wheel
		function drawWheel() {
			const width = canvas.width;
			const height = canvas.height;
			const centerX = width / 2;
			const centerY = height / 2;
			const radius = (width / 2) - 30;

			ctx.clearRect(0, 0, width, height);

			if (candidates.length === 0) {
				// Empty placeholder wheel
				ctx.beginPath();
				ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
				ctx.fillStyle = '#1e1438';
				ctx.fill();
				ctx.lineWidth = 12;
				ctx.strokeStyle = '#9353ff';
				ctx.stroke();

				ctx.fillStyle = '#94a3b8';
				ctx.font = 'bold 32px sans-serif';
				ctx.textAlign = 'center';
				ctx.textBaseline = 'middle';
				ctx.fillText('No Candidates Loaded', centerX, centerY);
				return;
			}

			const sliceAngle = (2 * Math.PI) / candidates.length;

			for (let i = 0; i < candidates.length; i++) {
				const startAngle = currentRotation + (i * sliceAngle);
				const endAngle = startAngle + sliceAngle;

				// Slice background
				ctx.beginPath();
				ctx.moveTo(centerX, centerY);
				ctx.arc(centerX, centerY, radius, startAngle, endAngle);
				ctx.closePath();
				ctx.fillStyle = sliceColors[i % sliceColors.length];
				ctx.fill();
				ctx.lineWidth = 2;
				ctx.strokeStyle = 'rgba(255,255,255,0.15)';
				ctx.stroke();

				// Slice text
				ctx.save();
				ctx.translate(centerX, centerY);
				ctx.rotate(startAngle + (sliceAngle / 2));
				ctx.textAlign = 'right';
				ctx.fillStyle = '#ffffff';
				
				// Dynamic font sizing based on slice count
				let fontSize = candidates.length > 30 ? 18 : (candidates.length > 15 ? 24 : 30);
				ctx.font = `bold ${fontSize}px sans-serif`;
				
				let displayName = candidates[i].name;
				if (displayName.length > 18) {
					displayName = displayName.substring(0, 16) + '..';
				}
				ctx.fillText(displayName, radius - 40, fontSize / 3);
				ctx.restore();
			}

			// Outer Border Rim
			ctx.beginPath();
			ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
			ctx.lineWidth = 16;
			ctx.strokeStyle = '#9353ff';
			ctx.stroke();
		}

		// Fetch Candidates Ajax
		function loadCandidates() {
			const filterType = document.getElementById('filter-type-select').value;
			const eventId = document.getElementById('event-id-select').value;
			const badge = document.getElementById('candidate-count-badge');

			if (filterType === 'manual') {
				document.getElementById('manual-paste-container').style.display = 'block';
				document.getElementById('event-select-container').style.display = 'none';
				const text = document.getElementById('manual-names-input').value.trim();
				const lines = text ? text.split('\n').map(l => l.trim()).filter(Boolean) : [];
				candidates = lines.map((name, idx) => ({
					id: 'm_' + idx,
					name: name,
					email: '',
					code: '#' + (idx + 1),
					tag: 'Manual Entry'
				}));
				badge.textContent = candidates.length + ' Loaded';
				drawWheel();
				return;
			} else {
				document.getElementById('manual-paste-container').style.display = 'none';
				document.getElementById('event-select-container').style.display = 'block';
			}

			badge.textContent = 'Loading...';

			fetch(ajaxurl + `?action=aws_sbg_get_wheel_candidates&filter_type=${encodeURIComponent(filterType)}&event_id=${encodeURIComponent(eventId)}`)
				.then(res => res.json())
				.then(data => {
					if (data.success && data.data.candidates) {
						candidates = data.data.candidates;
						badge.textContent = candidates.length + ' Loaded';
						drawWheel();
					} else {
						candidates = [];
						badge.textContent = '0 Loaded';
						drawWheel();
					}
				})
				.catch(err => {
					badge.textContent = 'Error';
				});
		}

		// Spin Physics Loop
		let lastSliceIndex = -1;
		function animateSpin() {
			if (!isSpinning) return;

			currentRotation += spinVelocity;
			currentRotation = currentRotation % (2 * Math.PI);
			spinVelocity *= deceleration;

			// Ticker audio detection
			if (candidates.length > 0) {
				const sliceAngle = (2 * Math.PI) / candidates.length;
				const currentTopAngle = ( (3 * Math.PI / 2) - currentRotation + (4 * Math.PI) ) % (2 * Math.PI);
				const currentSliceIndex = Math.floor(currentTopAngle / sliceAngle) % candidates.length;
				if (currentSliceIndex !== lastSliceIndex) {
					playTickSound();
					lastSliceIndex = currentSliceIndex;
				}
			}

			drawWheel();

			if (spinVelocity < 0.002) {
				isSpinning = false;
				spinVelocity = 0;
				announceWinner();
			} else {
				requestAnimationFrame(animateSpin);
			}
		}

		// Trigger Spin
		function startSpin() {
			if (isSpinning || candidates.length === 0) return;
			isSpinning = true;
			// Random initial velocity between 0.35 and 0.55 rad/frame
			spinVelocity = 0.38 + (Math.random() * 0.20);
			// Random deceleration between 0.982 and 0.988 for unpredictability
			deceleration = 0.982 + (Math.random() * 0.006);
			animateSpin();
		}

		// Announce Winner
		function announceWinner() {
			if (candidates.length === 0) return;

			const sliceAngle = (2 * Math.PI) / candidates.length;
			// Top pointer is at 270 degrees (3 * Math.PI / 2)
			const pointerAngle = ( (3 * Math.PI / 2) - currentRotation + (4 * Math.PI) ) % (2 * Math.PI);
			const winningIndex = Math.floor(pointerAngle / sliceAngle) % candidates.length;
			const winner = candidates[winningIndex];

			if (!winner) return;

			playWinSound();

			const swagTitle = document.getElementById('swag-prize-input').value;
			document.getElementById('winner-name-text').textContent = winner.name;
			document.getElementById('winner-detail-text').textContent = `${winner.code} • ${winner.email ? winner.email + ' • ' : ''}${winner.tag}`;
			document.getElementById('winner-swag-title').textContent = swagTitle;
			
			const modal = document.getElementById('winner-modal');
			modal.style.display = 'flex';

			// Add to history log
			const historyList = document.getElementById('winners-history-list');
			const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
			const entry = document.createElement('div');
			entry.style.cssText = 'padding:6px 8px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:5px;';
			entry.innerHTML = `<strong>${winner.name}</strong> <span style="color:#64748b;">(${winner.code})</span> - <span style="color:#7928ca; font-weight:700;">${swagTitle}</span> <span style="font-size:10px; color:#94a3b8; float:right;">${timeStr}</span>`;
			
			if (historyList.querySelector('div[style*="italic"]')) {
				historyList.innerHTML = '';
			}
			historyList.prepend(entry);

			// Remove winner from wheel if toggled
			const removeWinner = document.getElementById('remove-winner-toggle').checked;
			if (removeWinner) {
				candidates.splice(winningIndex, 1);
				document.getElementById('candidate-count-badge').textContent = candidates.length + ' Loaded';
			}
		}

		// Event Listeners
		document.getElementById('spin-wheel-btn').addEventListener('click', startSpin);
		document.getElementById('fetch-candidates-btn').addEventListener('click', loadCandidates);
		document.getElementById('filter-type-select').addEventListener('change', loadCandidates);
		document.getElementById('event-id-select').addEventListener('change', loadCandidates);
		document.getElementById('manual-names-input').addEventListener('input', loadCandidates);

		document.getElementById('swag-prize-input').addEventListener('input', function(e) {
			document.getElementById('stage-prize-title').textContent = e.target.value;
		});

		document.getElementById('close-winner-modal-btn').addEventListener('click', function() {
			document.getElementById('winner-modal').style.display = 'none';
			drawWheel();
		});

		document.getElementById('clear-winners-btn').addEventListener('click', function() {
			document.getElementById('winners-history-list').innerHTML = '<div style="color:#94a3b8; font-style:italic; font-size:11px;">No winners drawn yet.</div>';
		});

		// Fullscreen Projector Toggle
		document.getElementById('fullscreen-btn').addEventListener('click', function() {
			const arena = document.getElementById('stage-arena');
			if (!document.fullscreenElement) {
				arena.requestFullscreen().catch(err => {});
			} else {
				document.exitFullscreen().catch(err => {});
			}
		});

		// Initial load
		loadCandidates();
	})();
	</script>
	<?php
}
