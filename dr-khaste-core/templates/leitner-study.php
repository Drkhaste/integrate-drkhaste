<?php
get_header();

$topic_slug  = get_query_var( 'topic_slug' );
$lesson_slug = get_query_var( 'lesson_slug' );
$course_slug = get_query_var( 'course_slug' );

$topic_id = 0;
if ( $topic_slug ) {
	$posts = get_posts( array(
		'post_type'      => 'topic',
		'name'           => $topic_slug,
		'posts_per_page' => 1,
	) );
	if ( empty( $posts ) ) {
		$posts = get_posts( array(
			'post_type'      => 'topic',
			'meta_key'       => '_mcp_english_slug',
			'meta_value'     => $topic_slug,
			'posts_per_page' => 1,
		) );
	}
	if ( ! empty( $posts ) ) {
		$topic_id = $posts[0]->ID;
	}
}

if ( ! $topic_id ) {
	echo '<div class="container" style="padding:40px 0;"><p>مبحث مورد نظر یافت نشد.</p></div>';
	get_footer();
	exit;
}

$lesson_id   = get_post_meta( $topic_id, '_mcp_lesson_id', true );
$course_id   = $lesson_id ? get_post_meta( $lesson_id, '_mcp_course_id', true ) : null;
$user_id     = get_current_user_id();
$categorized = Dr_Khaste_Leitner::get_topic_cards_categorized( $user_id, $topic_id );
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
        <div class="breadcrumb-item">
            <a href="<?php echo esc_url( get_permalink( $topic_id ) ); ?>"><?php echo esc_html( get_the_title( $topic_id ) ); ?></a>
        </div>
        <div class="breadcrumb-item active">
            <span>مطالعه لایتنر</span>
        </div>
    </div>

    <div class="leitner-study-container" id="mcp-leitner-app">
        <div class="leitner-header">
            <h2>مطالعه فلش‌کارت‌ها: <?php echo esc_html( get_the_title( $topic_id ) ); ?></h2>
        </div>

        <?php if ( ! empty( $categorized['ready'] ) || ! empty( $categorized['h24'] ) || ! empty( $categorized['d3'] ) ) : ?>

            <div class="leitner-tabs">
                <button class="tab-link active" onclick="openLeitnerTab(event, 'ready')">آماده مرور (<span id="mcp-count-ready"><?php echo count( $categorized['ready'] ); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'h24')">۲۴ ساعت آینده (<span id="mcp-count-h24"><?php echo count( $categorized['h24'] ); ?></span>)</button>
                <button class="tab-link" onclick="openLeitnerTab(event, 'd3')">۳ روز آینده (<span id="mcp-count-d3"><?php echo count( $categorized['d3'] ); ?></span>)</button>
            </div>

            <div id="ready" class="leitner-tab-content" style="display: block;">
                <?php foreach ( $categorized['ready'] as $flashcard ) : ?>
                    <?php dr_khaste_render_study_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

            <div id="h24" class="leitner-tab-content">
                <?php foreach ( $categorized['h24'] as $flashcard ) : ?>
                    <?php dr_khaste_render_study_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

            <div id="d3" class="leitner-tab-content">
                <?php foreach ( $categorized['d3'] as $flashcard ) : ?>
                    <?php dr_khaste_render_study_flashcard_item( $flashcard, $topic_id ); ?>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <div class="no-cards-message" style="text-align:center; padding:30px;">
                <p>هنوز فلش‌کارتی برای این مبحث اضافه نشده است.</p>
                <a href="<?php echo esc_url( get_permalink( $topic_id ) ); ?>" class="btn study-mode-btn">بازگشت به مبحث</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
if ( ! function_exists( 'dr_khaste_render_study_flashcard_item' ) ) {
    function dr_khaste_render_study_flashcard_item( $flashcard, $topic_id ) {
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
