<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-dark-bg text-white' ); ?>>
	<?php wp_body_open(); ?>

	<!-- Clean Announcement Bar: AWS Student Rewards -->
	<aside aria-label="AWS Student Rewards Announcement" class="bg-[#000000] text-gray-200 py-2 sm:py-2.5 px-4 text-[11px] sm:text-xs border-b border-[#9353ff]/20 relative z-40">
		<div class="max-w-7xl mx-auto flex items-center justify-center text-center">
			<a href="https://builder.aws.com/start?trk=8e356433-7e2c-4ee9-8c3a-5bccc682a4f5&amp;sc_channel=el" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center justify-center gap-2 text-gray-300 hover:text-white font-bold tracking-wider transition-colors">
				<span>CLAIM $579 AWS STUDENT REWARDS</span>
				<span class="text-[#9353ff] group-hover:text-white transition-all group-hover:translate-x-1">→</span>
			</a>
		</div>
	</aside>
	<!-- Glassmorphic Header Styling -->
	<style id="site-glass-header-css">
		/* ==========================================================================
		   FULL-WIDTH FROSTED GLASSMORPHIC HEADER
		   Translucent refraction layer with dynamic scroll reactivity & edge sheen
		   ========================================================================== */
		.site-glass-header {
			position: sticky;
			top: 0;
			z-index: 50;
			background-color: #000000 !important;
			border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
			box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
			transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
		}

		/* Luminous bottom refraction hairline */
		.site-glass-header::after {
			content: '';
			position: absolute;
			bottom: -1px;
			left: 0;
			right: 0;
			height: 1px;
			background: linear-gradient(90deg, transparent 5%, rgba(147, 83, 255, 0.5) 50%, transparent 95%);
			opacity: 0.7;
			pointer-events: none;
			transition: opacity 0.3s ease;
		}

		/* Scrolled state: Clean solid black with purple edge glow */
		.site-glass-header.is-scrolled {
			background-color: #000000 !important;
			border-bottom-color: rgba(147, 83, 255, 0.3) !important;
			box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 1px 0 0 rgba(147, 83, 255, 0.25) !important;
		}

		.site-glass-header.is-scrolled::after {
			opacity: 1;
			background: linear-gradient(90deg, transparent 0%, rgba(147, 83, 255, 0.75) 50%, transparent 100%);
		}

		/* Mobile Menu Drawer Glass */
		#mobile-navigation-menu {
			background-color: rgba(10, 13, 20, 0.94) !important;
			backdrop-filter: blur(28px) saturate(200%) !important;
			-webkit-backdrop-filter: blur(28px) saturate(200%) !important;
		}
	</style>

	<!-- Navigation Header (Full-Width Frosted Glassmorphism) -->
	<header id="site-header" class="site-glass-header sticky top-0 z-50">
		<nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-center h-20 gap-2 md:gap-4">
				
				<!-- Left: Desktop Nav & Mobile Toggle -->
				<div class="flex-1 lg:flex-1 flex justify-start items-center">
					
					<!-- Mobile Menu Toggle Button -->
					<button 
						id="mobile-menu-toggle" 
						type="button" 
						class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-xl text-gray-200 hover:text-white bg-white/[0.05] hover:bg-white/[0.10] border border-white/10 focus:outline-none transition-all shadow-sm"
						aria-controls="mobile-navigation-menu" 
						aria-expanded="false" 
						aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'aws-sbg-theme' ); ?>"
					>
						<svg class="h-6 w-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
						</svg>
					</button>

					<!-- Desktop Navigation Menu -->
					<div class="hidden lg:flex items-center space-x-1 font-semibold text-xs tracking-wider">
						<?php
						$desktop_menu = array(
							array( 'title' => 'HOME', 'url' => home_url( '/' ) ),
							array( 'title' => 'COMMUNITY', 'url' => home_url( '/community/' ) ),
							array( 'title' => 'EVENTS', 'url' => home_url( '/events/' ) ),
							array( 'title' => 'CONTACT US', 'url' => home_url( '/contact/' ) ),
						);

						$current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );

						foreach ( $desktop_menu as $item ) {
							echo '<a href="' . esc_url( $item['url'] ) . '" class="px-3.5 py-2 text-gray-300 hover:text-white transition-colors whitespace-nowrap">';
							echo esc_html( $item['title'] );
							echo '</a>';
						}
						?>
					</div>
				</div>

				<!-- Center: Logo -->
				<div class="flex-1 md:flex-shrink-0 flex justify-center items-center min-w-0">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center justify-center hover:opacity-90 transition-opacity" aria-label="AWS SBG Home">
						<img id="header-logo-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/SBG-Logo-White.png' ) ); ?>" alt="AWS SBG Air University" class="h-9 sm:h-10 md:h-12 w-auto max-w-full object-contain">
					</a>
				</div>

				<!-- Right: CTA (Meetup) -->
				<div class="flex-1 lg:flex-1 flex justify-end items-center">
					<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="px-4 py-2 sm:px-5 sm:py-2.5 bg-[#9353ff] hover:bg-[#7928ca] text-white font-bold text-xs sm:text-sm rounded-xl transition-all duration-200 shadow-md shadow-[#9353ff]/30 hover:shadow-[#9353ff]/50 whitespace-nowrap flex items-center gap-2">
						<span>Community</span>
						<span>→</span>
					</a>
				</div>

			</div>
		</nav>
	</header>

	<!-- Full-Screen Mobile Drawer Overlay (Strictly z-[99999] above all hero/page elements) -->
	<div 
		id="mobile-navigation-menu" 
		class="hidden fixed inset-0 z-[99999] bg-[#0a0d14] flex flex-col overflow-y-auto"
	>
		<!-- Top Bar inside Drawer -->
		<div class="flex items-center justify-between px-6 h-20 border-b border-[#1d2636] bg-[#0a0d14] shrink-0">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/SBG-Logo-White.png' ) ); ?>" alt="AWS SBG" class="h-8 w-auto object-contain">
			</a>
			<button 
				id="mobile-menu-close" 
				type="button" 
				class="inline-flex items-center justify-center p-2.5 rounded-xl text-gray-300 hover:text-white bg-white/[0.06] hover:bg-white/[0.12] border border-white/10 focus:outline-none transition-all"
				aria-label="Close menu"
			>
				<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
				</svg>
			</button>
		</div>

		<!-- Scrollable Menu Content -->
		<div class="flex-1 p-5 sm:p-6 space-y-5 max-w-lg mx-auto w-full">
			
			<!-- Clean Navigation Links -->
			<div class="space-y-2 font-mono text-xs tracking-wider">
				<?php
				$mobile_menu = array(
					array( 'title' => 'HOME', 'url' => home_url( '/' ) ),
					array( 'title' => 'COMMUNITY', 'url' => home_url( '/community/' ) ),
					array( 'title' => 'EVENTS', 'url' => home_url( '/events/' ) ),
					array( 'title' => 'CONTACT US', 'url' => home_url( '/contact/' ) ),
				);

				foreach ( $mobile_menu as $item ) {
					echo '<a href="' . esc_url( $item['url'] ) . '" class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-white/[0.06] transition-colors">';
					echo esc_html( $item['title'] );
					echo '</a>';
				}
				?>
			</div>

		</div>
	</div>

	<!-- Dynamic Scroll & Mobile Drawer JavaScript -->
	<script>
		// Immediate check for glassmorphic header scroll state to prevent initial flicker
		(function() {
			var siteHeader = document.getElementById('site-header');
			if (!siteHeader) return;

			var handleScroll = function() {
				if (window.scrollY > 15) {
					siteHeader.classList.add('is-scrolled');
				} else {
					siteHeader.classList.remove('is-scrolled');
				}
			};

			window.addEventListener('scroll', handleScroll, { passive: true });
			handleScroll();
		})();

		document.addEventListener('DOMContentLoaded', function() {
			var toggleBtn = document.getElementById('mobile-menu-toggle');
			var closeBtn = document.getElementById('mobile-menu-close');
			var mobileMenu = document.getElementById('mobile-navigation-menu');

			if (!mobileMenu) return;

			function openMenu() {
				mobileMenu.classList.remove('hidden');
				document.body.style.overflow = 'hidden';
				if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
			}

			function closeMenu() {
				mobileMenu.classList.add('hidden');
				document.body.style.overflow = '';
				if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
			}

			if (toggleBtn) {
				toggleBtn.addEventListener('click', function(e) {
					e.stopPropagation();
					openMenu();
				});
			}

			if (closeBtn) {
				closeBtn.addEventListener('click', function(e) {
					e.stopPropagation();
					closeMenu();
				});
			}

			// Close on link click
			var navLinks = mobileMenu.querySelectorAll('.mobile-nav-link');
			navLinks.forEach(function(link) {
				link.addEventListener('click', function() {
					closeMenu();
				});
			});

			// Close on ESC key
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
					closeMenu();
					if (toggleBtn) toggleBtn.focus();
				}
			});
		});
	</script>

