<?php
/**
 * Front Page Template - AWS Student Builder Group at Air University
 *
 * Designed in strict alignment with official AWS SBG brand creative:
 * Deep navy-charcoal grid matrix, vibrant purple voxel blocks, pixel chip motifs,
 * and high-contrast developer-centric typography.
 */
get_header();
?>

<!-- Load Google Fonts & Tailwind -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700;800&family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
	tailwind.config = {
		theme: {
			extend: {
				colors: {
					brand: '#9353ff',
					'brand-purple': '#9353ff',
					'brand-dark': '#7928ca',
					'brand-light': '#b57aff',
					'brand-glow': '#a855f7',
					bgdark: '#0a0d14',
					surface: '#0f141f',
					'surface-border': '#1d2636',
				},
				fontFamily: {
					mono: ['"JetBrains Mono"', 'monospace'],
					sans: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
				}
			}
		}
	}
</script>

<style>
	body {
		font-family: 'Space Grotesk', sans-serif;
		background-color: #0a0814;
		color: #e2e8f0;
		overflow-x: hidden;
	}

	.font-mono-tech {
		font-family: 'JetBrains Mono', monospace;
	}

	/* Clean Architectural Dark Grid Matrix */
	.bg-grid-matrix {
		background-color: #0a0814;
		background-image: 
			linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
		background-size: 48px 48px;
	}

	/* Rich Dark Purple Gradient specifically on Hero Section */
	.hero-purple-gradient {
		background: 
			radial-gradient(ellipse 90% 85% at 50% 0%, rgba(147, 83, 255, 0.28) 0%, rgba(76, 25, 138, 0.45) 50%, transparent 95%),
			linear-gradient(180deg, #150b2e 0%, #0e071f 55%, #0a0814 100%);
	}

	.grid-panel {
		background: rgba(16, 13, 28, 0.85);
		backdrop-filter: blur(12px);
		border: 1px solid rgba(255, 255, 255, 0.08);
		transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
	}
	.grid-panel:hover {
		border-color: #9353ff;
		transform: translateY(-2px);
	}

	/* Pixel Chip SVG pattern helper */
	.pixel-block {
		background: #9353ff;
	}
</style>

<main class="min-h-screen bg-grid-matrix relative selection:bg-brand/40 selection:text-white">

	<!-- ========================================================================= -->
	<!-- 1. HERO SECTION (DEDICATED DARK PURPLE GRADIENT) -->
	<!-- ========================================================================= -->
	<section class="hero-purple-gradient pt-10 sm:pt-16 md:pt-24 pb-20 px-4 sm:px-6 lg:px-8 border-b border-[#1d2636] relative z-10">
		<div class="max-w-7xl mx-auto">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
				
				<!-- Left Column: Content (7 cols) -->
				<div class="lg:col-span-7 space-y-6">
					
					<!-- Brand Header Pill (Aligned to Left with SBG Logo) -->
					<div class="flex items-center justify-start">
						<div class="inline-flex items-center gap-2.5 px-3.5 py-2 bg-brand text-white font-mono-tech font-bold text-xs sm:text-sm tracking-wider uppercase shadow-lg shadow-brand/20">
							<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/SBG-Logo-White-Transparent.png' ) ); ?>" alt="AWS SBG" class="h-5 sm:h-6 w-auto object-contain shrink-0">
							<span>AWS Student Builder Group</span>
						</div>
					</div>

				<!-- Hero Statement (Matching Creative: "Want to build the future? Join the club.") -->
				<div class="space-y-2">
					<h1 class="text-4xl sm:text-6xl md:text-7xl font-extrabold font-mono-tech tracking-tight text-white leading-[1.08]">
						Want to build<br>
						the future?<br>
						<span class="text-brand-light">Join the club.</span>
					</h1>
				</div>

				<!-- Mission Subtitle -->
				<p class="text-base sm:text-lg text-gray-300 font-normal leading-relaxed max-w-xl">
					We are part of the <strong class="text-white font-semibold">AWS global student initiative</strong> at <strong class="text-white font-semibold">Air University Islamabad</strong>, empowering 900+ builders to master cloud computing and emerging technologies.
				</p>

				<!-- CTAs -->
				<div class="flex flex-col sm:flex-row gap-3 pt-2">
					<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" 
					   target="_blank" 
					   rel="noopener noreferrer" 
					   class="inline-flex items-center justify-center gap-3 px-7 py-4 font-mono-tech font-bold text-sm text-white bg-brand hover:bg-brand-dark transition-all duration-150 shadow-xl shadow-brand/25 border border-brand-light/30 text-center">
						<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/meetup-icon.png' ) ); ?>" alt="Meetup" class="w-5 h-5 object-contain">
						<span>Become Our Member</span>
					</a>

					<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" 
					   target="_blank" 
					   rel="noopener noreferrer" 
					   class="inline-flex items-center justify-center gap-3 px-7 py-4 font-mono-tech font-bold text-sm text-gray-200 bg-[#0f141f] hover:bg-[#151c2b] border border-[#1d2636] hover:border-brand/40 transition-all duration-150 text-center">
						<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/whatsapp-color-icon.png' ) ); ?>" alt="WhatsApp" class="w-5 h-5 object-contain">
						<span>WhatsApp Community</span>
					</a>
				</div>

				<!-- Builder Metadata Row -->
				<div class="pt-4 flex flex-wrap items-center gap-4 text-xs font-mono-tech text-gray-400">
					<div class="flex items-center gap-2">
						<span class="w-2 h-2 bg-brand rounded-none"></span>
						<span>900+ Members</span>
					</div>
					<span class="text-gray-600">/</span>
					<div class="flex items-center gap-2">
						<span class="w-2 h-2 bg-brand rounded-none"></span>
						<span>Led by Anas Riaz</span>
					</div>
					<span class="text-gray-600">/</span>
					<div class="flex items-center gap-2">
						<span class="w-2 h-2 bg-brand rounded-none"></span>
						<span>Air University Islamabad</span>
					</div>
				</div>

			</div>

			<!-- Right Column: Geometric Grid Motif from Brand Creative (5 cols) -->
			<div class="lg:col-span-5 relative">
				<div class="border border-[#1d2636] bg-[#0c1018]/90 p-6 sm:p-8 relative overflow-hidden shadow-2xl">
					
					<!-- Inner Grid Illustration (Pixel Grid Art) -->
					<div class="grid grid-cols-5 gap-2 pb-6 border-b border-[#1d2636]">
						<!-- Row 1 -->
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 bg-brand"></div>

						<!-- Row 2 -->
						<div class="h-10 bg-brand"></div>
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 bg-brand"></div>

						<!-- Row 3 -->
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 border border-[#1d2636]"></div>

						<!-- Row 4 -->
						<div class="h-10 border border-[#1d2636]"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 bg-brand"></div>
						<div class="h-10 border border-[#1d2636]"></div>
					</div>

					<!-- Key Chapter Highlights -->
					<div class="pt-6 space-y-4 font-mono-tech">
						<div class="flex items-center justify-between text-xs border-b border-[#1d2636]/60 pb-3">
							<span class="text-gray-400">ORGANIZATION</span>
							<span class="text-white font-bold">AWS Student Builder Group</span>
						</div>
						<div class="flex items-center justify-between text-xs border-b border-[#1d2636]/60 pb-3">
							<span class="text-gray-400">AFFILIATION</span>
							<span class="text-brand-light font-bold">AWS Global Initiative</span>
						</div>
						<div class="flex items-center justify-between text-xs border-b border-[#1d2636]/60 pb-3">
							<span class="text-gray-400">LOCATION</span>
							<span class="text-white font-bold">Air University Islamabad</span>
						</div>
						<div class="flex items-center justify-between text-xs">
							<span class="text-gray-400">LEADER</span>
							<span class="text-white font-bold">Anas Riaz</span>
						</div>
					</div>

				</div>
			</div>

		</div>
	</section>

	<!-- ========================================================================= -->
	<!-- SPECIAL FEATURE: OFFICIAL AWS BUILDER CENTER -->
	<!-- ========================================================================= -->
	<section class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative z-10">
		<div class="relative overflow-hidden border border-[#9353ff]/40 bg-gradient-to-r from-[#0f141f] via-[#141226] to-[#0f141f] p-6 sm:p-10 shadow-2xl">
			
			<!-- Background Grid Subtle Texture -->
			<div class="absolute inset-0 bg-grid-matrix opacity-30 pointer-events-none"></div>
			
			<div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
				
				<!-- Content Column -->
				<div class="lg:col-span-8 space-y-4">
					<div class="inline-flex items-center gap-2 px-3 py-1 bg-[#9353ff]/20 border border-[#9353ff]/50 text-[#b57aff] font-mono-tech text-xs font-bold uppercase tracking-wider">
						<span>⚡ OFFICIAL AWS PLATFORM</span>
						<span class="text-white/40">/</span>
						<span>FREE ACCESS</span>
					</div>

					<h2 class="text-2xl sm:text-4xl font-extrabold font-mono-tech text-white tracking-tight leading-tight">
						Activate Your Free <span class="text-[#b57aff]">AWS Builder Center</span> Account
					</h2>

					<p class="text-gray-300 text-sm sm:text-base leading-relaxed max-w-2xl">
						AWS has launched the <strong>AWS Builder Center</strong> — an official community platform packed with free hands-on labs, guided projects, cloud credits, and certification pathways. Every sign-up through our chapter link directly records your builder milestones.
					</p>

					<!-- Perks List -->
					<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 font-mono-tech text-xs">
						<div class="flex items-center gap-2 text-gray-200 bg-white/[0.03] p-2.5 border border-white/5">
							<span class="text-[#9353ff] font-bold">✓</span>
							<span>Free Hands-on Labs</span>
						</div>
						<div class="flex items-center gap-2 text-gray-200 bg-white/[0.03] p-2.5 border border-white/5">
							<span class="text-[#9353ff] font-bold">✓</span>
							<span>AWS Cloud Credits</span>
						</div>
						<div class="flex items-center gap-2 text-gray-200 bg-white/[0.03] p-2.5 border border-white/5">
							<span class="text-[#9353ff] font-bold">✓</span>
							<span>Official Cert Badges</span>
						</div>
					</div>
				</div>

				<!-- Action CTA Column -->
				<div class="lg:col-span-4 flex flex-col items-center lg:items-end justify-center space-y-3">
					<a href="https://bit.ly/4w5nmGl" 
					   target="_blank" 
					   rel="noopener noreferrer" 
					   class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 font-mono-tech font-bold text-sm text-white bg-[#9353ff] hover:bg-[#7928ca] transition-all duration-150 shadow-xl shadow-[#9353ff]/30 border border-[#b57aff]/40 text-center">
						<span>Sign Up on AWS Builder Center</span>
						<span>↗</span>
					</a>
					<span class="text-[11px] font-mono-tech text-gray-400 text-center">
						Unique Chapter Link: <span class="text-purple-300">bit.ly/4w5nmGl</span>
					</span>
				</div>

			</div>
		</div>
	</section>

	<!-- ========================================================================= -->
	<!-- 2. WHO WE ARE -->
	<!-- ========================================================================= -->
	<section class="py-16 border-t border-[#1d2636] bg-[#0c1018]/50 relative z-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
				
				<div class="lg:col-span-4 space-y-2">
					<span class="font-mono-tech text-xs font-bold text-brand uppercase tracking-wider">// 01. WHO WE ARE</span>
					<h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
						AWS Global Initiative at Air University
					</h2>
				</div>

				<div class="lg:col-span-8 space-y-4 text-gray-300 text-sm sm:text-base leading-relaxed">
					<p>
						The <strong class="text-white font-semibold">AWS Student Builder Group at Air University</strong> is an official community chapter under the global Amazon Web Services student initiative. Led by <strong class="text-white font-semibold">Anas Riaz</strong>, AWS Student Builder Group Leader, our chapter unites 900+ passionate students, developers, and cloud enthusiasts in Islamabad.
					</p>
					<p class="text-gray-400 text-sm">
						We bridge university academia with real production cloud environments, giving builders direct access to AWS services, hands-on architectural labs, certification guidance, and global tech network connections.
					</p>
				</div>

			</div>
		</div>
	</section>

	<!-- ========================================================================= -->
	<!-- 3. WHAT WE DO (CORE OFFERINGS) -->
	<!-- ========================================================================= -->
	<section class="py-20 border-t border-[#1d2636] relative z-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
			
			<div class="space-y-2">
				<span class="font-mono-tech text-xs font-bold text-brand uppercase tracking-wider">// 02. WHAT WE DO</span>
				<h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
					Creating Cloud &amp; Emerging Tech Opportunities
				</h2>
				<p class="text-gray-400 text-sm max-w-2xl font-mono-tech">
					High-impact learning tracks designed to take builders from fundamentals to industry certification.
				</p>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				
				<!-- Pillar 1: Industry Experts -->
				<div class="grid-panel p-6 space-y-4 flex flex-col justify-between">
					<div class="space-y-3">
						<div class="font-mono-tech text-xs font-bold text-brand">[01] SESSIONS</div>
						<h3 class="text-lg font-bold text-white">Sessions with Industry Experts</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Masterclasses and live technical breakdowns hosted by AWS Solutions Architects, DevOps leaders, and global trainers.
						</p>
					</div>
					<span class="w-6 h-1 bg-brand"></span>
				</div>

				<!-- Pillar 2: Certification Sessions -->
				<div class="grid-panel p-6 space-y-4 flex flex-col justify-between">
					<div class="space-y-3">
						<div class="font-mono-tech text-xs font-bold text-brand">[02] TRACKS</div>
						<h3 class="text-lg font-bold text-white">AWS Certification Sessions</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Dedicated exam training bootcamps focused on AWS Certified Cloud Practitioner (CLF) and Solutions Architect (SAA).
						</p>
					</div>
					<span class="w-6 h-1 bg-brand"></span>
				</div>

				<!-- Pillar 3: Certification Vouchers -->
				<div class="grid-panel p-6 space-y-4 flex flex-col justify-between">
					<div class="space-y-3">
						<div class="font-mono-tech text-xs font-bold text-brand">[03] REWARDS</div>
						<h3 class="text-lg font-bold text-white">AWS Certification Vouchers</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Performance diagnostics and access to official AWS examination discount vouchers for qualifying community members.
						</p>
					</div>
					<span class="w-6 h-1 bg-brand"></span>
				</div>

				<!-- Pillar 4: Swags & Recognition -->
				<div class="grid-panel p-6 space-y-4 flex flex-col justify-between">
					<div class="space-y-3">
						<div class="font-mono-tech text-xs font-bold text-brand">[04] MERCH</div>
						<h3 class="text-lg font-bold text-white">Official Swags &amp; Recognition</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Exclusive branded AWS swag kits, custom event badges, and cryptographic certificates of completion for active participants.
						</p>
					</div>
					<span class="w-6 h-1 bg-brand"></span>
				</div>

			</div>

		</div>
	</section>

	<!-- ========================================================================= -->
	<!-- 4. WHAT WE BUILT FOR OUR COMMUNITY -->
	<!-- ========================================================================= -->
	<section class="py-20 border-t border-[#1d2636] bg-[#0c1018]/50 relative z-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
			
			<div class="space-y-2">
				<span class="font-mono-tech text-xs font-bold text-brand uppercase tracking-wider">// 03. IN-HOUSE ENGINEERING</span>
				<h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
					What We Built for Our Community
				</h2>
				<p class="text-gray-400 text-sm max-w-2xl font-mono-tech">
					Custom software systems engineered by our team to automate learning, scheduling, and certification.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				
				<!-- Built Platform 1 -->
				<a href="<?php echo esc_url( home_url( '/exam-portal/' ) ); ?>" class="grid-panel p-7 flex flex-col justify-between space-y-6 group">
					<div class="space-y-3">
						<div class="flex items-center justify-between font-mono-tech text-xs">
							<span class="text-brand font-bold">[ENGINE_01]</span>
							<span class="text-gray-400">ACTIVE</span>
						</div>
						<h3 class="text-xl font-bold text-white group-hover:text-brand-light transition-colors">Exam Portal Engine</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Full-featured interactive mock examination environment with instant scoring, domain analytics, and dynamic certificate issuing.
						</p>
					</div>
					<span class="font-mono-tech text-xs font-bold text-brand-light flex items-center gap-1">LAUNCH_PORTAL &gt;</span>
				</a>

				<!-- Built Platform 2 -->
				<a href="<?php echo esc_url( home_url( '/reserve-slot/' ) ); ?>" class="grid-panel p-7 flex flex-col justify-between space-y-6 group">
					<div class="space-y-3">
						<div class="flex items-center justify-between font-mono-tech text-xs">
							<span class="text-brand font-bold">[ENGINE_02]</span>
							<span class="text-gray-400">SCHEDULE</span>
						</div>
						<h3 class="text-xl font-bold text-white group-hover:text-brand-light transition-colors">Slot Reservation System</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Automated exam seat scheduler that verifies event registration, manages seating capacity, and issues access passcodes.
						</p>
					</div>
					<span class="font-mono-tech text-xs font-bold text-brand-light flex items-center gap-1">RESERVE_SLOT &gt;</span>
				</a>

				<!-- Built Platform 3 -->
				<a href="<?php echo esc_url( home_url( '/claim-certificate/' ) ); ?>" class="grid-panel p-7 flex flex-col justify-between space-y-6 group">
					<div class="space-y-3">
						<div class="flex items-center justify-between font-mono-tech text-xs">
							<span class="text-brand font-bold">[ENGINE_03]</span>
							<span class="text-gray-400">CREDENTIAL</span>
						</div>
						<h3 class="text-xl font-bold text-white group-hover:text-brand-light transition-colors">Certificate Generator</h3>
						<p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
							Automated certificate generation and verification platform for bootcamp and workshop participants.
						</p>
					</div>
					<span class="font-mono-tech text-xs font-bold text-brand-light flex items-center gap-1">CLAIM_CREDENTIAL &gt;</span>
				</a>

			</div>

		</div>
	</section>

	<!-- ========================================================================= -->
	<!-- 5. FINAL CALL TO ACTION -->
	<!-- ========================================================================= -->
	<section class="py-24 border-t border-[#1d2636] relative z-10 text-center">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
			
			<div class="inline-block px-4 py-1.5 bg-brand/10 border border-brand/30 text-brand-light font-mono-tech text-xs font-bold uppercase tracking-widest">
				// JOIN 900+ BUILDERS
			</div>

			<h2 class="text-3xl sm:text-5xl font-extrabold font-mono-tech text-white tracking-tight leading-tight">
				Want to build the future?<br>
				<span class="text-brand-light">Join the community.</span>
			</h2>

			<p class="text-sm sm:text-base text-gray-300 max-w-xl mx-auto leading-relaxed">
				Connect with students, alumni, and cloud architects at Air University Islamabad. Access free workshops, practice exams, certification vouchers, and official AWS swag.
			</p>

			<div class="flex flex-col sm:flex-row gap-3.5 justify-center items-center pt-2">
				<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" 
				   target="_blank" 
				   rel="noopener noreferrer" 
				   class="inline-flex items-center justify-center gap-3 px-8 py-4 font-mono-tech font-bold text-sm text-white bg-brand hover:bg-brand-dark transition-all duration-150 shadow-xl shadow-brand/25 border border-brand-light/30 w-full sm:w-auto">
					<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/meetup-icon.png' ) ); ?>" alt="Meetup" class="w-5 h-5 object-contain">
					<span>Join on Meetup</span>
				</a>

				<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" 
				   target="_blank" 
				   rel="noopener noreferrer" 
				   class="inline-flex items-center justify-center gap-3 px-8 py-4 font-mono-tech font-bold text-sm text-gray-200 bg-[#0f141f] hover:bg-[#151c2b] border border-[#1d2636] hover:border-brand/40 transition-all duration-150 w-full sm:w-auto">
					<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/whatsapp-color-icon.png' ) ); ?>" alt="WhatsApp" class="w-5 h-5 object-contain">
					<span>WhatsApp Community</span>
				</a>
			</div>

		</div>
	</section>

</main>

<?php get_footer(); ?>
