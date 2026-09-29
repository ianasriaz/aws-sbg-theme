<?php
/**
 * Template Name: Campus Giveaway Page
 * File: page-giveaway.php
 *
 * Premium On-Campus Giveaway Landing Page for AWS Student Builder Group at Air University.
 * Built using the signature AWS SBG EduFi visual design system:
 * Multi-color Aurora Glow Canvas, Architectural Blueprint Grid, 3D Levitating Swag Kit,
 * Live Social Proof Beacon Bar, Interactive Bento Drop Card with dynamic progress tracking,
 * Swag Bundle Bento Showcase, and an Apple Wallet-inspired Digital Lucky Ticket pass.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$settings    = function_exists( 'aws_sbg_get_giveaway_settings' ) ? aws_sbg_get_giveaway_settings() : array();
$campaign_id = $settings['campaign_id'] ?? 'campus-campaign';
$live_count  = function_exists( 'aws_sbg_get_giveaway_live_count' ) ? aws_sbg_get_giveaway_live_count( $campaign_id ) : 0;
$status       = $settings['campaign_status'] ?? 'active';
$tasks        = $settings['tasks'] ?? array();
$source_tag   = isset( $_GET['src'] ) ? sanitize_text_field( $_GET['src'] ) : 'campus_qr';

// If winners announced mode, fetch published winners
$published_winners = array();
if ( 'announced' === $status ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'aws_giveaway_entries';
	$published_winners = $wpdb->get_results( $wpdb->prepare(
		"SELECT ticket_number, full_name, email, winner_drawn_at 
		 FROM $table_name 
		 WHERE campaign_id = %s AND is_winner = 1 
		 ORDER BY id ASC",
		$campaign_id
	), ARRAY_A );
}

$swag_kit_url = file_exists( get_theme_file_path( 'media/AWS-Swags-Kit.webp' ) )
	? get_theme_file_uri( 'media/AWS-Swags-Kit.webp' )
	: ( file_exists( get_theme_file_path( 'media/AWS-Swags.webp' ) )
		? get_theme_file_uri( 'media/AWS-Swags.webp' )
		: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp' );

$linkedin_icon = file_exists( get_theme_file_path( 'media/icons/wired-outline-2677-logo-square-linkedin.png' ) )
	? get_theme_file_uri( 'media/icons/wired-outline-2677-logo-square-linkedin.png' )
	: '';

$instagram_icon = file_exists( get_theme_file_path( 'media/icons/wired-outline-2542-logo-instagram.png' ) )
	? get_theme_file_uri( 'media/icons/wired-outline-2542-logo-instagram.png' )
	: '';

$whatsapp_icon = file_exists( get_theme_file_path( 'media/icons/wired-outline-2672-logo-square-whatsapp.png' ) )
	? get_theme_file_uri( 'media/icons/wired-outline-2672-logo-square-whatsapp.png' )
	: '';
?>

<!-- Scoped Styles for Elevated Giveaway Landing Page -->
<style>
	/* ==========================================================================
	   1. CORE BASE & TYPOGRAPHY
	   ========================================================================== */
	.sbg-giveaway-root {
		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
		background-color: #ffffff !important;
		color: #0f172a !important;
		width: 100%;
		overflow-x: hidden;
		min-height: 85vh;
		position: relative;
	}

	.sbg-giveaway-root h1,
	.sbg-giveaway-root h2,
	.sbg-giveaway-root h3,
	.sbg-giveaway-root h4 {
		color: #020617 !important;
		margin: 0;
	}

	.sbg-giveaway-root p {
		color: #475569 !important;
		margin: 0;
	}

	.sbg-giveaway-root a {
		text-decoration: none !important;
	}

	.sbg-giveaway-root * {
		box-sizing: border-box;
	}

	/* ==========================================================================
	   2. MULTI-COLOR AURORA CANVAS & ARCHITECTURAL GRIDLINES
	   ========================================================================== */
	.sbg-giveaway-root .aurora-canvas {
		position: relative;
		overflow: hidden;
		background: #ffffff;
	}

	.sbg-giveaway-root .aurora-blob {
		position: absolute;
		border-radius: 9999px;
		filter: blur(80px);
		pointer-events: none;
		will-change: transform;
		z-index: 0;
	}

	.sbg-giveaway-root .aurora-purple {
		width: 550px;
		height: 550px;
		top: -140px;
		right: -100px;
		background: radial-gradient(circle, rgba(147, 83, 255, 0.22), transparent 65%);
		animation: sbgAuroraFloat 12s ease-in-out infinite;
	}

	.sbg-giveaway-root .aurora-orange {
		width: 480px;
		height: 480px;
		top: 15%;
		left: -140px;
		background: radial-gradient(circle, rgba(255, 153, 0, 0.18), transparent 65%);
		animation: sbgAuroraFloat 14s ease-in-out infinite reverse;
	}

	.sbg-giveaway-root .aurora-green {
		width: 420px;
		height: 420px;
		bottom: 5%;
		right: 15%;
		background: radial-gradient(circle, rgba(0, 239, 136, 0.16), transparent 65%);
		animation: sbgAuroraFloat 10s ease-in-out infinite 2s;
	}

	@keyframes sbgAuroraFloat {
		0%, 100% { transform: translate(0, 0) scale(1); }
		50%      { transform: translate(25px, -20px) scale(1.06); }
	}

	/* Technical Blueprint Gridlines */
	.sbg-giveaway-root .sbg-grid-lines {
		position: absolute;
		inset: 0;
		background-size: 38px 38px;
		background-image: 
			linear-gradient(to right, rgba(147, 83, 255, 0.06) 1px, transparent 1px),
			linear-gradient(to bottom, rgba(147, 83, 255, 0.06) 1px, transparent 1px);
		mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		-webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000000 40%, transparent 92%);
		pointer-events: none;
		z-index: 1;
	}

	/* ==========================================================================
	   3. TEXT HIGHLIGHTS & BRAND GRADIENTS
	   ========================================================================== */
	.sbg-giveaway-root .sbg-text-highlight-green {
		background: #00ef88 !important;
		color: #020617 !important;
		padding: 3px 12px;
		border-radius: 10px;
		display: inline-block;
		transform: rotate(-1deg);
		box-shadow: 0 4px 15px rgba(0, 239, 136, 0.28);
	}

	.sbg-giveaway-root .sbg-text-highlight-orange {
		background: #FF9900 !important;
		color: #020617 !important;
		padding: 2px 14px;
		border-radius: 12px;
		display: inline-block;
		transform: rotate(1deg);
		box-shadow: 0 4px 15px rgba(255, 153, 0, 0.28);
	}

	.sbg-giveaway-root .sbg-gradient-text-purple {
		background: linear-gradient(92deg, #7928ca 0%, #9353ff 55%, #b47bff 100%) !important;
		-webkit-background-clip: text !important;
		background-clip: text !important;
		-webkit-text-fill-color: transparent !important;
		color: transparent !important;
	}

	/* ==========================================================================
	   4. BUTTONS & INTERACTIVE ELEMENTS
	   ========================================================================== */
	.sbg-giveaway-root .btn-launch {
		position: relative;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 10px;
		width: 100%;
		padding: 16px 28px;
		border-radius: 16px;
		background: linear-gradient(135deg, #7928ca 0%, #9353ff 100%) !important;
		color: #ffffff !important;
		font-weight: 800;
		font-size: 15px;
		letter-spacing: 0.02em;
		box-shadow: 0 12px 28px -6px rgba(147, 83, 255, 0.45);
		transition: transform .22s cubic-bezier(.16,1,.3,1), box-shadow .22s ease;
		overflow: hidden;
		cursor: pointer;
		border: none;
		text-decoration: none !important;
	}

	.sbg-giveaway-root .btn-launch::after {
		content: "";
		position: absolute;
		inset: 0;
		background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,0.35) 50%, transparent 65%);
		transform: translateX(-120%);
		transition: transform .65s ease;
	}

	.sbg-giveaway-root .btn-launch:hover {
		transform: translateY(-2px);
		box-shadow: 0 16px 36px -6px rgba(147, 83, 255, 0.55);
	}

	.sbg-giveaway-root .btn-launch:hover::after {
		transform: translateX(120%);
	}

	.sbg-giveaway-root .btn-launch:disabled {
		opacity: 0.7;
		cursor: not-allowed;
		transform: none;
	}

	/* Step Row Action Buttons (Clean Light Pills - Zero Black!) */
	.sbg-giveaway-root .step-pill-btn {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 7px 15px;
		border-radius: 9999px;
		font-size: 12px;
		font-weight: 800;
		text-decoration: none !important;
		transition: all .18s ease;
		white-space: nowrap;
		flex-shrink: 0;
		background: #ffffff;
		color: #0f172a;
		border: 1.5px solid #e2e8f0;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
	}

	.sbg-giveaway-root .step-pill-btn:hover {
		background: #faf5ff;
		color: #7928ca;
		border-color: #c084fc;
		transform: translateY(-1px);
	}

	.sbg-giveaway-root .step-pill-btn.is-completed {
		background: #ecfdf5 !important;
		color: #059669 !important;
		border-color: #a7f3d0 !important;
		box-shadow: none;
	}

	/* ==========================================================================
	   5. 3D LEVITATING SWAG SHOWCASE
	   ========================================================================== */
	@keyframes sbgHeroLevitate {
		0%, 100% {
			transform: translateY(0px) rotate(0deg) scale(1);
			filter: drop-shadow(0 18px 28px rgba(147, 83, 255, 0.22));
		}
		25% {
			transform: translateY(-8px) rotate(1deg) scale(1.015);
			filter: drop-shadow(0 24px 34px rgba(147, 83, 255, 0.26));
		}
		50% {
			transform: translateY(-15px) rotate(-0.5deg) scale(1.025);
			filter: drop-shadow(0 32px 42px rgba(147, 83, 255, 0.30));
		}
		75% {
			transform: translateY(-6px) rotate(-1deg) scale(1.01);
			filter: drop-shadow(0 22px 30px rgba(147, 83, 255, 0.24));
		}
	}

	.sbg-giveaway-root .sbg-hero-swag-anim {
		animation: sbgHeroLevitate 6.5s ease-in-out infinite;
		transform-origin: center bottom;
		will-change: transform, filter;
		transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), filter 0.4s ease;
	}

	.sbg-giveaway-root .sbg-swag-glow-halo {
		position: absolute;
		inset: 0;
		background: radial-gradient(circle, rgba(255, 153, 0, 0.22) 0%, rgba(147, 83, 255, 0.20) 45%, rgba(0, 239, 136, 0.16) 75%, transparent 100%);
		border-radius: 9999px;
		filter: blur(50px);
		pointer-events: none;
		z-index: 1;
	}

	/* ==========================================================================
	   6. HIGH-END POPUP MODAL ARCHITECTURE
	   ========================================================================== */
	.sbg-giveaway-root .giveaway-modal-backdrop {
		position: fixed;
		inset: 0;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		background: rgba(15, 23, 42, 0.75);
		backdrop-filter: blur(14px);
		-webkit-backdrop-filter: blur(14px);
		z-index: 999999;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px 16px;
		box-sizing: border-box;
		overflow-y: auto;
	}

	.sbg-giveaway-root [x-cloak],
	[x-cloak] {
		display: none !important;
	}

	.sbg-giveaway-root .giveaway-modal-card {
		position: relative !important;
		background: #ffffff !important;
		border-radius: 26px !important;
		max-width: 440px !important;
		width: 100% !important;
		margin: auto !important;
		padding: 0 !important;
		box-shadow: 0 30px 80px -15px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(147, 83, 255, 0.12) !important;
		border: 1px solid rgba(226, 232, 240, 0.9) !important;
		box-sizing: border-box !important;
		text-align: left !important;
		max-height: calc(100vh - 40px) !important;
		overflow-y: auto !important;
		overflow-x: hidden !important;
		scrollbar-width: none !important;
		-ms-overflow-style: none !important;
		animation: sbgModalPop 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
	}

	.sbg-giveaway-root .giveaway-modal-card::-webkit-scrollbar {
		display: none !important;
		width: 0 !important;
		height: 0 !important;
	}

	@keyframes sbgModalPop {
		0% { transform: scale(0.94) translateY(10px); opacity: 0; }
		100% { transform: scale(1) translateY(0); opacity: 1; }
	}

	/* Top Luminous Gradient Strip */
	.sbg-giveaway-root .modal-accent-bar {
		height: 5px !important;
		width: 100% !important;
		background: linear-gradient(90deg, #7928ca 0%, #9353ff 35%, #ff9900 70%, #00ef88 100%) !important;
	}

	.sbg-giveaway-root .modal-inner-content {
		padding: 20px 20px 22px !important;
	}

	/* Single Horizontal Line of Action Chips (Super Quick & Compact) */
	.sbg-giveaway-root .sbg-task-chips-row {
		display: flex !important;
		align-items: center !important;
		gap: 8px !important;
		width: 100% !important;
	}

	.sbg-giveaway-root .sbg-task-chip {
		flex: 1 !important;
		min-width: 0 !important;
		height: 38px !important;
		padding: 0 8px !important;
		border-radius: 10px !important;
		background: #f8fafc !important;
		border: 1.5px solid #e2e8f0 !important;
		color: #334155 !important;
		font-size: 12px !important;
		font-weight: 700 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		gap: 4px !important;
		cursor: pointer !important;
		user-select: none !important;
		white-space: nowrap !important;
		transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
		box-sizing: border-box !important;
		text-decoration: none !important;
	}

	.sbg-giveaway-root .sbg-task-chip:hover {
		background: #ffffff !important;
		border-color: #cbd5e1 !important;
		color: #7928ca !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 3px 8px rgba(15, 23, 42, 0.05) !important;
	}

	.sbg-giveaway-root .sbg-task-chip.is-completed {
		background: #f0fdf4 !important;
		border-color: #86efac !important;
		color: #15803d !important;
		box-shadow: 0 2px 6px rgba(34, 197, 94, 0.12) !important;
	}

	.sbg-giveaway-root .sbg-chip-icon {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: #15803d !important;
		flex-shrink: 0 !important;
	}

	.sbg-giveaway-root .sbg-chip-text {
		overflow: hidden !important;
		text-overflow: ellipsis !important;
		white-space: nowrap !important;
	}

	/* Agreement Tile (Compact) */
	.sbg-giveaway-root .sbg-agree-tile {
		display: flex !important;
		align-items: flex-start !important;
		gap: 9px !important;
		padding: 9px 11px !important;
		border-radius: 12px !important;
		background: #f8fafc !important;
		border: 1.5px solid #e2e8f0 !important;
		cursor: pointer !important;
		user-select: none !important;
		transition: all 0.18s ease !important;
		margin-top: 1px !important;
		box-sizing: border-box !important;
	}

	.sbg-giveaway-root .sbg-agree-tile.is-checked {
		background: #faf5ff !important;
		border-color: #c084fc !important;
		box-shadow: 0 2px 8px rgba(192, 132, 252, 0.12) !important;
	}

	.sbg-giveaway-root .sbg-task-checkbox {
		width: 18px !important;
		height: 18px !important;
		min-width: 18px !important;
		border-radius: 5px !important;
		border: 1.5px solid #cbd5e1 !important;
		background: #ffffff !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: #ffffff !important;
		flex-shrink: 0 !important;
		transition: all 0.18s ease !important;
		box-sizing: border-box !important;
	}

	.sbg-giveaway-root .sbg-task-checkbox.checked {
		background: #10b981 !important;
		border-color: #10b981 !important;
		box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35) !important;
	}

	.sbg-giveaway-root .modal-field-group {
		display: flex !important;
		flex-direction: column !important;
		gap: 4px !important;
		text-align: left !important;
	}

	.sbg-giveaway-root .modal-field-group label {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		font-size: 11.5px !important;
		font-weight: 700 !important;
		color: #334155 !important;
		margin: 0 !important;
		padding: 0 !important;
		line-height: 1.2 !important;
	}

	.sbg-giveaway-root .modal-field-group input {
		width: 100% !important;
		height: 40px !important;
		margin: 0 !important;
		padding: 0 13px !important;
		border: 1.5px solid #cbd5e1 !important;
		border-radius: 10px !important;
		font-size: 13px !important;
		line-height: normal !important;
		background: #ffffff !important;
		color: #0f172a !important;
		box-sizing: border-box !important;
		outline: none !important;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
		transition: border-color .18s ease, box-shadow .18s ease !important;
	}

	.sbg-giveaway-root .modal-field-group input:focus {
		border-color: #9353ff !important;
		box-shadow: 0 0 0 3px rgba(147, 83, 255, 0.14) !important;
	}

	.sbg-giveaway-root .modal-field-group input::placeholder {
		color: #94a3b8 !important;
		font-size: 12.5px !important;
	}

	.sbg-giveaway-root .light-domain-tag {
		color: #7c3aed !important;
		font-weight: 700 !important;
		background: #f5f3ff !important;
		border: 1px solid #ede9fe !important;
		padding: 2px 7px !important;
		border-radius: 6px !important;
		font-size: 11px !important;
		display: inline-block !important;
		font-family: inherit !important;
	}

	.sbg-giveaway-root code {
		background: #f5f3ff !important;
		color: #7c3aed !important;
		border: 1px solid #ede9fe !important;
		padding: 2px 6px !important;
		border-radius: 6px !important;
		font-size: 12px !important;
		font-family: inherit !important;
	}

	@media (max-width: 900px) {
		.sbg-hero-grid {
			grid-template-columns: 1fr !important;
			gap: 40px !important;
		}
	}
</style>

<!-- Canvas Confetti CDN -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

<div class="sbg-giveaway-root" x-data="awsCampusGiveaway()" x-init="initGiveaway()">

	<!-- ========================================================================= -->
	<!-- AURORA CANVAS HERO: ATMOSPHERIC LAUNCHPAD WITH BLUEPRINT GRID              -->
	<!-- ========================================================================= -->
	<div class="aurora-canvas">
		<!-- Multi-Color Glowing Aurora Orbs -->
		<div class="aurora-blob aurora-purple"></div>
		<div class="aurora-blob aurora-orange"></div>
		<div class="aurora-blob aurora-green"></div>

		<!-- Architectural Blueprint Gridlines -->
		<div class="sbg-grid-lines"></div>

		<div style="max-width:1160px; margin:0 auto; padding:54px 20px 72px; position:relative; z-index:10;">

			<!-- ======================================================================
			     PHASE 1: ACTIVE GIVEAWAY MODE (BENTO SPLIT HERO)
			     ====================================================================== -->
			<?php if ( 'announced' !== $status ) : ?>

				<div class="sbg-hero-grid" style="display:grid; grid-template-columns: 1.15fr 0.85fr; gap:52px; align-items:center;">

					<!-- Left Column: High-Contrast EduFi Pitch, Live Proof Beacon & Focused CTA -->
					<div>
						<!-- Campus Chapter Badge -->
						<div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
							<span class="sbg-text-highlight-green" style="font-size:11.5px; font-weight:900; letter-spacing:0.04em;">
								AIR UNIVERSITY ISLAMABAD
							</span>
							<span style="font-size:12.5px; font-weight:700; color:#64748b;">
								• On-Campus Drop
							</span>
						</div>

						<h1 style="font-size:clamp(36px, 5.2vw, 58px); font-weight:900; letter-spacing:-0.035em; line-height:1.02; margin-bottom:18px;">
							EXCLUSIVE <br>
							<span class="sbg-gradient-text-purple">CAMPUS SWAG</span> <span class="sbg-text-highlight-orange">DROP.</span>
						</h1>

						<p style="font-size:16px; line-height:1.7; color:#475569; margin-bottom:28px; max-width:520px;">
							<?php echo esc_html( ! empty( $settings['campaign_description'] ) ? $settings['campaign_description'] : 'Complete the quick steps to claim your official Lucky Ticket. 5 random Air University students will win exclusive AWS Swag Bundles in this giveaway.' ); ?>
						</p>

						<!-- Focused Primary CTA Button -->
						<div>
							<template x-if="!myTicket">
								<div>
									<button type="button" @click="openEntryModal()" class="btn-launch" style="max-width:320px; font-size:16px; padding:18px 36px;">
										<span>Enter Giveaway</span>
										<span>🎟️</span>
									</button>
								</div>
							</template>

							<template x-if="myTicket">
								<div>
									<div class="apple-pass-ticket" style="max-width:340px; margin-bottom:14px; text-align:left; padding:16px 20px;">
										<div style="font-size:11px; font-weight:800; color:#7c3aed; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:2px;">
											Your Confirmed Lucky Ticket
										</div>
										<div style="font-size:24px; font-weight:900; color:#7928ca; letter-spacing:0.04em;" x-text="'#' + myTicket.ticket_number"></div>
										<div style="font-size:12px; color:#059669; font-weight:700; margin-top:4px;">
											✓ Confirmed in Campus Giveaway
										</div>
									</div>
									<button type="button" @click="openEntryModal()" class="btn-launch" style="max-width:320px;">
										<span>🎟️ View Full Lucky Ticket</span>
										<span>→</span>
									</button>
								</div>
							</template>
						</div>
					</div>

					<!-- Right Column: 3D Levitating Swag Kit Showcase Centerpiece -->
					<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
						<div style="position:relative; width:100%; max-width:480px;">
							<!-- Pulsing Ambient Glow Halo -->
							<div class="sbg-swag-glow-halo"></div>

							<img src="<?php echo esc_url( $swag_kit_url ); ?>"
							     onerror="this.onerror=null;this.src='https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';"
							     alt="Official AWS Student Builder Group Swags Kit"
							     class="sbg-hero-swag-anim"
							     style="width:100%; height:auto; object-fit:contain; position:relative; z-index:2; user-select:none;"
							     loading="eager">
						</div>
					</div>

				</div>


			<!-- ======================================================================
			     PHASE 2: WINNERS ANNOUNCED SHOWCASE MODE
			     ====================================================================== -->
			<?php else : ?>

				<div style="max-width:840px; margin:0 auto; text-align:center;" x-init="triggerConfettiCelebration()">
					
					<div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:14px;">
						<span class="sbg-text-highlight-green" style="font-size:11.5px; font-weight:800; letter-spacing:0.04em;">
							OFFICIAL RESULTS
						</span>
						<span style="font-size:12.5px; font-weight:700; color:#64748b;">
							• Air University Chapter
						</span>
					</div>

					<h1 style="font-size:clamp(34px, 5.5vw, 52px); font-weight:900; letter-spacing:-0.03em; margin-bottom:14px; color:#020617;">
						CAMPUS GIVEAWAY <span class="sbg-gradient-text-purple">WINNERS.</span>
					</h1>

					<p style="font-size:16px; color:#475569; max-width:580px; margin:0 auto 36px; line-height:1.6;">
						Thank you to all <strong><?php echo esc_html( number_format( $live_count ) ); ?> AU students</strong> who participated in our campus drop. Here are our 5 lucky giveaway winners:
					</p>

					<!-- 5 Winner Cards Grid -->
					<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:18px; margin-bottom:44px; text-align:left;">
						<?php if ( ! empty( $published_winners ) ) : ?>
							<?php foreach ( $published_winners as $idx => $win ) : 
								$parts = explode( '@', $win['email'] );
								$user_part = $parts[0] ?? '';
								$domain_part = $parts[1] ?? 'students.au.edu.pk';
								$masked_email = ( strlen( $user_part ) > 3 ) 
									? substr( $user_part, 0, 3 ) . '***@' . $domain_part 
									: '***@' . $domain_part;
							?>
								<div class="apple-pass-ticket" style="text-align:left; padding:22px;">
									<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
										<span style="font-size:11px; font-weight:900; color:#059669; text-transform:uppercase; letter-spacing:0.06em;">
											Winner #<?php echo esc_html( $idx + 1 ); ?>
										</span>
										<span style="background:#ecfdf5; color:#059669; font-size:11px; font-weight:800; padding:2px 8px; border-radius:6px;">
											🎉 Swag Winner
										</span>
									</div>
									<div style="font-size:22px; font-weight:900; color:#7928ca; margin-bottom:4px;">
										#<?php echo esc_html( $win['ticket_number'] ); ?>
									</div>
									<h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-bottom:4px;">
										<?php echo esc_html( $win['full_name'] ); ?>
									</h3>
									<div style="font-size:12.5px; color:#64748b;">
										<?php echo esc_html( $masked_email ); ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<!-- Ticket Search / Verification Box -->
					<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:24px; padding:28px 24px; max-width:540px; margin:0 auto 36px; box-shadow:0 12px 30px -8px rgba(15,23,42,0.06);">
						<h4 style="font-size:16px; font-weight:800; color:#0f172a; margin-bottom:6px;">
							Check Your Ticket Status
						</h4>
						<p style="font-size:13px; color:#64748b; margin-bottom:16px;">
							Enter your Ticket # (e.g. <code>AU-AWS-1042</code>) or AU Student Email:
						</p>

						<form @submit.prevent="checkTicketStatus()" style="display:flex; gap:8px; margin:0; padding:0;">
							<input type="text" x-model="checkQuery" placeholder="Ticket # or AU Email..." style="flex:1; height:46px; margin:0 !important; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:12px; font-size:14px; background:#ffffff !important; color:#0f172a !important; box-sizing:border-box !important;" required>
							<button type="submit" class="btn-launch" style="width:auto; height:46px; padding:0 22px; font-size:13.5px; border-radius:12px;" :disabled="checking">
								<span x-text="checking ? 'Checking...' : 'Check Status'"></span>
							</button>
						</form>

						<template x-if="checkResult">
							<div style="margin-top:16px; padding:14px; border-radius:12px; font-size:13px; font-weight:700;" :style="checkResult.is_winner ? 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;' : 'background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe;'">
								<template x-if="checkResult.is_winner">
									<div>
										🎉 Congratulations, <span x-text="checkResult.full_name"></span>! You are one of our 5 lucky winners! Please visit the campus desk with your Student ID to claim your swag.
									</div>
								</template>
								<template x-if="!checkResult.is_winner && checkResult.found">
									<div>
										Thank you for participating, <span x-text="checkResult.full_name"></span>! Your ticket was entered in the giveaway. Stay connected on WhatsApp for future giveaways!
									</div>
								</template>
								<template x-if="!checkResult.found">
									<div style="color:#ef4444;">
										<span x-text="checkResult.message"></span>
									</div>
								</template>
							</div>
						</template>
					</div>

					<!-- Booth Claim Guidelines -->
					<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:20px; padding:20px 24px; max-width:540px; margin:0 auto 28px;">
						<div style="font-size:11.5px; font-weight:800; color:#ea580c; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">
							Claim Instructions
						</div>
						<p style="font-size:13.5px; color:#475569; line-height:1.5;">
							<?php echo esc_html( $settings['booth_claim_location'] ); ?>
						</p>
					</div>

					<!-- WhatsApp Community Button -->
					<a href="https://chat.whatsapp.com/KlNgxGopitiCJoTJEyqTaL" target="_blank" rel="noopener noreferrer" class="btn-launch" style="display:inline-flex; width:auto; padding:14px 28px; font-size:14px;">
						💬 Join WhatsApp Community for Next Campus Drop ↗
					</a>

				</div>

			<?php endif; ?>

		</div>
	</div>

	<!-- ======================================================================
	     POP-UP MODAL: 3-FIELD ENTRY FORM & APPLE WALLET DIGITAL TICKET
	     ====================================================================== -->
	<div x-show="showModal" 
	     x-cloak 
	     class="giveaway-modal-backdrop" 
	     x-transition:enter="transition ease-out duration-200"
	     x-transition:enter-start="opacity-0"
	     x-transition:enter-end="opacity-100"
	     x-transition:leave="transition ease-in duration-150"
	     x-transition:leave-start="opacity-100"
	     x-transition:leave-end="opacity-0"
	     @click.self="closeModal()"
	     @keydown.escape.window="closeModal()">

		<div class="giveaway-modal-card" @click.stop>
			
			<!-- Top Luminous Gradient Strip -->
			<div class="modal-accent-bar"></div>

			<!-- Inner Modal Content -->
			<div class="modal-inner-content">

				<!-- Close button -->
				<button type="button" @click.stop="closeModal()" style="position:absolute; top:18px; right:18px; background:#f1f5f9; border:none; width:30px; height:30px; border-radius:9px; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:13px; font-weight:700; cursor:pointer; transition:all .15s ease;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'" aria-label="Close">
					✕
				</button>

				<!-- MODAL VIEW A: ENTRY FORM -->
				<template x-if="!myTicket">
					<div>
						<!-- Modal Top Header -->
						<div style="margin-bottom:16px; padding-right:28px; text-align:left;">
							<div style="display:inline-flex; align-items:center; gap:6px; background:#faf5ff; border:1px solid #ede9fe; color:#7928ca; font-size:10px; font-weight:800; padding:3px 9px; border-radius:999px; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:7px;">
								<span>🎟️</span> <span>AU Campus Giveaway Drop</span>
							</div>
							<h3 style="font-size:22px; font-weight:900; color:#0f172a; margin:0 0 3px; letter-spacing:-0.03em; line-height:1.2;">
								Win AWS Swags
							</h3>
							<p style="font-size:12px; color:#64748b; margin:0; line-height:1.4;">
								Complete the quick tasks and enter your AU details below.
							</p>
						</div>

						<!-- Error Notice -->
						<div x-show="errorMessage" style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; font-size:12px; font-weight:700; padding:8px 12px; border-radius:10px; margin-bottom:12px;" x-text="errorMessage"></div>

						<form @submit.prevent="submitEntry()" style="display:flex; flex-direction:column; gap:10px; margin:0; padding:0;">
							<!-- Anti-spam Honeypot -->
							<input type="text" name="website_url" x-model="form.website_url" style="display:none !important;" tabindex="-1" autocomplete="off">

							<!-- Interactive Tasks Section with Dynamic Progress Track -->
							<div>
								<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">
									<div style="display:flex; align-items:center; gap:4px;">
										<span style="font-size:11px; font-weight:900; color:#475569; text-transform:uppercase; letter-spacing:0.06em;">
											Required Tasks
										</span>
										<span style="color:#ef4444; font-weight:900;">*</span>
									</div>
									<div style="display:flex; align-items:center; gap:7px;">
										<!-- Animated Mini Progress Bar -->
										<div style="width:48px; height:5px; background:#e2e8f0; border-radius:999px; overflow:hidden;">
											<div style="height:100%; background:linear-gradient(90deg, #9353ff, #10b981); border-radius:999px; transition:width 0.3s cubic-bezier(0.16,1,0.3,1);"
											     :style="'width: ' + ((getCompletedCount() / <?php echo max(1, count($tasks)); ?>) * 100) + '%'"></div>
										</div>
										<span style="font-size:10.5px; font-weight:800; color:#7928ca; background:#f5f3ff; border:1px solid #ede9fe; padding:2px 7px; border-radius:6px;" 
										      x-text="getCompletedCount() + '/' + <?php echo count( $tasks ); ?> + ' Done'"></span>
									</div>
								</div>

								<!-- Single Horizontal Line of Action Chips (Super Quick & Compact) -->
								<div class="sbg-task-chips-row">
									<?php foreach ( $tasks as $idx => $task ) : 
										$tid = esc_attr( $task['id'] );
										$short_label = preg_replace( '/^(Follow\s+on\s+|Follow\s+@|Follow\s+|Join\s+)/i', '', $task['title'] );
										$short_label = trim( preg_replace( '/\s+Community$/i', '', $short_label ) );
										if ( empty( $short_label ) ) {
											$short_label = $task['title'];
										}
									?>
										<button type="button" 
										        class="sbg-task-chip" 
										        :class="{ 'is-completed': tasks['<?php echo $tid; ?>'] }"
										        @click="handleChipClick('<?php echo $tid; ?>', '<?php echo esc_url( $task['url'] ?? '' ); ?>')"
										        title="<?php echo esc_attr( $task['title'] ); ?>">
											<span class="sbg-chip-icon" x-show="tasks['<?php echo $tid; ?>']">
												<svg viewBox="0 0 16 16" fill="currentColor" style="width:12px; height:12px;">
													<path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/>
												</svg>
											</span>
											<span class="sbg-chip-text" x-text="tasks['<?php echo $tid; ?>'] ? '<?php echo esc_js( $short_label ); ?>' : '<?php echo esc_js( $short_label ); ?> ↗'"></span>
										</button>
									<?php endforeach; ?>
								</div>
							</div>

							<!-- 1. Full Name -->
							<div class="modal-field-group">
								<label>
									<span>Full Name <span style="color:#ef4444;">*</span></span>
								</label>
								<input type="text" x-model="form.full_name" placeholder="Ali Ahmed" required autocomplete="name">
							</div>

							<!-- 2. AU Student Email -->
							<div class="modal-field-group">
								<label>
									<span>AU Student Email <span style="color:#ef4444;">*</span></span>
									<span class="light-domain-tag">@students.au.edu.pk</span>
								</label>
								<input type="email" x-model="form.email" placeholder="210942@students.au.edu.pk" required autocomplete="email">
							</div>

							<!-- 3. WhatsApp Phone Number -->
							<div class="modal-field-group">
								<label>
									<span>WhatsApp Phone Number <span style="color:#ef4444;">*</span></span>
									<span style="font-size:11px; color:#64748b; font-weight:600;">For winner notification</span>
								</label>
								<input type="tel" x-model="form.phone" placeholder="0300 1234567" required autocomplete="tel">
							</div>

							<!-- Verification Agreement Tile -->
							<label class="sbg-agree-tile" :class="{ 'is-checked': form.agree_verification }">
								<div class="sbg-task-checkbox" :class="{ 'checked': form.agree_verification }" style="margin-top:1px;">
									<svg x-show="form.agree_verification" viewBox="0 0 16 16" fill="currentColor" style="width:11px; height:11px;">
										<path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/>
									</svg>
								</div>
								<input type="checkbox" x-model="form.agree_verification" required style="display:none !important;">
								<span style="font-size:11.5px; line-height:1.42; color:#475569; font-weight:600; text-align:left;">
									I confirm all tasks are followed and my details are correct. If unverified, my chance will be given to the next participant.
								</span>
							</label>

							<div style="margin-top:2px;">
								<button type="submit" class="btn-launch" :disabled="submitting" style="padding:12px 24px; font-size:14.5px; height:46px; border-radius:12px;">
									<span x-text="submitting ? 'Submitting...' : 'Submit'"></span>
								</button>
							</div>
						</form>
					</div>
				</template>

			<!-- MODAL VIEW B: CONFIRMED LUCKY TICKET VIEW (APPLE WALLET VIP PASS) -->
			<template x-if="myTicket">
				<div>
					<div class="apple-pass-ticket" style="margin-bottom:20px; text-align:center;">
						
						<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ede9fe; padding-bottom:12px; margin-bottom:16px;">
							<span style="font-size:11px; font-weight:800; color:#7c3aed; text-transform:uppercase; letter-spacing:0.06em;">
								Official Entry Pass
							</span>
							<span style="background:#ecfdf5; color:#059669; font-weight:800; font-size:11.5px; padding:2px 8px; border-radius:6px; border:1px solid #a7f3d0;">
								✓ Verified
							</span>
						</div>

						<!-- Ticket Number Highlight -->
						<div style="background:#ffffff; border:1px solid #ede9fe; border-radius:14px; padding:14px; margin-bottom:14px; box-shadow:0 2px 8px rgba(147,83,255,0.08);">
							<div style="font-size:11px; font-weight:700; color:#7c3aed; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:3px;">
								Your Lucky Ticket Number
							</div>
							<div style="font-size:28px; font-weight:900; color:#7928ca; letter-spacing:0.04em;" x-text="'#' + myTicket.ticket_number"></div>
						</div>

						<!-- Participant Details -->
						<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; margin-bottom:14px; text-align:left;">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; font-size:12.5px;">
								<span style="color:#64748b; font-weight:600;">Participant:</span>
								<span style="font-weight:800; color:#0f172a;" x-text="myTicket.full_name"></span>
							</div>
							<div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
								<span style="color:#64748b; font-weight:600;">AU Email:</span>
								<span style="font-weight:700; color:#0f172a;" x-text="myTicket.email"></span>
							</div>
						</div>

						<p style="font-size:12px; color:#64748b; line-height:1.45; margin:0;">
							Keep this ticket saved! 5 winners will be announced on WhatsApp and campus channels.
						</p>
					</div>

					<!-- Viral Share on WhatsApp Loop -->
					<div style="display:flex; gap:10px;">
						<a :href="'https://wa.me/?text=' + encodeURIComponent('I just entered the AWS SBG Campus Swag Giveaway! My Ticket is #' + myTicket.ticket_number + '. Scan to enter here: ' + window.location.href)" 
						   target="_blank" 
						   style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; background:#25d366; color:#ffffff; font-weight:800; font-size:13.5px; height:46px; border-radius:14px; text-decoration:none; box-shadow:0 8px 20px -4px rgba(37,211,102,0.35);">
							💬 Share with Friends
						</a>
						<button type="button" @click.stop="closeModal()" style="background:#f8fafc; border:1px solid #e2e8f0; color:#475569; font-weight:700; font-size:13px; padding:0 20px; height:46px; border-radius:14px; cursor:pointer;">
							Close
						</button>
					</div>

					<div style="margin-top:12px; text-align:center;">
						<button type="button" @click.stop="resetEntry()" style="background:none; border:none; color:#64748b; font-size:11.5px; text-decoration:underline; cursor:pointer; padding:4px 8px;">
							Enter with another AU student email
						</button>
					</div>
				</div>
			</template>

			</div><!-- .modal-inner-content -->

		</div><!-- .giveaway-modal-card -->

</div>

<!-- Alpine.js Application Logic -->
<script>
function awsCampusGiveaway() {
	return {
		liveCount: <?php echo intval( $live_count ); ?>,
		showModal: false,
		submitting: false,
		checking: false,
		errorMessage: '',
		checkQuery: '',
		checkResult: null,
		myTicket: null,
		source: '<?php echo esc_js( $source_tag ); ?>',
		tasks: {},

		form: {
			full_name: '',
			email: '',
			phone: '',
			website_url: '',
			agree_verification: false,
		},

		initGiveaway() {
			try {
				const savedTasks = localStorage.getItem('aws_giveaway_tasks');
				if (savedTasks) {
					this.tasks = JSON.parse(savedTasks);
				}
				const savedTicket = localStorage.getItem('aws_giveaway_ticket');
				if (savedTicket) {
					this.myTicket = JSON.parse(savedTicket);
				}
			} catch (e) {
				console.warn('Storage unavailable', e);
			}

			// Soft background poll for live count every 25 seconds
			setInterval(() => {
				fetch('<?php echo esc_url( rest_url( 'aws-sbg/v1/giveaway/stats' ) ); ?>')
					.then(r => r.json())
					.then(res => {
						if (res && res.count !== undefined) {
							this.liveCount = res.count;
						}
					})
					.catch(() => {});
			}, 25000);
		},

		getCompletedCount() {
			return Object.values(this.tasks).filter(Boolean).length;
		},

		saveTasks() {
			try {
				localStorage.setItem('aws_giveaway_tasks', JSON.stringify(this.tasks));
			} catch(e) {}
		},

		completeTask(taskId) {
			this.tasks[taskId] = true;
			this.saveTasks();
		},

		toggleTask(taskId) {
			this.tasks[taskId] = !this.tasks[taskId];
			this.saveTasks();
		},

		handleChipClick(taskId, url) {
			if (!this.tasks[taskId]) {
				if (url && url !== '#') {
					window.open(url, '_blank');
				}
				this.tasks[taskId] = true;
			} else {
				this.tasks[taskId] = false;
			}
			this.saveTasks();
		},

		openEntryModal() {
			this.errorMessage = '';
			this.showModal = true;
		},

		closeModal() {
			this.showModal = false;
			this.errorMessage = '';
		},

		resetEntry() {
			if (confirm('Clear saved ticket on this browser to submit a new entry?')) {
				this.myTicket = null;
				try {
					localStorage.removeItem('aws_giveaway_ticket');
				} catch(e) {}
				this.errorMessage = '';
				this.showModal = true;
			}
		},

		submitEntry() {
			const totalTasks = <?php echo count( $tasks ); ?>;
			if (totalTasks > 0 && this.getCompletedCount() < totalTasks) {
				this.errorMessage = 'Please complete and check all required tasks to enter!';
				return;
			}

			if (!this.form.agree_verification) {
				this.errorMessage = 'Please agree to the verification rule to enter.';
				return;
			}

			this.submitting = true;
			this.errorMessage = '';

			fetch('<?php echo esc_url( rest_url( 'aws-sbg/v1/giveaway/enter' ) ); ?>', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					full_name: this.form.full_name,
					email: this.form.email,
					phone: this.form.phone,
					website_url: this.form.website_url,
					source: this.source,
				})
			})
			.then(r => r.json().then(data => ({ status: r.status, body: data })))
			.then(({ status, body }) => {
				this.submitting = false;

				if (status >= 400 || !body.success) {
					this.errorMessage = body.message || 'Error submitting your entry.';
					return;
				}

				this.myTicket = {
					ticket_number: body.ticket_number,
					full_name: body.full_name,
					email: body.email,
					created_at: body.created_at,
				};

				try {
					localStorage.setItem('aws_giveaway_ticket', JSON.stringify(this.myTicket));
				} catch(e) {}

				if (!body.already_entered && body.live_count) {
					this.liveCount = body.live_count;
				}

				this.triggerConfettiCelebration();
			})
			.catch(err => {
				this.submitting = false;
				this.errorMessage = 'Network error. Please try again.';
			});
		},

		checkTicketStatus() {
			if (!this.checkQuery.trim()) return;
			this.checking = true;
			this.checkResult = null;

			fetch('<?php echo esc_url( rest_url( 'aws-sbg/v1/giveaway/check-ticket' ) ); ?>?q=' + encodeURIComponent(this.checkQuery.trim()))
				.then(r => r.json())
				.then(res => {
					this.checking = false;
					this.checkResult = res;
				})
				.catch(() => {
					this.checking = false;
					this.checkResult = { found: false, message: 'Unable to verify status.' };
				});
		},

		triggerConfettiCelebration() {
			if (typeof confetti === 'function') {
				confetti({
					particleCount: 80,
					spread: 70,
					origin: { y: 0.6 },
					colors: ['#9353ff', '#ff9900', '#00ef88', '#ffffff']
				});
			}
		}
	};
}
</script>

<?php
get_footer();
