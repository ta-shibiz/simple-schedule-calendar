<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================
クイック編集 UI
================================ */

add_action( 'quick_edit_custom_box', function( $column, $post_type ) {

    if ( $post_type !== 'schedule' ) return;

    // 1回だけ表示
    static $printed = false;
    if ( $printed ) return;

    // 「日付」カラムの位置で1回だけ差し込む
    if ( $column !== 'ssc_date' ) return;

    $printed = true;
    $default_field = function_exists( 'ssc_get_default_field' ) ? ssc_get_default_field() : '';
    ?>
    <fieldset class="inline-edit-col-right ssc-quickedit-wrap">
        <?php wp_nonce_field( 'ssc_quick_edit', 'ssc_quick_edit_nonce' ); ?>
        <div class="inline-edit-col">
            <div class="ssc-quickedit-grid">

                <div class="ssc-qe-item">
                    <label>
                        <span class="title">日付</span>
                        <span class="input-text-wrap">
                            <input type="date" name="ssc_date">
                        </span>
                    </label>
                </div>

                <div class="ssc-qe-item">
                    <label>
                        <span class="title">時間帯</span>
                        <select name="ssc_time_slot">
                            <option value="">---</option>
                            <?php foreach ( ssc_get_time_slots() as $key => $label ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>">
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="ssc-qe-item">
                    <label>
                        <span class="title">使用フィールド</span>
                        <select name="ssc_field">
                            <option value="" <?php selected( $default_field, '' ); ?>>---</option>
                            <?php foreach ( ssc_get_fields() as $key => $label ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $default_field, $key ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

            </div>
        </div>
    </fieldset>

    <style>
    .inline-edit-row .ssc-quickedit-wrap {
        clear: both;
        width: 100%;
        margin-top: 12px;
        padding-top: 8px;
        border-top: 1px solid #ddd;
    }

    .inline-edit-row .ssc-quickedit-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 16px 20px;
        align-items: flex-end;
    }

    .inline-edit-row .ssc-qe-item {
        min-width: 180px;
        max-width: 320px;
    }

    .inline-edit-row .ssc-qe-item label {
        display: block;
        width: 100%;
    }

    .inline-edit-row .ssc-qe-item .title {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .inline-edit-row .ssc-qe-item input[type="date"],
    .inline-edit-row .ssc-qe-item select {
        width: 100%;
        max-width: 100%;
    }

    .inline-edit-row .inline-edit-col-left,
    .inline-edit-row .inline-edit-col-center {
        vertical-align: top;
    }
    </style>
    <?php

}, 10, 2 );


/* ================================
値セット JS
================================ */

add_action( 'admin_footer-edit.php', function() {

    global $post_type;
    if ( $post_type !== 'schedule' ) return;
    $default_field = function_exists( 'ssc_get_default_field' ) ? ssc_get_default_field() : '';
    ?>
    <script>
    jQuery(function($){

        const wpInlineEdit = inlineEditPost.edit;

        inlineEditPost.edit = function(id){
            wpInlineEdit.apply(this, arguments);

            let postId = 0;

            if (typeof(id) === 'object') {
                postId = parseInt(this.getId(id), 10);
            } else {
                postId = parseInt(id, 10);
            }

            if (!postId) return;

            const $row  = $('#post-' + postId);
            const $edit = $('#edit-' + postId);

            const date  = String($row.find('.ssc-date').attr('data-value') || '');
            const time  = String($row.find('.ssc-time').attr('data-value') || '');
            const field = String($row.find('.ssc-field').attr('data-value') || <?php echo wp_json_encode( $default_field ); ?>);

            $edit.find('input[name="ssc_date"]').val(date);
            $edit.find('select[name="ssc_time_slot"]').val(time);
            $edit.find('select[name="ssc_field"]').val(field);
        };

    });
    </script>
    <?php

} );


/* ================================
保存処理
================================ */

add_action( 'save_post', function( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['_inline_edit'] ) ) return;
    if ( ! isset( $_POST['ssc_quick_edit_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ssc_quick_edit_nonce'] ) ), 'ssc_quick_edit' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( get_post_type( $post_id ) !== 'schedule' ) return;

    foreach ( [ 'ssc_date', 'ssc_time_slot', 'ssc_field' ] as $key ) {
        $value = isset( $_POST[ $key ] )
            ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) )
            : '';

        if ( $key === 'ssc_field' && $value === '' && function_exists( 'ssc_get_default_field' ) ) {
            $value = ssc_get_default_field();
        }

        // クイック編集で値を取得できなかった場合は既存値を保持する。
        if ( $value === '' ) continue;

        update_post_meta( $post_id, $key, $value );
    }

}, 10 );
