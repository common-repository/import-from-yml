<?php
/**
 * The main class of the plugin Import from YML
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param        
 *
 * @depends                 classes:    IPYTW_Data_Arr
 *                                      IPYTW_Settings_Page
 *                                      IPYTW_Debug_Page
 *                                      IPYTW_Error_Log
 *                                      IPYTW_Import_XML
 *                                      ICPD_Feedback
 *                                      ICPD_Promo
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      univ_option_get
 *                          constants:  IPYTW_PLUGIN_VERSION
 *                                      IPYTW_PLUGIN_BASENAME
 *                                      IPYTW_PLUGIN_DIR_URL
 *                                      IPYTW_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

final class ImportProductsYMLtoWooCommerce {
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
		'p' => [ 'class' => true, 'style' => true ],
		'kbd' => [ 'class' => true ]
	];
	/**
	 * Plugin version
	 * @var string
	 */
	private $plugin_version = IPYTW_PLUGIN_VERSION; // 1.0.0

	protected static $instance;
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Срабатывает при активации плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! is_dir( IPYTW_PLUGIN_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( IPYTW_PLUGIN_UPLOADS_DIR_PATH ) ) {
				error_log(
					sprintf( '%s %s; Файл: import-from-yml.php; Строка: ',
						'ERROR: Ошибка создания папки',
						IPYTW_PLUGIN_UPLOADS_DIR_PATH,
						__LINE__
					), 0 );
			}
		}

		$ipytw_registered_feeds_arr = [ 
			0 => [ 'last_id' => '1' ],
			1 => [ 'id' => '1' ]
		];

		$def_plugin_date_arr = new IPYTW_Data_Arr();
		$ipytw_settings_arr = [];
		$ipytw_settings_arr['1'] = $def_plugin_date_arr->get_opts_name_and_def_date( 'all' );

		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'ipytw_keeplogs', '0' );
			add_blog_option( get_current_blog_id(), 'ipytw_enable_backend_debug', '0' );
			add_blog_option( get_current_blog_id(), 'ipytw_version', IPYTW_PLUGIN_VERSION );
			add_blog_option( get_current_blog_id(), 'ipytw_disable_notices', '0' );
			add_blog_option( get_current_blog_id(), 'ipytw_settings_arr', $ipytw_settings_arr );
			add_blog_option( get_current_blog_id(), 'ipytw_status_sborki1', '-1' );
			add_blog_option( get_current_blog_id(), 'ipytw_last_element1', '-1' );
			add_blog_option( get_current_blog_id(), 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
		} else {
			add_option( 'ipytw_keeplogs', '0' );
			add_option( 'ipytw_enable_backend_debug', '0' );
			add_option( 'ipytw_version', IPYTW_PLUGIN_VERSION, '', 'no' ); // без автозагрузки
			add_option( 'ipytw_disable_notices', '0', '', 'no' );
			add_option( 'ipytw_settings_arr', $ipytw_settings_arr );
			add_option( 'ipytw_status_sborki1', '-1' );
			add_option( 'ipytw_last_element1', '-1' );
			add_option( 'ipytw_registered_feeds_arr', $ipytw_registered_feeds_arr );
		}
	}

	/**
	 * Срабатывает при отключении плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// отключим все кроны и переведём все сборки в "отключено"
		$ipytw_registered_feeds_arr = univ_option_get( 'ipytw_registered_feeds_arr', [] );
		if ( ! empty( $ipytw_registered_feeds_arr ) ) {
			for ( $i = 1; $i < count( $ipytw_registered_feeds_arr ); $i++ ) {
				// с единицы, т.к инфа по конкретным фидам там
				$feed_id = $ipytw_registered_feeds_arr[ $i ]['id']; // тут у нас тип string

				wp_clear_scheduled_hook( 'ipytw_cron_period', [ $feed_id ] ); // отключаем крон
				wp_clear_scheduled_hook( 'ipytw_cron_sborki', [ $feed_id ] ); // отключаем крон

				common_option_upd( 'ipytw_run_cron', 'disabled', 'no', $feed_id, 'ipytw' );
				common_option_upd( 'ipytw_status_cron', 'off', 'no', $feed_id, 'ipytw' );

				// * c id == '1' у нас, будет загвоздка при таком подходе, но в целом пойдёт и так
				univ_option_upd( 'ipytw_status_sborki' . $feed_id, '-1' );
				univ_option_upd( 'ipytw_last_element' . $feed_id, '-1' );
			}
		}
	}

	/**
	 * The main class of the plugin Import from YML
	 */
	public function __construct() {
		$this->check_options_upd(); // проверим, нужны ли обновления опций плагина
		$this->init_classes();
		$this->init_hooks(); // подключим хуки
	}

	/**
	 * Checking whether the plugin options need to be updated
	 * 
	 * @return void
	 */
	public function check_options_upd() {
		if ( false == common_option_get( 'ipytw_version' ) ) { // это первая установка
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'ipytw_version', $this->plugin_version );
			} else {
				update_option( 'ipytw_version', $this->plugin_version );
			}
		} else {
			$this->set_new_options();
		}
	}

	/**
	 * Summary of set_new_options
	 * 
	 * @return void
	 */
	public function set_new_options() {
		// Если предыдущая версия плагина меньше текущей
		if ( version_compare( $this->get_plugin_version(), $this->plugin_version, '<' ) ) {
			new IPYTW_Error_Log( sprintf( '%1$s (%2$s < %3$s). %4$s; Файл: %5$s; Строка: %6$s',
				'Предыдущая версия плагина меньше текущей',
				(string) $this->get_plugin_version(),
				(string) $this->plugin_version,
				'Обновляем опции плагина',
				'import-from-yml.php',
				__LINE__
			) );
		} else { // обновления не требуются
			return;
		}

		// получим список дефолтных настроек
		$ipytw_data_arr_obj = new IPYTW_Data_Arr();
		$opts_arr = $ipytw_data_arr_obj->get_opts_name_and_def_date_obj( 'all' ); // список дефолтных настроек
		// проверим, заданы ли дефолтные настройки
		$ipytw_settings_arr = univ_option_get( 'ipytw_settings_arr' );
		$ipytw_settings_arr_keys_arr = array_keys( $ipytw_settings_arr );
		for ( $i = 0; $i < count( $ipytw_settings_arr_keys_arr ); $i++ ) {
			// ! т.к у нас работа с array_keys, то в $feed_id может быть int. Для гарантии сделаем string
			$feed_id = (string) $ipytw_settings_arr_keys_arr[ $i ];
			for ( $n = 0; $n < count( $opts_arr ); $n++ ) {
				$name = $opts_arr[ $n ]->name; // get_name();
				$value = $opts_arr[ $n ]->opt_def_value; // get_value();
				if ( ! isset( $ipytw_settings_arr[ $feed_id ][ $name ] ) ) {
					// если какой-то опции нет - добавим в БД
					common_option_upd( $name, $value, 'no', $feed_id, 'ipytw' );
				}
			}
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'ipytw_version', $this->plugin_version );
		} else {
			update_option( 'ipytw_version', $this->plugin_version );
		}
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		new IPYTW_Interface_Hoocked();
		new ICPD_Feedback( [ 
			'plugin_name' => 'Import from YML',
			'plugin_version' => $this->get_plugin_version(),
			'logs_url' => IPYTW_PLUGIN_UPLOADS_DIR_URL . '/plugin.log',
			'pref' => 'ipytw',
		] );
		new ICPD_Promo( 'ipytw' );
		return;
	}

	/**
	 * Get the plugin version from the site database
	 * 
	 * @return string
	 */
	public function get_plugin_version() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'ipytw_version' );
		} else {
			$v = get_option( 'ipytw_version' );
		}
		return (string) $v;
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_init', [ $this, 'listen_submits' ], 10 ); // ещё можно слушать чуть раньше на wp_loaded
		add_action( 'admin_init', function () {
			wp_register_style( 'ipytw-admin-css', IPYTW_PLUGIN_DIR_URL . 'assets/css/ipytw_style.css' );
		}, 9999 ); // Регаем стили только для страницы настроек плагина
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 10, 1 );
		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		add_filter( 'upload_mimes', [ $this, 'add_mime_types' ], 99 ); // чутка позже остальных
		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals_func' ], 10, 1 );

		add_action( 'ipytw_cron_sborki', [ $this, 'ipytw_do_this_fifty_sec' ], 10, 1 );
		add_action( 'ipytw_cron_period', [ $this, 'ipytw_do_this_event' ], 10, 1 );
		add_action( 'admin_notices', [ $this, 'notices_prepare' ], 10, 1 );

		// дополнительные данные для фидбэка
		add_filter( 'ipytw_f_feedback_additional_info', [ $this, 'feedback_additional_info' ], 10, 1 );
	}

	/**
	 * Listen submits
	 * 
	 * @return void
	 */
	public function listen_submits() {
		do_action( 'ipytw_listen_submits' );

		if ( isset( $_REQUEST['ipytw_submit_action'] ) ) {
			$message = __( 'Updated', 'import-from-yml' );
			$class = 'notice-success';
			if ( isset( $_POST['ipytw_run_cron'] ) && sanitize_text_field( $_POST['ipytw_run_cron'] ) !== 'off' ) {
				$message .= '. ' . __(
					'Import products is running. You can continue working with the website',
					'import-from-yml'
				);
			}

			add_action( 'admin_notices', function () use ($message, $class) {
				$this->print_admin_notice( $message, $class );
			}, 10, 2 );
		}
	}

	/**
	 * Register the style sheet on separate pages of our plugin.
	 * Function for `admin_print_styles-[page_suffix]` action-hook.
	 * 
	 * @return void
	 */
	public function admin_css_func() {
		wp_enqueue_style( 'ipytw-admin-css' ); // Ставим css-файл в очередь на вывод	
	}

	/**
	 * Add items to admin menu. Function for `admin_menu` action-hook.
	 * 
	 * @return void
	 */
	public function add_admin_menu() {
		$page_suffix = add_menu_page(
			null,
			__( 'Import from YML', 'import-from-yml' ),
			'manage_options',
			'ipytwexport',
			[ $this, 'get_plugin_settings_page' ],
			'dashicons-redo',
			51
		);
		// создаём хук, чтобы стили выводились только на странице настроек
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_css_func' ] );

		$page_suffix = add_submenu_page(
			'ipytwexport',
			__( 'Debug', 'import-from-yml' ),
			__( 'Debug page', 'import-from-yml' ),
			'manage_woocommerce',
			'ipytwdebug',
			[ $this, 'get_debug_page_func' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_css_func' ] );

		$page_subsuffix = add_submenu_page(
			'ipytwexport',
			__( 'Add Extensions', 'import-from-yml' ),
			__( 'Extensions', 'import-from-yml' ),
			'manage_options',
			'ipytwextensions',
			[ $this, 'get_extensions_page_func' ]
		);
		add_action( 'admin_print_styles-' . $page_subsuffix, [ $this, 'admin_css_func' ] );
	}

	/**
	 * Вывод страницы настроек плагина
	 * 
	 * @return void
	 */
	public function get_plugin_settings_page() {
		new IPYTW_Settings_Page();
		return;
	}

	/**
	 * Вывод страницы отладки плагина
	 * 
	 * @return void
	 */
	public function get_debug_page_func() {
		new IPYTW_Debug_Page();
		return;
	}

	/**
	 * Вывод страницы расширений плагина
	 * 
	 * @return void
	 */
	public function get_extensions_page_func() {
		new IPYTW_Extensions_Page();
		return;
	}

	/**
	 * Function for `plugin_action_links` action-hook.
	 * 
	 * @param string[] $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory
	 * 
	 * @return string[]
	 */
	public function add_plugin_action_links( $actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, IPYTW_PLUGIN_BASENAME ) ) { // проверка, что у нас текущий плагин
			return $actions;
		}

		$settings_link = sprintf( '<a style="%s" href="/wp-admin/admin.php?page=%s">%s</a>',
			'color: green; font-weight: 700;',
			'ipytwextensions',
			__( 'More features', 'import-from-yml' )
		);
		array_unshift( $actions, $settings_link );

		$settings_link = sprintf( '<a href="/wp-admin/admin.php?page=%s">%s</a>',
			'ipytwexport',
			__( 'Settings', 'import-from-yml' )
		);
		array_unshift( $actions, $settings_link );

		return $actions;
	}

	/**
	 * Разрешим загрузку xml и csv файлов. Function for `upload_mimes` action-hook.
	 * 
	 * @param array $mimes
	 * 
	 * @return array
	 */
	public function add_mime_types( $mimes ) {
		$mimes['csv'] = 'text/csv';
		$mimes['xml'] = 'text/xml';
		$mimes['yml'] = 'text/xml';
		return $mimes;
	}

	/**
	 * Add cron intervals to WordPress. Function for `cron_schedules` action-hook.
	 * 
	 * @param array $schedules
	 * 
	 * @return array
	 */
	public function add_cron_intervals_func( $schedules ) {
		$schedules['fifty_sec'] = [ // * тут опечатка, но оставляем как есть для обратной совместимости
			'interval' => 61,
			'display' => __( '61 seconds', 'import-from-yml' )
		];
		$schedules['three_hours'] = [ 
			'interval' => 10800,
			'display' => __( '3 hours', 'import-from-yml' )
		];
		$schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( '6 hours', 'import-from-yml' )
		];
		$schedules['every_two_days'] = [ 
			'interval' => 172800,
			'display' => __( 'Every two days', 'import-from-yml' )
		];
		$schedules['week'] = [ 
			'interval' => 604800,
			'display' => __( '1 week', 'import-from-yml' )
		];
		return $schedules;
	}

	/* ----------------- функции крона ----------------- */

	/**
	 * Summary of ipytw_do_this_seventy_sec
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function ipytw_do_this_fifty_sec( $feed_id ) {
		new IPYTW_Error_Log( 'Cтартовала крон-задача do_this_seventy_sec' );
		$import = new IPYTW_Import_XML( $feed_id ); // делаем что-либо каждые 50 сек
		$import->run();
	}

	/**
	 * Summary of ipytw_do_this_event
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function ipytw_do_this_event( $feed_id ) {
		new IPYTW_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$feed_id,
			'Крон ipytw_do_this_event включен. Делаем что-то каждый час',
			'class-import-from-yml.php',
			__LINE__
		) );
		ipytw_optionUPD( 'ipytw_status_sborki', '1', $feed_id );
		ipytw_optionUPD( 'ipytw_last_element', '0', $feed_id ); // выставляем в положения начала сборки

		wp_clear_scheduled_hook( 'ipytw_cron_sborki', [ $feed_id ] );

		if ( ! wp_next_scheduled( 'ipytw_cron_sborki', [ $feed_id ] ) ) {
			// Возвращает nul|false. null когда планирование завершено. false в случае неудачи
			$res = wp_schedule_event( time(), 'fifty_sec', 'ipytw_cron_sborki', [ $feed_id ] );
		} else {
			$res = false;
		}
		if ( false === $res ) {
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; ERROR: %2$s; Файл: %3$s; Строка: %4$s',
				$feed_id,
				'Не удалось запланировань CRON fifty_sec',
				'class-import-from-yml.php',
				__LINE__
			) );
		} else {
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$feed_id,
				'CRON fifty_sec успешно запланирован',
				'class-import-from-yml.php',
				__LINE__
			) );
		}
	}
	/* ----------------- end функции крона ----------------- */

	/**
	 * Вывод различных notices
	 * 
	 * @see https://wpincode.com/kak-dobavit-sobstvennye-uvedomleniya-v-adminke-wordpress/
	 * 
	 * @return void
	 */
	public function notices_prepare() {
		if ( class_exists( 'ImportProductsYMLtoWooCommercePro' ) ) {
			$plugin = '/import-from-yml-pro/import-from-yml-pro.php';
			// /home/www/site.ru/wp-content/plugins/import-from-yml-pro/import-from-yml-pro.php';
			$pro_plugin_file = WP_PLUGIN_DIR . $plugin;
			$get_from_headers_arr = [ 'ver' => 'Version', 'name' => 'Plugin Name' ];
			$pro_plugin_data = get_file_data( $pro_plugin_file, $get_from_headers_arr );
			if ( version_compare( $pro_plugin_data['ver'], '2.0.0', '<' ) ) {
				$this->need_critical_update( [ 
					'plugin_name' => 'Import products from YML PRO',
					'plugin_slug' => 'import-from-yml-pro',
					'plugin_need_version' => '2.0.0'
				] );
			}
		}

		$ipytw_enable_backend_debug = ipytw_optionGET( 'ipytw_enable_backend_debug' );
		if ( $ipytw_enable_backend_debug === 'on' ) {
			print '<div class="updated notice notice-success is-dismissible"><p>';
			print '<h1>Зарегистрированные фиды:</h1>';
			$ipytw_registered_feeds_arr = ipytw_optionGET( 'ipytw_registered_feeds_arr' );
			echo get_array_as_string( $ipytw_registered_feeds_arr, '<br/>' );

			print '<h1>Статусы сборок:</h1>';
			for ( $i = 1; $i < count( $ipytw_registered_feeds_arr ); $i++ ) { // с единицы, т.к инфа по конкретным фидам там
				$feed_id = $ipytw_registered_feeds_arr[ $i ]['id'];
				$ipytw_status_sborki_val = ipytw_optionGET( 'ipytw_status_sborki', $feed_id );
				$ipytw_last_element_val = ipytw_optionGET( 'ipytw_last_element', $feed_id );
				if ( $feed_id === '0' || $feed_id === 0 ) {
					$feed_id = '';
				}
				$ipytw_status_sborki_key = 'ipytw_status_sborki' . $feed_id;
				$ipytw_last_element_key = 'ipytw_last_element' . $feed_id;
				print '$ipytw_status_sborki_key = ' . $ipytw_status_sborki_key . '; $ipytw_status_sborki_val = ' . $ipytw_status_sborki_val . '<br/>';
				print '$ipytw_last_element_key = ' . $ipytw_last_element_key . '; $ipytw_last_element_val = ' . $ipytw_last_element_val . '<br/>';
			}

			print '<h1>Динамические опции:</h1>';
			$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
			echo get_array_as_string( $ipytw_settings_arr, '<br/>' );

			print '<h1>Крон задачи (ipytw_cron_period) и (ipytw_cron_sborki):</h1>';
			$cron_zadachi = get_option( 'cron' ); // получаем все задачи из базы данных
			echo get_array_as_string( $cron_zadachi, '<br/>' ); // можно использовать функции print_r() или var_dump() для вывода всех задач
			print '</p></div>';
		}

		$ipytw_disable_notices = ipytw_optionGET( 'ipytw_disable_notices' );
		if ( $ipytw_disable_notices === 'on' ) {
		} else {
			$ipytw_registered_feeds_arr = ipytw_optionGET( 'ipytw_registered_feeds_arr' );
			for ( $i = 1; $i < count( $ipytw_registered_feeds_arr ); $i++ ) { // с единицы, т.к инфа по конкретным фидам там
				$feed_id = $ipytw_registered_feeds_arr[ $i ]['id'];
				$status_sborki = ipytw_optionGET( 'ipytw_status_sborki', $feed_id ); // ожидается тип (int)
				switch ( $status_sborki ) {
					case 1:
						printf( '<div class="%1$s"><p>%2$s = %3$s. %4$s. %5$s: 1. %6$s</p></div>',
							'updated notice notice-success is-dismissible',
							__( 'Feed ID', 'import-from-yml' ),
							$feed_id,
							__( 'Import products is running', 'import-from-yml' ),
							__( 'Step', 'import-from-yml' ),
							__( 'Getting a feed from the source site', 'import-from-yml' )
						);
						break;
					case 2:
						printf( '<div class="%1$s"><p>%2$s = %3$s. %4$s. %5$s: 2. %6$s</p></div>',
							'updated notice notice-success is-dismissible',
							__( 'Feed ID', 'import-from-yml' ),
							$feed_id,
							__( 'Import products is running', 'import-from-yml' ),
							__( 'Step', 'import-from-yml' ),
							__( 'Importing a list of categories', 'import-from-yml' )
						);
						break;
					case 3:
						$ipytw_last_element = (int) ipytw_optionGET( 'ipytw_last_element', $feed_id );
						$count_offers_in_feed = (int) common_option_get( 'ipytw_count_elements', false, $feed_id, 'ipytw' );
						if ( $count_offers_in_feed == -1 ) {
							$count_offers_in_feed = '(' . __( 'products are being counted', 'import-from-yml' ) . '...)';
						}
						printf( '<div class="%1$s"><p>%2$s = %3$s. %4$s. %5$s: 3. %6$s %7$s %8$s %9$s</p></div>',
							'updated notice notice-success is-dismissible',
							__( 'Feed ID', 'import-from-yml' ),
							$feed_id,
							__( 'Import products is running', 'import-from-yml' ),
							__( 'Step', 'import-from-yml' ),
							__( 'Processed products', 'import-from-yml' ),
							$ipytw_last_element,
							__( 'of', 'import-from-yml' ),
							$count_offers_in_feed
						);
						break;
					case 4:
						printf( '<div class="%s"><p>%s...</p></div>',
							'updated notice notice-success is-dismissible',
							__( 'Removing products', 'import-from-yml' )
						);
						break;
					default:
				}
			}
		}
	}

	/**
	 * Summary of need_critical_update
	 * 
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	private function need_critical_update( $data_arr ) {
		$utm = sprintf(
			'?utm_source=%1$s&utm_medium=organic&utm_campaign=%2$s&utm_content=need_critical_update&utm_term=',
			'import-from-yml',
			$data_arr['plugin_slug']
		);
		$class = 'notice-error';
		$message = sprintf( '<h1>%1$s <strong style="font-weight: 700;">%2$s</strong> %3$s v.%4$s %5$s!</h1>
			<p><strong style="font-weight: 700;">%6$s:</strong></p>
			<ol>
			<li><a href="/wp-admin/admin.php?page=ipytwdebug">%7$s</a> Import from YML;</li>
			<li>%8$s "%9$s". (%10$s <a href="https://icopydoc.ru/product-category/plagins/%11$s">icopydoc.ru</a>);</li>
			<li>%12$s "<a href="/wp-admin/plugins.php">%13$s</a>" %14$s <strong style="font-weight: 700;">%2$s</strong> %15$s;</li>
			<li>%16$s "%9$s".</li>
			</ol>
			<p><strong style="font-weight: 700;">%17$s!</strong></p>
			<p><a href="https://icopydoc.ru/instruktsiya-po-srochnym-obnovleniyam-plagina/%18$s">%19$s</a></p>',
			__( 'Срочно обновите плагин', 'import-from-yml' ),
			$data_arr['plugin_name'],
			__( 'до версии', 'import-from-yml' ),
			$data_arr['plugin_need_version'],
			__( 'или более свежей', 'import-from-yml' ),
			__( 'Для этого сделайте следующее', 'import-from-yml' ),
			__( 'Перейдите на страницу отладки плагина', 'import-from-yml' ),
			__( 'Нажмите', 'import-from-yml' ),
			__( 'Обновить данные лицензии', 'import-from-yml' ),
			__( 'Если ваша лицензия истекла, то сначала продлите её на сайте', 'import-from-yml' ),
			$utm . 'renew_license',
			__( 'После этого перейдите на страницу', 'import-from-yml' ),
			__( 'Плагины', 'import-from-yml' ),
			__( 'и обновите плагин', 'import-from-yml' ),
			__( 'нажав на ссылку "обновить сейчас"', 'import-from-yml' ),
			__(
				'После обновления премиум-версии ещё раз вернитесь на страницу отладки и нажмите',
				'import-from-yml'
			),
			__(
				'Если этого не сделать, то фид может формироваться с ошибками или вовсе не создаваться',
				'import-from-yml'
			),
			$utm . 'read_more',
			__( 'Прочитать полную инструкцию и задать вопросы', 'import-from-yml' )
		);

		$this->print_admin_notice( $message, $class );
	}

	/**
	 * Print admin notice
	 * 
	 * @param string $message
	 * @param string $class
	 * 
	 * @return void
	 */
	private function print_admin_notice( $message, $class ) {
		$ipytw_disable_notices = univ_option_get( 'ipytw_disable_notices' );
		if ( $ipytw_disable_notices === 'on' ) {
			return;
		} else {
			printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			return;
		}
	}

	/**
	 * Summary of feedback_additional_info
	 * 
	 * @param string $additional_info
	 * 
	 * @return string
	 */
	public function feedback_additional_info( $additional_info ) {
		$possible_problems_arr = IPYTW_Debug_Page::get_possible_problems_list();
		$additional_info .= 'Самодиагностика: ';
		if ( $possible_problems_arr[1] > 0 ) {
			$additional_info .= sprintf( '<ol>%s</ol>', $possible_problems_arr[0] );
		} else {
			$additional_info .= sprintf( '<p>%s</p>', 'Функции самодиагностики не выявили потенциальных проблем' );
		}

		if ( ! class_exists( 'ImportProductsYMLtoWooCommercePro' ) ) {
			$additional_info .= "Pro: не активна" . "<br />";
		} else {
			if ( defined( 'IPYTWP_PLUGIN_VERSION' ) ) {
				$v = IPYTWP_PLUGIN_VERSION;
			} else if ( defined( 'ipytwp_VER' ) ) {
				$v = ipytwp_VER;
			} else {
				$v = 'н/д';
			}
			$order_id = univ_option_get( 'ipytwp_order_id' );
			$order_email = univ_option_get( 'ipytwp_order_email' );
			$additional_info .= sprintf( 'PRO: активна (v %s (#%s / %s)<br />',
				$v,
				$order_id,
				$order_email
			);
		}
		$yandex_zen_rss = univ_option_get( 'yzen_yandex_zen_rss' );
		$additional_info .= "RSS for Yandex Zen: " . $yandex_zen_rss . "<br />";
		$settings_arr = univ_option_get( 'ipytw_settings_arr', [] );
		$settings_arr_keys_arr = array_keys( $settings_arr );
		for ( $i = 0; $i < count( $settings_arr_keys_arr ); $i++ ) {
			$feed_id = (string) $settings_arr_keys_arr[ $i ];
			$additional_info .= sprintf(
				'<h2>ФИД №%1$s</h2>
				<p>status_sborki: %2$s<br />
				УРЛ: %3$s<br />
				УРЛ XML-фида: %4$s<br />
				Временный файл: %5$s<br />
				Что экспортировать: %6$s<br />
				Автоматическое создание файла: %7$s<br />
				Обновить фид при обновлении карточки товара: %8$s<br />
				Дата последней сборки XML: %9$s<br />
				Что продаёт: %10$s</p>',
				(string) $feed_id,
				common_option_get( 'ipytw_status_sborki', false, $feed_id, 'ipytw' ),
				get_site_url(),
				urldecode( common_option_get( 'ipytw_file_url', false, $feed_id, 'ipytw' ) ),
				urldecode( common_option_get( 'ipytw_file_file', false, $feed_id, 'ipytw' ) ),
				common_option_get( 'ipytw_whot_export', false, $feed_id, 'ipytw' ),
				common_option_get( 'ipytw_status_cron', false, $feed_id, 'ipytw' ),
				common_option_get( 'ipytw_ufup', false, $feed_id, 'ipytw' ),
				common_option_get( 'ipytw_date_sborki', false, $feed_id, 'ipytw' ),
				common_option_get( 'ipytw_main_product', false, $feed_id, 'ipytw' )
			);
		}
		return $additional_info;
	}
} /* end class ImportProductsYMLtoWooCommerce */