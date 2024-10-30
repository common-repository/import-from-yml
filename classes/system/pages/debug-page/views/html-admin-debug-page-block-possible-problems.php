<?php
/**
 * Print Possible problems block
 * 
 * @version 3.0.0 (25-09-2023)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="postbox">
	<h2 class="hndle">
		<?php _e( 'Possible problems', 'import-from-yml' ); ?>
	</h2>
	<div class="inside">
		<?php
		$possible_problems_arr = IPYTW_Debug_Page::get_possible_problems_list();
		if ( $possible_problems_arr[1] > 0 ) {
			printf( '<ol>%s</ol>', $possible_problems_arr[0] );
		} else {
			printf( '<p>%s</p>',
				__( 'Self-diagnosis functions did not reveal potential problems', 'import-from-yml' )
			);
		}
		?>
	</div>
</div>