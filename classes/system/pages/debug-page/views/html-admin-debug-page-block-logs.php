<?php
/**
 * Print Debug page
 * 
 * @version 3.1.1 (22-01-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['keeplogs']
 * @param $view_arr['input_name_keeplogs']
 * @param $view_arr['submit_name_clear_logs']
 * @param $view_arr['input_name_disable_notices']
 * @param $view_arr['disable_notices']
 * @param $view_arr['nonce_action_debug_page']
 * @param $view_arr['nonce_field_debug_page']
 * @param $view_arr['submit_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="postbox">
	<h2 class="hndle">
		<?php _e( 'Logs', 'import-from-yml' ); ?>
	</h2>
	<div class="inside">
		<?php if ( $view_arr['keeplogs'] === 'on' ) {
			printf( '<p><strong>%1$s:</strong><br /><a target="_blank" href="%2$s%3$s">%4$s%3$s</a></p>',
				__( 'Log-file here', 'import-from-yml' ),
				IPYTW_PLUGIN_UPLOADS_DIR_URL,
				'/import-from-yml.log',
				IPYTW_PLUGIN_UPLOADS_DIR_PATH
			);
		} ?>
		<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $view_arr['input_name_keeplogs'] ); ?>">
								<?php _e( 'Keep logs', 'import-from-yml' ); ?>
							</label><br />
							<input class="button" type="submit"
								id="<?php echo esc_attr( $view_arr['submit_name_clear_logs'] ); ?>"
								name="<?php echo esc_attr( $view_arr['submit_name_clear_logs'] ); ?>"
								value="<?php _e( 'Clear logs', 'import-from-yml' ); ?>" />
						</th>
						<td class="overalldesc">
							<input name="<?php echo esc_attr( $view_arr['input_name_keeplogs'] ); ?>" type="checkbox"
								<?php checked( $view_arr['keeplogs'], 'on' ); ?>
								id="<?php echo esc_attr( $view_arr['input_name_keeplogs'] ); ?>" /><br />
							<span class="description">
								<?php _e( 'Do not check this box if you are not a developer', 'import-from-yml' ); ?>!
							</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $view_arr['input_name_disable_notices'] ); ?>">
								<?php _e( 'Disable notices', 'import-from-yml' ); ?>
							</label>
						</th>
						<td class="overalldesc">
							<input name="<?php echo esc_attr( $view_arr['input_name_disable_notices'] ); ?>"
								type="checkbox" <?php checked( $view_arr['disable_notices'], 'on' ); ?>
								id="<?php echo esc_attr( $view_arr['input_name_disable_notices'] ); ?>" /><br />
							<span class="description">
								<?php _e( 'Disable import notification from YML file', 'import-from-yml' ); ?>!
							</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="button-primary"></label></th>
						<td class="overalldesc">
							<?php wp_nonce_field( $view_arr['nonce_action_debug_page'], $view_arr['nonce_field_debug_page'] ); ?>
							<input id="button-primary" class="button-primary" type="submit"
								name="<?php echo esc_attr( $view_arr['submit_name'] ); ?>"
								value="<?php _e( 'Save', 'import-from-yml' ); ?>" /><br />
							<span class="description">
								<?php _e( 'Click to save the settings', 'import-from-yml' ); ?>
							</span>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>