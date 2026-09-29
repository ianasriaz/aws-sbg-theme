	<!-- Footer -->
	<footer class="bg-[#0a0d14] border-t border-[#1d2636] mt-16 sm:mt-24 relative">
		<!-- Background Glow Effects -->
		<div class="absolute inset-0 overflow-hidden pointer-events-none">
			<div class="absolute w-[500px] h-[500px] -top-40 -right-40 bg-[#9353ff]/5 rounded-full blur-[100px]"></div>
			<div class="absolute w-[400px] h-[400px] -bottom-32 -left-40 bg-[#ff9900]/5 rounded-full blur-[80px]"></div>
		</div>
		
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 relative z-10">
			
			<!-- Enhanced Footer Content -->
			<div class="flex flex-col items-center text-center space-y-10">
				
				<!-- Logo with Glow Effect -->
				<div class="mb-6 relative">
					<div class="absolute -inset-4 bg-gradient-to-r from-[#9353ff]/20 to-[#ff9900]/20 rounded-2xl blur-xl opacity-50"></div>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block hover:opacity-90 transition-opacity duration-300 relative">
						<img src="https://awsairuniversity.com/wp-content/uploads/2026/09/SBG-Logo-White-Transparent.png" alt="AWS SBG Air University" class="h-14 sm:h-16 md:h-18 w-auto max-w-full object-contain drop-shadow-lg">
					</a>
				</div>

				<!-- Description with Enhanced Typography -->
				<div class="max-w-2xl mx-auto">
					<p class="text-gray-300 text-base sm:text-lg leading-relaxed font-medium mb-4">
						Official AWS Student Community at Air University Islamabad.
					</p>
					<p class="text-gray-400 text-sm sm:text-base leading-relaxed">
						Empowering students to build, innovate, and lead in Cloud and AI.
					</p>
				</div>

				<!-- Contact Info with Enhanced Styling -->
				<div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-8 text-base font-medium text-gray-300">
					<a href="mailto:info@awscloudclubau.com" class="group inline-flex items-center gap-3 px-5 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all duration-300 hover:border-[#9353ff]/30 hover:shadow-lg hover:shadow-[#9353ff]/10">
						<span class="text-[#9353ff] text-xl group-hover:scale-110 transition-transform">✉</span>
						<span class="group-hover:text-white transition-colors">info@awscloudclubau.com</span>
					</a>
					
					<div class="hidden sm:block w-px h-8 bg-white/10"></div>
					
					<a href="tel:+923492449953" class="group inline-flex items-center gap-3 px-5 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all duration-300 hover:border-[#ff9900]/30 hover:shadow-lg hover:shadow-[#ff9900]/10">
						<span class="text-[#ff9900] text-xl group-hover:scale-110 transition-transform">☎</span>
						<span class="group-hover:text-white transition-colors">+92 349 2449953</span>
					</a>
				</div>

				<!-- Social Proof / Trust Badge -->
				<div class="pt-6 border-t border-white/10 w-full max-w-md">
					<div class="flex items-center justify-center gap-3 text-xs text-gray-400 font-mono">
						<span class="flex items-center gap-1.5">
							<span class="w-1.5 h-1.5 rounded-full bg-[#00ef88]"></span>
							<span>Official AWS Student Builder Group</span>
						</span>
						<span class="text-gray-600">•</span>
						<span class="flex items-center gap-1.5">
							<span class="w-1.5 h-1.5 rounded-full bg-[#9353ff]"></span>
							<span>Air University Islamabad</span>
						</span>
					</div>
				</div>

			</div>

			<!-- Enhanced Bottom Bar -->
			<div class="border-t border-[#1d2636] pt-10 mt-12">
				<div class="flex flex-col sm:flex-row items-center justify-between gap-6 text-sm font-mono">
					<div class="text-gray-400 flex items-center gap-2">
						<span>© 2026 AWS Student Builder Group</span>
						<span class="text-gray-600">•</span>
						<span>Air University Islamabad</span>
					</div>
					<div class="flex items-center gap-3 text-gray-400">
						<span class="flex items-center gap-1.5">
							<span>Powered by</span>
							<span class="text-[#ff9900] font-bold flex items-center gap-1">
								<span>AWS</span>
								<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
									<path d="M12 2L4.5 20.5 19.5 13.5z"/>
								</svg>
							</span>
						</span>
						<span class="text-gray-600">•</span>
						<span class="flex items-center gap-1.5">
							<span>Deployed on</span>
							<span class="text-gray-300 font-semibold">EC2</span>
						</span>
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
