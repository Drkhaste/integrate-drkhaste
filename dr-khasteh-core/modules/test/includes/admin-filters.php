<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_add_admin_list_filters() {
    global $typenow;
    if ( in_array( $typenow, [ 'lesson', 'topic' ] ) ) {
        $current_course = isset( $_GET['mtp_filter_course'] ) ? absint( $_GET['mtp_filter_course'] ) : 0;
        $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        echo '<select name="mtp_filter_course" id="mtp_filter_course"><option value="">همه کورس‌ها</option>';
        foreach ( $courses as $course ) echo '<option value="' . $course->ID . '" ' . selected( $current_course, $course->ID, false ) . '>' . esc_html( $course->post_title ) . '</option>';
        echo '</select>';

        if ( $typenow === 'topic' ) {
            $current_lesson = isset( $_GET['mtp_filter_lesson'] ) ? absint( $_GET['mtp_filter_lesson'] ) : 0;
            echo '<select name="mtp_filter_lesson" id="mtp_filter_lesson"><option value="">همه درس‌ها</option>';
            if ( $current_course ) {
                $lessons = get_posts( [ 'post_type' => 'lesson', 'meta_key' => '_mcp_course_id', 'meta_value' => $current_course, 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
                foreach ( $lessons as $lesson ) echo '<option value="' . $lesson->ID . '" ' . selected( $current_lesson, $lesson->ID, false ) . '>' . esc_html( $lesson->post_title ) . '</option>';
            }
            echo '</select>';
        }
    }
}
add_action( 'restrict_manage_posts', 'mtp_add_admin_list_filters' );

function mtp_filter_admin_list_query( $query ) {
    global $pagenow;
    if ( ! is_admin() || $pagenow !== 'edit.php' || ! $query->is_main_query() ) return;
    $post_type = $query->get( 'post_type' );
    if ( in_array( $post_type, [ 'lesson', 'topic' ] ) ) {
        $meta_query = (array) $query->get( 'meta_query' );
        if ( ! empty( $_GET['mtp_filter_course'] ) ) $meta_query[] = [ 'key' => '_mcp_course_id', 'value' => absint( $_GET['mtp_filter_course'] ) ];
        if ( $post_type === 'topic' && ! empty( $_GET['mtp_filter_lesson'] ) ) $meta_query[] = [ 'key' => '_mcp_lesson_id', 'value' => absint( $_GET['mtp_filter_lesson'] ) ];
        if ( ! empty( $meta_query ) ) $query->set( 'meta_query', $meta_query );
    }
}
add_action( 'pre_get_posts', 'mtp_filter_admin_list_query' );
