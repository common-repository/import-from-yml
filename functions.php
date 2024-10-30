<?php defined( 'ABSPATH' ) || exit;
/**
 * Возвращает то, что может быть результатом add_blog_option, add_option
 * 
 * @since 1.0.0
 *
 * @param string $option_name - Rrequired
 * @param string $value - Rrequired
 * @param string $n - Optional
 * @param string $autoload - Optional
 * @param string $type - Optional (@since 1.3.0)
 * @param string $source_settings_name - Optional (@since 1.3.0)
 *
 * @return bool
 */
function ipytw_optionADD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = 'option', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $option_name === 'ipytw_status_sborki' || $option_name === 'ipytw_last_element' ) {
				if ( $n === '0' || $n === 0 ) {
					$n = '';
				}
			}
			$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
			$ipytw_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'ipytw_settings_arr', $ipytw_settings_arr );
			} else {
				return update_option( 'ipytw_settings_arr', $ipytw_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$ipytw_settings_arr = ipytw_optionGET( $source_settings_name );
			$ipytw_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $ipytw_settings_arr );
			} else {
				return update_option( $source_settings_name, $ipytw_settings_arr, $autoload );
			}
		default:
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return add_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return add_option( $option_name, $value, '', $autoload );
			}
	}
}
/**
 * Возвращает то, что может быть результатом update_blog_option, update_option
 *
 * @since 1.0.0
 *
 * @param string $option_name - Rrequired
 * @param mixed $value - Rrequired
 * @param string $n - Optional
 * @param string $autoload - Optional
 * @param string $type - Optional (@since 1.3.0)
 * @param string $source_settings_name - Optional (@since 1.3.0)
 *
 * @return bool
 */
function ipytw_optionUPD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $option_name === 'ipytw_status_sborki' || $option_name === 'ipytw_last_element' ) {
				if ( $n === '0' || $n === 0 ) {
					$n = '';
				}
			}
			if ( $n == '' ) {
				return false;
			}
			$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
			$ipytw_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'ipytw_settings_arr', $ipytw_settings_arr );
			} else {
				return update_option( 'ipytw_settings_arr', $ipytw_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$ipytw_settings_arr = ipytw_optionGET( $source_settings_name );
			$ipytw_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $ipytw_settings_arr );
			} else {
				return update_option( $source_settings_name, $ipytw_settings_arr, $autoload );
			}
		default:
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return update_option( $option_name, $value, $autoload );
			}
	}
}
/**
 * Возвращает то, что может быть результатом get_blog_option, get_option
 * 
 * @since 1.0.0
 *
 * @param string $optName - Rrequired
 * @param string $n - Optional
 * @param string $type - Optional (@since 1.3.0)
 * @param string $source_settings_name - Optional (@since 1.3.0)
 *
 * @return string|array|false (Значение опции (string/array) или false)
 */
function ipytw_optionGET( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $option_name === 'ipytw_status_sborki' || $option_name === 'ipytw_last_element' ) {
				if ( $n === '0' || $n === 0 ) {
					$n = '';
				}
			}
			$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
			if ( isset( $ipytw_settings_arr[ $n ][ $option_name ] ) ) {
				return $ipytw_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$ipytw_settings_arr = ipytw_optionGET( $source_settings_name );
			if ( isset( $ipytw_settings_arr[ $n ][ $option_name ] ) ) {
				return $ipytw_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "for_update_option":
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		default:
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
	}
}
/**
 * С версии 1.0.0
 * Возвращает то, что может быть результатом delete_blog_option, delete_option
 *
 * @param string $option_name - Rrequired
 * @param string $n - Optional
 * @param string $type - Optional (@since 1.3.0)
 * @param string $source_settings_name - Optional (@since 1.3.0)
 *
 * @return bool
 */
function ipytw_optionDEL( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $option_name === 'ipytw_status_sborki' || $option_name === 'ipytw_last_element' ) {
				if ( $n === '0' || $n === 0 ) {
					$n = '';
				}
			}
			$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
			unset( $ipytw_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'ipytw_settings_arr', $ipytw_settings_arr );
			} else {
				return update_option( 'ipytw_settings_arr', $ipytw_settings_arr );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$ipytw_settings_arr = ipytw_optionGET( $source_settings_name );
			unset( $ipytw_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $ipytw_settings_arr );
			} else {
				return update_option( $source_settings_name, $ipytw_settings_arr );
			}
		default:
			if ( $n === '0' || $n === 0 ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return delete_blog_option( get_current_blog_id(), $option_name );
			} else {
				return delete_option( $option_name );
			}
	}
}
/**
 * Проверяет наличие и при необходимости создаёт папку кэша плагина
 * @since 1.0.0
 *
 * @param string $name_dir - Rrequired
 *
 * @return bool
 */
function ipytw_check_dir( $name_dir ) {
	if ( ! is_dir( $name_dir ) ) {
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'ERROR: Нет папки ' . $name_dir . '! И создать не вышло! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
			return false;
		} else {
			error_log( 'ERROR: Создали папку ' . $name_dir . '! Файл: functions.php; Строка: ' . __LINE__, 0 );
			return true;
		}
	}
	return true;
}
/**
 * Проверяет, синхронили ли мы товар/картинку
 * @since 1.0.0
 *
 * @param string $meta_value - Rrequired
 * @param int $feed_id - Rrequired - @since 1.1.0
 * @param string $meta_key - Optional
 * @param string $post_type - Optional
 * @param bool $product_sku - Optional - @since 1.2.2
 *
 * @return int|false
 */
function ipytw_check_sync( $meta_value, $feed_id, $post_type = 'attachment', $meta_key = '_ipytw_import_feed_picture_url', $product_sku = false ) {
	if ( $post_type === 'product' ) {
		$meta_key = '_ipytw_feed_product_id';
		$args = [ 
			'post_type' => $post_type,
			'post_status' => [ 'publish', 'pending', 'draft', 'future' ], // 'trash'
			'fields' => 'ids',
			'relation' => 'AND',
			'meta_query' => [ 
				'relation' => 'AND',
				[ 
					'key' => $meta_key,
					'value' => $meta_value
				],
				[ 
					'key' => '_ipytw_feed_id',
					'value' => $feed_id
				]
			]
		];
	} else if ( $post_type === 'product_variation' ) {
		$meta_key = '_ipytw_feed_var_id';
		$args = [ 
			'post_type' => $post_type,
			'post_status' => [ 'publish', 'pending', 'draft', 'future' ], // 'trash'
			'fields' => 'ids',
			'relation' => 'AND',
			'meta_query' => [ 
				'relation' => 'AND',
				[ 
					'key' => $meta_key,
					'value' => $meta_value
				],
				[ 
					'key' => '_ipytw_feed_id',
					'value' => $feed_id
				]
			]
		];
	} else {
		$args = [ 
			'post_type' => $post_type,
			'fields' => 'ids',
			'relation' => 'AND',
			'meta_query' => [ 
				[ 
					'key' => $meta_key,
					'value' => $meta_value
				]
			]
		];
	}
	if ( $ids = get_posts( $args ) ) {
		$result = array_pop( $ids );
	} else {
		$result = false;
		// if ($product_sku === false) {
		//	$result = false;
		// } else {
		// 	if ($post_type === 'product') {
		// 		$isset_product_id = wc_get_product_id_by_sku($product_sku);
		// 		if ($isset_product_id > 0) {
		// 			$result = $isset_product_id;
		// 			new IPYTW_Error_Log(
		//	'NOTICE: Артикул '.$product_sku.' обнаружен у товара с $isset_product_id = '.
		// $isset_product_id.'; Файл: function.php; Строка: '.__LINE__
		// );
		// 		} else {
		// 			$result = false;
		// 		}
		// 	} else {
		// 		$result = false;
		// 	}
		// } /
	}
	return $result;
}
/**
 * Проверяет, существует ли в системе глобальный атрибут. Если не существует - создаёт
 * 
 * @see https://stackoverflow.com/questions/29549525/create-new-product-attribute-programmatically-in-woocommerce/51994543#51994543
 * @see https://stackoverflow.com/questions/58942156/woocommerce-programmatically-add-product-attributes-and-their-corresponding-valu
 *
 * @param string $name - Rrequired
 *
 * @return int|false
 */
function ipytw_create_product_attribute( $attribute_name ) {
	// $singular_name - для наглядности ибо get_taxonomy() 
	// ["name"] => string(24) "pa_второй-тест" 
	// ["singular_name"] => string(21) "Второй тест" 
	new IPYTW_Error_Log( 'INFO: Стартавала ipytw_create_product_attribute. $attribute_name = ' . $attribute_name . '; Файл: function.php; Строка: ' . __LINE__ );
	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
	} else {
		global $woocommerce;
		$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
	}
	$attribute_id = 0;
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
			if ( $one_tax->attribute_label == $attribute_name ) {
				$attribute_id = (int) $one_tax->attribute_id;
				$taxonomy_name = $one_tax->attribute_name;
				new IPYTW_Error_Log(
					'Есть глобальный атрибут "' . $attribute_name . '" с $taxonomy_name = ' . $taxonomy_name . ', $attribute_id = ' . $attribute_id . '; Файл: function.php; Строка: ' . __LINE__
				);
				break;
			}
		}
	}
	if ( $attribute_id === 0 ) {
		new IPYTW_Error_Log( 'Нет глобального атрибута $attribute_name = ' . $attribute_name . '; Файл: function.php; Строка: ' . __LINE__ );
	} else {
		new IPYTW_Error_Log( 'INFO: Перед return1; $attribute_id = ' . $attribute_id . ' gettype(' . gettype( $attribute_id ) . '); Файл: function.php; Строка: ' . __LINE__ );
		return $attribute_id;
	}

	$slug = wc_sanitize_taxonomy_name( $attribute_name ); // приводим к виду второй-тест
	if ( strlen( $slug ) >= 28 ) {
		$slug = ipytw_strlen_slug( $slug );
	}

	if ( wc_check_if_attribute_name_is_reserved( $slug ) ) {
		new IPYTW_Error_Log( 'ERROR: Ошибка создания атрибута. Название таксономии $slug = ' . $slug . ' зарезервировано; Файл: function.php; Строка: ' . __LINE__ );
		return false;
	}

	$attribute_id = wc_create_attribute( array(
		'name' => $attribute_name,
		'slug' => $slug,
		'type' => 'select',
		'order_by' => 'menu_order',
		'has_archives' => false, // Enable archives ==> true
	) );

	if ( is_wp_error( $attribute_id ) ) {
		$error_message = $attribute_id->get_error_message();
		$error_code = $attribute_id->get_error_code();
		new IPYTW_Error_Log( 'ERROR: Ошибка создания атрибута $attribute_name = ' . $attribute_name . '; $error_message = ' . $error_message . '; $error_code = ' . $error_code . '; Файл: function.php; Строка: ' . __LINE__ );
		return false;
	} else {
		new IPYTW_Error_Log( 'INFO: Глобальный атрибут ' . $attribute_name . '; $attribute_id = ' . $attribute_id . ' успешно создан; Файл: function.php; Строка: ' . __LINE__ );
	}

	// Register it as a wordpress taxonomy for just this session. Later on this will be loaded from the woocommerce taxonomy table.
	register_taxonomy(
		$slug,
		apply_filters( 'woocommerce_taxonomy_objects_' . $slug, [ 'product' ] ),
		apply_filters( 'woocommerce_taxonomy_args_' . $slug, [ 
			'labels' => [ 'name' => $attribute_name ],
			'hierarchical' => true,
			'show_ui' => false,
			'query_var' => true,
			'rewrite' => false,
		] )
	);

	// do_action('woocommerce_attribute_added', $id, $data);
	wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
	delete_transient( 'wc_attribute_taxonomies' ); // Clear caches

	new IPYTW_Error_Log( 'INFO: Перед return2; $attribute_id = ' . $attribute_id . ' gettype(' . gettype( $attribute_id ) . '); Файл: function.php; Строка: ' . __LINE__ );
	return $attribute_id;
}
/**
 * @since 1.1.1
 * 
 * Проверяет, существует ли значение у данного глобального атрибута. Если не существует - создаёт
 * 
 * @see https://stackoverflow.com/questions/29549525/create-new-product-attribute-programmatically-in-woocommerce/51994543#51994543
 * @see https://stackoverflow.com/questions/58942156/woocommerce-programmatically-add-product-attributes-and-their-corresponding-valu
 *
 * @param string $attribute_value - Rrequired
 * @param int $attribute_id - Optional
 * @param string $attribute_taxonomy - Optional
 *
 * @return array (2) (["id"]=> int ["slug"]=> string) / array(2) (["id"]=> false ["slug"]=> false)
 */
function ipytw_create_attribute_term( $attribute_value, $attribute_id = 0 /*, $attribute_taxonomy = ''*/) {
	if ( $attribute_id === 0 ) {
		return [ 'id' => false, 'slug' => false ];
	}
	$attribute_id = (int) $attribute_id;

	$attribute_taxonomy_name = wc_attribute_taxonomy_name_by_id( $attribute_id );

	// Look if there is already a term for this attribute?
	$term = get_term_by( 'name', $attribute_value, $attribute_taxonomy_name );

	if ( ! $term ) { //No, create new term.
		$term = wp_insert_term( $attribute_value, $attribute_taxonomy_name );
		if ( is_wp_error( $term ) ) {
			new IPYTW_Error_Log(
				'ERROR: Невозможно создать новый термин атрибута $attribute_value = ' . $attribute_value . ' таксономии $attribute_taxonomy_name = ' . $attribute_taxonomy_name . '; Файл: function.php; Строка: ' . __LINE__
			);
			$error_message = $term->get_error_message();
			$error_code = $term->get_error_code();
			new IPYTW_Error_Log(
				'ERROR: Ошибка создания термина атрибута $attribute_value = ' . $attribute_value . '; $error_message = ' . $error_message . '; $error_code = ' . $error_code . '; Файл: function.php; Строка: ' . __LINE__
			);
			return [ 'id' => false, 'slug' => false ];
		}
		$termId = $term['term_id'];
		$term_slug = get_term( $termId, $attribute_taxonomy_name )->slug; // Get the term slug
	} else {
		// Yes, grab it's id and slug
		$termId = $term->term_id;
		$term_slug = $term->slug;
	}
	return [ 'id' => $termId, 'slug' => $term_slug ];
}
/**
 * Возвращает размер файла на удалённом сервере
 * 
 * @since 1.2.0
 * @see https://yandex.ru/turbo/internet-technologies.ru/s/articles/opredelenie-razmera-udalennogo-fayla.html
 *
 * @param string $path - Rrequired
 * @param string $unit - Optional
 * @param int $round - Optional
 * @param string $show_unit - Optional
 * @param string $dec_point - Optional
 * @param string $thousands_sep - Optional
 *
 * @return int|null
 */
function ipytw_fsize( $path, $unit = 'B', $show_unit = 'no', $round = 2, $dec_point = '', $thousands_sep = '' ) {
	$result = null;
	if ( empty( $path ) ) {
		return $result;
	}
	$fp = fopen( $path, "r" );
	if ( false == $fp ) {
		return $result;
	}
	$inf = stream_get_meta_data( $fp );
	fclose( $fp );
	foreach ( $inf["wrapper_data"] as $v ) {
		if ( stristr( $v, "content-length" ) ) {
			$v = explode( ":", $v );
			$result = trim( $v[1] );
			switch ( $unit ) {
				case 'GB':
					$result = number_format( $result / 1073741824, $round, $dec_point, $thousands_sep );
					break;
				case 'MB':
					$result = number_format( $result / 1048576, $round, $dec_point, $thousands_sep );
					break;
				case 'KB':
					$result = number_format( $result / 1024, $round, $dec_point, $thousands_sep );
					break;
				case 'B':
					$result = number_format( $result / 1, $round, $dec_point, $thousands_sep );
					break;
				default:
					$result = number_format( $result / 1, $round, $dec_point, $thousands_sep );
			}
			if ( $show_unit === 'yes' ) {
				$result = $result . ' ' . $unit;
			}
			break;
		}
	}
	return $result;
}
/**
 * Проверяет, есть ли товар с таким SKU
 * 
 * @since 1.2.2
 * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-product-functions.html
 *
 * @param string $sku - Rrequired
 *
 * @return bool
 */
function ipytw_is_uniq_sku( $sku ) {
	if ( false === $sku || $sku === '' ) {
		return false;
	}
	if ( wc_get_product_id_by_sku( $sku ) > 0 ) {
		return true;
	} else {
		return false;
	}
}
/**
 * @since 1.2.3
 *
 * @param string $slug - Rrequired
 *
 * @return string
 */
function ipytw_strlen_slug( $slug ) {
	$slug = mb_substr( $slug, 0, 27 ); // 27 символом на слаг
	if ( strlen( $slug ) >= 28 ) { // если в итоге байт больше
		for ( $i = mb_strlen( $slug ); $i = 1; $i-- ) {
			$slug = mb_substr( $slug, 0, -1 );
			if ( strlen( $slug ) < 28 ) {
				break;
			}
		}
	}
	return $slug;
}
/**
 * Получает первый фид. Используется на случай если get-параметр feed_id не указан
 * 
 * @since 1.3.0
 *
 * @return string (feed ID or (string)'')
 */
function get_first_feed_id() {
	$ipytw_settings_arr = ipytw_optionGET( 'ipytw_settings_arr' );
	if ( ! empty( $ipytw_settings_arr ) ) {
		$v = array_key_first( $ipytw_settings_arr );
		// удалим фид с нулевым id (актуально для перехода со старых версий)
		if ( $v == '0' ) {
			ipytw_del_feed_zero();
			return get_first_feed_id();
		}
		// end удалим фид с нулевым id (актуально для перехода со старых версий)
		return $v;
	} else {
		return '';
	}
}
/**
 * Вместо file_get_contents используем curl
 * 
 * @since 1.3.0
 *
 * @param string $url - Rrequired
 * @param string $feed_id - Optional
 */
function ipytw_file_get_contents_curl( $url, $feed_id = false ) {
	$ch = curl_init(); // Инициализируем curl

	if ( $feed_id !== false ) {
		$ipytw_feed_login = ipytw_optionGET( 'ipytw_feed_login', $feed_id, 'set_arr' );
		$ipytw_feed_pwd = ipytw_optionGET( 'ipytw_feed_pwd', $feed_id, 'set_arr' );
		if ( ! empty( $ipytw_feed_login ) && ! empty( $ipytw_feed_pwd ) ) {
			$userpwd = $ipytw_feed_login . ':' . $ipytw_feed_pwd; // 'логин:пароль'
			curl_setopt( $ch, CURLOPT_USERPWD, $userpwd );
		}
	}

	curl_setopt( $ch, CURLOPT_URL, $url ); // Указываем ссылку
	curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE ); // подставляем Referer при перенаправлении
	curl_setopt( $ch, CURLOPT_HEADER, 0 ); // Не выводим заголовки
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); // Сохраним полученный результат в переменную 
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE ); // Идем по перенаправлению 
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false ); // Не проверяем SSL сертификат
	// Установим Useragent
	curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:69.0) Gecko/20100101 Firefox/69.0' );

	$getData = curl_exec( $ch ); // Выполняем запрос на сайт

	// Обработка результата выполнения запроса
	if ( ! $getData ) {
		return false;
	} else {
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( $http_code !== 200 ) {
			return false;
		}
	}
	curl_close( $ch ); // Закрываем curl

	return $getData; // Возвращаем из функции полученные данные
}

/**
 * Отправка запросов курлом
 * 
 * @version			2.1.4
 * @see				https://snipp.ru/php/curl
 * 
 * @param	string	$request_url - Rrequired
 * @param	mixed	$feed_id - Optional
 * @param	array	$postfields_arr - Optional
 * @param	array	$headers_arr - Optional
 * @param	string	$request_type - Optional
 * @param	array	$pwd_arr - Optional
 * @param	string	$encode_type - Optional
 * @param	int		$timeout - Optional
 * @param	string	$proxy - Optional // example: '165.22.115.179:8080'
 * @param	bool	$debug - Optional
 * @param	string	$sep - Optional
 * @param	string	$useragent - Optional
 * 
 * @return 	array	keys: errors, status, http_code, body, header_request, header_answer
 * 
 */
function ipytw_curl( $request_url,
	$feed_id = false,
	$postfields_arr = [],
	$headers_arr = [],
	$request_type = 'GET',
	$pwd_arr = [],
	$encode_type = 'json_encode',
	$timeout = 30,
	$proxy = '',
	$debug = false,
	$sep = PHP_EOL,
	$useragent = 'PHP Bot'
) {
	$curl = curl_init(); // инициализация cURL

	if ( $feed_id !== false ) {
		$ipytw_feed_login = ipytw_optionGET( 'ipytw_feed_login', $feed_id, 'set_arr' );
		$ipytw_feed_pwd = ipytw_optionGET( 'ipytw_feed_pwd', $feed_id, 'set_arr' );
		if ( ! empty( $ipytw_feed_login ) && ! empty( $ipytw_feed_pwd ) ) {
			$userpwd = $ipytw_feed_login . ':' . $ipytw_feed_pwd; // 'логин:пароль'
			curl_setopt( $curl, CURLOPT_USERPWD, $userpwd );
		}
	}

	if ( ! empty( $pwd_arr ) ) {
		if ( isset( $pwd_arr['login'] ) && isset( $pwd_arr['pwd'] ) ) {
			$userpwd = $pwd_arr['login'] . ':' . $pwd_arr['pwd']; // 'логин:пароль'
			curl_setopt( $curl, CURLOPT_USERPWD, $userpwd );
		}
	}

	curl_setopt( $curl, CURLOPT_URL, $request_url );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ); // проверять ли подлинность присланного сертификата сервера

	// задает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения. 
	// Значение 0 - без проверки, 
	// значение 1 означает проверку существования имени, 
	// значение 2 - кроме того, и проверку соответствия имени хоста. Рекомендуется 2.
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $curl, CURLOPT_USERAGENT, $useragent );
	// количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания
	curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $timeout );

	curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers_arr );

	$answer_arr = [];
	$answer_arr['body_request'] = null;
	if ( $request_type !== 'GET' ) {
		switch ( $encode_type ) {
			case 'json_encode':
				$answer_arr['body_request'] = json_encode( $postfields_arr );
				break;
			case 'http_build_query':
				$answer_arr['body_request'] = http_build_query( $postfields_arr );
				break;
			case 'dont_encode':
				$answer_arr['body_request'] = $postfields_arr;
				break;
			default:
				$answer_arr['body_request'] = json_encode( $postfields_arr );
		}
	}

	if ( $request_type === 'POST' ) { // отправляется POST запрос
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
		// $postfields_arr - массив с передаваемыми параметрами POST
	}

	if ( $request_type === 'DELETE' ) { // отправляется DELETE запрос
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
	}

	if ( $request_type === 'PUT' ) { // отправляется PUT запрос
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
		// http_build_query($postfields_arr, '', '&') // $postfields_arr - массив с передаваемыми параметрами POST
	}

	if ( ! empty( $proxy ) ) {
		curl_setopt( $curl, CURLOPT_TIMEOUT, 400 ); // зададим максимальное кол-во секунд для выполнения cURL-функций
		curl_setopt( $curl, CURLOPT_PROXY, $proxy ); // HTTP-прокси, через который будут направляться запросы
	}

	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // вернуть результат запроса, а не выводить в браузер
	curl_setopt( $curl, CURLOPT_HEADER, true ); // опция позволяет включать в ответ от сервера его HTTP - заголовки
	curl_setopt( $curl, CURLINFO_HEADER_OUT, true ); // true - для отслеживания строки запроса дескриптора

	$result = curl_exec( $curl ); // выполняем cURL

	// Обработка результата выполнения запроса
	if ( ! $result ) {
		$answer_arr['errors'] = 'Ошибка cURL: ' . curl_errno( $curl ) . ' - ' . curl_error( $curl );
		$answer_arr['body_answer'] = null;
	} else {
		$answer_arr['status'] = true; // true - получили ответ
		// Разделение полученных HTTP-заголовков и тела ответа
		$response_headers_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$response_headers = substr( $result, 0, $response_headers_size );
		$response_body = substr( $result, $response_headers_size );
		$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$answer_arr['http_code'] = $http_code;

		if ( $http_code == 200 ) {
			// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
			$decoded_body = json_decode( $response_body );
			$answer_arr['body_answer'] = $decoded_body;
		} else {
			// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
			if ( ! empty( $response_body ) ) {
				$decoded_body = json_decode( $response_body );
				if ( $decoded_body != null ) {
					// Если ответ содержит тело в формате JSON, 
					// то возвращаем отформатированное тело в формате JSON
					$answer_arr['body_answer'] = $decoded_body;
				} else {
					// Если не удалось декодировать JSON либо тело имеет другой формат, 
					// то возвращаем преобразованное тело ответа
					$answer_arr['body_answer'] = htmlspecialchars( $response_body );
				}
			} else {
				$answer_arr['body_answer'] = null;
			}
		}
		// Вывод необработанных HTTP-заголовков запроса и ответа
		$answer_arr['header_request'] = curl_getinfo( $curl, CURLINFO_HEADER_OUT ); // Заголовки запроса
		$answer_arr['header_answer'] = $response_headers; // Заголовки ответа
	}

	curl_close( $curl );
	return $answer_arr;
}

/**
 * Удаление всех постов
 * 
 * @since 1.9.3
 *
 * @param string $feed_id - Rrequired
 *
 * @return bool
 */
function ipytw_delete_all_imported_products( $feed_id ) {
	$args = [ 
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'relation' => 'AND',
		'fields' => 'ids',
		'meta_query' => [ 
			[ 
				'key' => '_ipytw_feed_id',
				'value' => (string) $feed_id
			]
		]
	];

	$query = new WP_Query( $args );
	$old_ids_arr = [];
	if ( $query->have_posts() ) {
		$old_ids_arr = $query->posts; // id товаров, которые импортированы
	} else {
		return false;
	}
	foreach ( $old_ids_arr as $id ) {
		wooс_delete_product( $id );
	}
	return true;
}

/**
 * Summary of sync_list_cat_feed
 * 
 * @param object $xml_categories_object
 * 
 * @return array
 */
function sync_list_cat_feed( $xml_categories_object ) {
	$feed_categories_arr = []; // данные по всем категориям в фиде
	$feed_exists_cat_id_arr = []; // id всех существующих категорий в фиде. Нужен для проверки.
	for ( $i = 0; $i < count( $xml_categories_object->children() ); $i++ ) {
		$category_name = $xml_categories_object->category[ $i ];
		$xml_category_attr_object = $xml_categories_object->category[ $i ]->attributes();

		$attr_id = $xml_category_attr_object->id;
		$attr_parent_id = $xml_category_attr_object->parentId; // если нет атрибута parentId вернёт null
		if ( null === $attr_parent_id ) {
			$attr_parent_id = 0;
		}
		$feed_categories_arr[] = [ 
			'id' => (int) $attr_id,
			'parent_id' => (int) $attr_parent_id,
			'name' => (string) $category_name
		];
		array_push( $feed_exists_cat_id_arr, (int) $attr_id );
	}
	new IPYTW_Error_Log( $feed_categories_arr );
	return $feed_categories_arr;
}