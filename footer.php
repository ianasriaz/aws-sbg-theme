<?php
/**
 * Footer wrapper - includes the actual footer partial
 *
 * @package AWS_SBG_Theme
 */

global $sbg_footer_loaded;
if ( ! isset( $sbg_footer_loaded ) ) {
    $sbg_footer_loaded = true;
    require_once get_template_directory() . '/templates/partials/footer.php';
}
