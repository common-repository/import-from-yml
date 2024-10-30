<?php
/**
 * Starts feed import
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   3.1.0
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       	
 *
 * @depends                 classes:    IPYTW_Error_Log
 *                                      IPYTW_Import_Parsing
 *                                      IPYTW_Import_XML_Helper
 *                                      IPYTW_Import_Create_Simple_Product
 *                                      IPYTW_Import_Create_Variable_Product
 *                                      IPYTW_Import_Create_External_Product
 *                                      WP_Query
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      univ_option_get
 *                                      ipytw_optionGET
 *                                      ipytw_optionUPD
 *                          constants:  IPYTW_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Import_XML {
	/**
	 * Feed prefix
	 * @var string
	 */
	private $pref = 'ipytw';
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Summary of __construct
	 * 
	 * @param string $feed_id
	 */
	public function __construct( $feed_id ) {
		$this->feed_id = $feed_id;
	}

	/**
	 * Run import
	 * 
	 * @return void
	 */
	public function run() {
		new IPYTW_Error_Log( sprintf(
			'FEED № %1$s; Стартовала run. Файл: %2$s; Строка: %3$s',
			$this->get_feed_id(),
			'class-ipytw-import-xml.php',
			__LINE__
		) );
		$status_sborki = (int) ipytw_optionGET( 'ipytw_status_sborki', $this->get_feed_id() );
		$time_start_last_step = (int) current_time( 'timestamp', 1 ); // unixtime

		$last_element = (int) ipytw_optionGET( 'ipytw_last_element', $this->get_feed_id() );
		$step_import = common_option_get( 'ipytw_step_import', false, $this->get_feed_id(), 'ipytw' );
		$timeout = $time_start_last_step + $step_import - 2;

		$args_arr = [ 
			'feed_id' => $this->get_feed_id(),
			'last_element' => $last_element,
			'timeout' => $timeout
		];
		$import_helper_obj = new IPYTW_Import_XML_Helper( $args_arr );

		switch ( $status_sborki ) {
			case 1:
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Шаг 1. Загрузка файла с фидом на наш сервер',
					'class-ipytw-import-xml.php',
					__LINE__
				) );
				do_action( 'ipytw_before_construct', $this->get_feed_id() );

				$res_step = $this->copy_feed_from_source();
				if ( false === $res_step ) {
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; ERROR: %2$s. Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Ошибка загрузки файла фида на наш сервер',
						'class-ipytw-import-xml.php',
						__LINE__
					) );
					$this->stop( 'copy_err' );
					return;
				} else {
					ipytw_optionUPD( 'ipytw_status_sborki', 2, $this->get_feed_id() );
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Загрузка файла с фидом на наш сервер прошла успешно',
						'class-ipytw-import-xml.php',
						__LINE__
					) );
					return;
				}
			case 2:
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Шаг 2. Проверка фида и синхронизация категорий',
					'class-ipytw-import-xml.php',
					__LINE__
				) );

				if ( false === $import_helper_obj->check_yml_format() ) {
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; ERROR: Это не YML-формат Яндекса. Импорт невозможен. Файл: %2$s; Строка: %3$s',
						$this->get_feed_id(),
						'class-ipytw-import-xml.php',
						__LINE__
					) );
					$this->stop( 'yml_format_err' );
					ipytw_optionUPD( 'ipytw_err_list_code_arr', [ '26' ], $this->get_feed_id(), 'yes', 'set_arr' );
					return;
				} else {
					ipytw_optionUPD( 'ipytw_err_list_code_arr', [], $this->get_feed_id(), 'yes', 'set_arr' );
				}

				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Шаг 2. Проверка фида пройдена. Старт синхронизации категорий',
					'class-ipytw-import-xml.php',
					__LINE__
				) );
				$import_helper_obj->import_cat();
				ipytw_optionUPD( 'ipytw_status_sborki', 3, $this->get_feed_id() );

				// удалим файл со списком новых товаров т.к у нас начло сборки
				$name_dir = IPYTW_PLUGIN_UPLOADS_DIR_PATH;
				if ( is_dir( $name_dir ) ) {
					$filename_new_id = $name_dir . '/feed-importetd-product-ids-new-' . $this->get_feed_id() . '.tmp';
					if ( file_exists( $filename_new_id ) ) {
						unlink( $filename_new_id );
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s $filename_new_id = %3$s. Файл: %5$s; Строка: %5$s',
							'Успешно удалили файл со списком новых товаров из прошлой сборки',
							$filename_new_id,
							$this->get_feed_id(),
							'class-ipytw-import-xml.php',
							__LINE__
						) );
					}
				}
				return;
			case 3:
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Шаг 3. Импорт товаров',
					'class-ipytw-import-xml.php',
					__LINE__
				) );

				$count_offers_in_feed = (int) common_option_get( 'ipytw_count_elements', false, $this->get_feed_id(), 'ipytw' );
				if ( $count_offers_in_feed < 0 ) {
					if ( true === $import_helper_obj->set_count_elements() ) {
						// чтобы проверить, что всё работает мы тянем offers ещё из базы, а не напрямую из метода класса 
						$count_offers_in_feed = (int) common_option_get( 'ipytw_count_elements', false, $this->get_feed_id(), 'ipytw' );
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s %3$s. Файл: %4$s; Строка: %5$s',
							$this->get_feed_id(),
							'После работы set_count_elements() $count_offers_in_feed =',
							$count_offers_in_feed,
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						// также устанавливаем значение счётчика обработанных товаров
						ipytw_optionUPD( 'ipytw_last_element', 0, $this->get_feed_id() );
					} else {
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; WARNING: %2$s. Файл: %3$s; Строка: %4$s',
							$this->get_feed_id(),
							'В фиде нет товаров! Останавливаем импорт',
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						$this->stop( 'no_offers' );
						return;
					}
				}
				// $last_element - это номер последний обработанный элемент offer в фиде. Нумерация эл-тов с единицы
				$last_element = (int) ipytw_optionGET( 'ipytw_last_element', $this->get_feed_id() );

				$feed_imported_cat_arr = $this->get_imported_cat();
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; %2$s %3$s. %4$s %5$s. Файл: %6$s; Строка: %7$s',
					$this->get_feed_id(),
					'Последний обработанный элемент offer в фиде: $last_element =',
					$last_element,
					'Колчиество элементов offer в фиде: $count_offers_in_feed =',
					$count_offers_in_feed,
					'class-ipytw-import-xml.php',
					__LINE__
				) );
				if ( $count_offers_in_feed > $last_element ) {
					// ещё не все товары обработаны. Обработаем
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; %2$s $count_offers_in_feed = %3$s > $last_element = %4$s. Файл: %5$s; Строка: %6$s',
						$this->get_feed_id(),
						'Ещё не все товары в фиде обработаны. Обрабатываем',
						$count_offers_in_feed,
						$last_element,
						'class-ipytw-import-xml.php',
						__LINE__
					) );

					// $xml_parsing_obj - это объект класса IPYTW_XML_Parsing
					$xml_parsing_obj = $import_helper_obj->get_feed_our_site();
					$ipytw_external = common_option_get( 'ipytw_external', false, $this->get_feed_id(), 'ipytw' );

					while ( $last_element < $count_offers_in_feed ) {
						$i = $last_element + 1;
						$xml_parsing_obj->set_pointer_to( $i, 'offer' );
						$offer_simple_xml_element = $xml_parsing_obj->get_outer_xml();
						// получим атрибуты тега offer
						$offer_simple_xml_element_attr_obj = $offer_simple_xml_element->attributes();
						if ( null === $offer_simple_xml_element_attr_obj->group_id ) {
							// * это простой товар
							$feed_product_id = (string) $offer_simple_xml_element_attr_obj->id;

							$parsing_obj = new IPYTW_Import_Parsing(
								$offer_simple_xml_element,
								$feed_imported_cat_arr,
								$this->get_feed_id()
							);
							$args_arr = $parsing_obj->get_simple_arr();

							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; %2$s %3$s. %4$s %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								'ID товара в фиде $feed_product_id = ',
								$feed_product_id,
								'Массив данных после парсинга $parsing_obj->get_simple_arr() $args_arr = ',
								get_array_as_string( $args_arr ) . PHP_EOL,
								'class-ipytw-import-xml.php',
								__LINE__
							) );
							new IPYTW_Error_Log( '<<<' . serialize( $args_arr ) . '>>>' );

							if ( $ipytw_external === 'enabled' ) {
								$obj = new IPYTW_Import_Create_External_Product(
									$args_arr,
									$offer_simple_xml_element,
									$this->get_feed_id(),
									$feed_product_id
								);
							} else {
								$obj = new IPYTW_Import_Create_Simple_Product(
									$args_arr,
									$offer_simple_xml_element,
									$this->get_feed_id(),
									$feed_product_id
								);
							}

							$obj->add_product();
							$imported_ids = $obj->get_imported_ids();
						} else {
							// * это вариативный товар
							$all_offers_of_variation_product_arr = [];
							$all_offers_of_variation_product_arr[] = $offer_simple_xml_element;
							$feed_product_id = (string) $offer_simple_xml_element_attr_obj->group_id;
							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; %2$s %3$s %4$s; Файл: %5$s; Строка: %6$s',
								$this->get_feed_id(),
								'Работаем с вариативным товаром group_id = ',
								$feed_product_id,
								'($feed_product_id)',
								'class-ipytw-import-xml.php',
								__LINE__
							) );

							// если существует следующий offer в фиде устанавливаем на него курсор
							if ( true === $xml_parsing_obj->set_pointer_to( $i + 1, 'offer' ) ) {
								for ( $z = ( $i + 1 ); $z < $count_offers_in_feed; $z++ ) {
									$xml_parsing_obj->set_pointer_to( $z, 'offer' );
									$offer_simple_xml_element2 = $xml_parsing_obj->get_outer_xml();
									$offer_simple_xml_element_attr_obj2 = $offer_simple_xml_element2->attributes();
									if ( null === $offer_simple_xml_element_attr_obj2->group_id ) {
										// это не вариативный товар
										new IPYTW_Error_Log( sprintf(
											'FEED № %1$s; Оффер с индексом %2$s %3$s; Файл: %4$s; Строка: %5$s',
											$this->get_feed_id(),
											$z,
											'простой товар',
											'class-ipytw-import-xml.php',
											__LINE__
										) );
										break;
									} else {
										$feed_product_id_of_next_offer = (string) $offer_simple_xml_element_attr_obj2->group_id;
										if ( $feed_product_id == $feed_product_id_of_next_offer ) {
											$all_offers_of_variation_product_arr[] = $offer_simple_xml_element2;
											$i++; // увеличим индекс т.к. эту вариацию смотреть не надо потом
										} else {
											// это вариация другого товара
											new IPYTW_Error_Log( sprintf(
												'FEED № %1$s; Оффер с индексом %2$s %3$s; Файл: %4$s; Строка: %5$s',
												$this->get_feed_id(),
												$z,
												'вариация другого товара',
												'class-ipytw-import-xml.php',
												__LINE__
											) );
											break;
										}
									}
								}
							}

							if ( count( $all_offers_of_variation_product_arr ) == (int) 1 ) {
								// если у нас одна вариация
								$behaviour_one_variation = common_option_get( 'ipytw_behaviour_one_variation', false, $this->get_feed_id(), 'ipytw' );
								if ( $behaviour_one_variation === 'add_as_simple' ) {
									// передадим всё же group_id товара, а не id вариции на случай, если потом будет
									// несколько вариаций в фиде для данного товара
									$feed_product_id = (string) $offer_simple_xml_element_attr_obj->group_id;
									new IPYTW_Error_Log( sprintf(
										'FEED № %1$s; Оффер с индексом %2$s %3$s %4$s; Файл: %5$s; Строка: %6$s',
										$this->get_feed_id(),
										'Пробуем импортировать вариативный с group_id =',
										$feed_product_id,
										'как простой',
										'class-ipytw-import-xml.php',
										__LINE__
									) );

									$parsing_obj = new IPYTW_Import_Parsing(
										$offer_simple_xml_element,
										$feed_imported_cat_arr,
										$this->get_feed_id()
									);
									$args_arr = $parsing_obj->get_simple_arr();
									if ( $ipytw_external === 'enabled' ) {
										$obj = new IPYTW_Import_Create_External_Product(
											$args_arr,
											$offer_simple_xml_element,
											$this->get_feed_id(),
											$feed_product_id
										);
									} else {
										$obj = new IPYTW_Import_Create_Simple_Product(
											$args_arr,
											$offer_simple_xml_element,
											$this->get_feed_id(),
											$feed_product_id
										);
									}
									$obj->add_product();
									$imported_ids = $obj->get_imported_ids();
								} else {
									new IPYTW_Error_Log( sprintf(
										'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
										$this->get_feed_id(),
										'Пропускаем вариативный товар с единственной вариацией',
										'class-ipytw-import-xml.php',
										__LINE__
									) );
									$imported_ids = false; // пока пропустим
								}
							} else {
								$parsing_obj = new IPYTW_Import_Parsing(
									$all_offers_of_variation_product_arr,
									$feed_imported_cat_arr,
									$this->get_feed_id()
								);
								$args_arr = $parsing_obj->get_variable_arr();
								new IPYTW_Error_Log( sprintf(
									'FEED № %1$s; %2$s %3$s %4$s. %5$s %6$s; Файл: %7$s; Строка: %8$s',
									$this->get_feed_id(),
									'ID товара в фиде $feed_product_id = ',
									$feed_product_id,
									count( $all_offers_of_variation_product_arr ),
									'Массив данных после парсинга $parsing_obj->get_variable_arr() $args_arr = ',
									get_array_as_string( $args_arr ) . PHP_EOL,
									'class-ipytw-import-xml.php',
									__LINE__
								) );
								new IPYTW_Error_Log( '<<<' . serialize( $args_arr ) . '>>>' );

								$obj = new IPYTW_Import_Create_Variable_Product(
									$args_arr,
									$all_offers_of_variation_product_arr,
									$this->get_feed_id(),
									$feed_product_id
								);
								$obj->add_product();
								$imported_ids = $obj->get_imported_ids();
							}
						}

						if ( false === $imported_ids ) {
							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; $obj->get_imported_ids() вернула false; Файл: %2$s; Строка: %3$s',
								$this->get_feed_id(),
								'class-ipytw-import-xml.php',
								__LINE__
							) );
						} else {
							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; $obj->get_imported_ids() вернула true. %2$s; Файл: %3$s; Строка: %4$s',
								$this->get_feed_id(),
								'записываем кэш-файл',
								'class-ipytw-import-xml.php',
								__LINE__
							) );
							new IPYTW_Error_Log( $imported_ids );
							$this->ipytw_wf( $imported_ids ); // записываем кэш-файл
						}

						// https://wp-kama.ru/handbook/codex/object-cache
						wp_suspend_cache_addition( true );
						// ещё раз проверим необходимость остановки всей сборки
						$status_sborki = ipytw_optionGET( 'ipytw_status_sborki', $this->get_feed_id() );
						wp_suspend_cache_addition( false );
						if ( $status_sborki == -1 ) { // останавливаем сборку
							$this->stop( 'additional_stop' );
							break;
						}

						ipytw_optionUPD( 'ipytw_last_element', $i, $this->get_feed_id() );
						$last_element = $i;

						$unixtime = (int) current_time( 'timestamp', 1 );
						if ( $unixtime >= $timeout ) {
							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; $unixtime (%2$s) >= $timeout (%3$s). %4$s; Файл: %5$s; Строка: %6$s',
								$this->get_feed_id(),
								$unixtime,
								$timeout,
								'Ставим импорт на паузу на несколько секунд',
								'class-ipytw-import-xml.php',
								__LINE__
							) );
							break;
						} else {
							new IPYTW_Error_Log( sprintf(
								'FEED № %1$s; $unixtime (%2$s) < $timeout (%3$s). %4$s; Файл: %5$s; Строка: %6$s',
								$this->get_feed_id(),
								$unixtime,
								$timeout,
								'Пауза импорта пока не требуется',
								'class-ipytw-import-xml.php',
								__LINE__
							) );
						}
					}
				} else {
					ipytw_optionUPD( 'ipytw_status_sborki', 4, $this->get_feed_id() );
					return;
				}
				break;
			case 4:
				$ipytw_missing_product = ipytw_optionGET( 'ipytw_missing_product', $this->get_feed_id(), 'set_arr' );
				if ( $ipytw_missing_product === 'del'
					|| $ipytw_missing_product === 'del_with_pic'
					|| $ipytw_missing_product === 'out_of_stock' ) {
					$name_dir = IPYTW_PLUGIN_UPLOADS_DIR_PATH;
					if ( is_dir( $name_dir ) ) {
						$filename_new_id = $name_dir . '/feed-importetd-product-ids-new-' . $this->get_feed_id() . '.tmp';
					}

					$name_dir = IPYTW_PLUGIN_UPLOADS_DIR_PATH;
					if ( is_dir( $name_dir ) ) {
						$args = [ 
							'post_type' => 'product',
							'post_status' => 'publish',
							'posts_per_page' => -1,
							'relation' => 'AND',
							'fields' => 'ids',
							'meta_query' => [ 
								[ 
									'key' => '_ipytw_feed_id',
									'value' => (string) $this->get_feed_id()
								]
							]
						];

						$query = new WP_Query( $args );
						$old_ids_arr = [];
						if ( $query->have_posts() ) {
							$old_ids_arr = $query->posts; // id товаров, которые добавили в этой выгрузке
						}

						$filename_new_id = $name_dir . '/feed-importetd-product-ids-new-' . $this->get_feed_id() . '.tmp';
						$string1 = file_get_contents( $filename_new_id );
						$res_arr = explode( PHP_EOL, $string1 );

						$new_ids_arr = []; // id товаров, которые добавили в этой выгрузке
						for ( $i = 0; $i < count( $res_arr ); $i++ ) {
							$v = explode( '-fisi-', $res_arr[ $i ] );
							$new_ids_arr[] = (int) $v[1];
						}

						$del_ids_arr = [];
						foreach ( $old_ids_arr as $id ) {
							if ( in_array( $id, $new_ids_arr ) ) {

							} else { // нет в массиве
								$del_ids_arr[] = $id;
							}
						}

						if ( ! empty( $del_ids_arr ) ) {
							$del_ids_arr = array_unique( $del_ids_arr );
							foreach ( $del_ids_arr as $product_id ) {
								if ( $ipytw_missing_product === 'del' || $ipytw_missing_product === 'del_with_pic' ) {
									// !возможно нужно изменить последовательность удаления поста и вложения
									wooс_delete_product( $product_id );

									if ( $ipytw_missing_product === 'del_with_pic' ) {
										$remove_obj = new IPYTW_Remove_Attachments_Pictures();
										$remove_obj->remove_attachments( $product_id );
									}
								}

								if ( $ipytw_missing_product === 'out_of_stock' ) {
									$product = wc_get_product( $product_id );
									$product->set_manage_stock( false );
									$product->set_stock_status( 'outofstock' );
									// !возможно сюда надо дату обновления последнего синхрона
									// $site_product_id = $product->save();
								}
							}
						}
					}
				}
				$this->stop( 'full' );
				return;
			default:
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; Шаг default; $status_sborki = %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$status_sborki,
					'class-ipytw-import-xml.php',
					__LINE__
				) );
				$this->stop( 'default' );
				return;
		} // end switch($status_sborki)
		return; // final return from public function run()
	}

	/**
	 * Summary of copy_feed_from_source
	 * 
	 * @return bool
	 */
	public function copy_feed_from_source() {
		$ipytw_url_yml_file = common_option_get( 'ipytw_url_yml_file', false, $this->get_feed_id(), 'ipytw' );
		if ( $ipytw_url_yml_file == '' ) {
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; ERROR: %2$s: %3$s. %4$s; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Не указан URL фида',
				$ipytw_url_yml_file,
				'Остановим сборку',
				'class-ipytw-import-xml.php',
				__LINE__
			) );
			return false;
		}
		$xml_string = ipytw_file_get_contents_curl( $ipytw_url_yml_file, $this->get_feed_id() );
		if ( false === $xml_string ) {
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; ERROR: copy_feed: %2$s: %3$s. %4$s; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Нет доступа к файлу',
				$ipytw_url_yml_file,
				'Остановим сборку',
				'class-ipytw-import-xml.php',
				__LINE__
			) );
			return false;
		} else {
			if ( is_dir( IPYTW_PLUGIN_UPLOADS_DIR_PATH ) ) {
				$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/' . $this->get_feed_id() . '.xml';
				$fp = fopen( $filename, "w" );
				fwrite( $fp, $xml_string ); // записываем в файл текст
				fclose( $fp ); // закрываем
				ipytw_optionUPD( 'ipytw_copy_feed_from_source', $filename, $this->get_feed_id(), 'yes', 'set_arr' );
				return true;
			} else {
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s: %3$s. %4$s; Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Нет папки',
					IPYTW_PLUGIN_UPLOADS_DIR_PATH,
					'Остановим сборку',
					'class-ipytw-import-xml.php',
					__LINE__
				) );
				return false;
			}
		}
	}

	/**
	 * Summary of ipytw_wf
	 * 
	 * @param array $imported_ids_arr
	 * 
	 * @return void
	 */
	public function ipytw_wf( $imported_ids_arr ) {
		$name_dir = IPYTW_PLUGIN_UPLOADS_DIR_PATH;
		if ( ! is_dir( $name_dir ) ) {
			new IPYTW_Error_Log(
				'WARNING: Папка $name_dir =' . $name_dir . ' нет; Файл: functions.php; Строка: ' . __LINE__
			);
			if ( ! mkdir( $name_dir ) ) {
				new IPYTW_Error_Log(
					'ERROR: Создать папку $name_dir =' . $name_dir . ' не вышло; Файл: functions.php; Строка: ' . __LINE__
				);
			} else {
				// if (ipytw_optionGET('yzen_yandex_zen_rss') == 'enabled') {$result_yml = ipytw_optionGET('ipytw_feed_content');};
			}
		} else {
			// if (ipytw_optionGET('yzen_yandex_zen_rss') == 'enabled') {$result_yml = ipytw_optionGET('ipytw_feed_content');};
		}

		if ( is_dir( $name_dir ) ) {
			$filename = $name_dir . '/feed-importetd-product-ids-new-' . $this->get_feed_id() . '.tmp';
			$content = $imported_ids_arr['feed_product_id'] . '-fisi-' . $imported_ids_arr['site_product_id'] . PHP_EOL;
			file_put_contents( $filename, $content, FILE_APPEND );
		} else {
			new IPYTW_Error_Log(
				'ERROR: Нет папки import-from-yml! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__
			);
		}
	}

	/* для следующих версий
		  public function write_log($imported_ids_arr) {
			  $name_dir = IPYTW_PLUGIN_UPLOADS_DIR_PATH;
			  if (!is_dir($name_dir)) {
				  new IPYTW_Error_Log('WARNING: Папка $name_dir ='.$name_dir.' нет; Файл: functions.php; Строка: '.__LINE__);
				  if (!mkdir($name_dir)) {
					  new IPYTW_Error_Log('ERROR: Создать папку $name_dir ='.$name_dir.' не вышло; Файл: functions.php; Строка: '.__LINE__);
				  } else { 
					  // if (ipytw_optionGET('yzen_yandex_zen_rss') == 'enabled') {$result_yml = ipytw_optionGET('ipytw_feed_content');};
				  }
			  } else {
				  // if (ipytw_optionGET('yzen_yandex_zen_rss') == 'enabled') {$result_yml = ipytw_optionGET('ipytw_feed_content');};
			  }

			  if (is_dir($name_dir)) {  
				  $filename = $name_dir.'/feed-not-importetd-product-ids-'.$this->get_feed_id().'.tmp';
				  $content = $imported_ids_arr['feed_product_id'].'-fisi-'.$imported_ids_arr['site_product_id'].PHP_EOL;
				  file_put_contents($filename, $content, FILE_APPEND);
			  } else {
				  new IPYTW_Error_Log('ERROR: Нет папки import-from-yml! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__);
			  }
		  }*/

	/**
	 * Возвращает массив просинхронизированных категорий. 
	 * Где ключ - id массива в фиде. Значение - id категори у нас на сайте
	 *
	 * @return array
	 * 
	 */
	public function get_imported_cat() {
		new IPYTW_Error_Log( sprintf(
			'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			'Стартовала get_imported_cat',
			'class-ipytw-import-xml.php',
			__LINE__
		) );

		ipytw_check_dir( IPYTW_PLUGIN_UPLOADS_DIR_PATH );
		$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/feed-importetd-cat-ids-' . $this->get_feed_id() . '.tmp';

		if ( is_file( $filename ) ) {
			$result_arr = file_get_contents( $filename );
			return unserialize( $result_arr );
		} else {
			// Файла со списком просинхронизированных категорий нет
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; ERROR: Нет файла: %2$s. Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				$filename,
				'class-ipytw-import-xml.php',
				__LINE__
			) );
			return [];
		}
	}

	/**
	 * Stop import
	 * 
	 * @param string $status - Maybe `full`, `copy_err`, `yml_format_err`, `no_offers`, `additional_stop`, `default`
	 * 
	 * @return void
	 */
	public function stop( $status = 'full' ) {
		$status_sborki = -1;
		if ( 'once' === common_option_get( 'ipytw_run_cron', false, $this->get_feed_id(), 'ipytw' ) ) {
			// если была одноразовая сборка - переводим переключатель в `отключено`
			common_option_upd( 'ipytw_run_cron', 'disabled', 'no', $this->get_feed_id(), 'ipytw' );
			common_option_upd( 'ipytw_status_cron', 'off', 'no', $this->get_feed_id(), 'ipytw' );
		}
		if ( $status === 'full' ) {
			ipytw_optionUPD( 'ipytw_date_sborki_end', current_time( 'Y-m-d H:i' ), $this->get_feed_id(), 'yes', 'set_arr' );
		}
		ipytw_optionUPD( 'ipytw_status_sborki', $status_sborki, $this->get_feed_id() );
		ipytw_optionUPD( 'ipytw_last_element', '-1', $this->get_feed_id() );
		ipytw_optionUPD( 'ipytw_count_elements', -1, $this->get_feed_id(), 'yes', 'set_arr' );
		wp_clear_scheduled_hook( 'ipytw_cron_sborki', [ $this->get_feed_id() ] ); // останавливаем крон сборки
		do_action( 'ipytw_after_construct', $this->get_feed_id(), $status ); // сборка закончена
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}
}