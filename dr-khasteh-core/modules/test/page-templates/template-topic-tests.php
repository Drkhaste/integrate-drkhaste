<?php
/**
 * Template Name: Topic Tests
 */

get_header();

$topic_id = get_the_ID();
if (!$topic_id || get_post_type($topic_id) !== 'topic') {
    $topic_slug = get_query_var('mtp_topic_slug');
    $topics = get_posts(['post_type' => 'topic', 'name' => $topic_slug, 'posts_per_page' => 1]);
    if ($topics) $topic_id = $topics[0]->ID;
}

if (!$topic_id) wp_die('مبحث یافت نشد.');

$lesson_id = get_post_meta( $topic_id, '_lesson_id', true );
$course_id = $lesson_id ? get_post_meta( $lesson_id, '_course_id', true ) : null;

$tests = get_posts([
    'post_type' => 'test',
    'meta_key' => '_topic_id',
    'meta_value' => $topic_id,
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
]);
?>

<main class="container" style="direction: rtl; text-align: right; margin-top: 25px; margin-bottom: 60px;">
    <div class="breadcrumb-container" style="margin-bottom: 25px;">
        <?php if ( $course_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo mtp_get_permalink($course_id); ?>"><?php echo get_the_title($course_id); ?></a>
            </div>
        <?php endif; ?>
        <?php if ( $lesson_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo mtp_get_permalink($lesson_id); ?>"><?php echo get_the_title($lesson_id); ?></a>
            </div>
        <?php endif; ?>
        <div class="breadcrumb-item">
            <a href="<?php echo mtp_get_permalink($topic_id); ?>"><?php echo get_the_title($topic_id); ?></a>
        </div>
        <div class="breadcrumb-item active">
            <span>تست‌ها</span>
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
                                <div class="mtp-explanation-content"><?php echo function_exists( 'dr_khasteh_render_explanation' ) ? dr_khasteh_render_explanation( $explanation ) : apply_filters( 'the_content', $explanation ); ?></div>
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
            <div class="mtp-no-tests">هنوز تستی برای این مبحث ثبت نشده است.</div>
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

<?php get_footer(); ?>
