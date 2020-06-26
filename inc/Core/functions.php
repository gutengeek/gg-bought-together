<?php

use GG_Woo_BT\Core\Constant;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 * @return string|array
 */
function gg_woo_bt_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'gg_woo_bt_clean', $var );
	}

	return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
}

/**
 * Get Options Value by Key
 *
 * @return mixed
 *
 */
function gg_woo_bt_get_option( $key, $default = '' ) {
	global $gg_woo_bt_options;

	$value = isset( $gg_woo_bt_options[ $key ] ) ? $gg_woo_bt_options[ $key ] : $default;
	$value = apply_filters( 'gg_woo_bt_option_', $value, $key, $default );

	return apply_filters( 'gg_woo_bt_option_' . $key, $value, $key, $default );
}

/**
 * Sanitize ids.
 *
 * @param $ids
 * @return string
 */
function gg_woo_bt_sanitize_ids( $ids ) {
	$ids = preg_replace( '/[^.%,\/0-9]/', '', $ids );

	return $ids;
}

/**
 * Sanitize price.
 *
 * @param $price
 * @return string
 */
function gg_woo_bt_sanitize_price( $price ) {
	$price = preg_replace( '/[^.%0-9]/', '', $price );

	return $price;
}

/**
 * Get new price.
 *
 * @param $old_price
 * @param $new_price
 * @return float|int
 */
function gg_woo_bt_get_new_price( $old_price, $new_price ) {
	if ( strpos( $new_price, '%' ) !== false ) {
		$calc_price = ( (float) $new_price * $old_price ) / 100;
	} else {
		$calc_price = $new_price;
	}

	return $calc_price;
}

/**
 * @param        $product \WC_Product
 * @param string $price
 * @param int    $qty
 * @param bool   $search
 */
function gg_woo_bt_get_product_search_results_template( $product ) {
	$product_id = $product->get_id();
	?>
    <li <?php echo ! $product->is_in_stock() ? 'class="out-of-stock"' : ''; ?> data-id="<?php echo absint( $product_id ); ?>">
        <span class="gg-woo-bt-pname"><?php echo esc_html( $product->get_name() ); ?></span> (<span
                class="gg-woo-bt-pinfo"><?php echo $product->get_type(); ?>#<?php echo absint( $product_id ); ?></span>) | <span
                class="gg-woo-bt-pprice"><?php echo $product->get_price_html(); ?></span>
    </li>
	<?php
}

/**
 * @param        $product \WC_Product
 * @param string $price
 * @param int    $qty
 * @param bool   $search
 */
function gg_woo_bt_get_product_search_template( $product, $price = '100%', $qty = 1 ) {
	$product_id = $product->get_id();
	?>
    <li <?php echo ! $product->is_in_stock() ? 'class="out-of-stock"' : ''; ?> data-id="<?php echo absint( $product_id ); ?>">
        <span class="move dashicons dashicons-menu"></span>
        <span class="gg-woo-bt-admin-item-price">
            <input class="gg-woo-bt-admin-input gg_woo_bt_price" type="text" name="gg_woo_bt_price" value="<?php echo esc_attr( $price ); ?>"/>
        <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Set a new price using a number (ex: "99") or percentage (ex: "90%" of original price)',
	        'gg-woo-bt' ); ?>"></span>
        </span>
        <span class="gg-woo-bt-admin-item-qty">
            <input class="gg-woo-bt-admin-input gg_woo_bt_qty" type="number" name="gg_woo_bt_qty" value="<?php echo absint( $qty ); ?>" step="1"/>
        <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Default quantity', 'gg-woo-bt' ); ?>"></span>
        </span>
        <span class="gg-woo-bt-pname">
            <?php echo esc_html( $product->get_name() ); ?></span> (<a class="gg-woo-bt-pinfo" href="<?php echo get_edit_post_link( $product_id ); ?>" target="_blank"><?php echo $product->get_type();
			?>#<?php echo
			absint(
				$product_id );
			?></a>) |
        <span
                class="gg-woo-bt-pprice"><?php echo $product->get_price_html(); ?></span>
        <span class="remove"><?php esc_html_e( 'Remove', 'gg-woo-bt' ); ?></span>
    </li>
	<?php
}

/**
 * Get items.
 *
 * @param $ids
 * @return array|bool
 */
function gg_woo_bt_get_items( $ids ) {
	$result_items = [];
	$ids          = gg_woo_bt_sanitize_ids( $ids );

	if ( ! empty( $ids ) ) {
		$items = explode( ',', $ids );

		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				$item_data      = explode( '/', $item );
				$result_items[] = [
					'id'    => absint( isset( $item_data[0] ) ? $item_data[0] : 0 ),
					'price' => isset( $item_data[1] ) ? gg_woo_bt_sanitize_price( $item_data[1] ) : '100%',
					'qty'   => (float) ( isset( $item_data[2] ) ? $item_data[2] : 1 ),
				];
			}
		}
	}

	return $result_items;
}

/**
 * Get meta items.
 *
 * @param $ids
 * @return array
 */
function gg_woo_bt_get_meta_items( $ids ) {
	$result_items = [];
	$ids          = gg_woo_bt_sanitize_ids( $ids );

	if ( ! empty( $ids ) ) {
		$items = explode( ',', $ids );

		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				$item_data  = explode( '/', $item );
				$temp_id    = absint( isset( $item_data[0] ) ? $item_data[0] : 0 );
				$temp_price = isset( $item_data[1] ) ? gg_woo_bt_sanitize_price( $item_data[1] ) : '100%';
				$temp_qty   = (float) ( isset( $item_data[2] ) ? $item_data[2] : 1 );
				$product    = wc_get_product( $temp_id );

				if ( $product ) {
					if ( $product->is_type( 'variable' ) && $product->has_child() ) {
						$children_ids = $product->get_visible_children();
						if ( is_array( $children_ids ) && ( count( $children_ids ) > 0 ) ) {
							foreach ( $children_ids as $child_id ) {
								$result_items[] = [
									'id'    => $child_id,
									'price' => $temp_price,
									'qty'   => $temp_qty,
								];
							}
						}
					} else {
						$result_items[] = [
							'id'    => $product->get_id(),
							'price' => $temp_price,
							'qty'   => $temp_qty,
						];
					}
				}
			}
		}
	}

	return $result_items;
}

/**
 * Check if has variables.
 *
 * @param $items
 * @return bool
 */
function gg_woo_bt_has_variables( $items ) {
	foreach ( $items as $item ) {
		if ( is_array( $item ) && isset( $item['id'] ) ) {
			$item_id = $item['id'];
		} else {
			$item_id = absint( $item );
		}

		$item_product = wc_get_product( $item_id );

		if ( ! $item_product ) {
			continue;
		}

		if ( $item_product->is_type( 'variable' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get product types.
 *
 * @return array
 */
function gg_woo_bt_get_product_types() {
	return Constant::PRODUCT_TYPES;
}

/**
 * @param $product_id int
 * @return array|bool
 */
function gg_woo_bt_get_products( $product_id ) {
	$product = wc_get_product( $product_id );

	if ( $ids = get_post_meta( $product->get_id(), 'gg_woo_bt_ids', true ) ) {
		return gg_woo_bt_get_items( $ids );
	}

	return gg_woo_bt_get_default_products( $product );
}

/**
 * @param $product_id int
 * @return array|bool
 */
function gg_woo_bt_get_allowed_products( $product_id ) {
	$product = wc_get_product( $product_id );

	if ( $ids = get_post_meta( $product->get_id(), 'gg_woo_bt_ids', true ) ) {
		return gg_woo_bt_get_meta_items( $ids );
	}

	return gg_woo_bt_get_default_products( $product );
}

/**
 * @param $product \WC_Product
 * @return array
 */
function gg_woo_bt_get_default_products( $product ) {
	$items        = [];
	$default_type = gg_woo_bt_get_option( 'default_products', '' );

	switch ( $default_type ) {
		case 'upsells':
			$items = $product->get_upsell_ids();
			break;
		case 'related':
			$items = wc_get_related_products( $product->get_id() );
			break;
		case 'related_upsells':
			$items_upsells = $product->get_upsell_ids();
			$items_related = wc_get_related_products( $product->get_id() );
			$items         = array_merge( $items_upsells, $items_related );
			break;
		case 'rules':
			$items = gg_woo_bt_query_rules( $product );
			break;
	}

	return $items;
}

/**
 * @param $product \WC_Product
 */
function gg_woo_bt_query_rules( $product ) {
	$items = [];

	$bad_keywords = gg_woo_bt_get_option( 'bad_keywords', '' );
	if ( $bad_keywords ) {
		$bad_keywords = explode( ',', $bad_keywords );
		$bad_keywords = array_map( 'trim', $bad_keywords );
		$bad_keywords = array_map( 'strtolower', $bad_keywords );

		$title         = $product->get_title();
		$good_keywords = str_replace( $bad_keywords, '', strtolower( $title ) );
		$good_keywords = trim( $good_keywords );

		if ( $good_keywords ) {
			$good_keywords = explode( ' ', $good_keywords );
			$good_keywords = array_filter( $good_keywords );
			$good_keywords = array_map( 'strtolower', $good_keywords );

			$query_parts = [];
			foreach ( $good_keywords as $val ) {
				$query_parts[] = "'%" . sanitize_text_field( $val ) . "%'";
			}

			$limit    = gg_woo_bt_get_option( 'product_limit', 5 );
			$order_by = gg_woo_bt_get_option( 'order_by', 'ID' );
			$order    = gg_woo_bt_get_option( 'order', 'DESC' );

			if ( $query_parts ) {
				$string             = implode( ' OR post_title LIKE ', $query_parts );
				$allowed_categories = gg_woo_bt_get_option( 'allowed_categories', [] );

				$this_product_cats_list = [];
				$this_product_cats      = get_the_terms( $product->get_id(), 'product_cat' );
				if ( $this_product_cats ) {
					$this_product_cats_list = wp_list_pluck( $this_product_cats, 'slug' );
				}

				$mapping = gg_woo_bt_get_mapping_setting();

				$maybe = [];
				if ( $this_product_cats_list ) {
					foreach ( $this_product_cats_list as $this_product_cat_slug ) {
						if ( array_key_exists( $this_product_cat_slug, $mapping ) ) {
							if ( isset( $mapping[ $this_product_cat_slug ] ) && $mapping[ $this_product_cat_slug ] ) {
								foreach ( $mapping[ $this_product_cat_slug ] as $value ) {
									$maybe[] = $value;
								}
							}
						}
					}
				}

				$category_in = [];
				if ( $allowed_categories ) {
					if ( $maybe ) {
						$category_in = array_intersect( $maybe, $allowed_categories );
					} else {
						$category_in = $allowed_categories;
					}
				} else {
					if ( $maybe ) {
						$category_in = $maybe;
					} else {
						$category_in = [];
					}
				}

				$post_in = '';
				if ( $category_in ) {
					$args = [
						'post_type'   => 'product',
						'post_status' => 'publish',
						'numberposts' => -1,
						'tax_query'   => [
							[
								'taxonomy' => 'product_cat',
								'field'    => 'slug',
								'terms'    => $category_in,
							],
						],
					];

					$allowed_products = get_posts( $args );

					if ( $allowed_products ) {
						$allowed_ids = wp_list_pluck( $allowed_products, 'ID' );
						$post_in     = " AND ID IN ( '" . implode( "','", $allowed_ids ) . "' )";
					}
				}

				global $wpdb;
				$product_id = $product->get_id();
				$query      = "SELECT ID FROM {$wpdb->posts} WHERE ID != {$product_id}{$post_in} AND post_status = 'publish' AND post_type = 'product' AND ( LOWER(post_title) LIKE {$string} ) ORDER BY {$order_by} {$order} LIMIT {$limit}";

				$results = $wpdb->get_results( $query );
				$items   = wp_list_pluck( $results, 'ID' );
			}
		}
	}

	return $items;
}

/**
 * @param        $product \WC_Product
 * @param        $meta_key
 * @param string $taxonomy
 * @return mixed|string
 */
function gg_woo_bt_get_product_term_meta( $product, $meta_key, $taxonomy = 'product_cat' ) {
	$id = $product->get_id();
	if ( $product->is_type( 'variation' ) ) {
		$id = $product->get_parent_id();
	}

	try {
		$terms = get_the_terms( $id, $taxonomy );

		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		foreach ( $terms as $term ) {
			$term_value = get_term_meta( $term->term_id, Constant::PRODUCT_TAX_PREFIX . $meta_key, true );
			if ( $term_value ) {
				return $term_value;
			}
		}

		return '';
	} catch ( Exception $e ) {
		return '';
	}
}

add_action( 'gg_woo_bt_after_save_settings', 'gg_woo_bt_save_mapping_data', 10, 2 );

function gg_woo_bt_save_mapping_data( $update_options, $old_options ) {
	$mapping_category = isset( $_POST['mapping_category'] ) ? gg_woo_bt_clean( $_POST['mapping_category'] ) : [];
	$mapping_to       = isset( $_POST['mapping_to'] ) ? gg_woo_bt_clean( $_POST['mapping_to'] ) : [];
	$new_options      = [];

	if ( $mapping_category && $mapping_to ) {
		$new_options['mapping_category'] = $mapping_category;
		$new_options['mapping_to']       = $mapping_to;
	}

	update_option( 'gg_woo_bt_mapping', $new_options );
}

function gg_woo_bt_get_mapping_setting() {
	$mapping_option   = get_option( 'gg_woo_bt_mapping', [] );
	$mapping_category = isset( $mapping_option['mapping_category'] ) && $mapping_option['mapping_category'] ? $mapping_option['mapping_category'] : [];
	$mapping_to       = isset( $mapping_option['mapping_to'] ) && $mapping_option['mapping_to'] ? $mapping_option['mapping_to'] : [];

	$settings = [];
	foreach ( $mapping_category as $mapping_category_key => $mapping_category_value ) {
		if ( isset( $mapping_to[ $mapping_category_key ] ) && $mapping_to[ $mapping_category_key ] ) {
			$settings[ $mapping_category_value ] = explode( '|', $mapping_to[ $mapping_category_key ] );
		}
	}

	return $settings;
}
