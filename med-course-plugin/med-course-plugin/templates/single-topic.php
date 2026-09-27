<?php
    get_header();
    $topic_id = get_the_ID();
    $lesson_id = get_post_meta( $topic_id, '_mcp_lesson_id', true );
    $course_id = $lesson_id ? get_post_meta( $lesson_id, '_mcp_course_id', true ) : null;

    // Leitner Logic Integration
    $repository = new MCP_Leitner_Repository();
    $service = new MCP_Leitner_Service($repository);
    $user_id = get_current_user_id();
    $categorized = $service->get_topic_cards_categorized($user_id, $topic_id);
?>

<main class="container">
    <div class="breadcrumb-container">
        <?php if ( $course_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo mcp_get_permalink($course_id); ?>"><?php echo get_the_title($course_id); ?></a>
            </div>
        <?php endif; ?>
        <?php if ( $lesson_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo mcp_get_permalink($lesson_id); ?>"><?php echo get_the_title($lesson_id); ?></a>
            </div>
        <?php endif; ?>
        <div class="breadcrumb-item active">
            <span><?php the_title(); ?></span>
        </div>
    </div>

    <?php
    $sections = get_post_meta( $topic_id, '_mcp_sections', true );
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $section ) {
            $section_type_id = $section['section_type'];
            $section_type_title = get_the_title( $section_type_id );
            $icon_class = get_post_meta( $section_type_id, '_mcp_icon_class', true );
            ?>
            <?php
            // Ensure icon has proper FA6 classes
            $final_icon_class = $icon_class;
            if ( ! empty( $icon_class ) && strpos( $icon_class, 'fa-' ) === 0 ) {
                if ( strpos( $icon_class, 'fa-solid' ) === false && strpos( $icon_class, 'fa-brands' ) === false && strpos( $icon_class, 'fab ' ) === false ) {
                    $final_icon_class = 'fa-solid ' . $icon_class;
                }
            } else if ( empty( $icon_class ) ) {
                $final_icon_class = 'fa-solid fa-book'; // Fallback
            }
            ?>
            <details class="section-card" <?php echo (is_array($sections) && count($sections) === 1) ? 'open' : ''; ?>>
                <summary>
                    <div class="sec-title">
                        <i class="<?php echo esc_attr( $final_icon_class ); ?>"></i> <?php echo esc_html( $section_type_title ); ?>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="card-content">
                    <?php echo apply_filters( 'the_content', $section['content'] ); ?>
                </div>
            </details>
            <?php
        }
    }
    ?>

    <div class="flashcard-box">
        <h3 style="text-align: center;">مرور با فلش کارت</h3>

        <?php if ( ! empty( $categorized['ready'] ) || ! empty( $categorized['h24'] ) || ! empty( $categorized['d3'] ) ) : ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <?php
                $course_slug = get_post_meta( $course_id, '_mcp_english_slug', true );
                $lesson_slug = get_post_meta( $lesson_id, '_mcp_english_slug', true );
                $topic_slug = get_post_meta( $topic_id, '_mcp_english_slug', true );

                if ( ! empty( $course_slug ) && ! empty( $lesson_slug ) && ! empty( $topic_slug ) ) :
                    $study_url = home_url( "/leitner/{$course_slug}/{$lesson_slug}/{$topic_slug}/" );
                ?>
                    <a href="<?php echo esc_url($study_url); ?>" class="btn study-mode-btn">ورود به مود مطالعه اختصاصی</a>
                <?php else : ?>
                    <p style="color: red; font-size: 0.9em;">جهت مشاهده حالت مطالعه اختصاصی، لطفا برای این مبحث، درس و دوره مربوطه نامک انگلیسی (Slug) تنظیم کنید.</p>
                <?php endif; ?>
            </div>

            <div class="leitner-tabs">
                <button class="tab-link active" onclick="openLeitnerTab(event, 'ready')">آماده مرور (<span id="mcp-count-ready"><?php echo count($categorized['ready']); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'h24')">۲۴ ساعت آینده (<span id="mcp-count-h24"><?php echo count($categorized['h24']); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'd3')">۳ روز آینده (<span id="mcp-count-d3"><?php echo count($categorized['d3']); ?></span>)</button>
            </div>

            <div id="ready" class="leitner-tab-content" style="display: block;">
                <?php foreach ( $categorized['ready'] as $flashcard ) : ?>
                    <?php mcp_render_flashcard_item($flashcard, $topic_id); ?>
                <?php endforeach; ?>
            </div>

            <div id="h24" class="leitner-tab-content">
                <?php foreach ( $categorized['h24'] as $flashcard ) : ?>
                    <?php mcp_render_flashcard_item($flashcard, $topic_id); ?>
                <?php endforeach; ?>
            </div>

            <div id="d3" class="leitner-tab-content">
                <?php foreach ( $categorized['d3'] as $flashcard ) : ?>
                    <?php mcp_render_flashcard_item($flashcard, $topic_id); ?>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <p style="text-align:center; margin-top: 20px;">هنوز فلش‌کارتی برای این مبحث اضافه نشده است.</p>
        <?php endif; ?>
    </div>
</main>

<?php
function mcp_render_flashcard_item($flashcard, $topic_id) {
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

    function mcpToggleLeitnerAnswer(btn) {
        const container = btn.nextElementSibling;
        if(container.style.display === 'block'){
            container.style.display = 'none';
            btn.textContent = 'نمایش پاسخ';
        } else {
            container.style.display = 'block';
            btn.textContent = 'بستن پاسخ';
        }
    }
</script>

<?php get_footer(); ?>
