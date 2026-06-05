<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * [schedule_calendar category="event,reserve" field="little,hs_full"]
 */
add_shortcode( 'schedule_calendar', function ( $atts ) {

    $atts = shortcode_atts(
        [
            'category' => '',
            'field'    => '',
        ],
        $atts,
        'schedule_calendar'
    );

    /**
     * ▼ 表示年月
     */
    $ym = get_query_var( 'sc_month' );

    if ( ! $ym && isset( $_GET['sc_month'] ) ) {
        $ym = sanitize_text_field( wp_unslash( $_GET['sc_month'] ) );
    }

    if ( $ym && preg_match( '/^\d{4}-\d{2}$/', $ym ) ) {

        [ $year, $month ] = explode( '-', $ym );

        $year  = intval( $year );
        $month = intval( $month );

    } else {

        $current_time = current_time( 'timestamp' );

        $year  = intval( date( 'Y', $current_time ) );
        $month = intval( date( 'n', $current_time ) );
    }

    /**
     * ▼ スケジュール区分 taxonomy（ssc_category）
     *
     * 例：
     * [schedule_calendar category="reserve,event"]
     */
    $categories = [];

    if ( ! empty( $atts['category'] ) ) {

        $categories = array_filter(
            array_map(
                'trim',
                explode( ',', $atts['category'] )
            )
        );

        $categories = array_values( array_unique( $categories ) );
    }

    /**
     * ▼ 使用フィールド（ssc_field）
     *
     * config-fields.php の ssc_get_fields() を参照。
     *
     * 重要：
     * DBには little / hs_full などの「キー」が保存されているため、
     * 日本語ラベルではなく、ショートコードで指定されたキーをそのまま使う。
     *
     * 例：
     * [schedule_calendar field="little"]
     * [schedule_calendar field="hs_yellow_half,hs_red_half,hs_full"]
     */
    $field_map = [];

    if ( function_exists( 'ssc_get_fields' ) ) {
        $field_map = ssc_get_fields();
    }

    $fields = [];

    if ( ! empty( $atts['field'] ) ) {

        $requested_fields = array_filter(
            array_map(
                'trim',
                explode( ',', $atts['field'] )
            )
        );

        foreach ( $requested_fields as $field_key ) {

            /**
             * config-fields.php に存在するキーだけ許可
             */
            if ( isset( $field_map[ $field_key ] ) ) {
                $fields[] = $field_key;
            }
        }

        $fields = array_values( array_unique( $fields ) );
    }

    ob_start();

    ssc_render_calendar(
        $year,
        $month,
        $categories,
        $fields
    );

    return ob_get_clean();
} );