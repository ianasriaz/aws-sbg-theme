<?php
/**
 * AWS SBG Admin Dashboard for Event Registrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Admin Menu Page
 */
function aws_sbg_register_admin_dashboard() {
	add_menu_page(
		'AWS SBG Dashboard',
		'AWS Dashboard',
		'manage_options',
		'aws-sbg-dashboard',
		'aws_sbg_render_admin_dashboard',
		'dashicons-cloud',
		2
	);
}
add_action( 'admin_menu', 'aws_sbg_register_admin_dashboard' );

/**
 * Render the Admin Dashboard
 */
function aws_sbg_render_admin_dashboard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized access.' );
	}

	// Fetch registrations
	$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$args = array(
		'post_type' => 'aws_event_reg',
		'posts_per_page' => 50,
		'paged' => $paged,
		'post_status' => 'publish',
	);
	
	$meta_query = array( 'relation' => 'AND' );

	// Handle event filtering
	$selected_event = isset( $_GET['filter_event'] ) ? intval( $_GET['filter_event'] ) : 0;
	if ( $selected_event ) {
		$meta_query[] = array(
			'key' => 'event_id',
			'value' => $selected_event,
			'compare' => '=',
		);
	}

	// Handle search by Registration ID
	$search_reg = isset( $_GET['search_reg'] ) ? sanitize_text_field( $_GET['search_reg'] ) : '';
	if ( $search_reg ) {
		$meta_query[] = array(
			'key' => 'registration_id',
			'value' => $search_reg,
			'compare' => 'LIKE',
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	$registrations = new WP_Query( $args );

	// Fetch all events for the filter dropdown
	$events = get_posts( array(
		'post_type' => 'aws_event',
		'posts_per_page' => -1,
		'post_status' => 'publish',
	) );
	?>
	<div class="wrap">
		<!-- Load Tailwind CSS just for this dashboard to override WP admin styles -->
		<script src="https://cdn.tailwindcss.com"></script>
		<script>
			tailwind.config = {
				theme: {
					extend: {
						colors: {
							brand: '#ad5cff',
							'supporting-green': '#00ef88',
							dark: '#0a0a0a',
							card: '#151515',
						}
					}
				}
			}
		</script>
		<style>
			@media print {
				/* Hide WP Admin elements completely */
				#adminmenumain, #wpadminbar, #adminmenuwrap, #wpfooter, .update-nag, .notice { display: none !important; }
				html, body, #wpcontent, #wpwrap { background: #fff !important; color: #000 !important; margin: 0 !important; padding: 0 !important; margin-left: 0 !important; }
				
				/* Hide dashboard widgets / interactive elements */
				.flex.justify-between.items-center.mb-10, .grid.grid-cols-1, form { display: none !important; }
				
				/* Strip dark theme from main containers */
				.wrap, .mt-8, .bg-dark, .bg-card, table, tbody, tr, th, td {
					background: transparent !important;
					border: none !important;
					box-shadow: none !important;
					color: #000 !important;
					min-height: auto !important;
					margin: 0 !important;
					padding: 0 !important;
				}
				
				/* Clean up table for printing */
				.overflow-hidden { overflow: visible !important; }
				table { width: 100% !important; border-collapse: collapse !important; margin-top: 20px !important; }
				th, td { border: 1px solid #ccc !important; padding: 12px 10px !important; text-align: left !important; font-size: 13px !important; white-space: normal !important; }
				th { background: #f8f9fa !important; font-weight: bold !important; text-transform: uppercase !important; font-size: 12px !important; }
				
				/* Hide unnecessary columns to keep export clean */
				th:nth-child(6), td:nth-child(6),
				th:nth-child(7), td:nth-child(7),
				th:nth-child(8), td:nth-child(8),
				th:nth-child(9), td:nth-child(9) {
					display: none !important;
				}
				
				/* Format text & badges */
				span, a { color: #000 !important; border: none !important; background: transparent !important; text-decoration: none !important; padding: 0 !important; font-weight: normal !important; }
				.font-bold { font-weight: bold !important; }
				
				/* Add a title just for the print page */
				table {
					caption-side: top;
				}
				table caption {
					font-size: 20px !important;
					font-weight: bold !important;
					margin-bottom: 20px !important;
					text-align: left !important;
					color: #000 !important;
				}
			}
		</style>

		<div class="mt-8 bg-dark min-h-screen rounded-xl overflow-hidden shadow-2xl p-8 border border-white/10 text-white font-sans">
			
			<!-- Dashboard Header -->
			<div class="flex justify-between items-center mb-10">
				<div>
					<h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand to-supporting-green mb-2">AWS SBG Event Dashboard</h1>
					<p class="text-gray-400">Manage all your event registrations and community data in one place.</p>
				</div>
				<div>
					<button type="button" onclick="if(!document.fullscreenElement){document.documentElement.requestFullscreen();}else{document.exitFullscreen();}" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg transition-colors border border-white/20 shadow-lg inline-flex items-center gap-2 mr-3">
						<span class="dashicons dashicons-editor-expand before:mt-1"></span> Full Screen
					</button>
					<a href="#" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg transition-colors border border-white/20 shadow-lg inline-flex items-center gap-2" onclick="window.print()">
						<span class="dashicons dashicons-media-spreadsheet before:mt-1"></span> Export Data
					</a>
				</div>
			</div>

			<!-- Metrics Cards -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
				<div class="bg-card rounded-xl p-6 border border-brand/20 shadow-[0_0_15px_rgba(173,92,255,0.1)] relative overflow-hidden group">
					<div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
						<span class="dashicons dashicons-groups text-8xl text-brand"></span>
					</div>
					<h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Total Registrations</h3>
					<div class="text-5xl font-bold text-white"><?php echo esc_html( $registrations->found_posts ); ?></div>
				</div>
				
				<div class="bg-card rounded-xl p-6 border border-supporting-green/20 shadow-[0_0_15px_rgba(0,239,136,0.1)] relative overflow-hidden group">
					<div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
						<span class="dashicons dashicons-calendar-alt text-8xl text-supporting-green"></span>
					</div>
					<h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider mb-2">Active Events</h3>
					<div class="text-5xl font-bold text-white"><?php echo count($events); ?></div>
				</div>
			</div>

			<!-- Filters -->
			<div class="bg-card rounded-xl p-6 border border-white/10 mb-8">
				<form method="GET" action="" class="flex flex-col md:flex-row items-center justify-between gap-6">
					<input type="hidden" name="page" value="aws-sbg-dashboard">
					
					<!-- Search -->
					<div class="flex items-center gap-3 w-full md:w-auto">
						<div class="relative w-full md:w-80">
							<div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
								<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
							</div>
							<input type="text" name="search_reg" value="<?php echo esc_attr( $search_reg ); ?>" placeholder="Search by Reg # (e.g. 1001)" class="!bg-[#151515] !border-white/20 !text-white text-sm rounded-lg focus:!ring-brand focus:!border-brand block w-full !pl-10 !py-2.5 transition-colors placeholder:text-gray-500 shadow-inner">
						</div>
						<button type="submit" class="px-5 py-2.5 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-[#923deb] transition-colors shadow-lg">Search</button>
						<?php if ( $search_reg ) : ?>
							<a href="?page=aws-sbg-dashboard" class="px-4 py-2.5 bg-white/10 text-white text-sm font-semibold rounded-lg hover:bg-white/20 transition-colors">Clear</a>
						<?php endif; ?>
					</div>

					<!-- Event Filter -->
					<div class="flex items-center gap-3 w-full md:w-auto">
						<span class="font-medium text-gray-300 whitespace-nowrap">Filter Event:</span>
						<select name="filter_event" onchange="this.form.submit()" class="bg-black/50 border border-white/20 text-white text-sm rounded-lg focus:ring-brand focus:border-brand block w-full md:w-64 p-2.5 cursor-pointer hover:border-brand/50 transition-colors">
							<option value="0">All Events</option>
							<?php foreach ( $events as $event ) : ?>
								<option value="<?php echo esc_attr( $event->ID ); ?>" <?php selected( $selected_event, $event->ID ); ?>>
									<?php echo esc_html( $event->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</form>
			</div>

			<!-- Data Table -->
			<div class="bg-card rounded-xl border border-white/10 overflow-hidden">
				<table class="w-full text-sm text-left text-gray-400">
					<thead class="text-xs text-gray-300 uppercase bg-white/5 border-b border-white/10">
						<tr>
							<th scope="col" class="px-6 py-4 font-semibold">Reg #</th>
							<th scope="col" class="px-6 py-4 font-semibold">Attendee Name</th>
							<th scope="col" class="px-6 py-4 font-semibold">Event</th>
							<th scope="col" class="px-6 py-4 font-semibold">Email</th>
							<th scope="col" class="px-6 py-4 font-semibold">WhatsApp</th>
							<th scope="col" class="px-6 py-4 font-semibold">University/Org</th>
							<th scope="col" class="px-6 py-4 font-semibold">Ticket</th>
							<th scope="col" class="px-6 py-4 font-semibold text-center">Payment Proof</th>
							<th scope="col" class="px-6 py-4 font-semibold">Registered Date</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $registrations->have_posts() ) : ?>
							<?php while ( $registrations->have_posts() ) : $registrations->the_post(); 
								$reg_id_meta = get_post_meta( get_the_ID(), 'registration_id', true );
								$reg_event_id = get_post_meta( get_the_ID(), 'event_id', true );
								$reg_event_title = $reg_event_id ? get_the_title( $reg_event_id ) : 'N/A';
								$email = get_post_meta( get_the_ID(), 'email', true );
								$whatsapp = get_post_meta( get_the_ID(), 'whatsapp_number', true );
								$uni = get_post_meta( get_the_ID(), 'university_organization', true );
								$ticket = get_post_meta( get_the_ID(), 'ticket_tier', true );
								$ticket_label = ( $ticket === 'pro' ) ? 'PRO' : 'FREE';
								$ticket_class = ( $ticket === 'pro' ) ? 'bg-orange-500/20 text-orange-400 border-orange-500/30' : 'bg-supporting-green/20 text-supporting-green border-supporting-green/30';
								
								$payment_proof_id = get_post_meta( get_the_ID(), 'payment_screenshot', true );
								$payment_proof_url = $payment_proof_id ? wp_get_attachment_url( $payment_proof_id ) : false;

								// Extract full name from post title (Format: Name (RegID) - Event)
								$raw_title = get_the_title();
								$full_name = $raw_title;
								if ( $reg_event_title && $reg_event_title !== 'N/A' ) {
									// Strip the " - Event Title" from the end
									$full_name = preg_replace('/( - | – )' . preg_quote($reg_event_title, '/') . '$/', '', $full_name);
								}
								// Strip the " (1001)" from the end
								$full_name = preg_replace('/ \([0-9]+\)$/', '', $full_name);
							?>
								<tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
									<td class="px-6 py-4 font-bold text-brand whitespace-nowrap">
										#<?php echo esc_html( $reg_id_meta ? $reg_id_meta : 'N/A' ); ?>
									</td>
									<td class="px-6 py-4 font-medium text-white whitespace-nowrap">
										<?php echo esc_html( $full_name ); ?>
									</td>
									<td class="px-6 py-4">
										<span class="px-3 py-1 text-xs font-medium bg-brand/20 text-brand rounded-full border border-brand/30">
											<?php echo esc_html( $reg_event_title ); ?>
										</span>
									</td>
									<td class="px-6 py-4">
										<a href="mailto:<?php echo esc_attr( $email ); ?>" class="hover:text-brand transition-colors"><?php echo esc_html( $email ); ?></a>
									</td>
									<td class="px-6 py-4">
										<?php echo esc_html( $whatsapp ); ?>
									</td>
									<td class="px-6 py-4">
										<?php echo esc_html( $uni ); ?>
									</td>
									<td class="px-6 py-4">
										<span class="px-2 py-1 text-xs font-bold uppercase tracking-wider rounded border <?php echo esc_attr( $ticket_class ); ?>">
											<?php echo esc_html( $ticket_label ); ?>
										</span>
									</td>
									<td class="px-6 py-4 text-center">
										<?php if ( $ticket === 'pro' ) : ?>
											<?php if ( $payment_proof_url ) : ?>
												<a href="<?php echo esc_url( $payment_proof_url ); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#ad5cff]/10 text-[#ad5cff] hover:bg-[#ad5cff]/20 rounded-lg text-xs font-semibold transition-colors border border-[#ad5cff]/20">
													<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
													View Receipt
												</a>
											<?php else : ?>
												<span class="text-xs text-red-400 font-medium bg-red-400/10 px-2 py-1 rounded border border-red-400/20">Missing</span>
											<?php endif; ?>
										<?php else : ?>
											<span class="text-xs text-gray-600 font-medium">-</span>
										<?php endif; ?>
									</td>
									<td class="px-6 py-4 text-gray-500">
										<?php echo get_the_date( 'M j, Y g:i a' ); ?>
									</td>
								</tr>
							<?php endwhile; wp_reset_postdata(); ?>
						<?php else : ?>
							<tr>
								<td colspan="9" class="px-6 py-8 text-center text-gray-500">
									No registrations found.
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
				
				<!-- Pagination Controls -->
				<?php
				$total_pages = $registrations->max_num_pages;
				if ( $total_pages > 1 ) :
				?>
					<div class="px-6 py-4 border-t border-white/10 flex justify-between items-center bg-white/5">
						<span class="text-sm text-gray-400 font-medium">Page <?php echo esc_html( $paged ); ?> of <?php echo esc_html( $total_pages ); ?></span>
						<div class="flex gap-2">
							<?php if ( $paged > 1 ) : ?>
								<a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="px-4 py-2 bg-white/10 text-white text-sm font-semibold rounded-lg hover:bg-white/20 transition-colors border border-white/10 shadow-sm">Previous</a>
							<?php endif; ?>
							<?php if ( $paged < $total_pages ) : ?>
								<a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="px-4 py-2 bg-white/10 text-white text-sm font-semibold rounded-lg hover:bg-white/20 transition-colors border border-white/10 shadow-sm">Next</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
	<?php
}
