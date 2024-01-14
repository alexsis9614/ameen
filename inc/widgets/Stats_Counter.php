<?php
    namespace LMS\inc\widgets;

    use Elementor\Group_Control_Typography;
    use Elementor_STM_Stats_Counter;
    use Elementor\Controls_Manager;

    class Stats_Counter extends Elementor_STM_Stats_Counter
    {
        public function get_script_depends(): array
        {
            return array( 'stm-stats_counter', 'countUp.min.js' );
        }

        public function get_name(): string
        {
            return 'ameen_' . parent::get_name();
        }

        public function get_title(): string
        {
            return esc_html__( 'Ameen Stats Counter', 'masterstudy-elementor-widgets' );
        }

        public function get_categories(): array
        {
            return array( 'ameen_lms' );
        }

        protected function register_controls()
        {
            $this->start_controls_section(
                'section_content',
                array(
                    'label' => Controls_Manager::TAB_CONTENT,
                )
            );

            $this->add_control(
                'counter_value',
                array(
                    'label'   => __( 'Counter Value', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::NUMBER,
                    'default' => '1000',
                )
            );

            $this->add_control(
                'prefix',
                array(
                    'label'   => __( 'Prefix', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::TEXT,
                    'default' => '',
                )
            );

            $this->add_control(
                'suffix',
                array(
                    'label'   => __( 'Suffix', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::TEXT,
                    'default' => '',
                )
            );

            $this->add_control(
                'duration',
                array(
                    'label'   => __( 'Duration', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::TEXT,
                    'default' => '2.5',
                )
            );

            $this->add_control(
                'title',
                array(
                    'label' => __( 'Text', 'masterstudy-elementor-widgets' ),
                    'type'  => Controls_Manager::WYSIWYG,
                )
            );

            $this->add_control(
                'icon',
                array(
                    'label'   => __( 'Icon', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::ICONS,
                    'default' => array(
                        'value' => '',
                    ),
                )
            );

            $this->end_controls_section();

            $this->add_dimensions( '.masterstudy_elementor_stats_counter_' );

            $this->get_icon_style();

            $this->get_counter_style();

            $this->get_counter_text_style();
        }

        protected function get_icon_style()
        {
            $selector    = '{{WRAPPER}} .stats_counter .icon > i';
            $align_style = $this->get_align_style();

            $align_style['selectors'] = array(
                '{{WRAPPER}} .stats_counter .icon' => 'text-align: {{VALUE}}',
            );

            $this->start_controls_section(
                'section_icon_style',
                array(
                    'label' => esc_html__( 'Icon', 'masterstudy-elementor-widgets' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_control(
                'icon_size',
                [
                    'label'       => esc_html_x( 'Image Dimension', 'Image Size Control', 'elementor' ),
                    'type'        => Controls_Manager::IMAGE_DIMENSIONS,
                    'description' => esc_html__( 'You can crop the original image size to any custom size. You can also set a single value for height or width in order to keep the original size ratio.', 'elementor' ),
                    'default'     => [
                        'width'  => '',
                        'height' => '90',
                    ],
                    'selectors'   => array(
                        '{{WRAPPER}} .stats_counter .icon' => 'width: {{WIDTH}}; height: {{HEIGHT}};',
                    )
                ]
            );

            $this->add_control(
                'font_icon_size',
                array(
                    'label'       => __( 'Font Icon Size', 'masterstudy-elementor-widgets' ),
                    'type'        => Controls_Manager::NUMBER,
                    'default'     => '65',
                    'description' => __( 'Enter icon size in px', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control( 'icon_text_alignment', $align_style );

            $this->add_control(
                'icon_text_color',
                array(
                    'label'       => __( 'Color', 'elementor' ),
                    'type'        => Controls_Manager::COLOR,
                    'default'     => 'white',
                    'selectors'   => [
                        $selector => 'color: {{VALUE}};',
                    ],
                )
            );

            $this->end_controls_section();
        }

        protected function get_counter_style()
        {
            $selector    = '{{WRAPPER}} .stats_counter .h1';
            $align_style = $this->get_align_style();

            $align_style['selectors'] = array(
                $selector => 'text-align: {{VALUE}}',
            );

            $this->start_controls_section(
                'section_counter_style',
                array(
                    'label' => esc_html__( 'Counter', 'masterstudy-elementor-widgets' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'     => 'counter_typography',
                    'selector' => $selector,
                )
            );

            $this->add_control(
                'counter_color',
                array(
                    'label'       => __( 'Color', 'elementor' ),
                    'type'        => Controls_Manager::COLOR,
                    'default'     => 'white',
                    'selectors'   => [
                        $selector => 'color: {{VALUE}};',
                    ],
                )
            );

            $this->add_responsive_control( 'align_counter_style', $align_style );

            $this->add_responsive_control(
                'counter_padding',
                [
                    'label' => esc_html__( 'Padding', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'selectors' => [
                        $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'before',
                ]
            );

            $this->add_responsive_control(
                'counter_margin',
                [
                    'label' => esc_html__( 'Margin', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'selectors' => [
                        $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );

            $this->end_controls_section();
        }

        protected function get_counter_text_style()
        {
            $align_style = $this->get_align_style();
            $selector    = '{{WRAPPER}} .stats_counter_title.h5';

            $align_style['selectors'] = array(
                $selector => 'text-align: {{VALUE}}',
            );

            $this->start_controls_section(
                'section_counter_text_style',
                array(
                    'label' => esc_html__( 'Counter text', 'masterstudy-elementor-widgets' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                array(
                    'name'     => 'counter_text_typography',
                    'selector' => $selector,
                )
            );

            $this->add_control(
                'counter_text_color',
                array(
                    'label'       => __( 'Color', 'elementor' ),
                    'type'        => Controls_Manager::COLOR,
                    'default'     => 'white',
                    'selectors'   => [
                        $selector => 'color: {{VALUE}};',
                    ],
                )
            );

            $this->add_responsive_control( 'align_counter_text_style', $align_style );

            $this->add_responsive_control(
                'counter_text_padding',
                [
                    'label' => esc_html__( 'Padding', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'selectors' => [
                        $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'before',
                ]
            );

            $this->add_responsive_control(
                'counter_text_margin',
                [
                    'label' => esc_html__( 'Margin', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'selectors' => [
                        $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                    ],
                ]
            );

            $this->end_controls_section();
        }

        protected function get_align_style()
        {
            return array(
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
            );
        }

        protected function render() {
            if ( function_exists( 'masterstudy_show_template' ) ) {

                $settings = $this->get_settings_for_display();

                $settings['css_class'] = ' masterstudy_elementor_stats_counter_';

                $settings['id'] = 'counter_' . stm_create_unique_id( $settings );

                $settings['icon'] = ( isset( $settings['icon']['value'] ) ) ? $settings['icon']['value'] : '';

                $settings['prefix'] = ( isset( $settings['prefix'] ) ) ? $settings['prefix'] : '';

                $settings['suffix'] = ( isset( $settings['suffix'] ) ) ? $settings['suffix'] : '';

                masterstudy_show_template( 'stats_counter', $settings );

            }
        }

    }
