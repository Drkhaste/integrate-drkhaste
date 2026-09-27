<?php
/**
 * Generates dynamic CSS from theme settings with Dark Mode support.
 *
 * @package Dr_Khasteh
 */

defined( 'ABSPATH' ) || exit;

class Dr_Khasteh_Text_Styler_CSS_Generator {

	const OPTION_KEY = 'dr_khasteh_text_styler_css';

	public static function init() {
		add_action( 'update_option_dr_khasteh_highlights', array( __CLASS__, 'regenerate' ) );
		add_action( 'update_option_dr_khasteh_boxes', array( __CLASS__, 'regenerate' ) );
		add_action( 'update_option_dr_khasteh_custom_styles', array( __CLASS__, 'regenerate' ) );
		add_action( 'update_option_dr_khasteh_num_style', array( __CLASS__, 'regenerate' ) );
		add_action( 'update_option_dr_khasteh_custom_symbols', array( __CLASS__, 'regenerate' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_css' ), 15 );
	}

	/**
	 * Build CSS string from current settings.
	 *
	 * @return string
	 */
	public static function build_css() {
		$highlights = get_option( 'dr_khasteh_highlights', Dr_Khasteh_Text_Styler_Defaults::highlights() );
		$boxes      = get_option( 'dr_khasteh_boxes', Dr_Khasteh_Text_Styler_Defaults::boxes() );
		$num_style  = array_merge( Dr_Khasteh_Text_Styler_Defaults::num_style(), get_option( 'dr_khasteh_num_style', array() ) );
		$custom     = Dr_Khasteh_Settings::get_custom_styles();
		$symbols    = get_option( 'dr_khasteh_custom_symbols', Dr_Khasteh_Text_Styler_Defaults::symbols() );

		$css = "/* Dr. Khasteh Text Styler – Dynamic CSS */\n";
		$css .= self::get_tip_box_css();

		// Highlights
		foreach ( $highlights as $h ) {
			$key   = sanitize_html_class( $h['key'] );
			$color = sanitize_hex_color( $h['color'] );
			$dark  = sanitize_hex_color( $h['dark_color'] ?? $color );

			if ( ! $key ) continue;

			if ( $color ) {
				$css .= ".plugin-highlight-{$key} { background-color: {$color}; }\n";
			}
			if ( $dark ) {
				$css .= "body.dark-mode .plugin-highlight-{$key} { background-color: {$dark}; }\n";
			}
		}

		// Boxes
		foreach ( $boxes as $b ) {
			$key    = sanitize_html_class( $b['key'] );
			$bg     = sanitize_hex_color( $b['bg_color'] );
			$border = sanitize_hex_color( $b['border_color'] );
			$dark_bg     = sanitize_hex_color( $b['dark_bg_color'] ?? $bg );
			$dark_border = sanitize_hex_color( $b['dark_border_color'] ?? $border );

			$bw     = absint( $b['border_width'] );
			$br     = absint( $b['border_radius'] );
			$pad    = absint( $b['padding'] );
			$mar    = absint( $b['margin'] );

			if ( ! $key ) continue;

			$css .= ".plugin-box-{$key} {\n";
			if ( $bg ) $css .= "  background-color: {$bg};\n";
			if ( $border && $bw ) $css .= "  border: {$bw}px solid {$border};\n";
			if ( $br )  $css .= "  border-radius: {$br}px;\n";
			if ( $pad ) $css .= "  padding: {$pad}px;\n";
			if ( $mar ) $css .= "  margin: {$mar}px 0;\n";
			$css .= "}\n";

			// Dark mode for boxes
			if ( $dark_bg || $dark_border ) {
				$css .= "body.dark-mode .plugin-box-{$key} {\n";
				if ( $dark_bg ) $css .= "  background-color: {$dark_bg};\n";
				if ( $dark_border && $bw ) $css .= "  border-color: {$dark_border};\n";
				$css .= "}\n";
			}
		}

		// Fantasy Numbers
		$css .= "span.plugin-num-badge {\n";
		$css .= "  display: inline-flex !important;\n";
		$css .= "  align-items: center !important;\n";
		$css .= "  justify-content: center !important;\n";
		$css .= "  unicode-bidi: isolate !important;\n";
		$css .= "  vertical-align: middle !important;\n";
		$css .= "  margin-inline: 4px !important;\n";
		$css .= "  background: transparent !important;\n";
		$css .= "  border: none !important;\n";
		$css .= "  box-shadow: none !important;\n";
		$css .= "  padding: 0 !important;\n";
		$css .= "}\n";

		$css .= "span.plugin-num-badge .plugin-num-inner {\n";
		$css .= "  display: flex !important;\n";
		$css .= "  align-items: center !important;\n";
		$css .= "  justify-content: center !important;\n";
		if ( ! empty( $num_style['badge_size'] ) ) {
			$css .= "  width: {$num_style['badge_size']}px !important;\n";
			$css .= "  height: {$num_style['badge_size']}px !important;\n";
		}
		if ( ! empty( $num_style['font_size'] ) ) {
			$css .= "  font-size: {$num_style['font_size']}px !important;\n";
		}
		$css .= "  font-weight: 800 !important;\n";
		$css .= "  line-height: 1 !important;\n";
		if ( ! empty( $num_style['bg_color'] ) )      $css .= "  background-color: {$num_style['bg_color']} !important;\n";
		if ( ! empty( $num_style['border_color'] ) )  $css .= "  border: 2px solid {$num_style['border_color']} !important;\n";
		if ( ! empty( $num_style['text_color'] ) )    $css .= "  color: {$num_style['text_color']} !important;\n";
		if ( ! empty( $num_style['border_radius'] ) ) $css .= "  border-radius: {$num_style['border_radius']}px !important;\n";
		if ( ! empty( $num_style['glow_color'] ) )    $css .= "  box-shadow: 0 0 10px {$num_style['glow_color']} !important;\n";
		$css .= "  transition: all 0.3s ease !important;\n";
		$css .= "}\n";

		$css .= "body.dark-mode span.plugin-num-badge .plugin-num-inner {\n";
		if ( ! empty( $num_style['dark_bg_color'] ) )     $css .= "  background-color: {$num_style['dark_bg_color']} !important;\n";
		if ( ! empty( $num_style['dark_border_color'] ) ) $css .= "  border-color: {$num_style['dark_border_color']} !important;\n";
		if ( ! empty( $num_style['dark_text_color'] ) )   $css .= "  color: {$num_style['dark_text_color']} !important;\n";
		if ( ! empty( $num_style['dark_glow_color'] ) )   $css .= "  box-shadow: 0 0 12px {$num_style['dark_glow_color']} !important;\n";
		$css .= "}\n";

		// Custom Symbols
		$css .= "[class^='plugin-symbol-'], [class*=' plugin-symbol-'] {\n";
		$css .= "  display: inline-flex !important;\n";
		$css .= "  align-items: center !important;\n";
		$css .= "  justify-content: center !important;\n";
		$css .= "  vertical-align: middle !important;\n";
		$css .= "  unicode-bidi: isolate !important;\n";
		$css .= "  margin-inline: 4px !important;\n";
		$css .= "}\n";

		foreach ( $symbols as $sym ) {
			$key   = sanitize_html_class( $sym['key'] );
			$color = sanitize_hex_color( $sym['color'] );
			$dark  = sanitize_hex_color( $sym['dark_color'] ?? $color );
			$fs    = absint( $sym['font_size'] ?? 18 );

			if ( ! $key ) continue;

			$css .= ".plugin-symbol-{$key} {\n";
			if ( $color ) $css .= "  color: {$color};\n";
			if ( $fs )    $css .= "  font-size: {$fs}px;\n";
			$css .= "}\n";

			if ( $dark ) {
				$css .= "body.dark-mode .plugin-symbol-{$key} { color: {$dark}; }\n";
			}
		}

		// Custom Styles
		foreach ( $custom as $s ) {
			$key = sanitize_html_class( $s['key'] );
			if ( ! $key || $key === 'tip_box' ) continue;

			switch ( $key ) {
				case 'accent_h3':
					$color = sanitize_hex_color( $s['color'] );
					$dark  = sanitize_hex_color( $s['dark_color'] );
					$line  = sanitize_hex_color( $s['line_color'] );

					$css .= "h3.plugin-{$key} {\n";
					if ( $color ) $css .= "  color: {$color};\n";
					if ( $line )  $css .= "  border-inline-start: 4px solid {$line};\n";
					$css .= "  padding-inline-start: 15px;\n";
					$css .= "  margin: 20px 0;\n";
					$css .= "}\n";
					if ( $dark ) {
						$css .= "body.dark-mode h3.plugin-{$key} { color: {$dark}; }\n";
					}
					break;

				case 'symbol_bullet':
					$color  = sanitize_hex_color( $s['color'] );
					$dark   = sanitize_hex_color( $s['dark_color'] );
					$symbol = $s['symbol'] ?? '';

					$css .= ".plugin-{$key} {\n";
					$css .= "  display: block;\n";
					$css .= "  position: relative;\n";
					$css .= "  padding-inline-start: 25px;\n";
					$css .= "  margin: 10px 0;\n";
					$css .= "}\n";
					$css .= ".plugin-{$key}::before {\n";
					$css .= "  content: '{$symbol}';\n";
					$css .= "  position: absolute;\n";
					$css .= "  inset-inline-start: 0;\n";
					if ( $color ) $css .= "  color: {$color};\n";
					$css .= "}\n";
					if ( $dark ) {
						$css .= "body.dark-mode .plugin-{$key}::before { color: {$dark}; }\n";
					}
					break;

				case 'glow_text':
					$color = sanitize_hex_color( $s['color'] );
					$dark  = sanitize_hex_color( $s['dark_color'] );
					$glow  = sanitize_hex_color( $s['glow_color'] );
					$dglow = sanitize_hex_color( $s['dark_glow_color'] );
					$fs    = absint( $s['font_size'] ?? 0 );

					$css .= ".plugin-{$key} {\n";
					if ( $color ) $css .= "  color: {$color};\n";
					if ( $fs )    $css .= "  font-size: {$fs}px;\n";
					if ( $glow )  $css .= "  text-shadow: 0 0 8px {$glow};\n";
					$css .= "}\n";
					if ( $dark || $dglow ) {
						$css .= "body.dark-mode .plugin-{$key} {\n";
						if ( $dark )  $css .= "  color: {$dark};\n";
						if ( $dglow ) $css .= "  text-shadow: 0 0 8px {$dglow};\n";
						$css .= "}\n";
					}
					break;

				case 'corner_border':
					$bc    = sanitize_hex_color( $s['border_color'] );
					$dbc   = sanitize_hex_color( $s['dark_border_color'] );
					$bg    = sanitize_hex_color( $s['bg_color'] );
					$dbg   = sanitize_hex_color( $s['dark_bg_color'] );

					$css .= ".plugin-{$key} {\n";
					if ( $bg ) $css .= "  background-color: {$bg};\n";
					if ( $bc ) $css .= "  border: 2px solid {$bc};\n";
					$css .= "  border-radius: 0 15px 0 15px;\n";
					$css .= "  padding: 15px;\n";
					$css .= "  margin: 15px 0;\n";
					$css .= "}\n";
					if ( $dbg || $dbc ) {
						$css .= "body.dark-mode .plugin-{$key} {\n";
						if ( $dbg ) $css .= "  background-color: {$dbg};\n";
						if ( $dbc ) $css .= "  border-color: {$dbc};\n";
						$css .= "}\n";
					}
					break;


				default:
					$color  = sanitize_hex_color( $s['color'] );
					$dark   = sanitize_hex_color( $s['dark_color'] );
					$bg     = sanitize_hex_color( $s['bg_color'] );
					$dbg    = sanitize_hex_color( $s['dark_bg_color'] );
					$symbol = $s['symbol'] ?? '';
					$fs     = absint( $s['font_size'] ?? 0 );

					$css .= ".plugin-{$key} {\n";
					if ( $bg )    $css .= "  background-color: {$bg};\n";
					if ( $color ) $css .= "  color: {$color};\n";
					if ( $fs )    $css .= "  font-size: {$fs}px;\n";
					$css .= "}\n";

					if ( ! empty( $symbol ) ) {
						$css .= ".plugin-{$key}::before {\n";
						$css .= "  content: '{$symbol}';\n";
						$css .= "  font-size: 1.2em;\n";
						$css .= "}\n";
					}

					if ( $dbg || $dark ) {
						$css .= "body.dark-mode .plugin-{$key} {\n";
						if ( $dbg )  $css .= "  background-color: {$dbg};\n";
						if ( $dark ) $css .= "  color: {$dark};\n";
						$css .= "}\n";
					}
					break;
			}
		}

		return $css;
	}

	public static function get_tip_box_css() {
		$css = ".plugin-tip_box {\n";
		$css .= "  --round: 0.75rem;\n";
		$css .= "  cursor: pointer;\n";
		$css .= "  position: relative;\n";
		$css .= "  display: inline-flex !important;\n";
		$css .= "  align-items: center;\n";
		$css .= "  justify-content: center;\n";
		$css .= "  overflow: hidden;\n";
		$css .= "  transition: all 0.25s ease;\n";
		$css .= "  background: radial-gradient(65.28% 65.28% at 50% 100%, rgba(223, 113, 255, 0.8) 0%, rgba(223, 113, 255, 0) 100%), linear-gradient(0deg, #7a5af8, #7a5af8);\n";
		$css .= "  border-radius: var(--round);\n";
		$css .= "  border: none;\n";
		$css .= "  outline: none;\n";
		$css .= "  padding: 12px 18px;\n";
		$css .= "  margin: 10px 0;\n";
		$css .= "  font-weight: 500;\n";
		$css .= "  font-size: 16px;\n";
		$css .= "  color: white !important;\n";
		$css .= "  line-height: 1.5;\n";
		$css .= "  text-decoration: none;\n";
		$css .= "  vertical-align: middle !important;\n";
		$css .= "  unicode-bidi: isolate !important;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box::before, .plugin-tip_box::after {\n";
		$css .= "  content: '';\n";
		$css .= "  position: absolute;\n";
		$css .= "  transition: all 0.5s ease-in-out;\n";
		$css .= "  z-index: 0;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box::before {\n";
		$css .= "  inset: 1px;\n";
		$css .= "  border-radius: calc(var(--round) - 1px);\n";
		$css .= "  background: linear-gradient(177.95deg, rgba(255, 255, 255, 0.19) 0%, rgba(255, 255, 255, 0) 100%);\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box::after {\n";
		$css .= "  inset: 2px;\n";
		$css .= "  border-radius: calc(var(--round) - 2px);\n";
		$css .= "  background: radial-gradient(65.28% 65.28% at 50% 100%, rgba(223, 113, 255, 0.8) 0%, rgba(223, 113, 255, 0) 100%), linear-gradient(0deg, #7a5af8, #7a5af8);\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box .fold {\n";
		$css .= "  z-index: 1;\n";
		$css .= "  position: absolute;\n";
		$css .= "  top: 0;\n";
		$css .= "  right: 0;\n";
		$css .= "  height: 1rem;\n";
		$css .= "  width: 1rem;\n";
		$css .= "  display: inline-block;\n";
		$css .= "  transition: all 0.5s ease-in-out;\n";
		$css .= "  background: radial-gradient(100% 75% at 55%, rgba(223, 113, 255, 0.8) 0%, rgba(223, 113, 255, 0) 100%);\n";
		$css .= "  box-shadow: 0 0 3px black;\n";
		$css .= "  border-bottom-left-radius: 0.5rem;\n";
		$css .= "  border-top-right-radius: var(--round);\n";
		$css .= "  font-size: 0;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box .fold::after {\n";
		$css .= "  content: '';\n";
		$css .= "  position: absolute;\n";
		$css .= "  top: 0;\n";
		$css .= "  right: 0;\n";
		$css .= "  width: 150%;\n";
		$css .= "  height: 150%;\n";
		$css .= "  transform: rotate(45deg) translateX(0%) translateY(-18px);\n";
		$css .= "  background-color: #e8e8e8;\n";
		$css .= "  pointer-events: none;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box .points_wrapper {\n";
		$css .= "  overflow: hidden;\n";
		$css .= "  width: 100%;\n";
		$css .= "  height: 100%;\n";
		$css .= "  pointer-events: none;\n";
		$css .= "  position: absolute;\n";
		$css .= "  inset: 0;\n";
		$css .= "  z-index: 1;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box .point {\n";
		$css .= "  bottom: -10px;\n";
		$css .= "  position: absolute;\n";
		$css .= "  animation: floating-points-tip infinite ease-in-out;\n";
		$css .= "  pointer-events: none;\n";
		$css .= "  width: 2px;\n";
		$css .= "  height: 2px;\n";
		$css .= "  background-color: #fff;\n";
		$css .= "  border-radius: 9999px;\n";
		$css .= "  font-size: 0;\n";
		$css .= "}\n";

		$css .= "@keyframes floating-points-tip {\n";
		$css .= "  0% { transform: translateY(0); }\n";
		$css .= "  85% { opacity: 0; }\n";
		$css .= "  100% { transform: translateY(-55px); opacity: 0; }\n";
		$css .= "}\n";

		$delays = [0.2, 0.5, 0.1, 0, 0, 1.5, 0.2, 0.2, 0.1, 0.2];
		$durs   = [2.35, 2.5, 2.2, 2.05, 1.9, 1.5, 2.2, 2.25, 2.6, 2.5];
		$lefts  = [10, 30, 25, 44, 50, 75, 88, 58, 98, 65];

		for ($i = 0; $i < 10; $i++) {
			$idx = $i + 1;
			$css .= ".plugin-tip_box .point:nth-child({$idx}) { left: {$lefts[$i]}%; animation-duration: {$durs[$i]}s; animation-delay: {$delays[$i]}s; }\n";
		}

		$css .= ".plugin-tip_box .inner {\n";
		$css .= "  z-index: 2;\n";
		$css .= "  gap: 6px;\n";
		$css .= "  position: relative;\n";
		$css .= "  display: inline-flex;\n";
		$css .= "  align-items: center;\n";
		$css .= "  justify-content: center;\n";
		$css .= "  line-height: 1.5;\n";
		$css .= "  color: white !important;\n";
		$css .= "}\n";

		$css .= ".plugin-tip_box .inner::before {\n";
		$css .= "  content: '';\n";
		$css .= "  width: 20px;\n";
		$css .= "  height: 20px;\n";
		$css .= "  display: inline-block;\n";
		$css .= "  vertical-align: middle;\n";
		$css .= "  margin-left: 6px;\n";
		$css .= "  background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='13.18 1.37 13.18 9.64 21.45 9.64 10.82 22.63 10.82 14.36 2.55 14.36 13.18 1.37'%3E%3C/polyline%3E%3C/svg%3E\");\n";
		$css .= "  background-repeat: no-repeat;\n";
		$css .= "  background-position: center;\n";
		$css .= "  background-size: contain;\n";
		$css .= "}\n";

		return $css;
	}

	/**
	 * Regenerate and cache CSS.
	 */
	public static function regenerate() {
		update_option( self::OPTION_KEY, self::build_css() );
	}

	public static function get_css() {
		$css = get_option( self::OPTION_KEY, '' );
		// Force refresh if the new Tip Box style, pseudo-element icon, or RTL stability rules are missing
		if ( empty( $css ) || strpos( $css, 'inner::before' ) === false || strpos( $css, 'unicode-bidi' ) === false ) {
			$css = self::build_css();
			update_option( self::OPTION_KEY, $css );
		}
		return $css;
	}

	/**
	 * Enqueue generated CSS.
	 */
	public static function enqueue_css() {
		wp_add_inline_style( 'dr-khasteh-text-styler', self::get_css() );
	}
}
