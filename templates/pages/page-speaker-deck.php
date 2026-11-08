<?php
/**
 * Template Name: Speaker Deck (Call for Speakers)
 * Description: Standalone, distraction-free speaker proposal & guide deck for AWS re:Invent 2026 Watch & Build Party Islamabad. Invite-only, unlisted, non-downloadable, no password required.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. CLEAR 404 & SHIELD FROM SEARCH ENGINES & WEB CRAWLERS (INVITE-ONLY)
global $wp_query;
if ( isset( $wp_query ) && is_object( $wp_query ) ) {
	$wp_query->is_404  = false;
	$wp_query->is_page = true;
}
status_header( 200 );
header( 'X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true );

// 2. FULLSCREEN DISTRACTION-FREE CANVAS
add_filter( 'show_admin_bar', '__return_false' );

$asset_dir = get_template_directory() . '/assets/design-kit';
$asset_uri = get_template_directory_uri() . '/assets/design-kit';
$ver       = file_exists( $asset_dir . '/design-kit.js' ) ? (string) filemtime( $asset_dir . '/design-kit.js' ) : '1.0.0';
?><!DOCTYPE html>
<html <?php language_attributes(); ?> style="background:#06040C; color-scheme:dark;">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
	<title><?php esc_html_e( 'Call for Speakers · AWS re:Invent Watch & Build Party 2026', 'aws-sbg' ); ?></title>

	<?php wp_head(); ?>

	<!-- Design Kit Stylesheet -->
	<link rel="stylesheet" id="aws-sbg-speaker-deck-css" href="<?php echo esc_url( $asset_uri . '/design-kit.css?ver=' . $ver ); ?>" />

	<!-- Anti-download & Anti-print Guards -->
	<style id="aws-sbg-speaker-deck-protections">
		html, body {
			margin: 0 !important;
			padding: 0 !important;
			background: #06040C !important;
			color: #FFFFFF !important;
			overflow: hidden !important;
			height: 100% !important;
			height: 100dvh !important;
			width: 100% !important;
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			user-select: none;
		}

		/* Strictly suppress any browser print dialogue / print preview */
		@media print {
			html, body, #root, * {
				display: none !important;
				visibility: hidden !important;
				height: 0 !important;
				width: 0 !important;
				overflow: hidden !important;
			}
		}

		#root {
			height: 100% !important;
			height: 100dvh !important;
			width: 100% !important;
			display: flex;
			flex-direction: column;
			overflow: hidden;
		}
	</style>

	<script>
		// Prevent context menu (right-click save/inspect)
		document.addEventListener('contextmenu', function(e) {
			e.preventDefault();
		});

		// Block print & save keystrokes (Ctrl+P, Ctrl+S, Cmd+P, Cmd+S)
		window.addEventListener('keydown', function(e) {
			if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's' || e.key === 'P' || e.key === 'S')) {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		});

		// Explicit view signal for Vite bundle
		window.__AWS_VIEW__ = 'speaker-deck';
		window.REINVENT_KIT_ENV = {
			view: 'speaker-deck',
			title: 'AWS re:Invent 2026 Speaker Deck'
		};
	</script>
</head>
<body <?php body_class( 'aws-sbg-speaker-deck-body' ); ?> oncontextmenu="return false;">
	<div id="root" data-view="speaker-deck">
		<noscript>
			<div style="max-width:600px;margin:80px auto;padding:24px;text-align:center;color:#ffffff;font-family:sans-serif;">
				<h2 style="font-size:20px;margin-bottom:12px;">JavaScript Required</h2>
				<p style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.6;">
					Please enable JavaScript in your browser to view the AWS re:Invent Call for Speakers presentation.
				</p>
			</div>
		</noscript>
	</div>

	<!-- Design Kit Module Bundle -->
	<script type="module" id="aws-sbg-speaker-deck-js" src="<?php echo esc_url( $asset_uri . '/design-kit.js?ver=' . $ver ); ?>"></script>

	<?php wp_footer(); ?>
</body>
</html>
