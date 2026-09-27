<?php
/**
 * Default values for highlights and boxes with Dark Mode support.
 *
 * @package Dr_Khasteh
 */

defined( 'ABSPATH' ) || exit;

class Dr_Khasteh_Text_Styler_Defaults {

	/**
	 * Default highlight colors.
	 *
	 * @return array[]
	 */
	public static function highlights() {
		return array(
			array(
				'key'        => 'yellow',
				'label'      => __( 'Yellow', 'dr-khasteh' ),
				'color'      => '#fff176',
				'dark_color' => '#827717',
			),
			array(
				'key'        => 'green',
				'label'      => __( 'Green', 'dr-khasteh' ),
				'color'      => '#b9f6ca',
				'dark_color' => '#1b5e20',
			),
			array(
				'key'        => 'blue',
				'label'      => __( 'Blue', 'dr-khasteh' ),
				'color'      => '#b3e5fc',
				'dark_color' => '#01579b',
			),
			array(
				'key'        => 'red',
				'label'      => __( 'Red', 'dr-khasteh' ),
				'color'      => '#ffcdd2',
				'dark_color' => '#b71c1c',
			),
		);
	}

	/**
	 * Default custom styles.
	 *
	 * @return array[]
	 */
	public static function custom_styles() {
		return array(
			array(
				'key'        => 'accent_h3',
				'label'      => __( 'Accent Heading', 'dr-khasteh' ),
				'color'      => '#1e88e5',
				'dark_color' => '#0ea5e9',
				'line_color' => '#1e88e5',
			),
			array(
				'key'        => 'symbol_bullet',
				'label'      => __( 'Symbol Bullet', 'dr-khasteh' ),
				'color'      => '#2e7d32',
				'dark_color' => '#10b981',
				'symbol'     => '✦',
			),
			array(
				'key'        => 'glow_text',
				'label'      => __( 'Glow Text', 'dr-khasteh' ),
				'color'      => '#7b1fa2',
				'dark_color' => '#a855f7',
				'glow_color' => '#e1bee7',
				'dark_glow_color' => '#4a148c',
			),
			array(
				'key'        => 'corner_border',
				'label'      => __( 'Corner Border', 'dr-khasteh' ),
				'border_color' => '#f9a825',
				'dark_border_color' => '#f59e0b',
				'bg_color'   => '#fff8e1',
				'dark_bg_color' => '#451a03',
			),
		);
	}

	/**
	 * Default box presets.
	 *
	 * @return array[]
	 */
	public static function boxes() {
		return array(
			array(
				'key'                => 'info',
				'label'              => __( 'Info Box', 'dr-khasteh' ),
				'bg_color'           => '#e3f2fd',
				'border_color'       => '#1e88e5',
				'dark_bg_color'      => '#0c4a6e',
				'dark_border_color'  => '#0ea5e9',
				'border_width'       => '2',
				'border_radius'      => '6',
				'padding'            => '14',
				'margin'             => '16',
			),
			array(
				'key'                => 'warning',
				'label'              => __( 'Warning Box', 'dr-khasteh' ),
				'bg_color'           => '#fff8e1',
				'border_color'       => '#f9a825',
				'dark_bg_color'      => '#78350f',
				'dark_border_color'  => '#f59e0b',
				'border_width'       => '2',
				'border_radius'      => '6',
				'padding'            => '14',
				'margin'             => '16',
			),
			array(
				'key'                => 'success',
				'label'              => __( 'Success Box', 'dr-khasteh' ),
				'bg_color'           => '#e8f5e9',
				'border_color'       => '#2e7d32',
				'dark_bg_color'      => '#064e3b',
				'dark_border_color'  => '#10b981',
				'border_width'       => '2',
				'border_radius'      => '6',
				'padding'            => '14',
				'margin'             => '16',
			),
			array(
				'key'                => 'custom',
				'label'              => __( 'Custom Box', 'dr-khasteh' ),
				'bg_color'           => '#f3e5f5',
				'border_color'       => '#7b1fa2',
				'dark_bg_color'      => '#581c87',
				'dark_border_color'  => '#a855f7',
				'border_width'       => '2',
				'border_radius'      => '6',
				'padding'            => '14',
				'margin'             => '16',
			),
		);
	}

	/**
	 * Default settings for Fantasy Numbers.
	 *
	 * @return array
	 */
	public static function num_style() {
		return array(
			'bg_color'          => '#f8fafc',
			'dark_bg_color'     => '#1e293b',
			'border_color'      => '#6366f1',
			'dark_border_color' => '#818cf8',
			'text_color'        => '#4338ca',
			'dark_text_color'   => '#e0e7ff',
			'glow_color'        => 'rgba(99, 102, 241, 0.3)',
			'dark_glow_color'   => 'rgba(129, 140, 248, 0.5)',
			'border_radius'     => '8',
			'badge_size'        => '32',
			'font_size'         => '18',
		);
	}

	/**
	 * Default custom symbols.
	 *
	 * @return array[]
	 */
	public static function symbols() {
		return array(
			array(
				'key'        => 'diamond',
				'label'      => __( 'Diamond Symbol', 'dr-khasteh' ),
				'char'       => '❖',
				'color'      => '#4f46e5',
				'dark_color' => '#818cf8',
				'font_size'  => '20',
			),
			array(
				'key'        => 'circle',
				'label'      => __( 'Circle Symbol', 'dr-khasteh' ),
				'char'       => '●',
				'color'      => '#e11d48',
				'dark_color' => '#fb7185',
				'font_size'  => '18',
			),
		);
	}
}
