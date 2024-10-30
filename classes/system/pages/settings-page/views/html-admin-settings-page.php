<?php
/**
 * Settings page
 * 
 * @version 3.0.0 (25-09-2023)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1>
		<?php _e( 'Import products from YML to WooCommerce', 'import-from-yml' ); ?>
	</h1>
	<div id="poststuff">
		<?php include_once __DIR__ . '/html-admin-settings-page-feeds-list.php'; ?>
		<div id="post-body" class="columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php
					
					do_action( 'ipytw_activation_forms' );

					do_action( 'ipytw_feedback_block' );

					do_action( 'ipytw_before_container_1', $view_arr['feed_id'] );

					do_action( 'ipytw_between_container_1', $view_arr['feed_id'] );

					do_action( 'ipytw_append_container_1', $view_arr['feed_id'] );
					?>
				</div>
			</div><!-- /postbox-container-1 -->

			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					<?php include_once __DIR__ . '/html-admin-settings-page-tabs.php'; ?>

					<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
						enctype="multipart/form-data">
						<input type="hidden" name="ipytw_feed_id_for_save"
							value="<?php echo esc_attr( $view_arr['feed_id'] ); ?>">
						<?php
						switch ( $view_arr['tab_name'] ) {
							case 'tags_settings_tab':
								include_once __DIR__ . '/html-admin-settings-page-tab-tags.php';
								break;
							default:
								$html_template = __DIR__ . '/html-admin-settings-page-tab-another.php';
								$html_template = apply_filters( 'ipytw_f_html_template_tab',
									$html_template,
									[ 
										'tab_name' => $view_arr['tab_name'],
										'view_arr' => $view_arr
									]
								);
								include_once $html_template;
						}

						do_action( 'ipytw_between_container_2', $view_arr['feed_id'] );

						include_once __DIR__ . '/html-admin-settings-page-save-btn.php';
						?>
					</form>
				</div>
			</div><!-- /postbox-container-2 -->

		</div>
	</div><!-- /poststuff -->
	<?php
	do_action( 'print_view_html_icp_banners', 'ipytw' );
	do_action( 'print_view_html_icpd_my_plugins_list', 'ipytw' );
	?>
</div>