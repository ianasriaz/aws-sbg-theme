<?php
/**
 * AWS SBG Brand Kit
 * Embeds the built re:Invent design kit (reinvent-kit Vite bundle) as a
 * full-screen WordPress admin tool under the AWS SBG Suite menu.
 *
 * Kit assets live in assets/design-kit/ and are loaded only on this page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. LOAD THE KIT ONLY ON THE BRAND KIT ADMIN PAGE
 *    Printed manually (not wp_enqueue_script) because the Vite bundle is an
 *    ES module and needs <script type="module">, which WP core enqueue
 *    doesn't support on all deployed versions.
 */
function aws_sbg_design_kit_load_assets() {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'aws-brand-kit' !== $page ) {
		return;
	}

	$dir = get_template_directory() . '/assets/design-kit';
	if ( ! file_exists( $dir . '/design-kit.js' ) ) {
		echo '<div class="wrap"><p><strong>Brand Kit assets missing.</strong> Expected: assets/design-kit/design-kit.js — rebuild reinvent-kit and copy its dist into the theme.</p></div>';
		return;
	}

	$ver = (string) filemtime( $dir . '/design-kit.js' );
	$uri = get_template_directory_uri() . '/assets/design-kit';

	printf(
		'<link rel="stylesheet" id="aws-sbg-design-kit-css" href="%1$s/design-kit.css?ver=%2$s" />',
		esc_url( $uri ),
		esc_attr( $ver )
	);

	// Blend the admin shell into the kit's dark canvas (kit CSS resets are global).
	printf(
		'<style id="aws-sbg-design-kit-canvas">%s</style>',
		'body.wp-admin { background:#1a1225 !important; }
		 #wpcontent, #wpbody-content { padding-left:0 !important; }
		 #wpbody-content { padding-bottom:0 !important; }
		 #root { height:100vh; }'
	);

	// Reserved for phase 2: hydrate card data from WordPress.
	printf(
		'<script>window.REINVENT_KIT_ENV = %s;</script>',
		wp_json_encode( array(
			'wpAdminUrl' => admin_url(),
		) )
	);

	printf(
		'<script type="module" id="aws-sbg-design-kit-js" src="%1$s/design-kit.js?ver=%2$s"></script>',
		esc_url( $uri ),
		esc_attr( $ver )
	);
}
add_action( 'admin_enqueue_scripts', 'aws_sbg_design_kit_load_assets', 5 );

/**
 * 2. RENDER THE BRAND KIT PAGE
 */
function aws_sbg_render_design_kit_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div id="root"></div>
	<?php
}
