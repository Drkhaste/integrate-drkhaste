<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_add_custom_columns( $columns ) {
    $new_columns = [];
    foreach ( $columns as $key => $value ) {
        $new_columns[$key] = $value;
        if ( 'title' === $key ) {
            $post_type = get_post_type();
            if ( 'lesson' === $post_type ) $new_columns['course'] = 'Course';
            if ( 'topic' === $post_type ) { $new_columns['course'] = 'Course'; $new_columns['lesson'] = 'Lesson'; }
        }
    }
    return $new_columns;
}
add_filter( 'manage_lesson_posts_columns', 'mtp_add_custom_columns' );
add_filter( 'manage_topic_posts_columns', 'mtp_add_custom_columns' );

function mtp_custom_column_content( $column, $post_id ) {
    if ( 'course' === $column ) {
        $course_id = get_post_meta( $post_id, '_mcp_course_id', true );
        echo $course_id ? esc_html( get_the_title( $course_id ) ) : '—';
    }
    if ( 'lesson' === $column ) {
        $lesson_id = get_post_meta( $post_id, '_mcp_lesson_id', true );
        echo $lesson_id ? esc_html( get_the_title( $lesson_id ) ) : '—';
    }
}
add_action( 'manage_lesson_posts_custom_column', 'mtp_custom_column_content', 10, 2 );
add_action( 'manage_topic_posts_custom_column', 'mtp_custom_column_content', 10, 2 );
