<?php
/**
 * Handles script and style registration for Text Styler in the theme.
 *
 * @package Dr_Khasteh
 */

defined( 'ABSPATH' ) || exit;

class Dr_Khasteh_Text_Styler_Assets {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_enqueue' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_font_styles' ) );
	}

	public static function admin_enqueue( $hook ) {
		$font_source = get_option( 'dr_khasteh_font_source', 'cdn' );
		$upload_dir  = wp_upload_dir();
		$local_base  = $upload_dir['baseurl'] . '/dr-khasteh-fonts';

		if ( $font_source === 'local' ) {
			wp_enqueue_style( 'dr-khasteh-local-fonts', $local_base . '/local-fonts.css', [], '1.0.0' );
			wp_enqueue_style( 'dr-khasteh-fontawesome-local', $local_base . '/fontawesome-local.css', [], '6.5.1' );
		} else {
			wp_enqueue_style( 'vazirmatn', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap', [], null );
			wp_enqueue_style( 'playpen-sans-arabic', 'https://cdn.jsdelivr.net/npm/@fontsource/playpen-sans-arabic/index.css', [], null );
			wp_enqueue_style( 'zain', 'https://cdn.jsdelivr.net/npm/@fontsource/zain/index.css', [], null );
			wp_enqueue_style( 'noto-sans-arabic', 'https://cdn.jsdelivr.net/npm/@fontsource/noto-sans-arabic/index.css', [], null );
			wp_enqueue_style( 'dr-khasteh-fontawesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css', [], '6.5.1' );
		}

		// Base CSS for editor and previews
		wp_register_style(
			'dr-khasteh-text-styler',
			get_template_directory_uri() . '/assets/css/text-styler.css',
			array(),
			'1.0.0'
		);

		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->base ) {
			$allowed = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
			if ( in_array( $screen->post_type, (array) $allowed, true ) ) {
				wp_enqueue_style( 'dr-khasteh-text-styler' );
				// We also need to add the dynamic CSS for editor preview if needed
				wp_add_inline_style( 'dr-khasteh-text-styler', Dr_Khasteh_Text_Styler_CSS_Generator::get_css() );
			}
		}

		// Settings page (Appearance > Theme Settings)
		if ( 'appearance_page_dr-khasteh-settings' === $hook ) {
			wp_add_inline_style( 'wp-admin', "
				.drkh-accordion-header:hover { background: #eee !important; }
				.drkh-accordion-item.active .drkh-accordion-header { background: #f0f0f0 !important; }
				.drkh-accordion-header .dashicons { transition: transform 0.3s ease; }
				.drkh-accordion-item.active .drkh-accordion-header .dashicons { transform: rotate(180deg); }
				.drkh-accordion-content { overflow: hidden; }
			" );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script(
				'dr-khasteh-text-styler-settings',
				get_template_directory_uri() . '/assets/js/settings.js',
				array( 'jquery', 'wp-color-picker' ),
				'1.0.0',
				true
			);
		}
	}

	public static function frontend_enqueue() {
		wp_enqueue_style(
			'dr-khasteh-text-styler',
			get_template_directory_uri() . '/assets/css/text-styler.css',
			array(),
			'1.0.0'
		);
		// Dynamic CSS is added via Dr_Khasteh_Text_Styler_CSS_Generator::enqueue_css()
	}

	public static function admin_font_styles() {
		$admin_font = get_option( 'dr_khasteh_admin_font', '' );
		if ( empty( $admin_font ) ) {
			return;
		}

		echo "<style>
			:root { --drkh-admin-font: {$admin_font}, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif; }
			body, #wpadminbar *, .wp-core-ui, .edit-post-visual-editor, .block-editor-block-list__block, .wp-block {
				font-family: var(--drkh-admin-font) !important;
			}
			/* Prevent icons from breaking */
			.dashicons, .dashicons-before:before, [class^='plugin-symbol-'], [class*=' plugin-symbol-'], .plugin-tip_box .inner::before, .fa, .fas, .far, .fab {
				font-family: inherit !important; /* Dashicons handle their own font usually, but let's be careful */
			}
			.dashicons, .dashicons-before:before {
				font-family: dashicons !important;
			}
			.fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands {
				font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands' !important;
			}
		</style>";
	}
}
