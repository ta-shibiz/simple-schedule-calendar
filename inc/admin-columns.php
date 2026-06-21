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
            $value = get_post_meta( $post_id, 'ssc_date', true );
            echo '<span class="ssc-date" data-value="' . esc_attr( $value ) . '">' . ssc_get_meta( $post_id, 'ssc_date' ) . '</span>';
            return;

        case 'ssc_time':
            $value = get_post_meta( $post_id, 'ssc_time_slot', true );
            echo '<span class="ssc-time" data-value="' . esc_attr( $value ) . '">' . ssc_get_meta( $post_id, 'ssc_time_slot' ) . '</span>';
            return;

        case 'ssc_field':
            $value = get_post_meta( $post_id, 'ssc_field', true );
            echo '<span class="ssc-field" data-value="' . esc_attr( $value ) . '">' . ssc_get_meta( $post_id, 'ssc_field' ) . '</span>';
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
