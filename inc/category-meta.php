<?php
/**
 * ssc_category 用 カスタムフィールド
 * ・画像URL
 * ・リンクURL（NEW）
 * 保存先：term meta
 */

if (!defined('ABSPATH')) exit;

/**
 * 新規ターム追加画面
 */
add_action('ssc_category_add_form_fields', function () {
?>
<div class="form-field term-ssc-image-wrap">
  <label for="ssc_image_url">画像URL</label>
  <input type="text" name="ssc_image_url" id="ssc_image_url" value="" />
  <p class="description">カテゴリに紐づける画像のURLを入力してください</p>
</div>

<div class="form-field term-ssc-link-wrap">
  <label for="ssc_link_url">リンクURL</label>
  <input type="text" name="ssc_link_url" id="ssc_link_url" value="" />
  <p class="description">この区分クリック時の遷移先URL（未入力ならモーダル表示）</p>
</div>
<?php
});

/**
 * ターム編集画面
 */
add_action('ssc_category_edit_form_fields', function ($term) {

  $image_url = get_term_meta($term->term_id, 'ssc_image_url', true);
  $link_url  = get_term_meta($term->term_id, 'ssc_link_url', true);
?>
<tr class="form-field term-ssc-image-wrap">
  <th scope="row"><label for="ssc_image_url">画像URL</label></th>
  <td>
    <input
      type="text"
      name="ssc_image_url"
      id="ssc_image_url"
      value="<?php echo esc_attr($image_url); ?>"
      class="regular-text"
    />
    <p class="description">カテゴリに紐づける画像のURLを入力してください</p>
    <?php if ($image_url): ?>
      <div style="margin-top:8px;">
        <img src="<?php echo esc_url($image_url); ?>" style="max-width:150px;height:auto;">
      </div>
    <?php endif; ?>
  </td>
</tr>

<tr class="form-field term-ssc-link-wrap">
  <th scope="row"><label for="ssc_link_url">リンクURL</label></th>
  <td>
    <input
      type="text"
      name="ssc_link_url"
      id="ssc_link_url"
      value="<?php echo esc_attr($link_url); ?>"
      class="regular-text"
    />
    <p class="description">クリック時の遷移先URL（設定するとモーダルは出ません）</p>
  </td>
</tr>
<?php
});

/**
 * 保存処理（画像＋リンク）
 */
add_action('edited_ssc_category', 'ssc_save_category_meta', 10, 1);
add_action('created_ssc_category', 'ssc_save_category_meta', 10, 1);

function ssc_save_category_meta($term_id) {

  // =========================
  // 画像URL
  // =========================
  if (isset($_POST['ssc_image_url'])) {

    $image_url = trim($_POST['ssc_image_url']);

    if ($image_url === '') {
      delete_term_meta($term_id, 'ssc_image_url');
    } else {

      $image_url = esc_url_raw($image_url);
      $existing  = get_term_meta($term_id, 'ssc_image_url', true);

      if ($existing === '') {
        add_term_meta($term_id, 'ssc_image_url', $image_url, true);
      } else {
        update_term_meta($term_id, 'ssc_image_url', $image_url);
      }
    }
  }

  // =========================
  // リンクURL（NEW）
  // =========================
  if (isset($_POST['ssc_link_url'])) {

    $link_url = trim($_POST['ssc_link_url']);

    if ($link_url === '') {
      delete_term_meta($term_id, 'ssc_link_url');
    } else {

      $link_url = esc_url_raw($link_url);
      $existing = get_term_meta($term_id, 'ssc_link_url', true);

      if ($existing === '') {
        add_term_meta($term_id, 'ssc_link_url', $link_url, true);
      } else {
        update_term_meta($term_id, 'ssc_link_url', $link_url);
      }
    }
  }
}

/**
 * 管理画面 ターム一覧に画像カラム追加
 */
add_filter('manage_edit-ssc_category_columns', function ($columns) {

  $new = [];

  foreach ($columns as $key => $label) {
    if ($key === 'name') {
      $new['ssc_image'] = '画像';
    }
    $new[$key] = $label;
  }

  return $new;
});

/**
 * カラム内容出力
 */
add_filter('manage_ssc_category_custom_column', function ($content, $column_name, $term_id) {

  if ($column_name !== 'ssc_image') {
    return $content;
  }

  $image_url = get_term_meta($term_id, 'ssc_image_url', true);

  if (!$image_url) {
    return '—';
  }

  return sprintf(
    '<img src="%s" style="width:48px;height:48px;object-fit:cover;border-radius:4px;" />',
    esc_url($image_url)
  );

}, 10, 3);

/**
 * カラム幅調整
 */
add_action('admin_head', function () {

  $screen = get_current_screen();

  if ($screen && $screen->taxonomy === 'ssc_category') {
    echo '<style>
      .wp-list-table .column-ssc_image {
        width: 120px;
        text-align: center;
      }
      .wp-list-table .column-ssc_image img{
        width:100% !important;
        height:auto !important;
        display:block;
      }
    </style>';
  }
});