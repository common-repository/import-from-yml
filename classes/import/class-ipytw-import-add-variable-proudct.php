<?php
/**
 * Import of variable products
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 3.1.6 (31-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param        
 *
 * @depends                 classes:    IPYTW_Import_Create_Product_Attributes
 *                                      IPYTW_Download_Pictures
 *                                      IPYTW_Error_Log
 *                                      WC_Product
 *                                      WC_Product_Variable
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
/*
$args_arr = array(
	'name' => 'Товар через класс',
	'description' => 'Описание',
	'sku' => '0001',
	'catid' => array(72, 74),
	'stock_status' => 'instock',
	'pictures' => array(
		'https://icopydoc.ru/wp-content/uploads/2021/09/210924-import-from-yml-pro.jpg',
		'https://icopydoc.ru/wp-content/uploads/2020/10/201004-import-products-to-ok-ru-pro.jpg'
	),
	'attributes_simple' => array(
		"Производитель" => array("Адидас"), 
		"Страна" => array('Россия')
	),
	'attributes_variable' => array(
		"Color2" => array("blue", "red"), 
		"Size3" => array('46', '48')
	),
	'variations' => array(
			array(
				'attributes' => array(
					array('name' => 'Color2', 'value' => 'blue'),
					array('name' => 'Size3', 'value' => '46')
				),
				'url' => 'https://icopydoc.ru/product_var/1',
				'feed_var_id' => 1,
				'description' => 'Описание вариации1',
				'sku' => '0001',
				'regular_price' => 60,
				'sale_price' => 50,
				'picture' => array(''),
				'stock_status' => 'instock',
				'length' => 50,
				'width' => 50,
				'height' => 50,
				'weight' => 50,
				'barcode' => '3182550724142'
			),
			array(
				'attributes' => array(
					array('name' => 'Color2', 'value' => 'blue'),
					array('name' => 'Size3', 'value' => '48')
				),
				'url' => 'https://icopydoc.ru/product_var/3',
				'feed_var_id' => 3,
				'description' => 'Описание вариации2',
				'sku' => '0002',
				'regular_price' => 150,
				'sale_price' => 250,
				'picture' => array(''),
				'stock_status' => 'instock',
				'length' => 50,
				'width' => 50,
				'height' => 50,
				'weight' => 50,
				'barcode' => '3182550724159
			)			
	)
);
*/
defined( 'ABSPATH' ) || exit;

class IPYTW_Import_Create_Variable_Product {
	private $all_offers_of_variation_product;
	private $feed_id;
	private $feed_product_id;

	private $name;
	private $description;
	private $sku;
	private $catid_arr;
	private $stock_status;
	private $pictures_arr;
	private $attributes_simple_arr;
	private $attributes_variable_arr;
	private $variations_arr;

	private $imported_ids = false;

	/**
	 * Import of variable products
	 * 
	 * @param array $args_arr
	 * @param object $all_offers_of_variation_product
	 * @param string $feed_id
	 * @param string $feed_product_id
	 */
	public function __construct( $args_arr, $all_offers_of_variation_product, $feed_id, $feed_product_id ) {
		$this->all_offers_of_variation_product = $all_offers_of_variation_product;
		$this->feed_id = $feed_id;
		$this->feed_product_id = $feed_product_id;

		$this->name = $args_arr['name'];
		$this->description = $args_arr['description'];
		$this->sku = $args_arr['sku'];
		$this->catid_arr = $args_arr['catid'];
		$this->stock_status = $args_arr['stock_status'];
		$this->pictures_arr = $args_arr['pictures'];
		$this->attributes_simple_arr = $args_arr['attributes_simple'];
		$this->attributes_variable_arr = $args_arr['attributes_variable'];
		$this->variations_arr = $args_arr['variations'];
	}

	public function upd_product( $product ) {
		//  if ($this->get_sku() !== '' && wc_get_product_id_by_sku($this->get_sku()) > 0) {
		//	  $product = wc_get_product_id_by_sku($this->get_sku());
		//  } else {
		//	  $product = new WC_Product();
		//  }

		$ipytw_product_was_sync = ipytw_optionGET( 'ipytw_product_was_sync', $this->get_feed_id(), 'set_arr' );
		switch ( $ipytw_product_was_sync ) {
			case 'whole':
				return false;
			case 'whole_except_cat':
				$this->catid_arr = $product->get_category_ids(); // $args_arr['catid'];
				return false;
			case 'price_only':
				for ( $i = 0; $i < count( $this->get_variations_arr() ); $i++ ) {
					$variation_data_arr = $this->get_variations_arr()[ $i ];
					$check_sync_product = ipytw_check_sync( $variation_data_arr['feed_var_id'], $this->get_feed_id(), 'product_variation' );
					if ( false === $check_sync_product ) {
						continue;
					} else {
						$var_id = (int) $check_sync_product;
						new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариация ранее была импортирована; $var_id = ' . $var_id . '. Обновляем цену; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
						$variation = wc_get_product( $var_id );
					}

					if ( $variation_data_arr['sale_price'] > 0 ) {
						$variation->set_regular_price( $variation_data_arr['sale_price'] );
						$variation->set_sale_price( $variation_data_arr['regular_price'] );
					} else {
						$variation->set_regular_price( $variation_data_arr['regular_price'] );
						$variation->set_sale_price( '' );
					}
					$variation->save();
					$unixtime = (string) current_time( 'Y-m-d H:i' );
					$var_id = $variation->get_id();
					update_post_meta( $var_id, '_ipytw_date_last_import', $unixtime );
				}
				return true;
			case 'stock_only':
				for ( $i = 0; $i < count( $this->get_variations_arr() ); $i++ ) {
					$variation_data_arr = $this->get_variations_arr()[ $i ];
					$check_sync_product = ipytw_check_sync( $variation_data_arr['feed_var_id'], $this->get_feed_id(), 'product_variation' );
					if ( false === $check_sync_product ) {
						continue;
					} else {
						new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариация ранее была импортирована; $var_id = ' . $var_id . '. Обновляем остатки; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
						$var_id = (int) $check_sync_product;
						$variation = wc_get_product( $var_id );
					}

					$variation->set_stock_status( $variation_data_arr['stock_status'] );
					$variation = apply_filters( 'ipytw_f_product_upd_case_stock_only', $variation, array( 'variation_data_arr' => $variation_data_arr ), $this->get_feed_id() );

					$variation->save();
					$unixtime = (string) current_time( 'Y-m-d H:i' );
					$var_id = $variation->get_id();
					update_post_meta( $var_id, '_ipytw_date_last_import', $unixtime );
				}
				return true;
			case 'price_and_stock':
				for ( $i = 0; $i < count( $this->get_variations_arr() ); $i++ ) {
					$variation_data_arr = $this->get_variations_arr()[ $i ];
					$check_sync_product = ipytw_check_sync( $variation_data_arr['feed_var_id'], $this->get_feed_id(), 'product_variation' );
					if ( false === $check_sync_product ) {
						continue;
					} else {
						new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариация ранее была импортирована; $var_id = ' . $var_id . '. Обновляем цену и остатки; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
						$var_id = (int) $check_sync_product;
						$variation = wc_get_product( $var_id );
					}

					if ( $variation_data_arr['sale_price'] > 0 ) {
						$variation->set_regular_price( $variation_data_arr['sale_price'] );
						$variation->set_sale_price( $variation_data_arr['regular_price'] );
					} else {
						$variation->set_regular_price( $variation_data_arr['regular_price'] );
						$variation->set_sale_price( '' );
					}

					$variation->set_stock_status( $variation_data_arr['stock_status'] );
					$variation = apply_filters(
						'ipytw_f_variation_product_upd_case_price_and_stock',
						$variation,
						[ 'variation_data_arr' => $variation_data_arr ],
						$this->get_feed_id()
					);

					$variation->save();
					$unixtime = (string) current_time( 'Y-m-d H:i' );
					$var_id = $variation->get_id();
					update_post_meta( $var_id, '_ipytw_date_last_import', $unixtime );
				}

				// $site_product_id = $product->save();
				// $this->save_info_about_sync($site_product_id);

				return true;
			case 'dont_update':
				return true;
			default:
				// $product = apply_filters('ipytw_variation_product_upd_case_default_filter', $product, array('get_all_offers_of_variation_product' => $this->get_all_offers_of_variation_product()), $this->get_feed_id());
				return true;
		}
	}

	public function add_product() {
		$ipytw_whot_import = ipytw_optionGET( 'ipytw_whot_import', $this->get_feed_id(), 'set_arr' );
		if ( $ipytw_whot_import !== 'all' ) {
			return false;
		}

		$check_sync_product = ipytw_check_sync( $this->get_feed_product_id(), $this->get_feed_id(), 'product' );
		if ( false === $check_sync_product ) {
			new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариативный товар ' . $this->get_feed_product_id() . ' ранее не был импортирован; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
			// тут нужна доработка. В данном случае мы просто пропускаем вариативный товар, если есть такой же артикул.
			// однако, для вариативных, такеже как и для простых товаров, надо бы настроить чтобы пахала опция
			// замены товары с таким же артикулом. Но есть сложность. Не всегда реально сделать автозамену т.к
			// артикулы могут пересекаться у какой-то одной вариации или сразу с разными простыми товарами...
			// $ipytw_if_isset_sku = ipytw_optionGET('ipytw_if_isset_sku', $this->get_feed_id(), 'set_arr');
			if ( ipytw_is_uniq_sku( $this->get_sku() ) === true ) {
				new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Но мы его пропускаем из-за пересечения артикулов с другими товарами; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
				return false;
			}
			$product = new WC_Product();
		} else {
			$site_product_id = (int) $check_sync_product;
			new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариативный товар ранее был импортирован $site_product_id = ' . $site_product_id . '; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
			$product = wc_get_product( $site_product_id );
			if ( $product->is_type( 'simple' ) ) {
				$product = new WC_Product_Variable( $site_product_id );
				$site_product_id = $product->save();
				$product = wc_get_product( $site_product_id );
				new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариативный $site_product_id = ' . $site_product_id . ' товар ранее был простым, а теперь товар стал ' . $product->get_type() . '; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
				// полностью обновим
			} else {
				$r = $this->upd_product( $product );
				if ( $r == true ) {
					return $site_product_id;
				}
			}
		}

		$product->set_name( $this->get_name() );

		$ipytw_description_into = ipytw_optionGET( 'ipytw_description_into', $this->get_feed_id(), 'set_arr' );
		if ( $ipytw_description_into === 'excerpt' ) {
			$product->set_short_description( $this->get_description() );
		} else {
			$product->set_description( $this->get_description() );
		}

		$product->set_sku( $this->get_sku() );
		$product->set_category_ids( $this->get_catid() );
		$product->set_stock_status( $this->get_stock_status() );

		$product = apply_filters( 'ipytw_var_product_add_before_save_filter',
			$product,
			[ 
				'get_all_offers_of_variation_product' => $this->get_all_offers_of_variation_product()
			],
			$this->get_feed_id()
		);
		$site_product_id = $product->save();

		$obj1 = new IPYTW_Import_Create_Product_Attributes( $this->get_attributes_simple_arr(), $site_product_id );
		$attribs1 = $obj1->create( false ); // false - тк атрибуты не вариативные
		$obj2 = new IPYTW_Import_Create_Product_Attributes( $this->get_attributes_variable_arr(), $site_product_id );
		$attribs2 = $obj2->create( true ); // true - тк атрибуты вариативные
		$attribs = array_merge( $attribs1, $attribs2 );
		$product = new WC_Product_Variable( $site_product_id );
		$product->set_props( array(
			'attributes' => $attribs,
			// Set any other properties of the product here you want - price, name, etc.
		) );
		$site_product_id = $product->save();

		$img_ids_arr = [];
		foreach ( $this->get_pictures_arr() as $feed_picture_url ) {
			$check_sync_img = ipytw_check_sync( $feed_picture_url, $this->get_feed_id(), 'attachment' ); /* ! */// picture 
			if ( false === $check_sync_img ) {

				$dwlp_obj = new IPYTW_Download_Pictures( $feed_picture_url, [ 'name' => $this->get_name() ], $this->get_feed_id() );
				if ( $dwlp_obj->get_image_id() === -1 ) {
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; %2$s url = %3$s. Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						'Ошибка загрузки изображения',
						$feed_picture_url,
						'class-ipytw-import-xml.php',
						__LINE__
					) );
				} else {
					$image_id = $dwlp_obj->get_image_id();
					unset( $dwlp_obj );
					if ( ! class_exists( 'ImportProductsYMLtoWooCommercePro' ) ) {
						$product->set_image_id( $image_id );
						break;
					} else {
						$img_ids_arr[] = $image_id;
						do_action( 'ipytw_img_add', [ 'img_id' => $image_id, 'product' => $product ] );
					}
				}

			} else {
				$image_id = $check_sync_img;
				if ( ! class_exists( 'ImportProductsYMLtoWooCommercePro' ) ) {
					$product->set_image_id( $image_id );
					break;
				} else {
					$img_ids_arr[] = $image_id;
					do_action( 'ipytw_img_add', [ 'img_id' => $image_id, 'product' => $product ] );
				}
			}
		}
		do_action( 'ipytw_imgs_add', [ 'img_ids_arr' => $img_ids_arr, 'product' => $product ] );

		$site_product_id = $product->save();

		// https://hotexamples.com/examples/-/WC_Product_Variation/-/php-wc_product_variation-class-examples.html
		for ( $i = 0; $i < count( $this->get_variations_arr() ); $i++ ) {
			$variation_data_arr = $this->get_variations_arr()[ $i ];
			if ( isset( $variation_data_arr['attributes'] ) ) {
				// нужно
				$attributes_arr = $variation_data_arr['attributes'];
			} else {
				// * такое происходит потому, что group_id в фиде есть, а param-ов у вариации - нет.
				new IPYTW_Error_Log(
					sprintf(
						'FEED № %1$s; NOTICE: product_id = %2$s - %3$s; Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						$site_product_id,
						'group_id в фиде есть, а param-ов у вариации нет',
						'class-ipytw-import-add-variable-product.php',
						__LINE__
					)
				);
				$attributes_arr = [];
			}

			$for_set_attributes_arr = [];
			for ( $ii = 0; $ii < count( $attributes_arr ); $ii++ ) {
				$pa_slug = (string) $this->get_attribute_pa_slug( $attributes_arr[ $ii ]['name'] );
				$for_set_attributes_arr[ $pa_slug ] = $this->get_attribute_value_pa_slug( (string) $attributes_arr[ $ii ]['value'], $pa_slug ); // (string)$attributes_arr[$ii]['value'];
			}

			$check_sync_product = ipytw_check_sync( $variation_data_arr['feed_var_id'], $this->get_feed_id(), 'product_variation' );
			if ( false === $check_sync_product ) {
				new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариация ранее не была импортирована; $this->get_feed_product_id() = ' . $this->get_feed_product_id() . '; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
				$variation = new \WC_Product_Variation();
				$variation->set_parent_id( $product->get_id() );
				$variation->set_attributes( $for_set_attributes_arr ); // array('pa_color2' => 'blue', 'pa_size3' => '46')
				$variation->save();
				// echo get_array_as_string($for_set_attributes_arr, '<br/>');
			} else {
				$var_id = (int) $check_sync_product;
				new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Вариация ранее была импортирована; $var_id = ' . $var_id . '; Файл: class-ipytw-import-add-variable-product.php; Строка: ' . __LINE__ );
				$variation = wc_get_product( $var_id );
				// $variation->set_attributes($for_set_attributes_arr); // array('pa_color2' => 'blue', 'pa_size3' => '46')
				// $variation->save();
			}

			if ( $variation_data_arr['sale_price'] > 0 ) {
				$variation->set_regular_price( $variation_data_arr['sale_price'] );
				$variation->set_sale_price( $variation_data_arr['regular_price'] );
			} else {
				$variation->set_regular_price( $variation_data_arr['regular_price'] );
				$variation->set_sale_price( '' );
			}
			$variation->set_length( $variation_data_arr['length'] ); // Задаёт длину товара
			$variation->set_width( $variation_data_arr['width'] ); // Задаёт ширину товара
			$variation->set_height( $variation_data_arr['height'] ); // Задаёт высоту товара */
			$variation->set_weight( $variation_data_arr['weight'] ); // Задаёт вес товара
			$variation->set_stock_status( $variation_data_arr['stock_status'] );
			if ( $product->set_description( $this->get_description() !== $variation_data_arr['description'] ) ) {
				// если описание у каждой вариации уникально
				$variation->set_description( $variation_data_arr['description'] );
			}
			/** 
			 * артикул для отдельной вариации НЕ прописываем если:
			 * 1 - он пуст
			 * 2 - он совпадает с общим артикулом товара
			 * 3 - он есть на сайте у другого товара
			 */
			$flag_no_sku = false;
			if ( empty( $variation_data_arr['sku'] ) ) {
				$flag_no_sku = true;
			}
			if ( $this->get_sku() == $variation_data_arr['sku'] ) {
				$flag_no_sku = true;
			}
			// если первые два условия не сработали
			if ( $flag_no_sku == false ) { // чекаем, есть ли такой артикул у др.товара
				// сперва чекаем, есть ли в принципе такой артикул
				$v_id = (int) wc_get_product_id_by_sku( $variation_data_arr['sku'] );
				if ( $v_id > 0 ) {
					// такой артикул есть. чекаем, может это артикул вариации которую мы редачим?
					if ( (int) $check_sync_product == (int) $v_id ) {
						// да это наша вариация
					} else {
						// нет, значит артикул нельзя добавлять
						$flag_no_sku = true;
					}
				}
			}
			if ( $flag_no_sku == false ) {
				$variation->set_sku( $variation_data_arr['sku'] );
			}

			// $variation_2->set_date_on_sale_to('32532537600');
			//		$variation->set_status('private');
			$data_arr = [];
			$data_arr['data']['variation_data_arr'] = $variation_data_arr;
			$variation = apply_filters( 'ipytw_f_variation_product_add_before_save', $variation, $data_arr, $this->get_feed_id() );
			$variation->save();
			// Now update some value unrelated to attributes.
			$variation = wc_get_product( $variation->get_id() );
			$variation->set_status( 'publish' );
			$variation->save();

			// блок отвечает за индивидуальные картинки вариаций
			if ( ! empty( $variation_data_arr['picture'] ) && empty( array_diff( $variation_data_arr['picture'], $this->get_pictures_arr() ) ) ) {
				$feed_picture_url = $variation_data_arr['picture'][0]; // первая картинка для вариации
				$check_sync_img = ipytw_check_sync( $feed_picture_url, $this->get_feed_id(), 'attachment' );
				if ( false === $check_sync_img ) {

					$dwlp_obj = new IPYTW_Download_Pictures( $feed_picture_url, [ 'name' => $this->get_name() ], $this->get_feed_id() );
					if ( $dwlp_obj->get_image_id() === -1 ) {
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s url = %3$s. Файл: %4$s; Строка: %5$s',
							$this->get_feed_id(),
							'Ошибка загрузки изображения',
							$feed_picture_url,
							'class-ipytw-import-xml.php',
							__LINE__
						) );
					} else {
						$image_id = $dwlp_obj->get_image_id();
						$variation->set_image_id( $image_id );

						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; %2$s id = %3$s %4$s url = %5$s image_id = %6$s. Файл: %7$s; Строка: %8$s',
							$this->get_feed_id(),
							'Прописываем вариации',
							$variation->get_id(),
							'картинку',
							$feed_picture_url,
							$image_id,
							'class-ipytw-import-xml.php',
							__LINE__
						) );
						$variation->save();
					}

				} else {
					$image_id = $check_sync_img;
					$variation->set_image_id( $image_id );

					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; %2$s id = %3$s %4$s url = %5$s image_id = %6$s. Файл: %7$s; Строка: %8$s',
						$this->get_feed_id(),
						'Прописываем вариации',
						$variation->get_id(),
						'картинку',
						$feed_picture_url,
						$image_id,
						'class-ipytw-import-xml.php',
						__LINE__
					) );
					$variation->save();
				}
			}

			$unixtime = (string) current_time( 'Y-m-d H:i' );
			$var_id = $variation->get_id();

			$this->set_barcode( $var_id, $variation_data_arr['barcode'], $variation );

			update_post_meta( $var_id, '_ipytw_feed_var_id', (string) $variation_data_arr['feed_var_id'] ); // id вариации товара в фиде
			update_post_meta( $var_id, '_ipytw_feed_id', $this->get_feed_id() ); // id фида
			update_post_meta( $var_id, '_ipytw_date_last_import', $unixtime );
		}

		//	Create a new variation with the color 'green'.
		//  $variation = new \WC_Product_Variation();
		//  $variation->set_parent_id($product->get_id());
		//  $variation->set_attributes(array('pa_color2' => 'blue', 'pa_size3' => '112'));
		//  $variation->set_regular_price(22);
		//  $variation->set_status('private');
		//  $variation->save();
		//  // Now update some value unrelated to attributes.
		//  $variation = wc_get_product($variation->get_id());
		//  $variation->set_status('publish');
		//  $variation->save();

		$ipytw_post_status = common_option_get( 'ipytw_post_status', false, $this->get_feed_id(), 'ipytw' );
		$product->set_status( $ipytw_post_status );

		$this->save_info_about_sync( $site_product_id );
		return $site_product_id;
	}

	/**
	 * Set barcode
	 * 
	 * @param string $site_product_id
	 * @param string $barcode_val
	 * @param WC_Product_Variation|WC_Product $variation
	 * 
	 * @return void
	 */
	private function set_barcode( $site_product_id, $barcode_val, $variation ) {
		$ipytw_barcode = common_option_get( 'ipytw_barcode', false, $this->get_feed_id(), 'ipytw' );
		if ( $ipytw_barcode === 'post_meta' ) {
			$post_meta_value = common_option_get( 'ipytw_barcode_post_meta_value', false, $this->get_feed_id(), 'ipytw' );
			if ( ! empty( $barcode_val ) && ! empty( $post_meta_value ) ) {
				update_post_meta( $site_product_id, $post_meta_value, $barcode_val );
			}
		} else if ( $ipytw_barcode === 'global_unique_id' ) {
			$variation->set_global_unique_id( $barcode_val );
			$variation->save();
		}
	}

	/**
	 * Save product synchronization information
	 * 
	 * @param string $site_product_id
	 * 
	 * @return void
	 */
	private function save_info_about_sync( $site_product_id ) {
		$unixtime = (string) current_time( 'Y-m-d H:i' );
		update_post_meta( $site_product_id, '_ipytw_feed_product_id', $this->get_feed_product_id() ); // id товара в фиде
		update_post_meta( $site_product_id, '_ipytw_feed_id', $this->get_feed_id() ); // id фида
		update_post_meta( $site_product_id, '_ipytw_date_last_import', $unixtime );

		$imported_ids = [];
		$imported_ids['feed_product_id'] = $this->get_feed_product_id();
		$imported_ids['site_product_id'] = $site_product_id;
		$imported_ids['feed_product_cat'] = 'null';
		$imported_ids['site_product_cat'] = $this->get_catid();
		$imported_ids['product_price'] = 'null';
		$this->imported_ids = $imported_ids;
		return;
	}

	/**
	 * Get attribute `pa_` slug
	 * 
	 * @param string $search_string
	 * 
	 * @return string|false
	 */
	private function get_attribute_pa_slug( $search_string ) {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( count( $attribute_taxonomies ) > 0 ) {
			foreach ( $attribute_taxonomies as $one_tax ) {
				/**
				 * $one_tax->attribute_id => 6
				 * $one_tax->attribute_name] => слаг (на инглише или русском)
				 * $one_tax->attribute_label] => Еще один атрибут (это как раз название)
				 * $one_tax->attribute_type] => select 
				 * $one_tax->attribute_orderby] => menu_order
				 * $one_tax->attribute_public] => 0			
				 */
				if ( $search_string == $one_tax->attribute_label ) {
					$id = (int) $one_tax->attribute_id;
					return wc_attribute_taxonomy_name_by_id( $id );
				}
			}
		}
		return false;
	}

	/**
	 * Get attribute value `pa_` slug
	 * 
	 * @param string $search_string
	 * @param string $attribute_pa_slug
	 * 
	 * @return string|false
	 */
	private function get_attribute_value_pa_slug( $search_string, $attribute_pa_slug ) {
		$attribute_taxonomies = get_terms( [ 
			'taxonomy' => $attribute_pa_slug,
			'hide_empty' => false
		] );
		if ( count( $attribute_taxonomies ) > 0 ) {
			foreach ( $attribute_taxonomies as $one_tax ) {
				/**
				 * $one_tax->term_id]     => 162
				 * $one_tax->name]        => Здоровье
				 * $one_tax->slug]        => zdorove
				 * $one_tax->term_group]  => 0
				 * $one_tax->term_taxonomy_id] => 170
				 * $one_tax->taxonomy]    => my_taxonomy
				 * $one_tax->description] =>
				 * $one_tax->parent]      => 0
				 * $one_tax->count]       => 2		
				 */
				if ( $search_string == $one_tax->name ) {
					// $id = (int)$one_tax->term_id;
					return $one_tax->slug;
				}
			}
		}
		return false;
	}

	private function get_all_offers_of_variation_product() {
		return $this->all_offers_of_variation_product;
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
	 * Get the product ID in the feed
	 * 
	 * @return string
	 */
	private function get_feed_product_id() {
		return $this->feed_product_id;
	}

	private function get_name() {
		return $this->name;
	}

	private function get_description() {
		return $this->description;
	}

	private function get_sku() {
		return $this->sku;
	}

	private function get_catid() {
		return $this->catid_arr;
	}

	private function get_stock_status() {
		return $this->stock_status;
	}

	private function get_pictures_arr() {
		return $this->pictures_arr;
	}

	private function get_attributes_simple_arr() {
		return $this->attributes_simple_arr;
	}

	private function get_attributes_variable_arr() {
		return $this->attributes_variable_arr;
	}

	private function get_variations_arr() {
		return $this->variations_arr;
	}

	public function get_imported_ids() {
		return $this->imported_ids;
	}
}