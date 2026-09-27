<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Admin_Columns {

	public static function init() {
		// Course columns
		add_filter( 'manage_course_posts_columns', array( __CLASS__, 'add_course_columns' ) );
		add_action( 'manage_course_posts_custom_column', array( __CLASS__, 'render_course_columns' ), 10, 2 );

		// Lesson columns
		add_filter( 'manage_lesson_posts_columns', array( __CLASS__, 'add_lesson_columns' ) );
		add_action( 'manage_lesson_posts_custom_column', array( __CLASS__, 'render_lesson_columns' ), 10, 2 );

		// Topic columns
		add_filter( 'manage_topic_posts_columns', array( __CLASS__, 'add_topic_columns' ) );
		add_action( 'manage_topic_posts_custom_column', array( __CLASS__, 'render_topic_columns' ), 10, 2 );

		// Test columns
		add_filter( 'manage_test_posts_columns', array( __CLASS__, 'add_test_columns' ) );
		add_action( 'manage_test_posts_custom_column', array( __CLASS__, 'render_test_columns' ), 10, 2 );
	}

	public static function add_course_columns( $columns ) {
		$columns['english_slug'] = 'نامک انگلیسی';
		return $columns;
	}

	public static function render_course_columns( $column, $post_id ) {
		if ( 'english_slug' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_mcp_english_slug', true ) );
		}
	}

	public static function add_lesson_columns( $columns ) {
		$columns['parent_course'] = 'کورس مربوطه';
		$columns['english_slug']  = 'نامک انگلیسی';
		return $columns;
	}

	public static function render_lesson_columns( $column, $post_id ) {
		if ( 'parent_course' === $column ) {
			$cid = get_post_meta( $post_id, '_mcp_course_id', true );
			if ( $cid ) {
				echo esc_html( get_the_title( $cid ) );
			} else {
				echo '—';
			}
		} elseif ( 'english_slug' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_mcp_english_slug', true ) );
		}
	}

	public static function add_topic_columns( $columns ) {
		$columns['parent_course'] = 'کورس مربوطه';
		$columns['parent_lesson'] = 'درس مربوطه';
		$columns['english_slug']  = 'نامک انگلیسی';
		return $columns;
	}

	public static function render_topic_columns( $column, $post_id ) {
		if ( 'parent_course' === $column ) {
			$cid = get_post_meta( $post_id, '_mcp_course_id', true );
			echo $cid ? esc_html( get_the_title( $cid ) ) : '—';
		} elseif ( 'parent_lesson' === $column ) {
			$lid = get_post_meta( $post_id, '_mcp_lesson_id', true );
			echo $lid ? esc_html( get_the_title( $lid ) ) : '—';
		} elseif ( 'english_slug' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_mcp_english_slug', true ) );
		}
	}

	public static function add_test_columns( $columns ) {
		$columns['parent_topic'] = 'مبحث مربوطه';
		$columns['exam_info']    = 'آزمون / سال';
		return $columns;
	}

	public static function render_test_columns( $column, $post_id ) {
		if ( 'parent_topic' === $column ) {
			$tid = get_post_meta( $post_id, '_mtp_topic_id', true );
			if ( ! $tid ) {
				$tid = get_post_meta( $post_id, '_mcp_topic_id', true );
			}
			echo $tid ? esc_html( get_the_title( $tid ) ) : '—';
		} elseif ( 'exam_info' === $column ) {
			$exam = get_post_meta( $post_id, '_mtp_exam', true );
			$date = get_post_meta( $post_id, '_mtp_date', true );
			echo esc_html( trim( "$exam $date" ) );
		}
	}
}

Dr_Khaste_Admin_Columns::init();
