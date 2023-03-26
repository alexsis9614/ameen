<?php
    use Bookit\Classes\Database\Categories;

    class STM_LMS_Bookit_Categories
    {
        public static function create($term_id)
        {
            $category = get_term($term_id);
            $sync_id  = get_term_meta($term_id, STM_LMS_Bookit_Sync::$field_name, true);

            if ( empty( $sync_id ) ) {
                Categories::insert( array('name' => $category->name) );
                $sync_id = Categories::insert_id();
                update_term_meta($term_id, STM_LMS_Bookit_Sync::$field_name, $sync_id);
            }
            else {
                Categories::update( array('name' => $category->name), [ 'id' => $sync_id ] );
            }

            return $sync_id;
        }

        public static function delete($term, $taxonomy)
        {
            if ( !$term || $taxonomy !== STM_LMS_Bookit_Sync::$taxonomy ) return;

            $sync_id = get_term_meta($term->term_id, STM_LMS_Bookit_Sync::$field_name, true);

            if ( $sync_id ) {
                Categories::deleteCategory( $sync_id );
            }
        }
    }