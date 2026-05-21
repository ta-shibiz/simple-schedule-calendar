<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ==================================================
 * 共通：メタ取得（安全）
 * ==================================================
 */
function ssc_get_meta( $post_id, $key ) {
    $value = get_post_meta( $post_id, $key, true );
    return ( $value !== '' ) ? esc_html( $value ) : '—';
}

/**
 * ==================================================
 * カラム追加
 * ==================================================
 */
add_filter( 'manage_schedule_posts_columns', function ( $columns ) {

    $new = [];

    foreach ( $columns as $key => $label ) {

        $new[$key] = $label;

        if ( $key === 'title' ) {
            $new['ssc_category_image'] = '区分';
            $new['ssc_date']           = '日付';
            $new['ssc_time']           = '時間帯';
            $new['ssc_field']          = '使用フィールド';
        }
    }

    return $new;
});

/**
 * ==================================================
 * カラム表示
 * ==================================================
 */
add_action( 'manage_schedule_posts_custom_column', function ( $column, $post_id ) {

    switch ( $column ) {

        case 'ssc_category_image':

            $terms = get_the_terms( $post_id, 'ssc_category' );

            if ( empty($terms) || is_wp_error($terms) ) {
                echo '—';
                return;
            }

            $term = $terms[0];
            $image_url = get_term_meta( $term->term_id, 'ssc_image_url', true );

            if ( $image_url ) {
                echo '<img src="' . esc_url($image_url) . '" style="max-width:150px;height:auto;max-height:80px;border-radius:4px;" />';
            } else {
                echo esc_html( $term->name );
            }

            return;

        case 'ssc_date':
            echo ssc_get_meta( $post_id, 'ssc_date' );
            return;

        case 'ssc_time':
            echo ssc_get_meta( $post_id, 'ssc_time_slot' );
            return;

        case 'ssc_field':
            echo ssc_get_meta( $post_id, 'ssc_field' );
            return;
    }

}, 10, 2 );

/**
 * ==================================================
 * ソート可能カラム
 * ==================================================
 */
add_filter( 'manage_edit-schedule_sortable_columns', function ( $columns ) {
    $columns['ssc_date'] = 'ssc_date';
    return $columns;
});

/**
 * ==================================================
 * 保存処理（※今回は維持）
 * ==================================================
 */
add_action('save_post', function($post_id){

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;
    if ( get_post_type($post_id) !== 'schedule' ) return;

    if ( isset($_POST['ssc_date']) ) {
        update_post_meta($post_id, 'ssc_date', sanitize_text_field($_POST['ssc_date']));
    }

    if ( isset($_POST['ssc_time_slot']) ) {
        update_post_meta($post_id, 'ssc_time_slot', sanitize_text_field($_POST['ssc_time_slot']));
    }

    if ( isset($_POST['ssc_field']) ) {
        update_post_meta($post_id, 'ssc_field', sanitize_text_field($_POST['ssc_field']));
    }

});

/**
 * ==================================================
 * ソート処理（重要）
 * ==================================================
 */
add_action('pre_get_posts', function($query){

    if ( !is_admin() || !$query->is_main_query() ) return;
    if ( $query->get('post_type') !== 'schedule' ) return;

    if ( $query->get('orderby') === 'ssc_date' ) {

        $query->set('meta_key', 'ssc_date');
        $query->set('orderby', 'meta_value');

        // 数値日付ならこっち
        // $query->set('orderby', 'meta_value_num');
    }

});