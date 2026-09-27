<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Import_Export {

	public static function init() {
		add_action( 'wp_ajax_dr_khaste_import_csv', array( __CLASS__, 'ajax_import_csv' ) );
		add_action( 'wp_ajax_dr_khaste_export_csv', array( __CLASS__, 'ajax_export_csv' ) );
	}

	public static function render_admin_page() {
		?>
		<div class="wrap">
			<h1>ورود / خروج داده‌ها (Import / Export)</h1>
			<div class="card" style="max-width: 700px; margin-top: 20px;">
				<h2>ورود از فایل CSV</h2>
				<p>از این قسمت می‌توانید سوالات تست و فلش‌کارت‌ها را بر اساس ساختار تعریف شده وارد سیستم نمایید.</p>
				<form method="post" enctype="multipart/form-data">
					<p><input type="file" name="import_csv_file" accept=".csv"></p>
					<p><input type="submit" name="dr_khaste_do_import" class="button button-primary" value="شروع ورود داده‌ها"></p>
				</form>
			</div>
		</div>
		<?php
	}

	public static function ajax_import_csv() {
		check_ajax_referer( 'dr_khaste_import_nonce' );
		wp_send_json_success( array( 'message' => 'امکان ورود داده‌ها آماده است.' ) );
	}

	public static function ajax_export_csv() {
		check_ajax_referer( 'dr_khaste_export_nonce' );
		wp_send_json_success();
	}
}

Dr_Khaste_Import_Export::init();
