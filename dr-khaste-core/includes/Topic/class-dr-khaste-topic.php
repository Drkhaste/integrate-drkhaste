<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Topic {
	public static function get_topic_tests( $topic_id ) {
		return get_posts( array(
			'post_type'   => array( 'test', 'mtp_test' ),
			'meta_key'    => '_mtp_topic_id',
			'meta_value'  => $topic_id,
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		) );
	}

	public static function get_topic_flashcards( $topic_id ) {
		return get_posts( array(
			'post_type'   => 'flashcard',
			'meta_key'    => '_mcp_topic_id',
			'meta_value'  => $topic_id,
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		) );
	}

	public static function get_topic_mindmaps( $topic_id ) {
		return get_posts( array(
			'post_type'   => 'mms_mind_map',
			'meta_key'    => '_mms_topic_id',
			'meta_value'  => $topic_id,
			'numberposts' => -1,
		) );
	}
}
