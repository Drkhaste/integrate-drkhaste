<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Compat {

	public static function init() {
		// Clean unified helper functions
	}
}

if ( ! function_exists( 'dr_khaste_get_course_lessons' ) ) {
	function dr_khaste_get_course_lessons( $course_id ) {
		return Dr_Khaste_Course_Repository::get_lessons( $course_id );
	}
}

if ( ! function_exists( 'dr_khaste_get_lesson_topics' ) ) {
	function dr_khaste_get_lesson_topics( $lesson_id ) {
		return Dr_Khaste_Lesson_Repository::get_topics( $lesson_id );
	}
}

if ( ! function_exists( 'dr_khaste_get_topic_flashcards' ) ) {
	function dr_khaste_get_topic_flashcards( $topic_id ) {
		return Dr_Khaste_Topic_Repository::get_flashcards( $topic_id );
	}
}

if ( ! function_exists( 'dr_khaste_get_topic_tests' ) ) {
	function dr_khaste_get_topic_tests( $topic_id ) {
		return Dr_Khaste_Topic_Repository::get_tests( $topic_id );
	}
}

if ( ! function_exists( 'dr_khaste_get_topic_mindmaps' ) ) {
	function dr_khaste_get_topic_mindmaps( $topic_id ) {
		return Dr_Khaste_Topic_Repository::get_mindmaps( $topic_id );
	}
}

Dr_Khaste_Compat::init();
