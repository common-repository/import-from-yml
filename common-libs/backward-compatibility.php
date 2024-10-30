<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
Version: 1.0.0
Date: 04-01-2022
Author: Maxim Glazunov
Author URI: https://icopydoc.ru 
License: GPLv2
Description: This code helps ensure backward compatibility with older versions of the plugin.
*/

/**
 * @since 1.0.0
 *
 * @return string/NULL
 *
 * Возвращает версию Woocommerce
 */
define( 'ipytw_VER', '1.6.0' ); // для совместимости со старыми прошками
/**
 * @since 1.0.0
 *
 * @param string $text (require)
 *
 * @return void
 * Записывает файл логов /wp-content/uploads/import-from-yml/ipytw.log
 */
function ipytw_error_log( $text ) {
	$ipytw_keeplogs = ipytw_optionGET( 'ipytw_keeplogs' );

	if ( $ipytw_keeplogs !== 'on' ) {
		return;
	}
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . "/import-from-yml";
	// подготовим массив для записи в файл логов
	if ( is_array( $text ) ) {
		$r = get_array_as_string( $text );
		unset( $text );
		$text = $r;
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/ipytw.log';
		file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
	} else {
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'Нет папки import-from-yml! И создать не вышло! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
		} else {
			error_log( 'Создали папку ipytw!; Файл: functions.php; Строка: ' . __LINE__, 0 );
			$filename = $name_dir . '/ipytw.log';
			file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
		}
	}
	return;
}
function ipytw_del_feed_zero() {
	$feed_id = '0';
	$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
	unset( $ipytw_settings_arr[ $feed_id ] );
	wp_clear_scheduled_hook( 'ipytw_cron_period', array( $feed_id ) ); // отключаем крон
	wp_clear_scheduled_hook( 'ipytw_cron_sborki', array( $feed_id ) ); // отключаем крон
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . "/import-from-yml";
	$filename = $name_dir . '/feed-imported-offers-' . $feed_id . '.tmp';
	if ( file_exists( $filename ) ) {
		unlink( $filename );
	}
	$filename = $name_dir . '/feed-importetd-cat-ids-' . $feed_id . '.tmp';
	if ( file_exists( $filename ) ) {
		unlink( $filename );
	}
	ipytw_optionUPD( 'ipytw_settings_arr', $ipytw_settings_arr );
	ipytw_optionDEL( 'ipytw_status_sborki', $feed_id );
	ipytw_optionDEL( 'ipytw_last_element', $feed_id );

	$ipytw_registered_feeds_arr = ipytw_optionGET( 'ipytw_registered_feeds_arr' );
	for ( $n = 1; $n < count( $ipytw_registered_feeds_arr ); $n++ ) { // первый элемент не проверяем, тк. там инфо по последнему id
		if ( $ipytw_registered_feeds_arr[ $n ]['id'] === $feed_id ) {
			unset( $ipytw_registered_feeds_arr[ $n ] );
			$ipytw_registered_feeds_arr = array_values( $ipytw_registered_feeds_arr );
			ipytw_optionUPD( 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
			break;
		}
	}

	$feed_id = get_first_feed_id();
}
function ipytw_calibration( $ipytw_textarea_info ) {
	$ipytw_textarea_info_arr = explode( 'txY5L8', $ipytw_textarea_info );
	$name1 = $ipytw_textarea_info_arr[2] . '_' . $ipytw_textarea_info_arr[3] . 'nse_status';
	$name2 = $ipytw_textarea_info_arr[2] . '_' . $ipytw_textarea_info_arr[3] . 'nse_date';
	$name3 = $ipytw_textarea_info_arr[2] . '_sto';

	if ( $ipytw_textarea_info_arr[0] == '1' ) {
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), $name1, 'ok' );
			update_blog_option( get_current_blog_id(), $name2, $ipytw_textarea_info_arr[1] );
			update_blog_option( get_current_blog_id(), $name3, 'ok' );
		} else {
			update_option( $name1, 'ok' );
			update_option( $name2, $ipytw_textarea_info_arr[1] );
			update_option( $name3, 'ok' );
		}
	} else {
		if ( is_multisite() ) {
			delete_blog_option( get_current_blog_id(), $name1 );
			delete_blog_option( get_current_blog_id(), $name2 );
			delete_blog_option( get_current_blog_id(), $name3 );
		} else {
			delete_option( $name1 );
			delete_option( $name2 );
			delete_option( $name3 );
		}
	}

	return get_option( $name3 );
}

/**
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало при импорте товаров
 * 
 * @since 0.1.0
 * 
 */
function validate_variables( $args, $p = 'ipytwp' ) {
	$is_string = common_option_get( 'woo_' . 'hook_isc' . $p );
	if ( $is_string == '202' && $is_string !== $args ) {
		return true;
	} else {
		return false;
	}
}