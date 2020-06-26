<?php
/**
 * Plugin Name:       GG Bought Together for WooCommerce
 * Plugin URI:        https://gutengeek.com
 * Description:       GG Bought Together for WooCommerce allows increasing the average spent of your store by showing a box with the products purchased together more frequently.
 * Version:           1.0.2
 * Author:            GutenGeek
 * Author URI:        https://gutengeek.com/contact
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gg-woo-bt
 * Domain Path:       /languages
 * WC requires at least: 3.6
 * WC tested up to: 4.2.0
 */

// If this file is called directly, abort.
use GG_Woo_BT\Core\Init;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */
define( 'GGWOOBT', 'gg-woo-bt' );
define( 'GGWOOBT_VERSION', '1.0.2' );
define( 'GGWOOBT_DIR', plugin_dir_path( __FILE__ ) );
define( 'GGWOOBT_URL', plugin_dir_url( __FILE__ ) );
define( 'GGWOOBT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'GGWOOBT_PLUGIN_TEXT_DOMAIN', 'gg-woo-bt' );
define( 'GGWOOBT_METABOX_PREFIX', '_' );

require_once( GGWOOBT_DIR . 'vendor/autoload.php' );
require_once( GGWOOBT_DIR . 'inc/Core/functions.php' );
require_once( GGWOOBT_DIR . 'inc/Core/mix-functions.php' );
require_once( GGWOOBT_DIR . 'inc/Core/template-functions.php' );
require_once( GGWOOBT_DIR . 'inc/Core/ajax-functions.php' );

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook( __FILE__, array( 'GG_Woo_BT\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */
register_deactivation_hook( __FILE__, array( 'GG_Woo_BT\Core\Deactivator', 'deactivate' ) );

/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 */
class GG_Woo_BT {

	/**
	 * The instance of the plugin.
	 *
	 * @var      Init $init Instance of the plugin.
	 */
	private static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null === self::$init ) {
			self::$init = new Init();
			self::$init->run();
		}

		return self::$init;
	}
}

/**
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 **/
function gg_woo_bt_init() {
	return GG_Woo_BT::init();
}

gg_woo_bt_init();
