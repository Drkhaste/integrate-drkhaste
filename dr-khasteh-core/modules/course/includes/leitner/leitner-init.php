<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'class-mcp-leitner-repository.php';
require_once plugin_dir_path(__FILE__) . 'class-mcp-leitner-service.php';

class MCP_Leitner_Hooks {
    private $service;

    public function __construct() {
        $repository = new MCP_Leitner_Repository();
        $this->service = new MCP_Leitner_Service($repository);

        add_action('wp_ajax_mcp_leitner_record_progress', array($this, 'ajax_record_progress'));
    }

    public function ajax_record_progress() {
        check_ajax_referer('mcp_leitner_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }

        $user_id = get_current_user_id();
        $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;

        if (!$card_id || !$topic_id || !$status) {
            wp_send_json_error(array('message' => 'Missing parameters'));
        }

        $result = $this->service->record_progress($user_id, $card_id, $topic_id, $status);

        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Failed to save progress'));
        }
    }
}

new MCP_Leitner_Hooks();
