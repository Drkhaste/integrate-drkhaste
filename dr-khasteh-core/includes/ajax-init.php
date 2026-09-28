<?php
/**
 * Shared Core AJAX Handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get lessons by course ID.
 */
function dr_khasteh_get_lessons_by_course_ajax() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
    if ( ! $course_id ) {
        wp_send_json_error( [ 'message' => 'Invalid course ID.' ] );
    }

    $lessons = get_posts( [
        'post_type'   => 'lesson',
        'meta_key'    => '_course_id',
        'meta_value'  => $course_id,
        'numberposts' => -1,
        'orderby'     => 'menu_order title',
        'order'       => 'ASC',
    ] );

    $data = [];
    foreach ( $lessons as $lesson ) {
        $data[] = [
            'id'    => $lesson->ID,
            'title' => $lesson->post_title,
        ];
    }

    wp_send_json_success( $data );
}
add_action( 'wp_ajax_dr_khasteh_get_lessons_by_course', 'dr_khasteh_get_lessons_by_course_ajax' );

/**
 * Get topics by lesson ID.
 */
function dr_khasteh_get_topics_by_lesson_ajax() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $lesson_id = isset( $_POST['lesson_id'] ) ? absint( $_POST['lesson_id'] ) : 0;
    if ( ! $lesson_id ) {
        wp_send_json_error( [ 'message' => 'Invalid lesson ID.' ] );
    }

    $topics = get_posts( [
        'post_type'   => 'topic',
        'meta_key'    => '_lesson_id',
        'meta_value'  => $lesson_id,
        'numberposts' => -1,
        'orderby'     => 'menu_order title',
        'order'       => 'ASC',
    ] );

    $data = [];
    foreach ( $topics as $topic ) {
        $data[] = [
            'id'    => $topic->ID,
            'title' => $topic->post_title,
        ];
    }

    wp_send_json_success( $data );
}
add_action( 'wp_ajax_dr_khasteh_get_topics_by_lesson', 'dr_khasteh_get_topics_by_lesson_ajax' );

/**
 * Update reorderable items menu order.
 */
function dr_khasteh_update_items_order_ajax() {
    check_ajax_referer( 'dr_khasteh_order_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : [];
    if ( empty( $order ) ) {
        wp_send_json_error( [ 'message' => 'No order data provided.' ] );
    }

    foreach ( $order as $index => $post_id ) {
        wp_update_post( [
            'ID'         => $post_id,
            'menu_order' => $index,
        ] );
    }

    wp_send_json_success( [ 'message' => 'Order updated successfully.' ] );
}
add_action( 'wp_ajax_dr_khasteh_update_items_order', 'dr_khasteh_update_items_order_ajax' );
