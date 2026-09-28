<?php
if (!defined('ABSPATH')) exit;

class MCP_Leitner_Service {
    private $repository;

    public function __construct($repository) {
        $this->repository = $repository;
    }

    public function get_topic_cards_categorized($user_id, $topic_id) {
        $all_cards = get_posts([
            'post_type' => 'flashcard',
            'meta_key' => '_topic_id',
            'meta_value' => $topic_id,
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'ASC'
        ]);

        $progress = $this->repository->get_user_progress_for_topic($user_id, $topic_id);
        $current_time = current_time('mysql');

        $categories = [
            'ready' => [],
            'h24'   => [],
            'd3'    => []
        ];

        $time_24h = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($current_time)));
        // We add a little buffer as in the original plugin if needed, but let's stick to simple logic for now
        // Simple logic:
        // Ready: no progress OR next_review <= current_time
        // 24h: current_time < next_review <= 24h from now
        // 3d: next_review > 24h from now

        foreach ($all_cards as $card) {
            $cid = $card->ID;
            $card_meta = isset($progress[$cid]) ? $progress[$cid] : null;
            $next_review = $card_meta ? $card_meta->next_review : null;

            if (!$card_meta || !$next_review || $next_review <= $current_time) {
                $categories['ready'][] = $card;
            } elseif ($next_review <= $time_24h) {
                $categories['h24'][] = $card;
            } else {
                $categories['d3'][] = $card;
            }
        }

        return $categories;
    }

    public function record_progress($user_id, $card_id, $topic_id, $status) {
        return $this->repository->update_progress($user_id, $card_id, $topic_id, $status);
    }
}
