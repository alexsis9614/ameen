(function ($) {
    $(window).on('load', function() {
       let $element = $('.ameen_main-page-carousel .swiper-container');

       if ( $element.length ) {
            let swiper = $element.data('swiper');

            if ( 'undefined' !== typeof swiper ) {
                swiper.params.centeredSlides = true;
                swiper.params.slideToClickedSlide = true;
                swiper.update();
            }
       }
    });
})( jQuery );

