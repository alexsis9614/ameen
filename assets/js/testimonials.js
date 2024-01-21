(function ($) {
    $(window).load( function () {
        let swiper = $( '#main-carousel .swiper-container' ).data( 'swiper' );

        console.log(swiper);
        swiper.params.loop = false;
        swiper.params.centeredSlides = true;
        swiper.autoplay.running = false;
        swiper.update();
    });
})( jQuery );