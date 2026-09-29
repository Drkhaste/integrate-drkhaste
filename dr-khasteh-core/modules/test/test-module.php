<?php
/**
 * Test Module Initialization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MTP_MODULE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MTP_MODULE_URL', plugin_dir_url( __FILE__ ) );

if ( ! function_exists( 'mtp_register_post_types' ) ) {
    if ( file_exists( MTP_MODULE_DIR . 'includes/admin-columns.php' ) ) {
        require_once MTP_MODULE_DIR . 'includes/admin-columns.php';
    }
    if ( file_exists( MTP_MODULE_DIR . 'includes/admin-filters.php' ) ) {
        require_once MTP_MODULE_DIR . 'includes/admin-filters.php';
    }
    if ( file_exists( MTP_MODULE_DIR . 'includes/import-export.php' ) ) {
        require_once MTP_MODULE_DIR . 'includes/import-export.php';
    }
    if ( file_exists( MTP_MODULE_DIR . 'includes/hooks.php' ) ) {
        require_once MTP_MODULE_DIR . 'includes/hooks.php';
    }
}

if ( file_exists( MTP_MODULE_DIR . 'medical-test-plugin.php' ) && ! function_exists( 'mtp_enqueue_assets' ) ) {
    require_once MTP_MODULE_DIR . 'medical-test-plugin.php';
}
