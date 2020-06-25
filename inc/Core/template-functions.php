<?php
/**
 * @param       $template
 * @param array $args
 * @return string
 */
function gg_woo_bt_get_template( $template, $args = [] ) {
	return GG_Woo_BT\Core\View::render_template( $template, $args );
}

/**
 * Render template.
 */
function gg_woo_bt_render_template( $template, $args = [] ) {
	echo gg_woo_bt_get_template( $template, $args );
}
