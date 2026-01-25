<?php
if (!defined('ABSPATH')) exit;

/**
 * [schedule_calendar category="event,reserve"]
 */
add_shortcode('schedule_calendar', function ($atts) {

  $atts = shortcode_atts([
    'category' => '',
  ], $atts);

  // 表示年月（URL or デフォルト）
  if (isset($_GET['sc_month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['sc_month'])) {
    [$year, $month] = explode('-', $_GET['sc_month']);
    $month = intval($month);
  } else {
    $year  = date('Y');
    $month = date('n');
  }

  // category は将来用（今は未使用だが壊さない）
  $categories = [];
  if ($atts['category']) {
    $categories = array_map('trim', explode(',', $atts['category']));
  }

  ob_start();
  ssc_render_calendar($year, $month);
  return ob_get_clean();
});
