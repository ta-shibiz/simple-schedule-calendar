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
    ?>
    <fieldset class="inline-edit-col-right ssc-quickedit-wrap">
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
                            <option value="">---</option>
                            <?php foreach ( ssc_get_fields() as $key => $label ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>">
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
    ?>
    <script>
    jQuery(function($){

        const wpInlineEdit = inlineEditPost.edit;

        inlineEditPost.edit = function(id){
            wpInlineEdit.apply(this, arguments);

            let postId = 0;

            if (typeof(id) === 'object') {
                postId = parseInt(this.getId(id), 10);
            }

            if (!postId) return;

            const $row  = $('#post-' + postId);
            const $edit = $('#edit-' + postId);

            const date  = $row.find('.ssc-date').data('value') || '';
            const time  = $row.find('.ssc-time').data('value') || '';
            const field = $row.find('.ssc-field').data('value') || '';

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

}, 10 );