<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: var(--current-font), sans-serif;
            background-color: #fff;
            color: #000;
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>

    <?php wp_footer(); ?>
</body>
</html>
