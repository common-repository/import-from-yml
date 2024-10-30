<?php
/**
 * The this class manages the list of feeds
 *
 * @package                 iCopyDoc Plugins (v1, core 16-08-2023)
 * @subpackage              Import from YML
 * @since                   0.1.0
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html 
 *                          https://wp-kama.ru/function/wp_list_table
 * 
 * @param      	
 *
 * @depends                 classes:    WP_List_Table
 *                                      IPYTW_Data_Arr
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      ipytw_optionGET
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Settings_Page_Feeds_WP_List_Table extends WP_List_Table {
	/**	
	 * The this class manages the list of feeds
	 */
	function __construct() {
		global $status, $page;
		parent::__construct( [ 
			// По умолчанию: '' ($this->screen->base);
			// Название для множественного числа, используется во всяких заголовках, например в css классах,
			// в заметках, например 'posts', тогда 'posts' будет добавлен в класс table
			'plural' => '',

			// По умолчанию: ''; Название для единственного числа, например 'post'. 
			'singular' => '',

			// По умолчанию: false; Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод 
			// _js_vars() в подвале, чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события.
			'ajax' => false,

			// По умолчанию: null; Строка содержащая название хука, нужного для определения текущей страницы. 
			// Если null, то будет установлен текущий экран.
			'screen' => null
		] );

		add_action( 'admin_footer', [ $this, 'print_style_footer' ] ); // меняем ширину колонок
	}

	/**	
	 * Печатает форму
	 * 
	 * @return void
	 */
	public function print_html_form() {
		echo '<form method="get"><input type="hidden" name="ipytw_form_id" value="ipytw_wp_list_table" />';
		wp_nonce_field( 'ipytw_nonce_action_f', 'ipytw_nonce_field_f' );
		printf( '<input type="hidden" name="page" value="%s" />', esc_attr( $_REQUEST['page'] ) );
		$this->prepare_items();
		$this->display();
		echo '</form>';
	}

	/**
	 * Сейчас у таблицы стандартные стили WordPress. Чтобы это исправить, вам нужно адаптировать классы CSS, которые
	 * были автоматически применены к каждому столбцу. Название класса состоит из строки «column-» и ключевого имени 
	 * массива $columns, например «column-isbn» или «column-author».
	 * В качестве примера мы переопределим ширину столбцов (для простоты, стили прописаны непосредственно 
	 * в HTML разделе head)
	 * 
	 * @return void
	 */
	public function print_style_footer() {
		print ( '<style type="text/css">#ipytw_feed_id, .column-ipytw_feed_id {width: 7%;}</style>' );
	}

	/**	
	 *	Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы. 
	 *	Ключи в массиве должны быть теми же, что и в массиве данных, 
	 *	иначе соответствующие столбцы не будут отображены.
	 */
	function get_columns() {
		$columns = [ 
			'cb' => '<input type="checkbox" />', // флажок сортировки. см get_bulk_actions и column_cb
			'ipytw_feed_id' => __( 'Feed ID', 'import-from-yml' ),
			'ipytw_url_yml_file' => __( 'YML File', 'import-from-yml' ),
			'ipytw_run_cron' => __( 'Start automatic import', 'import-from-yml' ),
			'ipytw_step_import' => __( 'Step of import', 'import-from-yml' ),
			'ipytw_date_sborki_end' => __( 'Imported', 'import-from-yml' ),
			'ipytw_count_products_in_feed' => __( 'Products', 'import-from-yml' )
		];
		return $columns;
	}

	/**	
	 * Метод вытаскивает из БД данные, которые будут лежать в таблице
	 * $this->table_data();
	 * 
	 * @return array
	 */
	private function table_data() {
		$ipytw_settings_arr = common_option_get( 'ipytw_settings_arr' );
		$result_arr = [];
		if ( $ipytw_settings_arr == '' || empty( $ipytw_settings_arr ) ) {
			return $result_arr;
		}
		$ipytw_settings_arr_keys_arr = array_keys( $ipytw_settings_arr );
		for ( $i = 0; $i < count( $ipytw_settings_arr_keys_arr ); $i++ ) {
			$key = $ipytw_settings_arr_keys_arr[ $i ];

			$text_column_ipytw_feed_id = $key;

			$ipytw_url_yml_file = ipytw_optionGET( 'ipytw_url_yml_file', $text_column_ipytw_feed_id, 'set_arr' );
			if ( $ipytw_url_yml_file == '' ) {
				$text_column_ipytw_url_xml_file = __( 'No feed address set', 'import-from-yml' );
			} else {
				$text_column_ipytw_url_xml_file = sprintf( '<a target="_blank" href="%1$s">%1$s</a>',
					urldecode( $ipytw_url_yml_file )
				);
			}

			$ipytw_err_list_code_arr = ipytw_optionGET( 'ipytw_err_list_code_arr', $text_column_ipytw_feed_id, 'set_arr' );
			if ( ! empty( $ipytw_err_list_code_arr ) ) {
				if ( $ipytw_err_list_code_arr[0] === '26' ) {
					$text_column_ipytw_url_xml_file = $text_column_ipytw_url_xml_file .
						sprintf( '<br/><i><span style="color: red;">%s!</span></i>',
							__( 'This feed cannot be imported because its contents do not comply with the YML standard', 'import-from-yml' )
						);
				}
				if ( $ipytw_err_list_code_arr[0] === '27' ) {
					$text_column_ipytw_url_xml_file = $text_column_ipytw_url_xml_file .
						sprintf( '<br/><i><span style="color: red;">%s <a href="%s">%s</a>. %s. <a href="%s">%s</a>.</span></i>',
							__( 'Products of this feed do not have the name attribute. The names of the products during import were generated according to these', 'import-from-yml' ),
							'https://yandex.ru/support/partnermarket/guides/mobile-phones.html',
							__( 'Yandex rules', 'import-from-yml' ),
							__( 'You can change the rules using the filters', 'import-from-yml' ),
							'https://icopydoc.ru/kak-importirovat-tovar-bez-atributa-name/?utm_source=import-from-yml&utm_medium=organic&utm_campaign=in-plugin-import-from-yml&utm_content=settings&utm_term=custom_yml_format',
							__( 'Read more', 'import-from-yml' )
						);
				}
			}
			if ( $ipytw_settings_arr[ $key ]['ipytw_feed_assignment'] === '' ) {

			} else {
				$text_column_ipytw_url_xml_file = sprintf( '%1$s<br/>(%2$s: %3$s)',
					$text_column_ipytw_url_xml_file,
					__( 'Feed assignment', 'yml-for-yandex-market' ),
					$ipytw_settings_arr[ $key ]['ipytw_feed_assignment']
				);
			}

			$ipytw_status_cron = common_option_get( 'ipytw_status_cron', false, $text_column_ipytw_feed_id, 'ipytw' );
			switch ( $ipytw_status_cron ) {
				case 'off':
					$text_status_cron = __( "Don't start", "import-from-yml" );
					break;
				case 'once':
					$text_status_cron = sprintf( '%s (%s)',
						__( 'Import once', 'import-from-yml' ),
						__( 'launch now', 'import-from-yml' )
					);
					break;
				case 'hourly':
					$text_status_cron = __( 'Hourly', 'import-from-yml' );
					break;
				case 'three_hours':
					$text_status_cron = __( 'Every three hours', 'import-from-yml' );
					break;
				case 'six_hours':
					$text_status_cron = __( 'Every six hours', 'import-from-yml' );
					break;
				case 'twicedaily':
					$text_status_cron = __( 'Twice a day', 'import-from-yml' );
					break;
				case 'daily':
					$text_status_cron = __( 'Daily', 'import-from-yml' );
					break;
				case 'every_two_days':
					$text_status_cron = __( 'Every two days', 'import-from-yml' );
					break;
				case 'week':
					$text_status_cron = __( 'Once a week', 'import-from-yml' );
					break;
				default:
					$text_status_cron = __( "Don't start", "import-from-yml" );
			}

			$cron_info = wp_get_scheduled_event( 'ipytw_cron_sborki', [ (string) $key ] );
			if ( false === $cron_info ) {
				$cron_info = wp_get_scheduled_event( 'ipytw_cron_period', [ (string) $key ] );
				if ( false === $cron_info ) {
					$text_column_ipytw_run_cron = sprintf( '%s<br/><small>%s</small>',
						$text_status_cron,
						__(
							'There are no scheduled CRON tasks for importing products from this feed',
							'import-from-yml'
						)
					);
				} else {
					$text_column_ipytw_run_cron = sprintf( '%s<br/><small>%s:<br/>%s</small>',
						$text_status_cron,
						__( 'The next import from the feed is scheduled for', 'import-from-yml' ),
						wp_date( 'Y-m-d H:i:s', $cron_info->timestamp )
					);
				}

			} else {
				$after_time = $cron_info->timestamp - current_time( 'timestamp', 1 );
				if ( $after_time < 0 ) {
					$after_time = 0;
				}
				$text_column_ipytw_run_cron = sprintf( '%s<br/><small>%s...<br/>%s:<br/>%s (%s %s %s)</small>',
					$text_status_cron,
					__( 'Products from the feed are imported', 'import-from-yml' ),
					__( 'The next step is scheduled for', 'import-from-yml' ),
					wp_date( 'Y-m-d H:i:s', $cron_info->timestamp ),
					__( 'after', 'import-from-yml' ),
					$after_time,
					__( 'sec', 'import-from-yml' )
				);
			}

			$ipytw_step_import = ipytw_optionGET( 'ipytw_step_import', $text_column_ipytw_feed_id, 'set_arr' );

			if ( $ipytw_settings_arr[ $key ]['ipytw_date_sborki_end'] === '0000000001' ) {
				$text_date_sborki_end = '-';
			} else {
				$text_date_sborki_end = $ipytw_settings_arr[ $key ]['ipytw_date_sborki_end'];
			}

			$args = [ 
				'post_type' => 'product',
				'posts_per_page' => -1,
				'relation' => 'AND',
				'fields' => 'ids',
				'meta_query' => [ 
					[ 
						'key' => '_ipytw_feed_id',
						'value' => (string) $text_column_ipytw_feed_id
					]
				]
			];
			$query = new \WP_Query( $args );
			$count_products_in_feed = $query->found_posts;

			$result_arr[ $i ] = [ 
				'ipytw_feed_id' => $text_column_ipytw_feed_id,
				'ipytw_url_yml_file' => $text_column_ipytw_url_xml_file,
				'ipytw_run_cron' => $text_column_ipytw_run_cron,
				'ipytw_step_import' => $ipytw_step_import . ' ' . __( 'sec', 'import-from-yml' ),
				'ipytw_date_sborki_end' => $text_date_sborki_end,
				'ipytw_count_products_in_feed' => $count_products_in_feed
			];
		}
		return $result_arr;
	}

	/**
	 * @see https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options
	 * 
	 * prepare_items определяет два массива, управляющие работой таблицы:
	 * $hidden - определяет скрытые столбцы
	 * $sortable - определяет, может ли таблица быть отсортирована по этому столбцу.
	 *
	 * @return void
	 */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns(); // вызов сортировки
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		// пагинация 
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->table_data() );
		$found_data = array_slice( $this->table_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( [ 
			'total_items' => $total_items, // Мы должны вычислить общее количество элементов
			'per_page' => $per_page // Мы должны определить, сколько элементов отображается на странице
		] );
		// end пагинация 
		$this->items = $found_data; // $this->items = $this->table_data() // Получаем данные для формирования таблицы
	}

	/**
	 * Данные таблицы.
	 * Наконец, метод назначает данные из примера на переменную представления данных класса — items.
	 * Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например, 
	 * function column_ipytw_url_xml_file. Такой метод должен быть указан для каждого столбца. Но чтобы не создавать 
	 * эти методы для всех столбцов в отдельности, можно использовать column_default. Эта функция обработает все 
	 * столбцы, для которых не определён специальный метод
	 * 
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ipytw_feed_id':
			case 'ipytw_url_yml_file':
			case 'ipytw_run_cron':
			case 'ipytw_step_import':
			case 'ipytw_date_sborki_end':
			case 'ipytw_count_products_in_feed':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Мы отображаем целый массив во избежание проблем
		}
	}

	/**
	 * Функция сортировки.
	 * Второй параметр в массиве значений $sortable_columns отвечает за порядок сортировки столбца. 
	 * Если значение true, столбец будет сортироваться в порядке возрастания, если значение false, столбец 
	 * сортируется в порядке убывания, или не упорядочивается. Это необходимо для маленького треугольника около 
	 * названия столбца, который указывает порядок сортировки, чтобы строки отображались в правильном направлении
	 * 
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = [ 
			'ipytw_url_yml_file' => [ 'ipytw_url_yml_file', false ]
		];
		return $sortable_columns;
	}

	/**
	 * Действия.
	 * Эти действия появятся, если пользователь проведет курсор мыши над таблицей
	 * column_{key_name} - в данном случае для колонки ipytw_url_xml_file - function column_ipytw_url_xml_file
	 * 
	 * @return string
	 */
	function column_ipytw_url_yml_file( $item ) {
		$actions = [ 
			'edit' => sprintf( '<a href="?page=%s&action=%s&feed_id=%s">%s</a>',
				$_REQUEST['page'],
				'edit', $item['ipytw_feed_id'],
				__( 'Edit', 'import-from-yml' )
			)
		];
		return sprintf( '%1$s %2$s', $item['ipytw_url_yml_file'], $this->row_actions( $actions ) );
	}

	/**
	 * 	Массовые действия.
	 *	Bulk action осуществляются посредством переписывания метода get_bulk_actions() и возврата связанного массива
	 *	Этот код просто помещает выпадающее меню и кнопку «применить» вверху и внизу таблицы
	 *	ВАЖНО! Чтобы работало нужно оборачивать вызов класса в form:
	 *	<form id="events-filter" method="get"> 
	 *	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> 
	 *	<?php $wp_list_table->display(); ?> 
	 *	</form> 
	 * 
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = [ 
			'delete' => __( 'Delete feed', 'import-from-yml' ) . '. ' . __( "Don't delete imported products", "import-from-yml" ),
			'delete_all_imported_products' => __( 'Delete all imported products', 'import-from-yml' )
		];
		return $actions;
	}

	/**
	 * Флажки для строк должны быть определены отдельно. Как упоминалось выше, есть метод column_{column} для 
	 * отображения столбца. cb-столбец – особый случай:
	 * 
	 * @param array $item
	 * 
	 * @return string
	 */
	function column_cb( $item ) {
		/* ! */
		if ( $item['ipytw_feed_id'] === '-1' ) {
			return sprintf(
				'<input type="checkbox" name="checkbox_yml_file[]" value="%s" disabled />', $item['ipytw_feed_id']
			);
		} else {
			return sprintf(
				'<input type="checkbox" name="checkbox_yml_file[]" value="%s" />', $item['ipytw_feed_id']
			);
		}
	}

	/**
	 * Нет элементов.
	 * Если в списке нет никаких элементов, отображается стандартное сообщение «No items found.». Если вы хотите 
	 * изменить это сообщение, вы можете переписать метод no_items()
	 * 
	 * @return void
	 */
	function no_items() {
		esc_html_e( 'YML feeds not found', 'import-from-yml' );
	}
}