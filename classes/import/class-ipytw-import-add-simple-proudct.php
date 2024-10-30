<?php
/**
 * Import of simple products
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
 * @depends                 classes:	IPYTW_Import_Create_Product_Attributes
 *                                      IPYTW_Download_Pictures
 *                                      IPYTW_Error_Log
 *                                      WC_Product
 *                                      WC_Product_Simple
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
/*
$args_arr = array(
	'url' => 'https://site.ru/product/designside/',
	'name' => 'Товар через класс',
	'description' => 'Описание',
	'sku' => '0001',
	'catid' => array(72, 74),
	'regular_price' => 100,
	'sale_price' => 50,
	'stock_status' => 'instock',
	'length' => 50,
	'width' => 50,
	'height' => 50,
	'weight' => 50,
	'barcode' => '3182550724142'
	'pictures' => array('', ''),
	'attributes' => array(
		"Color2" => array("blue", "red"), 
		"Size3" => array('112', '13', '14')
	)

	'attributes_simple' => array(
		array('name' => 'Производитель', 'values' => array('Адидас', 'Найк')),
		array('name' => 'Страна', 'values' => array('Россия'))
	),
);
*/
defined( 'ABSPATH' ) || exit;

class IPYTW_Import_Create_Simple_Product {
	private $offer_xml_object;
	private $feed_id;
	private $feed_product_id;

	private $url;
	private $name;
	private $description;
	private $sku;
	private $catid_arr;
	private $regular_price;
	private $sale_price;
	private $length; // Задаёт длину товара
	private $width; // Задаёт ширину товара
	private $height; // Задаёт высоту товара 
	private $weight; // Задаёт вес товара
	private $barcode;
	private $stock_status;
	private $pictures_arr;
	private $attributes_simple_arr;

	private $imported_ids = false;
	// private $not_imported_ids = [];

	/**
	 * Import of simple products
	 * 
	 * @param array $args_arr
	 * @param object $offer_xml_object
	 * @param string $feed_id
	 * @param string $feed_product_id
	 */
	public function __construct( $args_arr, $offer_xml_object, $feed_id, $feed_product_id ) {
		$this->offer_xml_object = $offer_xml_object;
		$this->feed_id = $feed_id;
		$this->feed_product_id = $feed_product_id;

		$this->url = $args_arr['url'];
		$this->name = $args_arr['name'];
		$this->description = $args_arr['description'];
		$this->sku = $args_arr['sku'];
		$this->catid_arr = $args_arr['catid'];
		$this->regular_price = $args_arr['regular_price'];
		$this->sale_price = $args_arr['sale_price'];
		$this->length = $args_arr['length'];
		$this->width = $args_arr['width'];
		$this->height = $args_arr['height'];
		$this->weight = $args_arr['weight'];
		$this->barcode = $args_arr['barcode'];
		$this->stock_status = $args_arr['stock_status'];
		$this->pictures_arr = $args_arr['pictures'];
		$this->attributes_simple_arr = $args_arr['attributes_simple'];
	}

	/**
	 * Update product
	 * 
	 * @param WC_Product $product
	 * 
	 * @return bool
	 */
	public function upd_product( $product ) {
		$ipytw_product_was_sync = common_option_get( 'ipytw_product_was_sync', false, $this->get_feed_id(), 'ipytw' );
		switch ( $ipytw_product_was_sync ) {
			case 'whole':
				return false;
			case 'whole_except_cat':
				$this->catid_arr = $product->get_category_ids(); // $args_arr['catid'];
				return false;
			case 'price_only':
				$regular_price = $this->get_regular_price();
				$sale_price = $this->get_sale_price();
				if ( $sale_price > 0 ) {
					$product->set_regular_price( $sale_price );
					$product->set_sale_price( $regular_price );
				} else {
					$product->set_regular_price( $regular_price );
					$product->set_sale_price( '' );
				}
				$site_product_id = $product->save();
				$this->save_info_about_sync( $site_product_id );

				return true;
			case 'stock_only':
				$product->set_stock_status( $this->get_stock_status() );

				$data_arr = [];
				$data_arr['data']['offer_xml_object'] = $this->get_offer_xml_object();
				$product = apply_filters( 'ipytw_f_product_upd_case_stock_only', $product, $data_arr, $this->get_feed_id() );

				$site_product_id = $product->save();
				$this->save_info_about_sync( $site_product_id );

				return true;
			case 'price_and_stock':
				$regular_price = $this->get_regular_price();
				$sale_price = $this->get_sale_price();
				if ( $sale_price > 0 ) {
					$product->set_regular_price( $sale_price );
					$product->set_sale_price( $regular_price );
				} else {
					$product->set_regular_price( $regular_price );
					$product->set_sale_price( '' );
				}

				$product->set_stock_status( $this->get_stock_status() );

				$data_arr = [];
				$data_arr['data']['offer_xml_object'] = $this->get_offer_xml_object();
				$product = apply_filters( 'ipytw_f_product_upd_case_price_and_stock', $product, $data_arr, $this->get_feed_id() );

				$site_product_id = $product->save();
				$this->save_info_about_sync( $site_product_id );

				return true;
			case 'dont_update':
				return true;
			default:
				// $product = apply_filters('ipytw_product_upd_case_default_filter', $product, array('ipytw_product_was_sync' => $ipytw_product_was_sync, 'data' => $data), $this->get_feed_id());
				return true;
		}
	}

	/**
	 * Summary of add_product
	 * @return bool|int
	 */
	public function add_product() {
		$stop_flag = false;
		$stop_flag = apply_filters(
			'ipytw_f_stop_flag',
			$stop_flag,
			[ 
				'offer_xml_object' => $this->get_offer_xml_object()
			],
			$this->get_feed_id()
		);
		if ( true === $stop_flag ) {
			new IPYTW_Error_Log( 'NOTICE: Пропускаем товар $feed_product_id = ' . $this->get_feed_product_id() . ' по флагу; Файл: offer.php; Строка: ' . __LINE__ );
			// $this->not_imported_ids[] = '';
			return false;
		}

		// проверяем, существует ли товар с таким артикулом
		$product_sku = $this->get_sku();
		if ( true === ipytw_is_uniq_sku( $product_sku ) ) {
			new IPYTW_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s %4$s; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Товар с артикулом $product_sku = ',
				$product_sku,
				'уже существует',
				'class-ipytw-import-add-simle-product.php',
				__LINE__
			) );
			$ipytw_if_isset_sku = ipytw_optionGET( 'ipytw_if_isset_sku', $this->get_feed_id(), 'set_arr' );
			if ( $ipytw_if_isset_sku === 'disabled' || $ipytw_if_isset_sku === '' ) {
				$sync_post_id = (int) wc_get_product_id_by_sku( $product_sku );
				if ( get_post_meta( $sync_post_id, '_ipytw_feed_id', true ) !== '' ) {
					$f_id = (int) get_post_meta( $sync_post_id, '_ipytw_feed_id', true );
				} else {
					$f_id = -1;
				}
				if ( $f_id == $this->get_feed_id() ) {
					if ( get_post_meta( $sync_post_id, '_ipytw_feed_product_id', true ) !== '' ) {
						$feed_product_id = get_post_meta( $sync_post_id, '_ipytw_feed_product_id', true );
					} else {
						$feed_product_id = -1;
					}
					if ( $feed_product_id == $this->get_feed_product_id() ) {
						new IPYTW_Error_Log( sprintf( 'FEED № %1$s; WARNING: %2$s; Файл: %3$s; Строка: %4$s',
							$this->get_feed_id(),
							'Артукул дублирован, но тк. прошлый синхорн был из этого же фида - игнорируем опцию и не препятсвуем обновлению товара',
							'class-ipytw-import-add-simle-product.php',
							__LINE__
						) );
						$check_sync_product = $sync_post_id;
					} else {
						new IPYTW_Error_Log( sprintf( 'FEED № %1$s; WARNING: %2$s; Файл: %3$s; Строка: %4$s',
							$this->get_feed_id(),
							'Товар пропущен по причине дублирующегося артикула',
							'class-ipytw-import-add-simle-product.php',
							__LINE__
						) );
						return false;
					}
				} else {
					new IPYTW_Error_Log( sprintf( 'FEED № %1$s; WARNING: %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Товар пропущен по причине дублирующегося артикула',
						'class-ipytw-import-add-simle-product.php',
						__LINE__
					) );
					return false;
				}
			}
			if ( $ipytw_if_isset_sku === 'update' ) {
				$check_sync_product = (int) wc_get_product_id_by_sku( $product_sku );
			} else {
				$check_sync_product = ipytw_check_sync( $this->get_feed_product_id(), $this->get_feed_id(), 'product' );
				if ( $ipytw_if_isset_sku === 'without_sku' ) {
					$sku_flag = true;
				}
			}
		} else {
			$check_sync_product = ipytw_check_sync( $this->get_feed_product_id(), $this->get_feed_id(), 'product' );
		}
		//	$check_sync_product = ipytw_check_sync($this->get_feed_product_id(), $this->get_feed_id(), 'product');
		if ( false === $check_sync_product ) {
			new IPYTW_Error_Log(
				'FEED № ' . $this->get_feed_id() . '; Простой товар ранее не был импортирован; Файл: class-ipytw-import-add-simle-product.php; Строка: ' . __LINE__
			);
			$product = new WC_Product();
		} else {
			$site_product_id = (int) $check_sync_product;
			new IPYTW_Error_Log(
				'FEED № ' . $this->get_feed_id() . '; Простой товар ранее был импортирован $site_product_id = ' . $site_product_id . '; Файл: class-ipytw-import-add-simle-product.php; Строка: ' . __LINE__
			);
			$product = wc_get_product( $site_product_id );
			$r = $this->upd_product( $product );
			if ( true == $r ) {
				return $site_product_id;
			} else {
				$sku_flag = true; // флаг исправляет глюк. см if (isset($sku_flag) && true === ipytw_is_uniq_sku($product_sku))
			}
		}

		$product->set_name( $this->get_name() );

		$ipytw_description_into = ipytw_optionGET( 'ipytw_description_into', $this->get_feed_id(), 'set_arr' );
		if ( $ipytw_description_into === 'excerpt' ) {
			$product->set_short_description( $this->get_description() );
		} else {
			$product->set_description( $this->get_description() );
		}

		if ( isset( $sku_flag ) && true === ipytw_is_uniq_sku( $product_sku ) ) {
			new IPYTW_Error_Log( sprintf( 'FEED № %1$s; %2$s $site_product_id = %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Не устанавливаем артикул при обновлении товара (вероятно вариативного) как простого с',
				$site_product_id,
				'class-ipytw-import-add-simle-product.php',
				__LINE__
			) );
		} else {
			$product->set_sku( $this->get_sku() );
		}
		$product->set_category_ids( $this->get_catid() );
		$regular_price = $this->get_regular_price();
		$sale_price = $this->get_sale_price();
		if ( $sale_price > 0 ) {
			$product->set_regular_price( $sale_price );
			$product->set_sale_price( $regular_price );
		} else {
			$product->set_regular_price( $regular_price );
			$product->set_sale_price( '' );
		}
		$product->set_length( $this->get_length() ); // Задаёт длину товара
		$product->set_width( $this->get_width() ); // Задаёт ширину товара
		$product->set_height( $this->get_height() ); // Задаёт высоту товара 
		$product->set_weight( $this->get_weight() ); // Задаёт вес товара
		$product->set_stock_status( $this->get_stock_status() );

		$data_arr = [];
		$data_arr['data']['offer_xml_object'] = $this->get_offer_xml_object();
		$product = apply_filters( 'ipytw_f_product_add_before_save', $product, $data_arr, $this->get_feed_id() );
		$site_product_id = $product->save();
		new IPYTW_Error_Log( sprintf(
			'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %4$s',
			$this->get_feed_id(),
			'$site_product_id = ',
			$site_product_id,
			'class-ipytw-import-add-simle-product.php',
			__LINE__
		) );

		$obj = new IPYTW_Import_Create_Product_Attributes( $this->get_attributes_simple_arr(), $site_product_id );
		$attribs = $obj->create( false ); // false - тк атрибуты не вариативные
		$product = new WC_Product_Simple( $site_product_id );
		$product->set_props( [ 
			'attributes' => $attribs
			// Set any other properties of the product here you want - price, name, etc.
		] );

		$img_ids_arr = [];
		foreach ( $this->get_pictures_arr() as $feed_picture_url ) {
			$check_sync_img = ipytw_check_sync( $feed_picture_url, $this->get_feed_id(), 'attachment' ); /* ! */// picture 
			if ( false === $check_sync_img ) {

				$dwlp_obj = new IPYTW_Download_Pictures( $feed_picture_url, [ 'name' => $this->get_name() ], $this->get_feed_id() );
				if ( $dwlp_obj->get_image_id() === -1 ) {
					new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Ошибка загрузки изображения ' . $feed_picture_url . '; Файл: class-ipytw-import-add-simple-product.php; Строка: ' . __LINE__ );
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

		$ipytw_post_status = ipytw_optionGET( 'ipytw_post_status', $this->get_feed_id(), 'set_arr' );
		$product->set_status( $ipytw_post_status );
		$site_product_id = $product->save();
		new IPYTW_Error_Log( sprintf(
			'FEED № %1$s; %2$s %3$s ipytw_post_status = %4$s; Файл: %5$s; Строка: %6$s',
			$this->get_feed_id(),
			'$site_product_id = ',
			$site_product_id,
			$ipytw_post_status,
			'class-ipytw-import-add-simle-product.php',
			__LINE__,
		) );

		$this->set_barcode( $site_product_id, $product );

		$this->save_info_about_sync( $site_product_id );
		return $site_product_id;
	}

	/**
	 * Set barcode
	 * 
	 * @param string $site_product_id
	 * @param WC_Product $product
	 * 
	 * @return void
	 */
	private function set_barcode( $site_product_id, $product ) {
		$ipytw_barcode = common_option_get( 'ipytw_barcode', false, $this->get_feed_id(), 'ipytw' );
		if ( $ipytw_barcode === 'post_meta' ) {
			$post_meta_value = common_option_get( 'ipytw_barcode_post_meta_value', false, $this->get_feed_id(), 'ipytw' );
			if ( ! empty( $this->get_barcode() ) && ! empty( $post_meta_value ) ) {
				update_post_meta( $site_product_id, $post_meta_value, $this->get_barcode() );
			}
		} else if ( $ipytw_barcode === 'global_unique_id' ) {
			$product->set_global_unique_id( $this->get_barcode() );
			$site_product_id = $product->save();
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
		$imported_ids['product_price'] = $this->get_regular_price();
		$this->imported_ids = $imported_ids;
		return;
	}

	/**
	 * Get offer_xml_object
	 * 
	 * @return object
	 */
	private function get_offer_xml_object() {
		return $this->offer_xml_object;
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

	/**
	 * Summary of get_url
	 * 
	 * @return string
	 */
	private function get_url() {
		return $this->url;
	}

	/**
	 * Summary of get_name
	 * 
	 * @return string
	 */
	private function get_name() {
		return $this->name;
	}

	/**
	 * Summary of get_description
	 * 
	 * @return string
	 */
	private function get_description() {
		return $this->description;
	}

	private function get_sku() {
		return $this->sku;
	}

	private function get_catid() {
		return $this->catid_arr;
	}

	/**
	 * Get regular price
	 * 
	 * @return float
	 */
	private function get_regular_price() {
		return (float) $this->regular_price;
	}

	/**
	 * Get sale price
	 * 
	 * @return float
	 */
	private function get_sale_price() {
		return (float) $this->sale_price;
	}

	private function get_length() {
		return $this->length;
	}

	private function get_width() {
		return $this->width;
	}

	private function get_height() {
		return $this->height;
	}

	private function get_weight() {
		return $this->weight;
	}

	private function get_barcode() {
		return $this->barcode;
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

	public function get_imported_ids() {
		return $this->imported_ids;
	}
}