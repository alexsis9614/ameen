<?php
    namespace LMS\inc\classes;

    use WP_Roles;
    use WP_User;
    use WP_User_Query;
    use WP_Users_List_Table;

    class STM_History_Table extends WP_Users_List_Table
    {

        public $role;

        /**
         * Constructor.
         *
         * @since 0.1
         * @access public
         *
         * @see WP_List_Table::__construct() for more information on default arguments.
         *
         * @param array $args An associative array of arguments.
         */
        public function __construct( $args )
        {
            $this->role = 'subscriber';

            parent::__construct( $args );
        }

        /**
         * Prepare the volunteers list for display.
         *
         * @since 0.1
         * @access public
         */
        public function prepare_items() {
            global $usersearch, $wpdb;

            //Prepare the columns
            $columns  = $this->get_columns();
            $hidden   = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);

            $usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

            $per_page = 'users_per_page';
            $users_per_page = $this->get_items_per_page( $per_page );

            $paged = $this->get_pagenum();

            $args = array(
                'number' 	=> $users_per_page,
                'offset' 	=> ( $paged-1 ) * $users_per_page,
                'search' 	=> $usersearch,
                'role'      => $this->role,
                'fields' 	=> 'all_with_meta'
            );

            if ( ! empty( $volunteer_ids ) ) {
                $args['include'] = $volunteer_ids;
            }

            if ( '' !== $args['search'] )
                $args['search'] = '*' . $args['search'] . '*';

            if ( isset( $_REQUEST['orderby'] ) )
                $args['orderby'] = $_REQUEST['orderby'];

            if ( isset( $_REQUEST['order'] ) )
                $args['order'] = $_REQUEST['order'];

            // Order by phone number if necessary.
            if ( isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] === 'phone' ) {
                $args['meta_key'] = $wpdb->prefix . 'phone';
                $args['orderby']  = 'meta_value_num';
            }

            // Query the user IDs for this page
            $wp_user_search = new WP_User_Query( $args );

            $this->items = $wp_user_search->get_results();

            $this->set_pagination_args( array(
                'total_items' => $wp_user_search->get_total(),
                'per_page'    => $users_per_page,
            ) );

            error_log( print_r( $args, true ) );
        }

        /**
         * Generate HTML for a single row on the volunteers list admin panel.
         *
         * @since 0.1
         * @access public
         *
         * @global WP_Roles $wp_roles User roles object.
         *
         * @param object $user_object The current user object.
         * @param string $style       Deprecated. Not used.
         * @param string $role        Optional. Key for the $wp_roles array. Default empty.
         * @param int    $numposts    Optional. Post counts to display for this user. Defaults
         *                            to zero, as in, a new user has made zero posts.
         * @return string Output for a single row.
         */
        public function single_row( $user_object, $style = '', $role = '', $numposts = 0 ) {
            global $wp_roles;

            if ( ! ( $user_object instanceof WP_User ) ) {
                $user_object = get_userdata( (int) $user_object );
            }
            $user_object->filter = 'display';
            $email      = $user_object->user_email;
            $phone 		= $user_object->__get('billing_phone');
            $admin_url 	= get_edit_user_link( $user_object->ID );

            if ( $this->is_site_users )
                $url = "site-users.php?id={$this->site_id}&amp;";
            else
                $url = 'users.php?';

            $checkbox = $edit = '';
            // Check if the volunteer for this row is editable
            if ( current_user_can( 'list_users' ) ) {
                // Set up the user editing link
                $edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) );

                // Set up the hover actions for this user
                $actions = array();

                if ( current_user_can( 'edit_user',  $user_object->ID ) ) {
                    $actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit', 'stm-volunteer-management' ) . '</a>';
                }
                else {
                    $edit = "<strong>$user_object->user_login</strong><br />";
                }

                if ( !is_multisite() && get_current_user_id() != $user_object->ID && current_user_can( 'delete_user', $user_object->ID ) ) {
                    $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$user_object->ID", 'bulk-users' ) . "'>" . __( 'Delete', 'stm-volunteer-management' ) . "</a>";
                }
                if ( is_multisite() && get_current_user_id() != $user_object->ID && current_user_can( 'remove_user', $user_object->ID ) ) {
                    $actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( $url."action=remove&amp;user=$user_object->ID", 'bulk-users' ) . "'>" . __( 'Remove', 'stm-volunteer-management' ) . "</a>";
                }

                /**
                 * Filter the action links displayed under each volunteer in the Users list table.
                 *
                 * @since 2.8.0
                 *
                 * @param array   $actions     An array of action links to be displayed.
                 *                             Default 'Edit', 'Delete' for single site, and
                 *                             'Edit', 'Remove' for Multisite.
                 * @param WP_User $user_object WP_User object for the currently-listed user.
                 */
                $actions = apply_filters( 'user_row_actions', $actions, $user_object );
                $edit    .= $this->row_actions( $actions );

                // Set up the checkbox ( because the user is editable, otherwise it's empty )
                $checkbox = '<label class="screen-reader-text" for="user_' . $user_object->ID . '">' . sprintf( __( 'Select %s', 'stm-volunteer-management' ), $user_object->user_login ) . '</label>'
                    . "<input type='checkbox' name='users[]' id='user_{$user_object->ID}' class='$role' value='{$user_object->ID}' />";

            } else {
                $edit = '<strong>' . $user_object->user_login . '</strong>';
            }
            $avatar = get_avatar( $user_object->ID, 32 );

            $r = "<tr id='user-$user_object->ID'>";

            list( $columns, $hidden ) = $this->get_column_info();

            foreach ( $columns as $column_name => $column_display_name ) {
                $class = "class=\"$column_name column-$column_name\"";

                $style = '';
                if ( in_array( $column_name, $hidden ) ) {
                    $style = ' style="display:none;"';
                }

                $attributes = "$class$style";

                switch ( $column_name ) {
                    case 'cb':
                        $r .= "<td $attributes>
                                $checkbox
                            </td>
                        ";
                        break;
                    case 'name':
                        $r .= "<td $attributes>
                                    $avatar 
                                    <a href='" . $admin_url . "'>
                                        <strong>
                                            $user_object->display_name
                                        <strong>
                                    </a>
                                    $edit
                               </td>";
                        break;
                    case 'email':
                        $r .= "<td $attributes>$email</td>";
                        break;
                    case 'phone':
                        $r .= "<td $attributes>$phone</td>";
                        break;
                    default:
                        $r .= "<td $attributes>";

                        /**
                         * Filter the display output of custom columns in the volunteers list table.
                         *
                         * @since 0.1
                         *
                         * @param string $output      Custom column output. Default empty.
                         * @param string $column_name Column name.
                         * @param int    $user_id     ID of the currently-listed user.
                         */
                        $r .= apply_filters( 'manage_users_custom_column', '', $column_name, $user_object->ID );
                        $r .= "</td>";
                }
            }
            $r .= '</tr>';

            return $r;
        }

        /**
         * Get a list of columns for the list table.
         *
         * @since  0.1
         * @access public
         *
         * @return array Array in which the key is the ID of the column, and the value is the description.
         */
        public function get_columns(): array
        {
            return array(
                'cb'      => '',
                'name'    => __( 'Name', 'masterstudy-child' ),
                'email'   => __( 'E-mail', 'masterstudy-child' ),
                'phone'   => __( 'Phone Number', 'masterstudy-child' ),
                'device'  => __( 'Count Device', 'masterstudy-child' ),
            );
        }

        /**
         * Get a list of sortable columns for the list table.
         *
         * @since 0.1
         * @access protected
         *
         * @return array Array of sortable columns.
         */
        protected function get_sortable_columns(): array
        {
            return array(
                'name'    => array( 'name', false ),
                'email'   => array( 'email', false ),
                'phone'   => array( 'phone', false ),
                'device'  => array( 'device', false ),
            );
        }

        /**
         * Retrieve an associative array of bulk actions available on this table. We have none so it's an empty array.
         *
         * @since  0.1
         * @access protected
         *
         * @return array Array of bulk actions.
         */
        protected function get_bulk_actions(): array
        {
            $actions = array();

            if ( is_multisite() ) {
                if ( current_user_can( 'remove_users' ) ) {
                    $actions['remove'] = __( 'Remove' );
                }
            } else {
                if ( current_user_can( 'delete_users' ) ) {
                    $actions['delete'] = __( 'Delete' );
                }
            }

            return $actions;
        }

        /**
         * Output nothing for extra bulk changes since we don't allow that at this point.
         *
         * @since 0.1
         * @access protected
         *
         * @param string $which Whether this is being invoked above ("top") or below the table ("bottom").
         */
        protected function extra_tablenav( $which ) {
            echo '';
        }

        /**
         * Message to be displayed when no volunteers have signed up yet or when a search returns no results.
         *
         * Checks that volunteer users do exist and if not, it shows a message that people will show up when they sign up
         * to volunteer. If they do exist then a message shows that the search returned no results.
         *
         * @since 0.1
         * @access public
         */
        public function no_items()
        {
            global $role;

            var_dump($role);

            $user_counts = count_users();

            if( !isset( $user_counts['avail_roles'][ $this->role ] ) || $user_counts['avail_roles'][ $this->role ] == 0 ){
                echo sprintf(
                    __('No %s yet. Once they sign up they\'ll appear here.', 'masterstudy-child'),
                    $this->_args[ 'plural' ]
                );
            }
            else {
                echo sprintf(
                    __('Oops. Your search didn\'t return any %s. Please try again.', 'masterstudy-child'),
                    $this->_args[ 'plural' ]
                );
            }
        }

    }