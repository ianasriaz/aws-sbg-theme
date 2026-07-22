<?php
get_header();
?>

<!-- Main Content -->
<main class="min-h-screen bg-cloud-dark">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
		<?php if ( have_posts() ) : ?>
			<!-- Posts Loop -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="group bg-aws-blue/50 border border-aws-gold/30 rounded-lg overflow-hidden hover:border-aws-gold/60 transition-all duration-300 shadow-lg hover:shadow-glow">
						<!-- Featured Image -->
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="relative overflow-hidden h-48">
								<?php
								the_post_thumbnail(
									'medium',
									array(
										'class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-300',
									)
								);
								?>
							</div>
						<?php endif; ?>

						<!-- Content -->
						<div class="p-6">
							<!-- Meta Information -->
							<div class="flex items-center gap-4 mb-4 text-sm text-cloud-light/60">
								<span><?php echo esc_html( get_the_date() ); ?></span>
							</div>

							<!-- Title -->
							<h2 class="text-xl font-bold text-aws-gold mb-3 group-hover:text-white transition-colors">
								<a href="<?php the_permalink(); ?>" class="hover:underline">
									<?php the_title(); ?>
								</a>
							</h2>

							<!-- Excerpt -->
							<div class="text-cloud-light/80 mb-6 line-clamp-3">
								<?php the_excerpt(); ?>
							</div>

							<!-- Read More Link -->
							<a 
								href="<?php the_permalink(); ?>" 
								class="inline-flex items-center gap-2 text-aws-gold hover:text-aws-gold/80 font-semibold transition-colors"
							>
								<?php esc_html_e( 'Read More', 'aws-sbg-theme' ); ?>
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
								</svg>
							</a>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<!-- Pagination -->
			<div class="mt-12 flex justify-center">
				<nav class="flex gap-2">
					<?php
					$pagination = paginate_links(
						array(
											'type' => 'array',
											'prev_text' => '← ' . esc_html__( 'Previous', 'aws-sbg-theme' ),
											'next_text' => esc_html__( 'Next', 'aws-sbg-theme' ) . ' →',
										)
									);

					if ( ! empty( $pagination ) ) {
						foreach ( $pagination as $link ) {
							echo '<div class="px-3 py-2 border border-aws-gold/30 rounded-lg hover:bg-aws-blue/50 transition-colors">' . wp_kses_post( $link ) . '</div>';
						}
					}
					?>
				</nav>
			</div>

		<?php else : ?>
			<!-- No Posts Found -->
			<div class="text-center py-20">
				<div class="text-6xl mb-6">📭</div>
				<h2 class="text-3xl font-bold text-aws-gold mb-4">
					<?php esc_html_e( 'No Posts Found', 'aws-sbg-theme' ); ?>
				</h2>
				<p class="text-cloud-light/80 mb-8 text-lg">
					<?php esc_html_e( 'It seems we couldn\'t find what you\'re looking for. Check back soon for updates!', 'aws-sbg-theme' ); ?>
				</p>
				<a 
					href="<?php echo esc_url( home_url( '/' ) ); ?>" 
					class="inline-block px-8 py-3 bg-aws-gold text-aws-blue font-bold rounded-lg hover:bg-aws-gold/90 transition-colors"
				>
					<?php esc_html_e( 'Back to Home', 'aws-sbg-theme' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
