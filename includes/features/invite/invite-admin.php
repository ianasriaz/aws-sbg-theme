<?php
/**
 * AWS SBG Invitation Confirmation — Admin
 * File: invite-admin.php
 *
 * Event settings meta boxes, submissions screen, CSV export, shareable links.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================================
 * 1. META BOXES
 * ============================================================================ */

function aws_sbg_invite_add_meta_boxes() {
	add_meta_box( 'aws_sbg_invite_settings', 'Invitation Settings', 'aws_sbg_invite_render_settings_box', 'aws_invite_event', 'normal', 'high' );
	add_meta_box( 'aws_sbg_invite_share', 'Share & Embed', 'aws_sbg_invite_render_share_box', 'aws_invite_event', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'aws_sbg_invite_add_meta_boxes' );

function aws_sbg_invite_render_settings_box( $post ) {
	wp_nonce_field( 'aws_sbg_invite_meta_action', 'aws_sbg_invite_meta_nonce' );
	$m = aws_sbg_invite_get_event_meta( $post->ID );
	?>
	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;">
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Status</label>
			<select name="event_status" style="width:100%;">
				<option value="open" <?php selected( $m['event_status'], 'open' ); ?>>Open, accepting confirmations</option>
				<option value="closed" <?php selected( $m['event_status'], 'closed' ); ?>>Closed</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Photo required</label>
			<select name="photo_required" style="width:100%;">
				<option value="yes" <?php selected( $m['photo_required'], 'yes' ); ?>>Yes</option>
				<option value="no" <?php selected( $m['photo_required'], 'no' ); ?>>No</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Allow guests</label>
			<select name="allow_guests" style="width:100%;">
				<option value="yes" <?php selected( $m['allow_guests'], 'yes' ); ?>>Yes (Speaker + Guest + Leader)</option>
				<option value="no" <?php selected( $m['allow_guests'], 'no' ); ?>>No (Speaker + Leader only)</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Notify admin</label>
			<select name="notify_admin" style="width:100%;">
				<option value="yes" <?php selected( $m['notify_admin'], 'yes' ); ?>>Yes</option>
				<option value="no" <?php selected( $m['notify_admin'], 'no' ); ?>>No</option>
			</select>
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Deadline (optional)</label>
			<input type="datetime-local" name="deadline" value="<?php echo esc_attr( $m['deadline'] ); ?>" style="width:100%;">
		</div>
		<div>
			<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">WhatsApp link (optional)</label>
			<input type="text" name="whatsapp_url" value="<?php echo esc_attr( $m['whatsapp_url'] ); ?>" style="width:100%;">
		</div>
	</div>
	<div style="margin-top:14px;">
		<label style="display:block;font-weight:700;font-size:12px;margin-bottom:4px;">Confirmation message (shown after submit)</label>
		<textarea name="confirm_message" rows="2" style="width:100%;"><?php echo esc_textarea( $m['confirm_message'] ); ?></textarea>
	</div>
	<?php
}

function aws_sbg_invite_render_share_box( $post ) {
	$slug = $post->post_name;
	if ( ! $slug ) {
		$slug = sanitize_title( $post->post_title );
	}
	$shortcode    = '[aws_invite_form event="' . $slug . '"]';
	$standalone   = home_url( '/confirm/?event=' . $slug );
	?>
	<p style="font-size:12px;color:#64748b;margin-top:0;">Use either link to share this invitation.</p>
	<p style="font-size:11px;font-weight:800;text-transform:uppercase;color:#334155;margin-bottom:4px;">Shortcode (embed in a page)</p>
	<input type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select()" style="width:100%;font-family:monospace;font-size:12px;">
	<p style="font-size:11px;font-weight:800;text-transform:uppercase;color:#334155;margin:16px 0 4px;">Standalone page (full-screen)</p>
	<input type="text" readonly value="<?php echo esc_url( $standalone ); ?>" onclick="this.select()" style="width:100%;font-family:monospace;font-size:12px;">
	<?php
}

function aws_sbg_invite_save_meta( $post_id ) {
	if ( ! isset( $_POST['aws_sbg_invite_meta_nonce'] ) || ! wp_verify_nonce( $_POST['aws_sbg_invite_meta_nonce'], 'aws_sbg_invite_meta_action' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$select_keys = array( 'event_status', 'photo_required', 'allow_guests', 'notify_admin' );
	foreach ( $select_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_key( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	if ( isset( $_POST['deadline'] ) ) {
		update_post_meta( $post_id, 'deadline', sanitize_text_field( wp_unslash( $_POST['deadline'] ) ) );
	}
	if ( isset( $_POST['whatsapp_url'] ) ) {
		update_post_meta( $post_id, 'whatsapp_url', esc_url_raw( wp_unslash( $_POST['whatsapp_url'] ) ) );
	}
	if ( isset( $_POST['confirm_message'] ) ) {
		update_post_meta( $post_id, 'confirm_message', sanitize_textarea_field( wp_unslash( $_POST['confirm_message'] ) ) );
	}
}
add_action( 'save_post_aws_invite_event', 'aws_sbg_invite_save_meta' );

/* ============================================================================
 * 2. ROW ACTION + ADMIN MENU
 * ============================================================================ */

function aws_sbg_invite_row_actions( $actions, $post ) {
	if ( 'aws_invite_event' === $post->post_type ) {
		$actions['submissions'] = '<a href="' . esc_url( admin_url( 'admin.php?page=aws-invite-submissions&event_id=' . $post->ID ) ) . '">View Confirmations</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'aws_sbg_invite_row_actions', 10, 2 );

function aws_sbg_invite_register_admin_menu() {
	add_submenu_page(
		'aws-sbg-suite',
		'Speaker & Guest Confirmations',
		'Speaker & Guest Confirmations',
		'manage_options',
		'aws-invite-submissions',
		'aws_sbg_invite_render_submissions_page'
	);
}
add_action( 'admin_menu', 'aws_sbg_invite_register_admin_menu', 23 );

/* ============================================================================
 * 3. STANDALONE PAGE (auto-created once)
 * ============================================================================ */

function aws_sbg_invite_maybe_create_confirm_page() {
	if ( ! is_admin() ) {
		return;
	}
	$existing = get_page_by_path( 'confirm' );
	if ( $existing ) {
		return;
	}
	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => 'Invitation Confirmation',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_name'    => 'confirm',
	) );
	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-invite-confirmation.php' );
	}
	flush_rewrite_rules();
}
add_action( 'admin_init', 'aws_sbg_invite_maybe_create_confirm_page' );

/* ============================================================================
 * 4. SUBMISSIONS SCREEN
 * ============================================================================ */

function aws_sbg_invite_render_submissions_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_invite_submissions';

	$events = get_posts( array(
		'post_type'      => 'aws_invite_event',
		'posts_per_page' => 100,
		'post_status'    => 'any',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
	if ( ! $event_id && ! empty( $events ) ) {
		$event_id = $events[0]->ID;
	}

	$role_filter = isset( $_GET['role'] ) ? sanitize_key( wp_unslash( $_GET['role'] ) ) : '';
	$search      = isset( $_GET['s'] ) ? sanitize_text_field( trim( wp_unslash( $_GET['s'] ) ) ) : '';
	$per_page    = 50;
	$page        = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$offset      = ( $page - 1 ) * $per_page;

	$where = $wpdb->prepare( ' WHERE event_id = %d', $event_id );
	if ( '' !== $role_filter ) {
		if ( 'community_partner' === $role_filter || 'community_leader' === $role_filter ) {
			$where .= " AND (role = 'community_partner' OR role = 'community_leader')";
		} else {
			$where .= $wpdb->prepare( ' AND role = %s', $role_filter );
		}
	}
	if ( '' !== $search ) {
		$like   = '%' . $wpdb->esc_like( $search ) . '%';
		$where .= $wpdb->prepare( ' AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR reference LIKE %s)', $like, $like, $like, $like );
	}

	$rows           = $wpdb->get_results( "SELECT * FROM $table $where ORDER BY id DESC LIMIT $per_page OFFSET $offset", ARRAY_A );
	$total_filtered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table $where" );
	$total_pages    = (int) ceil( $total_filtered / $per_page );

	$role_counts = array();
	if ( $event_id ) {
		$counts = $wpdb->get_results( $wpdb->prepare( "SELECT role, COUNT(*) AS n FROM $table WHERE event_id = %d GROUP BY role", $event_id ), ARRAY_A );
		foreach ( $counts as $c ) {
			$k = ( 'community_leader' === $c['role'] ) ? 'community_partner' : $c['role'];
			$role_counts[ $k ] = ( $role_counts[ $k ] ?? 0 ) + (int) $c['n'];
		}
	}

	$roles = aws_sbg_invite_roles();
	$notice = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
	$count  = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
	?>
	<div class="wrap" style="width:100%;max-width:none;margin:10px 0 30px 0;padding-right:20px;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
		<?php if ( function_exists( 'aws_sbg_render_admin_nav_tabs' ) ) { aws_sbg_render_admin_nav_tabs( 'invites' ); } ?>

		<?php if ( 'entry_deleted' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Entry deleted.</strong> The confirmation entry and any uploaded test photo have been removed.</p>
			</div>
		<?php elseif ( 'event_cleared' === $notice ) : ?>
			<div class="notice notice-success is-dismissible" style="border-left-color:#16a34a;margin-bottom:16px;">
				<p><strong>Successfully cleared <?php echo esc_html( $count ); ?> confirmation entries.</strong> A safety snapshot backup was preserved in WordPress options.</p>
			</div>
		<?php elseif ( 'error_code_mismatch' === $notice ) : ?>
			<div class="notice notice-error is-dismissible" style="margin-bottom:16px;">
				<p><strong>Clear operation cancelled:</strong> You must type <code>CLEAR</code> exactly in capital letters to confirm clearing entries. No data was deleted.</p>
			</div>
		<?php elseif ( 'error_invalid_event' === $notice ) : ?>
			<div class="notice notice-error is-dismissible" style="margin-bottom:16px;">
				<p><strong>Error:</strong> Invalid event selected.</p>
			</div>
		<?php endif; ?>

		<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px 28px;margin-bottom:24px;">
			<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
				<div>
					<h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;">Speaker &amp; Guest Confirmations</h1>
					<p style="margin:4px 0 0;color:#64748b;font-size:13px;">Review invitation confirmations per event, with photos and role-specific details.</p>
				</div>
				<div style="display:flex;gap:10px;align-items:center;">
					<?php if ( $event_id && $total_filtered > 0 ) : ?>
						<button type="button" class="button" style="color:#b91c1c;border-color:#fca5a5;background:#fff5f5;" onclick="document.getElementById('aws-invite-clear-modal').style.display='flex'">Clear Entries…</button>
					<?php endif; ?>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aws_invite_event' ) ); ?>" class="button button-primary">+ New Event</a>
					<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=aws_sbg_invite_export_csv&event_id=' . $event_id ) ); ?>" class="button">Export CSV</a>
				</div>
			</div>

			<form method="get" style="margin-top:18px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
				<input type="hidden" name="page" value="aws-invite-submissions">
				<select name="event_id" style="min-width:240px;padding:6px 10px;">
					<?php if ( empty( $events ) ) : ?>
						<option value="0">No events yet</option>
					<?php else : ?>
						<?php foreach ( $events as $ev ) : ?>
							<option value="<?php echo esc_attr( $ev->ID ); ?>" <?php selected( $event_id, $ev->ID ); ?>><?php echo esc_html( $ev->post_title ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<select name="role" style="padding:6px 10px;">
					<option value="">All roles</option>
					<?php foreach ( $roles as $role_key => $role_label ) : ?>
						<option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $role_filter, $role_key ); ?>><?php echo esc_html( $role_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search name, email, phone, ref…" style="padding:6px 10px;width:240px;">
				<button type="submit" class="button">Search</button>
			</form>

			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-top:18px;">
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;">
					<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;">Total</div>
					<div style="font-size:26px;font-weight:900;color:#0f172a;"><?php echo esc_html( number_format( array_sum( $role_counts ) ) ); ?></div>
				</div>
				<?php foreach ( $roles as $role_key => $role_label ) : ?>
					<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;">
						<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;"><?php echo esc_html( $role_label ); ?></div>
						<div style="font-size:26px;font-weight:900;color:#7928ca;"><?php echo esc_html( isset( $role_counts[ $role_key ] ) ? number_format( $role_counts[ $role_key ] ) : 0 ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( ! $event_id ) : ?>
			<div style="background:#fff;border:2px dashed #e2e8f0;border-radius:16px;padding:60px 20px;text-align:center;">
				<h3 style="margin:0 0 6px;color:#0f172a;">No invitation events yet</h3>
				<p style="color:#64748b;">Create your first invitation event to start collecting confirmations.</p>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aws_invite_event' ) ); ?>" class="button button-primary">+ Create Event</a>
			</div>
		<?php else : ?>
			<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
				<table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
					<thead>
						<tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;">
							<th style="padding:12px 18px;">Photo</th>
							<th style="padding:12px 18px;">Reference</th>
							<th style="padding:12px 18px;">Name / Shield</th>
							<th style="padding:12px 18px;">Role</th>
							<th style="padding:12px 18px;">Contact</th>
							<th style="padding:12px 18px;">Vehicle</th>
							<th style="padding:12px 18px;">Details</th>
							<th style="padding:12px 18px;">Consent</th>
							<th style="padding:12px 18px;">Date</th>
							<th style="padding:12px 18px;text-align:right;">Action</th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="10" style="padding:50px 20px;text-align:center;color:#64748b;">No confirmations yet for this event.</td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $r ) : ?>
							<?php
							$payload = json_decode( $r['payload'], true );
							if ( ! is_array( $payload ) ) {
								$payload = array();
							}
							$photo_url = $r['photo_id'] ? wp_get_attachment_url( (int) $r['photo_id'] ) : '';
							$vehicle   = '';
							if ( 'yes' === ( $payload['vehicle'] ?? 'no' ) ) {
								$vehicle = ucfirst( $payload['vehicle_type'] ?? '' ) . ( ! empty( $payload['vehicle_number'] ) ? ' · ' . $payload['vehicle_number'] : '' );
							}
							?>
							<tr style="border-bottom:1px solid #f1f5f9;vertical-align:top;">
								<td style="padding:12px 18px;">
									<?php if ( $photo_url ) : ?>
										<a href="<?php echo esc_url( $photo_url ); ?>" target="_blank"><img src="<?php echo esc_url( $photo_url ); ?>" loading="lazy" style="height:40px;width:40px;object-fit:cover;border-radius:8px;"></a>
									<?php else : ?>
										<span style="color:#cbd5e1;">—</span>
									<?php endif; ?>
								</td>
								<td style="padding:12px 18px;font-family:monospace;font-weight:800;color:#7928ca;"><?php echo esc_html( $r['reference'] ); ?></td>
								<td style="padding:12px 18px;">
									<div style="font-weight:700;color:#0f172a;"><?php echo esc_html( $r['name'] ); ?></div>
									<div style="font-size:12px;color:#64748b;"><?php echo esc_html( $r['shield_line'] ); ?></div>
								</td>
								<td style="padding:12px 18px;">
									<?php $display_role = ( 'community_leader' === $r['role'] ) ? 'community_partner' : $r['role']; ?>
									<span style="background:#f5f3ff;color:#7c3aed;font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;"><?php echo esc_html( $roles[ $display_role ] ?? $r['role'] ); ?></span>
								</td>
								<td style="padding:12px 18px;color:#475569;">
									<div style="font-weight:600;color:#0f172a;"><?php echo esc_html( $r['email'] ); ?></div>
									<div style="font-size:12px;margin-top:2px;"><?php echo esc_html( $r['phone'] ); ?></div>
									<?php if ( ! empty( $payload['cnic'] ) ) : ?>
										<div style="margin-top:4px;"><span style="font-size:11px;font-family:ui-monospace,monospace;background:#f1f5f9;color:#334155;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0;" title="CNIC for AU Gate Clearance">CNIC: <?php echo esc_html( $payload['cnic'] ); ?></span></div>
									<?php endif; ?>
								</td>
								<td style="padding:12px 18px;color:#475569;"><?php echo $vehicle ? esc_html( $vehicle ) : '<span style="color:#cbd5e1;">—</span>'; ?></td>
								<td style="padding:12px 18px;color:#334155;font-size:12px;">
									<?php
									if ( 'speaker' === $r['role'] ) {
										echo '<div><strong>Topic:</strong> ' . esc_html( $payload['topic'] ?? '' ) . '</div>';
										echo '<div><strong>Slot:</strong> ' . esc_html( ucfirst( $payload['slot'] ?? '' ) ) . '</div>';
										if ( ! empty( $payload['comment'] ) ) {
											echo '<details style="margin-top:4px;"><summary style="cursor:pointer;color:#7928ca;">Comment</summary><div style="margin-top:4px;white-space:pre-wrap;">' . esc_html( $payload['comment'] ) . '</div></details>';
										}
									} elseif ( 'community_partner' === $r['role'] || 'community_leader' === $r['role'] ) {
										if ( ! empty( $payload['university'] ) ) {
											echo '<div><strong>University:</strong> ' . esc_html( $payload['university'] ) . '</div>';
										}
										if ( ! empty( $payload['program'] ) ) {
											$programs = aws_sbg_invite_programs();
											echo '<div><strong>Program:</strong> ' . esc_html( $programs[ $payload['program'] ] ?? $payload['program'] ) . '</div>';
										}
										if ( ! empty( $payload['term'] ) ) {
											echo '<div><strong>Term:</strong> ' . esc_html( $payload['term'] ) . '</div>';
										}
										if ( ! empty( $payload['about'] ) ) {
											echo '<details style="margin-top:4px;"><summary style="cursor:pointer;color:#7928ca;">Intro &amp; Leadership</summary><div style="margin-top:4px;white-space:pre-wrap;">' . esc_html( $payload['about'] ) . '</div></details>';
										}
										if ( ! empty( $payload['partner_promo_consent'] ) && 'yes' === $payload['partner_promo_consent'] ) {
											echo '<div style="margin-top:4px;font-size:11px;color:#16a34a;">&#10003; Community promo committed</div>';
										}
									} elseif ( 'guest' === $r['role'] ) {
										if ( ! empty( $payload['about'] ) ) {
											echo '<details style="margin-top:4px;"><summary style="cursor:pointer;color:#7928ca;">Intro &amp; Achievements</summary><div style="margin-top:4px;white-space:pre-wrap;">' . esc_html( $payload['about'] ) . '</div></details>';
										} else {
											echo '<span style="color:#cbd5e1;">—</span>';
										}
									} else {
										echo '<span style="color:#cbd5e1;">—</span>';
									}
									?>
								</td>
								<td style="padding:12px 18px;color:#16a34a;font-weight:700;">&#10003;</td>
								<td style="padding:12px 18px;color:#64748b;font-size:12px;"><?php echo esc_html( mysql2date( 'M j, g:i a', $r['created_at'] ) ); ?></td>
								<td style="padding:12px 18px;text-align:right;white-space:nowrap;">
									<?php
									$delete_url = wp_nonce_url(
										add_query_arg(
											array(
												'action'   => 'aws_sbg_invite_delete_entry',
												'id'       => $r['id'],
												'event_id' => $event_id,
											),
											admin_url( 'admin-post.php' )
										),
										'aws_sbg_invite_delete_entry_' . $r['id']
									);
									?>
									<a href="<?php echo esc_url( $delete_url ); ?>"
									   onclick="return confirm('Permanently delete entry for <?php echo esc_js( $r['name'] ); ?> (Ref: <?php echo esc_js( $r['reference'] ); ?>)? If a photo was uploaded, it will also be deleted from the media library.');"
									   style="color:#dc2626;text-decoration:none;font-weight:600;font-size:12px;padding:4px 8px;border:1px solid #fecaca;border-radius:6px;background:#fef2f2;display:inline-block;"
									   title="Delete entry">
										Delete
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#64748b;">
						<div>Page <?php echo esc_html( $page ); ?> of <?php echo esc_html( $total_pages ); ?></div>
						<div style="display:flex;gap:6px;">
							<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
								<a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" style="padding:4px 10px;border:1px solid #cbd5e1;border-radius:6px;text-decoration:none;<?php echo ( $i === $page ) ? 'background:#7928ca;color:#fff;' : 'color:#0f172a;'; ?>"><?php echo esc_html( $i ); ?></a>
							<?php endfor; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $event_id && $total_filtered > 0 ) : ?>
			<div id="aws-invite-clear-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(2px);z-index:99999;align-items:center;justify-content:center;padding:20px;">
				<div style="background:#ffffff;border-radius:16px;max-width:520px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #fee2e2;">
					<div style="background:#fef2f2;border-bottom:1px solid #fee2e2;padding:20px 24px;display:flex;align-items:center;gap:12px;">
						<div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;border:2px solid #fca5a5;">&#9888;</div>
						<div>
							<h3 style="margin:0;font-size:16px;font-weight:800;color:#991b1b;">Clear Event Confirmations</h3>
							<div style="font-size:12px;color:#b91c1c;margin-top:2px;">Testing data cleanup with safety backup</div>
						</div>
					</div>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:24px;">
						<?php wp_nonce_field( 'aws_sbg_invite_clear_event_entries' ); ?>
						<input type="hidden" name="action" value="aws_sbg_invite_clear_event_entries">
						<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">

						<p style="margin:0 0 14px;font-size:13px;line-height:1.6;color:#334155;">
							You are about to delete <strong>all <?php echo esc_html( $total_filtered ); ?> confirmations</strong> for this event. This is intended for cleaning up test submissions.
						</p>

						<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#475569;line-height:1.5;">
							<strong style="color:#0f172a;">&#128274; Safety Snapshot:</strong> A complete snapshot of these records will be saved to your WordPress options (<code>aws_invite_backup_...</code>) prior to deletion so data can be recovered if needed.
						</div>

						<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#334155;margin-bottom:16px;cursor:pointer;">
							<input type="checkbox" name="clean_photos" value="1" checked style="border-radius:4px;">
							<span>Also delete uploaded test photos from WordPress Media Library</span>
						</label>

						<div style="margin-bottom:20px;">
							<label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">
								Type <span style="color:#dc2626;font-family:monospace;font-size:14px;">CLEAR</span> to confirm:
							</label>
							<input type="text" name="confirm_code" autocomplete="off" required placeholder="Type CLEAR here" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:monospace;font-size:14px;letter-spacing:2px;font-weight:700;">
						</div>

						<div style="display:flex;justify-content:flex-end;gap:10px;">
							<button type="button" class="button" onclick="document.getElementById('aws-invite-clear-modal').style.display='none'">Cancel</button>
							<button type="submit" class="button" style="background:#dc2626;color:#ffffff;border-color:#b91c1c;font-weight:700;">Permanently Clear Entries</button>
						</div>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* ============================================================================
 * 5. CSV EXPORT
 * ============================================================================ */

function aws_sbg_invite_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	global $wpdb;
	$table    = $wpdb->prefix . 'aws_invite_submissions';
	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d ORDER BY id ASC", $event_id ), ARRAY_A );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=invite-confirmations-' . $event_id . '-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Reference', 'Role', 'Name', 'Shield Line', 'Email', 'Phone', 'CNIC (Gate Clearance)', 'Vehicle Type', 'Vehicle Number', 'Photo URL', 'Consent', 'Topic', 'Slot', 'Comment', 'University / Institute', 'About / Bio', 'Program (Legacy)', 'Term (Legacy)', 'Status', 'Created At' ) );

	$programs = aws_sbg_invite_programs();
	$roles    = aws_sbg_invite_roles();

	foreach ( $rows as $r ) {
		$payload = json_decode( $r['payload'], true );
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}
		$photo_url   = $r['photo_id'] ? wp_get_attachment_url( (int) $r['photo_id'] ) : '';
		$display_role = ( 'community_leader' === $r['role'] ) ? 'AWS Community Partner' : ( $roles[ $r['role'] ] ?? $r['role'] );

		fputcsv( $output, array(
			$r['reference'],
			$display_role,
			$r['name'],
			$r['shield_line'],
			$r['email'],
			$r['phone'],
			$payload['cnic'] ?? '',
			$payload['vehicle_type'] ?? '',
			$payload['vehicle_number'] ?? '',
			$photo_url,
			'yes',
			$payload['topic'] ?? '',
			ucfirst( $payload['slot'] ?? '' ),
			$payload['comment'] ?? '',
			$payload['university'] ?? '',
			$payload['about'] ?? '',
			$programs[ $payload['program'] ] ?? ( $payload['program'] ?? '' ),
			$payload['term'] ?? '',
			$r['status'],
			$r['created_at'],
		) );
	}
	fclose( $output );
	exit;
}
add_action( 'wp_ajax_aws_sbg_invite_export_csv', 'aws_sbg_invite_export_csv' );

/* ============================================================================
 * 6. DELETE & CLEAR CONFIRMATIONS (SAFE TESTING CLEANUP)
 * ============================================================================ */

/**
 * Handle deletion of a single confirmation entry.
 */
function aws_sbg_invite_handle_delete_entry() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	$entry_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
	$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

	check_admin_referer( 'aws_sbg_invite_delete_entry_' . $entry_id );

	global $wpdb;
	$table = $wpdb->prefix . 'aws_invite_submissions';

	$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $entry_id ), ARRAY_A );

	if ( $entry ) {
		// Safety backup of single entry into wp_options (keep up to 50 recent deleted entries)
		$recent_deleted = get_option( 'aws_invite_recent_deleted_entries', array() );
		if ( ! is_array( $recent_deleted ) ) {
			$recent_deleted = array();
		}
		$entry['_deleted_at'] = current_time( 'mysql' );
		$entry['_deleted_by'] = get_current_user_id();
		array_unshift( $recent_deleted, $entry );
		$recent_deleted = array_slice( $recent_deleted, 0, 50 );
		update_option( 'aws_invite_recent_deleted_entries', $recent_deleted, false );

		// Delete photo attachment if present
		if ( ! empty( $entry['photo_id'] ) ) {
			wp_delete_attachment( (int) $entry['photo_id'], true );
		}

		$wpdb->delete( $table, array( 'id' => $entry_id ), array( '%d' ) );
	}

	$redirect = add_query_arg(
		array(
			'page'     => 'aws-invite-submissions',
			'event_id' => $event_id,
			'notice'   => 'entry_deleted',
		),
		admin_url( 'admin.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_aws_sbg_invite_delete_entry', 'aws_sbg_invite_handle_delete_entry' );

/**
 * Handle bulk clearance of all confirmations for an event with typed confirmation & snapshot backup.
 */
function aws_sbg_invite_handle_clear_event_entries() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized', 'Error', array( 'response' => 403 ) );
	}

	check_admin_referer( 'aws_sbg_invite_clear_event_entries' );

	$event_id     = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$confirm_code = isset( $_POST['confirm_code'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['confirm_code'] ) ) ) : '';
	$clean_photos = ! empty( $_POST['clean_photos'] );

	$redirect_base = add_query_arg(
		array(
			'page'     => 'aws-invite-submissions',
			'event_id' => $event_id,
		),
		admin_url( 'admin.php' )
	);

	if ( 'CLEAR' !== $confirm_code ) {
		wp_safe_redirect( add_query_arg( 'notice', 'error_code_mismatch', $redirect_base ) );
		exit;
	}

	if ( ! $event_id ) {
		wp_safe_redirect( add_query_arg( 'notice', 'error_invalid_event', $redirect_base ) );
		exit;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_invite_submissions';

	// Fetch all records for backup
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d", $event_id ), ARRAY_A );
	$count = count( $rows );

	if ( $count > 0 ) {
		// Snapshot backup
		$backup_key  = 'aws_invite_backup_' . $event_id . '_' . time();
		$backup_data = array(
			'event_id'     => $event_id,
			'backed_up_at' => current_time( 'mysql' ),
			'admin_user'   => get_current_user_id(),
			'count'        => $count,
			'rows'         => $rows,
		);
		update_option( $backup_key, $backup_data, false );

		// Clean photos if requested
		if ( $clean_photos ) {
			foreach ( $rows as $r ) {
				if ( ! empty( $r['photo_id'] ) ) {
					wp_delete_attachment( (int) $r['photo_id'], true );
				}
			}
		}

		// Delete entries
		$wpdb->delete( $table, array( 'event_id' => $event_id ), array( '%d' ) );
	}

	wp_safe_redirect( add_query_arg( array( 'notice' => 'event_cleared', 'count' => $count ), $redirect_base ) );
	exit;
}
add_action( 'admin_post_aws_sbg_invite_clear_event_entries', 'aws_sbg_invite_handle_clear_event_entries' );
