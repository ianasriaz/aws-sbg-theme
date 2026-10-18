<?php
/**
 * Front Page Template - AWS Student Builder Group at Air University
 *
 * High-conversion launchpad featuring clean EduFi-inspired UI/UX, multi-color aurora glow canvas,
 * dark AWS container section shifts, and high-contrast typography adhering strictly to the brand palette + AWS Orange.
 *
 * @package AWS_SBG_Theme
 */

get_header();
?>

<style>
	/* ==========================================================================
	   1. CORE BASE & TYPOGRAPHY ENHANCEMENTS
	   ========================================================================== */
	html, body, .sbg-home-root {
		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
		background-color: #ffffff !important;
		color: #0f172a !important;
		overflow-x: hidden;
		width: 100%;
	}

	.sbg-home-root h1,
	.sbg-home-root h2,
	.sbg-home-root h3,
	.sbg-home-root h4 {
		color: #020617 !important;
		margin: 0;
	}

	.sbg-home-root p {
		color: #475569 !important;
		margin: 0;
	}

	.sbg-home-root a {
		text-decoration: none !important;
	}

	.sbg-home-root * {
		box-sizing: border-box;
	}

	/* ==========================================================================
	   2. MULTI-COLOR AURORA & MESH CANVAS
	   ========================================================================== */
	.sbg-home-root .aurora-canvas {
		position: relative;
		overflow: hidden;
	}

	.sbg-home-root .aurora-blob {
		position: absolute;
		border-radius: 9999px;
		filter: blur(80px);
		pointer-events: none;
		will-change: transform;
		z-index: 0;
	}

	.sbg-home-root .aurora-purple {
		width: 550px; height: 550px;
		top: -160px; right: -120px;
		background: radial-gradient(circle, rgba(147, 83, 255, 0.28), transparent 65%);
		animation: sbgAuroraFloat 12s ease-in-out infinite;
	}

	.sbg-home-root .aurora-orange {
		width: 480px; height: 480px;
		top: 10%; left: -140px;
		background: radial-gradient(circle, rgba(255, 153, 0, 0.22), transparent 65%);
		animation: sbgAuroraFloat 14s ease-in-out infinite reverse;
	}

	.sbg-home-root .aurora-green {
		width: 420px; height: 420px;
		bottom: -100px; right: 15%;
		background: radial-gradient(circle, rgba(0, 239, 136, 0.20), transparent 65%);
		animation: sbgAuroraFloat 10s ease-in-out infinite 2s;
	}

	@keyframes sbgAuroraFloat {
		0%, 100% { transform: translate(0, 0) scale(1); }
		50%      { transform: translate(30px, -24px) scale(1.08); }
	}

	/* Crisp Technical Blueprint Gridlines */
	.sbg-grid-lines {
		position: absolute;
		inset: 0;
		background-size: 38px 38px;
		background-image: 
			linear-gradient(to right, rgba(147, 83, 255, 0.07) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(147, 83, 255, 0.07) 1px, transparent 1px);
		mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		-webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		pointer-events: none;
	}

	.sbg-dot-matrix {
		background-image: radial-gradient(rgba(147, 83, 255, 0.15) 1px, transparent 1px);
		background-size: 20px 20px;
	}

	/* ==========================================================================
	   3. TEXT HIGHLIGHT PILLS & GRADIENTS
	   ========================================================================== */
	.sbg-text-highlight-green {
		background: #00ef88 !important;
		color: #020617 !important;
		padding: 2px 14px;
		border-radius: 12px;
		display: inline-block;
		transform: rotate(-1deg);
		box-shadow: 0 4px 15px rgba(0, 239, 136, 0.3);
	}

	.sbg-text-highlight-orange {
		background: #FF9900 !important;
		color: #020617 !important;
		padding: 2px 14px;
		border-radius: 12px;
		display: inline-block;
		transform: rotate(1deg);
		box-shadow: 0 4px 15px rgba(255, 153, 0, 0.3);
	}

	.sbg-gradient-text-purple {
		background: linear-gradient(92deg, #7928ca 0%, #9353ff 55%, #b47bff 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}

	.sbg-gradient-text-orange {
		background: linear-gradient(92deg, #FF9900 0%, #ffaa2b 60%, #ffc46b 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}

	/* ==========================================================================
	   4. BUTTON STYLES
	   ========================================================================== */
	.sbg-home-root .btn-launch {
		position: relative;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 10px;
		padding: 15px 32px;
		border-radius: 16px;
		background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%) !important;
		color: #ffffff !important;
		font-weight: 800;
		font-size: 14.5px;
		letter-spacing: 0.02em;
		box-shadow: 0 10px 26px -6px rgba(147,83,255,0.45);
		transition: transform .22s cubic-bezier(.16,1,.3,1), box-shadow .22s ease;
		overflow: hidden;
		text-decoration: none !important;
	}

	.sbg-home-root .btn-launch::after {
		content: "";
		position: absolute; inset: 0;
		background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,0.35) 50%, transparent 65%);
		transform: translateX(-120%);
		transition: transform .6s ease;
	}

	.sbg-home-root .btn-launch:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 34px -6px rgba(147,83,255,0.55);
	}

	.sbg-home-root .btn-launch:hover::after { transform: translateX(120%); }

	.sbg-home-root .btn-orange {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 9px;
		padding: 15px 30px;
		border-radius: 16px;
		background: linear-gradient(135deg, #FF9900 0%, #ffaa2b 100%) !important;
		color: #020617 !important;
		font-weight: 800;
		font-size: 14.5px;
		box-shadow: 0 10px 26px -6px rgba(255, 153, 0, 0.40);
		transition: all 0.22s ease;
		text-decoration: none !important;
	}

	.sbg-home-root .btn-orange:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 34px -6px rgba(255, 153, 0, 0.55);
	}

	.sbg-home-root .btn-ghost {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 9px;
		padding: 14px 28px;
		border-radius: 16px;
		border: 1.5px solid #e2e8f0 !important;
		background: #ffffff !important;
		color: #0f172a !important;
		font-weight: 700;
		font-size: 14.5px;
		transition: all .22s ease;
		text-decoration: none !important;
	}

	.sbg-home-root .btn-ghost:hover {
		border-color: #25D366 !important;
		color: #0f172a !important;
		box-shadow: 0 8px 20px -8px rgba(37,211,102,0.30);
		transform: translateY(-2px);
	}

	/* Hero 3D Swag Levitation */
	@keyframes sbgHeroLevitate {
		0%, 100% {
			transform: translateY(0px) rotate(0deg) scale(1);
			filter: drop-shadow(0 18px 28px rgba(147, 83, 255, 0.22));
		}
		25% {
			transform: translateY(-9px) rotate(1.2deg) scale(1.015);
			filter: drop-shadow(0 26px 36px rgba(147, 83, 255, 0.28));
		}
		50% {
			transform: translateY(-16px) rotate(-0.5deg) scale(1.025);
			filter: drop-shadow(0 34px 44px rgba(147, 83, 255, 0.32));
		}
		75% {
			transform: translateY(-7px) rotate(-1.2deg) scale(1.01);
			filter: drop-shadow(0 24px 32px rgba(147, 83, 255, 0.26));
		}
	}

	.sbg-hero-swag-anim {
		animation: sbgHeroLevitate 6.5s ease-in-out infinite;
		transform-origin: center bottom;
		will-change: transform, filter;
		transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), filter 0.4s ease;
	}

	/* ==========================================================================
	   5. RICH SLEEK FEATURE CARDS (LIGHT MODE SECTIONS)
	   ========================================================================== */
	.sbg-home-root .feature-card {
		position: relative;
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 24px;
		overflow: hidden;
		box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03), 0 10px 25px -8px rgba(147, 83, 255, 0.06);
		transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease, border-color 0.28s ease;
	}

	.sbg-home-root .feature-card-accent-orange::before {
		content: "";
		position: absolute;
		top: 0; left: 0; right: 0;
		height: 4px;
		background: linear-gradient(90deg, #FF9900, #ffaa2b);
		transform: scaleX(0);
		transform-origin: left;
		transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
		z-index: 2;
	}

	.sbg-home-root .feature-card-accent-purple::before {
		content: "";
		position: absolute;
		top: 0; left: 0; right: 0;
		height: 4px;
		background: linear-gradient(90deg, #7928ca, #9353ff);
		transform: scaleX(0);
		transform-origin: left;
		transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
		z-index: 2;
	}

	.sbg-home-root .feature-card-accent-green::before {
		content: "";
		position: absolute;
		top: 0; left: 0; right: 0;
		height: 4px;
		background: linear-gradient(90deg, #00ef88, #4dff9f);
		transform: scaleX(0);
		transform-origin: left;
		transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
		z-index: 2;
	}

	.sbg-home-root .feature-card:hover {
		transform: translateY(-6px);
		border-color: rgba(147, 83, 255, 0.45);
		box-shadow: 0 20px 42px -12px rgba(147, 83, 255, 0.20);
	}

	.sbg-home-root .feature-card:hover::before {
		transform: scaleX(1);
	}

	.sbg-home-root .icon-badge-orange {
		width: 52px; height: 52px;
		border-radius: 16px;
		background: rgba(255, 153, 0, 0.12);
		border: 1px solid rgba(255, 153, 0, 0.25);
		color: #FF9900;
		display: flex; align-items: center; justify-content: center;
		flex-shrink: 0;
		transition: all 0.25s ease;
	}

	.sbg-home-root .feature-card:hover .icon-badge-orange {
		background: #FF9900;
		color: #020617;
		transform: rotate(-5deg) scale(1.08);
	}

	.sbg-home-root .icon-badge-purple {
		width: 52px; height: 52px;
		border-radius: 16px;
		background: rgba(147, 83, 255, 0.12);
		border: 1px solid rgba(147, 83, 255, 0.25);
		color: #9353ff;
		display: flex; align-items: center; justify-content: center;
		flex-shrink: 0;
		transition: all 0.25s ease;
	}

	.sbg-home-root .feature-card:hover .icon-badge-purple {
		background: #9353ff;
		color: #ffffff;
		transform: rotate(-5deg) scale(1.08);
	}

	.sbg-home-root .icon-badge-green {
		width: 52px; height: 52px;
		border-radius: 16px;
		background: rgba(0, 239, 136, 0.12);
		border: 1px solid rgba(0, 239, 136, 0.25);
		color: #00c96f;
		display: flex; align-items: center; justify-content: center;
		flex-shrink: 0;
		transition: all 0.25s ease;
	}

	.sbg-home-root .feature-card:hover .icon-badge-green {
		background: #00ef88;
		color: #020617;
		transform: rotate(-5deg) scale(1.08);
	}

	/* ==========================================================================
	   6. DEEP DARK AWS CONTAINER SECTION SHIFT (SECTION 3 VISUAL BREAK)
	   ========================================================================== */
	.sbg-dark-container-section {
		position: relative;
		background: linear-gradient(180deg, #070913 0%, #0c0f24 100%) !important;
		color: #ffffff !important;
		border-radius: 32px;
		overflow: hidden;
		box-shadow: 0 30px 80px -20px rgba(7, 9, 19, 0.5);
		width: 100%;
	}

	.sbg-dark-container-section h1,
	.sbg-dark-container-section h2,
	.sbg-dark-container-section h3,
	.sbg-dark-container-section h4 {
		color: #ffffff !important;
	}

	.sbg-dark-container-section p {
		color: #94a3b8 !important;
	}

	.sbg-dark-card {
		background: rgba(15, 23, 42, 0.70) !important;
		backdrop-filter: blur(14px);
		-webkit-backdrop-filter: blur(14px);
		border: 1px solid rgba(255, 255, 255, 0.10);
		border-radius: 22px;
		transition: all 0.3s ease;
	}

	.sbg-dark-card:hover {
		transform: translateY(-5px);
		background: rgba(15, 23, 42, 0.90) !important;
		border-color: rgba(255, 153, 0, 0.40);
		box-shadow: 0 20px 40px -10px rgba(255, 153, 0, 0.20);
	}

	/* ==========================================================================
	   7. TICKER STRIP
	   ========================================================================== */
	.sbg-home-root .ticker-wrap {
		overflow: hidden;
		border-top: 1px solid #ede9fe;
		border-bottom: 1px solid #ede9fe;
		background: linear-gradient(90deg, rgba(255, 153, 0, 0.04), rgba(147, 83, 255, 0.05));
		mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent);
		-webkit-mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent);
	}

	.sbg-home-root .ticker-track {
		display: flex;
		width: max-content;
		animation: tickerFlow 28s linear infinite;
	}

	.sbg-home-root .ticker-track:hover {
		animation-play-state: paused;
	}

	@keyframes tickerFlow {
		from { transform: translateX(0); }
		to   { transform: translateX(-50%); }
	}

	.sbg-home-root .ticker-item {
		display: inline-flex;
		align-items: center;
		gap: 12px;
		padding: 14px 28px;
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.14em;
		text-transform: uppercase;
		color: #0f172a;
		white-space: nowrap;
	}

	.sbg-home-root .ticker-dot {
		width: 7px; height: 7px;
		border-radius: 99px;
		background: #FF9900;
		box-shadow: 0 0 0 4px rgba(255, 153, 0, 0.2);
	}

	/* ==========================================================================
	   8. FAQ ACCORDION ENHANCEMENTS (FULL-WIDTH ALIGNED)
	   ========================================================================== */
	.sbg-home-root .faq-section-wrapper {
		width: 100%;
	}

	.sbg-home-root .faq-list {
		width: 100% !important;
		max-width: 100% !important;
		padding-top: 24px;
		display: flex;
		flex-direction: column;
		gap: 14px;
	}

	.sbg-home-root .faq-card {
		width: 100% !important;
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 20px;
		overflow: hidden;
		transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
		box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.03);
	}

	.sbg-home-root .faq-card:hover {
		border-color: rgba(255, 153, 0, 0.4);
		box-shadow: 0 8px 24px -6px rgba(255, 153, 0, 0.12);
	}

	.sbg-home-root .faq-card.open {
		border-color: #FF9900;
		border-left-width: 4px;
		border-left-color: #FF9900;
		background: #ffffff;
		box-shadow: 0 12px 30px -8px rgba(255, 153, 0, 0.18);
	}

	.sbg-home-root .faq-header {
		width: 100%;
		text-align: left;
		background: transparent;
		border: none;
		cursor: pointer;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		padding: 22px 26px;
		font-weight: 800;
		font-size: 16.5px;
		line-height: 1.4;
		color: #020617 !important;
		transition: color 0.15s ease;
		outline: none;
	}

	.sbg-home-root .faq-card.open .faq-header {
		color: #cc7a00 !important;
	}

	.sbg-home-root .faq-toggle-icon {
		width: 34px; height: 34px;
		border-radius: 10px;
		display: flex; align-items: center; justify-content: center;
		background: rgba(255,153,0,0.12); color: #FF9900;
		font-family: ui-monospace, monospace; font-weight: 900; font-size: 18px;
		flex-shrink: 0; transition: all .3s cubic-bezier(.16,1,.3,1);
	}

	.sbg-home-root .faq-card.open .faq-toggle-icon {
		background: linear-gradient(135deg, #FF9900, #ffaa2b);
		color: #020617;
		transform: rotate(135deg);
	}

	.sbg-home-root .faq-body {
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1);
	}

	.sbg-home-root .faq-body-inner {
		padding: 0 26px 22px;
		font-size: 14.5px;
		line-height: 1.75;
		color: #475569 !important;
		border-top: 1px dashed rgba(255, 153, 0, 0.2);
		padding-top: 16px;
	}

	@media (prefers-reduced-motion: reduce) {
		.sbg-hero-swag-anim,
		.sbg-home-root .aurora-blob,
		.ticker-track {
			animation: none !important;
			transition: none !important;
		}
	}
</style>

<div class="sbg-home-root min-h-screen bg-white">

	<!-- ========================================================================= -->
	<!-- 1. HERO SECTION (MULTI-COLOR AURORA, CLEAN BOLD TYPOGRAPHY & IMAGE)      -->
	<!-- ========================================================================= -->
	<section class="aurora-canvas py-14 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 border-b border-slate-100 relative">
		<!-- Multi-Color Radial Glow Spots -->
		<div class="aurora-blob aurora-purple"></div>
		<div class="aurora-blob aurora-orange"></div>
		<div class="aurora-blob aurora-green"></div>
		
		<!-- Architectural Gridlines -->
		<div class="sbg-grid-lines"></div>

		<div class="max-w-6xl mx-auto relative z-10">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
				
				<!-- Left Column: Copy & Simple CTAs -->
				<div class="lg:col-span-7 text-left space-y-6">

					<!-- Impactful Headline with Highlight Pills -->
					<h1 style="font-size:clamp(38px,5.5vw,64px); font-weight:900; letter-spacing:-0.03em; line-height:1.05; text-transform:uppercase; color:#020617 !important;">
						LEARN, <span class="sbg-text-highlight-green">BUILD.</span><br>
						&amp; GROW WITH <span class="sbg-gradient-text-orange">AWS</span><span class="text-[#FF9900]">.</span>
					</h1>

					<!-- Description -->
					<p style="font-size:16px; line-height:1.75; color:#475569 !important; max-width:540px;">
						AWS Student Builder Group at Air University Islamabad — the official AWS student community. Get hands-on with Cloud &amp; AI, prepare for <strong style="color:#0f172a;">AWS certifications</strong>, earn promotional cloud credits, and build real-world software with <strong style="color:#0f172a;">industry experts</strong>.
					</p>

					<!-- Simple CTAs Stack -->
					<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2 w-full sm:w-auto">
						<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" 
						   target="_blank" 
						   rel="noopener noreferrer" 
						   class="btn-launch text-center">
							<span>Become Member</span>
							<span class="text-base leading-none">→</span>
						</a>

						<a href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI" 
						   target="_blank" 
						   rel="noopener noreferrer" 
						   class="btn-ghost text-center">
							<svg class="w-4 h-4 text-[#25D366] shrink-0" fill="currentColor" viewBox="0 0 24 24">
								<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
							</svg>
							<span>WhatsApp Community</span>
						</a>
					</div>

				</div>

				<!-- Right Column: Hero Visual with Ambient Glow -->
				<div class="lg:col-span-5 relative flex items-center justify-center pt-8 lg:pt-0">
					<!-- Ambient Halo Aura -->
					<div class="absolute inset-0 bg-gradient-to-tr from-[#FF9900]/25 via-[#9353ff]/22 to-[#00ef88]/20 rounded-full blur-3xl pointer-events-none"></div>

					<?php
$hp_img_url = file_exists( get_theme_file_path( 'assets/images/AWS-Home.webp' ) )
					? get_theme_file_uri( 'assets/images/AWS-Home.webp' )
						: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Home.webp';
					?>
					<img src="<?php echo esc_url( $hp_img_url ); ?>" 
					     onerror="this.onerror=null;this.src='https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Home.webp';"
					     alt="Official AWS Student Builder Group Member Swags and Lanyard" 
					     class="sbg-hero-swag-anim relative z-10 w-full max-w-[420px] lg:max-w-[460px] h-auto object-contain select-none cursor-pointer"
					     loading="eager"
					     fetchpriority="high">
				</div>

			</div>
		</div>
	</section>

	<!-- Continuous Ticker Strip -->
	<div class="ticker-wrap">
		<div class="ticker-track">
			<?php for ( $i = 0; $i < 2; $i++ ) : ?>
				<span class="ticker-item"><span class="ticker-dot"></span>Official AWS Student Chapter</span>
				<span class="ticker-item"><span class="ticker-dot"></span>Sessions with Industry Experts</span>
				<span class="ticker-item"><span class="ticker-dot"></span>AWS Certification Preparation</span>
				<span class="ticker-item"><span class="ticker-dot"></span>Exam Discount Vouchers</span>
				<span class="ticker-item"><span class="ticker-dot"></span>Free AWS Cloud Credits</span>
				<span class="ticker-item"><span class="ticker-dot"></span>Official AWS Swags &amp; Badges</span>
				<span class="ticker-item"><span class="ticker-dot"></span>Air University Islamabad</span>
			<?php endfor; ?>
		</div>
	</div>

	<!-- Main Body Container -->
	<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

		<!-- ========================================================================= -->
		<!-- 2. BRIDGING CLASSROOMS (LIGHT SURFACE `#f8fafc` WITH ACCENT CARDS)        -->
		<!-- ========================================================================= -->
		<section id="bridging" class="space-y-8 p-8 sm:p-12 rounded-3xl bg-slate-50/80 border border-slate-200/80 relative overflow-hidden">
			<div class="sbg-dot-matrix absolute inset-0 opacity-40 pointer-events-none"></div>

			<div class="max-w-2xl relative z-10">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; color:#020617 !important; line-height:1.08;">
					Bridging Classrooms <span class="sbg-gradient-text-purple">with AWS Cloud</span>
				</h2>
				<p style="padding-top:12px; font-size:15px; sm:font-size:16px; line-height:1.75; color:#475569 !important;">
					AWS Student Builder Groups are university communities supported directly by Amazon Web Services. We give students direct access to AWS technologies, mentorship from certified cloud architects, and career pathways.
				</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
				
				<!-- Card 1 (Orange Accent) -->
				<div class="feature-card feature-card-accent-orange p-6 sm:p-7 space-y-4">
					<div class="icon-badge-orange">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
						</svg>
					</div>
					<h3 style="font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: -0.01em; color: #020617 !important;">
						Hands-On Cloud Labs
					</h3>
					<p style="font-size: 14px; line-height: 1.7; color: #475569 !important;">
						Go beyond theory. Launch virtual servers on EC2, configure secure S3 storage, build serverless Lambda workflows, and experiment with generative AI on Amazon Bedrock.
					</p>
				</div>

				<!-- Card 2 (Purple Accent) -->
				<div class="feature-card feature-card-accent-purple p-6 sm:p-7 space-y-4">
					<div class="icon-badge-purple">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
						</svg>
					</div>
					<h3 style="font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: -0.01em; color: #020617 !important;">
						Peer-to-Peer Learning
					</h3>
					<p style="font-size: 14px; line-height: 1.7; color: #475569 !important;">
						Collaborate with fellow students across all academic semesters. Share development tips, work on group hackathon projects, and build software portfolios together.
					</p>
				</div>

				<!-- Card 3 (Green Accent) -->
				<div class="feature-card feature-card-accent-green p-6 sm:p-7 space-y-4">
					<div class="icon-badge-green">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
						</svg>
					</div>
					<h3 style="font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: -0.01em; color: #020617 !important;">
						Industry Career Growth
					</h3>
					<p style="font-size: 14px; line-height: 1.7; color: #475569 !important;">
						Build industry-demanded skills, prepare for high-value AWS certifications, and network with cloud professionals and tech recruiters worldwide.
					</p>
				</div>

			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- 3. CORE COMMUNITY PILLARS (DEEP DARK CONTAINER SECTION SHIFT)             -->
		<!-- ========================================================================= -->
		<section id="offers" class="sbg-dark-container-section p-8 sm:p-14 space-y-10">
			<!-- Ambient Backlights -->
			<div class="absolute -top-24 -right-24 w-80 h-80 bg-[#FF9900]/20 rounded-full blur-3xl pointer-events-none"></div>
			<div class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#9353ff]/25 rounded-full blur-3xl pointer-events-none"></div>

			<div class="relative z-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-white/10">
				<div>
					<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; color:#ffffff !important; line-height:1.08;">
						What We Offer to <span class="sbg-gradient-text-orange">Every Member</span>
					</h2>
					<p style="padding-top:8px; font-size:15px; line-height:1.75; color:#94a3b8 !important;">
						Practical workshops, certified cloud mentors, and official AWS opportunities.
					</p>
				</div>
				<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" 
				   target="_blank" 
				   rel="noopener noreferrer" 
				   class="btn-orange self-start sm:self-auto text-sm py-3 px-6">
					<span>Become Member</span>
					<span class="text-base leading-none">→</span>
				</a>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
				
				<!-- Pillar 1 -->
				<div class="sbg-dark-card p-7 sm:p-8 space-y-4">
					<div class="w-12 h-12 rounded-xl bg-[#FF9900]/15 border border-[#FF9900]/30 text-[#FF9900] flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
						</svg>
					</div>
					<div class="space-y-2">
						<h3 style="font-size: 21px; font-weight: 900; text-transform: uppercase; color: #ffffff !important;">
							Sessions with Industry Experts
						</h3>
						<p style="font-size: 14px; line-height: 1.75; color: #94a3b8 !important;">
							Learn directly from certified AWS Solutions Architects, DevOps leads, and cloud consultants. Join live technical masterclasses, architectural reviews, and career AMAs.
						</p>
					</div>
				</div>

				<!-- Pillar 2 -->
				<div class="sbg-dark-card p-7 sm:p-8 space-y-4">
					<div class="w-12 h-12 rounded-xl bg-[#9353ff]/15 border border-[#9353ff]/30 text-[#9353ff] flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
						</svg>
					</div>
					<div class="space-y-2">
						<h3 style="font-size: 21px; font-weight: 900; text-transform: uppercase; color: #ffffff !important;">
							AWS Certification Prep &amp; Vouchers
						</h3>
						<p style="font-size: 14px; line-height: 1.75; color: #94a3b8 !important;">
							Structured study cohorts for AWS Certified Cloud Practitioner (CLF-C02) and Solutions Architect Associate (SAA-C03), featuring practice exams and official discount vouchers.
						</p>
					</div>
				</div>

				<!-- Pillar 3 -->
				<div class="sbg-dark-card p-7 sm:p-8 space-y-4">
					<div class="w-12 h-12 rounded-xl bg-[#00ef88]/15 border border-[#00ef88]/30 text-[#00ef88] flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 114 0v4m-4 0H4a2 2 0 00-2 2v2a2 2 0 002 2h16a2 2 0 002-2v-2a2 2 0 00-2-2h-8z"/>
						</svg>
					</div>
					<div class="space-y-2">
						<h3 style="font-size: 21px; font-weight: 900; text-transform: uppercase; color: #ffffff !important;">
							Free AWS Cloud Credits
						</h3>
						<p style="font-size: 14px; line-height: 1.75; color: #94a3b8 !important;">
							Build without cloud bill anxiety. We distribute promotional AWS credits to active community members and student teams so you can deploy prototypes on real AWS services.
						</p>
					</div>
				</div>

				<!-- Pillar 4 -->
				<div class="sbg-dark-card p-7 sm:p-8 space-y-4">
					<div class="w-12 h-12 rounded-xl bg-amber-400/15 border border-amber-400/30 text-amber-400 flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
						</svg>
					</div>
					<div class="space-y-2">
						<h3 style="font-size: 21px; font-weight: 900; text-transform: uppercase; color: #ffffff !important;">
							Official AWS Swags &amp; Recognition
						</h3>
						<p style="font-size: 14px; line-height: 1.75; color: #94a3b8 !important;">
							Get rewarded for your passion. Active community participants and hackathon builders earn official AWS merchandise, stickers, t-shirts, pins, and recognized certificates.
						</p>
					</div>
				</div>

			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- 4. OFFICIAL AWS BUILDER CENTER BANNER                                    -->
		<!-- ========================================================================= -->
		<section id="builder-center" class="feature-card p-8 sm:p-12 border border-[#FF9900]/30 bg-gradient-to-br from-amber-500/10 via-white to-purple-500/10 relative overflow-hidden">
			<div class="sbg-grid-lines opacity-40"></div>

			<div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
				<div class="space-y-3 max-w-2xl">
					<h2 style="font-size:clamp(26px,3.8vw,40px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; color:#020617 !important; line-height:1.1;">
						Activate Your Free <span class="sbg-gradient-text-orange">AWS Builder Center</span> Account
					</h2>
					<p style="padding-top:4px; font-size:15px; line-height:1.75; color:#475569 !important;">
						Access 900+ guided hands-on labs, 12 months of Skill Builder Premium, and certification roadmaps. Sign up through our chapter link to claim your student rewards.
					</p>
				</div>

				<div class="flex-shrink-0">
					<a href="https://builder.aws.com/start?trk=8e356433-7e2c-4ee9-8c3a-5bccc682a4f5&amp;sc_channel=el" 
					   target="_blank" 
					   rel="noopener noreferrer" 
					   class="btn-orange whitespace-nowrap text-center w-full sm:w-auto">
						<span>Sign Up on Builder Center</span>
						<span class="text-base leading-none">↗</span>
					</a>
				</div>
			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- 5. INTERACTIVE FAQ ACCORDION (FULL-WIDTH ALIGNED)                         -->
		<!-- ========================================================================= -->
		<section id="faq" class="faq-section-wrapper">
			<div class="w-full">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; color:#020617 !important; line-height:1.08;">
					Frequently Asked <span class="sbg-gradient-text-purple">Questions</span>
				</h2>
				<p style="padding-top:8px; font-size:15px; line-height:1.75; color:#475569 !important;">
					Everything you need to know about joining and participating in our chapter.
				</p>
			</div>

			<div class="faq-list">
				
				<!-- FAQ 1 -->
				<div class="faq-card">
					<button type="button" class="faq-header" onclick="sbgHomeToggleFaq(this)">
						<span>Who can join the AWS Student Builder Group at Air University?</span>
						<span class="faq-toggle-icon">+</span>
					</button>
					<div class="faq-body">
						<div class="faq-body-inner">
							Any student enrolled at Air University Islamabad can join. Membership is 100% free and open to all degree programs, including Computer Science, Software Engineering, IT, Cyber Security, and Electrical Engineering.
						</div>
					</div>
				</div>

				<!-- FAQ 2 -->
				<div class="faq-card">
					<button type="button" class="faq-header" onclick="sbgHomeToggleFaq(this)">
						<span>Do I need prior cloud or programming experience?</span>
						<span class="faq-toggle-icon">+</span>
					</button>
					<div class="faq-body">
						<div class="faq-body-inner">
							No prior cloud experience is required. We start with absolute foundational cloud concepts (AWS Cloud Practitioner level) and step-by-step console navigation before advancing to architectural design and deployments.
						</div>
					</div>
				</div>

				<!-- FAQ 3 -->
				<div class="faq-card">
					<button type="button" class="faq-header" onclick="sbgHomeToggleFaq(this)">
						<span>How do I receive AWS Exam Discount Vouchers and Cloud Credits?</span>
						<span class="faq-toggle-icon">+</span>
					</button>
					<div class="faq-body">
						<div class="faq-body-inner">
							Official AWS exam discount vouchers and promotional credits are awarded to active community members who participate in certification study bootcamps, complete hands-on labs, and contribute to chapter projects.
						</div>
					</div>
				</div>

				<!-- FAQ 4 -->
				<div class="faq-card">
					<button type="button" class="faq-header" onclick="sbgHomeToggleFaq(this)">
						<span>How do I get official AWS Swags?</span>
						<span class="faq-toggle-icon">+</span>
					</button>
					<div class="faq-body">
						<div class="faq-body-inner">
							Official AWS merchandise (t-shirts, stickers, notebooks, pins, badges) is distributed during in-person workshops, bootcamps, hackathons, and community challenge events.
						</div>
					</div>
				</div>

			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- 6. FINAL CALL TO ACTION                                                  -->
		<!-- ========================================================================= -->
		<section class="sbg-dark-container-section p-8 sm:p-16 text-center space-y-8 relative overflow-hidden">
			<!-- Particle & Glow Spot Overlay -->
			<div class="absolute inset-0 bg-gradient-to-r from-[#9353ff]/20 via-transparent to-[#FF9900]/20 pointer-events-none"></div>

			<div class="relative z-10 max-w-xl mx-auto space-y-4">
				<h2 style="font-size:clamp(28px,4.5vw,46px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; color:#ffffff !important; line-height:1.08;">
					Ready to Start Your <span class="sbg-gradient-text-orange">Cloud Journey</span>?
				</h2>
				<p style="padding-top:4px; font-size:15px; sm:font-size:16px; line-height:1.75; color:#94a3b8 !important; max-width:540px; margin:0 auto;">
					Join fellow students and builders at Air University Islamabad. Connect with cloud mentors, earn official certifications, and accelerate your tech career.
				</p>
			</div>

			<div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-4 pt-2 max-w-md mx-auto w-full">
				<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" 
				   target="_blank" 
				   rel="noopener noreferrer" 
				   class="btn-launch w-full sm:w-auto text-center">
					<span>Become Member</span>
					<span class="text-base leading-none">→</span>
				</a>

				<a href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI" 
				   target="_blank" 
				   rel="noopener noreferrer" 
				   class="btn-ghost w-full sm:w-auto text-center">
					<svg class="w-4 h-4 text-[#25D366] shrink-0" fill="currentColor" viewBox="0 0 24 24">
						<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
					</svg>
					<span>WhatsApp Community</span>
				</a>
			</div>
		</section>

	</main>

</div>

<script>
	function sbgHomeToggleFaq(btn) {
		const card   = btn.closest('.faq-card');
		const body   = card.querySelector('.faq-body');
		const isOpen = card.classList.contains('open');

		document.querySelectorAll('.sbg-home-root .faq-card.open').forEach(function(el) {
			el.classList.remove('open');
			el.querySelector('.faq-body').style.maxHeight = null;
		});

		if (!isOpen) {
			card.classList.add('open');
			const inner = body.querySelector('.faq-body-inner');
			body.style.maxHeight = (body.scrollHeight + (inner ? inner.scrollHeight : 100)) + 'px';
		}
	}
</script>

<?php get_footer(); ?>
