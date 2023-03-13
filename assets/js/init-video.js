"use strict";

(function ($) {
    $(document).ready(function () {
        let containerVideo = 'stm_testimonials_video_single',
            containerVideoElement = $('.' + containerVideo);

        if ( containerVideoElement.length > 0 ) {
            containerVideoElement.each(function () {
                let options = {
                        currentTime: 3
                    },
                    $this   = $(this),
                    type    = $this.data('type'),
                    id      = 'video-' + type;

                // videojs(this, options);

                let YPlayer,
                    VPlayer,
                    YVideo = $('[data-type="youtube"]', $this),
                    VVideo = $('[data-type="vimeo"]', $this),
                    VHtml5 = $('video#stm_lms_video_html5_api');

                if ( ! $( '#' + id ).length ) {
                    let tag = document.createElement('script');

                    tag.id = id;

                    if ( $this.data('type') === 'youtube' ) {
                        tag.src = "https://www.youtube.com/iframe_api";
                    }
                    else if ( $this.data('type') === 'vimeo' ) {
                        tag.src = "https://player.vimeo.com/api/player.js";
                    }
                    let firstScriptTag = document.getElementsByTagName('script')[0];
                    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                }
            });
        }

        function onYouTubeIframeAPIReady( element ) {
            if ( $( element ).length && $( element ).data('id') ) {
                new YT.Player(element, {
                    height: 500,
                    videoId: $( element ).data('id'),
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

        function onVimeoIframeAPIReady( element ) {
            if ( $( element ).length && $( element ).data('id') ) {
                 new Vimeo.Player($( element ).attr('id'), {
                    height: 500,
                    id: $( element ).data('id'),
                    autoplay: true
                });
            }
        }

        containerVideoElement.on('click', function () {
            let $this = $(this);
            $this.addClass('visible');

            // if ($this.hasClass( containerVideo )) {

                if ( 'youtube' === $this.data('type') ) {
                    onYouTubeIframeAPIReady(this);
                }
                else if ( 'vimeo' === $this.data('type') ) {
                    onVimeoIframeAPIReady(this);
                }
                else {
                    let $iframe = $this.find('iframe');
                    $iframe.attr('src', $iframe.attr('data-src'));
                }
            // }
        });
    });

})(jQuery);