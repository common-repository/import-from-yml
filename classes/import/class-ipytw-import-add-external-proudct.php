<?php
/**
 * Import of external products
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
 *                                      WC_Product_External
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

class IPYTW_Import_Create_External_Product {
	private $offer_xml_object;
	private $feed_id;
	private $feed_product_id;

	private $url;
	private $name;
	private $description;
	private $sku;
	private $barcode;
	private $catid_arr;
	private $regular_price;
	private $sale_price;
	private $pictures_arr;
	private $attributes_simple_arr;

	private $imported_ids = false;

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
		$this->barcode = $args_arr['barcode'];
		$this->pictures_arr = $args_arr['pictures'];
		$this->attributes_simple_arr = $args_arr['attributes_simple'];
	}

	/**
	 * Summary of upd_product
	 * @param mixed $product
	 * 
	 * @return bool
	 */
	public function upd_product( $product ) {
		// if ($this->get_sku() !== '' && wc_get_product_id_by_sku($this->get_sku()) > 0) {
		// 	$product = wc_get_product_id_by_sku($this->get_sku());
		// } else {
		// 	$product = new WC_Product();
		// }
		$product = $this->set_external( $product );

		$ipytw_product_was_sync = ipytw_optionGET( 'ipytw_product_was_sync', $this->get_feed_id(), 'set_arr' );
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

				//				$data_arr = [];
//				$data_arr['data']['offer_xml_object'] = $this->get_offer_xml_object();
//				$product = apply_filters('ipytw_f_product_upd_case_price_and_stock', $product, $data_arr, $this->get_feed_id());

				$site_product_id = $product->save();
				$this->save_info_about_sync( $site_product_id );

				return true;
			case 'dont_update':
				return true;
			default:
				//				$product = apply_filters('ipytw_product_upd_case_default_filter', $product, array('ipytw_product_was_sync' => $ipytw_product_was_sync, 'data' => $data), $this->get_feed_id());
				return true;
		}
	}

	public function add_product() {
		$stop_flag = false;
		$stop_flag = apply_filters( 'ipytw_f_stop_flag', $stop_flag, array( 'offer_xml_object' => $this->get_offer_xml_object() ), $this->get_feed_id() );
		if ( $stop_flag === true ) {
			new IPYTW_Error_Log(
				'NOTICE: Пропускаем товар $feed_product_id = ' . $this->get_feed_product_id() . ' по флагу; Файл: offer.php; Строка: ' . __LINE__
			);
			return false;
		}

		// проверяем, существует ли товар с таким артикулом
		$product_sku = $this->get_sku();
		if ( ipytw_is_uniq_sku( $product_sku ) === true ) {
			new IPYTW_Error_Log(
				'FEED № ' . $this->get_feed_id() . '; Товар с артикулом $product_sku = ' . $product_sku . ' уже существует; Файл: class-ipytw-import-add-simle-product.php; Строка: ' . __LINE__
			);
			$ipytw_if_isset_sku = ipytw_optionGET( 'ipytw_if_isset_sku', $this->get_feed_id(), 'set_arr' );
			if ( $ipytw_if_isset_sku === 'disabled' || $ipytw_if_isset_sku === '' ) {
				new IPYTW_Error_Log(
					'FEED № ' . $this->get_feed_id() . '; WARNING: Товар пропущен по причине дублирующегося артикула; Файл: class-ipytw-import-add-simle-product.php; Строка: ' . __LINE__
				);
				return false;
			}
			if ( $ipytw_if_isset_sku === 'update' ) {
				$check_sync_product = (int) wc_get_product_id_by_sku( $product_sku );
			} else {
				$check_sync_product = ipytw_check_sync( $this->get_feed_product_id(), $this->get_feed_id(), 'product' );
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
			if ( $r == true ) {
				return $site_product_id;
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
		$regular_price = $this->get_regular_price();
		$sale_price = $this->get_sale_price();
		if ( $sale_price > 0 ) {
			$product->set_regular_price( $sale_price );
			$product->set_sale_price( $regular_price );
		} else {
			$product->set_regular_price( $regular_price );
			$product->set_sale_price( '' );
		}

		$data_arr = [];
		$data_arr['data']['offer_xml_object'] = $this->get_offer_xml_object();
		$product = apply_filters( 'ipytw_f_product_add_before_save', $product, $data_arr, $this->get_feed_id() );
		$site_product_id = $product->save();

		$obj = new IPYTW_Import_Create_Product_Attributes( $this->get_attributes_simple_arr(), $site_product_id );
		$attribs = $obj->create( false ); // false - тк атрибуты не вариативные
		$product = new WC_Product_External( $site_product_id );
		$product->set_props( [ 
			'attributes' => $attribs,
			// Set any other properties of the product here you want - price, name, etc.
		] );
		$product = $this->set_external( $product );

		$img_ids_arr = [];
		foreach ( $this->get_pictures_arr() as $feed_picture_url ) {
			$check_sync_img = ipytw_check_sync( $feed_picture_url, $this->get_feed_id(), 'attachment' ); /* ! */ // picture 
			if ( false === $check_sync_img ) {

				$dwlp_obj = new IPYTW_Download_Pictures( $feed_picture_url, [ 'name' => $this->get_name() ], $this->get_feed_id() );
				if ( $dwlp_obj->get_image_id() === -1 ) {
					new IPYTW_Error_Log(
						'FEED № ' . $this->get_feed_id() . '; Ошибка загрузки изображения ' . $feed_picture_url . '; Файл: class-ipytw-import-add-external-product.php; Строка: ' . __LINE__
					);
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

		$this->set_barcode( $site_product_id, $product );

		$this->save_info_about_sync( $site_product_id );
		return $site_product_id;
	}

	/**
	 * Set barcode
	 * 
	 * @param string $site_product_id
	 * @param WC_Product_External $product
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
			$product->save();
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

	private function get_url() {
		return $this->url;
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

	/**
	 * Get regular price
	 * 
	 * @return float
	 */
	private function get_regular_price() {
		return $this->regular_price;
	}

	private function get_sale_price() {
		return $this->sale_price;
	}

	private function get_barcode() {
		return $this->barcode;
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

	/**
	 * Set External product
	 * 
	 * @param WC_Product_External $product
	 * 
	 * @return WC_Product_External
	 */
	private function set_external( $product ) {
		$ipytw_partner_link = ipytw_optionGET( 'ipytw_partner_link', $this->get_feed_id(), 'set_arr' );
		$url = $this->get_url() . $ipytw_partner_link;

		if ( $product->get_type() == 'external' ) {
			if ( $product->get_product_url() != $url ) {
				$product->set_product_url( $url );
				$site_product_id = $product->save();
				$product = wc_get_product( $site_product_id );
			}
		} else {
			$site_product_id = $product->get_id();
			$product = new WC_Product_External( $site_product_id );
			$product->set_product_url( $url );
			$site_product_id = $product->save();
			$product = wc_get_product( $site_product_id );
		}

		return $product;
	}
}