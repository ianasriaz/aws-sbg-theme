<?php
/**
 * Template Name: Call for Speakers (Standalone)
 * Description: Distraction-free, fast 90-second speaker pitch form. Resolves the
 * event via ?event=<slug-or-id>, or default latest active event.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event = null;

if ( isset( $_GET['event'] ) ) {
	$event = aws_sbg_cfs_resolve_event( sanitize_text_field( wp_unslash( $_GET['event'] ) ) );
} else {
	$event = aws_sbg_cfs_resolve_event();
}

add_filter( 'show_admin_bar', '__return_false' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $event ? esc_html( 'Call for Speakers — ' . $event->post_title ) : 'Call for Speakers'; ?></title>
	<?php wp_head(); ?>
	<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
</head>
<body <?php body_class(); ?> style="margin:0; background:#F8FAFC; color:#0F141F;">
<?php
if ( $event && function_exists( 'aws_sbg_cfs_render_form' ) ) {
	echo aws_sbg_cfs_render_form( $event->ID );
} else {
	echo '<div style="max-width:640px;margin:0 auto;padding:80px 24px;text-align:center;font-family:system-ui,sans-serif;"><p style="color:#64748b;">No active event found for Call for Speakers. Add <code style="background:#f1f5f9;padding:2px 6px;border-radius:6px;">?event=your-event-slug</code> to the URL.</p></div>';
}

wp_footer();
?>
</body>
</html>
