<?php
namespace GG_Woo_BT\Common\Model;

class Search {
	/**
	 * Search products query.
	 *
	 * @param array $post
	 * @return \WP_Query
	 */
	public static function search_products( $post ) {
		$keyword         = sanitize_text_field( $post['keyword'] );
		$id              = absint( $post['id'] );
		$ids             = gg_woo_bt_sanitize_ids( $post['ids'] );
		$excluded_ids    = [ $id ];
		$products_exists = explode( ',', $ids );
		$limit           = gg_woo_bt_get_option( 'search_limit', '5' );

		$query_args = [
			'post_type'   => 'product',
			'post_status' => 'publish',
		];

		if ( is_numeric( $keyword ) ) {
			$query_args['p'] = absint( $keyword );
		} else {
			$query_args['is_gg_woo_bt']   = true;
			$query_args['s']              = $keyword;
			$query_args['posts_per_page'] = $limit;
			$query_args['tax_query']      = [
				[
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => gg_woo_bt_get_product_types(),
					'operator' => 'IN',
				],
			];
		}

		if ( is_array( $products_exists ) && count( $products_exists ) ) {
			foreach ( $products_exists as $product_exist ) {
				$product_exist_data = explode( '/', $product_exist );
				$excluded_ids[]     = absint( $product_exist_data[0] ?: 0 );
			}
		}

		$query_args['post__not_in'] = $excluded_ids;

		return new \WP_Query( $query_args );
	}

	/**
	 * Search by sku.
	 *
	 * @param $query
	 */
	public static function search_by_sku( $query ) {
		if ( $query->is_search && isset( $query->query['is_gg_woo_bt'] ) ) {
			global $wpdb;
			$sku = $query->query['s'];
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );

			if ( ! $ids ) {
				return;
			}

			unset( $query->query['s'], $query->query_vars['s'] );
			$query->query['post__in'] = [];

			foreach ( $ids as $id ) {
				$post = get_post( $id );

				if ( $post->post_type === 'product_variation' ) {
					$query->query['post__in'][]      = $post->post_parent;
					$query->query_vars['post__in'][] = $post->post_parent;
				} else {
					$query->query_vars['post__in'][] = $post->ID;
				}
			}
		}
	}
}
