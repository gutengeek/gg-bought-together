<?php
/**
 * The Template for displaying bought together products.
 *
 * Override this template by copying it to yourtheme/gg-woo-bought-together/list.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

$product_id  = $product->get_id();
$custom_qty  = get_post_meta( $product_id, 'gg_woo_bt_custom_qty', true ) === 'on';
$sync_qty    = get_post_meta( $product_id, 'gg_woo_bt_sync_qty', true ) === 'on';
$checked_all = get_post_meta( $product_id, 'gg_woo_bt_checked_all', true ) === 'on';
$separately  = get_post_meta( $product_id, 'gg_woo_bt_separately', true ) === 'on';
$before_text = get_post_meta( $product_id, 'gg_woo_bt_before_text', true );
$before_text = $before_text ? $before_text : gg_woo_bt_get_option( 'before_text', '' );
$after_text  = get_post_meta( $product_id, 'gg_woo_bt_after_text', true );
$after_text  = $after_text ? $after_text : gg_woo_bt_get_option( 'after_text', '' );
$count       = 1;

$items = gg_woo_bt_get_products( $product_id );
if ( ! $items || empty( $items ) ) {
	return;
}

echo '<div class="gg_woo_bt-wrap gg_woo_bt-wrap-' . esc_attr( $product_id ) . '" data-id="' . esc_attr( $product_id ) . '">';

do_action( 'gg_woo_bt_wrap_before', $product );

if ( $before_text ) {
	echo '<div class="gg_woo_bt_before_text gg_woo_bt-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
}
?>
    <div class="gg_woo_bt_products gg_woo_bt-products"
         data-show-price="<?php echo esc_attr( gg_woo_bt_get_option( 'show_price', 'on' ) ); ?>"
         data-optional="<?php echo esc_attr( $custom_qty ? 'on' : 'off' ); ?>"
         data-sync-qty="<?php echo esc_attr( $sync_qty ? 'on' : 'off' ); ?>"
         data-variables="<?php echo esc_attr( gg_woo_bt_has_variables( $items ) ? 'yes' : 'no' ); ?>"
         data-product-id="<?php echo esc_attr( $product->get_type() === 'variable' ? '0' : $product_id ); ?>"
         data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
         data-product-price="<?php echo esc_attr( $product->get_type() === 'variable' ? '0' : wc_get_price_to_display( $product ) ); ?>"
         data-product-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
         data-product-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
         data-product-o-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
         data-product-price-html="<?php echo esc_attr( htmlentities( $product->get_price_html() ) ); ?>"
         data-discount="<?php echo esc_attr( ! $separately && get_post_meta( $product_id, 'gg_woo_bt_discount', true ) ? get_post_meta( $product_id, 'gg_woo_bt_discount', true ) : '0' ); ?>">
		<?php
		// Show Main product.
		if ( 'on' === gg_woo_bt_get_option( 'show_main_product', 'off' ) ) : ?>
			<?php
			$discount       = get_post_meta( $product_id, 'gg_woo_bt_discount', true );
			$data_new_price = ! $separately && $discount ? $product->get_price() * ( 100 - (float) $discount ) / 100 : '100%';
			?>
            <div class="gg_woo_bt-product gg_woo_bt-product-main"
                 data-id="<?php echo esc_attr( $product_id ); ?>"
                 data-new-price="<?php echo esc_attr( $data_new_price ); ?>"
                 data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
                 data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                 data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $product, [ 'price' => $product->get_regular_price() ] ) ); ?>"
                 data-qty="1"
                 data-qty-ori="1">
				<?php do_action( 'gg_woo_bt_product_before', $product ); ?>

                <div class="gg_woo_bt-choose">
                    <input class="gg_woo_bt-checkbox" type="checkbox" checked disabled/>
                </div>

				<?php if ( 'on' === gg_woo_bt_get_option( 'show_thumbnail', 'on' ) ) : ?>
                    <div class="gg_woo_bt-thumb">
						<?php echo $product->get_image(); ?>
                    </div>
				<?php endif; ?>

                <div class="gg_woo_bt-title">
					<?php echo esc_html__( 'Main item:', 'gg_woo_bt' ) . ' <span>' . $product->get_name() . '</span>'; ?>
                </div>

				<?php if ( $custom_qty ) : ?>
                    <div class="gg_woo_bt-quantity">
						<?php
						woocommerce_quantity_input( [
							'classes' => [
								'input-text',
								'gg_woo_bt-qty',
								'gg_woo_bt-main-qty',
								'qty',
								'text',
							],
						], $product );
						?>
                    </div>
				<?php endif;

				if ( 'on' === gg_woo_bt_get_option( 'show_price', 'on' ) ) : ?>
                    <div class="gg_woo_bt-price">
                        <div class="gg_woo_bt-price-new">
							<?php
							if ( ! $separately && ( $discount = get_post_meta( $product_id, 'gg_woo_bt_discount', true ) ) ) {
								$sale_price = $product->get_price() * ( 100 - (float) $discount ) / 100;
								echo wc_format_sale_price( $product->get_price(), $sale_price ) . $product->get_price_suffix( $sale_price );
							} else {
								echo $product->get_price_html();
							}
							?>
                        </div>
                        <div class="gg_woo_bt-price-ori">
							<?php echo $product->get_price_html(); ?>
                        </div>
                    </div>
				<?php endif;

				do_action( 'gg_woo_bt_product_after', $product );
				?>
            </div>
		<?php endif;

		// Added products.
		foreach ( $items as $item ) :
			if ( is_array( $item ) ) {
				$item_id      = $item['id'];
				$item_product = wc_get_product( $item_id );
				$item_price   = $item['price'];
				$item_qty     = $item['qty'];
			} else {
				// Default product.
				$item_id      = absint( $item );
				$item_product = wc_get_product( $item_id );
				$item_price   = '100%';
				$discount     = gg_woo_bt_get_product_term_meta( $item_product, 'discount' );
				if ( $discount ) {
					$all_percent        = '100';
					$category_new_price = (float) $all_percent - (float) $discount;
					if ( $category_new_price >= '0' ) {
						$item_price = $category_new_price . '%';
					}
				}

				$item_qty = 1;
			}

			if ( ! $item_product || ! in_array( $item_product->get_type(), gg_woo_bt_get_product_types() ) ) {
				continue;
			}

			$item_qty_min = 1;
			$item_qty_max = 100;

			if ( $custom_qty ) {
				if ( get_post_meta( $product_id, 'gg_woo_bt_limit_each_min_default', true ) === 'on' ) {
					$item_qty_min = $item_qty;
				} else {
					$item_qty_min = absint( get_post_meta( $product_id, 'gg_woo_bt_limit_each_min', true ) ?: 0 );
				}

				$item_qty_max = absint( get_post_meta( $product_id, 'gg_woo_bt_limit_each_max', true ) ?: 100 );

				if ( $item_qty < $item_qty_min ) {
					$item_qty = $item_qty_min;
				}

				if ( $item_qty > $item_qty_max ) {
					$item_qty = $item_qty_max;
				}
			}

			$checked_individual    = apply_filters( 'gg_woo_bt_checked_individual', false, $item_id, $product_id );
			$show_variation_select = gg_woo_bt_get_option( 'show_variation_select', 'on' );
			?>
            <div class="gg_woo_bt-product gg_woo_bt-product-together <?php echo ( 'on' === $show_variation_select ) ? 'show-variation-select' : ''; ?>"
                 data-id="<?php echo esc_attr( $item_product->is_type( 'variable' ) || ! $item_product->is_in_stock() ? 0 : $item_id ); ?>"
                 data-name="<?php echo esc_attr( $item_product->get_name() ); ?>"
                 data-new-price="<?php echo esc_attr( ! $separately ? $item_price : '100%' ); ?>"
                 data-price-suffix="<?php echo esc_attr( htmlentities( $item_product->get_price_suffix() ) ); ?>"
                 data-price="<?php echo esc_attr( wc_get_price_to_display( $item_product ) ); ?>"
                 data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $item_product, [ 'price' => $item_product->get_regular_price() ] ) ); ?>"
                 data-qty="<?php echo esc_attr( $item_qty ); ?>"
                 data-qty-ori="<?php echo esc_attr( $item_qty ); ?>">

				<?php do_action( 'gg_woo_bt_product_before', $item_product, $count ); ?>

                <div class="gg_woo_bt-choose">
                    <input class="gg_woo_bt-checkbox" type="checkbox"
                           value="<?php echo esc_attr( $item_id ); ?>" <?php echo( ! $item_product->is_in_stock() ? 'disabled' : '' ); ?> <?php echo( $item_product->is_in_stock() && ( $checked_all || $checked_individual ) ? 'checked' : '' ); ?>/>
                </div>

				<?php if ( 'on' === gg_woo_bt_get_option( 'show_thumbnail', 'on' ) ) { ?>
                    <div class="gg_woo_bt-thumb">
                        <div class="gg_woo_bt-thumb-ori">
							<?php echo $item_product->get_image(); ?>
                        </div>
                        <div class="gg_woo_bt-thumb-new"></div>
                    </div>
				<?php } ?>

                <div class="gg_woo_bt-title">
					<?php if ( ! $custom_qty ) {
						$item_product_qty = '<span class="gg_woo_bt-qty-num"><span class="gg_woo_bt-qty">' . $item_qty . '</span> × </span>';
					} else {
						$item_product_qty = '';
					}

					if ( $item_product->is_in_stock() ) {
						$item_product_name = $item_product->get_name();
					} else {
						$item_product_name = '<s>' . $item_product->get_name() . '</s>';
					}

					if ( gg_woo_bt_get_option( 'view_detail', 'new_tab' ) ) {
						$item_product_name = '<a href="' . $item_product->get_permalink() . '" ' . ( 'new_tab' === gg_woo_bt_get_option( 'view_detail',
								'new_tab' ) ? 'target="_blank"' : '' ) . '>' . $item_product_name . '</a>';
					}

					echo apply_filters( 'gg_woo_bt_product_name', $item_product_qty . $item_product_name, $item_product );

					if ( $item_product->is_type( 'variable' ) && $item_product->has_child() ) :
						$attributes = $item_product->get_variation_attributes();
						$selected_attributes = $item_product->get_default_attributes();
						$attribute_keys = array_keys( $attributes );
						// Get Available variations?
						$get_variations       = count( $item_product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $item_product );
						$available_variations = $get_variations ? $item_product->get_available_variations() : false;
						$variations_json      = wp_json_encode( $available_variations );
						$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
						?>
                        <div class="variations_form" data-product_id="<?php echo absint( $item_product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok.
						?>">
                            <div class="variations">
								<?php foreach ( $attributes as $attribute_name => $options ) : ?>
                                    <div class="variation">
                                        <div class="label"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></div>
                                        <div class="select">
											<?php
											wc_dropdown_variation_attribute_options(
												[
													'options'   => $options,
													'attribute' => $attribute_name,
													'product'   => $item_product,
												]
											);
											?>
                                        </div>
                                    </div>
									<?php echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link',
										'<div class="reset"><a class="reset_variations" href="#">' . esc_html__( 'Clear', 'gg-woo-bt' ) . '</a></div>' ) ) : ''; ?>
								<?php endforeach; ?>
                            </div>
                        </div>
					<?php endif;

					if ( 'on' === gg_woo_bt_get_option( 'show_description', 'off' ) ) {
						echo '<div class="gg_woo_bt-description">' . $item_product->get_short_description() . '</div>';
					}

					echo '<div class="gg_woo_bt-availability">' . wc_get_stock_html( $item_product ) . '</div>';
					?>
                </div>

				<?php if ( $custom_qty ) :
					echo '<div class="gg_woo_bt-quantity">';

					woocommerce_quantity_input( [
						'classes'     => [ 'input-text', 'gg_woo_bt-qty', 'qty', 'text' ],
						'input_value' => $item_qty,
						'min_value'   => $item_qty_min,
						'max_value'   => $item_qty_max,
					], $item_product );

					echo '</div>';
				endif;

				if ( 'on' === gg_woo_bt_get_option( 'show_price', 'on' ) ) : ?>
                    <div class="gg_woo_bt-price">
                        <div class="gg_woo_bt-price-new"></div>
                        <div class="gg_woo_bt-price-ori">
							<?php
							if ( ! $separately && ( $item_price !== '100%' ) ) {
								$item_new_price = gg_woo_bt_get_new_price( wc_get_price_to_display( $item_product ), $item_price );

								if ( $item_new_price < $item_product->get_price() ) {
									$item_product_price = wc_format_sale_price( wc_get_price_to_display( $item_product ), $item_new_price );
								} else {
									$item_product_price = wc_price( $item_new_price );
								}

								$item_product_price .= $item_product->get_price_suffix();
							} else {
								$item_product_price = $item_product->get_price_html();
							}

							echo apply_filters( 'gg_woo_bt_product_price', $item_product_price, $item_product );
							?>
                        </div>
                    </div>
				<?php endif;

				do_action( 'gg_woo_bt_product_after', $item_product, $count );
				?>
            </div>
			<?php
			$count++;
		endforeach; ?>
    </div>
<?php
echo '<div class="gg_woo_bt_total gg_woo_bt-total gg_woo_bt-text"></div>';
echo '<div class="gg_woo_bt_alert gg_woo_bt-notice gg_woo_bt-text" style="display: none"></div>';

if ( $after_text ) {
	echo '<div class="gg_woo_bt_after_text gg_woo_bt-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
}

do_action( 'gg_woo_bt_wrap_after', $product );

echo '</div>';

