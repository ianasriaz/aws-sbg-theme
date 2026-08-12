<?php
/**
 * Single Activity / Event Template
 * File: single-aws_event.php
 * Package: AWS_SBG_Theme
 */

get_header();

$event_id = get_the_ID();
?>

<div class="min-h-screen bg-[#07040d] text-white font-sans selection:bg-[#ad5cff] selection:text-white py-8 md:py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
	
	<!-- Background Gradients -->
	<div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#ad5cff]/15 rounded-full blur-[140px] pointer-events-none"></div>
	<div class="absolute bottom-10 right-10 w-[400px] h-[400px] bg-[#00ef88]/10 rounded-full blur-[120px] pointer-events-none"></div>

	<div class="max-w-4xl mx-auto relative z-10 space-y-6">

		<!-- TITLE OUTSIDE FORM -->
		<h1 class="text-4xl sm:text-6xl font-black text-center text-white uppercase tracking-tight font-display mb-2">
			Register Now
		</h1>

		<!-- REGISTRATION FORM CONTAINER -->
		<div id="register-section">
			<?php echo do_shortcode( '[aws_activity_form activity_id="' . $event_id . '"]' ); ?>
		</div>

	</div>
</div>

<?php
get_footer();
