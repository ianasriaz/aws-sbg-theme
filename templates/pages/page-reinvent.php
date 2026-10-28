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
<body <?php body_class(); ?> style="margin:0; background:#ffffff; color:#0f172a;">
<?php
$title    = 'AWS re:Invent Watch & Build Party Islamabad 2026';
$subtitle = 'An invite-only evening for builders, dreamers, and cloud enthusiasts. Apply below and we will notify you once your seat is approved.';
$date     = '';
$location = 'Islamabad, Pakistan';

echo aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location );

wp_footer();
?>
</body>
</html>
