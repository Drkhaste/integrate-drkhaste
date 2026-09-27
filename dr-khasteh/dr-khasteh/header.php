<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php
    $logo_align = get_option( 'dr_khasteh_logo_align', 'right' );
    $logo_width = get_option( 'dr_khasteh_logo_width', 150 );
    ?>
    <header class="sticky-header">
        <div class="header-container logo-align-<?php echo esc_attr( $logo_align ); ?>">
            <div class="logo-area" style="max-width: <?php echo esc_attr( $logo_width ); ?>px;">
                <?php
                $tag = is_front_page() ? 'h1' : 'div';
                echo '<' . $tag . ' class="site-logo-container">';
                if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-title">' . get_bloginfo( 'name' ) . '</a>';
                }
                echo '</' . $tag . '>';
                ?>
            </div>

            <nav class="main-navigation">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ] );
                ?>
            </nav>

            <div class="header-actions">
                <?php
                $show_font_settings = false;
                if ( is_singular() ) {
                    $post_id  = get_the_ID();
                    $override = get_post_meta( $post_id, '_dr_khasteh_font_settings_override', true );

                    if ( 'enabled' === $override ) {
                        $show_font_settings = true;
                    } elseif ( 'disabled' === $override ) {
                        $show_font_settings = false;
                    } else {
                        // Default logic: check post type settings
                        $allowed_pts = get_option( 'dr_khasteh_font_settings_post_types', array( 'topic' ) );
                        if ( in_array( get_post_type( $post_id ), (array) $allowed_pts, true ) ) {
                            $show_font_settings = true;
                        }
                    }
                }
                ?>
                <?php if ( $show_font_settings ) : ?>
                    <div class="font-settings-toggle" id="font-settings-toggle"><i class="fa-solid fa-font"></i></div>
                <?php endif; ?>
                <div class="theme-toggle" id="theme-toggle"><i class="fa-solid fa-moon" id="theme-icon"></i></div>
            </div>
        </div>
    </header>

    <?php if ( $show_font_settings ) : ?>
    <div id="font-settings-panel" class="settings-panel">
        <h4>تنظیمات متن</h4>
        <div class="setting-item">
            <label>فونت:</label>
            <select id="font-family-select">
                <option value="Vazirmatn">وزیر</option>
                <option value="'IBM Plex Sans Arabic'">آی‌بی‌ام پلکس</option>
                <option value="'Playpen Sans Arabic'">پلی‌پن</option>
                <option value="Zain">زین</option>
                <option value="'Noto Sans Arabic'">نوتو</option>
            </select>
        </div>
        <div class="setting-item">
            <label>اندازه متن:</label>
            <div class="font-size-controls">
                <button id="font-size-decrease">-</button>
                <span id="font-size-display">16</span>
                <button id="font-size-increase">+</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="site-content">
