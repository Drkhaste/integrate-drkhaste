<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_add_import_export_menu() {
    add_submenu_page( 'course_builder_page', 'ایمپورت آزمون (CSV)', 'ایمپورت آزمون', 'manage_options', 'mtp-csv-import', 'mtp_render_import_page' );
    add_submenu_page( 'course_builder_page', 'اکسپورت آزمون (CSV)', 'اکسپورت آزمون', 'manage_options', 'mtp-csv-export', 'mtp_render_export_page' );
}
add_action( 'admin_menu', 'mtp_add_import_export_menu' );

function mtp_render_import_page() {
    ?>
    <div class="wrap">
        <h1>ایمپورت از فایل CSV</h1>

        <?php if ( isset($_GET['status']) && $_GET['status'] === 'success' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p>تست‌ها با موفقیت وارد شدند!</p>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">

            <!-- بخش ۱: ایمپورت خودکار و کلی -->
            <div style="flex: 1; min-width: 350px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0; color: #111; font-size: 1.3rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">آپلود و ساخت خودکار تست‌ها و مباحث (پیشنهادی)</h2>
                <p>در این بخش کافیست فایل CSV نمونه را آپلود کنید تا تمامی مراحل ساخت خودکار کورس، درس، مبحث و تست‌ها کاملاً به صورت هوشمند و خودکار از روی فایل انجام گیرد.</p>

                <form id="mtp-auto-import-form" method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=mtp-csv-import'); ?>">
                    <?php wp_nonce_field( 'mtp_auto_import_action', 'mtp_auto_import_nonce' ); ?>
                    <p>
                        <label style="display: block; font-weight: bold; margin-bottom: 10px;">انتخاب فایل CSV:</label>
                        <input type="file" name="mtp_auto_csv_file" accept=".csv" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </p>
                    <p style="margin-top: 20px;">
                        <input type="submit" name="mtp_auto_import_submit" class="button button-primary button-large" value="شروع ایمپورت خودکار و هوشمند">
                    </p>
                </form>
            </div>

            <!-- بخش ۲: ایمپورت دستی به مبحث مشخص -->
            <div style="flex: 1; min-width: 350px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0; color: #111; font-size: 1.3rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">ایمپورت دستی (به مبحث مشخص شده)</h2>
                <p>اگر مایلید تست‌های فایل CSV مستقیماً به یک مبحث خاص که از قبل ساخته‌اید متصل شوند، از این بخش استفاده کنید.</p>

                <form id="mtp-import-form" method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=mtp-csv-import'); ?>">
                    <?php wp_nonce_field( 'mtp_import_export_action', 'mtp_import_export_nonce' ); ?>
                    <table class="form-table" style="margin-top: 0;">
                        <tr>
                            <th scope="row" style="padding: 10px 0; width: 100px;">کورس</th>
                            <td>
                                <select id="mtp-course" name="mcp_course_id" required style="width: 100%;">
                                    <option value="">— انتخاب کورس —</option>
                                    <?php foreach ( get_posts(['post_type' => 'course', 'numberposts' => -1]) as $c ) echo '<option value="'.$c->ID.'">'.$c->post_title.'</option>'; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" style="padding: 10px 0;">درس</th>
                            <td><select id="mtp-lesson" name="mcp_lesson_id" disabled required style="width: 100%;"><option value="">— انتخاب درس —</option></select></td>
                        </tr>
                        <tr>
                            <th scope="row" style="padding: 10px 0;">مبحث</th>
                            <td><select id="mtp-topic" name="mcp_topic_id" disabled required style="width: 100%;"><option value="">— انتخاب مبحث —</option></select></td>
                        </tr>
                        <tr>
                            <th scope="row" style="padding: 10px 0;">فایل CSV</th>
                            <td><input type="file" name="mtp_csv_file" accept=".csv" required style="width: 100%;"></td>
                        </tr>
                    </table>
                    <p style="margin-top: 20px;">
                        <input type="submit" name="mtp_import_submit" class="button button-secondary button-large" value="ایمپورت به مبحث مشخص شده">
                    </p>
                </form>
            </div>

        </div>

        <!-- بخش ۳: راهنمای کامل ساختار فایل CSV ورودی -->
        <div style="margin-top: 30px; background: #fff; padding: 25px; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); direction: rtl; text-align: right;">
            <h2 style="margin-top: 0; color: #111; font-size: 1.3rem; border-bottom: 1px solid #eee; padding-bottom: 10px; font-weight: bold;">راهنمای ساختار فایل CSV ورودی (اکسل)</h2>
            <p style="line-height: 1.6; font-size: 0.95rem; color: #444;">برای وارد کردن صحیح تست‌ها به صورت هوشمند، فایل CSV انتخابی شما باید حتماً دارای هدرهای انگلیسی زیر در اولین سطر خود باشد:</p>

            <table class="widefat striped" style="margin-top: 15px; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f9f9f9;">
                        <th style="font-weight: bold; padding: 10px;">نام ستون (هدر انگلیسی)</th>
                        <th style="font-weight: bold; padding: 10px;">توضیحات و فرمت داده</th>
                        <th style="font-weight: bold; padding: 10px;">مثال نمونه در فایل</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px;"><strong>Question Number</strong></td>
                        <td style="padding: 10px;">شماره یا شناسه سوال در آزمون (متن یا عدد)</td>
                        <td style="padding: 10px;"><code>1</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Exam</strong></td>
                        <td style="padding: 10px;">نام آزمون مربوطه (مثلاً پره انترنی)</td>
                        <td style="padding: 10px;"><code>پره انترنی</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Date</strong></td>
                        <td style="padding: 10px;">تاریخ برگزاری یا زمان دوره آزمون</td>
                        <td style="padding: 10px;"><code>آذر 1404</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Lesson</strong></td>
                        <td style="padding: 10px;">نام کورس/دوره اصلی (در صورت عدم وجود، خودکار ساخته می‌شود)</td>
                        <td style="padding: 10px;"><code>ریه</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Subject</strong></td>
                        <td style="padding: 10px;">نام درس و مبحث (در صورت عدم وجود، خودکار ساخته می‌شود)</td>
                        <td style="padding: 10px;"><code>سرفه</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Question Text</strong></td>
                        <td style="padding: 10px;">متن کامل سوال تستی</td>
                        <td style="padding: 10px;"><code>خانم 55 ساله با شکایت خلط شدید پشت حلق...</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Option 1</strong></td>
                        <td style="padding: 10px;">متن گزینه اول (الف)</td>
                        <td style="padding: 10px;"><code>بستری و آنتی بیوتیک تزریقی</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Option 2</strong></td>
                        <td style="padding: 10px;">متن گزینه دوم (ب)</td>
                        <td style="padding: 10px;"><code>کورتیکواسترویید استنشاقی و تزریقی</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Option 3</strong></td>
                        <td style="padding: 10px;">متن گزینه سوم (ج)</td>
                        <td style="padding: 10px;"><code>شربت دکسترومتورفان خوراکی</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Option 4</strong></td>
                        <td style="padding: 10px;">متن گزینه چهارم (د)</td>
                        <td style="padding: 10px;"><code>آنتی هیستامین و استرویید نازال</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Correct Options</strong></td>
                        <td style="padding: 10px;">شماره گزینه(های) صحیح از ۱ تا ۴. در صورت چندگزینه‌ای بودن با کاما جدا کنید.</td>
                        <td style="padding: 10px;"><code>4</code> یا <code>1,3</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Explanation</strong></td>
                        <td style="padding: 10px;">پاسخ تشریحی کامل و تحلیل علمی سوال</td>
                        <td style="padding: 10px;"><code>درمان سرفه مزمن با CXR طبیعی اغلب...</code></td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 15px; color: #d32f2f; font-weight: bold; line-height: 1.6; font-size: 0.95rem;">⚠️ توجه بسیار مهم: برای جلوگیری از به هم ریختگی کلمات فارسی، لطفا اطمینان حاصل کنید که فایل CSV انتخابی شما حتما با فرمت UTF-8 ذخیره (Save) شده باشد.</p>
        </div>

    </div>
    <?php
}

function mtp_render_export_page() {
    ?>
    <div class="wrap">
        <h1>اکسپورت CSV</h1>
        <form id="mtp-export-form" method="post">
            <?php wp_nonce_field( 'mtp_import_export_action', 'mtp_import_export_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">کورس</th>
                    <td>
                        <select id="mtp-course" name="mcp_course_id" required>
                            <option value="">— انتخاب کورس —</option>
                            <?php foreach ( get_posts(['post_type' => 'course', 'numberposts' => -1]) as $c ) echo '<option value="'.$c->ID.'">'.$c->post_title.'</option>'; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">درس</th>
                    <td><select id="mtp-lesson" name="mcp_lesson_id" disabled required><option value="">— انتخاب درس —</option></select></td>
                </tr>
                <tr>
                    <th scope="row">مبحث</th>
                    <td><select id="mtp-topic" name="mcp_topic_id" disabled required><option value="">— انتخاب مبحث —</option></select></td>
                </tr>
            </table>
            <input type="submit" name="mtp_export_submit" class="button button-primary" value="اکسپورت به CSV">
        </form>
    </div>
    <?php
}

function mtp_handle_export_request() {
    if ( ! isset( $_POST['mtp_export_submit'] ) || ! wp_verify_nonce( $_POST['mtp_import_export_nonce'], 'mtp_import_export_action' ) ) return;
    $topic_id = absint( $_POST['mcp_topic_id'] );
    $lesson_id = get_post_meta( $topic_id, '_mcp_lesson_id', true );
    $course_id = get_post_meta( $topic_id, '_mcp_course_id', true );

    $lesson_title = $lesson_id ? get_the_title( $lesson_id ) : '';
    $course_title = $course_id ? get_the_title( $course_id ) : '';

    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=tests-export-'.date('Y-m-d').'.csv' );
    $output = fopen( 'php://output', 'w' );
    fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );
    fputcsv( $output, [ 'Question Number', 'Exam', 'Date', 'Lesson', 'Subject', 'Question Text', 'Option 1', 'Option 2', 'Option 3', 'Option 4', 'Correct Options', 'Explanation' ] );
    $items = get_posts(['post_type' => 'mtp_test', 'meta_key' => '_mcp_topic_id', 'meta_value' => $topic_id, 'numberposts' => -1]);
    foreach ( $items as $item ) {
        fputcsv( $output, [
            get_post_meta( $item->ID, '_mtp_question_number', true ),
            get_post_meta( $item->ID, '_mtp_exam', true ),
            get_post_meta( $item->ID, '_mtp_date', true ),
            $course_title,
            $lesson_title,
            get_post_meta( $item->ID, '_mtp_question', true ),
            get_post_meta( $item->ID, '_mtp_option1', true ),
            get_post_meta( $item->ID, '_mtp_option2', true ),
            get_post_meta( $item->ID, '_mtp_option3', true ),
            get_post_meta( $item->ID, '_mtp_option4', true ),
            get_post_meta( $item->ID, '_mtp_correct_option', true ),
            get_post_meta( $item->ID, '_mtp_explanation', true )
        ] );
    }
    fclose( $output ); exit;
}
add_action( 'init', 'mtp_handle_export_request' );

function mtp_find_or_create_post( $title, $post_type, $parent_meta_key = '', $parent_meta_value = '' ) {
    $title = trim( $title );
    if ( empty( $title ) ) {
        return 0;
    }

    $args = [
        'post_type'      => $post_type,
        'title'          => $title,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ];
    if ( ! empty( $parent_meta_key ) ) {
        $args['meta_query'] = [
            [
                'key'   => $parent_meta_key,
                'value' => $parent_meta_value,
            ]
        ];
    }
    $posts = get_posts( $args );
    if ( ! empty( $posts ) ) {
        return $posts[0]->ID;
    }

    // Create the post
    $slug = sanitize_title( $title );
    $post_id = wp_insert_post( [
        'post_title'  => $title,
        'post_name'   => $slug,
        'post_type'   => $post_type,
        'post_status' => 'publish',
    ] );

    if ( $post_id && ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, '_mcp_english_slug', $slug );
        if ( ! empty( $parent_meta_key ) ) {
            update_post_meta( $post_id, $parent_meta_key, $parent_meta_value );
        }
        return $post_id;
    }
    return 0;
}

function mtp_handle_auto_import_request() {
    if ( ! isset( $_POST['mtp_auto_import_submit'] ) || ! wp_verify_nonce( $_POST['mtp_auto_import_nonce'], 'mtp_auto_import_action' ) ) return;
    if ( ! isset( $_FILES['mtp_auto_csv_file'] ) || $_FILES['mtp_auto_csv_file']['error'] !== UPLOAD_ERR_OK ) return;

    $filepath = $_FILES['mtp_auto_csv_file']['tmp_name'];
    setlocale( LC_ALL, 'en_US.UTF-8' );

    $handle = fopen( $filepath, 'r' );
    if ( ! $handle ) return;

    $header = null;
    while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
        if ( empty( $row ) ) {
            continue;
        }

        // Handle BOM
        if ( ! $header ) {
            $row[0] = preg_replace( '/^[\x{FEFF}\x{EF}\x{BB}\x{BF}]+/u', '', $row[0] );
            $header = array_map( 'trim', $row );
            $header = array_map( function( $h ) {
                return trim( $h, "\"' \t\n\r\0\x0B" );
            }, $header );
            continue;
        }

        if ( count( $row ) < count( $header ) ) {
            $row = array_pad( $row, count( $header ), '' );
        } elseif ( count( $row ) > count( $header ) ) {
            $row = array_slice( $row, 0, count( $header ) );
        }

        $data = array_combine( $header, $row );

        $question_number = isset( $data['Question Number'] ) ? trim( $data['Question Number'] ) : '';
        $exam            = isset( $data['Exam'] ) ? trim( $data['Exam'] ) : '';
        $date            = isset( $data['Date'] ) ? trim( $data['Date'] ) : '';
        $lesson_name     = isset( $data['Lesson'] ) ? trim( $data['Lesson'] ) : ''; // Course
        $subject_name    = isset( $data['Subject'] ) ? trim( $data['Subject'] ) : ''; // Lesson & Topic
        $question_text   = isset( $data['Question Text'] ) ? trim( $data['Question Text'] ) : '';
        $option1         = isset( $data['Option 1'] ) ? trim( $data['Option 1'] ) : '';
        $option2         = isset( $data['Option 2'] ) ? trim( $data['Option 2'] ) : '';
        $option3         = isset( $data['Option 3'] ) ? trim( $data['Option 3'] ) : '';
        $option4         = isset( $data['Option 4'] ) ? trim( $data['Option 4'] ) : '';
        $correct_options = isset( $data['Correct Options'] ) ? trim( $data['Correct Options'] ) : '';
        $explanation     = isset( $data['Explanation'] ) ? trim( $data['Explanation'] ) : '';

        if ( empty( $question_text ) ) {
            continue;
        }

        // 1. Course (from Lesson column)
        $course_id = mtp_find_or_create_post( $lesson_name, 'course' );
        if ( ! $course_id ) continue;

        // 2. Lesson (from Subject column)
        $lesson_id = mtp_find_or_create_post( $subject_name, 'lesson', '_mcp_course_id', $course_id );
        if ( ! $lesson_id ) continue;

        // 3. Topic (same as Subject)
        $topic_id = mtp_find_or_create_post( $subject_name, 'topic', '_mcp_lesson_id', $lesson_id );
        if ( ! $topic_id ) continue;
        update_post_meta( $topic_id, '_mcp_course_id', $course_id );

        // Duplicate Check
        $existing_test = get_posts( [
            'post_type'      => 'mtp_test',
            'meta_query'     => [
                [ 'key' => '_mcp_topic_id', 'value' => $topic_id ],
                [ 'key' => '_mtp_question', 'value' => wp_kses_post( $question_text ) ],
            ],
            'posts_per_page' => 1,
        ] );
        if ( ! empty( $existing_test ) ) {
            continue;
        }

        // Correct options
        $correct_options_parsed = [];
        if ( ! empty( $correct_options ) ) {
            $parts = explode( ',', $correct_options );
            foreach ( $parts as $part ) {
                $num = trim( $part );
                if ( in_array( $num, [ '1', '2', '3', '4' ], true ) ) {
                    $correct_options_parsed[] = (int) $num;
                }
            }
        }
        $correct_option_meta = implode( ',', $correct_options_parsed );

        // 4. Create Test CPT
        $test_title = wp_trim_words( $question_text, 10, '...' );
        $test_id = wp_insert_post( [
            'post_title'  => $test_title,
            'post_type'   => 'mtp_test',
            'post_status' => 'publish',
        ] );

        if ( $test_id && ! is_wp_error( $test_id ) ) {
            update_post_meta( $test_id, '_mcp_topic_id', $topic_id );
            update_post_meta( $test_id, '_mcp_lesson_id', $lesson_id );
            update_post_meta( $test_id, '_mcp_course_id', $course_id );
            update_post_meta( $test_id, '_mtp_identifier', sanitize_text_field( $question_number ) );
            update_post_meta( $test_id, '_mtp_question_number', sanitize_text_field( $question_number ) );
            update_post_meta( $test_id, '_mtp_exam', sanitize_text_field( $exam ) );
            update_post_meta( $test_id, '_mtp_date', sanitize_text_field( $date ) );
            update_post_meta( $test_id, '_mtp_question', wp_kses_post( $question_text ) );
            update_post_meta( $test_id, '_mtp_option1', sanitize_text_field( $option1 ) );
            update_post_meta( $test_id, '_mtp_option2', sanitize_text_field( $option2 ) );
            update_post_meta( $test_id, '_mtp_option3', sanitize_text_field( $option3 ) );
            update_post_meta( $test_id, '_mtp_option4', sanitize_text_field( $option4 ) );
            update_post_meta( $test_id, '_mtp_correct_option', $correct_option_meta );
            update_post_meta( $test_id, '_mtp_explanation', wp_kses_post( $explanation ) );
        }
    }
    fclose( $handle );

    wp_redirect( admin_url( 'admin.php?page=mtp-csv-import&status=success' ) ); exit;
}
add_action( 'init', 'mtp_handle_auto_import_request' );

function mtp_handle_import_request() {
    if ( ! isset( $_POST['mtp_import_submit'] ) || ! wp_verify_nonce( $_POST['mtp_import_export_nonce'], 'mtp_import_export_action' ) ) return;
    if ( ! isset( $_FILES['mtp_csv_file'] ) || $_FILES['mtp_csv_file']['error'] !== UPLOAD_ERR_OK ) return;
    $topic_id = absint( $_POST['mcp_topic_id'] );
    $lesson_id = get_post_meta( $topic_id, '_mcp_lesson_id', true );
    $course_id = get_post_meta( $topic_id, '_mcp_course_id', true );

    setlocale( LC_ALL, 'en_US.UTF-8' );
    if ( ( $handle = fopen( $_FILES['mtp_csv_file']['tmp_name'], 'r' ) ) !== false ) {
        $header = fgetcsv( $handle );
        if ( $header ) {
            $header[0] = preg_replace( '/^[\x{FEFF}\x{EF}\x{BB}\x{BF}]+/u', '', $header[0] );
            $header = array_map( 'trim', $header );
            $header = array_map( function( $h ) {
                return trim( $h, "\"' \t\n\r\0\x0B" );
            }, $header );
        }

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            if ( empty( $row ) ) continue;
            if ( count( $row ) < count( $header ) ) {
                $row = array_pad( $row, count( $header ), '' );
            } elseif ( count( $row ) > count( $header ) ) {
                $row = array_slice( $row, 0, count( $header ) );
            }
            $data = array_combine( $header, $row );

            $q_num = isset( $data['Question Number'] ) ? trim( $data['Question Number'] ) : ( isset( $data['identifier'] ) ? trim( $data['identifier'] ) : '' );
            $exam = isset( $data['Exam'] ) ? trim( $data['Exam'] ) : '';
            $date = isset( $data['Date'] ) ? trim( $data['Date'] ) : '';
            $question = isset( $data['Question Text'] ) ? trim( $data['Question Text'] ) : ( isset( $data['question'] ) ? trim( $data['question'] ) : '' );
            $opt1 = isset( $data['Option 1'] ) ? trim( $data['Option 1'] ) : ( isset( $data['option1'] ) ? trim( $data['option1'] ) : '' );
            $opt2 = isset( $data['Option 2'] ) ? trim( $data['Option 2'] ) : ( isset( $data['option2'] ) ? trim( $data['option2'] ) : '' );
            $opt3 = isset( $data['Option 3'] ) ? trim( $data['Option 3'] ) : ( isset( $data['option3'] ) ? trim( $data['option3'] ) : '' );
            $opt4 = isset( $data['Option 4'] ) ? trim( $data['Option 4'] ) : ( isset( $data['option4'] ) ? trim( $data['option4'] ) : '' );
            $correct_raw = isset( $data['Correct Options'] ) ? trim( $data['Correct Options'] ) : ( isset( $data['correct_option'] ) ? trim( $data['correct_option'] ) : '' );
            $exp = isset( $data['Explanation'] ) ? trim( $data['Explanation'] ) : ( isset( $data['explanation'] ) ? trim( $data['explanation'] ) : '' );

            if ( empty( $question ) ) continue;

            $existing_test = get_posts( [
                'post_type'      => 'mtp_test',
                'meta_query'     => [
                    [ 'key' => '_mcp_topic_id', 'value' => $topic_id ],
                    [ 'key' => '_mtp_question', 'value' => wp_kses_post( $question ) ],
                ],
                'posts_per_page' => 1,
            ] );
            if ( ! empty( $existing_test ) ) {
                continue;
            }

            $correct_options_parsed = [];
            if ( ! empty( $correct_raw ) ) {
                $parts = explode( ',', $correct_raw );
                foreach ( $parts as $part ) {
                    $num = trim( $part );
                    if ( in_array( $num, [ '1', '2', '3', '4' ], true ) ) {
                        $correct_options_parsed[] = (int) $num;
                    }
                }
            }
            $correct_option_meta = implode( ',', $correct_options_parsed );

            $post_id = wp_insert_post([
                'post_title' => wp_trim_words( $question, 10, '...' ),
                'post_type' => 'mtp_test',
                'post_status' => 'publish'
            ]);
            if ( $post_id ) {
                update_post_meta( $post_id, '_mcp_topic_id', $topic_id );
                update_post_meta( $post_id, '_mcp_lesson_id', $lesson_id );
                update_post_meta( $post_id, '_mcp_course_id', $course_id );
                update_post_meta( $post_id, '_mtp_identifier', sanitize_text_field( $q_num ) );
                update_post_meta( $post_id, '_mtp_question_number', sanitize_text_field( $q_num ) );
                update_post_meta( $post_id, '_mtp_exam', sanitize_text_field( $exam ) );
                update_post_meta( $post_id, '_mtp_date', sanitize_text_field( $date ) );
                update_post_meta( $post_id, '_mtp_question', wp_kses_post( $question ) );
                update_post_meta( $post_id, '_mtp_option1', sanitize_text_field( $opt1 ) );
                update_post_meta( $post_id, '_mtp_option2', sanitize_text_field( $opt2 ) );
                update_post_meta( $post_id, '_mtp_option3', sanitize_text_field( $opt3 ) );
                update_post_meta( $post_id, '_mtp_option4', sanitize_text_field( $opt4 ) );
                update_post_meta( $post_id, '_mtp_correct_option', $correct_option_meta );
                update_post_meta( $post_id, '_mtp_explanation', wp_kses_post( $exp ) );
            }
        }
        fclose( $handle );
    }
    wp_redirect( admin_url( 'admin.php?page=mtp-csv-import&status=success' ) ); exit;
}
add_action( 'init', 'mtp_handle_import_request' );
