<?php
/**
 * Template Name: Sponsor Deck (AWS re:Invent 2026)
 * Description: Standalone, distraction-free sponsorship proposal deck for AWS re:Invent 2026 Watch & Build Party Islamabad.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. CLEAR 404 & SHIELD FROM SEARCH ENGINES (UNLISTED)
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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
	<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
	<title><?php esc_html_e( 'Sponsorship Proposal · AWS re:Invent 2026 Watch & Build Party Islamabad', 'aws-sbg' ); ?></title>

	<?php wp_head(); ?>

	<!-- Design Kit Stylesheet -->
	<link rel="stylesheet" id="aws-sbg-sponsor-deck-css" href="<?php echo esc_url( $asset_uri . '/design-kit.css?ver=' . $ver ); ?>" />

	<style id="aws-sbg-sponsor-deck-layout">
		html, body {
			margin: 0 !important;
			padding: 0 !important;
			background: #06040C !important;
			color: #FFFFFF !important;
			min-height: 100%;
			width: 100%;
			overflow-x: auto;
		}
		#root {
			min-height: 100vh;
			width: 100%;
			padding: 28px 16px 64px;
			box-sizing: border-box;
		}
	</style>

	<script>
		window.__AWS_VIEW__ = 'sponsor-deck';
		window.REINVENT_KIT_ENV = {
			view: 'sponsor-deck',
			title: 'AWS re:Invent 2026 Sponsorship Proposal'
		};
	</script>
</head>
<body <?php body_class( 'aws-sbg-sponsor-deck-body' ); ?>>
<?php wp_body_open(); ?>
	<div id="root" data-view="sponsor-deck">
		<noscript>
			<div style="max-width:600px;margin:80px auto;padding:24px;text-align:center;color:#ffffff;font-family:sans-serif;">
				<h2 style="font-size:20px;margin-bottom:12px;">JavaScript Required</h2>
				<p style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.6;">
					Please enable JavaScript in your browser to view the AWS re:Invent 2026 Sponsorship Proposal.
				</p>
			</div>
		</noscript>
	</div>

	<!-- Design Kit Module Bundle -->
	<script type="module" id="aws-sbg-sponsor-deck-js" src="<?php echo esc_url( $asset_uri . '/design-kit.js?ver=' . $ver ); ?>"></script>

	<?php wp_footer(); ?>
</body>
</html>
