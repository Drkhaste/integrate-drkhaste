<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_register_meta_boxes() {
    add_meta_box( 'mtp_lesson_parent_meta_box', 'دوره', 'mtp_lesson_parent_meta_box_html', 'lesson', 'side' );
    add_meta_box( 'mtp_topic_course_meta_box', 'دوره', 'mtp_topic_course_meta_box_html', 'topic', 'side' );
    add_meta_box( 'mtp_topic_parent_meta_box', 'درس', 'mtp_topic_parent_meta_box_html', 'topic', 'side' );
    add_meta_box( 'mtp_test_course_meta_box', 'دوره', 'mtp_topic_course_meta_box_html', 'test', 'side' );
    add_meta_box( 'mtp_test_lesson_meta_box', 'درس', 'mtp_topic_parent_meta_box_html', 'test', 'side' );
    add_meta_box( 'mtp_test_parent_meta_box', 'مبحث', 'mtp_test_parent_meta_box_html', 'test', 'side' );
    add_meta_box( 'mtp_test_details_meta_box', 'جزئیات تست', 'mtp_test_details_meta_box_html', 'test', 'normal' );
    add_meta_box( 'mtp_topic_tests_meta_box', 'افزودن تست', 'mtp_topic_tests_meta_box_html', 'topic', 'normal' );
    add_meta_box( 'mtp_course_lessons_order_meta_box', 'ترتیب درس‌ها', 'mtp_course_lessons_order_meta_box_html', 'course', 'normal' );
    add_meta_box( 'mtp_lesson_topics_order_meta_box', 'ترتیب مباحث', 'mtp_lesson_topics_order_meta_box_html', 'lesson', 'normal' );

    $slug_post_types = [ 'course', 'lesson', 'topic' ];
    foreach ( $slug_post_types as $post_type ) {
        add_meta_box( 'mtp_english_slug_meta_box', 'English Slug', 'mtp_english_slug_meta_box_html', $post_type, 'side' );
    }
}
add_action( 'add_meta_boxes', 'mtp_register_meta_boxes' );

function mtp_english_slug_meta_box_html( $post ) {
    $slug = get_post_meta( $post->ID, '_english_slug', true );
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    echo '<input type="text" name="mtp_english_slug" value="'.esc_attr($slug).'" class="widefat">';
}

function mtp_lesson_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_course_id', true );
    $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1 ] );
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <select name="mcp_course_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $courses as $course ) : ?>
            <option value="<?php echo $course->ID; ?>" <?php selected( $parent_id, $course->ID ); ?>><?php echo esc_html( $course->post_title ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function mtp_topic_course_meta_box_html( $post ) {
    $course_id = get_post_meta( $post->ID, '_course_id', true );
    $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1 ] );
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <select id="mcp_course_id" name="mcp_course_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $courses as $course ) : ?>
            <option value="<?php echo $course->ID; ?>" <?php selected( $course_id, $course->ID ); ?>><?php echo esc_html( $course->post_title ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function mtp_topic_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_lesson_id', true );
    $lessons = get_posts( [ 'post_type' => 'lesson', 'numberposts' => -1 ] );
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <select id="mcp_lesson_id" name="mcp_lesson_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $lessons as $lesson ) : ?>
            <option value="<?php echo $lesson->ID; ?>" <?php selected( $parent_id, $lesson->ID ); ?>><?php echo esc_html( $lesson->post_title ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function mtp_test_parent_meta_box_html( $post ) {
    $parent_id = get_post_meta( $post->ID, '_topic_id', true );
    $topics = get_posts( [ 'post_type' => 'topic', 'numberposts' => -1 ] );
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <select id="mcp_topic_id" name="mcp_topic_id" class="widefat">
        <option value="">— انتخاب —</option>
        <?php foreach ( $topics as $topic ) : ?>
            <option value="<?php echo $topic->ID; ?>" <?php selected( $parent_id, $topic->ID ); ?>><?php echo esc_html( $topic->post_title ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function mtp_test_details_meta_box_html( $post ) {
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
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <p><label>Identifier:</label><input type="text" name="mtp_identifier" value="<?php echo esc_attr($identifier); ?>" class="widefat"></p>
    <p><label>Question Number:</label><input type="text" name="mtp_question_number" value="<?php echo esc_attr($question_number); ?>" class="widefat"></p>
    <p><label>Exam:</label><input type="text" name="mtp_exam" value="<?php echo esc_attr($exam); ?>" class="widefat"></p>
    <p><label>Date:</label><input type="text" name="mtp_date" value="<?php echo esc_attr($date); ?>" class="widefat"></p>
    <p><label>Question:</label><?php wp_editor( $question, 'mtp_question', [ 'textarea_name' => 'mtp_question' ] ); ?></p>
    <p><label>Option 1:</label><input type="text" name="mtp_option1" value="<?php echo esc_attr($option1); ?>" class="widefat"></p>
    <p><label>Option 2:</label><input type="text" name="mtp_option2" value="<?php echo esc_attr($option2); ?>" class="widefat"></p>
    <p><label>Option 3:</label><input type="text" name="mtp_option3" value="<?php echo esc_attr($option3); ?>" class="widefat"></p>
    <p><label>Option 4:</label><input type="text" name="mtp_option4" value="<?php echo esc_attr($option4); ?>" class="widefat"></p>
    <p><label>Correct Options:</label><br>
        <?php for($i=1; $i<=4; $i++): ?>
            <label style="margin-right: 15px;">
                <input type="checkbox" name="mtp_correct_options[]" value="<?php echo $i; ?>" <?php checked( in_array( (string)$i, $correct_options, true ) ); ?>> Option <?php echo $i; ?>
            </label>
        <?php endfor; ?>
    </p>
    <p><label>Explanation:</label><?php wp_editor( $explanation, 'mtp_explanation', [ 'textarea_name' => 'mtp_explanation' ] ); ?></p>
    <?php
}

function mtp_topic_tests_meta_box_html( $post ) {
    $tests = get_posts(['post_type' => 'test', 'meta_key' => '_topic_id', 'meta_value' => $post->ID, 'numberposts' => -1, 'orderby' => 'date', 'order' => 'ASC']);
    wp_nonce_field( 'mtp_save_meta_box_data', 'mtp_meta_box_nonce' );
    ?>
    <div id="mtp-tests-repeater">
        <div class="mtp-test-template" style="display: none;">
            <div class="mtp-test">
                <p><label>Identifier:</label><input type="text" name="mtp_tests[__INDEX__][identifier]" class="widefat"></p>
                <p><label>Question Number:</label><input type="text" name="mtp_tests[__INDEX__][question_number]" class="widefat"></p>
                <p><label>Exam:</label><input type="text" name="mtp_tests[__INDEX__][exam]" class="widefat"></p>
                <p><label>Date:</label><input type="text" name="mtp_tests[__INDEX__][date]" class="widefat"></p>
                <p><label>Question:</label><textarea id="mtp_tests___INDEX___question" name="mtp_tests[__INDEX__][question]" class="mtp-test-editor-area" style="width: 100%;" rows="5"></textarea></p>
                <p><label>Option 1:</label><input type="text" name="mtp_tests[__INDEX__][option1]" class="widefat"></p>
                <p><label>Option 2:</label><input type="text" name="mtp_tests[__INDEX__][option2]" class="widefat"></p>
                <p><label>Option 3:</label><input type="text" name="mtp_tests[__INDEX__][option3]" class="widefat"></p>
                <p><label>Option 4:</label><input type="text" name="mtp_tests[__INDEX__][option4]" class="widefat"></p>
                <p><label>Correct Options:</label><br>
                    <?php for($i=1; $i<=4; $i++): ?>
                        <label style="margin-right: 15px;">
                            <input type="checkbox" name="mtp_tests[__INDEX__][correct_options][]" value="<?php echo $i; ?>"> Option <?php echo $i; ?>
                        </label>
                    <?php endfor; ?>
                </p>
                <p><label>Explanation:</label><textarea id="mtp_tests___INDEX___explanation" name="mtp_tests[__INDEX__][explanation]" class="mtp-test-editor-area" style="width: 100%;" rows="5"></textarea></p>
                <input type="hidden" name="mtp_tests[__INDEX__][id]" value="">
                <button type="button" class="button mtp-remove-test">Remove Test</button>
            </div>
        </div>
        <div class="mtp-tests-container">
            <?php foreach ( $tests as $index => $test ) :
                $correct_option = get_post_meta( $test->ID, '_test_correct_option', true );
                $correct_options = array_filter( array_map( 'trim', explode( ',', $correct_option ) ) );
            ?>
                <div class="mtp-test">
                    <p><label>Identifier:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][identifier]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_identifier', true ) ); ?>"></p>
                    <p><label>Question Number:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][question_number]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_question_number', true ) ); ?>"></p>
                    <p><label>Exam:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][exam]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_exam', true ) ); ?>"></p>
                    <p><label>Date:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][date]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_date', true ) ); ?>"></p>
                    <p><label>Question:</label><?php wp_editor( get_post_meta( $test->ID, '_test_question', true ), 'mtp_tests_' . $index . '_question', [ 'textarea_name' => 'mtp_tests[' . $index . '][question]' ] ); ?></p>
                    <p><label>Option 1:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option1]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option1', true ) ); ?>"></p>
                    <p><label>Option 2:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option2]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option2', true ) ); ?>"></p>
                    <p><label>Option 3:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option3]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option3', true ) ); ?>"></p>
                    <p><label>Option 4:</label><input type="text" name="mtp_tests[<?php echo $index; ?>][option4]" class="widefat" value="<?php echo esc_attr( get_post_meta( $test->ID, '_test_option4', true ) ); ?>"></p>
                    <p><label>Correct Options:</label><br>
                        <?php for($i=1; $i<=4; $i++): ?>
                            <label style="margin-right: 15px;">
                                <input type="checkbox" name="mtp_tests[<?php echo $index; ?>][correct_options][]" value="<?php echo $i; ?>" <?php checked( in_array( (string)$i, $correct_options, true ) ); ?>> Option <?php echo $i; ?>
                            </label>
                        <?php endfor; ?>
                    </p>
                    <p><label>Explanation:</label><?php wp_editor( get_post_meta( $test->ID, '_test_explanation', true ), 'mtp_tests_' . $index . '_explanation', [ 'textarea_name' => 'mtp_tests[' . $index . '][explanation]' ] ); ?></p>
                    <input type="hidden" name="mtp_tests[<?php echo $index; ?>][id]" value="<?php echo $test->ID; ?>">
                    <button type="button" class="button mtp-remove-test">Remove Test</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="mtp-add-test" class="button button-primary">Add Test</button>
    </div>
    <?php
}

function mtp_course_lessons_order_meta_box_html( $post ) {
    $lessons = get_posts(['post_type' => 'lesson', 'meta_key' => '_course_id', 'meta_value' => $post->ID, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
    echo '<ul id="mtp-sortable-lessons" class="mtp-sortable-list">';
    foreach ( $lessons as $lesson ) echo '<li class="ui-state-default" data-id="' . $lesson->ID . '"><span class="dashicons dashicons-move"></span> ' . esc_html( $lesson->post_title ) . '</li>';
    echo '</ul>';
    wp_nonce_field( 'mtp_update_order_nonce', 'mtp_order_nonce' );
}

function mtp_lesson_topics_order_meta_box_html( $post ) {
    $topics = get_posts(['post_type' => 'topic', 'meta_key' => '_lesson_id', 'meta_value' => $post->ID, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
    echo '<ul id="mtp-sortable-topics" class="mtp-sortable-list">';
    foreach ( $topics as $topic ) echo '<li class="ui-state-default" data-id="' . $topic->ID . '"><span class="dashicons dashicons-move"></span> ' . esc_html( $topic->post_title ) . '</li>';
    echo '</ul>';
    wp_nonce_field( 'mtp_update_order_nonce', 'mtp_order_nonce' );
}

function mtp_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['mtp_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['mtp_meta_box_nonce'], 'mtp_save_meta_box_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['mtp_english_slug'] ) ) {
        $slug = sanitize_title( $_POST['mtp_english_slug'] );
        update_post_meta( $post_id, '_english_slug', $slug );

        // Sync with the native WordPress slug
        if ( ! wp_is_post_revision( $post_id ) ) {
            remove_action( 'save_post', 'mtp_save_meta_box_data' );
            wp_update_post( [ 'ID' => $post_id, 'post_name' => $slug ] );
            add_action( 'save_post', 'mtp_save_meta_box_data' );
        }
    }

    if ( isset( $_POST['mcp_course_id'] ) ) update_post_meta( $post_id, '_course_id', absint( $_POST['mcp_course_id'] ) );
    if ( isset( $_POST['mcp_lesson_id'] ) ) update_post_meta( $post_id, '_lesson_id', absint( $_POST['mcp_lesson_id'] ) );
    if ( isset( $_POST['mcp_topic_id'] ) ) update_post_meta( $post_id, '_topic_id', absint( $_POST['mcp_topic_id'] ) );

    if ( get_post_type($post_id) == 'test' ) {
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

    if ( isset( $_POST['mtp_tests'] ) ) {
        remove_action( 'save_post', 'mtp_save_meta_box_data' );
        $existing_ids = get_posts(['post_type' => 'test', 'meta_key' => '_topic_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        $submitted_ids = [];
        foreach ( $_POST['mtp_tests'] as $test_data ) {
            $test_id = ! empty( $test_data['id'] ) ? absint( $test_data['id'] ) : 0;
            $question = wp_kses_post( $test_data['question'] );
            if ( empty( $question ) ) { if ( $test_id ) wp_delete_post( $test_id, true ); continue; }
            $post_arr = [ 'post_title' => wp_trim_words( $question, 10, '...' ), 'post_type' => 'test', 'post_status' => 'publish' ];
            if ( $test_id ) { $post_arr['ID'] = $test_id; wp_update_post( $post_arr ); } else { $test_id = wp_insert_post( $post_arr ); }
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
        foreach ( array_diff( $existing_ids, $submitted_ids ) as $deleted_id ) wp_delete_post( $deleted_id, true );
        add_action( 'save_post', 'mtp_save_meta_box_data' );
    }
}
add_action( 'save_post', 'mtp_save_meta_box_data' );
