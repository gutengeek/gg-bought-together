<?php
namespace GG_Woo_BT\Admin\Metabox;

use GG_Woo_BT\Core\Constant;

class Product_Cat_Metabox {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	protected $prefix = Constant::PRODUCT_TAX_PREFIX;

	/**
	 * Product_Cat_Metabox constructor.
	 */
	public function __construct() {
		add_action( 'product_cat_add_form_fields', [ $this, 'add_product_cat_meta' ], 20 );
		add_action( 'product_cat_edit_form_fields', [ $this, 'edit_product_cat_meta' ], 20 );

		add_action( 'created_term', [ $this, 'save_category_fields' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'save_category_fields' ], 10, 3 );
	}

	/**
	 * Add product data panel.
	 *
	 * @return string
	 */
	public function add_product_cat_meta() {
		?>
        <h2><?php esc_html_e( 'GG Woo Bought Together', 'gg-woo-bt' ); ?></h2>

        <div class="form-field">
            <label for="<?php echo esc_attr( $this->prefix ); ?>discount"><?php esc_html_e( 'Discount', 'gg-woo-bt' ); ?></label>
            <input type="number" min="0" max="100" name="<?php echo esc_attr( $this->prefix ); ?>discount" id="<?php echo esc_attr( $this->prefix ); ?>discount" style="width: 65px;">%
            <p><?php esc_html_e( 'Discount for each product in this category when bought together with another.', 'gg-woo-bt' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Edit product data panel.
	 *
	 * @return string
	 */
	public function edit_product_cat_meta( $term ) {
		$discount = get_term_meta( $term->term_id, $this->prefix . 'discount', true );
		?>
        <tr>
            <td colspan="2"><h2><?php esc_html_e( 'GG Woo Bought Together', 'gg-woo-bt' ); ?></h2></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label><?php esc_html_e( 'Discount', 'gg-woo-bt' ); ?></label></th>
            <td>
                <input type="number" min="0" max="100" name="<?php echo esc_attr( $this->prefix ); ?>discount" id="<?php echo esc_attr( $this->prefix ); ?>discount" value="<?php echo esc_attr(
                        $discount );
                ?>"
                       style="width: 65px;">%
                <p><?php esc_html_e( 'Discount for each product in this category when bought together with another.', 'gg-woo-bt' ); ?></p>
            </td>
        </tr>
		<?php
	}

	/**
	 * Save category fields
	 *
	 * @param mixed  $term_id  Term ID being saved.
	 * @param mixed  $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( 'product_cat' === $taxonomy ) {
			$terms_meta = [
				'discount',
			];

			foreach ( $terms_meta as $term_meta ) {
				$key = $this->prefix . $term_meta;
				if ( isset( $_POST[ $key ] ) ) { // WPCS: CSRF ok, input var ok.
					update_term_meta( $term_id, $key, esc_attr( $_POST[ $key ] ) ); // WPCS: CSRF ok, sanitization ok, input var ok.
				}
			}
		}
	}
}
