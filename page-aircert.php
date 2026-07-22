<?php
get_header();
?>

<!-- Success/Error Notification Banner -->
<?php
$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

if ( 'success' === $status ) {
	echo '<div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-40 max-w-md w-full mx-4">';
	echo '<div class="bg-success/20 border border-success text-green-300 px-6 py-4 rounded-lg shadow-lg" role="status">';
	echo '<p class="font-semibold text-lg">' . esc_html__( '✓ Registration Successful!', 'aws-sbg-theme' ) . '</p>';
	echo '<p class="text-sm mt-1">' . esc_html__( 'Thank you for registering for AirCert. We\'ll be in touch soon!', 'aws-sbg-theme' ) . '</p>';
	echo '</div>';
	echo '</div>';
}

if ( 'error' === $status ) {
	echo '<div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-40 max-w-md w-full mx-4">';
	echo '<div class="bg-danger/20 border border-danger text-red-300 px-6 py-4 rounded-lg shadow-lg" role="alert">';
	echo '<p class="font-semibold text-lg">' . esc_html__( '✗ Registration Failed', 'aws-sbg-theme' ) . '</p>';
	echo '<p class="text-sm mt-1">' . esc_html__( 'Please fill in all fields correctly and try again.', 'aws-sbg-theme' ) . '</p>';
	echo '</div>';
	echo '</div>';
}
?>

<!-- Event Hero Section -->
<section class="relative min-h-96 bg-gradient-to-br from-aws-blue via-cloud-dark to-aws-blue flex items-center pt-32 pb-20 overflow-hidden">
	<!-- Background Elements -->
	<div class="absolute inset-0 opacity-20">
		<div class="absolute top-10 right-10 w-72 h-72 bg-aws-gold rounded-full mix-blend-screen blur-3xl"></div>
		<div class="absolute bottom-0 left-10 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen blur-3xl" style="animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
	</div>

	<!-- Content -->
	<div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<div class="mb-6 inline-block">
			<span class="px-4 py-2 rounded-full bg-aws-gold/10 border border-aws-gold/50 text-aws-gold text-sm font-semibold">
				<?php esc_html_e( 'AWS Certification Program', 'aws-sbg-theme' ); ?>
			</span>
		</div>

		<h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
			<?php esc_html_e( 'AirCert: AWS Certification BootCamp', 'aws-sbg-theme' ); ?>
		</h1>

		<p class="text-xl md:text-2xl text-cloud-light/90 mb-8 leading-relaxed max-w-3xl mx-auto">
			<?php esc_html_e( 'Level up your cloud architecture skills with our intensive bootcamp. Gain hands-on experience and prepare for AWS certifications with industry experts.', 'aws-sbg-theme' ); ?>
		</p>

		<!-- Register Button (opens modal) -->
		<button 
			@click="showModal = true" 
			class="px-8 py-4 bg-aws-gold text-aws-blue font-bold rounded-lg hover:bg-aws-gold/90 shadow-glow transition-all transform hover:scale-105"
		>
			<?php esc_html_e( 'Register Now', 'aws-sbg-theme' ); ?>
		</button>
	</div>
</section>

<!-- Event Details Section -->
<section class="py-20 bg-cloud-dark border-t border-aws-gold/20">
	<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
			<!-- Left: Event Information -->
			<div>
				<h2 class="text-3xl font-bold text-aws-gold mb-8">
					<?php esc_html_e( 'Event Details', 'aws-sbg-theme' ); ?>
				</h2>

				<div class="space-y-8">
					<?php
					$event_details = array(
						array(
							'icon' => '📅',
							'title' => esc_html__( 'Duration', 'aws-sbg-theme' ),
							'content' => esc_html__( '5-Day Intensive Program', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '📍',
							'title' => esc_html__( 'Location', 'aws-sbg-theme' ),
							'content' => esc_html__( 'Air University Islamabad, Main Campus', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '👥',
							'title' => esc_html__( 'Capacity', 'aws-sbg-theme' ),
							'content' => esc_html__( 'Limited to 50 participants for hands-on learning', 'aws-sbg-theme' ),
						),
						array(
							'icon' => '🏆',
							'title' => esc_html__( 'Certification', 'aws-sbg-theme' ),
							'content' => esc_html__( 'AWS Fundamentals Certificate of Completion', 'aws-sbg-theme' ),
						),
					);

					foreach ( $event_details as $detail ) {
						echo '<div class="flex gap-6">';
						echo '<span class="text-4xl flex-shrink-0">' . esc_html( $detail['icon'] ) . '</span>';
						echo '<div>';
						echo '<h3 class="text-xl font-semibold text-aws-gold mb-2">' . esc_html( $detail['title'] ) . '</h3>';
						echo '<p class="text-cloud-light/80">' . esc_html( $detail['content'] ) . '</p>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			</div>

			<!-- Right: Program Highlights -->
			<div>
				<h2 class="text-3xl font-bold text-aws-gold mb-8">
					<?php esc_html_e( 'What You\'ll Learn', 'aws-sbg-theme' ); ?>
				</h2>

				<div class="space-y-4">
					<?php
					$topics = array(
						esc_html__( 'AWS Cloud Architecture Fundamentals', 'aws-sbg-theme' ),
						esc_html__( 'Designing Scalable Cloud Solutions', 'aws-sbg-theme' ),
						esc_html__( 'Security & Compliance Best Practices', 'aws-sbg-theme' ),
						esc_html__( 'Cost Optimization Strategies', 'aws-sbg-theme' ),
						esc_html__( 'DevOps & Infrastructure as Code', 'aws-sbg-theme' ),
						esc_html__( 'Serverless Architecture & Lambda', 'aws-sbg-theme' ),
						esc_html__( 'Database Design & Migration', 'aws-sbg-theme' ),
						esc_html__( 'Hands-On Labs & Case Studies', 'aws-sbg-theme' ),
					);

					foreach ( $topics as $topic ) {
						echo '<div class="flex items-start gap-4 p-4 rounded-lg bg-aws-blue/50 border border-aws-gold/20 hover:border-aws-gold/50 transition-colors">';
						echo '<span class="text-aws-gold font-bold text-xl flex-shrink-0">✓</span>';
						echo '<p class="text-cloud-light">' . esc_html( $topic ) . '</p>';
						echo '</div>';
					}
					?>
				</div>

				<!-- Secondary CTA -->
				<button 
					@click="showModal = true"
					class="w-full mt-8 px-8 py-4 bg-gradient-to-r from-aws-gold to-yellow-500 text-aws-blue font-bold rounded-lg hover:shadow-glow transition-all transform hover:scale-105"
				>
					<?php esc_html_e( 'Secure Your Spot', 'aws-sbg-theme' ); ?>
				</button>
			</div>
		</div>
	</div>
</section>

<!-- Alpine.js Modal for Registration Form -->
<div 
	x-data="{
		showModal: false,
		isSubmitting: false,
		formData: {
			full_name: '',
			email: '',
			whatsapp: '',
			status: 'student',
			institute: ''
		},
		async submitForm() {
			this.isSubmitting = true;
			// Form will be submitted normally via HTTP POST
		}
	}"
	@click.outside="showModal = false"
>
	<!-- Modal Overlay -->
	<div 
		x-show="showModal" 
		x-transition:enter="transition ease-out duration-300"
		x-transition:enter-start="opacity-0"
		x-transition:enter-end="opacity-100"
		x-transition:leave="transition ease-in duration-300"
		x-transition:leave-start="opacity-100"
		x-transition:leave-end="opacity-0"
		class="fixed inset-0 bg-black/70 z-50"
		style="display: none;"
	></div>

	<!-- Modal Dialog -->
	<div 
		x-show="showModal" 
		x-transition:enter="transition ease-out duration-300"
		x-transition:enter-start="opacity-0 scale-95"
		x-transition:enter-end="opacity-100 scale-100"
		x-transition:leave="transition ease-in duration-300"
		x-transition:leave-start="opacity-100 scale-100"
		x-transition:leave-end="opacity-0 scale-95"
		class="fixed inset-0 z-50 flex items-center justify-center p-4"
		style="display: none;"
	>
		<div class="bg-aws-blue border-2 border-aws-gold/50 rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
			<!-- Modal Header -->
			<div class="sticky top-0 bg-gradient-to-r from-aws-blue to-cloud-dark border-b border-aws-gold/30 px-8 py-6 flex justify-between items-center">
				<h2 class="text-2xl font-bold text-aws-gold">
					<?php esc_html_e( 'Register for AirCert', 'aws-sbg-theme' ); ?>
				</h2>
				<button 
					@click="showModal = false"
					class="text-cloud-light hover:text-aws-gold transition-colors"
					aria-label="<?php esc_attr_e( 'Close modal', 'aws-sbg-theme' ); ?>"
				>
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
					</svg>
				</button>
			</div>

			<!-- Modal Body -->
			<div class="p-8">
				<form 
					method="POST" 
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					@submit="isSubmitting = true"
					class="space-y-6"
				>
					<!-- Hidden Action Field -->
					<input type="hidden" name="action" value="submit_aircert_rsvp">
					<?php wp_nonce_field( 'aircert_rsvp_action', 'aircert_rsvp_nonce' ); ?>

					<!-- Full Name Field -->
					<div>
						<label for="full_name" class="block text-sm font-semibold text-aws-gold mb-2">
							<?php esc_html_e( 'Full Name', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
						</label>
						<input 
							type="text" 
							id="full_name" 
							name="full_name" 
							x-model="formData.full_name"
							required
							class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
							placeholder="<?php esc_attr_e( 'Your Full Name', 'aws-sbg-theme' ); ?>"
						>
					</div>

					<!-- Email Field -->
					<div>
						<label for="email" class="block text-sm font-semibold text-aws-gold mb-2">
							<?php esc_html_e( 'Email Address', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
						</label>
						<input 
							type="email" 
							id="email" 
							name="email" 
							x-model="formData.email"
							required
							class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
							placeholder="<?php esc_attr_e( 'you@example.com', 'aws-sbg-theme' ); ?>"
						>
					</div>

					<!-- WhatsApp Number Field -->
					<div>
						<label for="whatsapp" class="block text-sm font-semibold text-aws-gold mb-2">
							<?php esc_html_e( 'WhatsApp Number', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
						</label>
						<input 
							type="tel" 
							id="whatsapp" 
							name="whatsapp" 
							x-model="formData.whatsapp"
							required
							class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
							placeholder="<?php esc_attr_e( '+92 300 1234567', 'aws-sbg-theme' ); ?>"
						>
					</div>

					<!-- Attendee Status Field -->
					<div>
						<label class="block text-sm font-semibold text-aws-gold mb-3">
							<?php esc_html_e( 'Attendee Status', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
						</label>
						<div class="space-y-3">
							<label class="flex items-center gap-3 p-3 rounded-lg border border-aws-gold/30 cursor-pointer hover:bg-aws-blue/50 transition-colors">
								<input 
									type="radio" 
									name="status" 
									value="student" 
									x-model="formData.status"
									required
									class="w-4 h-4 accent-aws-gold"
								>
								<span class="text-cloud-light"><?php esc_html_e( 'Student', 'aws-sbg-theme' ); ?></span>
							</label>
							<label class="flex items-center gap-3 p-3 rounded-lg border border-aws-gold/30 cursor-pointer hover:bg-aws-blue/50 transition-colors">
								<input 
									type="radio" 
									name="status" 
									value="professional" 
									x-model="formData.status"
									required
									class="w-4 h-4 accent-aws-gold"
								>
								<span class="text-cloud-light"><?php esc_html_e( 'Professional', 'aws-sbg-theme' ); ?></span>
							</label>
						</div>
					</div>

					<!-- Institute/Organization Field -->
					<div>
						<label for="institute" class="block text-sm font-semibold text-aws-gold mb-2">
							<?php esc_html_e( 'Institute/Organization Name', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
						</label>
						<input 
							type="text" 
							id="institute" 
							name="institute" 
							x-model="formData.institute"
							required
							class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
							placeholder="<?php esc_attr_e( 'Air University / Your Company', 'aws-sbg-theme' ); ?>"
						>
					</div>

					<!-- Submit Button -->
					<button 
						type="submit" 
						:disabled="isSubmitting"
						:class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
						class="w-full px-6 py-3 bg-gradient-to-r from-aws-gold to-yellow-500 text-aws-blue font-bold rounded-lg hover:shadow-glow transition-all transform hover:scale-105 disabled:hover:scale-100"
					>
						<span x-show="!isSubmitting"><?php esc_html_e( 'Complete Registration', 'aws-sbg-theme' ); ?></span>
						<span x-show="isSubmitting" class="flex items-center justify-center gap-2">
							<span class="inline-block w-4 h-4 border-2 border-aws-blue border-t-transparent rounded-full animate-spin"></span>
							<?php esc_html_e( 'Registering...', 'aws-sbg-theme' ); ?>
						</span>
					</button>

					<!-- Terms Note -->
					<p class="text-xs text-cloud-light/60 text-center">
						<?php esc_html_e( 'By registering, you agree to receive updates about AirCert and AWS SBG events.', 'aws-sbg-theme' ); ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
