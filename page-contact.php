<?php
get_header();
?>

<!-- Page Header -->
<section class="relative bg-gradient-to-br from-[#0a0a0a] to-[#120a1f] py-20 border-b border-white/5">
	<div class="absolute inset-0 overflow-hidden pointer-events-none">
		<div class="absolute -top-[30%] -right-[10%] w-[70%] h-[130%] rounded-full bg-gradient-to-b from-[#ad5cff]/10 to-transparent blur-3xl mix-blend-screen"></div>
	</div>
	
	<div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
		<h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight uppercase">
			Contact <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ad5cff] to-[#7000ff]">Us</span>
		</h1>
		<p class="text-lg text-slate-400 max-w-xl mx-auto leading-relaxed">
			<?php esc_html_e( 'Reach out to us directly via email or phone. We\'d love to hear from you.', 'aws-sbg-theme' ); ?>
		</p>
	</div>
</section>

<!-- Contact Cards Section -->
<section class="py-16 md:py-24 bg-[#050505] min-h-[50vh]">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			
			<!-- Email Card -->
			<a href="mailto:info@awscloudclubau.com" class="group relative bg-[#0a0a0a] border border-white/5 rounded-2xl p-8 hover:border-[#ad5cff]/50 transition-all duration-300 hover:shadow-[0_0_30px_rgba(173,92,255,0.15)] hover:-translate-y-1 overflow-hidden flex flex-col items-center text-center">
				<div class="absolute inset-0 bg-gradient-to-br from-[#ad5cff]/0 to-[#ad5cff]/0 group-hover:from-[#ad5cff]/5 group-hover:to-transparent transition-colors duration-500"></div>
				
				<div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:border-[#ad5cff]/30 transition-all duration-300">
					<svg class="w-7 h-7 text-slate-300 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
				</div>
				<h3 class="text-xl font-bold text-white mb-2 uppercase tracking-wide">Email Us</h3>
				<p class="text-slate-400 mb-6 text-sm">For partnerships, general inquiries, and support.</p>
				<span class="text-[#ad5cff] font-semibold text-lg tracking-wide group-hover:text-white transition-colors">info@awscloudclubau.com</span>
			</a>

			<!-- Phone Card -->
			<a href="tel:+923492449953" class="group relative bg-[#0a0a0a] border border-white/5 rounded-2xl p-8 hover:border-[#00ef88]/50 transition-all duration-300 hover:shadow-[0_0_30px_rgba(0,239,136,0.1)] hover:-translate-y-1 overflow-hidden flex flex-col items-center text-center">
				<div class="absolute inset-0 bg-gradient-to-br from-[#00ef88]/0 to-[#00ef88]/0 group-hover:from-[#00ef88]/5 group-hover:to-transparent transition-colors duration-500"></div>
				
				<div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:border-[#00ef88]/30 transition-all duration-300">
					<svg class="w-7 h-7 text-slate-300 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
				</div>
				<h3 class="text-xl font-bold text-white mb-2 uppercase tracking-wide">Call Us</h3>
				<p class="text-slate-400 mb-6 text-sm">For urgent inquiries, event details, or direct support.</p>
				<span class="text-[#00ef88] font-semibold text-lg tracking-wide group-hover:text-white transition-colors">+92 349 2449953</span>
			</a>

		</div>
	</div>
</section>

<?php
get_footer();
