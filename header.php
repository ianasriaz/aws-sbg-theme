<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-dark-bg text-white' ); ?>>
	<?php wp_body_open(); ?>

	<!-- Clean Mobile-First Announcement Bar for AWS Builder Center -->
	<aside aria-label="AWS Builder Center Announcement" class="bg-[#141226] text-gray-200 py-2.5 px-4 font-mono text-[11px] sm:text-xs border-b border-[#9353ff]/30 relative z-40">
		<div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-3 text-center">
			<span class="text-gray-300 leading-snug">
				Get free cloud labs, AWS credits &amp; digital badges on the official <strong>AWS Builder Center</strong>.
			</span>
			<a href="https://bit.ly/4w5nmGl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[#b57aff] hover:text-white font-bold underline shrink-0 transition-colors">
				<span>Join Free</span>
				<span>↗</span>
			</a>
		</div>
	</aside>

	<!-- Navigation Header -->
	<header class="sticky top-0 z-40 bg-[#0a0d14]/95 border-b border-[#1d2636] backdrop-blur-md">
		<nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-center h-20 gap-2 md:gap-4">
				
				<!-- Left: Desktop Nav & Mobile Toggle -->
				<div class="flex-1 lg:flex-1 flex justify-start items-center">
					
					<!-- Mobile Menu Toggle Button -->
					<button 
						id="mobile-menu-toggle"
						type="button"
						class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-xl text-gray-200 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 focus:outline-none transition-all"
						aria-controls="mobile-navigation-menu"
						aria-expanded="false"
						aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'aws-sbg-theme' ); ?>"
					>
						<!-- Hamburger Icon -->
						<svg class="h-6 w-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
						</svg>
					</button>

					<!-- Desktop Navigation Menu -->
					<div class="hidden lg:flex items-center space-x-1 font-mono text-xs tracking-wider">
						<?php
						$desktop_menu = array(
							array( 'title' => 'HOME', 'url' => home_url( '/' ) ),
							array( 'title' => 'EVENTS', 'url' => home_url( '/events/' ) ),
							array( 'title' => 'CONTACT US', 'url' => home_url( '/contact/' ) ),
						);

						$current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );

						foreach ( $desktop_menu as $item ) {
							$is_current = ( untrailingslashit( $current_url ) === untrailingslashit( $item['url'] ) );
							$current_class = $is_current 
								? 'text-[#9353ff] font-bold bg-[#9353ff]/10 border border-[#9353ff]/30' 
								: 'text-gray-300 hover:text-white hover:bg-white/5 border border-transparent';
							echo '<a href="' . esc_url( $item['url'] ) . '" class="px-3.5 py-2 rounded-lg transition-all duration-150 ' . esc_attr( $current_class ) . '">' . esc_html( $item['title'] ) . '</a>';
						}
						?>
					</div>
				</div>

				<!-- Center: Logo -->
				<div class="flex-1 md:flex-shrink-0 flex justify-center items-center min-w-0">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center justify-center hover:opacity-90 transition-opacity duration-200">
						<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/SBG-Logo-White-Transparent.png' ) ); ?>" alt="AWS SBG Air University" class="h-9 sm:h-10 md:h-12 w-auto max-w-full object-contain">
					</a>
				</div>

				<!-- Right: CTAs (Builder Center + Meetup) -->
				<div class="flex-1 lg:flex-1 flex justify-end items-center gap-2 sm:gap-3">
					<a href="https://bit.ly/4w5nmGl" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 bg-white/[0.06] hover:bg-white/[0.12] border border-[#9353ff]/50 text-white font-mono font-bold text-xs rounded-lg transition-all duration-150 whitespace-nowrap shadow-sm">
						<span class="text-[#9353ff]">⚡</span>
						<span>Builder Center</span>
						<span class="text-[10px] text-gray-400">↗</span>
					</a>
					<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="px-3.5 py-2 md:px-5 md:py-2.5 bg-[#9353ff] hover:bg-[#7928ca] text-white font-mono font-bold text-xs sm:text-sm rounded-lg transition-all duration-150 shadow-md shadow-[#9353ff]/20 whitespace-nowrap flex items-center gap-2">
						<span>Join<span class="hidden sm:inline"> Community</span></span>
						<span class="hidden sm:inline">→</span>
					</a>
				</div>

			</div>
		</nav>
	</header>

	<!-- Full-Screen Mobile Drawer Overlay (Strictly z-[99999] above all hero/page elements) -->
	<div 
		id="mobile-navigation-menu" 
		class="hidden fixed inset-0 z-[99999] bg-[#0a0d14] flex flex-col justify-between overflow-y-auto"
	>
		<!-- Top Bar inside Drawer -->
		<div class="flex items-center justify-between px-6 h-20 border-b border-[#1d2636] bg-[#0a0d14] shrink-0">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center">
				<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/SBG-Logo-White-Transparent.png' ) ); ?>" alt="AWS SBG" class="h-8 w-auto object-contain">
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
			
			<!-- Featured VIP Builder Center Banner -->
			<a href="https://bit.ly/4w5nmGl" target="_blank" rel="noopener noreferrer" class="block p-4 rounded-2xl bg-gradient-to-r from-[#9353ff]/20 via-[#7928ca]/20 to-[#9353ff]/10 border border-[#9353ff]/50 text-white font-mono shadow-lg">
				<div class="flex items-center justify-between">
					<div class="flex items-center gap-2">
						<span class="text-base">🚀</span>
						<span class="font-bold text-xs text-[#b57aff]">AWS BUILDER CENTER</span>
					</div>
					<span class="text-xs text-white font-bold bg-[#9353ff] px-2 py-0.5 rounded">FREE ↗</span>
				</div>
				<p class="text-[11px] text-gray-300 mt-1.5 leading-relaxed">
					Get free cloud labs, AWS credits &amp; digital badges under our chapter.
				</p>
			</a>

			<!-- Navigation Links List (HOME, EVENTS, CONTACT US) -->
			<div class="space-y-2 font-mono text-sm">
				<?php
				$mobile_menu = array(
					array( 'title' => 'Home', 'subtitle' => 'Main community portal', 'url' => home_url( '/' ), 'icon' => '🏠' ),
					array( 'title' => 'Events', 'subtitle' => 'Bootcamps & sessions', 'url' => home_url( '/events/' ), 'icon' => '📅' ),
					array( 'title' => 'Contact Us', 'subtitle' => 'Get in touch with leadership', 'url' => home_url( '/contact/' ), 'icon' => '💬' ),
				);

				foreach ( $mobile_menu as $item ) {
					$is_current = ( untrailingslashit( $current_url ) === untrailingslashit( $item['url'] ) );
					$active_class = $is_current 
						? 'bg-[#9353ff]/15 text-[#9353ff] border-[#9353ff]/40 font-bold' 
						: 'bg-[#0f141f] text-gray-200 hover:text-white hover:bg-[#151c2b] border-[#1d2636]';
					
					echo '<a href="' . esc_url( $item['url'] ) . '" class="mobile-nav-link flex items-center justify-between p-3.5 rounded-xl border transition-all ' . esc_attr( $active_class ) . '">';
					echo '  <div class="flex items-center gap-3.5">';
					echo '    <span class="text-lg">' . esc_html( $item['icon'] ) . '</span>';
					echo '    <div>';
					echo '      <div class="font-bold text-sm leading-snug">' . esc_html( $item['title'] ) . '</div>';
					echo '      <div class="text-[11px] text-gray-400 font-normal">' . esc_html( $item['subtitle'] ) . '</div>';
					echo '    </div>';
					echo '  </div>';
					echo '  <span class="text-gray-400 font-bold text-sm">→</span>';
					echo '</a>';
				}
				?>
			</div>

			<!-- Mobile Direct Action Buttons -->
			<div class="pt-2 grid grid-cols-2 gap-3 border-t border-[#1d2636]">
				<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2.5 p-3.5 rounded-xl bg-[#9353ff] hover:bg-[#7928ca] text-white font-mono font-bold text-xs text-center shadow-lg shadow-[#9353ff]/20 transition-all">
					<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/meetup-icon.png' ) ); ?>" alt="Meetup" class="w-4 h-4 object-contain">
					<span>Meetup</span>
				</a>
				<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2.5 p-3.5 rounded-xl bg-[#0f141f] hover:bg-[#151c2b] border border-[#1d2636] hover:border-emerald-500/40 text-emerald-400 font-mono font-bold text-xs text-center transition-all">
					<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/whatsapp-color-icon.png' ) ); ?>" alt="WhatsApp" class="w-4 h-4 object-contain">
					<span>WhatsApp</span>
				</a>
			</div>

		</div>

		<!-- Bottom Drawer Footer -->
		<div class="py-4 text-center text-[11px] font-mono text-gray-400 border-t border-[#1d2636] bg-[#0a0d14] shrink-0">
			AWS Student Builder Group • Air University Islamabad
		</div>
	</div>

	<!-- Vanilla JavaScript for Resilient Mobile Fullscreen Drawer -->
	<script>
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

