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

$lesson_id = get_post_meta( $topic_id, '_mcp_lesson_id', true );
$course_id = $lesson_id ? get_post_meta( $lesson_id, '_mcp_course_id', true ) : null;

$tests = get_posts([
    'post_type' => 'mtp_test',
    'meta_key' => '_mcp_topic_id',
    'meta_value' => $topic_id,
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
]);
?>

<main class="container">
    <div class="breadcrumb-container">
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
            <div class="mtp-no-tests">هنوز تستی برای این مبحث ثبت نشده است.</div>
        <?php endif; ?>
    </div>
</main>

<style>
.mtp-quiz-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--primary);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.mtp-quiz-progress {
    text-align: center;
    margin-bottom: 25px;
    font-weight: bold;
    color: var(--primary);
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
    color: var(--text-main);
    font-weight: 600;
}

.q-label {
    color: var(--primary);
    margin-bottom: 10px;
    font-weight: 900;
}

.mtp-vertical-options {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Increased spacing between options */
    margin-bottom: 40px; /* More margin before the check button */
}

.mtp-option-label {
    cursor: pointer;
    margin: 0;
}

.mtp-option-label input { display: none; }

.mtp-option-box {
    display: flex;
    align-items: center;
    background: var(--bg-body);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px; /* Increased padding */
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
    color: var(--text-main);
    flex-shrink: 0;
}

.mtp-option-text {
    font-weight: 600;
    color: var(--text-main);
    font-size: 1.1rem;
}

.mtp-option-label:hover .mtp-option-box {
    border-color: var(--primary);
    background: rgba(15, 118, 110, 0.02);
}

.mtp-option-label input:checked + .mtp-option-box {
    border-color: var(--primary);
    background: rgba(15, 118, 110, 0.05);
}

.mtp-option-label input:checked + .mtp-option-box .mtp-option-letter {
    background: var(--primary);
    color: white;
}

.mtp-actions {
    margin-bottom: 20px;
}

.btn-mtp-check {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 12px 35px;
    border-radius: 12px;
    font-weight: bold;
    cursor: pointer;
    font-family: inherit; /* FOLLOW THEME FONT */
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-mtp-check:hover { filter: brightness(110%); transform: translateY(-2px); }

.mtp-feedback-area {
    margin-top: 25px;
    padding: 25px;
    border-radius: 12px;
    background: var(--bg-body);
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
    color: var(--primary);
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
    background: var(--bg-body);
    border: 1px solid var(--primary);
    color: var(--primary);
    padding: 10px 30px;
    border-radius: 12px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: inherit; /* FOLLOW THEME FONT */
}

.mtp-nav-btn:hover:not(:disabled) {
    background: var(--primary);
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
    color: var(--text-main);
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

    // Split correct answers by comma (e.g. "1,3" -> ["1", "3"])
    const correctAnswers = String(correctAnswer).split(',').map(item => item.trim());

    labels.forEach(label => {
        const input = label.querySelector('input');
        label.classList.remove('is-correct', 'is-wrong');
        // Highlight all correct options in green
        if (correctAnswers.includes(input.value)) {
            label.classList.add('is-correct');
        }
        // Disable the inputs and make options unclickable
        input.disabled = true;
        label.style.pointerEvents = 'none';
    });

    // Check if the user's selected option is one of the correct answers
    if (correctAnswers.includes(selected.value)) {
        status.className = 'mtp-feedback-status correct';
        status.innerHTML = '<i class="fa-solid fa-circle-check"></i> آفرین! پاسخ شما صحیح بود.';
    } else {
        status.className = 'mtp-feedback-status wrong';
        status.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> متاسفانه پاسخ اشتباه بود.';
        selected.closest('.mtp-option-label').classList.add('is-wrong');
    }

    // Disable and style the check button so it cannot be clicked again
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

<?php get_footer(); ?>
