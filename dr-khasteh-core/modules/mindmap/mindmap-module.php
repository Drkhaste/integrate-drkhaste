<?php
/**
 * MindMap Module Initialization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MMS_MODULE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MMS_MODULE_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'MIND_MAP_STUDIO_VERSION' ) ) {
    define( 'MIND_MAP_STUDIO_VERSION', '1.1.0' );
}
if ( ! defined( 'MIND_MAP_STUDIO_PATH' ) ) {
    define( 'MIND_MAP_STUDIO_PATH', MMS_MODULE_DIR );
}
if ( ! defined( 'MIND_MAP_STUDIO_URL' ) ) {
    define( 'MIND_MAP_STUDIO_URL', MMS_MODULE_URL );
}

if ( file_exists( MMS_MODULE_DIR . 'inc/class-mind-map-studio.php' ) ) {
    require_once MMS_MODULE_DIR . 'inc/class-mind-map-studio.php';
    if ( class_exists( 'Mind_Map_Studio' ) && ! has_action( 'plugins_loaded', array( 'Mind_Map_Studio', 'init' ) ) ) {
        add_action( 'plugins_loaded', array( 'Mind_Map_Studio', 'init' ), 15 );
    }
}
