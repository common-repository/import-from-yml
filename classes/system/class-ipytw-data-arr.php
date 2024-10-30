<?php
/**
 * Set and Get the Plugin Data
 *
 * @package                 iCopyDoc Plugins (v1, core 16-08-2023)
 * @subpackage              Import from YML
 * @since                   0.1.0
 * 
 * @version                 3.1.5 (29-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       array       $data_arr - Optional
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Data_Arr {
	/**
	 * The plugin data array
	 *
	 * @var array
	 */
	private $data_arr = [];

	/**
	 * Summary of __construct
	 * 
	 * @param array $data_arr - Optional
	 */
	public function __construct( $data_arr = [] ) {
		if ( empty( $data_arr ) ) {
			$this->data_arr = [ 
				[ // 0 => 'ipytw_status_cron', 1 => 'off', 2 => 'private', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_status_cron',
					'def_val' => 'off',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_date_sborki', 1 => '0000000001', 2 => 'private', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_date_sborki',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_date_sborki_end', 1 => '0000000001', 2 => 'private', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_date_sborki_end',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_date_save_set', 1 => '0000000001', 2 => 'private', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_date_save_set',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_count_elements', -1, 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_count_elements',
					'def_val' => -1,
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_count_offers', 1 => 0, 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_count_offers',
					'def_val' => 0,
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // 0 => 'ipytw_list_import_err_code_arr', 1 => [], 2 => 'public', // TODO: Удалить потом эту строку	
					'opt_name' => 'ipytw_list_import_err_code_arr',
					'def_val' => [],
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				// ------------------- ОСНОВНЫЕ НАСТРОЙКИ -------------------
				[ // 0 => 'ipytw_run_cron', 1 => 'off', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_run_cron',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Start automatic import', 'import-from-yml' ),
						'desc' => sprintf( '%s. %s "%s"',
							__( 'Products import interval from feed', 'import-from-yml' ),
							__( 'At the end of the import, the parameter will change its value to', 'import-from-yml' ),
							__( 'Disabled', 'import-from-yml' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-from-yml' ) ],
							[ 
								'value' => 'once',
								'text' => sprintf( '%s (%s)',
									__( 'Import once', 'import-from-yml' ),
									__( 'launch now', 'import-from-yml' )
								)
							],
							[ 'value' => 'hourly', 'text' => __( 'Hourly', 'import-from-yml' ) ],
							[ 'value' => 'three_hours', 'text' => __( 'Every three hours', 'import-from-yml' ) ],
							[ 'value' => 'six_hours', 'text' => __( 'Every six hours', 'import-from-yml' ) ],
							[ 'value' => 'twicedaily', 'text' => __( 'Twice a day', 'import-from-yml' ) ],
							[ 'value' => 'daily', 'text' => __( 'Daily', 'import-from-yml' ) ],
							[ 'value' => 'every_two_days', 'text' => __( 'Every two days', 'import-from-yml' ) ],
							[ 'value' => 'week', 'text' => __( 'Once a week', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_feed_assignment', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_feed_assignment',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Feed assignment', 'import-from-yml' ),
						'desc' => __( "Doesn't affect imports. Inner note for your convenience", "import-from-yml" ),
						'placeholder' => __( 'For Yandex Market', 'import-from-yml' )
					]
				],
				[ // 0 => 'ipytw_url_yml_file', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_url_yml_file',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text_and_file_btn',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'YML File', 'import-from-yml' ),
						'desc' => __(
							'Specify URL to the YML feed in the field above or upload the file from your computer using the button below',
							'import-from-yml'
						),
						'placeholder' => 'https://site.ru/feed.yml'
					]
				],
				[ // 0 => 'ipytw_feed_login', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_feed_login',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Login', 'import-from-yml' ) . ' ' . __( 'to access the feed', 'import-from-yml' ),
						'desc' => __( "Don't fill in if the feed is freely available", "import-from-yml" ),
						'placeholder' => ''
					]
				],
				[ // 0 => 'ipytw_feed_pwd', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_feed_pwd',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Password', 'import-from-yml' ) . ' ' . __( 'to access the feed', 'import-from-yml' ),
						'desc' => __( "Don't fill in if the feed is freely available", "import-from-yml" ),
						'placeholder' => ''
					]
				],
				[ // 0 => 'ipytw_when_import_category', 1 => 'always', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_when_import_category',
					'def_val' => 'always',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'When to import categories', 'import-from-yml' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'always', 'text' => __( 'Always', 'import-from-yml' ) ],
							[ 'value' => 'once', 'text' => __( 'Import once', 'import-from-yml' ) ],
							[ 'value' => 'disabled', 'text' => __( 'Never', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_product_was_sync', 1 => 'price_only', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_product_was_sync',
					'def_val' => 'price_only',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'If the product was previously synced', 'import-from-yml' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'whole', 'text' => __( 'Update whole', 'import-from-yml' ) ],
							[ 'value' => 'whole_except_cat', 'text' => __( 'Update whole except categories', 'import-from-yml' ) ],
							[ 'value' => 'price_only', 'text' => __( 'Update price only', 'import-from-yml' ) ],
							[ 'value' => 'stock_only', 'text' => __( 'Update stock only', 'import-from-yml' ) ],
							[ 'value' => 'price_and_stock', 'text' => __( 'Update price and stock only', 'import-from-yml' ) ],
							[ 'value' => 'dont_update', 'text' => __( "Don't update this product", "import-from-yml" ) ]
						]
					]
				],
				[ // 0 => 'ipytw_post_status', 1 => 'publish', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_post_status',
					'def_val' => 'publish',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Set post status after import products', 'import-from-yml' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'publish', 'text' => __( 'Publish', 'import-from-yml' ) ],
							[ 'value' => 'draft', 'text' => __( 'Draft', 'import-from-yml' ) ],
							[ 'value' => 'pending', 'text' => __( 'Pending', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_step_import', 1 => 25, 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_step_import',
					'def_val' => 25,
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Step of import', 'import-from-yml' ),
						'desc' =>
							sprintf( '%s. %s',
								__( 'The value affects the speed of import', 'import-from-yml' ),
								__( 'If you are having trouble importing, try reduce the value in this field', 'import-from-yml' )
							),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => '10', 'text' => '10 ' . __( 'sec', 'import-from-yml' ) ],
							[ 'value' => '15', 'text' => '15 ' . __( 'sec', 'import-from-yml' ) ],
							[ 'value' => '20', 'text' => '20 ' . __( 'sec', 'import-from-yml' ) ],
							[ 'value' => '25', 'text' => '25 ' . __( 'sec', 'import-from-yml' ) ],
							[ 'value' => '30', 'text' => '30 ' . __( 'sec', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_whot_import', 1 => 'simple', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_whot_import',
					'def_val' => 'simple',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Whot import', 'import-from-yml' ),
						'desc' => __( 'Important. The feature is under testing. Use it with care', 'import-from-yml' ),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'simple', 'text' => __( 'Only simple products', 'import-from-yml' ) ],
							[ 'value' => 'all', 'text' => __( 'Simple & Variable products', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_behaviour_one_variation', 1 => 'skip', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_behaviour_one_variation',
					'def_val' => 'skip',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'If only one variation', 'import-from-yml' ),
						'desc' => __( 'Important. The feature is under testing. Use it with care', 'import-from-yml' ),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'skip', 'text' => __( 'Skip', 'import-from-yml' ) ],
							[ 'value' => 'add_as_simple', 'text' => __( 'Add as simple product', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_external', 1 => 'disabled', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_external',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Import all products as external', 'import-from-yml' ),
						'desc' => __(
							'Important. The feature is under testing. Only simple products from YML will be imported as external products',
							'import-from-yml'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-from-yml' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_partner_link', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_partner_link',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Partner link', 'import-from-yml' ),
						'desc' => sprintf( '%s "%s" %s "%s"',
							__( 'This setting works only if', 'import-from-yml' ),
							__( 'Import all products as external', 'import-from-yml' ),
							__( 'is selected in the', 'import-from-yml' ),
							__( 'Enabled', 'import-from-yml' )
						),
						'placeholder' => '?partner=12345'
					]
				],
				[ // 0 => 'ipytw_description_into', 1 => 'full', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_description_into',
					'def_val' => 'full',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Description of products to import into', 'import-from-yml' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'full', 'text' => __( 'Full description', 'import-from-yml' ) ],
							[ 'value' => 'excerpt', 'text' => __( 'Short description', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_barcode', 1 => 'disabled', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_barcode',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Barcode', 'import-from-yml' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( "Don't import", "import-from-yml" ) ],
							[ 
								'value' => 'global_unique_id',
								'text' => sprintf( '%s "GTIN, UPC, EAN or ISBN"',
									__( 'Import to the standard WooCommerce field', 'import-from-yml' )
								)
							],
							[ 'value' => 'global_attr', 'text' => __( 'Import to global attribute', 'import-from-yml' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Import to post_meta', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_barcode_post_meta_value', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_barcode_post_meta_value',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => '',
						'desc' => sprintf( '%s <span class="ipytw_bold">"%s"</span> %s',
							__( 'If selected', 'import-from-yml' ),
							__( 'Import to post_meta', 'import-from-yml' ),
							__( 'do not forget to fill out this field', 'import-from-yml' )
						),
						'placeholder' => ''
					]
				],
				[ // 0 => 'ipytw_source_sku', 1 => 'disabled', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_source_sku',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'SKU', 'import-from-yml' ),
						'desc' => __( 'Select the feed element, which contains the SKU', 'import-from-yml' ),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( "Don't import", "import-from-yml" ) ],
							[ 'value' => 'vendor_code', 'text' => 'vendor_code' ],
							[ 'value' => 'shop_sku', 'text' => 'shop_sku' ],
							[ 'value' => 'sku', 'text' => 'sku' ],
							[ 'value' => 'article', 'text' => 'article' ],
							[ 'value' => 'product_id', 'text' => __( 'Product ID', 'import-from-yml' ) ],
							[ 'value' => 'param_artikul', 'text' => 'param name="Артикул"' ]
						]
					]
				],
				[ // 0 => 'ipytw_add_pref_to_sku', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_add_pref_to_sku',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => sprintf( '%s %s',
							__( 'Add a prefix to', 'import-from-yml' ),
							__( 'SKU', 'import-from-yml' )
						),
						'desc' => sprintf( '%s %s',
							__( 'Add a prefix to', 'import-from-yml' ),
							__( 'SKU', 'import-from-yml' )
						),
						'placeholder' => 'sp1_'
					]
				],
				[ // 0 => 'ipytw_params_separator', 1 => '', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_params_separator',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => sprintf( '%s param',
							__( 'Value separator for', 'import-from-yml' )
						),
						'desc' => __(
							'If there can be several values in one "param" element at once, specify the separator',
							'import-from-yml'
						),
						'placeholder' => '#$'
					]
				],
				[ // 0 => 'ipytw_find_sale_price', 1 => 'enabled', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_find_sale_price',
					'def_val' => 'enabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Sale price', 'import-from-yml' ) . ' (oldprice)',
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'enabled', 'text' => __( 'Import', 'import-from-yml' ) ],
							[ 'value' => 'disabled', 'text' => __( "Don't import", "import-from-yml" ) ]
						]
					]
				],
				[ // 0 => 'ipytw_if_isset_sku', 1 => 'update', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_if_isset_sku',
					'def_val' => 'update',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'If there is a product on the site with the same SKU', 'import-from-yml' ),
						'desc' => __(
							'Choose what to do if, when importing products, an product with the same SKU as in the feed is found on your website',
							'import-from-yml'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( "Don't import", "import-from-yml" ) ],
							[ 'value' => 'update', 'text' => __( 'Replace product on your site', 'import-from-yml' ) ],
							[ 'value' => 'without_sku', 'text' => __( 'Import without SKU', 'import-from-yml' ) ]
						]
					]
				],
				[ // 0 => 'ipytw_missing_product', 1 => 'disabled', 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_missing_product',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => sprintf( '%s <br /><small><i>(%s</i>!)</small>',
							__( 'If the product is not in the feed', 'import-from-yml' ),
							__( 'Important. The feature is under testing. Use it with care', 'import-from-yml' )
						),
						'desc' => sprintf( '%s<br /><strong>**%s!</strong> %s',
							__( 'What if a previously imported product is missing from the feed', 'import-from-yml' ),
							__( 'Important', 'import-from-yml' ),
							__( 'The function is under testing and may cause the server to freeze', 'import-from-yml' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Nothing', 'import-from-yml' ) ],
							[ 
								'value' => 'del',
								'text' => __( 'Delete the product, but do not delete its attachments', 'import-from-yml' )
							],
							[ 
								'value' => 'del_with_pic',
								'text' => __( 'Delete the product and its attachments', 'import-from-yml' ) . ' **'
							],
							[ 
								'value' => 'out_of_stock',
								'text' => sprintf( '%s "%s"',
									__( 'Set the status', 'import-from-yml' ),
									__( 'Not in stock', 'import-from-yml' )
								)
							]
						]
					]
				],
				[ // 0 => 'ipytw_fsize_limit', 1 => 5, 2 => 'public', // TODO: Удалить потом эту строку
					'opt_name' => 'ipytw_fsize_limit',
					'def_val' => 5,
					'mark' => 'public',
					'required' => true,
					'type' => 'number',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Skip images larger than', 'import-from-yml' ),
						'desc' => sprintf( '%s (MB) <strong><i>(%s: 5)',
							__( 'Skip images larger than', 'import-from-yml' ),
							__( 'Default', 'import-from-yml' )
						),
						'placeholder' => '5'
					]
				]
			];
		} else {
			$this->data_arr = $data_arr;
		}

		$this->data_arr = apply_filters( 'ipytw_f_set_default_feed_settings_result_arr', $this->get_data_arr() );
	}

	/**
	 * Get the plugin data array
	 * 
	 * @return array
	 */
	public function get_data_arr() {
		return $this->data_arr;
	}

	/**
	 * Get data for tabs
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_data_for_tabs( $whot = 'dont_compare' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			// echo get_array_as_string($this->get_data_arr(), '<br/>');
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "wp_list_table":
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}
						break;
					case "dont_compare":
						$arr = $this->get_data_arr()[ $i ]['data'];
						$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
						$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
						$arr['type'] = $this->get_data_arr()[ $i ]['type'];
						$res_arr[] = $arr;
						break;
					default:
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
				}
			}
			// echo get_array_as_string($res_arr, '<br/>');
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_opts_name( $whot = '' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (array)
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)
	 */
	public function get_opts_name_and_def_date( $whot = 'all' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (stdClass object)
	 * 
	 * @param string $whot
	 * 
	 * @return array<stdClass>
	 */
	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {
		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$obj = new stdClass();
			$obj->name = $key;
			$obj->opt_def_value = $value;
			$res_arr[] = $obj; // unit obj
			unset( $obj );
		}
		return $res_arr;
	}
}