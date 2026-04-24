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

    $first = strtotime( "$year-$month-01" );
    $start = strtotime( 'last sunday', $first );
    $last = strtotime( date( 'Y-m-t', $first ) );
    $end = strtotime( 'next saturday', $last );

    $query_args = [
        'post_type' => 'schedule',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'ssc_date',
                'value' => [
                    date( 'Y-m-d', $start ),
                    date( 'Y-m-d', $end )
                ],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ]
        ]
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

    $query = new WP_Query( $query_args );
    $posts = $query->posts;

    $map = [];
    foreach ( $posts as $p ) {
        $d = get_post_meta( $p->ID, 'ssc_date', true );
        $map[ $d ][] = $p;
    }

    echo '<table class="ssc-calendar">';
    echo '<tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>';

    for ( $w = $start; $w <= $end; $w = strtotime( '+1 week', $w ) ) {
        echo '<tr>';

        for ( $i = 0; $i < 7; $i++ ) {

            $day = date( 'Y-m-d', strtotime( "+$i day", $w ) );

            echo '<td>';
            echo '<div class="ssc-date">' . date( 'j', strtotime( $day ) ) . '</div>';

            if ( !empty( $map[ $day ] ) ) {

                foreach ( $map[ $day ] as $p ) {

                    $body = get_post_meta( $p->ID, 'ssc_body', true );
                    $edit_link = get_edit_post_link( $p->ID );

                    // =========================
                    // ★投稿URL（最優先）
                    // =========================
                    $post_url = get_post_meta( $p->ID, 'ssc_url', true );

                    // =========================
                    // ターム情報
                    // =========================
                    $terms = get_the_terms( $p->ID, 'ssc_category' );

                    $image_url = '';
                    $term_url = '';

                    if ( $terms && !is_wp_error( $terms ) ) {

                        foreach ( $terms as $term ) {

                            if ( !$image_url ) {
                                $image_url = get_term_meta( $term->term_id, 'ssc_image_url', true );
                            }

                            if ( !$term_url ) {
                                $term_url = get_term_meta( $term->term_id, 'ssc_link_url', true );
                            }
                        }
                    }

                    // =========================
                    // ★最終リンク決定
                    // =========================
                    $link_url = '';

                    if ( $post_url ) {
                        $link_url = $post_url;
                    } elseif ( $term_url ) {
                        $link_url = $term_url;
                    }


                    // =========================
                    // 出力
                    // =========================
                    echo '<div class="ssc-item"
    data-title="' . esc_attr( $p->post_title ) . '"
    data-body="' . esc_attr( wp_strip_all_tags( $body ) ) . '"
    data-edit="' . esc_url( $edit_link ) . '"
    data-link="' . esc_url( $link_url ) . '"
>';

                    if ( $image_url ) {

                        echo '<img 
        src="' . esc_url( $image_url ) . '" 
        alt="' . esc_attr( $p->post_title ) . '" 
        class="ssc-item-image">';

                        echo '<span class="ssc-ttl">' . esc_html( $p->post_title ) . '</span>';

                    } else {

                        echo '<span class="ssc-item-title">' . esc_html( $p->post_title ) . '</span>';
                    }

                    echo '</div>';


                    // =========================
                    // ★編集リンク（NEW）
                    // =========================
                    if ( is_user_logged_in() ) {

                        echo '<a href="' . esc_url( $edit_link ) . '" 
        class="ssc-item-edit"
        target="_blank">
        編集
    </a>';
                    }


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
