<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Init {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	private function includes() {
		// Core
		require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-cpt.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-rewrite.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-compat.php';

		// Admin
		require_once DR_KHASTE_CORE_PATH . 'includes/Admin/class-dr-khaste-admin-menu.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Admin/class-dr-khaste-meta-boxes.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Admin/class-dr-khaste-admin-columns.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Admin/class-dr-khaste-settings.php';

		// Modules
		require_once DR_KHASTE_CORE_PATH . 'includes/Course/class-dr-khaste-course.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Lesson/class-dr-khaste-lesson.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Topic/class-dr-khaste-topic.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Tests/class-dr-khaste-tests.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Flashcards/class-dr-khaste-flashcards.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Leitner/class-dr-khaste-leitner.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/MindMaps/class-dr-khaste-mindmaps.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/ImportExport/class-dr-khaste-import-export.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Migration/class-dr-khaste-migration.php';
	}

	private function init_hooks() {
		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function on_init() {
		// Register CPTs and Rewrite rules
		Dr_Khaste_CPT::register();
		Dr_Khaste_Rewrite::init();
	}

	public function enqueue_frontend_assets() {
		wp_enqueue_style( 'dr-khaste-fontawesome', DR_KHASTE_CORE_URL . 'assets/vendor/fontawesome/css/all.min.css', array(), DR_KHASTE_CORE_VERSION );
		wp_enqueue_style( 'dr-khaste-custom', DR_KHASTE_CORE_URL . 'assets/css/custom.css', array(), DR_KHASTE_CORE_VERSION );
		wp_enqueue_style( 'dr-khaste-leitner', DR_KHASTE_CORE_URL . 'assets/css/leitner.css', array(), DR_KHASTE_CORE_VERSION );
		wp_enqueue_style( 'dr-khaste-jsmind', DR_KHASTE_CORE_URL . 'assets/css/jsmind.css', array(), DR_KHASTE_CORE_VERSION );
		wp_enqueue_style( 'dr-khaste-mms', DR_KHASTE_CORE_URL . 'assets/css/mms-frontend.css', array(), DR_KHASTE_CORE_VERSION );

		wp_enqueue_script( 'dr-khaste-jsmind', DR_KHASTE_CORE_URL . 'assets/vendor/jsmind.js', array( 'jquery' ), DR_KHASTE_CORE_VERSION, true );
		wp_enqueue_script( 'dr-khaste-mindmap-frontend', DR_KHASTE_CORE_URL . 'assets/js/mindmap-frontend.js', array( 'dr-khaste-jsmind' ), DR_KHASTE_CORE_VERSION, true );
		wp_enqueue_script( 'dr-khaste-leitner-js', DR_KHASTE_CORE_URL . 'assets/js/leitner.js', array( 'jquery' ), DR_KHASTE_CORE_VERSION, true );
		wp_enqueue_script( 'dr-khaste-accordion-js', DR_KHASTE_CORE_URL . 'assets/js/mcp-accordion.js', array( 'jquery' ), DR_KHASTE_CORE_VERSION, true );

		wp_localize_script( 'dr-khaste-leitner-js', 'mcp_leitner_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mcp_leitner_nonce' ),
		) );
	}

	public function enqueue_admin_assets( $hook ) {
		wp_enqueue_style( 'dr-khaste-admin-styles', DR_KHASTE_CORE_URL . 'assets/css/admin-styles.css', array(), DR_KHASTE_CORE_VERSION );
		wp_enqueue_script( 'dr-khaste-admin-scripts', DR_KHASTE_CORE_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), DR_KHASTE_CORE_VERSION, true );
		wp_enqueue_script( 'dr-khaste-sortable', DR_KHASTE_CORE_URL . 'assets/js/sortable-items.js', array( 'jquery', 'jquery-ui-sortable' ), DR_KHASTE_CORE_VERSION, true );
	}

	public static function activate() {
		require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-cpt.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Core/class-dr-khaste-rewrite.php';
		require_once DR_KHASTE_CORE_PATH . 'includes/Leitner/class-dr-khaste-leitner.php';

		Dr_Khaste_CPT::register();
		Dr_Khaste_Rewrite::add_rewrite_rules();
		Dr_Khaste_Leitner::create_table();

		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
