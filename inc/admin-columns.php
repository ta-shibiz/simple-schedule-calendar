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
 * カラム表示（data属性付き）
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
            $val = get_post_meta($post_id, 'ssc_date', true);
            echo '<span class="ssc-date" data-value="'.esc_attr($val).'">'.esc_html($val ?: '—').'</span>';
            return;

case 'ssc_time':

    $val = get_post_meta($post_id, 'ssc_time_slot', true);
    $map = ssc_get_time_slots();

    echo isset($map[$val]) ? $map[$val] : '—';
    return;


case 'ssc_field':

    $val = get_post_meta($post_id, 'ssc_field', true);
    $map = ssc_get_fields();

    echo isset($map[$val]) ? $map[$val] : '—';
    return;
    }

}, 10, 2 );

/**
 * ==================================================
 * クイック編集UI
 * ==================================================
 */
add_action('quick_edit_custom_box', function($column_name, $post_type){

    if ($post_type !== 'schedule') return;

    if ($column_name === 'ssc_date') {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title">日付</span>
                    <input type="date" name="ssc_date">
                </label>
            </div>
        </fieldset>
        <?php
    }


}, 10, 2);

/**
 * ==================================================
 * クイック編集：値セットJS（超重要）
 * ==================================================
 */
add_action('admin_footer-edit.php', function(){

    global $post_type;
    if ($post_type !== 'schedule') return;
?>
<script>
jQuery(function($){

    const $wp_inline_edit = inlineEditPost.edit;

    inlineEditPost.edit = function(id){
        $wp_inline_edit.apply(this, arguments);

        let post_id = 0;

        if (typeof(id) === 'object') {
            post_id = parseInt(this.getId(id));
        }

        if (post_id > 0) {

            const $row = $('#post-' + post_id);
            const $edit = $('#edit-' + post_id);

            const date  = $row.find('.ssc-date').data('value');
            const time  = $row.find('.ssc-time').data('value');
            const field = $row.find('.ssc-field').data('value');

            $edit.find('input[name="ssc_date"]').val(date);
            $edit.find('select[name="ssc_time_slot"]').val(time);
            $edit.find('select[name="ssc_field"]').val(field);
        }
    };

});
</script>
<?php
});

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
 * 保存処理
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
 * ソート処理
 * ==================================================
 */
add_action('pre_get_posts', function($query){

    if ( !is_admin() || !$query->is_main_query() ) return;
    if ( $query->get('post_type') !== 'schedule' ) return;

    if ( $query->get('orderby') === 'ssc_date' ) {

        $query->set('meta_key', 'ssc_date');
        $query->set('orderby', 'meta_value');
    }

});