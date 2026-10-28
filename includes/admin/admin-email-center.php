<?php
/**
 * AWS SBG Email Center
 * Manage SMTP connections, per-context routing, and the mail send log.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aws_sbg_render_email_center_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	aws_sbg_mail_migrate_legacy();

	$connections = aws_sbg_mail_get_connections();
	$settings    = aws_sbg_mail_get_settings();
	$edit_id     = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( $_GET['edit'] ) ) : '';
	$msg         = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '';

	/* ---------------------------------------------------------------
	 * Process actions (POST) — runs before any output.
	 * ------------------------------------------------------------ */
	if ( isset( $_POST['aws_mail_action'] ) ) {
		check_admin_referer( 'aws_mail_center', 'aws_mail_nonce' );
		$action    = sanitize_key( wp_unslash( $_POST['aws_mail_action'] ) );
		$redirect  = array( 'page' => 'aws-email-center' );

		if ( 'save_connection' === $action ) {
			$label      = sanitize_text_field( wp_unslash( $_POST['conn_label'] ?? '' ) );
			$host       = sanitize_text_field( wp_unslash( $_POST['conn_host'] ?? '' ) );
			$port       = max( 1, intval( $_POST['conn_port'] ?? 465 ) );
			$enc        = ( 'tls' === ( $_POST['conn_encryption'] ?? '' ) ) ? 'tls' : 'ssl';
			$user       = sanitize_text_field( wp_unslash( $_POST['conn_username'] ?? '' ) );
			$pass       = (string) ( $_POST['conn_password'] ?? '' );
			$from_email = sanitize_email( wp_unslash( $_POST['conn_from_email'] ?? '' ) );
			$from_name  = sanitize_text_field( wp_unslash( $_POST['conn_from_name'] ?? '' ) );
			$id         = sanitize_key( wp_unslash( $_POST['conn_id'] ?? '' ) );

			if ( ! $label || ! $host || ! $user ) {
				$redirect['msg'] = 'error_required';
			} else {
				$connections = aws_sbg_mail_get_connections();
				if ( ! $id ) {
					$id   = sanitize_title( $label );
					$base = $id;
					$n    = 2;
					while ( isset( $connections[ $id ] ) ) {
						$id = $base . '-' . $n;
						$n++;
					}
				}
				$conn           = isset( $connections[ $id ] ) ? $connections[ $id ] : array();
				$conn['id']     = $id;
				$conn['label']  = $label;
				$conn['host']   = $host;
				$conn['port']   = $port;
				$conn['encryption'] = $enc;
				$conn['username']   = $user;
				if ( $pass ) {
					$conn['password'] = $pass;
				} elseif ( empty( $conn['password'] ) ) {
					$conn['password'] = '';
				}
				$conn['from_email'] = $from_email ? $from_email : $user;
				$conn['from_name']  = $from_name ? $from_name : 'AWS Air University';

				$connections[ $id ] = $conn;
				update_option( 'aws_sbg_mail_connections', $connections );

				$settings = aws_sbg_mail_get_settings();
				if ( empty( $settings['default_connection'] ) ) {
					$settings['default_connection'] = $id;
					update_option( 'aws_sbg_mail_settings', $settings );
				}

				$redirect['msg'] = 'connection_saved';
			}
		}
		elseif ( 'delete_connection' === $action ) {
			$id         = sanitize_key( wp_unslash( $_POST['conn_id'] ?? '' ) );
			$connections = aws_sbg_mail_get_connections();
			if ( $id && isset( $connections[ $id ] ) ) {
				unset( $connections[ $id ] );
				update_option( 'aws_sbg_mail_connections', $connections );

				$settings = aws_sbg_mail_get_settings();
				if ( $settings['default_connection'] === $id ) {
					$settings['default_connection'] = '';
				}
				foreach ( $settings['routing'] as $ctx => $cid ) {
					if ( $cid === $id ) {
						$settings['routing'][ $ctx ] = '';
					}
				}
				update_option( 'aws_sbg_mail_settings', $settings );
				$redirect['msg'] = 'connection_deleted';
			}
		}
		elseif ( 'set_default' === $action ) {
			$id      = sanitize_key( wp_unslash( $_POST['conn_id'] ?? '' ) );
			$settings = aws_sbg_mail_get_settings();
			if ( $id ) {
				$settings['default_connection'] = $id;
				update_option( 'aws_sbg_mail_settings', $settings );
				$redirect['msg'] = 'default_set';
			}
		}
		elseif ( 'save_settings' === $action ) {
			$settings = aws_sbg_mail_get_settings();
			$contexts = array( 'reinvent', 'exam', 'general' );
			$routing  = array();
			foreach ( $contexts as $ctx ) {
				$cid = sanitize_key( wp_unslash( $_POST[ 'route_' . $ctx ] ?? '' ) );
				$routing[ $ctx ] = $cid;
			}
			$settings['routing']           = $routing;
			$settings['notify_admin_rsvp'] = isset( $_POST['notify_admin_rsvp'] ) ? 'yes' : 'no';
			$settings['admin_email']       = sanitize_email( wp_unslash( $_POST['admin_email'] ?? '' ) );
			if ( empty( $settings['admin_email'] ) ) {
				$settings['admin_email'] = get_option( 'admin_email' );
			}
			update_option( 'aws_sbg_mail_settings', $settings );
			$redirect['msg'] = 'settings_saved';
		}
		elseif ( 'send_test' === $action ) {
			$id = sanitize_key( wp_unslash( $_POST['test_connection'] ?? '' ) );
			$to = sanitize_email( wp_unslash( $_POST['test_to'] ?? '' ) );
			if ( $id && $to ) {
				$ok = aws_sbg_mail_send( 'general', $to, 'AWS SBG test email', 'Email Center', 'SMTP test', '<p style="margin:0;">If you are reading this, the connection <strong style="color:#FFFFFF;">' . esc_html( $id ) . '</strong> works and HTML rendering looks correct.</p>', '', '', $id );
				$redirect['msg'] = $ok ? 'test_sent' : 'test_failed';
			} else {
				$redirect['msg'] = 'error_required';
			}
		}

		wp_safe_redirect( add_query_arg( $redirect, admin_url( 'admin.php' ) ) );
		exit;
	}

	$edit_conn = ( $edit_id && isset( $connections[ $edit_id ] ) ) ? $connections[ $edit_id ] : null;
	$notices   = array(
		'connection_saved'  => array( 'Connection saved.', 'success' ),
		'connection_deleted'=> array( 'Connection deleted.', 'success' ),
		'default_set'       => array( 'Default connection updated.', 'success' ),
		'settings_saved'    => array( 'Routing and notification settings saved.', 'success' ),
		'test_sent'         => array( 'Test email sent — check the inbox and the log below.', 'success' ),
		'test_failed'       => array( 'Test send FAILED — see the log below for the SMTP error.', 'error' ),
		'error_required'    => array( 'Missing required fields.', 'error' ),
	);
	?>
	<div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; max-width:1200px;">
		<?php if ( $msg && isset( $notices[ $msg ] ) ) :
			list( $text, $type ) = $notices[ $msg ]; ?>
			<div style="background:#ffffff; border-left:4px solid <?php echo 'error' === $type ? '#ef4444' : '#10b981'; ?>; padding:14px 18px; border-radius:10px; margin:18px 0; font-size:13px; font-weight:700; color:#0f172a; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
				<?php echo esc_html( $text ); ?>
			</div>
		<?php endif; ?>

		<div class="flex flex-wrap justify-between items-center gap-4 mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
			<div>
				<h1 class="text-2xl font-black text-slate-900 tracking-tight">Email Center</h1>
				<p class="text-slate-500 text-xs mt-1 font-medium">Manage SMTP connections, route each product's mail to its own sender, and audit every send.</p>
			</div>
		</div>

		<!-- ============ CONNECTIONS ============ -->
		<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
				<h2 style="margin:0; font-size:15px; font-weight:900; color:#0f172a;">SMTP Connections</h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-email-center' ) ); ?>" style="font-size:12px; font-weight:800; color:#ad5cff; text-decoration:none;">+ Add new</a>
			</div>

			<?php if ( empty( $connections ) ) : ?>
				<p style="font-size:13px; color:#64748b; margin:0 0 14px;">No connections yet. Add your first SMTP connection below (e.g. Purelymail: smtp.purelymail.com, port 465, SSL).</p>
			<?php else : ?>
				<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px; margin-bottom:20px;">
					<?php foreach ( $connections as $conn ) :
						$is_default = ( $settings['default_connection'] === $conn['id'] ); ?>
						<div style="border:1px solid #e2e8f0; border-radius:14px; padding:16px;">
							<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
								<div style="font-size:14px; font-weight:900; color:#0f172a;"><?php echo esc_html( $conn['label'] ); ?></div>
								<?php if ( $is_default ) : ?>
									<span style="background:#f1f5f9; color:#334155; font-weight:800; font-size:9px; padding:3px 8px; border-radius:99px; text-transform:uppercase; letter-spacing:0.5px;">Default</span>
								<?php endif; ?>
							</div>
							<div style="font-size:12px; color:#64748b; margin-top:6px; font-family:ui-monospace,monospace;"><?php echo esc_html( $conn['host'] ); ?> : <?php echo esc_html( $conn['port'] ); ?> (<?php echo esc_html( strtoupper( $conn['encryption'] ?? 'ssl' ) ); ?>)</div>
							<div style="font-size:12px; color:#64748b; margin-top:2px;">From: <?php echo esc_html( $conn['from_email'] ?? '' ); ?></div>

							<form method="POST" style="display:flex; gap:6px; margin-top:12px; align-items:center; flex-wrap:wrap;">
								<?php wp_nonce_field( 'aws_mail_center', 'aws_mail_nonce' ); ?>
								<input type="hidden" name="aws_mail_action" value="send_test">
								<input type="hidden" name="test_connection" value="<?php echo esc_attr( $conn['id'] ); ?>">
								<input type="email" name="test_to" placeholder="youremail@domain.com" required style="flex:1 1 130px; padding:7px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px;">
								<button type="submit" style="background:#0f172a; color:#ffffff; border:none; border-radius:8px; padding:8px 12px; font-size:11px; font-weight:800; cursor:pointer;">Test</button>
							</form>

							<div style="display:flex; gap:10px; margin-top:10px; align-items:center;">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-email-center&edit=' . $conn['id'] ) ); ?>" style="font-size:12px; font-weight:800; color:#ad5cff; text-decoration:none;">Edit</a>
								<?php if ( ! $is_default ) : ?>
									<form method="POST" style="display:inline;">
										<?php wp_nonce_field( 'aws_mail_center', 'aws_mail_nonce' ); ?>
										<input type="hidden" name="aws_mail_action" value="set_default">
										<input type="hidden" name="conn_id" value="<?php echo esc_attr( $conn['id'] ); ?>">
										<button type="submit" style="background:none; border:none; font-size:12px; font-weight:800; color:#334155; cursor:pointer; padding:0;">Set default</button>
									</form>
									<form method="POST" style="display:inline;" onsubmit="return confirm('Delete this connection?');">
										<?php wp_nonce_field( 'aws_mail_center', 'aws_mail_nonce' ); ?>
										<input type="hidden" name="aws_mail_action" value="delete_connection">
										<input type="hidden" name="conn_id" value="<?php echo esc_attr( $conn['id'] ); ?>">
										<button type="submit" style="background:none; border:none; font-size:12px; font-weight:800; color:#ef4444; cursor:pointer; padding:0;">Delete</button>
									</form>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			$f_label = $edit_conn['label'] ?? '';
			$f_host  = $edit_conn['host'] ?? '';
			$f_port  = $edit_conn['port'] ?? 465;
			$f_enc   = $edit_conn['encryption'] ?? 'ssl';
			$f_user  = $edit_conn['username'] ?? '';
			$f_mail  = $edit_conn['from_email'] ?? '';
			$f_name  = $edit_conn['from_name'] ?? '';
			?>
			<form method="POST" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:20px;">
				<?php wp_nonce_field( 'aws_mail_center', 'aws_mail_nonce' ); ?>
				<input type="hidden" name="aws_mail_action" value="save_connection">
				<input type="hidden" name="conn_id" value="<?php echo esc_attr( $edit_id ); ?>">

				<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px;">
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">Label
						<input type="text" name="conn_label" value="<?php echo esc_attr( $f_label ); ?>" required placeholder="e.g. Community Mail" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">SMTP Host
						<input type="text" name="conn_host" value="<?php echo esc_attr( $f_host ); ?>" required placeholder="smtp.purelymail.com" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">Port
						<select name="conn_port" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
							<option value="465" <?php selected( $f_port, 465 ); ?>>465 (SSL)</option>
							<option value="587" <?php selected( $f_port, 587 ); ?>>587 (TLS)</option>
						</select>
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">Username
						<input type="text" name="conn_username" value="<?php echo esc_attr( $f_user ); ?>" required placeholder="mail@domain.com" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">Password
						<input type="password" name="conn_password" value="" placeholder="<?php echo $edit_conn ? 'Leave blank to keep current' : 'SMTP password'; ?>" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">From Email
						<input type="email" name="conn_from_email" value="<?php echo esc_attr( $f_mail ); ?>" placeholder="mail@domain.com" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
					<label style="display:block; font-size:12px; font-weight:800; color:#334155;">From Name
						<input type="text" name="conn_from_name" value="<?php echo esc_attr( $f_name ); ?>" placeholder="AWS Air University" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
					</label>
				</div>

				<div style="display:flex; gap:10px; margin-top:16px; align-items:center;">
					<button type="submit" style="background:#ad5cff; color:#ffffff; border:none; border-radius:10px; padding:12px 24px; font-size:13px; font-weight:900; cursor:pointer;">
						<?php echo $edit_conn ? 'Update connection' : 'Add connection'; ?>
					</button>
					<?php if ( $edit_conn ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aws-email-center' ) ); ?>" style="font-size:12px; font-weight:800; color:#64748b; text-decoration:none;">Cancel edit</a>
					<?php endif; ?>
					<span style="font-size:11px; color:#94a3b8;">Passwords are stored in the WordPress database, visible only to site administrators.</span>
				</div>
			</form>
		</div>

		<!-- ============ ROUTING ============ -->
		<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px;">
			<h2 style="margin:0 0 6px; font-size:15px; font-weight:900; color:#0f172a;">Routing</h2>
			<p style="margin:0 0 18px; font-size:12px; color:#64748b;">Choose which connection sends mail for each product. Anything not listed here falls back to the default connection.</p>

			<form method="POST">
				<?php wp_nonce_field( 'aws_mail_center', 'aws_mail_nonce' ); ?>
				<input type="hidden" name="aws_mail_action" value="save_settings">

				<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px;">
					<?php
					$contexts = array(
						'reinvent' => 're:Invent Registrations',
						'exam'     => 'Mock Exams',
						'general'  => 'Default (everything else)',
					);
					foreach ( $contexts as $ctx => $ctx_label ) :
						$current = $settings['routing'][ $ctx ] ?? ''; ?>
						<label style="display:block; font-size:12px; font-weight:800; color:#334155;"><?php echo esc_html( $ctx_label ); ?>
							<select name="route_<?php echo esc_attr( $ctx ); ?>" style="width:100%; margin-top:4px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px;">
								<option value="">— Default connection —</option>
								<?php foreach ( $connections as $conn ) : ?>
									<option value="<?php echo esc_attr( $conn['id'] ); ?>" <?php selected( $current, $conn['id'] ); ?>><?php echo esc_html( $conn['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endforeach; ?>
				</div>

				<div style="margin-top:16px; padding:14px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
					<label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:800; color:#334155;">
						<input type="checkbox" name="notify_admin_rsvp" <?php checked( $settings['notify_admin_rsvp'], 'yes' ); ?>>
						Notify admin of new re:Invent RSVPs
					</label>
					<label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:800; color:#334155;">
						Admin email
						<input type="email" name="admin_email" value="<?php echo esc_attr( $settings['admin_email'] ); ?>" style="padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; width:230px;">
					</label>
				</div>

				<button type="submit" style="background:#0f172a; color:#ffffff; border:none; border-radius:10px; padding:12px 24px; font-size:13px; font-weight:900; cursor:pointer; margin-top:16px;">Save routing</button>
			</form>
		</div>

		<!-- ============ MAIL LOG ============ -->
		<?php
		global $wpdb;
		$table   = $wpdb->prefix . 'aws_mail_log';
		$log     = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 50" );
		?>
		<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
			<h2 style="margin:0 0 6px; font-size:15px; font-weight:900; color:#0f172a;">Mail Log</h2>
			<p style="margin:0 0 18px; font-size:12px; color:#64748b;">Latest 50 sends. Older entries are pruned automatically.</p>

			<table class="widefat striped" style="border-radius:10px; overflow:hidden;">
				<thead>
					<tr>
						<th style="width:130px;">Time</th>
						<th style="width:90px;">Context</th>
						<th style="width:140px;">Connection</th>
						<th>To</th>
						<th>Subject</th>
						<th style="width:90px;">Status</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $log ) ) : ?>
						<tr><td colspan="6" style="padding:16px; color:#64748b;">No emails sent yet.</td></tr>
					<?php else : ?>
						<?php foreach ( $log as $row ) : ?>
							<tr>
								<td style="font-family:ui-monospace,monospace; font-size:11px;"><?php echo esc_html( $row->sent_at ); ?></td>
								<td style="font-size:11px; font-weight:700; text-transform:uppercase;"><?php echo esc_html( $row->context ); ?></td>
								<td style="font-size:11px;"><?php echo esc_html( $row->connection ); ?></td>
								<td style="font-size:11px;"><?php echo esc_html( $row->to_email ); ?></td>
								<td style="font-size:11px;"><?php echo esc_html( $row->subject ); ?><?php if ( $row->error ) : ?><br><span style="color:#ef4444; font-size:10px;"><?php echo esc_html( $row->error ); ?></span><?php endif; ?></td>
								<td style="text-align:center;">
									<?php if ( 'sent' === $row->status ) : ?>
										<span style="background:#ecfdf5; color:#059669; font-weight:800; font-size:10px; padding:3px 8px; border-radius:99px;">SENT</span>
									<?php else : ?>
										<span style="background:#fef2f2; color:#dc2626; font-weight:800; font-size:10px; padding:3px 8px; border-radius:99px;">FAILED</span>
									<?php endif; ?>
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
