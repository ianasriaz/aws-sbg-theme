<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-dark-bg text-white' ); ?>>
	<?php wp_body_open(); ?>

	<!-- Navigation Header -->
	<header class="sticky top-0 z-50 bg-gradient-to-r from-[#0a0a0a] via-[#1a0a2e] to-[#0a0a0a] border-b border-brand/20 shadow-2xl backdrop-blur-sm">
		<nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
			mobileMenuOpen: false,
			toggleMobileMenu() {
				this.mobileMenuOpen = !this.mobileMenuOpen;
			}
		}">
			<div class="flex justify-between items-center h-20">
				<!-- Logo with Brand -->
				<div class="flex-shrink-0 group">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center space-x-3 hover:opacity-90 transition-opacity duration-300">
						<!-- Logo Icon -->
						<div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-brand to-supporting-green rounded-lg shadow-lg group-hover:shadow-brand/50 transition-all">
							<svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
							</svg>
						</div>
						<!-- Text Logo -->
						<div class="hidden sm:block">
							<div class="text-xl font-bold bg-gradient-to-r from-brand to-supporting-green bg-clip-text text-transparent">AWS SBG</div>
							<div class="text-xs text-supporting-green/80 font-medium">Student Builder</div>
						</div>
					</a>
				</div>

				<!-- Desktop Navigation Menu -->
				<div class="hidden md:flex items-center space-x-2 lg:space-x-4">
					<?php
					$menu_items = array(
						array( 'title' => 'Home', 'url' => home_url( '/' ), 'icon' => '🏠' ),
						array( 'title' => 'AirCert BootCamp', 'url' => home_url( '/aircert/' ), 'icon' => '🎓' ),
						array( 'title' => 'Contact', 'url' => home_url( '/contact/' ), 'icon' => '✉️' ),
					);

					foreach ( $menu_items as $item ) {
						$is_current = ( $_SERVER['REQUEST_URI'] === $item['url'] || ( isset( $_GET['page_id'] ) && get_permalink( $_GET['page_id'] ) === $item['url'] ) );
						$current_class = $is_current ? 'text-brand font-semibold border-b-2 border-brand shadow-lg shadow-brand/20' : 'text-gray-300 hover:text-brand';
						echo '<a href="' . esc_url( $item['url'] ) . '" class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 ' . esc_attr( $current_class ) . ' hover:bg-brand/10 hover:shadow-brand/20 hover:shadow-sm">' . esc_html( $item['title'] ) . '</a>';
					}
					?>
				</div>

				<!-- CTA and Mobile Menu Toggle -->
				<div class="flex items-center space-x-4">
					<!-- CTA Button (Desktop) -->
					<a href="<?php echo esc_url( home_url( '/aircert/' ) ); ?>" class="hidden lg:inline-flex px-6 py-2.5 bg-gradient-to-r from-brand to-supporting-green text-white font-semibold rounded-lg shadow-lg shadow-brand/30 hover:shadow-xl hover:shadow-brand/40 transition-all duration-300 hover:scale-105">
						<?php esc_html_e( 'Register Now', 'aws-sbg-theme' ); ?>
					</a>

					<!-- Mobile Menu Toggle Button -->
					<button 
						@click="toggleMobileMenu()" 
						:aria-expanded="mobileMenuOpen"
						class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-brand hover:bg-brand/10 focus:outline-none focus:ring-2 focus:ring-brand transition-all"
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
				x-transition:enter-start="opacity-0 -translate-y-2"
				x-transition:enter-end="opacity-100 translate-y-0"
				x-transition:leave="transition ease-in duration-150"
				x-transition:leave-start="opacity-100 translate-y-0"
				x-transition:leave-end="opacity-0 -translate-y-2"
				class="md:hidden bg-gradient-to-b from-[#1a0a2e] to-[#0a0a0a] border-t border-brand/20 py-4 px-2"
			>
				<div class="space-y-2">
					<?php
					foreach ( $menu_items as $item ) {
						$is_current = ( $_SERVER['REQUEST_URI'] === $item['url'] );
						$current_class = $is_current ? 'bg-brand/20 text-brand border-l-2 border-brand' : 'text-gray-300 hover:text-brand hover:bg-brand/10';
						echo '<a href="' . esc_url( $item['url'] ) . '" class="block px-4 py-3 rounded-lg text-base font-medium transition-all duration-300 ' . esc_attr( $current_class ) . '" @click="mobileMenuOpen = false">' . esc_html( $item['title'] ) . '</a>';
					}
					?>
					<a href="<?php echo esc_url( home_url( '/aircert/' ) ); ?>" class="block px-4 py-3 mt-4 bg-gradient-to-r from-brand to-supporting-green text-white font-semibold rounded-lg text-center transition-all hover:shadow-lg hover:shadow-brand/30" @click="mobileMenuOpen = false">
						<?php esc_html_e( 'Register Now', 'aws-sbg-theme' ); ?>
					</a>
				</div>
			</div>
		</nav>
	</header>
