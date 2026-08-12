<?php
/**
 * Template Name: AWS Events Portal
 * Description: Dedicated Hub Page listing all AWS SBG Activities, Bootcamps, Workshops, and Events.
 * Package: AWS_SBG_Theme
 */

get_header();

// Fetch all published AWS Events
$events_query = new WP_Query( array(
	'post_type'      => 'aws_event',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
?>

<div class="min-h-screen bg-[#07040d] text-white font-sans selection:bg-[#ad5cff] selection:text-white py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
	
	<!-- Glowing Background Accents -->
	<div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#ad5cff]/15 rounded-full blur-[140px] pointer-events-none"></div>
	<div class="absolute bottom-10 right-10 w-[400px] h-[400px] bg-[#00ef88]/10 rounded-full blur-[120px] pointer-events-none"></div>

	<div class="max-w-6xl mx-auto relative z-10 space-y-12">

		<!-- HERO HEADER -->
		<div class="text-center space-y-4">
			<div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1c1032] border border-[#ad5cff]/30 text-xs font-bold text-[#ad5cff] tracking-wide uppercase shadow-lg shadow-[#ad5cff]/10">
				<span class="w-2 h-2 rounded-full bg-[#00ef88] animate-pulse"></span>
				AWS Student Builder Group • Air University Islamabad
			</div>
			<h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white font-display uppercase">
				Activities & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ad5cff] to-[#00ef88]">Events Portal</span>
			</h1>
			<p class="text-gray-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
				Explore upcoming bootcamps, hands-on cloud workshops, certification preparation days, and virtual tech talks.
			</p>
		</div>

		<!-- EVENTS GRID -->
		<?php if ( $events_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
				<?php while ( $events_query->have_posts() ) : $events_query->the_post();
					$event_id = get_the_ID();
					$event_type = get_post_meta( $event_id, 'aws_event_type', true ) ?: 'paid';
					$event_status = get_post_meta( $event_id, 'aws_event_status', true ) ?: 'open';
					
					// Dynamic price fetching from actual event meta
					$price_eb  = get_post_meta( $event_id, 'aws_event_price_earlybird', true );
					$price_reg = get_post_meta( $event_id, 'aws_event_price_regular', true );
					$price_pro = get_post_meta( $event_id, 'aws_event_price_pro', true );
					$price_sup = get_post_meta( $event_id, 'aws_event_price_super', true );

					$display_price = '';
					if ( '' !== $price_eb && false !== $price_eb && ! empty( $price_eb ) ) {
						$display_price = $price_eb;
					} elseif ( '' !== $price_reg && false !== $price_reg && ! empty( $price_reg ) ) {
						$display_price = $price_reg;
					} elseif ( ! empty( $price_pro ) ) {
						$display_price = $price_pro;
					} elseif ( ! empty( $price_sup ) ) {
						$display_price = $price_sup;
					} else {
						$display_price = '0';
					}

					$raw_perks = ( $event_type === 'free' ) 
						? ( get_post_meta( $event_id, 'aws_event_perks_free_custom', true ) ?: "E-Certificate of Participation\nVirtual Masterclasses Access" )
						: ( get_post_meta( $event_id, 'aws_event_perks_earlybird_custom', true ) ?: "AWS Certification Mock Exam\nAWS Exam Voucher Eligibility\nNetworking Lunch & Refreshments" );
					
					$perks_list = array_slice( array_filter( array_map( 'trim', explode( "\n", $raw_perks ) ) ), 0, 4 );
					$permalink = get_permalink( $event_id );

					// Timer Metadata
					$enable_timer        = (bool) get_post_meta( $event_id, 'aws_event_enable_timer', true );
					$timer_end           = get_post_meta( $event_id, 'aws_event_timer_end', true );
					$timer_end_timestamp = ( $enable_timer && ! empty( $timer_end ) ) ? strtotime( $timer_end ) * 1000 : 0;
				?>
					<div class="bg-[#120a22]/90 border border-[#ad5cff]/30 rounded-3xl p-6 shadow-2xl backdrop-blur-xl flex flex-col justify-between hover:border-[#ad5cff]/70 transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden">
						
						<!-- Top Badges -->
						<div>
							<div class="flex items-center justify-between gap-2 mb-4">
								<span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider <?php echo $event_type === 'free' ? 'bg-[#00ef88]/15 text-[#00ef88] border border-[#00ef88]/30' : 'bg-[#ad5cff]/15 text-[#ad5cff] border border-[#ad5cff]/30'; ?>">
									<?php echo $event_type === 'free' ? '🟢 Free RSVP' : '🟣 Paid Event'; ?>
								</span>

								<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $event_status === 'closed' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'; ?>">
									<?php echo $event_status === 'closed' ? '🛑 Full' : '✅ Active'; ?>
								</span>
							</div>

							<!-- Title -->
							<h3 class="text-xl font-bold text-white group-hover:text-[#ad5cff] transition-colors leading-snug mb-2">
								<a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
							</h3>

							<!-- Mini Countdown Badge -->
							<?php if ( $enable_timer && $timer_end_timestamp > 0 ) : ?>
								<div class="mb-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#ad5cff]/15 border border-[#ad5cff]/40 text-[11px] font-bold text-[#ad5cff]"
								     x-data="{ distance: <?php echo floatval($timer_end_timestamp); ?> - new Date().getTime(), days: 0, hours: 0, mins: 0, secs: 0 }"
								     x-init="
								        const update = () => {
								            distance = <?php echo floatval($timer_end_timestamp); ?> - new Date().getTime();
								            if (distance > 0) {
								                days = Math.floor(distance / (1000 * 60 * 60 * 24));
								                hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
								                mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
								                secs = Math.floor((distance % (1000 * 60)) / 1000);
								            }
								        };
								        update();
								        setInterval(update, 1000);
								     ">
									<span>⏱️</span>
									<template x-if="distance > 0">
										<span>Ends in: <strong x-text="`${days}d ${hours}h ${mins}m ${secs}s`" class="text-[#ad5cff]"></strong></span>
									</template>
									<template x-if="distance <= 0">
										<span class="text-[#ad5cff]">Deadline Passed</span>
									</template>
								</div>
							<?php endif; ?>

							<!-- Excerpt / Description -->
							<p class="text-xs text-gray-400 leading-relaxed mb-6 line-clamp-3">
								<?php echo wp_strip_all_tags( get_the_excerpt() ? get_the_excerpt() : get_the_content() ); ?>
							</p>

							<!-- Perks Bullet Preview -->
							<div class="space-y-2 mb-6 pt-4 border-t border-white/10">
								<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Included Perks:</div>
								<ul class="space-y-1.5 text-xs text-gray-300">
									<?php foreach ( $perks_list as $perk ) : ?>
										<li class="flex items-center gap-2">
											<span class="text-[#00ef88] font-bold">✓</span>
											<span class="truncate"><?php echo esc_html( $perk ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>

						<!-- Footer & CTA -->
						<div class="pt-4 border-t border-white/10 flex items-center justify-center">
							<a href="<?php echo esc_url( $permalink ); ?>" class="w-full py-3 bg-gradient-to-r from-[#ad5cff] to-[#7000ff] hover:opacity-90 text-white font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 group-hover:shadow-lg group-hover:shadow-[#ad5cff]/20">
								<span>Register Now ➔</span>
							</a>
						</div>

					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<!-- Empty State -->
			<div class="bg-[#120a22]/90 border border-white/10 rounded-3xl p-12 text-center space-y-4 max-w-lg mx-auto">
				<div class="w-16 h-16 rounded-full bg-[#ad5cff]/10 text-[#ad5cff] flex items-center justify-center mx-auto text-2xl font-bold">📅</div>
				<h3 class="text-xl font-bold text-white uppercase">No Activities Published Yet</h3>
				<p class="text-xs text-gray-400 leading-relaxed">
					New activities and bootcamps are being prepared. Check back soon or follow our community channels!
				</p>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php
get_footer();
