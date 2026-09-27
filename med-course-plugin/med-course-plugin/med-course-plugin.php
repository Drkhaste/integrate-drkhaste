<?php
/**
 * Plugin Name:       Medical Course Plugin
 * Description:       A plugin to create and manage medical courses, lessons, and topics.
 * Version:           1.0.2
 * Author:            Jules
 * Text Domain:       med-course-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the Custom Post Type registration file.
require_once plugin_dir_path( __FILE__ ) . 'includes/cpt-init.php';

// Include the meta box registration file.
require_once plugin_dir_path( __FILE__ ) . 'includes/meta-boxes.php';

// Include the admin columns file.
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-columns.php';

// Include the admin filters file.
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-filters.php';

// Include the hooks file.
require_once plugin_dir_path( __FILE__ ) . 'includes/hooks.php';

// Include the import/export file.
require_once plugin_dir_path( __FILE__ ) . 'includes/import-export.php';

// Include the settings file.
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';

// Include Leitner module.
require_once plugin_dir_path( __FILE__ ) . 'includes/leitner/leitner-init.php';

/**
 * Enqueue admin scripts and styles.
 */
function mcp_admin_enqueue_scripts( $hook ) {
    global $post;

    $local_assets = get_option( 'mcp_local_assets', '0' ) === '1';
    $vendor_url = plugin_dir_url( __FILE__ ) . 'assets/vendor/';
    $vendor_path = plugin_dir_path( __FILE__ ) . 'assets/vendor/';

    // List of post types that use our custom scripts
    $post_types = ['topic', 'flashcard', 'section_type'];

    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && isset($post->post_type) && in_array($post->post_type, $post_types) ) {
        // Enqueue Select2 for topic edit page
        if ( 'topic' === $post->post_type ) {
            $s2_css = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            $s2_js = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';

            if ( $local_assets && file_exists( $vendor_path . 'select2/css/select2.min.css' ) ) {
                $s2_css = $vendor_url . 'select2/css/select2.min.css';
                $s2_js = $vendor_url . 'select2/js/select2.min.js';
            }

            wp_enqueue_style( 'mcp-select2', $s2_css, [], '4.1.0-rc.0' );
            wp_enqueue_script( 'mcp-select2', $s2_js, [ 'jquery' ], '4.1.0-rc.0', true );
            wp_enqueue_script(
                'mcp-topic-editor',
                plugin_dir_url( __FILE__ ) . 'assets/js/topic-editor.js',
                [ 'jquery', 'mcp-select2', 'mcp-admin-scripts' ],
                '1.0.0',
                true
            );
        }

        // Force load of editor assets
        wp_enqueue_editor();

        wp_enqueue_script(
            'mcp-admin-scripts',
            plugin_dir_url( __FILE__ ) . 'assets/js/admin-scripts.js',
            [ 'jquery', 'wp-editor' ], // Add wp-editor as a dependency
            '1.0.2', // Incremented version
            true
        );
        wp_localize_script( 'mcp-admin-scripts', 'mcp_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'create_section_type_nonce' => wp_create_nonce( 'mcp_create_section_type_nonce' )
        ] );
    }

    // Enqueue styles and scripts for post edit pages
    $post_types_with_styles = ['section_type', 'course', 'lesson'];
    if ( in_array( get_post_type(), $post_types_with_styles ) ) {
        if ( 'section_type' === get_post_type() ) {
            $fa_css = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css';
            if ( $local_assets && file_exists( $vendor_path . 'fontawesome/css/all.min.css' ) ) {
                $fa_css = $vendor_url . 'fontawesome/css/all.min.css';
            }
            wp_enqueue_style(
                'mcp-fontawesome',
                $fa_css,
                [],
                '6.5.1'
            );
        }
        wp_enqueue_style(
            'mcp-admin-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css',
            [],
            '1.0.1'
        );

        if ( in_array( get_post_type(), ['course', 'lesson'] ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script(
                'mcp-sortable-items',
                plugin_dir_url( __FILE__ ) . 'assets/js/sortable-items.js',
                [ 'jquery', 'jquery-ui-sortable' ],
                '1.0.0',
                true
            );
            wp_localize_script( 'mcp-sortable-items', 'mcp_sort_ajax', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'mcp_update_order_nonce' )
            ] );
        }
    }

    // Enqueue script for the import/export page
    if ( strpos( $hook, 'mcp-csv-import' ) !== false || strpos( $hook, 'mcp-csv-export' ) !== false ) {
        wp_enqueue_style(
            'mcp-import-export-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/import-export.css',
            [],
            '1.0.0'
        );
        wp_enqueue_script(
            'mcp-import-export-scripts',
            plugin_dir_url( __FILE__ ) . 'assets/js/import-export.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );
        wp_localize_script( 'mcp-import-export-scripts', 'mcp_ajax', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
    }

    // Enqueue scripts for the post list page (filtering)
    if ( $hook == 'edit.php' && in_array( get_post_type(), [ 'lesson', 'topic' ] ) ) {
        wp_enqueue_script(
            'mcp-admin-filters',
            plugin_dir_url( __FILE__ ) . 'assets/js/admin-filters.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );
        wp_localize_script( 'mcp-admin-filters', 'mcp_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ] );
    }
}
add_action( 'admin_enqueue_scripts', 'mcp_admin_enqueue_scripts' );

/**
 * Enqueue frontend scripts and styles.
 */
function mcp_frontend_enqueue_scripts() {
    if ( is_singular( ['course', 'lesson', 'topic'] ) || get_query_var( 'mcp_leitner_study' ) ) {
        $local_assets = get_option( 'mcp_local_assets', '0' ) === '1';
        $vendor_url = plugin_dir_url( __FILE__ ) . 'assets/vendor/';
        $vendor_path = plugin_dir_path( __FILE__ ) . 'assets/vendor/';

        // Always enqueue FontAwesome on frontend for these pages as it is used in templates
        $fa_css = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css';
        if ( $local_assets && file_exists( $vendor_path . 'fontawesome/css/all.min.css' ) ) {
            $fa_css = $vendor_url . 'fontawesome/css/all.min.css';
        }
        wp_enqueue_style( 'mcp-fontawesome', $fa_css, [], '6.5.1' );

        wp_enqueue_style(
            'mcp-custom-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/custom.css',
            [],
            '1.0.0'
        );
        wp_enqueue_style(
            'mcp-flashcard-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/flashcard.css',
            [],
            '1.0.0'
        );

        wp_enqueue_style(
            'mcp-leitner-styles',
            plugin_dir_url( __FILE__ ) . 'assets/css/leitner.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'mcp-leitner-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/leitner.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );

        wp_localize_script( 'mcp-leitner-js', 'mcp_leitner_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mcp_leitner_nonce' )
        ] );

        wp_enqueue_script(
            'mcp-accordion-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/mcp-accordion.js',
            [],
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mcp_frontend_enqueue_scripts' );

/**
 * Add viewport meta tag to disable zooming on course-related pages.
 */
function mcp_add_no_zoom_meta() {
    if ( is_singular( ['course', 'lesson', 'topic'] ) || get_query_var( 'mcp_leitner_study' ) ) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
    }
}
add_action( 'wp_head', 'mcp_add_no_zoom_meta', 1 );

/**
 * Load custom templates for CPTs.
 */
function mcp_load_custom_templates( $template ) {
    if ( get_query_var( 'mcp_leitner_study' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'page-templates/template-leitner-study.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    } elseif ( is_singular( 'topic' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-topic.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    } elseif ( is_singular( 'lesson' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-lesson.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    } elseif ( is_singular( 'course' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-course.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter( 'template_include', 'mcp_load_custom_templates' );

/**
 * Handle dedicated study mode template redirect.
 */
function mcp_study_mode_template_redirect() {
    if ( get_query_var( 'mcp_leitner_study' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'page-templates/template-leitner-study.php';
        if ( file_exists( $plugin_template ) ) {
            include $plugin_template;
            exit;
        }
    }
}
add_action( 'template_redirect', 'mcp_study_mode_template_redirect' );

/**
 * Add custom rewrite rules for study pages.
 */
function mcp_custom_rewrite_rules() {
    // Rule for Leitner Study
    add_rewrite_rule(
        '^leitner/([^/]+)/([^/]+)/([^/]+)/?$',
        'index.php?mcp_leitner_study=1&topic_slug=$matches[3]&course_slug=$matches[1]&lesson_slug=$matches[2]',
        'top'
    );
    // Rule for Course/Study - Topic
    add_rewrite_rule(
        '^(?:course|study)/([^/]+)/([^/]+)/([^/]+)/?$',
        'index.php?post_type=topic&name=$matches[3]&topic_slug=$matches[3]&lesson_slug=$matches[2]&course_slug=$matches[1]',
        'top'
    );
    // Rule for Course/Study - Lesson
    add_rewrite_rule(
        '^(?:course|study)/([^/]+)/([^/]+)/?$',
        'index.php?post_type=lesson&name=$matches[2]&lesson_slug=$matches[2]&course_slug=$matches[1]',
        'top'
    );
    // Rule for Course/Study - Course
    add_rewrite_rule(
        '^(?:course|study)/([^/]+)/?$',
        'index.php?post_type=course&name=$matches[1]&course_slug=$matches[1]',
        'top'
    );
}
add_action( 'init', 'mcp_custom_rewrite_rules' );

/**
 * Register custom query vars.
 */
function mcp_register_query_vars( $vars ) {
    $vars[] = 'course_slug';
    $vars[] = 'lesson_slug';
    $vars[] = 'topic_slug';
    $vars[] = 'mcp_leitner_study';
    return $vars;
}
add_filter( 'query_vars', 'mcp_register_query_vars' );

/**
 * Handle query modification for Leitner study page.
 */
function mcp_pre_get_posts( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( get_query_var( 'mcp_leitner_study' ) ) {
        $topic_slug = get_query_var( 'topic_slug' );
        if ( ! empty( $topic_slug ) ) {
            $query->set( 'post_type', 'topic' );
            $query->set( 'name', $topic_slug );

            $query->is_home = false;
            $query->is_single = true;
            $query->is_singular = true;
            $query->is_archive = false;
            $query->is_404 = false;
            $query->is_page = false;
        }
    }
}
add_action( 'pre_get_posts', 'mcp_pre_get_posts' );

/**
 * Disable canonical redirect for Leitner study pages.
 */
function mcp_disable_canonical_redirect( $redirect_url ) {
    if ( get_query_var( 'mcp_leitner_study' ) ) {
        return false;
    }
    return $redirect_url;
}
add_filter( 'redirect_canonical', 'mcp_disable_canonical_redirect' );

/**
 * Flush rewrite rules on plugin activation.
 */
function mcp_activate() {
    // Register CPTs to ensure they are available for flushing
    require_once plugin_dir_path( __FILE__ ) . 'includes/cpt-init.php';
    mcp_register_post_types(); // Correct function name

    // Create Leitner table
    require_once plugin_dir_path( __FILE__ ) . 'includes/leitner/class-mcp-leitner-repository.php';
    $leitner_repo = new MCP_Leitner_Repository();
    $leitner_repo->create_table();

    // Add the rewrite rule
    mcp_custom_rewrite_rules();

    // Flush the rewrite rules
    flush_rewrite_rules();
}

// Ensure rewrite rules are flushed on next load if needed (debugging helper)
add_action('init', function() {
    if (get_option('mcp_flush_rewrite_rules_once')) {
        mcp_custom_rewrite_rules();
        flush_rewrite_rules();
        delete_option('mcp_flush_rewrite_rules_once');
    }
}, 20);
register_activation_hook( __FILE__, 'mcp_activate' );

/**
 * Display admin notices.
 */
function mcp_admin_notices() {
    if ( $notice = get_transient( 'mcp-admin-notice' ) ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $notice ); ?></p>
        </div>
        <?php
        delete_transient( 'mcp-admin-notice' );
    }
}
add_action( 'admin_notices', 'mcp_admin_notices' );

/**
 * Custom permalink function.
 */
function mcp_get_permalink( $post_id ) {
    $post_type = get_post_type( $post_id );
    $slugs = [];

    switch ( $post_type ) {
        case 'topic':
            $topic_slug = get_post_meta( $post_id, '_mcp_english_slug', true );
            $lesson_id = get_post_meta( $post_id, '_mcp_lesson_id', true );
            if ( $lesson_id ) {
                $lesson_slug = get_post_meta( $lesson_id, '_mcp_english_slug', true );
                $course_id = get_post_meta( $lesson_id, '_mcp_course_id', true );
                if ( $course_id ) {
                    $course_slug = get_post_meta( $course_id, '_mcp_english_slug', true );
                    $slugs = [ $course_slug, $lesson_slug, $topic_slug ];
                }
            }
            break;
        case 'lesson':
            $lesson_slug = get_post_meta( $post_id, '_mcp_english_slug', true );
            $course_id = get_post_meta( $post_id, '_mcp_course_id', true );
            if ( $course_id ) {
                $course_slug = get_post_meta( $course_id, '_mcp_english_slug', true );
                $slugs = [ $course_slug, $lesson_slug ];
            }
            break;
        case 'course':
            $course_slug = get_post_meta( $post_id, '_mcp_english_slug', true );
            $slugs = [ $course_slug ];
            break;
    }

    if ( ! empty( $slugs ) ) {
        return home_url( '/course/' . implode( '/', $slugs ) . '/' );
    }

    return get_permalink( $post_id );
}

/**
 * AJAX handler for creating a new section type.
 */
function mcp_create_section_type_ajax_handler() {
    check_ajax_referer( 'mcp_create_section_type_nonce', 'nonce' );

    $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $icon_class = isset( $_POST['icon_class'] ) ? sanitize_text_field( $_POST['icon_class'] ) : '';
    $is_starred = isset( $_POST['is_starred'] ) && $_POST['is_starred'] === 'true' ? '1' : '0';

    if ( empty( $title ) ) {
        wp_send_json_error( [ 'message' => 'Title is required.' ] );
    }

    $post_data = [
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_type'   => 'section_type',
    ];

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
    }

    update_post_meta( $post_id, '_mcp_icon_class', $icon_class );
    update_post_meta( $post_id, '_mcp_is_starred', $is_starred );

    wp_send_json_success( [
        'id'    => $post_id,
        'title' => $title,
    ] );
}
add_action( 'wp_ajax_mcp_create_section_type', 'mcp_create_section_type_ajax_handler' );

/**
 * AJAX handler for getting lessons by course.
 */
function mcp_get_lessons_by_course_ajax_handler() {
    $course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;

    if ( ! $course_id ) {
        wp_send_json_error( [ 'message' => 'Invalid course ID.' ] );
    }

    $lessons = get_posts( [
        'post_type'  => 'lesson',
        'meta_key'   => '_mcp_course_id',
        'meta_value' => $course_id,
        'numberposts' => -1,
        'orderby'    => 'title',
        'order'      => 'ASC'
    ] );

    $data = [];
    foreach ( $lessons as $lesson ) {
        $data[] = [
            'id'    => $lesson->ID,
            'title' => $lesson->post_title,
        ];
    }

    wp_send_json_success( $data );
}
add_action( 'wp_ajax_mcp_get_lessons_by_course', 'mcp_get_lessons_by_course_ajax_handler' );

/**
 * AJAX handler for updating items order.
 */
function mcp_update_items_order_ajax_handler() {
    check_ajax_referer( 'mcp_update_order_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : [];

    if ( empty( $order ) ) {
        wp_send_json_error( [ 'message' => 'No order data provided.' ] );
    }

    // Unhook validation to prevent status changes during reordering
    remove_action( 'save_post', 'mcp_save_meta_box_data' );

    foreach ( $order as $index => $post_id ) {
        wp_update_post( [
            'ID'         => $post_id,
            'menu_order' => $index,
        ] );
    }

    // Re-hook validation
    add_action( 'save_post', 'mcp_save_meta_box_data' );

    wp_send_json_success( [ 'message' => 'Order updated successfully.' ] );
}
add_action( 'wp_ajax_mcp_update_items_order', 'mcp_update_items_order_ajax_handler' );
