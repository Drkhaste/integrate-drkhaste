<?php get_header(); ?>

<main class="container">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('content-card'); ?>>
                <header class="entry-header">
                    <?php if ( is_singular() ) : ?>
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    <?php else : ?>
                        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php endif; ?>
                </header>

                <div class="entry-content">
                    <?php
                    the_content();
                    wp_link_pages( [
                        'before' => '<div class="page-links">' . __( 'Pages:', 'dr-khasteh' ),
                        'after'  => '</div>',
                    ] );
                    ?>
                </div>
            </article>
        <?php endwhile; ?>

        <div class="navigation">
            <?php the_posts_navigation(); ?>
        </div>

    <?php else : ?>
        <p>محتوایی یافت نشد.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
