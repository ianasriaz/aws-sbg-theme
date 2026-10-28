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

function aws_sbg_email_template( $kicker, $title, $body_html, $cta_url = '', $cta_label = '' ) {
	$cta_html = '';
	if ( $cta_url && $cta_label ) {
		$cta_html = '<table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin:22px 0 6px 0;"><tr><td align="center" bgcolor="#00DFD8" style="border-radius:10px;"><a href="' . esc_url( $cta_url ) . '" target="_blank" style="display:inline-block;padding:14px 30px;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;font-size:13px;font-weight:800;letter-spacing:1px;color:#06040C;text-decoration:none;border-radius:10px;">' . esc_html( $cta_label ) . '</a></td></tr></table>';
	}

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="color-scheme" content="dark">
	<title><?php echo esc_html( $title ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#06040C;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#06040C;padding:24px 10px;">
		<tr>
			<td align="center">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width:480px;width:100%;background-color:#0d1322;border:1px solid #1e293b;border-radius:16px;">
					<tr>
						<td style="padding:28px 24px;">
							<div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#00DFD8;margin-bottom:10px;"><?php echo esc_html( $kicker ); ?></div>
							<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;font-size:22px;font-weight:800;color:#FFFFFF;line-height:1.25;margin-bottom:14px;"><?php echo esc_html( $title ); ?></div>
							<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;font-size:14px;color:#cbd5e1;line-height:1.7;"><?php echo $body_html; ?></div>
							<?php echo $cta_html; ?>
							<div style="border-top:1px solid #1a2236;margin-top:24px;padding-top:14px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;letter-spacing:1px;color:#64748b;">AWS STUDENT BUILDER GROUP · AIR UNIVERSITY ISLAMABAD · <?php echo esc_html( date( 'Y' ) ); ?></div>
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

function aws_sbg_mail_send( $context, $to, $subject, $kicker, $title, $body_html, $cta_url = '', $cta_label = '', $connection_id = '' ) {
	$conn = null;

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

	$html    = aws_sbg_email_template( $kicker, $title, $body_html, $cta_url, $cta_label );
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
