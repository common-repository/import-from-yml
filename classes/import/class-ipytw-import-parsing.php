<?php
/**
 * Parsing products data
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 3.1.3 (28-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param        	
 *
 * @depends                 classes:    IPYTW_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  ipytw_optionGET
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Import_Parsing {
	/**
	 * Feed ID
	 * @var string
	 */
	private $feed_id;
	/**
	 * An array of imported categories in the feed
	 * @var array
	 */
	private $feed_imported_cat_arr;
	/**
	 * Summary of offer_xml_object
	 * @var SimpleXMLElement
	 */
	private $offer_xml_object;
	/**
	 * Summary of all_offers_of_variation_products
	 * @var 
	 */
	private $all_offers_of_variation_products;

	/**
	 * Parsing products data
	 * 
	 * @param SimpleXMLElement|array $offer_xml_object
	 * @param array $feed_imported_cat_arr
	 * @param string $feed_id
	 */
	public function __construct( $offer_xml_object, $feed_imported_cat_arr, $feed_id ) {
		$this->offer_xml_object = $offer_xml_object;
		$this->feed_imported_cat_arr = $feed_imported_cat_arr;
		$this->feed_id = $feed_id;
	}

	/**
	 * Get array of parsing data simple product 
	 * 
	 * @return array
	 */
	public function get_simple_arr() {
		$result_arr = [];

		$result_arr['id'] = $this->find_id();
		$result_arr['sku'] = $this->find_sku();
		$result_arr['url'] = $this->find_url();
		$result_arr['name'] = $this->find_name();
		$result_arr['description'] = $this->find_description();
		$result_arr['regular_price'] = $this->find_regular_price();
		$result_arr['sale_price'] = $this->find_sale_price();
		$result_arr['stock_status'] = $this->find_stock_status();
		$result_arr['pictures'] = $this->find_pictures_url();
		$result_arr['catid'] = $this->find_catid();
		$result_arr['attributes_simple'] = $this->find_attributes_simple();
		$result_arr = $this->find_dimensions( $result_arr );
		$result_arr['weight'] = $this->find_weight();
		$result_arr['barcode'] = $this->find_barcode();
		return $result_arr;
	}

	/**
	 * Get array of parsing data variable product
	 * 
	 * @return array
	 */
	public function get_variable_arr() {
		$result_arr = [];
		/**	
		 * [
		 *	'attributes' => [
		 *		['name' => 'Color', 'value' => 'blue'],
		 *		['name' => 'Size', 'value' => '46']
		 *	],
		 *	'url' => 'https://site.ru/product/designside/',
		 *	'feed_var_id' => 1,
		 *	'description' => 'Описание вариации1',
		 *	'sku' => '0001',
		 *	'regular_price' => 60,
		 *	'sale_price' => 50,
		 *	'picture' => [ ],
		 *	'stock_status' => 'instock'
		 * ],
		 */
		$all_offers_of_variation_products = $this->get_offer_xml_object(); // тут у нас хитрая рокировка
		$variation_attribute_arr = $this->get_variation_attributes_arr( $all_offers_of_variation_products );

		$attributes_arr = [];
		$attributes_simple_arr = [];
		$attributes_variable_arr = [];
		for ( $x = 0; $x < count( $all_offers_of_variation_products ); $x++ ) {
			$offer_xml_object = $all_offers_of_variation_products[ $x ]; // тут у нас хитрая рокировка
			$this->offer_xml_object = $offer_xml_object; // тут у нас хитрая рокировка

			if ( $x == 0 ) { // общее описание товара для всех вариаций
				$result_arr['description'] = $this->find_description();
			}

			$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
			$variation_id = (string) $xml_offer_attr_object->id; // id вариации в фиде
			$attributes_arr[ $x ]['url'] = $this->find_url();
			$attributes_arr[ $x ]['feed_var_id'] = $variation_id;
			$attributes_arr[ $x ]['description'] = $this->find_description();
			$attributes_arr[ $x ]['sku'] = $this->find_sku();
			$attributes_arr[ $x ]['regular_price'] = $this->find_regular_price();
			$attributes_arr[ $x ]['sale_price'] = $this->find_sale_price();
			$attributes_arr[ $x ]['picture'] = $this->find_pictures_url();
			$attributes_arr[ $x ]['stock_status'] = $this->find_stock_status();
			$attributes_arr[ $x ] = $this->find_dimensions( $attributes_arr[ $x ] );
			$attributes_arr[ $x ]['weight'] = $this->find_weight();
			$attributes_arr[ $x ]['barcode'] = $this->find_barcode();

			$attributes_arr = apply_filters( 'ipytw_f_variation_get_variable_arr_attributes_arr',
				$attributes_arr,
				[ 
					'x' => $x,
					'offer_xml_object' => $this->get_offer_xml_object()
				],
				$this->get_feed_id()
			);
			$offer_xml_params_object = $this->find_all_param_element( $this->get_offer_xml_object() );

			if ( null !== $offer_xml_params_object ) {
				for ( $i = 0; $i < count( $offer_xml_params_object ); $i++ ) {
					$params_attributes_object = $offer_xml_params_object[ $i ]->attributes();
					$attribute_name = (string) $params_attributes_object->name;
					$attribute_value = (string) $offer_xml_params_object[ $i ];

					// если этот атрибут у нас используется как атрибут вариации
					if ( isset( $variation_attribute_arr[ $attribute_name ] ) ) {
						$attributes_arr[ $x ]['attributes'][] = [ 
							'name' => $attribute_name,
							'value' => $attribute_value
						];

						$f = false;
						$index = -1;
						for ( $ii = 0; $ii < count( $attributes_variable_arr ); $ii++ ) {
							if ( $attributes_variable_arr[ $ii ]['name'] == $attribute_name ) {
								$f = true;
								$index = $ii;
								break;
							}
						}
						if ( $f == false ) {
							array_push( $attributes_variable_arr,
								[ 
									'name' => $attribute_name,
									'values' => [ $attribute_value ]
								]
							);
						}
						if ( $f == true && $index > -1 ) {
							if ( ! in_array( $attribute_value, $attributes_variable_arr[ $index ]['values'] ) ) {
								$attributes_variable_arr[ $index ]['values'][] = $attribute_value;
							}
						}
					} else {
						$f = false;
						for ( $ii = 0; $ii < count( $attributes_simple_arr ); $ii++ ) {
							if ( $attributes_simple_arr[ $ii ]['name'] == $attribute_name ) {
								$f = true;
								break;
							}
						}
						if ( $f == false ) {
							array_push( $attributes_simple_arr,
								[ 
									'name' => $attribute_name,
									'values' => [ $attribute_value ]
								]
							);
						}
					}
				}
			}
		}

		$result_arr['name'] = $this->find_name();
		$result_arr['id'] = $this->find_group_id();
		$result_arr['preorder'] = $this->find_preorder();
		$result_arr['sku'] = $this->find_sku();
		// $result_arr['description'] = $this->find_description();
		$result_arr['stock_status'] = $this->find_stock_status();
		$result_arr['pictures'] = $this->find_pictures_url();
		$result_arr['catid'] = $this->find_catid();
		$result_arr['variations'] = $attributes_arr;
		$result_arr['attributes_simple'] = $attributes_simple_arr;
		$result_arr['attributes_variable'] = $attributes_variable_arr;

		return $result_arr;
	}

	public function check_skip() {

	}

	/**
	 * Summary of find_id
	 * 
	 * @return string
	 */
	private function find_id() {
		$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
		return (string) $xml_offer_attr_object->id;
	}

	/**
	 * Summary of find_group_id
	 * 
	 * @return string
	 */
	private function find_group_id() {
		$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
		return (string) $xml_offer_attr_object->group_id;
	}

	/**
	 * Summary of find_preorder
	 * 
	 * @return string
	 */
	private function find_preorder() {
		$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
		return (string) $xml_offer_attr_object->preorder;
	}

	/**
	 * Summary of find_sku
	 * 
	 * @return string
	 */
	private function find_sku() {
		$ipytw_source_sku = ipytw_optionGET( 'ipytw_source_sku', $this->get_feed_id(), 'set_arr' );
		$product_sku = '';
		switch ( $ipytw_source_sku ) {
			case 'vendor_code':
				$product_sku = $this->get_offer_xml_object()->vendorCode;
				break;
			case 'shop_sku':
				$el_name = 'shop-sku';
				$product_sku = $this->get_offer_xml_object()->$el_name;
				break;
			case 'sku':
				$product_sku = $this->get_offer_xml_object()->sku;
				break;
			case 'article':
				$product_sku = $this->get_offer_xml_object()->article;
				break;
			case 'product_id':
				$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
				$product_sku = $xml_offer_attr_object->id;
				break;
			case 'param_artikul':
				$offer_xml_params_object = $this->find_all_param_element( $this->get_offer_xml_object() );
				if ( null !== $offer_xml_params_object ) {
					for ( $i = 0; $i < count( $offer_xml_params_object ); $i++ ) {
						$params_attributes_object = $offer_xml_params_object[ $i ]->attributes();
						$attribute_name = (string) $params_attributes_object->name;
						if ( 'Артикул' == $attribute_name || 'артикул' == $attribute_name ) {
							$product_sku = (string) $offer_xml_params_object[ $i ];
							unset( $offer_xml_params_object );
							break;
						}
					}
				}
				break;
			default:
		}
		$product_sku = apply_filters(
			'ipytw_parsing_find_sku_filter',
			$product_sku,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);
		$ipytw_add_pref_to_sku = (string) ipytw_optionGET( 'ipytw_add_pref_to_sku', $this->get_feed_id(), 'set_arr' );
		if ( ! empty( $product_sku ) && ! empty( $ipytw_add_pref_to_sku ) ) {
			$product_sku = trim( $ipytw_add_pref_to_sku ) . $product_sku;
		}
		return (string) $product_sku;
	}

	/**
	 * Summary of find_url
	 * 
	 * @return string
	 */
	private function find_url() {
		if ( property_exists( $this->get_offer_xml_object(), 'url' ) ) {
			$product_url = trim( (string) $this->get_offer_xml_object()->url );
		} else {
			$product_url = '';
		}
		$product_url = apply_filters(
			'ipytw_parsing_find_url_filter',
			$product_url,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);
		return $product_url;
	}

	/**
	 * Summary of find_name
	 * 
	 * @return string
	 */
	private function find_name() {
		if ( property_exists( $this->get_offer_xml_object(), 'name' ) ) {
			$product_name = trim( (string) $this->get_offer_xml_object()->name );
		} else {
			if ( property_exists( $this->get_offer_xml_object(), 'typePrefix' ) &&
				property_exists( $this->get_offer_xml_object(), 'vendor' ) &&
				property_exists( $this->get_offer_xml_object(), 'model' ) ) {
				$typePrefix = trim( (string) $this->get_offer_xml_object()->typePrefix );
				$vendor = trim( (string) $this->get_offer_xml_object()->vendor );
				$model = trim( (string) $this->get_offer_xml_object()->model );
				$product_name = sprintf( '%s %s %s', $typePrefix, $vendor, $model );
				$product_name = apply_filters( 'ipytw_custom_yml_format_product_name_f',
					$product_name,
					[ 
						'offer_xml_object' => $this->get_offer_xml_object(),
						'typePrefix' => $typePrefix,
						'vendor' => $vendor,
						'model' => $model
					],
					$this->get_feed_id()
				);
				ipytw_optionUPD( 'ipytw_err_list_code_arr', [ '27' ], $this->get_feed_id(), 'yes', 'set_arr' );
			} else if ( property_exists( $this->get_offer_xml_object(), 'model' ) ) {
				$product_name = trim( (string) $this->get_offer_xml_object()->model );
			} else {
				$product_name = '';
				// ipytw_optionUPD('ipytw_err_list_code_arr', [ '26' ], $this->get_feed_id(), 'yes', 'set_arr');
			}
		}
		$product_name = apply_filters(
			'ipytw_parsing_find_name_filter',
			$product_name,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);
		return $product_name;
	}

	/**
	 * Summary of find_description
	 * 
	 * @return string
	 */
	private function find_description() {
		if ( property_exists( $this->get_offer_xml_object(), 'description' ) ) {
			$product_description = (string) $this->get_offer_xml_object()->description;
		} else {
			$product_description = '';
		}
		$product_description = apply_filters(
			'ipytw_parsing_find_description_filter',
			$product_description,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);
		return $product_description;
	}

	/**
	 * Summary of find_regular_price
	 * 
	 * @return string
	 */
	private function find_regular_price() {
		$price_element = 'price';
		$price_element = apply_filters( 'ipytw_f_price_element', $price_element, $this->get_feed_id() );
		$product_price = (float) $this->get_offer_xml_object()->$price_element;
		$product_price = apply_filters( 'ipytw_f_parsing_find_regular_price',
			$product_price,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);

		// TODO: этот фильтр в дальнейшем можно будет удалить
		$product_price = apply_filters( 'ipytw_f_product_price', $product_price, $this->get_feed_id() );
		return $product_price;
	}

	/**
	 * Summary of find_sale_price
	 * 
	 * @return string
	 */
	private function find_sale_price() {
		$ipytw_find_sale_price = ipytw_optionGET( 'ipytw_find_sale_price', $this->get_feed_id(), 'set_arr' );
		if ( $ipytw_find_sale_price === 'disabled' ) {
			return '';
		}

		$oldprice_element = 'oldprice';
		$oldprice_element = apply_filters( 'ipytww_parsing_sale_price_element_filter', $oldprice_element, $this->get_feed_id() );
		$product_oldprice = (float) $this->get_offer_xml_object()->$oldprice_element;
		$product_oldprice = apply_filters(
			'ipytw_parsing_find_sale_price_filter',
			$product_oldprice,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);

		// TODO: этот фильтр в дальнейшем можно будет удалить
		$product_oldprice = apply_filters( 'ipytw_f_product_oldprice', $product_oldprice, $this->get_feed_id() );
		if ( $product_oldprice > (float) 0 ) {
			return $product_oldprice;
		} else {
			return '';
		}
	}

	/**
	 * Summary of find_stock_status. 
	 * 
	 * @return string - 'instock' or 'outofstock'
	 */
	private function find_stock_status() {
		$xml_offer_attr_object = $this->get_offer_xml_object()->attributes();
		if ( property_exists( $xml_offer_attr_object, 'available' ) ) {
			$feed_product_available = (string) $xml_offer_attr_object->available;
			if ( $feed_product_available === 'true' ) {
				$stock_status = 'instock';
			} else {
				// если в значении только цифры и при этом не '0'
				if ( true === ctype_digit( $feed_product_available ) && $feed_product_available !== '0' ) {
					$stock_status = 'instock';
				} else {
					$stock_status = 'outofstock';
				}
			}
		} else if ( property_exists( $this->get_offer_xml_object(), 'available' ) ) {
			$feed_product_available = (string) $this->get_offer_xml_object()->available;
			if ( $feed_product_available === 'true' ) {
				$stock_status = 'instock';
			} else {
				// если в значении только цифры и при этом не '0'
				if ( true === ctype_digit( $feed_product_available ) && $feed_product_available !== '0' ) {
					$stock_status = 'instock';
				} else {
					$stock_status = 'outofstock';
				}
			}
		} else {
			$stock_status = 'outofstock';
		}
		return $stock_status;
	}

	/**
	 * Summary of find_pictures_url
	 * 
	 * @return array
	 */
	private function find_pictures_url() {
		$imgs_url_arr = [];
		if ( property_exists( $this->get_offer_xml_object(), 'pictures' ) ) {
			$pictures_obj = $this->get_offer_xml_object()->pictures;
		} else {
			$pictures_obj = $this->get_offer_xml_object();
		}

		foreach ( $pictures_obj->picture as $pic ) {
			$feed_picture_url = (string) $pic;
			$feed_picture_url = get_from_url( $feed_picture_url );
			if ( null == get_file_extension( $feed_picture_url ) ) {
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: Картинка url = %2$s не имеет расширения. Возможно у нас формат webp, пробуем загружать...; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					$feed_picture_url,
					'class-ipytw-import-parsing.php',
					__LINE__
				) );
				$imgs_url_arr[] = trim( $feed_picture_url );
			} else {
				// if (preg_match('/^(?:.*\.(?=(jpg|jpeg|png|gif|webp)$))?[^.]*$/i', $feed_picture_url, $matches)) {
				if ( preg_match( "/^(?:.*\b(\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG|\.gif|\.GIF|\.webp|\.WEBP))\b/", $feed_picture_url, $matches ) ) {
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; NOTICE: url картинки = %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						$feed_picture_url,
						'class-ipytw-import-parsing.php',
						__LINE__
					) );
					$imgs_url_arr[] = trim( $feed_picture_url );
				} else {
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; WARNING: Картинка url = %2$s пропущена. Допустимые расширения jpg|jpeg|png|gif|webp; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						$feed_picture_url,
						'class-ipytw-import-parsing.php',
						__LINE__
					) );
				}
			}
		}
		$imgs_url_arr = apply_filters(
			'ipytw_parsing_imgs_url_filter',
			$imgs_url_arr,
			$this->get_offer_xml_object(),
			$this->get_feed_id()
		);
		return $imgs_url_arr;
	}

	/**
	 * Summary of find_catid
	 * 
	 * @return array
	 */
	private function find_catid() {
		$site_product_cats = [];
		$category_id_tag_val = $this->get_offer_xml_object()->categoryId;
		if ( $category_id_tag_val !== '' ) {
			// на случай, если в фиде несколько id категорий через запятую
			$product_category_ids = explode( ',', $category_id_tag_val );
			foreach ( $product_category_ids as $product_category_id ) {
				if ( $product_category_id !== '' ) {
					$feed_product_cat = (int) $product_category_id;
					new IPYTW_Error_Log( sprintf(
						'FEED № %1$s; Пробуем отыскать категорию с $feed_product_cat = %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						$feed_product_cat,
						'class-ipytw-import-parsing.php',
						__LINE__
					) );
					if ( isset( $this->get_feed_imported_cat_arr()[ $feed_product_cat ] ) ) {
						$site_product_cat = (int) $this->get_feed_imported_cat_arr()[ $feed_product_cat ];
						new IPYTW_Error_Log( sprintf(
							'FEED № %1$s; Категории с $feed_product_cat = %2$s соответствует категория $site_product_cat = %3$s на нашем сайте; Файл: %4$s; Строка: %5$s',
							$this->get_feed_id(),
							$feed_product_cat,
							$site_product_cat,
							'class-ipytw-import-parsing.php',
							__LINE__
						) );
						array_push( $site_product_cats, $site_product_cat );
					}
				}
			}
		}
		$result_category_ids = $site_product_cats; // $result_category_ids = [ $site_product_cat ];
		return $result_category_ids;
	}

	/**
	 * Summary of find_attributes_simple
	 * 
	 * @return array
	 */
	private function find_attributes_simple() {
		$attributes_arr = [];
		$offer_xml_params_object = $this->find_all_param_element( $this->get_offer_xml_object() );

		if ( null !== $offer_xml_params_object ) {
			$separator = ipytw_optionGET( 'ipytw_params_separator', $this->get_feed_id(), 'set_arr' );
			for ( $i = 0; $i < count( $offer_xml_params_object ); $i++ ) {
				$params_attributes_object = $offer_xml_params_object[ $i ]->attributes();
				$attribute_name = (string) $params_attributes_object->name;
				$attribute_value = (string) $offer_xml_params_object[ $i ];
				$attributes_arr[ $i ]['name'] = $attribute_name;
				if ( empty( $separator ) ) {
					$attributes_arr[ $i ]['values'] = [ $attribute_value ];
				} else {
					$attribute_value = str_replace( $separator . ' ', $separator, $attribute_value );
					$attribute_value = explode( $separator, $attribute_value );
					$attributes_arr[ $i ]['values'] = $attribute_value;
				}
			}
		}

		if ( property_exists( $this->get_offer_xml_object(), 'vendor' ) ) {
			$vendor_value = (string) $this->get_offer_xml_object()->vendor;
			array_push( $attributes_arr, [ 'name' => 'Производитель', 'values' => [ $vendor_value ] ] );
			// $attribute_name = "Производитель";
			// $attributes_arr[$attribute_name] = [ $vendor_value ];
		}

		if ( property_exists( $this->get_offer_xml_object(), 'brand' ) ) {
			$brand_value = (string) $this->get_offer_xml_object()->brand;
			array_push( $attributes_arr, [ 'name' => 'Бренд', 'values' => [ $brand_value ] ] );
		}

		if ( property_exists( $this->get_offer_xml_object(), 'barcode' ) ) {
			$ipytw_barcode = common_option_get( 'ipytw_barcode', false, $this->get_feed_id(), 'ipytw' );
			if ( $ipytw_barcode === 'global_attr' ) {
				// если в настройках стоит импорт штрихкода в атрибуты
				$barcode_value = (string) $this->get_offer_xml_object()->barcode;
				array_push( $attributes_arr, [ 'name' => 'Штрихкод', 'values' => [ $barcode_value ] ] );
			}
		}
		return $attributes_arr;
	}

	/**
	 * Summary of find_dimensions
	 * 
	 * @param array $result_arr
	 * 
	 * @return array
	 */
	private function find_dimensions( $result_arr ) {
		if ( property_exists( $this->get_offer_xml_object(), 'dimensions' ) ) {
			$dimensions_str = (string) $this->get_offer_xml_object()->dimensions;
			$pieces = explode( '/', $dimensions_str );
			$result_arr['length'] = (float) $pieces[0];
			$result_arr['width'] = (float) $pieces[1];
			$result_arr['height'] = (float) $pieces[2];
			return $result_arr;
		}
		if ( property_exists( $this->get_offer_xml_object(), 'length' ) ) {
			$result_arr['length'] = (float) $this->get_offer_xml_object()->length;
		} else {
			$result_arr['length'] = '';
		}
		if ( property_exists( $this->get_offer_xml_object(), 'width' ) ) {
			$result_arr['width'] = (float) $this->get_offer_xml_object()->width;
		} else {
			$result_arr['width'] = '';
		}
		if ( property_exists( $this->get_offer_xml_object(), 'height' ) ) {
			$result_arr['height'] = (float) $this->get_offer_xml_object()->height;
		} else {
			$result_arr['height'] = '';
		}
		return $result_arr;
	}

	/**
	 * Summary of find_weight
	 * 
	 * @return float|string
	 */
	private function find_weight() {
		if ( property_exists( $this->get_offer_xml_object(), 'weight' ) ) {
			return (float) $this->get_offer_xml_object()->weight;
		} else {
			return '';
		}
	}

	/**
	 * Summary of find_barcode
	 * 
	 * @return string
	 */
	private function find_barcode() {
		if ( property_exists( $this->get_offer_xml_object(), 'barcode' ) ) {
			return (string) $this->get_offer_xml_object()->barcode;
		} else {
			return '';
		}
	}

	/**
	 * Summary of get_feed_id
	 * 
	 * @return string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Summary of get_feed_imported_cat_arr
	 * 
	 * @return array
	 */
	public function get_feed_imported_cat_arr() {
		return $this->feed_imported_cat_arr;
	}

	/**
	 * Get offer_xml_object
	 * 
	 * @return SimpleXMLElement
	 */
	public function get_offer_xml_object() {
		return $this->offer_xml_object;
	}

	/**
	 * Возвращаем массив в котором ключ - имя вариативного атрибута, а массив значений этого ключа - вариации 
	 *
	 * @param object $all_offers_of_variation_products
	 * 
	 * @return array 
	 *	[color]
	 *		[0] - blue
	 *		[1] - green
	 *		[1] - white
	 *	[size]
	 *		[0] - XL
	 *		[1] - S
	 */
	private function get_variation_attributes_arr( $all_offers_of_variation_products ) {
		$variation_attribute_arr = [];

		for ( $x = 0; $x < count( $all_offers_of_variation_products ); $x++ ) {
			$offer_xml_object = $all_offers_of_variation_products[ $x ];
			$offer_xml_params_object = $this->find_all_param_element( $offer_xml_object );

			if ( null !== $offer_xml_params_object ) {
				for ( $i = 0; $i < count( $offer_xml_params_object ); $i++ ) {
					$params_attributes_object = $offer_xml_params_object[ $i ]->attributes();
					$attribute_name = (string) $params_attributes_object->name;
					$attribute_value = (string) $offer_xml_params_object[ $i ];

					// если нет массива с таким атрибутом - создадим
					if ( ! isset( $variation_attribute_arr[ $attribute_name ] ) ) {
						$variation_attribute_arr[ $attribute_name ] = [];
					}
					array_push( $variation_attribute_arr[ $attribute_name ], $attribute_value );
				}
			}
		}
		// вычислим вариативные атрибуты

		foreach ( $variation_attribute_arr as $key => $value ) {
			$variation_attribute_arr[ $key ] = array_unique( $variation_attribute_arr[ $key ] );
			if ( count( $variation_attribute_arr[ $key ] ) < 2 ) { // минимум два различия в значении		
				unset( $variation_attribute_arr[ $key ] );
			}
		}
		// echo get_array_as_string($variation_attribute_arr, '<br/>');
		return $variation_attribute_arr;
	}

	/** 
	 * Возвращаем объект содержащий все элементы param или null
	 *
	 * @param object $object
	 * @param object|null $result_params_obj
	 * 
	 * @return object
	 */
	private function find_all_param_element( $object, $result_params_obj = null ) {
		if ( property_exists( $object, 'params' ) ) {
			new IPYTW_Error_Log( sprintf(
				'FEED № %1$s; NOTICE: Параметры обёрнуты в params; Файл: %2$s; Строка: %3$s',
				$this->get_feed_id(),
				'class-ipytw-import-parsing.php',
				__LINE__
			) );
			if ( property_exists( $object->params, 'param' ) ) {
				new IPYTW_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: Внутри params нашли param; Файл: %2$s; Строка: %3$s',
					$this->get_feed_id(),
					'class-ipytw-import-parsing.php',
					__LINE__
				) );
				$result_params_obj = $object->params->param;
			}
		} else if ( property_exists( $object, 'param' ) ) {
			$result_params_obj = $object->param;
		}

		return $result_params_obj;
	}
}