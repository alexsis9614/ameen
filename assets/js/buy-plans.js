"use strict";

(function ($) {
    $(document).on('click', '[data-course-id]', function (e) {
        const $this   = $(this),
              item_id = $this.data('course-id'),
              plan    = $this.data('course-plan');

        if (typeof item_id === 'undefined') {
            window.location = $this.attr('href');
            return false;
        }

        $.ajax({
            url: stm_lms_ajaxurl,
            dataType: 'json',
            context: this,
            data: {
                action: 'stm_lms_child_add_to_cart',
                nonce: stm_lms_nonces['stm_lms_add_to_cart'],
                item_id: item_id,
                plan: plan
            },
            beforeSend: function beforeSend() {
                $this.addClass('loading');
            },
            complete: function complete(data) {
                data = data['responseJSON'];
                $this.removeClass('loading');
                $this.find('span').text(data['text']);

                if (data['cart_url']) {
                    if (data['redirect']) window.location = data['cart_url'];
                    $this.attr('href', data['cart_url']).removeAttr('data-buy-course');
                }
            }
        });
        e.preventDefault();
    });
})(jQuery);