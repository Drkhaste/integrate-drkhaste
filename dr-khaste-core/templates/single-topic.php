<?php
get_header();
$topic_id  = get_the_ID();
$lesson_id = get_post_meta( $topic_id, '_mcp_lesson_id', true );
$course_id = $lesson_id ? get_post_meta( $lesson_id, '_mcp_course_id', true ) : null;

$user_id     = get_current_user_id();
$categorized = Dr_Khaste_Leitner::get_topic_cards_categorized( $user_id, $topic_id );
$accordions  = get_post_meta( $topic_id, '_mms_accordions', true );
?>

<main class="container">
    <div class="breadcrumb-container">
        <?php if ( $course_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>
            </div>
        <?php endif; ?>
        <?php if ( $lesson_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo esc_url( get_permalink( $lesson_id ) ); ?>"><?php echo esc_html( get_the_title( $lesson_id ) ); ?></a>
            </div>
        <?php endif; ?>
        <div class="breadcrumb-item active">
            <span><?php the_title(); ?></span>
        </div>
    </div>

    <!-- 1. Text Sections -->
    <?php
    $sections = get_post_meta( $topic_id, '_mcp_sections', true );
    if ( ! empty( $sections ) && is_array( $sections ) ) {
        foreach ( $sections as $section ) {
            $section_type_id    = isset( $section['section_type'] ) ? $section['section_type'] : 0;
            $section_type_title = $section_type_id ? get_the_title( $section_type_id ) : 'بخش';
            $icon_class         = $section_type_id ? get_post_meta( $section_type_id, '_mcp_icon_class', true ) : '';

            $final_icon_class = $icon_class;
            if ( ! empty( $icon_class ) && strpos( $icon_class, 'fa-' ) === 0 ) {
                if ( strpos( $icon_class, 'fa-solid' ) === false && strpos( $icon_class, 'fa-brands' ) === false && strpos( $icon_class, 'fab ' ) === false ) {
                    $final_icon_class = 'fa-solid ' . $icon_class;
                }
            } else if ( empty( $icon_class ) ) {
                $final_icon_class = 'fa-solid fa-book';
            }
            ?>
            <details class="section-card" <?php echo ( count( $sections ) === 1 ) ? 'open' : ''; ?>>
                <summary>
                    <div class="sec-title">
                        <i class="<?php echo esc_attr( $final_icon_class ); ?>"></i> <?php echo esc_html( $section_type_title ); ?>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="card-content">
                    <?php echo apply_filters( 'the_content', isset( $section['content'] ) ? $section['content'] : '' ); ?>
                </div>
            </details>
            <?php
        }
    }
    ?>

    <!-- 2. Mind Maps -->
    <?php if ( ! empty( $accordions ) && is_array( $accordions ) ) : ?>
        <div class="mindmaps-box" style="margin-top: 30px;">
            <h3 style="text-align: center; margin-bottom:20px;"><i class="fa-solid fa-sitemap"></i> نقشه‌های ذهنی مبحث</h3>
            <?php foreach ( $accordions as $a_index => $acc ) : ?>
                <details class="section-card" open>
                    <summary>
                        <div class="sec-title">
                            <i class="fa-solid fa-brain"></i> <?php echo esc_html( isset( $acc['title'] ) ? $acc['title'] : 'نقشه ذهنی' ); ?>
                        </div>
                        <i class="fa-solid fa-chevron-down"></i>
                    </summary>
                    <div class="card-content">
                        <?php
                        $mindmaps = isset( $acc['mindmaps'] ) && is_array( $acc['mindmaps'] ) ? $acc['mindmaps'] : array();
                        foreach ( $mindmaps as $m_index => $mm ) :
                            $unique_id   = 'mms_fe_' . $topic_id . '_' . $a_index . '_' . $m_index;
                            $data        = isset( $mm['data'] ) ? $mm['data'] : '';
                            $layout      = isset( $mm['layout'] ) ? $mm['layout'] : 'both';
                            $node_styles = isset( $mm['node_styles'] ) ? $mm['node_styles'] : '{}';
                            $mm_title    = isset( $mm['title'] ) ? $mm['title'] : '';
                            if ( empty( trim( $data ) ) ) continue;
                        ?>
                            <?php if ( ! empty( $mm_title ) ) : ?>
                                <h4 style="margin:15px 0 10px; color:var(--primary);"><?php echo esc_html( $mm_title ); ?></h4>
                            <?php endif; ?>
                            <div class="mindmap-studio-wrapper" style="width:100%;margin:15px 0;">
                                <div class="mindmap-studio-capture" id="capture_<?php echo esc_attr( $unique_id ); ?>" style="width:100%;background:transparent;position:relative;">
                                    <div
                                        id="<?php echo esc_attr( $unique_id ); ?>"
                                        class="mindmap-studio-container"
                                        data-mindmap-data="<?php echo esc_attr( $data ); ?>"
                                        data-mindmap-layout="<?php echo esc_attr( $layout ); ?>"
                                        data-node-styles="<?php echo esc_attr( $node_styles ); ?>"
                                        data-line-color="#333333"
                                        data-line-style="curved"
                                        data-line-width="2">
                                    </div>
                                </div>
                            </div>
                            <style>
                                #<?php echo esc_attr( $unique_id ); ?> jmnode { font-family: inherit !important; border-radius: 12px !important; }
                                #<?php echo esc_attr( $unique_id ); ?> { direction: ltr !important; overflow: hidden !important; }
                                #<?php echo esc_attr( $unique_id ); ?> jmexpander { display: none !important; }
                            </style>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- 3. Flashcards & Leitner Study Box -->
    <div class="flashcard-box" style="margin-top: 30px;">
        <h3 style="text-align: center;"><i class="fa-solid fa-layer-group"></i> مرور با فلش کارت</h3>

        <?php if ( ! empty( $categorized['ready'] ) || ! empty( $categorized['h24'] ) || ! empty( $categorized['d3'] ) ) : ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <?php
                $course_slug = $course_id ? get_post_meta( $course_id, '_mcp_english_slug', true ) : '';
                $lesson_slug = $lesson_id ? get_post_meta( $lesson_id, '_mcp_english_slug', true ) : '';
                $topic_slug  = get_post_meta( $topic_id, '_mcp_english_slug', true );

                if ( ! empty( $course_slug ) && ! empty( $lesson_slug ) && ! empty( $topic_slug ) ) :
                    $study_url = home_url( "/leitner/{$course_slug}/{$lesson_slug}/{$topic_slug}/" );
                ?>
                    <a href="<?php echo esc_url( $study_url ); ?>" class="btn study-mode-btn">ورود به مود مطالعه اختصاصی</a>
                <?php else : ?>
                    <p style="color: #666; font-size: 0.9em;">برای مرور در حالت مطالعه اختصاصی لایتنر، نامک انگلیسی تنظیم شده است.</p>
                <?php endif; ?>
            </div>

            <div class="leitner-tabs">
                <button class="tab-link active" onclick="openLeitnerTab(event, 'ready')">آماده مرور (<span id="mcp-count-ready"><?php echo count( $categorized['ready'] ); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'h24')">۲۴ ساعت آینده (<span id="mcp-count-h24"><?php echo count( $categorized['h24'] ); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'd3')">۳ روز آینده (<span id="mcp-count-d3"><?php echo count( $categorized['d3'] ); ?></span>)</button>
            </div>

            <div id="ready" class="leitner-tab-content" style="display: block;">
                <?php foreach ( $categorized['ready'] as $flashcard ) : ?>
                    <?php dr_khaste_render_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

            <div id="h24" class="leitner-tab-content">
                <?php foreach ( $categorized['h24'] as $flashcard ) : ?>
                    <?php dr_khaste_render_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

            <div id="d3" class="leitner-tab-content">
                <?php foreach ( $categorized['d3'] as $flashcard ) : ?>
                    <?php dr_khaste_render_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <p style="text-align:center; margin-top: 20px;">هنوز فلش‌کارتی برای این مبحث اضافه نشده است.</p>
        <?php endif; ?>
    </div>

    <!-- 4. Topic Tests -->
    <div class="topic-tests-box" style="margin-top: 30px;">
        <?php echo do_shortcode( '[topic_tests topic_id="' . $topic_id . '"]' ); ?>
    </div>
</main>

<?php
if ( ! function_exists( 'dr_khaste_render_flashcard_item' ) ) {
    function dr_khaste_render_flashcard_item( $flashcard, $topic_id ) {
        ?>
        <div class="fc-item" data-id="<?php echo $flashcard->ID; ?>" data-topic="<?php echo $topic_id; ?>" onclick="mcpFlipCard(this)">
            <div class="fc-card-inner">
                <div class="fc-card-front">
                    <div class="fc-question"><?php echo apply_filters( 'the_content', get_post_meta( $flashcard->ID, '_mcp_question', true ) ); ?></div>
                    <div style="margin-top: 15px; color: var(--primary); font-size: 0.8em; opacity: 0.7;">برای مشاهده پاسخ کلیک کنید</div>
                </div>
                <div class="fc-card-back">
                    <div class="fc-ans-content"><?php echo apply_filters( 'the_content', get_post_meta( $flashcard->ID, '_mcp_answer', true ) ); ?></div>
                    <div class="leitner-actions">
                        <button class="btn-fail" onclick="mcpSubmitLeitner(event, this, 1)">۱. بلد نبودم</button>
                        <button class="btn-doubt" onclick="mcpSubmitLeitner(event, this, 2)">۲. با شک بلد بودم</button>
                        <button class="btn-pass" onclick="mcpSubmitLeitner(event, this, 3)">۳. مسلط بودم</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>

<script>
    function openLeitnerTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("leitner-tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>

<?php get_footer(); ?>
