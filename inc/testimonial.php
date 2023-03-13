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
                            'html5'   => 'HTML5',
                        )
                    ],
                    'testimonial_video_url' => [
                        'label' => __('Video url', 'masterstudy-child'),
                        'type'  => 'text',
                    ],
                ],
            ]);
    });