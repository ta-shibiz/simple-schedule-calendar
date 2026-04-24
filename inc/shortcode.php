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

  /**
   * ▼ 表示年月（最強版）
   * 優先順位：
   * 1. get_query_var
   * 2. $_GET
   * 3. 現在日時
   */
  $ym = get_query_var('sc_month');

  if (!$ym && isset($_GET['sc_month'])) {
    $ym = $_GET['sc_month'];
  }

  if ($ym && preg_match('/^\d{4}-\d{2}$/', $ym)) {
    [$year, $month] = explode('-', $ym);
    $year  = intval($year);
    $month = intval($month);
  } else {
    $current_time = current_time('timestamp');
    $year  = date('Y', $current_time);
    $month = date('n', $current_time);
  }

  /**
   * ▼ taxonomy（ssc_category）
   */
  $categories = [];
  if ($atts['category']) {
    $categories = array_map('trim', explode(',', $atts['category']));
  }

  /**
   * ▼ 使用フィールド（ssc_field）
   */
  $field_map = [
'base_met' => 'base_met',
'base_ub' => 'base_ub',
'base_b' => 'base_b',
'base_u' => 'base_u',
'met_lr' => 'met_lr',
'met_l' => 'met_l',
'met_r' => 'met_r',
      
  ];

  $fields = [];
  if ($atts['field']) {
    foreach (array_map('trim', explode(',', $atts['field'])) as $f) {
      if (isset($field_map[$f])) {
        $fields[] = $field_map[$f];
      }
    }
  }

  /**
   * ▼ デバッグ（必要なら一時ON）
   */
  /*
  echo '<pre>';
  echo 'GET:';
  var_dump($_GET);
  echo 'QUERY_VAR:';
  var_dump(get_query_var('sc_month'));
  echo 'YM:' . $year . '-' . $month;
  echo '</pre>';
  */

  ob_start();
  ssc_render_calendar($year, $month, $categories, $fields);
  return ob_get_clean();
});