<?php
/**
 * Template Name: Community Hub & Student Rewards
 *
 * Freshers Week campaign page in a printed campus-notice voice: a masthead hero, the
 * giveaway as a ticket stub, a colophon of channels, and an indexed FAQ. Reached mainly
 * by poster QR scans on phones.
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
		--surface: #F8FAFC;
		--surface-2: #F1F5F9;
		--brand: #7928CA;
		--accent: #FF9900;
		--danger: #B91C1C;
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
	.community-page-wrapper .cp-btn-secondary { background: transparent; color: var(--ink); border: 1.5px solid #64748B; }
	.community-page-wrapper .cp-btn-secondary:hover { border-color: var(--ink); background: var(--surface-2); }
	.community-page-wrapper .cp-btn-accent { background: var(--accent); color: var(--ink); }
	.community-page-wrapper .cp-btn-accent:hover { background: #FFB33A; }

	/* Giveaway ticket */
	.community-page-wrapper .cp-giveaway-section { padding: 48px 0 8px; }
	.community-page-wrapper .cp-ticket {
		position: relative; overflow: hidden;
		background: var(--ink); color: #FFFFFF; border-radius: 16px;
		box-shadow: 0 26px 60px -32px rgba(15, 20, 31, 0.6);
	}
	.community-page-wrapper .cp-ticket-body { padding: 30px 24px 4px; }
	.community-page-wrapper .cp-label-dark { color: #94A3B8; }
	.community-page-wrapper .cp-ticket-title {
		margin-top: 12px; font-family: var(--serif); font-weight: 700;
		font-size: clamp(1.6rem, 4.2vw, 2.4rem); line-height: 1.1;
		letter-spacing: -0.02em; color: #FFFFFF;
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

	/* Swag prizes */
	.community-page-wrapper .cp-stub { flex: 1 1 320px; }
	.community-page-wrapper .cp-stub-lead { margin-top: 10px; }
	.community-page-wrapper .cp-prizes { list-style: none; margin: 20px 0 0; padding: 0; display: grid; gap: 10px; }
	.community-page-wrapper .cp-prize {
		display: flex; align-items: flex-start; gap: 14px;
		padding: 14px 16px; border: 1px solid var(--rule-dark); border-radius: 10px;
		background: rgba(255, 255, 255, 0.03);
		transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
	}
	.community-page-wrapper .cp-prize:hover {
		border-color: var(--accent);
		background: rgba(255, 153, 0, 0.08);
		box-shadow: 0 16px 32px -24px rgba(0, 0, 0, 0.95);
	}
	.community-page-wrapper .cp-prize-n {
		flex: 0 0 26px; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;
		border: 1px solid var(--accent); border-radius: 6px; color: var(--accent);
		font-family: var(--sans); font-size: 0.75rem; font-weight: 800; font-variant-numeric: tabular-nums;
		transition: background-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-prize:hover .cp-prize-n { background: var(--accent); color: var(--ink); }
	.community-page-wrapper .cp-prize-body { transition: transform 0.18s ease; }
	.community-page-wrapper .cp-prize:hover .cp-prize-body { transform: translateX(3px); }
	.community-page-wrapper .cp-prize-name {
		display: block; font-family: var(--serif); font-size: 1rem; font-weight: 700;
		line-height: 1.2; color: #FFFFFF;
	}
	.community-page-wrapper .cp-prize-items { list-style: none; display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0 0; padding: 0; }
	.community-page-wrapper .cp-prize-items li {
		padding: 3px 10px; border: 1px solid var(--rule-dark); border-radius: 999px;
		font-size: 0.8125rem; color: #CBD5E1; white-space: nowrap;
		transition: border-color 0.18s ease, color 0.18s ease;
	}
	.community-page-wrapper .cp-prize:hover .cp-prize-items li { border-color: rgba(255, 153, 0, 0.55); color: #FFFFFF; }

	/* Prizes reveal as the ticket scrolls into view */
	.community-page-wrapper .cp-prizes.is-anim .cp-prize {
		opacity: 0; transform: translateY(12px);
		transition: opacity 0.45s ease, transform 0.45s ease,
			border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
	}
	.community-page-wrapper .cp-prizes.is-anim.is-in .cp-prize { opacity: 1; transform: none; }
	.community-page-wrapper .cp-prizes.is-anim.is-in .cp-prize:nth-child(2) { transition-delay: 0.08s; }
	.community-page-wrapper .cp-prizes.is-anim.is-in .cp-prize:nth-child(3) { transition-delay: 0.16s; }

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

	/* Socials inside the giveaway success panel */
	.community-page-wrapper .cp-social-block { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--line); }
	.community-page-wrapper .cp-social-note { font-size: 0.9375rem; color: var(--muted); }
	.community-page-wrapper .cp-social-block .cp-social { margin-top: 14px; gap: 10px; }
	.community-page-wrapper .cp-social-block .cp-social-link { width: 46px; height: 46px; }
	.community-page-wrapper .cp-social-block .cp-social-link svg { width: 22px; height: 22px; }

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

	/* Giveaway popup */
	.community-page-wrapper .cp-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
	.community-page-wrapper .cp-modal {
		position: fixed; inset: 0; z-index: 9999;
		display: flex; align-items: center; justify-content: center; padding: 20px;
		visibility: hidden; opacity: 0;
		transition: opacity 0.22s ease, visibility 0s linear 0.22s;
	}
	.community-page-wrapper .cp-modal.is-open { visibility: visible; opacity: 1; transition-delay: 0s; }
	.community-page-wrapper .cp-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 20, 31, 0.62); }
	.community-page-wrapper .cp-modal-grab { display: none; }
	.community-page-wrapper .cp-modal-card {
		position: relative; display: flex; flex-direction: column;
		width: 100%; max-width: 440px; max-height: 90vh; max-height: 90dvh; overflow: hidden;
		background: var(--paper); border: 1px solid var(--line); border-radius: 14px;
		box-shadow: 0 24px 60px -24px rgba(15, 20, 31, 0.45);
		transform: translateY(14px);
		transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1);
	}
	.community-page-wrapper .cp-modal.is-open .cp-modal-card { transform: none; }
	.community-page-wrapper .cp-modal-card:focus-visible { outline: none; }
	.community-page-wrapper .cp-modal-head {
		display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
		padding: 20px 18px 14px; border-bottom: 1px solid var(--line); flex-shrink: 0;
	}
	.community-page-wrapper .cp-modal-title {
		font-family: var(--serif); font-weight: 700; font-size: 1.375rem;
		line-height: 1.2; letter-spacing: -0.02em; margin: 0;
	}
	.community-page-wrapper .cp-modal-x {
		flex: 0 0 auto; width: 44px; height: 44px;
		display: inline-flex; align-items: center; justify-content: center;
		border: 1px solid var(--line); background: var(--paper); color: var(--ink-2);
		border-radius: 8px; font: inherit; font-size: 0.875rem; font-weight: 700; cursor: pointer;
	}
	.community-page-wrapper .cp-modal-x:hover { background: var(--surface-2); }
	.community-page-wrapper .cp-modal-body {
		padding: 16px 18px 22px;
		overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;
	}
	.community-page-wrapper .cp-modal-panel { margin-top: 0; }
	.community-page-wrapper .cp-gw-steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; }
	.community-page-wrapper .cp-gw-step { border: 1px solid var(--line); border-radius: 10px; padding: 14px; }
	.community-page-wrapper .cp-gw-step-head { display: flex; align-items: center; gap: 10px; }
	.community-page-wrapper .cp-gw-step-n {
		flex: 0 0 24px; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;
		background: var(--ink); color: #FFFFFF; font-family: var(--sans); font-size: 0.75rem; font-weight: 800; border-radius: 6px;
	}
	.community-page-wrapper .cp-gw-step h3 { font-family: var(--serif); font-size: 1rem; font-weight: 700; }
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
		margin: 16px 0; padding: 18px; text-align: center;
		border: 1px solid var(--line); border-top: 2px dashed var(--line);
		border-radius: 0 0 10px 10px; background: var(--surface);
	}
	.community-page-wrapper .cp-gw-ticket-label { display: block; font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: var(--muted); }
	.community-page-wrapper .cp-gw-ticket-num { font-family: var(--serif); font-size: 1.75rem; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums; }
	.community-page-wrapper .cp-gw-ticket-name { margin-top: 4px; font-size: 0.875rem; color: var(--ink-2); }
	.community-page-wrapper .cp-note { margin-top: 12px; font-size: 0.9375rem; color: var(--muted); }
	body.cp-modal-open { overflow: hidden; }

	@media (min-width: 640px) {
		.community-page-wrapper .cp-figures { grid-template-columns: repeat(3, 1fr); }
		.community-page-wrapper .cp-figure + .cp-figure { border-left: 1px solid var(--rule-dark); padding-left: 24px; }
	}
	@media (min-width: 768px) {
		.community-page-wrapper .cp-hero { padding: 88px 0 16px; }
		.community-page-wrapper .cp-giveaway-section { padding: 64px 0 12px; }
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
		.community-page-wrapper .cp-modal { padding: 0; align-items: flex-end; }
		.community-page-wrapper .cp-modal-backdrop { background: rgba(15, 20, 31, 0.7); }
		.community-page-wrapper .cp-modal-card {
			width: 100%; max-width: none;
			max-height: 92vh; max-height: 92dvh;
			border: 0; border-radius: 18px 18px 0 0;
			box-shadow: 0 -24px 56px -30px rgba(15, 20, 31, 0.65);
			transform: translateY(100%);
		}
		.community-page-wrapper .cp-modal-grab {
			display: flex; align-items: center; justify-content: center;
			flex: 0 0 auto; width: 100%; height: 22px; padding: 0; margin: 0;
			border: 0; background: none; cursor: pointer;
		}
		.community-page-wrapper .cp-modal-grab span {
			width: 42px; height: 4px; border-radius: 999px; background: #CBD5E1;
		}
		.community-page-wrapper .cp-modal-head { padding: 12px 18px 14px; }
		.community-page-wrapper .cp-modal-body { padding-bottom: calc(28px + env(safe-area-inset-bottom)); }
		.community-page-wrapper .cp-ticket-stub .cp-ticket-cta { width: 100%; }
	}
	@media (prefers-reduced-motion: reduce) {
		.community-page-wrapper .cp-faq-a,
		.community-page-wrapper .cp-faq-icon::after,
		.community-page-wrapper .cp-modal,
		.community-page-wrapper .cp-modal-card,
		.community-page-wrapper .cp-prize,
		.community-page-wrapper .cp-prize-body,
		.community-page-wrapper .cp-prize-n,
		.community-page-wrapper .cp-prize-items li { transition: none; }
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

	<!-- Giveaway ticket -->
	<section class="cp-giveaway-section" id="giveaway">
		<div class="cp-wrap">
			<div class="cp-ticket">
				<div class="cp-ticket-body">
					<span class="cp-label cp-label-dark">Freshers Week giveaway</span>
					<h2 class="cp-ticket-title">Join AWS Builder Center and enter the giveaway</h2>
					<p class="cp-ticket-lead">
						AWS Builder Center is the free student hub from AWS. Verify your student status to
						unlock $579 in rewards, then upload a screenshot of your verified profile to enter.
						No credit card.
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
						<span class="cp-label cp-label-dark">Swag for 3 winners</span>
						<p class="cp-stub-lead">Three entries picked at random. Each winner gets an AWS swag bundle.</p>
						<ul class="cp-prizes">
							<li class="cp-prize">
								<span class="cp-prize-n" aria-hidden="true">1</span>
								<div class="cp-prize-body">
									<span class="cp-prize-name">Winner 1</span>
									<ul class="cp-prize-items">
										<li>AWS bottle</li>
										<li>Pen</li>
									</ul>
								</div>
							</li>
							<li class="cp-prize">
								<span class="cp-prize-n" aria-hidden="true">2</span>
								<div class="cp-prize-body">
									<span class="cp-prize-name">Winner 2</span>
									<ul class="cp-prize-items">
										<li>Pouch</li>
										<li>Stickers sheet</li>
										<li>Pen</li>
									</ul>
								</div>
							</li>
							<li class="cp-prize">
								<span class="cp-prize-n" aria-hidden="true">3</span>
								<div class="cp-prize-body">
									<span class="cp-prize-name">Winner 3</span>
									<ul class="cp-prize-items">
										<li>Mobile holder</li>
										<li>Pen</li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
					<?php if ( $giveaway ) : ?>
						<a class="cp-btn cp-btn-accent cp-ticket-cta" href="<?php echo esc_url( $giveaway_url ); ?>" data-cp-open>
							Enter Giveaway
						</a>
					<?php else : ?>
						<p class="cp-ticket-cta">Entries open soon.</p>
					<?php endif; ?>
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

	<?php
	if ( $giveaway ) :
		$gid    = $giveaway->ID;
		$fields = function_exists( 'aws_sbg_get_giveaway_fields' ) ? aws_sbg_get_giveaway_fields( $gid ) : array();
		$reqs   = function_exists( 'aws_sbg_get_giveaway_requirements' ) ? aws_sbg_get_giveaway_requirements( $gid ) : array();
		$gstart = $gw_success ? 'success' : ( $gw_failed ? 'form' : 'closed' );
		?>
		<div class="cp-modal" id="cp-giveaway" role="dialog" aria-modal="true" aria-labelledby="cp-giveaway-title" data-initial="<?php echo esc_attr( $gstart ); ?>">
			<div class="cp-modal-backdrop" data-cp-close></div>
			<div class="cp-modal-card" tabindex="-1">
				<button type="button" class="cp-modal-grab" data-cp-close tabindex="-1" aria-label="Close">
					<span aria-hidden="true"></span>
				</button>
				<div class="cp-modal-head">
					<h2 class="cp-modal-title" id="cp-giveaway-title"><?php echo esc_html( $gw_success ? "You're in" : 'Enter the giveaway' ); ?></h2>
					<button type="button" class="cp-modal-x" data-cp-close aria-label="Close">&#10005;</button>
				</div>
				<div class="cp-modal-body">
					<div class="cp-modal-panel" data-panel="success" <?php echo $gw_success ? '' : 'hidden'; ?>>
						<p>Your entry is confirmed. Keep this ticket, the winners are picked from it.</p>
						<div class="cp-gw-ticket">
							<span class="cp-gw-ticket-label">Your ticket</span>
							<div class="cp-gw-ticket-num">#<?php echo esc_html( $gw_ticket ); ?></div>
							<?php if ( '' !== $gw_name ) : ?>
								<div class="cp-gw-ticket-name"><?php echo esc_html( $gw_name ); ?></div>
							<?php endif; ?>
						</div>
						<div class="cp-social-block">
							<p class="cp-social-note">Follow our socials to keep updated.</p>
							<?php $render_social(); ?>
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
		var card = modal.querySelector('.cp-modal-card');
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
				return !el.closest('[hidden]') && el.getClientRects().length > 0;
			});
		}

		function openModal(name) {
			lastFocus = document.activeElement;
			showPanel(name);
			modal.classList.add('is-open');
			document.body.classList.add('cp-modal-open');
			if (card) { card.focus(); }
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
				var atStart = document.activeElement === first || document.activeElement === card;
				if (e.shiftKey && atStart) {
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

<script>
	(function () {
		/* Prize list reveal */
		var lists = document.querySelectorAll('.community-page-wrapper .cp-prizes');
		var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (!lists.length || calm || !('IntersectionObserver' in window)) { return; }

		Array.prototype.forEach.call(lists, function (list) {
			list.classList.add('is-anim');

			var observer = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) { return; }
					entry.target.classList.add('is-in');
					observer.unobserve(entry.target);
					window.setTimeout(function () { entry.target.classList.remove('is-anim'); }, 900);
				});
			}, { threshold: 0.25 });

			observer.observe(list);
		});
	})();
</script>

<?php
get_footer();
