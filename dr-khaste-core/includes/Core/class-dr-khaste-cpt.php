<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_CPT {

	public static function register() {
		// 1. Central Course CPT
		register_post_type( 'course', array(
			'labels' => array(
				'name'          => 'کورس‌ها',
				'singular_name' => 'کورس',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'course', 'courses' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => true,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
		) );

		// 2. Central Lesson CPT
		register_post_type( 'lesson', array(
			'labels' => array(
				'name'          => 'درس‌ها',
				'singular_name' => 'درس',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'lesson', 'lessons' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => true,
			'menu_icon'           => 'dashicons-book',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		) );

		// 3. Central Topic CPT
		register_post_type( 'topic', array(
			'labels' => array(
				'name'          => 'مباحث',
				'singular_name' => 'مبحث',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'topic', 'topics' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => true,
			'menu_icon'           => 'dashicons-analytics',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		) );

		// 4. Test CPT ('test')
		register_post_type( 'test', array(
			'labels' => array(
				'name'          => 'تست‌ها',
				'singular_name' => 'تست',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'test', 'tests' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => true,
			'menu_icon'           => 'dashicons-text-page',
			'supports'            => array( 'title' ),
		) );

		// 5. Flashcard CPT
		register_post_type( 'flashcard', array(
			'labels' => array(
				'name'          => 'فلش‌کارت‌ها',
				'singular_name' => 'فلش‌کارت',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'flashcard', 'flashcards' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => array( 'slug' => 'flashcard', 'with_front' => true ),
			'query_var'           => true,
			'menu_icon'           => 'dashicons-slides',
			'supports'            => array( 'title' ),
		) );

		// 6. Mind Map CPT ('mms_mind_map')
		register_post_type( 'mms_mind_map', array(
			'labels' => array(
				'name'          => 'نقشه‌های ذهنی',
				'singular_name' => 'نقشه ذهنی',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => 'dr_khaste_main_menu',
			'show_in_nav_menus'   => true,
			'has_archive'         => false,
			'capability_type'     => array( 'mindmap', 'mindmaps' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => array( 'slug' => 'mind-map', 'with_front' => true ),
			'query_var'           => true,
			'menu_icon'           => 'dashicons-chart-pie',
			'supports'            => array( 'title', 'editor' ),
		) );

		// 7. Section Types (Internal helper post type)
		register_post_type( 'section_type', array(
			'labels' => array( 'name' => 'انواع سرفصل' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'dr_khaste_main_menu',
			'supports'           => array( 'title' ),
		) );
	}
}
