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
					'free' => 'FREE (Online Sessions Only)',
					'pro'  => 'PRO (Sessions + Mock Exam + Voucher + Closing Ceremony)',
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
	</style>
	<div class="not-prose max-w-3xl mx-auto md:my-10 w-full">
		<div class="bg-white md:rounded-[28px] px-5 py-10 md:p-12 shadow-none md:shadow-[0_1px_2px_rgb(0,0,0,0.04),0_20px_60px_-15px_rgb(0,0,0,0.08)] border-none md:border border-gray-100">

			<!-- Heading -->
			<div class="text-center mb-10">
				<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ad5cff]/[0.08] text-[#ad5cff] text-[11px] font-bold uppercase tracking-widest mb-4">
					<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 01.894.553l1.382 2.764 3.05.443a1 1 0 01.554 1.706l-2.207 2.152.521 3.04a1 1 0 01-1.451 1.054L10 12.28l-2.743 1.432a1 1 0 01-1.451-1.054l.521-3.04-2.207-2.152a1 1 0 01.554-1.706l3.05-.443L9.106 2.553A1 1 0 0110 2z"></path></svg>
					Registration Open
				</span>
				<h3 class="text-3xl md:text-[34px] font-extrabold text-gray-900 mb-2.5 tracking-tight">
					Register for <?php echo esc_html( $event_title ); ?>
				</h3>
				<p class="text-gray-500 text-[15px]">Takes less than 2 minutes. Secure your spot below.</p>
			</div>

			<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'success' ) : 
				$reg_id = isset( $_GET['reg_id'] ) ? sanitize_text_field( $_GET['reg_id'] ) : 'N/A';
			?>
				<!-- Success Modal -->
				<div x-data="{ open: true }" x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
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
						
						<h3 class="text-2xl font-extrabold text-gray-900 mb-2">You're In!</h3>
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

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" class="space-y-8" @submit="isSubmitting = true" x-data="{ selectedTier: 'free', fullName: '', isSubmitting: false }">
				<input type="hidden" name="action" value="submit_aws_event_registration">
				<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
				<?php wp_nonce_field( 'aws_event_registration_action', 'aws_event_registration_nonce' ); ?>

				<!-- Personal Details -->
				<div class="space-y-5">
					<h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Your Details</h4>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
						<!-- Full Name -->
						<div class="relative">
							<input type="text" id="reg_name" name="full_name" required placeholder=" " x-model="fullName"
								class="peer w-full bg-gray-50/70 border border-gray-200 rounded-xl px-4 pt-5 pb-2 text-[15px] text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-4 focus:ring-[#ad5cff]/10 transition-all duration-200">
							<label for="reg_name"
								class="absolute left-4 top-3.5 text-gray-400 text-[15px] transition-all duration-200 pointer-events-none
									peer-focus:top-1.5 peer-focus:text-[11px] peer-focus:font-semibold peer-focus:text-[#ad5cff]
									peer-[&:not(:placeholder-shown)]:top-1.5 peer-[&:not(:placeholder-shown)]:text-[11px] peer-[&:not(:placeholder-shown)]:font-semibold peer-[&:not(:placeholder-shown)]:text-gray-500">
								Full Name
							</label>
						</div>

						<!-- Email -->
						<div class="relative">
							<input type="email" id="reg_email" name="email" required placeholder=" "
								class="peer w-full bg-gray-50/70 border border-gray-200 rounded-xl px-4 pt-5 pb-2 text-[15px] text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-4 focus:ring-[#ad5cff]/10 transition-all duration-200">
							<label for="reg_email"
								class="absolute left-4 top-3.5 text-gray-400 text-[15px] transition-all duration-200 pointer-events-none
									peer-focus:top-1.5 peer-focus:text-[11px] peer-focus:font-semibold peer-focus:text-[#ad5cff]
									peer-[&:not(:placeholder-shown)]:top-1.5 peer-[&:not(:placeholder-shown)]:text-[11px] peer-[&:not(:placeholder-shown)]:font-semibold peer-[&:not(:placeholder-shown)]:text-gray-500">
								Email Address
							</label>
						</div>

						<!-- WhatsApp -->
						<div class="relative">
							<input type="tel" id="reg_whatsapp" name="whatsapp_number" required placeholder=" "
								class="peer w-full bg-gray-50/70 border border-gray-200 rounded-xl px-4 pt-5 pb-2 text-[15px] text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-4 focus:ring-[#ad5cff]/10 transition-all duration-200">
							<label for="reg_whatsapp"
								class="absolute left-4 top-3.5 text-gray-400 text-[15px] transition-all duration-200 pointer-events-none
									peer-focus:top-1.5 peer-focus:text-[11px] peer-focus:font-semibold peer-focus:text-[#ad5cff]
									peer-[&:not(:placeholder-shown)]:top-1.5 peer-[&:not(:placeholder-shown)]:text-[11px] peer-[&:not(:placeholder-shown)]:font-semibold peer-[&:not(:placeholder-shown)]:text-gray-500">
								WhatsApp Number
							</label>
						</div>

						<!-- University / Organization -->
						<div class="relative">
							<input type="text" id="reg_uni" name="university_organization" required placeholder=" "
								class="peer w-full bg-gray-50/70 border border-gray-200 rounded-xl px-4 pt-5 pb-2 text-[15px] text-gray-900 placeholder-transparent focus:outline-none focus:bg-white focus:border-[#ad5cff] focus:ring-4 focus:ring-[#ad5cff]/10 transition-all duration-200">
							<label for="reg_uni"
								class="absolute left-4 top-3.5 text-gray-400 text-[15px] transition-all duration-200 pointer-events-none
									peer-focus:top-1.5 peer-focus:text-[11px] peer-focus:font-semibold peer-focus:text-[#ad5cff]
									peer-[&:not(:placeholder-shown)]:top-1.5 peer-[&:not(:placeholder-shown)]:text-[11px] peer-[&:not(:placeholder-shown)]:font-semibold peer-[&:not(:placeholder-shown)]:text-gray-500">
								University / Organization
							</label>
						</div>
					</div>
				</div>

				<!-- Ticket Selection -->
				<div class="space-y-5 pt-2">
					<h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">How would you like to attend?</h4>

					<div class="flex flex-col gap-4">
						<!-- FREE Tier -->
						<label class="group relative cursor-pointer block" @click="selectedTier = 'free'">
							<input type="radio" name="ticket_tier" value="free" class="sr-only" x-model="selectedTier" required>
							<div class="p-5 rounded-2xl border transition-all duration-300 ease-out bg-white"
								 :class="selectedTier === 'free' ? 'border-[#ad5cff] ring-1 ring-[#ad5cff]/60 shadow-md' : 'border-gray-200 hover:border-gray-300'">
								<div class="flex items-start gap-4">
									<div class="w-5 h-5 mt-0.5 rounded-full border-2 flex items-center justify-center transition-all duration-200 shrink-0"
										 :class="selectedTier === 'free' ? 'border-[#ad5cff] bg-[#ad5cff]' : 'border-gray-300'">
										<svg class="w-2.5 h-2.5 text-white transition-opacity duration-200" :class="selectedTier === 'free' ? 'opacity-100' : 'opacity-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
									</div>
									<div class="w-full">
										<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1 sm:gap-4">
											<div>
												<span class="block text-[15px] font-extrabold text-gray-900">Virtual Only</span>
												<span class="block text-[13px] text-gray-500 mt-0.5">Access to Online Sessions Only</span>
											</div>
											<div class="text-left sm:text-right shrink-0 mt-1 sm:mt-0">
												<span class="block text-lg font-extrabold text-gray-900">Free</span>
											</div>
										</div>
											<div class="mt-3 space-y-1.5 text-[12px] font-medium text-gray-500">
												<span class="flex items-center gap-2 text-gray-700"><svg class="w-3.5 h-3.5 text-[#00ef88]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Access to All Virtual Sessions</span>
												<span class="flex items-center gap-2 opacity-50 line-through decoration-gray-400"><svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg> AWS Mock Exam & Voucher Eligibility</span>
												<span class="flex items-center gap-2 opacity-50 line-through decoration-gray-400"><svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg> Invitation to Closing Event</span>
												<span class="flex items-center gap-2 opacity-50 line-through decoration-gray-400"><svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg> AWS Swags, Refreshment Lunch, Activities</span>
											</div>
										</div>
									</div>
								</div>
						</label>

						<!-- PRO Tier -->
						<label class="group relative cursor-pointer block" @click="selectedTier = 'pro'">
							<input type="radio" name="ticket_tier" value="pro" class="sr-only" x-model="selectedTier" required>
							<div class="p-5 rounded-2xl border transition-all duration-300 ease-out bg-white relative"
								 :class="selectedTier === 'pro' ? 'border-[#ad5cff] ring-1 ring-[#ad5cff]/60 shadow-md' : 'border-gray-200 hover:border-[#ad5cff]/40'">
								<div class="flex items-start gap-4">
									<div class="w-5 h-5 mt-0.5 rounded-full border-2 flex items-center justify-center transition-all duration-200 shrink-0"
										 :class="selectedTier === 'pro' ? 'border-[#ad5cff] bg-[#ad5cff]' : 'border-gray-300'">
										<svg class="w-2.5 h-2.5 text-white transition-opacity duration-200" :class="selectedTier === 'pro' ? 'opacity-100' : 'opacity-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
									</div>
									<div class="w-full">
										<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1 sm:gap-4">
											<div>
												<div class="flex items-center gap-2 flex-wrap">
													<span class="text-[15px] font-extrabold text-gray-900">Virtual + In-Person</span>
													<span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md uppercase tracking-widest hidden sm:inline-block">Covers Food & Swag</span>
												</div>
												<span class="block text-[13px] text-gray-500 mt-0.5">Get full access & Invitation to Closing Event in Islamabad.</span>
											</div>
											<div class="text-left sm:text-right shrink-0 mt-1 sm:mt-0 flex flex-row sm:flex-col items-center sm:items-end gap-2 sm:gap-0">
												<span class="block text-lg font-extrabold text-[#ad5cff]">Rs. 699</span>
												<div class="flex items-center gap-1.5 sm:mt-0.5">
													<span class="text-[10px] text-gray-400 line-through">Rs. 999</span>
													<span class="text-[9px] font-bold text-[#ff9900] bg-[#ff9900]/10 px-1.5 py-0.5 rounded uppercase tracking-widest">Earlybird</span>
												</div>
											</div>
										</div>
											<div class="mt-3 space-y-1.5 text-[12px] font-medium text-gray-700">
												<span class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#ad5cff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Access to All Virtual Sessions</span>
												<span class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#ad5cff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> AWS Mock Exam & Voucher Eligibility</span>
												<span class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#ad5cff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Invitation to Closing Event</span>
												<span class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-[#ad5cff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> AWS Swags, Refreshment Lunch, Activities</span>
											</div>
										</div>
									</div>
								</div>
						</label>
					</div>
				</div>

				<!-- PRO Conditional Block (Payment + Preview) -->
			<div x-show="selectedTier === 'pro'" 
				 x-transition:enter="transition ease-out duration-300"
				 x-transition:enter-start="opacity-0 translate-y-4"
				 x-transition:enter-end="opacity-100 translate-y-0"
				 style="display: none;"
				 class="mt-8 pt-8 border-t border-gray-100">
				
				<div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-start">
					<!-- Left: Details & Payment -->
					<div class="space-y-8">

						<!-- Payment Instructions -->
						<div>
							<h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
								<svg class="w-4 h-4 text-[#ad5cff]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
								Secure Payment Instructions
							</h4>
						
						<div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 mb-5 shadow-sm">
							<div class="flex justify-between items-center px-4 py-3">
								<span class="text-sm text-gray-500">Bank Name</span>
								<span class="text-sm font-bold text-gray-900">Meezan Bank</span>
							</div>
							<div class="flex justify-between items-center px-4 py-3">
								<span class="text-sm text-gray-500">Account Title</span>
								<span class="text-sm font-bold text-gray-900">AWS Cloud Club</span>
							</div>
							<div class="flex justify-between items-center px-4 py-3">
								<span class="text-sm text-gray-500">Account Number</span>
								<span class="text-sm font-bold text-gray-900 font-mono tracking-tight">01234567891234</span>
							</div>
							<div class="flex justify-between items-center px-4 py-3 bg-[#ad5cff]/[0.05]">
								<span class="text-sm text-gray-500">Amount to Send</span>
								<span class="text-sm font-extrabold text-[#ad5cff]">Rs. 699</span>
							</div>
						</div>

						<div>
							<label for="payment_screenshot" class="block text-sm font-semibold text-gray-700 mb-2">
								Upload Payment Screenshot <span class="text-red-500">*</span>
							</label>
							<div class="relative">
								<input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" :required="selectedTier === 'pro'"
									class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#ad5cff] file:text-white hover:file:opacity-90 file:transition-opacity file:cursor-pointer transition-all border border-gray-200 rounded-xl bg-white cursor-pointer shadow-sm focus:outline-none focus:ring-4 focus:ring-[#ad5cff]/10 focus:border-[#ad5cff]">
							</div>
							<p class="mt-2 text-xs text-gray-400 flex items-center gap-1.5">
								<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"></path></svg>
								Please upload a clear image of your transaction receipt (JPG/PNG).
							</p>
						</div>
					</div>
					</div>

					<!-- Right: Live ID Card Preview -->
					<div class="relative flex flex-col items-center">
						<h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2 self-start w-full">
							<svg class="w-4 h-4 text-[#ff9900]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
							Your Live Custom ID Card Preview
						</h4>
						
						<div class="relative w-full max-w-[280px] rounded-2xl overflow-hidden shadow-xl border border-gray-200 group bg-gray-50 flex justify-center">
							<img src="https://awscloudclubau.com/wp-content/uploads/2026/07/Aircert-Preview.jpg" alt="AWS AirCert ID Preview" draggable="false" oncontextmenu="return false;" class="w-full h-auto object-contain transition-transform duration-700 group-hover:scale-105 pointer-events-none select-none">
							
							<!-- Live Name Overlay targeting the center of the image -->
							<div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
								<div class="text-center px-4 w-full">
									<div class="inline-block bg-[#1a1a1a] text-gray-100 font-mono text-xl sm:text-2xl font-bold uppercase tracking-widest px-4 py-1.5 min-w-[80%] max-w-full truncate shadow-lg" x-text="fullName || 'YOUR NAME'"></div>
								</div>
							</div>
						</div>
						<p class="mt-4 text-xs text-center text-gray-500 max-w-[280px]">
							This is a preview of the physical personalized ID card you will receive at the Closing Ceremony.
						</p>
					</div>
				</div>
			</div>

				<!-- Submit Button -->
				<div class="pt-4 relative z-20">
					<button type="submit" 
							@click="isSubmitting = true"
							:disabled="isSubmitting"
							:class="isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:opacity-90 hover:-translate-y-0.5'"
							class="block w-full box-border px-8 py-4 bg-[#ad5cff] text-white font-bold text-[15px] rounded-2xl focus:outline-none focus:ring-4 focus:ring-[#ad5cff]/25 transition-all duration-200 transform shadow-[0_8px_24px_-6px_rgba(173,92,255,0.45)] flex items-center justify-center gap-2">
						
						<span x-show="!isSubmitting" x-text="selectedTier === 'pro' ? 'Confirm - Full Access' : 'Confirm - Sessions Only'">Confirm Registration</span>
						
						<span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
							<svg class="animate-spin -ml-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
							Processing...
						</span>
					</button>
					<p class="mt-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
						Made with <span class="text-red-500 text-xs">❤️</span> by Anas, Hosted on AWS
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

	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$full_name = isset( $_POST['full_name'] ) ? sanitize_text_field( $_POST['full_name'] ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$whatsapp   = isset( $_POST['whatsapp_number'] ) ? sanitize_text_field( $_POST['whatsapp_number'] ) : '';
	$university = isset( $_POST['university_organization'] ) ? sanitize_text_field( $_POST['university_organization'] ) : '';
	$ticket_tier= isset( $_POST['ticket_tier'] ) ? sanitize_text_field( $_POST['ticket_tier'] ) : 'free';
	$event_id    = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
	$full_name   = isset( $_POST['full_name'] ) ? sanitize_text_field( $_POST['full_name'] ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$whatsapp    = isset( $_POST['whatsapp_number'] ) ? sanitize_text_field( $_POST['whatsapp_number'] ) : '';
	$university  = isset( $_POST['university_organization'] ) ? sanitize_text_field( $_POST['university_organization'] ) : '';
	$ticket_tier = isset( $_POST['ticket_tier'] ) ? sanitize_text_field( $_POST['ticket_tier'] ) : 'free';

	if ( empty( $full_name ) || empty( $email ) || empty( $whatsapp ) || empty( $university ) || empty( $event_id ) ) {
		wp_die( 'Required fields are missing.' );
	}

	if ( $ticket_tier === 'pro' && empty( $_FILES['payment_screenshot']['name'] ) ) {
		wp_die( 'Payment screenshot is strictly required for the PRO tier.' );
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

	// Update Post Title to include Registration ID
	wp_update_post( array(
		'ID'         => $post_id,
		'post_title' => $full_name . ' (' . $registration_id . ') - ' . get_the_title( $event_id )
	) );

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
	if ( $ticket_tier === 'pro' && ! empty( $_FILES['payment_screenshot']['name'] ) ) {
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
		'reg_id' => $registration_id
	), wp_get_referer() );
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_nopriv_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );
add_action( 'admin_post_submit_aws_event_registration', 'aws_sbg_handle_event_registration' );