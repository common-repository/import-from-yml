<?php defined( 'ABSPATH' ) || exit;
/**
 * Plugin Name: Import from YML
 * Plugin URI: https://icopydoc.ru/category/documentation/import-from-yml/ 
 * Description: Import products from YML to WooCommerce
 * Version: 3.1.6
 * Requires at least: 4.5
 * Requires PHP: 7.4.0
 * Author: Maxim Glazunov
 * Author URI: https://icopydoc.ru
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: import-from-yml
 * Domain Path: /languages
 * Tags: yml, yandex, import, export, woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 9.2.3
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * Copyright 2018-2024 (Author emails: djdiplomat@yandex.ru, support@icopydoc.ru)
 */

$nr = false;
// Check php version
if ( version_compare( phpversion(), '7.4.0', '<' ) ) { // не совпали версии
	add_action( 'admin_notices', function () {
		warning_notice( 'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">%1$s</strong> %2$s 7.4.0 %3$s %4$s',
				'Import from YML',
				__( 'plugin requires a php version of at least', 'import-from-yml' ),
				__( 'You have the version installed', 'import-from-yml' ),
				phpversion()
			)
		);
	} );
	$nr = true;
}

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if ( ! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) ) )
	&& ! ( is_multisite()
		&& array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', [] ) ) )
) {
	add_action( 'admin_notices', function () {
		warning_notice(
			'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">Import from YML</strong> %1$s',
				__( 'requires WooCommerce installed and activated', 'import-from-yml' )
			)
		);
	} );
	$nr = true;
} else {
	// поддержка HPOS
	add_action( 'before_woocommerce_init', function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
}

if ( ! function_exists( 'warning_notice' ) ) {
	/**
	 * Display a notice in the admin Plugins page. Usually used in a @hook 'admin_notices'
	 * 
	 * @since 0.1.0
	 * 
	 * @param string $class - Optional
	 * @param string $message - Optional
	 * 
	 * @return void
	 */
	function warning_notice( $class = 'notice', $message = '' ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}

// Define constants
define( 'IPYTW_PLUGIN_VERSION', '3.1.6' );

$upload_dir = wp_get_upload_dir();
// http://site.ru/wp-content/uploads
define( 'IPYTW_SITE_UPLOADS_URL', $upload_dir['baseurl'] );

// /home/site.ru/public_html/wp-content/uploads
define( 'IPYTW_SITE_UPLOADS_DIR_PATH', $upload_dir['basedir'] );

// http://site.ru/wp-content/uploads/import-from-yml
define( 'IPYTW_PLUGIN_UPLOADS_DIR_URL', $upload_dir['baseurl'] . '/import-from-yml' );

// /home/site.ru/public_html/wp-content/uploads/import-from-yml
define( 'IPYTW_PLUGIN_UPLOADS_DIR_PATH', $upload_dir['basedir'] . '/import-from-yml' );
unset( $upload_dir );

// http://site.ru/wp-content/plugins/import-from-yml/
define( 'IPYTW_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

// /home/p135/www/site.ru/wp-content/plugins/import-from-yml/
define( 'IPYTW_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

// /home/p135/www/site.ru/wp-content/plugins/import-from-yml/import-from-yml.php
define( 'IPYTW_PLUGIN_MAIN_FILE_PATH', __FILE__ );

// import-from-yml - псевдоним плагина
define( 'IPYTW_PLUGIN_SLUG', wp_basename( dirname( __FILE__ ) ) );

// import-from-yml/import-from-yml.php - полный псевдоним плагина (папка плагина + имя главного файла)
define( 'IPYTW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// $nr = apply_filters('ipytw_f_nr', $nr);

// load translation
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'import-from-yml', false, dirname( IPYTW_PLUGIN_BASENAME ) . '/languages/' );
} );

if ( false === $nr ) {
	unset( $nr );
	require_once IPYTW_PLUGIN_DIR_PATH . '/packages.php';
	register_activation_hook( __FILE__, [ 'ImportProductsYMLtoWooCommerce', 'on_activation' ] );
	register_deactivation_hook( __FILE__, [ 'ImportProductsYMLtoWooCommerce', 'on_deactivation' ] );
	add_action( 'plugins_loaded', [ 'ImportProductsYMLtoWooCommerce', 'init' ], 10 ); // активируем плагин
	define( 'IPYTW_ACTIVE', true );
}