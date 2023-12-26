<?php
    namespace LMS\inc\Elementor;

    use Elementor_STM_Stats_Counter;
    use Elementor\Controls_Manager;

    class Stats_Counter extends Elementor_STM_Stats_Counter
    {
        public function get_style_depends(): array
        {
            return array( 'stm-stats_counter-style_1' );
        }

        public function get_script_depends(): array
        {
            return array( 'stm-stats_counter', 'countUp.min.js' );
        }

        public function get_name(): string
        {
            return 'ameen_stm_stats_counter';
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
                    'label' => __( 'Content', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'title',
                array(
                    'label' => __( 'Title', 'masterstudy-elementor-widgets' ),
                    'type'  => Controls_Manager::TEXT,
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
                'icon',
                array(
                    'label'   => __( 'Icon', 'masterstudy-elementor-widgets' ),
                    'type'    => \Elementor\Controls_Manager::ICONS,
                    'default' => array(
                        'value' => '',
                    ),
                )
            );

            $this->add_control(
                'icon_size',
                array(
                    'label'       => __( 'Icon Size', 'masterstudy-elementor-widgets' ),
                    'type'        => Controls_Manager::NUMBER,
                    'default'     => '65',
                    'description' => __( 'Enter icon size in px', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'icon_width',
                array(
                    'label'       => __( 'Icon Width', 'masterstudy-elementor-widgets' ),
                    'type'        => Controls_Manager::NUMBER,
                    'description' => __( 'Enter icon width in px', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'icon_height',
                array(
                    'label'       => __( 'Icon Height', 'masterstudy-elementor-widgets' ),
                    'type'        => Controls_Manager::NUMBER,
                    'default'     => '90',
                    'description' => __( 'Enter icon height in px', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'icon_text_alignment',
                array(
                    'label'       => __( 'Text alignment', 'masterstudy-elementor-widgets' ),
                    'type'        => Controls_Manager::SELECT,
                    'options'     => array(
                        'left'   => __( 'Left', 'masterstudy-elementor-widgets' ),
                        'right'  => __( 'Right', 'masterstudy-elementor-widgets' ),
                        'center' => __( 'Center', 'masterstudy-elementor-widgets' ),
                    ),
                    'default'     => 'left',
                    'description' => __( 'Text alignment in block', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'icon_text_color',
                array(
                    'label'       => __( 'Text color', 'masterstudy-elementor-widgets' ),
                    'type'        => \Elementor\Controls_Manager::COLOR,
                    'description' => __( 'Text color(white - default)', 'masterstudy-elementor-widgets' ),
                    'default'     => 'white',
                )
            );

            $this->add_control(
                'icon_background_color',
                array(
                    'label' => __( 'Icon background color', 'masterstudy-elementor-widgets' ),
                    'type'  => \Elementor\Controls_Manager::COLOR,
                )
            );

            $this->add_control(
                'text_font_size',
                array(
                    'label' => __( 'Text font size (px)', 'masterstudy-elementor-widgets' ),
                    'type'  => Controls_Manager::NUMBER,
                )
            );

            $this->add_control(
                'counter_text_color',
                array(
                    'label'       => __( 'Counter text color', 'masterstudy-elementor-widgets' ),
                    'type'        => \Elementor\Controls_Manager::COLOR,
                    'description' => __( 'Counter Text color(yellow - default)', 'masterstudy-elementor-widgets' ),
                )
            );

            $this->add_control(
                'counter_text_font_size',
                array(
                    'label' => __( 'Counter text font size (px)', 'masterstudy-elementor-widgets' ),
                    'type'  => Controls_Manager::NUMBER,
                )
            );

            $this->add_control(
                'border',
                array(
                    'label'   => __( 'Include Border', 'masterstudy-elementor-widgets' ),
                    'type'    => Controls_Manager::SELECT,
                    'options' => array(
                        'none'  => __( 'None', 'masterstudy-elementor-widgets' ),
                        'right' => __( 'Right', 'masterstudy-elementor-widgets' ),
                    ),
                )
            );

            $this->end_controls_section();

            $this->add_dimensions( '.masterstudy_elementor_stats_counter_' );
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
