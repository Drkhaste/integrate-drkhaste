<?php
/**
 * Theme Settings page (Appearance > Theme Settings).
 *
 * @package Dr_Khasteh
 */

defined( 'ABSPATH' ) || exit;

class Dr_Khasteh_Settings {

	const MENU_SLUG = 'dr-khasteh-settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_upload' ) );
	}

	public static function add_menu() {
		add_theme_page(
			esc_html__( 'Theme Settings', 'dr-khasteh' ),
			esc_html__( 'Theme Settings', 'dr-khasteh' ),
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function handle_upload() {
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== self::MENU_SLUG ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'upload_fonts' ) {
			return;
		}

		if ( ! isset( $_POST['upload_fonts_btn'] ) ) {
			return;
		}

		check_admin_referer( 'dr_khasteh_upload_fonts', 'dr_khasteh_fonts_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'dr-khasteh' ) );
		}

		if ( empty( $_FILES['dr_khasteh_fonts_zip']['tmp_name'] ) ) {
			add_settings_error( 'dr_khasteh_localization_group', 'no_file', __( 'Please select a ZIP file to upload.', 'dr-khasteh' ), 'error' );
			wp_redirect( admin_url( 'themes.php?page=' . self::MENU_SLUG . '&tab=localization' ) );
			exit;
		}

		$file = $_FILES['dr_khasteh_fonts_zip'];

		// Verify file type
		$file_type = wp_check_filetype( $file['name'] );
		if ( $file_type['ext'] !== 'zip' ) {
			add_settings_error( 'dr_khasteh_localization_group', 'invalid_file', __( 'Invalid file type. Please upload a ZIP file.', 'dr-khasteh' ), 'error' );
			wp_redirect( admin_url( 'themes.php?page=' . self::MENU_SLUG . '&tab=localization' ) );
			exit;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$upload_dir = wp_upload_dir();
		$target_dir = $upload_dir['basedir'] . '/dr-khasteh-fonts';

		// Create target dir if not exists
		if ( ! $wp_filesystem->is_dir( $target_dir ) ) {
			$wp_filesystem->mkdir( $target_dir );
		}

		// Temporary directory for extraction
		$temp_dir = $upload_dir['basedir'] . '/dr-khasteh-fonts-temp';
		if ( $wp_filesystem->is_dir( $temp_dir ) ) {
			$wp_filesystem->delete( $temp_dir, true );
		}
		$wp_filesystem->mkdir( $temp_dir );

		// Unzip to temp
		$unzipped = unzip_file( $file['tmp_name'], $temp_dir );

		if ( is_wp_error( $unzipped ) ) {
			add_settings_error( 'dr_khasteh_localization_group', 'unzip_error', $unzipped->get_error_message(), 'error' );
			wp_redirect( admin_url( 'themes.php?page=' . self::MENU_SLUG . '&tab=localization' ) );
			exit;
		}

		// Security: Filter files - only allow specific extensions
		$allowed_exts = array( 'css', 'woff2', 'woff', 'ttf', 'eot', 'otf', 'svg' );

		// Use a recursive function to move files and validate
		self::secure_move_files( $temp_dir, $target_dir, $allowed_exts );

		// Clean up temp
		$wp_filesystem->delete( $temp_dir, true );

		add_settings_error( 'dr_khasteh_localization_group', 'upload_success', __( 'Assets uploaded and localized successfully!', 'dr-khasteh' ), 'updated' );
		wp_redirect( admin_url( 'themes.php?page=' . self::MENU_SLUG . '&tab=localization' ) );
		exit;
	}

	private static function secure_move_files( $source, $destination, $allowed_exts ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem->is_dir( $destination ) ) {
			$wp_filesystem->mkdir( $destination );
		}

		$file_list = $wp_filesystem->dirlist( $source );

		foreach ( $file_list as $file ) {
			$source_path = $source . '/' . $file['name'];
			$dest_path   = $destination . '/' . $file['name'];

			if ( $file['type'] === 'd' ) {
				self::secure_move_files( $source_path, $dest_path, $allowed_exts );
			} else {
				$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
				if ( in_array( strtolower( $ext ), $allowed_exts, true ) ) {
					$wp_filesystem->copy( $source_path, $dest_path, true );
				}
			}
		}
	}

	/**
	 * Check if local assets exist in the uploads directory.
	 *
	 * @return array Status of local assets.
	 */
	public static function get_localization_status() {
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'] . '/dr-khasteh-fonts';

		return array(
			'fonts'      => file_exists( $base_dir . '/local-fonts.css' ),
			'fontawesome' => file_exists( $base_dir . '/fontawesome-local.css' ),
		);
	}

	/**
	 * Merges saved custom styles with defaults.
	 */
	public static function get_custom_styles() {
		$defaults = Dr_Khasteh_Text_Styler_Defaults::custom_styles();
		$saved    = get_option( 'dr_khasteh_custom_styles', array() );

		if ( empty( $saved ) ) {
			return $defaults;
		}

		$merged = array();
		foreach ( $defaults as $default ) {
			$found = false;
			foreach ( $saved as $item ) {
				if ( isset( $item['key'] ) && $item['key'] === $default['key'] ) {
					$merged[] = array_merge( $default, $item );
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$merged[] = $default;
			}
		}

		return $merged;
	}

	public static function register_settings() {
		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_highlights',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_highlights' ),
				'default'           => Dr_Khasteh_Text_Styler_Defaults::highlights(),
			)
		);

		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_custom_styles',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_custom_styles' ),
				'default'           => Dr_Khasteh_Text_Styler_Defaults::custom_styles(),
			)
		);

		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_boxes',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_boxes' ),
				'default'           => Dr_Khasteh_Text_Styler_Defaults::boxes(),
			)
		);

		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_post_types',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_post_types' ),
				'default'           => array( 'post', 'page' ),
			)
		);

		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_num_style',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_num_style' ),
				'default'           => Dr_Khasteh_Text_Styler_Defaults::num_style(),
			)
		);

		register_setting(
			'dr_khasteh_text_styler_group',
			'dr_khasteh_custom_symbols',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_custom_symbols' ),
				'default'           => Dr_Khasteh_Text_Styler_Defaults::symbols(),
			)
		);

		register_setting(
			'dr_khasteh_localization_group',
			'dr_khasteh_font_source',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'cdn',
			)
		);

		register_setting(
			'dr_khasteh_localization_group',
			'dr_khasteh_admin_font',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'dr_khasteh_general_group',
			'dr_khasteh_admin_css',
			array(
				'sanitize_callback' => 'wp_strip_all_tags',
				'default'           => '',
			)
		);

		register_setting(
			'dr_khasteh_general_group',
			'dr_khasteh_login_css',
			array(
				'sanitize_callback' => 'wp_strip_all_tags',
				'default'           => '',
			)
		);

		register_setting(
			'dr_khasteh_general_group',
			'dr_khasteh_logo_align',
			array(
				'sanitize_callback' => 'sanitize_key',
				'default'           => 'right',
			)
		);

		register_setting(
			'dr_khasteh_general_group',
			'dr_khasteh_logo_width',
			array(
				'sanitize_callback' => 'absint',
				'default'           => 150,
			)
		);

		register_setting(
			'dr_khasteh_general_group',
			'dr_khasteh_font_settings_post_types',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_post_types' ),
				'default'           => array( 'topic' ),
			)
		);

	}

	public static function sanitize_highlights( $raw ) {
		if ( ! is_array( $raw ) ) {
			return Dr_Khasteh_Text_Styler_Defaults::highlights();
		}
		$clean = array();
		foreach ( $raw as $item ) {
			$clean[] = array(
				'key'        => sanitize_html_class( $item['key'] ?? '' ),
				'label'      => sanitize_text_field( $item['label'] ?? '' ),
				'color'      => sanitize_hex_color( $item['color'] ?? '#ffffff' ),
				'dark_color' => sanitize_hex_color( $item['dark_color'] ?? '#ffffff' ),
			);
		}
		add_action( 'shutdown', array( 'Dr_Khasteh_Text_Styler_CSS_Generator', 'regenerate' ) );
		return $clean;
	}

	public static function sanitize_custom_styles( $raw ) {
		if ( ! is_array( $raw ) ) {
			return Dr_Khasteh_Text_Styler_Defaults::custom_styles();
		}
		$clean = array();
		foreach ( $raw as $item ) {
			$entry = array(
				'key'   => sanitize_html_class( $item['key'] ?? '' ),
				'label' => sanitize_text_field( $item['label'] ?? '' ),
			);

			if ( isset( $item['color'] ) )             $entry['color']             = sanitize_hex_color( $item['color'] );
			if ( isset( $item['dark_color'] ) )        $entry['dark_color']        = sanitize_hex_color( $item['dark_color'] );
			if ( isset( $item['line_color'] ) )        $entry['line_color']        = sanitize_hex_color( $item['line_color'] );
			if ( isset( $item['symbol'] ) )            $entry['symbol']            = sanitize_text_field( $item['symbol'] );
			if ( isset( $item['glow_color'] ) )        $entry['glow_color']        = sanitize_hex_color( $item['glow_color'] );
			if ( isset( $item['dark_glow_color'] ) )   $entry['dark_glow_color']   = sanitize_hex_color( $item['dark_glow_color'] );
			if ( isset( $item['border_color'] ) )      $entry['border_color']      = sanitize_hex_color( $item['border_color'] );
			if ( isset( $item['dark_border_color'] ) ) $entry['dark_border_color'] = sanitize_hex_color( $item['dark_border_color'] );
			if ( isset( $item['bg_color'] ) )          $entry['bg_color']          = sanitize_hex_color( $item['bg_color'] );
			if ( isset( $item['dark_bg_color'] ) )     $entry['dark_bg_color']     = sanitize_hex_color( $item['dark_bg_color'] );
			if ( isset( $item['font_size'] ) )         $entry['font_size']         = absint( $item['font_size'] );
			if ( isset( $item['padding_x'] ) )         $entry['padding_x']         = absint( $item['padding_x'] );
			if ( isset( $item['padding_y'] ) )         $entry['padding_y']         = absint( $item['padding_y'] );
			if ( isset( $item['border_radius'] ) )     $entry['border_radius']     = absint( $item['border_radius'] );

			$clean[] = $entry;
		}
		add_action( 'shutdown', array( 'Dr_Khasteh_Text_Styler_CSS_Generator', 'regenerate' ) );
		return $clean;
	}

	public static function sanitize_boxes( $raw ) {
		if ( ! is_array( $raw ) ) {
			return Dr_Khasteh_Text_Styler_Defaults::boxes();
		}
		$clean = array();
		foreach ( $raw as $item ) {
			$clean[] = array(
				'key'                => sanitize_html_class( $item['key'] ?? '' ),
				'label'              => sanitize_text_field( $item['label'] ?? '' ),
				'bg_color'           => sanitize_hex_color( $item['bg_color'] ?? '#ffffff' ),
				'border_color'       => sanitize_hex_color( $item['border_color'] ?? '#000000' ),
				'dark_bg_color'      => sanitize_hex_color( $item['dark_bg_color'] ?? '#ffffff' ),
				'dark_border_color'  => sanitize_hex_color( $item['dark_border_color'] ?? '#000000' ),
				'border_width'       => absint( $item['border_width'] ?? 2 ),
				'border_radius'      => absint( $item['border_radius'] ?? 6 ),
				'padding'            => absint( $item['padding'] ?? 14 ),
				'margin'             => absint( $item['margin'] ?? 16 ),
			);
		}
		add_action( 'shutdown', array( 'Dr_Khasteh_Text_Styler_CSS_Generator', 'regenerate' ) );
		return $clean;
	}

	public static function sanitize_post_types( $raw ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}
		return array_map( 'sanitize_key', $raw );
	}

	public static function sanitize_custom_symbols( $raw ) {
		if ( ! is_array( $raw ) ) {
			return Dr_Khasteh_Text_Styler_Defaults::symbols();
		}
		$clean = array();
		foreach ( $raw as $item ) {
			if ( empty( $item['char'] ) ) continue;
			$clean[] = array(
				'key'        => sanitize_html_class( $item['key'] ?? 'sym-' . substr(md5($item['char']), 0, 6) ),
				'label'      => sanitize_text_field( $item['label'] ?? '' ),
				'char'       => sanitize_text_field( $item['char'] ),
				'color'      => sanitize_hex_color( $item['color'] ?? '' ),
				'dark_color' => sanitize_hex_color( $item['dark_color'] ?? '' ),
				'font_size'  => absint( $item['font_size'] ?? 18 ),
			);
		}
		add_action( 'shutdown', array( 'Dr_Khasteh_Text_Styler_CSS_Generator', 'regenerate' ) );
		return $clean;
	}

	public static function sanitize_num_style( $raw ) {
		if ( ! is_array( $raw ) ) {
			return Dr_Khasteh_Text_Styler_Defaults::num_style();
		}
		return array(
			'bg_color'          => sanitize_hex_color( $raw['bg_color'] ?? '' ),
			'dark_bg_color'     => sanitize_hex_color( $raw['dark_bg_color'] ?? '' ),
			'border_color'      => sanitize_hex_color( $raw['border_color'] ?? '' ),
			'dark_border_color' => sanitize_hex_color( $raw['dark_border_color'] ?? '' ),
			'text_color'        => sanitize_hex_color( $raw['text_color'] ?? '' ),
			'dark_text_color'   => sanitize_hex_color( $raw['dark_text_color'] ?? '' ),
			'glow_color'        => sanitize_text_field( $raw['glow_color'] ?? '' ),
			'dark_glow_color'   => sanitize_text_field( $raw['dark_glow_color'] ?? '' ),
			'border_radius'     => absint( $raw['border_radius'] ?? 8 ),
			'badge_size'        => absint( $raw['badge_size'] ?? 32 ),
			'font_size'         => absint( $raw['font_size'] ?? 18 ),
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'dr-khasteh' ) );
		}

		$highlights  = get_option( 'dr_khasteh_highlights', Dr_Khasteh_Text_Styler_Defaults::highlights() );
		$boxes       = get_option( 'dr_khasteh_boxes', Dr_Khasteh_Text_Styler_Defaults::boxes() );
		$num_style   = get_option( 'dr_khasteh_num_style', Dr_Khasteh_Text_Styler_Defaults::num_style() );
		$custom_symbols = get_option( 'dr_khasteh_custom_symbols', Dr_Khasteh_Text_Styler_Defaults::symbols() );
		$custom_styles = self::get_custom_styles();
		$post_types  = get_option( 'dr_khasteh_post_types', array( 'post', 'page' ) );
		$font_source = get_option( 'dr_khasteh_font_source', 'cdn' );
		$admin_font  = get_option( 'dr_khasteh_admin_font', '' );
		$all_types   = get_post_types( array( 'public' => true ), 'objects' );

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		settings_errors( 'dr_khasteh_text_styler_group' );
		settings_errors( 'dr_khasteh_localization_group' );
		settings_errors( 'dr_khasteh_general_group' );
		?>
		<style>
			.dr-khasteh-ltr-textarea {
				direction: ltr !important;
				text-align: left !important;
				font-family: monospace;
			}
		</style>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme Settings', 'dr-khasteh' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="?page=<?php echo self::MENU_SLUG; ?>&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General Settings', 'dr-khasteh' ); ?>
				</a>
				<a href="?page=<?php echo self::MENU_SLUG; ?>&tab=text_styler" class="nav-tab <?php echo $active_tab === 'text_styler' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Text Styler', 'dr-khasteh' ); ?>
				</a>
				<a href="?page=<?php echo self::MENU_SLUG; ?>&tab=localization" class="nav-tab <?php echo $active_tab === 'localization' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Asset Localization', 'dr-khasteh' ); ?>
				</a>
			</h2>

			<?php if ( 'general' === $active_tab ) : ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'dr_khasteh_general_group' );
				$admin_css  = get_option( 'dr_khasteh_admin_css', '' );
				$login_css  = get_option( 'dr_khasteh_login_css', '' );
				$logo_align = get_option( 'dr_khasteh_logo_align', 'right' );
				$logo_width = get_option( 'dr_khasteh_logo_width', 150 );
				$font_settings_pt = get_option( 'dr_khasteh_font_settings_post_types', array( 'topic' ) );
				?>

				<h2><?php esc_html_e( 'Logo Settings', 'dr-khasteh' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Logo Alignment', 'dr-khasteh' ); ?></th>
						<td>
							<select name="dr_khasteh_logo_align">
								<option value="right" <?php selected( $logo_align, 'right' ); ?>><?php _e( 'Right', 'dr-khasteh' ); ?></option>
								<option value="center" <?php selected( $logo_align, 'center' ); ?>><?php _e( 'Center', 'dr-khasteh' ); ?></option>
								<option value="left" <?php selected( $logo_align, 'left' ); ?>><?php _e( 'Left', 'dr-khasteh' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Logo Width (px)', 'dr-khasteh' ); ?></th>
						<td>
							<input type="number" name="dr_khasteh_logo_width" value="<?php echo esc_attr( $logo_width ); ?>" />
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Font Settings Visibility', 'dr-khasteh' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Enable for Post Types', 'dr-khasteh' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $all_types as $type ) : ?>
								<label style="display:block; margin:.3em 0;">
									<input type="checkbox" name="dr_khasteh_font_settings_post_types[]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $font_settings_pt, true ) ); ?> />
									<?php echo esc_html( $type->label ); ?>
								</label>
								<?php endforeach; ?>
							</fieldset>
							<p class="description"><?php _e( 'Choose which post types should display the font family and size settings in the header.', 'dr-khasteh' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Custom CSS', 'dr-khasteh' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Admin Dashboard CSS', 'dr-khasteh' ); ?></th>
						<td>
							<textarea name="dr_khasteh_admin_css" rows="10" cols="50" class="large-text dr-khasteh-ltr-textarea"><?php echo esc_textarea( $admin_css ); ?></textarea>
							<p class="description"><?php _e( 'CSS added here will be applied to the WordPress Admin Dashboard.', 'dr-khasteh' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Login Page CSS', 'dr-khasteh' ); ?></th>
						<td>
							<textarea name="dr_khasteh_login_css" rows="10" cols="50" class="large-text dr-khasteh-ltr-textarea"><?php echo esc_textarea( $login_css ); ?></textarea>
							<p class="description"><?php _e( 'CSS added here will be applied to the WordPress Login Page.', 'dr-khasteh' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Save General Settings', 'dr-khasteh' ) ); ?>
			</form>
			<?php endif; ?>

			<?php if ( 'localization' === $active_tab ) : ?>
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
				<h2><?php esc_html_e( 'Localization Status', 'dr-khasteh' ); ?></h2>
				<?php
				$status = self::get_localization_status();
				?>
				<ul style="margin: 0; padding: 0; list-style: none;">
					<li style="margin-bottom: 10px;">
						<strong><?php _e( 'Local Fonts CSS:', 'dr-khasteh' ); ?></strong>
						<?php if ( $status['fonts'] ) : ?>
							<span style="color: green; font-weight: bold;">[<?php _e( 'Available', 'dr-khasteh' ); ?>]</span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">[<?php _e( 'Not Found', 'dr-khasteh' ); ?>]</span>
						<?php endif; ?>
					</li>
					<li>
						<strong><?php _e( 'FontAwesome Local CSS:', 'dr-khasteh' ); ?></strong>
						<?php if ( $status['fontawesome'] ) : ?>
							<span style="color: green; font-weight: bold;">[<?php _e( 'Available', 'dr-khasteh' ); ?>]</span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">[<?php _e( 'Not Found', 'dr-khasteh' ); ?>]</span>
						<?php endif; ?>
					</li>
				</ul>
			</div>

			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
				<h2><?php esc_html_e( 'Asset Localization', 'dr-khasteh' ); ?></h2>
				<p><?php _e( 'To use fonts locally, first download the ZIP file using the tool below, then upload it here.', 'dr-khasteh' ); ?></p>
				<a href="<?php echo get_template_directory_uri() . '/assets/font-tool.html'; ?>" target="_blank" class="button"><?php _e( 'Open Font Downloader Tool', 'dr-khasteh' ); ?></a>

				<hr>

				<form method="post" action="<?php echo admin_url( 'themes.php?page=' . self::MENU_SLUG . '&action=upload_fonts' ); ?>" enctype="multipart/form-data">
					<?php wp_nonce_field( 'dr_khasteh_upload_fonts', 'dr_khasteh_fonts_nonce' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Upload Local Assets (ZIP)', 'dr-khasteh' ); ?></th>
							<td>
								<input type="file" name="dr_khasteh_fonts_zip" accept=".zip" />
								<p class="description"><?php _e( 'Upload the "dr-khasteh-local-assets.zip" file generated by the tool.', 'dr-khasteh' ); ?></p>
								<input type="submit" name="upload_fonts_btn" class="button button-secondary" value="<?php _e( 'Upload & Extract', 'dr-khasteh' ); ?>" />
							</td>
						</tr>
					</table>
				</form>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'dr_khasteh_localization_group' );
				?>
				<!-- FONT SOURCE -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Asset Source', 'dr-khasteh' ); ?></h2>
				<fieldset>
					<label style="display:block; margin:.3em 0;">
						<input type="radio" name="dr_khasteh_font_source" value="cdn" <?php checked( $font_source, 'cdn' ); ?> />
						<?php _e( 'CDN (Remote Assets)', 'dr-khasteh' ); ?>
					</label>
					<label style="display:block; margin:.3em 0;">
						<input type="radio" name="dr_khasteh_font_source" value="local" <?php checked( $font_source, 'local' ); ?> />
						<?php _e( 'Local (Stored in uploads folder)', 'dr-khasteh' ); ?>
					</label>
				</fieldset>

				<h2 style="margin-top:2em;"><?php esc_html_e( 'Admin Dashboard Font', 'dr-khasteh' ); ?></h2>
				<p class="description"><?php _e( 'Choose a font to apply to the WordPress Admin Dashboard. Note: The font must be available (either via CDN or Local Assets).', 'dr-khasteh' ); ?></p>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Select Font', 'dr-khasteh' ); ?></th>
						<td>
							<select name="dr_khasteh_admin_font">
								<option value="" <?php selected( $admin_font, '' ); ?>><?php _e( 'Default WordPress Font', 'dr-khasteh' ); ?></option>
								<option value="Vazirmatn" <?php selected( $admin_font, 'Vazirmatn' ); ?>>Vazirmatn</option>
								<option value="'IBM Plex Sans Arabic'" <?php selected( $admin_font, "'IBM Plex Sans Arabic'" ); ?>>IBM Plex Sans Arabic</option>
								<option value="'Playpen Sans Arabic'" <?php selected( $admin_font, "'Playpen Sans Arabic'" ); ?>>Playpen Sans Arabic</option>
								<option value="Zain" <?php selected( $admin_font, 'Zain' ); ?>>Zain</option>
								<option value="'Noto Sans Arabic'" <?php selected( $admin_font, "'Noto Sans Arabic'" ); ?>>Noto Sans Arabic</option>
							</select>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Save Localization Settings', 'dr-khasteh' ) ); ?>
			</form>
			<?php endif; ?>

			<?php if ( 'text_styler' === $active_tab ) : ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'dr_khasteh_text_styler_group' );
				?>

				<!-- HIGHLIGHTS -->
				<h2><?php esc_html_e( 'Highlight Colors', 'dr-khasteh' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Light Mode Color', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Dark Mode Color', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Preview', 'dr-khasteh' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $highlights as $i => $h ) : ?>
						<tr>
							<td>
								<input type="hidden" name="dr_khasteh_highlights[<?php echo $i; ?>][key]" value="<?php echo esc_attr( $h['key'] ); ?>" />
								<input type="text" name="dr_khasteh_highlights[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $h['label'] ); ?>" class="regular-text" />
							</td>
							<td>
								<input type="text" name="dr_khasteh_highlights[<?php echo $i; ?>][color]" value="<?php echo esc_attr( $h['color'] ); ?>" class="wpts-color-picker" />
							</td>
							<td>
								<input type="text" name="dr_khasteh_highlights[<?php echo $i; ?>][dark_color]" value="<?php echo esc_attr( $h['dark_color'] ); ?>" class="wpts-color-picker" />
							</td>
							<td>
								<span class="wpts-preview-swatch" style="background:<?php echo esc_attr( $h['color'] ); ?>; padding:2px 10px; border-radius:3px; color: #000;">
									Light
								</span>
								<span class="wpts-preview-swatch-dark" style="background:<?php echo esc_attr( $h['dark_color'] ); ?>; padding:2px 10px; border-radius:3px; color: #fff; margin-left: 5px;">
									Dark
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<!-- BOXES -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Box Presets', 'dr-khasteh' ); ?></h2>
				<?php foreach ( $boxes as $i => $b ) : ?>
				<div class="drkh-accordion-item" style="border: 1px solid #ccc; margin-bottom: 10px; background: #fff;">
					<div class="drkh-accordion-header" style="cursor:pointer; padding: 12px 15px; font-weight:600; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9;">
						<span><?php echo esc_html( $b['label'] ); ?></span>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="drkh-accordion-content" style="display: none; padding: 15px; border-top: 1px solid #eee;">
					<div style="display: flex; gap: 20px;">
						<div style="flex: 1;">
							<input type="hidden" name="dr_khasteh_boxes[<?php echo $i; ?>][key]" value="<?php echo esc_attr( $b['key'] ); ?>" />
							<p><label><?php _e('Label:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_boxes[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $b['label'] ); ?>" /></p>

							<h4><?php _e('Light Mode', 'dr-khasteh'); ?></h4>
							<p><label><?php _e('Background:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_boxes[<?php echo $i; ?>][bg_color]" value="<?php echo esc_attr( $b['bg_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Border:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_boxes[<?php echo $i; ?>][border_color]" value="<?php echo esc_attr( $b['border_color'] ); ?>" class="wpts-color-picker" /></p>

							<h4><?php _e('Dark Mode', 'dr-khasteh'); ?></h4>
							<p><label><?php _e('Background:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_boxes[<?php echo $i; ?>][dark_bg_color]" value="<?php echo esc_attr( $b['dark_bg_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Border:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_boxes[<?php echo $i; ?>][dark_border_color]" value="<?php echo esc_attr( $b['dark_border_color'] ); ?>" class="wpts-color-picker" /></p>
						</div>
						<div style="flex: 1;">
							<h4><?php _e('Dimensions', 'dr-khasteh'); ?></h4>
							<p><label><?php _e('Border Width:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_boxes[<?php echo $i; ?>][border_width]" value="<?php echo esc_attr( $b['border_width'] ); ?>" /></p>
							<p><label><?php _e('Border Radius:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_boxes[<?php echo $i; ?>][border_radius]" value="<?php echo esc_attr( $b['border_radius'] ); ?>" /></p>
							<p><label><?php _e('Padding:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_boxes[<?php echo $i; ?>][padding]" value="<?php echo esc_attr( $b['padding'] ); ?>" /></p>
							<p><label><?php _e('Margin:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_boxes[<?php echo $i; ?>][margin]" value="<?php echo esc_attr( $b['margin'] ); ?>" /></p>
						</div>
					</div>
					</div>
				</div>
				<?php endforeach; ?>

				<!-- CUSTOM STYLES -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Custom Styles', 'dr-khasteh' ); ?></h2>
				<div class="dr-khasteh-custom-styles-wrapper">
					<?php foreach ( $custom_styles as $i => $s ) : ?>
					<div class="drkh-accordion-item" style="border: 1px solid #ccc; margin-bottom: 10px; background: #fff;">
						<div class="drkh-accordion-header" style="cursor:pointer; padding: 12px 15px; font-weight:600; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9;">
							<span><?php echo esc_html( $s['label'] ); ?></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</div>
						<div class="drkh-accordion-content" style="display: none; padding: 15px; border-top: 1px solid #eee;">
						<?php if ( $s['key'] === 'tip_box' ) : ?>
							<p><?php _e('The style for this element is fixed and cannot be changed.', 'dr-khasteh'); ?></p>
							<input type="hidden" name="dr_khasteh_custom_styles[<?php echo $i; ?>][key]" value="tip_box" />
							<input type="hidden" name="dr_khasteh_custom_styles[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $s['label'] ); ?>" />
						<?php else : ?>
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
							<div>
								<input type="hidden" name="dr_khasteh_custom_styles[<?php echo $i; ?>][key]" value="<?php echo esc_attr( $s['key'] ); ?>" />
								<p><label><?php _e('Label:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $s['label'] ); ?>" class="regular-text" /></p>

								<p><label><?php _e('Symbol:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][symbol]" value="<?php echo esc_attr( $s['symbol'] ?? '' ); ?>" /></p>

								<p><label><?php _e('Font Size (px):', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_custom_styles[<?php echo $i; ?>][font_size]" value="<?php echo esc_attr( $s['font_size'] ?? 14 ); ?>" /></p>

								<?php if ( isset($s['line_color']) ) : ?>
								<p><label><?php _e('Line Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][line_color]" value="<?php echo esc_attr( $s['line_color'] ); ?>" class="wpts-color-picker" /></p>
								<?php endif; ?>

								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
									<p><label><?php _e('Glow (Light):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][glow_color]" value="<?php echo esc_attr( $s['glow_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
									<p><label><?php _e('Glow (Dark):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][dark_glow_color]" value="<?php echo esc_attr( $s['dark_glow_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
								</div>
							</div>
							<div>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
									<p><label><?php _e('Color (Light):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][color]" value="<?php echo esc_attr( $s['color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
									<p><label><?php _e('Color (Dark):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][dark_color]" value="<?php echo esc_attr( $s['dark_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
								</div>

								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
									<p><label><?php _e('BG (Light):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][bg_color]" value="<?php echo esc_attr( $s['bg_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
									<p><label><?php _e('BG (Dark):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][dark_bg_color]" value="<?php echo esc_attr( $s['dark_bg_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
								</div>

								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
									<p><label><?php _e('Border (Light):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][border_color]" value="<?php echo esc_attr( $s['border_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
									<p><label><?php _e('Border (Dark):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_custom_styles[<?php echo $i; ?>][dark_border_color]" value="<?php echo esc_attr( $s['dark_border_color'] ?? '' ); ?>" class="wpts-color-picker" /></p>
								</div>

								<div style="display: flex; gap: 10px; align-items: flex-end;">
									<p style="flex:1;"><label><?php _e('Pad X:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_custom_styles[<?php echo $i; ?>][padding_x]" value="<?php echo esc_attr( $s['padding_x'] ?? 12 ); ?>" style="width:100%;" /></p>
									<p style="flex:1;"><label><?php _e('Pad Y:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_custom_styles[<?php echo $i; ?>][padding_y]" value="<?php echo esc_attr( $s['padding_y'] ?? 2 ); ?>" style="width:100%;" /></p>
									<p style="flex:1;"><label><?php _e('Radius:', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_custom_styles[<?php echo $i; ?>][border_radius]" value="<?php echo esc_attr( $s['border_radius'] ?? 6 ); ?>" style="width:100%;" /></p>
								</div>
							</div>
						</div>
						<?php endif; ?>
						</div>
					</div>
					<?php endforeach; ?>
				</div>

				<!-- CUSTOM SYMBOLS -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Custom Symbols Buttons', 'dr-khasteh' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Label & Key', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Character', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Colors (Light/Dark)', 'dr-khasteh' ); ?></th>
							<th><?php esc_html_e( 'Size (px)', 'dr-khasteh' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $custom_symbols as $i => $sym ) : ?>
						<tr>
							<td>
								<input type="text" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $sym['label'] ); ?>" placeholder="Label" class="regular-text" style="width:100%; margin-bottom:5px;" /><br>
								<input type="text" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][key]" value="<?php echo esc_attr( $sym['key'] ); ?>" placeholder="Key (unique)" class="regular-text" style="width:100%;" />
							</td>
							<td>
								<input type="text" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][char]" value="<?php echo esc_attr( $sym['char'] ); ?>" style="font-size: 24px; width: 60px; text-align: center;" />
							</td>
							<td>
								<input type="text" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][color]" value="<?php echo esc_attr( $sym['color'] ); ?>" class="wpts-color-picker" />
								<input type="text" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][dark_color]" value="<?php echo esc_attr( $sym['dark_color'] ); ?>" class="wpts-color-picker" />
							</td>
							<td>
								<input type="number" name="dr_khasteh_custom_symbols[<?php echo $i; ?>][font_size]" value="<?php echo esc_attr( $sym['font_size'] ); ?>" style="width:60px;" />
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<!-- FANTASY NUMBERS -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Fantasy Numbers Style', 'dr-khasteh' ); ?></h2>
				<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
						<div>
							<h4><?php _e('Light Mode', 'dr-khasteh'); ?></h4>
							<p><label><?php _e('Background Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[bg_color]" value="<?php echo esc_attr( $num_style['bg_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Border Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[border_color]" value="<?php echo esc_attr( $num_style['border_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Text Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[text_color]" value="<?php echo esc_attr( $num_style['text_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Glow Color (CSS value):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[glow_color]" value="<?php echo esc_attr( $num_style['glow_color'] ); ?>" class="regular-text" /></p>
						</div>
						<div>
							<h4><?php _e('Dark Mode', 'dr-khasteh'); ?></h4>
							<p><label><?php _e('Background Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[dark_bg_color]" value="<?php echo esc_attr( $num_style['dark_bg_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Border Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[dark_border_color]" value="<?php echo esc_attr( $num_style['dark_border_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Text Color:', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[dark_text_color]" value="<?php echo esc_attr( $num_style['dark_text_color'] ); ?>" class="wpts-color-picker" /></p>
							<p><label><?php _e('Glow Color (CSS value):', 'dr-khasteh'); ?></label><br><input type="text" name="dr_khasteh_num_style[dark_glow_color]" value="<?php echo esc_attr( $num_style['dark_glow_color'] ); ?>" class="regular-text" /></p>
						</div>
					</div>
					<hr>
					<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
						<p><label><?php _e('Badge Size (px):', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_num_style[badge_size]" value="<?php echo esc_attr( $num_style['badge_size'] ); ?>" /></p>
						<p><label><?php _e('Font Size (px):', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_num_style[font_size]" value="<?php echo esc_attr( $num_style['font_size'] ); ?>" /></p>
						<p><label><?php _e('Border Radius (px):', 'dr-khasteh'); ?></label><br><input type="number" name="dr_khasteh_num_style[border_radius]" value="<?php echo esc_attr( $num_style['border_radius'] ); ?>" /></p>
					</div>
				</div>

				<!-- POST TYPES -->
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Enable for Post Types', 'dr-khasteh' ); ?></h2>
				<fieldset>
					<?php foreach ( $all_types as $type ) : ?>
					<label style="display:block; margin:.3em 0;">
						<input type="checkbox" name="dr_khasteh_post_types[]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $post_types, true ) ); ?> />
						<?php echo esc_html( $type->label ); ?>
					</label>
					<?php endforeach; ?>
				</fieldset>

				<?php submit_button( esc_html__( 'Save Settings', 'dr-khasteh' ) ); ?>
			</form>
			<?php endif; ?>
		</div>
		<?php
	}
}
