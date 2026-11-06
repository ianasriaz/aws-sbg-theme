<?php
/**
 * AWS SBG Event Invitation Confirmation System
 * File: feature-invite-confirmation.php
 *
 * Entry point. Loads the reusable Speaker / Guest / AWS Community Leader
 * invitation confirmation engine. One "Invitation Event" post = one form.
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/includes/features/invite/invite-core.php';
require_once get_template_directory() . '/includes/features/invite/invite-handler.php';
require_once get_template_directory() . '/includes/features/invite/invite-render.php';
require_once get_template_directory() . '/includes/features/invite/invite-admin.php';
