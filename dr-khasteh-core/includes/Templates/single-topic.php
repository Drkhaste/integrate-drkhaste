<?php
    $topic_id  = get_the_ID();
    $lesson_id = get_post_meta( $topic_id, '_lesson_id', true );
    $course_id = $lesson_id ? get_post_meta( $lesson_id, '_course_id', true ) : null;

    // Enqueue Mind Map assets BEFORE get_header() so CSS/JS are placed in wp_head()
    $accordions = get_post_meta( $topic_id, '_topic_mindmaps', true );
    if ( ! empty( $accordions ) ) {
        wp_enqueue_style( 'jsmind' );
        wp_enqueue_style( 'mms-frontend' );
        wp_enqueue_script( 'jsmind' );
        wp_enqueue_script( 'mindmap-studio-frontend' );

        $inline_settings = array(
            'watermark'   => array(
                'text'            => get_option( 'mind_map_watermark_text',    '' ),
                'size'            => (float) get_option( 'mind_map_watermark_size',    14 ),
                'spacing_desktop' => (int)   get_option( 'mind_map_watermark_spacing', 220 ),
                'spacing_mobile'  => (int)   round( get_option( 'mind_map_watermark_spacing', 220 ) / 2 ),
                'color'           => get_option( 'mind_map_watermark_color',   '#94a3b8' ),
                'opacity'         => (float) get_option( 'mind_map_watermark_opacity', 0.18 ),
            ),
            'theme_light'   => get_option( 'mind_map_theme_light',        'primary' ),
            'theme_dark'    => get_option( 'mind_map_theme_dark',         'primary' ),
            'line_color'    => get_option( 'mind_map_line_color',         '#94a3b8' ),
            'line_style'    => get_option( 'mind_map_line_style',         'bezier' ),
            'line_width'    => (float) get_option( 'mind_map_line_width', 2 ),
            'border_radius' => (int)   get_option( 'mind_map_node_border_radius', 12 ),
        );
        wp_add_inline_script(
            'mindmap-studio-frontend',
            'window.mindMapStudioSettings = ' . wp_json_encode( $inline_settings ) . ';',
            'before'
        );
    }

    get_header();
?>

<main class="container">
    <div class="breadcrumb-container">
        <?php if ( $course_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo class_exists('Mind_Map_Studio') ? Mind_Map_Studio::get_mms_permalink($course_id) : get_permalink($course_id); ?>"><?php echo get_the_title($course_id); ?></a>
            </div>
        <?php endif; ?>
        <?php if ( $lesson_id ) : ?>
            <div class="breadcrumb-item">
                <a href="<?php echo class_exists('Mind_Map_Studio') ? Mind_Map_Studio::get_mms_permalink($lesson_id) : get_permalink($lesson_id); ?>"><?php echo get_the_title($lesson_id); ?></a>
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
            $topic_slug  = get_post_meta( $topic_id, '_english_slug', true );
            $test_url    = home_url( "/test/{$course_slug}/{$lesson_slug}/{$topic_slug}/" );
            $mms_url     = class_exists('Mind_Map_Studio') ? Mind_Map_Studio::get_mms_permalink($topic_id) : '';
        ?>
        <div style="margin: 20px 0; display:flex; gap:12px; flex-wrap:wrap;">
            <a href="<?php echo esc_url( $test_url ); ?>" class="btn-topic-test" style="display:inline-block; padding:12px 30px; font-size:1.1rem;"><?php echo esc_html__( 'شروع تست‌های این مبحث', 'medical-test-plugin' ); ?></a>
            <?php if ( $mms_url ) : ?>
                <a href="<?php echo esc_url( $mms_url ); ?>" class="btn-topic-mindmap" style="display:inline-block; padding:12px 30px; font-size:1.1rem; background: #0f766e; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold;"><?php echo esc_html__( 'نقشه ذهنی کامل مبحث', 'dr-khasteh-core' ); ?></a>
            <?php endif; ?>
        </div>

        <div class="topic-content" style="line-height:1.8;">
            <?php the_content(); ?>
        </div>

        <?php if ( ! empty( $accordions ) ) : ?>
            <div class="topic-mindmaps-section" style="margin-top:40px; padding-top:20px; border-top:2px dashed #e2e8f0;">
                <h3 style="color:#0f766e; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:1.4rem;">⬡</span> <?php echo esc_html__( 'نقشه‌های ذهنی این مبحث', 'dr-khasteh-core' ); ?>
                </h3>
                <?php foreach ( $accordions as $a_index => $accordion ) : ?>
                    <details class="section-card" <?php echo ( $a_index === 0 ) ? 'open' : ''; ?> style="margin-bottom:15px; border:1px solid #cbd5e1; border-radius:10px; padding:12px; background:#fff;">
                        <summary style="cursor:pointer; font-weight:bold; font-size:1.1rem; color:#1e293b; display:flex; justify-space-between; align-items:center;">
                            <div class="sec-title" style="display:flex; align-items:center; gap:8px;">
                                <span>📌</span> <?php echo esc_html( $accordion['title'] ); ?>
                            </div>
                        </summary>
                        <div class="card-content" style="padding-top:15px;">
                            <?php if ( ! empty( $accordion['mindmaps'] ) ) : ?>
                                <?php foreach ( $accordion['mindmaps'] as $m_index => $mindmap ) :
                                    $unique_id   = "mms_front_topic_{$a_index}_{$m_index}";
                                    $node_styles = isset( $mindmap['node_styles'] ) ? $mindmap['node_styles'] : '{}';
                                ?>
                                    <div class="mms-front-mindmap-item" style="margin-bottom: 25px;">
                                        <h4 style="margin-bottom: 10px; border-right: 3px solid #0f766e; padding-right: 10px; color:#334155;"><?php echo esc_html( $mindmap['title'] ); ?></h4>
                                        <div class="mindmap-studio-wrapper" style="width:100%; position:relative;">
                                            <div class="mindmap-studio-capture" id="capture_<?php echo $unique_id; ?>" style="width:100%; position:relative;">
                                                <div
                                                    id="<?php echo $unique_id; ?>"
                                                    class="mindmap-studio-container"
                                                    data-mindmap-data="<?php echo esc_attr( $mindmap['data'] ); ?>"
                                                    data-mindmap-layout="<?php echo esc_attr( $mindmap['layout'] ); ?>"
                                                    data-node-styles="<?php echo esc_attr( $node_styles ); ?>"
                                                    data-line-color="<?php echo esc_attr( $inline_settings['line_color'] ); ?>"
                                                    data-line-style="<?php echo esc_attr( $inline_settings['line_style'] ); ?>"
                                                    data-line-width="<?php echo esc_attr( $inline_settings['line_width'] ); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .mindmap-studio-container jmnode {
        font-family: inherit !important;
        border-radius: <?php echo isset($inline_settings['border_radius']) ? (int) $inline_settings['border_radius'] : 12; ?>px !important;
    }
    .mindmap-studio-container { direction: ltr !important; overflow: hidden !important; }
    .mindmap-studio-container jmexpander { display: none !important; }
</style>

<?php get_footer(); ?>
