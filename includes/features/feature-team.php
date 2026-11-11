<?php
/**
 * AWS SBG Team Hiring & Volunteer System
 * File: feature-team.php
 *
 * Loads the Event Crew & Core Team application, validation, and review system.
 *
 * @package AWS_SBG_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/includes/features/team/team-core.php';
require_once get_template_directory() . '/includes/features/team/team-handler.php';
require_once get_template_directory() . '/includes/features/team/team-render.php';
require_once get_template_directory() . '/includes/features/team/team-admin.php';
