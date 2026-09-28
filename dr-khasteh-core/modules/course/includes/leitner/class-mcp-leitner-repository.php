<?php
if (!defined('ABSPATH')) exit;

class MCP_Leitner_Repository {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'sl_user_progress';
    }

    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            card_id bigint(20) NOT NULL,
            box_id bigint(20) NOT NULL,
            next_review datetime DEFAULT NULL,
            last_status tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY user_card (user_id, card_id),
            KEY box_user_time (box_id, user_id, next_review)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_user_progress_for_topic($user_id, $topic_id) {
        global $wpdb;

        // In this integrated version, box_id refers to topic_id
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT card_id, next_review FROM $this->table_name WHERE user_id = %d AND box_id = %d",
                $user_id, $topic_id
            ), OBJECT_K
        );
    }

    public function update_progress($user_id, $card_id, $topic_id, $status) {
        global $wpdb;

        $current_time = current_time('mysql');
        $next_review = null;

        if ($status == 1) { // Fail
            $next_review = $current_time;
        } elseif ($status == 2) { // Doubt
            $next_review = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($current_time)));
        } elseif ($status == 3) { // Pass
            $next_review = date('Y-m-d H:i:s', strtotime('+3 days', strtotime($current_time)));
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $this->table_name WHERE user_id = %d AND card_id = %d",
            $user_id, $card_id
        ));

        if ($exists) {
            return $wpdb->update(
                $this->table_name,
                array(
                    'next_review' => $next_review,
                    'last_status' => $status,
                    'box_id'      => $topic_id
                ),
                array('id' => $exists),
                array('%s', '%d', '%d'),
                array('%d')
            );
        } else {
            return $wpdb->insert(
                $this->table_name,
                array(
                    'user_id'     => $user_id,
                    'card_id'     => $card_id,
                    'box_id'      => $topic_id,
                    'next_review' => $next_review,
                    'last_status' => $status
                ),
                array('%d', '%d', '%d', '%s', '%d')
            );
        }
    }
}
