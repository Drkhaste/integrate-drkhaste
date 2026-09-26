<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Course_Repository {

	public static function get_by_id( $course_id ) {
		$post = get_post( $course_id );
		return ( $post && 'course' === $post->post_type ) ? $post : null;
	}

	public static function get_lessons( $course_id, $limit = -1 ) {
		return get_posts( array(
			'post_type'   => 'lesson',
			'meta_key'    => '_mcp_course_id',
			'meta_value'  => absint( $course_id ),
			'numberposts' => $limit,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
	}
}
