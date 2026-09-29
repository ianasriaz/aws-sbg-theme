	<!-- Footer -->
	<footer class="bg-[#0a0d14] border-t border-[#1d2636] mt-16 sm:mt-24 relative">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
			
			<!-- Main Footer Grid -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">
				
				<!-- Column 1: Brand & About -->
				<div class="space-y-4">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block hover:opacity-90 transition-opacity">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/SBG-Logo-White.png' ) ); ?>" alt="AWS SBG Air University" class="h-9 sm:h-10 w-auto max-w-full object-contain">
					</a>
					<p class="text-gray-400 text-xs sm:text-sm leading-relaxed">
						Official AWS Student Builder Group chapter at Air University Islamabad. Empowering students to build, innovate, and lead in Cloud and AI.
					</p>
					
					<!-- Social Links (Preserved as requested) -->
					<div class="flex space-x-3 pt-2">
						<a href="https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-brand/10 text-brand hover:bg-brand hover:text-white flex items-center justify-center transition-all duration-300 hover:scale-110" title="LinkedIn">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/wired-outline-2677-logo-square-linkedin.png' ) ); ?>" alt="LinkedIn" class="w-5 h-5 object-contain">
						</a>
						<a href="https://www.instagram.com/awssbgairuniversity/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-pink-500/10 text-pink-400 hover:bg-pink-600 hover:text-white flex items-center justify-center transition-all duration-300 hover:scale-110" title="Instagram">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/wired-outline-2542-logo-instagram.png' ) ); ?>" alt="Instagram" class="w-5 h-5 object-contain">
						</a>
						<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all duration-300 hover:scale-110" title="Meetup">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/meetup-icon.png' ) ); ?>" alt="Meetup" class="w-5 h-5 object-contain">
						</a>
						<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-all duration-300 hover:scale-110" title="WhatsApp Community">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/wired-outline-2672-logo-square-whatsapp.png' ) ); ?>" alt="WhatsApp" class="w-5 h-5 object-contain">
						</a>
					</div>
				</div>

				<!-- Column 2: Quick Links -->
				<div class="space-y-4">
					<h4 class="text-xs font-mono font-bold tracking-wider uppercase text-gray-200">
						<?php esc_html_e( 'Quick Links', 'aws-sbg-theme' ); ?>
					</h4>
					<ul class="space-y-2.5 text-xs sm:text-sm font-mono">
						<li>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff] group-hover:translate-x-0.5 transition-all">→</span>
								<span><?php esc_html_e( 'Home', 'aws-sbg-theme' ); ?></span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff] group-hover:translate-x-0.5 transition-all">→</span>
								<span><?php esc_html_e( 'Community & Rewards', 'aws-sbg-theme' ); ?></span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff] group-hover:translate-x-0.5 transition-all">→</span>
								<span><?php esc_html_e( 'Events & Activities', 'aws-sbg-theme' ); ?></span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff] group-hover:translate-x-0.5 transition-all">→</span>
								<span><?php esc_html_e( 'Contact Us', 'aws-sbg-theme' ); ?></span>
							</a>
						</li>
					</ul>
				</div>

				<!-- Column 3: Community Info -->
				<div class="space-y-4">
					<h4 class="text-xs font-mono font-bold tracking-wider uppercase text-gray-200">
						<?php esc_html_e( 'Campus Chapter', 'aws-sbg-theme' ); ?>
					</h4>
					<div class="space-y-3 text-xs sm:text-sm text-gray-400 leading-relaxed">
						<p>
							<span class="text-gray-200 font-semibold block"><?php esc_html_e( 'Institution', 'aws-sbg-theme' ); ?></span>
							<?php esc_html_e( 'Air University, Sector E-9, Islamabad', 'aws-sbg-theme' ); ?>
						</p>
						<p>
							<span class="text-gray-200 font-semibold block"><?php esc_html_e( 'Global Network', 'aws-sbg-theme' ); ?></span>
							<?php esc_html_e( 'Official AWS Student Builder Group', 'aws-sbg-theme' ); ?>
						</p>
					</div>
				</div>

				<!-- Column 4: Contact Info -->
				<div class="space-y-4">
					<h4 class="text-xs font-mono font-bold tracking-wider uppercase text-gray-200">
						<?php esc_html_e( 'Get in Touch', 'aws-sbg-theme' ); ?>
					</h4>
					<div class="space-y-2.5 text-xs sm:text-sm font-mono">
						<div>
							<a href="mailto:info@awscloudclubau.com" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff]">✉</span>
								<span>info@awscloudclubau.com</span>
							</a>
						</div>
						<div>
							<a href="tel:+923492449953" class="text-gray-400 hover:text-[#9353ff] transition-colors flex items-center space-x-2 group">
								<span class="text-gray-600 group-hover:text-[#9353ff]">☎</span>
								<span>+92 349 2449953</span>
							</a>
						</div>
					</div>
				</div>

			</div>

			<!-- Bottom Bar -->
			<div class="border-t border-[#1d2636] pt-8 mt-10">
				<div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono text-gray-400">
					<div>
						&copy; <?php echo gmdate('Y'); ?> AWS Student Builder Group • Air University Islamabad
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
