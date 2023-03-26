<?php
    use Bookit\Classes\Database\Customers;
    use Bookit\Classes\Database\Staff;
    use Bookit\Classes\Database\Staff_Services;
    use Bookit\Classes\Database\Staff_Working_Hours;

    class STM_LMS_Bookit_User
    {
        public static function create($user_id)
        {
            $user    = new WP_User( $user_id );
            $sync_id = $user->__get( STM_LMS_Bookit_Sync::$field_name );
            $data    = array(
                'wp_user_id' => $user_id,
                'full_name'  => $user->display_name,
                'email'      => $user->user_email,
                'phone'      => ''
            );

            $is_instructor = STM_LMS_Instructor::is_instructor( $user_id );

            if ( $is_instructor ) {
                unset($data['wp_user_id']);

                if ( ! empty( $sync_id ) ) {
                    $bookit_user = Staff::get('id', $sync_id);

                    if ( empty( $bookit_user ) ) {
                        $customer = Customers::get('id', $sync_id);

                        if ( ! empty( $customer ) ) {
                            Customers::delete_where('id', $sync_id);
                            delete_user_meta($user_id, STM_LMS_Bookit_Sync::$field_name);
                            $sync_id = 0;
                        }
                    }
                }
            }
            else {
                if ( ! empty( $sync_id ) ) {
                    $bookit_user = Customers::get('id', $sync_id);

                    if ( empty( $bookit_user ) ) {
                        $staff = Staff::get('id', $sync_id);

                        if ( ! empty( $staff ) ) {
                            Staff::delete_where('id', $sync_id);
                            Staff_Services::delete_where('staff_id', $sync_id);
                            delete_user_meta($user_id, STM_LMS_Bookit_Sync::$field_name);
                            $sync_id = 0;
                        }
                    }
                }
            }

            if ( empty( $sync_id ) ) {
                if ( $is_instructor ) {
                    Staff::insert( $data );

                    $sync_id = Staff::insert_id();
                }
                else {
                    Customers::insert( $data );

                    $sync_id = Customers::insert_id();
                }

                update_user_meta($user_id, STM_LMS_Bookit_Sync::$field_name, $sync_id);
            }
            else {
                if ( $is_instructor ) {
                    Staff::update($data, ['id' => $sync_id]);
                }
                else{
                    Customers::update($data, ['id' => $sync_id]);
                }
            }

            if ( $is_instructor ) {
                $working_hours = Staff_Working_Hours::get('staff_id', $sync_id);
                if ( empty( $working_hours ) ) {
                    $working_hours = self::working_hours($sync_id);

                    foreach ( $working_hours as $working_hour ) {
                        $insert = [
                            'staff_id'      => $sync_id,
                            'weekday'       => $working_hour['weekday'],
                            'start_time'    => $working_hour['start_time'],
                            'end_time'      => $working_hour['end_time'],
                            'break_from'    => $working_hour['break_from'],
                            'break_to'      => $working_hour['break_to']
                        ];
                        Staff_Working_Hours::insert( $insert );
                    }
                }
            }

            return $sync_id;
        }

        public static function delete($user_id)
        {
            $user = new WP_User( $user_id );
            $sync_id = $user->__get( STM_LMS_Bookit_Sync::$field_name );

            if ( empty( $sync_id ) ) return;

            if ( STM_LMS_Instructor::is_instructor( $user_id ) ) {
                Staff::deleteStaff( $sync_id );
            }
            else {
                Customers::deleteCustomer( $sync_id );
            }
        }

        public static function working_hours( $staff_id ): array
        {
            return array(
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 1,
                    "start_time" => "09:00:00",
                    "end_time" => "18:00:00",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 2,
                    "start_time" => "09:00:00",
                    "end_time"=>"18:00:00",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 3,
                    "start_time" => "09:00:00",
                    "end_time"=>"18:00:00",
                    "break_from"=>"NULL",
                    "break_to"=>"NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 4,
                    "start_time" => "09:00:00",
                    "end_time" => "18:00:00",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 5,
                    "start_time" => "09:00:00",
                    "end_time" => "18:00:00",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 6,
                    "start_time" => "NULL",
                    "end_time" => "NULL",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                ),
                array(
                    "staff_id" => $staff_id,
                    "weekday" => 7,
                    "start_time" => "NULL",
                    "end_time" => "NULL",
                    "break_from" => "NULL",
                    "break_to" => "NULL"
                )
            );
        }
    }