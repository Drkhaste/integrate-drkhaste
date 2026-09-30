<?php
/**
 * Core Custom Post Types & Rewrite Rules Initialization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Centralized CPT Registration
 */
function dr_khasteh_core_register_post_types() {
    // 1. Course CPT
    if ( ! post_type_exists( 'course' ) ) {
        register_post_type( 'course', [
            'label'               => __( 'کورس‌ها', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'کورس‌ها', 'dr-khasteh-core' ),
                'singular_name' => __( 'کورس', 'dr-khasteh-core' ),
                'menu_name'     => __( 'کورس‌ها', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن کورس', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن کورس جدید', 'dr-khasteh-core' ),
            ],
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => true,
            'menu_icon'           => 'dashicons-welcome-learn-more',
            'supports'            => [ 'title', 'editor', 'thumbnail' ],
        ] );
    }

    // 2. Lesson CPT
    if ( ! post_type_exists( 'lesson' ) ) {
        register_post_type( 'lesson', [
            'label'               => __( 'درس‌ها', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'درس‌ها', 'dr-khasteh-core' ),
                'singular_name' => __( 'درس', 'dr-khasteh-core' ),
                'menu_name'     => __( 'درس‌ها', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن درس', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن درس جدید', 'dr-khasteh-core' ),
            ],
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => true,
            'menu_icon'           => 'dashicons-book',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
        ] );
    }

    // 3. Topic CPT
    if ( ! post_type_exists( 'topic' ) ) {
        register_post_type( 'topic', [
            'label'               => __( 'مباحث', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'مباحث', 'dr-khasteh-core' ),
                'singular_name' => __( 'مبحث', 'dr-khasteh-core' ),
                'menu_name'     => __( 'مباحث', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن مبحث', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن مبحث جدید', 'dr-khasteh-core' ),
            ],
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => true,
            'menu_icon'           => 'dashicons-analytics',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
        ] );
    }

    // 4. Flashcard CPT
    if ( ! post_type_exists( 'flashcard' ) ) {
        register_post_type( 'flashcard', [
            'label'               => __( 'فلش کارت‌ها', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'فلش کارت‌ها', 'dr-khasteh-core' ),
                'singular_name' => __( 'فلش کارت', 'dr-khasteh-core' ),
                'menu_name'     => __( 'فلش کارت‌ها', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن فلش کارت', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن فلش کارت جدید', 'dr-khasteh-core' ),
            ],
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => [ 'slug' => 'flashcard', 'with_front' => true ],
            'query_var'           => true,
            'menu_icon'           => 'dashicons-slides',
            'supports'            => [ 'title' ],
        ] );
    }

    // 5. Section Type CPT
    if ( ! post_type_exists( 'section_type' ) ) {
        register_post_type( 'section_type', [
            'label'               => __( 'انواع بخش‌ها', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'انواع بخش‌ها', 'dr-khasteh-core' ),
                'singular_name' => __( 'نوع بخش', 'dr-khasteh-core' ),
                'menu_name'     => __( 'انواع بخش‌ها', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن نوع بخش', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن نوع بخش جدید', 'dr-khasteh-core' ),
            ],
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_rest'        => false,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => [ 'slug' => 'section-type', 'with_front' => true ],
            'query_var'           => true,
            'menu_icon'           => 'dashicons-tag',
            'supports'            => [ 'title' ],
        ] );
    }

    // 6. Medical Test CPT (test)
    if ( ! post_type_exists( 'test' ) ) {
        register_post_type( 'test', [
            'label'               => __( 'تست‌های پزشکی', 'dr-khasteh-core' ),
            'labels'              => [
                'name'          => __( 'تست‌های پزشکی', 'dr-khasteh-core' ),
                'singular_name' => __( 'تست پزشکی', 'dr-khasteh-core' ),
                'menu_name'     => __( 'تست‌های پزشکی', 'dr-khasteh-core' ),
                'add_new'       => __( 'افزودن تست', 'dr-khasteh-core' ),
                'add_new_item'  => __( 'افزودن تست جدید', 'dr-khasteh-core' ),
            ],
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'show_in_menu'        => 'course_builder_page',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'rewrite'             => [ 'slug' => 'test-item', 'with_front' => true ],
            'query_var'           => true,
            'menu_icon'           => 'dashicons-forms',
            'supports'            => [ 'title' ],
        ] );
    }
}
add_action( 'init', 'dr_khasteh_core_register_post_types', 5 );

/**
 * Centralized Custom Rewrite Rules
 */
function dr_khasteh_core_register_rewrite_rules() {
    // /test/ landing
    add_rewrite_rule( '^test/?$', 'index.php?mtp_landing=1', 'top' );

    // /test/course/lesson/topic/
    add_rewrite_rule( '^test/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]&mtp_lesson_slug=$matches[2]&mtp_topic_slug=$matches[3]', 'top' );
    add_rewrite_rule( '^test/([^/]+)/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]&mtp_lesson_slug=$matches[2]', 'top' );
    add_rewrite_rule( '^test/([^/]+)/?$', 'index.php?mtp_route=1&mtp_course_slug=$matches[1]', 'top' );

    // /leitner/course/lesson/topic/
    add_rewrite_rule( '^leitner/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?mcp_leitner_study=1&course_slug=$matches[1]&lesson_slug=$matches[2]&topic_slug=$matches[3]', 'top' );

    // /study/course/lesson/topic/
    add_rewrite_rule( '^study/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?post_type=topic&name=$matches[3]&topic_slug=$matches[3]&lesson_slug=$matches[2]&course_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^study/([^/]+)/([^/]+)/?$', 'index.php?post_type=lesson&name=$matches[2]&lesson_slug=$matches[2]&course_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^study/([^/]+)/?$', 'index.php?post_type=course&name=$matches[1]&course_slug=$matches[1]', 'top' );

    // /mindmap/course/lesson/topic/
    add_rewrite_rule( '^mindmap/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?mms_route=1&post_type=topic&name=$matches[3]&mms_topic_slug=$matches[3]&mms_lesson_slug=$matches[2]&mms_course_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^mindmap/([^/]+)/([^/]+)/?$', 'index.php?mms_route=1&post_type=lesson&name=$matches[2]&mms_lesson_slug=$matches[2]&mms_course_slug=$matches[1]', 'top' );
    add_rewrite_rule( '^mindmap/([^/]+)/?$', 'index.php?mms_route=1&post_type=course&name=$matches[1]&mms_course_slug=$matches[1]', 'top' );
}
add_action( 'init', 'dr_khasteh_core_register_rewrite_rules', 10 );

// Ensure rewrite rules are flushed once when needed
if ( ! get_option( 'dr_khasteh_core_rewrite_flushed_v1' ) ) {
    add_action( 'init', function() {
        dr_khasteh_core_register_post_types();
        dr_khasteh_core_register_rewrite_rules();
        flush_rewrite_rules();
        update_option( 'dr_khasteh_core_rewrite_flushed_v1', 1 );
    }, 99 );
}

/**
 * Query Vars
 */
function dr_khasteh_core_register_query_vars( $vars ) {
    $vars[] = 'mtp_landing';
    $vars[] = 'mtp_route';
    $vars[] = 'mtp_course_slug';
    $vars[] = 'mtp_lesson_slug';
    $vars[] = 'mtp_topic_slug';

    $vars[] = 'mcp_leitner_study';
    $vars[] = 'course_slug';
    $vars[] = 'lesson_slug';
    $vars[] = 'topic_slug';

    $vars[] = 'mms_route';
    $vars[] = 'mms_course_slug';
    $vars[] = 'mms_lesson_slug';
    $vars[] = 'mms_topic_slug';

    return $vars;
}
add_filter( 'query_vars', 'dr_khasteh_core_register_query_vars' );
