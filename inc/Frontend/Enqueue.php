<?php
namespace GG_Woo_BT\Frontend;

class Enqueue {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_text_domain The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name        The name of this plugin.
	 * @param string $version            The version of this plugin.
	 * @param string $plugin_text_domain The text domain of this plugin.
	 * @since       1.0.0
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {
		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->plugin_text_domain = $plugin_text_domain;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$suffix = ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ? '.min' : '';
		wp_enqueue_style( GGWOOBT, GGWOOBT_URL . 'assets/css/gg-woo-bt' . $suffix . '.css', [], $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$suffix = ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ? '.min' : '';
		wp_enqueue_script( $this->plugin_name, GGWOOBT_URL . 'assets/js/gg-woo-bt' . $suffix . '.js', [ 'jquery' ], $this->version, true );

		$price_display_suffix = get_option( 'woocommerce_price_display_suffix' );
		$price_display_suffix = $price_display_suffix && wc_tax_enabled() ? $price_display_suffix : '';

		wp_localize_script( $this->plugin_name, 'gg_woo_bt_params', [
				'position'                 => gg_woo_bt_get_option( 'position', 'before_add_to_cart' ),
				'recal_price'              => gg_woo_bt_get_option( 'recal_price', 'on' ),
				'main_price_selector'      => gg_woo_bt_get_option( 'main_price_selector', '.summary > .price' ),
				'counter'                  => gg_woo_bt_get_option( 'counter', 'individual' ),
				'price_format'             => get_woocommerce_price_format(),
				'price_suffix'             => $price_display_suffix,
				'price_decimals'           => wc_get_price_decimals(),
				'price_thousand_separator' => wc_get_price_thousand_separator(),
				'price_decimal_separator'  => wc_get_price_decimal_separator(),
				'currency_symbol'          => get_woocommerce_currency_symbol(),
				'text'                     => [
					'add_to_cart'      => esc_html__( 'Add to cart', 'gg-woo-bt' ),
					'variation_notice' => esc_html__( 'Please choose variation options for %s.', 'gg-woo-bt' ),
					'additional_price' => esc_html__( 'Additional price:', 'gg-woo-bt' ),
				],
			]
		);
	}
}
