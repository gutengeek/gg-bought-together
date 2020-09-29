<?php
namespace GG_Woo_BT\Frontend\Controller;

use GG_Woo_BT\Core\Controller;

class Product extends Controller {

	/**
	 * Register_ajax_hook_callbacks
	 */
	public function register_ajax_hook_callbacks() {

	}

	/**
	 * Register_hook_callbacks.
	 */
	public function register_hook_callbacks() {
		$this->template_position_hook();
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_to_cart_button' ] );
	}

	/**
	 * Hook to a position.
	 */
	public function template_position_hook() {
		switch ( gg_woo_bt_get_option( 'position', 'before_add_to_cart' ) ) {
			case 'before_add_to_cart':
				add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'add_to_cart_form' ] );
				break;
			case 'after_add_to_cart':
				add_action( 'woocommerce_after_add_to_cart_form', [ $this, 'add_to_cart_form' ] );
				break;
			case 'after_title':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 6 );
				break;
			case 'after_price':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 11 );
				break;
			case 'after_excerpt':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 21 );
				break;
			case 'after_meta':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 41 );
				break;
			case 'before_tabs':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 9 );
				break;
			case 'after_tabs':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 11 );
				break;
			case 'before_upsell':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 14 );
				break;
			case 'after_upsell':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 16 );
				break;
			case 'before_related':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 19 );
				break;
			case 'after_related':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 21 );
				break;
		}
	}

	/**
	 * Add to cart form.
	 */
	public function add_to_cart_form() {
		global $product;

		if ( $product->is_type( 'grouped' ) ) {
			return;
		}

		wp_enqueue_script( 'wc-add-to-cart-variation' );
		$this->show_items();
	}

	/**
	 * Render items.
	 */
	public function show_items() {
		return gg_woo_bt_render_template( 'list' );
	}

	/**
	 * Add hidden input ids.
	 */
	public function add_to_cart_button() {
		global $product;

		if ( $product->is_type( 'grouped' ) ) {
			return;
		}

		$gg_woo_bt_ids = get_post_meta( $product->get_id(), 'gg_woo_bt_ids', true );

		echo '<input name="gg_woo_bt_ids" class="gg_woo_bt_ids gg_woo_bt-ids" data-id="' . esc_attr( $product->get_id() ) . '" type="hidden" value="' . esc_attr( $gg_woo_bt_ids ) . '"/>';
	}
}
