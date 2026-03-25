<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 有効な使用フィールド一覧を取得
 * settings-page.php の ssc_fields を利用
 */
if ( ! function_exists( 'ssc_get_enabled_fields' ) ) {
	function ssc_get_enabled_fields() {
		$fields = get_option( 'ssc_fields', ssc_default_fields() );

		if ( ! is_array( $fields ) ) {
			$fields = ssc_default_fields();
		}

		uasort( $fields, function( $a, $b ) {
			$a_order = isset( $a['order'] ) ? (int) $a['order'] : 0;
			$b_order = isset( $b['order'] ) ? (int) $b['order'] : 0;
			return $a_order <=> $b_order;
		} );

		$enabled = array();

		foreach ( $fields as $key => $field ) {
			if ( empty( $field['enabled'] ) ) {
				continue;
			}

			$enabled[ $key ] = array(
				'label' => isset( $field['label'] ) ? $field['label'] : $key,
				'order' => isset( $field['order'] ) ? (int) $field['order'] : 0,
			);
		}

		return $enabled;
	}
}

/**
 * クイック編集UI
 */
add_action( 'quick_edit_custom_box', function( $column_name, $post_type ) {

	if ( $post_type !== 'schedule' ) {
		return;
	}

	// 同じ行で複数回呼ばれるので1回だけ出力
	static $printed = false;
	if ( $printed ) {
		return;
	}

	$printed = true;

	$fields = ssc_get_enabled_fields();

	$terms = get_terms( array(
		'taxonomy'   => 'ssc_category',
		'hide_empty' => false,
	) );
	?>
	<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<h4>スケジュール情報</h4>

			<?php wp_nonce_field( 'ssc_save_meta', 'ssc_nonce' ); ?>

			<label>
				<span class="title">区分</span>
				<span class="input-text-wrap">
					<select name="ssc_category_term">
						<option value="">— 選択してください —</option>
						<?php
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) :
							foreach ( $terms as $term ) :
								?>
								<option value="<?php echo esc_attr( $term->term_id ); ?>">
									<?php echo esc_html( $term->name ); ?>
								</option>
								<?php
							endforeach;
						endif;
						?>
					</select>
				</span>
			</label>

			<label>
				<span class="title">日付</span>
				<span class="input-text-wrap">
					<input type="date" name="ssc_date" value="">
				</span>
			</label>

			<label>
				<span class="title">時間帯</span>
				<span class="input-text-wrap">
					<input type="text" name="ssc_time_slot" value="">
				</span>
			</label>

			<label>
				<span class="title">使用フィールド</span>
				<span class="input-text-wrap">
					<select name="ssc_field">
						<option value="">— 選択してください —</option>
						<?php foreach ( $fields as $key => $field ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</span>
			</label>

			<label>
				<span class="title">本文（短文）</span>
				<span class="input-text-wrap">
					<input type="text" name="ssc_body" value="">
				</span>
			</label>

			<label>
				<span class="title">リンクURL</span>
				<span class="input-text-wrap">
					<input type="text" name="ssc_url" value="">
				</span>
			</label>
		</div>
	</fieldset>
	<?php
}, 10, 2 );

/**
 * 一覧行にクイック編集用データを埋め込む
 */
add_filter( 'post_row_actions', function( $actions, $post ) {

	if ( ! $post || $post->post_type !== 'schedule' ) {
		return $actions;
	}

	$ssc_date      = get_post_meta( $post->ID, 'ssc_date', true );
	$ssc_time_slot = get_post_meta( $post->ID, 'ssc_time_slot', true );
	$ssc_field     = get_post_meta( $post->ID, 'ssc_field', true );
	$ssc_body      = get_post_meta( $post->ID, 'ssc_body', true );
	$ssc_url       = get_post_meta( $post->ID, 'ssc_url', true );

	$term_ids = wp_get_object_terms( $post->ID, 'ssc_category', array(
		'fields' => 'ids',
	) );

	$term_id = '';
	if ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
		$term_id = (int) $term_ids[0];
	}

	$actions['ssc_inline_data'] =
		'<div class="ssc-inline-data" style="display:none;">' .
			'<span class="ssc_date">' . esc_html( $ssc_date ) . '</span>' .
			'<span class="ssc_time_slot">' . esc_html( $ssc_time_slot ) . '</span>' .

			'<span class="ssc_field">' . esc_html( $ssc_field ) . '</span>' .
			'<span class="ssc_body">' . esc_html( $ssc_body ) . '</span>' .
			'<span class="ssc_url">' . esc_html( $ssc_url ) . '</span>' .
			'<span class="ssc_category_term">' . esc_html( $term_id ) . '</span>' .
		'</div>';

	return $actions;
}, 10, 2 );

/**
 * クイック編集を開いたとき既存値を流し込む
 */
add_action( 'admin_footer-edit.php', function() {
	$screen = get_current_screen();

	if ( ! $screen || $screen->post_type !== 'schedule' ) {
		return;
	}
	?>
	<script>
	jQuery(function($){

		var $wp_inline_edit = inlineEditPost.edit;

		inlineEditPost.edit = function(id){
			$wp_inline_edit.apply(this, arguments);

			var postId = 0;

			if (typeof(id) === 'object') {
				postId = parseInt(this.getId(id), 10);
			} else {
				postId = parseInt(id, 10);
			}

			if (!postId) return;

			var $postRow  = $('#post-' + postId);
			var $editRow  = $('#edit-' + postId);
			var $dataWrap = $postRow.find('.ssc-inline-data');

			if (!$dataWrap.length) return;

			var sscDate         = $.trim($dataWrap.find('.ssc_date').text());
			var sscTimeSlot     = $.trim($dataWrap.find('.ssc_time_slot').text());
			var sscField        = $.trim($dataWrap.find('.ssc_field').text());
			var sscBody         = $.trim($dataWrap.find('.ssc_body').text());
			var sscUrl          = $.trim($dataWrap.find('.ssc_url').text());
			var sscCategoryTerm = $.trim($dataWrap.find('.ssc_category_term').text());

			$editRow.find('input[name="ssc_date"]').val(sscDate);
			$editRow.find('input[name="ssc_time_slot"]').val(sscTimeSlot);
			$editRow.find('select[name="ssc_field"]').val(sscField);
			$editRow.find('input[name="ssc_body"]').val(sscBody);
			$editRow.find('input[name="ssc_url"]').val(sscUrl);
			$editRow.find('select[name="ssc_category_term"]').val(sscCategoryTerm);
		};
	});
	</script>
	<?php
} );

/**
 * クイック編集から taxonomy（区分）を保存
 * meta は既存の save-meta.php 側で保存
 */
add_action( 'save_post_schedule', function( $post_id ) {

	if ( ! isset( $_POST['ssc_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['ssc_nonce'], 'ssc_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['ssc_category_term'] ) ) {
		$term_id = absint( $_POST['ssc_category_term'] );

		if ( $term_id > 0 ) {
			wp_set_object_terms( $post_id, array( $term_id ), 'ssc_category', false );
		} else {
			wp_set_object_terms( $post_id, array(), 'ssc_category', false );
		}
	}
} );