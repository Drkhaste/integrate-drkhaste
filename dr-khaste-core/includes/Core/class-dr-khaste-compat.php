<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Compat {

	public static function init() {
		// Provide fallback functions if theme or third party plugins call old mcp_*, mtp_*, mms_* functions
	}
}

if ( ! function_exists( 'mcp_get_course_lessons' ) ) {
	function mcp_get_course_lessons( $course_id ) {
		return Dr_Khaste_Course::get_course_lessons( $course_id );
	}
}

if ( ! function_exists( 'mcp_get_lesson_topics' ) ) {
	function mcp_get_lesson_topics( $lesson_id ) {
		return Dr_Khaste_Lesson::get_lesson_topics( $lesson_id );
	}
}

if ( ! function_exists( 'mcp_get_topic_flashcards' ) ) {
	function mcp_get_topic_flashcards( $topic_id ) {
		return Dr_Khaste_Topic::get_topic_flashcards( $topic_id );
	}
}

if ( ! function_exists( 'mtp_get_topic_tests' ) ) {
	function mtp_get_topic_tests( $topic_id ) {
		return Dr_Khaste_Topic::get_topic_tests( $topic_id );
	}
}

if ( ! function_exists( 'mms_get_topic_mindmaps' ) ) {
	function mms_get_topic_mindmaps( $topic_id ) {
		return Dr_Khaste_Topic::get_topic_mindmaps( $topic_id );
	}
}

Dr_Khaste_Compat::init();
