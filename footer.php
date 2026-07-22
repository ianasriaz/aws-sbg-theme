	<!-- Footer -->
	<footer class="bg-gradient-to-t from-aws-blue to-cloud-dark border-t border-aws-gold/20 mt-16">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
			<!-- Footer Grid -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
				<!-- About Section -->
				<div>
					<h3 class="text-lg font-bold text-aws-gold mb-4">AWS Student Builder Group</h3>
					<p class="text-cloud-light/80 text-sm leading-relaxed">
						Empowering the next generation of cloud architects and DevOps engineers at Air University Islamabad.
					</p>
				</div>

				<!-- Quick Links -->
				<div>
					<h4 class="text-base font-semibold text-aws-gold mb-4"><?php esc_html_e( 'Quick Links', 'aws-sbg-theme' ); ?></h4>
					<ul class="space-y-2 text-sm">
						<li>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-cloud-light/80 hover:text-aws-gold transition-colors">
								<?php esc_html_e( 'Home', 'aws-sbg-theme' ); ?>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/aircert/' ) ); ?>" class="text-cloud-light/80 hover:text-aws-gold transition-colors">
								<?php esc_html_e( 'AirCert BootCamp', 'aws-sbg-theme' ); ?>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="text-cloud-light/80 hover:text-aws-gold transition-colors">
								<?php esc_html_e( 'Contact Us', 'aws-sbg-theme' ); ?>
							</a>
						</li>
					</ul>
				</div>

				<!-- Community Info -->
				<div>
					<h4 class="text-base font-semibold text-aws-gold mb-4"><?php esc_html_e( 'Community', 'aws-sbg-theme' ); ?></h4>
					<p class="text-cloud-light/80 text-sm mb-3">
						<?php esc_html_e( 'Air University Islamabad', 'aws-sbg-theme' ); ?>
					</p>
					<p class="text-cloud-light/80 text-sm">
						<?php esc_html_e( 'Building leaders in cloud computing', 'aws-sbg-theme' ); ?>
					</p>
				</div>
			</div>

			<!-- Divider -->
			<div class="border-t border-aws-gold/20 pt-8">
				<!-- Copyright & Footer Meta -->
				<div class="flex flex-col md:flex-row justify-between items-center text-sm text-cloud-light/70">
					<div>
						<?php
						printf(
							/* translators: %s: Current year */
							esc_html__( '&copy; %s AWS Student Builder Group. All rights reserved.', 'aws-sbg-theme' ),
							gmdate( 'Y' )
						);
						?>
					</div>
					<div class="mt-4 md:mt-0">
						<p><?php esc_html_e( 'Proudly powered by WordPress', 'aws-sbg-theme' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
