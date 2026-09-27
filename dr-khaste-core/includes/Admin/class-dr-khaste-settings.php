<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Settings {

	public static function render_admin_page() {
		if ( isset( $_POST['dr_khaste_save_settings'] ) && check_admin_referer( 'dr_khaste_settings_nonce' ) ) {
			update_option( 'dr_khaste_enable_redirects', isset( $_POST['dr_khaste_enable_redirects'] ) ? 1 : 0 );
			update_option( 'dr_khaste_legacy_compat_mode', isset( $_POST['dr_khaste_legacy_compat_mode'] ) ? 1 : 0 );
			echo '<div class="updated"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
		}

		$redirects     = get_option( 'dr_khaste_enable_redirects', 1 );
		$legacy_compat = get_option( 'dr_khaste_legacy_compat_mode', 1 );
		?>
		<div class="wrap">
			<h1>تنظیمات سیستم یکپارچه دکتر خسته</h1>
			<form method="post" action="">
				<?php wp_nonce_field( 'dr_khaste_settings_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">هدایت خودکار لینک‌های قدیمی (301 Redirects)</th>
						<td>
							<label>
								<input type="checkbox" name="dr_khaste_enable_redirects" value="1" <?php checked( $redirects, 1 ); ?>>
								فعال‌سازی ریدایرکت خودکار آدرس‌های mtp_* و mms_* به آدرس‌های مرکزی topic/lesson/course
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">حالت سازگاری کدها و توابع قدیمی (Legacy Compatibility)</th>
						<td>
							<label>
								<input type="checkbox" name="dr_khaste_legacy_compat_mode" value="1" <?php checked( $legacy_compat, 1 ); ?>>
								پشتیبانی از توابع و قلاب‌های قدیمی `mcp_*` و `mtp_*` برای جلوگیری از خطای پوسته و پلاگین‌ها
							</label>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="dr_khaste_save_settings" class="button button-primary" value="ذخیره تنظیمات">
				</p>
			</form>
		</div>
		<?php
	}
}
