<?php
/**
 * Schedule post type & taxonomy
 * Safe version (taxonomy renamed to avoid DB conflict)
 */

/**
 * 投稿タイプ：schedule
 */
add_action('init', function () {

  register_post_type('schedule', [
    'label' => 'スケジュール',
    'public' => false,
    'show_ui' => true,
    'menu_position' => 20,
    'menu_icon' => 'dashicons-calendar-alt',
    'supports' => ['title', 'thumbnail'],
    'has_archive' => false,
    'rewrite' => false,
    'query_var' => false,
    'show_in_rest' => true,
  ]);

});

/**
 * タクソノミー：ssc_category
 * ※ schedule_category は使わない（DB事故回避）
 */
add_action('init', function () {

  register_taxonomy(
    'ssc_category',
    'schedule',
    [
      'label' => 'スケジュール区分',
      'public' => false,
      'show_ui' => true,
      'hierarchical' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,

      // 権限（管理者で確実に操作できる）
      'capabilities' => [
        'manage_terms' => 'manage_categories',
        'edit_terms'   => 'manage_categories',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts',
      ],

      'rewrite'   => false,
      'query_var'=> false,
    ]
  );

});
