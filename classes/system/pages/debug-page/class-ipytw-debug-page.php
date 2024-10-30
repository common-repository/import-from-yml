<?php
/**
 * Plugin Debug Page
 *
 * @package                 iCopyDoc Plugins (v1, core 16-08-2023)
 * @subpackage              Import from YML
 * @since                   0.1.0
 * 
 * @version                 3.0.0 (25-09-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param          string   $pref      
 *
 * @depends                 classes:    ICPD_Set_Admin_Notices
 *                          traits:     
 *                          methods:    
 *                          functions:  ipytw_run_sandbox
 *                                      univ_option_get
 *                          constants:  IPYTW_PLUGIN_UPLOADS_DIR_PATH
 *                                      IPYTW_PLUGIN_UPLOADS_DIR_URL
 *                                      IPYTW_PLUGIN_DIR_PATH
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Debug_Page {
	/**
	 * Prefix
	 * @var string
	 */
	private $pref = 'ipytw';

	/**
	 * Resust of simulation
	 * @var string
	 */
	private $simulation_result = '';

	/**
	 * Summary of simulation_result_report
	 * @var string
	 */
	private $simulation_result_report = '';

	/**
	 * Summary of __construct
	 * 
	 * @param mixed $pref
	 */
	public function __construct( $pref = null ) {
		if ( $pref ) {
			$this->pref = $pref;
		}

		$this->init_classes();
		$this->init_hooks();
		$this->listen_submit();
		$this->print_html_settings_page();
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
	 * Print HTML Settings page
	 * 
	 * @return void
	 */
	public function print_html_settings_page() {
		$ipytw_keeplogs = univ_option_get( $this->get_input_name_keeplogs() );
		$ipytw_disable_notices = univ_option_get( $this->get_input_name_disable_notices() );
		$view_arr = [ 
			'keeplogs' => $ipytw_keeplogs,
			'disable_notices' => $ipytw_disable_notices,
			'input_name_keeplogs' => $this->get_input_name_keeplogs(),
			'input_name_disable_notices' => $this->get_input_name_disable_notices(),
			'submit_name_clear_logs' => $this->get_submit_name_clear_logs(),
			'nonce_action_debug_page' => $this->get_nonce_action_debug_page(),
			'nonce_field_debug_page' => $this->get_nonce_field_debug_page(),
			'submit_name' => $this->get_submit_name(),
			'simulation_result' => $this->get_simulation_result(),
			'simulation_result_report' => $this->get_simulation_result_report()
		];
		if ( isset( $_POST['ipytw_feed_id'] ) ) {
			$view_arr['feed_id'] = sanitize_text_field( $_POST['ipytw_feed_id'] );
		} else {
			$view_arr['feed_id'] = '1';
		}
		if ( isset( $_POST['ipytw_simulated_post_id'] ) ) {
			$view_arr['simulated_post_id'] = sanitize_text_field( $_POST['ipytw_simulated_post_id'] );
		} else {
			$view_arr['simulated_post_id'] = '';
		}
		include_once __DIR__ . '/views/html-admin-debug-page.php';
	}

	/**
	 * Print html options tags for request simulation
	 * 
	 * @return void
	 */
	public static function print_html_options() {
		if ( is_multisite() ) {
			$cur_blog_id = get_current_blog_id();
		} else {
			$cur_blog_id = '0';
		}
		if ( isset( $_POST['ipytw_feed_id'] ) ) {
			$cur_feed_id = sanitize_text_field( $_POST['ipytw_feed_id'] );
		} else {
			$cur_feed_id = '1';
		}
		$ipytw_settings_arr = univ_option_get( 'ipytw_settings_arr' );
		$ipytw_settings_arr_keys_arr = array_keys( $ipytw_settings_arr );
		for ( $i = 0; $i < count( $ipytw_settings_arr_keys_arr ); $i++ ) {
			$feed_id = (string) $ipytw_settings_arr_keys_arr[ $i ];
			if ( $ipytw_settings_arr[ $feed_id ]['ipytw_feed_assignment'] === '' ) {
				$feed_assignment = '';
			} else {
				$feed_assignment = sprintf( ' (%s)',
					$ipytw_settings_arr[ $feed_id ]['ipytw_feed_assignment']
				);
			}

			printf( '<option value="%s" %s>%s %s: feed-xml-%s.xml%s</option>',
				$feed_id,
				selected( $cur_feed_id, $feed_id, false ),
				__( 'Feed', 'import-from-yml' ),
				$feed_id,
				$cur_blog_id,
				$feed_assignment
			);

		}
	}

	/**
	 * Summary of get_possible_problems_list
	 * 
	 * @return array
	 */
	public static function get_possible_problems_list() {
		$possibleProblems = '';
		$possibleProblemsCount = 0;
		$conflictWithPlugins = 0;
		$conflictWithPluginsList = '';
		if ( is_plugin_active( 'snow-storm/snow-storm.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Snow Storm<br/>';
		}
		if ( is_plugin_active( 'email-subscribers/email-subscribers.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'saphali-search-castom-filds/saphali-search-castom-filds.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'W3 Total Cache<br/>';
		}
		if ( is_plugin_active( 'docket-cache/docket-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Docket Cache<br/>';
		}
		if ( class_exists( 'MPSUM_Updates_Manager' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Easy Updates Manager<br/>';
		}
		if ( class_exists( 'OS_Disable_WordPress_Updates' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Disable All WordPress Updates<br/>';
		}
		if ( $conflictWithPlugins > 0 ) {
			$possibleProblemsCount++;
			$possibleProblems .= sprintf( '<li>
				<p>%1$s: Import from YML</p>
				%2$s
				<p>%3$s: <a href="mailto:%4$s">%4$s</a>.</p>
				</li>',
				__( 'Most likely, these plugins negatively affect the operation of', 'import-from-yml' ),
				$conflictWithPluginsList,
				__(
					'If you are a developer of one of the plugins from the list above, please contact me',
					'import-from-yml'
				),
				'mailto:support@icopydoc.ru'
			);
		}
		return [ $possibleProblems, $possibleProblemsCount, $conflictWithPlugins, $conflictWithPluginsList ];
	}

	/**
	 * Summary of get_pref
	 * 
	 * @return mixed|string
	 */
	private function get_pref() {
		return $this->pref;
	}

	/**
	 * Summary of get_input_name_keeplogs
	 * 
	 * @return string
	 */
	private function get_input_name_keeplogs() {
		return $this->get_pref() . '_keeplogs';
	}

	/**
	 * Summary of get_input_name_disable_notices
	 * 
	 * @return string
	 */
	private function get_input_name_disable_notices() {
		return $this->get_pref() . '_disable_notices';
	}

	/**
	 * Summary of get_submit_name
	 * 
	 * @return string
	 */
	private function get_submit_name() {
		return $this->get_pref() . '_submit_debug_page';
	}

	/**
	 * Summary of get_nonce_action_debug_page
	 * 
	 * @return string
	 */
	private function get_nonce_action_debug_page() {
		return $this->get_pref() . '_nonce_action_debug_page';
	}

	/**
	 * Summary of get_nonce_field_debug_page
	 * 
	 * @return string
	 */
	private function get_nonce_field_debug_page() {
		return $this->get_pref() . '_nonce_field_debug_page';
	}

	/**
	 * Summary of get_submit_name_clear_logs
	 * 
	 * @return string
	 */
	private function get_submit_name_clear_logs() {
		return $this->get_pref() . '_submit_clear_logs';
	}

	/**
	 * Summary of get_simulation_result
	 * 
	 * @return string
	 */
	private function get_simulation_result() {
		return $this->simulation_result;
	}

	/**
	 * Summary of get_simulation_result_report
	 * 
	 * @return string
	 */
	private function get_simulation_result_report() {
		return $this->simulation_result_report;
	}

	/**
	 * Summary of listen_submit
	 * 
	 * @return void
	 */
	private function listen_submit() {
		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			$this->save_data();
			$message = __( 'Updated', 'import-from-yml' );
			$class = 'notice-success';

			echo new ICPD_Set_Admin_Notices( $message, $class ); // ? приходится юзать echo, видимо хук работает позже
		}

		if ( isset( $_REQUEST[ $this->get_submit_name_clear_logs()] ) ) {
			$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/import-from-yml.log';
			if ( file_exists( $filename ) ) {
				$res = unlink( $filename );
			} else {
				$res = false;
			}
			if ( true == $res ) {
				$message = __( 'Logs were cleared', 'import-from-yml' );
				$class = 'notice-success';
			} else {
				$message = __(
					'Error accessing log file. The log file may have been deleted previously',
					'import-from-yml'
				);
				$class = 'notice-warning';
			}
			echo new ICPD_Set_Admin_Notices( $message, $class ); // ? приходится юзать echo, видимо хук работает позже
		}


		if ( isset( $_POST['ipytw_feed_id'] ) ) {
			$ipytw_feed_id = sanitize_text_field( $_POST['ipytw_feed_id'] );
		} else {
			$ipytw_feed_id = '1';
		}
		if ( isset( $_POST['ipytw_simulated_post_id'] ) ) {
			$ipytw_simulated_post_id = sanitize_text_field( $_POST['ipytw_simulated_post_id'] );
		} else {
			$ipytw_simulated_post_id = '';
		}
		if ( isset( $_POST['ipytw_textarea_info'] ) ) {
			$ipytw_textarea_info = sanitize_text_field( $_POST['ipytw_textarea_info'] );
		} else {
			$ipytw_textarea_info = '';
		}
		if ( isset( $_POST['ipytw_textarea_res'] ) ) {
			$ipytw_textarea_res = sanitize_text_field( $_POST['ipytw_textarea_res'] );
		} else {
			$ipytw_textarea_res = '';
		}
		if ( $ipytw_textarea_res == 'calibration' ) {
			$resust_report .= ipytw_calibration( $ipytw_textarea_info );
		}
		if ( isset( $_REQUEST['ipytw_submit_simulated'] ) ) {
			if ( ! empty( $_POST )
				&& check_admin_referer( 'ipytw_nonce_action_simulated', 'ipytw_nonce_field_simulated' ) ) {
				// TODO: Сделать симуляцию запроса
			}
		}

		return;
	}

	/**
	 * Summary of save_data
	 * 
	 * @return void
	 */
	private function save_data() {
		if ( ! empty( $_POST )
			&& check_admin_referer( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ) ) {
			if ( isset( $_POST[ $this->get_input_name_keeplogs()] ) ) {
				$keeplogs = sanitize_text_field( $_POST[ $this->get_input_name_keeplogs()] );
			} else {
				$keeplogs = '';
			}
			if ( isset( $_POST[ $this->get_input_name_disable_notices()] ) ) {
				$disable_notices = sanitize_text_field( $_POST[ $this->get_input_name_disable_notices()] );
			} else {
				$disable_notices = '';
			}
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'ipytw_keeplogs', $keeplogs );
				update_blog_option( get_current_blog_id(), 'ipytw_disable_notices', $disable_notices );
			} else {
				update_option( 'ipytw_keeplogs', $keeplogs );
				update_option( 'ipytw_disable_notices', $disable_notices );
			}
		}
		return;
	}
}