<?php
/**
 * Course & Leitner Module Initialization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MCP_MODULE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCP_MODULE_URL', plugin_dir_url( __FILE__ ) );

if ( ! function_exists( 'mcp_register_post_types' ) ) {
    require_once MCP_MODULE_DIR . 'includes/admin-columns.php';
    require_once MCP_MODULE_DIR . 'includes/admin-filters.php';
    require_once MCP_MODULE_DIR . 'includes/hooks.php';
    require_once MCP_MODULE_DIR . 'includes/import-export.php';
    require_once MCP_MODULE_DIR . 'includes/settings.php';
    require_once MCP_MODULE_DIR . 'includes/leitner/leitner-init.php';
}

if ( file_exists( MCP_MODULE_DIR . 'med-course-plugin.php' ) && ! function_exists( 'mcp_admin_enqueue_scripts' ) ) {
    require_once MCP_MODULE_DIR . 'med-course-plugin.php';
}
