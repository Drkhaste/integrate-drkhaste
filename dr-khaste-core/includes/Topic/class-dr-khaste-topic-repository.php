<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Topic_Repository {

	public static function get_by_id( $topic_id ) {
		$post = get_post( $topic_id );
		return ( $post && 'topic' === $post->post_type ) ? $post : null;
	}

	public static function get_tests( $topic_id ) {
		return get_posts( array(
			'post_type'   => 'test',
			'meta_key'    => '_mtp_topic_id',
			'meta_value'  => absint( $topic_id ),
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		) );
	}

	public static function get_flashcards( $topic_id ) {
		return get_posts( array(
			'post_type'   => 'flashcard',
			'meta_key'    => '_mcp_topic_id',
			'meta_value'  => absint( $topic_id ),
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		) );
	}

	public static function get_mindmaps( $topic_id ) {
		return get_posts( array(
			'post_type'   => 'mms_mind_map',
			'meta_key'    => '_mms_topic_id',
			'meta_value'  => absint( $topic_id ),
			'numberposts' => -1,
		) );
	}
}
