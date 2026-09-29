<?php
/**
 * Header wrapper - includes the actual header partial
 *
 * @package AWS_SBG_Theme
 */

global $sbg_header_loaded;
if ( ! isset( $sbg_header_loaded ) ) {
    $sbg_header_loaded = true;
    require_once get_template_directory() . '/templates/partials/header.php';
}
