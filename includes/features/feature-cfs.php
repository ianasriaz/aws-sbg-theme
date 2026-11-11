<?php
/**
 * AWS SBG Call for Speakers Feature
 * File: feature-cfs.php
 *
 * Loads the Call for Speakers intake, validation, review and admin management system.
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/includes/features/cfs/cfs-core.php';
require_once get_template_directory() . '/includes/features/cfs/cfs-handler.php';
require_once get_template_directory() . '/includes/features/cfs/cfs-render.php';
require_once get_template_directory() . '/includes/features/cfs/cfs-admin.php';
