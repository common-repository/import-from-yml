<?php
/**
 * Print Add New Feed button
 * 
 * @version 3.1.5 (29-08-2024)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit;

$feed_list_table = new IPYTW_Settings_Page_Feeds_WP_List_Table(); ?>
<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( 'ipytw_nonce_action_add_new_feed', 'ipytw_nonce_field_add_new_feed' ); ?>
	<input class="button" type="submit" name="ipytw_submit_add_new_feed"
		value="<?php esc_attr_e( 'Add New Feed', 'import-from-yml' ); ?>" />
</form>
<?php $feed_list_table->print_html_form();