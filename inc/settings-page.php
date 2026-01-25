<?php

add_action('admin_menu', function () {
  add_options_page(
    'スケジュール設定',
    'スケジュール設定',
    'manage_options',
    'ssc-settings',
    'ssc_render_settings_page'
  );
});

add_action('admin_init', function () {
  register_setting('ssc_settings', 'ssc_fields');
});

function ssc_default_fields() {
  return [
    'hs_yellow_half' => [
      'label' => 'ヘッドショット・イエローフィールド（片面）',
      'order' => 10,
      'enabled' => true,
    ],
    'hs_red_half' => [
      'label' => 'ヘッドショット・レッドフィールド（片面）',
      'order' => 20,
      'enabled' => true,
    ],
    'hs_full' => [
      'label' => 'ヘッドショット全面',
      'order' => 30,
      'enabled' => true,
    ],
    'little' => [
      'label' => 'リトルヘッドショット',
      'order' => 40,
      'enabled' => true,
    ],
  ];
}

function ssc_render_settings_page() {
  $fields = get_option('ssc_fields', ssc_default_fields());
  ?>
  <div class="wrap">
    <h1>スケジュール：フィールド設定</h1>
    <form method="post" action="options.php">
      <?php settings_fields('ssc_settings'); ?>
      <table class="widefat">
        <thead>
          <tr>
            <th>有効</th>
            <th>ID</th>
            <th>表示名</th>
            <th>並び順</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fields as $key => $f): ?>
          <tr>
            <td>
              <input type="checkbox"
                name="ssc_fields[<?= esc_attr($key) ?>][enabled]"
                value="1"
                <?= !empty($f['enabled']) ? 'checked' : '' ?>>
            </td>
            <td><code><?= esc_html($key) ?></code></td>
            <td>
              <input type="text"
                name="ssc_fields[<?= esc_attr($key) ?>][label]"
                value="<?= esc_attr($f['label']) ?>">
            </td>
            <td>
              <input type="number"
                name="ssc_fields[<?= esc_attr($key) ?>][order]"
                value="<?= esc_attr($f['order']) ?>">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}
