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
	<header class="sticky top-0 z-50 bg-[#0a0a0a]/90 border-b border-white/10 shadow-none md:shadow-2xl backdrop-blur-md">
		<nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
			mobileMenuOpen: false,
			toggleMobileMenu() {
				this.mobileMenuOpen = !this.mobileMenuOpen;
			}
		}">
			<div class="flex justify-between items-center h-20 gap-2 md:gap-4">
				
				<!-- Left: Menu (Desktop) & Hamburger (Mobile) -->
				<div class="flex-none md:flex-1 flex justify-start items-center">
					<!-- Desktop Navigation Menu -->
					<div class="hidden lg:flex items-center space-x-1 xl:space-x-2">
						<?php
						$menu_items = array(
							array( 'title' => 'HOME', 'url' => home_url( '/' ) ),
							array( 'title' => 'EVENTS', 'url' => home_url( '/events/' ) ),
							array( 'title' => 'CONTACT US', 'url' => home_url( '/contact/' ) ),
						);

						foreach ( $menu_items as $item ) {
							$is_current = ( $_SERVER['REQUEST_URI'] === $item['url'] || ( isset( $_GET['page_id'] ) && get_permalink( $_GET['page_id'] ) === $item['url'] ) );
							$current_class = $is_current ? 'text-white font-semibold bg-white/5' : 'text-gray-400 hover:text-white hover:bg-white/5';
							echo '<a href="' . esc_url( $item['url'] ) . '" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 ' . esc_attr( $current_class ) . '">' . esc_html( $item['title'] ) . '</a>';
						}
						?>
					</div>

					<!-- Mobile Menu Toggle Button -->
					<button 
						@click="toggleMobileMenu()" 
						:aria-expanded="mobileMenuOpen"
						class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-white hover:bg-white/10 focus:outline-none transition-all"
						aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'aws-sbg-theme' ); ?>"
					>
						<svg 
							x-show="!mobileMenuOpen" 
							class="h-6 w-6 block" 
							fill="none" 
							stroke="currentColor" 
							viewBox="0 0 24 24"
						>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
						</svg>
						<svg 
							x-show="mobileMenuOpen"
							style="display: none;"
							class="h-6 w-6 hidden" 
							fill="none" 
							stroke="currentColor" 
							viewBox="0 0 24 24"
						>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
						</svg>
					</button>
				</div>

				<!-- Center: Logo -->
				<div class="flex-1 md:flex-shrink-0 flex justify-center items-center min-w-0">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center justify-center hover:opacity-90 transition-opacity duration-300">
						<img src="<?php echo esc_url( content_url( '/uploads/2026/07/AWS-SBG-AIR-UNIVERSITY-LOGO-2-scaled.png' ) ); ?>" alt="AWS SBG Air University" class="h-9 sm:h-10 md:h-12 w-auto max-w-full object-contain">
					</a>
				</div>

				<!-- Right: CTA -->
				<div class="flex-none md:flex-1 flex justify-end items-center">
					<a href="https://www.meetup.com/aws-cloud-club-at-air-univ-islamabad/" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 md:px-6 md:py-2.5 bg-white text-black font-bold rounded-lg hover:bg-gray-200 transition-colors shadow-lg hover:shadow-xl hover:-translate-y-0.5 transform duration-300 text-[12px] sm:text-sm md:text-base whitespace-nowrap">
						Join<span class="hidden sm:inline"> Community</span>
					</a>
				</div>
			</div>

			<!-- Mobile Navigation Menu -->
			<div 
				x-show="mobileMenuOpen" 
				style="display: none;"
				x-transition:enter="transition ease-out duration-200"
				x-transition:enter-start="opacity-0 -translate-y-2"
				x-transition:enter-end="opacity-100 translate-y-0"
				x-transition:leave="transition ease-in duration-150"
				x-transition:leave-start="opacity-100 translate-y-0"
				x-transition:leave-end="opacity-0 -translate-y-2"
				class="lg:hidden bg-[#0a0a0a] border-t border-white/10 py-4 px-2"
			>
				<div class="space-y-2">
					<?php
					foreach ( $menu_items as $item ) {
						$is_current = ( $_SERVER['REQUEST_URI'] === $item['url'] );
						$current_class = $is_current ? 'bg-white/10 text-white border-l-2 border-white' : 'text-gray-400 hover:text-white hover:bg-white/5';
						echo '<a href="' . esc_url( $item['url'] ) . '" class="block px-4 py-3 rounded-lg text-base font-medium transition-all duration-300 ' . esc_attr( $current_class ) . '" @click="mobileMenuOpen = false">' . esc_html( $item['title'] ) . '</a>';
					}
					?>
				</div>
			</div>
		</nav>
	</header>
