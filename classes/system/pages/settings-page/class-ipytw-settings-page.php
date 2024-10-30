<?php
/**
 * The class return the Settings page of the plugin Import from YML
 *
 * @package                 iCopyDoc Plugins (ICPD)
 * @subpackage              Import from YML
 * @since                   0.1.0
 * 
 * @version                 3.1.4 (28-07-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param                   
 *
 * @depends                 classes:    IPYTW_Data_Arr
 *                                      IPYTW_Error_Log 
 *                                      IPYTW_WP_List_Table
 *                                      IPYTW_Settings_Feed_WP_List_Table
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      ipytw_optionGET
 *                                      ipytw_optionUPD
 *                                      ipytw_optionDEL
 *                          constants:  IPYTW_PLUGIN_UPLOADS_DIR_PATH
 *                          options:     
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Settings_Page {
	/**
	 * Allowed HTML tags for use in wp_kses()
	 */
	const ALLOWED_HTML_ARR = [ 
		'a' => [ 
			'href' => true,
			'title' => true,
			'target' => true,
			'class' => true,
			'style' => true
		],
		'br' => [ 'class' => true ],
		'i' => [ 'class' => true ],
		'small' => [ 'class' => true ],
		'strong' => [ 'class' => true, 'style' => true ],
		'p' => [ 'class' => true, 'style' => true ]
	];

	/**
	 * Feed ID
	 * @var string
	 */
	private $feed_id;
	/**
	 * The value of the current tab
	 * @var string
	 */
	private $cur_tab = 'main_tab';

	/**
	 * The class return the Settings page of the plugin Import from YML
	 */
	public function __construct() {
		if ( isset( $_GET['feed_id'] ) ) {
			$this->feed_id = sanitize_text_field( $_GET['feed_id'] );
		} else {
			if ( empty( get_first_feed_id() ) ) {
				$this->feed_id = '1';
			} else {
				$this->feed_id = get_first_feed_id();
			}
		}
		if ( isset( $_GET['tab'] ) ) {
			$this->cur_tab = sanitize_text_field( $_GET['tab'] );
		}

		$this->init_classes();
		$this->init_hooks();
		$this->listen_submit();

		$this->print_view_html_form();
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Initialization hooks
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
	 * The function listens for the send buttons
	 * 
	 * @return void
	 */
	private function listen_submit() {
		//$feed_id = get_first_feed_id();

		// массовое удаление фидов по чекбоксу checkbox_yml_file
		if ( isset( $_GET['ipytw_form_id'] ) && ( $_GET['ipytw_form_id'] === 'ipytw_wp_list_table' ) ) {
			if ( is_array( $_GET['checkbox_yml_file'] ) && ! empty( $_GET['checkbox_yml_file'] ) ) {
				if ( $_GET['action'] === 'delete' || $_GET['action2'] === 'delete' ) {
					$checkbox_yml_file_arr = $_GET['checkbox_yml_file'];
					$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
					for ( $i = 0; $i < count( $checkbox_yml_file_arr ); $i++ ) {
						$feed_id = $checkbox_yml_file_arr[ $i ];
						unset( $ipytw_settings_arr[ $feed_id ] );
						wp_clear_scheduled_hook( 'ipytw_cron_period', [ $feed_id ] ); // отключаем крон
						wp_clear_scheduled_hook( 'ipytw_cron_sborki', [ $feed_id ] ); // отключаем крон
						$name_dir = IPYTW_SITE_UPLOADS_DIR_PATH . '/import-from-yml';
						$filename = $name_dir . '/feed-imported-offers-' . $i . '.tmp';
						$res = unlink( $filename );
						$filename = $name_dir . '/feed-importetd-cat-ids-' . $i . '.tmp';
						$res = unlink( $filename );
						ipytw_optionDEL( 'ipytw_status_sborki', $i );
						ipytw_optionDEL( 'ipytw_last_element', $i );

						$ipytw_registered_feeds_arr = ipytw_optionGET( 'ipytw_registered_feeds_arr' );
						for ( $n = 1; $n < count( $ipytw_registered_feeds_arr ); $n++ ) {
							// первый элемент не проверяем, тк. там инфо по последнему id
							if ( $ipytw_registered_feeds_arr[ $n ]['id'] === $feed_id ) {
								unset( $ipytw_registered_feeds_arr[ $n ] );
								$ipytw_registered_feeds_arr = array_values( $ipytw_registered_feeds_arr );
								ipytw_optionUPD( 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
								break;
							}
						}
					}
					ipytw_optionUPD( 'ipytw_settings_arr', $ipytw_settings_arr );
					/* ! */
					$feed_id = get_first_feed_id();
				}

				if ( $_GET['action'] === 'delete_all_imported_products' || $_GET['action2'] === 'delete_all_imported_products' ) {
					$checkbox_yml_file_arr = $_GET['checkbox_yml_file'];
					for ( $i = 0; $i < count( $checkbox_yml_file_arr ); $i++ ) {
						$feed_id = $checkbox_yml_file_arr[ $i ];
						ipytw_delete_all_imported_products( $feed_id );
					}
				}
			}
		}

		if ( isset( $_REQUEST['ipytw_submit_add_new_feed'] ) ) { // если создаём новый фид
			if ( ! empty( $_POST ) && check_admin_referer( 'ipytw_nonce_action_add_new_feed', 'ipytw_nonce_field_add_new_feed' ) ) {
				$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );

				if ( is_multisite() ) {
					$ipytw_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'ipytw_registered_feeds_arr' );
					$feed_id = $ipytw_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$ipytw_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$ipytw_registered_feeds_arr[] = array( 'id' => (string) $feed_id );
					update_blog_option( get_current_blog_id(), 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
				} else {
					$ipytw_registered_feeds_arr = get_option( 'ipytw_registered_feeds_arr' );
					$feed_id = $ipytw_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$ipytw_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$ipytw_registered_feeds_arr[] = array( 'id' => (string) $feed_id );
					update_option( 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
				}
				$name_dir = IPYTW_SITE_UPLOADS_DIR_PATH . '/import-from-yml/feed' . $feed_id;
				if ( ! is_dir( $name_dir ) ) {
					if ( ! mkdir( $name_dir ) ) {
						error_log( 'ERROR: Ошибка создания папки ' . $name_dir . '; Файл: export.php; Строка: ' . __LINE__, 0 );
					}
				}

				$ipytw_data_arr_obj = new IPYTW_Data_Arr();
				$opts_arr = $ipytw_data_arr_obj->get_opts_name_and_def_date( 'all' );

				$ipytw_settings_arr[ $feed_id ] = $opts_arr;
				ipytw_optionUPD( 'ipytw_settings_arr', $ipytw_settings_arr );

				ipytw_optionADD( 'ipytw_status_sborki', '-1', $feed_id );
				ipytw_optionADD( 'ipytw_last_element', '-1', $feed_id );
				printf( '<div class="updated notice notice-success is-dismissible"><p>%s. ID = %s.</p></div>',
					__( 'Feed added', 'import-from-yml' ),
					$feed_id
				);

				$this->feed_id = $feed_id;
			}
		}

		$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
		if ( isset( $_REQUEST['ipytw_submit_action'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'ipytw_nonce_action', 'ipytw_nonce_field' ) ) {
				$feed_id = sanitize_text_field( $_POST['ipytw_feed_id_for_save'] );
				do_action( 'ipytw_prepend_submit_action', $feed_id );

				$unixtime = (string) current_time( 'timestamp', 1 );
				common_option_upd( 'ipytw_date_save_set', $unixtime, 'no', $feed_id, 'ipytw' );

				$def_plugin_date_arr = new IPYTW_Data_Arr();
				$opts_name_and_def_date_arr = $def_plugin_date_arr->get_opts_name_and_def_date( 'public' );
				foreach ( $opts_name_and_def_date_arr as $opt_name => $value ) {
					$save_if_empty = 'no';
					$save_if_empty = apply_filters( 'ipytw_f_save_if_empty', $save_if_empty, [ 'opt_name' => $opt_name ] );
					$this->save_plugin_set( $opt_name, $feed_id, $save_if_empty );
				}
				$this->feed_id = $feed_id;

				$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
				$ipytw_settings_arr[ $feed_id ]['ipytw_url_yml_file'] = sanitize_url( $_POST['ipytw_url_yml_file'] );
				if ( ! empty( $_FILES['ipytw_image_upload'] ) ) {
					if ( $_FILES['ipytw_image_upload']['name'] !== '' ) {
						require_once ( ABSPATH . 'wp-admin/includes/image.php' );
						require_once ( ABSPATH . 'wp-admin/includes/file.php' );
						require_once ( ABSPATH . 'wp-admin/includes/media.php' );

						// Позволим WordPress перехватить загрузку. 
						// не забываем указать атрибут name поля input - 'ipytw_image_upload'
						$attachment_id = media_handle_upload( 'ipytw_image_upload', 0 ); // 0 - не прикреплятьк посту

						if ( is_wp_error( $attachment_id ) ) {
							printf(
								'<div class="error notice notice-error is-dismissible"><p>YML %1$s %2$s! %3$s</p></div>',
								__( 'feed', 'import-from-yml' ),
								__( 'upload error', 'import-from-yml' ),
								$attachment_id->get_error_message()
							);
						} else {
							$feed_url = wp_get_attachment_url( $attachment_id );
							$ipytw_settings_arr[ $feed_id ]['ipytw_url_yml_file'] = $feed_url;
						}
					}
				}
				$ipytw_settings_arr = apply_filters( 'ipytw_upd_settings_arr_filter', $ipytw_settings_arr, $feed_id );
				ipytw_optionUPD( 'ipytw_settings_arr', $ipytw_settings_arr );

				$ipytw_run_cron = sanitize_text_field( $_POST['ipytw_run_cron'] );

				$ipytw_settings_arr[ $feed_id ]['ipytw_status_cron'] = $ipytw_run_cron;
				if ( $ipytw_run_cron === 'disabled' ) {
					// отключаем крон
					wp_clear_scheduled_hook( 'ipytw_cron_period', [ $feed_id ] );
					$ipytw_settings_arr[ $feed_id ]['ipytw_status_cron'] = 'off';

					wp_clear_scheduled_hook( 'ipytw_cron_sborki', [ $feed_id ] );
					ipytw_optionUPD( 'ipytw_status_sborki', '-1', $feed_id );
					ipytw_optionUPD( 'ipytw_last_element', '-1', $feed_id );
					$ipytw_settings_arr[ $feed_id ]['ipytw_count_elements'] = -1;
				} else if ( $ipytw_run_cron === 'once' ) {
					// единоразовый импорт
					ipytw_optionUPD( 'ipytw_status_sborki', '-1', $feed_id );
					// ? в теории тут можно регулировать "продолжить импорт" или "с нуля"
					ipytw_optionUPD( 'ipytw_last_element', '-1', $feed_id );
					wp_clear_scheduled_hook( 'ipytw_cron_period', [ $feed_id ] );
					wp_schedule_single_event( time() + 5, 'ipytw_cron_period', [ $feed_id ] ); // старт через 5 сек
					new IPYTW_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
						'Единоразово ipytw_cron_period внесен в список заданий',
						$this->get_feed_id(),
						'class-ipytw-settings-page.php',
						__LINE__
					) );
				} else {
					ipytw_optionUPD( 'ipytw_status_sborki', '-1', $feed_id );
					// ? в теории тут можно регулировать "продолжить импорт" или "с нуля"
					ipytw_optionUPD( 'ipytw_last_element', '-1', $feed_id );
					wp_clear_scheduled_hook( 'ipytw_cron_period', [ $feed_id ] );
					wp_schedule_event( time() + 5, $ipytw_run_cron, 'ipytw_cron_period', [ $feed_id ] ); // старт через 5 сек
					new IPYTW_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
						'ipytw_cron_period внесен в список заданий',
						$this->get_feed_id(),
						'class-ipytw-settings-page.php',
						__LINE__
					) );
				}
				ipytw_optionUPD( 'ipytw_settings_arr', $ipytw_settings_arr );

				$this->feed_id = $feed_id;
			}
		}

		return;
	}

	/**
	 * Summary of print_view_html_form
	 * 
	 * @return void
	 */
	public function print_view_html_form() {
		$view_arr = [ 
			'feed_id' => $this->get_feed_id(),
			'tab_name' => $this->get_tab_name(),
			'tabs_arr' => $this->get_tabs_arr(),
			'prefix_feed' => $this->get_prefix_feed(),
			'current_blog_id' => $this->get_current_blog_id()
		];
		include_once __DIR__ . '/views/html-admin-settings-page.php';
	}

	/**
	 * Get tabs arr
	 * 
	 * @param string $current
	 * 
	 * @return array
	 */
	public function get_tabs_arr( $current = 'main_tab' ) {
		$tabs_arr = [ 
			'main_tab' => sprintf( '%s (%s: %s)',
				__( 'Main settings', 'import-from-yml' ),
				__( 'Feed', 'import-from-yml' ),
				$this->get_feed_id()
			)
		];
		$tabs_arr = apply_filters( 'ipytw_f_tabs_arr', $tabs_arr, [ 'feed_id' => $this->get_feed_id() ] );
		return $tabs_arr;
	}

	/**
	 * Summary of print_view_html_fields
	 * 
	 * @param string $tab
	 * 
	 * @return void
	 */
	public static function print_view_html_fields( $tab, $feed_id ) {
		$ipytw_data_arr_obj = new IPYTW_Data_Arr();
		$data_for_tab_arr = $ipytw_data_arr_obj->get_data_for_tabs( $tab ); // список дефолтных настроек

		for ( $i = 0; $i < count( $data_for_tab_arr ); $i++ ) {
			switch ( $data_for_tab_arr[ $i ]['type'] ) {
				case 'text':
					self::get_view_html_field_input( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'number':
					self::get_view_html_field_number( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'text_and_file_btn':
					self::get_view_html_field_text_and_file_btn( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'select':
					self::get_view_html_field_select( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'textarea':
					self::get_view_html_field_textarea( $data_for_tab_arr[ $i ], $feed_id );
					break;
				default:
					do_action( 'ipytw_f_print_view_html_fields', $data_for_tab_arr[ $i ], $feed_id );
			}
		}
	}

	/**
	 * Summary of get_view_html_field_input
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_input( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="text" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s"
							class="ipytw_input"
							style="%6$s" /><br />
						<span class="description"><small>%7$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'ipytw' ) ),
			esc_html( $data_arr['placeholder'] ),
			'width: 100%;',
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Summary of get_view_html_field_number
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_number( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['min'] ) ) {
			$min = $data_arr['min'];
		} else {
			$min = '';
		}
		if ( isset( $data_arr['max'] ) ) {
			$max = $data_arr['max'];
		} else {
			$max = '';
		}
		if ( isset( $data_arr['step'] ) ) {
			$step = $data_arr['step'];
		} else {
			$step = '';
		}

		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="number" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s" 
							min="%6$s"
							max="%7$s"
							step="%8$s"
							class="ipytw_input"
							/><br />
						<span class="description"><small>%9$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'ipytw' ) ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( $min ),
			esc_attr( $max ),
			esc_attr( $step ),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Summary of get_view_html_field_text_and_file_btn
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_text_and_file_btn( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		$f = common_option_get( $data_arr['opt_name'], false, $feed_id, 'ipytw' );
		if ( empty( $f ) ) {
			$readonly = '';
		} else {
			$readonly = 'readonly';
		}

		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							%7$s
							type="text" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s" 
							class="ipytw_input" /><br />
						<span class="description"><small>%6$s</small></span><br/>
						<input 
							type="file" 
							name="ipytw_image_upload" 
							id="ipytw_image_upload" 
							multiple="false"
							accept=".xml,.yml" />
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			esc_html( $data_arr['label'] ),
			esc_attr( $f ),
			esc_html( $data_arr['placeholder'] ),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR ),
			esc_attr( $readonly )
		);
	}

	/**
	 * Summary of get_view_html_field_select
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_select( $data_arr, $feed_id ) {
		if ( isset( $data_arr['key_value_arr'] ) ) {
			$key_value_arr = $data_arr['key_value_arr'];
		} else {
			$key_value_arr = [];
		}
		if ( isset( $data_arr['categories_list'] ) ) {
			$categories_list = $data_arr['categories_list'];
		} else {
			$categories_list = false;
		}
		if ( isset( $data_arr['tags_list'] ) ) {
			$tags_list = $data_arr['tags_list'];
		} else {
			$tags_list = false;
		}
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['size'] ) ) {
			$size = $data_arr['size'];
		} else {
			$size = '1';
		}
		// массивы храним отдельно от других параметров
		if ( isset( $data_arr['multiple'] ) && true === $data_arr['multiple'] ) {
			$multiple = true;
			$multiple_val = '[]" multiple';
			// ? нужна ли unserialize
			// $value = unserialize( ipytw_optionGET( $data_arr['opt_name'], $feed_id ) );
			// ? $value = maybe_unserialize( univ_option_get( $data_arr['opt_name'] . $feed_id ) );
			$value = ipytw_optionGET( $data_arr['opt_name'], $feed_id );
		} else {
			$multiple = false;
			$multiple_val = '"';
			$value = common_option_get(
				$data_arr['opt_name'],
				false,
				$feed_id,
				'ipytw' );
		}

		if ( $data_arr['opt_name'] === 'ipytwp_exclude_cat_arr' ) {
			if ( isset( $_GET['upd_cat_list'] ) ) {
				$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/' . $feed_id . '.xml';
				if ( ! file_exists( $filename ) ) {
					$import = new IPYTW_Import_XML( $feed_id );
					$import->copy_feed_from_source();
					unset( $import );
				}
				$xml_object = new IPYTW_XML_Parsing( $filename );
				$xml_object->set_pointer_to( 1, 'categories' );
				$xml_obj = $xml_object->get_outer_xml();
				// $ipytwp_list_cat_feed_arr = ipytwp_sync_list_cat_feed( $feed_id );
				$ipytwp_list_cat_feed_arr = sync_list_cat_feed( $xml_obj );
				if ( is_array( $ipytwp_list_cat_feed_arr ) ) {
					ipytw_optionUPD( 'ipytwp_list_cat_feed_arr', $ipytwp_list_cat_feed_arr, $feed_id );
				}
			}
			$btn = sprintf( '<a href="%1$s&upd_cat_list=true" class="page-title-action">%2$s</a><br />',
				$_SERVER['REQUEST_URI'],
				__( 'Download/Update category list', 'ipytwp' )
			);
		} else {
			$btn = '';
		}

		printf( '<tr class="%1$s">
				<th scope="row"><label for="%2$s">%3$s</label></th>
				<td class="overalldesc">
					%8$s
					<select name="%2$s%5$s id="%2$s" size="%4$s"/>%6$s</select><br />
					<span class="description"><small>%7$s</small></span>
				</td>
			</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $size ),
			$multiple_val,
			self::print_view_html_option_for_select(
				$value,
				$data_arr['opt_name'],
				[ 
					'woo_attr' => $data_arr['woo_attr'],
					'key_value_arr' => $key_value_arr,
					'categories_list' => $categories_list,
					'tags_list' => $tags_list,
					'multiple' => $multiple,
					'feed_id' => (string) $feed_id
				]
			),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR ),
			$btn
		);
	}

	/**
	 * Summary of print_view_html_option_for_select
	 * 
	 * @param mixed $opt_value
	 * @param string $opt_name
	 * @param array $params_arr
	 * @param mixed $res
	 * 
	 * @return mixed
	 */
	public static function print_view_html_option_for_select( $opt_value, string $opt_name, $params_arr = [], $res = '' ) {
		if ( true === $params_arr['multiple'] ) {
			if ( $opt_name === 'ipytwp_exclude_cat_arr' ) {
				$res .= sprintf( '<optgroup label="%s">', __( 'Categories', 'import-from-yml' ) );
				$ipytwp_list_cat_feed_arr = ipytw_optionGET( 'ipytwp_list_cat_feed_arr', $params_arr['feed_id'] );
				if ( ! is_array( $ipytwp_list_cat_feed_arr ) ) {
					$ipytwp_list_cat_feed_arr = [];
				}
				for ( $i = 0; $i < count( $ipytwp_list_cat_feed_arr ); $i++ ) {
					if ( is_array( $opt_value ) && in_array( $ipytwp_list_cat_feed_arr[ $i ]['id'], $opt_value ) ) {
						$selected = 'selected="true"';
					} else {
						$selected = '';
					}
					$res .= sprintf( '<option value="%s" %s>%s</option>',
						$ipytwp_list_cat_feed_arr[ $i ]['id'],
						$selected,
						// esc_attr( selected( $ipytwp_list_cat_feed_arr[ $i ]['id'], $opt_value, false ) ),
						$ipytwp_list_cat_feed_arr[ $i ]['name']
					);
				}
				$res .= '</optgroup>';
			} else {
				$woo_attributes_arr = get_woo_attributes();
				foreach ( $woo_attributes_arr as $attribute ) {
					if ( ! empty( $opt_value ) ) {
						foreach ( $opt_value as $value ) {
							if ( (string) $attribute['id'] == (string) $value ) {
								$selected = ' selected="select" ';
								break;
							} else {
								$selected = '';
							}
						}
					} else {
						$selected = '';
					}
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $attribute['id'] ),
						$selected,
						esc_attr( $attribute['name'] )
					);
				}
				unset( $woo_attributes_arr );
			}
		} else {
			if ( ! empty( $params_arr['key_value_arr'] ) ) {
				for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
						esc_attr( selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false ) ),
						esc_attr( $params_arr['key_value_arr'][ $i ]['text'] )
					);
				}
			}

			if ( ! empty( $params_arr['woo_attr'] ) ) {
				$woo_attributes_arr = get_woo_attributes();
				for ( $i = 0; $i < count( $woo_attributes_arr ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $woo_attributes_arr[ $i ]['id'] ),
						esc_attr( selected( $opt_value, $woo_attributes_arr[ $i ]['id'], false ) ),
						esc_attr( $woo_attributes_arr[ $i ]['name'] )
					);
				}
				unset( $woo_attributes_arr );
			}
		}

		return $res;
	}

	/**
	 * Summary of get_view_html_field_textarea
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public static function get_view_html_field_textarea( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['rows'] ) ) {
			$rows = $data_arr['rows'];
		} else {
			$rows = '6';
		}
		if ( isset( $data_arr['cols'] ) ) {
			$cols = $data_arr['cols'];
		} else {
			$cols = '32';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<textarea 							 
							name="%2$s" 
							id="%2$s" 
							rows="%4$s"
							cols="%5$s"
							placeholder="%6$s">%7$s</textarea><br />
						<span class="description"><small>%8$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $rows ),
			esc_attr( $cols ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'ipytw' ) ),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get current tab
	 * 
	 * @return string
	 */
	private function get_tab_name() {
		return $this->cur_tab;
	}

	/**
	 * Save plugin settings
	 * 
	 * @param string $opt_name
	 * @param string $feed_id
	 * @param string $save_if_empty
	 * 
	 * @return void
	 */
	private function save_plugin_set( $opt_name, $feed_id = '1', $save_if_empty = 'no' ) {
		if ( isset( $_POST[ $opt_name ] ) ) {
			if ( is_array( $_POST[ $opt_name ] ) ) {
				// массивы храним отдельно от других параметров
				// TODO: univ_option_upd( $opt_name . $feed_id, maybe_serialize( $_POST[ $opt_name ] ) );
				ipytw_optionUPD( $opt_name, serialize( $_POST[ $opt_name ] ), $feed_id );
			} else {
				$value = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $_POST[ $opt_name ] );
				common_option_upd( $opt_name, $value, 'no', $feed_id, 'ipytw' );
			}
		} else {
			if ( 'empty_str' === $save_if_empty ) {
				common_option_upd( $opt_name, '', 'no', $feed_id, 'ipytw' );
			}
			if ( 'empty_arr' === $save_if_empty ) {
				// массивы храним отдельно от других параметров
				// TODO: univ_option_upd( $opt_name . $feed_id, maybe_serialize( [ ] ) );
				ipytw_optionUPD( $opt_name, serialize( [] ), $feed_id );
			}
		}
		return;
	}

	/**
	 * Возвращает префикс фида
	 * 
	 * @return string
	 */
	private function get_prefix_feed() {
		if ( $this->get_feed_id() == '1' ) {
			$prefix_feed = '';
		} else {
			$prefix_feed = $this->get_feed_id();
		}
		return (string) $prefix_feed;
	}

	/**
	 * Возвращает id текущего блога
	 * 
	 * @return string
	 */
	private function get_current_blog_id() {
		if ( is_multisite() ) {
			$cur_blog_id = get_current_blog_id();
		} else {
			$cur_blog_id = '0';
		}
		return (string) $cur_blog_id;
	}
}