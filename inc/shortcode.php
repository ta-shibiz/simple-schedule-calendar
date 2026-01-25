<?php
if (!defined('ABSPATH')) exit;

/**
 * [schedule_calendar category="event,reserve" field="red,yellow"]
 */
add_shortcode('schedule_calendar', function ($atts) {

  $atts = shortcode_atts([
    'category' => '',
    'field'    => '',
  ], $atts);

  // 表示年月
  if (isset($_GET['sc_month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['sc_month'])) {
    [$year, $month] = explode('-', $_GET['sc_month']);
    $month = intval($month);
  } else {
    $year  = date('Y');
    $month = date('n');
  }

  // taxonomy（ssc_category）
  $categories = [];
  if ($atts['category']) {
    $categories = array_map('trim', explode(',', $atts['category']));
  }

  // 使用フィールド（ssc_field）変換マップ
  $field_map = [
    'yellow' => 'hs_yellow_half',
    'red'    => 'hs_red_half',
    'full'   => 'hs_full',
    'little' => 'little',
  ];

  $fields = [];
  if ($atts['field']) {
    foreach (array_map('trim', explode(',', $atts['field'])) as $f) {
      if (isset($field_map[$f])) {
        $fields[] = $field_map[$f];
      }
    }
  }

  ob_start();
  ssc_render_calendar($year, $month, $categories, $fields);
  return ob_get_clean();
});
