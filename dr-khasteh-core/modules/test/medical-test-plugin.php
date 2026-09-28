<?php
/**
 * Plugin Name:       Medical Test Plugin
 * Description:       A plugin to create and manage medical tests, courses, lessons, and topics.
 * Version:           2.4.0
 * Author:            Jules
 * Text Domain:       medical-test-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MTP_PLUGIN_DIR . 'includes/cpt-init.php';
require_once MTP_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once MTP_PLUGIN_DIR . 'includes/admin-columns.php';
require_once MTP_PLUGIN_DIR . 'includes/admin-filters.php';
require_once MTP_PLUGIN_DIR . 'includes/import-export.php';
require_once MTP_PLUGIN_DIR . 'includes/hooks.php';

/**
 * Enqueue assets.
 */
function mtp_enqueue_assets() {
    $is_mtp = get_query_var('mtp_route') || get_query_var('mtp_landing');

    if ($is_mtp) {
        wp_enqueue_style('mtp-custom-css', MTP_PLUGIN_URL . 'assets/css/custom.css', [], '2.3.0');
        // FontAwesome is enqueued by theme dr-khasteh
    }
}
add_action('wp_enqueue_scripts', 'mtp_enqueue_assets');

function mtp_admin_assets($hook) {
    global $post;
    $mtp_types = ['course', 'lesson', 'topic', 'test'];
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && isset($post->post_type) && in_array($post->post_type, $mtp_types) ) {
        wp_enqueue_editor();
        wp_enqueue_style('mtp-admin-css', MTP_PLUGIN_URL . 'assets/css/admin-styles.css', [], '2.1.0');
        wp_enqueue_script('mtp-admin-js', MTP_PLUGIN_URL . 'assets/js/admin-scripts.js', ['jquery', 'wp-editor'], '2.1.0', true);
        wp_localize_script('mtp-admin-js', 'mtp_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);

        if (in_array($post->post_type, ['course', 'lesson'])) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('mtp-sortable', MTP_PLUGIN_URL . 'assets/js/sortable-items.js', ['jquery', 'jquery-ui-sortable'], '1.0.0', true);
            wp_localize_script('mtp-sortable', 'mtp_sort_ajax', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('dr_khasteh_order_nonce')]);
        }
    }
    if ( strpos( $hook, 'mtp-csv-import' ) !== false || strpos( $hook, 'mtp-csv-export' ) !== false ) {
        wp_enqueue_style( 'mtp-import-export-styles', MTP_PLUGIN_URL . 'assets/css/import-export.css', [], '1.0.0' );
        wp_enqueue_script( 'mtp-import-export-scripts', MTP_PLUGIN_URL . 'assets/js/import-export.js', [ 'jquery' ], '1.0.0', true );
        wp_localize_script( 'mtp-import-export-scripts', 'mtp_ajax', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
    }
}
add_action('admin_enqueue_scripts', 'mtp_admin_assets');

/**
 * Routing & Rewrite Rules
 */
function mtp_rewrite_rules() {
    // Structure: domain.com/test/
    add_rewrite_rule('^test/?$', 'index.php?mtp_landing=1', 'top');

    // Structure: domain.com/test/course/lesson/topic/
    add_rewrite_rule('^test/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]&mtp_lesson_slug=$matches[2]&mtp_topic_slug=$matches[3]', 'top');
    add_rewrite_rule('^test/([^/]+)/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]&mtp_lesson_slug=$matches[2]', 'top');
    add_rewrite_rule('^test/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]', 'top');
}
add_action('init', 'mtp_rewrite_rules');

function mtp_query_vars($vars) {
    $vars[] = 'mtp_landing';
    $vars[] = 'mtp_route';
    $vars[] = 'mtp_course_slug';
    $vars[] = 'mtp_lesson_slug';
    $vars[] = 'mtp_topic_slug';
    return $vars;
}
add_filter('query_vars', 'mtp_query_vars');

function mtp_resolve_route($query) {
    if (is_admin() || !$query->is_main_query()) return;

    if ($query->get('mtp_landing')) {
        $query->is_404 = false;
        $query->is_single = false;
        $query->is_singular = false;
        $query->is_archive = false;
        $query->is_home = false;
        $query->is_page = true;
        return;
    }

    if ($query->get('mtp_route')) {
        $t_slug = $query->get('mtp_topic_slug');
        $l_slug = $query->get('mtp_lesson_slug');
        $c_slug = $query->get('mtp_course_slug');

        $slug = '';
        $type = '';

        if (!empty($t_slug)) { $slug = $t_slug; $type = 'topic'; }
        elseif (!empty($l_slug)) { $slug = $l_slug; $type = 'lesson'; }
        elseif (!empty($c_slug)) { $slug = $c_slug; $type = 'course'; }

        if ($type) {
            $query->set('post_type', $type);
            $query->set('name', $slug);
            $query->is_404 = false;
            $query->is_single = true;
            $query->is_singular = true;
            $query->is_archive = false;
            $query->is_home = false;
            $query->is_page = false;
        }
    }
}
add_action('pre_get_posts', 'mtp_resolve_route');

/**
 * Template Loader
 */
function mtp_templates($template) {
    if (get_query_var('mtp_landing')) {
        return MTP_PLUGIN_DIR . 'page-templates/template-test-landing.php';
    }

    if (get_query_var('mtp_route')) {
        $t_slug = get_query_var('mtp_topic_slug');
        if (!empty($t_slug)) return MTP_PLUGIN_DIR . 'page-templates/template-topic-tests.php';

        $l_slug = get_query_var('mtp_lesson_slug');
        if (!empty($l_slug)) return MTP_PLUGIN_DIR . 'templates/single-lesson.php';

        $c_slug = get_query_var('mtp_course_slug');
        if (!empty($c_slug)) return MTP_PLUGIN_DIR . 'templates/single-course.php';
    }

    return $template;
}
add_filter( 'template_include', 'mtp_templates' );

/**
 * Activation & Flush
 */
function mtp_activate() {
    mtp_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mtp_activate');

add_action('init', function() {
    if (get_option('mtp_version_v3') !== '2.4.0') {
        mtp_rewrite_rules();
        flush_rewrite_rules();
        update_option('mtp_version_v3', '2.4.0');
    }
}, 30);

/**
 * Bulletproof Redirect for the Landing Page at /test/ or /test
 */
function mtp_landing_template_redirect() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $home_path = parse_url( home_url(), PHP_URL_PATH );
    $home_path = $home_path ? rtrim( $home_path, '/' ) : '';

    // Normalize request path relative to WP home URL
    $path = substr( $request_uri, strlen( $home_path ) );
    $path = parse_url( $path, PHP_URL_PATH );
    $path = trim( $path, '/' );

    if ( 'test' === $path ) {
        $template = MTP_PLUGIN_DIR . 'page-templates/template-test-landing.php';
        if ( file_exists( $template ) ) {
            include $template;
            exit;
        }
    }
}
add_action( 'template_redirect', 'mtp_landing_template_redirect', 5 );

