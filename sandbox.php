<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function
 * 
 * @since 0.1.0
 * @version 3.1.6 (31-08-2024)
 *
 * @return void
 */
function ipytw_run_sandbox() {
	$x = false; // установите true, чтобы использовать песочницу
	if ( true === $x ) {
		printf( '%s<br/>',
			esc_html__( __( 'The sandbox is working. The result will appear below', 'import-from-yml' ) )
		);
		$time_start = microtime( true );
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price();
		// $product->set_sale_price( '' );
		// $product->save();


		/* дальше не редактируем */
		$time_end = microtime( true );
		$time = $time_end - $time_start;
		printf( '<br/>%s<br/>%s %d %s',
			esc_html__( __( 'The sandbox is working correctly', 'import-from-yml' ) ),
			esc_html__( __( 'The execution time of the test script was', 'import-from-yml' ) ),
			esc_html( $time ),
			esc_html__( __( 'seconds', 'import-from-yml' ) )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( __( 'The sandbox is not active. To activate, edit the file', 'import-from-yml' ) )
		);
	}
}