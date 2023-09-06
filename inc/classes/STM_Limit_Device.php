<?php
    namespace LMS\inc\classes;

    class STM_Limit_Device
    {
        public $table_name = 'stm_lms_limit_device';
        public $user;

        public function __construct( $user_id )
        {
            $this->user = new \WP_User( $user_id );
        }

        public function using_hooks()
        {
            add_action( 'after_switch_theme', array( $this, 'db_table_create' ) );

            add_action( 'admin_menu', array( $this, 'add_submenu' ) );

            add_action( 'admin_init', array( $this, 'init' ) );
        }

        public function get_table_name(): string
        {
            global $wpdb;

            return $wpdb->prefix . $this->table_name;
        }

        public function db_table_create()
        {
            global $wpdb;

            $table_name = $this->get_table_name();

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                user_hash TEXT NOT NULL DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			    last_login DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta( $sql );
        }

        public function init()
        {
            if (
                isset( $_REQUEST['page'] ) && 'lms-history-devices' === $_REQUEST['page'] &&
                isset( $_REQUEST['action'] ) && 'clear-limit' === $_REQUEST['action'] &&
                isset( $_REQUEST['user'] ) && ! empty( $_REQUEST['user'] )
            ) {
                check_admin_referer( 'bulk-users' );

                $this->user = new \WP_User( $_REQUEST['user'] );

                $this->delete();

                wp_redirect( admin_url('users.php?page=lms-history-devices') );
            }
        }

        public function add_submenu()
        {
            add_submenu_page(
                'users.php',
                __('History Devices Login', 'masterstudy-child'),
                __('History Devices', 'masterstudy-child'),
                'list_users',
                'lms-history-devices',
                array( $this, 'page_list_history_device' )
            );
        }

        public function page_list_history_device()
        {
            \STM_LMS_Templates::show_lms_template('admin/list-history');
        }

        public function get_limits( $check_user_hash = false ): int
        {
            global $wpdb;

            $query = "
                SELECT COUNT(id) FROM {$this->get_table_name()}
                WHERE user_id = %d
            ";

            $query_args = array(
                $this->user->ID
            );

            if ( $check_user_hash ) {
                $query .= ' AND user_hash = %s';
                $query_args[] = $this->get_current_hash();
            }

            $query = $wpdb->prepare( $query, $query_args );

            return absint( $wpdb->get_var( $query ) );
        }

        public function check(): bool
        {
            $exhausted = false;

            if ( in_array('subscriber', $this->user->roles) && $this->get_limits() >= 3 ) {
                $exhausted = true;
            }

            return $exhausted;
        }

        public function check_unique(): bool
        {
            $_count = $this->get_limits( true );

            if ( ! $_count ) {
                return true;
            }

            return false;
        }

        public function insert(): bool
        {
            global $wpdb;

            $wpdb->insert( $this->get_table_name(),
                array(
                    'user_id'    => $this->user->ID,
                    'user_hash'  => $this->get_current_hash(),
                    'created_at' => $this->get_current_date(),
                    'last_login' => $this->get_current_date()
                )
            );

            return ( ! empty( $wpdb->insert_id ) );
        }

        public function add(): bool
        {
            global $wpdb;

            $unique = $this->check_unique();

            if ( ! $this->check() ) {
                if ( $unique ) {
                    return $this->insert();
                }
                else {
                    $wpdb->update( $this->get_table_name(),
                        array(
                            'last_login' => $this->get_current_date()
                        ),
                        array(
                            'user_id'   => $this->user->ID,
                            'user_hash' => $this->get_current_hash()
                        )
                    );

                    return true;
                }
            }
            else {
                $this->insert();
            }

            return false;
        }

        public function delete(): bool
        {
            global $wpdb;

            return $wpdb->delete( $this->get_table_name(),
                array(
                    'user_id'   => $this->user->ID,
                )
            );
        }

        public function request(): bool
        {
//            $admin_email = get_option('admin_email');
            $admin_email = '6763410@gmail.com';
            $subject     = __('Request to update device limit', 'masterstudy-child');
            $message     = "
                %s <br />
                Name: %s <br />
                Phone: %s <br />
            ";
            $message     = sprintf( $message,
                __('The user is requesting an update of the login limit from another device', 'masterstudy-child'),
                $this->user->display_name,
                $this->user->__get('billing_phone')
            );
            $headers     = array(
                'From: ' . get_bloginfo('name') . ' <info@ameen.uz>',
                'content-type: text/html'
            );

            return wp_mail( $admin_email, $subject, $message, $headers );
        }

        public function get_current_date(): string
        {
            return wp_date( 'Y-m-d H:i:s' );
        }

        public function get_current_hash(): string
        {
            return md5( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] );
        }
    }