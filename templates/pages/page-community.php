<?php
/**
 * Template Name: Community Hub & Student Rewards
 *
 * Freshers Week campaign page in a printed campus-notice voice: a masthead hero, the
 * student rewards as a ticket stub, a colophon of channels, and an indexed FAQ. Reached
 * mainly by poster QR scans on phones.
 *
 * @package AWS_SBG_Theme
 */

get_header();

$swag_kit_url = file_exists( get_theme_file_path( 'assets/images/AWS-Swags-Kit.webp' ) )
	? get_theme_file_uri( 'assets/images/AWS-Swags-Kit.webp' )
	: 'https://awsairuniversity.com/wp-content/uploads/2026/09/AWS-Swags-Kit.webp';

$rewards_url = 'https://builder.aws.com/?inviteId=daab681a-b41c-43d0-bfcb-b21f2993b354';

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

$socials = array(
	array(
		'label' => 'WhatsApp community (opens in a new tab)',
		'title' => 'WhatsApp',
		'url'   => 'https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI',
		'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>',
	),
	array(
		'label' => 'LinkedIn page (opens in a new tab)',
		'title' => 'LinkedIn',
		'url'   => 'https://www.linkedin.com/company/aws-student-builder-group-air-university-islamabad/',
		'icon'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>',
	),
	array(
		'label' => 'Instagram account (opens in a new tab)',
		'title' => 'Instagram',
		'url'   => 'https://www.instagram.com/awssbgairuniversity/',
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"></circle></svg>',
	),
);

$render_social = function () use ( $socials ) {
	?>
	<ul class="cp-social">
		<?php foreach ( $socials as $social ) : ?>
			<li>
				<a class="cp-social-link" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>" title="<?php echo esc_attr( $social['title'] ); ?>">
					<?php echo $social['icon']; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
};
?>

<style>
	.community-page-wrapper {
		--ink: #0F141F;
		--ink-2: #334155;
		--muted: #475569;
		--line: #E2E8F0;
		--paper: #FFFFFF;
		--brand: #7928CA;
		--accent: #FF9900;
		--rule-dark: rgba(255, 255, 255, 0.22);
		--wrap: 1080px;
		--serif: 'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, serif;
		--sans: 'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

		background: var(--paper);
		color: var(--ink-2);
		font-family: var(--sans);
		line-height: 1.6;
		min-height: 100vh;
		overflow-x: hidden;
		-webkit-font-smoothing: antialiased;
	}
	.community-page-wrapper * { box-sizing: border-box; }
	.community-page-wrapper .cp-wrap { width: 100%; max-width: var(--wrap); margin: 0 auto; padding: 0 20px; }
	.community-page-wrapper h1,
	.community-page-wrapper h2,
	.community-page-wrapper h3 { margin: 0; color: var(--ink); }
	.community-page-wrapper p { margin: 0; }
	.community-page-wrapper a { color: var(--brand); text-decoration: none; }
	.community-page-wrapper img { display: block; max-width: 100%; height: auto; }
	.community-page-wrapper :focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; border-radius: 2px; }
	.community-page-wrapper .cp-vh {
		position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
		overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
	}
	.community-page-wrapper .cp-label {
		font-family: var(--sans); font-size: 0.6875rem; font-weight: 800;
		letter-spacing: 0.07em; text-transform: uppercase; color: var(--muted);
	}

	/* Hero */
	.community-page-wrapper .cp-hero { padding: 48px 0 8px; }
	.community-page-wrapper .cp-hero-grid { display: grid; gap: 32px; }
	.community-page-wrapper .cp-kicker { font-size: 0.875rem; font-weight: 700; color: var(--muted); }
	.community-page-wrapper .cp-hero h1 {
		margin-top: 14px;
		font-size: clamp(2rem, 6vw, 3.25rem);
		font-weight: 800;
		line-height: 1.02;
		letter-spacing: -0.015em;
		text-transform: uppercase;
		color: var(--ink);
	}
	.community-page-wrapper .cp-lead { margin-top: 18px; max-width: 52ch; font-size: 1.0625rem; color: var(--ink-2); }
	.community-page-wrapper .cp-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
	.community-page-wrapper .cp-hero-media { margin: 0; }
	.community-page-wrapper .cp-hero-media img { width: 100%; max-width: 460px; margin: 0 auto; }

	/* Buttons */
	.community-page-wrapper .cp-btn {
		display: inline-flex; align-items: center; justify-content: center; gap: 10px;
		min-height: 48px; padding: 13px 22px; border-radius: 8px;
		font-family: var(--sans); font-weight: 700; font-size: 0.9375rem; line-height: 1.2;
		transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-btn-primary { background: var(--ink); color: #FFFFFF; }
	.community-page-wrapper .cp-btn-primary:hover { background: #232B3A; }
	.community-page-wrapper .cp-btn-accent { background: var(--accent); color: var(--ink); }
	.community-page-wrapper .cp-btn-accent:hover { background: #FFB33A; }

	/* Rewards ticket */
	.community-page-wrapper .cp-rewards-section { padding: 48px 0 8px; }
	.community-page-wrapper .cp-ticket {
		position: relative; overflow: hidden;
		background: var(--ink); color: #FFFFFF; border-radius: 16px;
		box-shadow: 0 26px 60px -32px rgba(15, 20, 31, 0.6);
	}
	.community-page-wrapper .cp-ticket-body { padding: 30px 24px 4px; }
	.community-page-wrapper .cp-label-dark { color: #94A3B8; }
	.community-page-wrapper .cp-ticket-title {
		margin-top: 12px; font-family: var(--serif); font-weight: 700;
		font-size: clamp(1.6rem, 4.2vw, 2.4rem); line-height: 1.08;
		letter-spacing: -0.01em; text-transform: uppercase; color: #FFFFFF;
	}
	.community-page-wrapper .cp-ticket-lead { margin-top: 14px; max-width: 62ch; font-size: 1rem; color: #CBD5E1; }
	.community-page-wrapper .cp-figures {
		list-style: none; margin: 0; padding: 22px 0 24px;
		display: grid; gap: 22px; border-top: 1px solid var(--rule-dark); margin-top: 26px;
	}
	.community-page-wrapper .cp-figure-value {
		font-family: var(--serif); font-weight: 700; font-size: clamp(2rem, 5vw, 2.75rem);
		line-height: 1; color: #FFFFFF; font-variant-numeric: tabular-nums;
	}
	.community-page-wrapper .cp-figure-label { margin-top: 10px; font-size: 0.875rem; color: #CBD5E1; }
	.community-page-wrapper .cp-ticket-source { padding-bottom: 24px; font-size: 0.8125rem; color: #94A3B8; }

	/* Perforation with punched notches */
	.community-page-wrapper .cp-perf { position: relative; height: 0; border-top: 2px dashed var(--rule-dark); }
	.community-page-wrapper .cp-perf::before,
	.community-page-wrapper .cp-perf::after {
		content: ""; position: absolute; top: -15px; width: 30px; height: 30px;
		border-radius: 50%; background: var(--paper);
	}
	.community-page-wrapper .cp-perf::before { left: -15px; }
	.community-page-wrapper .cp-perf::after { right: -15px; }

	/* Stub */
	.community-page-wrapper .cp-ticket-stub {
		display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;
		gap: 16px; padding: 24px;
	}
	.community-page-wrapper .cp-ticket-stub p { max-width: 46ch; font-size: 0.9375rem; color: #CBD5E1; }
	.community-page-wrapper .cp-ticket-cta { flex: 0 0 auto; }
	.community-page-wrapper .cp-stub { flex: 1 1 320px; }
	.community-page-wrapper .cp-stub-lead { margin-top: 10px; }
	.community-page-wrapper .cp-stub-lead a { color: #FFFFFF; text-decoration: underline; text-underline-offset: 3px; }
	.community-page-wrapper .cp-stub-lead a:hover { color: var(--accent); }

	/* Colophon */
	.community-page-wrapper .cp-colophon { padding: 44px 0; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-colophon-title { color: var(--muted); }
	.community-page-wrapper .cp-social { list-style: none; margin: 18px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 12px; }
	.community-page-wrapper .cp-social-link {
		display: inline-flex; align-items: center; justify-content: center;
		width: 52px; height: 52px; border: 1px solid var(--line); border-radius: 10px;
		background: var(--paper); color: var(--ink);
		transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-social-link:hover { background: var(--ink); border-color: var(--ink); color: #FFFFFF; }
	.community-page-wrapper .cp-social-link svg { width: 24px; height: 24px; }

	/* Indexed FAQ */
	.community-page-wrapper .cp-index { padding: 44px 0 56px; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-index-title {
		font-family: var(--serif); font-weight: 700; font-size: clamp(1.6rem, 4vw, 2.1rem);
		line-height: 1.1; letter-spacing: -0.02em;
	}
	.community-page-wrapper .cp-faq { margin-top: 22px; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-faq-item { border-bottom: 1px solid var(--line); }
	.community-page-wrapper .cp-faq-q {
		display: flex; align-items: center; gap: 14px; width: 100%; min-height: 56px;
		padding: 18px 0; background: none; border: 0; cursor: pointer; text-align: left;
		font: inherit; color: var(--ink);
	}
	.community-page-wrapper .cp-faq-n {
		flex: 0 0 30px; font-family: var(--serif); font-size: 0.875rem; font-weight: 700;
		color: var(--muted); font-variant-numeric: tabular-nums;
	}
	.community-page-wrapper .cp-faq-q-text { flex: 1 1 auto; font-family: var(--sans); font-weight: 700; font-size: 1.0625rem; }
	.community-page-wrapper .cp-faq-q:hover .cp-faq-q-text { color: var(--brand); }
	.community-page-wrapper .cp-faq-icon { position: relative; flex: 0 0 auto; width: 18px; height: 18px; }
	.community-page-wrapper .cp-faq-icon::before,
	.community-page-wrapper .cp-faq-icon::after { content: ""; position: absolute; background: var(--muted); }
	.community-page-wrapper .cp-faq-icon::before { left: 0; right: 0; top: 8px; height: 2px; }
	.community-page-wrapper .cp-faq-icon::after { top: 0; bottom: 0; left: 8px; width: 2px; transition: transform 0.18s ease; }
	.community-page-wrapper .cp-faq-q[aria-expanded="true"] .cp-faq-icon::after { transform: scaleY(0); }
	.community-page-wrapper .cp-faq-a { overflow: hidden; max-height: 0; transition: max-height 0.22s ease; }
	.community-page-wrapper .cp-faq-a p {
		padding: 0 0 22px 44px; max-width: 62ch; color: var(--muted); font-size: 0.9375rem;
	}

	@media (min-width: 640px) {
		.community-page-wrapper .cp-figures { grid-template-columns: repeat(3, 1fr); }
		.community-page-wrapper .cp-figure + .cp-figure { border-left: 1px solid var(--rule-dark); padding-left: 24px; }
	}
	@media (min-width: 768px) {
		.community-page-wrapper .cp-hero { padding: 88px 0 16px; }
		.community-page-wrapper .cp-rewards-section { padding: 64px 0 12px; }
		.community-page-wrapper .cp-ticket-body { padding: 36px 34px 4px; }
		.community-page-wrapper .cp-ticket-stub { padding: 26px 34px; }
		.community-page-wrapper .cp-colophon { padding: 52px 0; }
		.community-page-wrapper .cp-index { padding: 52px 0 72px; }
	}
	@media (min-width: 900px) {
		.community-page-wrapper .cp-hero-grid { grid-template-columns: 1.05fr 0.95fr; align-items: center; gap: 56px; }
		.community-page-wrapper .cp-hero-media img { margin: 0; }
	}
	@media (max-width: 599px) {
		.community-page-wrapper .cp-ticket-stub .cp-ticket-cta { width: 100%; }
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
				<h1>Unlock rewards</h1>
				<p class="cp-lead">
					AWS Student Builder Group is the official student-led AWS chapter on campus. We run
					hands-on labs, certification preparation, events, and community meetups so members
					build together and grow into cloud careers.
				</p>
				<div class="cp-actions">
					<a class="cp-btn cp-btn-primary"
					   href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI"
					   target="_blank" rel="noopener noreferrer">
						Join Community<span class="cp-vh"> (opens in a new tab)</span>
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

	<!-- Student rewards ticket -->
	<section class="cp-rewards-section" id="rewards">
		<div class="cp-wrap">
			<div class="cp-ticket">
				<div class="cp-ticket-body">
					<span class="cp-label cp-label-dark">AWS student rewards</span>
					<h2 class="cp-ticket-title">Claim upto $579 of AWS rewards</h2>
					<p class="cp-ticket-lead">
						Create your AWS Builder account, verify with your student email or documents, and
						claim AWS student rewards. No credit card.
					</p>

					<ul class="cp-figures">
						<li class="cp-figure">
							<div class="cp-figure-value">$449</div>
							<div class="cp-figure-label">12 months of Skill Builder Premium</div>
						</li>
						<li class="cp-figure">
							<div class="cp-figure-value">$30</div>
							<div class="cp-figure-label">AWS credits at 7 and 14 badges</div>
						</li>
						<li class="cp-figure">
							<div class="cp-figure-value">$100</div>
							<div class="cp-figure-label">AWS certification voucher at 21 badges</div>
						</li>
					</ul>
					<p class="cp-ticket-source">Values from the AWS student rewards program.</p>
				</div>

				<div class="cp-perf" aria-hidden="true"></div>

				<div class="cp-ticket-stub">
					<div class="cp-stub">
						<span class="cp-label cp-label-dark">Giveaway ended</span>
						<p class="cp-stub-lead">
							The Freshers Week giveaway has ended. Join our
							<a href="https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI" target="_blank" rel="noopener noreferrer">WhatsApp community<span class="cp-vh"> (opens in a new tab)</span></a>
							for updates.
						</p>
					</div>
					<a class="cp-btn cp-btn-accent cp-ticket-cta" href="<?php echo esc_url( $rewards_url ); ?>" target="_blank" rel="noopener noreferrer">
						Claim Rewards<span class="cp-vh"> (opens in a new tab)</span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Stay connected -->
	<section class="cp-colophon" id="channels">
		<div class="cp-wrap">
			<h2 class="cp-label cp-colophon-title">Stay connected</h2>
			<?php $render_social(); ?>
		</div>
	</section>

	<!-- Questions index -->
	<section class="cp-index" id="faq">
		<div class="cp-wrap">
			<h2 class="cp-index-title">Questions</h2>

			<div class="cp-faq">
				<?php foreach ( $faqs as $i => $faq ) :
					$qid = 'cp-faq-q-' . $i;
					$aid = 'cp-faq-a-' . $i;
					?>
					<div class="cp-faq-item">
						<h3>
							<button type="button" class="cp-faq-q" id="<?php echo esc_attr( $qid ); ?>"
							        aria-expanded="false" aria-controls="<?php echo esc_attr( $aid ); ?>">
								<span class="cp-faq-n" aria-hidden="true"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="cp-faq-q-text"><?php echo esc_html( $faq['q'] ); ?></span>
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
	})();
</script>

<?php
get_footer();
