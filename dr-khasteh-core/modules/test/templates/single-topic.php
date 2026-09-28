<?php
    get_header();
    $topic_id = get_the_ID();
    $lesson_id = get_post_meta( $topic_id, '_lesson_id', true );
    $course_id = $lesson_id ? get_post_meta( $lesson_id, '_course_id', true ) : null;
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
        <div class="breadcrumb-item active">
            <span><?php the_title(); ?></span>
        </div>
    </div>

    <div class="topic-box" style="background:var(--bg-card); color:var(--text-main); border: 1px solid var(--primary); border-radius:12px; padding:30px; margin-bottom:30px;">
        <h1 style="color:var(--primary); margin-top:0;"><?php the_title(); ?></h1>
        <?php
            $course_slug = get_post_meta( $course_id, '_english_slug', true );
            $lesson_slug = get_post_meta( $lesson_id, '_english_slug', true );
            $topic_slug = get_post_meta( $topic_id, '_english_slug', true );
            $test_url = home_url( "/test/{$course_slug}/{$lesson_slug}/{$topic_slug}/" );
        ?>
        <div style="margin: 20px 0;">
            <a href="<?php echo esc_url( $test_url ); ?>" class="btn-topic-test" style="display:inline-block; padding:12px 30px; font-size:1.1rem;"><?php echo esc_html__( 'شروع تست‌های این مبحث', 'medical-test-plugin' ); ?></a>
        </div>

        <div class="topic-content" style="line-height:1.8;">
            <?php the_content(); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
