(function ($) {
    $(window).load( function () {
        let swiper = $( '#main-carousel .swiper-container' ).data( 'swiper' );

        swiper.params.centeredSlides = true;
        swiper.params.autoplay = false;
        swiper.slideTo(0);
    });
})( jQuery );