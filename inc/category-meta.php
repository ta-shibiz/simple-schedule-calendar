<?php

// 追加フォーム
add_action('schedule_category_add_form_fields', function () {
  ?>
  <div class="form-field">
    <label>表示色</label>
    <input type="color" name="ssc_cat_color" value="#444444">
  </div>

  <div class="form-field">
    <label>アイコン（文字 or 絵文字）</label>
    <input type="text" name="ssc_cat_icon" placeholder="例：🔒">
  </div>
  <?php
});

// 編集フォーム
add_action('schedule_category_edit_form_fields', function ($term) {

  $color = get_term_meta($term->term_id, 'ssc_cat_color', true);
  $icon  = get_term_meta($term->term_id, 'ssc_cat_icon', true);
  ?>
  <tr class="form-field">
    <th>表示色</th>
    <td>
      <input type="color" name="ssc_cat_color" value="<?= esc_attr($color ?: '#444444') ?>">
    </td>
  </tr>

  <tr class="form-field">
    <th>アイコン</th>
    <td>
      <input type="text" name="ssc_cat_icon" value="<?= esc_attr($icon) ?>">
    </td>
  </tr>
  <?php
});

add_action('created_schedule_category', 'ssc_save_category_meta');
add_action('edited_schedule_category', 'ssc_save_category_meta');

function ssc_save_category_meta($term_id) {

  if (isset($_POST['ssc_cat_color'])) {
    update_term_meta($term_id, 'ssc_cat_color', sanitize_hex_color($_POST['ssc_cat_color']));
  }

  if (isset($_POST['ssc_cat_icon'])) {
    update_term_meta($term_id, 'ssc_cat_icon', sanitize_text_field($_POST['ssc_cat_icon']));
  }
}
