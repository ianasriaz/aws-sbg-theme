<?php
/**
 * Template Name: Community Hub & Student Rewards
 *
 * Community landing page: what the chapter is, the AWS student rewards, the guided
 * giveaway entry, where the chapter talks, and answers. Reached mainly by poster QR
 * scans on phones.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$swag_kit_url = file_exists( get_theme_file_path( 'assets/images/AWS-Swags-Kit.webp' ) )
	? get_theme_file_uri( 'assets/images/AWS-Swags-Kit.webp' )
	: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';

$src          = isset( $_GET['src'] ) ? sanitize_key( wp_unslash( $_GET['src'] ) ) : '';
$giveaway_url = home_url( '/giveaway/' );
if ( '' !== $src ) {
	$giveaway_url = add_query_arg( 'src', $src, $giveaway_url );
}

$giveaway = null;
if ( function_exists( 'aws_sbg_get_giveaways' ) ) {
	$open_giveaways = aws_sbg_get_giveaways( array( 'open' ) );
	if ( ! empty( $open_giveaways ) ) {
		$giveaway = $open_giveaways[0];
	}
}

$gw_status = isset( $_GET['gw_status'] ) ? sanitize_key( $_GET['gw_status'] ) : '';
$gw_ticket = isset( $_GET['gw_ticket'] ) ? sanitize_text_field( $_GET['gw_ticket'] ) : '';
$gw_name   = isset( $_GET['gw_name'] ) ? sanitize_text_field( $_GET['gw_name'] ) : '';
$gw_error  = isset( $_GET['gw_error'] ) ? sanitize_key( $_GET['gw_error'] ) : '';

$gw_errors = array(
	'closed'   => 'This giveaway is no longer accepting entries.',
	'spam'     => 'Your submission could not be processed.',
	'required' => 'Please fill in every required field.',
	'email'    => 'That email address does not look valid.',
	'domain'   => 'Only Air University student emails (@students.au.edu.pk) are accepted.',
	'db'       => 'We could not save your entry. Please try again.',
	'filesize' => 'That screenshot is larger than 2 MB.',
	'filetype' => 'Uploads must be a JPG, PNG or WebP image.',
	'upload'   => 'There was a problem saving your upload. Please try again.',
	'reqfile'  => 'Please upload the required screenshot.',
	'reqproof' => 'Please complete the required proof field.',
	'reqcheck' => 'Please confirm all required items.',
);

$gw_success = in_array( $gw_status, array( 'success', 'exists' ), true ) && '' !== $gw_ticket;
$gw_failed  = ( 'error' === $gw_status );
$gw_message = $gw_failed ? ( $gw_errors[ $gw_error ] ?? 'Something went wrong. Please try again.' ) : '';

$faqs = array(
	array(
		'q' => 'Who can claim the student rewards?',
		'a' => 'Any currently enrolled student at an accredited university or college. AWS verifies enrollment through SheerID.',
	),
	array(
		'q' => 'Do I need a credit card?',
		'a' => 'No. Verification only needs your university email address or proof of enrollment through SheerID.',
	),
	array(
		'q' => 'How long does SheerID verification take?',
		'a' => 'It is usually instant with a university email address. If a document review is needed, it normally finishes within 24 to 48 hours.',
	),
	array(
		'q' => 'Which exams does the voucher cover?',
		'a' => 'Either AWS Certified Cloud Practitioner (CLF-C02) or AWS Certified AI Practitioner (AIF-C01).',
	),
	array(
		'q' => 'What if I get stuck during verification?',
		'a' => 'Ask in the WhatsApp group. Chapter leads and other members walk people through it.',
	),
);
?>

<style>
	.community-page-wrapper {
		--ink: #0F141F;
		--ink-2: #334155;
		--muted: #475569;
		--line: #E2E8F0;
		--paper: #FFFFFF;
		--surface: #F8FAFC;
		--surface-2: #F1F5F9;
		--brand: #7928CA;
		--accent: #FF9900;
		--danger: #B91C1C;
		--wrap: 1080px;

		background: var(--paper);
		color: var(--ink-2);
		font-family: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		line-height: 1.6;
		min-height: 100vh;
		overflow-x: hidden;
		-webkit-font-smoothing: antialiased;
	}
	.community-page-wrapper * { box-sizing: border-box; }
	.community-page-wrapper .cp-wrap { width: 100%; max-width: var(--wrap); margin: 0 auto; padding: 0 20px; }
	.community-page-wrapper h1,
	.community-page-wrapper h2,
	.community-page-wrapper h3 { margin: 0; font-weight: 800; letter-spacing: -0.02em; color: var(--ink); }
	.community-page-wrapper p { margin: 0; color: var(--ink-2); }
	.community-page-wrapper a { color: var(--brand); text-decoration: none; }
	.community-page-wrapper img { display: block; max-width: 100%; height: auto; }
	.community-page-wrapper :focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; border-radius: 2px; }
	.community-page-wrapper .cp-vh {
		position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
		overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
	}

	/* Section frame */
	.community-page-wrapper .cp-section { padding: 56px 0; }
	.community-page-wrapper .cp-section-alt {
		background: var(--surface);
		border-top: 1px solid var(--line);
		border-bottom: 1px solid var(--line);
	}
	.community-page-wrapper .cp-h2 { font-size: clamp(1.6rem, 4.2vw, 2.25rem); line-height: 1.15; }
	.community-page-wrapper .cp-note { margin-top: 12px; font-size: 0.9375rem; color: var(--muted); }

	/* Hero */
	.community-page-wrapper .cp-hero { padding: 48px 0 8px; }
	.community-page-wrapper .cp-hero-grid { display: grid; gap: 32px; }
	.community-page-wrapper .cp-kicker { font-size: 0.875rem; font-weight: 700; color: var(--muted); }
	.community-page-wrapper .cp-hero h1 {
		margin-top: 14px;
		font-size: clamp(2rem, 6vw, 3.25rem);
		line-height: 1.05;
	}
	.community-page-wrapper .cp-lead { margin-top: 18px; max-width: 52ch; font-size: 1.0625rem; }
	.community-page-wrapper .cp-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
	.community-page-wrapper .cp-hero-media { margin: 0; }
	.community-page-wrapper .cp-hero-media img { width: 100%; max-width: 460px; margin: 0 auto; }

	/* Buttons */
	.community-page-wrapper .cp-btn {
		display: inline-flex; align-items: center; gap: 10px;
		min-height: 48px; padding: 13px 22px; border-radius: 10px;
		font-weight: 700; font-size: 0.9375rem; line-height: 1.2;
		transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-btn-primary { background: var(--ink); color: #FFFFFF; }
	.community-page-wrapper .cp-btn-primary:hover { background: #232B3A; }
	.community-page-wrapper .cp-btn-secondary { background: var(--paper); color: var(--ink); border: 1.5px solid #64748B; }
	.community-page-wrapper .cp-btn-secondary:hover { border-color: var(--ink); background: var(--surface-2); }
	.community-page-wrapper .cp-btn-accent { background: var(--accent); color: var(--ink); }
	.community-page-wrapper .cp-btn-accent:hover { background: #FFB33A; }

	/* Giveaway band */
	.community-page-wrapper .cp-giveaway { background: var(--ink); color: #FFFFFF; }
	.community-page-wrapper .cp-giveaway .cp-h2 { color: #FFFFFF; }
	.community-page-wrapper .cp-giveaway-lead { margin-top: 14px; max-width: 60ch; font-size: 1.0625rem; color: #CBD5E1; }
	.community-page-wrapper .cp-highlights { list-style: none; margin: 32px 0 0; padding: 0; display: grid; gap: 20px 28px; }
	.community-page-wrapper .cp-highlight { border-top: 1px solid rgba(255, 255, 255, 0.22); padding-top: 16px; }
	.community-page-wrapper .cp-highlight-value {
		font-size: clamp(1.7rem, 4vw, 2.25rem); font-weight: 900; line-height: 1.05;
		color: #FFFFFF; font-variant-numeric: tabular-nums;
	}
	.community-page-wrapper .cp-highlight-label { margin-top: 8px; font-size: 0.9375rem; color: #CBD5E1; }
	.community-page-wrapper .cp-giveaway-note { margin-top: 28px; font-size: 0.9375rem; color: #CBD5E1; }
	.community-page-wrapper .cp-giveaway-source { margin-top: 8px; font-size: 0.8125rem; color: #94A3B8; }
	.community-page-wrapper .cp-giveaway .cp-actions { margin-top: 26px; }

	/* Stay connected */
	.community-page-wrapper .cp-section-connect { padding: 48px 0; }
	.community-page-wrapper .cp-connect { text-align: center; }
	.community-page-wrapper .cp-connect-title {
		font-size: clamp(1.35rem, 3.2vw, 1.85rem);
		font-weight: 900;
		letter-spacing: 0.02em;
		text-transform: uppercase;
	}
	.community-page-wrapper .cp-social {
		list-style: none; margin: 24px 0 0; padding: 0;
		display: flex; flex-wrap: wrap; justify-content: center; gap: 14px;
	}
	.community-page-wrapper .cp-social-link {
		display: inline-flex; align-items: center; justify-content: center;
		width: 56px; height: 56px; border-radius: 12px;
		border: 1px solid var(--line); background: var(--paper); color: var(--ink);
		transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-social-link:hover { background: var(--ink); border-color: var(--ink); color: #FFFFFF; }
	.community-page-wrapper .cp-social-link svg { width: 26px; height: 26px; }

	/* FAQ */
	.community-page-wrapper .cp-faq { margin-top: 26px; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-faq-item { border-bottom: 1px solid var(--line); }
	.community-page-wrapper .cp-faq-q {
		display: flex; align-items: center; justify-content: space-between; gap: 16px;
		width: 100%; min-height: 56px; padding: 20px 6px;
		background: none; border: 0; cursor: pointer; text-align: left;
		font: inherit; font-weight: 700; font-size: 1.0625rem; color: var(--ink);
		transition: color 0.18s ease;
	}
	.community-page-wrapper .cp-faq-q:hover { color: var(--brand); }
	.community-page-wrapper .cp-faq-icon { position: relative; flex: 0 0 auto; width: 18px; height: 18px; }
	.community-page-wrapper .cp-faq-icon::before,
	.community-page-wrapper .cp-faq-icon::after { content: ""; position: absolute; background: var(--muted); }
	.community-page-wrapper .cp-faq-icon::before { left: 0; right: 0; top: 8px; height: 2px; }
	.community-page-wrapper .cp-faq-icon::after { top: 0; bottom: 0; left: 8px; width: 2px; transition: transform 0.18s ease; }
	.community-page-wrapper .cp-faq-q[aria-expanded="true"] .cp-faq-icon::after { transform: scaleY(0); }
	.community-page-wrapper .cp-faq-a { overflow: hidden; max-height: 0; transition: max-height 0.22s ease; }
	.community-page-wrapper .cp-faq-a p { padding: 0 6px 22px; max-width: 62ch; color: var(--muted); font-size: 0.9375rem; }

	/* Giveaway popup */
	.community-page-wrapper .cp-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
	.community-page-wrapper .cp-modal { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; }
	.community-page-wrapper .cp-modal.is-open { display: flex; }
	.community-page-wrapper .cp-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 20, 31, 0.6); }
	.community-page-wrapper .cp-modal-card {
		position: relative; width: 100%; max-width: 440px; max-height: 90vh; overflow-y: auto;
		background: var(--paper); border: 1px solid var(--line); border-radius: 14px;
		padding: 26px 22px; box-shadow: 0 24px 60px -24px rgba(15, 20, 31, 0.45);
	}
	.community-page-wrapper .cp-modal-x {
		position: absolute; top: 12px; right: 12px; width: 36px; height: 36px;
		display: inline-flex; align-items: center; justify-content: center;
		border: 1px solid var(--line); background: var(--paper); color: var(--ink-2);
		border-radius: 8px; font: inherit; font-size: 0.875rem; font-weight: 700; cursor: pointer;
	}
	.community-page-wrapper .cp-modal-x:hover { background: var(--surface-2); }
	.community-page-wrapper .cp-modal-title { font-size: 1.25rem; line-height: 1.25; padding-right: 40px; }
	.community-page-wrapper .cp-modal-panel { margin-top: 14px; }
	.community-page-wrapper .cp-gw-steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; }
	.community-page-wrapper .cp-gw-step { border: 1px solid var(--line); border-radius: 10px; padding: 14px; }
	.community-page-wrapper .cp-gw-step-head { display: flex; align-items: center; gap: 10px; }
	.community-page-wrapper .cp-gw-step-n {
		flex: 0 0 24px; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;
		background: var(--ink); color: #FFFFFF; font-size: 0.75rem; font-weight: 800; border-radius: 6px;
	}
	.community-page-wrapper .cp-gw-step h3 { font-size: 0.9375rem; }
	.community-page-wrapper .cp-gw-step-btn { margin-top: 12px; width: 100%; justify-content: center; }
	.community-page-wrapper .cp-gw-fields { display: grid; gap: 14px; margin-top: 12px; }
	.community-page-wrapper .cp-gw-field { display: flex; flex-direction: column; gap: 6px; }
	.community-page-wrapper .cp-gw-field label,
	.community-page-wrapper .cp-gw-upload label { font-size: 0.875rem; font-weight: 700; color: var(--ink); }
	.community-page-wrapper .cp-gw-input {
		width: 100%; padding: 11px 12px; border: 1.5px solid #64748B; border-radius: 8px;
		font: inherit; font-size: 1rem; color: var(--ink); background: var(--paper);
	}
	.community-page-wrapper .cp-gw-input:focus { border-color: var(--ink); }
	.community-page-wrapper .cp-gw-upload { margin-top: 16px; }
	.community-page-wrapper .cp-gw-help { margin-top: 4px; font-size: 0.8125rem; color: var(--muted); }
	.community-page-wrapper .cp-gw-file {
		display: block; width: 100%; margin-top: 8px; padding: 10px 12px;
		border: 1.5px dashed #64748B; border-radius: 8px; background: var(--surface);
		color: var(--ink-2); font: inherit; font-size: 0.875rem;
	}
	.community-page-wrapper .cp-gw-check { display: flex; align-items: flex-start; gap: 10px; margin-top: 16px; font-size: 0.9375rem; color: var(--ink-2); }
	.community-page-wrapper .cp-gw-check input { flex: 0 0 auto; width: 20px; height: 20px; margin: 1px 0 0; accent-color: var(--brand); }
	.community-page-wrapper .cp-star { color: var(--danger); }
	.community-page-wrapper .cp-gw-error { margin-top: 14px; font-size: 0.875rem; font-weight: 700; color: var(--danger); }
	.community-page-wrapper .cp-gw-submit { width: 100%; justify-content: center; margin-top: 18px; }
	.community-page-wrapper .cp-gw-ticket {
		margin: 16px 0; padding: 16px; text-align: center;
		border: 1px solid var(--line); border-radius: 10px; background: var(--surface);
	}
	.community-page-wrapper .cp-gw-ticket-label { display: block; font-size: 0.75rem; font-weight: 800; color: var(--muted); }
	.community-page-wrapper .cp-gw-ticket-num { font-size: 1.5rem; font-weight: 800; color: var(--ink); font-variant-numeric: tabular-nums; }
	.community-page-wrapper .cp-gw-ticket-name { margin-top: 4px; font-size: 0.875rem; color: var(--ink-2); }
	body.cp-modal-open { overflow: hidden; }

	@media (min-width: 768px) {
		.community-page-wrapper .cp-section { padding: 88px 0; }
		.community-page-wrapper .cp-hero { padding: 88px 0 16px; }
		.community-page-wrapper .cp-section-connect { padding: 64px 0; }
		.community-page-wrapper .cp-highlights { grid-template-columns: repeat(3, 1fr); }
	}
	@media (min-width: 900px) {
		.community-page-wrapper .cp-hero-grid { grid-template-columns: 1.05fr 0.95fr; align-items: center; gap: 56px; }
		.community-page-wrapper .cp-hero-media img { margin: 0; }
	}
	@media (prefers-reduced-motion: reduce) {
		.community-page-wrapper .cp-faq-a,
		.community-page-wrapper .cp-faq-icon::after { transition: none; }
	}
</style>

<main class="community-page-wrapper">

	<!-- Hero -->
	<header class="cp-hero">
		<div class="cp-wrap cp-hero-grid">
			<div>
				<p class="cp-kicker">Air University Islamabad</p>
				<h1>Win AWS swag</h1>
				<p class="cp-lead">
					AWS Student Builder Group is the official student-led AWS chapter on campus. We run
					hands-on labs, certification preparation, events, and community meetups so members
					build together and grow into cloud careers.
				</p>
				<div class="cp-actions">
					<a class="cp-btn cp-btn-primary"
					   href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI"
					   target="_blank" rel="noopener noreferrer">
						Join Community
					</a>
					<a class="cp-btn cp-btn-secondary" href="#giveaway">
						Giveaway
					</a>
				</div>
			</div>
			<figure class="cp-hero-media">
				<img src="<?php echo esc_url( $swag_kit_url ); ?>"
				     onerror="this.onerror=null;this.src='https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';"
				     alt="AWS swag kit"
				     loading="eager"
				     fetchpriority="high">
			</figure>
		</div>
	</header>

	<!-- Giveaway -->
	<section class="cp-section cp-giveaway" id="giveaway">
		<div class="cp-wrap">
			<h2 class="cp-h2">Join AWS Builder Center and enter the giveaway</h2>
			<p class="cp-giveaway-lead">
				AWS Builder Center is the free student hub from AWS. Verify your student status to
				unlock $579 in rewards, then upload a screenshot of your verified profile to enter the
				Freshers Week swag giveaway. No credit card.
			</p>

			<ul class="cp-highlights">
				<li class="cp-highlight">
					<div class="cp-highlight-value">$449</div>
					<div class="cp-highlight-label">12 months of Skill Builder Premium</div>
				</li>
				<li class="cp-highlight">
					<div class="cp-highlight-value">$30</div>
					<div class="cp-highlight-label">AWS credits at 7 and 14 badges</div>
				</li>
				<li class="cp-highlight">
					<div class="cp-highlight-value">$100</div>
					<div class="cp-highlight-label">AWS certification voucher at 21 badges</div>
				</li>
			</ul>

			<p class="cp-giveaway-note">Three entries are picked at random. Each winner gets an AWS swag bundle.</p>
			<p class="cp-giveaway-source">Values from the AWS student rewards program.</p>

			<?php if ( $giveaway ) : ?>
				<div class="cp-actions">
					<a class="cp-btn cp-btn-accent" href="<?php echo esc_url( $giveaway_url ); ?>" data-cp-open>
						Enter Giveaway
					</a>
				</div>
			<?php else : ?>
				<p class="cp-giveaway-note">Entries open soon. Check back during Freshers Week.</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Stay connected -->
	<section class="cp-section cp-section-alt cp-section-connect" id="channels">
		<div class="cp-wrap cp-connect">
			<h2 class="cp-connect-title">Stay connected</h2>

			<ul class="cp-social">
				<li>
					<a class="cp-social-link" href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp community (opens in a new tab)" title="WhatsApp">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
						</svg>
					</a>
				</li>
				<li>
					<a class="cp-social-link" href="https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn page (opens in a new tab)" title="LinkedIn">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
							<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/>
						</svg>
					</a>
				</li>
				<li>
					<a class="cp-social-link" href="https://www.instagram.com/awssbgairuniversity/" target="_blank" rel="noopener noreferrer" aria-label="Instagram account (opens in a new tab)" title="Instagram">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
							<rect x="3" y="3" width="18" height="18" rx="5"></rect>
							<circle cx="12" cy="12" r="4"></circle>
							<circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"></circle>
						</svg>
					</a>
				</li>
			</ul>
		</div>
	</section>

	<!-- FAQ -->
	<section class="cp-section" id="faq">
		<div class="cp-wrap">
			<h2 class="cp-h2">Questions</h2>

			<div class="cp-faq">
				<?php foreach ( $faqs as $i => $faq ) :
					$qid = 'cp-faq-q-' . $i;
					$aid = 'cp-faq-a-' . $i;
					?>
					<div class="cp-faq-item">
						<h3>
							<button type="button" class="cp-faq-q" id="<?php echo esc_attr( $qid ); ?>"
							        aria-expanded="false" aria-controls="<?php echo esc_attr( $aid ); ?>">
								<?php echo esc_html( $faq['q'] ); ?>
								<span class="cp-faq-icon" aria-hidden="true"></span>
							</button>
						</h3>
						<div class="cp-faq-a" id="<?php echo esc_attr( $aid ); ?>" role="region"
						     aria-labelledby="<?php echo esc_attr( $qid ); ?>">
							<p><?php echo esc_html( $faq['a'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	if ( $giveaway ) :
		$gid    = $giveaway->ID;
		$fields = function_exists( 'aws_sbg_get_giveaway_fields' ) ? aws_sbg_get_giveaway_fields( $gid ) : array();
		$reqs   = function_exists( 'aws_sbg_get_giveaway_requirements' ) ? aws_sbg_get_giveaway_requirements( $gid ) : array();
		$gstart = $gw_success ? 'success' : ( $gw_failed ? 'form' : 'closed' );
		?>
		<div class="cp-modal" id="cp-giveaway" role="dialog" aria-modal="true" aria-labelledby="cp-giveaway-title" data-initial="<?php echo esc_attr( $gstart ); ?>">
			<div class="cp-modal-backdrop" data-cp-close></div>
			<div class="cp-modal-card">
				<button type="button" class="cp-modal-x" data-cp-close aria-label="Close">&#10005;</button>
				<h2 class="cp-modal-title" id="cp-giveaway-title"><?php echo esc_html( $gw_success ? "You're in" : 'Enter the giveaway' ); ?></h2>

				<div class="cp-modal-panel" data-panel="success" <?php echo $gw_success ? '' : 'hidden'; ?>>
					<p>Your entry is confirmed. Keep this ticket, the winners are picked from it.</p>
					<div class="cp-gw-ticket">
						<span class="cp-gw-ticket-label">Your ticket</span>
						<div class="cp-gw-ticket-num">#<?php echo esc_html( $gw_ticket ); ?></div>
						<?php if ( '' !== $gw_name ) : ?>
							<div class="cp-gw-ticket-name"><?php echo esc_html( $gw_name ); ?></div>
						<?php endif; ?>
					</div>
					<button type="button" class="cp-btn cp-btn-primary cp-gw-submit" data-cp-close>Done</button>
				</div>

				<div class="cp-modal-panel" data-panel="form" <?php echo $gw_success ? 'hidden' : ''; ?>>
					<ol class="cp-gw-steps">
						<li class="cp-gw-step">
							<div class="cp-gw-step-head">
								<span class="cp-gw-step-n" aria-hidden="true">1</span>
								<h3>Create your Builder Center account</h3>
							</div>
							<a class="cp-btn cp-btn-secondary cp-gw-step-btn" href="https://builder.aws.com/?inviteId=daab681a-b41c-43d0-bfcb-b21f2993b354" target="_blank" rel="noopener noreferrer">Open Builder Center<span class="cp-vh"> (opens in a new tab)</span></a>
						</li>
						<li class="cp-gw-step">
							<div class="cp-gw-step-head">
								<span class="cp-gw-step-n" aria-hidden="true">2</span>
								<h3>Verify your student status</h3>
							</div>
							<a class="cp-btn cp-btn-secondary cp-gw-step-btn" href="https://builder.aws.com/student-rewards" target="_blank" rel="noopener noreferrer">Verify student status<span class="cp-vh"> (opens in a new tab)</span></a>
						</li>
						<li class="cp-gw-step">
							<div class="cp-gw-step-head">
								<span class="cp-gw-step-n" aria-hidden="true">3</span>
								<h3>Enter your details</h3>
							</div>

					<form class="cp-gw-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="aws_sbg_giveaway_submit">
						<input type="hidden" name="giveaway_id" value="<?php echo esc_attr( $gid ); ?>">
						<input type="hidden" name="gw_source" value="<?php echo esc_attr( '' !== $src ? $src : 'community' ); ?>">
						<input type="text" name="website_url" tabindex="-1" autocomplete="off" class="cp-hp" aria-hidden="true">
						<?php wp_nonce_field( 'aws_sbg_giveaway_submit', 'giveaway_nonce' ); ?>

						<div class="cp-gw-fields">
							<?php foreach ( $fields as $field ) :
								$fid  = $field['id'];
								$type = in_array( $field['type'], array( 'text', 'email', 'tel' ), true ) ? $field['type'] : 'text';
								?>
								<div class="cp-gw-field">
									<label for="cp-gw-<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' <span class="cp-star">*</span>' : ''; ?></label>
									<input class="cp-gw-input" type="<?php echo esc_attr( $type ); ?>" id="cp-gw-<?php echo esc_attr( $fid ); ?>" name="gw_field[<?php echo esc_attr( $fid ); ?>]" <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
								</div>
							<?php endforeach; ?>
						</div>

						<?php
						foreach ( $reqs as $req ) :
							$rid       = $req['id'];
							$is_reqd   = ! empty( $req['required'] );
							$star      = $is_reqd ? ' <span class="cp-star">*</span>' : '';
							$reqd_attr = $is_reqd ? 'required' : '';

							if ( 'upload' === $req['type'] ) :
								?>
								<div class="cp-gw-upload">
									<label for="cp-gw-file-<?php echo esc_attr( $rid ); ?>"><?php echo esc_html( $req['label'] ) . $star; ?></label>
									<?php if ( ! empty( $req['help'] ) ) : ?>
										<p class="cp-gw-help"><?php echo esc_html( $req['help'] ); ?></p>
									<?php endif; ?>
									<input class="cp-gw-file" type="file" id="cp-gw-file-<?php echo esc_attr( $rid ); ?>" name="gw_file_<?php echo esc_attr( $rid ); ?>" accept="image/png,image/jpeg,image/webp" <?php echo esc_attr( $reqd_attr ); ?>>
								</div>
								<?php
							elseif ( 'proof' === $req['type'] ) :
								?>
								<div class="cp-gw-upload">
									<label class="cp-gw-check" for="cp-gw-check-<?php echo esc_attr( $rid ); ?>">
										<input type="checkbox" id="cp-gw-check-<?php echo esc_attr( $rid ); ?>" name="gw_check[<?php echo esc_attr( $rid ); ?>]" value="1" <?php echo esc_attr( $reqd_attr ); ?>>
										<span><?php echo esc_html( $req['label'] ) . $star; ?></span>
									</label>
									<?php if ( ! empty( $req['help'] ) ) : ?>
										<p class="cp-gw-help"><?php echo esc_html( $req['help'] ); ?></p>
									<?php endif; ?>
									<input class="cp-gw-input" type="text" name="gw_text[<?php echo esc_attr( $rid ); ?>]" placeholder="Paste your link or proof" <?php echo esc_attr( $reqd_attr ); ?>>
								</div>
								<?php
							else :
								?>
								<label class="cp-gw-check" for="cp-gw-check-<?php echo esc_attr( $rid ); ?>">
									<input type="checkbox" id="cp-gw-check-<?php echo esc_attr( $rid ); ?>" name="gw_check[<?php echo esc_attr( $rid ); ?>]" value="1" <?php echo esc_attr( $reqd_attr ); ?>>
									<span><?php echo esc_html( $req['label'] ) . $star; ?></span>
								</label>
								<?php
							endif;
						endforeach;
						?>

						<p class="cp-gw-error" role="alert" aria-live="assertive"><?php echo esc_html( $gw_message ); ?></p>
						<button type="submit" class="cp-btn cp-btn-accent cp-gw-submit">
							Enter Giveaway
						</button>
						<p class="cp-note">Your details are only used to contact the winners.</p>
					</form>
						</li>
					</ol>
				</div>
			</div>
		</div>
	<?php endif; ?>

</main>

<script>
	(function () {
		/* FAQ accordion */
		var faqItems = document.querySelectorAll('.community-page-wrapper .cp-faq-item');
		if (faqItems.length) {
			function collapseFaq(item) {
				var btn = item.querySelector('.cp-faq-q');
				var panel = item.querySelector('.cp-faq-a');
				if (!btn || !panel || btn.getAttribute('aria-expanded') !== 'true') { return; }
				panel.style.maxHeight = panel.scrollHeight + 'px';
				requestAnimationFrame(function () { panel.style.maxHeight = ''; });
				btn.setAttribute('aria-expanded', 'false');
			}

			faqItems.forEach(function (item) {
				var btn = item.querySelector('.cp-faq-q');
				var panel = item.querySelector('.cp-faq-a');
				if (!btn || !panel) { return; }

				panel.addEventListener('transitionend', function () {
					if (btn.getAttribute('aria-expanded') === 'true') { panel.style.maxHeight = 'none'; }
				});

				btn.addEventListener('click', function () {
					var isOpen = btn.getAttribute('aria-expanded') === 'true';
					faqItems.forEach(function (other) { if (other !== item) { collapseFaq(other); } });
					if (isOpen) {
						collapseFaq(item);
					} else {
						panel.style.maxHeight = panel.scrollHeight + 'px';
						btn.setAttribute('aria-expanded', 'true');
					}
				});
			});
		}

		/* Giveaway popup */
		var modal = document.getElementById('cp-giveaway');
		if (!modal) { return; }

		var title = document.getElementById('cp-giveaway-title');
		var titles = { form: 'Enter the giveaway', success: "You're in" };
		var panels = modal.querySelectorAll('[data-panel]');
		var lastFocus = null;
		var focusables = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea';

		function showPanel(name) {
			panels.forEach(function (p) { p.hidden = (p.getAttribute('data-panel') !== name); });
			if (title && titles[name]) { title.textContent = titles[name]; }
		}

		function visibleFocusables() {
			return Array.prototype.filter.call(modal.querySelectorAll(focusables), function (el) {
				return !el.closest('[hidden]');
			});
		}

		function openModal(name) {
			lastFocus = document.activeElement;
			showPanel(name);
			modal.classList.add('is-open');
			document.body.classList.add('cp-modal-open');
			var first = visibleFocusables()[0];
			if (first) { first.focus(); }
		}

		function closeModal() {
			modal.classList.remove('is-open');
			document.body.classList.remove('cp-modal-open');
			if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
		}

		document.querySelectorAll('[data-cp-open]').forEach(function (trigger) {
			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				openModal('form');
			});
		});

		modal.querySelectorAll('[data-cp-close]').forEach(function (el) {
			el.addEventListener('click', function () { closeModal(); });
		});

		document.addEventListener('keydown', function (e) {
			if (!modal.classList.contains('is-open')) { return; }

			if (e.key === 'Escape') {
				closeModal();
				return;
			}

			if (e.key === 'Tab') {
				var list = visibleFocusables();
				if (!list.length) { return; }
				var first = list[0];
				var last = list[list.length - 1];
				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			}
		});

		var initial = modal.getAttribute('data-initial');
		if (initial === 'success' || initial === 'form') {
			openModal(initial);
			if (window.history && window.history.replaceState) {
				window.history.replaceState(null, '', window.location.pathname);
			}
		}
	})();
</script>

<?php
get_footer();
