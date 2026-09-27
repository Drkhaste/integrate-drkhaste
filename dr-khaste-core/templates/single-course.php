<?php
get_header();
$course_id = get_the_ID();
?>

<main class="container">
    <div class="breadcrumb-container">
        <div class="breadcrumb-item active">
            <span><?php the_title(); ?></span>
        </div>
    </div>
    <?php
    $lessons = get_posts( array(
        'post_type'   => 'lesson',
        'meta_key'    => '_mcp_course_id',
        'meta_value'  => $course_id,
        'numberposts' => -1,
        'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
    ) );

    if ( ! empty( $lessons ) ) {
        foreach ( $lessons as $lesson ) {
            ?>
            <details class="section-card" open>
                <summary>
                    <div class="sec-title">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <?php echo esc_html( get_the_title( $lesson->ID ) ); ?>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="card-content">
                    <?php
                    $topics = get_posts( array(
                        'post_type'   => 'topic',
                        'meta_key'    => '_mcp_lesson_id',
                        'meta_value'  => $lesson->ID,
                        'numberposts' => -1,
                        'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                    ) );

                    if ( ! empty( $topics ) ) {
                        echo '<ul class="topic-list">';
                        foreach ( $topics as $topic ) {
                            ?>
                            <li class="topic-list-item">
                                <span><?php echo esc_html( get_the_title( $topic->ID ) ); ?></span>
                                <div class="topic-buttons">
                                    <a href="<?php echo esc_url( get_permalink( $topic->ID ) ); ?>" class="btn-topic">درسنامه</a>
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
            </details>
            <?php
        }
    } else {
        echo '<p>هیچ درسی برای این کورس یافت نشد.</p>';
    }
    ?>
</main>

<?php get_footer(); ?>
