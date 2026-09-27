<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Lesson_Repository {

	public static function get_by_id( $lesson_id ) {
		$post = get_post( $lesson_id );
		return ( $post && 'lesson' === $post->post_type ) ? $post : null;
	}

	public static function get_topics( $lesson_id, $limit = -1 ) {
		return get_posts( array(
			'post_type'   => 'topic',
			'meta_key'    => '_mcp_lesson_id',
			'meta_value'  => absint( $lesson_id ),
			'numberposts' => $limit,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
	}
}
