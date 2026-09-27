<?php
/**
 * Dr. Khasteh Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme Setup
 */
function dr_khasteh_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );

    // Register Navigation Menu
    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'dr-khasteh' ),
    ] );

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ] );

    // Add support for core custom logo.
    add_theme_support( 'custom-logo', [
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ] );
}
add_action( 'after_setup_theme', 'dr_khasteh_setup' );

/**
 * Enqueue scripts and styles.
 */
function dr_khasteh_scripts() {
    $font_source = get_option( 'dr_khasteh_font_source', 'cdn' );
    $upload_dir  = wp_upload_dir();
    $local_base  = $upload_dir['baseurl'] . '/dr-khasteh-fonts';

    if ( $font_source === 'local' ) {
        // Enqueue local assets
        wp_enqueue_style( 'dr-khasteh-local-fonts', $local_base . '/local-fonts.css', [], '1.0.0' );
        wp_enqueue_style( 'dr-khasteh-fontawesome-local', $local_base . '/fontawesome-local.css', [], '6.5.1' );
    } else {
        // Fonts via CDN
        wp_enqueue_style( 'vazirmatn', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap', [], null );
        wp_enqueue_style( 'ibm-plex-sans-arabic', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap', [], null );
        wp_enqueue_style( 'playpen-sans-arabic', 'https://cdn.jsdelivr.net/npm/@fontsource/playpen-sans-arabic/index.css', [], null );
        wp_enqueue_style( 'zain', 'https://fonts.googleapis.com/css2?family=Zain:wght@200;300;400;700;800;900&display=swap', [], null );
        wp_enqueue_style( 'noto-sans-arabic', 'https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@100..900&display=swap', [], null );

        // Font Awesome - Using a more reliable CDN
        wp_enqueue_style( 'dr-khasteh-fontawesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css', [], '6.5.1' );
    }

    // Check if Raw Mode is active for the current page
    $is_raw_mode = is_page() && get_post_meta( get_the_ID(), '_dr_khasteh_raw_mode', true ) === '1';

    if ( ! $is_raw_mode ) {
        // Main Stylesheet
        wp_enqueue_style( 'dr-khasteh-style', get_stylesheet_uri(), [], '1.0.0' );
        wp_enqueue_style( 'dr-khasteh-custom', get_template_directory_uri() . '/assets/css/theme-style.css', [], '1.0.0' );

        // Scripts
        wp_enqueue_script( 'dr-khasteh-scripts', get_template_directory_uri() . '/assets/js/theme-scripts.js', [], '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'dr_khasteh_scripts' );

/**
 * Text Styler Integration
 */
require_once get_template_directory() . '/inc/class-dr-khasteh-text-styler-defaults.php';
require_once get_template_directory() . '/inc/class-dr-khasteh-text-styler-assets.php';
require_once get_template_directory() . '/inc/class-dr-khasteh-text-styler-css-generator.php';
require_once get_template_directory() . '/inc/class-dr-khasteh-text-styler-tinymce.php';
require_once get_template_directory() . '/inc/class-dr-khasteh-settings.php';

Dr_Khasteh_Text_Styler_Assets::init();
Dr_Khasteh_Text_Styler_CSS_Generator::init();
Dr_Khasteh_Text_Styler_TinyMCE::init();
Dr_Khasteh_Settings::init();

/**
 * Custom CSS for Admin Dashboard and Login Page.
 */
function dr_khasteh_custom_admin_css() {
    $css = get_option( 'dr_khasteh_admin_css', '' );
    if ( ! empty( $css ) ) {
        echo '<style id="dr-khasteh-custom-admin-css">' . wp_strip_all_tags( $css ) . '</style>';
    }
}
add_action( 'admin_head', 'dr_khasteh_custom_admin_css' );

function dr_khasteh_custom_login_css() {
    $css = get_option( 'dr_khasteh_login_css', '' );
    if ( ! empty( $css ) ) {
        echo '<style id="dr-khasteh-custom-login-css">' . wp_strip_all_tags( $css ) . '</style>';
    }
}
add_action( 'login_head', 'dr_khasteh_custom_login_css' );

/**
 * Inline script to prevent theme/font flashing.
 */
function dr_khasteh_pre_render_script() {
    ?>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }
            const savedFont = localStorage.getItem('fontFamily') || "'IBM Plex Sans Arabic'";
            const savedFontSize = localStorage.getItem('fontSize') || '16';
            document.documentElement.style.setProperty('--current-font', savedFont);
            document.documentElement.style.setProperty('--current-font-size', savedFontSize + 'px');
        })();
    </script>
    <?php
}
add_action( 'wp_body_open', 'dr_khasteh_pre_render_script' );

/**
 * Add custom classes to the body.
 */
function dr_khasteh_body_classes( $classes ) {
    $is_raw_mode = is_page() && get_post_meta( get_the_ID(), '_dr_khasteh_raw_mode', true ) === '1';
    if ( ! $is_raw_mode ) {
        $classes[] = 'dr-khasteh-theme';
    }
    return $classes;
}
add_filter( 'body_class', 'dr_khasteh_body_classes' );

/**
 * Add Raw Mode Meta Box
 */
function dr_khasteh_add_meta_boxes() {
    // Raw Mode Meta Box
    add_meta_box(
        'dr_khasteh_raw_mode_meta',
        'حالت خام (فقط فونت)',
        'dr_khasteh_render_raw_mode_meta_box',
        'page',
        'side',
        'default'
    );

    // Font Settings Visibility Meta Box
    $all_types = get_post_types( array( 'public' => true ) );
    foreach ( $all_types as $type ) {
        add_meta_box(
            'dr_khasteh_font_settings_meta',
            'تنظیمات فونت در هدر',
            'dr_khasteh_render_font_settings_meta_box',
            $type,
            'side',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'dr_khasteh_add_meta_boxes' );

function dr_khasteh_render_raw_mode_meta_box( $post ) {
    $value = get_post_meta( $post->ID, '_dr_khasteh_raw_mode', true );
    wp_nonce_field( 'dr_khasteh_raw_mode_nonce', 'dr_khasteh_raw_mode_nonce_field' );
    ?>
    <label for="dr_khasteh_raw_mode">
        <input type="checkbox" name="dr_khasteh_raw_mode" id="dr_khasteh_raw_mode" value="1" <?php checked( $value, '1' ); ?>>
        فعال‌سازی حالت خام
    </label>
    <p class="description">در این حالت استایل‌های قالب اعمال نشده و فقط فونت‌ها لود می‌شوند.</p>
    <?php
}

function dr_khasteh_render_font_settings_meta_box( $post ) {
    $value = get_post_meta( $post->ID, '_dr_khasteh_font_settings_override', true );
    if ( ! $value ) {
        $value = 'default';
    }
    wp_nonce_field( 'dr_khasteh_font_settings_nonce', 'dr_khasteh_font_settings_nonce_field' );
    ?>
    <select name="dr_khasteh_font_settings_override" id="dr_khasteh_font_settings_override" class="postbox-container" style="width: 100%;">
        <option value="default" <?php selected( $value, 'default' ); ?>>پیش‌فرض تنظیمات کل (General)</option>
        <option value="enabled" <?php selected( $value, 'enabled' ); ?>>فعال (همیشه نمایش داده شود)</option>
        <option value="disabled" <?php selected( $value, 'disabled' ); ?>>غیرفعال (مخفی بماند)</option>
    </select>
    <p class="description">تعیین وضعیت نمایش پنل تغییر فونت در هدر برای این مطلب.</p>
    <?php
}

function dr_khasteh_save_post_meta( $post_id ) {
    // Security checks
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Raw Mode
    if ( isset( $_POST['dr_khasteh_raw_mode_nonce_field'] ) && wp_verify_nonce( $_POST['dr_khasteh_raw_mode_nonce_field'], 'dr_khasteh_raw_mode_nonce' ) ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            $value = isset( $_POST['dr_khasteh_raw_mode'] ) ? '1' : '0';
            update_post_meta( $post_id, '_dr_khasteh_raw_mode', $value );
        }
    }

    // Font Settings Override
    if ( isset( $_POST['dr_khasteh_font_settings_nonce_field'] ) && wp_verify_nonce( $_POST['dr_khasteh_font_settings_nonce_field'], 'dr_khasteh_font_settings_nonce' ) ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            if ( isset( $_POST['dr_khasteh_font_settings_override'] ) ) {
                update_post_meta( $post_id, '_dr_khasteh_font_settings_override', sanitize_key( $_POST['dr_khasteh_font_settings_override'] ) );
            }
        }
    }
}
add_action( 'save_post', 'dr_khasteh_save_post_meta' );

/**
 * Raw Mode Assets and Content Cleanup
 */
function dr_khasteh_raw_mode_assets_cleanup() {
    if ( is_page() && get_post_meta( get_the_ID(), '_dr_khasteh_raw_mode', true ) === '1' ) {
        wp_dequeue_style( 'dr-khasteh-text-styler' );
        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_excerpt', 'wpautop' );
    }
}
add_action( 'wp_enqueue_scripts', 'dr_khasteh_raw_mode_assets_cleanup', 20 );

/**
 * Raw Mode Template Redirection
 */
function dr_khasteh_raw_mode_template( $template ) {
    if ( is_page() && get_post_meta( get_the_ID(), '_dr_khasteh_raw_mode', true ) === '1' ) {
        $new_template = locate_template( array( 'template-raw.php' ) );
        if ( '' != $new_template ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'dr_khasteh_raw_mode_template' );
