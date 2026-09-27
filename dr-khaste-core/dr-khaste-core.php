<?php
/**
 * Plugin Name: Dr.Khaste Core
 * Plugin URI: https://dr-khasteh.ir
 * Description: Unified Core Plugin for Dr.Khaste - Managing Courses, Lessons, Topics, Tests, Flashcards, Leitner, and Mind Maps.
 * Version: 1.0.0
 * Author: Dr.Khaste Team
 * Text Domain: dr-khaste-core
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DR_KHASTE_CORE_VERSION', '1.0.0' );
define( 'DR_KHASTE_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DR_KHASTE_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'DR_KHASTE_CORE_FILE', __FILE__ );

// Autoloader / Module Includes
require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-init.php';

// Activation & Deactivation Hooks
register_activation_hook( __FILE__, array( 'Dr_Khaste_Init', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Dr_Khaste_Init', 'deactivate' ) );

// Initialize Core
function dr_khaste_core() {
	return Dr_Khaste_Init::get_instance();
}

add_action( 'plugins_loaded', 'dr_khaste_core' );
