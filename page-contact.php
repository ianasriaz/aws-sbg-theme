<?php
get_header();
?>

<!-- Page Header -->
<section class="relative bg-gradient-to-br from-aws-blue via-cloud-dark to-aws-blue py-16 border-b border-aws-gold/20">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<h1 class="text-5xl md:text-6xl font-bold text-aws-gold mb-4">
			<?php esc_html_e( 'Get in Touch', 'aws-sbg-theme' ); ?>
		</h1>
		<p class="text-xl text-cloud-light/90 max-w-2xl mx-auto">
			<?php esc_html_e( 'Have questions? Want to collaborate? We\'d love to hear from you. Reach out to the AWS Student Builder Group.', 'aws-sbg-theme' ); ?>
		</p>
	</div>
</section>

<!-- Success/Error Notification Banner -->
<?php
$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

if ( 'success' === $status ) {
	echo '<div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-40 max-w-md w-full mx-4">';
	echo '<div class="bg-success/20 border border-success text-green-300 px-6 py-4 rounded-lg shadow-lg" role="status">';
	echo '<p class="font-semibold text-lg">' . esc_html__( '✓ Message Sent Successfully!', 'aws-sbg-theme' ) . '</p>';
	echo '<p class="text-sm mt-1">' . esc_html__( 'We\'ll get back to you as soon as possible.', 'aws-sbg-theme' ) . '</p>';
	echo '</div>';
	echo '</div>';
}

if ( 'error' === $status ) {
	echo '<div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-40 max-w-md w-full mx-4">';
	echo '<div class="bg-danger/20 border border-danger text-red-300 px-6 py-4 rounded-lg shadow-lg" role="alert">';
	echo '<p class="font-semibold text-lg">' . esc_html__( '✗ Message Failed', 'aws-sbg-theme' ) . '</p>';
	echo '<p class="text-sm mt-1">' . esc_html__( 'Please fill in all fields correctly and try again.', 'aws-sbg-theme' ) . '</p>';
	echo '</div>';
	echo '</div>';
}
?>

<!-- Two-Column Contact Section -->
<section class="py-20 bg-cloud-dark">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
			<!-- Left Column: Information -->
			<div class="flex flex-col justify-between">
				<div>
					<h2 class="text-4xl font-bold text-aws-gold mb-8">
						<?php esc_html_e( 'Campus Chapter Info', 'aws-sbg-theme' ); ?>
					</h2>

					<div class="space-y-8">
						<!-- Location Info -->
						<div class="flex gap-6">
							<div class="text-4xl flex-shrink-0">📍</div>
							<div>
								<h3 class="text-xl font-semibold text-aws-gold mb-2">
									<?php esc_html_e( 'Meeting Location', 'aws-sbg-theme' ); ?>
								</h3>
								<p class="text-cloud-light/80">
									<?php esc_html_e( 'Air University Islamabad', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Main Campus', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Islamabad, Pakistan', 'aws-sbg-theme' ); ?>
								</p>
							</div>
						</div>

						<!-- Meeting Times -->
						<div class="flex gap-6">
							<div class="text-4xl flex-shrink-0">⏰</div>
							<div>
								<h3 class="text-xl font-semibold text-aws-gold mb-2">
									<?php esc_html_e( 'Regular Meetings', 'aws-sbg-theme' ); ?>
								</h3>
								<p class="text-cloud-light/80">
									<?php esc_html_e( 'Every Friday, 3:00 PM - 5:00 PM', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Join us for knowledge sharing and hands-on labs', 'aws-sbg-theme' ); ?>
								</p>
							</div>
						</div>

						<!-- Communication -->
						<div class="flex gap-6">
							<div class="text-4xl flex-shrink-0">💬</div>
							<div>
								<h3 class="text-xl font-semibold text-aws-gold mb-2">
									<?php esc_html_e( 'Community Channels', 'aws-sbg-theme' ); ?>
								</h3>
								<p class="text-cloud-light/80">
									<?php esc_html_e( 'WhatsApp Group: AWS SBG Community', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Discord: Join our tech community server', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Email: contact@airuni-aws-sbg.edu.pk', 'aws-sbg-theme' ); ?>
								</p>
							</div>
						</div>

						<!-- Events -->
						<div class="flex gap-6">
							<div class="text-4xl flex-shrink-0">🎓</div>
							<div>
								<h3 class="text-xl font-semibold text-aws-gold mb-2">
									<?php esc_html_e( 'Programs & Events', 'aws-sbg-theme' ); ?>
								</h3>
								<p class="text-cloud-light/80">
									<?php esc_html_e( 'Monthly workshops and technical bootcamps', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Guest lectures from AWS professionals', 'aws-sbg-theme' ); ?><br>
									<?php esc_html_e( 'Certification preparation programs', 'aws-sbg-theme' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Community Stats -->
				<div class="mt-12 grid grid-cols-2 gap-4">
					<div class="bg-aws-blue/50 border border-aws-gold/30 rounded-lg p-6 text-center hover:border-aws-gold/60 transition-colors">
						<p class="text-3xl font-bold text-aws-gold">500+</p>
						<p class="text-sm text-cloud-light/80 mt-1">
							<?php esc_html_e( 'Active Members', 'aws-sbg-theme' ); ?>
						</p>
					</div>
					<div class="bg-aws-blue/50 border border-aws-gold/30 rounded-lg p-6 text-center hover:border-aws-gold/60 transition-colors">
						<p class="text-3xl font-bold text-aws-gold">50+</p>
						<p class="text-sm text-cloud-light/80 mt-1">
							<?php esc_html_e( 'Events Hosted', 'aws-sbg-theme' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Right Column: Contact Form -->
			<div>
				<div class="bg-gradient-to-br from-aws-blue/50 to-cloud-dark border border-aws-gold/30 rounded-lg p-8 shadow-glow-blue">
					<h2 class="text-3xl font-bold text-aws-gold mb-6">
						<?php esc_html_e( 'Send Us a Message', 'aws-sbg-theme' ); ?>
					</h2>

					<form 
						method="POST" 
						action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						class="space-y-5"
					>
						<!-- Hidden Action Field -->
						<input type="hidden" name="action" value="submit_contact">
						<?php wp_nonce_field( 'contact_action', 'contact_nonce' ); ?>

						<!-- Name Field -->
						<div>
							<label for="contact_name" class="block text-sm font-semibold text-aws-gold mb-2">
								<?php esc_html_e( 'Your Name', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
							</label>
							<input 
								type="text" 
								id="contact_name" 
								name="name" 
								required
								class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
								placeholder="<?php esc_attr_e( 'John Doe', 'aws-sbg-theme' ); ?>"
							>
						</div>

						<!-- Email Field -->
						<div>
							<label for="contact_email" class="block text-sm font-semibold text-aws-gold mb-2">
								<?php esc_html_e( 'Your Email', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
							</label>
							<input 
								type="email" 
								id="contact_email" 
								name="email" 
								required
								class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
								placeholder="<?php esc_attr_e( 'john@example.com', 'aws-sbg-theme' ); ?>"
							>
						</div>

						<!-- Subject Field -->
						<div>
							<label for="contact_subject" class="block text-sm font-semibold text-aws-gold mb-2">
								<?php esc_html_e( 'Subject', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
							</label>
							<input 
								type="text" 
								id="contact_subject" 
								name="subject" 
								required
								class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all"
								placeholder="<?php esc_attr_e( 'Inquiry / Partnership / Other', 'aws-sbg-theme' ); ?>"
							>
						</div>

						<!-- Message Field -->
						<div>
							<label for="contact_message" class="block text-sm font-semibold text-aws-gold mb-2">
								<?php esc_html_e( 'Message', 'aws-sbg-theme' ); ?> <span class="text-danger">*</span>
							</label>
							<textarea 
								id="contact_message" 
								name="message" 
								required
								rows="5"
								class="w-full px-4 py-3 bg-cloud-dark border border-aws-gold/30 rounded-lg text-cloud-light placeholder-cloud-light/50 focus:outline-none focus:ring-2 focus:ring-aws-gold focus:border-transparent transition-all resize-none"
								placeholder="<?php esc_attr_e( 'Tell us about your message...', 'aws-sbg-theme' ); ?>"
							></textarea>
						</div>

						<!-- Submit Button with Alpine.js Loading State -->
						<div x-data="{ isSubmitting: false }">
							<button 
								type="submit" 
								@click="isSubmitting = true"
								:disabled="isSubmitting"
								:class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
								class="w-full px-6 py-3 bg-gradient-to-r from-aws-gold to-yellow-500 text-aws-blue font-bold rounded-lg hover:shadow-glow transition-all transform hover:scale-105 disabled:hover:scale-100"
							>
								<span x-show="!isSubmitting" class="flex items-center justify-center gap-2">
									<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
									</svg>
									<?php esc_html_e( 'Send Message', 'aws-sbg-theme' ); ?>
								</span>
								<span x-show="isSubmitting" class="flex items-center justify-center gap-2">
									<span class="inline-block w-4 h-4 border-2 border-aws-blue border-t-transparent rounded-full animate-spin"></span>
									<?php esc_html_e( 'Sending...', 'aws-sbg-theme' ); ?>
								</span>
							</button>
						</div>

						<!-- Privacy Note -->
						<p class="text-xs text-cloud-light/60 text-center">
							<?php esc_html_e( 'We\'ll only use your information to respond to your inquiry. Your privacy is important to us.', 'aws-sbg-theme' ); ?>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
