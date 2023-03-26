<?php
    use Bookit\Classes\Database\Services;
    use Bookit\Classes\Database\Staff_Services;

    class STM_LMS_Bookit_Service
    {
        public static function course_added($validated_data, $course_id)
        {
            self::create($course_id, get_post($course_id));
        }

        public static function create($course_id, $course)
        {
            if (
                wp_is_post_revision( $course_id ) ||
                $course->post_status !== 'publish' ||
                $course->post_type !== STM_LMS_Bookit_Sync::$post_type
            ) {
                return;
            }

            $course = new WP_Post( $course );

            $category_sync_id = self::categories($course);
            $course_sync_id   = $course->__get( STM_LMS_Bookit_Sync::$field_name ) ?: 0;
            $duration         = $course->__get('duration_info');
            $author_id        = $course->post_author;
            $duration         = !empty( $duration ) ? floatval($duration) * 60 * 60 : floatval(1) * 60 * 60;

            $price       = $course->__get('price');
            $sale_price  = apply_filters('stm_lms_get_sale_price', $course->__get('sale_price'), $course->ID);
            if ( empty( $price ) && ! empty( $sale_price ) ) {
                $price   = $sale_price;
            }
            if ( ! empty( $price ) && ! empty( $sale_price ) ) {
                $price   = $sale_price;
            }

            $price = $price ?: 0.01;

            $data        = array(
                'id'          => $course_sync_id,
                'category_id' => $category_sync_id,
                'title'       => $course->post_title,
                'duration'    => $duration,
                'price'       => $price,
                'icon_id'     => ''
            );

            if ( ! empty( $data['id'] ) ) {
                Services::update( $data, [ 'id' => $data['id'] ] );
            } else {
                Services::insert( $data );
                $course_sync_id = Services::insert_id();
                update_post_meta($course_id, STM_LMS_Bookit_Sync::$field_name, $course_sync_id);
            }

            $author_sync_id = STM_LMS_Bookit_User::create( $author_id );
            $staff_services = Staff_Services::get('service_id', $course_sync_id);

            if ( empty( $staff_services ) || $staff_services->staff_id !== $author_sync_id ) {
                if ( isset( $staff_services->id ) ) {
                    Staff_Services::delete_where('id', $staff_services->id);
                }

                $insert = [
                    'staff_id'      => $author_sync_id,
                    'service_id'    => $course_sync_id,
                    'price'         => number_format((float)$price, 2, '.', ''),
                ];
                Staff_Services::insert( $insert );
            }
        }

        public static function categories($course)
        {
            $categories  = get_the_terms($course, 'stm_lms_course_taxonomy');
            $category_sync_id = 0;

            if ( ! empty( $categories ) ) {
                foreach ($categories as $index => $category) {
                    $sync_id = STM_LMS_Bookit_Categories::create($category->term_id);

                    if ( $index === 0 ) {
                        $category_sync_id = $sync_id;
                    }
                }
            }

            return $category_sync_id;
        }

        public static function delete($course_id)
        {
            if( get_post_type( $course_id ) !== STM_LMS_Bookit_Sync::$post_type ) return;

            $sync_id = get_post_meta($course_id, STM_LMS_Bookit_Sync::$field_name, true);

            if ( $sync_id ) {
                delete_post_meta($course_id, STM_LMS_Bookit_Sync::$field_name, $sync_id);
                Services::deleteService( $sync_id );
            }
        }

        public static function get_course_id($service_id): int
        {
            global $wpdb;

            $course_id = 0;
            $post_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta}
                WHERE meta_value = %d AND meta_key = %s
            ", $service_id, STM_LMS_Bookit_Sync::$field_name), ARRAY_A);

            if ( ! empty( $post_meta ) ) {
                $course_id = array_shift($post_meta)['post_id'] ?? 0;
            }

            return $course_id;
        }
    }