<?php
    get_header();
    $lesson_id = get_the_ID();
    $course_id = get_post_meta( $lesson_id, '_course_id', true );
?>

<main class="container">
    <div class="breadcrumb-container">
        <?php if ( $course_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo mcp_get_permalink($course_id); ?>"><?php echo get_the_title($course_id); ?></a>
            </div>
        <?php endif; ?>
        <div class="breadcrumb-item active">
            <span><?php the_title(); ?></span>
        </div>
    </div>
    <div class="topic-box">
        <?php
        $topics = get_posts([
            'post_type' => 'topic',
            'meta_key' => '_lesson_id',
            'meta_value' => $lesson_id,
            'numberposts' => -1,
            'orderby' => [ 'menu_order' => 'ASC', 'date' => 'ASC' ]
        ]);

        if ( ! empty( $topics ) ) {
            echo '<ul class="topic-list">';
            foreach ( $topics as $topic ) {
                ?>
                <li class="topic-list-item">
                    <span><?php echo get_the_title( $topic->ID ); ?></span>
                    <?php
                        $topic_slug = get_post_meta( $topic->ID, '_english_slug', true );
                    ?>
                    <div class="topic-buttons">
                        <a href="<?php echo mcp_get_permalink( $topic->ID ); ?>" class="btn-topic"><?php echo esc_html__( 'درسنامه', 'med-course-plugin' ); ?></a>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
        } else {
            echo '<p>هیچ مبحثی برای این درس یافت نشد.</p>';
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>
