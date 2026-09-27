<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Rewrite {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_routing_and_templates' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'filter_post_links' ), 10, 2 );
	}

	public static function add_rewrite_rules() {
		// Canonical hierarchy URLs
		add_rewrite_rule(
			'^course/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?post_type=topic&course_slug=$matches[1]&lesson_slug=$matches[2]&name=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^course/([^/]+)/([^/]+)/?$',
			'index.php?post_type=lesson&course_slug=$matches[1]&name=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^course/([^/]+)/?$',
			'index.php?post_type=course&name=$matches[1]',
			'top'
		);

		// Module routes
		add_rewrite_rule(
			'^leitner/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?mcp_leitner_study=1&course_slug=$matches[1]&lesson_slug=$matches[2]&topic_slug=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^tests/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?dr_khaste_topic_tests=1&course_slug=$matches[1]&lesson_slug=$matches[2]&topic_slug=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^mindmap/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?dr_khaste_mindmap=1&course_slug=$matches[1]&lesson_slug=$matches[2]&topic_slug=$matches[3]',
			'top'
		);
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'mcp_leitner_study';
		$vars[] = 'dr_khaste_topic_tests';
		$vars[] = 'dr_khaste_mindmap';
		$vars[] = 'course_slug';
		$vars[] = 'lesson_slug';
		$vars[] = 'topic_slug';
		return $vars;
	}

	public static function filter_post_links( $post_link, $post ) {
		if ( in_array( $post->post_type, array( 'course', 'lesson', 'topic' ), true ) ) {
			$slug = get_post_meta( $post->ID, '_mcp_english_slug', true );
			if ( ! $slug ) {
				$slug = $post->post_name;
			}

			if ( 'topic' === $post->post_type ) {
				$lesson_id = get_post_meta( $post->ID, '_mcp_lesson_id', true );
				$course_id = get_post_meta( $post->ID, '_mcp_course_id', true );

				$lesson_slug = $lesson_id ? get_post_field( 'post_name', $lesson_id ) : 'lesson';
				$course_slug = $course_id ? get_post_field( 'post_name', $course_id ) : 'course';

				return home_url( "/course/{$course_slug}/{$lesson_slug}/{$slug}/" );
			} elseif ( 'lesson' === $post->post_type ) {
				$course_id   = get_post_meta( $post->ID, '_mcp_course_id', true );
				$course_slug = $course_id ? get_post_field( 'post_name', $course_id ) : 'course';

				return home_url( "/course/{$course_slug}/{$slug}/" );
			} elseif ( 'course' === $post->post_type ) {
				return home_url( "/course/{$slug}/" );
			}
		}
		return $post_link;
	}

	public static function handle_routing_and_templates() {
		// Handle 301 Redirects for legacy post types
		if ( is_singular( array( 'mtp_course', 'mtp_lesson', 'mtp_topic', 'mms_course', 'mms_lesson', 'mms_topic' ) ) ) {
			$post_id = get_queried_object_id();
			$mapping = Dr_Khaste_Migration::get_mapping();
			$pt      = get_post_type( $post_id );
			$new_id  = 0;

			if ( strpos( $pt, 'course' ) !== false && isset( $mapping['courses'][ $post_id ] ) ) {
				$new_id = $mapping['courses'][ $post_id ];
			} elseif ( strpos( $pt, 'lesson' ) !== false && isset( $mapping['lessons'][ $post_id ] ) ) {
				$new_id = $mapping['lessons'][ $post_id ];
			} elseif ( strpos( $pt, 'topic' ) !== false && isset( $mapping['topics'][ $post_id ] ) ) {
				$new_id = $mapping['topics'][ $post_id ];
			}

			if ( $new_id ) {
				wp_redirect( get_permalink( $new_id ), 301 );
				exit;
			}
		}
	}
}

Dr_Khaste_Rewrite::init();
