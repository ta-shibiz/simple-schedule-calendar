<?php
/*
Plugin Name: Simple Schedule Calendar
Description: 日付・時間帯・フィールド単位で管理できる軽量スケジュールカレンダー
Version: 1.0.0
Author: Midnight Code
*/

if (!defined('ABSPATH')) exit;

define('SSC_PATH', plugin_dir_path(__FILE__));
define('SSC_URL', plugin_dir_url(__FILE__));

require_once SSC_PATH . 'inc/post-type.php';
require_once SSC_PATH . 'inc/meta-boxes.php';
require_once SSC_PATH . 'inc/save-meta.php';
require_once SSC_PATH . 'inc/settings-page.php';
require_once SSC_PATH . 'inc/calendar-render.php';
require_once SSC_PATH . 'inc/shortcode.php';

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'ssc-calendar',
    SSC_URL . 'assets/css/calendar.css',
    [],
    '1.0.0'
  );
  wp_enqueue_script(
    'ssc-modal',
    SSC_URL . 'assets/js/ssc-modal.js',
    [],
    '1.0.0',
    true
  );
});
