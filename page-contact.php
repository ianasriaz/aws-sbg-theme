
<?php
/**
 * Template Name: Contact Us
 * Description: Clean, light-theme contact page for AWS Student Builder Group.
 * Package: AWS_SBG_Theme
 */

get_header();
?>

<!-- Scoped styles to ensure zero conflict with theme style.css dark defaults -->
<style>
	.contact-page-wrapper {
		background-color: #ffffff !important;
		color: #0f172a !important;
	}
	.contact-page-wrapper h1,
	.contact-page-wrapper h2,
	.contact-page-wrapper h3 {
		color: #020617 !important;
	}
	.contact-page-wrapper p {
		color: #334155 !important;
	}
	.contact-page-wrapper .text-brand {
		color: #9353ff !important;
	}
	.contact-page-wrapper .card-clean {
		background-color: #ffffff !important;
		border: 1px solid #e2e8f0 !important;
		transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
	}
	.contact-page-wrapper .card-clean:hover {
		border-color: #9353ff !important;
		transform: translateY(-2px);
		box-shadow: 0 10px 25px -5px rgba(147, 83, 255, 0.1);
	}
</style>

<div class="contact-page-wrapper bg-white text-slate-900 min-h-[75vh] py-12 sm:py-20 px-4 sm:px-6 lg:px-8">
	<div class="max-w-4xl mx-auto space-y-12">
		
		<!-- Page Header -->
		<div class="text-center space-y-3">
			<h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-950 uppercase">
				Contact <span class="text-[#9353ff]">Us</span>
			</h1>
			<p class="text-slate-600 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
				<?php esc_html_e( 'Reach out to our leadership team directly via email or phone. We\'d love to hear from you.', 'aws-sbg-theme' ); ?>
			</p>
		</div>

		<!-- Contact Cards Grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			
			<!-- Email Card -->
			<a href="mailto:info@awscloudclubau.com" class="card-clean rounded-2xl p-8 flex flex-col items-center text-center group">
				<div class="w-16 h-16 rounded-2xl bg-[#9353ff]/10 border border-[#9353ff]/20 text-[#9353ff] flex items-center justify-center mb-5 group-hover:bg-[#9353ff] group-hover:text-white transition-all duration-200">
					<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
					</svg>
				</div>
				<h2 class="text-xl font-bold text-slate-950 mb-2 uppercase tracking-tight">Email Us</h2>
				<p class="text-slate-600 mb-5 text-xs sm:text-sm">For partnerships, student inquiries, and support.</p>
				<span class="text-[#9353ff] font-bold text-base sm:text-lg tracking-tight group-hover:underline">info@awscloudclubau.com</span>
			</a>

			<!-- Phone Card -->
			<a href="tel:+923492449953" class="card-clean rounded-2xl p-8 flex flex-col items-center text-center group">
				<div class="w-16 h-16 rounded-2xl bg-[#9353ff]/10 border border-[#9353ff]/20 text-[#9353ff] flex items-center justify-center mb-5 group-hover:bg-[#9353ff] group-hover:text-white transition-all duration-200">
					<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
					</svg>
				</div>
				<h2 class="text-xl font-bold text-slate-950 mb-2 uppercase tracking-tight">Call Us</h2>
				<p class="text-slate-600 mb-5 text-xs sm:text-sm">For urgent inquiries, event details, or direct help.</p>
				<span class="text-[#9353ff] font-bold text-base sm:text-lg tracking-tight group-hover:underline">+92 349 2449953</span>
			</a>

		</div>

	</div>
</div>

<?php
get_footer();
