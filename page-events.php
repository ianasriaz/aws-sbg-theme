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

<!-- Scoped styles to ensure zero conflict with theme style.css dark defaults -->
<style>
	.events-page-wrapper {
		background-color: #ffffff !important;
		color: #0f172a !important;
	}
	.events-page-wrapper h1,
	.events-page-wrapper h2,
	.events-page-wrapper h3,
	.events-page-wrapper h4 {
		color: #020617 !important;
	}
	.events-page-wrapper p {
		color: #334155 !important;
	}
	.events-page-wrapper .text-brand {
		color: #9353ff !important;
	}
	.events-page-wrapper .card-clean {
		background-color: #ffffff !important;
		border: 1px solid #e2e8f0 !important;
		transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
	}
	.events-page-wrapper .card-clean:hover {
		border-color: #9353ff !important;
		transform: translateY(-2px);
		box-shadow: 0 10px 25px -5px rgba(147, 83, 255, 0.1);
	}
	.events-page-wrapper .btn-effect {
		background-color: #9353ff !important;
		color: #ffffff !important;
		box-shadow: 0 4px 14px 0 rgba(147, 83, 255, 0.35);
		transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
		text-decoration: none !important;
	}
	.events-page-wrapper .btn-effect:hover {
		background-color: #7928ca !important;
		color: #ffffff !important;
		transform: translateY(-1px);
		box-shadow: 0 6px 20px 0 rgba(147, 83, 255, 0.45);
	}
</style>

<div class="events-page-wrapper bg-white text-slate-900 min-h-screen py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
	<div class="max-w-6xl mx-auto space-y-12">

		<!-- HERO HEADER -->
		<div class="text-center space-y-3">
			<h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-950 uppercase">
				Activities &amp; <span class="text-[#9353ff]">Events</span>
			</h1>
			<p class="text-slate-600 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
				Explore upcoming bootcamps, hands-on cloud workshops, certification preparation days, and tech sessions.
			</p>
		</div>

		<!-- EVENTS GRID -->
		<?php if ( $events_query->have_posts() ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
					$timer_end_timestamp = 0;
					if ( $enable_timer && ! empty( $timer_end ) ) {
						try {
							$timer_end_clean = str_replace( ' ', 'T', trim( $timer_end ) );
							$dt = new DateTime( $timer_end_clean, wp_timezone() );
							$timer_end_timestamp = $dt->getTimestamp() * 1000;
						} catch ( Exception $e ) {
							$timer_end_timestamp = strtotime( $timer_end ) * 1000;
						}
					}
				?>
					<div class="card-clean rounded-2xl p-6 flex flex-col justify-between group shadow-sm">
						
						<!-- Top Badges -->
						<div>
							<div class="flex items-center justify-between gap-2 mb-4">
								<span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-bold uppercase tracking-wider <?php echo $event_type === 'free' ? 'bg-slate-100 text-slate-700 border border-slate-200' : 'bg-[#9353ff]/10 text-[#9353ff] border border-[#9353ff]/30'; ?>">
									<?php echo $event_type === 'free' ? 'Free RSVP' : 'Paid Event'; ?>
								</span>

								<span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-bold uppercase tracking-wider <?php echo $event_status === 'closed' ? 'bg-slate-100 text-slate-500 border border-slate-200' : 'bg-[#9353ff]/10 text-[#9353ff] border border-[#9353ff]/30'; ?>">
									<?php echo $event_status === 'closed' ? 'Closed' : 'Open'; ?>
								</span>
							</div>

							<!-- Title -->
							<h2 class="text-lg sm:text-xl font-bold text-slate-950 group-hover:text-[#9353ff] transition-colors leading-snug mb-2">
								<a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
							</h2>

							<!-- Mini Countdown Badge -->
							<?php if ( $enable_timer && ( $timer_end_timestamp > 0 || ! empty( $timer_end ) ) ) : ?>
								<div class="mb-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#9353ff]/10 border border-[#9353ff]/30 text-[11px] font-bold text-[#9353ff]"
								     x-data="{ 
								        timerEndStr: '<?php echo esc_js( $timer_end ); ?>',
								        endTime: <?php echo floatval( $timer_end_timestamp ); ?>,
								        distance: 0, days: 0, hours: 0, mins: 0, secs: 0 
								     }"
								     x-init="
								        if (timerEndStr) {
								            let cleanStr = timerEndStr.trim().replace(' ', 'T');
								            if (cleanStr.length === 16) cleanStr += ':00';
								            let parsedDate = new Date(cleanStr);
								            if (parsedDate && !isNaN(parsedDate.getTime())) {
								                endTime = parsedDate.getTime();
								            }
								        }
								        const update = () => {
								            distance = endTime - new Date().getTime();
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
										<span>Ends in: <strong x-text="`${days}d ${hours}h ${mins}m ${secs}s`" class="text-[#9353ff]"></strong></span>
									</template>
									<template x-if="distance <= 0">
										<span class="text-rose-500 font-extrabold flex items-center gap-1">Registrations Closed</span>
									</template>

								</div>
							<?php endif; ?>

							<!-- Excerpt / Description -->
							<p class="text-xs text-slate-600 leading-relaxed mb-5 line-clamp-3">
								<?php echo wp_strip_all_tags( get_the_excerpt() ? get_the_excerpt() : get_the_content() ); ?>
							</p>

							<!-- Perks Bullet Preview -->
							<div class="space-y-2 mb-6 pt-4 border-t border-slate-100">
								<div class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">Included Perks:</div>
								<ul class="space-y-1.5 text-xs text-slate-600">
									<?php foreach ( $perks_list as $perk ) : ?>
										<li class="flex items-center gap-2">
											<span class="text-[#9353ff] font-bold">✓</span>
											<span class="truncate"><?php echo esc_html( $perk ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>

						<!-- Footer & CTA -->
						<div class="pt-4 border-t border-slate-100 flex items-center justify-center">
							<a href="<?php echo esc_url( $permalink ); ?>" class="btn-effect w-full py-3 rounded-xl font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-sm">
								<span>Register Now</span>
								<span>→</span>
							</a>
						</div>

					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<!-- Empty State -->
			<div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center space-y-4 max-w-lg mx-auto">
				<div class="w-12 h-12 rounded-xl bg-[#9353ff]/10 border border-[#9353ff]/20 text-[#9353ff] flex items-center justify-center mx-auto text-xl font-bold">📅</div>
				<h3 class="text-lg font-bold text-slate-950 uppercase tracking-tight">No Activities Published Yet</h3>
				<p class="text-xs text-slate-600 leading-relaxed">
					New activities and bootcamps are being scheduled. Check back soon or stay connected on our community channels!
				</p>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php
get_footer();
