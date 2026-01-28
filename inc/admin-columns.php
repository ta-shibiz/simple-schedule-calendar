<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 管理画面：スケジュール一覧カラム追加
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
} );

/**
 * カラム内容出力
 */
add_action( 'manage_schedule_posts_custom_column', function ( $column, $post_id ) {

    switch ( $column ) {

        /**
         * スケジュール区分（画像）
         */
        case 'ssc_category_image':

            $terms = get_the_terms( $post_id, 'ssc_category' );

            if ( empty( $terms ) || is_wp_error( $terms ) ) {
                echo '—';
                return;
            }

            $term = $terms[0];
            $image_url = get_term_meta( $term->term_id, 'ssc_image_url', true );

            if ( $image_url ) {
                echo '<img src="' . esc_url( $image_url ) . '" style="width:100%;max-width:150px;height:auto;max-height:80px;border-radius:4px;" />';
            } else {
                echo esc_html( $term->name );
            }
            break;

case 'ssc_date':
    $date = get_post_meta( $post_id, 'ssc_date', true );
    echo ( $date !== '' ) ? esc_html( $date ) : '—';
    break;

case 'ssc_time':
    $time = get_post_meta( $post_id, 'ssc_time_slot', true );
    echo ( $time !== '' ) ? esc_html( $time ) : '—';
    break;

case 'ssc_field':
    $field = get_post_meta( $post_id, 'ssc_field', true );
    echo ( $field !== '' ) ? esc_html( $field ) : '—';
    break;

    }

}, 10, 2 );


/**
 * ソート可能（任意）
 */
add_filter( 'manage_edit-schedule_sortable_columns', function ( $columns ) {
    $columns['ssc_date'] = 'ssc_date';
    return $columns;
});