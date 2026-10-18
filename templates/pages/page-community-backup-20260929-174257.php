<?php
/**
 * Template Name: Community Hub & Student Rewards
 *
 * Clean EduFi-inspired high-conversion launchpad featuring multi-color aurora glow canvas,
 * dark AWS container journey section, and high-contrast typography adhering strictly to the brand palette + AWS Orange.
 *
 * @package AWS_SBG_Theme
 */

get_header();
?>

<!-- Scoped styles for Community Hub -->
<style>
	/* ============ SCOPE & BASE ============ */
	.community-page-wrapper {
		background-color: #ffffff !important;
		color: #0f172a !important;
		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		width: 100%;
		overflow-x: hidden;
	}
	.community-page-wrapper h1,
	.community-page-wrapper h2,
	.community-page-wrapper h3,
	.community-page-wrapper h4 { color: #020617 !important; margin: 0; }
	.community-page-wrapper p  { color: #475569 !important; margin: 0; }
	.community-page-wrapper a  { text-decoration: none !important; }
	.community-page-wrapper *  { box-sizing: border-box; }

	/* ============ MULTI-COLOR AURORA BLOBS ============ */
	.community-page-wrapper .aurora-canvas {
		position: relative;
		overflow: hidden;
	}
	.community-page-wrapper .aurora-blob {
		position: absolute;
		border-radius: 9999px;
		filter: blur(80px);
		pointer-events: none;
		will-change: transform;
		z-index: 0;
	}
	.community-page-wrapper .aurora-purple {
		width: 550px; height: 550px;
		top: -160px; right: -120px;
		background: radial-gradient(circle, rgba(147,83,255,0.28), transparent 65%);
		animation: cpAuroraFloat 12s ease-in-out infinite;
	}
	.community-page-wrapper .aurora-orange {
		width: 480px; height: 480px;
		top: 10%; left: -140px;
		background: radial-gradient(circle, rgba(255,153,0,0.22), transparent 65%);
		animation: cpAuroraFloat 14s ease-in-out infinite reverse;
	}
	.community-page-wrapper .aurora-green {
		width: 420px; height: 420px;
		bottom: -100px; right: 15%;
		background: radial-gradient(circle, rgba(0,239,136,0.20), transparent 65%);
		animation: cpAuroraFloat 10s ease-in-out infinite 2s;
	}
	@keyframes cpAuroraFloat {
		0%,100% { transform: translate(0,0) scale(1); }
		50%     { transform: translate(30px,-24px) scale(1.08); }
	}

	/* Blueprint & Dot Matrix */
	.community-page-wrapper .cp-grid-lines {
		position: absolute; inset: 0;
		background-size: 38px 38px;
		background-image: 
			linear-gradient(to right, rgba(147,83,255,0.07) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(147,83,255,0.07) 1px, transparent 1px);
		mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		-webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		pointer-events: none;
	}
	.community-page-wrapper .cp-dot-matrix {
		background-image: radial-gradient(rgba(147,83,255,0.15) 1px, transparent 1px);
		background-size: 20px 20px;
	}

	/* ============ TEXT HIGHLIGHT PILLS ============ */
	.community-page-wrapper .cp-text-highlight-green {
		background: #00ef88 !important;
		color: #020617 !important;
		padding: 2px 14px;
		border-radius: 12px;
		display: inline-block;
		transform: rotate(-1deg);
		box-shadow: 0 4px 15px rgba(0,239,136,0.3);
	}
	.community-page-wrapper .cp-text-highlight-orange {
		background: #FF9900 !important;
		color: #020617 !important;
		padding: 2px 14px;
		border-radius: 12px;
		display: inline-block;
		transform: rotate(1deg);
		box-shadow: 0 4px 15px rgba(255,153,0,0.3);
	}

	/* ============ BRAND & AWS GRADIENT TEXT ============ */
	.community-page-wrapper .grad-text {
		background: linear-gradient(92deg, #7928ca 0%, #9353ff 55%, #b47bff 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}
	.community-page-wrapper .grad-text-orange {
		background: linear-gradient(92deg, #FF9900 0%, #ffaa2b 60%, #ffc46b 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}

	/* ============ BUTTONS ============ */
	.community-page-wrapper .btn-launch {
		position: relative;
		display: inline-flex; align-items: center; justify-content: center;
		gap: 10px; padding: 15px 32px; border-radius: 16px;
		background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%) !important;
		color: #ffffff !important;
		font-weight: 800; font-size: 14.5px;
		letter-spacing: 0.02em;
		box-shadow: 0 10px 26px -6px rgba(147,83,255,0.45);
		transition: transform .22s cubic-bezier(.16,1,.3,1), box-shadow .22s ease;
		overflow: hidden;
		text-decoration: none !important;
	}
	.community-page-wrapper .btn-launch::after {
		content: ""; position: absolute; inset: 0;
		background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,0.35) 50%, transparent 65%);
		transform: translateX(-120%);
		transition: transform .6s ease;
	}
	.community-page-wrapper .btn-launch:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 34px -6px rgba(147,83,255,0.55);
	}
	.community-page-wrapper .btn-launch:hover::after { transform: translateX(120%); }

	.community-page-wrapper .btn-orange {
		display: inline-flex; align-items: center; justify-content: center;
		gap: 9px; padding: 15px 30px; border-radius: 16px;
		background: linear-gradient(135deg, #FF9900 0%, #ffaa2b 100%) !important;
		color: #020617 !important;
		font-weight: 800; font-size: 14.5px;
		box-shadow: 0 10px 26px -6px rgba(255, 153, 0, 0.40);
		transition: all 0.22s ease;
		text-decoration: none !important;
	}
	.community-page-wrapper .btn-orange:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 34px -6px rgba(255, 153, 0, 0.55);
	}

	.community-page-wrapper .btn-ghost {
		display: inline-flex; align-items: center; justify-content: center;
		gap: 9px; padding: 14px 28px; border-radius: 16px;
		border: 1.5px solid #e2e8f0 !important;
		background: #ffffff !important;
		color: #0f172a !important;
		font-weight: 700; font-size: 14.5px;
		transition: all .22s ease;
		text-decoration: none !important;
	}
	.community-page-wrapper .btn-ghost:hover {
		border-color: #25D366 !important;
		color: #0f172a !important;
		box-shadow: 0 8px 20px -8px rgba(37, 211, 102, 0.30);
		transform: translateY(-2px);
	}

	/* ============ SWAG SHOWCASE & 3D ANIMATION ============ */
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
	.community-page-wrapper .sbg-hero-swag-anim {
		animation: sbgHeroLevitate 6.5s ease-in-out infinite;
		transform-origin: center bottom;
		will-change: transform, filter;
		transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), filter 0.4s ease;
	}

	/* ============ REWARD CARDS ============ */
	.community-page-wrapper .reward-card {
		position: relative;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 24px;
		overflow: hidden;
		transition: transform .28s cubic-bezier(.16,1,.3,1), box-shadow .28s ease, border-color .28s ease;
	}
	.community-page-wrapper .reward-card::before {
		content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
		background: linear-gradient(90deg, #FF9900, #ffaa2b, #9353ff);
		transform: scaleX(0); transform-origin: left;
		transition: transform .35s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .reward-card:hover {
		transform: translateY(-6px);
		border-color: rgba(255,153,0,0.45) !important;
		box-shadow: 0 22px 46px -16px rgba(255,153,0,0.25);
	}
	.community-page-wrapper .reward-card:hover::before { transform: scaleX(1); }
	.community-page-wrapper .reward-icon {
		width: 52px; height: 52px; border-radius: 16px;
		display: flex; align-items: center; justify-content: center;
		background: rgba(255,153,0,0.12);
		border: 1px solid rgba(255,153,0,0.25);
		color: #FF9900;
		transition: all .28s ease;
	}
	.community-page-wrapper .reward-card:hover .reward-icon {
		background: #FF9900;
		color: #020617;
		transform: rotate(-6deg) scale(1.08);
	}
	.community-page-wrapper .reward-value {
		font-size: 28px; font-weight: 900; letter-spacing: -0.02em;
		color: #020617 !important; line-height: 1.1;
	}

	/* ============ DEEP DARK AWS CONTAINER FOR JOURNEY STEPS ============ */
	.community-page-wrapper .cp-dark-journey-section {
		position: relative;
		background: linear-gradient(180deg, #070913 0%, #0c0f24 100%) !important;
		color: #ffffff !important;
		border-radius: 32px;
		overflow: hidden;
		box-shadow: 0 30px 80px -20px rgba(7, 9, 19, 0.5);
		padding: 32px;
		width: 100%;
	}
	@media (min-width: 640px) {
		.community-page-wrapper .cp-dark-journey-section { padding: 48px; }
	}

	.community-page-wrapper .journey-item {
		position: relative;
		background: rgba(15, 23, 42, 0.75) !important;
		backdrop-filter: blur(14px);
		-webkit-backdrop-filter: blur(14px);
		border: 1px solid rgba(255, 255, 255, 0.12);
		border-radius: 22px;
		transition: all .28s ease;
	}
	.community-page-wrapper .journey-item:hover {
		border-color: rgba(255,153,0,0.5) !important;
		box-shadow: 0 20px 42px -16px rgba(255,153,0,0.30);
		transform: translateX(6px);
	}
	.community-page-wrapper .journey-num {
		width: 48px; height: 48px; border-radius: 16px;
		display: flex; align-items: center; justify-content: center;
		background: linear-gradient(135deg, #FF9900, #ffaa2b) !important;
		color: #020617 !important;
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-weight: 900; font-size: 17px;
		flex-shrink: 0;
		box-shadow: 0 8px 18px -6px rgba(255,153,0,0.4);
		position: relative; z-index: 1;
	}
	.community-page-wrapper .journey-connector {
		display: none;
		position: absolute; left: 23px; top: 60px; bottom: -24px;
		width: 2px;
		background: linear-gradient(180deg, #FF9900, rgba(255,153,0,0.1));
	}
	@media (min-width: 640px) {
		.community-page-wrapper .journey-connector { display: block; }
	}

	.community-page-wrapper .step-chip {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 5px 12px;
		border-radius: 10px;
		background: rgba(255, 153, 0, 0.12);
		border: 1px solid rgba(255, 153, 0, 0.3);
		color: #ffaa2b;
		font-size: 12px;
		font-weight: 700;
	}

	/* ============ CHANNEL CARDS ============ */
	.community-page-wrapper .channel-card {
		position: relative;
		display: flex; flex-direction: column;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 24px;
		overflow: hidden;
		transition: all .28s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .channel-card::after {
		content: ""; position: absolute; inset: 0;
		background: linear-gradient(135deg, rgba(255,153,0,0.08), rgba(147,83,255,0.05));
		opacity: 0; transition: opacity .28s ease; pointer-events: none;
	}
	.community-page-wrapper .channel-card:hover {
		transform: translateY(-6px);
		border-color: rgba(255,153,0,0.5) !important;
		box-shadow: 0 24px 48px -18px rgba(255,153,0,0.28);
	}
	.community-page-wrapper .channel-card:hover::after { opacity: 1; }
	.community-page-wrapper .channel-icon {
		width: 54px; height: 54px; border-radius: 16px;
		display: flex; align-items: center; justify-content: center;
		background: #020617 !important; color: #ffffff;
		transition: all .28s ease;
		box-shadow: 0 10px 22px -8px rgba(2,6,23,0.45);
	}
	.community-page-wrapper .channel-card:hover .channel-icon {
		background: linear-gradient(135deg, #FF9900, #ffaa2b) !important;
		color: #020617;
		transform: rotate(-8deg) scale(1.08);
	}
	.community-page-wrapper .channel-foot {
		margin-top: auto;
		display: flex; align-items: center; justify-content: space-between;
		padding-top: 16px; border-top: 1px solid #f1f5f9;
		font-size: 13px; font-weight: 800; letter-spacing: 0.04em;
		color: #FF9900 !important;
	}
	.community-page-wrapper .arrow-slide { transition: transform .25s ease; }
	.community-page-wrapper .channel-card:hover .arrow-slide { transform: translateX(5px); }

	/* ============ FAQ ACCORDION (FULL WIDTH ALIGNED) ============ */
	.community-page-wrapper .faq-section-wrapper {
		width: 100%;
		padding: 88px 0 96px;
	}
	.community-page-wrapper .faq-list {
		width: 100% !important;
		max-width: 100% !important;
		padding-top: 36px;
		display: flex;
		flex-direction: column;
		gap: 14px;
	}
	.community-page-wrapper .faq-item {
		width: 100% !important;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 20px;
		overflow: hidden;
		transition: all .25s ease;
		box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.03);
	}
	.community-page-wrapper .faq-item:hover {
		border-color: rgba(255,153,0,0.4) !important;
		box-shadow: 0 8px 24px -6px rgba(255,153,0,0.12);
	}
	.community-page-wrapper .faq-item.open {
		border-color: #FF9900 !important;
		border-left-width: 4px;
		border-left-color: #FF9900 !important;
		box-shadow: 0 14px 34px -16px rgba(255,153,0,0.22);
	}
	.community-page-wrapper .faq-q {
		width: 100%; text-align: left;
		background: transparent; border: none; cursor: pointer;
		display: flex; align-items: center; justify-content: space-between; gap: 16px;
		padding: 22px 26px;
		font-weight: 800; font-size: 16.5px; color: #0f172a;
		font-family: inherit;
		outline: none;
	}
	.community-page-wrapper .faq-q:hover span:first-child { color: #cc7a00; }
	.community-page-wrapper .faq-q span:first-child { transition: color .2s ease; }
	.community-page-wrapper .faq-toggle {
		width: 34px; height: 34px; border-radius: 10px;
		display: flex; align-items: center; justify-content: center;
		background: rgba(255,153,0,0.12); color: #FF9900;
		font-family: ui-monospace, monospace; font-weight: 900; font-size: 18px;
		flex-shrink: 0; transition: all .3s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .faq-item.open .faq-toggle {
		background: linear-gradient(135deg, #FF9900, #ffaa2b);
		color: #020617;
		transform: rotate(135deg);
	}
	.community-page-wrapper .faq-a {
		max-height: 0; overflow: hidden;
		transition: max-height .35s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .faq-a-inner {
		padding: 0 26px 22px;
		font-size: 14.5px; line-height: 1.75; color: #475569;
		border-top: 1px dashed rgba(255,153,0,0.2);
		padding-top: 16px;
	}

	/* ============ SCROLL REVEAL ============ */
	.community-page-wrapper .reveal {
		opacity: 0; transform: translateY(26px);
		transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .reveal.in { opacity: 1; transform: translateY(0); }
	.community-page-wrapper .reveal-d1 { transition-delay: .08s; }
	.community-page-wrapper .reveal-d2 { transition-delay: .16s; }
	.community-page-wrapper .reveal-d3 { transition-delay: .24s; }

	/* ============ GRID HELPERS ============ */
	.community-page-wrapper .cp-grid { display: grid; gap: 24px; }
	@media (min-width: 640px)  { .community-page-wrapper .cp-grid-3 { grid-template-columns: repeat(3, 1fr); } }
	@media (min-width: 1024px) { .community-page-wrapper .cp-hero-split { grid-template-columns: 1.05fr 0.95fr; align-items: center; gap: 56px; } }

	.community-page-wrapper .cp-stats-grid-3 {
		display: grid; grid-template-columns: repeat(3, 1fr);
		gap: 16px; padding-top: 24px;
	}
	.community-page-wrapper .cp-stat-item-3 {
		padding: 0 12px;
	}
	.community-page-wrapper .cp-stat-item-3 + .cp-stat-item-3 {
		border-left: 1px solid #e2e8f0;
	}

	@media (max-width: 639px) {
		.community-page-wrapper .cp-stats-grid-3 {
			grid-template-columns: 1fr;
			gap: 14px;
		}
		.community-page-wrapper .cp-stat-item-3 {
			padding: 8px 0;
		}
		.community-page-wrapper .cp-stat-item-3 + .cp-stat-item-3 {
			border-left: none !important;
			border-top: 1px solid #e2e8f0;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.community-page-wrapper * { animation: none !important; transition: none !important; }
		.community-page-wrapper .reveal { opacity: 1; transform: none; }
	}
</style>

<div class="community-page-wrapper" style="background:#ffffff;">

	<!-- ========================================================================= -->
	<!-- HERO: JOIN THE MOVEMENT (MULTI-COLOR AURORA & CLEAN TYPOGRAPHY)          -->
	<!-- ========================================================================= -->
	<header class="aurora-canvas" style="position:relative; overflow:hidden; background:#ffffff; border-b:1px solid #f1f5f9;">
		<!-- Multi-Color Aurora Glow Spots -->
		<div class="aurora-blob aurora-purple"></div>
		<div class="aurora-blob aurora-orange"></div>
		<div class="aurora-blob aurora-green"></div>

		<!-- Architectural Gridlines -->
		<div class="cp-grid-lines"></div>

		<div style="max-width:1120px; margin:0 auto; padding:72px 20px 64px; position:relative; z-index:10;">
			<div class="cp-grid cp-hero-split">

				<!-- Copy Column -->
				<div class="space-y-6 reveal">
					
					<h1 style="font-size:clamp(38px,6vw,66px); font-weight:900; letter-spacing:-0.03em; line-height:1.02; text-transform:uppercase;">
						LET'S <span class="cp-text-highlight-orange">BUILD.</span><br>
						TOGETHER<span class="text-[#9353ff]">.</span>
					</h1>

					<p style="font-size:16px; line-height:1.75; color:#475569 !important; max-width:520px;">
						AWS Student Builder Group at Air University Islamabad — the official AWS student community active in <strong style="color:#0f172a;">60+ countries</strong> across <strong style="color:#0f172a;">800+ campuses</strong>. Get hands-on with Cloud &amp; AI, earn badges, and unlock real AWS rewards.
					</p>

					<div style="display:flex; flex-wrap:wrap; gap:14px; padding-top:4px;">
						<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="btn-launch">
							Become Member
							<span class="arrow-slide">→</span>
						</a>
						<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="btn-ghost">
							<svg class="w-4 h-4 text-[#25D366] shrink-0" fill="currentColor" viewBox="0 0 24 24">
								<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
							</svg>
							<span>WhatsApp Community</span>
						</a>
					</div>

					<!-- 3 Quick Highlights Grid -->
					<div class="cp-stats-grid-3">
						<div class="cp-stat-item-3">
							<div style="font-size:24px; font-weight:900; color:#020617; line-height:1.1;">AWS</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Backed Community</div>
						</div>
						<div class="cp-stat-item-3">
							<div style="font-size:24px; font-weight:900; color:#020617; line-height:1.1;">Global</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Recognition</div>
						</div>
						<div class="cp-stat-item-3">
							<div style="font-size:24px; font-weight:900; color:#020617; line-height:1.1;">Swag</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Giveaways</div>
						</div>
					</div>
				</div>

				<!-- Visual Column with Ambient Glow -->
				<div class="reveal reveal-d1 relative flex items-center justify-center pt-8 lg:pt-0">
					<!-- Pulsing Halo -->
					<div class="absolute inset-0 bg-gradient-to-tr from-[#FF9900]/25 via-[#9353ff]/22 to-[#00ef88]/20 rounded-full blur-3xl pointer-events-none"></div>

					<?php
$swag_kit_url = file_exists( get_theme_file_path( 'assets/images/AWS-Swags-Kit.webp' ) )
					? get_theme_file_uri( 'assets/images/AWS-Swags-Kit.webp' )
						: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';
					?>
					<img src="<?php echo esc_url( $swag_kit_url ); ?>"
					     onerror="this.onerror=null;this.src='https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';"
					     alt="Official AWS Student Builder Group Member Swags Kit"
					     class="sbg-hero-swag-anim relative z-10"
					     style="width:100%; max-width:480px; height:auto; object-fit:contain; user-select:none;"
					     loading="eager">
				</div>

			</div>
		</div>
	</header>

	<main style="max-width:1120px; margin:0 auto; padding:0 20px;">

		<!-- ========================================================================= -->
		<!-- REWARDS SECTION (LIGHT SURFACE `#f8fafc` WITH ACCENT CARDS)               -->
		<!-- ========================================================================= -->
		<section id="rewards" style="padding:88px 0 0; width:100%;">
			<div class="reveal max-w-2xl">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase;">
					Claim Your <span class="grad-text-orange">Welcome Rewards</span>
				</h2>
				<p style="padding-top:10px; font-size:15px; line-height:1.75; color:#475569 !important;">
					Air University students unlock premium training, real cloud credits for projects, and a certification voucher.
				</p>
			</div>

			<div class="cp-grid cp-grid-3" style="padding-top:36px;">
				<!-- Reward 1 -->
				<div class="reward-card reveal p-7 space-y-4">
					<div class="reward-icon">
						<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
					</div>
					<div>
						<div class="reward-value">12 Months</div>
						<h3 style="font-weight:800; font-size:16.5px; color:#0f172a !important; padding-top:4px;">Skill Builder Premium</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							900+ official hands-on labs, courses, and practice exams — free for a full year.
						</p>
					</div>
				</div>

				<!-- Reward 2 -->
				<div class="reward-card reveal reveal-d1 p-7 space-y-4">
					<div class="reward-icon">
						<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
					</div>
					<div>
						<div class="reward-value">$30 AWS</div>
						<h3 style="font-weight:800; font-size:16.5px; color:#0f172a !important; padding-top:4px;">Cloud Credits</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Build and host your semester and final-year projects on real AWS infrastructure.
						</p>
					</div>
				</div>

				<!-- Reward 3 -->
				<div class="reward-card reveal reveal-d2 p-7 space-y-4">
					<div class="reward-icon">
						<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/></svg>
					</div>
					<div>
						<div class="reward-value">Free Exam</div>
						<h3 style="font-weight:800; font-size:16.5px; color:#0f172a !important; padding-top:4px;">Certification Voucher</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Official Pearson VUE voucher for Cloud Practitioner or AI Practitioner.
						</p>
					</div>
				</div>
			</div>

			<div class="reveal reveal-d1 flex flex-wrap items-center gap-4" style="padding-top:36px;">
				<a href="https://builder.aws.com/start?trk=8e356433-7e2c-4ee9-8c3a-5bccc682a4f5&amp;sc_channel=el" target="_blank" rel="noopener noreferrer" class="btn-orange">
					Claim Student Rewards <span class="arrow-slide">→</span>
				</a>
			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- JOURNEY STEPS (DEEP DARK CONTAINER SECTION SHIFT)                         -->
		<!-- ========================================================================= -->
		<section id="journey" style="padding:88px 0 0; width:100%;">
			<div class="cp-dark-journey-section">
				<div class="reveal max-w-2xl">
					<h3 style="font-weight:900; font-size:clamp(28px,4vw,44px); text-transform:uppercase; letter-spacing:-0.02em; color:#ffffff !important;">
						Your Journey <span class="grad-text-orange">in 3 Steps</span>
					</h3>
					<p style="padding-top:8px; font-size:15px; color:#94a3b8 !important;">
						Follow these quick steps on Builder Center to unlock your student benefits.
					</p>
				</div>

				<div style="padding-top:36px; position:relative;">
					<div class="space-y-6">

						<!-- Step 1 -->
						<div class="journey-item reveal flex gap-6 p-6 sm:p-7">
							<div class="journey-connector"></div>
							<div class="journey-num">01</div>
							<div>
								<h4 style="font-weight:800; font-size:17px; color:#ffffff !important;">Verify Student Status</h4>
								<p style="font-size:14px; line-height:1.75; color:#94a3b8 !important; padding-top:6px;">
									Sign in to Builder Center and verify via SheerID with your name, institution, and student email. Most verifications finish in minutes.
								</p>
							</div>
						</div>

						<!-- Step 2 -->
						<div class="journey-item reveal reveal-d1 flex gap-6 p-6 sm:p-7">
							<div class="journey-connector"></div>
							<div class="journey-num">02</div>
							<div>
								<h4 style="font-weight:800; font-size:17px; color:#ffffff !important;">Complete Your Profile</h4>
								<p style="font-size:14px; line-height:1.75; color:#94a3b8 !important; padding-top:6px;">
									Add a photo and fill your About section — this instantly unlocks <strong style="color:#ffffff;">12 months of Skill Builder Premium</strong>.
								</p>
							</div>
						</div>

						<!-- Step 3 -->
						<div class="journey-item reveal reveal-d2 flex gap-6 p-6 sm:p-7">
							<div class="journey-num">03</div>
							<div>
								<h4 style="font-weight:800; font-size:17px; color:#ffffff !important;">Earn Badges, Unlock More</h4>
								<p style="font-size:14px; line-height:1.75; color:#94a3b8 !important; padding-top:6px;">
									Publish articles and stay engaged on Builder Center. The more badges you earn, the bigger the reward:
								</p>
								<div class="flex flex-wrap gap-2 pt-3">
									<span class="step-chip">7 Badges: $10 Credits</span>
									<span class="step-chip">14 Badges: +$20 Credits</span>
									<span class="step-chip">21 Badges: $100 Exam Voucher</span>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- CHANNELS SECTION                                                         -->
		<!-- ========================================================================= -->
		<section id="channels" style="padding:88px 0 0; width:100%;">
			<div class="reveal max-w-2xl">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase;">
					Get <span class="grad-text-orange">Connected</span>
				</h2>
				<p style="padding-top:8px; font-size:15px; line-height:1.75; color:#475569 !important;">
					Join our active student channels to collaborate, ask questions, and get instant updates.
				</p>
			</div>

			<div class="cp-grid cp-grid-3" style="padding-top:36px;">

				<!-- WhatsApp -->
				<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="channel-card reveal p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="https://awsairuniversity.com/wp-content/uploads/2026/09/wired-outline-2672-logo-square-whatsapp.png" alt="WhatsApp" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">WhatsApp</h3>
						<p style="font-size:14px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Ask questions, get cloud lab help, and receive instant event alerts from peers and leads.
						</p>
					</div>
					<div class="channel-foot"><span>Join WhatsApp Community</span><span class="arrow-slide">→</span></div>
				</a>

				<!-- LinkedIn -->
				<a href="https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/" target="_blank" rel="noopener noreferrer" class="channel-card reveal reveal-d1 p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="https://awsairuniversity.com/wp-content/uploads/2026/09/wired-outline-2677-logo-square-linkedin.png" alt="LinkedIn" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">LinkedIn</h3>
						<p style="font-size:14px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Celebrate certifications, follow student wins, and connect with alumni in industry.
						</p>
					</div>
					<div class="channel-foot"><span>Follow on LinkedIn</span><span class="arrow-slide">→</span></div>
				</a>

				<!-- Instagram -->
				<a href="https://www.instagram.com/awssbgairuniversity/" target="_blank" rel="noopener noreferrer" class="channel-card reveal reveal-d2 p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="https://awsairuniversity.com/wp-content/uploads/2026/09/wired-outline-2542-logo-instagram.png" alt="Instagram" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">Instagram</h3>
						<p style="font-size:14px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Event reels, workshop recaps, speaker stories, and campus moments as they happen.
						</p>
					</div>
					<div class="channel-foot"><span>Follow @awssbgairuniversity</span><span class="arrow-slide">→</span></div>
				</a>

			</div>
		</section>

		<!-- ========================================================================= -->
		<!-- FAQ SECTION (FULL-WIDTH ALIGNED WITH OTHER SECTIONS)                      -->
		<!-- ========================================================================= -->
		<section id="faq" class="faq-section-wrapper">
			<div class="reveal w-full">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase;">
					Questions? <span class="grad-text-orange">Answered.</span>
				</h2>
			</div>

			<div class="faq-list">

				<div class="faq-item reveal">
					<button type="button" class="faq-q" onclick="toggleFaq(this)">
						<span>Who is eligible for these student rewards?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Any currently enrolled higher-education student at an accredited university or college is eligible. Verification is completed through SheerID.
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d1">
					<button type="button" class="faq-q" onclick="toggleFaq(this)">
						<span>Is a credit card required to claim rewards?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						No credit card is required. Student verification only needs your university email address or active enrollment proof via SheerID.
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d2">
					<button type="button" class="faq-q" onclick="toggleFaq(this)">
						<span>How long does SheerID verification take?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Verification is typically instant with your official university email. If manual document review is needed, it usually completes within 24–48 hours.
					</div></div>
				</div>

				<div class="faq-item reveal">
					<button type="button" class="faq-q" onclick="toggleFaq(this)">
						<span>Which certifications are covered by the exam voucher?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						The reward grants a 100% discount voucher for either AWS Certified Cloud Practitioner (CLF-C02) or AWS Certified AI Practitioner (AIF-C01).
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d1">
					<button type="button" class="faq-q" onclick="toggleFaq(this)">
						<span>What if I get stuck during verification?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Join our WhatsApp community group above — student leads and peers are active daily to walk you through every step.
					</div></div>
				</div>

			</div>
		</section>

	</main>
</div>

<script>
	/* FAQ accordion — smooth height animation */
	function toggleFaq(btn) {
		const item   = btn.closest('.faq-item');
		const answer = item.querySelector('.faq-a');
		const isOpen = item.classList.contains('open');

		// Close all
		document.querySelectorAll('.community-page-wrapper .faq-item.open').forEach(function(el) {
			el.classList.remove('open');
			el.querySelector('.faq-a').style.maxHeight = null;
			el.querySelector('.faq-toggle').textContent = '+';
		});

		if (!isOpen) {
			item.classList.add('open');
			answer.style.maxHeight = answer.scrollHeight + 'px';
			item.querySelector('.faq-toggle').textContent = '−';
		}
	}

	/* Scroll reveal */
	(function () {
		const targets = document.querySelectorAll('.community-page-wrapper .reveal');
		if (!('IntersectionObserver' in window)) {
			targets.forEach(function (t) { t.classList.add('in'); });
			return;
		}
		const io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('in');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12 });
		targets.forEach(function (t) { io.observe(t); });
	})();
</script>

<?php
get_footer();