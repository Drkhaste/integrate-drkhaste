<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Course {
	public static function get_course_lessons( $course_id ) {
		return get_posts( array(
			'post_type'   => 'lesson',
			'meta_key'    => '_mcp_course_id',
			'meta_value'  => $course_id,
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		) );
	}
}
