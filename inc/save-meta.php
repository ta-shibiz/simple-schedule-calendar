<?php

add_action('save_post_schedule', function ($post_id) {

  if (!isset($_POST['ssc_nonce'])) return;
  if (!wp_verify_nonce($_POST['ssc_nonce'], 'ssc_save_meta')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  $keys = ['ssc_date', 'ssc_time_slot', 'ssc_field', 'ssc_body', 'ssc_url'];

  foreach ($keys as $key) {

    if (!isset($_POST[$key])) {
      if ($key === 'ssc_field' && function_exists('ssc_get_default_field')) {
        $default_field = ssc_get_default_field();

        if ($default_field !== '') {
          update_post_meta($post_id, $key, $default_field);
        }
      }

      continue;
    }

    $value = sanitize_text_field(wp_unslash($_POST[$key]));

    if ($key === 'ssc_field' && $value === '' && function_exists('ssc_get_default_field')) {
      $value = ssc_get_default_field();
    }

    // 🔒 空文字は保存しない（特に select 用）
    if ($value === '') {
      delete_post_meta($post_id, $key);
      continue;
    }

    update_post_meta($post_id, $key, $value);
  }
});
