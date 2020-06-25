<?php
namespace GG_Woo_BT\Admin\Controller;

use GG_Woo_BT\Core\Controller;

class Product extends Controller {
	/**
	 * Register Hook Callback functions is called.
	 */
	public function register_hook_callbacks() {
		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
	}

	/**
	 * Display post states
	 */
	public function display_post_states( $states, $post ) {
		if ( 'product' == get_post_type( $post->ID ) ) {
			if ( $ids = get_post_meta( $post->ID, 'gg_woo_bt_ids', true ) ) {
				$ids = gg_woo_bt_sanitize_ids( $ids );

				if ( ! empty( $ids ) ) {
					$count    = count( explode( ',', $ids ) );
					$states[] = apply_filters( 'gg_woo_bt_post_states', '<span class="gg_woo_bt-state">' . sprintf( esc_html__( 'Associate (%s)', 'gg_woo_bt' ), $count ) . '</span>', $count,
						$post->ID );
				}
			}
		}

		return $states;
	}
}
