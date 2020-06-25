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
class Search extends Core\Metabox {

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 */
	public function get_tab() {
		return [ 'id' => 'search', 'heading' => esc_html__( 'Search' ) ];
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
				'id'          => 'search_limit',
				'name'        => esc_html__( 'Limit', 'gg-woo-bt' ),
				'type'        => 'text_number',
				'default'     => '5',
				'description' => esc_html__( 'Maximum number of products to be displayed when searching.', 'gg-woo-bt' ),
			],
		];

		return apply_filters( 'gg_woo_bt_settings_search', $fields );
	}
}
