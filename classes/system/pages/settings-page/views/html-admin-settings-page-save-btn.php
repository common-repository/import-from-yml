<?php
/**
 * Print the Save button
 * 
 * @version 3.1.5 (29-08-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

if ( $view_arr['tab_name'] === 'no_submit_tab' ) {
	return;
}
?>
<div class="postbox">
	<div class="inside">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="button-primary"></label></th>
					<td class="overalldesc">
						<?php wp_nonce_field( 'ipytw_nonce_action', 'ipytw_nonce_field' ); ?>
						<input id="button-primary" class="button-primary" name="ipytw_submit_action" type="submit"
							value="<?php
							if ( $view_arr['tab_name'] === 'main_tab' ) {
								printf( '%s & %s',
									esc_attr__( 'Save', 'import-from-yml' ),
									esc_attr__( 'Run import', 'import-from-yml' )
								);
							} else {
								esc_attr_e( 'Save', 'import-from-yml' );
							}
							?>" /><br />
						<span class="description">
							<small>
								<?php esc_html_e( 'Click to save the settings', 'import-from-yml' ); ?>
							</small>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>