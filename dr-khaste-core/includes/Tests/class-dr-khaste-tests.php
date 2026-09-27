<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Tests {

	public static function init() {
		add_shortcode( 'topic_tests', array( __CLASS__, 'render_topic_tests_shortcode' ) );
		add_action( 'wp_ajax_dr_khaste_add_test_inline', array( __CLASS__, 'ajax_add_test_inline' ) );
	}

	public static function render_topic_tests_shortcode( $atts ) {
		$atts     = shortcode_atts( array( 'topic_id' => 0 ), $atts );
		$topic_id = absint( $atts['topic_id'] );
		if ( ! $topic_id ) {
			$topic_id = get_the_ID();
		}

		$tests = Dr_Khaste_Topic_Repository::get_tests( $topic_id );
		if ( empty( $tests ) ) {
			return '<div class="dr-khaste-tests-empty"><p>هیچ تستی برای این مبحث ثبت نشده است.</p></div>';
		}

		ob_start();
		?>
		<div class="dr-khaste-topic-tests-wrapper">
			<h3>تست‌های مبحث (<?php echo esc_html( count( $tests ) ); ?> سوال)</h3>
			<?php foreach ( $tests as $index => $test ) :
				$q_num       = get_post_meta( $test->ID, '_mtp_question_number', true );
				$exam        = get_post_meta( $test->ID, '_mtp_exam', true );
				$date        = get_post_meta( $test->ID, '_mtp_date', true );
				$question    = get_post_meta( $test->ID, '_mtp_question', true );
				$opt1        = get_post_meta( $test->ID, '_mtp_option1', true );
				$opt2        = get_post_meta( $test->ID, '_mtp_option2', true );
				$opt3        = get_post_meta( $test->ID, '_mtp_option3', true );
				$opt4        = get_post_meta( $test->ID, '_mtp_option4', true );
				$correct_val = get_post_meta( $test->ID, '_mtp_correct_option', true );
				$corrects    = array_filter( array_map( 'trim', explode( ',', (string) $correct_val ) ) );
				$explanation = get_post_meta( $test->ID, '_mtp_explanation', true );
				?>
				<div class="dr-khaste-test-card" id="test-<?php echo $test->ID; ?>" style="border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
					<div class="test-header" style="font-weight: bold; margin-bottom: 10px;">
						<span class="q-num">سوال <?php echo esc_html( $q_num ? $q_num : ($index + 1) ); ?>:</span>
						<?php if ( $exam || $date ) : ?>
							<span class="exam-tag" style="float: left; font-size: 12px; background: #f0f4f8; padding: 3px 8px; border-radius: 4px;">
								<?php echo esc_html( trim( "$exam $date" ) ); ?>
							</span>
						<?php endif; ?>
					</div>
					<div class="test-question" style="margin-bottom: 15px;">
						<?php echo wp_kses_post( $question ); ?>
					</div>
					<div class="test-options" style="margin-bottom: 15px;">
						<?php for ( $i = 1; $i <= 4; $i++ ) :
							$opt_text = ${"opt$i"};
							if ( empty( $opt_text ) ) continue;
							$is_correct = in_array( (string) $i, $corrects, true );
							?>
							<div class="test-option <?php echo $is_correct ? 'correct-option' : ''; ?>" style="padding: 8px 12px; margin-bottom: 5px; background: #fafafa; border: 1px solid #eee; border-radius: 4px;">
								<strong><?php echo $i; ?>)</strong> <?php echo esc_html( $opt_text ); ?>
							</div>
						<?php endfor; ?>
					</div>
					<?php if ( ! empty( $explanation ) ) : ?>
						<details class="test-explanation" style="margin-top: 10px; background: #f9fbfd; padding: 10px; border-radius: 4px;">
							<summary style="cursor: pointer; font-weight: bold; color: #2271b1;">نمایش پاسخ تشریحی</summary>
							<div style="margin-top: 10px; font-size: 14px;">
								<?php echo wp_kses_post( $explanation ); ?>
							</div>
						</details>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function ajax_add_test_inline() {
		check_ajax_referer( 'dr_khaste_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$topic_id = isset( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;
		$question = isset( $_POST['question'] ) ? wp_kses_post( $_POST['question'] ) : '';

		if ( ! $topic_id || empty( $question ) ) {
			wp_send_json_error( array( 'message' => 'ورودی معتبر نیست' ) );
		}

		$test_id = wp_insert_post( array(
			'post_title'  => wp_trim_words( $question, 10, '...' ),
			'post_type'   => 'test',
			'post_status' => 'publish',
		) );

		if ( $test_id && ! is_wp_error( $test_id ) ) {
			update_post_meta( $test_id, '_mtp_topic_id', $topic_id );
			update_post_meta( $test_id, '_mcp_topic_id', $topic_id );
			update_post_meta( $test_id, '_mtp_question', $question );
			wp_send_json_success( array( 'test_id' => $test_id ) );
		}

		wp_send_json_error( array( 'message' => 'خطا در ثبت تست' ) );
	}
}

Dr_Khaste_Tests::init();
