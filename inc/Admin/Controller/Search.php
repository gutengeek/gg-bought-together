<?php
namespace GG_Woo_BT\Admin\Controller;

use GG_Woo_BT\Core\Controller;
use GG_Woo_BT\Common\Model\Search as Search_Model;

class Search extends Controller {
	/**
	 * Register Hook Callback functions is called.
	 */
	public function register_hook_callbacks() {
		add_filter( 'pre_get_posts', [ $this, 'search_by_sku' ], 99 );
	}

	/**
	 * Search by SKU.
	 */
	public function search_by_sku( $query ) {
		Search_Model::search_by_sku( $query );
	}
}
