<?php
/**
 * The template for displaying all single posts, pages, and custom post types.
 */
get_header();

// Detect if we should use light theme (Events or pages with the event form)
$post_content = get_post() ? get_post()->post_content : '';
$is_light_theme = is_singular( 'aws_event' ) || has_shortcode( $post_content, 'aws_event_form' );

$bg_class = $is_light_theme ? 'bg-gray-50' : 'bg-[#0a0a0a]';
$prose_class = $is_light_theme ? 'prose-gray text-gray-800' : 'prose-invert text-gray-300';
$heading_class = $is_light_theme ? 'text-gray-900' : 'text-transparent bg-clip-text bg-gradient-to-r from-brand to-supporting-green';
$date_class = $is_light_theme ? 'text-gray-500' : 'text-gray-400';
?>

<!-- Main Content -->
<main class="min-h-screen <?php echo esc_attr( $bg_class ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php $wrapper_class = has_shortcode( $post_content, 'aws_event_form' ) ? 'px-0 md:px-8 py-0 md:py-24' : 'px-4 sm:px-6 lg:px-8 py-16 md:py-24'; ?>
		<div class="max-w-4xl mx-auto <?php echo esc_attr( $wrapper_class ); ?>">
			
			<!-- Featured Image (if exists and not a page) -->
			<?php if ( has_post_thumbnail() && ! is_page() ) : ?>
				<div class="mb-10 rounded-2xl overflow-hidden shadow-2xl border <?php echo $is_light_theme ? 'border-gray-200' : 'border-white/10'; ?>">
					<?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-auto object-cover' ) ); ?>
				</div>
			<?php endif; ?>

			<!-- Header -->
			<?php if ( ! has_shortcode( $post_content, 'aws_event_form' ) ) : ?>
			<header class="mb-12 text-center">
				<h1 class="text-4xl md:text-5xl font-extrabold <?php echo esc_attr( $heading_class ); ?> mb-4">
					<?php the_title(); ?>
				</h1>
				<?php if ( ! is_page() ) : ?>
					<div class="text-sm font-medium <?php echo esc_attr( $date_class ); ?>">
						<?php echo get_the_date(); ?>
					</div>
				<?php endif; ?>
			</header>
			<?php endif; ?>

			<!-- Content -->
			<!-- Using Tailwind Typography (prose) for standard content formatting -->
			<div class="prose prose-lg max-w-none mx-auto <?php echo esc_attr( $prose_class ); ?>">
				<?php the_content(); ?>
			</div>
		</div>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
