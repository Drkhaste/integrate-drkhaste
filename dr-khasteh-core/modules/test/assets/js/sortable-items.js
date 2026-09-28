jQuery(document).ready(function($) {
    $('.mtp-sortable-list').sortable({
        update: function(event, ui) {
            var order = $(this).sortable('toArray', { attribute: 'data-id' });
            $.ajax({
                url: mtp_sort_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mtp_update_items_order',
                    order: order,
                    nonce: mtp_sort_ajax.nonce
                }
            });
        }
    });
});
