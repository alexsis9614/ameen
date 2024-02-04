"use strict";

(function ($) {

    let swiper = new Swiper('.stm_lms_ameen_course_bundle__slider', {
        direction: 'vertical',
        slidesPerView: 1,
        height: 300,
        spaceBetween: 16,
        loop: true,
        autoplay: {
            delay: 0,
            pauseOnMouseEnter: true,
            disableOnInteraction: false,
        },
        loopAdditionalSlides: 2,
        speed: 3000,
        allowTouchMove: false
    });

})(jQuery);