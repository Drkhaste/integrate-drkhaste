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
        $meta_query[] = [ 'key' => '_test_exam', 'value' => $exam_filter ];
        $title_parts[] = $exam_filter;
    }
    if ( ! empty( $date_filter ) ) {
        $meta_query[] = [ 'key' => '_test_date', 'value' => $date_filter ];
        $title_parts[] = $date_filter;
    }
    if ( ! empty( $course_filter ) ) {
        $meta_query[] = [ 'key' => '_course_id', 'value' => $course_filter ];
        $title_parts[] = get_the_title( $course_filter );
    }

    $tests = get_posts([
        'post_type'   => 'test',
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
    <main class="container" style="direction: rtl; text-align: right; margin-top: 25px; margin-bottom: 60px;">
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

                <!-- Progress Bar -->
                <div class="mtp-progress-bar-container">
                    <div class="mtp-progress-info">
                        <span><i class="fa-solid fa-list-check"></i> سوال <strong id="current-q-num">1</strong> از <?php echo count($tests); ?></span>
                        <span id="mtp-progress-percent">0%</span>
                    </div>
                    <div class="mtp-progress-track">
                        <div class="mtp-progress-fill" id="mtp-progress-fill" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Question Palette Drawer / Grid -->
                <div class="mtp-palette-box" id="mtp-palette-box">
                    <div class="mtp-palette-header">
                        <span><i class="fa-solid fa-grid-2"></i> پالت شماره سوالات (پرش سریع)</span>
                        <button type="button" class="mtp-palette-close-btn" onclick="togglePaletteDrawer()"><i class="fa-solid fa-xmark"></i> بستن</button>
                    </div>
                    <div class="mtp-palette-grid">
                        <?php foreach ($tests as $i => $t) : ?>
                            <button type="button" class="mtp-palette-btn <?php echo $i === 0 ? 'is-current' : ''; ?>" id="palette-btn-<?php echo $i; ?>" onclick="jumpToQuestion(<?php echo $i; ?>)">
                                <?php echo ($i + 1); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="mtp-palette-legend">
                        <span class="legend-item"><span class="badge unanswered"></span> دیده‌نشده</span>
                        <span class="legend-item"><span class="badge answered"></span> انتخاب‌شده</span>
                        <span class="legend-item"><span class="badge correct"></span> صحیح</span>
                        <span class="legend-item"><span class="badge wrong"></span> نادرست</span>
                    </div>
                </div>

                <!-- Mobile Trigger for Question Palette -->
                <div class="mtp-mobile-palette-bar">
                    <button type="button" class="btn-mtp-palette-trigger" onclick="togglePaletteDrawer()">
                        <i class="fa-solid fa-border-all"></i> پالت شماره سوالات
                    </button>
                </div>

                <!-- Question Container -->
                <div class="mtp-test-container">
                    <?php foreach ( $tests as $index => $test ) :
                        $test_id = $test->ID;
                        $question = get_post_meta( $test_id, '_test_question', true );
                        $options = [
                            '1' => get_post_meta( $test_id, '_test_option1', true ),
                            '2' => get_post_meta( $test_id, '_test_option2', true ),
                            '3' => get_post_meta( $test_id, '_test_option3', true ),
                            '4' => get_post_meta( $test_id, '_test_option4', true ),
                        ];
                        $correct_answer = get_post_meta( $test_id, '_test_correct_option', true );
                        $explanation = get_post_meta( $test_id, '_test_explanation', true );

                        $q_num = get_post_meta( $test_id, '_test_question_number', true );
                        $exam = get_post_meta( $test_id, '_test_exam', true );
                        $date = get_post_meta( $test_id, '_test_date', true );
                    ?>
                        <div class="mtp-test-card" id="test-card-<?php echo $index; ?>" data-correct="<?php echo esc_attr($correct_answer); ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                            <?php if ( ! empty( $exam ) || ! empty( $date ) || ! empty( $q_num ) ) : ?>
                                <div class="mtp-test-meta-bar">
                                    <?php if ( ! empty( $exam ) ) : ?>
                                        <span><i class="fa-solid fa-graduation-cap"></i> <strong>آزمون:</strong> <?php echo esc_html( $exam ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $date ) ) : ?>
                                        <span><i class="fa-solid fa-calendar-days"></i> <strong>تاریخ:</strong> <?php echo esc_html( $date ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $q_num ) ) : ?>
                                        <span><i class="fa-solid fa-hashtag"></i> <strong>شماره دفترچه:</strong> <?php echo esc_html( $q_num ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="test-question">
                                <div class="q-label"><i class="fa-solid fa-circle-question"></i> سوال <?php echo ($index + 1); ?>:</div>
                                <div class="q-text"><?php echo apply_filters( 'the_content', $question ); ?></div>
                            </div>

                            <div class="test-options mtp-vertical-options">
                                <?php foreach ( $options as $key => $option ) : if ( empty( $option ) ) continue; ?>
                                    <label class="mtp-option-label" for="opt-<?php echo $index . '-' . $key; ?>">
                                        <input type="radio" name="option-<?php echo $index; ?>" id="opt-<?php echo $index . '-' . $key; ?>" value="<?php echo $key; ?>" onchange="onOptionSelected(<?php echo $index; ?>)">
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
                                <button class="btn-mtp-check" onclick="checkAnswer(<?php echo $index; ?>, '<?php echo esc_js( $correct_answer ); ?>')">
                                    <i class="fa-solid fa-check-double"></i> بررسی پاسخ
                                </button>
                            </div>

                            <div class="mtp-feedback-area" id="feedback-<?php echo $index; ?>" style="display:none;">
                                <div class="mtp-feedback-status"></div>
                                <div class="mtp-explanation-box">
                                    <strong><i class="fa-solid fa-book-open-reader"></i> پاسخ تشریحی:</strong>
                                    <div class="mtp-explanation-content"><?php echo apply_filters( 'the_content', $explanation ); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Controls -->
                <div class="mtp-navigation">
                    <button id="prev-btn" class="mtp-nav-btn" onclick="changeQuestion(-1)" disabled>
                        <i class="fa-solid fa-chevron-right"></i> قبلی
                    </button>
                    <div class="mtp-shortcuts-tip" style="font-size: 0.85rem; color: #64748b; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-keyboard"></i> <span>میانبر کیبورد: کلید ۱ تا ۴ (انتخاب) | Enter (بررسی) | جهت‌نماها (قبلی/بعدی)</span>
                    </div>
                    <button id="next-btn" class="mtp-nav-btn" onclick="changeQuestion(1)">
                        بعدی <i class="fa-solid fa-chevron-left"></i>
                    </button>
                </div>

                <!-- Sticky Mobile Bottom Bar -->
                <div class="mtp-sticky-mobile-nav">
                    <button id="mobile-prev-btn" class="mtp-mobile-btn" onclick="changeQuestion(-1)" disabled>
                        <i class="fa-solid fa-chevron-right"></i> قبلی
                    </button>
                    <button id="mobile-check-btn" class="mtp-mobile-btn primary" onclick="triggerMobileCheck()">
                        <i class="fa-solid fa-circle-check"></i> بررسی
                    </button>
                    <button id="mobile-next-btn" class="mtp-mobile-btn" onclick="changeQuestion(1)">
                        بعدی <i class="fa-solid fa-chevron-left"></i>
                    </button>
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
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        position: relative;
    }

    /* Progress Bar Styles */
    .mtp-progress-bar-container {
        margin-bottom: 25px;
        background: var(--bg-body, #f8fafc);
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .mtp-progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        color: var(--primary, #0f766e);
        margin-bottom: 10px;
        font-size: 1rem;
    }
    .mtp-progress-track {
        width: 100%;
        height: 10px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    .mtp-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary, #0f766e), #14b8a6);
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    /* Palette Styles */
    .mtp-palette-box {
        background: var(--bg-body, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .mtp-palette-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        color: var(--primary, #0f766e);
        margin-bottom: 15px;
        font-size: 1.05rem;
    }
    .mtp-palette-close-btn {
        display: none;
        background: none;
        border: none;
        color: #ef4444;
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
    }
    .mtp-palette-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(42px, 1fr));
        gap: 10px;
        margin-bottom: 15px;
        max-height: 220px;
        overflow-y: auto;
        padding: 5px;
    }
    .mtp-palette-btn {
        height: 42px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-weight: bold;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: inherit;
    }
    .mtp-palette-btn:hover {
        border-color: var(--primary, #0f766e);
        color: var(--primary, #0f766e);
    }
    .mtp-palette-btn.is-current {
        border: 2px solid var(--primary, #0f766e) !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.2);
        font-weight: 900;
    }
    .mtp-palette-btn.is-answered {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #38bdf8;
    }
    .mtp-palette-btn.is-correct {
        background: #dcfce7 !important;
        color: #15803d !important;
        border-color: #22c55e !important;
    }
    .mtp-palette-btn.is-wrong {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: #ef4444 !important;
    }

    .mtp-palette-legend {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #64748b;
        flex-wrap: wrap;
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .legend-item .badge {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        display: inline-block;
    }
    .legend-item .badge.unanswered { background: #ffffff; border: 1px solid #cbd5e1; }
    .legend-item .badge.answered { background: #e0f2fe; border: 1px solid #38bdf8; }
    .legend-item .badge.correct { background: #dcfce7; border: 1px solid #22c55e; }
    .legend-item .badge.wrong { background: #fee2e2; border: 1px solid #ef4444; }

    .mtp-mobile-palette-bar {
        display: none;
        margin-bottom: 20px;
    }
    .btn-mtp-palette-trigger {
        width: 100%;
        background: var(--bg-body, #f8fafc);
        border: 1px solid var(--primary, #0f766e);
        color: var(--primary, #0f766e);
        padding: 10px;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.95rem;
    }

    /* Question & Option Styles with Theme Font Binding */
    .test-question {
        font-family: var(--current-font, inherit);
        font-size: calc(var(--current-font-size, 16px) * 1.15);
        line-height: 1.8;
        margin-bottom: 30px;
        color: var(--text-main, #1e293b);
        font-weight: 600;
    }
    .q-label {
        color: var(--primary, #0f766e);
        margin-bottom: 12px;
        font-weight: 900;
        font-size: 1.1em;
    }

    .mtp-test-meta-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #64748b;
        background: rgba(0,0,0,0.02);
        padding: 10px 14px;
        border-radius: 10px;
        flex-wrap: wrap;
        border: 1px solid #f1f5f9;
    }

    .mtp-vertical-options {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 35px;
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
        padding: 16px 20px;
        transition: all 0.2s ease;
    }

    .mtp-option-letter {
        width: 38px;
        height: 38px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-left: 18px;
        font-weight: 900;
        color: var(--text-main, #1e293b);
        flex-shrink: 0;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .mtp-option-text {
        font-family: var(--current-font, inherit);
        font-size: var(--current-font-size, 1.05rem);
        font-weight: 600;
        color: var(--text-main, #1e293b);
        line-height: 1.7;
    }

    .mtp-option-label:hover .mtp-option-box {
        border-color: var(--primary, #0f766e);
        background: rgba(15, 118, 110, 0.03);
    }

    .mtp-option-label input:checked + .mtp-option-box {
        border-color: var(--primary, #0f766e);
        background: rgba(15, 118, 110, 0.06);
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
    }

    .mtp-option-label input:checked + .mtp-option-box .mtp-option-letter {
        background: var(--primary, #0f766e);
        color: white;
        border-color: var(--primary, #0f766e);
    }

    .mtp-actions {
        margin-bottom: 25px;
    }

    .btn-mtp-check {
        background-color: var(--primary, #0f766e);
        color: white;
        border: none;
        padding: 13px 36px;
        border-radius: 12px;
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.3s ease;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-mtp-check:hover { filter: brightness(110%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 118, 110, 0.2); }

    .mtp-feedback-area {
        margin-top: 25px;
        padding: 25px;
        border-radius: 12px;
        background: var(--bg-body, #f8fafc);
        border-right: 6px solid #64748b;
        animation: fadeIn 0.3s ease;
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

    .mtp-explanation-content {
        font-family: var(--current-font, inherit);
        font-size: var(--current-font-size, 1.05rem);
        line-height: 1.8;
    }

    .mtp-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        font-size: 1rem;
    }

    .mtp-nav-btn:hover:not(:disabled) {
        background: var(--primary, #0f766e);
        color: white;
    }

    .mtp-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* Dynamic Answer Highlights */
    .mtp-option-label.is-correct .mtp-option-box {
        background: #dcfce7 !important;
        border-color: #22c55e !important;
        color: #15803d !important;
    }
    .mtp-option-label.is-correct .mtp-option-text { color: #15803d !important; }
    .mtp-option-label.is-correct .mtp-option-letter {
        background: #22c55e !important;
        color: white !important;
        border-color: #22c55e !important;
    }
    .mtp-option-label.is-wrong .mtp-option-box {
        background: #fee2e2 !important;
        border-color: #ef4444 !important;
        color: #b91c1c !important;
    }
    .mtp-option-label.is-wrong .mtp-option-text { color: #b91c1c !important; }
    .mtp-option-label.is-wrong .mtp-option-letter {
        background: #ef4444 !important;
        color: white !important;
        border-color: #ef4444 !important;
    }

    /* Sticky Mobile Bottom Bar */
    .mtp-sticky-mobile-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--bg-card, #ffffff);
        border-top: 1px solid #e2e8f0;
        padding: 10px 15px;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.08);
        z-index: 999;
        justify-content: space-between;
        gap: 10px;
    }
    .mtp-mobile-btn {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid var(--primary, #0f766e);
        background: var(--bg-body, #f8fafc);
        color: var(--primary, #0f766e);
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.9rem;
    }
    .mtp-mobile-btn.primary {
        background: var(--primary, #0f766e);
        color: white;
    }
    .mtp-mobile-btn:disabled {
        opacity: 0.4;
    }

    @media (max-width: 768px) {
        .mtp-mobile-palette-bar { display: block; }
        .mtp-shortcuts-tip { display: none !important; }
        .mtp-palette-box {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 1000;
            background: var(--bg-card, #ffffff);
            border-radius: 0;
            overflow-y: auto;
        }
        .mtp-palette-box.active-drawer { display: block; }
        .mtp-palette-close-btn { display: block; }
        .mtp-sticky-mobile-nav { display: flex; }
        .mtp-navigation { margin-bottom: 50px; }
    }
    </style>

    <script>
    let currentQuestion = 0;
    const totalQuestions = <?php echo count($tests); ?>;
    const userAnswers = {}; // tracks selected answer for each q
    const checkedStatus = {}; // tracks check status for each q

    function updateProgress() {
        document.getElementById('current-q-num').innerText = currentQuestion + 1;
        const answeredCount = Object.keys(userAnswers).length;
        const percent = Math.round(((currentQuestion + 1) / totalQuestions) * 100);

        const fillEl = document.getElementById('mtp-progress-fill');
        const percentEl = document.getElementById('mtp-progress-percent');
        if (fillEl) fillEl.style.width = percent + '%';
        if (percentEl) percentEl.innerText = percent + '%';
    }

    function updatePaletteButtons() {
        for (let i = 0; i < totalQuestions; i++) {
            const btn = document.getElementById(`palette-btn-${i}`);
            if (!btn) continue;

            btn.classList.remove('is-current');
            if (i === currentQuestion) {
                btn.classList.add('is-current');
            }
        }
    }

    function changeQuestion(delta) {
        if (currentQuestion + delta < 0 || currentQuestion + delta >= totalQuestions) return;

        const currentCard = document.getElementById(`test-card-${currentQuestion}`);
        if (currentCard) currentCard.style.display = 'none';

        currentQuestion += delta;

        const nextCard = document.getElementById(`test-card-${currentQuestion}`);
        if (nextCard) nextCard.style.display = 'block';

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const mobPrevBtn = document.getElementById('mobile-prev-btn');
        const mobNextBtn = document.getElementById('mobile-next-btn');

        if (prevBtn) prevBtn.disabled = (currentQuestion === 0);
        if (nextBtn) nextBtn.disabled = (currentQuestion === totalQuestions - 1);
        if (mobPrevBtn) mobPrevBtn.disabled = (currentQuestion === 0);
        if (mobNextBtn) mobNextBtn.disabled = (currentQuestion === totalQuestions - 1);

        updateProgress();
        updatePaletteButtons();

        window.scrollTo({ top: document.querySelector('.mtp-quiz-wrapper').offsetTop - 60, behavior: 'smooth' });
    }

    function jumpToQuestion(index) {
        if (index < 0 || index >= totalQuestions) return;

        const currentCard = document.getElementById(`test-card-${currentQuestion}`);
        if (currentCard) currentCard.style.display = 'none';

        currentQuestion = index;

        const targetCard = document.getElementById(`test-card-${currentQuestion}`);
        if (targetCard) targetCard.style.display = 'block';

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const mobPrevBtn = document.getElementById('mobile-prev-btn');
        const mobNextBtn = document.getElementById('mobile-next-btn');

        if (prevBtn) prevBtn.disabled = (currentQuestion === 0);
        if (nextBtn) nextBtn.disabled = (currentQuestion === totalQuestions - 1);
        if (mobPrevBtn) mobPrevBtn.disabled = (currentQuestion === 0);
        if (mobNextBtn) mobNextBtn.disabled = (currentQuestion === totalQuestions - 1);

        updateProgress();
        updatePaletteButtons();

        // Close palette drawer on mobile if open
        const paletteBox = document.getElementById('mtp-palette-box');
        if (paletteBox && paletteBox.classList.contains('active-drawer')) {
            paletteBox.classList.remove('active-drawer');
        }

        window.scrollTo({ top: document.querySelector('.mtp-quiz-wrapper').offsetTop - 60, behavior: 'smooth' });
    }

    function onOptionSelected(index) {
        userAnswers[index] = true;
        const paletteBtn = document.getElementById(`palette-btn-${index}`);
        if (paletteBtn && !checkedStatus[index]) {
            paletteBtn.classList.add('is-answered');
        }
    }

    function checkAnswer(index, correctAnswer) {
        const card = document.getElementById(`test-card-${index}`);
        const selected = card.querySelector('input:checked');
        const feedback = document.getElementById(`feedback-${index}`);
        const status = feedback.querySelector('.mtp-feedback-status');
        const labels = card.querySelectorAll('.mtp-option-label');
        const paletteBtn = document.getElementById(`palette-btn-${index}`);

        if (!selected) {
            alert('لطفاً ابتدا یک گزینه را انتخاب کنید.');
            return;
        }

        checkedStatus[index] = true;
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
            if (paletteBtn) {
                paletteBtn.classList.remove('is-answered', 'is-wrong');
                paletteBtn.classList.add('is-correct');
            }
        } else {
            status.className = 'mtp-feedback-status wrong';
            status.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> متاسفانه پاسخ اشتباه بود.';
            selected.closest('.mtp-option-label').classList.add('is-wrong');
            if (paletteBtn) {
                paletteBtn.classList.remove('is-answered', 'is-correct');
                paletteBtn.classList.add('is-wrong');
            }
        }

        const checkButton = card.querySelector('.btn-mtp-check');
        if (checkButton) {
            checkButton.disabled = true;
            checkButton.style.opacity = '0.6';
            checkButton.style.cursor = 'not-allowed';
            checkButton.innerHTML = '<i class="fa-solid fa-check"></i> پاسخ بررسی شد';
        }

        feedback.style.display = 'block';
    }

    function triggerMobileCheck() {
        const card = document.getElementById(`test-card-${currentQuestion}`);
        if (!card) return;
        const correct = card.getAttribute('data-correct');
        checkAnswer(currentQuestion, correct);
    }

    function togglePaletteDrawer() {
        const paletteBox = document.getElementById('mtp-palette-box');
        if (paletteBox) paletteBox.classList.toggle('active-drawer');
    }

    // Keyboard Shortcuts Listener
    document.addEventListener('keydown', (e) => {
        // Ignore keydown if user is typing in an input/textarea
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

        const card = document.getElementById(`test-card-${currentQuestion}`);
        if (!card) return;

        // Keys 1, 2, 3, 4
        if (['1', '2', '3', '4'].includes(e.key)) {
            const optionInput = card.querySelector(`input[value="${e.key}"]`);
            if (optionInput && !optionInput.disabled) {
                optionInput.checked = true;
                onOptionSelected(currentQuestion);
            }
        } else if (e.key === 'Enter') {
            const correct = card.getAttribute('data-correct');
            checkAnswer(currentQuestion, correct);
        } else if (e.key === 'ArrowLeft') {
            changeQuestion(1);
        } else if (e.key === 'ArrowRight') {
            changeQuestion(-1);
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateProgress();
        updatePaletteButtons();
    });
    </script>
    <?php
} else {
    // ----------------------------------------
    // LANDING PAGE VIEW
    // ----------------------------------------
    global $wpdb;

    // Fetch distinct Exam + Date combinations from postmeta for test CPT
    $exam_dates = $wpdb->get_results( "
        SELECT DISTINCT
            m1.meta_value AS exam,
            m2.meta_value AS date
        FROM {$wpdb->postmeta} m1
        JOIN {$wpdb->postmeta} m2 ON m1.post_id = m2.post_id
        JOIN {$wpdb->posts} p ON m1.post_id = p.ID
        WHERE p.post_type = 'test'
          AND p.post_status = 'publish'
          AND m1.meta_key = '_test_exam'
          AND m2.meta_key = '_test_date'
          AND m1.meta_value != ''
          AND m2.meta_value != ''
    " );

    // Fetch all courses
    $all_courses = get_posts([
        'post_type'    => 'course',
        'numberposts' => -1,
        'orderby'      => 'title',
        'order'        => 'ASC'
    ]);
    ?>
    <main class="container" style="direction: rtl; text-align: right; margin-top: 30px; margin-bottom: 50px; font-family: inherit;">

        <!-- Header Section -->
        <div class="mtp-landing-header" style="text-align: center; margin-bottom: 30px; background: linear-gradient(135deg, var(--primary, #0f766e) 0%, #115e59 100%); padding: 40px 20px; border-radius: 16px; color: white; box-shadow: 0 4px 15px rgba(15, 118, 110, 0.15);">
            <h1 style="color: white; margin-top: 0; font-size: 2.2rem; font-weight: 900; margin-bottom: 15px;"><i class="fa-solid fa-graduation-cap"></i> پلتفرم شبیه‌ساز آزمون‌های تخصصی</h1>
            <p style="font-size: 1.15rem; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.8;">ابتدا نوع یا دوره آزمون خود را انتخاب کنید، سپس بر اساس «سال برگزاری» یا «موضوع کورس تخصصی» شروع نمایید.</p>
        </div>

        <?php
        // Fetch distinct exam types/names from postmeta
        $raw_exams = $wpdb->get_col( "
            SELECT DISTINCT m.meta_value
            FROM {$wpdb->postmeta} m
            JOIN {$wpdb->posts} p ON m.post_id = p.ID
            WHERE p.post_type = 'test' AND p.post_status = 'publish'
              AND m.meta_key = '_test_exam' AND m.meta_value != ''
            ORDER BY m.meta_value ASC
        " );
        ?>

        <!-- Step 1: Exam Type Selection Filter Bar -->
        <div class="mtp-type-filter-wrapper" style="background: var(--bg-card, #fff); border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 25px; margin-bottom: 35px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary, #0f766e); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-filter"></i> <span>انتخاب نوع / دوره آزمون:</span>
            </div>
            <div class="mtp-type-pills" style="display: flex; flex-wrap: wrap; gap: 10px;">
                <button type="button" class="mtp-type-pill active" data-exam-type="all" onclick="filterMtpExamType('all', this)" style="padding: 9px 20px; border-radius: 25px; border: 2px solid var(--primary, #0f766e); background: var(--primary, #0f766e); color: white; font-weight: bold; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.95rem;">
                    <i class="fa-solid fa-border-all"></i> همه آزمون‌ها
                </button>
                <?php if ( ! empty( $raw_exams ) ) :
                    foreach ( $raw_exams as $raw_exam ) : ?>
                        <button type="button" class="mtp-type-pill" data-exam-type="<?php echo esc_attr($raw_exam); ?>" onclick="filterMtpExamType('<?php echo esc_js($raw_exam); ?>', this)" style="padding: 9px 20px; border-radius: 25px; border: 2px solid #cbd5e1; background: var(--bg-body, #f8fafc); color: #475569; font-weight: bold; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.95rem;">
                            <i class="fa-solid fa-notes-medical"></i> <?php echo esc_html($raw_exam); ?>
                        </button>
                    <?php endforeach;
                endif; ?>
            </div>
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
                            WHERE p.post_type = 'test' AND p.post_status = 'publish'
                              AND m1.meta_key = '_test_exam' AND m1.meta_value = %s
                              AND m2.meta_key = '_test_date' AND m2.meta_value = %s
                        ", $exam_date->exam, $exam_date->date ) );

                        // Find distinct courses that have questions in this Year/Exam
                        $courses_in_exam = $wpdb->get_results( $wpdb->prepare( "
                            SELECT DISTINCT m3.meta_value AS course_id
                            FROM {$wpdb->posts} p
                            JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id
                            JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id
                            JOIN {$wpdb->postmeta} m3 ON p.ID = m3.post_id
                            WHERE p.post_type = 'test' AND p.post_status = 'publish'
                              AND m1.meta_key = '_test_exam' AND m1.meta_value = %s
                              AND m2.meta_key = '_test_date' AND m2.meta_value = %s
                              AND m3.meta_key = '_course_id' AND m3.meta_value != ''
                        ", $exam_date->exam, $exam_date->date ) );
                    ?>
                        <div class="mtp-year-card" data-exam-type="<?php echo esc_attr( $exam_date->exam ); ?>" style="background: var(--bg-card, #fff); border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: all 0.3s ease; display: flex; flex-direction: column;">
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
                                                    WHERE p.post_type = 'test' AND p.post_status = 'publish'
                                                      AND m1.meta_key = '_test_exam' AND m1.meta_value = %s
                                                      AND m2.meta_key = '_test_date' AND m2.meta_value = %s
                                                      AND m3.meta_key = '_course_id' AND m3.meta_value = %d
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
                            WHERE p.post_type = 'test' AND p.post_status = 'publish'
                              AND m.meta_key = '_course_id' AND m.meta_value = %d
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
    function filterMtpExamType(selectedType, btnEl) {
        document.querySelectorAll('.mtp-type-pill').forEach(pill => {
            pill.style.background = 'var(--bg-body, #f8fafc)';
            pill.style.color = '#475569';
            pill.style.borderColor = '#cbd5e1';
            pill.classList.remove('active');
        });
        if (btnEl) {
            btnEl.classList.add('active');
            btnEl.style.background = 'var(--primary, #0f766e)';
            btnEl.style.color = '#ffffff';
            btnEl.style.borderColor = 'var(--primary, #0f766e)';
        }

        const cards = document.querySelectorAll('.mtp-year-card');
        cards.forEach(card => {
            const cardType = card.getAttribute('data-exam-type');
            if (selectedType === 'all' || cardType === selectedType) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

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
