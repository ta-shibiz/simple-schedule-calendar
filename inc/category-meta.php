<?php
/**
 * ssc_category 用 カスタムフィールド（画像URL）
 * 保存先：term meta
 */

/**
 * 新規ターム追加画面
 */
add_action( 'ssc_category_add_form_fields', function () {
            ?>
<div class="form-field term-ssc-image-wrap">
    <label for="ssc_image_url">画像URL</label>
    <input type="text" name="ssc_image_url" id="ssc_image_url" value="" />
    <p class="description">カテゴリに紐づける画像のURLを入力してください</p>
</div>
<?php
} );

/**
 * ターム編集画面
 */
add_action( 'ssc_category_edit_form_fields', function ( $term ) {
            $image_url = get_term_meta( $term->term_id, 'ssc_image_url', true );
            ?>
<tr class="form-field term-ssc-image-wrap">
    <th scope="row"> <label for="ssc_image_url">画像URL</label>
    </th>
    <td><input
        type="text"
        name="ssc_image_url"
        id="ssc_image_url"
        value="<?php echo esc_attr($image_url); ?>"
        class="regular-text"
      />
        <p class="description">カテゴリに紐づける画像のURLを入力してください</p>
        <?php if ($image_url): ?>
        <div style="margin-top:8px;"> <img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width:150px;height:auto;"> </div>
        <?php endif; ?></td>
</tr>
<?php
} );


/**
 * 保存処理（安全版）
 */
// 編集時
add_action( 'edited_ssc_category', 'ssc_save_category_image_meta', 10, 1 );
// 新規作成時
add_action( 'created_ssc_category', 'ssc_save_category_image_meta', 10, 1 );

function ssc_save_category_image_meta( $term_id ) {

    // フィールドが送られていなければ何もしない
    if ( !isset( $_POST[ 'ssc_image_url' ] ) ) {
        return;
    }

    $image_url = trim( $_POST[ 'ssc_image_url' ] );

    // 空の場合は削除
    if ( $image_url === '' ) {
        delete_term_meta( $term_id, 'ssc_image_url' );
        return;
    }

    $image_url = esc_url_raw( $image_url );

    // 既存チェック（★超重要）
    $existing = get_term_meta( $term_id, 'ssc_image_url', true );

    if ( $existing === '' ) {
        // まだ無ければ add（重複INSERT防止）
        add_term_meta( $term_id, 'ssc_image_url', $image_url, true );
    } else {
        // 既にあれば update（INSERTを発生させない）
        update_term_meta( $term_id, 'ssc_image_url', $image_url );
    }
}


/**
 * ssc_category : 管理画面 ターム一覧に画像カラムを追加
 */

if ( !defined( 'ABSPATH' ) )exit;

/**
 * カラム追加
 */
add_filter( 'manage_edit-ssc_category_columns', function ( $columns ) {

    // 名前の前に画像を入れたい場合
    $new = [];

    foreach ( $columns as $key => $label ) {
        if ( $key === 'name' ) {
            $new[ 'ssc_image' ] = '画像';
        }
        $new[ $key ] = $label;
    }

    return $new;
} );

/**
 * カラム内容出力
 */
add_filter( 'manage_ssc_category_custom_column', function ( $content, $column_name, $term_id ) {
    if ( $column_name !== 'ssc_image' ) {
        return $content;
    }
    $image_url = get_term_meta( $term_id, 'ssc_image_url', true );
    if ( !$image_url ) {
        return '—';
    }
    return sprintf(
        '<img src="%s" style="width:48px;height:48px;object-fit:cover;border-radius:4px;" />',
        esc_url( $image_url )
    );

}, 10, 3 );

/**
 * カラム幅調整（任意・安全）
 */
add_action( 'admin_head', function () {
    $screen = get_current_screen();
    if ( $screen && $screen->taxonomy === 'ssc_category' ) {
        echo '<style>
            .wp-list-table .column-ssc_image {
                width: 120px;
                text-align: center;
            }
            .wp-list-table .column-ssc_image img{
            width:100% !important;
            height:auto !important;
            display:block;
            }
        </style>';
    }
} );
