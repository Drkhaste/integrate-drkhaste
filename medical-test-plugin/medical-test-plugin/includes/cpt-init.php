<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_register_post_types() {
    $cpts = [
        'course' => [ 'name' => 'کورس‌ها', 'singular' => 'کورس', 'icon' => 'dashicons-welcome-learn-more' ],
        'lesson' => [ 'name' => 'درس‌ها', 'singular' => 'درس', 'icon' => 'dashicons-book' ],
        'topic'  => [ 'name' => 'مباحث', 'singular' => 'مبحث', 'icon' => 'dashicons-analytics' ],
        'mtp_test'   => [ 'name' => 'تست‌ها', 'singular' => 'تست', 'icon' => 'dashicons-text-page' ],
    ];

    foreach ($cpts as $type => $labels) {
        if ( post_type_exists( $type ) ) {
            continue;
        }
        register_post_type($type, [
            'labels' => [ 'name' => $labels['name'], 'singular_name' => $labels['singular'] ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'mtp_main_menu',
            'menu_icon' => $labels['icon'],
            'supports' => ($type === 'mtp_test') ? [ 'title' ] : [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
            'rewrite' => false,
            'query_var' => true,
            'publicly_queryable' => true,
            'has_archive' => false,
        ]);
    }
}
add_action( 'init', 'mtp_register_post_types', 5 );

function mtp_admin_menu_setup() {
    if (!empty($GLOBALS['admin_page_hooks']['mtp_main_menu'])) return;
    add_menu_page( 'مدیریت تست‌ها', 'مدیریت تست‌ها', 'manage_options', 'mtp_main_menu', null, 'dashicons-welcome-learn-more', 2 );
}
add_action( 'admin_menu', 'mtp_admin_menu_setup' );
