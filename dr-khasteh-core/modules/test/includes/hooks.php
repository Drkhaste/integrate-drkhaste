<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_get_permalink($post_id) {
    $post_type = get_post_type($post_id);
    $slug = get_post_meta($post_id, '_mcp_english_slug', true);
    if (empty($slug)) $slug = get_post_field('post_name', $post_id);

    if ($post_type == 'course') return home_url("/test/$slug/");

    if ($post_type == 'lesson') {
        $c_id = get_post_meta($post_id, '_mcp_course_id', true);
        $c_slug = get_post_meta($c_id, '_mcp_english_slug', true);
        if (empty($c_slug)) $c_slug = get_post_field('post_name', $c_id);
        return home_url("/test/$c_slug/$slug/");
    }

    if ($post_type == 'topic') {
        $l_id = get_post_meta($post_id, '_mcp_lesson_id', true);
        $l_slug = get_post_meta($l_id, '_mcp_english_slug', true);
        if (empty($l_slug)) $l_slug = get_post_field('post_name', $l_id);

        $c_id = get_post_meta($l_id, '_mcp_course_id', true);
        $c_slug = get_post_meta($c_id, '_mcp_english_slug', true);
        if (empty($c_slug)) $c_slug = get_post_field('post_name', $c_id);

        return home_url("/test/$c_slug/$l_slug/$slug/");
    }

    return get_permalink($post_id);
}

function mtp_handle_cascading_delete( $post_id ) {
    $post_type = get_post_type( $post_id );
    if ( $post_type === 'course' ) {
        $lessons = get_posts(['post_type' => 'lesson', 'meta_key' => '_mcp_course_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($lessons as $id) wp_delete_post($id, true);
    } elseif ( $post_type === 'lesson' ) {
        $topics = get_posts(['post_type' => 'topic', 'meta_key' => '_mcp_lesson_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($topics as $id) wp_delete_post($id, true);
    } elseif ( $post_type === 'topic' ) {
        $tests = get_posts(['post_type' => 'mtp_test', 'meta_key' => '_mcp_topic_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($tests as $id) wp_delete_post($id, true);
    }
}
add_action( 'before_delete_post', 'mtp_handle_cascading_delete' );
