<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mtp_get_permalink($post_id) {
    $post_type = get_post_type($post_id);
    $slug = get_post_meta($post_id, '_english_slug', true);
    if (empty($slug)) $slug = get_post_field('post_name', $post_id);

    if ($post_type == 'course') return home_url("/test/$slug/");

    if ($post_type == 'lesson') {
        $c_id = get_post_meta($post_id, '_course_id', true);
        $c_slug = get_post_meta($c_id, '_english_slug', true);
        if (empty($c_slug)) $c_slug = get_post_field('post_name', $c_id);
        return home_url("/test/$c_slug/$slug/");
    }

    if ($post_type == 'topic') {
        $l_id = get_post_meta($post_id, '_lesson_id', true);
        $l_slug = get_post_meta($l_id, '_english_slug', true);
        if (empty($l_slug)) $l_slug = get_post_field('post_name', $l_id);

        $c_id = get_post_meta($l_id, '_course_id', true);
        $c_slug = get_post_meta($c_id, '_english_slug', true);
        if (empty($c_slug)) $c_slug = get_post_field('post_name', $c_id);

        return home_url("/test/$c_slug/$l_slug/$slug/");
    }

    return get_permalink($post_id);
}

function mtp_handle_cascading_delete( $post_id ) {
    $post_type = get_post_type( $post_id );
    if ( $post_type === 'course' ) {
        $lessons = get_posts(['post_type' => 'lesson', 'meta_key' => '_course_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($lessons as $id) wp_delete_post($id, true);
    } elseif ( $post_type === 'lesson' ) {
        $topics = get_posts(['post_type' => 'topic', 'meta_key' => '_lesson_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($topics as $id) wp_delete_post($id, true);
    } elseif ( $post_type === 'topic' ) {
        $tests = get_posts(['post_type' => 'test', 'meta_key' => '_topic_id', 'meta_value' => $post_id, 'fields' => 'ids', 'numberposts' => -1]);
        foreach($tests as $id) wp_delete_post($id, true);
    }
}
add_action( 'before_delete_post', 'mtp_handle_cascading_delete' );

/**
 * Render test explanation with support for Option Analysis tables.
 *
 * @param string $explanation
 * @return string
 */
if ( ! function_exists( 'dr_khasteh_parse_markdown_inline' ) ) {
    function dr_khasteh_parse_markdown_inline( $text ) {
        $text = trim( $text );
        // Strip markdown headings like ### at start
        $text = preg_replace( '/^#{1,6}\s*(?:توضیحات?\s*تکمیلی)?[\r\n\s]*/u', '', $text );
        // Convert **bold**
        $text = preg_replace( '/\*\*(.*?)\*\*/u', '<strong>$1</strong>', $text );
        // Convert *italic*
        $text = preg_replace( '/\*(.*?)\*/u', '<em>$1</em>', $text );
        // Convert newlines to <br>
        $text = nl2br( trim( $text ) );
        return $text;
    }
}

/**
 * Render test explanation with support for Option Analysis tables.
 *
 * @param string $explanation
 * @return string
 */
function dr_khasteh_render_explanation( $explanation ) {
    if ( empty( trim( $explanation ) ) ) {
        return '';
    }

    $raw = trim( $explanation );
    $analysis_items = [];
    $additional_explanation = '';
    $has_table = false;

    // 1. Try parsing Markdown table
    if ( preg_match( '/(?:\|[^\n]+\|\r?\n){2,}/u', $raw, $matches, PREG_OFFSET_CAPTURE ) ) {
        $table_str = $matches[0][0];
        $start_pos = $matches[0][1];
        $end_pos = $start_pos + strlen( $table_str );

        $lines = explode( "\n", str_replace( "\r", "", trim( $table_str ) ) );
        $rows = [];
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) || strpos( $line, '|' ) === false ) continue;

            $cols = array_map( 'trim', explode( '|', trim( $line, '|' ) ) );

            // Check if separator line
            $is_separator = true;
            foreach ( $cols as $col ) {
                if ( ! preg_match( '/^[\s:\-]+$/u', $col ) ) {
                    $is_separator = false;
                    break;
                }
            }
            if ( $is_separator ) continue;

            if ( count( $cols ) >= 2 ) {
                $rows[] = $cols;
            }
        }

        if ( count( $rows ) >= 2 ) {
            $header = array_map( 'mb_strtolower', $rows[0] );

            $status_idx = -1;
            $option_idx = -1;
            $exp_idx = -1;

            foreach ( $header as $idx => $h ) {
                if ( in_array( $h, [ 'وضعیت', 'status', 'نتیجه', 'صحت' ], true ) ) {
                    $status_idx = $idx;
                } elseif ( in_array( $h, [ 'گزینه', 'option', 'شماره گزینه', 'نام گزینه' ], true ) ) {
                    $option_idx = $idx;
                } elseif ( in_array( $h, [ 'توضیح', 'توضیحات', 'پاسخ', 'تحلیل', 'explanation', 'reason' ], true ) ) {
                    $exp_idx = $idx;
                }
            }

            if ( $status_idx === -1 ) $status_idx = 0;
            if ( $option_idx === -1 ) $option_idx = ( count( $header ) > 2 ) ? 1 : -1;
            if ( $exp_idx === -1 ) $exp_idx = ( count( $header ) > 2 ) ? 2 : 1;

            for ( $i = 1; $i < count( $rows ); $i++ ) {
                $row = $rows[$i];
                $raw_status = isset( $row[$status_idx] ) ? trim( $row[$status_idx] ) : '';
                $raw_opt    = ( $option_idx !== -1 && isset( $row[$option_idx] ) ) ? trim( $row[$option_idx] ) : '';
                $raw_exp    = isset( $row[$exp_idx] ) ? trim( $row[$exp_idx] ) : '';

                $status_lower = mb_strtolower( $raw_status );
                $clean_status = 'incorrect';

                if ( in_array( $status_lower, [ 'incorrect', 'false', 'نادرست', 'غلط', 'اشتباه', '۰', '0' ], true )
                     || strpos( $status_lower, 'incorrect' ) !== false
                     || strpos( $status_lower, 'نادرست' ) !== false
                     || strpos( $status_lower, 'غلط' ) !== false
                     || strpos( $status_lower, 'اشتباه' ) !== false ) {
                    $clean_status = 'incorrect';
                } elseif ( in_array( $status_lower, [ 'correct', 'true', 'صحیح', 'درست', '۱', '1', 'تایید' ], true )
                     || strpos( $status_lower, 'correct' ) !== false
                     || strpos( $status_lower, 'صحیح' ) !== false
                     || ( strpos( $status_lower, 'درست' ) !== false && strpos( $status_lower, 'نادرست' ) === false ) ) {
                    $clean_status = 'correct';
                }

                $clean_opt = preg_replace( '/[✓✗✔❌✕✖]/u', '', $raw_opt );
                $clean_exp = preg_replace( '/[✓✗✔❌✕✖]/u', '', $raw_exp );

                if ( ! empty( $clean_exp ) || ! empty( $clean_opt ) ) {
                    $analysis_items[] = [
                        'status'      => $clean_status,
                        'option'      => trim( $clean_opt ),
                        'explanation' => trim( $clean_exp ),
                    ];
                }
            }

            if ( ! empty( $analysis_items ) ) {
                $has_table = true;
                $additional_explanation = trim( substr( $raw, $end_pos ) );
            }
        }
    }

    // 2. Fallback: Check HTML <table> if no Markdown table matched
    if ( ! $has_table && ( strpos( $raw, '<table' ) !== false && strpos( $raw, '</table>' ) !== false ) ) {
        if ( preg_match( '/<table[^>]*>(.*?)<\/table>/is', $raw, $matches, PREG_OFFSET_CAPTURE ) ) {
            $table_html = $matches[0][0];
            $end_pos = $matches[0][1] + strlen( $table_html );

            if ( preg_match_all( '/<tr[^>]*>(.*?)<\/tr>/is', $table_html, $tr_matches ) ) {
                $rows = [];
                foreach ( $tr_matches[1] as $tr_content ) {
                    if ( preg_match_all( '/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $tr_content, $cell_matches ) ) {
                        $rows[] = array_map( 'strip_tags', array_map( 'trim', $cell_matches[1] ) );
                    }
                }

                if ( count( $rows ) >= 2 ) {
                    for ( $i = 1; $i < count( $rows ); $i++ ) {
                        $row = $rows[$i];
                        $status_str = isset( $row[0] ) ? trim( $row[0] ) : '';
                        $opt_str    = ( count( $row ) > 2 && isset( $row[1] ) ) ? trim( $row[1] ) : '';
                        $exp_str    = isset( $row[ count($row) - 1 ] ) ? trim( $row[ count($row) - 1 ] ) : '';

                        $status_lower = mb_strtolower( $status_str );
                        $clean_status = ( strpos( $status_lower, 'correct' ) !== false || strpos( $status_lower, 'صحیح' ) !== false ) ? 'correct' : 'incorrect';

                        $analysis_items[] = [
                            'status'      => $clean_status,
                            'option'      => preg_replace( '/[✓✗✔❌✕✖]/u', '', $opt_str ),
                            'explanation' => preg_replace( '/[✓✗✔❌✕✖]/u', '', $exp_str ),
                        ];
                    }
                    if ( ! empty( $analysis_items ) ) {
                        $has_table = true;
                        $additional_explanation = trim( substr( $raw, $end_pos ) );
                    }
                }
            }
        }
    }

    if ( ! $has_table || empty( $analysis_items ) ) {
        return dr_khasteh_parse_markdown_inline( $explanation );
    }

    // Clean up additional explanation
    if ( ! empty( $additional_explanation ) ) {
        $additional_explanation = trim( $additional_explanation );
        $additional_explanation = preg_replace( '/^(?:#+\s*)?توضیحات?\s*تکمیلی:?[\r\n\s]*/u', '', $additional_explanation );
    }

    // Render Option Analysis HTML
    $roman_numerals = [ 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X' ];

    $html = '<div class="mtp-option-analysis-container" style="margin-top: 15px;">';
    $html .= '<div class="mtp-option-analysis-grid" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">';

    foreach ( $analysis_items as $index => $item ) {
        $roman = isset( $roman_numerals[$index] ) ? $roman_numerals[$index] : ($index + 1);
        $is_correct = ( $item['status'] === 'correct' );

        $bg_color     = $is_correct ? '#f0fdf4' : '#fef2f2';
        $border_color = $is_correct ? '#22c55e' : '#ef4444';
        $text_color   = $is_correct ? '#14532d' : '#991b1b';

        $html .= '<div class="mtp-analysis-card ' . ( $is_correct ? 'is-correct' : 'is-incorrect' ) . '" style="background: ' . $bg_color . '; border-right: 5px solid ' . $border_color . '; border-radius: 10px; padding: 14px 18px; position: relative; box-shadow: 0 1px 4px rgba(0,0,0,0.02);">';

        // Minimal Roman Numeral Badge in top-left corner
        $html .= '<div style="position: absolute; top: 10px; left: 12px; font-weight: 900; font-size: 0.85rem; color: ' . $text_color . '; opacity: 0.75; font-family: monospace;">' . $roman . '.</div>';

        if ( ! empty( $item['option'] ) ) {
            $html .= '<div style="font-weight: 800; font-size: 0.95rem; color: ' . $text_color . '; margin-bottom: 6px; padding-left: 30px;">' . dr_khasteh_parse_markdown_inline( $item['option'] ) . '</div>';
        }

        $html .= '<div style="font-family: var(--current-font, inherit); font-size: var(--current-font-size, 1rem); line-height: 1.7; color: #1e293b;">' . dr_khasteh_parse_markdown_inline( $item['explanation'] ) . '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';

    if ( ! empty( trim( $additional_explanation ) ) ) {
        $html .= '<div class="mtp-additional-explanation" style="background: var(--bg-card, #ffffff); border: 1px dashed #cbd5e1; border-radius: 10px; padding: 16px 20px; margin-top: 15px;">';
        $html .= '<strong style="display: block; color: var(--primary, #0f766e); margin-bottom: 8px; font-size: 1.05rem;"><i class="fa-solid fa-circle-info"></i> توضیحات تکمیلی:</strong>';
        $html .= '<div style="font-family: var(--current-font, inherit); font-size: var(--current-font-size, 1rem); line-height: 1.8;">' . dr_khasteh_parse_markdown_inline( $additional_explanation ) . '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}
