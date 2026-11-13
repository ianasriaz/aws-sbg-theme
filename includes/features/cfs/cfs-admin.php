<?php
/**
 * AWS SBG Call for Speakers — Admin Dashboard & Review Screen
 * File: cfs-admin.php
 *
 * Proposal review table, status changers, WhatsApp outreach links,
 * CSV export, and event metabox integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================================
 * 1. ADMIN MENU REGISTRATION & ROW ACTIONS
 * ============================================================================ */

function aws_sbg_cfs_register_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'Call for Speakers Proposals',
		'Call for Speakers',
		'manage_options',
		'aws-cfs-submissions',
		'aws_sbg_cfs_render_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_cfs_register_admin_menu', 24 );

function aws_sbg_cfs_row_actions( $actions, $post ) {
	if ( 'aws_invite_event' === $post->post_type ) {
		$actions['cfs_proposals'] = '<a href="' . esc_url( admin_url( 'admin.php?page=aws-cfs-submissions&event_id=' . $post->ID ) ) . '">View CFS Pitches</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'aws_sbg_cfs_row_actions', 10, 2 );

/* ============================================================================
 * 2. CFS METABOX ON INVITATION EVENTS
 * ============================================================================ */

function aws_sbg_cfs_meta_boxes() {
	add_meta_box(
		'aws_cfs_settings',
		'Call for Speakers (CFS) Intake Settings',
		'aws_sbg_cfs_render_event_metabox',
		'aws_invite_event',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'aws_sbg_cfs_meta_boxes' );

function aws_sbg_cfs_render_event_metabox( $post ) {
	wp_nonce_field( 'aws_sbg_cfs_save_meta', 'aws_sbg_cfs_meta_nonce' );
	$m = aws_sbg_cfs_get_event_meta( $post->ID );
	$slug = $post->post_name ? $post->post_name : $post->ID;
	$speak_url = home_url( '/speak/?event=' . $slug );
	?>
	<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 16px;margin-bottom:16px;">
		<span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:4px;">Public Pitch URL</span>
		<div style="display:flex;align-items:center;gap:10px;">
			<code style="flex:1;background:#ffffff;border:1px solid #cbd5e1;padding:6px 10px;border-radius:4px;font-size:13px;color:#0f172a;word-break:break-all;"><?php echo esc_html( $speak_url ); ?></code>
			<a href="<?php echo esc_url( $speak_url ); ?>" target="_blank" class="button button-secondary">Open</a>
		</div>
	</div>

	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">CFS Intake Status</label>
			<select name="cfs_status" style="width:100%;">
				<option value="open" <?php selected( $m['cfs_status'], 'open' ); ?>>● Open (Accepting pitches)</option>
				<option value="closed" <?php selected( $m['cfs_status'], 'closed' ); ?>>○ Closed</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">CFS Deadline (optional)</label>
			<input type="datetime-local" name="cfs_deadline" value="<?php echo esc_attr( $m['cfs_deadline'] ); ?>" style="width:100%;">
		</div>
	</div>

	<div style="margin-bottom:14px;">
		<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Closed Notice (shown when CFS is closed)</label>
		<textarea name="cfs_closed_msg" rows="2" style="width:100%;font-size:12px;"><?php echo esc_textarea( $m['cfs_closed_msg'] ); ?></textarea>
	</div>
	<?php
}

function aws_sbg_cfs_save_event_meta( $post_id ) {
	if ( ! isset( $_POST['aws_sbg_cfs_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aws_sbg_cfs_meta_nonce'] ) ), 'aws_sbg_cfs_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['cfs_status'] ) ) {
		update_post_meta( $post_id, 'cfs_status', sanitize_key( wp_unslash( $_POST['cfs_status'] ) ) );
	}
	if ( isset( $_POST['cfs_deadline'] ) ) {
		update_post_meta( $post_id, 'cfs_deadline', sanitize_text_field( wp_unslash( $_POST['cfs_deadline'] ) ) );
	}
	if ( isset( $_POST['cfs_closed_msg'] ) ) {
		update_post_meta( $post_id, 'cfs_closed_msg', sanitize_textarea_field( wp_unslash( $_POST['cfs_closed_msg'] ) ) );
	}
}
add_action( 'save_post_aws_invite_event', 'aws_sbg_cfs_save_event_meta' );

/* ============================================================================
 * 3. STANDALONE /SPEAK PAGE AUTO-CREATION
 * ============================================================================ */

function aws_sbg_cfs_maybe_create_speak_page() {
	if ( ! is_admin() ) {
		return;
	}
	$existing = get_page_by_path( 'speak' );
	if ( $existing ) {
		return;
	}

	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => 'Call for Speakers',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_name'    => 'speak',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-speak.php' );
	}
	flush_rewrite_rules();
}
add_action( 'admin_init', 'aws_sbg_cfs_maybe_create_speak_page' );

/* ============================================================================
 * 4. ADMIN PROPOSALS DASHBOARD PAGE
 * ============================================================================ */

function aws_sbg_cfs_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_cfs_submissions';

	// Events list for filter
	$events = get_posts( array(
		'post_type'      => 'aws_invite_event',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$current_event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
	if ( ! $current_event_id && ! empty( $events ) ) {
		$current_event_id = $events[0]->ID;
	}

	$current_status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all';
	$current_track  = isset( $_GET['track'] ) ? sanitize_key( wp_unslash( $_GET['track'] ) ) : 'all';
	$search_query   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

	// Build Query
	$where = '1=1';
	$args  = array();

	if ( $current_event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $current_event_id;
	}

	if ( 'all' !== $current_status && array_key_exists( $current_status, aws_sbg_cfs_statuses() ) ) {
		$where  .= ' AND status = %s';
		$args[] = $current_status;
	}

	if ( 'all' !== $current_track && array_key_exists( $current_track, aws_sbg_cfs_tracks() ) ) {
		$where  .= ' AND track = %s';
		$args[] = $current_track;
	}

	if ( '' !== $search_query ) {
		$where  .= ' AND (name LIKE %s OR email LIKE %s OR talk_title LIKE %s OR organization LIKE %s)';
		$like   = '%' . $wpdb->esc_like( $search_query ) . '%';
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
	}

	$sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC";
	$rows = ! empty( $args ) ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

	$counts = aws_sbg_cfs_get_counts( $current_event_id );
	$tracks = aws_sbg_cfs_tracks();
	$formats = aws_sbg_cfs_formats();
	$levels = aws_sbg_cfs_levels();
	$statuses = aws_sbg_cfs_statuses();

	$event_map = array();
	foreach ( $events as $ev ) {
		$event_map[ (int) $ev->ID ] = array(
			'title' => $ev->post_title,
			'slug'  => $ev->post_name ? $ev->post_name : $ev->ID,
		);
	}

	$current_event = get_post( $current_event_id );
	$current_event_slug = ( $current_event && $current_event->post_name ) ? $current_event->post_name : $current_event_id;
	$notice = isset( $_GET['cfs_notice'] ) ? sanitize_key( wp_unslash( $_GET['cfs_notice'] ) ) : '';

	?>
	<div class="wrap aws-cfs-admin" style="max-width:1360px;margin-top:20px;">
		<style>
			.aws-cfs-admin .aws-cfs-card { background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 1px 2px rgba(15,23,42,0.04); }
			.aws-cfs-admin .aws-cfs-table th { font-weight:700; color:#334155; font-size:12px; text-transform:uppercase; letter-spacing:0.04em; background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:12px 14px; }
			.aws-cfs-admin .aws-cfs-table td { padding:14px; vertical-align:top; border-bottom:1px solid #f1f5f9; }
			.aws-cfs-admin .aws-cfs-pitch-preview { font-size:13px; color:#334155; line-height:1.55; background:#f8fafc; padding:10px 12px; border-radius:6px; border:1px solid #e2e8f0; white-space:pre-wrap; text-align:left; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
			.aws-cfs-admin .aws-cfs-select-status { height:32px; line-height:1.2; padding:4px 24px 4px 10px; font-size:12px; font-weight:700; border-radius:6px; border:1px solid rgba(15,23,42,0.12); width:100%; cursor:pointer; }
			.aws-cfs-drawer-backdrop { position:fixed; inset:0; background:rgba(15,23,42,0.48); backdrop-filter:blur(2px); z-index:100000; display:none; justify-content:flex-end; }
			.aws-cfs-drawer-backdrop.is-open { display:flex; }
			.aws-cfs-drawer { width:100%; max-width:640px; height:100%; background:#ffffff; box-shadow:-12px 0 36px rgba(15,23,42,0.18); display:flex; flex-direction:column; overflow:hidden; }
			.aws-cfs-drawer-head { padding:20px 24px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
			.aws-cfs-drawer-body { flex:1; overflow-y:auto; padding:22px 24px; }
			.aws-cfs-drawer-section { margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid #f1f5f9; }
			.aws-cfs-drawer-section:last-child { border-bottom:0; margin-bottom:0; padding-bottom:0; }
			.aws-cfs-drawer-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-bottom:4px; display:block; }
			.aws-cfs-drawer-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
			.aws-cfs-drawer-field { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 12px; }
			.aws-cfs-drawer-val { font-size:13px; color:#0f172a; font-weight:500; word-break:break-word; }
		</style>
		<?php if ( 'saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="margin:0 0 14px 0;"><p>Proposal review &amp; notes updated.</p></div>
		<?php elseif ( 'deleted' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="margin:0 0 14px 0;"><p>Proposal deleted.</p></div>
		<?php endif; ?>
		<?php
		if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) {
			aws_sbg_render_admin_nav_tabs( 'cfs' );
		}
		?>

		<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
			<div>
				<h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0 0 4px 0;">Call for Speakers Proposals</h1>
				<p style="margin:0;color:#64748b;font-size:13px;">Review and evaluate prospective speaker pitches, track statuses, and reach out via WhatsApp.</p>
			</div>
			<div style="display:flex;align-items:center;gap:10px;">
				<?php if ( $current_event_id ) : ?>
					<a href="<?php echo esc_url( home_url( '/speak/?event=' . $current_event_slug ) ); ?>" target="_blank" class="button button-secondary">
						Public Pitch Form &rarr;
					</a>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_cfs_export_csv&event_id=' . $current_event_id ), 'cfs_export' ) ); ?>" class="button button-primary">
						Export Proposals (CSV)
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Event Selector & Search Filters -->
		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:20px;">
			<form method="GET" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
				<input type="hidden" name="page" value="aws-cfs-submissions">
				<input type="hidden" name="status" value="<?php echo esc_attr( $current_status ); ?>">

				<div style="flex:1;min-width:220px;">
					<select name="event_id" onchange="this.form.submit()" style="width:100%;padding:6px 10px;font-weight:600;">
						<option value="0">All Events</option>
						<?php foreach ( $events as $ev ) : ?>
							<option value="<?php echo esc_attr( $ev->ID ); ?>" <?php selected( $current_event_id, $ev->ID ); ?>>
								<?php echo esc_html( $ev->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div style="min-width:180px;">
					<select name="track" onchange="this.form.submit()" style="width:100%;padding:6px 10px;">
						<option value="all">All Tracks</option>
						<?php foreach ( $tracks as $k => $lbl ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $current_track, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div style="flex:1;min-width:200px;">
					<input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search speaker, email, title..." style="width:100%;padding:6px 10px;">
				</div>

				<div>
					<button type="submit" class="button button-secondary">Filter</button>
					<?php if ( $search_query || 'all' !== $current_track || 'all' !== $current_status ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-cfs-submissions&event_id=' . $current_event_id ) ); ?>" class="button button-link">Reset</a>
					<?php endif; ?>
				</div>
			</form>

			<!-- Status Pills -->
			<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;">
				<?php
				$status_items = array( 'all' => 'All Proposals' ) + $statuses;
				foreach ( $status_items as $s_key => $s_label ) :
					$count = isset( $counts[ $s_key ] ) ? $counts[ $s_key ] : 0;
					$is_active = ( $current_status === $s_key );
					$url = add_query_arg( array(
						'page'     => 'aws-cfs-submissions',
						'event_id' => $current_event_id,
						'status'   => $s_key,
						'track'    => $current_track,
						's'        => $search_query,
					), admin_url( 'admin.php' ) );
					?>
					<a href="<?php echo esc_url( $url ); ?>" style="text-decoration:none;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px;<?php echo $is_active ? 'background:#0f172a;color:#ffffff;' : 'background:#f8fafc;color:#475569;border:1px solid #e2e8f0;'; ?>">
						<span><?php echo esc_html( $s_label ); ?></span>
						<span style="font-size:11px;opacity:0.8;background:<?php echo $is_active ? '#334155' : '#e2e8f0'; ?>;color:<?php echo $is_active ? '#ffffff' : '#0f172a'; ?>;padding:1px 6px;border-radius:999px;">
							<?php echo intval( $count ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Proposals Table -->
		<div class="aws-cfs-card" style="overflow:hidden;">
			<table class="wp-list-table widefat fixed striped aws-cfs-table" style="border:0;">
				<thead>
					<tr>
						<th style="width:135px;">Ref &amp; Date</th>
						<th style="width:240px;">Speaker &amp; Contact</th>
						<th>Session Pitch</th>
						<th style="width:145px;">Status</th>
						<th style="width:165px;text-align:right;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td colspan="5" style="text-align:center;padding:48px 16px;color:#64748b;">
								<p style="font-size:15px;margin-bottom:6px;">No speaker proposals found.</p>
								<span style="font-size:12px;">Share your public pitch link to invite speakers!</span>
							</td>
						</tr>
					<?php else : ?>
						<?php
						$cfs_drawer_payloads = array();
						foreach ( $rows as $r ) :
							$row_id       = (int) $r['id'];
							$row_event_id = (int) $r['event_id'];
							$ev_info      = isset( $event_map[ $row_event_id ] ) ? $event_map[ $row_event_id ] : array( 'title' => 'AWS Event', 'slug' => $current_event_slug );
							$wa_digits    = preg_replace( '/[^0-9]/', '', $r['phone'] );
							if ( substr( $wa_digits, 0, 1 ) === '0' ) {
								$wa_digits = '92' . substr( $wa_digits, 1 );
							}
							$wa_url = $wa_digits ? ( 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode( 'Hi ' . $r['name'] . ', thank you for submitting your speaker pitch for ' . $ev_info['title'] . '!' ) ) : '';
							$status_colors = array(
								'received'     => 'background:#f1f5f9;color:#0f172a;',
								'under_review' => 'background:#fef3c7;color:#92400e;',
								'shortlisted'  => 'background:#dbeafe;color:#1e40af;',
								'accepted'     => 'background:#dcfce7;color:#166534;',
								'declined'     => 'background:#fee2e2;color:#991b1b;',
							);
							$st_style     = isset( $status_colors[ $r['status'] ] ) ? $status_colors[ $r['status'] ] : 'background:#f1f5f9;color:#0f172a;';
							$st_label     = isset( $statuses[ $r['status'] ] ) ? $statuses[ $r['status'] ] : ucfirst( $r['status'] );
							$track_lbl    = isset( $tracks[ $r['track'] ] ) ? $tracks[ $r['track'] ] : $r['track'];
							$format_lbl   = isset( $formats[ $r['format'] ] ) ? $formats[ $r['format'] ] : $r['format'];
							$level_lbl    = isset( $levels[ $r['audience_level'] ] ) ? $levels[ $r['audience_level'] ] : ( 'Level ' . $r['audience_level'] );
							$confirm_link = home_url( '/confirm/?event=' . $ev_info['slug'] . '&role=speaker' );
							$pitch_clean  = trim( (string) $r['talk_pitch'] );
							$notes_clean  = trim( (string) ( $r['admin_notes'] ?? '' ) );

							$cfs_drawer_payloads[ $row_id ] = array(
								'id'              => $row_id,
								'reference'       => $r['reference'],
								'event_title'     => $ev_info['title'],
								'created_at'      => date( 'M j, Y · g:i a', strtotime( $r['created_at'] ) ),
								'name'            => $r['name'],
								'email'           => $r['email'],
								'phone'           => $r['phone'],
								'wa_url'          => $wa_url,
								'city'            => $r['city'],
								'organization'    => $r['organization'],
								'linkedin_url'    => $r['linkedin_url'],
								'talk_title'      => $r['talk_title'],
								'talk_pitch'      => $pitch_clean,
								'track'           => $track_lbl,
								'format'          => $format_lbl,
								'audience_level'  => $level_lbl,
								'past_experience' => ( 'yes' === $r['past_experience'] ) ? 'Yes — Experienced Speaker' : 'No — First-time Speaker',
								'experience_link' => $r['experience_link'],
								'status'          => $r['status'],
								'status_label'    => $st_label,
								'status_style'    => $st_style,
								'admin_notes'     => $notes_clean,
								'ip_address'      => $r['ip_address'] ?? '',
								'confirm_link'    => $confirm_link,
							);
							?>
							<tr>
								<!-- Ref & Date -->
								<td>
									<button type="button" onclick="awsCfsOpenDrawer(<?php echo esc_attr( $row_id ); ?>)" style="background:none;border:0;padding:0;cursor:pointer;font-family:monospace;font-size:13px;font-weight:700;color:#7928ca;display:block;text-align:left;">
										<?php echo esc_html( $r['reference'] ); ?>
									</button>
									<span style="font-size:11.5px;color:#64748b;display:block;margin-top:2px;">
										<?php echo esc_html( date( 'M j, Y', strtotime( $r['created_at'] ) ) ); ?><br>
										<?php echo esc_html( date( 'g:i a', strtotime( $r['created_at'] ) ) ); ?>
									</span>
									<?php if ( 0 === $current_event_id ) : ?>
										<span style="display:inline-block;margin-top:6px;font-size:10.5px;font-weight:600;color:#475569;background:#f1f5f9;padding:2px 6px;border-radius:4px;">
											<?php echo esc_html( $ev_info['title'] ); ?>
										</span>
									<?php endif; ?>
								</td>

								<!-- Speaker Info -->
								<td>
									<strong style="font-size:14px;color:#0f172a;display:block;margin-bottom:2px;">
										<?php echo esc_html( $r['name'] ); ?>
									</strong>
									<span style="font-size:12px;color:#475569;display:block;margin-bottom:8px;">
										<?php echo esc_html( $r['organization'] ); ?> &middot; <?php echo esc_html( $r['city'] ); ?>
									</span>
									<div style="font-size:12px;line-height:1.55;color:#334155;">
										<div style="word-break:break-all;">
											<a href="mailto:<?php echo esc_attr( $r['email'] ); ?>" style="text-decoration:none;color:#0f172a;font-weight:500;">&#9993; <?php echo esc_html( $r['email'] ); ?></a>
										</div>
										<?php if ( $r['phone'] ) : ?>
											<div style="margin-top:2px;">
												<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" style="text-decoration:none;color:#15803d;font-weight:600;">&#128172; <?php echo esc_html( $r['phone'] ); ?></a>
											</div>
										<?php endif; ?>
										<?php if ( $r['linkedin_url'] ) : ?>
											<div style="margin-top:2px;">
												<a href="<?php echo esc_url( $r['linkedin_url'] ); ?>" target="_blank" style="text-decoration:none;color:#0077b5;font-weight:600;">LinkedIn Profile &nearr;</a>
											</div>
										<?php endif; ?>
									</div>
								</td>

								<!-- Session Pitch -->
								<td>
									<div style="margin-bottom:6px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
										<button type="button" onclick="awsCfsOpenDrawer(<?php echo esc_attr( $row_id ); ?>)" style="background:none;border:0;padding:0;cursor:pointer;text-align:left;font-size:14.5px;font-weight:700;color:#0f172a;">
											<?php echo esc_html( $r['talk_title'] ); ?>
										</button>
										<?php if ( '' !== $notes_clean ) : ?>
											<span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:999px;background:#fef3c7;color:#92400e;white-space:nowrap;">Reviewer Note</span>
										<?php endif; ?>
									</div>
									<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:8px;">
										<span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;background:#f5f3ff;color:#7928ca;border:1px solid #ddd6fe;">
											<?php echo esc_html( $track_lbl ); ?>
										</span>
										<span style="font-size:11px;padding:2px 7px;border-radius:4px;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
											<?php echo esc_html( $format_lbl ); ?>
										</span>
										<span style="font-size:11px;padding:2px 7px;border-radius:4px;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
											<?php echo esc_html( $level_lbl ); ?>
										</span>
										<?php if ( 'yes' === $r['past_experience'] ) : ?>
											<span style="font-size:11px;padding:2px 7px;border-radius:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
												Experienced
											</span>
										<?php else : ?>
											<span style="font-size:11px;padding:2px 7px;border-radius:4px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;">
												First-time speaker
											</span>
										<?php endif; ?>
									</div>
									<div class="aws-cfs-pitch-preview"><?php echo esc_html( $pitch_clean ); ?></div>
									<?php if ( $r['experience_link'] ) : ?>
										<div style="margin-top:6px;font-size:11.5px;">
											<span style="color:#64748b;">Past talk:</span>
											<a href="<?php echo esc_url( $r['experience_link'] ); ?>" target="_blank" style="color:#7928ca;word-break:break-all;">
												<?php echo esc_html( $r['experience_link'] ); ?>
											</a>
										</div>
									<?php endif; ?>
								</td>

								<!-- Status Selector -->
								<td>
									<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="aws_sbg_cfs_update_status">
										<input type="hidden" name="submission_id" value="<?php echo esc_attr( $row_id ); ?>">
										<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
										<input type="hidden" name="filter_status" value="<?php echo esc_attr( $current_status ); ?>">
										<input type="hidden" name="filter_track" value="<?php echo esc_attr( $current_track ); ?>">
										<input type="hidden" name="filter_s" value="<?php echo esc_attr( $search_query ); ?>">
										<?php wp_nonce_field( 'cfs_update_status_' . $row_id, 'cfs_status_nonce' ); ?>
										<select name="new_status" onchange="this.form.submit()" class="aws-cfs-select-status" style="<?php echo esc_attr( $st_style ); ?>">
											<?php foreach ( $statuses as $st_k => $st_lbl ) : ?>
												<option value="<?php echo esc_attr( $st_k ); ?>" <?php selected( $r['status'], $st_k ); ?> style="background:#ffffff;color:#0f172a;">
													<?php echo esc_html( $st_lbl ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</form>
								</td>

								<!-- Actions -->
								<td style="text-align:right;">
									<div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
										<button type="button" class="button button-secondary button-small" onclick="awsCfsOpenDrawer(<?php echo esc_attr( $row_id ); ?>)" style="font-weight:600;">
											View Full Details
										</button>
										<?php if ( 'accepted' === $r['status'] ) : ?>
											<button type="button" class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js( $confirm_link ); ?>'); alert('Speaker Confirmation link copied to clipboard!\n\n<?php echo esc_js( $confirm_link ); ?>');" title="Copy speaker confirmation onboarding link" style="color:#166534;font-weight:600;">
												Copy Confirm Link
											</button>
										<?php endif; ?>
										<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('Permanently delete proposal for <?php echo esc_js( $r['name'] ); ?>?');">
											<input type="hidden" name="action" value="aws_sbg_cfs_delete_entry">
											<input type="hidden" name="entry_id" value="<?php echo esc_attr( $row_id ); ?>">
											<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
											<input type="hidden" name="filter_status" value="<?php echo esc_attr( $current_status ); ?>">
											<input type="hidden" name="filter_track" value="<?php echo esc_attr( $current_track ); ?>">
											<input type="hidden" name="filter_s" value="<?php echo esc_attr( $search_query ); ?>">
											<?php wp_nonce_field( 'cfs_delete_' . $row_id, 'cfs_delete_nonce' ); ?>
											<button type="submit" class="button button-link-delete" style="font-size:12px;color:#dc2626;padding:0;">Delete</button>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Slide-Over Proposal Detail Drawer -->
		<div id="aws-cfs-drawer-backdrop" class="aws-cfs-drawer-backdrop" onclick="if (event.target === this) awsCfsCloseDrawer();">
			<div class="aws-cfs-drawer" role="dialog" aria-modal="true" aria-labelledby="aws-cfs-drawer-title">
				<div class="aws-cfs-drawer-head">
					<div>
						<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
							<span id="cfs-d-ref" style="font-family:monospace;font-size:13px;font-weight:700;color:#7928ca;"></span>
							<span id="cfs-d-status-badge" style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;"></span>
							<span id="cfs-d-date" style="font-size:12px;color:#64748b;"></span>
						</div>
						<h2 id="aws-cfs-drawer-title" style="margin:0;font-size:18px;font-weight:700;color:#0f172a;line-height:1.3;"></h2>
						<div id="cfs-d-event" style="font-size:12px;color:#64748b;margin-top:4px;"></div>
					</div>
					<button type="button" onclick="awsCfsCloseDrawer()" class="button button-secondary" aria-label="Close details">&times; Close</button>
				</div>

				<div class="aws-cfs-drawer-body">
					<!-- Session Details -->
					<div class="aws-cfs-drawer-section">
						<h3 style="margin:0 0 12px 0;font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.05em;">Session Proposal</h3>
						<div class="aws-cfs-drawer-grid" style="margin-bottom:14px;">
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Track</span>
								<div id="cfs-d-track" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Session Format</span>
								<div id="cfs-d-format" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Audience Level</span>
								<div id="cfs-d-level" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Speaking Experience</span>
								<div id="cfs-d-exp" class="aws-cfs-drawer-val"></div>
							</div>
						</div>
						<div class="aws-cfs-drawer-field">
							<span class="aws-cfs-drawer-label">Full Talk Pitch / Abstract</span>
							<div id="cfs-d-pitch" class="aws-cfs-drawer-val" style="white-space:pre-wrap;line-height:1.6;"></div>
						</div>
						<div class="aws-cfs-drawer-field" style="margin-top:12px;">
							<span class="aws-cfs-drawer-label">Past Talk / Video / Slides Link</span>
							<div id="cfs-d-exp-link" class="aws-cfs-drawer-val"></div>
						</div>
					</div>

					<!-- Speaker Profile & Contact -->
					<div class="aws-cfs-drawer-section">
						<h3 style="margin:0 0 12px 0;font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.05em;">Speaker Profile &amp; Contact</h3>
						<div class="aws-cfs-drawer-grid">
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Full Name</span>
								<div id="cfs-d-name" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Organization / University</span>
								<div id="cfs-d-org" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">City</span>
								<div id="cfs-d-city" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">LinkedIn Profile</span>
								<div id="cfs-d-linkedin" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Email Address</span>
								<div id="cfs-d-email" class="aws-cfs-drawer-val"></div>
							</div>
							<div class="aws-cfs-drawer-field">
								<span class="aws-cfs-drawer-label">Phone / WhatsApp</span>
								<div id="cfs-d-phone" class="aws-cfs-drawer-val"></div>
							</div>
						</div>
					</div>

					<!-- Reviewer Evaluation & Notes -->
					<div class="aws-cfs-drawer-section">
						<h3 style="margin:0 0 12px 0;font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.05em;">Evaluation &amp; Reviewer Notes</h3>
						<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="aws_sbg_cfs_save_review">
							<input type="hidden" name="submission_id" id="cfs-d-form-id" value="">
							<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
							<input type="hidden" name="filter_status" value="<?php echo esc_attr( $current_status ); ?>">
							<input type="hidden" name="filter_track" value="<?php echo esc_attr( $current_track ); ?>">
							<input type="hidden" name="filter_s" value="<?php echo esc_attr( $search_query ); ?>">
							<?php wp_nonce_field( 'cfs_save_review', 'cfs_review_nonce' ); ?>

							<div style="margin-bottom:12px;">
								<label for="cfs-d-status-select" class="aws-cfs-drawer-label">Proposal Status</label>
								<select id="cfs-d-status-select" name="new_status" style="width:100%;max-width:260px;">
									<?php foreach ( $statuses as $st_k => $st_lbl ) : ?>
										<option value="<?php echo esc_attr( $st_k ); ?>"><?php echo esc_html( $st_lbl ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div style="margin-bottom:14px;">
								<label for="cfs-d-notes" class="aws-cfs-drawer-label">Internal Reviewer Notes</label>
								<textarea id="cfs-d-notes" name="admin_notes" rows="4" style="width:100%;" placeholder="Add internal evaluation comments, feedback, or scheduling notes..."></textarea>
							</div>
							<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
								<button type="submit" class="button button-primary">Save Review &amp; Notes</button>
								<a id="cfs-d-wa-btn" href="#" target="_blank" class="button button-secondary" style="color:#15803d;">WhatsApp Speaker</a>
								<a id="cfs-d-mail-btn" href="#" class="button button-secondary">Email Speaker</a>
								<button type="button" id="cfs-d-confirm-btn" class="button button-secondary" style="color:#166534;display:none;" onclick="awsCfsCopyConfirmFromDrawer()">Copy Confirm Link</button>
							</div>
						</form>
						<div style="margin-top:12px;font-size:11px;color:#94a3b8;">
							Submission IP: <span id="cfs-d-ip"></span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
		(function() {
			var proposals = <?php echo wp_json_encode( $cfs_drawer_payloads ?? array() ); ?>;
			var activeConfirmLink = '';

			window.awsCfsOpenDrawer = function(id) {
				var p = proposals[id];
				if (!p) return;

				document.getElementById('cfs-d-ref').textContent = p.reference || '';
				var badge = document.getElementById('cfs-d-status-badge');
				badge.textContent = p.status_label || '';
				badge.setAttribute('style', 'font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;' + (p.status_style || ''));
				document.getElementById('cfs-d-date').textContent = p.created_at || '';
				document.getElementById('aws-cfs-drawer-title').textContent = p.talk_title || '(Untitled Pitch)';
				document.getElementById('cfs-d-event').textContent = p.event_title ? ('Event: ' + p.event_title) : '';

				document.getElementById('cfs-d-track').textContent = p.track || '—';
				document.getElementById('cfs-d-format').textContent = p.format || '—';
				document.getElementById('cfs-d-level').textContent = p.audience_level || '—';
				document.getElementById('cfs-d-exp').textContent = p.past_experience || '—';
				document.getElementById('cfs-d-pitch').textContent = p.talk_pitch || '—';

				var expLinkEl = document.getElementById('cfs-d-exp-link');
				expLinkEl.innerHTML = '';
				if (p.experience_link) {
					var aExp = document.createElement('a');
					aExp.href = p.experience_link;
					aExp.target = '_blank';
					aExp.rel = 'noopener';
					aExp.textContent = p.experience_link;
					expLinkEl.appendChild(aExp);
				} else {
					expLinkEl.textContent = 'Not provided';
				}

				document.getElementById('cfs-d-name').textContent = p.name || '—';
				document.getElementById('cfs-d-org').textContent = p.organization || '—';
				document.getElementById('cfs-d-city').textContent = p.city || '—';

				var liEl = document.getElementById('cfs-d-linkedin');
				liEl.innerHTML = '';
				if (p.linkedin_url) {
					var aLi = document.createElement('a');
					aLi.href = p.linkedin_url;
					aLi.target = '_blank';
					aLi.rel = 'noopener';
					aLi.textContent = p.linkedin_url;
					liEl.appendChild(aLi);
				} else {
					liEl.textContent = 'Not provided';
				}

				var emailEl = document.getElementById('cfs-d-email');
				emailEl.innerHTML = '';
				if (p.email) {
					var aMail = document.createElement('a');
					aMail.href = 'mailto:' + p.email;
					aMail.textContent = p.email;
					emailEl.appendChild(aMail);
				} else {
					emailEl.textContent = '—';
				}

				document.getElementById('cfs-d-phone').textContent = p.phone || '—';

				document.getElementById('cfs-d-form-id').value = p.id;
				document.getElementById('cfs-d-status-select').value = p.status || 'received';
				document.getElementById('cfs-d-notes').value = p.admin_notes || '';
				document.getElementById('cfs-d-ip').textContent = p.ip_address || '—';

				var waBtn = document.getElementById('cfs-d-wa-btn');
				if (p.wa_url) {
					waBtn.href = p.wa_url;
					waBtn.style.display = 'inline-block';
				} else {
					waBtn.style.display = 'none';
				}
				var mailBtn = document.getElementById('cfs-d-mail-btn');
				mailBtn.href = p.email ? ('mailto:' + p.email) : '#';

				activeConfirmLink = p.confirm_link || '';
				var confirmBtn = document.getElementById('cfs-d-confirm-btn');
				confirmBtn.style.display = (p.status === 'accepted' && activeConfirmLink) ? 'inline-block' : 'none';

				document.getElementById('aws-cfs-drawer-backdrop').classList.add('is-open');
			};

			window.awsCfsCloseDrawer = function() {
				document.getElementById('aws-cfs-drawer-backdrop').classList.remove('is-open');
			};

			window.awsCfsCopyConfirmFromDrawer = function() {
				if (!activeConfirmLink) return;
				navigator.clipboard.writeText(activeConfirmLink);
				alert('Speaker Confirmation link copied to clipboard!\n\n' + activeConfirmLink);
			};

			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') window.awsCfsCloseDrawer();
			});
		})();
		</script>
	</div>
	<?php
}

/* ============================================================================
 * 5. ADMIN ACTIONS: STATUS UPDATE, REVIEW NOTES & DELETE
 * ============================================================================ */

function aws_sbg_cfs_admin_redirect_url( $event_id, $notice = '' ) {
	$args = array(
		'page'     => 'aws-cfs-submissions',
		'event_id' => $event_id,
	);
	if ( ! empty( $_POST['filter_status'] ) ) {
		$args['status'] = sanitize_key( wp_unslash( $_POST['filter_status'] ) );
	}
	if ( ! empty( $_POST['filter_track'] ) ) {
		$args['track'] = sanitize_key( wp_unslash( $_POST['filter_track'] ) );
	}
	if ( isset( $_POST['filter_s'] ) && '' !== $_POST['filter_s'] ) {
		$args['s'] = sanitize_text_field( wp_unslash( $_POST['filter_s'] ) );
	}
	if ( '' !== $notice ) {
		$args['cfs_notice'] = $notice;
	}
	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

function aws_sbg_cfs_handle_update_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	$id = isset( $_POST['submission_id'] ) ? intval( $_POST['submission_id'] ) : 0;
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$new_status = isset( $_POST['new_status'] ) ? sanitize_key( wp_unslash( $_POST['new_status'] ) ) : '';

	check_admin_referer( 'cfs_update_status_' . $id, 'cfs_status_nonce' );

	if ( $id && array_key_exists( $new_status, aws_sbg_cfs_statuses() ) ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_cfs_submissions';
		$wpdb->update( $table, array( 'status' => $new_status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
	}

	wp_safe_redirect( aws_sbg_cfs_admin_redirect_url( $event_id, 'saved' ) );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_update_status', 'aws_sbg_cfs_handle_update_status' );

function aws_sbg_cfs_handle_save_review() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	check_admin_referer( 'cfs_save_review', 'cfs_review_nonce' );

	$id         = isset( $_POST['submission_id'] ) ? intval( $_POST['submission_id'] ) : 0;
	$event_id   = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$new_status = isset( $_POST['new_status'] ) ? sanitize_key( wp_unslash( $_POST['new_status'] ) ) : '';
	$notes      = isset( $_POST['admin_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['admin_notes'] ) ) : '';

	if ( $id ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'aws_cfs_submissions';
		$data   = array( 'admin_notes' => $notes );
		$format = array( '%s' );
		if ( array_key_exists( $new_status, aws_sbg_cfs_statuses() ) ) {
			$data['status'] = $new_status;
			$format[]       = '%s';
		}
		$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
	}

	wp_safe_redirect( aws_sbg_cfs_admin_redirect_url( $event_id, 'saved' ) );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_save_review', 'aws_sbg_cfs_handle_save_review' );

function aws_sbg_cfs_handle_delete_entry() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	$entry_id = isset( $_POST['entry_id'] ) ? intval( $_POST['entry_id'] ) : 0;
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;

	check_admin_referer( 'cfs_delete_' . $entry_id, 'cfs_delete_nonce' );

	if ( $entry_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_cfs_submissions';
		$wpdb->delete( $table, array( 'id' => $entry_id ), array( '%d' ) );
	}

	wp_safe_redirect( aws_sbg_cfs_admin_redirect_url( $event_id, 'deleted' ) );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_delete_entry', 'aws_sbg_cfs_handle_delete_entry' );

/* ============================================================================
 * 6. CSV EXPORT
 * ============================================================================ */

function aws_sbg_cfs_handle_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	check_admin_referer( 'cfs_export' );

	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

	global $wpdb;
	$table = $wpdb->prefix . 'aws_cfs_submissions';

	$where = '1=1';
	$args  = array();
	if ( $event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $event_id;
	}

	$sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC";
	$rows = ! empty( $args ) ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

	$tracks = aws_sbg_cfs_tracks();
	$formats = aws_sbg_cfs_formats();
	$levels = aws_sbg_cfs_levels();

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=cfs-proposals-' . $event_id . '-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fputs( $output, "\xEF\xBB\xBF" ); // UTF-8 BOM

	fputcsv( $output, array(
		'Reference',
		'Name',
		'Email',
		'Phone',
		'City',
		'Organization',
		'LinkedIn URL',
		'Talk Title',
		'Track',
		'Format',
		'Audience Level',
		'Talk Pitch',
		'Past Speaking Experience',
		'Past Experience Link',
		'Status',
		'Reviewer Notes',
		'Submitted At',
	) );

	foreach ( $rows as $r ) {
		$track_name = isset( $tracks[ $r['track'] ] ) ? $tracks[ $r['track'] ] : $r['track'];
		$format_name = isset( $formats[ $r['format'] ] ) ? $formats[ $r['format'] ] : $r['format'];
		$level_name = isset( $levels[ $r['audience_level'] ] ) ? $levels[ $r['audience_level'] ] : $r['audience_level'];

		fputcsv( $output, array(
			$r['reference'],
			$r['name'],
			$r['email'],
			$r['phone'],
			$r['city'],
			$r['organization'],
			$r['linkedin_url'],
			$r['talk_title'],
			$track_name,
			$format_name,
			$level_name,
			$r['talk_pitch'],
			$r['past_experience'],
			$r['experience_link'],
			$r['status'],
			$r['admin_notes'] ?? '',
			$r['created_at'],
		) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_export_csv', 'aws_sbg_cfs_handle_export_csv' );
