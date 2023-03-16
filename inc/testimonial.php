<?php
    add_action( 'admin_init', function () {
        STM_PostType::addMetaBox('testimonial_video', __('Testimonial Video', 'masterstudy-child'),
            ['testimonial'], '', '', '', [
                'fields' => [
                    'testimonial_video_type' => [
                        'label'   => __('Video type', 'masterstudy-child'),
                        'type'    => 'select',
                        'options' => array(
                            'youtube' => 'Youtube',
                            'vimeo'   => 'Vimeo',
//                            'html5'   => 'HTML5',
                        )
                    ],
                    'testimonial_video_url' => [
                        'label' => __('Video url', 'masterstudy-child'),
                        'type'  => 'text',
                    ],
                ],
            ]);
    });

    function stm_modal_testimonials()
    {
        ?>

        <!-- Modal -->
        <div class="modal fade" id="review-video-modal" tabindex="-1" role="dialog" aria-labelledby="videoModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">


                    <div class="modal-body">

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="embed-responsive embed-responsive-16by9">
                            <div id="review-video"></div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <?php
    }