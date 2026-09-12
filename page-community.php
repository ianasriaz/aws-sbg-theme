<?php
/**
 * Template Name: Community Hub & Student Rewards
 *
 * Fresh "Launchpad" concept — bold hero, aurora gradient, stats ticker,
 * journey steps, reward drops, channel cards, glassy FAQ.
 * Palette locked: black #020617, charcoal #0f172a/#1e293b, white, brand purple #9353ff.
 *
 * @package AWS_SBG_Theme
 */

get_header();
?>

<!-- Scoped styles: zero conflict with theme style.css dark defaults -->
<style>
	/* ============ SCOPE & BASE ============ */
	.community-page-wrapper {
		background-color: #ffffff !important;
		color: #0f172a !important;
		font-family: inherit;
	}
	.community-page-wrapper h1,
	.community-page-wrapper h2,
	.community-page-wrapper h3,
	.community-page-wrapper h4 { color: #020617 !important; margin: 0; }
	.community-page-wrapper p  { color: #334155 !important; margin: 0; }
	.community-page-wrapper a  { text-decoration: none !important; }
	.community-page-wrapper *  { box-sizing: border-box; }

	/* ============ BRAND TEXT & GRADIENTS ============ */
	.community-page-wrapper .text-brand  { color: #9353ff !important; }
	.community-page-wrapper .grad-text {
		background: linear-gradient(92deg, #7928ca 0%, #9353ff 55%, #b47bff 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}
	.community-page-wrapper .grad-fill {
		background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%) !important;
	}
	.community-page-wrapper .grad-line {
		background: linear-gradient(90deg, #9353ff, rgba(147,83,255,0)) !important;
	}
	.community-page-wrapper .grad-soft {
		background: linear-gradient(135deg, rgba(147,83,255,0.10), rgba(121,40,202,0.04)) !important;
	}

	/* ============ AURORA BLOBS ============ */
	.community-page-wrapper .aurora {
		position: absolute;
		border-radius: 9999px;
		filter: blur(70px);
		pointer-events: none;
		will-change: transform;
	}
	.community-page-wrapper .aurora-1 {
		width: 520px; height: 520px;
		top: -180px; right: -140px;
		background: radial-gradient(circle, rgba(147,83,255,0.30), transparent 65%);
		animation: auroraFloat 11s ease-in-out infinite;
	}
	.community-page-wrapper .aurora-2 {
		width: 420px; height: 420px;
		bottom: -160px; left: -160px;
		background: radial-gradient(circle, rgba(121,40,202,0.22), transparent 65%);
		animation: auroraFloat 13s ease-in-out infinite reverse;
	}
	@keyframes auroraFloat {
		0%,100% { transform: translate(0,0) scale(1); }
		50%     { transform: translate(26px,-22px) scale(1.07); }
	}

	/* ============ BUTTONS ============ */
	.community-page-wrapper .btn-launch {
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
		font-size: 14px;
		letter-spacing: 0.02em;
		box-shadow: 0 10px 26px -6px rgba(147,83,255,0.45);
		transition: transform .22s cubic-bezier(.16,1,.3,1), box-shadow .22s ease;
		overflow: hidden;
	}
	.community-page-wrapper .btn-launch::after {
		content: "";
		position: absolute; inset: 0;
		background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,0.35) 50%, transparent 65%);
		transform: translateX(-120%);
		transition: transform .6s ease;
	}
	.community-page-wrapper .btn-launch:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 34px -6px rgba(147,83,255,0.55);
	}
	.community-page-wrapper .btn-launch:hover::after { transform: translateX(120%); }
	.community-page-wrapper .btn-launch:active { transform: translateY(0); }

	.community-page-wrapper .btn-ghost {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 8px;
		padding: 14px 26px;
		border-radius: 16px;
		border: 1.5px solid #e2e8f0 !important;
		background: #ffffff !important;
		color: #0f172a !important;
		font-weight: 700;
		font-size: 14px;
		transition: all .22s ease;
	}
	.community-page-wrapper .btn-ghost:hover {
		border-color: #9353ff !important;
		color: #9353ff !important;
		box-shadow: 0 8px 20px -8px rgba(147,83,255,0.30);
		transform: translateY(-2px);
	}

	/* ============ TICKER ============ */
	.community-page-wrapper .ticker-wrap {
		overflow: hidden;
		border-top: 1px solid #e9e4f8;
		border-bottom: 1px solid #e9e4f8;
		background: linear-gradient(90deg, rgba(147,83,255,0.05), rgba(121,40,202,0.03));
	}
	.community-page-wrapper .ticker-track {
		display: flex;
		width: max-content;
		animation: tickerScroll 26s linear infinite;
	}
	.community-page-wrapper .ticker-track:hover { animation-play-state: paused; }
	@keyframes tickerScroll {
		from { transform: translateX(0); }
		to   { transform: translateX(-50%); }
	}
	.community-page-wrapper .ticker-item {
		display: inline-flex;
		align-items: center;
		gap: 12px;
		padding: 14px 28px;
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.14em;
		text-transform: uppercase;
		color: #1e293b !important;
		white-space: nowrap;
	}
	.community-page-wrapper .ticker-dot {
		width: 7px; height: 7px; border-radius: 99px;
		background: #9353ff;
		box-shadow: 0 0 0 4px rgba(147,83,255,0.15);
	}

	/* ============ SWAG SHOWCASE ============ */
	.community-page-wrapper .swag-card {
		position: relative;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 28px;
		overflow: hidden;
		box-shadow: 0 24px 60px -20px rgba(15,23,42,0.18);
		transform: rotate(0deg);
		transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease;
	}
	.community-page-wrapper .swag-card:hover {
		transform: rotate(-1.2deg) translateY(-4px);
		box-shadow: 0 32px 70px -18px rgba(147,83,255,0.35);
	}
	.community-page-wrapper .swag-card img {
		display: block; width: 100%; height: auto;
		transition: transform .6s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .swag-card:hover img { transform: scale(1.03); }

	/* ============ PILLS / TAGS ============ */
	.community-page-wrapper .pill {
		display: inline-flex; align-items: center; gap: 7px;
		padding: 7px 15px; border-radius: 99px;
		border: 1px solid rgba(147,83,255,0.35);
		background: rgba(147,83,255,0.07);
		color: #7928ca !important;
		font-size: 11px; font-weight: 800;
		letter-spacing: 0.12em; text-transform: uppercase;
	}
	.community-page-wrapper .pill .live-dot {
		width: 7px; height: 7px; border-radius: 99px;
		background: #9353ff;
		animation: pulseDot 1.8s ease-in-out infinite;
	}
	@keyframes pulseDot {
		0%,100% { box-shadow: 0 0 0 0 rgba(147,83,255,0.45); }
		50%     { box-shadow: 0 0 0 6px rgba(147,83,255,0); }
	}

	/* ============ SECTION LABEL ============ */
	.community-page-wrapper .sec-label {
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-size: 11px; font-weight: 800;
		letter-spacing: 0.22em; text-transform: uppercase;
		color: #9353ff !important;
	}

	/* ============ REWARD CARDS ============ */
	.community-page-wrapper .reward-card {
		position: relative;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 22px;
		overflow: hidden;
		transition: transform .28s cubic-bezier(.16,1,.3,1), box-shadow .28s ease, border-color .28s ease;
	}
	.community-page-wrapper .reward-card::before {
		content: "";
		position: absolute; top: 0; left: 0; right: 0; height: 5px;
		background: linear-gradient(90deg, #7928ca, #9353ff, #b47bff);
		transform: scaleX(0);
		transform-origin: left;
		transition: transform .35s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .reward-card:hover {
		transform: translateY(-6px);
		border-color: rgba(147,83,255,0.45) !important;
		box-shadow: 0 22px 46px -16px rgba(147,83,255,0.30);
	}
	.community-page-wrapper .reward-card:hover::before { transform: scaleX(1); }
	.community-page-wrapper .reward-icon {
		width: 52px; height: 52px; border-radius: 15px;
		display: flex; align-items: center; justify-content: center;
		background: linear-gradient(135deg, rgba(147,83,255,0.14), rgba(121,40,202,0.08));
		border: 1px solid rgba(147,83,255,0.25);
		color: #9353ff;
		transition: all .28s ease;
	}
	.community-page-wrapper .reward-card:hover .reward-icon {
		background: linear-gradient(135deg, #7928ca, #9353ff);
		color: #ffffff;
		transform: rotate(-6deg) scale(1.06);
	}
	.community-page-wrapper .reward-value {
		font-size: 28px; font-weight: 900; letter-spacing: -0.02em;
		color: #020617 !important; line-height: 1.1;
	}

	/* ============ JOURNEY STEPS ============ */
	.community-page-wrapper .journey-item {
		position: relative;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 20px;
		transition: all .28s ease;
	}
	.community-page-wrapper .journey-item:hover {
		border-color: rgba(147,83,255,0.45) !important;
		box-shadow: 0 18px 40px -18px rgba(147,83,255,0.28);
		transform: translateX(6px);
	}
	.community-page-wrapper .journey-num {
		width: 46px; height: 46px; border-radius: 14px;
		display: flex; align-items: center; justify-content: center;
		background: #020617 !important; color: #ffffff !important;
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
		font-weight: 800; font-size: 16px;
		flex-shrink: 0;
		box-shadow: 0 8px 18px -6px rgba(2,6,23,0.4);
		position: relative; z-index: 1;
	}
	.community-page-wrapper .journey-num::after {
		content: "";
		position: absolute; inset: -5px;
		border-radius: 18px;
		border: 1.5px dashed rgba(147,83,255,0.5);
		animation: spinRing 14s linear infinite;
	}
	@keyframes spinRing {
		from { transform: rotate(0deg); }
		to   { transform: rotate(360deg); }
	}
	.community-page-wrapper .journey-connector {
		display: none;
		position: absolute; left: 22px; top: 60px; bottom: -24px;
		width: 2px;
		background: linear-gradient(180deg, #9353ff, rgba(147,83,255,0.08));
	}
	@media (min-width: 640px) {
		.community-page-wrapper .journey-connector { display: block; }
	}
	.community-page-wrapper .badge-row {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		padding-top: 12px;
	}
	.community-page-wrapper .badge-tag {
		display: inline-flex; align-items: center;
		font-size: 10px; font-weight: 800;
		letter-spacing: 0.08em;
		padding: 4px 10px; border-radius: 8px;
		background: rgba(147,83,255,0.10);
		color: #7928ca !important;
		border: 1px solid rgba(147,83,255,0.25);
		font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
	}

	/* ============ CHANNEL CARDS ============ */
	.community-page-wrapper .channel-card {
		position: relative;
		display: flex; flex-direction: column;
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 22px;
		overflow: hidden;
		transition: all .28s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .channel-card::after {
		content: "";
		position: absolute; inset: 0;
		background: linear-gradient(135deg, rgba(147,83,255,0.10), rgba(121,40,202,0.05));
		opacity: 0; transition: opacity .28s ease;
		pointer-events: none;
	}
	.community-page-wrapper .channel-card:hover {
		transform: translateY(-6px);
		border-color: rgba(147,83,255,0.5) !important;
		box-shadow: 0 24px 48px -18px rgba(147,83,255,0.32);
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
		background: linear-gradient(135deg, #7928ca, #9353ff) !important;
		transform: rotate(-8deg) scale(1.08);
	}
	.community-page-wrapper .channel-foot {
		margin-top: auto;
		display: flex; align-items: center; justify-content: space-between;
		padding-top: 16px; border-top: 1px solid #f1f5f9;
		font-size: 12px; font-weight: 800; letter-spacing: 0.04em;
		color: #9353ff !important;
	}
	.community-page-wrapper .arrow-slide { transition: transform .25s ease; }
	.community-page-wrapper .channel-card:hover .arrow-slide { transform: translateX(5px); }

	/* ============ FAQ ============ */
	.community-page-wrapper .faq-item {
		background: #ffffff !important;
		border: 1px solid #e2e8f0;
		border-radius: 18px;
		overflow: hidden;
		transition: all .25s ease;
	}
	.community-page-wrapper .faq-item.open {
		border-color: rgba(147,83,255,0.5) !important;
		box-shadow: 0 14px 34px -16px rgba(147,83,255,0.28);
	}
	.community-page-wrapper .faq-q {
		width: 100%; text-align: left;
		background: transparent; border: none; cursor: pointer;
		display: flex; align-items: center; justify-content: space-between; gap: 16px;
		padding: 18px 22px;
		font-weight: 700; font-size: 15px; color: #0f172a;
		font-family: inherit;
	}
	.community-page-wrapper .faq-q:hover span:first-child { color: #7928ca; }
	.community-page-wrapper .faq-q span:first-child { transition: color .2s ease; }
	.community-page-wrapper .faq-toggle {
		width: 30px; height: 30px; border-radius: 10px;
		display: flex; align-items: center; justify-content: center;
		background: rgba(147,83,255,0.10); color: #9353ff;
		font-family: ui-monospace, monospace; font-weight: 800; font-size: 16px;
		flex-shrink: 0; transition: all .3s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .faq-item.open .faq-toggle {
		background: linear-gradient(135deg, #7928ca, #9353ff);
		color: #ffffff;
		transform: rotate(135deg);
	}
	.community-page-wrapper .faq-a {
		max-height: 0; overflow: hidden;
		transition: max-height .35s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .faq-a-inner {
		padding: 0 22px 20px;
		font-size: 14px; line-height: 1.7; color: #475569;
		border-top: 1px dashed #e9e4f8;
		padding-top: 14px;
		margin: 0 22px 20px; padding-left: 0; padding-right: 0;
	}

	/* ============ SCROLL REVEAL ============ */
	.community-page-wrapper .reveal {
		opacity: 0;
		transform: translateY(26px);
		transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1);
	}
	.community-page-wrapper .reveal.in { opacity: 1; transform: translateY(0); }
	.community-page-wrapper .reveal-d1 { transition-delay: .08s; }
	.community-page-wrapper .reveal-d2 { transition-delay: .16s; }
	.community-page-wrapper .reveal-d3 { transition-delay: .24s; }

	/* ============ GRID HELPERS (no-Tailwind-safe fallbacks) ============ */
	.community-page-wrapper .cp-grid { display: grid; gap: 20px; }
	@media (min-width: 640px)  { .community-page-wrapper .cp-grid-3 { grid-template-columns: repeat(3, 1fr); } }
	@media (min-width: 1024px) { .community-page-wrapper .cp-hero-split { grid-template-columns: 1.05fr 0.95fr; align-items: center; gap: 56px; } }

	/* Hero Highlights: 4 in a row on desktop, 2 on first & 2 on second on mobile */
	.community-page-wrapper .cp-stats-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 20px 0;
		padding-top: 18px;
	}
	.community-page-wrapper .cp-stat-item {
		padding-right: 14px;
	}
	.community-page-wrapper .cp-stat-item:nth-child(2n) {
		padding-left: 16px;
		padding-right: 0;
		border-left: 1px solid #e2e8f0;
	}
	@media (min-width: 640px) {
		.community-page-wrapper .cp-stats-grid {
			grid-template-columns: repeat(4, 1fr);
			gap: 0;
		}
		.community-page-wrapper .cp-stat-item {
			padding: 0 14px 0 0;
			border-left: none;
		}
		.community-page-wrapper .cp-stat-item + .cp-stat-item {
			padding-left: 16px;
			border-left: 1px solid #e2e8f0;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.community-page-wrapper * { animation: none !important; transition: none !important; }
		.community-page-wrapper .reveal { opacity: 1; transform: none; }
	}
</style>

<div class="community-page-wrapper" style="background:#ffffff;">

	<!-- ================= HERO: JOIN THE MOVEMENT ================= -->
	<header style="position:relative; overflow:hidden; background:#ffffff;">
		<div class="aurora aurora-1"></div>
		<div class="aurora aurora-2"></div>

		<div style="max-width:1120px; margin:0 auto; padding:72px 20px 56px; position:relative;">
			<div class="cp-grid cp-hero-split">

				<!-- Copy -->
				<div class="space-y-6 reveal">
					<h1 style="font-size:clamp(38px,6vw,66px); font-weight:900; letter-spacing:-0.03em; line-height:1.02; text-transform:uppercase;">
						<span class="grad-text">Let's Build</span><br>
						Together
					</h1>

					<p style="font-size:16px; line-height:1.75; color:#475569 !important; max-width:520px;">
						AWS Student Builder Group at Air University Islamabad — the official AWS student community active in <strong style="color:#0f172a;">60+ countries</strong> across <strong style="color:#0f172a;">800+ campuses</strong>. Get hands-on with Cloud &amp; AI, earn badges, and unlock real AWS rewards.
					</p>

					<div style="display:flex; flex-wrap:wrap; gap:14px; padding-top:8px;">
						<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer" class="btn-launch">
							Become a Community Member
							<span class="arrow-slide">→</span>
						</a>
						<a href="#rewards" class="btn-ghost">See Rewards ↓</a>
					</div>

					<!-- 4 Highlights: 4 in a row on desktop, 2 on first and 2 on second on mobile -->
					<div class="cp-stats-grid">
						<div class="cp-stat-item">
							<div style="font-size:26px; font-weight:900; color:#020617; line-height:1.1;">1K<span style="font-size:16px; color:#9353ff;">+</span></div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Community Members</div>
						</div>
						<div class="cp-stat-item">
							<div style="font-size:26px; font-weight:900; color:#020617; line-height:1.1;">AWS</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Backed Community</div>
						</div>
						<div class="cp-stat-item">
							<div style="font-size:26px; font-weight:900; color:#020617; line-height:1.1;">Global</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Recognition</div>
						</div>
						<div class="cp-stat-item">
							<div style="font-size:26px; font-weight:900; color:#020617; line-height:1.1;">Swag</div>
							<div style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; padding-top:4px;">Giveaways</div>
						</div>
					</div>
				</div>

				<!-- Swag showcase -->
				<div class="reveal reveal-d1" style="position:relative;">
					<div class="swag-card">
						<?php
						$swag_img_url = file_exists( get_theme_file_path( 'media/AWS-Swags.webp' ) )
							? get_theme_file_uri( 'media/AWS-Swags.webp' )
							: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags.webp';
						?>
						<img src="<?php echo esc_url( $swag_img_url ); ?>"
							 alt="Official AWS Student Builder Group Air University Member Swags and Perks"
							 loading="eager">
					</div>
				</div>

			</div>
		</div>

		<!-- Stats ticker -->
		<div class="ticker-wrap">
			<div class="ticker-track">
				<?php for ( $i = 0; $i < 2; $i++ ) : ?>
					<span class="ticker-item"><span class="ticker-dot"></span>Skill Builder Premium — Free</span>
					<span class="ticker-item"><span class="ticker-dot"></span>$30 AWS Credits</span>
					<span class="ticker-item"><span class="ticker-dot"></span>Foundational Exam Voucher</span>
					<span class="ticker-item"><span class="ticker-dot"></span>60+ Countries · 800+ Campuses</span>
					<span class="ticker-item"><span class="ticker-dot"></span>Hands-on Cloud Labs</span>
					<span class="ticker-item"><span class="ticker-dot"></span>Verified Student Community</span>
				<?php endfor; ?>
			</div>
		</div>
	</header>

	<main style="max-width:1120px; margin:0 auto; padding:0 20px;">

		<!-- ================= REWARDS ================= -->
		<section id="rewards" style="padding:88px 0 0;">
			<div class="reveal" style="max-width:640px;">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase; padding-top:10px;">
					Claim Your <span class="grad-text">Welcome Rewards</span>
				</h2>
				<p style="padding-top:14px; font-size:15px; line-height:1.75; color:#475569 !important;">
					Air University students unlock premium training, real cloud credits for projects, and a certification voucher — worth over $549.
				</p>
			</div>

			<div class="cp-grid cp-grid-3" style="padding-top:40px;">
				<!-- Reward 1 -->
				<div class="reward-card reveal p-6 sm:p-7 space-y-4">
					<div class="flex items-start justify-between">
						<div class="reward-icon">
							<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
						</div>
						<span class="badge-tag">$449 VALUE</span>
					</div>
					<div>
						<div class="reward-value">12 Months</div>
						<h3 style="font-weight:800; font-size:16px; color:#0f172a !important; padding-top:2px;">Skill Builder Premium</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							900+ official hands-on labs, courses, and practice exams — free for a full year.
						</p>
					</div>
				</div>

				<!-- Reward 2 -->
				<div class="reward-card reveal reveal-d1 p-6 sm:p-7 space-y-4">
					<div class="flex items-start justify-between">
						<div class="reward-icon">
							<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
						</div>
						<span class="badge-tag">CREDITS</span>
					</div>
					<div>
						<div class="reward-value">$30 AWS</div>
						<h3 style="font-weight:800; font-size:16px; color:#0f172a !important; padding-top:2px;">Cloud Credits</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Build and host your semester and final-year projects on real AWS infrastructure.
						</p>
					</div>
				</div>

				<!-- Reward 3 -->
				<div class="reward-card reveal reveal-d2 p-6 sm:p-7 space-y-4">
					<div class="flex items-start justify-between">
						<div class="reward-icon">
							<svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/></svg>
						</div>
						<span class="badge-tag">$100 VOUCHER</span>
					</div>
					<div>
						<div class="reward-value">Free Exam</div>
						<h3 style="font-weight:800; font-size:16px; color:#0f172a !important; padding-top:2px;">Certification Voucher</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Official Pearson VUE voucher for Cloud Practitioner or AI Practitioner.
						</p>
					</div>
				</div>
			</div>

			<div class="reveal reveal-d1 flex flex-wrap items-center gap-4" style="padding-top:36px;">
				<a href="https://builder.aws.com/start?trk=8e356433-7e2c-4ee9-8c3a-5bccc682a4f5&amp;sc_channel=el" target="_blank" rel="noopener noreferrer" class="btn-launch">
					Claim Student Rewards <span class="arrow-slide">→</span>
				</a>
				<span style="font-size:12.5px; color:#64748b; font-weight:600;">No credit card required · SheerID verified</span>
			</div>

			<!-- Journey -->
			<div class="reveal" style="padding-top:64px;">
				<h3 style="font-weight:900; font-size:20px; text-transform:uppercase; letter-spacing:-0.01em;">Your Journey <span class="grad-text">in 3 Steps</span></h3>
			</div>

			<div style="padding-top:28px; position:relative;">
				<div class="space-y-5">

					<div class="journey-item reveal flex gap-5 p-5 sm:p-6" style="position:relative;">
						<div class="journey-connector"></div>
						<div class="journey-num">01</div>
						<div>
							<h4 style="font-weight:800; font-size:15.5px; color:#0f172a !important;">Verify Student Status</h4>
							<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:6px;">
								Sign in to Builder Center and verify via SheerID with your name, institution, and student email. Most verifications finish in minutes.
							</p>
						</div>
					</div>

					<div class="journey-item reveal reveal-d1 flex gap-5 p-5 sm:p-6" style="position:relative;">
						<div class="journey-connector"></div>
						<div class="journey-num">02</div>
						<div>
							<h4 style="font-weight:800; font-size:15.5px; color:#0f172a !important;">Complete Your Profile</h4>
							<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:6px;">
								Add a photo and fill your About section — this instantly unlocks <strong style="color:#0f172a;">12 months of Skill Builder Premium ($449 value)</strong>.
							</p>
						</div>
					</div>

					<div class="journey-item reveal reveal-d2 flex gap-5 p-5 sm:p-6">
						<div class="journey-num">03</div>
						<div>
							<h4 style="font-weight:800; font-size:15.5px; color:#0f172a !important;">Earn Badges, Unlock More</h4>
							<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:6px;">
								Publish articles and stay engaged on Builder Center. The more badges you earn, the bigger the reward:
							</p>
							<div class="badge-row">
								<span class="badge-tag">7 badges → $10 credits</span>
								<span class="badge-tag">14 badges → +$20 credits</span>
								<span class="badge-tag">21 badges → $100 exam voucher</span>
							</div>
						</div>
					</div>

				</div>
			</div>
		</section>

		<!-- ================= CHANNELS ================= -->
		<section style="padding:88px 0 0;">
			<div class="reveal" style="max-width:640px;">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.03em; text-transform:uppercase; padding-top:10px;">
					Get <span class="grad-text">Connected</span>
				</h2>
				<p style="padding-top:14px; font-size:15px; line-height:1.75; color:#475569 !important;">
					Join our community on social channels.
				</p>
			</div>

			<div class="cp-grid cp-grid-3" style="padding-top:40px;">

				<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="channel-card reveal p-6 sm:p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/wired-outline-2672-logo-square-whatsapp.png' ) ); ?>" alt="WhatsApp" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">WhatsApp</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Ask questions, get cloud lab help, and receive instant event alerts from peers and leads.
						</p>
					</div>
					<div class="channel-foot"><span>Join WhatsApp Community</span><span class="arrow-slide">→</span></div>
				</a>

				<a href="https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/" target="_blank" rel="noopener noreferrer" class="channel-card reveal reveal-d1 p-6 sm:p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/wired-outline-2677-logo-square-linkedin.png' ) ); ?>" alt="LinkedIn" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">LinkedIn</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Celebrate certifications, follow student wins, and connect with alumni in industry.
						</p>
					</div>
					<div class="channel-foot"><span>Follow on LinkedIn</span><span class="arrow-slide">→</span></div>
				</a>

				<a href="https://www.instagram.com/awssbgairuniversity/" target="_blank" rel="noopener noreferrer" class="channel-card reveal reveal-d2 p-6 sm:p-7 space-y-5">
					<div>
						<div class="channel-icon">
							<img src="<?php echo esc_url( get_theme_file_uri( 'media/icons/wired-outline-2542-logo-instagram.png' ) ); ?>" alt="Instagram" class="w-7 h-7 object-contain">
						</div>
					</div>
					<div>
						<h3 style="font-weight:900; font-size:21px; color:#020617 !important;">Instagram</h3>
						<p style="font-size:13.5px; line-height:1.7; color:#64748b !important; padding-top:8px;">
							Event reels, workshop recaps, speaker stories, and campus moments as they happen.
						</p>
					</div>
					<div class="channel-foot"><span>Follow @awssbgairuniversity</span><span class="arrow-slide">→</span></div>
				</a>

			</div>
		</section>

		<!-- ================= FAQ ================= -->
		<section style="padding:88px 0 96px;">
			<div class="reveal" style="max-width:640px;">
				<h2 style="font-size:clamp(28px,4vw,44px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase; padding-top:10px;">
					Questions? <span class="grad-text">Answered.</span>
				</h2>
			</div>

			<div class="space-y-3.5" style="padding-top:36px; max-width:820px;">

				<div class="faq-item reveal">
					<button class="faq-q" onclick="toggleFaq(this)">
						<span>Who is eligible for these student rewards?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Any currently enrolled higher-education student at an accredited university or college is eligible. Verification is completed through SheerID.
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d1">
					<button class="faq-q" onclick="toggleFaq(this)">
						<span>Is a credit card required to claim rewards?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						No credit card is required. Student verification only needs your university email address or active enrollment proof via SheerID.
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d2">
					<button class="faq-q" onclick="toggleFaq(this)">
						<span>How long does SheerID verification take?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Verification is typically instant with your official university email. If manual document review is needed, it usually completes within 24–48 hours.
					</div></div>
				</div>

				<div class="faq-item reveal">
					<button class="faq-q" onclick="toggleFaq(this)">
						<span>Which certifications are covered by the exam voucher?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						The reward grants a 100% discount voucher for either AWS Certified Cloud Practitioner (CLF-C02) or AWS Certified AI Practitioner (AIF-C01).
					</div></div>
				</div>

				<div class="faq-item reveal reveal-d1">
					<button class="faq-q" onclick="toggleFaq(this)">
						<span>What if I get stuck during verification?</span>
						<span class="faq-toggle">+</span>
					</button>
					<div class="faq-a"><div class="faq-a-inner">
						Join our WhatsApp community group above — student leads and peers are active daily to walk you through every step.
					</div></div>
				</div>

			</div>

			<!-- Final CTA band -->
			<div class="reveal" style="margin-top:72px; position:relative; overflow:hidden; border-radius:28px; background:linear-gradient(135deg,#7928ca 0%,#9353ff 100%); padding:56px 32px; text-align:center;">
				<div style="position:absolute; width:340px; height:340px; border-radius:99px; background:rgba(255,255,255,0.12); filter:blur(60px); top:-140px; left:-80px;"></div>
				<div style="position:absolute; width:300px; height:300px; border-radius:99px; background:rgba(255,255,255,0.10); filter:blur(60px); bottom:-130px; right:-60px;"></div>
				<h2 style="font-size:clamp(26px,4vw,40px); font-weight:900; letter-spacing:-0.02em; text-transform:uppercase; color:#ffffff !important; position:relative;">
					Ready to Launch Your Cloud Career?
				</h2>
				<p style="color:rgba(255,255,255,0.85) !important; font-size:15px; padding-top:12px; position:relative;">
					Join hundreds of Air University builders already earning badges and rewards.
				</p>
				<a href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/" target="_blank" rel="noopener noreferrer"
				   style="position:relative; display:inline-flex; align-items:center; gap:10px; margin-top:28px; padding:15px 36px; border-radius:16px; background:#ffffff; color:#7928ca !important; font-weight:800; font-size:14px; box-shadow:0 12px 30px -8px rgba(0,0,0,0.3); transition:transform .22s ease;">
					Become a Community Member <span>→</span>
				</a>
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