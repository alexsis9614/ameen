(function ($) {
    $(window).on('load', function() {
       let $element = $('.ameen_main-page-carousel .swiper-container');

       if ( $element.length ) {
            let swiper = $element.data('swiper');

            if ( 'undefined' !== typeof swiper ) {
                swiper.params.centeredSlides = true;
                swiper.update();
            }
       }
    });
    // $(window).on('elementor/frontend/init', function ( ) {
    //     const addHandler = ( $element ) => {
    //         if ( ! $element.hasClass('ameen_main-page-carousel') ) {
    //             return $element;
    //         }
    //
    //         let container = $element.find( '.swiper-container' );
    //
    //         console.log(container[0]);
    //
    //         let settings = $element.attr('data-settings');
    //
    //         if ( settings ) {
    //             settings = JSON.parse(settings);
    //
    //             settings.centeredSlides = true;
    //
    //             $element.attr( 'data-settings', settings );
    //         }
    //
    //         let swiperConfig = {
    //             // autoplay: {
    //             //     enabled: true
    //             // },
    //             // loop: false,
    //             // centeredSlides: true
    //         };
    //
    //         //
    //         // console.log($element);
    //         // console.log();
    //         // console.log(container.first().swiper.params);
    //         //
    //         // container.first().swiper.params.centeredSlides = true;
    //         // container.first().swiper.params.update();
    //
    //         // if ( 'undefined' === typeof Swiper ) {
    //         //     const asyncSwiper = elementorFrontend.utils.swiper;
    //         //
    //         //     new asyncSwiper( container, swiperConfig ).then(
    //         //         ( newSwiperInstance ) => {
    //         //             console.log( 'New Swiper instance is ready: ', newSwiperInstance );
    //         //
    //         //             $element = newSwiperInstance;
    //         //         }
    //         //     );
    //         // } else {
    //         //     $element = new Swiper( container, swiperConfig );
    //         // }
    //         //
    //         // return $element;
    //     };
    //
    //     elementorFrontend.hooks.addAction( 'frontend/element_handler_ready/media-carousel.default', addHandler );
    // });
})( jQuery );

