<?php
/**
 * Plugin Name:       Dr. Khasteh Core
 * Description:       افزونه جامع و ادغام‌شده دکتر خسته (شامل کورس‌ها، آزمون‌های پزشکی، لایتنر و نقشه ذهنی).
 * Version:           1.0.0
 * Author:            Dr. Khasteh
 * Text Domain:       dr-khasteh-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'DR_KHASTEH_CORE_VERSION', '1.0.0' );
define( 'DR_KHASTEH_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DR_KHASTEH_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Class
 */
class Dr_Khasteh_Core {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 1 );
        $this->load_modules();
    }

    public function register_admin_menu() {
        if ( empty( $GLOBALS['admin_page_hooks']['course_builder_page'] ) ) {
            add_menu_page(
                'کورس ساز',
                'کورس ساز',
                'manage_options',
                'course_builder_page',
                'dr_khasteh_core_admin_page_callback',
                'dashicons-welcome-learn-more',
                1
            );
        }
    }

    private function load_modules() {
        // Load Core CPTs and Admin Menu if exists
        if ( file_exists( DR_KHASTEH_CORE_PATH . 'includes/cpt-init.php' ) ) {
            require_once DR_KHASTEH_CORE_PATH . 'includes/cpt-init.php';
        }

        // Load Course Module
        if ( file_exists( DR_KHASTEH_CORE_PATH . 'modules/course/course-module.php' ) ) {
            require_once DR_KHASTEH_CORE_PATH . 'modules/course/course-module.php';
        }

        // Load Medical Test Module
        if ( file_exists( DR_KHASTEH_CORE_PATH . 'modules/test/test-module.php' ) ) {
            require_once DR_KHASTEH_CORE_PATH . 'modules/test/test-module.php';
        }

        // Load Mind Map Module
        if ( file_exists( DR_KHASTEH_CORE_PATH . 'modules/mindmap/mindmap-module.php' ) ) {
            require_once DR_KHASTEH_CORE_PATH . 'modules/mindmap/mindmap-module.php';
        }
    }
}

function dr_khasteh_core_admin_page_callback() {
    if ( function_exists( 'mcp_leitner_page_callback' ) ) {
        mcp_leitner_page_callback();
    } else {
        echo '<div class="wrap"><h1>کورس ساز</h1><p>خوش آمدید به سیستم مدیریت کورس ساز دکتر خسته.</p></div>';
    }
}

function dr_khasteh_core() {
    return Dr_Khasteh_Core::get_instance();
}

add_action( 'plugins_loaded', 'dr_khasteh_core' );
