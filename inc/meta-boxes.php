<?php

add_action('add_meta_boxes', function () {
  add_meta_box(
    'ssc_schedule_meta',
    'スケジュール情報',
    'ssc_render_meta_box',
    'schedule',
    'normal',
    'high'
  );
});

function ssc_render_meta_box($post) {

  $date  = get_post_meta($post->ID, 'ssc_date', true);
  $slot  = get_post_meta($post->ID, 'ssc_time_slot', true);
  $field = get_post_meta($post->ID, 'ssc_field', true);
  $url   = get_post_meta($post->ID, 'ssc_url', true);
  $body  = get_post_meta($post->ID, 'ssc_body', true);

  wp_nonce_field('ssc_save_meta', 'ssc_nonce');
  ?>
  <p>
    <label>日付</label><br>
    <input type="date" name="ssc_date" value="<?= esc_attr($date) ?>">
  </p>

  <p>
    <label>時間帯</label><br>
    <select name="ssc_time_slot">
      <option value="day" <?= selected($slot, 'day') ?>>昼</option>
      <option value="night" <?= selected($slot, 'night') ?>>夜</option>
      <option value="full" <?= selected($slot, 'full') ?>>終日</option>
    </select>
  </p>

  <p>
    <label>使用フィールド</label><br>
     
<?php
$saved_field = get_post_meta($post->ID, 'ssc_field', true);
?>
<select name="ssc_field">
<option value="">選択してください</option>
  <option value="hs_yellow_half" <?php selected($saved_field, 'hs_yellow_half'); ?>>
    HS イエロー（片面）
  </option>
  <option value="hs_red_half" <?php selected($saved_field, 'hs_red_half'); ?>>
    HS レッド（片面）
  </option>
  <option value="hs_full" <?php selected($saved_field, 'hs_full'); ?>>
    HS 全面
  </option>
  <option value="little" <?php selected($saved_field, 'little'); ?>>
    リトルヘッドショット
  </option>
</select>
  </p>
  <p>
    <label>本文（短文）</label><br>
    <textarea name="ssc_body" rows="3"><?= esc_textarea($body) ?></textarea>
  </p>

  <p>
    <label>リンクURL</label><br>
    <input type="url" name="ssc_url" value="<?= esc_attr($url) ?>" style="width:100%;">
  </p>
<?php
}
