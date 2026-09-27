<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dr_Khaste_Migration {

	private static $option_mapping_key = 'dr_khaste_migration_mapping';
	private static $option_log_key     = 'dr_khaste_migration_log';

	public static function init() {
		add_action( 'wp_ajax_dr_khaste_run_migration', array( __CLASS__, 'ajax_run_migration' ) );
		add_action( 'wp_ajax_dr_khaste_rollback_migration', array( __CLASS__, 'ajax_rollback_migration' ) );
	}

	public static function get_mapping() {
		return get_option( self::$option_mapping_key, array(
			'courses'  => array(),
			'lessons'  => array(),
			'topics'   => array(),
			'tests'    => array(),
			'mindmaps' => array(),
			'created'  => array(),
		) );
	}

	public static function save_mapping( $mapping ) {
		update_option( self::$option_mapping_key, $mapping );
	}

	public static function ajax_run_migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user.' ) );
		}
		check_ajax_referer( 'dr_khaste_migration_nonce', 'nonce' );

		$report = self::execute_migration();
		wp_send_json_success( $report );
	}

	public static function ajax_rollback_migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user.' ) );
		}
		check_ajax_referer( 'dr_khaste_migration_nonce', 'nonce' );

		self::execute_rollback();
		wp_send_json_success( array( 'message' => 'Rollback complete' ) );
	}

	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied.' );
		}

		$mapping = self::get_mapping();
		$logs    = get_option( self::$option_log_key, array() );

		$mtp_courses  = count( get_posts( array( 'post_type' => 'mtp_course', 'numberposts' => -1 ) ) );
		$mtp_lessons  = count( get_posts( array( 'post_type' => 'mtp_lesson', 'numberposts' => -1 ) ) );
		$mtp_topics   = count( get_posts( array( 'post_type' => 'mtp_topic', 'numberposts' => -1 ) ) );

		$mms_courses  = count( get_posts( array( 'post_type' => 'mms_course', 'numberposts' => -1 ) ) );
		$mms_lessons  = count( get_posts( array( 'post_type' => 'mms_lesson', 'numberposts' => -1 ) ) );
		$mms_topics   = count( get_posts( array( 'post_type' => 'mms_topic', 'numberposts' => -1 ) ) );

		$mtp_tests    = count( get_posts( array( 'post_type' => 'mtp_test', 'numberposts' => -1 ) ) );

		if ( isset( $_POST['dr_khaste_start_migration_btn'] ) && check_admin_referer( 'dr_khaste_migration_nonce' ) ) {
			$report = self::execute_migration();
			echo '<div class="updated"><p><strong>مهاجرت با موفقیت انجام شد!</strong></p>';
			echo '<p>تعداد موارد انتقال‌یافته: ' . intval( $report['migrated'] ) . ' | صرف‌نظر شده (قبلاً وجود داشته): ' . intval( $report['skipped'] ) . ' | خطاها: ' . intval( $report['failed'] ) . '</p></div>';
			$mapping = self::get_mapping();
			$logs    = get_option( self::$option_log_key, array() );
		}

		if ( isset( $_POST['dr_khaste_rollback_btn'] ) && check_admin_referer( 'dr_khaste_migration_nonce' ) ) {
			self::execute_rollback();
			echo '<div class="notice notice-warning"><p>عملیات بازگردانی (Rollback) انجام گردید و پُست‌های جدید ساخته‌شده حین مهاجرت حذف شدند.</p></div>';
			$mapping = self::get_mapping();
			$logs    = get_option( self::$option_log_key, array() );
		}
		?>
		<div class="wrap">
			<h1>پنل مهاجرت و یکپارچه‌سازی داده‌ها (Dr.Khaste Migration)</h1>
			<p>در این صفحه می‌توانید تمام داده‌های موجود در پلاگین‌های قدیمی (`mtp_*` و `mms_*`) را به مدل مرکزی (`course`, `lesson`, `topic`, `test`, `flashcard`, `mms_mind_map`) منتقل کنید.</p>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>۱. آمار داده‌های شناسایی شده برای مهاجرت</h2>
				<ul>
					<li>کورس‌های قدیمی MTP: <strong><?php echo $mtp_courses; ?></strong> | درس‌ها: <strong><?php echo $mtp_lessons; ?></strong> | مباحث: <strong><?php echo $mtp_topics; ?></strong></li>
					<li>کورس‌های قدیمی MMS: <strong><?php echo $mms_courses; ?></strong> | درس‌ها: <strong><?php echo $mms_lessons; ?></strong> | مباحث: <strong><?php echo $mms_topics; ?></strong></li>
					<li>تست‌های قدیمی MTP: <strong><?php echo $mtp_tests; ?></strong></li>
				</ul>
				<hr>
				<h2>۲. گزارش Migration Mapping (قدیمی → جدید)</h2>
				<ul>
					<li>کورس‌های نگاشت‌شده: <strong><?php echo count( $mapping['courses'] ); ?></strong></li>
					<li>درس‌های نگاشت‌شده: <strong><?php echo count( $mapping['lessons'] ); ?></strong></li>
					<li>مباحث نگاشت‌شده: <strong><?php echo count( $mapping['topics'] ); ?></strong></li>
					<li>تست‌های نگاشت‌شده: <strong><?php echo count( $mapping['tests'] ); ?></strong></li>
				</ul>
				<hr>
				<h2>۳. اجرای عملیات</h2>
				<form method="post" action="">
					<?php wp_nonce_field( 'dr_khaste_migration_nonce' ); ?>
					<p>
						<input type="submit" name="dr_khaste_start_migration_btn" class="button button-primary button-large" value="شروع مهاجرت امن داده‌ها (Start Migration)">
						<input type="submit" name="dr_khaste_rollback_btn" class="button button-secondary button-large" value="بازگردانی داده‌ها (Rollback)" onclick="return confirm('آیا از بازگردانی و حذف پست‌های ساخت‌شده مطمئن هستید؟');">
					</p>
				</form>
			</div>

			<?php if ( ! empty( $logs ) ) : ?>
				<div class="card" style="max-width: 800px; margin-top: 20px;">
					<h3>آخرین گزارش عملیات (Logs)</h3>
					<div style="background:#f4f4f4; padding:10px; max-height:200px; overflow-y:auto; font-family:monospace; font-size:12px;">
						<?php foreach ( array_reverse( $logs ) as $log ) : ?>
							<div><?php echo esc_html( $log ); ?></div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function execute_migration() {
		$mapping  = self::get_mapping();
		if ( ! isset( $mapping['created'] ) || ! is_array( $mapping['created'] ) ) {
			$mapping['created'] = array();
		}

		$logs     = array();
		$migrated = 0;
		$skipped  = 0;
		$failed   = 0;

		$time   = current_time( 'mysql' );
		$logs[] = "[$time] Migration started...";

		// 1. Migrate MTP Courses to Central Course
		$mtp_courses = get_posts( array( 'post_type' => 'mtp_course', 'numberposts' => -1 ) );
		foreach ( $mtp_courses as $old_course ) {
			if ( isset( $mapping['courses'][ $old_course->ID ] ) ) {
				$skipped++;
				continue;
			}
			$slug = get_post_meta( $old_course->ID, '_mtp_english_slug', true );
			$existing = get_posts( array( 'post_type' => 'course', 'title' => $old_course->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$new_id = $existing[0]->ID;
				$mapping['courses'][ $old_course->ID ] = $new_id;
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_course->post_title,
					'post_content' => $old_course->post_content,
					'post_type'    => 'course',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					if ( $slug ) {
						update_post_meta( $new_id, '_mcp_english_slug', $slug );
					}
					$mapping['courses'][ $old_course->ID ] = $new_id;
					$mapping['created'][]                  = $new_id;
					$migrated++;
					$logs[] = "Migrated MTP Course {$old_course->ID} -> New Course {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		// 2. Migrate MMS Courses to Central Course
		$mms_courses = get_posts( array( 'post_type' => 'mms_course', 'numberposts' => -1 ) );
		foreach ( $mms_courses as $old_course ) {
			if ( isset( $mapping['courses'][ $old_course->ID ] ) ) {
				$skipped++;
				continue;
			}
			$existing = get_posts( array( 'post_type' => 'course', 'title' => $old_course->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$mapping['courses'][ $old_course->ID ] = $existing[0]->ID;
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_course->post_title,
					'post_content' => $old_course->post_content,
					'post_type'    => 'course',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					$mapping['courses'][ $old_course->ID ] = $new_id;
					$mapping['created'][]                  = $new_id;
					$migrated++;
					$logs[] = "Migrated MMS Course {$old_course->ID} -> New Course {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		// 3. Migrate MTP Lessons
		$mtp_lessons = get_posts( array( 'post_type' => 'mtp_lesson', 'numberposts' => -1 ) );
		foreach ( $mtp_lessons as $old_lesson ) {
			if ( isset( $mapping['lessons'][ $old_lesson->ID ] ) ) {
				$skipped++;
				continue;
			}
			$old_course_id = get_post_meta( $old_lesson->ID, '_mtp_course_id', true );
			$new_course_id = isset( $mapping['courses'][ $old_course_id ] ) ? $mapping['courses'][ $old_course_id ] : $old_course_id;

			$existing = get_posts( array( 'post_type' => 'lesson', 'title' => $old_lesson->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$new_id = $existing[0]->ID;
				$mapping['lessons'][ $old_lesson->ID ] = $new_id;
				update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_lesson->post_title,
					'post_content' => $old_lesson->post_content,
					'post_type'    => 'lesson',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
					$slug = get_post_meta( $old_lesson->ID, '_mtp_english_slug', true );
					if ( $slug ) {
						update_post_meta( $new_id, '_mcp_english_slug', $slug );
					}
					$mapping['lessons'][ $old_lesson->ID ] = $new_id;
					$mapping['created'][]                  = $new_id;
					$migrated++;
					$logs[] = "Migrated MTP Lesson {$old_lesson->ID} -> New Lesson {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		// 4. Migrate MMS Lessons
		$mms_lessons = get_posts( array( 'post_type' => 'mms_lesson', 'numberposts' => -1 ) );
		foreach ( $mms_lessons as $old_lesson ) {
			if ( isset( $mapping['lessons'][ $old_lesson->ID ] ) ) {
				$skipped++;
				continue;
			}
			$old_course_id = get_post_meta( $old_lesson->ID, '_mms_course_id', true );
			$new_course_id = isset( $mapping['courses'][ $old_course_id ] ) ? $mapping['courses'][ $old_course_id ] : $old_course_id;

			$existing = get_posts( array( 'post_type' => 'lesson', 'title' => $old_lesson->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$new_id = $existing[0]->ID;
				$mapping['lessons'][ $old_lesson->ID ] = $new_id;
				update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_lesson->post_title,
					'post_content' => $old_lesson->post_content,
					'post_type'    => 'lesson',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
					$mapping['lessons'][ $old_lesson->ID ] = $new_id;
					$mapping['created'][]                  = $new_id;
					$migrated++;
					$logs[] = "Migrated MMS Lesson {$old_lesson->ID} -> New Lesson {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		// 5. Migrate MTP Topics & MMS Topics
		$mtp_topics = get_posts( array( 'post_type' => 'mtp_topic', 'numberposts' => -1 ) );
		foreach ( $mtp_topics as $old_topic ) {
			if ( isset( $mapping['topics'][ $old_topic->ID ] ) ) {
				$skipped++;
				continue;
			}
			$old_course_id = get_post_meta( $old_topic->ID, '_mtp_course_id', true );
			$old_lesson_id = get_post_meta( $old_topic->ID, '_mtp_lesson_id', true );

			$new_course_id = isset( $mapping['courses'][ $old_course_id ] ) ? $mapping['courses'][ $old_course_id ] : $old_course_id;
			$new_lesson_id = isset( $mapping['lessons'][ $old_lesson_id ] ) ? $mapping['lessons'][ $old_lesson_id ] : $old_lesson_id;

			$existing = get_posts( array( 'post_type' => 'topic', 'title' => $old_topic->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$new_id = $existing[0]->ID;
				$mapping['topics'][ $old_topic->ID ] = $new_id;
				update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
				update_post_meta( $new_id, '_mcp_lesson_id', $new_lesson_id );
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_topic->post_title,
					'post_content' => $old_topic->post_content,
					'post_type'    => 'topic',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
					update_post_meta( $new_id, '_mcp_lesson_id', $new_lesson_id );
					$slug = get_post_meta( $old_topic->ID, '_mtp_english_slug', true );
					if ( $slug ) {
						update_post_meta( $new_id, '_mcp_english_slug', $slug );
					}
					$mapping['topics'][ $old_topic->ID ] = $new_id;
					$mapping['created'][]                 = $new_id;
					$migrated++;
					$logs[] = "Migrated MTP Topic {$old_topic->ID} -> New Topic {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		$mms_topics = get_posts( array( 'post_type' => 'mms_topic', 'numberposts' => -1 ) );
		foreach ( $mms_topics as $old_topic ) {
			if ( isset( $mapping['topics'][ $old_topic->ID ] ) ) {
				$skipped++;
				continue;
			}
			$old_course_id = get_post_meta( $old_topic->ID, '_mms_course_id', true );
			$old_lesson_id = get_post_meta( $old_topic->ID, '_mms_lesson_id', true );

			$new_course_id = isset( $mapping['courses'][ $old_course_id ] ) ? $mapping['courses'][ $old_course_id ] : $old_course_id;
			$new_lesson_id = isset( $mapping['lessons'][ $old_lesson_id ] ) ? $mapping['lessons'][ $old_lesson_id ] : $old_lesson_id;

			$existing = get_posts( array( 'post_type' => 'topic', 'title' => $old_topic->post_title, 'numberposts' => 1 ) );
			if ( ! empty( $existing ) ) {
				$new_id = $existing[0]->ID;
				$mapping['topics'][ $old_topic->ID ] = $new_id;
				update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
				update_post_meta( $new_id, '_mcp_lesson_id', $new_lesson_id );
				$skipped++;
			} else {
				$new_id = wp_insert_post( array(
					'post_title'   => $old_topic->post_title,
					'post_content' => $old_topic->post_content,
					'post_type'    => 'topic',
					'post_status'  => 'publish',
				) );
				if ( $new_id && ! is_wp_error( $new_id ) ) {
					update_post_meta( $new_id, '_mcp_course_id', $new_course_id );
					update_post_meta( $new_id, '_mcp_lesson_id', $new_lesson_id );
					$mapping['topics'][ $old_topic->ID ] = $new_id;
					$mapping['created'][]                 = $new_id;
					$migrated++;
					$logs[] = "Migrated MMS Topic {$old_topic->ID} -> New Topic {$new_id}";
				} else {
					$failed++;
				}
			}
		}

		// 6. Update Test topic references to new Topic IDs
		$mtp_tests = get_posts( array( 'post_type' => 'mtp_test', 'numberposts' => -1 ) );
		foreach ( $mtp_tests as $test ) {
			$old_topic_id = get_post_meta( $test->ID, '_mtp_topic_id', true );
			if ( $old_topic_id && isset( $mapping['topics'][ $old_topic_id ] ) ) {
				$new_topic_id = $mapping['topics'][ $old_topic_id ];
				update_post_meta( $test->ID, '_mtp_topic_id', $new_topic_id );
				update_post_meta( $test->ID, '_mcp_topic_id', $new_topic_id );
				$logs[] = "Re-linked Test {$test->ID} to New Topic {$new_topic_id}";
			}
		}

		// 7. Update Flashcard topic references to new Topic IDs
		$flashcards = get_posts( array( 'post_type' => 'flashcard', 'numberposts' => -1 ) );
		foreach ( $flashcards as $card ) {
			$old_topic_id = get_post_meta( $card->ID, '_mcp_topic_id', true );
			if ( $old_topic_id && isset( $mapping['topics'][ $old_topic_id ] ) ) {
				$new_topic_id = $mapping['topics'][ $old_topic_id ];
				update_post_meta( $card->ID, '_mcp_topic_id', $new_topic_id );
				$logs[] = "Re-linked Flashcard {$card->ID} to New Topic {$new_topic_id}";
			}
		}

		// 8. Update MindMap topic references to new Topic IDs
		$mindmaps = get_posts( array( 'post_type' => 'mms_mind_map', 'numberposts' => -1 ) );
		foreach ( $mindmaps as $mm ) {
			$old_topic_id = get_post_meta( $mm->ID, '_mms_topic_id', true );
			if ( $old_topic_id && isset( $mapping['topics'][ $old_topic_id ] ) ) {
				$new_topic_id = $mapping['topics'][ $old_topic_id ];
				update_post_meta( $mm->ID, '_mms_topic_id', $new_topic_id );
				update_post_meta( $mm->ID, '_mcp_topic_id', $new_topic_id );
				$logs[] = "Re-linked MindMap {$mm->ID} to New Topic {$new_topic_id}";
			}
		}

		// Save final mapping and logs
		self::save_mapping( $mapping );
		update_option( self::$option_log_key, $logs );

		return array(
			'migrated' => $migrated,
			'skipped'  => $skipped,
			'failed'   => $failed,
		);
	}

	public static function execute_rollback() {
		$mapping = self::get_mapping();
		if ( isset( $mapping['created'] ) && is_array( $mapping['created'] ) ) {
			foreach ( $mapping['created'] as $pid ) {
				wp_delete_post( $pid, true );
			}
		}

		self::save_mapping( array(
			'courses'  => array(),
			'lessons'  => array(),
			'topics'   => array(),
			'tests'    => array(),
			'mindmaps' => array(),
			'created'  => array(),
		) );
		update_option( self::$option_log_key, array( 'Rollback performed at ' . current_time( 'mysql' ) ) );
	}
}

Dr_Khaste_Migration::init();
