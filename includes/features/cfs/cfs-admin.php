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

	$current_event = get_post( $current_event_id );
	$current_event_slug = ( $current_event && $current_event->post_name ) ? $current_event->post_name : $current_event_id;

	?>
	<div class="wrap" style="max-width:1300px;margin-top:20px;">
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
		<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
			<table class="wp-list-table widefat fixed striped" style="border:0;">
				<thead>
					<tr>
						<th style="width:130px;font-weight:700;">Ref &amp; Date</th>
						<th style="width:200px;font-weight:700;">Speaker</th>
						<th style="font-weight:700;">Session Pitch</th>
						<th style="width:140px;font-weight:700;">Status</th>
						<th style="width:170px;text-align:right;font-weight:700;">Actions</th>
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
						<?php foreach ( $rows as $r ) : ?>
							<?php
							$wa_digits = preg_replace( '/[^0-9]/', '', $r['phone'] );
							if ( substr( $wa_digits, 0, 1 ) === '0' ) {
								$wa_digits = '92' . substr( $wa_digits, 1 );
							}
							$wa_url = 'https://wa.me/' . $wa_digits . '?text=' . rawurlencode( 'Hi ' . $r['name'] . ', thank you for submitting your speaker pitch for ' . ( $current_event ? $current_event->post_title : 'AWS Event' ) . '!' );
							$status_colors = array(
								'received'     => 'background:#e2e8f0;color:#0f172a;',
								'under_review' => 'background:#fef3c7;color:#92400e;',
								'shortlisted'  => 'background:#dbeafe;color:#1e40af;',
								'accepted'     => 'background:#dcfce7;color:#166534;',
								'declined'     => 'background:#fee2e2;color:#991b1b;',
							);
							$st_style = isset( $status_colors[ $r['status'] ] ) ? $status_colors[ $r['status'] ] : 'background:#e2e8f0;color:#0f172a;';
							$track_lbl = isset( $tracks[ $r['track'] ] ) ? $tracks[ $r['track'] ] : $r['track'];
							$format_lbl = isset( $formats[ $r['format'] ] ) ? $formats[ $r['format'] ] : $r['format'];
							$confirm_link = home_url( '/confirm/?event=' . $current_event_slug . '&role=speaker' );
							?>
							<tr>
								<!-- Ref & Date -->
								<td>
									<strong style="font-family:monospace;font-size:13px;color:#7928ca;display:block;">
										<?php echo esc_html( $r['reference'] ); ?>
									</strong>
									<span style="font-size:11.5px;color:#64748b;">
										<?php echo esc_html( date( 'M j, Y', strtotime( $r['created_at'] ) ) ); ?><br>
										<?php echo esc_html( date( 'g:i a', strtotime( $r['created_at'] ) ) ); ?>
									</span>
								</td>

								<!-- Speaker Info -->
								<td>
									<strong style="font-size:14px;color:#0f172a;display:block;">
										<?php echo esc_html( $r['name'] ); ?>
									</strong>
									<span style="font-size:12px;color:#475569;display:block;margin-bottom:6px;">
										<?php echo esc_html( $r['organization'] ); ?> &middot; <?php echo esc_html( $r['city'] ); ?>
									</span>
									<div style="display:flex;align-items:center;gap:8px;font-size:12px;">
										<a href="mailto:<?php echo esc_attr( $r['email'] ); ?>" title="<?php echo esc_attr( $r['email'] ); ?>" style="text-decoration:none;color:#64748b;">
											&#9993; Email
										</a>
										&middot;
										<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" style="text-decoration:none;color:#16a34a;font-weight:600;">
											&#128172; WhatsApp
										</a>
										<?php if ( $r['linkedin_url'] ) : ?>
											&middot;
											<a href="<?php echo esc_url( $r['linkedin_url'] ); ?>" target="_blank" style="text-decoration:none;color:#0077b5;font-weight:600;">
												in
											</a>
										<?php endif; ?>
									</div>
								</td>

								<!-- Session Pitch -->
								<td>
									<div style="margin-bottom:6px;">
										<strong style="font-size:14.5px;color:#0f172a;">
											<?php echo esc_html( $r['talk_title'] ); ?>
										</strong>
									</div>
									<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:8px;">
										<span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;background:#f5f3ff;color:#7928ca;border:1px solid #ddd6fe;">
											<?php echo esc_html( $track_lbl ); ?>
										</span>
										<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
											<?php echo esc_html( $format_lbl ); ?>
										</span>
										<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
											Lvl <?php echo esc_html( $r['audience_level'] ); ?>
										</span>
										<?php if ( 'yes' === $r['past_experience'] ) : ?>
											<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f0fdf4;color:#15803d;">
												Experienced
											</span>
										<?php else : ?>
											<span style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#64748b;">
												First-time speaker
											</span>
										<?php endif; ?>
									</div>
									<div style="font-size:13px;color:#334155;line-height:1.5;background:#f8fafc;padding:8px 12px;border-radius:4px;border:1px solid #e2e8f0;white-space:pre-wrap;">
										<?php echo esc_html( $r['talk_pitch'] ); ?>
									</div>
									<?php if ( $r['experience_link'] ) : ?>
										<div style="margin-top:6px;font-size:11.5px;">
											<span style="color:#64748b;">Past talk link:</span>
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
										<input type="hidden" name="submission_id" value="<?php echo esc_attr( $r['id'] ); ?>">
										<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
										<?php wp_nonce_field( 'cfs_update_status_' . $r['id'], 'cfs_status_nonce' ); ?>
										<select name="new_status" onchange="this.form.submit()" style="font-size:12px;font-weight:600;border-radius:4px;padding:3px 6px;<?php echo esc_attr( $st_style ); ?>">
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
									<?php if ( 'accepted' === $r['status'] ) : ?>
										<button type="button" class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js( $confirm_link ); ?>'); alert('Speaker Confirmation link copied to clipboard!\n\n<?php echo esc_js( $confirm_link ); ?>');" title="Copy speaker confirmation onboarding link" style="color:#166534;font-weight:600;margin-bottom:4px;">
											Copy Confirm Link
										</button>
										<br>
									<?php endif; ?>
									<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('Permanently delete proposal for <?php echo esc_js( $r['name'] ); ?>?');">
										<input type="hidden" name="action" value="aws_sbg_cfs_delete_entry">
										<input type="hidden" name="entry_id" value="<?php echo esc_attr( $r['id'] ); ?>">
										<input type="hidden" name="event_id" value="<?php echo esc_attr( $current_event_id ); ?>">
										<?php wp_nonce_field( 'cfs_delete_' . $r['id'], 'cfs_delete_nonce' ); ?>
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
 * 5. ADMIN ACTIONS: STATUS UPDATE & DELETE
 * ============================================================================ */

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

	wp_safe_redirect( admin_url( 'admin.php?page=aws-cfs-submissions&event_id=' . $event_id ) );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_update_status', 'aws_sbg_cfs_handle_update_status' );

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

	wp_safe_redirect( admin_url( 'admin.php?page=aws-cfs-submissions&event_id=' . $event_id ) );
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
			$r['created_at'],
		) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_post_aws_sbg_cfs_export_csv', 'aws_sbg_cfs_handle_export_csv' );
