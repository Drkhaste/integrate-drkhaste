<?php
/**
 * Registers TinyMCE plugin and buttons for the theme.
 *
 * @package Dr_Khasteh
 */

defined( 'ABSPATH' ) || exit;

class Dr_Khasteh_Text_Styler_TinyMCE {

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'setup' ) );
	}

	public static function setup() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$rich = get_user_option( 'rich_editing' );
		if ( $rich === '0' || $rich === false ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( __CLASS__, 'register_plugin' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'register_buttons' ) );
		add_filter( 'mce_buttons_3', array( __CLASS__, 'register_buttons_row_3' ) );
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'extend_valid_elements' ) );
		add_filter( 'mce_css', array( __CLASS__, 'editor_css' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'print_js_config' ), 1 );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'add_quicktags' ) );

		// Preserve leading spaces before plugin-related HTML tags in the editor
		add_filter( 'content_edit_pre', array( __CLASS__, 'preserve_spaces_for_editor' ) );
	}

	public static function register_plugin( $plugins ) {
		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->base ) {
			$allowed = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
			if ( ! in_array( $screen->post_type, (array) $allowed, true ) ) {
				return $plugins;
			}
		}

		$plugins['dr_khasteh_text_styler'] = get_template_directory_uri() . '/assets/js/tinymce-plugin.js?ver=2.1.0';
		return $plugins;
	}

	public static function register_buttons( $buttons ) {
		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->base ) {
			$allowed = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
			if ( ! in_array( $screen->post_type, (array) $allowed, true ) ) {
				return $buttons;
			}
		}

		$buttons[] = 'separator';
		$buttons[] = 'drkh_hl_yellow';
		$buttons[] = 'drkh_hl_green';
		$buttons[] = 'drkh_hl_blue';
		$buttons[] = 'drkh_hl_red';
		$buttons[] = 'separator';
		$buttons[] = 'drkh_box_info';
		$buttons[] = 'drkh_box_warning';
		$buttons[] = 'drkh_box_success';
		$buttons[] = 'drkh_box_custom';
		$buttons[] = 'separator';
		$buttons[] = 'drkh_custom_tip_box';
		$buttons[] = 'drkh_custom_accent_h3';
		$buttons[] = 'drkh_custom_symbol_bullet';
		$buttons[] = 'drkh_custom_glow_text';
		$buttons[] = 'drkh_custom_corner_border';
		$buttons[] = 'separator';

		// Custom Symbols
		$symbols = get_option( 'dr_khasteh_custom_symbols', Dr_Khasteh_Text_Styler_Defaults::symbols() );
		foreach ( $symbols as $sym ) {
			if ( ! empty( $sym['key'] ) ) {
				$buttons[] = 'drkh_sym_' . $sym['key'];
			}
		}

		$buttons[] = 'separator';
		$buttons[] = 'drkh_remove';
		return $buttons;
	}

	public static function register_buttons_row_3( $buttons ) {
		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->base ) {
			$allowed = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
			if ( ! in_array( $screen->post_type, (array) $allowed, true ) ) {
				return $buttons;
			}
		}

		$buttons[] = 'drkh_n0';
		$buttons[] = 'drkh_n1';
		$buttons[] = 'drkh_n2';
		$buttons[] = 'drkh_n3';
		$buttons[] = 'drkh_n4';
		$buttons[] = 'drkh_n5';
		$buttons[] = 'drkh_n6';
		$buttons[] = 'drkh_n7';
		$buttons[] = 'drkh_n8';
		$buttons[] = 'drkh_n9';
		return $buttons;
	}

	public static function extend_valid_elements( $init ) {
		$add = 'span[*],div[*],h3[*],i[*],svg[*],polyline[*],path[*]';
		if ( ! empty( $init['extended_valid_elements'] ) ) {
			$init['extended_valid_elements'] .= ',' . $add;
		} else {
			$init['extended_valid_elements'] = $add;
		}

		// Robust whitespace handling
		$init['trim_span_elements'] = false;
		$init['whitespace_elements'] = 'span,div,h3';

		return $init;
	}

	/**
	 * Convert leading spaces before plugin tags to &nbsp; before content is loaded into the editor.
	 */
	public static function preserve_spaces_for_editor( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		// Regex to find spaces (not &nbsp;) followed by a plugin class tag
		return preg_replace_callback(
			'/([ ]+)(<[^>]+class=["\'][^"\']*plugin-[^"\']*["\'])/i',
			function( $matches ) {
				return str_replace( ' ', '&nbsp;', $matches[1] ) . $matches[2];
			},
			$content
		);
	}

	public static function editor_css( $mce_css ) {
		$url = add_query_arg(
			array(
				'action' => 'dr_khasteh_editor_css',
				'ver'    => '1.9.0',
			),
			admin_url( 'admin-ajax.php' )
		);
		$mce_css .= ( $mce_css ? ',' : '' ) . esc_url_raw( $url );

		// Also add the base CSS file
		$base_url = get_template_directory_uri() . '/assets/css/text-styler.css?ver=1.1.0';
		$mce_css .= ',' . $base_url;

		return $mce_css;
	}

	public static function print_js_config() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'post' === $screen->base ) {
			$allowed = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
			if ( ! in_array( $screen->post_type, (array) $allowed, true ) ) {
				return;
			}
		} else {
			return;
		}

		$highlights = get_option( 'dr_khasteh_highlights', Dr_Khasteh_Text_Styler_Defaults::highlights() );
		$boxes      = get_option( 'dr_khasteh_boxes', Dr_Khasteh_Text_Styler_Defaults::boxes() );
		$custom     = Dr_Khasteh_Settings::get_custom_styles();
		$symbols    = get_option( 'dr_khasteh_custom_symbols', Dr_Khasteh_Text_Styler_Defaults::symbols() );

		echo '<script>window.drKhastehTextStylerConfig = ' . wp_json_encode( array(
			'highlights' => $highlights,
			'boxes'      => $boxes,
			'custom'     => $custom,
			'symbols'    => $symbols,
		) ) . ';</script>' . "\n";
	}

	/**
	 * Add buttons to the Text editor (Quicktags).
	 */
	public static function add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
			$tip_html = '<span class="plugin-tip_box" contenteditable="false">' .
				'<span class="fold">&nbsp;</span>' .
				'<span class="points_wrapper">' .
					'<span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span>' .
					'<span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span><span class="point">&nbsp;</span>' .
				'</span>' .
				'<span class="inner">' .
					'نکته' .
				'</span>' .
			'</span>&nbsp;';
			?>
			<script type="text/javascript">
				if (typeof QTags !== 'undefined') {
					QTags.addButton('drkh_qt_tip', 'Tip Box', <?php echo wp_json_encode($tip_html); ?>, '');
					QTags.addButton('drkh_qt_accent_h3', 'Accent H3', '<h3 class="plugin-accent_h3">', '</h3>');
				}
			</script>
			<?php
		}
	}
}

// AJAX: serve dynamic CSS into the TinyMCE iframe
add_action( 'wp_ajax_dr_khasteh_editor_css', 'dr_khasteh_serve_editor_css' );
function dr_khasteh_serve_editor_css() {
	header( 'Content-Type: text/css; charset=UTF-8' );
	// We call build_css directly here to ensure the editor always gets the latest hardcoded styles
	echo Dr_Khasteh_Text_Styler_CSS_Generator::build_css();
	exit;
}
