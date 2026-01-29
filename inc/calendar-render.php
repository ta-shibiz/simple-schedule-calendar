<?php

function ssc_render_calendar( $year, $month, $categories = [], $fields = [] ) {

    $current = strtotime( "$year-$month-01" );
    $prev = date( 'Y-m', strtotime( '-1 month', $current ) );
    $next = date( 'Y-m', strtotime( '+1 month', $current ) );

    echo '<div class="ssc-month-nav">';
    echo '<a href="?sc_month=' . esc_attr( $prev ) . '#calendar">前月</a>';
    echo '<span>' . esc_html( $year . '年' . $month . '月' ) . '</span>';
    echo '<a href="?sc_month=' . esc_attr( $next ) . '#calendar">次月</a>';
    echo '</div>';

    // ↓↓↓ ここから下は【あなたの既存コードを一切変更しない】 ↓↓↓


    $first = strtotime( "$year-$month-01" );
    $start = strtotime( 'last sunday', $first );
    $end = strtotime( 'next sunday', strtotime( 'last day of', $first ) );

    $query_args = [
        'post_type' => 'schedule',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'ssc_date',
                'value' => [
                    date( 'Y-m-01', strtotime( "$year-$month-01" ) ),
                    date( 'Y-m-t', strtotime( "$year-$month-01" ) ),
                ],
                'compare' => 'BETWEEN',
                'type' => 'DATE',
            ],
        ],
    ];

    if ( !empty( $fields ) ) {
        $query_args[ 'meta_query' ][] = [
            'key' => 'ssc_field',
            'value' => $fields,
            'compare' => 'IN',
        ];
    }

    if ( !empty( $categories ) ) {
        $query_args[ 'tax_query' ] = [
            [
                'taxonomy' => 'ssc_category',
                'field' => 'slug',
                'terms' => $categories,
            ],
        ];
    }

    // ★★★ これが必要 ★★★
    $query = new WP_Query( $query_args );
    $posts = $query->posts;
    $map = [];
    foreach ( $posts as $p ) {
        $d = get_post_meta( $p->ID, 'ssc_date', true );
        $map[ $d ][] = $p;
        // 区分ターム取得
        $terms = get_the_terms( $p->ID, 'ssc_category' );
        $image_url = '';

        if ( $terms && !is_wp_error( $terms ) ) {
            $image_url = get_term_meta( $terms[ 0 ]->term_id, 'ssc_image_url', true );
        }
    }


    echo '<table class="ssc-calendar">';
    echo '<tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>';

    for ( $w = $start; $w <= $end; $w = strtotime( '+1 week', $w ) ) {
        echo '<tr>';
        for ( $i = 0; $i < 7; $i++ ) {
            $day = date( 'Y-m-d', strtotime( "+$i day", $w ) );
            echo '<td><div class="ssc-date">' . date( 'j', strtotime( $day ) ) . '</div>';
            if ( !empty( $map[ $day ] ) ) {

                foreach ( $map[ $day ] as $p ) {

                    $body = get_post_meta( $p->ID, 'ssc_body', true );
                    $edit_link = get_edit_post_link( $p->ID );

                    // ★★★ ここで取得する ★★★
                    $terms = get_the_terms( $p->ID, 'ssc_category' );
                    $image_url = '';

                    if ( $terms && !is_wp_error( $terms ) ) {
                        $image_url = get_term_meta( $terms[ 0 ]->term_id, 'ssc_image_url', true );
                    }

                    echo '<div class="ssc-item"
        data-title="' . esc_attr( $p->post_title ) . '"
        data-body="' . esc_attr( wp_strip_all_tags( $body ) ) . '"
        data-edit="' . esc_url( $edit_link ) . '"
    >';

                    if ( $image_url ) {
                        echo '<img 
            src="' . esc_url( $image_url ) . '" 
            alt="' . esc_attr( $p->post_title ) . '" 
            class="ssc-item-image"
        >';
                    } else {
                        echo '<span class="ssc-item-title">' . esc_html( $p->post_title ) . '</span>';
                    }

                    echo '</div>';
                }


            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';

    ?>
<div id="ssc-modal" class="ssc-modal">
    <div class="ssc-modal-bg"></div>
    <div class="ssc-modal-box">
        <button class="ssc-modal-close">×</button>
        <h3 id="ssc-modal-title"></h3>
        <div id="ssc-modal-body"></div>
        <?php if (is_user_logged_in()) : ?>
        <p class="ssc-edit-link"> <a href="#" target="_blank">この予定を編集</a> </p>
        <?php endif; ?>
    </div>
</div>
<?php

}
