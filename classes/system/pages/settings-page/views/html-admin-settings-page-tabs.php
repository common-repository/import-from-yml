<?php
/**
 * Print tabs
 * 
 * @version 3.1.5 (29-08-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tabs_arr']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="nav-tab-wrapper" style="margin-bottom: 10px;">
	<?php
	foreach ( $view_arr['tabs_arr'] as $tab => $name ) {
		if ( $tab === $view_arr['tab_name'] ) {
			$class = ' nav-tab-active';
		} else {
			$class = '';
		}
		if ( isset( $_GET['feed_id'] ) ) {
			$nf = '&feed_id=' . sanitize_text_field( $_GET['feed_id'] );
		} else {
			$nf = '&feed_id=' . get_first_feed_id();
		}
		printf(
			'<a class="nav-tab%1$s" href="?page=ipytwexport&tab=%2$s%3$s">%4$s</a>',
			esc_attr( $class ), esc_attr( $tab ), esc_attr( $nf ), esc_html( $name )
		);
	}
	?>
</div>