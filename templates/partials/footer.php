	<!-- Footer -->
	<footer class="bg-[#0a0d14] border-t border-[#1d2636] mt-16 sm:mt-24 relative">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
			
			<!-- Clean Minimal Footer -->
			<div class="flex flex-col items-center text-center space-y-8">
				
				<!-- Logo -->
				<div class="mb-4">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block hover:opacity-90 transition-opacity">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/SBG-Logo-White.png' ) ); ?>" alt="AWS SBG Air University" class="h-12 sm:h-14 w-auto max-w-full object-contain">
					</a>
				</div>

				<!-- Description -->
				<p class="text-gray-400 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
					Official AWS Student Community at Air University Islamabad. Empowering students to build, innovate, and lead in Cloud and AI.
				</p>

				<!-- Contact Info -->
				<div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 text-sm font-mono text-gray-400">
					<a href="mailto:info@awscloudclubau.com" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
						<span class="text-gray-600 group-hover:text-[#9353ff]">✉</span>
						<span>info@awscloudclubau.com</span>
					</a>
					
					<span class="hidden sm:inline text-gray-600">•</span>
					
					<a href="tel:+923492449953" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
						<span class="text-gray-600 group-hover:text-[#9353ff]">☎</span>
						<span>+92 349 2449953</span>
					</a>
				</div>

			</div>

			<!-- Bottom Bar -->
			<div class="border-t border-[#1d2636] pt-8 mt-10">
				<div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono text-gray-400">
					<div>
						© 2026 AWS Student Builder Group • Air University Islamabad
					</div>
					<div class="flex items-center gap-2 text-gray-400">
						<span>Powered by</span>
						<span class="text-[#ff9900] font-bold">AWS</span>
						<span>• Deployed on EC2</span>
					</div>
				</div>
			</div>

		</div>

		<!-- Back to Top Button -->
		<div class="fixed bottom-6 right-6 z-40" x-data="{ showScroll: false }" @scroll.window="showScroll = window.pageYOffset > 250" style="display: none;">
			<button 
				@click="window.scrollTo({top: 0, behavior: 'smooth'})" 
				x-show="showScroll"
				x-transition
				class="w-10 h-10 bg-[#0f141f] hover:bg-[#9353ff] text-gray-300 hover:text-white border border-[#1d2636] hover:border-[#9353ff] rounded-xl shadow-lg flex items-center justify-center transition-all hover:scale-105"
				title="<?php esc_attr_e( 'Back to top', 'aws-sbg-theme' ); ?>"
			>
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
			</button>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
