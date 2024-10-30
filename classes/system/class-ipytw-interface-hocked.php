<?php
/**
 * This class is responsible for the plugin interface Import from YML
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param         
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

final class IPYTW_Interface_Hoocked {
	/**
	 * This class is responsible for the plugin interface Import from YML
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_classes();
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_to_product_sync_info' ], 99, 1 );
		add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'add_to_product_sync_info' ], 99, 3 );
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Function for `woocommerce_product_options_general_product_data` action-hook 
	 * and `woocommerce_product_options_inventory_product_data` action-hook 
	 * 
	 * @return void
	 */
	public function add_to_product_sync_info() {
		global $product, $post;

		if ( get_post_meta( $post->ID, '_ipytw_feed_product_id', true ) !== '' ) {
			$feed_product_id = get_post_meta( $post->ID, '_ipytw_feed_product_id', true );
		}

		if ( get_post_meta( $post->ID, '_ipytw_feed_id', true ) !== '' ) {
			$feed_id = get_post_meta( $post->ID, '_ipytw_feed_id', true );
		}

		if ( get_post_meta( $post->ID, '_ipytw_date_last_import', true ) !== '' ) {
			$date_last_import = get_post_meta( $post->ID, '_ipytw_date_last_import', true );
		}

		if ( ! empty( $feed_product_id ) && ! empty( $feed_id ) && ! empty( $feed_product_id ) ) {
			printf( '<div class="hide-if-no-js"><p>%1$s: "%2$s" (%3$s). %4$s: "%5$s"</p></div>',
				esc_html__( 'The product was imported from the feed ID', 'import-from-yml' ),
				esc_html__( $feed_id ),
				esc_html__( $date_last_import ),
				esc_html__( 'His ID in this feed', 'import-from-yml' ),
				esc_html__( $feed_product_id )
			);
		}
	}
} // end class IPYTW_Interface_Hoocked