<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_MindMaps {

	public static function init() {
		add_shortcode( 'mindmap', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_ajax_get_mindmap_list', array( __CLASS__, 'ajax_get_mindmap_list' ) );
	}

	public static function get_frontend_settings() {
		return array(
			'watermark'     => get_option( 'mms_watermark', 'دکتر خسته' ),
			'theme_light'   => get_option( 'mms_theme_light', 'default' ),
			'line_color'    => get_option( 'mms_line_color', '#333333' ),
			'line_style'    => get_option( 'mms_line_style', 'curved' ),
			'line_width'    => get_option( 'mms_line_width', '2' ),
			'border_radius' => get_option( 'mms_border_radius', '5' ),
		);
	}

	public static function render_shortcode( $atts ) {
		$atts    = shortcode_atts( array( 'id' => 0 ), $atts );
		$post_id = intval( $atts['id'] );
		if ( ! $post_id ) {
			return '';
		}

		$data        = get_post_meta( $post_id, '_mind_map_data', true );
		$layout      = get_post_meta( $post_id, '_mind_map_layout', true ) ?: 'both';
		$node_styles = get_post_meta( $post_id, '_mind_map_node_styles', true ) ?: '{}';
		if ( ! $data ) {
			return '';
		}

		$s         = self::get_frontend_settings();
		$unique_id = 'mms_' . $post_id . '_' . wp_unique_id();

		ob_start();
		?>
		<div class="mindmap-studio-wrapper" style="width:100%;margin:20px 0;">
			<div class="mindmap-studio-capture" id="capture_<?php echo esc_attr( $unique_id ); ?>" style="width:100%;background:transparent;position:relative;">
				<div
					id="<?php echo esc_attr( $unique_id ); ?>"
					class="mindmap-studio-container"
					data-mindmap-data="<?php echo esc_attr( $data ); ?>"
					data-mindmap-layout="<?php echo esc_attr( $layout ); ?>"
					data-node-styles="<?php echo esc_attr( $node_styles ); ?>"
					data-line-color="<?php echo esc_attr( $s['line_color'] ); ?>"
					data-line-style="<?php echo esc_attr( $s['line_style'] ); ?>"
					data-line-width="<?php echo esc_attr( $s['line_width'] ); ?>">
				</div>
			</div>
		</div>
		<style>
			#<?php echo esc_attr( $unique_id ); ?> jmnode { font-family: inherit !important; border-radius: <?php echo (int) $s['border_radius']; ?>px !important; }
			#<?php echo esc_attr( $unique_id ); ?> { direction: ltr !important; overflow: hidden !important; }
			#<?php echo esc_attr( $unique_id ); ?> jmexpander { display: none !important; }
		</style>
		<?php
		return ob_get_clean();
	}

	public static function ajax_get_mindmap_list() {
		check_ajax_referer( 'mind_map_tinymce', 'security' );
		$query = new WP_Query( array(
			'post_type'      => 'mms_mind_map',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$list = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$list[] = array(
					'id'    => get_the_ID(),
					'title' => get_the_title(),
				);
			}
			wp_reset_postdata();
		}
		wp_send_json_success( $list );
	}
}

Dr_Khaste_MindMaps::init();
