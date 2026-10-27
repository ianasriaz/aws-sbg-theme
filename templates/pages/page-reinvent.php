<?php
/**
 * Template Name: re:Invent Registration
 * Description: Dedicated registration page for AWS re:Invent Watch & Build Party.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$title    = 'AWS re:Invent Watch & Build Party Islamabad 2026';
$subtitle = 'An invite-only evening for builders, dreamers, and cloud enthusiasts. Apply below and we will notify you once your seat is approved.';
$date     = '';
$location = 'Islamabad, Pakistan';
?>

<main class="min-h-screen bg-gray-50">
	<div class="px-0 md:px-8 py-0 md:py-12">
		<div class="max-w-5xl mx-auto">
			<?php echo aws_sbg_render_reinvent_form( $title, $subtitle, $date, $location ); ?>
		</div>
	</div>
</main>

<?php
get_footer();
