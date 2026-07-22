<?php
get_header();
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#1a0a2e] to-[#0a0a0a]">
	<!-- Animated Background Gradient -->
	<div class="absolute inset-0 opacity-30 pointer-events-none">
		<div class="absolute top-0 left-1/4 w-96 h-96 bg-brand rounded-full mix-blend-screen blur-[100px] animate-pulse"></div>
		<div class="absolute bottom-0 right-1/4 w-96 h-96 bg-supporting-green rounded-full mix-blend-screen blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
	</div>

	<!-- Content -->
	<div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-20">
		<div class="mb-8 inline-block animate-fade-in-up">
			<span class="px-5 py-2.5 rounded-full bg-brand/10 border border-brand/30 text-brand text-sm font-semibold tracking-wide shadow-[0_0_15px_rgba(173,92,255,0.2)]">
				<?php esc_html_e( 'Welcome to AWS SBG', 'aws-sbg-theme' ); ?>
			</span>
		</div>

		<h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand via-white to-supporting-green mb-8 leading-tight animate-fade-in-up" style="animation-delay: 0.1s;">
			<?php esc_html_e( 'Empower Your Cloud Journey', 'aws-sbg-theme' ); ?>
		</h1>

		<p class="text-xl md:text-2xl text-gray-300 mb-12 leading-relaxed max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 0.2s;">
			<?php esc_html_e( 'Master cloud architecture, DevOps practices, and serverless technologies with the AWS Student Builder Group at Air University Islamabad.', 'aws-sbg-theme' ); ?>
		</p>

		<!-- CTA Buttons -->
		<div class="flex flex-col sm:flex-row justify-center gap-6 animate-fade-in-up" style="animation-delay: 0.3s;">
			<a 
				href="<?php echo esc_url( home_url( '/aircert/' ) ); ?>" 
				class="px-8 py-4 bg-gradient-to-r from-brand to-supporting-green text-white font-bold rounded-lg shadow-[0_0_20px_rgba(173,92,255,0.4)] hover:shadow-[0_0_30px_rgba(0,239,136,0.5)] transition-all duration-300 transform hover:-translate-y-1 hover:scale-105"
			>
				<?php esc_html_e( 'Explore AirCert', 'aws-sbg-theme' ); ?>
			</a>
			<a 
				href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" 
				class="px-8 py-4 border-2 border-brand/50 text-white font-bold rounded-lg hover:bg-brand/10 hover:border-brand transition-all duration-300 transform hover:-translate-y-1"
			>
				<?php esc_html_e( 'Get in Touch', 'aws-sbg-theme' ); ?>
			</a>
		</div>
	</div>

	<!-- Scroll Indicator -->
	<div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-10">
		<div class="animate-bounce p-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm">
			<svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
			</svg>
		</div>
	</div>
</section>

<!-- About Section -->
<section class="py-24 bg-[#0a0a0a] border-t border-white/5 relative overflow-hidden">
    <!-- Subtle Background Element -->
    <div class="absolute top-1/2 left-0 w-72 h-72 bg-brand/5 rounded-full blur-[80px] -translate-y-1/2 -translate-x-1/2"></div>
    
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
			<!-- Text Content -->
			<div>
				<h2 class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-brand to-supporting-green mb-8">
					<?php esc_html_e( 'About AWS SBG', 'aws-sbg-theme' ); ?>
				</h2>
				<p class="text-lg text-gray-300 mb-6 leading-relaxed">
					<?php esc_html_e( 'The AWS Student Builder Group is a vibrant community of tech enthusiasts dedicated to advancing cloud computing knowledge and skills.', 'aws-sbg-theme' ); ?>
				</p>
				<p class="text-lg text-gray-300 mb-10 leading-relaxed">
					<?php esc_html_e( 'We focus on three core pillars: Cloud Architecture, DevOps Engineering, and Serverless Technologies. Join us to build, learn, and grow together.', 'aws-sbg-theme' ); ?>
				</p>

				<!-- Core Focus Areas -->
				<div class="space-y-6">
					<?php
					$focus_areas = array(
						array(
							'icon' => '☁️',
							'title' => esc_html__( 'Cloud Architecture', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Learn to design scalable and resilient cloud systems.', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '⚙️',
							'title' => esc_html__( 'DevOps Engineering', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Master CI/CD pipelines and infrastructure automation.', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '⚡',
							'title' => esc_html__( 'Serverless Computing', 'aws-sbg-theme' ),
							'description' => esc_html__( 'Build modern applications without managing servers.', 'aws-sbg-theme' ),
						),
					);

					foreach ( $focus_areas as $area ) {
						echo '<div class="flex items-start gap-5 p-4 rounded-xl hover:bg-white/5 transition-colors border border-transparent hover:border-white/10">';
						echo '<div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-br from-brand/20 to-supporting-green/20 border border-brand/20 text-2xl flex-shrink-0 shadow-[0_0_10px_rgba(173,92,255,0.1)]">' . esc_html( $area['icon'] ) . '</div>';
						echo '<div>';
						echo '<h3 class="text-xl font-semibold text-white mb-2">' . esc_html( $area['title'] ) . '</h3>';
						echo '<p class="text-gray-400 leading-relaxed">' . esc_html( $area['description'] ) . '</p>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			</div>

			<!-- Visual Element -->
			<div class="relative lg:h-full flex items-center justify-center">
                <!-- Glowing Backdrop -->
                <div class="absolute inset-0 bg-gradient-to-tr from-brand/20 via-transparent to-supporting-green/20 blur-[50px] rounded-full"></div>
                
				<div class="relative w-full aspect-square max-w-md rounded-2xl bg-[#131313]/80 backdrop-blur-xl border border-white/10 shadow-[0_8px_30px_rgb(0,0,0,0.5)] flex flex-col items-center justify-center p-10 transform hover:scale-105 transition-transform duration-500 hover:border-brand/40 overflow-hidden group">
                    
                    <!-- Inner Grid Pattern -->
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>

					<div class="relative z-10 text-center">
						<div class="text-7xl mb-6 transform group-hover:-translate-y-2 group-hover:scale-110 transition-all duration-300 drop-shadow-[0_0_15px_rgba(173,92,255,0.5)]">🚀</div>
						<h3 class="text-2xl font-bold text-white mb-3">
							<?php esc_html_e( 'Cloud Leaders', 'aws-sbg-theme' ); ?>
						</h3>
						<p class="text-brand font-medium text-lg">
							<?php esc_html_e( 'Building the Future', 'aws-sbg-theme' ); ?>
						</p>
					</div>
                    
                    <!-- Decorative border line -->
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-brand to-supporting-green transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Events & Milestones Grid -->
<section class="py-24 bg-gradient-to-b from-[#0a0a0a] to-[#1a0a2e] border-t border-white/5 relative">
    <div class="absolute top-0 right-0 w-1/3 h-px bg-gradient-to-r from-transparent via-supporting-green/50 to-transparent"></div>
    
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
		<!-- Section Header -->
		<div class="text-center mb-20">
			<h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
				<?php esc_html_e( 'Community Impact', 'aws-sbg-theme' ); ?>
			</h2>
			<p class="text-xl text-gray-400 max-w-2xl mx-auto">
				<?php esc_html_e( 'Showcasing the achievements and milestones of our thriving AWS Student Builder Group community.', 'aws-sbg-theme' ); ?>
			</p>
		</div>

		<!-- Impact Grid -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
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

			foreach ( $milestones as $index => $milestone ) {
                $delay = $index * 100;
				echo '<div class="group relative" style="animation-delay: ' . $delay . 'ms;">';
				echo '<div class="absolute inset-0 bg-gradient-to-br from-brand/20 to-supporting-green/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>';
				echo '<div class="relative h-full bg-[#151515] backdrop-blur-md border border-white/5 rounded-2xl p-8 hover:border-brand/40 hover:-translate-y-2 transition-all duration-300 shadow-lg">';
                echo '<div class="w-12 h-1 mb-6 bg-gradient-to-r from-brand to-supporting-green rounded-full"></div>';
				echo '<h3 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-white to-gray-500 mb-4 group-hover:from-brand group-hover:to-brand-light transition-all duration-300">' . esc_html( $milestone['number'] ) . '</h3>';
				echo '<h4 class="text-xl font-bold text-white mb-3">' . esc_html( $milestone['title'] ) . '</h4>';
				echo '<p class="text-gray-400 leading-relaxed">' . esc_html( $milestone['description'] ) . '</p>';
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
	</div>
</section>

<?php
get_footer();
