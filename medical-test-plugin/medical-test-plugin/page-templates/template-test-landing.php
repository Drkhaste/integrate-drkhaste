<?php
/**
 * Template Name: Test Landing Page
 */

get_header();

// Check if we should load the Quiz Player
if ( isset( $_GET['view'] ) && $_GET['view'] === 'quiz' ) {
    $exam_filter   = isset( $_GET['exam'] ) ? sanitize_text_field( $_GET['exam'] ) : '';
    $date_filter   = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';
    $course_filter = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;

    $meta_query = [];
    $title_parts = [];

    if ( ! empty( $exam_filter ) ) {
        $meta_query[] = [ 'key' => '_mtp_exam', 'value' => $exam_filter ];
        $title_parts[] = $exam_filter;
    }
    if ( ! empty( $date_filter ) ) {
        $meta_query[] = [ 'key' => '_mtp_date', 'value' => $date_filter ];
        $title_parts[] = $date_filter;
    }
    if ( ! empty( $course_filter ) ) {
        $meta_query[] = [ 'key' => '_mtp_course_id', 'value' => $course_filter ];
        $title_parts[] = get_the_title( $course_filter );
    }

    $tests = get_posts([
        'post_type'   => 'mtp_test',
        'numberposts' => -1,
        'orderby'     => 'date',
        'order'       => 'ASC',
        'meta_query'  => $meta_query
    ]);

    $quiz_title = implode( ' - ', $title_parts );
    if ( empty( $quiz_title ) ) {
        $quiz_title = 'آزمون جامع پزشکان';
    }
    ?>
    <main class="container" style="direction: rtl; text-align: right; margin-top: 30px; margin-bottom: 50px;">
        <div class="breadcrumb-container" style="margin-bottom: 25px;">
            <div class="breadcrumb-item">
                <a href="<?php echo home_url('/test/'); ?>">صفحه اصلی آزمون‌ها</a>
            </div>
            <div class="breadcrumb-item active">
                <span><?php echo esc_html( $quiz_title ); ?></span>
            </div>
        </div>

        <div class="mtp-quiz-wrapper">
            <?php if ( ! empty( $tests ) ) : ?>
                <div class="mtp-quiz-progress">
                    سوال <span id="current-q-num">1</span> از <?php echo count($tests); ?>
                </div>

                <div class="mtp-test-container">
                    <?php foreach ( $tests as $index => $test ) :
                        $test_id = $test->ID;
                        $question = get_post_meta( $test_id, '_mtp_question', true );
                        $options = [
                            '1' => get_post_meta( $test_id, '_mtp_option1', true ),
                            '2' => get_post_meta( $test_id, '_mtp_option2', true ),
                            '3' => get_post_meta( $test_id, '_mtp_option3', true ),
                            '4' => get_post_meta( $test_id, '_mtp_option4', true ),
                        ];
                        $correct_answer = get_post_meta( $test_id, '_mtp_correct_option', true );
                        $explanation = get_post_meta( $test_id, '_mtp_explanation', true );

                        $q_num = get_post_meta( $test_id, '_mtp_question_number', true );
                        $exam = get_post_meta( $test_id, '_mtp_exam', true );
                        $date = get_post_meta( $test_id, '_mtp_date', true );
                    ?>
                        <div class="mtp-test-card" id="test-card-<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                            <?php if ( ! empty( $exam ) || ! empty( $date ) || ! empty( $q_num ) ) : ?>
                                <div class="mtp-test-meta-bar" style="display: flex; gap: 15px; margin-bottom: 15px; font-size: 0.9rem; color: #64748b; background: rgba(0,0,0,0.02); padding: 8px 12px; border-radius: 8px; flex-wrap: wrap;">
                                    <?php if ( ! empty( $exam ) ) : ?>
                                        <span><i class="fa-solid fa-graduation-cap"></i> <strong>آزمون:</strong> <?php echo esc_html( $exam ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $date ) ) : ?>
                                        <span><i class="fa-solid fa-calendar-days"></i> <strong>تاریخ:</strong> <?php echo esc_html( $date ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $q_num ) ) : ?>
                                        <span><i class="fa-solid fa-hashtag"></i> <strong>شماره سوال در دفترچه:</strong> <?php echo esc_html( $q_num ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="test-question">
                                <div class="q-label">سوال <?php echo ($index + 1); ?>:</div>
                                <?php echo apply_filters( 'the_content', $question ); ?>
                            </div>

                            <div class="test-options mtp-vertical-options">
                                <?php foreach ( $options as $key => $option ) : if ( empty( $option ) ) continue; ?>
                                    <label class="mtp-option-label" for="opt-<?php echo $index . '-' . $key; ?>">
                                        <input type="radio" name="option-<?php echo $index; ?>" id="opt-<?php echo $index . '-' . $key; ?>" value="<?php echo $key; ?>">
                                        <span class="mtp-option-box">
                                            <span class="mtp-option-letter"><?php
                                                $letters = ['1' => 'الف', '2' => 'ب', '3' => 'ج', '4' => 'د'];
                                                echo $letters[$key];
                                            ?></span>
                                            <span class="mtp-option-text"><?php echo esc_html( $option ); ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="mtp-actions">
                                <button class="btn-mtp-check" onclick="checkAnswer(<?php echo $index; ?>, '<?php echo esc_js( $correct_answer ); ?>')">بررسی پاسخ</button>
                            </div>

                            <div class="mtp-feedback-area" id="feedback-<?php echo $index; ?>" style="display:none;">
                                <div class="mtp-feedback-status"></div>
                                <div class="mtp-explanation-box">
                                    <strong>پاسخ تشریحی:</strong>
                                    <div class="mtp-explanation-content"><?php echo apply_filters( 'the_content', $explanation ); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mtp-navigation">
                    <button id="prev-btn" class="mtp-nav-btn" onclick="changeQuestion(-1)" disabled><i class="fa-solid fa-chevron-right"></i> قبلی</button>
                    <button id="next-btn" class="mtp-nav-btn" onclick="changeQuestion(1)">بعدی <i class="fa-solid fa-chevron-left"></i></button>
                </div>

            <?php else : ?>
                <div class="mtp-no-tests">تستی با فیلترهای مشخص شده یافت نشد.</div>
            <?php endif; ?>
        </div>
    </main>

    <style>
    .mtp-quiz-wrapper {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--primary, #0f766e);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .mtp-quiz-progress {
        text-align: center;
        margin-bottom: 25px;
        font-weight: bold;
        color: var(--primary, #0f766e);
        font-size: 1.1rem;
    }

    .mtp-test-card {
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .test-question {
        font-size: 1.25rem;
        line-height: 1.8;
        margin-bottom: 30px;
        color: var(--text-main, #1e293b);
        font-weight: 600;
    }

    .q-label {
        color: var(--primary, #0f766e);
        margin-bottom: 10px;
        font-weight: 900;
    }

    .mtp-vertical-options {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 40px;
    }

    .mtp-option-label {
        cursor: pointer;
        margin: 0;
    }

    .mtp-option-label input { display: none; }

    .mtp-option-box {
        display: flex;
        align-items: center;
        background: var(--bg-body, #f8fafc);
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        transition: all 0.2s ease;
        height: 100%;
    }

    .mtp-option-letter {
        width: 36px;
        height: 36px;
        background: #fff;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-left: 20px;
        font-weight: 900;
        color: var(--text-main, #1e293b);
        flex-shrink: 0;
    }

    .mtp-option-text {
        font-weight: 600;
        color: var(--text-main, #1e293b);
        font-size: 1.1rem;
    }

    .mtp-option-label:hover .mtp-option-box {
        border-color: var(--primary, #0f766e);
        background: rgba(15, 118, 110, 0.02);
    }

    .mtp-option-label input:checked + .mtp-option-box {
        border-color: var(--primary, #0f766e);
        background: rgba(15, 118, 110, 0.05);
    }

    .mtp-option-label input:checked + .mtp-option-box .mtp-option-letter {
        background: var(--primary, #0f766e);
        color: white;
    }

    .mtp-actions {
        margin-bottom: 20px;
    }

    .btn-mtp-check {
        background-color: var(--primary, #0f766e);
        color: white;
        border: none;
        padding: 12px 35px;
        border-radius: 12px;
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-mtp-check:hover { filter: brightness(110%); transform: translateY(-2px); }

    .mtp-feedback-area {
        margin-top: 25px;
        padding: 25px;
        border-radius: 12px;
        background: var(--bg-body, #f8fafc);
        border-right: 6px solid #64748b;
    }

    .mtp-feedback-status {
        font-weight: 900;
        margin-bottom: 15px;
        font-size: 1.15rem;
    }

    .mtp-feedback-status.correct { color: #22c55e; }
    .mtp-feedback-status.wrong { color: #ef4444; }

    .mtp-explanation-box strong {
        display: block;
        margin-bottom: 10px;
        color: var(--primary, #0f766e);
        font-size: 1.1rem;
    }

    .mtp-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }

    .mtp-nav-btn {
        background: var(--bg-body, #f8fafc);
        border: 1px solid var(--primary, #0f766e);
        color: var(--primary, #0f766e);
        padding: 10px 30px;
        border-radius: 12px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }

    .mtp-nav-btn:hover:not(:disabled) {
        background: var(--primary, #0f766e);
        color: white;
    }

    .mtp-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* Dynamic answer highlights - Legible in both Light & Dark modes */
    .mtp-option-label.is-correct .mtp-option-box {
        background: #dcfce7 !important;
        border-color: #22c55e !important;
        color: #15803d !important;
    }
    .mtp-option-label.is-correct .mtp-option-text {
        color: #15803d !important;
    }
    .mtp-option-label.is-correct .mtp-option-letter {
        background: #22c55e !important;
        color: white !important;
    }
    .mtp-option-label.is-wrong .mtp-option-box {
        background: #fee2e2 !important;
        border-color: #ef4444 !important;
        color: #b91c1c !important;
    }
    .mtp-option-label.is-wrong .mtp-option-text {
        color: #b91c1c !important;
    }
    .mtp-option-label.is-wrong .mtp-option-letter {
        background: #ef4444 !important;
        color: white !important;
    }

    .mtp-no-tests {
        text-align: center;
        padding: 50px;
        color: var(--text-main, #1e293b);
        font-weight: bold;
    }
    </style>

    <script>
    let currentQuestion = 0;
    const totalQuestions = <?php echo count($tests); ?>;

    function changeQuestion(delta) {
        const currentCard = document.getElementById(`test-card-${currentQuestion}`);
        currentCard.style.display = 'none';
        currentQuestion += delta;
        const nextCard = document.getElementById(`test-card-${currentQuestion}`);
        nextCard.style.display = 'block';

        document.getElementById('current-q-num').innerText = currentQuestion + 1;
        document.getElementById('prev-btn').disabled = (currentQuestion === 0);
        document.getElementById('next-btn').disabled = (currentQuestion === totalQuestions - 1);

        window.scrollTo({ top: document.querySelector('.mtp-quiz-wrapper').offsetTop - 50, behavior: 'smooth' });
    }

    function checkAnswer(index, correctAnswer) {
        const card = document.getElementById(`test-card-${index}`);
        const selected = card.querySelector('input:checked');
        const feedback = document.getElementById(`feedback-${index}`);
        const status = feedback.querySelector('.mtp-feedback-status');
        const labels = card.querySelectorAll('.mtp-option-label');

        if (!selected) {
            alert('لطفا یک گزینه را انتخاب کنید.');
            return;
        }

        const correctAnswers = String(correctAnswer).split(',').map(item => item.trim());

        labels.forEach(label => {
            const input = label.querySelector('input');
            label.classList.remove('is-correct', 'is-wrong');
            if (correctAnswers.includes(input.value)) {
                label.classList.add('is-correct');
            }
            input.disabled = true;
            label.style.pointerEvents = 'none';
        });

        if (correctAnswers.includes(selected.value)) {
            status.className = 'mtp-feedback-status correct';
            status.innerHTML = '<i class="fa-solid fa-circle-check"></i> آفرین! پاسخ شما صحیح بود.';
        } else {
            status.className = 'mtp-feedback-status wrong';
            status.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> متاسفانه پاسخ اشتباه بود.';
            selected.closest('.mtp-option-label').classList.add('is-wrong');
        }

        const checkButton = card.querySelector('.btn-mtp-check');
        if (checkButton) {
            checkButton.disabled = true;
            checkButton.style.opacity = '0.6';
            checkButton.style.cursor = 'not-allowed';
            checkButton.innerText = 'پاسخ بررسی شد';
        }

        feedback.style.display = 'block';
    }
    </script>
    <?php
} else {
    // ----------------------------------------
    // LANDING PAGE VIEW
    // ----------------------------------------
    global $wpdb;

    // Fetch distinct Exam + Date combinations from postmeta for mtp_test CPT
    $exam_dates = $wpdb->get_results( "
        SELECT DISTINCT
            m1.meta_value AS exam,
            m2.meta_value AS date
        FROM {$wpdb->postmeta} m1
        JOIN {$wpdb->postmeta} m2 ON m1.post_id = m2.post_id
        JOIN {$wpdb->posts} p ON m1.post_id = p.ID
        WHERE p.post_type = 'mtp_test'
          AND p.post_status = 'publish'
          AND m1.meta_key = '_mtp_exam'
          AND m2.meta_key = '_mtp_date'
          AND m1.meta_value != ''
          AND m2.meta_value != ''
    " );

    // Fetch all courses
    $all_courses = get_posts([
        'post_type'    => 'mtp_course',
        'numberposts' => -1,
        'orderby'      => 'title',
        'order'        => 'ASC'
    ]);
    ?>
    <main class="container" style="direction: rtl; text-align: right; margin-top: 30px; margin-bottom: 50px; font-family: inherit;">

        <!-- Header Section -->
        <div class="mtp-landing-header" style="text-align: center; margin-bottom: 40px; background: linear-gradient(135deg, var(--primary, #0f766e) 0%, #115e59 100%); padding: 45px 20px; border-radius: 16px; color: white; box-shadow: 0 4px 15px rgba(15, 118, 110, 0.15);">
            <h1 style="color: white; margin-top: 0; font-size: 2.2rem; font-weight: 900; margin-bottom: 15px;"><i class="fa-solid fa-graduation-cap"></i> پلتفرم شبیه‌ساز آزمون‌های تخصصی</h1>
            <p style="font-size: 1.15rem; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.8;">نوع سنجش خود را انتخاب کنید. می‌توانید آزمون‌های جامع را بر اساس «سال برگزاری» یا «موضوع کورس تخصصی» شروع کنید.</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="mtp-tabs-container" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 35px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
            <button class="mtp-tab-btn active" onclick="switchMtpTab('years')" id="tab-btn-years" style="background: none; border: none; padding: 10px 25px; font-size: 1.15rem; font-weight: bold; cursor: pointer; color: var(--primary, #0f766e); border-bottom: 3px solid var(--primary, #0f766e); transition: all 0.2s ease; font-family: inherit;">
                <i class="fa-solid fa-calendar-days"></i> سنجش بر اساس سال و دوره
            </button>
            <button class="mtp-tab-btn" onclick="switchMtpTab('courses')" id="tab-btn-courses" style="background: none; border: none; padding: 10px 25px; font-size: 1.15rem; font-weight: bold; cursor: pointer; color: #64748b; border-bottom: 3px solid transparent; transition: all 0.2s ease; font-family: inherit;">
                <i class="fa-solid fa-book-medical"></i> سنجش بر اساس کورس تخصصی
            </button>
        </div>

        <!-- Tab 1 Content: Years -->
        <div class="mtp-tab-content active-content" id="mtp-tab-years">
            <?php if ( ! empty( $exam_dates ) ) : ?>
                <div class="mtp-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
                    <?php foreach ( $exam_dates as $exam_date ) :
                        // Find how many tests are in this Year/Exam
                        $test_count = $wpdb->get_var( $wpdb->prepare( "
                            SELECT COUNT(p.ID)
                            FROM {$wpdb->posts} p
                            JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id
                            JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id
                            WHERE p.post_type = 'mtp_test' AND p.post_status = 'publish'
                              AND m1.meta_key = '_mtp_exam' AND m1.meta_value = %s
                              AND m2.meta_key = '_mtp_date' AND m2.meta_value = %s
                        ", $exam_date->exam, $exam_date->date ) );

                        // Find distinct courses that have questions in this Year/Exam
                        $courses_in_exam = $wpdb->get_results( $wpdb->prepare( "
                            SELECT DISTINCT m3.meta_value AS course_id
                            FROM {$wpdb->posts} p
                            JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id
                            JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id
                            JOIN {$wpdb->postmeta} m3 ON p.ID = m3.post_id
                            WHERE p.post_type = 'mtp_test' AND p.post_status = 'publish'
                              AND m1.meta_key = '_mtp_exam' AND m1.meta_value = %s
                              AND m2.meta_key = '_mtp_date' AND m2.meta_value = %s
                              AND m3.meta_key = '_mtp_course_id' AND m3.meta_value != ''
                        ", $exam_date->exam, $exam_date->date ) );
                    ?>
                        <div class="mtp-year-card" style="background: var(--bg-card, #fff); border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: all 0.3s ease; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                                <span style="font-weight: 900; font-size: 1.25rem; color: var(--primary, #0f766e);"><i class="fa-solid fa-folder-open"></i> <?php echo esc_html( $exam_date->exam ); ?></span>
                                <span style="background: #e0f2fe; color: #0369a1; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;"><?php echo esc_html( $exam_date->date ); ?></span>
                            </div>

                            <div style="margin-bottom: 20px; color: #64748b; font-size: 0.95rem;">
                                <span><i class="fa-solid fa-list-check"></i> کل سوالات سال: <strong><?php echo esc_html( $test_count ); ?> سوال</strong></span>
                            </div>

                            <div style="margin-top: auto; display: flex; flex-direction: column; gap: 10px;">
                                <!-- Button 1: Full Exam Quiz -->
                                <a href="<?php echo esc_url( add_query_arg([ 'view' => 'quiz', 'exam' => $exam_date->exam, 'date' => $exam_date->date ], home_url('/test/')) ); ?>" class="mtp-btn-landing-primary" style="display: block; text-align: center; background: var(--primary, #0f766e); color: white !important; font-weight: bold; padding: 12px; border-radius: 8px; text-decoration: none; transition: all 0.2s ease;">
                                    <i class="fa-solid fa-play"></i> آزمون جامع کل سوالات سال
                                </a>

                                <?php if ( ! empty( $courses_in_exam ) ) : ?>
                                    <!-- Collapsible Section: Course specific filters -->
                                    <details style="margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <summary style="padding: 10px; cursor: pointer; font-weight: bold; font-size: 0.9rem; color: #475569; display: flex; align-items: center; justify-content: space-between;">
                                            <span><i class="fa-solid fa-layer-group"></i> تفکیک بر اساس کورس تخصصی</span>
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                                        </summary>
                                        <div style="padding: 10px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 8px; background: #fafafa; max-height: 200px; overflow-y: auto;">
                                            <?php foreach ( $courses_in_exam as $cie ) :
                                                $cie_title = get_the_title( $cie->course_id );
                                                if ( empty( $cie_title ) ) continue;

                                                // Count tests in this specific course & exam/date combo
                                                $cie_count = $wpdb->get_var( $wpdb->prepare( "
                                                    SELECT COUNT(p.ID)
                                                    FROM {$wpdb->posts} p
                                                    JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id
                                                    JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id
                                                    JOIN {$wpdb->postmeta} m3 ON p.ID = m3.post_id
                                                    WHERE p.post_type = 'mtp_test' AND p.post_status = 'publish'
                                                      AND m1.meta_key = '_mtp_exam' AND m1.meta_value = %s
                                                      AND m2.meta_key = '_mtp_date' AND m2.meta_value = %s
                                                      AND m3.meta_key = '_mtp_course_id' AND m3.meta_value = %d
                                                ", $exam_date->exam, $exam_date->date, $cie->course_id ) );
                                            ?>
                                                <a href="<?php echo esc_url( add_query_arg([ 'view' => 'quiz', 'exam' => $exam_date->exam, 'date' => $exam_date->date, 'course_id' => $cie->course_id ], home_url('/test/')) ); ?>" style="display: flex; justify-content: space-between; align-items: center; color: #1e293b !important; text-decoration: none; padding: 8px 10px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem; font-weight: bold; transition: all 0.2s ease;" class="mtp-sub-course-link">
                                                    <span><i class="fa-solid fa-chevron-left" style="font-size:0.75rem; color: var(--primary, #0f766e);"></i> <?php echo esc_html( $cie_title ); ?></span>
                                                    <span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem;"><?php echo esc_html($cie_count); ?> تست</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div style="text-align: center; padding: 50px 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; color: #64748b;">
                    <i class="fa-regular fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <strong style="font-size: 1.1rem; display: block;">هیچ آزمون سالانه‌ای یافت نشد.</strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 2 Content: Courses -->
        <div class="mtp-tab-content" id="mtp-tab-courses" style="display: none;">
            <?php if ( ! empty( $all_courses ) ) : ?>
                <div class="mtp-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                    <?php foreach ( $all_courses as $course ) :
                        // Fetch total questions for this Course across all exams/dates
                        $course_test_count = $wpdb->get_var( $wpdb->prepare( "
                            SELECT COUNT(p.ID)
                            FROM {$wpdb->posts} p
                            JOIN {$wpdb->postmeta} m ON p.ID = m.post_id
                            WHERE p.post_type = 'mtp_test' AND p.post_status = 'publish'
                              AND m.meta_key = '_mtp_course_id' AND m.meta_value = %d
                        ", $course->ID ) );
                    ?>
                        <div class="mtp-course-card" style="background: var(--bg-card, #fff); border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <div style="width: 55px; height: 55px; background: rgba(15, 118, 110, 0.08); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: var(--primary, #0f766e); font-size: 1.4rem;">
                                    <i class="fa-solid fa-stethoscope"></i>
                                </div>
                                <h3 style="margin: 0 0 10px; font-size: 1.2rem; font-weight: bold; color: var(--text-main, #1e293b);"><?php echo esc_html( $course->post_title ); ?></h3>
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;"><?php echo esc_html( $course_test_count ); ?> تست موجود</span>
                            </div>

                            <a href="<?php echo esc_url( add_query_arg([ 'view' => 'quiz', 'course_id' => $course->ID ], home_url('/test/')) ); ?>" class="mtp-btn-landing-secondary" style="display: block; background: #e2e8f0; color: #475569 !important; font-weight: bold; padding: 10px; border-radius: 8px; text-decoration: none; transition: all 0.2s ease;">
                                شروع آزمون جامع کورس
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div style="text-align: center; padding: 50px 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; color: #64748b;">
                    <i class="fa-regular fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <strong style="font-size: 1.1rem; display: block;">هیچ کورس تخصصی ثبت نشده است.</strong>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <style>
    .mtp-tab-btn {
        transition: all 0.3s ease;
    }
    .mtp-tab-btn:hover {
        color: var(--primary, #0f766e) !important;
    }
    .mtp-year-card:hover,
    .mtp-course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.07) !important;
        border-color: var(--primary, #0f766e) !important;
    }
    .mtp-sub-course-link:hover {
        background: rgba(15, 118, 110, 0.03) !important;
        border-color: var(--primary, #0f766e) !important;
    }
    .mtp-btn-landing-primary:hover {
        filter: brightness(110%);
    }
    .mtp-btn-landing-secondary:hover {
        background: var(--primary, #0f766e) !important;
        color: white !important;
    }
    </style>

    <script>
    function switchMtpTab(tabName) {
        // Toggle Buttons
        const tabYearsBtn = document.getElementById('tab-btn-years');
        const tabCoursesBtn = document.getElementById('tab-btn-courses');

        // Toggle Contents
        const contentYears = document.getElementById('mtp-tab-years');
        const contentCourses = document.getElementById('mtp-tab-courses');

        if (tabName === 'years') {
            tabYearsBtn.classList.add('active');
            tabYearsBtn.style.color = 'var(--primary, #0f766e)';
            tabYearsBtn.style.borderBottom = '3px solid var(--primary, #0f766e)';

            tabCoursesBtn.classList.remove('active');
            tabCoursesBtn.style.color = '#64748b';
            tabCoursesBtn.style.borderBottom = '3px solid transparent';

            contentYears.style.display = 'grid';
            contentCourses.style.display = 'none';
        } else {
            tabCoursesBtn.classList.add('active');
            tabCoursesBtn.style.color = 'var(--primary, #0f766e)';
            tabCoursesBtn.style.borderBottom = '3px solid var(--primary, #0f766e)';

            tabYearsBtn.classList.remove('active');
            tabYearsBtn.style.color = '#64748b';
            tabYearsBtn.style.borderBottom = '3px solid transparent';

            contentCourses.style.display = 'grid';
            contentYears.style.display = 'none';
        }
    }
    </script>
    <?php
}
get_footer();
