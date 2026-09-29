<?php
/**
 * Unified Meta Boxes for Dr. Khasteh Core
 *
 * Consolidates meta boxes for Course, Lesson, Topic, Flashcard, Section Type, Test, and MindMap Studio.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register all meta boxes.
 */
function dr_khasteh_core_register_meta_boxes() {
    // 1. Course Meta Boxes
    add_meta_box(
        'dr_khasteh_course_lessons_order_meta_box',
        'ترتیب درس‌ها',
        'dr_khasteh_course_lessons_order_meta_box_html',
        'course',
        'normal'
    );

    // 2. Lesson Meta Boxes
    add_meta_box(
        'dr_khasteh_lesson_parent_meta_box',
        'دوره',
        'dr_khasteh_lesson_parent_meta_box_html',
        'lesson',
        'side'
    );
    add_meta_box(
        'dr_khasteh_lesson_topics_order_meta_box',
        'ترتیب مباحث',
        'dr_khasteh_lesson_topics_order_meta_box_html',
        'lesson',
        'normal'
    );

    // 3. Topic Meta Boxes
    add_meta_box(
        'dr_khasteh_topic_course_meta_box',
        'دوره',
        'dr_khasteh_topic_course_meta_box_html',
        'topic',
        'side'
    );
    add_meta_box(
        'dr_khasteh_topic_parent_meta_box',
        'درس',
        'dr_khasteh_topic_parent_meta_box_html',
        'topic',
        'side'
    );
    add_meta_box(
        'dr_khasteh_topic_sections_meta_box',
        'بخش‌ها',
        'dr_khasteh_topic_sections_meta_box_html',
        'topic',
        'normal'
    );
    add_meta_box(
        'dr_khasteh_topic_flashcards_meta_box',
        'افزودن فلش‌کارت',
        'dr_khasteh_topic_flashcards_meta_box_html',
        'topic',
        'normal'
    );
    add_meta_box(
        'dr_khasteh_topic_tests_meta_box',
        'افزودن تست',
        'dr_khasteh_topic_tests_meta_box_html',
        'topic',
        'normal'
    );

    // 4. Test Meta Boxes
    add_meta_box(
        'dr_khasteh_test_course_meta_box',
        'دوره',
        'dr_khasteh_topic_course_meta_box_html',
        'test',
        'side'
    );
    add_meta_box(
        'dr_khasteh_test_lesson_meta_box',
        'درس',
        'dr_khasteh_topic_parent_meta_box_html',
        'test',
        'side'
    );
    add_meta_box(
        'dr_khasteh_test_parent_meta_box',
        'مبحث',
        'dr_khasteh_test_parent_meta_box_html',
        'test',
        'side'
    );
    add_meta_box(
        'dr_khasteh_test_details_meta_box',
        'جزئیات تست',
        'dr_khasteh_test_details_meta_box_html',
        'test',
        'normal'
    );

    // 5. Flashcard Meta Boxes
    add_meta_box(
        'dr_khasteh_flashcard_course_meta_box',
        'دوره',
        'dr_khasteh_topic_course_meta_box_html',
        'flashcard',
        'side'
    );
    add_meta_box(
        'dr_khasteh_flashcard_lesson_meta_box',
        'درس',
        'dr_khasteh_topic_parent_meta_box_html',
        'flashcard',
        'side'
    );
    add_meta_box(
        'dr_khasteh_flashcard_parent_meta_box',
        'مبحث',
        'dr_khasteh_flashcard_parent_meta_box_html',
        'flashcard',
        'side'
    );
    add_meta_box(
        'dr_khasteh_flashcard_details_meta_box',
        'جزئیات فلش‌کارت',
        'dr_khasteh_flashcard_details_meta_box_html',
        'flashcard',
        'normal'
    );

    // 6. Section Type Icon Meta Box
    add_meta_box(
        'dr_khasteh_section_type_icon_meta_box',
        'آیکون و نشان‌دار',
        'dr_khasteh_section_type_icon_meta_box_html',
        'section_type',
        'side'
    );

    // 7. English Slug Meta Box (for course, lesson, topic, test)
    $slug_post_types = [ 'course', 'lesson', 'topic', 'test' ];
    foreach ( $slug_post_types as $post_type ) {
        add_meta_box(
            'dr_khasteh_english_slug_meta_box',
            'English Slug',
            'dr_khasteh_english_slug_meta_box_html',
            $post_type,
            'side'
        );
    }
}
add_action( 'add_meta_boxes', 'dr_khasteh_core_register_meta_boxes' );

/**
 * Display English Slug Meta Box
 */
function dr_khasteh_english_slug_meta_box_html( $post ) {
    $slug = get_post_meta( $post->ID, '_english_slug', true );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="dr_khasteh_english_slug">Enter a URL-friendly slug:</label>
    <input type="text" id="dr_khasteh_english_slug" name="dr_khasteh_english_slug" value="<?php echo esc_attr( $slug ); ?>" class="widefat">
    <p class="howto">فقط از حروف کوچک انگلیسی، أعداد و خط تیره استفاده کنید.</p>
    <?php
}

/**
 * Display Lesson Parent (Course) Meta Box
 */
function dr_khasteh_lesson_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_course_id', true );
    $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="mcp_course_id">انتخاب دوره:</label>
    <select id="mcp_course_id" name="mcp_course_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $courses as $course ) : ?>
            <option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $parent_id, $course->ID ); ?>>
                <?php echo esc_html( $course->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Display Topic / Test / Flashcard Course Meta Box
 */
function dr_khasteh_topic_course_meta_box_html( $post ) {
    $course_id = get_post_meta( $post->ID, '_course_id', true );
    $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="mcp_topic_course_id">انتخاب دوره:</label>
    <select id="mcp_topic_course_id" name="mcp_topic_course_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $courses as $course ) : ?>
            <option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
                <?php echo esc_html( $course->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Display Topic / Test / Flashcard Lesson Meta Box
 */
function dr_khasteh_topic_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_lesson_id', true );
    $lessons = get_posts( [ 'post_type' => 'lesson', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="mcp_lesson_id">انتخاب درس:</label>
    <select id="mcp_lesson_id" name="mcp_lesson_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $lessons as $lesson ) : ?>
            <option value="<?php echo esc_attr( $lesson->ID ); ?>" <?php selected( $parent_id, $lesson->ID ); ?>>
                <?php echo esc_html( $lesson->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Display Test Parent (Topic) Meta Box
 */
function dr_khasteh_test_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_topic_id', true );
    $topics = get_posts( [ 'post_type' => 'topic', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="mcp_topic_id">انتخاب مبحث:</label>
    <select id="mcp_topic_id" name="mcp_topic_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $topics as $topic ) : ?>
            <option value="<?php echo esc_attr( $topic->ID ); ?>" <?php selected( $parent_id, $topic->ID ); ?>>
                <?php echo esc_html( $topic->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Display Flashcard Parent (Topic) Meta Box
 */
function dr_khasteh_flashcard_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_topic_id', true );
    $topics = get_posts( [ 'post_type' => 'topic', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <label for="mcp_topic_id">انتخاب مبحث:</label>
    <select id="mcp_topic_id" name="mcp_topic_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $topics as $topic ) : ?>
            <option value="<?php echo esc_attr( $topic->ID ); ?>" <?php selected( $parent_id, $topic->ID ); ?>>
                <?php echo esc_html( $topic->post_title ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Display Flashcard Details Meta Box
 */
function dr_khasteh_flashcard_details_meta_box_html( $post ) {
    $question = get_post_meta( $post->ID, '_question', true );
    $answer = get_post_meta( $post->ID, '_answer', true );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <p>
        <label for="mcp_question">صورت سوال:</label>
        <textarea id="mcp_question" name="mcp_question" class="widefat" rows="5"><?php echo esc_textarea( $question ); ?></textarea>
    </p>
    <p>
        <label for="mcp_answer">پاسخ:</label>
        <textarea id="mcp_answer" name="mcp_answer" class="widefat" rows="5"><?php echo esc_textarea( $answer ); ?></textarea>
    </p>
    <?php
}

/**
 * Display Test Details Meta Box
 */
function dr_khasteh_test_details_meta_box_html( $post ) {
    $identifier = get_post_meta( $post->ID, '_test_identifier', true );
    $question_number = get_post_meta( $post->ID, '_test_question_number', true );
    $exam = get_post_meta( $post->ID, '_test_exam', true );
    $date = get_post_meta( $post->ID, '_test_date', true );
    $question = get_post_meta( $post->ID, '_test_question', true );
    $option1 = get_post_meta( $post->ID, '_test_option1', true );
    $option2 = get_post_meta( $post->ID, '_test_option2', true );
    $option3 = get_post_meta( $post->ID, '_test_option3', true );
    $option4 = get_post_meta( $post->ID, '_test_option4', true );
    $correct_option = get_post_meta( $post->ID, '_test_correct_option', true );
    $correct_options = array_filter( array_map( 'trim', explode( ',', $correct_option ) ) );
    $explanation = get_post_meta( $post->ID, '_test_explanation', true );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <p><label>شناسه (Identifier):</label><input type="text" name="mtp_identifier" value="<?php echo esc_attr($identifier); ?>" class="widefat"></p>
    <p><label>شماره سوال:</label><input type="text" name="mtp_question_number" value="<?php echo esc_attr($question_number); ?>" class="widefat"></p>
    <p><label>آزمون:</label><input type="text" name="mtp_exam" value="<?php echo esc_attr($exam); ?>" class="widefat"></p>
    <p><label>تاریخ:</label><input type="text" name="mtp_date" value="<?php echo esc_attr($date); ?>" class="widefat"></p>
    <p><label>سوال:</label><?php wp_editor( $question, 'mtp_question', [ 'textarea_name' => 'mtp_question' ] ); ?></p>
    <p><label>گزینه ۱:</label><input type="text" name="mtp_option1" value="<?php echo esc_attr($option1); ?>" class="widefat"></p>
    <p><label>گزینه ۲:</label><input type="text" name="mtp_option2" value="<?php echo esc_attr($option2); ?>" class="widefat"></p>
    <p><label>گزینه ۳:</label><input type="text" name="mtp_option3" value="<?php echo esc_attr($option3); ?>" class="widefat"></p>
    <p><label>گزینه ۴:</label><input type="text" name="mtp_option4" value="<?php echo esc_attr($option4); ?>" class="widefat"></p>
    <p><label>گزینه(های) صحیح:</label><br>
        <?php for($i=1; $i<=4; $i++): ?>
            <label style="margin-right: 15px;">
                <input type="checkbox" name="mtp_correct_options[]" value="<?php echo $i; ?>" <?php checked( in_array( (string)$i, $correct_options, true ) ); ?>> گزینه <?php echo $i; ?>
            </label>
        <?php endfor; ?>
    </p>
    <p><label>پاسخ تشریحی:</label><?php wp_editor( $explanation, 'mtp_explanation', [ 'textarea_name' => 'mtp_explanation' ] ); ?></p>
    <?php
}

/**
 * Display Topic Sections Meta Box
 */
function dr_khasteh_topic_sections_meta_box_html( $post ) {
    $sections = get_post_meta( $post->ID, '_sections', true );

    $all_section_types = get_posts( [
        'post_type' => 'section_type',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ] );

    $starred = [];
    $not_starred = [];
    foreach ($all_section_types as $st) {
        if (get_post_meta($st->ID, '_mcp_is_starred', true) == '1') {
            $starred[] = $st;
        } else {
            $not_starred[] = $st;
        }
    }

    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <input type="hidden" name="dr_khasteh_topic_sections_posted" value="1">
    <div id="mcp-sections-repeater">
        <div class="mcp-section-template" style="display: none;">
            <div class="mcp-section">
                <label>نوع بخش:</label>
                <div class="mcp-section-type-wrapper">
                    <select class="mcp-section-type-select" name="mcp_sections[__INDEX__][section_type]" style="width: 80%;">
                        <option value="">— انتخاب —</option>
                        <?php if ( ! empty( $starred ) ) : ?>
                            <optgroup label="نشان‌دار">
                                <?php foreach ( $starred as $section_type ) : ?>
                                    <option value="<?php echo esc_attr( $section_type->ID ); ?>"><?php echo esc_html( $section_type->post_title ); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <optgroup label="سایر">
                            <?php foreach ( $not_starred as $section_type ) : ?>
                                <option class="non-starred-option" value="<?php echo esc_attr( $section_type->ID ); ?>"><?php echo esc_html( $section_type->post_title ); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                    <button type="button" class="button mcp-add-new-section-type">افزودن جدید</button>
                    <div class="mcp-inline-form-wrapper" style="display: none;">
                        <h4>ایجاد نوع بخش جدید</h4>
                        <input type="text" class="mcp-new-section-type-title" placeholder="عنوان">
                        <input type="text" class="mcp-new-section-type-icon" placeholder="کلاس آیکون (مثلا fa-book)">
                        <label><input type="checkbox" class="mcp-new-section-type-starred"> نشان‌دار</label>
                        <button type="button" class="button button-primary mcp-save-new-section-type">ذخیره</button>
                        <button type="button" class="button mcp-cancel-new-section-type">انصراف</button>
                    </div>
                </div>
                <label>محتوا:</label>
                <textarea id="mcp_sections___INDEX___content" name="mcp_sections[__INDEX__][content]" class="mcp-editor-area" style="width: 100%;" rows="8"></textarea>
                <button type="button" class="button mcp-remove-section">حذف بخش</button>
            </div>
        </div>

        <div class="mcp-sections-container">
            <?php if ( ! empty( $sections ) ) : ?>
                <?php foreach ( $sections as $index => $section ) : ?>
                    <div class="mcp-section">
                        <label>نوع بخش:</label>
                        <div class="mcp-section-type-wrapper">
                            <select class="mcp-section-type-select" name="mcp_sections[<?php echo $index; ?>][section_type]" style="width: 80%;">
                                <option value="">— انتخاب —</option>
                                <?php if ( ! empty( $starred ) ) : ?>
                                    <optgroup label="نشان‌دار">
                                        <?php foreach ( $starred as $section_type ) : ?>
                                            <option value="<?php echo esc_attr( $section_type->ID ); ?>" <?php selected( $section['section_type'], $section_type->ID ); ?>><?php echo esc_html( $section_type->post_title ); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <optgroup label="سایر">
                                    <?php foreach ( $not_starred as $section_type ) : ?>
                                        <option class="non-starred-option" value="<?php echo esc_attr( $section_type->ID ); ?>" <?php selected( $section['section_type'], $section_type->ID ); ?>><?php echo esc_html( $section_type->post_title ); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <button type="button" class="button mcp-add-new-section-type">افزودن جدید</button>
                            <div class="mcp-inline-form-wrapper" style="display: none;">
                                <h4>ایجاد نوع بخش جدید</h4>
                                <input type="text" class="mcp-new-section-type-title" placeholder="عنوان">
                                <input type="text" class="mcp-new-section-type-icon" placeholder="کلاس آیکون (مثلا fa-book)">
                                <label><input type="checkbox" class="mcp-new-section-type-starred"> نشان‌دار</label>
                                <button type="button" class="button button-primary mcp-save-new-section-type">ذخیره</button>
                                <button type="button" class="button mcp-cancel-new-section-type">انصراف</button>
                            </div>
                        </div>
                        <label>محتوا:</label>
                        <?php wp_editor( $section['content'], 'mcp_sections_' . $index . '_content', [ 'textarea_name' => 'mcp_sections[' . $index . '][content]' ] ); ?>
                        <button type="button" class="button mcp-remove-section">حذف بخش</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="mcp-add-section" class="button button-primary">افزودن بخش</button>
    </div>
    <?php
}

/**
 * Display Section Type Icon Meta Box
 */
function dr_khasteh_section_type_icon_meta_box_html( $post ) {
    $icon_class = get_post_meta( $post->ID, '_mcp_icon_class', true );
    $is_starred = get_post_meta( $post->ID, '_mcp_is_starred', true );
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );

    $icons = [
        'fa-user-doctor', 'fa-stethoscope', 'fa-pills', 'fa-notes-medical', 'fa-kit-medical', 'fa-hospital', 'fa-heart-pulse', 'fa-file-medical', 'fa-dna', 'fa-capsules', 'fa-brain', 'fa-book-medical', 'fa-band-aid', 'fa-syringe', 'fa-virus', 'fa-lungs', 'fa-microscope', 'fa-prescription-bottle', 'fa-crutch', 'fa-weight-scale',
        'fa-book', 'fa-book-open-reader', 'fa-graduation-cap', 'fa-chalkboard-user', 'fa-school', 'fa-pencil', 'fa-highlighter', 'fa-award', 'fa-atom', 'fa-flask', 'fa-lightbulb', 'fa-question', 'fa-certificate', 'fa-file-lines', 'fa-list-check', 'fa-bullseye', 'fa-clipboard-question', 'fa-magnifying-glass', 'fa-globe', 'fa-calculator',
        'fa-star', 'fa-bookmark', 'fa-flag', 'fa-check', 'fa-circle-info', 'fa-hourglass-half', 'fa-key', 'fa-sitemap', 'fa-tasks', 'fa-lightbulb'
    ];
    ?>
    <p>
        <input type="checkbox" id="mcp_is_starred" name="mcp_is_starred" value="1" <?php checked( $is_starred, '1' ); ?>>
        <label for="mcp_is_starred">نشان‌دار (نمایش در بالای لیست)</label>
    </p>
    <hr>
    <label for="mcp_icon_class">کلاس آیکون Font Awesome:</label>
    <input type="text" id="mcp_icon_class" name="mcp_icon_class" value="<?php echo esc_attr( $icon_class ); ?>" class="widefat">
    <p class="howto">مثال: fa-book-medical</p>

    <div class="mcp-icon-picker-wrapper">
        <div class="mcp-icon-picker">
            <?php foreach ( $icons as $icon ) : ?>
                <i class="fas <?php echo esc_attr( $icon ); ?>" data-icon="<?php echo esc_attr( $icon ); ?>"></i>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Display Topic Flashcards Meta Box
 */
function dr_khasteh_topic_flashcards_meta_box_html( $post ) {
    $flashcards = get_posts([
        'post_type' => 'flashcard',
        'meta_key' => '_topic_id',
        'meta_value' => $post->ID,
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'ASC'
    ]);
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <input type="hidden" name="dr_khasteh_topic_flashcards_posted" value="1">
    <div id="mcp-flashcards-repeater">
        <div class="mcp-flashcard-template" style="display: none;">
            <div class="mcp-flashcard">
                <label>سوال:</label>
                <textarea name="mcp_flashcards[__INDEX__][question]" class="widefat" rows="3"></textarea>
                <label>پاسخ:</label>
                <textarea name="mcp_flashcards[__INDEX__][answer]" class="widefat" rows="3"></textarea>
                <input type="hidden" name="mcp_flashcards[__INDEX__][id]" value="">
                <button type="button" class="button mcp-remove-flashcard">حذف فلش‌کارت</button>
            </div>
        </div>

        <div class="mcp-flashcards-container">
            <?php if ( ! empty( $flashcards ) ) : ?>
                <?php foreach ( $flashcards as $index => $flashcard ) : ?>
                    <div class="mcp-flashcard">
                        <label>سوال:</label>
                        <textarea name="mcp_flashcards[<?php echo $index; ?>][question]" class="widefat" rows="3"><?php echo esc_textarea( get_post_meta( $flashcard->ID, '_question', true ) ); ?></textarea>
                        <label>پاسخ:</label>
                        <textarea name="mcp_flashcards[<?php echo $index; ?>][answer]" class="widefat" rows="3"><?php echo esc_textarea( get_post_meta( $flashcard->ID, '_answer', true ) ); ?></textarea>
                        <input type="hidden" name="mcp_flashcards[<?php echo $index; ?>][id]" value="<?php echo esc_attr( $flashcard->ID ); ?>">
                        <button type="button" class="button mcp-remove-flashcard">حذف فلش‌کارت</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="mcp-add-flashcard" class="button button-primary">افزودن فلش‌کارت</button>
    </div>
    <?php
}

/**
 * Display Topic Tests Meta Box
 */
function dr_khasteh_topic_tests_meta_box_html( $post ) {
    $tests = get_posts([
        'post_type' => 'test',
        'meta_key' => '_topic_id',
        'meta_value' => $post->ID,
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'ASC'
    ]);
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
    ?>
    <input type="hidden" name="dr_khasteh_topic_tests_posted" value="1">
    <div id="mtp-tests-repeater">
        <div class="mtp-test-template" style="display: none;">
            <div class="mtp-test">
                <p><label>شناسه (Identifier):</label><input type="text" name="mtp_tests[__INDEX__][identifier]" class="widefat"></p>
                <p><label>شماره سوال:</label><input type="text" name="mtp_tests[__INDEX__][question_number]" class="widefat"></p>
                <p><label>آزمون:</label><input type="text" name="mtp_tests[__INDEX__][exam]" class="widefat"></p>
                <p><label>تاریخ:</label><input type="text" name="mtp_tests[__INDEX__][date]" class="widefat"></p>
                <p><label>سوال:</label><textarea id="mtp_tests___INDEX___question" name="mtp_tests[__INDEX__][question]" class="mtp-test-editor-area" style="width: 100%;" rows="5"></textarea></p>
                <p><label>گزینه ۱:</label><input type="text" name="mtp_tests[__INDEX__][option1]" class="widefat"></p>
                <p><label>گزینه ۲:</label><input type="text" name="mtp_tests[__INDEX__][option2]" class="widefat"></p>
                <p><label>گزینه ۳:</label><input type="text" name="mtp_tests[__INDEX__][option3]" class="widefat"></p>
                <p><label>گزینه ۴:</label><input type="text" name="mtp_tests[__INDEX__][option4]" class="widefat"></p>
                <p><label>گزینه(های) صحیح:</label><br>
                    <?php for($i=1; $i<=4; $i++): ?>
                        <label style="margin-right: 15px;">
                            <input type="checkbox" name="mtp_tests[__INDEX__][correct_options][]" value="<?php echo $i; ?>"> گزینه <?php echo $i; ?>
                        </label>
                    <?php endfor; ?>
                </p>
                <p><label>پاسخ تشریحی:</label><textarea id="mtp_tests___INDEX___explanation" name="mtp_tests[__INDEX__][explanation]" class="mtp-test-editor-area" style="width: 100%;" rows="5"></textarea></p>
                <input type="hidden" name="mtp_tests[__INDEX__][id]" value="">
                <button type="button" class="button mtp-remove-test">حذف تست</button>
            </div>
        </div>

        <div class="mtp-tests-container">
            <?php foreach ( $tests as $index => $test ) :
                $correct_option = get_post_meta( $test->ID, '_test_correct_option', true );
                $correct_options = array_filter( array_map( 'trim', explode( ',', $correct_option ) ) );
            ?>
                <div class="mtp-test">
                    <p><label>شناسه (Identifier):</label><input type="text" name="mtp_tests[<?php echo $index; ?>][identifier]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_identifier', true ) ); ?>"></p>
                    <p><label>شماره سوال:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][question_number]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_question_number', true ) ); ?>"></p>
                    <p><label>آزمون:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][exam]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_exam', true ) ); ?>"></p>
                    <p><label>تاریخ:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][date]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_date', true ) ); ?>"></p>
                    <p><label>سوال:</label><?php wp_editor( get_post_meta( $test->ID, '_test_question', true ), 'mtp_tests_' . $index . '_question', [ 'textarea_name' => 'mtp_tests[' . $index . '][question]' ] ); ?></p>
                    <p><label>گزینه ۱:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option1]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option1', true ) ); ?>"></p>
                    <p><label>گزینه ۲:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option2]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option2', true ) ); ?>"></p>
                    <p><label>گزینه ۳:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option3]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option3', true ) ); ?>"></p>
                    <p><label>گزینه ۴:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option4]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option4', true ) ); ?>"></p>
                    <p><label>گزینه(های) صحیح:</label><br>
                        <?php for($i=1; $i<=4; $i++): ?>
                            <label style="margin-right: 15px;">
                                <input type="checkbox" name="mtp_tests[<?php echo $index; ?>][correct_options][]" value="<?php echo $i; ?>" <?php checked( in_array( (string)$i, $correct_options, true ) ); ?>> گزینه <?php echo $i; ?>
                            </label>
                        <?php endfor; ?>
                    </p>
                    <p><label>پاسخ تشریحی:</label><?php wp_editor( get_post_meta( $test->ID, '_test_explanation', true ), 'mtp_tests_' . $index . '_explanation', [ 'textarea_name' => 'mtp_tests[' . $index . '][explanation]' ] ); ?></p>
                    <input type="hidden" name="mtp_tests[<?php echo $index; ?>][id]" value="<?php echo $test->ID; ?>">
                    <button type="button" class="button mtp-remove-test">حذف تست</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="mtp-add-test" class="button button-primary">افزودن تست</button>
    </div>
    <?php
}

/**
 * Display Course Lessons Order Meta Box
 */
function dr_khasteh_course_lessons_order_meta_box_html( $post ) {
    $lessons = get_posts([
        'post_type' => 'lesson',
        'meta_key' => '_course_id',
        'meta_value' => $post->ID,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ]);

    echo '<p>برای تغییر ترتیب درس‌ها جابه‌جا کنید:</p>';
    echo '<ul id="mcp-sortable-lessons" class="mcp-sortable-list">';
    if ( ! empty( $lessons ) ) {
        foreach ( $lessons as $lesson ) {
            echo '<li class="ui-state-default" data-id="' . esc_attr( $lesson->ID ) . '"><span class="dashicons dashicons-move"></span> ' . esc_html( $lesson->post_title ) . '</li>';
        }
    } else {
        echo '<li>درسی برای این دوره یافت نشد.</li>';
    }
    echo '</ul>';
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
}

/**
 * Display Lesson Topics Order Meta Box
 */
function dr_khasteh_lesson_topics_order_meta_box_html( $post ) {
    $topics = get_posts([
        'post_type' => 'topic',
        'meta_key' => '_lesson_id',
        'meta_value' => $post->ID,
        'numberposts' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ]);

    echo '<p>برای تغییر ترتیب مباحث جابه‌جا کنید:</p>';
    echo '<ul id="mcp-sortable-topics" class="mcp-sortable-list">';
    if ( ! empty( $topics ) ) {
        foreach ( $topics as $topic ) {
            echo '<li class="ui-state-default" data-id="' . esc_attr( $topic->ID ) . '"><span class="dashicons dashicons-move"></span> ' . esc_html( $topic->post_title ) . '</li>';
        }
    } else {
        echo '<li>مبحثی برای این درس یافت نشد.</li>';
    }
    echo '</ul>';
    wp_nonce_field( 'dr_khasteh_save_meta_box_data', 'dr_khasteh_meta_box_nonce', false );
}

/**
 * Single Unified Save Handler
 */
function dr_khasteh_core_save_meta_box_data( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['dr_khasteh_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['dr_khasteh_meta_box_nonce'], 'dr_khasteh_save_meta_box_data' ) ) {
        if ( ! isset( $_POST['mcp_english_slug_meta_box_nonce'] ) && ! isset( $_POST['mtp_meta_box_nonce'] ) ) {
            return;
        }
    }

    $post_type = get_post_type( $post_id );

    // 1. English Slug
    if ( isset( $_POST['dr_khasteh_english_slug'] ) || isset( $_POST['mcp_english_slug'] ) || isset( $_POST['mtp_english_slug'] ) ) {
        $raw_slug = isset( $_POST['dr_khasteh_english_slug'] ) ? $_POST['dr_khasteh_english_slug'] : ( isset( $_POST['mcp_english_slug'] ) ? $_POST['mcp_english_slug'] : $_POST['mtp_english_slug'] );
        $slug = sanitize_title( $raw_slug );
        update_post_meta( $post_id, '_english_slug', $slug );

        if ( ! empty( $slug ) ) {
            remove_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );
            wp_update_post( [ 'ID' => $post_id, 'post_name' => $slug ] );
            add_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );
        }
    }

    // 2. Course ID
    if ( isset( $_POST['mcp_course_id'] ) ) {
        update_post_meta( $post_id, '_course_id', absint( $_POST['mcp_course_id'] ) );
    } elseif ( isset( $_POST['mcp_topic_course_id'] ) ) {
        update_post_meta( $post_id, '_course_id', absint( $_POST['mcp_topic_course_id'] ) );
    }

    // 3. Lesson ID
    if ( isset( $_POST['mcp_lesson_id'] ) ) {
        update_post_meta( $post_id, '_lesson_id', absint( $_POST['mcp_lesson_id'] ) );
    }

    // 4. Topic ID
    if ( isset( $_POST['mcp_topic_id'] ) ) {
        update_post_meta( $post_id, '_topic_id', absint( $_POST['mcp_topic_id'] ) );
    }

    // 5. Section Type Icon & Starred
    if ( isset( $_POST['mcp_icon_class'] ) ) {
        update_post_meta( $post_id, '_mcp_icon_class', sanitize_text_field( $_POST['mcp_icon_class'] ) );
    }
    if ( isset( $_POST['mcp_is_starred'] ) ) {
        update_post_meta( $post_id, '_mcp_is_starred', '1' );
    } elseif ( $post_type === 'section_type' ) {
        update_post_meta( $post_id, '_mcp_is_starred', '0' );
    }

    // 6. Topic Sections
    if ( isset( $_POST['dr_khasteh_topic_sections_posted'] ) ) {
        $sections = [];
        if ( isset( $_POST['mcp_sections'] ) && is_array( $_POST['mcp_sections'] ) ) {
            foreach ( $_POST['mcp_sections'] as $section ) {
                if ( ! empty( $section['section_type'] ) ) {
                    $sections[] = [
                        'section_type' => absint( $section['section_type'] ),
                        'content' => wp_kses_post( $section['content'] ),
                    ];
                }
            }
        }
        update_post_meta( $post_id, '_sections', $sections );
    }

    // 7. Flashcard Details
    if ( isset( $_POST['mcp_question'] ) ) {
        update_post_meta( $post_id, '_question', sanitize_textarea_field( $_POST['mcp_question'] ) );
    }
    if ( isset( $_POST['mcp_answer'] ) ) {
        update_post_meta( $post_id, '_answer', sanitize_textarea_field( $_POST['mcp_answer'] ) );
    }

    // 8. Repeater Flashcards on Topic
    if ( isset( $_POST['dr_khasteh_topic_flashcards_posted'] ) ) {
        remove_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );

        $existing_ids = get_posts([
            'post_type' => 'flashcard',
            'meta_key' => '_topic_id',
            'meta_value' => $post_id,
            'fields' => 'ids',
            'numberposts' => -1
        ]);
        $submitted_ids = [];

        if ( isset( $_POST['mcp_flashcards'] ) && is_array( $_POST['mcp_flashcards'] ) ) {
            foreach ( $_POST['mcp_flashcards'] as $flashcard_data ) {
                $flashcard_id = ! empty( $flashcard_data['id'] ) ? absint( $flashcard_data['id'] ) : 0;
                $question = sanitize_textarea_field( $flashcard_data['question'] );
                $answer = sanitize_textarea_field( $flashcard_data['answer'] );

                if ( empty( $question ) && empty( $answer ) ) {
                    if ( $flashcard_id ) {
                        wp_delete_post( $flashcard_id, true );
                    }
                    continue;
                }

                if ( $flashcard_id ) {
                    wp_update_post( [
                        'ID' => $flashcard_id,
                        'post_title' => wp_trim_words( $question, 10, '...' ),
                    ] );
                    update_post_meta( $flashcard_id, '_question', $question );
                    update_post_meta( $flashcard_id, '_answer', $answer );
                    $submitted_ids[] = $flashcard_id;
                } else {
                    $new_flashcard_id = wp_insert_post( [
                        'post_title' => wp_trim_words( $question, 10, '...' ),
                        'post_type' => 'flashcard',
                        'post_status' => 'publish'
                    ] );
                    if ( $new_flashcard_id ) {
                        update_post_meta( $new_flashcard_id, '_topic_id', $post_id );
                        update_post_meta( $new_flashcard_id, '_question', $question );
                        update_post_meta( $new_flashcard_id, '_answer', $answer );
                    }
                }
            }
        }

        $deleted_ids = array_diff( $existing_ids, $submitted_ids );
        foreach ( $deleted_ids as $deleted_id ) {
            wp_delete_post( $deleted_id, true );
        }

        add_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );
    }

    // 9. Single Test Details
    if ( $post_type === 'test' ) {
        if ( isset( $_POST['mtp_identifier'] ) ) update_post_meta( $post_id, '_test_identifier', sanitize_text_field( $_POST['mtp_identifier'] ) );
        if ( isset( $_POST['mtp_question_number'] ) ) update_post_meta( $post_id, '_test_question_number', sanitize_text_field( $_POST['mtp_question_number'] ) );
        if ( isset( $_POST['mtp_exam'] ) ) update_post_meta( $post_id, '_test_exam', sanitize_text_field( $_POST['mtp_exam'] ) );
        if ( isset( $_POST['mtp_date'] ) ) update_post_meta( $post_id, '_test_date', sanitize_text_field( $_POST['mtp_date'] ) );
        if ( isset( $_POST['mtp_question'] ) ) update_post_meta( $post_id, '_test_question', wp_kses_post( $_POST['mtp_question'] ) );
        if ( isset( $_POST['mtp_option1'] ) ) update_post_meta( $post_id, '_test_option1', sanitize_text_field( $_POST['mtp_option1'] ) );
        if ( isset( $_POST['mtp_option2'] ) ) update_post_meta( $post_id, '_test_option2', sanitize_text_field( $_POST['mtp_option2'] ) );
        if ( isset( $_POST['mtp_option3'] ) ) update_post_meta( $post_id, '_test_option3', sanitize_text_field( $_POST['mtp_option3'] ) );
        if ( isset( $_POST['mtp_option4'] ) ) update_post_meta( $post_id, '_test_option4', sanitize_text_field( $_POST['mtp_option4'] ) );

        $correct_options_val = '';
        if ( isset( $_POST['mtp_correct_options'] ) && is_array( $_POST['mtp_correct_options'] ) ) {
            $correct_options_val = implode( ',', array_map( 'absint', $_POST['mtp_correct_options'] ) );
        }
        update_post_meta( $post_id, '_test_correct_option', $correct_options_val );

        if ( isset( $_POST['mtp_explanation'] ) ) update_post_meta( $post_id, '_test_explanation', wp_kses_post( $_POST['mtp_explanation'] ) );
    }

    // 10. Repeater Tests on Topic
    if ( isset( $_POST['dr_khasteh_topic_tests_posted'] ) ) {
        remove_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );

        $existing_ids = get_posts([
            'post_type' => 'test',
            'meta_key' => '_topic_id',
            'meta_value' => $post_id,
            'fields' => 'ids',
            'numberposts' => -1
        ]);
        $submitted_ids = [];

        if ( isset( $_POST['mtp_tests'] ) && is_array( $_POST['mtp_tests'] ) ) {
            foreach ( $_POST['mtp_tests'] as $test_data ) {
                $test_id = ! empty( $test_data['id'] ) ? absint( $test_data['id'] ) : 0;
                $question = wp_kses_post( $test_data['question'] );

                if ( empty( $question ) ) {
                    if ( $test_id ) {
                        wp_delete_post( $test_id, true );
                    }
                    continue;
                }

                $post_arr = [
                    'post_title' => wp_trim_words( $question, 10, '...' ),
                    'post_type' => 'test',
                    'post_status' => 'publish'
                ];

                if ( $test_id ) {
                    $post_arr['ID'] = $test_id;
                    wp_update_post( $post_arr );
                } else {
                    $test_id = wp_insert_post( $post_arr );
                }

                if ( $test_id ) {
                    update_post_meta( $test_id, '_topic_id', $post_id );
                    update_post_meta( $test_id, '_test_identifier', sanitize_text_field( $test_data['identifier'] ) );
                    update_post_meta( $test_id, '_test_question_number', sanitize_text_field( $test_data['question_number'] ) );
                    update_post_meta( $test_id, '_test_exam', sanitize_text_field( $test_data['exam'] ) );
                    update_post_meta( $test_id, '_test_date', sanitize_text_field( $test_data['date'] ) );
                    update_post_meta( $test_id, '_test_question', $question );
                    update_post_meta( $test_id, '_test_option1', sanitize_text_field( $test_data['option1'] ) );
                    update_post_meta( $test_id, '_test_option2', sanitize_text_field( $test_data['option2'] ) );
                    update_post_meta( $test_id, '_test_option3', sanitize_text_field( $test_data['option3'] ) );
                    update_post_meta( $test_id, '_test_option4', sanitize_text_field( $test_data['option4'] ) );

                    $correct_options_val = '';
                    if ( isset( $test_data['correct_options'] ) && is_array( $test_data['correct_options'] ) ) {
                        $correct_options_val = implode( ',', array_map( 'absint', $test_data['correct_options'] ) );
                    }
                    update_post_meta( $test_id, '_test_correct_option', $correct_options_val );

                    update_post_meta( $test_id, '_test_explanation', wp_kses_post( $test_data['explanation'] ) );
                    $submitted_ids[] = $test_id;
                }
            }
        }

        $deleted_ids = array_diff( $existing_ids, $submitted_ids );
        foreach ( $deleted_ids as $deleted_id ) {
            wp_delete_post( $deleted_id, true );
        }

        add_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );
    }
}
add_action( 'save_post', 'dr_khasteh_core_save_meta_box_data' );
