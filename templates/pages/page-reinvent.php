<?php
/**
 * Template Name: re:Invent Registration
 * Description: Distraction-free registration page for AWS re:Invent Watch & Build Party (no site header/footer).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Full-screen landing page: hide the admin bar and skip site chrome.
add_filter( 'show_admin_bar', '__return_false' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> style="margin:0; background:#06040c; color:#ffffff;">
<?php
$title    = 'AWS re:Invent Watch & Build Party Islamabad 2026';
$subtitle = 'Experience AWS re:Invent in Islamabad.';
$date     = '';
$location = 'Islamabad, Pakistan';

echo aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location );

wp_footer();
?>
</body>
</html>
