<?php

use GG_Woo_BT\Common\Model\Search;

/**
 * Get search results via AJAX.
 */
function gg_woo_bt_get_search_results() {
	$query = Search::search_products( $_POST );

	echo '<ul class="gg-woo-bt-search-results">';

	if ( $query->have_posts() ) {

		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );

			if ( ! $product ) {
				continue;
			}

			gg_woo_bt_get_product_search_results_template( $product );
		}

		wp_reset_postdata();
	} else {
		echo '<span>' . sprintf( esc_html__( 'No products found for "%s". Try again!', 'gg-woo-bt' ), sanitize_text_field( $_POST['keyword'] ) ) . '</span>';
	}

	echo '</ul>';

	wp_die();
}

add_action( 'wp_ajax_gg_woo_bt_get_search_results', 'gg_woo_bt_get_search_results' );

/**
 * Add result product meta via AJAX.
 */
function gg_woo_bt_add_result_product_meta() {
	$id      = absint( $_POST['id'] );
	$product = wc_get_product( $id );

	if ( $product ) {
		gg_woo_bt_get_product_search_template( $product, '100%', 1 );
	}

	wp_die();
}

add_action( 'wp_ajax_gg_woo_bt_add_result_product_meta', 'gg_woo_bt_add_result_product_meta' );

/**
 * Get template mapping via AJAX.
 *
 * @return void
 */
function gg_woo_bt_add_new_filter_condition() {
	try {
		ob_start();
		?>
		<tr>
			<td>
				<i class="gg_woo_bt-sort dashicons dashicons-move"></i>
			</td>
            <td>
				<?php
				$categories = get_terms( [
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				] );
				?>
                <select name="mapping_category[]" required>
					<?php if ( $categories ) : ?>
						<?php foreach ( $categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category->slug ); ?>"><?php echo esc_html( $category->name ); ?>
                                (<?php echo absint( $category->count ); ?>)
                            </option>
						<?php endforeach; ?>
					<?php endif; ?>
                </select>
            </td>
            <td>
                <select name="" class="mapping_to_select attr_type gg_woo_bt-not-empty" multiple="multiple">
					<?php foreach ( $categories as $category_1 ) : ?>
                        <option value="<?php echo esc_attr( $category_1->slug ); ?>"><?php echo esc_html( $category_1->name ); ?>
                            (<?php echo absint( $category_1->count ); ?>)
                        </option>
					<?php endforeach; ?>
                </select>
                <input type="hidden" class="mapping_to_input" name="mapping_to[]" value="">
            </td>
			<td>
				<i class="gg_woo_bt-del-condition dashicons dashicons-no-alt"></i>
			</td>
		</tr>
		<?php

		wp_send_json_success( [
			'row' => ob_get_clean(),
		], 200 );
		wp_die();
	} catch ( \Exception $e ) {
		wp_send_json_error( [
			'message' => $e->getMessage(),
		], 400 );
	}
}

add_action( 'wp_ajax_gg_woo_bt_add_new_filter_condition', 'gg_woo_bt_add_new_filter_condition' );
