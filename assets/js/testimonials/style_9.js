"use strict";

(function ($) {
    $(window).on('load', function () {
        t_carousel();
    });

    function t_carousel() {
        let owlRtl = false;

        if ($('body').hasClass('rtl')) {
            owlRtl = true;
        }

        $('.stm_testimonials_wrapper_style_9').each(function () {
            let $this  = $(this),
                slides = $this.data('slides');

            $this.owlCarousel({
                center: true,
                rtl: owlRtl,
                nav: false,
                dots: true,
                items: slides,
                autoplay: false,
                autoplayHoverPause: true,
                loop: (slides > 1),
                slideBy: 1,
                margin: 0,
                responsive: {
                    0: {
                        items: 1
                    },
                    700: {
                        items: 2
                    },
                    992: {
                        items: 3
                    },
                    1200: {
                        items: slides
                    }
                }
            });
        });
    }
})(jQuery);