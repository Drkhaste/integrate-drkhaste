<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_AJAX {

	public static function init() {
		add_action( 'wp_ajax_dr_khaste_search_courses', array( __CLASS__, 'search_courses' ) );
		add_action( 'wp_ajax_dr_khaste_search_lessons', array( __CLASS__, 'search_lessons' ) );
		add_action( 'wp_ajax_dr_khaste_search_topics', array( __CLASS__, 'search_topics' ) );
		add_action( 'wp_ajax_dr_khaste_update_order', array( __CLASS__, 'update_order' ) );
	}

	public static function search_courses() {
		check_ajax_referer( 'dr_khaste_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$search = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
		$posts  = get_posts( array(
			'post_type'      => 'course',
			's'              => $search,
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		) );

		$results = array();
		foreach ( $posts as $p ) {
			$results[] = array( 'id' => $p->ID, 'text' => $p->post_title );
		}

		wp_send_json_success( $results );
	}

	public static function search_lessons() {
		check_ajax_referer( 'dr_khaste_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$search    = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;

		$args = array(
			'post_type'      => 'lesson',
			's'              => $search,
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		);

		if ( $course_id ) {
			$args['meta_key']   = '_mcp_course_id';
			$args['meta_value'] = $course_id;
		}

		$posts = get_posts( $args );

		$results = array();
		foreach ( $posts as $p ) {
			$results[] = array( 'id' => $p->ID, 'text' => $p->post_title );
		}

		wp_send_json_success( $results );
	}

	public static function search_topics() {
		check_ajax_referer( 'dr_khaste_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$search    = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
		$lesson_id = isset( $_GET['lesson_id'] ) ? absint( $_GET['lesson_id'] ) : 0;

		$args = array(
			'post_type'      => 'topic',
			's'              => $search,
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		);

		if ( $lesson_id ) {
			$args['meta_key']   = '_mcp_lesson_id';
			$args['meta_value'] = $lesson_id;
		}

		$posts = get_posts( $args );

		$results = array();
		foreach ( $posts as $p ) {
			$results[] = array( 'id' => $p->ID, 'text' => $p->post_title );
		}

		wp_send_json_success( $results );
	}

	public static function update_order() {
		check_ajax_referer( 'dr_khaste_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$order = isset( $_POST['order'] ) ? array_map( 'absint', (array) $_POST['order'] ) : array();
		foreach ( $order as $menu_order => $post_id ) {
			wp_update_post( array(
				'ID'         => $post_id,
				'menu_order' => $menu_order,
			) );
		}

		wp_send_json_success();
	}
}

Dr_Khaste_AJAX::init();
