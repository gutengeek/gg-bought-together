<?php
namespace GG_Woo_BT\Frontend\Controller;

use GG_Woo_BT\Core\Controller;

class Order extends Controller {

	/**
	 * Process Save Data Post Profile
	 *
	 *    Display Sidebar on left side and next is main content
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function register_ajax_hook_callbacks() {

	}

	/**
	 * Process Save Data Post Profile
	 *
	 *    Display Sidebar on left side and next is main content
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function register_hook_callbacks() {
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'add_order_item_meta', ], 10, 3 );
		add_filter( 'woocommerce_order_item_name', [ $this, 'cart_item_name' ], 10, 2 );

		add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hidden_order_item_meta', ], 10, 1 );
		add_action( 'woocommerce_before_order_itemmeta', [ $this, 'before_order_item_meta', ], 10, 1 );
	}

	public function add_order_item_meta( $item, $cart_item_key, $values ) {
		if ( isset( $values['gg_woo_bt_parent_id'] ) ) {
			$item->update_meta_data( '_gg_woo_bt_parent_id', $values['gg_woo_bt_parent_id'] );
		}

		if ( isset( $values['gg_woo_bt_ids'] ) ) {
			$item->update_meta_data( '_gg_woo_bt_ids', $values['gg_woo_bt_ids'] );
		}
	}

	public function cart_item_name( $item_name, $item ) {
		if ( isset( $item['gg_woo_bt_parent_id'] ) && ! empty( $item['gg_woo_bt_parent_id'] ) ) {
			$associated_text = get_option( '_gg_woo_bt_associated_text', '' );

			if ( empty( $associated_text ) ) {
				$associated_text = esc_html__( '(bought together %s)', 'gg-woo-bt' );
			}

			if ( strpos( $item_name, '</a>' ) !== false ) {
				$name = sprintf( $associated_text, '<a href="' . get_permalink( $item['gg_woo_bt_parent_id'] ) . '">' . get_the_title( $item['gg_woo_bt_parent_id'] ) . '</a>' );
			} else {
				$name = sprintf( $associated_text, get_the_title( $item['gg_woo_bt_parent_id'] ) );
			}

			$item_name .= ' <span class="gg_woo_bt-item-name">' . apply_filters( 'gg_woo_bt_item_name', $name, $item ) . '</span>';
		}

		return $item_name;
	}

	public function hidden_order_item_meta( $hidden ) {
		return array_merge( $hidden, [
			'_gg_woo_bt_parent_id',
			'_gg_woo_bt_ids',
			'gg_woo_bt_parent_id',
			'gg_woo_bt_ids',
		] );
	}

	public function before_order_item_meta( $item_id ) {
		if ( $parent_id = wc_get_order_item_meta( $item_id, '_gg_woo_bt_parent_id', true ) ) {
			$associated_text = get_option( '_gg_woo_bt_associated_text', '' );

			if ( empty( $associated_text ) ) {
				$associated_text = esc_html__( '(bought together %s)', 'gg-woo-bt' );
			}

			echo sprintf( $associated_text, get_the_title( $parent_id ) );
		}
	}
}
