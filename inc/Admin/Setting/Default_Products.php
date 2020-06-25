<?php
namespace GG_Woo_BT\Admin\Setting;

use GG_Woo_BT\Core as Core;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
class Default_Products extends Core\Metabox {

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 */
	public function get_tab() {
		return [ 'id' => 'default_products', 'heading' => esc_html__( 'Default Products' ) ];
	}

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 *
	 * @since    1.0.0
	 */
	public function get_settings() {
		$fields = [
			[
				'id'          => 'default_products',
				'name'        => esc_html__( 'Default Products', 'gg-woo-bt' ),
				'type'        => 'select',
				'options'     => [
					''                => esc_html__( 'None', 'gg-woo-bt' ),
					'related'         => esc_html__( 'Related', 'gg-woo-bt' ),
					'upsells'         => esc_html__( 'Upsells', 'gg-woo-bt' ),
					'related_upsells' => esc_html__( 'Related & Upsells', 'gg-woo-bt' ),
					'rules'           => esc_html__( 'Set below rules', 'gg-woo-bt' ),
				],
				'default'     => '',
				'description' => esc_html__( 'Automatically add frequently bought together products when not adding specified products for each main product.', 'gg-woo-bt' ),
			],
			[
				'name'        => esc_html__( 'Bad keywords', 'gg-woo-bt' ),
				'description' => esc_html__( 'A comma-separated list of bad keywords will be removed. List items will be query by remain keywords in product name.', 'gg-woo-bt' ),
				'id'          => 'bad_keywords',
				'type'        => 'textarea',
			],
			[
				'name'        => esc_html__( 'Allowed categories', 'gg-woo-bt' ),
				'description' => esc_html__( 'Select allowed categories.', 'gg-woo-bt' ),
				'id'          => 'allowed_categories',
				'type'        => 'taxonomy_select',
				'taxonomy'    => 'product_cat',
				'multiple'    => true,
			],
			[
				'id'          => 'product_limit',
				'name'        => esc_html__( 'Number of default products', 'gg-woo-bt' ),
				'type'        => 'text_number',
				'default'     => '5',
				'description' => esc_html__( 'Maximum number of products to be displayed.', 'gg-woo-bt' ),
			],
			[
				'id'          => 'order_by',
				'name'        => esc_html__( 'Sort by', 'gg-woo-bt' ),
				'type'        => 'select',
				'options'     => [
					'ID'         => esc_html__( 'ID', 'gg-woo-bt' ),
					'post_title' => esc_html__( 'Product Name', 'gg-woo-bt' ),
				],
				'default'     => 'ID',
				'description' => esc_html__( 'Sort order by one of options.', 'gg-woo-bt' ),
			],
			[
				'id'          => 'order',
				'name'        => esc_html__( 'Sort order', 'gg-woo-bt' ),
				'type'        => 'select',
				'options'     => [
					'DESC' => esc_html__( 'Descending', 'gg-woo-bt' ),
					'ASC'  => esc_html__( 'Ascending', 'gg-woo-bt' ),
				],
				'default'     => 'DESC',
				'description' => esc_html__( 'Sort result sets in either Ascending or Descending order', 'gg-woo-bt' ),
			],
			[
				'id'      => 'mapping',
				'name'    => esc_html__( 'Mapping', 'gg-woo-bt' ),
				'type'    => 'html',
				'content' => $this->get_mapping_html(),
			],
		];

		return apply_filters( 'gg_woo_bt_settings_search', $fields );
	}

	public function get_mapping_html() {
		$options = get_option( 'gg_woo_bt_mapping', [] );
		// var_dump($options);
		$mapping_category = isset( $options['mapping_category'] ) && $options['mapping_category'] ? $options['mapping_category'] : [];
		$mapping_to       = isset( $options['mapping_to'] ) && $options['mapping_to'] ? $options['mapping_to'] : [];
		ob_start();
		?>
        <h3>Mapping categories</h3>
        <table class="table tree widefat fixed gg_woo_bt-table-filter" style="width: 100%;" id="gg_woo_bt-table-filter">
            <thead>
            <tr>
                <th></th>
                <th><?php esc_html_e( 'Category', 'gg-woo-bt' ); ?></th>
                <th><?php esc_html_e( 'Map to categories', 'gg-woo-bt' ); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
			<?php
			$has_condition = $mapping_category && $mapping_to;
			if ( $has_condition ) :
				foreach ( $mapping_category as $key => $mapping_category_value ) :
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
                                        <option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $mapping_category_value,
											$category->slug ); ?>><?php echo esc_html( $category->name ); ?>
                                            (<?php echo absint( $category->count ); ?>)
                                        </option>
									<?php endforeach; ?>
								<?php endif; ?>
                            </select>
                        </td>
                        <td>
                            <select name="" class="mapping_to_select attr_type gg_woo_bt-not-empty" multiple="multiple">
                                <option></option>
								<?php foreach ( $categories as $category_1 ) : ?>
                                    <option value="<?php echo esc_attr( $category_1->slug ); ?>" <?php selected( true,
										in_array( $category_1->slug, explode( '|', $mapping_to[ $key ] ) ) ); ?>><?php echo esc_html( $category_1->name ); ?>
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
				endforeach;
			endif; ?>
            </tbody>
            <tfoot>
            <tr class="gg_woo_bt-no-conditions" <?php echo ! $has_condition ? 'style="display: none;"' : ''; ?>>
                <td colspan="5">
                    <p><?php esc_html_e( 'No conditions', 'gg-woo-bt' ); ?></p>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <button type="button" class="gg_woo_bt-btn gg_woo_bt-btn-primary" id="gg_woo_bt-add-new-condition">
                        <span class="dashicons dashicons-plus"></span><?php esc_html_e( 'Add New Mapping', 'gg-woo-bt' ); ?>
                    </button>
                </td>
            </tr>
            </tfoot>
        </table>
		<?php

		return ob_get_clean();
	}
}
