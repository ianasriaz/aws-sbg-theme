<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-cloud-dark text-cloud-light' ); ?>>
	<?php wp_body_open(); ?>

	<!-- Navigation Header -->
	<header class="sticky top-0 z-50 bg-gradient-to-b from-aws-blue to-cloud-dark border-b border-aws-gold/20 shadow-lg">
		<nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
			mobileMenuOpen: false,
			toggleMobileMenu() {
				this.mobileMenuOpen = !this.mobileMenuOpen;
			}
		}">
			<div class="flex justify-between items-center h-16">
				<!-- Logo -->
				<div class="flex-shrink-0">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center">
						<span class="text-2xl font-bold text-aws-gold">AWS</span>
						<span class="ml-2 text-lg font-semibold text-cloud-light">SBG</span>
					</a>
				</div>

				<!-- Desktop Navigation Menu -->
				<div class="hidden md:flex items-center space-x-1">
					<?php
					$menu_items = array(
						array( 'title' => 'Home', 'url' => home_url( '/' ) ),
						array( 'title' => 'AirCert BootCamp', 'url' => home_url( '/aircert/' ) ),
						array( 'title' => 'Contact Us', 'url' => home_url( '/contact/' ) ),
					);

					foreach ( $menu_items as $item ) {
						$current = ( $_SERVER['REQUEST_URI'] === $item['url'] || ( isset( $_GET['page_id'] ) && get_permalink( $_GET['page_id'] ) === $item['url'] ) ) ? ' text-aws-gold border-b-2 border-aws-gold' : ' text-cloud-light hover:text-aws-gold';
						echo '<a href="' . esc_url( $item['url'] ) . '" class="px-4 py-2 rounded-md text-sm font-medium transition-colors' . esc_attr( $current ) . '">' . esc_html( $item['title'] ) . '</a>';
					}
					?>
				</div>

				<!-- Mobile Menu Toggle Button -->
				<div class="md:hidden">
					<button 
						@click="toggleMobileMenu()" 
						:aria-expanded="mobileMenuOpen"
						class="inline-flex items-center justify-center p-2 rounded-md text-aws-gold hover:bg-aws-blue focus:outline-none focus:ring-2 focus:ring-inset focus:ring-aws-gold transition-all"
						aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'aws-sbg-theme' ); ?>"
					>
						<svg 
							:class="{ 'hidden': mobileMenuOpen, 'block': !mobileMenuOpen }" 
							class="h-6 w-6" 
							fill="none" 
							stroke="currentColor" 
							viewBox="0 0 24 24"
						>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
						</svg>
						<svg 
							:class="{ 'block': mobileMenuOpen, 'hidden': !mobileMenuOpen }" 
							class="h-6 w-6" 
							fill="none" 
							stroke="currentColor" 
							viewBox="0 0 24 24"
						>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
						</svg>
					</button>
				</div>
			</div>

			<!-- Mobile Navigation Menu -->
			<div 
				x-show="mobileMenuOpen" 
				x-transition:enter="transition ease-out duration-200"
				x-transition:enter-start="opacity-0 -translate-y-1"
				x-transition:enter-end="opacity-100 translate-y-0"
				x-transition:leave="transition ease-in duration-150"
				x-transition:leave-start="opacity-100 translate-y-0"
				x-transition:leave-end="opacity-0 -translate-y-1"
				class="md:hidden bg-aws-blue border-t border-aws-gold/20"
			>
				<div class="px-2 pt-2 pb-3 space-y-1">
					<?php
					foreach ( $menu_items as $item ) {
						$current = ( $_SERVER['REQUEST_URI'] === $item['url'] ) ? ' bg-aws-blue/50 text-aws-gold' : ' text-cloud-light hover:bg-aws-blue/30 hover:text-aws-gold';
						echo '<a href="' . esc_url( $item['url'] ) . '" class="block px-3 py-2 rounded-md text-base font-medium transition-colors' . esc_attr( $current ) . '" @click="mobileMenuOpen = false">' . esc_html( $item['title'] ) . '</a>';
					}
					?>
				</div>
			</div>
		</nav>
	</header>
