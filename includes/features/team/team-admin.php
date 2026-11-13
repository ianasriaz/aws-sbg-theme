<?php
/**
 * AWS SBG Team Hiring & Volunteer System — Admin Dashboard
 * File: team-admin.php
 *
 * Candidate review table, multi-campus filtering, status changers,
 * 1-click WhatsApp outreach, CSV export, and event metabox controls.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================================
 * 1. ADMIN MENU REGISTRATION & ROW ACTIONS
 * ============================================================================ */

function aws_sbg_team_register_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'Team & Volunteers Roster',
		'Team & Volunteers',
		'manage_options',
		'aws-team-hiring',
		'aws_sbg_team_render_admin_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_team_register_admin_menu', 25 );

function aws_sbg_team_row_actions( $actions, $post ) {
	if ( 'aws_invite_event' === $post->post_type ) {
		$actions['team_applications'] = '<a href="' . esc_url( admin_url( 'admin.php?page=aws-team-hiring&event_id=' . $post->ID ) ) . '">View Team Applications</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'aws_sbg_team_row_actions', 10, 2 );

/* ============================================================================
 * 2. EVENT METABOX INTEGRATION (SCOPE & PER-ROLE CONTROLS)
 * ============================================================================ */

function aws_sbg_team_meta_boxes() {
	add_meta_box(
		'aws_team_settings',
		'Team Hiring & Crew Intake Settings',
		'aws_sbg_team_render_event_metabox',
		'aws_invite_event',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'aws_sbg_team_meta_boxes' );

function aws_sbg_team_render_event_metabox( $post ) {
	wp_nonce_field( 'aws_sbg_team_save_meta', 'aws_sbg_team_meta_nonce' );
	$m = aws_sbg_team_get_event_meta( $post->ID );
	$slug = $post->post_name ? $post->post_name : $post->ID;
	$join_url = home_url( '/join/?event=' . $slug );
	?>
	<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 16px;margin-bottom:16px;">
		<span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:4px;">Public Application URL</span>
		<div style="display:flex;align-items:center;gap:10px;">
			<code style="flex:1;background:#ffffff;border:1px solid #cbd5e1;padding:6px 10px;border-radius:4px;font-size:13px;color:#0f172a;word-break:break-all;"><?php echo esc_html( $join_url ); ?></code>
			<a href="<?php echo esc_url( $join_url ); ?>" target="_blank" class="button button-secondary">Open</a>
		</div>
	</div>

	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Roles Included in this Form</label>
			<select name="team_scope" style="width:100%;font-weight:600;">
				<option value="both" <?php selected( $m['team_scope'], 'both' ); ?>>● Both (Event Crew &amp; Core Team)</option>
				<option value="event_crew" <?php selected( $m['team_scope'], 'event_crew' ); ?>>○ Event Crew Only (Day-of-Event Volunteers)</option>
				<option value="core_team" <?php selected( $m['team_scope'], 'core_team' ); ?>>○ Core Team Only (Planning &amp; Leadership Leads)</option>
			</select>
			<p style="font-size:11px;color:#64748b;margin:4px 0 0;">Selecting a single role hides the role toggle and streamlines the form for candidates.</p>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Application Deadline (optional)</label>
			<input type="datetime-local" name="team_deadline" value="<?php echo esc_attr( $m['team_deadline'] ); ?>" style="width:100%;">
		</div>
	</div>

	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding-top:14px;border-top:1px solid #e2e8f0;">
		<!-- Event Crew Role Controls -->
		<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;">
			<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
				<strong style="font-size:13px;color:#0f172a;">Event Crew (Volunteers)</strong>
				<select name="role_status_event_crew" style="font-size:12px;font-weight:600;">
					<option value="open" <?php selected( $m['role_status_event_crew'], 'open' ); ?>>● Open</option>
					<option value="closed" <?php selected( $m['role_status_event_crew'], 'closed' ); ?>>○ Closed</option>
				</select>
			</div>
			<label style="display:block;font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;">Custom Closed Notice</label>
			<input type="text" name="role_msg_event_crew" value="<?php echo esc_attr( $m['role_msg_event_crew'] ); ?>" style="width:100%;font-size:12px;">
		</div>

		<!-- Core Team Role Controls -->
		<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;">
			<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
				<strong style="font-size:13px;color:#0f172a;">Core Team</strong>
				<select name="role_status_core_team" style="font-size:12px;font-weight:600;">
					<option value="open" <?php selected( $m['role_status_core_team'], 'open' ); ?>>● Open</option>
					<option value="closed" <?php selected( $m['role_status_core_team'], 'closed' ); ?>>○ Closed</option>
				</select>
			</div>
			<label style="display:block;font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;">Custom Closed Notice</label>
			<input type="text" name="role_msg_core_team" value="<?php echo esc_attr( $m['role_msg_core_team'] ); ?>" style="width:100%;font-size:12px;">
		</div>
	</div>
	<?php
}

function aws_sbg_team_save_event_meta( $post_id ) {
	if ( ! isset( $_POST['aws_sbg_team_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aws_sbg_team_meta_nonce'] ) ), 'aws_sbg_team_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$keys = array(
		'team_scope'             => 'sanitize_key',
		'team_deadline'          => 'sanitize_text_field',
		'role_status_event_crew' => 'sanitize_key',
		'role_msg_event_crew'    => 'sanitize_text_field',
		'role_status_core_team'  => 'sanitize_key',
		'role_msg_core_team'     => 'sanitize_text_field',
	);

	foreach ( $keys as $k => $sanitizer ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, call_user_func( $sanitizer, wp_unslash( $_POST[ $k ] ) ) );
		}
	}
}
add_action( 'save_post_aws_invite_event', 'aws_sbg_team_save_event_meta' );

/* ============================================================================
 * 3. STANDALONE /JOIN PAGE AUTO-CREATION
 * ============================================================================ */

function aws_sbg_team_maybe_create_join_page() {
	if ( ! is_admin() ) {
		return;
	}
	$existing = get_page_by_path( 'join' );
	if ( $existing ) {
		return;
	}

	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => 'Join the Team',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_name'    => 'join',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-join.php' );
	}
	flush_rewrite_rules();
}
add_action( 'admin_init', 'aws_sbg_team_maybe_create_join_page' );

/* ============================================================================
 * 4. ADMIN CANDIDATE ROSTER PAGE
 * ============================================================================ */

function aws_sbg_team_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_team_applications';

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

	$current_role   = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : 'all';
	$current_status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all';
	$search_query   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

	// Build Query
	$where = '1=1';
	$args  = array();

	if ( $current_event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $current_event_id;
	}

	if ( 'all' !== $current_role && in_array( $current_role, array( 'event_crew', 'core_team' ), true ) ) {
		$where  .= ' AND role = %s';
		$args[] = $current_role;
	}

	if ( 'all' !== $current_status && array_key_exists( $current_status, aws_sbg_team_statuses() ) ) {
		$where  .= ' AND status = %s';
		$args[] = $current_status;
	}

	if ( '' !== $search_query ) {
		$where  .= ' AND (name LIKE %s OR email LIKE %s OR university LIKE %s OR city LIKE %s OR primary_department LIKE %s)';
		$like   = '%' . $wpdb->esc_like( $search_query ) . '%';
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
		$args[] = $like;
	}

	$sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC";
	$rows = ! empty( $args ) ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

	$counts = aws_sbg_team_get_counts( $current_event_id, $current_role );
	$roles = aws_sbg_team_roles();
	$statuses = aws_sbg_team_statuses();
	$departments = aws_sbg_team_departments();

	$current_event = get_post( $current_event_id );
	$current_event_slug = ( $current_event && $current_event->post_name ) ? $current_event->post_name : $current_event_id;

	?>
	<div class="wrap" style="max-width:1340px;margin-top:20px;">
		<?php
		if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) {
			aws_sbg_render_admin_nav_tabs( 'team' );
		}
		?>

		<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
			<div>
				<h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0 0 4px 0;">Team &amp; Volunteers Roster</h1>
				<p style="margin:0;color:#64748b;font-size:13px;">Review applications for Event Crew &amp; Core Team across universities, track candidates, and coordinate via WhatsApp.</p>
			</div>
			<div style="display:flex;align-items:center;gap:10px;">
				<?php if ( $current_event_id ) : ?>
					<a href="<?php echo esc_url( home_url( '/join/?event=' . $current_event_slug ) ); ?>" target="_blank" class="button button-secondary">
						Public Join Form &rarr;
					</a>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aws_sbg_team_export_csv&event_id=' . $current_event_id . '&role=' . $current_role ), 'team_export' ) ); ?>" class="button button-primary">
						Export Roster (CSV)
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Filter Bar -->
		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:20px;">
			<form method="GET" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
				<input type="hidden" name="page" value="aws-team-hiring">
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
					<select name="role" onchange="this.form.submit()" style="width:100%;padding:6px 10px;font-weight:600;">
						<option value="all" <?php selected( $current_role, 'all' ); ?>>All Roles</option>
						<option value="event_crew" <?php selected( $current_role, 'event_crew' ); ?>>Event Crew (Volunteers)</option>
						<option value="core_team" <?php selected( $current_role, 'core_team' ); ?>>Core Team</option>
					</select>
				</div>

				<div style="flex:1;min-width:220px;">
					<input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search name, university, city..." style="width:100%;padding:6px 10px;">
				</div>

				<div>
					<button type="submit" class="button button-secondary">Filter</button>
					<?php if ( $search_query || 'all' !== $current_role || 'all' !== $current_status ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-team-hiring&event_id=' . $current_event_id ) ); ?>" class="button button-link">Reset</a>
					<?php endif; ?>
				</div>
			</form>

			<!-- Status Pills -->
			<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;">
				<?php
				$status_items = array( 'all' => 'All Applications' ) + $statuses;
				foreach ( $status_items as $s_key => $s_label ) :
					$count = isset( $counts[ $s_key ] ) ? $counts[ $s_key ] : 0;
					$is_active = ( $current_status === $s_key );
					$url = add_query_arg( array(
						'page'     => 'aws-team-hiring',
						'event_id' => $current_event_id,
						'role'     => $current_role,
						'status'   => $s_key,
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

		<!-- Candidates Roster Table -->
		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
			<table class="wp-list-table widefat fixed striped" style="border:0;">
				<thead>
					<tr>
						<th style="width:125px;font-weight:700;">Ref &amp; Date</th>
						<th style="width:230px;font-weight:700;">Candidate &amp; Campus</th>
						<th style="width:180px;font-weight:700;">Role &amp; Department</th>
						<th style="font-weight:700;">Pitch &amp; Strengths</th>
						<th style="width:135px;font-weight:700;">Status</th>
						<th style="width:80px;text-align:right;font-weight:700;">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td colspan="6" style="text-align:center;padding:48px 16px;color:#64748b;">
								<p style="font-size:15px;margin-bottom:6px;">No applications found.</p>
								<span style="font-size:12px;">Share your public join URL to recruit crew members and core team!</span>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $rows as $r ) : ?>
							<?php
							$wa_digits = preg_replace( '/[^0-9]/', '', $r['phone'] );
							if ( substr( $wa_digits, 0, 1 ) === '0' ) {
								$wa_digits = '92' . substr( $wa_digits, 1 );
							}
							$role_title = ( 'core_team' === $r['role'] ) ? 'Core Team' : 'Event Crew';
							$wa_greeting = 'Hi ' . $r['name'] . ', thank you for applying for the ' . $role_title . ' for ' . ( $current_event ? $current_event->post_title : 'AWS Event' ) . '! We loved your application and would like to coordinate next steps.';
							$wa_url = 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode( $wa_greeting );

							$status_colors = array(
								'received'    => 'background:#e2e8f0;color:#0f172a;',
								'screening'   => 'background:#fef3c7;color:#92400e;',
								'shortlisted' => 'background:#dbeafe;color:#1e40af;',
								'selected'    => 'background:#dcfce7;color:#166534;',
								'declined'    => 'background:#fee2e2;color:#991b1b;',
							);
							$st_style = isset( $status_colors[ $r['status'] ] ) ? $status_colors[ $r['status'] ] : 'background:#e2e8f0;color:#0f172a;';

							$role_depts = isset( $departments[ $r['role'] ] ) ? $departments[ $r['role'] ] : array();
							$dept_lbl = isset( $role_depts[ $r['primary_department'] ] ) ? $role_depts[ $r['primary_department'] ] : $r['primary_department'];
							$sec_dept_lbl = ( $r['secondary_department'] && isset( $role_depts[ $r['secondary_department'] ] ) ) ? $role_depts[ $r['secondary_department'] ] : '';
							?>
							<tr>
								<!-- Ref & Date -->
								<td>
									<strong style="font-family:monospace;font-size:12.5px;color:#7928ca;display:block;">
										<?php echo esc_html( $r['reference'] ); ?>
									</strong>
									<span style="font-size:11.5px;color:#64748b;">
										<?php echo esc_html( date( 'M j, Y', strtotime( $r['created_at'] ) ) ); ?><br>
										<?php echo esc_html( date( 'g:i a', strtotime( $r['created_at'] ) ) ); ?>
									</span>
								</td>

								<!-- Candidate & Campus -->
								<td>
									<strong style="font-size:14px;color:#0f172a;display:block;">
										<?php echo esc_html( $r['name'] ); ?>
									</strong>
									<div style="margin:4px 0 6px 0;">
										<span style="display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11.5px;font-weight:600;padding:2px 7px;border-radius:4px;word-break:break-word;">
											&#127891; <?php echo esc_html( $r['university'] ); ?>
										</span>
										<span style="display:block;font-size:11.5px;color:#64748b;margin-top:2px;">
											<?php echo esc_html( $r['city'] ); ?>
										</span>
									</div>
									<div style="font-size:12px;line-height:1.55;color:#334155;">
										<div style="word-break:break-all;">
											<a href="mailto:<?php echo esc_attr( $r['email'] ); ?>" style="text-decoration:none;color:#0f172a;font-weight:500;">&#9993; <?php echo esc_html( $r['email'] ); ?></a>
										</div>
										<?php if ( ! empty( $r['phone'] ) ) : ?>
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

								<!-- Role & Department -->
								<td>
									<div style="margin-bottom:6px;">
										<?php if ( 'core_team' === $r['role'] ) : ?>
											<span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;background:#f5f3ff;color:#7928ca;border:1px solid #ddd6fe;text-transform:uppercase;letter-spacing:0.04em;">
												Core Team
											</span>
										<?php else : ?>
											<span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;text-transform:uppercase;letter-spacing:0.04em;">
												Event Crew
											</span>
										<?php endif; ?>
									</div>
									<strong style="font-size:13px;color:#0f172a;display:block;">
										<?php echo esc_html( $dept_lbl ); ?>
									</strong>
									<?php if ( $sec_dept_lbl ) : ?>
										<span style="font-size:11.5px;color:#64748b;display:block;margin-top:2px;">
											Alt: <?php echo esc_html( $sec_dept_lbl ); ?>
										</span>
									<?php endif; ?>
								</td>

								<!-- Pitch & Experience -->
								<td>
									<div style="font-size:13px;color:#334155;line-height:1.5;background:#f8fafc;padding:8px 12px;border-radius:4px;border:1px solid #e2e8f0;white-space:pre-wrap;text-align:left;margin-bottom:6px;"><?php echo esc_html( trim( (string) $r['pitch'] ) ); ?></div>
									<?php if ( 'yes' === $r['past_experience'] && $r['experience_details'] ) : ?>
										<div style="font-size:11.5px;color:#64748b;">
											<strong>Prior exp:</strong> <?php echo esc_html( $r['experience_details'] ); ?>
										</div>
									<?php elseif ( 'no' === $r['past_experience'] ) : ?>
										<span style="font-size:11px;color:#64748b;font-style:italic;">First-time contributor</span>
									<?php endif; ?>
								</td>

								<!-- Status Selector -->
								<td>
									<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="aws_sbg_team_update_status">
										<input type="hidden" name="app_id" value="<?php echo esc_attr( $r['id'] ); ?>">
										<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
										<input type="hidden" name="role_filter" value="<?php echo esc_attr( $current_role ); ?>">
										<?php wp_nonce_field( 'team_update_status_' . $r['id'], 'team_status_nonce' ); ?>
										<select name="new_status" onchange="this.form.submit()" style="font-size:12px;font-weight:600;border-radius:4px;padding:3px 6px;width:100%;<?php echo esc_attr( $st_style ); ?>">
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
									<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('Permanently delete application for <?php echo esc_js( $r['name'] ); ?>?');">
										<input type="hidden" name="action" value="aws_sbg_team_delete_entry">
										<input type="hidden" name="entry_id" value="<?php echo esc_attr( $r['id'] ); ?>">
										<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
										<input type="hidden" name="role_filter" value="<?php echo esc_attr( $current_role ); ?>">
										<?php wp_nonce_field( 'team_delete_' . $r['id'], 'team_delete_nonce' ); ?>
										<button type="submit" class="button button-link-delete" style="font-size:12px;color:#dc2626;">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}

/* ============================================================================
 * 5. STATUS UPDATE & DELETE HANDLERS
 * ============================================================================ */

function aws_sbg_team_handle_update_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	$id = isset( $_POST['app_id'] ) ? intval( $_POST['app_id'] ) : 0;
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$role_filter = isset( $_POST['role_filter'] ) ? sanitize_key( wp_unslash( $_POST['role_filter'] ) ) : 'all';
	$new_status = isset( $_POST['new_status'] ) ? sanitize_key( wp_unslash( $_POST['new_status'] ) ) : '';

	check_admin_referer( 'team_update_status_' . $id, 'team_status_nonce' );

	if ( $id && array_key_exists( $new_status, aws_sbg_team_statuses() ) ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_team_applications';
		$wpdb->update( $table, array( 'status' => $new_status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=aws-team-hiring&event_id=' . $event_id . '&role=' . $role_filter ) );
	exit;
}
add_action( 'admin_post_aws_sbg_team_update_status', 'aws_sbg_team_handle_update_status' );

function aws_sbg_team_handle_delete_entry() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	$entry_id = isset( $_POST['entry_id'] ) ? intval( $_POST['entry_id'] ) : 0;
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$role_filter = isset( $_POST['role_filter'] ) ? sanitize_key( wp_unslash( $_POST['role_filter'] ) ) : 'all';

	check_admin_referer( 'team_delete_' . $entry_id, 'team_delete_nonce' );

	if ( $entry_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'aws_team_applications';
		$wpdb->delete( $table, array( 'id' => $entry_id ), array( '%d' ) );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=aws-team-hiring&event_id=' . $event_id . '&role=' . $role_filter ) );
	exit;
}
add_action( 'admin_post_aws_sbg_team_delete_entry', 'aws_sbg_team_handle_delete_entry' );

/* ============================================================================
 * 6. CSV EXPORT
 * ============================================================================ */

function aws_sbg_team_handle_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied.' );
	}

	check_admin_referer( 'team_export' );

	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
	$role_filter = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : 'all';

	global $wpdb;
	$table = $wpdb->prefix . 'aws_team_applications';

	$where = '1=1';
	$args  = array();
	if ( $event_id > 0 ) {
		$where  .= ' AND event_id = %d';
		$args[] = $event_id;
	}
	if ( 'all' !== $role_filter && in_array( $role_filter, array( 'event_crew', 'core_team' ), true ) ) {
		$where  .= ' AND role = %s';
		$args[] = $role_filter;
	}

	$sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC";
	$rows = ! empty( $args ) ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

	$departments = aws_sbg_team_departments();

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=team-roster-' . $event_id . '-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fputs( $output, "\xEF\xBB\xBF" ); // UTF-8 BOM

	fputcsv( $output, array(
		'Reference',
		'Role',
		'Name',
		'Email',
		'Phone',
		'City',
		'University / Campus',
		'LinkedIn URL',
		'Primary Department',
		'Secondary Department',
		'Past Experience',
		'Experience Details',
		'Pitch',
		'Status',
		'Applied At',
	) );

	foreach ( $rows as $r ) {
		$role_name = ( 'core_team' === $r['role'] ) ? 'Core Team' : 'Event Crew';
		$role_depts = isset( $departments[ $r['role'] ] ) ? $departments[ $r['role'] ] : array();
		$dept_name = isset( $role_depts[ $r['primary_department'] ] ) ? $role_depts[ $r['primary_department'] ] : $r['primary_department'];
		$sec_dept_name = isset( $role_depts[ $r['secondary_department'] ] ) ? $role_depts[ $r['secondary_department'] ] : $r['secondary_department'];

		fputcsv( $output, array(
			$r['reference'],
			$role_name,
			$r['name'],
			$r['email'],
			$r['phone'],
			$r['city'],
			$r['university'],
			$r['linkedin_url'],
			$dept_name,
			$sec_dept_name,
			$r['past_experience'],
			$r['experience_details'],
			$r['pitch'],
			$r['status'],
			$r['created_at'],
		) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_post_aws_sbg_team_export_csv', 'aws_sbg_team_handle_export_csv' );
