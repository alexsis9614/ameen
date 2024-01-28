<?php
    namespace LMS\inc;

    use Elementor\Controls_Manager;
	use Elementor\Element_Base;
    use Elementor\Group_Control_Background;
    use Elementor\Group_Control_Typography;
    use Elementor\Widget_Base;
    use Elementor_STM_Testimonials;
    use Elementor_STM_Child_Testimonials;

    class Elementor
    {

        public $prefix = 'ameen_';

        public function __construct()
        {
            add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ), 20 );

            add_filter( 'elementor/widget/render_content', array( $this, 'render_content' ), 10, 2 );

            add_action( 'elementor/widget/before_render_content', array( $this, 'before_render_content' ), 10, 1 );

            add_action( 'elementor/widget/stm_lms_instructors_carousel/skins_init', array( $this, 'instructors_carousel' ) );
        }

        public function register_widgets( $widgets_manager )
        {
            if ( class_exists('Elementor_STM_Testimonials') ) {
                $team_widget = new Elementor_STM_Testimonials();

                $widgets_manager->unregister( $team_widget->get_name() );

                require_once __DIR__ . '/widgets/class-testimonials.php';

                $widgets_manager->register( new Elementor_STM_Child_Testimonials() );
            }

            if ( class_exists( 'Elementor_STM_Stats_Counter' ) ) {
                require_once __DIR__ . '/widgets/Stats_Counter.php';

                $widgets_manager->register( new widgets\Stats_Counter() );
            }

            if ( class_exists( 'StmLmsElementor\Widgets\StmLmsCoursesCarousel' ) ) {
                require_once __DIR__ . '/widgets/Courses_Carousel.php';

                $widgets_manager->register( new widgets\Courses_Carousel() );
            }

            require_once __DIR__ . '/widgets/Course_Bundle.php';

            $widgets_manager->register( new widgets\Course_Bundle() );
        }

        public function render_content( $widget_content, Widget_Base $widget )
        {
            if ( 'accordion' === $widget->get_name() ) {
                $this->render_accordion( $widget_content, $widget );
            } else if ( 'media-carousel' === $widget->get_name() ) {
                $widget_content .= '<div class="main-swiper-pagination"></div>';
			} else if ( 'stm_lms_instructors_carousel' === $widget->get_name() ) {
                $settings = $widget->get_settings();

                $widget_content = str_replace( '<span class="subtitle">', '<span class="subtitle">' . $settings['subtitle'], $widget_content );
            }

            return $widget_content;
        }

        public function instructors_carousel( Widget_Base $widget )
        {
            $widget->add_style_depends( 'stm-instructors_carousel-style_3' );

            $control = $widget->get_controls( 'style' );

            $control['options']['style_3'] = __( 'Style 3', 'masterstudy-child' );

            $widget->update_control( 'style', $control );

            $widget->remove_control( 'title' );

            $widget->start_controls_section(
                'section_content_title',
                array(
                    'label' => __( 'Title', 'masterstudy-child' ),
                    'tab'   => Controls_Manager::TAB_CONTENT,
                )
            );

            $widget->add_control(
                'title',
                array(
                    'name'        => 'title',
                    'label'       => __( 'Title', 'masterstudy-child' ),
                    'type'        => Controls_Manager::TEXT,
                    'label_block' => true,
                )
            );

            $widget->add_control(
                'subtitle',
                array(
                    'name'        => 'title',
                    'label'       => __( 'Subtitle', 'masterstudy-child' ),
                    'type'        => Controls_Manager::TEXT,
                    'label_block' => true,
                )
            );

            $widget->end_controls_section();

            $widget->start_controls_section(
                'section_title_style',
                array(
                    'label' => esc_html__( 'Title', 'masterstudy-child' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $widget->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'     => 'typography',
                    'selector' => '{{WRAPPER}} .stm_lms_instructors_carousel__top h3',
                )
            );

            $widget->add_responsive_control(
                'align_title',
                array(
                    'label'     => esc_html__( 'Alignment', 'elementor' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => array(
                        'left'    => array(
                            'title' => esc_html__( 'Left', 'elementor' ),
                            'icon'  => 'eicon-text-align-left',
                        ),
                        'center'  => array(
                            'title' => esc_html__( 'Center', 'elementor' ),
                            'icon'  => 'eicon-text-align-center',
                        ),
                        'right'   => array(
                            'title' => esc_html__( 'Right', 'elementor' ),
                            'icon'  => 'eicon-text-align-right',
                        ),
                        'justify' => array(
                            'title' => esc_html__( 'Justified', 'elementor' ),
                            'icon'  => 'eicon-text-align-justify',
                        ),
                    ),
                    'default'   => '',
                    'selectors' => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__top' => 'justify-content: {{VALUE}}',
                    ),
                )
            );

            $widget->end_controls_section();

            $widget->start_controls_section(
                'section_subtitle_style',
                array(
                    'label' => esc_html__( 'Subtitle', 'masterstudy-child' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $widget->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'     => 'typography_subtitle',
                    'selector' => '{{WRAPPER}} .stm_lms_instructors_carousel__top .subtitle',
                )
            );

            $widget->end_controls_section();

            $widget->start_controls_section(
                'section_buttons_styles',
                array(
                    'label' => esc_html__( 'Prev/Next buttons', 'masterstudy-child' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );
            $widget->add_group_control(
                Group_Control_Background::get_type(),
                array(
                    'name'     => 'buttons_background',
                    'types'    => array( 'classic', 'gradient' ),
                    'selector' => '{{WRAPPER}} .stm_lms_instructors_carousel__button',
                )
            );
            $widget->add_control(
                'buttons_color',
                array(
                    'label'      => __( 'Color', 'masterstudy-child' ),
                    'type'       => Controls_Manager::COLOR,
                    'selectors'  => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__button' => 'color: {{VALUE}}',
                    ),
                )
            );
            $widget->add_control(
                'buttons_border_radius',
                array(
                    'label'      => __( 'Border Radius', 'masterstudy-child' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => array( 'px', '%' ),
                    'selectors'  => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                    'default'    => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                )
            );
            $widget->add_control(
                'prev_button_padding',
                array(
                    'label'      => esc_html__( 'Prev button padding', 'masterstudy-child' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => array( 'px', '%' ),
                    'selectors'  => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__button_prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                    'default'    => array(
                        'top'    => '0',
                        'right'  => '2',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                )
            );
            $widget->add_control(
                'next_button_padding',
                array(
                    'label'      => esc_html__( 'Next button padding', 'masterstudy-child' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => array( 'px', '%' ),
                    'selectors'  => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__button_next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                    'default'    => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '2',
                        'unit'   => 'px',
                    ),
                )
            );
            $widget->add_control(
                'buttons_margin',
                array(
                    'label'      => esc_html__( 'Margin', 'masterstudy-child' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => array( 'px', '%' ),
                    'selectors'  => array(
                        '{{WRAPPER}} .stm_lms_instructors_carousel__button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                    'default'    => array(
                        'top'    => '0',
                        'right'  => '0',
                        'bottom' => '0',
                        'left'   => '0',
                        'unit'   => 'px',
                    ),
                )
            );
            $widget->end_controls_section();

            $widget->start_controls_section(
                'section_view_all_style',
                array(
                    'label' => esc_html__( 'View all', 'masterstudy-child' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $widget->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'     => 'typography_view_all',
                    'selector' => '{{WRAPPER}} .stm_lms_instructors_carousel__top',
                )
            );

            $widget->end_controls_section();
        }

        public function before_render_content( Widget_Base $widget )
        {
            if ( 'media-carousel' === $widget->get_name() ) {
				wp_enqueue_script( 'stm-script-testimonials' );
				$widget->add_style_depends( 'stm-style-testimonials' );

				$widget->add_render_attribute(
					'_wrapper',
					[
						'class' => $this->prefix . 'main-page-carousel',
					]
				);
            }
        }

        public function render_accordion( &$widget_content, Widget_Base $widget )
        {
            $widget_content = preg_replace(
                '/<a class="elementor-accordion-title" (.*?)>(.*?)<\/a>/',
                '<span class="elementor-accordion-title" $1>$2</span>',
                $widget_content
            );

            $widget->add_style_depends( 'stm-accordion-style_1' );
        }
    }