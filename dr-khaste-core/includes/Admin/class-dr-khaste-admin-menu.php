<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Admin_Menu {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'setup_menu' ) );
	}

	public static function setup_menu() {
		if ( ! empty( $GLOBALS['admin_page_hooks']['dr_khaste_main_menu'] ) ) {
			return;
		}

		add_menu_page(
			'دکتر خسته',
			'دکتر خسته',
			'manage_options',
			'dr_khaste_main_menu',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-welcome-learn-more',
			2
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'پیشخوان',
			'پیشخوان',
			'manage_options',
			'dr_khaste_main_menu',
			array( __CLASS__, 'render_dashboard' )
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'کورس‌ها',
			'کورس‌ها',
			'manage_options',
			'edit.php?post_type=course'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'درس‌ها',
			'درس‌ها',
			'manage_options',
			'edit.php?post_type=lesson'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'مباحث',
			'مباحث',
			'manage_options',
			'edit.php?post_type=topic'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'تست‌ها',
			'تست‌ها',
			'manage_options',
			'edit.php?post_type=test'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'فلش‌کارت‌ها',
			'فلش‌کارت‌ها',
			'manage_options',
			'edit.php?post_type=flashcard'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'جعبه لایتنر',
			'جعبه لایتنر',
			'manage_options',
			'dr_khaste_leitner',
			array( 'Dr_Khaste_Leitner', 'render_admin_page' )
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'نقشه‌های ذهنی',
			'نقشه‌های ذهنی',
			'manage_options',
			'edit.php?post_type=mms_mind_map'
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'ورود / خروج',
			'ورود / خروج',
			'manage_options',
			'dr_khaste_import_export',
			array( 'Dr_Khaste_Import_Export', 'render_admin_page' )
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'مهاجرت داده‌ها',
			'مهاجرت داده‌ها',
			'manage_options',
			'dr_khaste_migration',
			array( 'Dr_Khaste_Migration', 'render_admin_page' )
		);

		add_submenu_page(
			'dr_khaste_main_menu',
			'تنظیمات',
			'تنظیمات',
			'manage_options',
			'dr_khaste_settings',
			array( 'Dr_Khaste_Settings', 'render_admin_page' )
		);
	}

	public static function render_dashboard() {
		?>
		<div class="wrap">
			<h1>سیستم آموزشی یکپارچه دکتر خسته (Dr.Khaste Core)</h1>
			<p>به سیستم مدیریت آموزشی خوش آمدید. کلیه پلاگین‌های کورس، تست، فلش‌کارت، لایتنر و مایند مپ به صورت یکپارچه در این پلاگین مدیریت می‌شوند.</p>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>وضعیت سیستم و ماژول‌ها</h2>
				<ul>
					<li><strong>کورس‌ها و درس‌ها:</strong> فعال (مدل مرکزی)</li>
					<li><strong>تست‌ها:</strong> متصل به مباحث مرکزی</li>
					<li><strong>فلش‌کارت و لایتنر:</strong> متصل به مباحث مرکزی</li>
					<li><strong>مایند مپ استودیو:</strong> متصل به مباحث مرکزی</li>
				</ul>
			</div>
		</div>
		<?php
	}
}

Dr_Khaste_Admin_Menu::init();
