<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Lesson {
	public static function get_lesson_topics( $lesson_id ) {
		return get_posts( array(
			'post_type'   => 'topic',
			'meta_key'    => '_mcp_lesson_id',
			'meta_value'  => $lesson_id,
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
	}
}
