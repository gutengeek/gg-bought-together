<?php
namespace GG_Woo_BT\Frontend\Controller;

use GG_Woo_BT\Core\Controller;

class Cart extends Controller {

	/**
	 * Register_ajax_hook_callbacks
	 */
	public function register_ajax_hook_callbacks() {
		// .
	}

	/**
	 * Register_hook_callbacks
	 */
	public function register_hook_callbacks() {
		add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', [ $this, 'individually_found_in_cart' ], 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 10, 2 );
		add_action( 'woocommerce_add_to_cart', [ $this, 'add_to_cart' ], 10, 6 );
		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 10, 2 );

		add_filter( 'woocommerce_get_cart_contents', [ $this, 'get_cart_contents' ], 10, 1 );
		add_filter( 'woocommerce_cart_item_name', [ $this, 'cart_item_name' ], 10, 2 );
		add_filter( 'woocommerce_cart_item_price', [ $this, 'cart_item_price' ], 10, 2 );

		if ( ! $this->is_enable_change_cart_quantity() ) {
			add_filter( 'woocommerce_cart_item_quantity', [ $this, 'cart_item_quantity' ], 10, 3 );
			add_action( 'woocommerce_after_cart_item_quantity_update', [ $this, 'update_cart_item_quantity' ], 10, 2 );
		}

		add_action( 'woocommerce_before_cart_item_quantity_zero', [ $this, 'cart_item_removed' ], 10, 2 );
		add_action( 'woocommerce_cart_item_removed', [ $this, 'cart_item_removed' ], 10, 2 );
	}

	/**
	 * Individually found in cart.
	 *
	 * Hook add_to_cart_sold_individually_found_in_cart
	 *
	 * @param $found_in_cart
	 * @param $product_id
	 * @return bool
	 */
	public function individually_found_in_cart( $found_in_cart, $product_id ) {
		if ( $this->check_in_cart( $product_id ) ) {
			return true;
		}

		return $found_in_cart;
	}

	/**
	 * Hook add_to_cart_validation.
	 *
	 * @param $passed
	 * @param $product_id
	 * @return bool
	 */
	public function add_to_cart_validation( $passed, $product_id ) {
		if ( ( get_post_meta( $product_id, 'gg_woo_bt_separately', true ) !== 'on' ) && get_post_meta( $product_id, 'gg_woo_bt_ids', true ) ) {
			if ( isset( $_POST['gg_woo_bt_ids'] ) && ! empty( $_POST['gg_woo_bt_ids'] ) ) {
				if ( $items = gg_woo_bt_get_items( $_POST['gg_woo_bt_ids'] ) ) {
					foreach ( $items as $item ) {
						$item_product = wc_get_product( $item['id'] );

						if ( ! $item_product ) {
							wc_add_notice( esc_html__( 'Invalid product.', 'gg-woo-bt' ), 'error' );

							return false;
						}

						if ( $item_product->is_type( 'variable' ) ) {
							wc_add_notice( sprintf( esc_html__( 'Un-purchasable. Please choose variation options for "%s".', 'gg-woo-bt' ), esc_html( $item_product->get_name() ) ), 'error' );

							return false;
						}

						if ( $item_product->is_sold_individually() && $this->check_in_cart( $item['id'] ) ) {
							wc_add_notice( sprintf( esc_html__( 'You can not add another "%s" to the cart. It is individually.', 'gg-woo-bt' ), esc_html( $item_product->get_name() ) ), 'error' );

							return false;
						}

						if ( 'on' === get_post_meta( $product_id, 'gg_woo_bt_custom_qty', true ) ) {
							if ( ( $limit_min = get_post_meta( $product_id, 'gg_woo_bt_limit_each_min', true ) ) && ( $item['qty'] < (float) $limit_min ) ) {
								wc_add_notice( sprintf( esc_html__( '"%1$s" does not reach the minimum quantity (%2$s).', 'gg-woo-bt' ), esc_html( $item_product->get_name() ), $limit_min ), 'error' );

								return false;
							}

							if ( ( $limit_max = get_post_meta( $product_id, 'gg_woo_bt_limit_each_max', true ) ) && ( $item['qty'] > (float) $limit_max ) ) {
								wc_add_notice( sprintf( esc_html__( '"%1$s" passes the maximum quantity (%2$s).', 'gg-woo-bt' ), esc_html( $item_product->get_name() ), $limit_max ), 'error' );

								return false;
							}
						}
					}
				}
			}
		}

		return $passed;
	}

	/**
	 * Hook add_to_cart.
	 *
	 * @param $cart_item_key
	 * @param $product_id
	 * @param $quantity
	 * @param $variation_id
	 * @param $variation
	 * @param $cart_item_data
	 * @throws \Exception
	 */
	public function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( isset( $cart_item_data['gg_woo_bt_ids'] ) && ! empty( $cart_item_data['gg_woo_bt_ids'] ) ) {
			if ( $items = gg_woo_bt_get_items( $cart_item_data['gg_woo_bt_ids'] ) ) {
				// Add child products
				foreach ( $items as $item ) {
					$item_id           = $item['id'];
					$item_price        = $item['price'];
					$item_qty          = $item['qty'];
					$item_variation_id = 0;
					$item_variation    = [];
					$item_product      = wc_get_product( $item_id );

					if ( $item_product instanceof \WC_Product_Variation ) {
						// Prevent add a variable product.
						$item_variation_id = $item_id;
						$item_id           = $item_product->get_parent_id();
						$item_variation    = $item_product->get_variation_attributes();
					}

					if ( $item_product && $item_product->is_in_stock() && $item_product->is_purchasable() ) {
						$item_new_price = gg_woo_bt_get_new_price( $item_product->get_price(), $item_price );

						// Add to cart
						if ( get_post_meta( $product_id, 'gg_woo_bt_separately', true ) !== 'on' ) {
							$item_key = WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation, [
								'gg_woo_bt_parent_id'  => $product_id,
								'gg_woo_bt_parent_key' => $cart_item_key,
								'gg_woo_bt_qty'        => $item_qty / $quantity,
								'gg_woo_bt_price'      => $item_new_price,
							] );

							if ( $item_key ) {
								WC()->cart->cart_contents[ $cart_item_key ]['gg_woo_bt_keys'][] = $item_key;
							}
						} else {
							WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation );
						}
					}
				}
			}
		}
	}

	/**
	 * Hook add_cart_item_data.
	 *
	 * @param $cart_item_data
	 * @param $product_id
	 * @return mixed
	 */
	public function add_cart_item_data( $cart_item_data, $product_id ) {
		if ( isset( $_POST['gg_woo_bt_ids'] ) && ( get_post_meta( $product_id, 'gg_woo_bt_ids', true ) || ( gg_woo_bt_get_option( 'default_products', '' ) !== '' ) ) ) {
			$ids = gg_woo_bt_sanitize_ids( $_POST['gg_woo_bt_ids'] );

			if ( ! empty( $ids ) ) {
				$valid_ids = [];
				$items     = gg_woo_bt_get_items( $ids );
				$data      = gg_woo_bt_get_allowed_products( $product_id );

				foreach ( $data as $data_key => $data_value ) {
					if ( ! is_array( $data_value ) ) {
						$data_price   = '100%';
						$item_product = wc_get_product( absint( $data_value ) );
						$discount     = gg_woo_bt_get_product_term_meta( $item_product, 'discount' );
						if ( $discount ) {
							$all_percent        = '100';
							$category_new_price = (float) $all_percent - (float) $discount;
							if ( $category_new_price >= '0' ) {
								$data_price = $category_new_price . '%';
							}
						}

						$data[ $data_key ] = [
							'id'    => absint( $data_value ),
							'price' => $data_price,
							'qty'   => 1,
						];
					}
				}

				$results = [];
				foreach ( $data as $data_array ) {
					$product = wc_get_product( $data_array['id'] );
					if ( $product ) {
						$results[] = $data_array;
						if ( $product->is_type( 'variable' ) && $product->has_child() ) {
							$children_ids = $product->get_visible_children();
							if ( is_array( $children_ids ) && ( count( $children_ids ) > 0 ) ) {
								foreach ( $children_ids as $child_id ) {
									$results[] = [
										'id'    => $child_id,
										'price' => $data_array['price'],
										'qty'   => $data_array['qty'],
									];
								}
							}
						}
					}
				}

				foreach ( $items as $item ) {
					$key = array_search( $item['id'], array_column( $results, 'id' ) );
					if ( false !== $key ) {
						$item['price'] = $results[ $key ]['price'];
						$valid_ids[]   = $item['id'] . '/' . $item['price'] . '/' . $item['qty'];
					}
				}

				if ( $valid_ids ) {
					$cart_item_data['gg_woo_bt_ids'] = implode( ',', $valid_ids );
				}
			}

			unset( $_POST['gg_woo_bt_ids'] );
		}

		return $cart_item_data;
	}

	/**
	 * Hook get_cart_item_from_session.
	 *
	 * @param $cart_item
	 * @param $item_session_values
	 * @return mixed
	 */
	public function get_cart_item_from_session( $cart_item, $item_session_values ) {
		if ( isset( $item_session_values['gg_woo_bt_ids'] ) && ! empty( $item_session_values['gg_woo_bt_ids'] ) ) {
			$cart_item['gg_woo_bt_ids'] = $item_session_values['gg_woo_bt_ids'];
		}

		if ( isset( $item_session_values['gg_woo_bt_parent_id'] ) ) {
			$cart_item['gg_woo_bt_parent_id']  = $item_session_values['gg_woo_bt_parent_id'];
			$cart_item['gg_woo_bt_parent_key'] = $item_session_values['gg_woo_bt_parent_key'];
			$cart_item['gg_woo_bt_price']      = $item_session_values['gg_woo_bt_price'];
			$cart_item['gg_woo_bt_qty']        = $item_session_values['gg_woo_bt_qty'];
		}

		return $cart_item;
	}

	/**
	 * Hook get_cart_contents.
	 *
	 * @param $cart_contents
	 * @return mixed
	 */
	public function get_cart_contents( $cart_contents ) {
		foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['gg_woo_bt_parent_id'], $cart_item['gg_woo_bt_price'] ) ) {
				$cart_item['data']->set_price( $cart_item['gg_woo_bt_price'] );
			}

			if ( ! empty( $cart_item['gg_woo_bt_ids'] ) && ( $discount = get_post_meta( $cart_item['product_id'], 'gg_woo_bt_discount', true ) ) && ( get_post_meta( $cart_item['product_id'],
						'gg_woo_bt_separately', true ) !== 'on' ) ) {
				if ( $cart_item['variation_id'] > 0 ) {
					$item_product = wc_get_product( $cart_item['variation_id'] );
				} else {
					$item_product = wc_get_product( $cart_item['product_id'] );
				}

				$ori_price = $item_product->get_price();

				// check if has linked products
				$has_linked = false;

				if ( isset( $cart_item['gg_woo_bt_keys'] ) ) {
					foreach ( $cart_item['gg_woo_bt_keys'] as $key ) {
						if ( isset( $cart_contents[ $key ] ) ) {
							$has_linked = true;
							break;
						}
					}
				}

				if ( $has_linked ) {
					$discount_price = $ori_price * ( 100 - (float) $discount ) / 100;
					$cart_item['data']->set_price( $discount_price );
				} else {
					$cart_item['data']->set_price( $ori_price );
				}
			}
		}

		return $cart_contents;
	}

	/**
	 * Hook cart_item_name.
	 *
	 * @param $item_name
	 * @param $item
	 * @return string
	 */
	public function cart_item_name( $item_name, $item ) {
		if ( isset( $item['gg_woo_bt_parent_id'] ) && ! empty( $item['gg_woo_bt_parent_id'] ) ) {

			$associated_text = esc_html__( '(bought together %s)', 'gg-woo-bt' );

			if ( strpos( $item_name, '</a>' ) !== false ) {
				$name = sprintf( $associated_text, '<a href="' . get_permalink( $item['gg_woo_bt_parent_id'] ) . '">' . get_the_title( $item['gg_woo_bt_parent_id'] ) . '</a>' );
			} else {
				$name = sprintf( $associated_text, get_the_title( $item['gg_woo_bt_parent_id'] ) );
			}

			$item_name .= ' <span class="gg_woo_bt-item-name">' . apply_filters( 'gg_woo_bt_item_name', $name, $item ) . '</span>';
		}

		return $item_name;
	}

	/**
	 * Hook cart_item_price.
	 *
	 * @param $price
	 * @param $cart_item
	 * @return string
	 */
	public function cart_item_price( $price, $cart_item ) {
		if ( isset( $cart_item['gg_woo_bt_parent_id'], $cart_item['gg_woo_bt_price'] ) ) {
			return wc_price( wc_get_price_to_display( $cart_item['data'], [ 'price' => $cart_item['gg_woo_bt_price'] ] ) );
		}

		return $price;
	}

	/**
	 * Check in cart.
	 *
	 * @param $product_id
	 * @return bool
	 */
	public function check_in_cart( $product_id ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Hook cart_item_quantity.
	 *
	 * @param $quantity
	 * @param $cart_item_key
	 * @param $cart_item
	 * @return mixed
	 */
	public function cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
		if ( isset( $cart_item['gg_woo_bt_parent_id'] ) ) {
			return $cart_item['quantity'];
		}

		return $quantity;
	}

	/**
	 * Hook update_cart_item_quantity.
	 *
	 * @param     $cart_item_key
	 * @param int $quantity
	 */
	public function update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
		if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['gg_woo_bt_keys'] ) ) {
			foreach ( WC()->cart->cart_contents[ $cart_item_key ]['gg_woo_bt_keys'] as $key ) {
				if ( isset( WC()->cart->cart_contents[ $key ] ) ) {
					if ( $quantity <= 0 ) {
						$qty = 0;
					} else {
						$qty = $quantity * ( WC()->cart->cart_contents[ $key ]['gg_woo_bt_qty'] ?: 1 );
					}

					WC()->cart->set_quantity( $key, $qty, false );
				}
			}
		}
	}

	/**
	 * Hook cart_item_removed.
	 * Hook before_cart_item_quantity_zero.
	 *
	 * @param $cart_item_key
	 * @param $cart
	 */
	public function cart_item_removed( $cart_item_key, $cart ) {
		if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['gg_woo_bt_keys'] ) ) {
			$keys = $cart->removed_cart_contents[ $cart_item_key ]['gg_woo_bt_keys'];

			foreach ( $keys as $key ) {
				unset( $cart->cart_contents[ $key ] );
			}
		}
	}

	/**
	 * Is enable change cart quantity.
	 *
	 * @return bool
	 */
	protected function is_enable_change_cart_quantity() {
		return 'on' === gg_woo_bt_get_option( 'cart_change_quantity', 'on' );
	}
}
