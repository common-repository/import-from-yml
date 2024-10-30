<?php defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
if ( is_multisite() ) {
	$ipytw_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'ipytw_registered_feeds_arr' );
	if ( is_array( $ipytw_registered_feeds_arr ) ) {
		// с единицы, т.к инфа по конкретным фидам там
		for ( $i = 1; $i < count( $ipytw_registered_feeds_arr ); $i++ ) {
			$feed_id = $ipytw_registered_feeds_arr[ $i ]['id'];
			if ( $feed_id === '0' || $feed_id === 0 ) {
				$feed_id = '';
			} // позже можно будет удалить
			delete_blog_option( get_current_blog_id(), 'ipytw_status_sborki' . $feed_id );
			delete_blog_option( get_current_blog_id(), 'ipytw_last_element' . $feed_id );
		}
	}
	delete_blog_option( get_current_blog_id(), 'ipytw_keeplogs' );
	delete_blog_option( get_current_blog_id(), 'ipytw_enable_backend_debug' );
	delete_blog_option( get_current_blog_id(), 'ipytw_version' );
	delete_blog_option( get_current_blog_id(), 'ipytw_disable_notices' );
	delete_blog_option( get_current_blog_id(), 'ipytw_settings_arr' );
	delete_blog_option( get_current_blog_id(), 'ipytw_registered_feeds_arr' );
} else {
	$ipytw_registered_feeds_arr = get_option( 'ipytw_registered_feeds_arr' );
	if ( is_array( $ipytw_registered_feeds_arr ) ) {
		// с единицы, т.к инфа по конкретным фидам там
		for ( $i = 1; $i < count( $ipytw_registered_feeds_arr ); $i++ ) {
			$feed_id = $ipytw_registered_feeds_arr[ $i ]['id'];
			if ( $feed_id === '0' || $feed_id === 0 ) {
				$feed_id = '';
			} // позже можно будет удалить
			delete_option( 'ipytw_status_sborki' . $feed_id );
			delete_option( 'ipytw_last_element' . $feed_id );
		}
	}
	delete_option( 'ipytw_keeplogs' );
	delete_option( 'ipytw_enable_backend_debug' );
	delete_option( 'ipytw_version' );
	delete_option( 'ipytw_disable_notices' );
	delete_option( 'ipytw_settings_arr' );
	delete_option( 'ipytw_registered_feeds_arr' );
}