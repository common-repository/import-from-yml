<?php
/**
 * Print Sandbox block
 * 
 * @version 3.0.0 (25-09-2023)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="postbox">
	<h2 class="hndle">
		<?php _e( 'Sandbox', 'import-from-yml' ); ?>
	</h2>
	<div class="inside">
		<?php
		require_once IPYTW_PLUGIN_DIR_PATH . '/sandbox.php';
		try {
			ipytw_run_sandbox();
		} catch (Exception $e) {
			echo 'Exception: ', $e->getMessage(), "\n";
		}
		?>
	</div>
</div>