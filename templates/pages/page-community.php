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

$channels = array(
	array(
		'name' => 'WhatsApp',
		'desc' => 'Questions, lab help, and instant event alerts from other members.',
		'cta'  => 'Join the group',
		'url'  => 'https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI',
	),
	array(
		'name' => 'LinkedIn',
		'desc' => 'Certifications, student wins, and alumni now working in cloud.',
		'cta'  => 'Follow the page',
		'url'  => 'https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/',
	),
	array(
		'name' => 'Instagram',
		'desc' => 'Event reels, workshop recaps, and campus moments.',
		'cta'  => 'Follow the account',
		'url'  => 'https://www.instagram.com/awssbgairuniversity/',
	),
);

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
	.community-page-wrapper .cp-intro { margin-top: 12px; max-width: 56ch; font-size: 1rem; }
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
	.community-page-wrapper .cp-mark { flex: 0 0 auto; width: 10px; height: 10px; background: var(--accent); }

	/* Reward value breakdown */
	.community-page-wrapper .cp-values { list-style: none; margin: 26px 0 0; padding: 0; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-value-row {
		display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between;
		gap: 6px 16px; padding: 18px 0; border-bottom: 1px solid var(--line);
	}
	.community-page-wrapper .cp-value-main { flex: 1 1 220px; min-width: 0; }
	.community-page-wrapper .cp-value-label { font-weight: 800; color: var(--ink); }
	.community-page-wrapper .cp-value-sub { display: block; margin-top: 4px; font-size: 0.9375rem; color: var(--muted); }
	.community-page-wrapper .cp-value-amount { flex: 0 0 auto; font-weight: 800; color: var(--ink); font-variant-numeric: tabular-nums; }
	.community-page-wrapper .cp-value-row-total { border-bottom: 0; }
	.community-page-wrapper .cp-value-row-total .cp-value-label,
	.community-page-wrapper .cp-value-row-total .cp-value-amount { font-size: 1.125rem; }
	.community-page-wrapper .cp-source { margin-top: 12px; font-size: 0.8125rem; color: var(--muted); }

	/* Channels */
	.community-page-wrapper .cp-channels { list-style: none; margin: 26px 0 0; padding: 0; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-channels li { border-bottom: 1px solid var(--line); }
	.community-page-wrapper .cp-channels a {
		display: flex; flex-wrap: wrap; align-items: center; gap: 6px 18px;
		min-height: 56px; padding: 18px 6px; color: var(--ink);
		transition: background-color 0.18s ease;
	}
	.community-page-wrapper .cp-channels a:hover { background: var(--surface-2); }
	.community-page-wrapper .cp-ch-name { flex: 0 0 auto; font-weight: 800; }
	.community-page-wrapper .cp-ch-desc { flex: 1 1 260px; color: var(--muted); font-size: 0.9375rem; }
	.community-page-wrapper .cp-ch-go { flex: 0 0 auto; color: var(--brand); font-weight: 700; font-size: 0.875rem; }

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
	.community-page-wrapper .cp-gw-steps { list-style: none; margin: 0 0 18px; padding: 0; display: grid; gap: 14px; }
	.community-page-wrapper .cp-gw-step { display: flex; gap: 12px; }
	.community-page-wrapper .cp-gw-step-n {
		flex: 0 0 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;
		background: var(--ink); color: #FFFFFF; font-size: 0.75rem; font-weight: 800; border-radius: 6px;
	}
	.community-page-wrapper .cp-gw-step h3 { font-size: 0.9375rem; }
	.community-page-wrapper .cp-gw-step p { margin-top: 2px; font-size: 0.875rem; color: var(--muted); }
	.community-page-wrapper .cp-gw-step a { font-weight: 700; }
	.community-page-wrapper .cp-gw-fields { display: grid; gap: 14px; }
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
		.community-page-wrapper .cp-value-row { padding: 22px 0; }
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
				<p class="cp-kicker">AWS Student Builder Group, Air University Islamabad</p>
				<h1>Learn cloud skills with other Air University students</h1>
				<p class="cp-lead">
					We are the official AWS student chapter on campus. We run hands-on labs, certification
					prep, and community meetups, and we help members claim the AWS student rewards.
				</p>
				<div class="cp-actions">
					<a class="cp-btn cp-btn-primary"
					   href="https://www.meetup.com/aws-sbg-at-air-university-e9-campus-islamabad/"
					   target="_blank" rel="noopener noreferrer">
						Join on Meetup
					</a>
					<a class="cp-btn cp-btn-secondary"
					   href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI"
					   target="_blank" rel="noopener noreferrer">
						Join the WhatsApp group
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

	<!-- Rewards -->
	<section class="cp-section" id="rewards">
		<div class="cp-wrap">
			<h2 class="cp-h2">Student rewards</h2>
			<p class="cp-intro">Verified students get $579 in total value: 12 months of Skill Builder, $30 in AWS credits, and a $100 certification voucher. Verification is free and needs no credit card.</p>

			<ul class="cp-values">
				<li class="cp-value-row">
					<div class="cp-value-main">
						<span class="cp-value-label">12 months of Skill Builder Premium</span>
						<span class="cp-value-sub">Unlocked when you verify and complete your profile</span>
					</div>
					<span class="cp-value-amount">$449</span>
				</li>
				<li class="cp-value-row">
					<div class="cp-value-main">
						<span class="cp-value-label">AWS credits at 7 badges</span>
						<span class="cp-value-sub">Reach 7 badges on Builder Center</span>
					</div>
					<span class="cp-value-amount">$10</span>
				</li>
				<li class="cp-value-row">
					<div class="cp-value-main">
						<span class="cp-value-label">AWS credits at 14 badges</span>
						<span class="cp-value-sub">Reach 14 badges on Builder Center</span>
					</div>
					<span class="cp-value-amount">$20</span>
				</li>
				<li class="cp-value-row">
					<div class="cp-value-main">
						<span class="cp-value-label">AWS certification exam voucher at 21 badges</span>
						<span class="cp-value-sub">Reach 21 badges on Builder Center</span>
					</div>
					<span class="cp-value-amount">$100</span>
				</li>
				<li class="cp-value-row cp-value-row-total">
					<div class="cp-value-main">
						<span class="cp-value-label">Total potential value</span>
					</div>
					<span class="cp-value-amount">$579</span>
				</li>
			</ul>
			<p class="cp-source">Values from the AWS student rewards program.</p>

			<p class="cp-note">Three entries will be picked at random. Each winner gets an AWS swag bundle.</p>

			<?php if ( $giveaway ) : ?>
				<div class="cp-actions">
					<a class="cp-btn cp-btn-primary" href="<?php echo esc_url( $giveaway_url ); ?>" data-cp-open>
						<span class="cp-mark" aria-hidden="true"></span>Claim &amp; Enter Giveaway
					</a>
				</div>
			<?php else : ?>
				<p class="cp-note">Giveaway entries open soon. Check back during Freshers Week.</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Channels -->
	<section class="cp-section cp-section-alt" id="channels">
		<div class="cp-wrap">
			<h2 class="cp-h2">Where the chapter talks</h2>
			<p class="cp-intro">Three places we post updates. Join the one you will actually read.</p>

			<ul class="cp-channels">
				<?php foreach ( $channels as $channel ) : ?>
					<li>
						<a href="<?php echo esc_url( $channel['url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<span class="cp-ch-name"><?php echo esc_html( $channel['name'] ); ?></span>
							<span class="cp-ch-desc"><?php echo esc_html( $channel['desc'] ); ?></span>
							<span class="cp-ch-go">
								<?php echo esc_html( $channel['cta'] ); ?><span aria-hidden="true">&#8599;</span>
								<span class="cp-vh">(opens in a new tab)</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
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
				<h2 class="cp-modal-title" id="cp-giveaway-title"><?php echo esc_html( $gw_success ? "You're in" : 'Claim and enter the giveaway' ); ?></h2>

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
							<span class="cp-gw-step-n" aria-hidden="true">1</span>
							<div>
								<h3>Create your Builder Center account</h3>
								<a href="https://builder.aws.com/?inviteId=daab681a-b41c-43d0-bfcb-b21f2993b354" target="_blank" rel="noopener noreferrer">Open Builder Center<span class="cp-vh"> (opens in a new tab)</span></a>
							</div>
						</li>
						<li class="cp-gw-step">
							<span class="cp-gw-step-n" aria-hidden="true">2</span>
							<div>
								<h3>Verify your student status</h3>
								<a href="https://builder.aws.com/student-rewards" target="_blank" rel="noopener noreferrer">Open student rewards<span class="cp-vh"> (opens in a new tab)</span></a>
							</div>
						</li>
					</ol>

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
						<button type="submit" class="cp-btn cp-btn-primary cp-gw-submit">
							<span class="cp-mark" aria-hidden="true"></span>Enter the giveaway
						</button>
						<p class="cp-note">Your details are only used to contact the winners.</p>
					</form>
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
		var titles = { form: 'Claim and enter the giveaway', success: "You're in" };
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
