"use strict";

(function ($) {
    let YTPlayer, VPlayer, PlayerType, videoId, sectionVideo = 'review-video';

    $(window).on('load', function () {
        let modal = $('#review-video-modal');

        t_carousel();

        $('.testimonials-play').click(function () {
            let $this   = $(this),
                type    = $this.data('type'),
                id      = 'video-' + type;

            PlayerType = type;

            videoId = $this.data('id');

            if ( ! $( '#' + id ).length ) {
                let tag = document.createElement('script');

                tag.id = id;

                if (type === 'youtube') {
                    tag.src = "https://www.youtube.com/iframe_api";
                } else if (type === 'vimeo') {
                    tag.src = "https://player.vimeo.com/api/player.js";
                }
                let firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }
        });

        modal.on('hide.bs.modal', function () {
            if ( PlayerType === 'youtube' && YTPlayer ) {
                YTPlayer.pauseVideo();
            }
            else if ( PlayerType === 'vimeo' && VPlayer ) {
                VPlayer.pause();
            }

            if ( PlayerType ) {
                let section = $('#' + sectionVideo),
                    parent = section.parent();

                section.remove();

                parent.append('<div id="'+ sectionVideo +'">');
            }
        });

        modal.on('shown.bs.modal', function () {
            if ( PlayerType === 'youtube' ) {
                onYouTubeIframeAPIReady(sectionVideo, videoId);
            }
            else if ( PlayerType === 'vimeo' ) {
                onVimeoIframeAPIReady(sectionVideo, videoId);
            }
        });
    });

    function onYouTubeIframeAPIReady( element, id ) {
        if ( $( '#' + element ).length && id ) {
            YTPlayer = new YT.Player(element, {
                height: 500,
                videoId: id,
                playerVars: {
                    'iv_load_policy': 3,
                    'playsinline': 1,
                    'rel': 0,
                    'modestbranding': 1,
                    'showinfo': 0,
                    'origin': window.location.origin
                },
                events: {
                    'onReady': function (event) {
                        event.target.playVideo();
                    }
                }
            });
        }
    }

    function onVimeoIframeAPIReady( element, id ) {
        if ( $( '#' + element ).length && id ) {
            VPlayer = new Vimeo.Player(element, {
                height: 500,
                id: id,
                autoplay: true
            });
        }
    }

    function t_carousel() {
        let owlRtl = false;

        if ($('body').hasClass('rtl')) {
            owlRtl = true;
        }

        $('.stm_testimonials_wrapper_style_9').each(function () {
            let $this  = $(this),
                slides = $this.data('slides');

            $this.on('initialized.owl.carousel', function(event) {
                let target = $( event.currentTarget ),
                    activeItem = target.find('.owl-item.active');

                activeItem.prev().prev().addClass('pre-prev-item');
                activeItem.next().next().addClass('pre-next-item');

                activeItem.prev().addClass('prev-item');
                activeItem.next().addClass('next-item');
            });

            $this.owlCarousel({
                center: true,
                rtl: owlRtl,
                nav: false,
                dots: true,
                // items: slides,
                autoplay: false,
                autoplayHoverPause: true,
                loop: (slides > 1),
                slideBy: 1,
                margin: 0,
                items: 1,
                responsive: {
                    0: {
                        stagePadding: 50
                    },
                    700: {
                        stagePadding: 100
                    },
                    992: {
                        stagePadding: 200,
                    },
                }
            });

            $('.owl-item').click(function() {
                if( $(this).next().hasClass('center') ){
                    // scroll to prev
                    $this.trigger('prev.owl.carousel');
                }
                if( $(this).prev().hasClass('center') ){
                    // scroll to next
                    $this.trigger('next.owl.carousel');
                }
            })

            $this.on('changed.owl.carousel', function(event) {
                let target = $( event.currentTarget );

                setTimeout(function () {
                    let activeItem = target.find('.owl-item.active.center');

                    target.find('.owl-item.prev-item').removeClass('prev-item');
                    target.find('.owl-item.next-item').removeClass('next-item');
                    activeItem.prev().addClass('prev-item').removeClass('pre-prev-item');
                    activeItem.next().addClass('next-item').removeClass('pre-next-item');
                }, 300)
            });
        });
    }
})(jQuery);