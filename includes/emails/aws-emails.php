<?php
/**
 * AWS SBG Email Center — shared mail system
 * Multiple SMTP connections, per-context routing, branded kit-style HTML
 * template, and a send log. All site communications go through
 * aws_sbg_mail_send( $context, ... ) which resolves the connection from
 * the Email Center routing map.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================================
 * 1. LOG TABLE
 * ======================================================================== */

function aws_sbg_mail_init_db() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table           = $wpdb->prefix . 'aws_mail_log';
	$sql             = "CREATE TABLE $table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		sent_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		context varchar(50) NOT NULL DEFAULT '',
		connection varchar(100) NOT NULL DEFAULT '',
		to_email varchar(255) NOT NULL DEFAULT '',
		subject varchar(255) NOT NULL DEFAULT '',
		status varchar(20) NOT NULL DEFAULT '',
		error text NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'admin_init', 'aws_sbg_mail_init_db' );

/* ============================================================================
 * 2. CONNECTIONS + SETTINGS REGISTRY
 * ======================================================================== */

function aws_sbg_mail_get_connections() {
	$connections = get_option( 'aws_sbg_mail_connections', array() );
	return is_array( $connections ) ? $connections : array();
}

function aws_sbg_mail_get_settings() {
	$defaults = array(
		'default_connection' => '',
		'routing'            => array(),
		'notify_admin_rsvp'  => 'yes',
		'admin_email'        => get_option( 'admin_email' ),
	);
	$settings = get_option( 'aws_sbg_mail_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();

	if ( empty( $settings['admin_email'] ) ) {
		$settings['admin_email'] = $defaults['admin_email'];
	}

	return array_merge( $defaults, $settings );
}

function aws_sbg_mail_get_connection_for_context( $context ) {
	$settings    = aws_sbg_mail_get_settings();
	$connections = aws_sbg_mail_get_connections();

	$id = isset( $settings['routing'][ $context ] ) ? $settings['routing'][ $context ] : '';
	if ( ! $id || ! isset( $connections[ $id ] ) ) {
		$id = $settings['default_connection'];
	}

	if ( $id && isset( $connections[ $id ] ) ) {
		return $connections[ $id ];
	}

	return null;
}

/* ============================================================================
 * 3. LEGACY CONFIG MIGRATION (aws_sbg_smtp_config -> connection)
 * ======================================================================== */

function aws_sbg_mail_migrate_legacy() {
	if ( get_option( 'aws_sbg_mail_migrated' ) ) {
		return;
	}

	$legacy = get_option( 'aws_sbg_smtp_config', array() );

	if ( ! empty( $legacy['host'] ) && ! empty( $legacy['username'] ) && empty( aws_sbg_mail_get_connections() ) ) {
		$id          = 'migrated-purelymail';
		$connections = array();

		$connections[ $id ] = array(
			'id'         => $id,
			'label'      => 'Migrated (Purelymail)',
			'host'       => $legacy['host'],
			'port'       => ! empty( $legacy['port'] ) ? intval( $legacy['port'] ) : 465,
			'encryption' => ! empty( $legacy['encryption'] ) ? $legacy['encryption'] : 'ssl',
			'username'   => $legacy['username'],
			'password'   => $legacy['password'],
			'from_email' => ! empty( $legacy['from_email'] ) ? $legacy['from_email'] : $legacy['username'],
			'from_name'  => ! empty( $legacy['from_name'] ) ? $legacy['from_name'] : 'AWS Air University',
		);

		update_option( 'aws_sbg_mail_connections', $connections );

		$settings                    = aws_sbg_mail_get_settings();
		$settings['default_connection'] = $id;
		$settings['routing']['exam']    = $id;
		update_option( 'aws_sbg_mail_settings', $settings );
	}

	update_option( 'aws_sbg_mail_migrated', 1 );
}
add_action( 'admin_init', 'aws_sbg_mail_migrate_legacy' );

/* ============================================================================
 * 4. BRANDED KIT-STYLE HTML TEMPLATE
 * ======================================================================== */

function aws_sbg_email_template( $kicker, $title, $body_html, $cta_url = '', $cta_label = '', $cta_sub = '', $closing_html = '' ) {
	// Sanitize legacy hardcoded dark-mode colors so all text renders cleanly on light cards
	$body_html = str_ireplace(
		array( 'color:#FFFFFF', 'color:#FFF', 'color:#00DFD8', 'color:#d0d0d0', 'color:#cbd5e1', 'color:#e2e8f0' ),
		array( 'color:#0f172a', 'color:#0f172a', 'color:#7928ca', 'color:#64748b', 'color:#334155', 'color:#475569' ),
		$body_html
	);

	$aws_logo_url = get_template_directory_uri() . '/assets/images/icons/aws-dark.png';

	$cta_html = '';
	if ( $cta_url && $cta_label ) {
		$is_wa     = ( false !== stripos( $cta_label, 'whatsapp' ) );
		$btn_bg    = $is_wa ? '#16a34a' : '#232F3E';
		$btn_color = '#FFFFFF';

		$cta_html = '<table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin:18px 0 4px 0;">'
			. '<tr><td align="left" bgcolor="' . esc_attr( $btn_bg ) . '" style="border-radius:6px;">'
			. '<a class="email-btn" href="' . esc_url( $cta_url ) . '" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:11px 22px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;font-size:13.5px;font-weight:600;letter-spacing:0.2px;color:' . esc_attr( $btn_color ) . ';text-decoration:none;border-radius:6px;white-space:nowrap;">'
			. esc_html( $cta_label )
			. '</a></td></tr>';

		if ( $cta_sub ) {
			$cta_html .= '<tr><td align="left" style="padding-top:6px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;font-size:12px;color:#64748b;">'
				. esc_html( $cta_sub )
				. '</td></tr>';
		}
		$cta_html .= '</table>';
	}

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="color-scheme" content="light">
	<meta name="supported-color-schemes" content="light">
	<title><?php echo esc_html( $title ? $title : 'AWS Student Builder Group' ); ?></title>
	<style>
		body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f4f6f8; }
		table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
		@media only screen and (max-width: 560px) {
			.email-outer-td { padding: 14px 8px !important; }
			.email-header-td { padding: 16px 20px !important; }
			.email-card-td { padding: 24px 20px !important; }
			.email-footer-td { padding: 16px 20px !important; }
			.email-title { font-size: 18px !important; }
			.email-body { font-size: 14px !important; }
			.pass-col { display: block !important; width: 100% !important; text-align: center !important; }
			.pass-qr-col { padding-top: 16px !important; text-align: center !important; }
		}
	</style>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;-webkit-font-smoothing:antialiased;">
	<table class="email-outer" width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation" style="background-color:#f4f6f8;width:100%;">
		<tr>
			<td class="email-outer-td" align="center" style="padding:32px 12px;">
				<table class="email-card" width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation" style="max-width:580px;width:100%;background-color:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
					<!-- Brand Header Strip -->
					<tr>
						<td class="email-header-td" style="background-color:#f8fafc;border-bottom:1px solid #e2e8f0;padding:18px 34px;">
							<table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
								<tr>
									<td align="left" valign="middle">
										<img src="<?php echo esc_url( $aws_logo_url ); ?>" alt="AWS" width="52" style="display:block;width:52px;height:auto;border:0;" />
									</td>
									<td align="right" valign="middle" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#64748b;">
										STUDENT BUILDER GROUP
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<!-- Main Body -->
					<tr>
						<td class="email-card-td" style="padding:34px 34px 28px 34px;">
							<?php if ( $kicker ) : ?>
								<div class="email-kicker" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#64748b;margin-bottom:8px;"><?php echo esc_html( $kicker ); ?></div>
							<?php endif; ?>
							<?php if ( $title ) : ?>
								<div class="email-title" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:19px;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:16px;"><?php echo esc_html( $title ); ?></div>
							<?php endif; ?>
							<div class="email-body" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14.5px;color:#1f2937;line-height:1.65;"><?php echo $body_html; ?></div>
							<?php echo $cta_html; ?>
							<?php if ( $closing_html ) : ?>
								<div class="email-closing" style="margin-top:18px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13.5px;color:#475569;line-height:1.55;"><?php echo $closing_html; ?></div>
							<?php endif; ?>
						</td>
					</tr>
					<!-- Unified Footer -->
					<tr>
						<td class="email-footer-td" align="center" style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 34px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10.5px;font-weight:600;letter-spacing:0.08em;color:#64748b;">
							TEAM - AWS SBG AIR UNIVERSITY ISLAMABAD
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	<?php
	return ob_get_clean();
}

/* ============================================================================
 * 5. CONTEXT-AWARE SENDER + MAIL LOG
 * ======================================================================== */

function aws_sbg_mail_send( $context, $to, $subject, $kicker, $title, $body_html, $cta_url = '', $cta_label = '', $connection_id = '', $cta_sub = '', $closing_html = '' ) {
	$conn = null;

	// Subject must always be plain text without HTML entities (&amp;, &#039;, etc.)
	$subject = html_entity_decode( wp_strip_all_tags( $subject ), ENT_QUOTES, 'UTF-8' );

	if ( $connection_id ) {
		$connections = aws_sbg_mail_get_connections();
		if ( isset( $connections[ $connection_id ] ) ) {
			$conn = $connections[ $connection_id ];
		}
	}

	if ( ! $conn ) {
		$conn = aws_sbg_mail_get_connection_for_context( $context );
	}

	$conn_label = $conn ? ( ! empty( $conn['label'] ) ? $conn['label'] : $conn['host'] ) : 'wp-native';

	$html    = aws_sbg_email_template( $kicker, $title, $body_html, $cta_url, $cta_label, $cta_sub, $closing_html );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$hook = null;
	if ( $conn && ! empty( $conn['host'] ) && ! empty( $conn['username'] ) ) {
		$hook = function( $phpmailer ) use ( $conn ) {
			$phpmailer->isSMTP();
			$phpmailer->Host     = $conn['host'];
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = trim( $conn['username'] );
			$phpmailer->Password = trim( $conn['password'] );

			$encryption = ! empty( $conn['encryption'] ) ? strtolower( trim( $conn['encryption'] ) ) : 'ssl';
			$port       = intval( $conn['port'] );
			if ( $port <= 0 ) {
				$port = ( 'tls' === $encryption ) ? 587 : 465;
			}

			$phpmailer->Port       = $port;
			$phpmailer->SMTPSecure = $encryption;
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);

			$from_email          = ! empty( $conn['from_email'] ) ? trim( $conn['from_email'] ) : trim( $conn['username'] );
			$phpmailer->From     = $from_email;
			$phpmailer->FromName = ! empty( $conn['from_name'] ) ? trim( $conn['from_name'] ) : 'AWS Air University';
			$phpmailer->Sender   = $from_email;
		};
		add_action( 'phpmailer_init', $hook, 100 );
	}

	$GLOBALS['aws_sbg_last_mail_error'] = '';
	$ok                                 = wp_mail( $to, $subject, $html, $headers );

	if ( $hook ) {
		remove_action( 'phpmailer_init', $hook, 100 );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'aws_mail_log';
	$wpdb->insert(
		$table,
		array(
			'context'    => sanitize_key( $context ),
			'connection' => $conn_label,
			'to_email'   => $to,
			'subject'    => $subject,
			'status'     => $ok ? 'sent' : 'failed',
			'error'      => $ok ? '' : ( ! empty( $GLOBALS['aws_sbg_last_mail_error'] ) ? $GLOBALS['aws_sbg_last_mail_error'] : 'Unknown error' ),
		)
	);

	$cutoff = $wpdb->get_var( "SELECT id FROM $table ORDER BY id DESC LIMIT 1 OFFSET 499" );
	if ( $cutoff ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id < %d", $cutoff ) );
	}

	return $ok;
}
