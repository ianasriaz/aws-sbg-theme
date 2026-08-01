<?php
/**
 * AWS SBG Event Registration System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Register Post Types
 */
function aws_sbg_event_post_types() {
	// Register Events Post Type
	register_post_type( 'aws_event', array(
		'labels' => array(
			'name' => 'Events',
			'singular_name' => 'Event',
			'add_new' => 'Add New Event',
			'add_new_item' => 'Add New Event',
		),
		'description' => 'Manage AWS SBG Events',
		'public' => true,
		'show_ui' => true,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		'menu_position' => 22,
		'menu_icon' => 'dashicons-calendar-alt',
		'has_archive' => true,
	) );

	// Register Event Registrations Post Type
	register_post_type( 'aws_event_reg', array(
		'labels' => array(
			'name' => 'Registrations',
			'singular_name' => 'Registration',
		),
		'description' => 'Event Registrations',
		'public' => false,
		'show_ui' => true,
		'supports' => array( 'title', 'custom-fields' ),
		'menu_position' => 23,
		'menu_icon' => 'dashicons-tickets-alt',
		'has_archive' => false,
		'can_export' => true,
	) );
}
add_action( 'init', 'aws_sbg_event_post_types' );

/**
 * 2. Register ACF Fields
 */
function aws_sbg_event_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_aws_event_registration',
		'title' => 'Event Registration Details',
		'fields' => array(
			array(
				'key' => 'field_reg_event',
				'label' => 'Event',
				'name' => 'event_id',
				'type' => 'post_object',
				'post_type' => array( 'aws_event' ),
				'return_format' => 'id',
				'ui' => 1,
				'required' => 1,
			),
			array(
				'key' => 'field_reg_email',
				'label' => 'Email',
				'name' => 'email',
				'type' => 'email',
				'required' => 1,
			),
			array(
				'key' => 'field_reg_whatsapp',
				'label' => 'WhatsApp Number',
				'name' => 'whatsapp_number',
				'type' => 'text',
				'required' => 1,
			),
			array(
				'key' => 'field_reg_university',
				'label' => 'University/Organization',
				'name' => 'university_organization',
				'type' => 'text',
				'required' => 1,
			),
			array(
				'key' => 'field_reg_ticket_tier',
				'label' => 'Ticket Tier',
				'name' => 'ticket_tier',
				'type' => 'radio',
				'choices' => array(
					'free'  => 'FREE (Online Sessions Only)',
					'pro'   => 'PRO (Sessions + Mock Exam + Voucher + Closing Ceremony)',
					'super' => 'SUPER (VIP Community Supporter)',
				),
				'required' => 1,
			),
			array(
				'key' => 'field_reg_payment_screenshot',
				'label' => 'Payment Screenshot',
				'name' => 'payment_screenshot',
				'type' => 'image',
				'return_format' => 'array',
				'required' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'aws_event_reg',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
	) );
}
add_action( 'acf/init', 'aws_sbg_event_acf_fields' );

/**
 * 3. Front-End Form Shortcode
 */
function aws_sbg_event_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'event_id' => 0,
		'slug'     => '',
	), $atts, 'aws_event_form' );

	$event_id = intval( $atts['event_id'] );
	
	// If slug is provided, find the event ID
	if ( ! $event_id && ! empty( $atts['slug'] ) ) {
		$event_post = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'aws_event' );
		if ( $event_post ) {
			$event_id = $event_post->ID;
		}
	}

	// If no ID passed, try to get current post ID
	if ( ! $event_id && is_singular( 'aws_event' ) ) {
		$event_id = get_the_ID();
	}

	if ( ! $event_id ) {
		return '<p class="text-red-500">Error: Event ID is missing. Please provide a slug like [aws_event_form slug="your-event-slug"]</p>';
	}

	$event_title = get_the_title( $event_id );

	ob_start();
	?>
	<style>
		/* Force white background on mobile to seamlessly blend the form */
		@media (max-width: 768px) {
			body { background-color: #ffffff !important; }
		}
		@keyframes shake {
			0%, 100% { transform: translateX(0); }
			20%, 60% { transform: translateX(-5px); }
			40%, 80% { transform: translateX(5px); }
		}
		.animate-shake { animation: shake 0.4s ease-in-out; }
	</style>
	<div class="not-prose max-w-2xl mx-auto my-8 md:my-12 w-full px-4 md:px-0">
		<div class="bg-white md:rounded-2xl px-6 py-8 md:p-10 shadow-sm border border-slate-200/80">

			<!-- Heading -->
			<div class="text-center mb-12">
				<h3 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight uppercase">
					REGISTER FOR <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ad5cff] to-[#7000ff]">CERTIFICATION DAY</span>
				</h3>
				<p class="text-slate-500 text-sm mt-1.5">Takes less than 2 minutes. Secure your spot below.</p>
			</div>

			<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'success' ) : 
				$reg_id = isset( $_GET['reg_id'] ) ? sanitize_text_field( $_GET['reg_id'] ) : 'N/A';
				$fname_full = isset( $_GET['fname'] ) ? sanitize_text_field( wp_unslash( $_GET['fname'] ) ) : '';
				$fname_parts = explode( ' ', trim( $fname_full ) );
				$first_name = ! empty( $fname_parts[0] ) ? $fname_parts[0] : '';
			?>
				<!-- Confetti Script (Loaded strictly via CDN on success to guarantee 0 server load) -->
				<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

				<!-- Success Modal -->
				<div x-data="{ open: true }" 
					 x-init="setTimeout(() => {
						if (typeof confetti !== 'undefined') {
							confetti({
								particleCount: 150,
								spread: 100,
								origin: { y: 0.6 },
								colors: ['#ad5cff', '#00ef88', '#ff9900'],
								zIndex: 1050
							});
						}
					 }, 400)"
					 x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
					<!-- Backdrop -->
					<div class="absolute inset-0 bg-[#0a0a0a]/60 backdrop-blur-sm" @click="open = false" 
						 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
						 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
					
					<!-- Modal Content -->
					<div class="bg-white rounded-[28px] shadow-2xl w-full max-w-sm relative z-10 p-8 text-center transform transition-all border border-gray-100"
						 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
						 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95">
						
						<!-- Check Icon -->
						<div class="w-20 h-20 bg-[#00ef88]/10 rounded-full flex items-center justify-center mx-auto mb-6">
							<svg class="w-10 h-10 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
								<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
							</svg>
						</div>
						
						<h3 class="text-2xl font-extrabold text-gray-900 mb-2">You're In<?php echo $first_name ? ', ' . esc_html( $first_name ) : ''; ?>!</h3>
						<p class="text-gray-500 text-[13px] leading-relaxed mb-6">Your registration was successful. Please save your unique Registration Number below.</p>
						
						<!-- Ticket Stub -->
						<div class="bg-gray-50 border border-gray-200 rounded-2xl py-5 px-6 mb-8 relative group overflow-hidden shadow-inner">
							<div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/60 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
							<span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Registration #</span>
							<span class="block text-[28px] font-mono font-extrabold text-[#ad5cff] tracking-tight"><?php echo esc_html( $reg_id ); ?></span>
						</div>
						
						<!-- Dismiss Button -->
						<button @click="open = false" type="button" class="w-full py-4 bg-gray-900 text-white font-bold text-[15px] rounded-xl hover:bg-gray-800 transition-colors shadow-lg hover:-translate-y-0.5 transform duration-200">
							Got it, thanks!
						</button>
					</div>
				</div>
			<?php endif; ?>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" class="space-y-8" 
				  x-data="{ 
					  isSuperSupporter: false,
					  showSuperDetails: false,
					  fullName: '', 
					  isSubmitting: false,
					  submitStatus: '',
					  formError: false,
					  validFields: { name: false, email: false, whatsapp: false, uni: false },
					  isCompressing: false,
					  compressedSize: '',
					  async handleImageUpload(e) {
						  const file = e.target.files[0];
						  if (!file || !file.type.startsWith('image/')) return;
						  
						  this.isCompressing = true;
						  this.compressedSize = '';
						  
						  try {
							  const compressedFile = await new Promise((resolve, reject) => {
								  const reader = new FileReader();
								  reader.readAsDataURL(file);
								  reader.onload = (ev) => {
									  const img = new Image();
									  img.src = ev.target.result;
									  img.onerror = () => reject('Image load failed');
									  img.onload = () => {
										  try {
											  const canvas = document.createElement('canvas');
											  let width = img.width; let height = img.height;
											  const MAX = 1200;
											  if (width > height) {
												  if (width > MAX) { height *= MAX / width; width = MAX; }
											  } else {
												  if (height > MAX) { width *= MAX / height; height = MAX; }
											  }
											  canvas.width = width; canvas.height = height;
											  const ctx = canvas.getContext('2d');
											  ctx.drawImage(img, 0, 0, width, height);
											  canvas.toBlob((blob) => {
												  if (!blob) return reject('Canvas export failed');
												  const newName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
												  resolve(new File([blob], newName, { type: 'image/webp' }));
											  }, 'image/webp', 0.8);
										  } catch(err) {
											  reject(err);
										  }
									  };
								  };
								  reader.onerror = () => reject('File read failed');
							  });

							  const dt = new DataTransfer();
							  dt.items.add(compressedFile);
							  e.target.files = dt.files;
							  this.compressedSize = (compressedFile.size / 1024).toFixed(1) + ' KB';
						  } catch (error) {
							  // Graceful Fallback: If anything fails, we just keep the original file!
							  console.warn('Compression failed, falling back to original file:', error);
							  this.compressedSize = 'Original File (Compression Skipped)';
						  } finally {
							  this.isCompressing = false;
						  }
					  },
					  handleFormSubmit(e) {
						  e.preventDefault();
						  this.isSubmitting = true;
						  this.submitStatus = 'verifying';
						  setTimeout(() => {
							  this.submitStatus = 'confirming';
							  setTimeout(() => {
								  this.submitStatus = 'processing';
								  e.target.submit();
							  }, 800);
						  }, 800);
					  }
				  }"
				  x-init="$watch('isSuperSupporter', value => { if(value) document.getElementById('payment-section').scrollIntoView({ behavior: 'smooth', block: 'center' }); })"
				  @submit="handleFormSubmit($event)"
				  @invalid.capture="formError = true; setTimeout(() => formError = false, 500)"
				  :class="formError ? 'animate-shake' : ''">
				<input type="hidden" name="action" value="submit_aws_event_registration">
				<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
				<?php wp_nonce_field( 'aws_event_registration_action', 'aws_event_registration_nonce' ); ?>

				<!-- Personal Details -->
				<div class="space-y-4">
					<div class="flex items-center gap-3">
						<span class="flex items-center justify-center w-6 h-6 rounded bg-[#ad5cff] text-white text-xs font-extrabold shadow-sm">1</span>
						<h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Personal Details</h4>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<!-- Full Name -->
						<div>
							<label for="reg_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
							<input type="text" id="reg_name" name="full_name" required x-model="fullName"
								class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-2 focus:ring-[#ad5cff]/15 transition-all">
						</div>

						<!-- Email -->
						<div>
							<label for="reg_email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
							<input type="email" id="reg_email" name="email" required
								class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-2 focus:ring-[#ad5cff]/15 transition-all">
						</div>

						<!-- WhatsApp -->
						<div>
							<label for="reg_whatsapp" class="block text-xs font-semibold text-slate-700 mb-1.5">WhatsApp Number <span class="text-red-500">*</span></label>
							<input type="tel" id="reg_whatsapp" name="whatsapp_number" required
								class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-2 focus:ring-[#ad5cff]/15 transition-all">
						</div>

						<!-- University / Organization -->
						<div>
							<label for="reg_uni" class="block text-xs font-semibold text-slate-700 mb-1.5">University / Organization <span class="text-red-500">*</span></label>
							<input type="text" id="reg_uni" name="university_organization" required
								class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-2 focus:ring-[#ad5cff]/15 transition-all">
						</div>
					</div>
				</div>

				<!-- Ticket Selection -->
				<div class="space-y-4 pt-6 mt-6 border-t border-slate-100">
					<div class="flex items-center gap-3 mb-2">
						<span class="flex items-center justify-center w-6 h-6 rounded bg-[#ad5cff] text-white text-xs font-extrabold shadow-sm">2</span>
						<h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Event Details</h4>
					</div>

					<div class="flex flex-col gap-3">
						<!-- PRO Tier Card (Complete Bootcamp) -->
						<div class="p-4 pt-5 sm:pt-4 rounded-xl border border-[#ad5cff] ring-1 ring-[#ad5cff]/20 bg-[#ad5cff]/[0.02] shadow-sm relative overflow-hidden block">
							<!-- Flush Recommended Badge -->
							<div class="absolute top-0 right-0 z-10 flex">
								<div class="bg-gradient-to-r from-[#ad5cff] to-[#7000ff] text-white text-[9px] font-extrabold uppercase tracking-widest py-1.5 px-3 rounded-bl-xl shadow-sm">In-Person Event</div>
							</div>

							<div class="flex flex-col sm:flex-row gap-4">
								<div class="flex-1">
									<div class="flex flex-wrap items-center gap-2 mb-1 pr-32 sm:pr-0">
										<div class="w-4 h-4 rounded-full border border-[#ad5cff] bg-[#ad5cff] flex items-center justify-center shrink-0">
											<div class="w-1.5 h-1.5 rounded-full bg-white"></div>
										</div>
										<h5 class="text-sm font-bold text-slate-900 flex items-center gap-2 flex-wrap">
											AWS Certification Day
											<span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 text-slate-500 text-[9px] rounded uppercase font-bold tracking-wider shadow-sm flex items-center gap-1 shrink-0">
												<svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
												Date: TBA
											</span>
										</h5>
									</div>
									<p class="text-[13px] text-slate-500 pl-7 leading-relaxed">Complete experience in Islamabad: Mock exams, swags, refreshments & Voucher Eligibility.</p>
									
									<!-- Feature List for Pro -->
									<div class="pl-7 mt-3 grid grid-cols-1 sm:grid-cols-2 gap-y-1.5 gap-x-4 text-[12px] text-slate-600">
										<div class="flex items-center gap-1.5">
											<svg class="w-3.5 h-3.5 text-[#ad5cff] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
											<span>AWS Certification Mock Exam</span>
										</div>
										<div class="flex items-center gap-1.5">
											<svg class="w-3.5 h-3.5 text-[#ad5cff] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
											<span>AWS Exam Voucher Eligibility</span>
										</div>
										<div class="flex items-center gap-1.5 sm:col-span-2">
											<svg class="w-3.5 h-3.5 text-[#ad5cff] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
											<span>Refreshments & Exclusive AWS Swags</span>
										</div>
									</div>
									
									<!-- Online Exception Note -->
									<div class="pl-7 mt-3.5">
										<div class="bg-amber-50/80 border border-amber-200/60 rounded-lg p-2.5 flex gap-2 items-start">
											<svg class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
											<p class="text-[11px] text-slate-600 leading-relaxed font-medium">
												<strong class="text-amber-800">Can't attend in-person?</strong> Complete registration and reach out to us at <a href="tel:+923492449953" class="font-bold underline hover:text-amber-800">+92 349 2449953</a> to request an online mock exam to remain eligible for the official AWS Exam Voucher.
											</p>
										</div>
									</div>
								</div>
								<div class="sm:pl-5 sm:border-l border-slate-100 pl-7 shrink-0 flex flex-col justify-start sm:justify-center sm:items-end mt-4 sm:mt-0">
									<div class="flex flex-col items-start sm:items-end gap-1.5">
										<div class="flex items-baseline gap-1.5">
											<span class="text-xs font-semibold text-slate-400 line-through">Rs. 799</span>
											<span class="text-xl font-extrabold text-[#ad5cff]">Rs. 499</span>
										</div>
										<span class="text-[9px] font-bold text-[#ad5cff] bg-[#ad5cff]/10 border border-[#ad5cff]/20 px-2 py-1 rounded uppercase tracking-wider inline-block">Earlybird</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<!-- Payment Block -->
			<div id="payment-section" class="mt-6 pt-6 border-t border-slate-100">
				
				<div class="space-y-6">
					<!-- Payment Instructions -->
					<div>
						<div class="flex items-center gap-3 mb-4">
							<span class="flex items-center justify-center w-6 h-6 rounded bg-[#ad5cff] text-white text-xs font-extrabold shadow-sm">3</span>
							<h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Copy Payment Details</h4>
						</div>
					
						<div class="bg-slate-50/80 rounded-xl border border-slate-200 divide-y divide-slate-200/60 overflow-hidden text-xs">
							<div class="flex justify-between items-center px-4 py-2.5">
								<span class="text-slate-500">Bank Name</span>
								<span class="font-semibold text-slate-900">Meezan Bank</span>
							</div>
							<div class="flex justify-between items-center px-4 py-2.5">
								<span class="text-slate-500">Account Title</span>
								<span class="font-semibold text-slate-900 uppercase">Muhammad Anas</span>
							</div>
							<div class="flex justify-between items-center px-4 py-2.5" x-data="{ copied: false }">
								<span class="text-slate-500">Account Number</span>
								<div class="flex items-center gap-2">
									<span class="font-mono font-semibold text-slate-900">03080111138765</span>
									<button type="button" @click="navigator.clipboard.writeText('03080111138765'); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-400 hover:text-[#ad5cff] transition-colors">
										<span x-show="!copied" class="text-[10px] font-semibold text-[#ad5cff] bg-[#ad5cff]/10 px-1.5 py-0.5 rounded">Copy</span>
										<span x-show="copied" style="display: none;" class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Copied!</span>
									</button>
								</div>
							</div>
							<div class="flex justify-between items-center px-4 py-2.5" x-data="{ copied: false }">
								<span class="text-slate-500">IBAN</span>
								<div class="flex items-center gap-2">
									<span class="font-mono font-semibold text-slate-900 text-[11px]">PK12MEZN0003080111138765</span>
									<button type="button" @click="navigator.clipboard.writeText('PK12MEZN0003080111138765'); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-400 hover:text-[#ad5cff] transition-colors">
										<span x-show="!copied" class="text-[10px] font-semibold text-[#ad5cff] bg-[#ad5cff]/10 px-1.5 py-0.5 rounded">Copy</span>
										<span x-show="copied" style="display: none;" class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Copied!</span>
									</button>
								</div>
							</div>
							<div class="flex justify-between items-center px-4 py-3 bg-[#ad5cff]/5">
								<span class="font-medium text-slate-700">Amount to Send</span>
								<span class="text-sm font-bold text-[#ad5cff]" x-text="isSuperSupporter ? 'Rs. 1,999' : 'Rs. 499'">Rs. 499</span>
							</div>
						</div>
					</div>

					<!-- Upload Screenshot -->
					<div>
						<div class="flex items-center gap-3 mb-4">
							<span class="flex items-center justify-center w-6 h-6 rounded bg-[#ad5cff] text-white text-xs font-extrabold shadow-sm">4</span>
							<h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">Upload Payment Screenshot <span class="text-red-500 ml-1.5 text-lg leading-none">*</span></h4>
						</div>
						
						<label for="payment_screenshot" class="sr-only">Upload Payment Receipt</label>
						<input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" required
							@change="handleImageUpload" :disabled="isCompressing"
							class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition-all border border-slate-200 rounded-xl bg-slate-50/50 cursor-pointer focus:outline-none focus:border-[#ad5cff]">
						
						<p x-show="!isCompressing && !compressedSize" class="mt-1.5 text-xs text-slate-400">
							Upload transaction receipt screenshot (max 2MB).
						</p>
						
						<p x-show="isCompressing" style="display: none;" class="mt-1.5 text-xs text-amber-600 flex items-center gap-1.5 font-medium animate-pulse">
							Compressing image in browser...
						</p>
						
						<p x-show="compressedSize" style="display: none;" class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1.5 font-medium">
							Optimized image size: <span x-text="compressedSize"></span>
						</p>
					</div>

					<!-- Student Community Disclaimer -->
					<div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex gap-3">
						<svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
						<p class="text-xs text-slate-600 leading-relaxed">
							<strong class="text-slate-800">We are a student-led community:</strong> Your registration fee helps cover venue, refreshments, and event logistics. If you're passionate about AWS but unable to pay the fee, please contact us at <a href="tel:+923492449953" class="font-semibold underline text-slate-900 hover:text-[#ad5cff]">+92 349 2449953</a>. We'd be happy to help you participate.
						</p>
					</div>
				</div>
			</div>

				<!-- Hidden True Tier Input -->
				<input type="hidden" name="ticket_tier" :value="isSuperSupporter ? 'super' : 'pro'">

				<!-- Standalone VIP Accordion Block -->
				<div class="mt-5 border-2 rounded-2xl overflow-hidden transition-all duration-300"
					 :class="isSuperSupporter ? 'border-[#ad5cff]/50 bg-[#ad5cff]/[0.02] shadow-[0_4px_20px_-4px_rgba(173,92,255,0.15)]' : 'border-slate-200 bg-slate-50/50 shadow-sm'">
					
					<button type="button" @click="showSuperDetails = !showSuperDetails" class="w-full flex flex-col sm:flex-row sm:items-center justify-between p-4 focus:outline-none hover:bg-[#ad5cff]/5 transition-colors gap-3">
						<div class="flex items-center gap-3 text-left w-full sm:w-auto">
							<div class="flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-[#ad5cff] to-[#7000ff] shadow-sm shrink-0">
								<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
								</svg>
							</div>
							<span class="text-[13px] sm:text-sm font-extrabold text-slate-800 tracking-wide uppercase leading-tight">BECOME A SUPER COMMUNITY SUPPORTER</span>
						</div>
						<div class="self-start sm:self-auto flex items-center gap-1.5 text-[11px] sm:text-xs font-bold text-[#ad5cff] shrink-0 bg-[#ad5cff]/10 border border-[#ad5cff]/20 px-3 py-1.5 rounded-full transition-all ml-11 sm:ml-0">
							<span x-text="showSuperDetails ? 'Close' : 'View VIP Tier'"></span>
							<svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="showSuperDetails ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
						</div>
					</button>

					<div x-show="showSuperDetails" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
						<div class="p-4 pt-0">
							<div class="relative overflow-hidden rounded-xl border border-[#ad5cff]/30 bg-gradient-to-br from-[#ad5cff]/[0.05] via-white to-[#7000ff]/[0.08] p-6 shadow-sm">
								<!-- Decorative Glass Blurs -->
								<div class="absolute -top-12 -right-12 w-48 h-48 bg-gradient-to-br from-[#ad5cff]/20 to-transparent rounded-full blur-3xl pointer-events-none"></div>
								<div class="absolute -bottom-12 -left-12 w-48 h-48 bg-gradient-to-tr from-[#7000ff]/15 to-transparent rounded-full blur-3xl pointer-events-none"></div>

								<div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5 border-b border-[#ad5cff]/10 pb-5">
									<div>
										<h4 class="text-lg font-extrabold text-slate-900 tracking-tight">Support the community. Enjoy exclusive recognition</h4>
										<p class="text-slate-600 text-[13px] mt-1.5 leading-relaxed font-medium">Help us grow the community and receive special recognition as one of our valued super community supporters.</p>
									</div>
									<div class="text-left sm:text-right shrink-0">
										<span class="block text-3xl font-black text-transparent bg-clip-text bg-gradient-to-br from-[#ad5cff] to-[#7000ff] drop-shadow-sm">Rs. 1,999</span>
									</div>
								</div>

								<div class="relative z-10 space-y-3 text-[13px] font-semibold text-slate-700 mb-7">
									<div class="flex items-start gap-2.5">
										<div class="w-5 h-5 rounded-full bg-[#00ef88]/20 flex items-center justify-center shrink-0 mt-0.5">
											<svg class="w-3 h-3 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span>Includes All AIRCERT Certification Day Benefits</span>
									</div>
									<div class="flex items-start gap-2.5">
										<div class="w-5 h-5 rounded-full bg-[#00ef88]/20 flex items-center justify-center shrink-0 mt-0.5">
											<svg class="w-3 h-3 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span>Exclusive Social Media Recognition</span>
									</div>
									<div class="flex items-start gap-2.5">
										<div class="w-5 h-5 rounded-full bg-[#00ef88]/20 flex items-center justify-center shrink-0 mt-0.5">
											<svg class="w-3 h-3 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span>Personalized VIP Event Pass</span>
									</div>
									<div class="flex items-start gap-2.5">
										<div class="w-5 h-5 rounded-full bg-[#00ef88]/20 flex items-center justify-center shrink-0 mt-0.5">
											<svg class="w-3 h-3 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span>Name Featured on Event Standee</span>
									</div>
									<div class="flex items-start gap-2.5">
										<div class="w-5 h-5 rounded-full bg-[#00ef88]/20 flex items-center justify-center shrink-0 mt-0.5">
											<svg class="w-3 h-3 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span>Exclusive AWS VIP Swag Kit</span>
									</div>
								</div>

								<label class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
									   :class="isSuperSupporter ? 'border-[#ad5cff] bg-white shadow-[0_2px_15px_-3px_rgba(173,92,255,0.2)]' : 'border-slate-200/80 bg-white/60 hover:bg-white hover:border-[#ad5cff]/30'">
									<div class="flex items-start sm:items-center gap-3.5">
										<div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-colors duration-200 mt-0.5 sm:mt-0"
											 :class="isSuperSupporter ? 'border-[#ad5cff] bg-[#ad5cff]' : 'border-slate-300'">
											<svg class="w-3 h-3 text-white transition-transform duration-200" :class="isSuperSupporter ? 'scale-100 opacity-100' : 'scale-50 opacity-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
										</div>
										<span class="font-bold text-sm sm:text-[15px] leading-snug" :class="isSuperSupporter ? 'text-slate-900' : 'text-slate-600'">Yes, I want to become a Super Supporter!</span>
									</div>
									<input type="checkbox" class="sr-only" x-model="isSuperSupporter">
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- Submit Button -->
				<div class="pt-4">
					<button type="submit" 
							:disabled="isSubmitting"
							class="w-full px-6 py-3.5 bg-[#ad5cff] hover:bg-[#9b42f5] active:bg-[#8b32e5] text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-sm flex items-center justify-center gap-2 disabled:opacity-75">
						
						<span x-show="!isSubmitting" x-text="isSuperSupporter ? 'Confirm - Super Supporter' : 'Confirm - Full Access'">Confirm Registration</span>
						
						<span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
							<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
							<span x-text="submitStatus === 'verifying' ? 'Verifying Details...' : (submitStatus === 'confirming' ? 'Confirming Spot...' : 'Processing...')"></span>
						</span>
					</button>
					<p class="mt-4 text-center text-[10px] font-medium text-slate-400">
						Made with <span class="inline-block animate-pulse text-red-500">❤️</span> by Anas. Hosted on AWS
					</p>
				</div>
			</form>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aws_event_form', 'aws_sbg_event_form_shortcode' );

/**
 * 4. Form Processing Handler
 */
function aws_sbg_handle_event_registration() {
	if ( ! isset( $_POST['aws_event_registration_nonce'] ) || ! wp_verify_nonce( $_POST['aws_event_registration_nonce'], 'aws_event_registration_action' ) ) {
		wp_die( 'Security check failed.' );
	}

	$event_id    = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$full_name   = isset( $_POST['full_name'] ) ? sanitize_text_field( $_POST['full_name'] ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$whatsapp    = isset( $_POST['whatsapp_number'] ) ? sanitize_text_field( $_POST['whatsapp_number'] ) : '';
	$university  = isset( $_POST['university_organization'] ) ? sanitize_text_field( $_POST['university_organization'] ) : '';
	$ticket_tier = isset( $_POST['ticket_tier'] ) ? sanitize_text_field( $_POST['ticket_tier'] ) : 'free';

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $university ) || empty( $event_id ) ) {
		wp_die( 'Required fields are missing.' );
	}

	if ( in_array( $ticket_tier, ['pro', 'super'] ) && empty( $_FILES['payment_screenshot']['name'] ) ) {
		wp_die( 'Payment screenshot is strictly required for Paid tiers.' );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'aws_event_reg',
		'post_title'  => $full_name . ' - ' . get_the_title( $event_id ),
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_die( 'Failed to create registration: ' . $post_id->get_error_message() );
	}

	// Generate Sequential Registration ID (Offset by 1000 to mask total count)
	$count = wp_count_posts('aws_event_reg')->publish;
	$base_number = 1000 + $count;
	$registration_id = (string) $base_number;



	// Save Registration ID as Post Meta
	update_post_meta( $post_id, 'registration_id', $registration_id );

	// Save meta via ACF if active
	if ( function_exists( 'update_field' ) ) {
		// Use field keys for new posts so ACF maps them correctly
		update_field( 'field_reg_event', $event_id, $post_id );
		update_field( 'field_reg_email', $email, $post_id );
		update_field( 'field_reg_whatsapp', $whatsapp, $post_id );
		update_field( 'field_reg_university', $university, $post_id );
		update_field( 'field_reg_ticket_tier', $ticket_tier, $post_id );
	}

	// Handle Payment Screenshot Upload securely
	if ( in_array( $ticket_tier, ['pro', 'super'] ) && ! empty( $_FILES['payment_screenshot']['name'] ) ) {
		$file_size = $_FILES['payment_screenshot']['size'];
		if ( $file_size > 2 * 1024 * 1024 ) { // 2MB Limit
			wp_die( 'The payment screenshot exceeds the maximum allowed file size of 2MB. Please compress your image and try again.' );
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$uploaded_file = $_FILES['payment_screenshot'];
		$upload_overrides = array( 'test_form' => false ); // Because we're not submitting from standard WP form

		$movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$filename = $movefile['file'];
			$filetype = wp_check_filetype( basename( $filename ), null );
			
			$attachment = array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
			
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			if ( ! is_wp_error( $attach_id ) ) {
				// Prevent WP from generating thumbnail sizes for this specific upload to save CPU
				add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 999 );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 999 );

				if ( function_exists( 'update_field' ) ) {
					update_field( 'field_reg_payment_screenshot', $attach_id, $post_id );
				}
			}
		} else {
			// If upload fails, you could technically delete the post or log an error.
			// For robustness, we allow the registration to proceed but it will lack the proof.
			error_log( 'AWS Event Reg Upload Error: ' . $movefile['error'] );
		}
	}

	// Redirect back
	$redirect_url = add_query_arg( array(
		'status' => 'success',
		'reg_id' => $registration_id,
		'fname'  => $full_name
	), wp_get_referer() );
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_nopriv_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );
add_action( 'admin_post_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );