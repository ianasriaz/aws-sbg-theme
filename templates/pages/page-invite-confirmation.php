<?php
/**
 * Template Name: Invitation Confirmation (Standalone)
 * Description: Distraction-free, full-screen confirmation form. Resolves the
 * event via ?event=<slug-or-id>, or a page-level _aws_invite_event_id meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event = null;

if ( isset( $_GET['event'] ) ) {
	$event = aws_sbg_invite_resolve_event( sanitize_text_field( wp_unslash( $_GET['event'] ) ) );
}

if ( ! $event && is_singular() ) {
	$page_event_id = get_post_meta( get_the_ID(), '_aws_invite_event_id', true );
	if ( $page_event_id ) {
		$event = aws_sbg_invite_resolve_event( intval( $page_event_id ) );
	}
}

add_filter( 'show_admin_bar', '__return_false' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> style="margin:0; background:#F8FAFC; color:#0F141F;">
<?php
if ( $event && function_exists( 'aws_sbg_invite_render_form' ) ) {
	echo aws_sbg_invite_render_form( $event->ID );
} else {
	echo '<div style="max-width:640px;margin:0 auto;padding:80px 24px;text-align:center;font-family:system-ui,sans-serif;"><p style="color:#64748b;">No invitation event selected. Add <code style="background:#f1f5f9;padding:2px 6px;border-radius:6px;">?event=your-event-slug</code> to the URL.</p></div>';
}

wp_footer();
?>
</body>
</html>
