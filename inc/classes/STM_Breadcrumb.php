<?php
    namespace LMS\inc\classes;

    class STM_Breadcrumb
    {
        public function __construct()
        {
            add_filter( 'bcn_display_separator', array( $this, 'display_separator' ), 20, 3 );

            add_filter( 'bcn_breadcrumb_title', array( $this, 'breadcrumb_title' ), 20, 2 );
        }

        public function display_separator( $separator, $position, $last_position )
        {
            if ( $position !== $last_position ) {
                return '
                    <svg width="20" height="20" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.08711 5.91016L6.91211 7.08516L10.7288 10.9102L6.91211 14.7352L8.08711 15.9102L13.0871 10.9102L8.08711 5.91016Z" fill="#97999D"/>
                    </svg>  
                ';
            }

            return $separator;
        }

        public function breadcrumb_title( $title, $type )
        {
            if ( is_array( $type ) && ! empty( $type ) && $type[0] === 'home' ) {
                $title = esc_html__('Home page', 'masterstudy-child');
            }

            return $title;
        }
    }