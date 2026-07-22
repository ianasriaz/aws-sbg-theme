<?php
get_header();
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-aws-blue via-cloud-dark to-aws-blue">
	<!-- Animated Background Gradient -->
	<div class="absolute inset-0 opacity-30">
		<div class="absolute top-0 left-1/4 w-96 h-96 bg-aws-gold rounded-full mix-blend-screen blur-3xl animate-pulse"></div>
		<div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
	</div>

	<!-- Content -->
	<div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<div class="mb-6 inline-block">
			<span class="px-4 py-2 rounded-full bg-aws-gold/10 border border-aws-gold/50 text-aws-gold text-sm font-semibold">
				<?php esc_html_e( 'Welcome to AWS SBG', 'aws-sbg-theme' ); ?>
			</span>
		</div>

		<h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
			<?php esc_html_e( 'Empower Your Cloud Journey', 'aws-sbg-theme' ); ?>
		</h1>

		<p class="text-xl md:text-2xl text-cloud-light/90 mb-8 leading-relaxed max-w-2xl mx-auto">
			<?php esc_html_e( 'Master cloud architecture, DevOps practices, and serverless technologies with the AWS Student Builder Group at Air University Islamabad.', 'aws-sbg-theme' ); ?>
		</p>

		<!-- CTA Buttons -->
		<div class="flex flex-col sm:flex-row justify-center gap-4">
			<a 
				href="<?php echo esc_url( home_url( '/aircert/' ) ); ?>" 
				class="px-8 py-4 bg-aws-gold text-aws-blue font-bold rounded-lg hover:bg-aws-gold/90 shadow-glow transition-all transform hover:scale-105"
			>
				<?php esc_html_e( 'Explore AirCert', 'aws-sbg-theme' ); ?>
			</a>
			<a 
				href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" 
				class="px-8 py-4 border-2 border-aws-gold text-aws-gold font-bold rounded-lg hover:bg-aws-gold/10 transition-all"
			>
				<?php esc_html_e( 'Get in Touch', 'aws-sbg-theme' ); ?>
			</a>
		</div>
	</div>

	<!-- Scroll Indicator -->
	<div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10">
		<div class="animate-bounce">
			<svg class="w-6 h-6 text-aws-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
			</svg>
		</div>
	</div>
</section>

<!-- About Section -->
<section class="py-20 bg-cloud-dark border-t border-aws-gold/20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
			<!-- Text Content -->
			<div>
				<h2 class="text-4xl md:text-5xl font-bold text-aws-gold mb-6">
					<?php esc_html_e( 'About AWS SBG', 'aws-sbg-theme' ); ?>
				</h2>
				<p class="text-lg text-cloud-light/80 mb-6 leading-relaxed">
					<?php esc_html_e( 'The AWS Student Builder Group is a vibrant community of tech enthusiasts dedicated to advancing cloud computing knowledge and skills.', 'aws-sbg-theme' ); ?>
				</p>
				<p class="text-lg text-cloud-light/80 mb-8 leading-relaxed">
					<?php esc_html_e( 'We focus on three core pillars: Cloud Architecture, DevOps Engineering, and Serverless Technologies. Join us to build, learn, and grow together.', 'aws-sbg-theme' ); ?>
				</p>

				<!-- Core Focus Areas -->
				<div class="space-y-4">
					<?php
					$focus_areas = array(
						array(
							'icon' => '☁️',
							'title' => esc_html__( 'Cloud Architecture', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Learn to design scalable and resilient cloud systems', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '⚙️',
							'title' => esc_html__( 'DevOps Engineering', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Master CI/CD pipelines and infrastructure automation', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '⚡',
							'title' => esc_html__( 'Serverless Computing', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Build modern applications without managing servers', 'aws-sbg-theme' ),
						),
					);

					foreach ( $focus_areas as $area ) {
						echo '<div class="flex items-start gap-4">';
						echo '<span class="text-3xl flex-shrink-0">' . esc_html( $area['icon'] ) . '</span>';
						echo '<div>';
						echo '<h3 class="text-lg font-semibold text-aws-gold mb-2">' . esc_html( $area['title'] ) . '</h3>';
						echo '<p class="text-cloud-light/70">' . esc_html( $area['description'] ) . '</p>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			</div>

			<!-- Visual Element -->
			<div class="relative">
				<div class="aspect-square rounded-lg bg-gradient-to-br from-aws-gold/10 to-blue-600/10 border border-aws-gold/30 shadow-glow-blue flex items-center justify-center p-8">
					<div class="text-center">
						<div class="text-6xl mb-4">🚀</div>
						<p class="text-cloud-light/80 text-lg">
							<?php esc_html_e( 'Building the Cloud Leaders of Tomorrow', 'aws-sbg-theme' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Events & Milestones Grid -->
<section class="py-20 bg-gradient-to-b from-cloud-dark to-aws-blue border-t border-aws-gold/20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div class="text-center mb-16">
			<h2 class="text-4xl md:text-5xl font-bold text-aws-gold mb-4">
				<?php esc_html_e( 'Community Impact', 'aws-sbg-theme' ); ?>
			</h2>
			<p class="text-xl text-cloud-light/80 max-w-2xl mx-auto">
				<?php esc_html_e( 'Showcasing the achievements and milestones of our thriving AWS Student Builder Group community', 'aws-sbg-theme' ); ?>
			</p>
		</div>

		<!-- Impact Grid -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			<?php
			$milestones = array(
				array(
					'number' => '500+',
					'title' => esc_html__( 'Community Members', 'aws-sbg-theme' ),
					'description' => esc_html__( 'Active learners growing together in cloud technologies', 'aws-sbg-theme' ),
				),
				array(
					'number' => '50+',
					'title' => esc_html__( 'Events Hosted', 'aws-sbg-theme' ),
					'description' => esc_html__( 'Workshops, webinars, and hands-on training sessions', 'aws-sbg-theme' ),
				),
				array(
					'number' => '200+',
					'title' => esc_html__( 'Certifications', 'aws-sbg-theme' ),
					'description' => esc_html__( 'Members earned AWS and cloud engineering certifications', 'aws-sbg-theme' ),
				),
				array(
					'number' => '15+',
					'title' => esc_html__( 'Expert Mentors', 'aws-sbg-theme' ),
					'description' => esc_html__( 'Industry professionals guiding the next generation', 'aws-sbg-theme' ),
				),
				array(
					'number' => '100%',
					'title' => esc_html__( 'Hands-On', 'aws-sbg-theme' ),
					'description' => esc_html__( 'All learning experiences include practical labs', 'aws-sbg-theme' ),
				),
				array(
					'number' => '24/7',
					'title' => esc_html__( 'Community Support', 'aws-sbg-theme' ),
					'description' => esc_html__( 'Access to peer support and learning resources anytime', 'aws-sbg-theme' ),
				),
			);

			foreach ( $milestones as $milestone ) {
				echo '<div class="group relative">';
				echo '<div class="absolute inset-0 bg-gradient-to-br from-aws-gold/20 to-blue-600/20 rounded-lg blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';
				echo '<div class="relative bg-aws-blue/50 backdrop-blur border border-aws-gold/30 rounded-lg p-8 hover:border-aws-gold/60 transition-all duration-300">';
				echo '<h3 class="text-4xl font-bold text-aws-gold mb-2">' . esc_html( $milestone['number'] ) . '</h3>';
				echo '<h4 class="text-xl font-semibold text-cloud-light mb-3">' . esc_html( $milestone['title'] ) . '</h4>';
				echo '<p class="text-cloud-light/70">' . esc_html( $milestone['description'] ) . '</p>';
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
	</div>
</section>

<?php
get_footer();
