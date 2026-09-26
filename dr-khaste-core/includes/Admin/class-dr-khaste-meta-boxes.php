<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Meta_Boxes {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
	}

	public static function register_meta_boxes() {
		// Parent selects
		add_meta_box( 'dr_khaste_lesson_course', 'کورس مربوطه', array( __CLASS__, 'render_lesson_course_box' ), 'lesson', 'side' );
		add_meta_box( 'dr_khaste_topic_course', 'کورس مربوطه', array( __CLASS__, 'render_topic_course_box' ), 'topic', 'side' );
		add_meta_box( 'dr_khaste_topic_lesson', 'درس مربوطه', array( __CLASS__, 'render_topic_lesson_box' ), 'topic', 'side' );

		add_meta_box( 'dr_khaste_test_topic', 'مبحث مربوطه', array( __CLASS__, 'render_test_topic_box' ), 'test', 'side' );
		add_meta_box( 'dr_khaste_flashcard_topic', 'مبحث مربوطه', array( __CLASS__, 'render_flashcard_topic_box' ), 'flashcard', 'side' );
		add_meta_box( 'dr_khaste_mindmap_topic', 'مبحث مربوطه', array( __CLASS__, 'render_mindmap_topic_box' ), 'mms_mind_map', 'side' );

		// English slug meta box
		foreach ( array( 'course', 'lesson', 'topic' ) as $pt ) {
			add_meta_box( 'dr_khaste_english_slug', 'نامک انگلیسی (English Slug)', array( __CLASS__, 'render_english_slug_box' ), $pt, 'side' );
		}

		// Topic sections
		add_meta_box( 'dr_khaste_topic_sections', 'سرفصل‌های محتوایی (Sections)', array( __CLASS__, 'render_topic_sections_box' ), 'topic', 'normal' );

		// Test Details
		add_meta_box( 'dr_khaste_test_details', 'جزئیات تست (Question Details)', array( __CLASS__, 'render_test_details_box' ), 'test', 'normal' );

		// Flashcard details
		add_meta_box( 'dr_khaste_flashcard_details', 'جزئیات فلش‌کارت', array( __CLASS__, 'render_flashcard_details_box' ), 'flashcard', 'normal' );
	}

	public static function render_english_slug_box( $post ) {
		$slug = get_post_meta( $post->ID, '_mcp_english_slug', true );
		if ( empty( $slug ) ) {
			$slug = get_post_meta( $post->ID, '_mtp_english_slug', true );
		}
		wp_nonce_field( 'dr_khaste_save_meta', 'dr_khaste_meta_nonce' );
		?>
		<input type="text" name="dr_khaste_english_slug" value="<?php echo esc_attr( $slug ); ?>" class="widefat">
		<p class="howto">حروف کوچک انگلیسی و خط تیره</p>
		<?php
	}

	public static function render_lesson_course_box( $post ) {
		$course_id = get_post_meta( $post->ID, '_mcp_course_id', true );
		$courses   = get_posts( array( 'post_type' => 'course', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_course_id" class="widefat">
			<option value="">— انتخاب —</option>
			<?php foreach ( $courses as $c ) : ?>
				<option value="<?php echo $c->ID; ?>" <?php selected( $course_id, $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_topic_course_box( $post ) {
		$course_id = get_post_meta( $post->ID, '_mcp_course_id', true );
		$courses   = get_posts( array( 'post_type' => 'course', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_course_id" class="widefat">
			<option value="">— انتخاب —</option>
			<?php foreach ( $courses as $c ) : ?>
				<option value="<?php echo $c->ID; ?>" <?php selected( $course_id, $c->ID ); ?>><?php echo esc_html( $c->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_topic_lesson_box( $post ) {
		$lesson_id = get_post_meta( $post->ID, '_mcp_lesson_id', true );
		$lessons   = get_posts( array( 'post_type' => 'lesson', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_lesson_id" class="widefat">
			<option value="">— انتخاب —</option>
			<?php foreach ( $lessons as $l ) : ?>
				<option value="<?php echo $l->ID; ?>" <?php selected( $lesson_id, $l->ID ); ?>><?php echo esc_html( $l->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_test_topic_box( $post ) {
		$topic_id = get_post_meta( $post->ID, '_mtp_topic_id', true );
		if ( empty( $topic_id ) ) {
			$topic_id = get_post_meta( $post->ID, '_mcp_topic_id', true );
		}
		$topics = get_posts( array( 'post_type' => 'topic', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_topic_id" class="widefat">
			<option value="">— انتخاب مبحث مرکزی —</option>
			<?php foreach ( $topics as $t ) : ?>
				<option value="<?php echo $t->ID; ?>" <?php selected( $topic_id, $t->ID ); ?>><?php echo esc_html( $t->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_flashcard_topic_box( $post ) {
		$topic_id = get_post_meta( $post->ID, '_mcp_topic_id', true );
		$topics   = get_posts( array( 'post_type' => 'topic', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_topic_id" class="widefat">
			<option value="">— انتخاب مبحث مرکزی —</option>
			<?php foreach ( $topics as $t ) : ?>
				<option value="<?php echo $t->ID; ?>" <?php selected( $topic_id, $t->ID ); ?>><?php echo esc_html( $t->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_mindmap_topic_box( $post ) {
		$topic_id = get_post_meta( $post->ID, '_mms_topic_id', true );
		if ( empty( $topic_id ) ) {
			$topic_id = get_post_meta( $post->ID, '_mcp_topic_id', true );
		}
		$topics = get_posts( array( 'post_type' => 'topic', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<select name="dr_khaste_topic_id" class="widefat">
			<option value="">— انتخاب مبحث مرکزی —</option>
			<?php foreach ( $topics as $t ) : ?>
				<option value="<?php echo $t->ID; ?>" <?php selected( $topic_id, $t->ID ); ?>><?php echo esc_html( $t->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function render_topic_sections_box( $post ) {
		$sections = get_post_meta( $post->ID, '_mcp_sections', true );
		if ( ! is_array( $sections ) ) {
			$sections = array();
		}
		?>
		<div id="dr-khaste-sections-wrapper">
			<p>سرفصل‌های متنی اضافه شده به این مبحث:</p>
			<?php foreach ( $sections as $i => $sec ) : ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
					<label>نوع بخش (Section Type ID):</label>
					<input type="number" name="dr_khaste_sections[<?php echo $i; ?>][section_type]" value="<?php echo esc_attr( isset( $sec['section_type'] ) ? $sec['section_type'] : '' ); ?>">
					<br><br>
					<label>محتوا:</label>
					<?php wp_editor( isset( $sec['content'] ) ? $sec['content'] : '', 'dr_khaste_sec_' . $i, array( 'textarea_name' => "dr_khaste_sections[{$i}][content]" ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public static function render_test_details_box( $post ) {
		$identifier      = get_post_meta( $post->ID, '_mtp_identifier', true );
		$question_number = get_post_meta( $post->ID, '_mtp_question_number', true );
		$exam            = get_post_meta( $post->ID, '_mtp_exam', true );
		$date            = get_post_meta( $post->ID, '_mtp_date', true );
		$question        = get_post_meta( $post->ID, '_mtp_question', true );
		$option1         = get_post_meta( $post->ID, '_mtp_option1', true );
		$option2         = get_post_meta( $post->ID, '_mtp_option2', true );
		$option3         = get_post_meta( $post->ID, '_mtp_option3', true );
		$option4         = get_post_meta( $post->ID, '_mtp_option4', true );
		$correct_option  = get_post_meta( $post->ID, '_mtp_correct_option', true );
		$explanation     = get_post_meta( $post->ID, '_mtp_explanation', true );

		$correct_options = array_filter( array_map( 'trim', explode( ',', (string) $correct_option ) ) );
		?>
		<p><label>شناسه (Identifier):</label> <input type="text" name="dr_khaste_test_identifier" value="<?php echo esc_attr( $identifier ); ?>" class="widefat"></p>
		<p><label>شماره سوال:</label> <input type="text" name="dr_khaste_test_question_number" value="<?php echo esc_attr( $question_number ); ?>" class="widefat"></p>
		<p><label>آزمون / منبع:</label> <input type="text" name="dr_khaste_test_exam" value="<?php echo esc_attr( $exam ); ?>" class="widefat"></p>
		<p><label>تاریخ / دوره:</label> <input type="text" name="dr_khaste_test_date" value="<?php echo esc_attr( $date ); ?>" class="widefat"></p>
		<p><label>متن سوال:</label><?php wp_editor( $question, 'dr_khaste_test_question', array( 'textarea_name' => 'dr_khaste_test_question' ) ); ?></p>
		<p><label>گزینه ۱:</label> <input type="text" name="dr_khaste_test_option1" value="<?php echo esc_attr( $option1 ); ?>" class="widefat"></p>
		<p><label>گزینه ۲:</label> <input type="text" name="dr_khaste_test_option2" value="<?php echo esc_attr( $option2 ); ?>" class="widefat"></p>
		<p><label>گزینه ۳:</label> <input type="text" name="dr_khaste_test_option3" value="<?php echo esc_attr( $option3 ); ?>" class="widefat"></p>
		<p><label>گزینه ۴:</label> <input type="text" name="dr_khaste_test_option4" value="<?php echo esc_attr( $option4 ); ?>" class="widefat"></p>
		<p><label>گزینه(های) صحیح:</label><br>
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<label style="margin-left: 15px;">
					<input type="checkbox" name="dr_khaste_test_correct_options[]" value="<?php echo $i; ?>" <?php checked( in_array( (string) $i, $correct_options, true ) ); ?>> گزینه <?php echo $i; ?>
				</label>
			<?php endfor; ?>
		</p>
		<p><label>پاسخ تشریحی:</label><?php wp_editor( $explanation, 'dr_khaste_test_explanation', array( 'textarea_name' => 'dr_khaste_test_explanation' ) ); ?></p>
		<?php
	}

	public static function render_flashcard_details_box( $post ) {
		$question = get_post_meta( $post->ID, '_mcp_question', true );
		$answer   = get_post_meta( $post->ID, '_mcp_answer', true );
		?>
		<p><label>صورت سوال / روی کارت:</label></p>
		<textarea name="dr_khaste_flashcard_question" class="widefat" rows="4"><?php echo esc_textarea( $question ); ?></textarea>
		<p><label>پاسخ / پشت کارت:</label></p>
		<textarea name="dr_khaste_flashcard_answer" class="widefat" rows="4"><?php echo esc_textarea( $answer ); ?></textarea>
		<?php
	}

	public static function save_meta_boxes( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['dr_khaste_meta_nonce'] ) && ! wp_verify_nonce( $_POST['dr_khaste_meta_nonce'], 'dr_khaste_save_meta' ) ) {
			return;
		}

		// Save English Slug
		if ( isset( $_POST['dr_khaste_english_slug'] ) ) {
			$slug = sanitize_title( $_POST['dr_khaste_english_slug'] );
			update_post_meta( $post_id, '_mcp_english_slug', $slug );
			update_post_meta( $post_id, '_mtp_english_slug', $slug );
		}

		// Save Course ID
		if ( isset( $_POST['dr_khaste_course_id'] ) ) {
			$cid = absint( $_POST['dr_khaste_course_id'] );
			update_post_meta( $post_id, '_mcp_course_id', $cid );
			update_post_meta( $post_id, '_mtp_course_id', $cid );
		}

		// Save Lesson ID
		if ( isset( $_POST['dr_khaste_lesson_id'] ) ) {
			$lid = absint( $_POST['dr_khaste_lesson_id'] );
			update_post_meta( $post_id, '_mcp_lesson_id', $lid );
			update_post_meta( $post_id, '_mtp_lesson_id', $lid );
		}

		// Save Topic ID
		if ( isset( $_POST['dr_khaste_topic_id'] ) ) {
			$tid = absint( $_POST['dr_khaste_topic_id'] );
			update_post_meta( $post_id, '_mcp_topic_id', $tid );
			update_post_meta( $post_id, '_mtp_topic_id', $tid );
			update_post_meta( $post_id, '_mms_topic_id', $tid );
		}

		// Save Topic Sections
		if ( isset( $_POST['dr_khaste_sections'] ) && is_array( $_POST['dr_khaste_sections'] ) ) {
			$sections = array();
			foreach ( $_POST['dr_khaste_sections'] as $sec ) {
				if ( ! empty( $sec['section_type'] ) ) {
					$sections[] = array(
						'section_type' => absint( $sec['section_type'] ),
						'content'      => wp_kses_post( $sec['content'] ),
					);
				}
			}
			update_post_meta( $post_id, '_mcp_sections', $sections );
		}

		// Save Flashcard details
		if ( isset( $_POST['dr_khaste_flashcard_question'] ) ) {
			update_post_meta( $post_id, '_mcp_question', sanitize_textarea_field( $_POST['dr_khaste_flashcard_question'] ) );
		}
		if ( isset( $_POST['dr_khaste_flashcard_answer'] ) ) {
			update_post_meta( $post_id, '_mcp_answer', sanitize_textarea_field( $_POST['dr_khaste_flashcard_answer'] ) );
		}

		// Save Test details
		if ( get_post_type( $post_id ) === 'test' || get_post_type( $post_id ) === 'mtp_test' ) {
			if ( isset( $_POST['dr_khaste_test_identifier'] ) ) {
				update_post_meta( $post_id, '_mtp_identifier', sanitize_text_field( $_POST['dr_khaste_test_identifier'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_question_number'] ) ) {
				update_post_meta( $post_id, '_mtp_question_number', sanitize_text_field( $_POST['dr_khaste_test_question_number'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_exam'] ) ) {
				update_post_meta( $post_id, '_mtp_exam', sanitize_text_field( $_POST['dr_khaste_test_exam'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_date'] ) ) {
				update_post_meta( $post_id, '_mtp_date', sanitize_text_field( $_POST['dr_khaste_test_date'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_question'] ) ) {
				update_post_meta( $post_id, '_mtp_question', wp_kses_post( $_POST['dr_khaste_test_question'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_option1'] ) ) {
				update_post_meta( $post_id, '_mtp_option1', sanitize_text_field( $_POST['dr_khaste_test_option1'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_option2'] ) ) {
				update_post_meta( $post_id, '_mtp_option2', sanitize_text_field( $_POST['dr_khaste_test_option2'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_option3'] ) ) {
				update_post_meta( $post_id, '_mtp_option3', sanitize_text_field( $_POST['dr_khaste_test_option3'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_option4'] ) ) {
				update_post_meta( $post_id, '_mtp_option4', sanitize_text_field( $_POST['dr_khaste_test_option4'] ) );
			}
			if ( isset( $_POST['dr_khaste_test_explanation'] ) ) {
				update_post_meta( $post_id, '_mtp_explanation', wp_kses_post( $_POST['dr_khaste_test_explanation'] ) );
			}

			$correct_val = '';
			if ( isset( $_POST['dr_khaste_test_correct_options'] ) && is_array( $_POST['dr_khaste_test_correct_options'] ) ) {
				$correct_val = implode( ',', array_map( 'absint', $_POST['dr_khaste_test_correct_options'] ) );
			}
			update_post_meta( $post_id, '_mtp_correct_option', $correct_val );
		}
	}
}

Dr_Khaste_Meta_Boxes::init();
