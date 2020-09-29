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
class General extends Core\Metabox {

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 */
	public function get_tab() {
		return [ 'id' => 'general', 'heading' => esc_html__( 'General' ) ];
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
				'id'          => 'position',
				'name'        => esc_html__( 'Position', 'gg-woo-bt' ),
				'type'        => 'select',
				'options'     => [
					''                   => esc_html__( 'None', 'gg-woo-bt' ),
					'before_add_to_cart' => esc_html__( 'Before add to cart button (woocommerce_before_add_to_cart_form)', 'gg-woo-bt' ),
					'after_add_to_cart'  => esc_html__( 'After add to cart button (woocommerce_after_add_to_cart_form)', 'gg-woo-bt' ),
					'after_title'        => esc_html__( 'After product title (woocommerce_single_product_summary)', 'gg-woo-bt' ),
					'after_price'        => esc_html__( 'After price (woocommerce_single_product_summary)', 'gg-woo-bt' ),
					'after_excerpt'      => esc_html__( 'After the excerpt (woocommerce_single_product_summary)', 'gg-woo-bt' ),
					'after_meta'         => esc_html__( 'After the meta (woocommerce_single_product_summary)', 'gg-woo-bt' ),
					'before_tabs' 		 => esc_html__( 'Before Tabs (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
					'after_tabs' 		 => esc_html__( 'After Tabs (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
					'before_upsell' 	 => esc_html__( 'Before Upsell (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
					'after_upsell' 		 => esc_html__( 'After Upsell (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
					'before_related' 	 => esc_html__( 'Before Related (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
					'after_related' 	 => esc_html__( 'After Related (woocommerce_after_single_product_summary)', 'gg-woo-bt' ),
				],
				'default'     => 'before_add_to_cart',
				'description' => esc_html__( 'Choose the position to show the frequently bought products.', 'gg-woo-bt' ),
			],
			[
				'name'        => esc_html__( 'Show main product', 'gg-woo-bt' ),
				'description' => esc_html__( 'Main product will be showed in the first of the list. ', 'gg-woo-bt' ),
				'id'          => 'show_main_product',
				'type'        => 'switch',
				'default'     => 'off',
			],
			[
				'name'        => esc_html__( 'Show thumbnail', 'gg-woo-bt' ),
				'description' => esc_html__( 'Show product thumnail in the list.', 'gg-woo-bt' ),
				'id'          => 'show_thumbnail',
				'type'        => 'switch',
				'default'     => 'on',
			],
			[
				'name'        => esc_html__( 'Show price', 'gg-woo-bt' ),
				'description' => esc_html__( 'Show price in the list.', 'gg-woo-bt' ),
				'id'          => 'show_price',
				'type'        => 'switch',
				'default'     => 'on',
			],
			[
				'name'        => esc_html__( 'Show variation select', 'gg-woo-bt' ),
				'description' => esc_html__( 'Show variation select when page loaded.', 'gg-woo-bt' ),
				'id'          => 'show_variation_select',
				'type'        => 'switch',
				'default'     => 'on',
			],
			[
				'id'          => 'view_detail',
				'name'        => esc_html__( 'View detail products', 'gg-woo-bt' ),
				'type'        => 'select',
				'options'     => [
					''         => esc_html__( 'No', 'gg-woo-bt' ),
					'same_tab' => esc_html__( 'Open in the same tab', 'gg-woo-bt' ),
					'new_tab'  => esc_html__( 'Open in the new tab', 'gg-woo-bt' ),
				],
				'default'     => 'new_tab',
				'description' => esc_html__( 'Choose the position to show the frequently bought products.', 'gg-woo-bt' ),
			],
			[
				'name'        => esc_html__( 'Re-calculate Main Price', 'gg-woo-bt' ),
				'description' => esc_html__( 'Re-calculate main product price when add items.', 'gg-woo-bt' ),
				'id'          => 'recal_price',
				'type'        => 'switch',
				'default'     => 'on',
			],
			[
				'name'        => esc_html__( 'Main Price Selector', 'gg-woo-bt' ),
				'description' => esc_html__( 'Main Price Selector', 'gg-woo-bt' ),
				'id'          => 'main_price_selector',
				'type'        => 'text',
				'default'     => '.summary > .price',
			],
			[
				'name'        => esc_html__( 'Change quantity in Cart page.', 'gg-woo-bt' ),
				'description' => esc_html__( 'Change quantity in Cart page.', 'gg-woo-bt' ),
				'id'          => 'cart_change_quantity',
				'type'        => 'switch',
				'default'     => 'on',
			],
			[
				'name' => esc_html__( 'Before text', 'gg-woo-bt' ),
				'id'   => 'before_text',
				'type' => 'textarea',
			],
			[
				'name' => esc_html__( 'After text', 'gg-woo-bt' ),
				'id'   => 'after_text',
				'type' => 'textarea',
			],
		];

		return apply_filters( 'gg_woo_bt_settings_general', $fields );
	}
}
