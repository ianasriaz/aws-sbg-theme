<?php
/**
 * Template Name: re:Invent Guests
 * Description: Distraction-free VIP Guest & Community Leader pass form for AWS re:Invent 2026.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'show_admin_bar', '__return_false' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> style="margin:0; background:#06040c; color:#ffffff;">
<?php wp_body_open(); ?>
<?php
if ( function_exists( 'aws_sbg_render_reinvent_guests_form' ) ) {
	echo aws_sbg_render_reinvent_guests_form();
}

wp_footer();
?>
</body>
</html>
