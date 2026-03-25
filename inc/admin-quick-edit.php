<?php

/* ================================
クイック編集 UI
================================ */

add_action('quick_edit_custom_box', function($column, $post_type){

    if($post_type !== 'schedule') return;

    if($column !== 'ssc_date') return;

?>

<fieldset class="inline-edit-col-left">
<div class="inline-edit-col">

<label>
<span class="title">日付</span>
<span class="input-text-wrap">
<input type="date" name="ssc_date">
</span>
</label>

<label>
<span class="title">時間帯</span>
<select name="ssc_time">
<option value="">---</option>
<option value="昼">昼</option>
<option value="夜">夜</option>
</select>
</label>

<label>
<span class="title">使用フィールド</span>
<input type="text" name="ssc_field">
</label>

<label>
<span class="title">本文（短文）</span>
<input type="text" name="ssc_body">
</label>

<label>
<span class="title">リンクURL</span>
<input type="text" name="ssc_url">
</label>

</div>
</fieldset>

<?php

},10,2);


/* ================================
保存処理
================================ */

add_action('save_post', function($post_id){

    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if(isset($_POST['ssc_date']))
        update_post_meta($post_id,'ssc_date',sanitize_text_field($_POST['ssc_date']));

    if(isset($_POST['ssc_time']))
        update_post_meta($post_id,'ssc_time',sanitize_text_field($_POST['ssc_time']));

    if(isset($_POST['ssc_field']))
        update_post_meta($post_id,'ssc_field',sanitize_text_field($_POST['ssc_field']));

    if(isset($_POST['ssc_body']))
        update_post_meta($post_id,'ssc_body',sanitize_text_field($_POST['ssc_body']));

    if(isset($_POST['ssc_url']))
        update_post_meta($post_id,'ssc_url',sanitize_text_field($_POST['ssc_url']));

});