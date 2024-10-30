<?php defined( 'ABSPATH' ) || exit;
/**
 * The class return the Extensions page of the plugin Import from YML
 *
 * @package                 iCopyDoc Plugins (ICPD)
 * @subpackage              Import from YML
 * @since                   0.1.0
 * 
 * @version                 2.0.4 (24-07-2023)
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
 *                          constants:  XFAVI_PLUGIN_DIR_URL
 *                          options:    
 */

class IPYTW_Extensions_Page {
	public function __construct() {
		$this->init_classes();
		$this->init_hooks();

		$this->print_extensions_page();
	}

	/**
	 * Init classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Init hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// наш класс, вероятно, вызывается во время срабатывания хука admin_menu.
		// admin_init - следующий в очереди срабатывания, на хуки раньше admin_menu нет смысла вешать
		// add_action('admin_init', [ $this, 'my_func' ], 10, 1);
		return;
	}

	/**
	 * Print extensions page
	 * 
	 * @return void
	 */
	public function print_extensions_page() {
		$view_arr = [];
		include_once __DIR__ . '/views/html-extensions-page.php';
	}
}