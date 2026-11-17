<?php
/**
 * Template Name: Community Partner Form
 * Description: Distraction-free Community Partner application page.
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
if ( function_exists( 'aws_sbg_render_community_partner_form' ) ) {
	echo aws_sbg_render_community_partner_form();
}

wp_footer();
?>
</body>
</html>
