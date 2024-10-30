<?php
/**
 * Print Debug page
 * 
 * @version 3.0.0 (25-09-2023)
 * @see     
 * @package 
 * 
 * @hooks list: ipytw_debug-page-container-1
 *              ipytw_debug-page-container-2
 *              ipytw_debug-page-container-3
 *              ipytw_debug-page-container-4
 *              ipytw_feedback_block
 *              ipytw_before_support_project // TODO: depricated
 * 
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="wrap">
	<h1>
		<?php
		printf( '%s v. %s', __( 'Debug page', 'import-from-yml' ), esc_html( univ_option_get( 'ipytw_version' ) ) );
		?>
	</h1>
	<?php do_action( 'my_admin_notices' ); ?>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-logs.php'; ?>
					<?php do_action( 'ipytw_debug-page-container-1' ); ?>
				</div>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-simulation.php'; ?>
					<?php do_action( 'ipytw_debug-page-container-2' ); ?>
				</div>
			</div>
			<div id="postbox-container-3" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-debug-page-block-possible-problems.php'; ?>
					<?php include_once __DIR__ . '/html-admin-debug-page-block-sandbox.php'; ?>
					<?php do_action( 'ipytw_debug-page-container-3' ); ?>
				</div>
			</div>
			<div id="postbox-container-4" class="postbox-container">
				<div class="meta-box-sortables">
					<?php
					do_action( 'ipytw_before_support_project' ); // TODO: depricated
					do_action( 'ipytw_feedback_block' );
					do_action( 'ipytw_debug-page-container-4' );
					?>
				</div>
			</div>
		</div>
	</div>
</div>