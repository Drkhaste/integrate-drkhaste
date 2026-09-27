<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Leitner {

	private static $table_name;

	public static function init() {
		global $wpdb;
		self::$table_name = $wpdb->prefix . 'sl_user_progress';

		add_action( 'wp_ajax_mcp_leitner_record_progress', array( __CLASS__, 'ajax_record_progress' ) );
		add_action( 'wp_ajax_dr_khaste_leitner_record_progress', array( __CLASS__, 'ajax_record_progress' ) );
	}

	public static function create_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'sl_user_progress';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			card_id bigint(20) NOT NULL,
			box_id bigint(20) NOT NULL,
			next_review datetime DEFAULT NULL,
			last_status tinyint(1) DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY user_card (user_id, card_id),
			KEY box_user_time (box_id, user_id, next_review)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function get_topic_cards_categorized( $user_id, $topic_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'sl_user_progress';

		$all_cards = get_posts( array(
			'post_type'   => 'flashcard',
			'meta_key'    => '_mcp_topic_id',
			'meta_value'  => absint( $topic_id ),
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
		) );

		$progress_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT card_id, next_review FROM $table WHERE user_id = %d AND box_id = %d",
				absint( $user_id ),
				absint( $topic_id )
			),
			OBJECT_K
		);

		$current_time = current_time( 'mysql' );
		$time_24h     = date( 'Y-m-d H:i:s', strtotime( '+1 day', strtotime( $current_time ) ) );

		$categories = array(
			'ready' => array(),
			'h24'   => array(),
			'd3'    => array(),
		);

		foreach ( $all_cards as $card ) {
			$cid         = $card->ID;
			$card_meta   = isset( $progress_rows[ $cid ] ) ? $progress_rows[ $cid ] : null;
			$next_review = $card_meta ? $card_meta->next_review : null;

			if ( ! $card_meta || ! $next_review || $next_review <= $current_time ) {
				$categories['ready'][] = $card;
			} elseif ( $next_review <= $time_24h ) {
				$categories['h24'][] = $card;
			} else {
				$categories['d3'][] = $card;
			}
		}

		return $categories;
	}

	public static function update_progress( $user_id, $card_id, $topic_id, $status ) {
		global $wpdb;
		$table = $wpdb->prefix . 'sl_user_progress';

		$current_time = current_time( 'mysql' );
		$next_review  = null;

		if ( 1 == $status ) { // Fail
			$next_review = $current_time;
		} elseif ( 2 == $status ) { // Doubt
			$next_review = date( 'Y-m-d H:i:s', strtotime( '+1 day', strtotime( $current_time ) ) );
		} elseif ( 3 == $status ) { // Pass
			$next_review = date( 'Y-m-d H:i:s', strtotime( '+3 days', strtotime( $current_time ) ) );
		}

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE user_id = %d AND card_id = %d",
			absint( $user_id ),
			absint( $card_id )
		) );

		if ( $exists ) {
			return $wpdb->update(
				$table,
				array(
					'next_review' => $next_review,
					'last_status' => absint( $status ),
					'box_id'      => absint( $topic_id ),
				),
				array( 'id' => absint( $exists ) ),
				array( '%s', '%d', '%d' ),
				array( '%d' )
			);
		} else {
			return $wpdb->insert(
				$table,
				array(
					'user_id'     => absint( $user_id ),
					'card_id'     => absint( $card_id ),
					'box_id'      => absint( $topic_id ),
					'next_review' => $next_review,
					'last_status' => absint( $status ),
				),
				array( '%d', '%d', '%d', '%s', '%d' )
			);
		}
	}

	public static function ajax_record_progress() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'User not logged in' ) );
		}
		check_ajax_referer( 'mcp_leitner_nonce', 'nonce' );

		$user_id  = get_current_user_id();
		$card_id  = isset( $_POST['card_id'] ) ? absint( $_POST['card_id'] ) : 0;
		$topic_id = isset( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;
		$status   = isset( $_POST['status'] ) ? absint( $_POST['status'] ) : 0;

		if ( ! $user_id || ! $card_id || ! $topic_id || ! $status ) {
			wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
		}

		self::update_progress( $user_id, $card_id, $topic_id, $status );
		wp_send_json_success();
	}

	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied.' );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'sl_user_progress';
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		?>
		<div class="wrap">
			<h1>مدیریت جعبه لایتنر (Leitner System)</h1>
			<p>سیستم الگوریتمی مرور فاصله‌دار (Spaced Repetition) با موفقیت فعال است.</p>

			<div class="card" style="max-width: 600px; margin-top: 15px;">
				<h3>آمار لایتنر</h3>
				<p>تعداد کل رکوردهای پیشرفت کاربران در دیتابیس: <strong><?php echo esc_html( $count ); ?></strong></p>
			</div>
		</div>
		<?php
	}
}

Dr_Khaste_Leitner::init();
