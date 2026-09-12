<?php
/**
 * Single Activity / Event Template
 * File: single-aws_event.php
 * Package: AWS_SBG_Theme
 */

get_header();

$event_id = get_the_ID();
?>

<!-- Scoped styles to ensure zero conflict with theme style.css dark defaults -->
<style>
	.single-event-wrapper {
		background-color: #ffffff !important;
		color: #0f172a !important;
	}
	.single-event-wrapper h1,
	.single-event-wrapper h2,
	.single-event-wrapper h3 {
		color: #020617 !important;
	}
	.single-event-wrapper p {
		color: #334155 !important;
	}
</style>

<div class="single-event-wrapper bg-white text-slate-900 min-h-screen py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
	<div class="max-w-4xl mx-auto space-y-8">

		<!-- TITLE OUTSIDE FORM -->
		<h1 class="text-3xl sm:text-5xl font-black text-center text-slate-950 uppercase tracking-tight mb-2">
			Register <span class="text-[#9353ff]">Now</span>
		</h1>

		<!-- REGISTRATION FORM CONTAINER -->
		<div id="register-section">
			<?php echo do_shortcode( '[aws_activity_form activity_id="' . $event_id . '"]' ); ?>
		</div>

	</div>
</div>

<?php
get_footer();
