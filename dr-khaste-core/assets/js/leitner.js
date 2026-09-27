jQuery(document).ready(function($) {
    // --- Global Card Flip Logic ---
    window.mcpFlipCard = function(element) {
        $(element).toggleClass('flipped');
    };

    // --- Leitner Submission Logic ---
    window.mcpSubmitLeitner = function(event, btn, status) {
        event.stopPropagation(); // Prevent card from flipping back when clicking buttons

        const $item = $(btn).closest('.fc-item');
        const cardId = $item.data('id');
        const topicId = $item.data('topic');

        // Disable all buttons in this card during request
        const $buttons = $item.find('.leitner-actions button');
        $buttons.prop('disabled', true);
        const originalText = $(btn).text();
        $(btn).text('...');

        $.ajax({
            url: mcp_leitner_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mcp_leitner_record_progress',
                nonce: mcp_leitner_ajax.nonce,
                card_id: cardId,
                topic_id: topicId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    const $tabContent = $item.closest('.leitner-tab-content');
                    const currentTabId = $tabContent.attr('id');

                    let targetTabId = '';
                    if (status === 2) targetTabId = 'h24';
                    if (status === 3) targetTabId = 'd3';
                    if (status === 1) targetTabId = 'ready';

                    // Update counts
                    if (currentTabId) {
                        const $currentCount = $('#mcp-count-' + currentTabId);
                        $currentCount.text(parseInt($currentCount.text()) - 1);

                        if (targetTabId && targetTabId !== currentTabId) {
                            const $targetCount = $('#mcp-count-' + targetTabId);
                            $targetCount.text(parseInt($targetCount.text()) + 1);

                            // Clone and move to target tab
                            const $clone = $item.clone();
                            $clone.removeClass('flipped');
                            $clone.find('.leitner-actions button').prop('disabled', false);

                            // Restore button texts in clone
                            $clone.find('.btn-fail').text('۱. بلد نبودم');
                            $clone.find('.btn-doubt').text('۲. با شک بلد بودم');
                            $clone.find('.btn-pass').text('۳. مسلط بودم');

                            $('#' + targetTabId).append($clone);
                        }
                    }

                    // Animate removal
                    $item.css('transition', 'all 0.4s ease');
                    $item.css({
                        'opacity': '0',
                        'transform': 'scale(0.8) translateY(-20px)'
                    });

                    setTimeout(function() {
                        $item.remove();
                    }, 400);

                } else {
                    alert('خطا در ثبت اطلاعات: ' + (response.data.message || 'Unknown error'));
                    $buttons.prop('disabled', false);
                    $(btn).text(originalText);
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور');
                $buttons.prop('disabled', false);
                $(btn).text(originalText);
            }
        });
    };

    // Helper to get button text (not strictly needed now as we store originalText)
    function getButtonText(status) {
        if (status === 1) return '۱. بلد نبودم';
        if (status === 2) return '۲. با شک بلد بودم';
        if (status === 3) return '۳. مسلط بودم';
        return '';
    }
});
