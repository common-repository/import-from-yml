<?php
/**
 * Import of products
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   3.1.0
 * 
 * @version                 3.1.2 (15-02-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       	
 *
 * @depends                 classes:    IPYTW_XML_Parsing
 *                                      IPYTW_Error_Log
 *                                      IPYTW_Sorting_Categories
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      univ_option_get
 *                                      ipytw_optionUPD
 *                          constants:  IPYTW_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Import_XML_Helper {
	/**
	 * Summary of feed_id
	 * @var 
	 */
	private $feed_id;
	/**
	 * Summary of last_element
	 * @var 
	 */
	private $last_element;
	/**
	 * Summary of timeout
	 * @var 
	 */
	private $timeout;

	/**
	 * Summary of __construct
	 * 
	 * @param array $args_arr
	 */
	function __construct( $args_arr ) {
		if ( isset( $args_arr['feed_id'] ) ) {
			$this->feed_id = (string) $args_arr['feed_id'];
		}
		if ( isset( $args_arr['last_element'] ) ) {
			$this->last_element = (int) $args_arr['last_element'];
		}
		if ( isset( $args_arr['timeout'] ) ) {
			$this->timeout = (int) $args_arr['timeout'];
		}
	}

	/**
	 * Summary of import_cat
	 * 
	 * @return bool
	 */
	public function import_cat() {
		$unixtime = (string) current_time( 'Y-m-d H:i' ); // время в unix формате 
		ipytw_optionUPD( 'ipytw_date_sborki', $unixtime, $this->get_feed_id(), 'yes', 'set_arr' );

		$when_import_category = common_option_get( 'ipytw_when_import_category', false, $this->get_feed_id(), 'ipytw' );
		if ( $when_import_category === 'always' || $when_import_category === 'once' ) {
			$xml_object = $this->get_feed_our_site();
			$xml_object->set_pointer_to( 1, 'categories' );
			$xml_categories_object = $xml_object->get_outer_xml();
			// $xml_categories_object = $xml_object->shop->categories;
			$feed_categories_arr = []; // данные по всем категориям в фиде
			$feed_exists_cat_id_arr = []; // id всех существующих категорий в фиде. Нужен для проверки.
			for ( $i = 0; $i < count( $xml_categories_object->children() ); $i++ ) {
				if ( property_exists( $xml_categories_object->category[ $i ], 'name' ) ) {
					$category_name = $xml_categories_object->category[ $i ]->name;
				} else {
					$category_name = $xml_categories_object->category[ $i ];
				}
				$xml_category_attr_object = $xml_categories_object->category[ $i ]->attributes();

				$attr_id = $xml_category_attr_object->id;
				$attr_parent_id = $xml_category_attr_object->parentId; // если нет атрибута parentId вернёт null
				if ( null === $attr_parent_id ) {
					$attr_parent_id = 0;
					// чтобы потом легче обрабатывать, сразу запихиваем в начало массива родительскую категорию
					array_unshift( $feed_categories_arr, [ 
						'id' => (int) $attr_id,
						'parent_id' => (int) $attr_parent_id,
						'name' => (string) $category_name
					] );
				} else {
					$feed_categories_arr[] = [ 
						'id' => (int) $attr_id,
						'parent_id' => (int) $attr_parent_id,
						'name' => (string) $category_name
					];
				}
				$feed_exists_cat_id_arr[] = (int) $attr_id;
			}
			unset( $xml_categories_object );
			$sorting_cat = new IPYTW_Sorting_Categories( $feed_categories_arr );
			$feed_categories_arr = $sorting_cat->get_new_arr();
			unset( $x );

			$i = 0;
			$find_cat_arr = [];
			while ( 0 < count( $feed_categories_arr ) ) {
				new IPYTW_Error_Log( '$feed_categories_arr[' . $i . '][id] = ' . $feed_categories_arr[ $i ]['id'] );
				// если это родительская категория
				if ( 0 == $feed_categories_arr[ $i ]['parent_id'] ) {
					$category_id = $feed_categories_arr[ $i ]['id'];
					$attr_parent_id = $feed_categories_arr[ $i ]['parent_id'];
					$category_name = $feed_categories_arr[ $i ]['name'];
					wp_suspend_cache_invalidation( true ); // с версии WP старше 6.0 нам может мешать КЭШ
					$category = term_exists( $category_name, 'product_cat' );
					wp_suspend_cache_invalidation( false );
					if ( null === $category ) {
						// таксономии нет. Нужно создать
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s $category_name = %3$s; $attr_parent_id = %4$s; $i = %5$s; Файл: %6$s; Строка: %7$s',
							$this->get_feed_id(),
							'Категории нет. Нужно создать.',
							$category_name,
							$attr_parent_id,
							(string) $i,
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						$data_arr = [];
						$data_arr['parent'] = $attr_parent_id;
						$insert_data = wp_insert_term( $category_name, 'product_cat', $data_arr );
						// update_term_meta($term_id, 'yml-importer:id', $id);

						if ( ! is_wp_error( $insert_data ) ) {
							// добавим её id в массив найденных 
							$find_cat_arr[ $feed_categories_arr[ $i ]['id'] ] = $insert_data['term_id'];
							// ! update_term_meta($insert_data['term_id'], 'ipytd_fid_cid', $this->get_feed_id().'-'.$category_id);
						}
					} else { // категория есть. Нужно обновить
						// if ( get_term_meta( $category['term_id'], 'ipytd_fid_cid', true ) !== '' ) {
						// 	$ipytd_fid_cid = get_term_meta( $category['term_id'], 'ipytd_fid_cid', true );
						// 	$myArray = explode( '-', $myString );
						// 	$sample = $this->get_feed_id() . '-' . $category_id;
						// 	if ( $sample == $ipytd_fid_cid ) { // нужно обновить категорию

						// 	} else { // нужно создать

						// 	}
						// }

						// new IPYTW_Error_Log( 
						//	'категория есть. Нужно обновить. $category_id = ' . $category_id . '; Файл: class-ipytw-import-xml-helper.php; Строка: ' . __LINE__ );
						// $category_id = $category['term_id'];
						// $insert_data = wp_update_term( $category_id, 'product_cat', array(
						// 	'name' => $category_name,
						// 	'parent' => $attr_parent_id
						// ) );

						// if ( ! is_wp_error( $insert_data ) ) { // добавим её id в массив найденных 
						// 	$find_cat_arr[ $feed_categories_arr[ $i ]['id'] ] = $insert_data['term_id']; 
						// }

						$category_id = $category['term_id'];
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
							$this->get_feed_id(),
							'категория есть. Пропускаем. $category_id =',
							$category_id,
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						$find_cat_arr[ $feed_categories_arr[ $i ]['id'] ] = $category_id;
					}
					array_splice( $feed_categories_arr, $i, 1 ); // сократим массив
					$i = 0;
				} else { // это дочерняя категория
					// проверим от глюков в фиде
					if ( ! in_array( $feed_categories_arr[ $i ]['parent_id'], $feed_exists_cat_id_arr ) ) {
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s %3$s $attr_parent_id = %4$s Файл: %5$s; Строка: %6$s',
							$this->get_feed_id(),
							'NOTICE: В фиде есть ошибка! Родительской категории с id = ',
							$feed_categories_arr[ $i ]['parent_id'],
							'Не существует! Пропустим данную категорию',
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						array_splice( $feed_categories_arr, $i, 1 );
						$i = 0;
						continue;
					}

					if ( array_key_exists( $feed_categories_arr[ $i ]['parent_id'], $find_cat_arr ) ) {
						// категория-родитель уже просинхронена. Можно добавить категорию.
						$category_id = $feed_categories_arr[ $i ]['id'];
						$attr_parent_id = $find_cat_arr[ $feed_categories_arr[ $i ]['parent_id'] ]; // ! важное различие с 1-м вариантом
						$category_name = $feed_categories_arr[ $i ]['name'];
						wp_suspend_cache_invalidation( true ); // с версии WP старше 6.0 нам может мешать КЭШ
						$category = term_exists( $category_name, 'product_cat' );
						wp_suspend_cache_invalidation( false );
						if ( null === $category ) { // таксономии нет. Нужно создать
							$data_arr = [];
							$data_arr['parent'] = $attr_parent_id;
							$insert_data = wp_insert_term( $category_name, 'product_cat', $data_arr );
							// update_term_meta($term_id, 'yml-importer:id', $id);

							if ( ! is_wp_error( $insert_data ) ) {
								// добавим её id в массив найденных 
								$find_cat_arr[ $feed_categories_arr[ $i ]['id'] ] = $insert_data['term_id'];
								// ! update_term_meta($insert_data['term_id'], 'ipytd_fid_cid', $this->get_feed_id().'-'.$category_id);
							}
						} else { // категория есть. Нужно обновить
							if ( is_array( $category ) ) {
								//	$category_id = $category['term_id'];
								//	$insert_data = wp_update_term($category_id, 'product_cat', array(
								//	/* ! */ 'name' => $category_name, 
								// иногда даёт ошибку PHP Notice:  Trying to get property 'category_id' of non-object in хз почему
								//		'parent' => $attr_parent_id
								//	));
								//	if (!is_wp_error($insert_data)) {
								// добавим её id в массив найденных 
								//		$find_cat_arr[$feed_categories_arr[$i]['id']] = $insert_data['term_id']; 
								//	}
								//

								$category_id = $category['term_id'];
								new IPYTW_Error_Log( sprintf(
									'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
									$this->get_feed_id(),
									'категория есть. Пропускаем. $category_id = ',
									$category_id,
									'class-ipytw-import-xml.php',
									__LINE__
								) );
								$find_cat_arr[ $feed_categories_arr[ $i ]['id'] ] = $category_id;
							} else {
								new IPYTW_Error_Log( sprintf(
									'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
									$this->get_feed_id(),
									'NOTICE: Логируем $category = ',
									$category,
									'class-ipytw-import-xml.php',
									__LINE__
								) );
							}
						}
						array_splice( $feed_categories_arr, $i, 1 ); // сократим массив
						$i = 0;
					} else { // категорию-родитель ещё не синхронили. Пока пропустим
						$i++;
						continue;
					}
				}
			}

			$res = ipytw_check_dir( IPYTW_PLUGIN_UPLOADS_DIR_PATH );
			if ( false === $res ) {
				error_log( sprintf( '%s %s %s %s',
					'ERROR: ipytw_feed_header: Создать папку IPYTW_PLUGIN_UPLOADS_DIR_PATH =',
					IPYTW_PLUGIN_UPLOADS_DIR_PATH,
					'; Файл: class-ipytw-import-xml-helper.php; Строка: ',
					__LINE__
				), 0 );
				return false;
			}

			if ( is_dir( IPYTW_PLUGIN_UPLOADS_DIR_PATH ) ) {
				$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/feed-importetd-cat-ids-' . $this->get_feed_id() . '.tmp';
				$fp = fopen( $filename, "w" );
				$ids_in_yml = serialize( $find_cat_arr );
				fwrite( $fp, $ids_in_yml );
				fclose( $fp );
				if ( $when_import_category === 'once' ) {
					ipytw_optionUPD( 'ipytw_when_import_category', 'disabled', $this->get_feed_id(), 'yes', 'set_arr' );
				}
			} else {
				error_log( sprintf( '%s %s %s %s',
					'ERROR: ipytw_feed_header: Нет папки import-from-yml! IPYTW_PLUGIN_UPLOADS_DIR_PATH =',
					IPYTW_PLUGIN_UPLOADS_DIR_PATH,
					'; Файл: class-ipytw-import-xml-helper.php; Строка:',
					__LINE__
				), 0 );
				return false;
			}
		} // end if ( $when_import_category === 'always' || $when_import_category === 'once' )
		return true;
	} // end public function import_cat()

	/**
	 * Set count elements
	 * 
	 * @return bool (true - элементы найдены и их больше 0; false - в остальных случаях)
	 */
	public function set_count_elements() {
		$xml_object = $this->get_feed_our_site();
		$ipytw_count_elements = $xml_object->element_count( 'offer' );
		if ( $ipytw_count_elements > 0 ) {
			ipytw_optionUPD( 'ipytw_count_elements', $ipytw_count_elements, $this->get_feed_id(), 'yes', 'set_arr' );
			return true;
		} else {
			ipytw_optionUPD( 'ipytw_count_elements', 0, $this->get_feed_id(), 'yes', 'set_arr' );
			return false;
		}
	}

	/**
	 * Get feed our site
	 * 
	 * @return IPYTW_XML_Parsing
	 */
	public function get_feed_our_site() {
		$filename = IPYTW_PLUGIN_UPLOADS_DIR_PATH . '/' . $this->get_feed_id() . '.xml';
		$xml_object = new IPYTW_XML_Parsing( $filename );
		return $xml_object;
	}

	/**
	 * Checking the YML-fomrat
	 * 
	 * @return bool
	 */
	public function check_yml_format() {
		$xml_object = $this->get_feed_our_site();
		// пытаемся найти в фиде всего один элемент yml_catalog. Больше и не нужно.
		if ( 1 === $xml_object->element_count( 'yml_catalog', 1 ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get index of last element
	 * 
	 * @return int
	 */
	public function get_last_element() {
		return $this->last_element;
	}

	/**
	 * Timeout in milliseconds
	 * 
	 * @return int
	 */
	public function get_timeout() {
		return $this->timeout;
	}
}