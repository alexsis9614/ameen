(function ($) {
    $(window).on('elementor/frontend/init', function ( ) {
        const addHandler = ( $element ) => {
            if ( ! $element.hasClass('ameen_main-page-carousel') ) {
                return $element;
            }

            let swiperConfig = {
                // autoplay: {
                //     enabled: true
                // },
                loop: false,
                centeredSlides: true
            };

            let container = $element.find( '.swiper-container' );

            if ( 'undefined' === typeof Swiper ) {
                const asyncSwiper = elementorFrontend.utils.swiper;

                new asyncSwiper( container, swiperConfig ).then(
                    ( newSwiperInstance ) => {
                        console.log( 'New Swiper instance is ready: ', newSwiperInstance );

                        $element = newSwiperInstance;
                    }
                );
            } else {
                $element = new Swiper( container, swiperConfig );
            }

            return $element;
        };

        elementorFrontend.hooks.addAction( 'frontend/element_ready/media-carousel.default', addHandler );
    });
})( jQuery );

