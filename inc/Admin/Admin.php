<?php
namespace GG_Woo_BT\Admin;

use GG_Woo_BT\Admin\Setting as Setting;
use GG_Woo_BT\Libraries as Libraries;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
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

	public $settings_objs = [];

	/**
	 * Register the stylesheets for the admin area.
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
		wp_enqueue_style( $this->plugin_name . '_admin', GGWOOBT_URL . 'assets/css/admin/admin' . $suffix . '.css', [], $this->version, 'all' );

		wp_enqueue_style( 'select2', GGWOOBT_URL . 'assets/3rd/select2/css/select2.min.css', null, '4.0.7', false );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/*
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
		wp_enqueue_script( 'select2', GGWOOBT_URL . 'assets/3rd/select2/js/select2.min.js', null, '4.0.7', false );
		wp_enqueue_script( 'clipboard', GGWOOBT_URL . 'assets/3rd/clipboard.min.js', [], '2.0.4', false );
		wp_enqueue_script( $this->plugin_name . '_admin', GGWOOBT_URL . 'assets/js/admin' . $suffix . '.js', [ 'jquery-ui-sortable' ], $this->version, false );

		do_action( 'gg_woo_bt_admin_enqueue_scripts', $this );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_submenu_page(
			'woocommerce',
			apply_filters( $this->plugin_name . '-settings-page-title', esc_html__( 'Bought Together', 'gg-woo-bt' ) ),
			apply_filters( $this->plugin_name . '-settings-menu-title', esc_html__( 'Bought Together', 'gg-woo-bt' ) ),
			'manage_options',
			$this->plugin_name . '-settings',
			[ $this, 'page_options' ]
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	private function load_object_settings() {
		if ( empty( $this->settings_objs ) ) {
			$matching = apply_filters( 'gg_woo_bt_load_settings', [
				'general'          => Setting\General::class,
				'search'           => Setting\Search::class,
				'default_products' => Setting\Default_Products::class,
			] );

			foreach ( $matching as $match => $class ) {
				$object                        = new $class();
				$this->settings_objs[ $match ] = $object;
			}
		}

		return $this->settings_objs;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function update_settings() {
		if ( isset( $_POST['save_page_options'] ) ) {
			$tab     = $this->get_tab_active();
			$objects = $this->load_object_settings();
			if ( isset( $objects[ $tab ] ) ) {

				$settings = $objects[ $tab ]->get_settings();
				$objects[ $tab ]->save_settings_options( $settings, 'gg_woo_bt_settings' );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	private function get_tab_active() {
		$tab = 'general';
		if ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		}

		return $tab;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function page_options() {
		$matching   = $this->load_object_settings();
		$tab_active = $this->get_tab_active();

		echo '<div class="gg_woo_bt-settings-page">';
		echo '<div class="setting-tab-head"><ul class="inline-list">';
		foreach ( $matching as $match => $object ) {
			$tab = $object->get_tab();

			$tab_url = esc_url( add_query_arg( [
				'settings-updated' => false,
				'tab'              => $tab['id'],
				'subtab'           => false,
			] ) );

			$class = $tab['id'] == $tab_active ? ' class="active"' : "";

			echo '<li' . $class . '><a href="' . $tab_url . '" >' . $tab['heading'] . '</a></li>';
		}
		echo '</ul></div>';


		$form = Libraries\Form\Form::get_instance();

		$form->setup( 'page_options', 'gg_woo_bt_settings' );

		$args = [];

		echo '<form action="" method="post">';
		if ( isset( $matching[ $tab_active ] ) && isset( $this->settings_objs[ $tab_active ] ) ) {
			$object   = $this->settings_objs[ $tab_active ];
			$settings = $object->get_settings();
			echo $form->render( $args, $settings );
		}

		echo '<button class="gg_woo_bt-btn gg_woo_bt-btn-submit" name="save_page_options" value="savedata" type="submit">' . esc_html__( 'Save' ) . '</button>';
		echo '</form>';

		echo '</div>';
	}
}
