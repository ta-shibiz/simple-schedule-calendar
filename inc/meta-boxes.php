<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ==================================================
 * メタボックス登録
 * ==================================================
 */
add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'ssc_schedule_meta',
        'スケジュール情報',
        'ssc_render_meta_box',
        'schedule',
        'normal',
        'high'
    );
} );

/**
 * ==================================================
 * メタボックス表示
 * ==================================================
 */
function ssc_render_meta_box( $post ) {

    $date  = get_post_meta( $post->ID, 'ssc_date', true );
    $slot  = get_post_meta( $post->ID, 'ssc_time_slot', true );
    $field = get_post_meta( $post->ID, 'ssc_field', true );
    $url   = get_post_meta( $post->ID, 'ssc_url', true );
    $body  = get_post_meta( $post->ID, 'ssc_body', true );

    wp_nonce_field( 'ssc_save_meta', 'ssc_nonce' );
    ?>

    <p>
        <label for="ssc_date">日付</label><br>
        <input type="date" id="ssc_date" name="ssc_date" value="<?php echo esc_attr( $date ); ?>">
    </p>

    <p>
        <label for="ssc_time_slot">時間帯</label><br>
        <select id="ssc_time_slot" name="ssc_time_slot">
            <option value="">選択</option>
            <?php foreach ( ssc_get_time_slots() as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $slot, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="ssc_field">使用フィールド</label><br>
        <select id="ssc_field" name="ssc_field">
            <option value="">選択</option>
            <?php foreach ( ssc_get_fields() as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $field, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="ssc_body">本文（短文）</label><br>
        <textarea id="ssc_body" name="ssc_body" rows="3" style="width: 100%;"><?php echo esc_textarea( $body ); ?></textarea>
    </p>

    <p>
        <label for="ssc_url">リンクURL</label><br>
        <input type="url" id="ssc_url" name="ssc_url" value="<?php echo esc_attr( $url ); ?>" style="width: 100%;">
    </p>

    <?php
}

/**
 * ==================================================
 * 保存処理
 * ==================================================
 */
add_action( 'save_post', function ( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ssc_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['ssc_nonce'], 'ssc_save_meta' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( get_post_type( $post_id ) !== 'schedule' ) return;

    if ( isset( $_POST['ssc_date'] ) ) {
        update_post_meta( $post_id, 'ssc_date', sanitize_text_field( $_POST['ssc_date'] ) );
    }

    if ( isset( $_POST['ssc_time_slot'] ) ) {
        update_post_meta( $post_id, 'ssc_time_slot', sanitize_text_field( $_POST['ssc_time_slot'] ) );
    }

    if ( isset( $_POST['ssc_field'] ) ) {
        update_post_meta( $post_id, 'ssc_field', sanitize_text_field( $_POST['ssc_field'] ) );
    }

    if ( isset( $_POST['ssc_body'] ) ) {
        update_post_meta( $post_id, 'ssc_body', sanitize_textarea_field( $_POST['ssc_body'] ) );
    }

    if ( isset( $_POST['ssc_url'] ) ) {
        update_post_meta( $post_id, 'ssc_url', esc_url_raw( $_POST['ssc_url'] ) );
    }

} );