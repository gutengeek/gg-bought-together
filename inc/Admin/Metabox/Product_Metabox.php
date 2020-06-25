<?php
namespace GG_Woo_BT\Admin\Metabox;

use GG_Woo_BT\Common\Dropdown;
use GG_Woo_BT\Core\Constant;

class Product_Metabox {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	protected $prefix = Constant::PRODUCT_META_PREFIX;

	/**
	 * Product_Metabox constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'add_product_tab' ], 99, 1 );
		add_action( 'woocommerce_product_data_panels', [ $this, 'add_product_data_panel' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_data' ] );
	}

	/**
	 * Add product tab.
	 *
	 * @param $tabs
	 * @return array
	 */
	public function add_product_tab( $tabs ) {
		$tabs['gg_woo_bt'] = [
			'label'    => __( 'Frequently Bought Together', 'gg-woo-bt' ),
			'target'   => 'gg_woo_bt_product_data',
			'priority' => 90,
		];

		return $tabs;
	}

	/**
	 * Add product data panel.
	 *
	 * @return string
	 */
	public function add_product_data_panel() {
		global $post;

		$prefix  = $this->prefix;
		$post_id = $post->ID;
		?>
        <div id="gg_woo_bt_product_data" class="panel woocommerce_options_panel gg_woo_bt_table">
            <table>
                <tr>
                    <th><?php esc_html_e( 'Associated products', 'gg-woo-bt' ); ?></th>
                    <td>
                        <div class="gg-woo-bt-main-input">
                            <span class="loading" id="gg_woo_bt_loading" style="display: none;"><?php esc_html_e( 'Loading...', 'gg-woo-bt' ); ?></span>
                            <input type="text" class="short" style="" name="gg_woo_bt_search" id="gg_woo_bt_search" value="" placeholder="<?php esc_attr_e( 'Type keyword...', 'gg-woo-bt' ); ?>">
                            <div id="gg_woo_bt_results" class="gg_woo_bt_results" style="display: none"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <div class="gg-woo-bt-main-input">
                            <input type="hidden" id="gg_woo_bt_id" name="gg_woo_bt_id" value="<?php echo esc_attr( $post_id ); ?>"/>
                            <input type="hidden" id="gg_woo_bt_ids" name="gg_woo_bt_ids" value="<?php echo get_post_meta( $post_id, 'gg_woo_bt_ids', true ); ?>"/>
                            <div id="gg_woo_bt_selected" class="gg_woo_bt_selected">
                                <ul>
									<?php
									if ( $ids = get_post_meta( $post_id, 'gg_woo_bt_ids', true ) ) {
										if ( $items = gg_woo_bt_get_items( $ids ) ) {
											foreach ( $items as $item ) {
												$item_id      = $item['id'];
												$item_price   = $item['price'];
												$item_qty     = $item['qty'];
												$item_product = wc_get_product( $item_id );

												if ( ! $item_product ) {
													continue;
												}

												gg_woo_bt_get_product_search_template( $item_product, $item_price, $item_qty );
											}
										}
									}
									?>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Add separately', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_separately" name="gg_woo_bt_separately"
                               type="checkbox" <?php echo( get_post_meta( $post_id, 'gg_woo_bt_separately', true ) === 'on' ? 'checked' : '' ); ?>/>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php esc_attr_e( 'If enabled, the associated products will be added as separate items and stay unaffected from the main product, their prices will change back to the original.',
							      'gg-woo-bt' ); ?>"></span>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Discount', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_discount" name="gg_woo_bt_discount"
                               type="number" min="0" max="100" step="0.0001" style="width: 50px"
                               value="<?php echo get_post_meta( $post_id, 'gg_woo_bt_discount', true ); ?>"/>%
                        <span class="woocommerce-help-tip"
                              data-tip="<?php esc_attr_e( 'Discount for the main product when buying at least one product in this list.', 'gg-woo-bt' ); ?>"></span>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Checked all', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_checked_all" name="gg_woo_bt_checked_all"
                               type="checkbox" <?php echo( get_post_meta( $post_id, 'gg_woo_bt_checked_all', true ) === 'on' ? 'checked' : '' ); ?>/>
                        <label for="gg_woo_bt_checked_all"><?php esc_html_e( 'Checked all by default.', 'gg-woo-bt' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Custom quantity', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_custom_qty" name="gg_woo_bt_custom_qty"
                               type="checkbox" <?php echo( get_post_meta( $post_id, 'gg_woo_bt_custom_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                        <label for="gg_woo_bt_custom_qty"><?php esc_html_e( 'Allow the customer can change the quantity of each product.', 'gg-woo-bt' ); ?></label>
                    </td>
                </tr>
                <tr class="gg_woo_bt_tr_space gg_woo_bt_tr_hide_if_custom_qty">
                    <th><?php esc_html_e( 'Sync quantity', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_sync_qty" name="gg_woo_bt_sync_qty"
                               type="checkbox" <?php echo( get_post_meta( $post_id, 'gg_woo_bt_sync_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                        <label for="gg_woo_bt_sync_qty"><?php esc_html_e( 'Sync the quantity of the main product with associated products.', 'gg-woo-bt' ); ?></label>
                    </td>
                </tr>
                <tr class="gg_woo_bt_tr_space gg_woo_bt_tr_show_if_custom_qty">
                    <th><?php esc_html_e( 'Limit each item', 'gg-woo-bt' ); ?></th>
                    <td>
                        <input id="gg_woo_bt_limit_each_min_default" name="gg_woo_bt_limit_each_min_default"
                               type="checkbox" <?php echo( get_post_meta( $post_id, 'gg_woo_bt_limit_each_min_default', true ) === 'on' ? 'checked' : '' ); ?>/>
                        <label for="gg_woo_bt_limit_each_min_default"><?php esc_html_e( 'Use default quantity as min', 'gg-woo-bt' ); ?></label>
                        <u>or set a range</u>
                        Min <input name="gg_woo_bt_limit_each_min" type="number" min="0"
                                   value="<?php echo( get_post_meta( $post_id, 'gg_woo_bt_limit_each_min', true ) ?: '' ); ?>"
                                   style="width: 60px; float: none"/>
                        Max <input
                                name="gg_woo_bt_limit_each_max"
                                type="number" min="1"
                                value="<?php echo( get_post_meta( $post_id, 'gg_woo_bt_limit_each_max', true ) ?: '' ); ?>"
                                style="width: 60px; float: none"/>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Before text', 'gg-woo-bt' ); ?></th>
                    <td>
                        <div class="gg-woo-bt-main-input">
                            <textarea name="gg_woo_bt_before_text" rows="1" style="width: 100%"><?php echo stripslashes( get_post_meta( $post_id, 'gg_woo_bt_before_text', true ) ); ?></textarea>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'After text', 'gg-woo-bt' ); ?></th>
                    <td>
                        <div class="gg-woo-bt-main-input">
                            <textarea name="gg_woo_bt_after_text" rows="1" style="width: 100%"><?php echo stripslashes( get_post_meta( $post_id, 'gg_woo_bt_after_text', true ) ); ?></textarea>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
		<?php
	}

	/**
	 * Save product meta data.
	 *
	 * @param int $post_id
	 */
	public function save_product_data( $post_id ) {
		$prefix = $this->prefix;

		$fields = [
			'id'                     => '',
			'ids'                    => '',
			'separately'             => '',
			'discount'               => '',
			'checked_all'            => '',
			'custom_qty'             => '',
			'sync_qty'               => '',
			'limit_each_min_default' => '',
			'limit_each_min'         => '',
			'limit_each_max'         => '',
			'before_text'            => '',
			'after_text'             => '',
		];

		$fields = apply_filters( 'gg_woo_bt_product_meta_fields_data', $fields );
		foreach ( $fields as $key => $type ) {
			$key   = $prefix . $key;
			$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
			switch ( $type ) {
				case 'int' :
					$value = absint( $value );
					break;
				default :
					$value = sanitize_text_field( $value );
			}
			update_post_meta( $post_id, $key, $value );
		}

		do_action( 'gg_woo_bt_product_meta_save_data', $post_id );
	}
}
